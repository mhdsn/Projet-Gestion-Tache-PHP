<?php
session_start(); // Une seule fois !
require 'db.php';

// --- SÉCURITÉ : VIGILE À L'ENTRÉE ---
// Si l'utilisateur n'est pas connecté, on le vire.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- PARTIE 1 : GESTION DES ACTIONS (Quand on soumet un formulaire) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION : AJOUTER
    if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {
        // CORRECTION : On ajoute user_id dans la requête
        $stmt = $pdo->prepare("INSERT INTO taches (titre, description, statut, user_id) VALUES (?, ?, ?, ?)");
        // On récupère l'ID depuis la session
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['statut'], $_SESSION['user_id']]);
    }

    // ACTION : MODIFIER
    if (isset($_POST['action']) && $_POST['action'] == 'modifier') {
        // SÉCURITÉ : On devrait vérifier si la tâche appartient à l'user, mais faisons simple pour l'instant
        $stmt = $pdo->prepare("UPDATE taches SET titre = ?, description = ?, statut = ? WHERE id = ?");
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['statut'], $_POST['id']]);
    }

    // ACTION : SUPPRIMER
    if (isset($_POST['action']) && $_POST['action'] == 'supprimer') {
        // SÉCURITÉ : Idem, l'admin peut tout supprimer, l'user seulement les siennes
        // Pour l'instant, on laisse l'action basique pour que ça marche
        $stmt = $pdo->prepare("DELETE FROM taches WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }

    // Redirection
    header("Location: index.php");
    exit();
}

// --- PARTIE 2 : PRÉPARATION DE L'AFFICHAGE (Pour index.php) ---

// A. Variables par défaut
$formAction = 'ajouter';
$formId = '';
$formTitre = '';
$formDesc = '';
$formStatut = 'En cours';
$btnClass = 'btn-success';
$btnText = 'Ajouter';
$cardTitle = 'Nouvelle tâche';

// B. Mode Modification
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM taches WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $tacheEdit = $stmt->fetch();

    if ($tacheEdit) {
        $formAction = 'modifier';
        $formId = $tacheEdit['id'];
        $formTitre = $tacheEdit['titre'];
        $formDesc = $tacheEdit['description'];
        $formStatut = $tacheEdit['statut'];
        $btnClass = 'btn-warning';
        $btnText = 'Modifier';
        $cardTitle = 'Modifier la tâche';
    }
}

// C. RÉCUPÉRATION DES TÂCHES (FILTRE ADMIN vs USER)
if ($_SESSION['role'] === 'admin') {
    // L'ADMIN voit TOUT
    $req = $pdo->query("SELECT * FROM taches ORDER BY id DESC");
    $taches = $req->fetchAll();
} else {
    // Le USER ne voit que SES tâches
    $stmt = $pdo->prepare("SELECT * FROM taches WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $taches = $stmt->fetchAll();
}
?>