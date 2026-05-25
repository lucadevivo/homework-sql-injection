<?php
session_start();

if (!isset($_SESSION['user_cf'])) { 
    header("Location: login.php"); 
    exit(); 
}

// Controllo real-time dello stato account
$id_check = $_SESSION['user_cf'];
$ruolo_check = $_SESSION['ruolo'];
$conn_check = new mysqli("db", "root", "root", "ospedale");

if ($ruolo_check == 'admin') $sql_check = "SELECT stato FROM admin WHERE username = '$id_check'";
elseif ($ruolo_check == 'paziente') $sql_check = "SELECT stato FROM pazienti WHERE codice_fiscale = '$id_check'";
else $sql_check = "SELECT stato FROM medici WHERE codice_medico = '$id_check'";

$res_check = $conn_check->query($sql_check);
if ($res_check) {
    $user_data = $res_check->fetch_assoc();
    if ($user_data['stato'] == 'sospeso') {
        session_destroy();
        header("Location: login.php?msg=Il tuo account è stato sospeso. Sessione terminata.");
        exit();
    }
}

$ruolo = $_SESSION['ruolo'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Azienda Ospedaliera</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #030404; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; display: flex; align-items: center; justify-content: space-between;}
        .menu-list { list-style: none; padding: 0; margin-top: 20px; }
        .menu-list li { margin-bottom: 12px; }
        .menu-list a { display: block; padding: 15px 20px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #0056b3; text-decoration: none; font-weight: bold; transition: all 0.2s; font-size: 16px;}
        .menu-list a:hover { background-color: #e0e7ff; border-color: #c7d2fe; transform: translateX(5px); }
        .logout-btn { display: inline-block; margin-top: 20px; color: #dc2626; text-decoration: none; font-weight: bold; }
        .logout-btn:hover { text-decoration: underline; }
        .badge { background: #e0e7ff; color: #3730a3; padding: 6px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;}
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>
    </div>

    <div class="container">
        <h2>Menu Principale <span class="badge">Ruolo: <?php echo strtoupper($ruolo); ?></span></h2>
        
        <ul class="menu-list">
            <li><a href="referti.php">📋 Gestione e Archivio Referti</a></li>

            <?php if ($ruolo == 'admin' || $ruolo == 'super_admin'): ?>
                <li><a href="gestione_utenti.php">⚙️ Gestione Utenti e Permessi</a></li>
                <li><a href="documenti_segreti.php">🔒 Archivio Documenti Segreti</a></li>
            <?php endif; ?>
        </ul>

        <a href="login.php" class="logout-btn">← Disconnetti (Esci)</a>
    </div>
</body>
</html>