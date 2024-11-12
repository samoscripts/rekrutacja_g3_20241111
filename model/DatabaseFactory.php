<?php

namespace model;

use Exception;

class DatabaseFactory
{
    /**
     * @throws Exception
     */
    public static function create($type): DatabaseInterface
    {
        switch ($type) {
            case 'mysql':
                return new DatabaseMysql();
            case 'sqlite':
                return new DatabaseSqlite();
            default:
                throw new Exception('Unsupported database type');
        }
    }
}