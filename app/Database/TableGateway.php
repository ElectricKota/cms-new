<?php

declare(strict_types=1);

namespace App\Database;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final readonly class TableGateway
{
    public function __construct(private Explorer $database)
    {
    }

    /** @return Selection<ActiveRow> */
    public function table(string $name): Selection
    {
        return $this->database->table($name);
    }

    public function find(string $table, int|string $id): ?ActiveRow
    {
        return $this->table($table)->get($id);
    }

    /** @param array<string, mixed> $values */
    public function insert(string $table, array $values): ActiveRow
    {
        return $this->table($table)->insert($values);
    }

    /** @param array<string, mixed> $values */
    public function update(string $table, int|string $id, array $values): void
    {
        $this->table($table)->wherePrimary($id)->update($values);
    }

    public function delete(string $table, int|string $id): void
    {
        $this->table($table)->wherePrimary($id)->delete();
    }
}
