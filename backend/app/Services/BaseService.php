<?php

namespace App\Services;

use App\Interfaces\RepositoryInterface;

abstract class BaseService
{
    public function __construct(protected RepositoryInterface $repository)
    {
    }

    public function all(): iterable
    {
        return $this->repository->all();
    }

    public function find(int|string $id): mixed
    {
        return $this->repository->find($id);
    }

    public function create(array $attributes): mixed
    {
        return $this->repository->create($attributes);
    }

    public function update(int|string $id, array $attributes): mixed
    {
        return $this->repository->update($id, $attributes);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }
}
