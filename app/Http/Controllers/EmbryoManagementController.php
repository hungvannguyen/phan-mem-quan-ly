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

	public function embryo() {}
}
