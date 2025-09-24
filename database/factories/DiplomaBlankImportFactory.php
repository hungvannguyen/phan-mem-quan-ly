<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\DiplomaBlankImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiplomaBlankImport>
 */
class DiplomaBlankImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromNumber = $this->faker->numberBetween(1, 1000);
        $quantity = $this->faker->numberBetween(10, 500);
        $toNumber = $fromNumber + $quantity - 1;

        return [
            'type_id' => 1, // Sẽ cần tham chiếu đến DiplomaBlankType hợp lệ
            'document_reference' => 'Số ' . $this->faker->numberBetween(100, 999) . '/QĐ-X02 ngày ' . $this->faker->date('d/m/Y'),
            'issue_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'import_date' => now()->format('Y-m-d'),
            'total_quantity' => $quantity,
            'prefix' => $this->faker->randomElement(['A.', 'B.', 'C.', null]),
            'suffix' => $this->faker->randomElement(['/X02CN', '/X02ĐT', '/X02CĐ', null]),
            'from_number' => str_pad($fromNumber, 5, '0', STR_PAD_LEFT),
            'to_number' => str_pad($toNumber, 5, '0', STR_PAD_LEFT),
            'status' => $this->faker->randomElement([
                ImportStatus::PENDING->value,
                ImportStatus::PROCESSING->value,
                ImportStatus::COMPLETED->value,
                ImportStatus::FAILED->value
            ]),
            'processed_count' => function (array $attributes) {
                $status = $attributes['status'];
                $totalQuantity = $attributes['total_quantity'];

                switch ($status) {
                    case ImportStatus::PENDING->value:
                        return 0;
                    case ImportStatus::PROCESSING->value:
                        return $this->faker->numberBetween(1, $totalQuantity - 1);
                    case ImportStatus::COMPLETED->value:
                        return $totalQuantity;
                    case ImportStatus::FAILED->value:
                        return $this->faker->numberBetween(0, $totalQuantity - 1);
                    default:
                        return 0;
                }
            },
            'last_processed_serial' => function (array $attributes) {
                if ($attributes['processed_count'] == 0) {
                    return null;
                }

                $lastNum = intval($attributes['from_number']) + $attributes['processed_count'] - 1;
                $lastNumPadded = str_pad($lastNum, 5, '0', STR_PAD_LEFT);

                return ($attributes['prefix'] ?? '') . $lastNumPadded . ($attributes['suffix'] ?? '');
            },
            'error_message' => function (array $attributes) {
                return $attributes['status'] === ImportStatus::FAILED->value ?
                    $this->faker->randomElement([
                        'Lỗi kết nối database',
                        'Dữ liệu không hợp lệ',
                        'Timeout trong quá trình xử lý',
                        'Lỗi duplicate serial number'
                    ]) : null;
            },
            'started_at' => function (array $attributes) {
                return in_array($attributes['status'], [
                    ImportStatus::PROCESSING->value,
                    ImportStatus::COMPLETED->value,
                    ImportStatus::FAILED->value
                ]) ? $this->faker->dateTimeBetween('-1 week', 'now') : null;
            },
            'completed_at' => function (array $attributes) {
                return $attributes['status'] === ImportStatus::COMPLETED->value ?
                    $this->faker->dateTimeBetween($attributes['started_at'] ?? '-1 week', 'now') : null;
            },
        ];
    }

    /**
     * State cho import đang chờ xử lý
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ImportStatus::PENDING->value,
                'processed_count' => 0,
                'last_processed_serial' => null,
                'error_message' => null,
                'started_at' => null,
                'completed_at' => null,
            ];
        });
    }

    /**
     * State cho import đang xử lý
     */
    public function processing()
    {
        return $this->state(function (array $attributes) {
            $processedCount = $this->faker->numberBetween(1, $attributes['total_quantity'] - 1);
            $lastNum = intval($attributes['from_number']) + $processedCount - 1;
            $lastNumPadded = str_pad($lastNum, 5, '0', STR_PAD_LEFT);
            $lastSerial = ($attributes['prefix'] ?? '') . $lastNumPadded . ($attributes['suffix'] ?? '');

            return [
                'status' => ImportStatus::PROCESSING->value,
                'processed_count' => $processedCount,
                'last_processed_serial' => $lastSerial,
                'error_message' => null,
                'started_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
                'completed_at' => null,
            ];
        });
    }

    /**
     * State cho import đã hoàn thành
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            $lastNum = intval($attributes['to_number']);
            $lastNumPadded = str_pad($lastNum, 5, '0', STR_PAD_LEFT);
            $lastSerial = ($attributes['prefix'] ?? '') . $lastNumPadded . ($attributes['suffix'] ?? '');
            $startedAt = $this->faker->dateTimeBetween('-1 week', '-1 day');

            return [
                'status' => ImportStatus::COMPLETED->value,
                'processed_count' => $attributes['total_quantity'],
                'last_processed_serial' => $lastSerial,
                'error_message' => null,
                'started_at' => $startedAt,
                'completed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }

    /**
     * State cho import bị lỗi
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            $processedCount = $this->faker->numberBetween(0, $attributes['total_quantity'] - 1);
            $lastSerial = null;

            if ($processedCount > 0) {
                $lastNum = intval($attributes['from_number']) + $processedCount - 1;
                $lastNumPadded = str_pad($lastNum, 5, '0', STR_PAD_LEFT);
                $lastSerial = ($attributes['prefix'] ?? '') . $lastNumPadded . ($attributes['suffix'] ?? '');
            }

            return [
                'status' => ImportStatus::FAILED->value,
                'processed_count' => $processedCount,
                'last_processed_serial' => $lastSerial,
                'error_message' => $this->faker->randomElement([
                    'Lỗi kết nối database',
                    'Dữ liệu không hợp lệ',
                    'Timeout trong quá trình xử lý',
                    'Lỗi duplicate serial number',
                    'Không đủ dung lượng storage',
                ]),
                'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'completed_at' => null,
            ];
        });
    }
}
