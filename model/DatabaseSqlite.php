<?php

namespace model;

use PDO;

class DatabaseSqlite implements DatabaseInterface
{
    private PDO $db;

    public function connect()
    {
        $this->db = new PDO('sqlite:my_database.sqlite3');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function tableExists($tableName): bool
    {
        $stmt = $this->db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:tableName");
        $stmt->bindParam(':tableName', $tableName);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    public function executeQuery($query, $params = []): void
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }

    public function fetchAll($query): array
    {
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    public function fetchColumn($query, $params = []): string
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function restoreDb(): void
    {
        $sql = file_get_contents('zadanie1sqlite.sql');
        $this->db->exec($sql);
    }
}