<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $permissions = Permission::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.permissions.index', compact('permissions', 'search'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create([
            'name' => strtolower(trim($validated['name'])),
            'guard_name' => 'web',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions')->ignore($permission->id)],
        ]);

        $permission->update([
            'name' => strtolower(trim($validated['name'])),
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}

