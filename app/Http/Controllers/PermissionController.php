<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request)
    {
        // Chỉ admin mới truy cập được
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền quản lý permissions');
        }

        $query = Permission::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'category');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $permissions = $query->paginate(20);

        // Get all categories for filter
        $categories = Permission::distinct()->pluck('category');

        return view('permissions.index', compact('permissions', 'categories'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền tạo permission');
        }

        // Get all categories
        $categories = Permission::distinct()->pluck('category');

        return view('permissions.create', compact('categories'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền tạo permission');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Tên permission không được bỏ trống',
            'name.unique' => 'Tên permission đã tồn tại',
            'display_name.required' => 'Tên hiển thị không được bỏ trống',
            'category.required' => 'Danh mục không được bỏ trống',
        ]);

        Permission::create($validated);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Đã thêm permission mới thành công!');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền sửa permission');
        }

        // Get all categories
        $categories = Permission::distinct()->pluck('category');

        return view('permissions.edit', compact('permission', 'categories'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền sửa permission');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->permission_id . ',permission_id',
            'display_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Tên permission không được bỏ trống',
            'name.unique' => 'Tên permission đã tồn tại',
            'display_name.required' => 'Tên hiển thị không được bỏ trống',
            'category.required' => 'Danh mục không được bỏ trống',
        ]);

        $permission->update($validated);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Đã cập nhật permission thành công!');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền xóa permission');
        }

        // Check if permission is assigned to any role
        if ($permission->roles()->count() > 0) {
            return redirect()
                ->route('permissions.index')
                ->with('error', 'Không thể xóa permission này vì đang được gán cho ' . $permission->roles()->count() . ' role(s)');
        }

        $permission->delete();

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Đã xóa permission thành công!');
    }
}
