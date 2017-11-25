<?php

namespace Kurt\Repoist\Repositories\Eloquent;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Kurt\Repoist\Exceptions\NoEntityDefined;
use Kurt\Repoist\Repositories\Contracts\RepositoryInterface;
use Kurt\Repoist\Repositories\Criteria\CriteriaInterface;

abstract class AbstractRepository implements RepositoryInterface, CriteriaInterface
{
    /**
     * @var mixed
     */
    protected $entity;

    public function __construct()
    {
        $this->entity = $this->resolveEntity();
    }

    /**
     * @return mixed
     */
    public function all($paginate = null)
    {
        return $this->processPagination($this->entity, $paginate);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $model = $this->entity->find($id);

        if (!$model) {
            throw (new ModelNotFoundException)->setModel(
                get_class($this->entity->getModel()),
                $id
            );
        }

        return $model;
    }

    /**
     * @param $column
     * @param $value
     * @return mixed
     */
    public function findWhere($column, $value, $paginate = null)
    {
    	$query = $this->entity->where($column, $value);

        return $this->processPagination($query, $paginate);
    }

    /**
     * @param $column
     * @param $value
     * @return mixed
     */
    public function findWhereFirst($column, $value)
    {
        $model = $this->entity->where($column, $value)->first();

        if (!$model) {
            throw (new ModelNotFoundException)->setModel(
                get_class($this->entity->getModel())
            );
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereLike($columns, $value, $paginate = null)
    {
    	$query = $this->entity;

        if (is_string($columns)) {
        	$columns = [$columns];
        }

		foreach ($columns as $column) {
	        $query->orWhere($column, 'like', $value);
		}

		return $this->processPagination($query, $paginate);
    }

    /**
     * @param $perPage
     * @return mixed
     */
    public function paginate($perPage = 10)
    {
        return $this->entity->paginate($perPage);
    }

    /**
     * @param array $properties
     * @return mixed
     */
    public function create(array $properties)
    {
        return $this->entity->create($properties);
    }

    /**
     * @param $id
     * @param array $properties
     * @return mixed
     */
    public function update($id, array $properties)
    {
        return $this->find($id)->update($properties);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->find($id)->delete();
    }

    /**
     * @param $criteria
     * @return mixed
     */
    public function withCriteria(...$criteria)
    {
        $criteria = array_flatten($criteria);

        foreach ($criteria as $criterion) {
            $this->entity = $criterion->apply($this->entity);
        }

        return $this;
    }

    protected function resolveEntity()
    {
        if (!method_exists($this, 'entity')) {
            throw new NoEntityDefined();
        }

        return app($this->entity());
    }

    private function processPagination($query, $paginate)
    {
    	return $paginate ? $query->paginate($paginate) : $query->get();
    }
}
