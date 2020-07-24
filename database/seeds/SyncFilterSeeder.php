<?php

use App\libraries\starrez_irio_sync\models\SyncFilterCriteria;
use Illuminate\Database\Seeder;

class SyncFilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SyncFilterCriteria::query()->truncate();
        SyncFilterCriteria::query()->insert([
            [
                'starrez_field' => 'RoomLocationID',
                'constraint_values' => '9,20,38,14,30,22,10,39,24,40,45,46,47,48,49,50,51,52,53,54,15,35,43,31,16,25,8,26,37,23,33,17,28,34,18,29,21,19,11,4,27,36,12,44,32,13,5,6,7,42,41'
            ],
            [
                'starrez_field' => 'TermSessionID',
                'constraint_values' => '47,64,50,49,76,77,78,60,61'
            ],
            [
                'starrez_field' => 'EntryStatusEnum',
                'constraint_values' => '1,2,5'
            ]
        ]);
    }
}
