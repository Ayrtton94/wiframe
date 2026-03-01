<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiveTransferRequest;
use App\Http\Requests\ShipTransferRequest;
use App\Http\Requests\StoreTransferRequest;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Store;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransferController extends Controller
{
    /**
     * Display a listing of transfers.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $assignedWarehouseIds = $user->hasRole('admin')
            ? null
            : $user->warehouses()->pluck('warehouses.id');

        $transfersQuery = Transfer::query()
            ->with(['fromWarehouse:id,name', 'toWarehouse:id,name'])
            ->latest();

        if ($assignedWarehouseIds !== null) {
            $transfersQuery->where(function ($query) use ($assignedWarehouseIds) {
                $query->whereIn('from_warehouse_id', $assignedWarehouseIds)
                    ->orWhereIn('to_warehouse_id', $assignedWarehouseIds);
            });
        }

        $transfers = $transfersQuery
            ->paginate(15)
            ->withQueryString();

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);

        $products = Store::query()
            ->orderBy('name_product')
            ->get(['id', 'code_product', 'name_product']);

        $products = Store::query()
            ->orderBy('name_product')
            ->get(['id', 'code_product', 'name_product']);

        return Inertia::render('Transfers/Index', [
            'transfers' => $transfers,
            'warehouses' => $warehouses,
            'products' => $products,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Store a newly created transfer request.
     */
    public function store(StoreTransferRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');

            if (! $assignedWarehouseIds->contains($validated['from_warehouse_id']) || ! $assignedWarehouseIds->contains($validated['to_warehouse_id'])) {
                throw ValidationException::withMessages([
                    'warehouse' => 'Solo puedes crear traslados usando almacenes asignados a tu usuario.',
                ]);
            }
        }

        $transfer = DB::transaction(function () use ($validated, $request) {
            $transfer = Transfer::create([
                'code' => 'TRF-'.strtoupper((string) str()->ulid()),
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'status' => 'requested',
                'requested_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'store_id' => $item['store_id'],
                    'kilos_requested' => (float) ($item['kilos'] ?? 0),
                    'metros_requested' => (float) ($item['metros'] ?? 0),
                ]);
            }

            return $transfer;
        });

        return redirect()
            ->route('transfers.show', $transfer)
            ->with('success', 'Traslado solicitado correctamente.');
    }

    /**
     * Display the specified transfer.
     */
    public function show(Transfer $transfer)
    {
        $transfer->load([
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'items.store:id,code_product,name_product,color,fabric_type',
            'requester:id,name',
            'shipper:id,name',
            'receiver:id,name',
        ]);

        return Inertia::render('Transfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    /**
     * Ship the transfer and discount stock from origin warehouse.
     */
    public function ship(ShipTransferRequest $request, Transfer $transfer)
    {
        if (! in_array($transfer->status, ['requested', 'approved'], true)) {
            throw ValidationException::withMessages([
                'transfer' => 'Este traslado no se puede despachar en su estado actual.',
            ]);
        }

        DB::transaction(function () use ($transfer, $request) {
            $transfer->load('items');

            foreach ($transfer->items as $item) {
                $stock = WarehouseStock::query()
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->where('store_id', $item->store_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw ValidationException::withMessages([
                        'stock' => "No hay stock configurado para el producto {$item->store_id} en el almacén origen.",
                    ]);
                }

                if ((float) $stock->kilos_available < (float) $item->kilos_requested) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuficiente en kilos para el producto {$item->store_id}.",
                    ]);
                }

                if ((float) $stock->metros_available < (float) $item->metros_requested) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuficiente en metros para el producto {$item->store_id}.",
                    ]);
                }

                $stock->decrement('kilos_available', $item->kilos_requested);
                $stock->decrement('metros_available', $item->metros_requested);

                $item->update([
                    'kilos_shipped' => $item->kilos_requested,
                    'metros_shipped' => $item->metros_requested,
                ]);
            }

            $transfer->update([
                'status' => 'shipped',
                'shipped_by' => $request->user()->id,
                'shipped_at' => now(),
                'notes' => $request->validated('notes') ?? $transfer->notes,
            ]);
        });

        return back()->with('success', 'Traslado despachado y stock descontado del almacén origen.');
    }

    /**
     * Receive transfer and increase stock at destination warehouse.
     */
    public function receive(ReceiveTransferRequest $request, Transfer $transfer)
    {
        if ($transfer->status !== 'shipped') {
            throw ValidationException::withMessages([
                'transfer' => 'Solo los traslados despachados se pueden recepcionar.',
            ]);
        }

        $receivedItemsById = collect($request->validated('items'))
            ->keyBy('transfer_item_id');

        DB::transaction(function () use ($transfer, $request, $receivedItemsById) {
            $transfer->load('items');

            $isPartial = false;

            foreach ($transfer->items as $item) {
                $received = $receivedItemsById->get($item->id);

                if (! $received) {
                    throw ValidationException::withMessages([
                        'items' => 'Debes confirmar la recepción de todos los productos del traslado.',
                    ]);
                }

                $kilosReceived = (float) ($received['kilos_received'] ?? 0);
                $metrosReceived = (float) ($received['metros_received'] ?? 0);

                if ($kilosReceived > (float) $item->kilos_shipped) {
                    throw ValidationException::withMessages([
                        "items.{$item->id}.kilos_received" => 'No puedes recepcionar más kilos que los despachados.',
                    ]);
                }

                if ($metrosReceived > (float) $item->metros_shipped) {
                    throw ValidationException::withMessages([
                        "items.{$item->id}.metros_received" => 'No puedes recepcionar más metros que los despachados.',
                    ]);
                }

                $destinationStock = WarehouseStock::query()
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->where('store_id', $item->store_id)
                    ->lockForUpdate()
                    ->first();

                if (! $destinationStock) {
                    $destinationStock = WarehouseStock::create([
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'store_id' => $item->store_id,
                        'kilos_available' => 0,
                        'metros_available' => 0,
                    ]);
                }

                $destinationStock->increment('kilos_available', $kilosReceived);
                $destinationStock->increment('metros_available', $metrosReceived);

                $item->update([
                    'kilos_received' => $kilosReceived,
                    'metros_received' => $metrosReceived,
                ]);

                if ($kilosReceived < (float) $item->kilos_shipped || $metrosReceived < (float) $item->metros_shipped) {
                    $isPartial = true;
                }
            }

            $transfer->update([
                'status' => $isPartial ? 'received_partial' : 'received',
                'received_by' => $request->user()->id,
                'received_at' => now(),
                'notes' => $request->validated('notes') ?? $transfer->notes,
            ]);
        });

        return back()->with('success', 'Recepción registrada correctamente en almacén destino.');
    }
}