<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\MasterInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterInfoService implements ExportServiceContract
{
    /**
     * Export master information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new MasterInfoExport($filters);
        return $export->download();
    }
}
