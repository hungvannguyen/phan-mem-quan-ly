<?php

namespace App\Http\Controllers;

use App\Models\DamageReason;
use App\Models\DiplomaBatche;
use Illuminate\Http\Request;

class DamageReasonController extends Controller
{
	public function create()
	{
		return view('damage-reason-create');
	}

	public function save(Request $request)
	{
		DamageReason::create($request->validated());
		$damageReasons = DamageReason::orderBy('created_at', 'desc')->paginate(10);

		return view('embryo-management',
				compact('damageReasons'));
	}

	public function update(Request $request, DamageReason $damageReason)
	{
		$damageReason->update($request->validated());

		$damageReasons = DamageReason::orderBy('created_at', 'desc')->paginate(10);

		return view('embryo-management',
				compact('damageReasons'));
	}
}
