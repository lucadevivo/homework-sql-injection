<?php
session_start();

// 1. Controllo base: se non c'è la sessione, vai al login
if (!isset($_SESSION['user_cf'])) { 
    header("Location: login.php"); 
    exit(); 
}

// 2. IL NUOVO PEZZO: Controllo real-time dello stato account nel Database
$id_check = $_SESSION['user_cf'];
$ruolo_check = $_SESSION['ruolo'];
$conn_check = new mysqli("db", "root", "root", "ospedale");

if ($ruolo_check == 'admin') {
    $sql_check = "SELECT stato FROM admin WHERE username = '$id_check'";
} elseif ($ruolo_check == 'paziente') {
    $sql_check = "SELECT stato FROM pazienti WHERE codice_fiscale = '$id_check'";
} else {
    $sql_check = "SELECT stato FROM medici WHERE codice_medico = '$id_check'";
}

$res_check = $conn_check->query($sql_check);
if ($res_check) {
    $user_data = $res_check->fetch_assoc();
    // Se nel DB risulta sospeso, distruggi la sessione e buttalo fuori
    if ($user_data['stato'] == 'sospeso') {
        session_destroy();
        header("Location: login.php?msg=Il tuo account è stato sospeso. Sessione terminata.");
        exit();
    }
}

if (!isset($_SESSION['user_cf']) || $_SESSION['ruolo'] == 'paziente') { header("Location: dashboard.php"); exit(); }

$conn = new mysqli("db", "root", "root", "ospedale");
$id = $_GET['id'];

if (isset($_POST['diagnosi'])) {
    $diag = $_POST['diagnosi'];
    $tera = $_POST['terapia'];
    
    // VULNERABILE: UPDATE via SQLi
    $sql = "UPDATE referti SET diagnosi = '$diag', terapia = '$tera' WHERE id = $id";
    $conn->multi_query($sql);
    header("Location: referti.php?msg=Modificato");
    exit();
}

$res = $conn->query("SELECT * FROM referti WHERE id = $id");
if (!$res || $res->num_rows == 0) {
    die("Referto non trovato.");
}
$ref = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Referto | Azienda Ospedaliera</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0056b3; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;}
        .form-group { margin-bottom: 25px; }
        label { display: block; font-weight: bold; color: #475569; margin-bottom: 10px; font-size: 15px; }
        textarea { width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; font-family: inherit; resize: vertical; min-height: 120px; transition: border-color 0.3s; }
        textarea:focus { border-color: #0056b3; outline: none; box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1); }
        .btn-submit { background-color: #008069; color: white; padding: 14px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background-color 0.3s; width: 100%; }
        .btn-submit:hover { background-color: #006d59; }
        .nav-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .nav-link:hover { color: #0f172a; }
        .id-badge { background-color: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 20px; font-size: 16px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Modifica Pratica Clinica</h1>
    </div>

    <div class="container">
        <a href="referti.php" class="nav-link">← Annulla e torna ai Referti</a>
        
        <h2>Aggiornamento Referto <span class='id-badge'>#<?php echo $id; ?></span></h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="diagnosi">Diagnosi Clinica:</label>
                <textarea id="diagnosi" name="diagnosi" required><?php echo htmlspecialchars($ref['diagnosi']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="terapia">Terapia Assegnata / Note:</label>
                <textarea id="terapia" name="terapia" required><?php echo htmlspecialchars($ref['terapia']); ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Salva Modifiche</button>
        </form>
    </div>

</body>
</html> 