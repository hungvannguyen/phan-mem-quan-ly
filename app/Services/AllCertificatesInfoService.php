<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\AllCertificatesInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AllCertificatesInfoService implements ExportServiceContract
{
    /**
     * Export all certificates information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new AllCertificatesInfoExport($filters);
        return $export->download();
    }
}
