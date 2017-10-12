<?php namespace Cub\CubLaravel\Transformers;

use Carbon\Carbon;
use Config;
use Cub;
use Cub_Object;
use Cub\CubLaravel\Contracts\CubTransformer;

class CubObjectTransformer implements CubTransformer
{
    /**
     * @var Cub_Object
     */
    protected $cubObject;

    /**
     * @param Cub_Object $cubObject
     */
    public function __construct(Cub_Object $cubObject)
    {
        $this->cubObject = $cubObject;
    }

    /**
     * @return bool
     */
    public function create()
    {
        $objectType = strtolower(get_class($this->cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        if (is_array($fields)) {
            $attributes = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $this->cubObject->{$cubField};
                    if (in_array($appField, $model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $attributes[$appField] = $value;
                }
            }
            if (count($attributes)) {
                return (bool) $model->create($attributes);
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update()
    {
        $objectType = strtolower(get_class($this->cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        $appObject = Cub::getObjectById($objectType, $this->cubObject->id);
        if (is_array($fields)) {
            $updates = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $this->cubObject->{$cubField};
                    if (in_array($appField, $model->getDates())) {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                    $updates[$appField] = $value;
                }
            }
            if (count($updates)) {
                return $appObject->update($updates);
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        $objectType = strtolower(get_class($this->cubObject));
        $appObject = Cub::getObjectById($objectType, $this->cubObject->id);
        return $appObject->delete();
    }
}
