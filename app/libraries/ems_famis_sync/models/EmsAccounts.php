<?php


namespace App\libraries\ems_famis_sync\models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\libraries\ems_famis_sync\models\EmsAccounts
 *
 * @property int $id
 * @property int $account_number
 * @property string $account_short_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts whereAccountShortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\ems_famis_sync\models\EmsAccounts whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmsAccounts extends Model
{

    protected $fillable = ['account_number','account_short_code'];

}
