<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Exports\IntermediatePoliticalTheoryInfoExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IntermediatePoliticalTheoryInfoService implements ExportServiceContract
{
    /**
     * Export intermediate political theory certificate information report
     *
     * @param array $data Must contain 'filters' key with filter array
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        $filters = $data['filters'] ?? [];

        $export = new IntermediatePoliticalTheoryInfoExport($filters);
        return $export->download();
    }
}
