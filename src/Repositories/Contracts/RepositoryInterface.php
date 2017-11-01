<?php

namespace Kurt\Repoist\Repositories\Contracts;

interface RepositoryInterface
{
    public function all();
    public function find($id);
    public function findWhere($column, $value);
    public function findWhereFirst($column, $value);
    public function paginate($perPage = 10);
    public function create(array $properties);
    public function update($id, array $properties);
    public function delete($id);
}
