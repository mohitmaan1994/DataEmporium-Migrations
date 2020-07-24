<?php


namespace App\libraries\starrez_irio_sync\models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\libraries\starrez_irio_sync\models\SyncFilterCriteria
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncFilterCriteria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncFilterCriteria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncFilterCriteria query()
 * @mixin \Eloquent
 */
class SyncFilterCriteria extends Model
{
    protected $table = 'filter_constraints';
    protected $primaryKey = 'constraint_id';
    public $timestamps = false;
  //  protected $connection = 'starrez-irio-sync';

    private $filterConstraints = array(
        "RoomLocationID" => [],
        "TermSessionID" => [],
        "EntryStatusEnum" => []
    );

    public function populateFromRepository()
    {
        $constraints = SyncFilterCriteria::all();
        foreach ($constraints as $constraint) {
            $this->filterConstraints[$constraint->starrez_field] = array_map("intval",
                explode(",", $constraint->constraint_values));
        }
    }

    public function getRoomLocationIds()
    {
        return $this->filterConstraints['RoomLocationID'];
    }

    public function getTermSessionIds()
    {
        return $this->filterConstraints['TermSessionID'];
    }

    public function getEntryStatusEnums()
    {
        return $this->filterConstraints['EntryStatusEnum'];
    }
}
