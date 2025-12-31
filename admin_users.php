<?php
session_start();
require 'db.php';

// --- 1. SÉCURITÉ ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = "";

// Variables par défaut pour le formulaire (Mode AJOUT)
$mode_edition = false;
$form_action = 'ajouter';
$id_edit = '';
$username_val = '';
$role_val = 'user';
$btn_text = 'Créer le compte';
$btn_class = 'btn-success';

// --- 2. TRAITEMENT DU FORMULAIRE (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // CAS A : AJOUTER
    if ($_POST['action'] === 'ajouter') {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$_POST['username'], sha1($_POST['password']), $_POST['role']]);
                $message = '<div class="alert alert-success">Utilisateur ajouté !</div>';
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Erreur : Ce pseudo existe déjà.</div>';
            }
        }
    }

    // CAS B : MODIFIER
    if ($_POST['action'] === 'modifier') {
        $id = $_POST['id'];
        $user = $_POST['username'];
        $role = $_POST['role'];
        $pass = $_POST['password'];

        try {
            // Si le mot de passe est rempli, on met tout à jour
            if (!empty($pass)) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$user, $role, sha1($pass), $id]);
            } 
            // Sinon, on met à jour SAUF le mot de passe
            else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$user, $role, $id]);
            }
            $message = '<div class="alert alert-warning">Utilisateur modifié avec succès !</div>';
            
            // On redirige pour nettoyer le formulaire (sortir du mode édition)
            header("Location: admin_users.php");
            exit();

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur lors de la modification.</div>';
        }
    }
}

// --- 3. TRAITEMENT DE L'URL (GET) ---

// CAS : SUPPRIMER
if (isset($_GET['supprimer'])) {
    if ($_GET['supprimer'] != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET['supprimer']]);
    }
    header("Location: admin_users.php");
    exit();
}

// CAS : PRÉPARER L'ÉDITION (Quand on clique sur "Modifier")
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $user_to_edit = $stmt->fetch();

    if ($user_to_edit) {
        $mode_edition = true;
        $form_action = 'modifier';
        $id_edit = $user_to_edit['id'];
        $username_val = $user_to_edit['username'];
        $role_val = $user_to_edit['role'];
        $btn_text = 'Mettre à jour';
        $btn_class = 'btn-warning';
    }
}

// --- 4. RÉCUPÉRATION DE LA LISTE ---
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Utilisateurs</h1>
        <a href="index.php" class="btn btn-secondary">Retour au site</a>
    </div>

    <?= $message ?>

    <div class="row">
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header text-white <?= $mode_edition ? 'bg-warning' : 'bg-success' ?>">
                    <h5 class="mb-0">
                        <?= $mode_edition ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="admin_users.php">
                        <input type="hidden" name="action" value="<?= $form_action ?>">
                        <input type="hidden" name="id" value="<?= $id_edit ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Pseudo</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($username_val) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="<?= $mode_edition ? '(Laisser vide pour ne pas changer)' : 'Mot de passe obligatoire' ?>" 
                                   <?= $mode_edition ? '' : 'required' ?>>
                            <?php if($mode_edition): ?>
                                <small class="text-muted" style="font-size: 0.8em;">Ne rien écrire si vous gardez l'ancien.</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="user" <?= $role_val == 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                <option value="admin" <?= $role_val == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                            </select>
                        </div>

                        <button type="submit" class="btn <?= $btn_class ?> w-100">
                            <?= $btn_text ?>
                        </button>

                        <?php if($mode_edition): ?>
                            <a href="admin_users.php" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Pseudo</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                            <tr class="<?= ($mode_edition && $u['id'] == $id_edit) ? 'table-warning' : '' ?>">
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <?php if($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="admin_users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning">
                                            ✏️
                                        </a>

                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin_users.php?supprimer=<?= $u['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer ?');">
                                               🗑️
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>