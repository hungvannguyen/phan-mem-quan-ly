<?php

namespace App;

enum StudentGender: int
{
	case Male = 0;
	case Female = 1;

	public function label(): string
	{
		return match ($this) {
			self::Male => 'Nam',
			self::Female => 'Nữ',
		};
	}
}
