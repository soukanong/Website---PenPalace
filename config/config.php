<?php
/**
 * Configuration and database interaction handler
 * Manages data operations between auth.php, functions.php, admin-functions.php and database
 */

require_once __DIR__ . "/database.php";

class Config
{
    private $db;
    private static $instance = null;

    private function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generic database query executor with prepared statements
     */
    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($params));
            throw new Exception("Database operation failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch multiple rows
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert record and return last insert ID
     */
    public function insert($sql, $params = [])
    {
        $this->executeQuery($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Update record and return affected rows
     */
    public function update($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete record and return affected rows
     */
    public function delete($sql, $params = [])
    {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->db->rollBack();
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserialize of the instance
    public function __wakeup()
    {
    }
}
