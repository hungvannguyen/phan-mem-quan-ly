<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\DoctorateInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorateInfoService implements ExportServiceContract
{
    /**
     * Export doctorate information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new DoctorateInfoExport($filters);
        return $export->download();
    }
}
