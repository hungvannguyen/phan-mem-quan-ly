<?php

namespace App\Http\Controllers;

use App\Models\DamageReason;
use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
	public function create()
	{
		return view('damage-reason-create');
	}

	public function save(Request $request)
	{
		Training::create($request->validated());
		$trainings = Training::orderBy('created_at', 'desc')->paginate(10);

		return view('embryo-management',
				compact('trainings'));
	}

	public function update(Request $request, Training $training)
	{
		$training->update($request->validated());

		$trainings = Training::orderBy('created_at', 'desc')->paginate(10);

		return view('embryo-management',
				compact('trainings'));
	}
}
