<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ExportServiceContract
{
    /**
     * Export document with given data
     *
     * @param array $data
     * @return BinaryFileResponse
     */
    public function export(array $data): BinaryFileResponse;
}
