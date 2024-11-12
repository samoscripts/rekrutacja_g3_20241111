<?php

namespace model;

use Form;
use PDO;

class DatabaseMysql implements DatabaseInterface
{
    private PDO $db;

    const string DB_HOST = 'localhost';
    const string DB_NAME = 'zadanie';
    const string DB_USERNAME = 'zadanie_user';
    const string DB_PASSWORD = '123haslo456';
    const string DB_PORT = '3306';

    public function connect()
    {
        $this->db = new PDO(
            "mysql:host=mysqldb_samo;dbname=" . self::DB_NAME . ";port=" . self::DB_PORT,
            self::DB_USERNAME,
            self::DB_PASSWORD
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function tableExists($tableName): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :dbName AND TABLE_NAME = :tableName");
        $dbName = self::DB_NAME;
        $stmt->bindParam(':dbName', $dbName);
        $stmt->bindParam(':tableName', $tableName);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
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
        $sql = file_get_contents('zadanie1mysql.sql');
        $this->db->exec($sql);
    }
}