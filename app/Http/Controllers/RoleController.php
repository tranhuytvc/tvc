<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->latest()->get();
        return view('cms.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::$all;
        return view('cms.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $permIds = Permission::whereIn('slug', $request->permissions)->pluck('id');
            $role->permissions()->sync($permIds);
        }

        return redirect()->route('cms.roles.index')->with('success', 'Tạo vai trò thành công!');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::$all;
        $assigned = $role->permissions->pluck('slug')->toArray();
        return view('cms.roles.edit', compact('role', 'permissions', 'assigned'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        $permIds = $request->has('permissions')
            ? Permission::whereIn('slug', $request->permissions)->pluck('id')
            : collect();

        $role->permissions()->sync($permIds);

        return redirect()->route('cms.roles.index')->with('success', 'Cập nhật vai trò thành công!');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();
        return redirect()->route('cms.roles.index')->with('success', 'Đã xóa vai trò!');
    }
}
