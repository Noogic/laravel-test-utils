<?php

namespace Noogic\TestUtils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseBuilder
{
    /**
     * @var Collection
     */
    protected $entities;
    protected $class;
    protected $data = [];

    protected $user = null;
    protected $belongsToUser = false;

    protected $related = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function create(array $data = [], int $quantity = 1)
    {
        $instance = new static($data);

        $instance->handleBelongsToUser();
        $instance->handlePlugins();
        $instance->handleRelated();

        $instance->entities = factory($instance->class(), $quantity)->create($instance->data);

        return $instance;
    }

    protected function handleBelongsToUser()
    {
        if ($this->belongsToUser !== true) {
            return;
        }

        $this->createIfMissing('user_id', UserBuilder::class);

        $this->user = config('test-utils.user')::find($this->data['user_id']);
    }

    protected function handlePlugins()
    {
        // TODO implement handlePlugins
    }

    protected function handleRelated()
    {
        foreach ($this->related as $key => $class) {
            $this->createIfMissing($key, $class);
        }
    }

    protected function createIfMissing($key, $class)
    {
        if ($id = array_get($this->data, $key)) {
            return;
        }

        $method = is_a(new $class, Model::class) ? 'createFromFactory' : 'createFromBuilder';

        $model = $this->{$method}($class);

        $this->data[$key] = $model->id;
    }

    protected function createFromFactory($class)
    {
        return factory($class)->create();
    }

    protected function createFromBuilder($builderClass)
    {
        return $builderClass::create()->get();
    }

    public static function fill($entity)
    {
        $instance = new static();

        $instance->entities = $entity;

        return $instance;
    }

    public function get()
    {
        return $this->entities->count() > 1 ? $this->entities : $this->entities->first();
    }

    protected static function setInstance(Model $entity)
    {
        $instance = new static();
        $instance->entities = $entity;

        return $instance;
    }

    protected function class()
    {
        if ($this->class) {
            return $this->class;
        }

        $namespace = config('test-utils.entities_namespace');
        $className = preg_replace(
            '/(Database\\\Builders\\\)(.*)(Builder)/',
            '$2',
            get_class($this)
        );

        return $namespace . $className;
    }
}