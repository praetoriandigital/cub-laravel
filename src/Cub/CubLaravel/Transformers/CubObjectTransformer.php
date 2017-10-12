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
        $this->objectType = strtolower(get_class($this->cubObject));
        $this->model = app()->make(Config::get('cub::config.maps.'.$this->objectType.'.model'));
        $this->fields = Config::get('cub::config.maps.'.$this->objectType.'.fields') ? : [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model
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
            foreach ($this->fields as $cubField => $appField) {
                if (in_array($appField, $this->model['fillable'])) {
                    $value = $this->cubObject->{$cubField};
                    if (in_array($appField, $this->model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $attributes[$appField] = $value;
                }
            }
            if (count($attributes)) {
                return (bool) $this->model->create($attributes);
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
        if (!empty($this->fields)) {
            $updates = [];
            foreach ($this->fields as $cubField => $appField) {
                if (in_array($appField, $this->model['fillable'])) {
                    $value = $this->cubObject->{$cubField};
                    if (in_array($appField, $this->model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $updates[$appField] = $value;
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
        return $this->appObject->delete();
    }
}
