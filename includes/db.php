<?php
require_once 'config.local.php';

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=3307",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    public function insert($table, $data) {
        // Handle JSON fields
        if (isset($data['amenities']) && is_array($data['amenities'])) {
            $data['amenities'] = json_encode($data['amenities']);
        }
        
        // Add updated_at timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, array_values($data));
        return $this->conn->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        try {
            // Add updated_at timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Build SET clause
            $fields = array_map(function($field) {
                return "{$field} = ?";
            }, array_keys($data));
            
            // Build WHERE clause
            $whereClause = $where;
            if (is_array($where)) {
                $whereClause = array_map(function($field) {
                    return "{$field} = ?";
                }, array_keys($where));
                $whereClause = implode(' AND ', $whereClause);
                $whereParams = array_values($where);
            }
            
            // Build and execute query
            $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$whereClause}";
            $params = array_values($data);
            
            if (!empty($whereParams)) {
                $params = array_merge($params, $whereParams);
            }
            
            return $this->query($sql, $params);
        } catch (Exception $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        foreach ($results as &$row) {
            if (isset($row['amenities'])) {
                $row['amenities'] = json_decode($row['amenities'], true);
            }
        }
        
        return $results;
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        if ($result && isset($result['amenities'])) {
            $result['amenities'] = json_decode($result['amenities'], true);
        }
        
        return $result;
    }
    
    // Alias of fetch for clearer naming
    public function fetchOne($sql, $params = []) {
        return $this->fetch($sql, $params);
    }
} 