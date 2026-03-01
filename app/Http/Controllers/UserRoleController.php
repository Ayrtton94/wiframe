<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleUpdateRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Inertia\Inertia;
use Inertia\Response;

class UserRoleController extends Controller
{
    /**
     * Display a listing of users and available roles.
     */
    public function index(): Response
    {
        $users = User::query()
            ->with('warehouses:id,name,code')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'warehouse_ids' => $user->warehouses->pluck('id')->values(),
            ]);

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Users/Roles', [
            'users' => $users,
            'roles' => $roles,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the selected role for the given user.
     */
    public function update(UserRoleUpdateRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->syncRoles([$validated['role']]);
        $user->warehouses()->sync($validated['warehouse_ids'] ?? []);

        return back()->with('success', 'Rol y almacenes actualizados correctamente.');
    }
}
