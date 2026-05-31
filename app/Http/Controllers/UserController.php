<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(20);
        return view('cms.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('cms.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('cms.users.index')->with('success', 'Tạo người dùng thành công!');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        return view('cms.users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    public function update(Request $request, User $user)
    {
        // Prevent editing super admin by non-super-admin
        if ($user->is_super_admin && !auth()->user()->is_super_admin) {
            abort(403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,id',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Prevent removing super_admin flag from self
        if (!$user->is_super_admin || auth()->user()->is_super_admin) {
            $data['is_super_admin'] = $request->boolean('is_super_admin');
        }

        $user->update($data);

        if (!$user->is_super_admin) {
            $user->roles()->sync($request->input('roles', []));
        }

        return redirect()->route('cms.users.index')->with('success', 'Cập nhật người dùng thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập!');
        }
        if ($user->is_super_admin && !auth()->user()->is_super_admin) {
            abort(403);
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('cms.users.index')->with('success', 'Đã xóa người dùng!');
    }
}
