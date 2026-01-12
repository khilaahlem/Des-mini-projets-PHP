<?php
// Fichier de configuration du compteur
$fichier_compteur = 'compteur.txt';

// Initialiser le compteur s'il n'existe pas
if (!file_exists($fichier_compteur)) {
    file_put_contents($fichier_compteur, '0');
}

// Lire le nombre de vues actuel
$nombre_vues = (int)file_get_contents($fichier_compteur);

// Incrémenter le compteur
$nombre_vues++;

// Enregistrer le nouveau nombre de vues
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-counter {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            animation: slideIn 0.5s ease-out;
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
        }
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        .info-text {
            color: #999;
            font-size: 0.9rem;
            margin-top: 30px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container-counter">
        <h1>👁️ Compteur de Vues</h1>
        
        <div class="label">Nombre de visites</div>
        <div class="counter-display"><?php echo number_format($nombre_vues, 0, ',', ' '); ?></div>
        
        <p class="info-text">
            Cette page a été visitée <strong><?php echo $nombre_vues; ?></strong> fois.
        </p>
        
        <button class="refresh-btn" onclick="location.reload()">
            🔄 Rafraîchir la page
        </button>
        
        <div class="info-text">
            <small>Chaque fois que vous visitez cette page, le compteur s'incrémente automatiquement.</small>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

