<?php
// Dashboard de Traffic
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connecte']) || !$_SESSION['connecte']) {
    header('Location: login.php');
    exit;
}

// Configuration
$fichier_compteur = 'compteur.txt';
$fichier_historique = 'historique_traffic.txt';

// Initialiser les fichiers s'ils n'existent pas
if (!file_exists($fichier_compteur)) {
    file_put_contents($fichier_compteur, '0');
}

if (!file_exists($fichier_historique)) {
    file_put_contents($fichier_historique, '');
}

// Lire le nombre de vues actuel
$nombre_vues = (int)file_get_contents($fichier_compteur);

// Enregistrer dans l'historique (une fois par jour)
$date_actuelle = date('Y-m-d');
$historique = file_get_contents($fichier_historique);
$lignes_historique = array_filter(explode("\n", $historique));

// Vérifier si on a déjà enregistré pour aujourd'hui
$enregistre_aujourd_hui = false;
$donnees_historique = array();

foreach ($lignes_historique as $ligne) {
    $parts = explode('|', $ligne);
    if (count($parts) == 2) {
        $donnees_historique[$parts[0]] = (int)$parts[1];
        if ($parts[0] == $date_actuelle) {
            $enregistre_aujourd_hui = true;
        }
    }
}

if (!$enregistre_aujourd_hui && !empty($nombre_vues)) {
    $donnees_historique[$date_actuelle] = $nombre_vues;
    ksort($donnees_historique);
    
    // Garder seulement les 30 derniers jours
    if (count($donnees_historique) > 30) {
        array_shift($donnees_historique);
    }
    
    $nouvelle_historique = '';
    foreach ($donnees_historique as $date => $vues) {
        $nouvelle_historique .= $date . '|' . $vues . "\n";
    }
    file_put_contents($fichier_historique, $nouvelle_historique);
}

// Calculer les statistiques
$moyenne_vues = count($donnees_historique) > 0 ? 
    round(array_sum($donnees_historique) / count($donnees_historique), 1) : 0;

$vues_max = count($donnees_historique) > 0 ? max($donnees_historique) : 0;
$vues_min = count($donnees_historique) > 0 ? min($donnees_historique) : 0;

// Calculer la progression
$progression_total = 0;
$progression_pourcentage = 0;
if (count($donnees_historique) > 1) {
    $dates = array_keys($donnees_historique);
    $derniere_date = end($dates);
    $avant_derniere_date = prev($donnees_historique);
    reset($donnees_historique);
    
    $premiere_valeur = reset($donnees_historique);
    $derniere_valeur = $donnees_historique[$derniere_date];
    
    $progression_total = $derniere_valeur - $premiere_valeur;
    if ($premiere_valeur > 0) {
        $progression_pourcentage = round(($progression_total / $premiere_valeur) * 100, 1);
    }
}

