<?php

namespace App\Enums;

enum StudentStatus: int
{
	case Studying = 0;
	case Graduate = 1;
	case DropOut = 2;

	public function label(): string
	{
		return match ($this) {
			self::Studying => 'Đang học',
			self::Graduate => 'Đã tốt nghiệp',
			self::DropOut => 'Bỏ học',
		};
	}
}
