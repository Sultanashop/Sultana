<?php
// Exemples d'API REST pour la boutique Sultana
// Endpoints pour gérer les produits, utilisateurs et commandes

require_once 'config_database.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

// Router simple basé sur la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = $request[0] ?? '';

switch ($resource) {
    case 'produits':
        handleProducts($method, $db);
        break;
    case 'utilisateurs':
        handleUsers($method, $db);
        break;
    case 'commandes':
        handleOrders($method, $db);
        break;
    case 'panier':
        handleCart($method, $db);
        break;
    case 'connexion':
        handleLogin($method, $db);
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Ressource non trouvée"]);
        break;
}

// Gestion des produits
function handleProducts($method, $db) {
    switch ($method) {
        case 'GET':
            // Lister tous les produits ou un produit spécifique
            $id = $_GET['id'] ?? null;
            $category = $_GET['category'] ?? null;
            
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM vue_produits_complets WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
                echo json_encode($product ?: ["message" => "Produit non trouvé"]);
            } elseif ($category) {
                $stmt = $db->prepare("SELECT * FROM vue_produits_complets WHERE categorie_nom = ?");
                $stmt->execute([$category]);
                $products = $stmt->fetchAll();
                echo json_encode($products);
            } else {
                $stmt = $db->query("SELECT * FROM vue_produits_complets");
                $products = $stmt->fetchAll();
                echo json_encode($products);
            }
            break;
            
        case 'POST':
            // Ajouter un nouveau produit (admin seulement)
            $data = json_decode(file_get_contents("php://input"), true);
            
            $stmt = $db->prepare("INSERT INTO produits (nom, description, prix, image_url, stock, categorie_id) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['nom'],
                $data['description'],
                $data['prix'],
                $data['image_url'] ?? null,
                $data['stock'] ?? 0,
                $data['categorie_id']
            ]);
            
            if ($result) {
                http_response_code(201);
                echo json_encode(["message" => "Produit créé avec succès", "id" => $db->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erreur lors de la création du produit"]);
            }
            break;
    }
}

// Gestion des utilisateurs
function handleUsers($method, $db) {
    switch ($method) {
        case 'POST':
            // Inscription d'un nouvel utilisateur
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Vérifier si l'email existe déjà
            $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$data['email']]);
            
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(["message" => "Cet email est déjà utilisé"]);
                return;
            }
            
            $stmt = $db->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, telephone, adresse, ville, pays) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['nom'],
                $data['email'],
                $data['mot_de_passe'], // En production, utiliser password_hash()
                $data['telephone'] ?? null,
                $data['adresse'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? null
            ]);
            
            if ($result) {
                http_response_code(201);
                echo json_encode(["message" => "Utilisateur créé avec succès", "id" => $db->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erreur lors de la création de l'utilisateur"]);
            }
            break;
            
        case 'GET':
            // Lister les utilisateurs (admin seulement)
            $stmt = $db->query("SELECT id, nom, email, role, created_at FROM utilisateurs");
            $users = $stmt->fetchAll();
            echo json_encode($users);
            break;
    }
}

// Gestion des commandes
function handleOrders($method, $db) {
    switch ($method) {
        case 'GET':
            // Lister les commandes d'un utilisateur
            $user_id = $_GET['user_id'] ?? null;
            
            if ($user_id) {
                $stmt = $db->prepare("SELECT * FROM vue_commandes_completes WHERE utilisateur_id = ?");
                $stmt->execute([$user_id]);
                $orders = $stmt->fetchAll();
                echo json_encode($orders);
            } else {
                $stmt = $db->query("SELECT * FROM vue_commandes_completes");
                $orders = $stmt->fetchAll();
                echo json_encode($orders);
            }
            break;
            
        case 'POST':
            // Créer une nouvelle commande
            $data = json_decode(file_get_contents("php://input"), true);
            
            $db->beginTransaction();
            
            try {
                // Créer la commande
                $stmt = $db->prepare("INSERT INTO commandes (utilisateur_id, total, adresse_livraison, mode_paiement) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $data['utilisateur_id'],
                    $data['total'],
                    $data['adresse_livraison'],
                    $data['mode_paiement']
                ]);
                
                $commande_id = $db->lastInsertId();
                
                // Ajouter les détails de commande
                foreach ($data['items'] as $item) {
                    $stmt = $db->prepare("INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, total_ligne) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $commande_id,
                        $item['produit_id'],
                        $item['quantite'],
                        $item['prix_unitaire'],
                        $item['total_ligne']
                    ]);
                }
                
                $db->commit();
                http_response_code(201);
                echo json_encode(["message" => "Commande créée avec succès", "commande_id" => $commande_id]);
                
            } catch (Exception $e) {
                $db->rollback();
                http_response_code(500);
                echo json_encode(["message" => "Erreur lors de la création de la commande: " . $e->getMessage()]);
            }
            break;
    }
}

