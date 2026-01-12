<?php
session_start();

// Configuration
$fichier_utilisateurs = 'utilisateurs.json';

// Initialiser le fichier utilisateurs s'il n'existe pas
if (!file_exists($fichier_utilisateurs)) {
    file_put_contents($fichier_utilisateurs, json_encode(array()));
}

$erreur = '';
$succes = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide';
    } else if (empty($password) || strlen($password) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } else if ($action === 'inscription' && empty($nom)) {
        $erreur = 'Le nom est requis pour l\'inscription';
    } else {
        // Charger les utilisateurs existants
        $utilisateurs = json_decode(file_get_contents($fichier_utilisateurs), true);
        
        if ($action === 'inscription') {
            // Vérifier si l'email existe déjà
            $email_existe = false;
            foreach ($utilisateurs as $user) {
                if ($user['email'] === $email) {
                    $email_existe = true;
                    break;
                }
            }
            
            if ($email_existe) {
                $erreur = 'Cet email est déjà utilisé';
            } else {
                // Créer un nouvel utilisateur
                $nouvel_utilisateur = array(
                    'id' => uniqid(),
                    'nom' => $nom,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'date_creation' => date('Y-m-d H:i:s')
                );
                
                $utilisateurs[] = $nouvel_utilisateur;
                file_put_contents($fichier_utilisateurs, json_encode($utilisateurs, JSON_PRETTY_PRINT));
                
                $succes = 'Inscription réussie ! Connectez-vous maintenant.';
            }
        } else if ($action === 'connexion') {
            // Vérifier les identifiants
            $utilisateur_trouve = null;
            foreach ($utilisateurs as $user) {
                if ($user['email'] === $email && password_verify($password, $user['password'])) {
                    $utilisateur_trouve = $user;
                    break;
                }
            }
            
            if ($utilisateur_trouve) {
                // Créer la session
                $_SESSION['utilisateur_id'] = $utilisateur_trouve['id'];
                $_SESSION['utilisateur_nom'] = $utilisateur_trouve['nom'];
                $_SESSION['utilisateur_email'] = $utilisateur_trouve['email'];
                $_SESSION['connecte'] = true;
                
                // Rediriger vers le dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $erreur = 'Email ou mot de passe incorrect';
            }
        }
    }
}

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['connecte']) && $_SESSION['connecte']) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - Dashboard Traffic</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }

        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .auth-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .auth-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn-auth {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 15px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-signup {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-signup:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .toggle-form {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .toggle-form p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .toggle-form a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .toggle-form a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
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
            }
            to {
                opacity: 1;
            }
        }

        .logo-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            .auth-container {
                border-radius: 15px;
            }

            .auth-header {
                padding: 30px 20px;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .auth-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Header -->
        <div class="auth-header">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1>Dashboard Traffic</h1>
            <p>Authentification Sécurisée</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($succes)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $succes; ?>
                </div>
            <?php endif; ?>

            <!-- Connexion Form -->
            <form id="loginForm" class="form-section active" method="POST" action="">
                <input type="hidden" name="action" value="connexion">
                
                <div class="form-group">
                    <label for="email_login">Email</label>
                    <input type="email" id="email_login" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password_login">Mot de passe</label>
                    <input type="password" id="password_login" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-auth btn-login">
                    <i class="fas fa-sign-in-alt"></i> Se Connecter
                </button>

                <div class="toggle-form">
                    <p>Pas encore de compte ? <a onclick="toggleForm()">S'inscrire</a></p>
                </div>
            </form>

            <!-- Inscription Form -->
            <form id="signupForm" class="form-section" method="POST" action="">
                <input type="hidden" name="action" value="inscription">
                
                <div class="form-group">
                    <label for="nom_signup">Nom Complet</label>
                    <input type="text" id="nom_signup" name="nom" placeholder="Votre nom complet" required>
                </div>

                <div class="form-group">
                    <label for="email_signup">Email</label>
                    <input type="email" id="email_signup" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password_signup">Mot de passe</label>
                    <input type="password" id="password_signup" name="password" placeholder="Minimum 6 caractères" required>
                </div>

                <button type="submit" class="btn-auth btn-login">
                    <i class="fas fa-user-plus"></i> S'inscrire
                </button>

                <div class="toggle-form">
                    <p>Vous avez un compte ? <a onclick="toggleForm()">Se connecter</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            
            loginForm.classList.toggle('active');
            signupForm.classList.toggle('active');

            // Reset forms
            loginForm.reset();
            signupForm.reset();
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
