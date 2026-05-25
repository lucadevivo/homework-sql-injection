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

if (!isset($_SESSION['user_cf']) || $_SESSION['ruolo'] != 'admin') { header("Location: dashboard.php"); exit(); }

$conn = new mysqli("db", "root", "root", "ospedale");
$admin_loggato = $_SESSION['user_cf'];

if (isset($_POST['titolo'])) {
    $titolo = $_POST['titolo'];
    $contenuto = $_POST['contenuto'];
    $livello = $_POST['livello_riservatezza'];

    // VULNERABILE
    $sql = "INSERT INTO documenti_segreti (titolo, contenuto, livello_riservatezza, admin_creazione) 
            VALUES ('$titolo', '$contenuto', '$livello', '$admin_loggato')";
    
    if ($conn->multi_query($sql)) {
        header("Location: documenti_segreti.php?msg=Documento Creato");
        exit();
    } else {
        $errore = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Documento | Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0056b3; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;}
        .nav-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .form-group { margin-bottom: 25px; }
        label { display: block; font-weight: bold; color: #475569; margin-bottom: 10px; font-size: 15px; }
        input[type="text"], select, textarea { width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 16px; font-family: inherit; }
        textarea { resize: vertical; min-height: 120px; }
        .btn-submit { background-color: #008069; color: white; padding: 14px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; margin-top: 10px; }
        .btn-submit:hover { background-color: #006d59; }
        .error-msg { background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 4px; border: 1px solid #f87171; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Classificazione Documentale</h1>
    </div>
    <div class="container">
        <a href="documenti_segreti.php" class="nav-link">← Annulla e torna ai Documenti</a>
        <h2>Nuovo Documento Segreto</h2>
        
        <?php if(isset($errore)) echo "<div class='error-msg'><strong>Errore SQL:</strong> $errore</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Titolo Documento:</label>
                <input type="text" name="titolo" placeholder="Es. PASSWORD SERVER BACKUP" required>
            </div>
            <div class="form-group">
                <label>Livello di Riservatezza:</label>
                <select name="livello_riservatezza" required>
                    <option value="RISERVATO">RISERVATO</option>
                    <option value="CONFIDENZIALE">CONFIDENZIALE</option>
                    <option value="TOP SECRET">TOP SECRET</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contenuto Sensibile:</label>
                <textarea name="contenuto" placeholder="Inserisci il contenuto da secretare..." required></textarea>
            </div>
            <button type="submit" class="btn-submit">🔒 Salva Documento Cifrato</button>
        </form>
    </div>
</body>
</html>