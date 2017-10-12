<?php namespace Cub\CubLaravel;

use Carbon\Carbon;
use Config;
use Cub_Object;
use Cub\CubLaravel\Contracts\CubTransformer;

class CubObjectTransformer implements CubTransformer
{
    /**
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function create(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        if (is_array($fields)) {
            $attributes = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $cubObject->{$cubField};
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
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function update(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $model = app()->make(Config::get('cub::config.maps.'.$objectType.'.model'));
        $fields = Config::get('cub::config.maps.'.$objectType.'.fields');
        $appObject = Cub::getObjectById($objectType, $cubObject->id);
        if (is_array($fields)) {
            $updates = [];
            foreach ($fields as $cubField => $appField) {
                if (in_array($appField, $model['fillable'])) {
                    $value = $cubObject->{$cubField};
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
     * @param Cub_Object $cubObject
     *
     * @return bool
     */
    public function delete(Cub_Object $cubObject)
    {
        $objectType = strtolower(get_class($cubObject));
        $appObject = Cub::getObjectById($objectType, $cubObject->id);
        return $appObject->delete();
    }
}
