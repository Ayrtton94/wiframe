<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Employees;
use App\Http\Requests\EmployeeRequest;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employees::all()->map(function ($employee) {
            $employee->foto_url = $employee->foto ? asset('storage/' . $employee->foto) : null;
            return $employee;
        });
        return Inertia::render("Employees/Index", ['employees' => $employees]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("Employees/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('employees', 'public');
        }

        Employees::create($validated);

        return redirect()->route('employees.index')->with('success', 'Empleado creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employees $employees)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employees $employee)
    {
        $employee->foto_url = $employee->foto ? asset('storage/' . $employee->foto) : null;

        return Inertia::render("Employees/Edit", ['employee' => $employee]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, Employees $employee)
    {
        $validated = $request->validated();
        unset($validated['foto']);

        $photo = $request->file('foto');
        if ($photo) {
            // Eliminar foto anterior si existe
            if ($employee->foto) {
                \Storage::disk('public')->delete($employee->foto);
            }
            $validated['foto'] = $photo->store('employees', 'public');
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Empleado actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employees $employee)
    {
        if ($employee->foto) {
            \Storage::disk('public')->delete($employee->foto);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Empleado eliminado exitosamente.');
    }
}
