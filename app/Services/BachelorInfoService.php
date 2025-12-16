<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\BachelorInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BachelorInfoService implements ExportServiceContract
{
    /**
     * Export bachelor information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new BachelorInfoExport($filters);
        return $export->download();
    }
}
