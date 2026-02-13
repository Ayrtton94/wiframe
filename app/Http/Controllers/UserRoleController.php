<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleUpdateRequest;
use App\Models\Role;
use App\Models\User;
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
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ]);

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return Inertia::render('Users/Roles', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the selected role for the given user.
     */
    public function update(UserRoleUpdateRequest $request, User $user)
    {
        $user->syncRoles([$request->validated('role')]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }
}
