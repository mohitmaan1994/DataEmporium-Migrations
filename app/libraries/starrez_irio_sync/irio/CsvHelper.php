<?php


namespace App\libraries\starrez_irio_sync\irio;


class CsvHelper
{
    const CSV_FILE_PATH = __DIR__.'/../../../../storage/irio-post.csv';

    public function createCsvFileFromAccumulatedData(array $accumulatedData)
    {
        $csvFp = fopen(self::CSV_FILE_PATH, 'w');

        fputcsv($csvFp, array('uin', 'name', 'sms', 'email', 'roomNumber', 'RoomLocation', 'tags'));

        foreach ($accumulatedData as $syncEntry) {
            $lineToWrite = implode(',', array(
                    $syncEntry->getUin(),
                    $syncEntry->getName(),
                    $syncEntry->getPhoneNumber(),
                    $syncEntry->getEmail(),
                    $syncEntry->getRoomNumber(),
                    $syncEntry->getRoomLocation(),
                    $syncEntry->getTags()
                ))."\n";
            fputs($csvFp, $lineToWrite);
        }

        fclose($csvFp);
    }

    public function getFilePointerToCsv()
    {
        return fopen(self::CSV_FILE_PATH, 'r');
    }
}