// Préparer les données pour le graphique
$labels = json_encode(array_keys($donnees_historique));
$data = json_encode(array_values($donnees_historique));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Traffic Analytics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .dashboard-header {
            color: white;
            margin-bottom: 40px;
            padding: 30px 0;
            text-align: center;
            animation: slideIn 0.6s ease-out;
        }

        .dashboard-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: fadeIn 0.6s ease-out;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card.highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-subtitle {
            font-size: 0.85rem;
            opacity: 0.6;
        }

        .progress-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .progress-positive {
            background-color: #d4edda;
            color: #155724;
        }

        .progress-negative {
            background-color: #f8d7da;
            color: #721c24;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease-out;
        }

        .chart-container h2 {
            color: #333;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin-bottom: 20px;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            flex-wrap: wrap;
            gap: 20px;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            font-weight: 600;
        }

        .user-email {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-logout:hover {
            background: white;
            color: #667eea;
        }

        .actions-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 30px;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-custom {
            background: white;
            color: #667eea;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            color: #764ba2;
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-secondary-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 2rem;
            }

            .cards-container {
                grid-template-columns: 1fr;
            }

            .chart-wrapper {
                height: 300px;
            }

            .actions-container {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Navbar -->
        <div class="navbar-custom">
            <div class="navbar-brand">
                <i class="fas fa-chart-line"></i> Dashboard
            </div>
            <div class="user-info">
                <div>
                    <div class="user-name">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['utilisateur_nom']); ?>
                    </div>
                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['utilisateur_email']); ?></div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <!-- Header -->
        <div class="dashboard-header">
            <h1><i class="fas fa-chart-line"></i> Dashboard Traffic</h1>
            <p>Analyse en temps réel de vos visiteurs</p>
        </div>

        <?php if ($nombre_vues > 0): ?>
            <!-- Statistics Cards -->
            <div class="cards-container">
                <!-- Total Views -->
                <div class="stat-card highlight">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-label">Vues Totales</div>
                    <div class="stat-value"><?php echo number_format($nombre_vues); ?></div>
                    <div class="stat-subtitle">Depuis le début</div>
                </div>

                <!-- Average Views -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar" style="color: #667eea;"></i>
                    </div>
                    <div class="stat-label">Moyenne Quotidienne</div>
                    <div class="stat-value"><?php echo number_format($moyenne_vues); ?></div>
                    <div class="stat-subtitle">Sur <?php echo count($donnees_historique); ?> jour(s)</div>
                </div>

                <!-- Max Views -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-up" style="color: #28a745;"></i>
                    </div>
                    <div class="stat-label">Pic de Traffic</div>
                    <div class="stat-value"><?php echo number_format($vues_max); ?></div>
                    <div class="stat-subtitle">Jour le plus actif</div>
                </div>

                <!-- Progression -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-pie" style="color: #764ba2;"></i>
                    </div>
                    <div class="stat-label">Progression</div>
                    <div class="stat-value">
                        <?php echo $progression_pourcentage >= 0 ? '+' : ''; 
                              echo $progression_pourcentage; ?>%
                    </div>
                    <div class="stat-subtitle">
                        <?php echo abs($progression_total) ?> vues
                    </div>
                    <?php if ($progression_pourcentage >= 0): ?>
                        <span class="progress-badge progress-positive">
                            <i class="fas fa-arrow-up"></i> En hausse
                        </span>
                    <?php else: ?>
                        <span class="progress-badge progress-negative">
                            <i class="fas fa-arrow-down"></i> En baisse
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <h2>
                    <i class="fas fa-chart-line" style="color: #667eea;"></i>
                    Évolution du Traffic
                </h2>
                <div class="chart-wrapper">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-container">
                <a href="Compteur.php" class="btn-action btn-primary-custom">
                    <i class="fas fa-sync-alt"></i> Voir le Compteur
                </a>
                <button class="btn-action btn-secondary-custom" onclick="location.reload();">
                    <i class="fas fa-redo"></i> Actualiser
                </button>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Pas de données</h2>
                <p>Aucune vue enregistrée pour le moment.</p>
                <a href="Compteur.php" class="btn-action btn-primary-custom" style="margin-top: 20px;">
                    <i class="fas fa-eye"></i> Visiter le Compteur
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chart.js Script -->
    <script>
        <?php if ($nombre_vues > 0): ?>
        const ctx = document.getElementById('trafficChart').getContext('2d');
        const trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Nombre de vues',
                    data: <?php echo $data; ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#764ba2',
                    hoverBorderWidth: 3,
                    segment: {
                        borderColor: ctx => ctx.p0 === null || ctx.p1 === null ? '#ccc' : '#667eea',
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            color: '#333',
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        borderColor: '#667eea',
                        borderWidth: 1,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return 'Vues: ' + context.parsed.y.toLocaleString('fr-FR');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#999',
                            font: { size: 12 },
                            callback: function(value) {
                                return value.toLocaleString('fr-FR');
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#999',
                            font: { size: 12 }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
