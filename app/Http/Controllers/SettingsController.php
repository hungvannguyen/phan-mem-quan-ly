<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBlankType;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings page with diploma blank types and majors.
     */
    public function index(Request $request)
    {
        // Get diploma blank types
        $typesQuery = DiplomaBlankType::query();

        if ($request->filled('search_type')) {
            $search = $request->search_type;
            $typesQuery->where(function ($q) use ($search) {
                $q->where('type_name', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%");
            });
        }

        $sortByType = $request->get('sort_by_type', 'created_at');
        $sortOrderType = $request->get('sort_order_type', 'desc');
        $typesQuery->orderBy($sortByType, $sortOrderType);

        $perPageType = $request->get('per_page_type', 15);
        $types = $typesQuery->paginate($perPageType, ['*'], 'types_page')->withQueryString();

        // Get majors
        $majorsQuery = Major::query();

        if ($request->filled('search_major')) {
            $search = $request->search_major;
            $majorsQuery->where(function ($q) use ($search) {
                $q->where('major_name', 'like', "%{$search}%")
                    ->orWhere('major_code', 'like', "%{$search}%");
            });
        }

        $sortByMajor = $request->get('sort_by_major', 'created_at');
        $sortOrderMajor = $request->get('sort_order_major', 'desc');
        $majorsQuery->orderBy($sortByMajor, $sortOrderMajor);

        $perPageMajor = $request->get('per_page_major', 15);
        $majors = $majorsQuery->paginate($perPageMajor, ['*'], 'majors_page')->withQueryString();

        return view('settings.index', compact('types', 'majors'));
    }

    /**
     * Show the form for creating a new diploma blank type.
     */
    public function createType()
    {
        return view('settings.types.create');
    }

    /**
     * Store a newly created diploma blank type.
     */
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:255|unique:diploma_blank_types,type_name',
            'prefix' => 'required|string|max:20|unique:diploma_blank_types,prefix',
        ], [
            'type_name.required' => 'Tên loại văn bằng là bắt buộc',
            'type_name.unique' => 'Tên loại văn bằng đã tồn tại',
            'prefix.required' => 'Mã tiền tố là bắt buộc',
            'prefix.unique' => 'Mã tiền tố đã tồn tại',
        ]);

        DiplomaBlankType::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Thêm loại văn bằng mới thành công!');
    }

    /**
     * Show the form for editing the specified diploma blank type.
     */
    public function editType(DiplomaBlankType $type)
    {
        return view('settings.types.edit', compact('type'));
    }

    /**
     * Update the specified diploma blank type.
     */
    public function updateType(Request $request, DiplomaBlankType $type)
    {
        $validated = $request->validate([
            'type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diploma_blank_types')->ignore($type->type_id, 'type_id'),
            ],
            'prefix' => [
                'required',
                'string',
                'max:20',
                Rule::unique('diploma_blank_types')->ignore($type->type_id, 'type_id'),
            ],
        ], [
            'type_name.required' => 'Tên loại văn bằng là bắt buộc',
            'type_name.unique' => 'Tên loại văn bằng đã tồn tại',
            'prefix.required' => 'Mã tiền tố là bắt buộc',
            'prefix.unique' => 'Mã tiền tố đã tồn tại',
        ]);

        $type->update($validated);

        return redirect()->route('settings.types.edit', $type->type_id)
            ->with('success', 'Cập nhật loại văn bằng thành công!');
    }

    /**
     * Remove the specified diploma blank type.
     */
    public function destroyType(DiplomaBlankType $type)
    {
        // Check if the type is being used
        if ($type->diplomaBlanks()->count() > 0) {
            return back()->with('error', 'Không thể xóa loại văn bằng đang được sử dụng!');
        }

        $type->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Xóa loại văn bằng thành công!');
    }

    // ========== MAJOR CRUD METHODS ==========

    /**
     * Show the form for creating a new major.
     */
    public function createMajor()
    {
        return view('settings.majors.create');
    }

    /**
     * Store a newly created major.
     */
    public function storeMajor(Request $request)
    {
        $validated = $request->validate([
            'major_name' => 'required|string|max:255',
            'major_code' => 'required|string|max:20|unique:majors,major_code',
        ], [
            'major_name.required' => 'Tên ngành đào tạo là bắt buộc',
            'major_code.required' => 'Mã ngành là bắt buộc',
            'major_code.unique' => 'Mã ngành đã tồn tại',
        ]);

        Major::create($validated);

        return redirect()->route('settings.index')
            ->with('success', 'Thêm ngành đào tạo mới thành công!');
    }

    /**
     * Show the form for editing the specified major.
     */
    public function editMajor(Major $major)
    {
        return view('settings.majors.edit', compact('major'));
    }

    /**
     * Update the specified major.
     */
    public function updateMajor(Request $request, Major $major)
    {
        $validated = $request->validate([
            'major_name' => 'required|string|max:255',
            'major_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('majors')->ignore($major->major_id, 'major_id'),
            ],
        ], [
            'major_name.required' => 'Tên ngành đào tạo là bắt buộc',
            'major_code.required' => 'Mã ngành là bắt buộc',
            'major_code.unique' => 'Mã ngành đã tồn tại',
        ]);

        $major->update($validated);

        return redirect()->route('settings.majors.edit', $major->major_id)
            ->with('success', 'Cập nhật ngành đào tạo thành công!');
    }

    /**
     * Remove the specified major.
     */
    public function destroyMajor(Major $major)
    {
        // Check if the major is being used by students
        if ($major->students()->count() > 0) {
            return back()->with('error', 'Không thể xóa ngành đào tạo đang có sinh viên!');
        }

        $major->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Xóa ngành đào tạo thành công!');
    }
}
