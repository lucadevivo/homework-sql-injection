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
    <title>Documenti Segreti | Admin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; color: #333; }
        .header { background-color: #0056b3; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 400; }
        .header-logo { font-weight: bold; font-size: 28px; }
        .container { max-width: 1200px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .nav-link { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .nav-link:hover { color: #0f172a; }
        
        /* Nuovo Bottone "Box" per Aggiungi Documento */
        .btn-add-box { 
            display: block; 
            background-color: #008069; 
            color: white; 
            padding: 20px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 18px; 
            font-weight: bold; 
            text-decoration: none; 
            transition: background-color 0.3s; 
            box-sizing: border-box; 
            width: 100%;
        }
        .btn-add-box:hover { background-color: #006d59; }

        /* Area Ricerca Normalizzata */
        .search-box { background-color: #e2e8f0; padding: 20px; border-radius: 6px; margin-bottom: 30px; display: flex; gap: 10px; align-items: center; }
        .search-box label { font-weight: bold; color: #1e293b; }
        input[type="text"] { flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        button.btn-search { background-color: #0056b3; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background-color 0.3s; }
        button.btn-search:hover { background-color: #004494; }
        
        /* Tabella */
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 15px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle;}
        th { background-color: #f8fafc; color: #475569; text-transform: uppercase; font-size: 13px; }
        tr:hover { background-color: #f1f5f9; }
        
        .badge { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-admin { background: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .date-text { color: #64748b; font-size: 0.85rem; }
        
        /* Bottoni Azione */
        .action-buttons-cell { display: flex; flex-direction: column; gap: 6px; width: 100px; }
        .action-btn { padding: 8px 0; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; text-align: center; border: none; cursor: pointer; width: 100%; box-sizing: border-box; font-family: inherit; margin: 0; transition: all 0.2s;}
        .btn-edit { background-color: #e0e7ff; color: #3730a3; }
        .btn-edit:hover { background-color: #c7d2fe; }
        .btn-delete { background-color: #fee2e2; color: #991b1b; }
        .btn-delete:hover { background-color: #fecaca; }
        
        /* Modale Eliminazione Normalizzata */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(2px); }
        .modal-box { background: white; padding: 30px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: popIn 0.3s ease-out; }
        .modal-box h3 { margin-top: 0; color: #1e293b; font-size: 20px; }
        .modal-box p { color: #475569; margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; justify-content: center; gap: 15px; }
        .btn-confirm { background-color: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; }
        .btn-confirm:hover { background-color: #b91c1c; }
        .btn-cancel { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; }
        .btn-cancel:hover { background-color: #e2e8f0; }

        @keyframes popIn { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">✚ Sistema Sanitario Nazionale</div>
        <h1>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>
    </div>
    <div class="container">
        <a href="dashboard.php" class="nav-link">← Torna alla Dashboard</a><br>
        
        <?php if(isset($_GET['msg'])) echo "<div style='background:#d1fae5; color:#065f46; padding:15px; margin-bottom:20px; border-radius:5px;'>Operazione completata: ".htmlspecialchars($_GET['msg'])."</div>"; ?>

        <a href="aggiungi_documento.php" class="btn-add-box">Aggiungi Nuovo Documento Segreto</a>

        <div class="search-box">
            <form method="POST" style="display: flex; width: 100%; gap: 15px; align-items: center;">
                <label>Filtra Risultati:</label>
                <input type="text" name="ricerca" placeholder="Filtra per titolo o contenuto..." value="<?php echo htmlspecialchars($ricerca); ?>">
                <button type="submit" class="btn-search">Applica Filtro</button>
            </form>
        </div>

        <?php
        $sql = "SELECT * FROM documenti_segreti WHERE titolo LIKE '%$ricerca%' OR contenuto LIKE '%$ricerca%' ORDER BY data_creazione DESC";
        
        if ($res = $conn->query($sql)) {
            echo "<table><tr><th>ID</th><th>Data</th><th>Autore</th><th>Titolo Documento</th><th>Contenuto Sensibile</th><th>Livello</th><th>Azioni</th></tr>";
            while ($row = $res->fetch_assoc()) {
                echo "<tr>";
                echo "<td><span style='background-color: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 14px;'>#{$row['id']}</span></td>";
                echo "<td><span class='date-text'>{$row['data_creazione']}</span></td>";
                echo "<td><span class='badge-admin'>".htmlspecialchars($row['admin_creazione'])."</span></td>";
                echo "<td><strong>{$row['titolo']}</strong></td>";
                echo "<td><code>" . htmlspecialchars($row['contenuto']) . "</code></td>";
                echo "<td><span class='badge'>{$row['livello_riservatezza']}</span></td>";
                echo "<td>
                        <div class='action-buttons-cell'>
                            <a href='modifica_documento.php?id={$row['id']}' class='action-btn btn-edit'>Modifica</a>
                            <button onclick='openDeleteModal({$row['id']})' class='action-btn btn-delete'>Elimina</button>
                        </div>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h3>⚠️ Conferma Eliminazione</h3>
            <p>Sei sicuro di voler eliminare definitivamente questo documento? L'operazione è irreversibile.</p>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn-cancel">Annulla</button>
                <button onclick="confirmDelete()" class="btn-confirm">Sì, Elimina</button>
            </div>
        </div>
    </div>

    <script>
        let targetId = null;
        function openDeleteModal(id) { targetId = id; document.getElementById('deleteModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('deleteModal').style.display = 'none'; }
        function confirmDelete() { window.location.href = 'elimina_documento.php?id=' + targetId; }
    </script>
</body>
</html>