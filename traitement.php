<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {
        $stmt = $pdo->prepare("INSERT INTO taches (titre, description, statut, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['statut'], $_SESSION['user_id']]);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'modifier') {
        $stmt = $pdo->prepare("UPDATE taches SET titre = ?, description = ?, statut = ? WHERE id = ?");
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['statut'], $_POST['id']]);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM taches WHERE id = ?");
        $stmt->execute([$_POST['id']]);
    }
    header("Location: index.php");
    exit();
}

$formAction = 'ajouter';
$formId = '';
$formTitre = '';
$formDesc = '';
$formStatut = 'En cours';
$btnClass = 'btn-success';
$btnText = 'Ajouter';
$cardTitle = 'Nouvelle tâche';

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

if ($_SESSION['role'] === 'admin') {
    $req = $pdo->query("SELECT * FROM taches ORDER BY id DESC");
    $taches = $req->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM taches WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $taches = $stmt->fetchAll();
}

?>
