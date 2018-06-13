<?php
use GoatPen\Tasks\ImportCampaignStatsTask;

$import = new ImportCampaignStatsTask(
    UPLOADS_DIR . DIRECTORY_SEPARATOR . $this->file,
    ($this->payload['campaign_id'] ?? null),
    $this
);

$this->log(
    sprintf("Importing %d stats records for campaign '%s'", $import->getRecords()->count(), $import->getCampaign()->name)
);

foreach ($import->getRecords() as $record) {
    try {
        $import->processRecord($record);
    } catch (Exception $exception) {
        $this->log($exception->getMessage());
    }
}

$this->log(
    sprintf('Import complete. Imported: %d. Skipped: %d', $import->getImported(), $import->getSkipped())
);
