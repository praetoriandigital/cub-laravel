<?php namespace Cub\CubLaravel\Transformers;

use Carbon\Carbon;
use Config;
use Cub;
use Cub\CubLaravel\Contracts\CubTransformer;
use Cub\CubLaravel\Exceptions\ObjectNotFoundByCubIdException;
use Cub_Object;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

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
     * @var array
     */
    protected $relations;

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
        $this->setObjectType($this->cubObject);
        $this->model = app()->make(Config::get('cub::config.maps.'.$this->objectType.'.model'));
        $this->fields = $this->getFields($this->objectType);
        $this->relations = $this->getRelations($this->objectType);
    }

    /**
     * @param Cub_Object $cubObject
     */
    protected function setObjectType(Cub_Object $cubObject)
    {
        $this->objectType = Cub::objectType($cubObject);
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
     * @param $objectType
     *
     * @return array
     */
    protected function getRelations($objectType)
    {
        return Config::get('cub::config.maps.'.$objectType.'.relations') ? : [];
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
    public function process()
    {
        if (!$this->appObject) {
            try {
                $this->setAppObject(Cub::getObjectById($this->objectType, $this->cubObject->id));
            } catch (ObjectNotFoundByCubIdException $e) {
                $this->setAppObject(Cub::getNewObject($this->objectType));
            }
        }
        if ($this->needsProcessing()) {

            $data = [];
            $this->prepareData($data);

            if (count($data)) {
                try {
                    if (!$this->appObject->fill($data)->save()) {
                        return false;
                    }
                } catch (QueryException $e) {
                    try {
                        $this->setAppObject(Cub::getObjectById($this->objectType, $this->cubObject->id));
                    } catch (ObjectNotFoundByCubIdException $e) {
                        return false;
                    }
                }
            }

            if ($this->needsToRestore() && !$this->appObject->restore()) {
                return false;
            } else if ($this->needsToDelete() && !$this->appObject->delete()) {
                return false;
            }

            return $this->appObject;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function needsProcessing()
    {
        if (isset($this->fields) && isset($this->appObject) && isset($this->cubObject)) {
            return !empty($this->fields) || ($this->appObject->deleted_at && !$this->cubObject->deleted);
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function needsToRestore()
    {
        if (isset($this->appObject) && isset($this->cubObject)) {
            return $this->appObject->deleted_at && !$this->cubObject->deleted;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function needsToDelete()
    {
        if (isset($this->appObject) && isset($this->cubObject)) {
            return !$this->appObject->deleted_at && $this->cubObject->deleted;
        }
        return false;
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
            $relations = $this->getRelations($this->getObjectType($cubObject));
            $fillable = $model['fillable'];
            $dates = $model->getDates();
        } else {
            $fields = $this->fields;
            $relations = $this->relations;
            $fillable = $this->model['fillable'];
            $dates = $this->model->getDates();
        }

        $this->processRelationsData($data);

        foreach ($fields as $cubField => $appField) {
            if (in_array($appField, $fillable)) {
                $value = $cubObject->{$cubField};
                if (in_array($appField, $dates)) {
                    if ($value instanceof \DateTime) {
                        $value = Carbon::instance($value);
                    } else {
                        $value = Carbon::parse($value)->setTimezone('UTC');
                    }
                }
                if (!is_array($value) && !$value instanceof Cub_Object) {
                    $data[$appField] = is_bool($value) ? (int) $value : $value;
                }
            }
        }
    }

    /**
     * @param array $data
     * @param Cub_Object $cubObject
     * @param Model $model
     */
    protected function processRelationsData(array &$data, Cub_Object $cubObject = null, Model $model = null)
    {
        $cubObject = $cubObject ? : $this->cubObject;
        if ($model) {
            $relations = $this->getRelations($this->getObjectType($cubObject));
            $fillable = $model['fillable'];
        } else {
            $relations = $this->relations;
            $fillable = $this->model['fillable'];
        }

        foreach ($relations as $cubField => $appField) {
            $value = $cubObject->{$cubField};
            if (in_array($appField, $fillable)) {
                if ($value instanceof Cub_Object && Cub::objectIsTracked($value)) {
                    if ($appObject = Cub::processObject($value, false)) {
                        $data[$appField] = $appObject->id;
                    }
                }
            } else if (Cub::objectNameIsTracked($appField)) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if ($v instanceof Cub_Object && Cub::objectIsTracked($v)) {
                            Cub::processObject($v, false);
                        }
                    }
                } else {
                    if ($value instanceof Cub_Object && Cub::objectIsTracked($value)) {
                        Cub::processObject($value, false);
                    }
                }
            }
        }
    }
}
