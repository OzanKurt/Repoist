<?php

namespace Kurt\Repoist\Repositories\Eloquent;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Kurt\Repoist\Exceptions\NoEntityDefined;
use Kurt\Repoist\Repositories\Contracts\RepositoryInterface;
use Kurt\Repoist\Repositories\Criteria\CriteriaInterface;
use Illuminate\Http\Request;

abstract class AbstractRepository implements RepositoryInterface, CriteriaInterface
{
    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;


    public function __construct(Request $request)
    {
        $this->entity = $this->resolveEntity();
        $this->request = $request; // reading request here allows many options.
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
        return $this->processPagination($this->entity, $perPage);
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
    	return $this->paginateIf($query>get(), $paginate);
    }

    private function paginateIf($records, $per_page)
    {

        if ($per_page>0) {
            // We can bypass and continue...
        } elseif ($this->request->input('limit') > 0){
            // $per_page=null, but we could try find "limit" form request
            $per_page = $this->request->input('limit');
        } else {
            return $records;
        }

        $page   = $this->request->input('page') > 0 ? $this->request->input('page') : 1;
        $offset = ($page * $per_page) - $per_page;

        return new LengthAwarePaginator(
            array_slice($records->toArray(), $offset, $per_page, true),
            $records->count(),
            $per_page,
            $page,
            ['path' => $this->request->url(), 'query' => $this->request->query()]
        );

    }
}
