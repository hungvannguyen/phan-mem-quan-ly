<?php

use App\Models\DiplomaBlankImport;
use App\Models\DiplomaBlankType;
use App\Models\DiplomaBlank;

// Find or create import record
$import = DiplomaBlankImport::first();
if (!$import) {
    $type = DiplomaBlankType::first();
    if ($type) {
        $import = DiplomaBlankImport::create([
            'document_reference' => 'CV-001/2024',
            'type_id' => $type->type_id,
            'quantity' => 100,
            'import_date' => now(),
            'issue_date' => now(),
            'status' => 2
        ]);
        echo "Created import record with ID: {$import->id}\n";
    }
}

// Update diploma blanks with import_id
if ($import) {
    $updated = DiplomaBlank::whereNull('import_id')->update(['import_id' => $import->id]);
    echo "Updated {$updated} diploma blanks with import_id: {$import->id}\n";
} else {
    echo "Could not create or find import record\n";
}