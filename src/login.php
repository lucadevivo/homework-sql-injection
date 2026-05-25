<?php
session_start();
$messaggio_errore = "";
if (isset($_POST['cf'])) {
    $conn = new mysqli("db", "root", "root", "ospedale");
    $cf = $_POST['cf'];
    $password = $_POST['password'];
    $sql_admin = "SELECT username as id, nome, 'admin' as tipo, stato FROM admin WHERE username = '$cf' AND password = '$password'";
    $res_admin = $conn->query($sql_admin);
    $sql_medico = "SELECT codice_medico as id, nome, livello as tipo, stato FROM medici WHERE codice_medico = '$cf' AND password = '$password'";
    $res_medico = $conn->query($sql_medico);
    $sql_paziente = "SELECT codice_fiscale as id, nome, 'paziente' as tipo, stato FROM pazienti WHERE codice_fiscale = '$cf' AND password = '$password'";
    $res_paziente = $conn->query($sql_paziente);
    $utente_trovato = null;
    if ($res_admin && $res_admin->num_rows > 0) $utente_trovato = $res_admin->fetch_assoc();
    elseif ($res_medico && $res_medico->num_rows > 0) $utente_trovato = $res_medico->fetch_assoc();
    elseif ($res_paziente && $res_paziente->num_rows > 0) $utente_trovato = $res_paziente->fetch_assoc();
    if ($utente_trovato) {
        if ($utente_trovato['stato'] === 'sospeso') {
            $messaggio_errore = "Accesso negato: account SOSPESO.";
        } else {
            $_SESSION['user_cf'] = $utente_trovato['id'];
            $_SESSION['ruolo'] = $utente_trovato['tipo'];
            $_SESSION['nome'] = $utente_trovato['nome'];
            header("Location: dashboard.php");
            exit();
        }
    } else { $messaggio_errore = "Credenziali errate."; }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accesso Portale Sanitario</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f4f8; margin: 0; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 28px; }
        .container { max-width: 400px; margin: 60px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0056b3; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #ccd1d9; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #0056b3; color: white; padding: 14px; border: none; border-radius: 4px; width: 100%; cursor: pointer; font-weight: bold; }
        .alert { padding: 15px; margin-top: 20px; border-radius: 4px; text-align: center; background-color: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <div class="header"><h1>✚ Sistema Sanitario Nazionale</h1></div>
    <div class="container">
        <h2>Accesso Unificato</h2>
        <form method="POST">
            <div class="form-group"><label>Codice Fiscale / ID Personale:</label><input type="text" name="cf" required></div>
            <div class="form-group"><label>Password:</label><input type="password" name="password" required></div>
            <button type="submit">Autenticati</button>
        </form>
        <?php if ($messaggio_errore !== "") echo "<div class='alert'>$messaggio_errore</div>"; ?>
    </div>
</body>
</html>