// Gestion du panier
function handleCart($method, $db) {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(["message" => "ID utilisateur requis"]);
        return;
    }
    
    switch ($method) {
        case 'GET':
            // Afficher le panier d'un utilisateur
            $stmt = $db->prepare("
                SELECT p.id, p.nom, p.prix, pa.quantite, (p.prix * pa.quantite) as total
                FROM panier pa
                JOIN produits p ON pa.produit_id = p.id
                WHERE pa.utilisateur_id = ?
            ");
            $stmt->execute([$user_id]);
            $cart = $stmt->fetchAll();
            echo json_encode($cart);
            break;
            
        case 'POST':
            // Ajouter un produit au panier
            $data = json_decode(file_get_contents("php://input"), true);
            
            $stmt = $db->prepare("
                INSERT INTO panier (utilisateur_id, produit_id, quantite)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantite = quantite + ?
            ");
            $result = $stmt->execute([$user_id, $data['produit_id'], $data['quantite'], $data['quantite']]);
            
            if ($result) {
                echo json_encode(["message" => "Produit ajouté au panier"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erreur lors de l'ajout au panier"]);
            }
            break;
            
        case 'DELETE':
            // Vider le panier
            $stmt = $db->prepare("DELETE FROM panier WHERE utilisateur_id = ?");
            $result = $stmt->execute([$user_id]);
            
            if ($result) {
                echo json_encode(["message" => "Panier vidé"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erreur lors du vidage du panier"]);
            }
            break;
    }
}

// Gestion de la connexion
function handleLogin($method, $db) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(["message" => "Méthode non autorisée"]);
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$data['email'], $data['mot_de_passe']]);
    $user = $stmt->fetch();
    
    if ($user) {
        // En production, générer un JWT token
        echo json_encode([
            "message" => "Connexion réussie",
            "user" => [
                "id" => $user['id'],
                "nom" => $user['nom'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Email ou mot de passe incorrect"]);
    }
}

// Exemples d'utilisation des API:
/*
GET /api_exemples.php/produits                    - Lister tous les produits
GET /api_exemples.php/produits?id=1               - Obtenir un produit spécifique
GET /api_exemples.php/produits?category=Caftan    - Filtrer par catégorie

POST /api_exemples.php/utilisateurs               - Inscription
{
    "nom": "Nouveau Client",
    "email": "client@example.com",
    "mot_de_passe": "password123",
    "ville": "Casablanca"
}

POST /api_exemples.php/connexion                 - Connexion
{
    "email": "ahmed.benali@email.com",
    "mot_de_passe": "password123"
}

GET /api_exemples.php/panier?user_id=1            - Voir le panier
POST /api_exemples.php/panier                     - Ajouter au panier
{
    "user_id": 1,
    "produit_id": 1,
    "quantite": 2
}

GET /api_exemples.php/commandes?user_id=1         - Voir les commandes
POST /api_exemples.php/commandes                  - Créer une commande
{
    "utilisateur_id": 1,
    "total": 2500.00,
    "adresse_livraison": "123 Rue Mohammed V",
    "mode_paiement": "Carte bancaire",
    "items": [
        {
            "produit_id": 1,
            "quantite": 1,
            "prix_unitaire": 2500.00,
            "total_ligne": 2500.00
        }
    ]
}
*/
?>
