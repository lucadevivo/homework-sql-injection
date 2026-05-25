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
$ricerca = isset($_POST['ricerca']) ? $_POST['ricerca'] : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti | Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        .container { max-width: 1200px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .nav-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .search-box { background-color: #e2e8f0; padding: 20px; border-radius: 6px; margin-bottom: 30px; display: flex; gap: 10px; align-items: center; }
        .search-box label { font-weight: bold; color: #1e293b; }
        input[type="text"] { flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        button.btn-search { background-color: #0056b3; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background-color 0.3s; }
        button.btn-search:hover { background-color: #004494; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: white;}
        th, td { padding: 15px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
        th { background-color: #f8fafc; color: #475569; text-transform: uppercase; font-size: 13px; font-weight: bold;}
        .role-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .role-admin { background-color: #fee2e2; color: #991b1b; }
        .role-medico { background-color: #fef3c7; color: #92400e; }
        .role-paziente { background-color: #e0e7ff; color: #3730a3; }
        .stato-attivo { color: #008069; font-weight: bold; }
        .stato-sospeso { color: #dc2626; font-weight: bold; text-decoration: line-through; }
        
        .action-btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; cursor: pointer; border: none; width: 100%; box-sizing: border-box; }
        .btn-suspend { background-color: #fee2e2; color: #991b1b; }
        .btn-suspend:hover { background-color: #fecaca; }
        .btn-unlock { background-color: #d1fae5; color: #065f46; }
        .btn-unlock:hover { background-color: #a7f3d0; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-box { background: white; padding: 30px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-confirm { background-color: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-confirm-green { background-color: #008069; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-cancel { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Pannello Amministrazione</h1>
    </div>
    <div class="container">
        <a href="dashboard.php" class="nav-link">← Torna alla Dashboard</a>
        
        <?php if(isset($_GET['msg'])) echo "<div style='background:#d1fae5; color:#065f46; padding:15px; margin-bottom:20px; border-radius:5px;'>".htmlspecialchars($_GET['msg'])."</div>"; ?>

        <div class="search-box">
            <form method="POST" style="display: flex; width: 100%; gap: 15px; align-items: center;">
                <label>Filtra Risultati:</label>
                <input type="text" name="ricerca" placeholder="Cerca per nome..." value="<?php echo htmlspecialchars($ricerca); ?>">
                <button type="submit" class="btn-search">Applica Filtro</button>
            </form>
        </div>

        <?php
        // Query VULNERABILE che ora preleva anche lo stato!
        $sql = "SELECT * FROM (
                    SELECT username AS id, nome, 'Admin' AS ruolo, stato FROM admin
                    UNION
                    SELECT codice_medico AS id, nome, livello AS ruolo, stato FROM medici
                    UNION
                    SELECT codice_fiscale AS id, nome, 'Paziente' AS ruolo, stato FROM pazienti
                ) AS tutti_utenti 
                WHERE nome LIKE '%$ricerca%' 
                ORDER BY ruolo, nome";
        
        if ($conn->multi_query($sql)) {
            do {
                if ($result = $conn->store_result()) {
                    if ($result->num_rows > 0) {
                        echo "<table><tr><th>Codice / ID</th><th>Nominativo</th><th>Stato Account</th><th>Livello Accesso</th><th>Azioni</th></tr>";
                        while ($row = $result->fetch_assoc()) {
                            $badge_class = 'role-paziente';
                            if ($row['ruolo'] == 'Admin') $badge_class = 'role-admin';
                            elseif (strpos($row['ruolo'], 'medico') !== false) $badge_class = 'role-medico';
                            
                            $stato_class = ($row['stato'] == 'attivo') ? 'stato-attivo' : 'stato-sospeso';

                            echo "<tr>";
                            echo "<td><strong style='color:#475569;'>" . htmlspecialchars($row['id']) . "</strong></td>";
                            echo "<td class='$stato_class'>" . htmlspecialchars($row['nome']) . "</td>";
                            echo "<td>" . strtoupper(htmlspecialchars($row['stato'])) . "</td>";
                            echo "<td><span class='role-badge $badge_class'>" . str_replace('_', ' ', $row['ruolo']) . "</span></td>";
                            
                            echo "<td>";
                            // TASTO DINAMICO: Se attivo fa sospendere, se sospeso fa sbloccare
                            if ($row['stato'] == 'attivo') {
                                echo "<button class='action-btn btn-suspend' onclick='openModal(\"sospendi\", \"{$row['id']}\", \"{$row['ruolo']}\")'>Sospendi</button>";
                            } else {
                                echo "<button class='action-btn btn-unlock' onclick='openModal(\"sblocca\", \"{$row['id']}\", \"{$row['ruolo']}\")'>Sblocca</button>";
                            }
                            echo "</td></tr>";
                        }
                        echo "</table>";
                    }
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }
        ?>
    </div>

    <div id="actionModal" class="modal-overlay">
        <div class="modal-box">
            <h3 id="modalTitle">⚠️ Conferma</h3>
            <p id="modalText">Sei sicuro?</p>
            <div>
                <button onclick="closeModal()" class="btn-cancel">Annulla</button>
                <button id="modalBtn" onclick="executeAction()" class="btn-confirm">Conferma</button>
            </div>
        </div>
    </div>

    <script>
        let currAction, currId, currRole;

        function openModal(azione, id, ruolo) {
            currAction = azione; currId = id; currRole = ruolo;
            
            if(azione === 'sospendi') {
                document.getElementById('modalTitle').innerHTML = "⚠️ Sospensione Account";
                document.getElementById('modalText').innerHTML = "L'utente non potrà più accedere al portale. Procedere?";
                document.getElementById('modalBtn').className = "btn-confirm";
                document.getElementById('modalBtn').innerHTML = "Sì, Sospendi";
            } else {
                document.getElementById('modalTitle').innerHTML = "Sblocco Account";
                document.getElementById('modalText').innerHTML = "L'utente sarà riabilitato all'accesso. Procedere?";
                document.getElementById('modalBtn').className = "btn-confirm-green";
                document.getElementById('modalBtn').innerHTML = "Sì, Sblocca";
            }
            document.getElementById('actionModal').style.display = 'flex';
        }

        function closeModal() { document.getElementById('actionModal').style.display = 'none'; }
        
        function executeAction() {
            window.location.href = `sospendi_utente.php?id=${currId}&ruolo=${currRole}&azione=${currAction}`;
        }
    </script>
</body>
</html>