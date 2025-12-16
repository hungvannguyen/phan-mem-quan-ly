<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\AdvancedPoliticalTheoryInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdvancedPoliticalTheoryInfoService implements ExportServiceContract
{
    /**
     * Export advanced political theory certificate information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new AdvancedPoliticalTheoryInfoExport($filters);
        return $export->download();
    }
}