<?php
session_start();

// 1. Controllo base
if (!isset($_SESSION['user_cf']) || $_SESSION['ruolo'] == 'paziente') { 
    header("Location: dashboard.php"); 
    exit(); 
}

// 2. Controllo real-time dello stato account
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

// 3. Logica di inserimento
$conn = new mysqli("db", "root", "root", "ospedale");
$cf_loggato = $_SESSION['user_cf'];
$ruolo = $_SESSION['ruolo'];

if (isset($_POST['paziente_cf'])) {
    $paziente = $_POST['paziente_cf'];
    $diagnosi = $_POST['diagnosi'];
    $terapia = $_POST['terapia'];

    // VULNERABILE: Concatenazione diretta per SQL Injection
    $sql = "INSERT INTO referti (paziente_cf, diagnosi, terapia) VALUES ('$paziente', '$diagnosi', '$terapia')";
    
    if ($conn->multi_query($sql)) {
        header("Location: referti.php?msg=Referto aggiunto con successo");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Referto | Azienda Ospedaliera</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0056b3; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-top: 0;}
        
        /* Stile Link Torna Indietro */
        .nav-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .nav-link:hover { color: #0f172a; }
        
        .form-group { margin-bottom: 25px; }
        label { display: block; font-weight: bold; color: #475569; margin-bottom: 10px; font-size: 15px; }
        
        /* Stile Menu a Tendina (Select) personalizzato */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: white;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            width: 100%;
            padding: 15px 40px 15px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            font-family: inherit;
            color: #333;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        /* Stile Textarea */
        textarea { 
            width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 6px; 
            box-sizing: border-box; font-size: 16px; font-family: inherit; 
            resize: vertical; min-height: 120px; transition: border-color 0.3s; 
        }

        /* Focus States per Select e Textarea */
        select:focus, textarea:focus { 
            border-color: #0056b3; outline: none; box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1); 
        }
        select:hover { border-color: #94a3b8; }

        /* Bottone Submit */
        .btn-submit { 
            background-color: #008069; color: white; padding: 14px 24px; border: none; 
            border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; 
            transition: background-color 0.3s; width: 100%; margin-top: 10px;
        }
        .btn-submit:hover { background-color: #006d59; }
        
        .error-msg { background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 4px; border: 1px solid #f87171; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Emissione Pratica Clinica</h1>
    </div>

    <div class="container">
        <a href="referti.php" class="nav-link">← Torna ad Archivio Referti</a>
        
        <h2>Compilazione Nuovo Referto</h2>
        
        <?php if(isset($errore)) echo "<div class='error-msg'><strong>Errore SQL:</strong> $errore</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label for="paziente_cf">Seleziona Paziente:</label>
                <select id="paziente_cf" name="paziente_cf" required>
                    <option value="" disabled selected>-- Scegli un paziente dall'elenco --</option>
                    <?php
                    // Mostra solo i pazienti assegnati se è un medico (l'admin vede tutti)
                    $filtro = ($ruolo != 'admin') ? "WHERE medico_assegnato = '$cf_loggato'" : "";
                    $res = $conn->query("SELECT codice_fiscale, nome FROM pazienti $filtro ORDER BY nome ASC");
                    while($p = $res->fetch_assoc()) {
                        echo "<option value='{$p['codice_fiscale']}'>{$p['nome']} ({$p['codice_fiscale']})</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="diagnosi">Diagnosi Clinica:</label>
                <textarea id="diagnosi" name="diagnosi" placeholder="Descrivi qui i sintomi e la diagnosi accertata..." required></textarea>
            </div>
            
            <div class="form-group">
                <label for="terapia">Terapia Assegnata / Note:</label>
                <textarea id="terapia" name="terapia" placeholder="Indica farmaci, posologia o note aggiuntive..." required></textarea>
            </div>
            
            <button type="submit" class="btn-submit">💾 Registra Referto nel Database</button>
        </form>
    </div>

</body>
</html>