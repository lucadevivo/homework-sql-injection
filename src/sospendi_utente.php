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

if (!isset($_SESSION['user_cf']) || $_SESSION['ruolo'] != 'admin') exit();

$conn = new mysqli("db", "root", "root", "ospedale");
$id = $_GET['id'];
$ruolo = $_GET['ruolo'];
$azione = $_GET['azione']; 

$nuovo_stato = ($azione == 'sospendi') ? 'sospeso' : 'attivo';

if ($ruolo == 'Admin') { $tabella = 'admin'; $pk = 'username'; }
elseif ($ruolo == 'Paziente') { $tabella = 'pazienti'; $pk = 'codice_fiscale'; }
else { $tabella = 'medici'; $pk = 'codice_medico'; }

// Esegui l'aggiornamento
$sql = "UPDATE $tabella SET stato = '$nuovo_stato' WHERE $pk = '$id'";
$conn->query($sql);

// --- LOGICA DI AUTO-ESPULSIONE ---
if ($azione == 'sospendi' && $id == $_SESSION['user_cf']) {
    session_destroy();
    header("Location: login.php?msg=Hai sospeso il tuo account. Accesso terminato.");
    exit();
}

header("Location: gestione_utenti.php?msg=Stato aggiornato con successo");
exit();
?>