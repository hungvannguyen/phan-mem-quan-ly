<?php

namespace App\Http\Controllers;

use App\Models\DiplomaBatche;
use Illuminate\Http\Request;

class EmbryoManagementController extends Controller
{
	public function index(Request $request)
	{

		$diplomaBatches = DiplomaBatche::latest()->paginate(10);

		if ($request->ajax()) {
			return view('components.embryos.table')->render();
		}

		return view('embryo-management', [
				'diplomaBatches' => $diplomaBatches,
		]);
	}

	public function create()
	{
		return view('embryo-create');
	}

	public function save(Request $request)
	{
		DiplomaBatche::create($request->validated());
		$diplomaBatches = DiplomaBatche::orderBy('created_at', 'desc')->pagination(10);

		return view('embryo-management',
				compact('diplomaBatches'));
	}

	public function embryo(DiplomaBatche $diplomaBatche) {}

	public function update(Request $request, DiplomaBatche $diplomaBatche)
	{
		$diplomaBatche->update($request->validated());

		$diplomaBatches = DiplomaBatche::orderBy('created_at', 'desc')->pagination(10);

		return view('embryo-management',
				compact('diplomaBatches'));
	}
}
