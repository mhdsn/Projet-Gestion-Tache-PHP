<?php
require 'traitement.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">
                Bonjour, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Invité') ?></strong> 
                
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <span class="badge bg-danger ms-2">Admin</span>
                <?php else: ?>
                    <span class="badge bg-primary ms-2">Utilisateur</span>
                <?php endif; ?>
            </span>

            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_users.php" class="btn btn-dark btn-sm">Gérer les utilisateurs</a>
                <?php endif; ?>

                <a href="logout.php" class="btn btn-outline-danger btn-sm">Se déconnecter</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            
            <div class="col-md-8">
                <h3 class="mb-4">Liste des tâches</h3>

                <?php if (count($taches) === 0): ?>
                    <div class="alert alert-info text-center">
                        Aucune tâche pour le moment. Profitez-en pour vous reposer ou en créer une !
                    </div>
                <?php endif; ?>

                <?php foreach($taches as $tache): ?>
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title fw-bold m-0 text-dark">
                                    <?= htmlspecialchars($tache['titre']) ?>
                                </h5>
                                
                                <?php $badgeColor = ($tache['statut'] === 'Terminée') ? 'bg-success' : 'bg-primary'; ?>
                                <span class="badge <?= $badgeColor ?>"><?= htmlspecialchars($tache['statut']) ?></span>
                            </div>
                            
                            <p class="card-text text-secondary"><?= nl2br(htmlspecialchars($tache['description'])) ?></p>

                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">ID: #<?= $tache['id'] ?></small>
                                
                                <div>
                                    <a href="index.php?edit=<?= $tache['id'] ?>" class="btn btn-outline-primary btn-sm">Modifier</a>
                                    
                                    <form action="traitement.php" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette tâche ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?= $tache['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 80px; z-index: 1;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><?= $cardTitle ?></h5>
                        
                        <?php if($formAction == 'modifier'): ?>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <form action="traitement.php" method="POST">
                            <input type="hidden" name="action" value="<?= $formAction ?>">
                            <input type="hidden" name="id" value="<?= $formId ?>">

                            <div class="mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Titre</label>
                                <input type="text" name="titre" class="form-control" required value="<?= htmlspecialchars($formTitre) ?>" placeholder="Ex: Faire les courses">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="4" required placeholder="Détails de la tâche..."><?= htmlspecialchars($formDesc) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-secondary small text-uppercase fw-bold">Statut</label>
                                <select name="statut" class="form-select">
                                    <option value="En cours" <?= $formStatut == 'En cours' ? 'selected' : '' ?>>En cours</option>
                                    <option value="Terminée" <?= $formStatut == 'Terminée' ? 'selected' : '' ?>>Terminée</option>
                                </select>
                            </div>

                            <button type="submit" class="btn <?= $btnClass ?> w-100 py-2 fw-bold">
                                <?= $btnText ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>
