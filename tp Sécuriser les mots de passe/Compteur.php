<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connecte']) || !$_SESSION['connecte']) {
    header('Location: login.php');
    exit;
}

$fichier_compteur = 'compteur.txt';

if (!file_exists($fichier_compteur)) {
    file_put_contents($fichier_compteur, '0');
}

$nombre_vues = (int)file_get_contents($fichier_compteur);


$nombre_vues++;

file_put_contents($fichier_compteur, $nombre_vues);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compteur de Vues</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
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

        .container-counter {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            animation: slideIn 0.5s ease-out;
            margin-top: 80px;
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
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.5rem;
        }
        .counter-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 40px 20px;
            margin: 30px 0;
            font-size: 4rem;
            font-weight: bold;
            letter-spacing: 5px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s ease;
        }
        .counter-display:hover {
            transform: scale(1.05);
        }
        .label {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            margin-right: 10px;
        }
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        .dashboard-btn {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .dashboard-btn:hover {
            background: #667eea;
            color: white;
        }
        .info-text {
            color: #999;
            font-size: 0.9rem;
            margin-top: 30px;
            line-height: 1.6;
        }
        .action-buttons {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar-custom">
        <div class="navbar-brand">
            <i class="fas fa-eye"></i> Compteur de Vues
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

    <div class="container-counter">
        <h1>👁️ Compteur de Vues</h1>
        
        <div class="label">Nombre de visites</div>
        <div class="counter-display"><?php echo number_format($nombre_vues, 0, ',', ' '); ?></div>
        
        <p class="info-text">
            Cette page a été visitée <strong><?php echo $nombre_vues; ?></strong> fois.
        </p>
        
        <div class="action-buttons">
            <button class="refresh-btn" onclick="location.reload()">
                🔄 Rafraîchir la page
            </button>
            <a href="dashboard.php" class="refresh-btn dashboard-btn">
                📊 Voir le Dashboard
            </a>
        </div>
        
        <div class="info-text">
            <small>Chaque fois que vous visitez cette page, le compteur s'incrémente automatiquement.</small>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

