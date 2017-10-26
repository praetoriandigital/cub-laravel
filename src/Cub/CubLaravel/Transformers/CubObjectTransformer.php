<?php namespace Cub\CubLaravel\Transformers;

use Carbon\Carbon;
use Config;
use Cub;
use Cub_Object;
use Cub\CubLaravel\Contracts\CubTransformer;
use Illuminate\Database\Eloquent\Model;

class CubObjectTransformer implements CubTransformer
{
    /**
     * @var Cub_Object
     */
    protected $cubObject;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $appObject;

    /**
     * @param Cub_Object $cubObject
     */
    public function __construct(Cub_Object $cubObject)
    {
        $this->cubObject = $cubObject;
        $this->objectType = $this->getObjectType($this->cubObject);
        $this->model = app()->make(Config::get('cub::config.maps.'.$this->objectType.'.model'));
        $this->fields = $this->getFields($this->objectType);
    }

    /**
     * @param Cub_Object $cubObject
     *
     * @return string
     */
    protected function getObjectType(Cub_Object $cubObject)
    {
        return strtolower(get_class($cubObject));
    }

    /**
     * @param $objectType
     *
     * @return array
     */
    protected function getFields($objectType)
    {
        return Config::get('cub::config.maps.'.$objectType.'.fields') ? : [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $appObject
     */
    protected function setAppObject(Model $appObject)
    {
        $this->appObject = $appObject;
    }

    /**
     * @return bool
     */
    public function create()
    {
        if (!empty($this->fields)) {
            $attributes = [];
            $this->prepareData($attributes);
            if (count($attributes)) {
                $appObject = $this->model->create($attributes);
                if ($appObject && $this->cubObject->deleted) {
                    return $appObject->delete();
                }
                return (bool) $appObject;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update()
    {
        if (!$this->appObject) {
            $this->setAppObject(Cub::getObjectById($this->objectType, $this->cubObject->id));
        }
        if (!empty($this->fields) || ($this->appObject->deleted_at && !$this->cubObject->deleted)) {
            $updates = [];
            $this->prepareData($updates);
            if ($this->appObject->deleted_at && !$this->cubObject->deleted) {
                if (!$this->appObject->restore()) {
                    return false;
                }
            }
            if (count($updates)) {
                return $this->appObject->fill($updates)->save();
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if (!$this->appObject) {
            $this->setAppObject(Cub::getObjectById($this->objectType, $this->cubObject->id));
        }
        $updates = [];
        $this->prepareData($updates);
        if (count($updates)) {
            $this->appObject->fill($updates)->save();
        }
        return $this->appObject->delete();
    }

    /**
     * @param array $data
     * @param Cub_Object $cubObject
     * @param Model $model
     */
    protected function prepareData(array &$data, Cub_Object $cubObject = null, Model $model = null)
    {
        $cubObject = $cubObject ? : $this->cubObject;
        if ($model) {
            $fields = $this->getFields($this->getObjectType($cubObject));
            $fillable = $model['fillable'];
            $dates = $model->getDates();
        } else {
            $fields = $this->fields;
            $fillable = $this->model['fillable'];
            $dates = $this->model->getDates();
        }

        foreach ($fields as $cubField => $appField) {
            if (in_array($appField, $fillable)) {
                $value = $cubObject->{$cubField};
                if (in_array($appField, $dates)) {
                    $value = Carbon::parse($value)->setTimezone('UTC');
                }
                $data[$appField] = $value;
            }
        }
    }
}
