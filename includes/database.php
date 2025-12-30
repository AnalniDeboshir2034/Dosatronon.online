<?php
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Ошибка подключения: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Ошибка базы данных: " . $e->getMessage());
        }
    }
    
  
    public function getAllProducts() {
        $query = "SELECT * FROM medicator ORDER BY id";
        $result = $this->connection->query($query);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
 
    public function getProductById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM medicator WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    

    public function searchProducts($searchTerm) {
        $stmt = $this->connection->prepare("
            SELECT * FROM medicator 
            WHERE name LIKE ? 
            OR d_dosing LIKE ? 
            OR performance LIKE ?
            ORDER BY id
        ");
        
        $searchPattern = "%{$searchTerm}%";
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}


$db = new Database();
?>