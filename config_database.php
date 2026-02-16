<?php
// Configuration de la base de données pour Sultana Boutique
// Fichier de connexion à la base de données MySQL

class Database {
    private $host = "localhost";
    private $db_name = "sultana_boutique";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    
    public $conn;
    
    // Connexion à la base de données
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    // Exécuter une requête SQL
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $exception) {
            echo "Erreur de requête: " . $exception->getMessage();
            return false;
        }
    }
    
    // Insérer des données
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $values = array_values($data);
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        return $this->executeQuery($sql, $values);
    }
    
    // Sélectionner des données
    public function select($table, $conditions = "", $params = []) {
        $sql = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE $conditions";
        }
        return $this->executeQuery($sql, $params);
    }
    
    // Mettre à jour des données
    public function update($table, $data, $conditions, $params = []) {
        $set_parts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $set_parts[] = "$column = ?";
            $values[] = $value;
        }
        
        $set_clause = implode(", ", $set_parts);
        $sql = "UPDATE $table SET $set_clause WHERE $conditions";
        
        return $this->executeQuery($sql, array_merge($values, $params));
    }
    
    // Supprimer des données
    public function delete($table, $conditions, $params = []) {
        $sql = "DELETE FROM $table WHERE $conditions";
        return $this->executeQuery($sql, $params);
    }
    
    // Obtenir le dernier ID inséré
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    // Commencer une transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    // Valider une transaction
    public function commit() {
        return $this->conn->commit();
    }
    
    // Annuler une transaction
    public function rollback() {
        return $this->conn->rollback();
    }
}

// Exemple d'utilisation
/*
$database = new Database();
$db = $database->getConnection();

// Insérer un utilisateur
$user_data = [
    'nom' => 'Test User',
    'email' => 'test@example.com',
    'mot_de_passe' => 'password123',
    'role' => 'client'
];
$database->insert('utilisateurs', $user_data);

// Sélectionner tous les produits
$products = $database->select('produits');
while ($row = $products->fetch()) {
    echo $row['nom'] . " - " . $row['prix'] . "€\n";
}

// Mettre à jour le stock d'un produit
$database->update('produits', 
    ['stock' => 50], 
    'id = ?', 
    [1]
);
*/
?>
