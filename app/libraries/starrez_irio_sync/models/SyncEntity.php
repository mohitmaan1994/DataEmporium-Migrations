<?php


namespace App\libraries\starrez_irio_sync\models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\libraries\starrez_irio_sync\models\SyncEntity
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntity query()
 * @mixin \Eloquent
 */
class SyncEntity extends Model
{
    protected $table = 'sync_records';
    protected $primaryKey = 'record_id';
    public $timestamps = false;
   // protected $connection = 'starrez-irio-sync';

    private $uin;
    private $name;
    private $phoneNumber;
    private $email;
    private $roomNumber;
    private $roomLocation;
    private $tags;

    /**
     * @return string
     */
    public function getUin()
    {
        return $this->uin;
    }

    /**
     * @param string $uin
     */
    public function setUin($uin): void
    {
        $this->uin = $uin;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getRoomNumber()
    {
        return $this->roomNumber;
    }

    /**
     * @param string $roomNumber
     */
    public function setRoomNumber($roomNumber): void
    {
        $this->roomNumber = $roomNumber;
    }

    /**
     * @return string
     */
    public function getRoomLocation()
    {
        return $this->roomLocation;
    }

    /**
     * @param string $roomLocation
     */
    public function setRoomLocation($roomLocation): void
    {
        $this->roomLocation = $roomLocation;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @param SyncEntityExtended $syncEntityExtended
     * @return SyncEntity
     */
    public static function getSyncEntity($syncEntityExtended)
    {
        $class_vars = get_class_vars(SyncEntity::class);
        $syncEntity = new SyncEntity();
        foreach ($class_vars as $key => $value) {
            $syncEntity->{$key} = $syncEntityExtended->{$key};
        }
        return $syncEntity;
    }

    /**
     * @param $uin
     * @param $name
     * @param $phoneNumber
     * @param $email
     * @param $roomNumber
     * @param $roomLocation
     * @param $tags
     * @return SyncEntity
     */
    public static function getSyncEntityWithParams($uin, $name, $phoneNumber, $email, $roomNumber, $roomLocation, $tags)
    {
        $instance = new self();
        $instance->setUin($uin);
        $instance->setName($name);
        $instance->setPhoneNumber($phoneNumber);
        $instance->setEmail($email);
        $instance->setRoomNumber($roomNumber);
        $instance->setRoomLocation($roomLocation);
        $instance->setTags($tags);
        return $instance;
    }
}
