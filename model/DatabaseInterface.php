<?php

namespace model;
interface DatabaseInterface
{
    public function connect();

    public function tableExists($tableName): bool;

    public function executeQuery($query, $params = []): void;

    public function fetchAll($query): array;

    public function fetchColumn($query, $params = []): string;

    public function restoreDb(): void;
}