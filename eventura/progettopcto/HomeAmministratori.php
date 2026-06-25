<?php
session_start();
include("./conf/db_config.php");
require_once("./includes/eventi_mappa.php");

if (($_SESSION['tipo'] ?? '') !== 'amministratore') {
    header("Location: ./index.php");
    exit;
}

$adminId = (int) ($_SESSION['id'] ?? 0);
$msg = null;

if (isset($_GET['accetta']) && $adminId > 0) {
    $eventoId = (int) $_GET['accetta'];
    if ($eventoId > 0) {
        $stmt = $conn->prepare("
            UPDATE eventi
            SET stato = 'approvato', approvato_da = ?
            WHERE id = ? AND stato = 'in_attesa'
        ");
        $stmt->bind_param("ii", $adminId, $eventoId);
        $stmt->execute();
        $msg = $stmt->affected_rows > 0 ? 'evento_approvato' : 'non_trovato';
    } else {
        $msg = 'errore';
    }
    header("Location: ./HomeAmministratori.php?msg=" . $msg);
    exit;
}

if (isset($_GET['rifiuta']) && $adminId > 0) {
    $eventoId = (int) $_GET['rifiuta'];
    if ($eventoId > 0) {
        $stmt = $conn->prepare("
            UPDATE eventi
            SET stato = 'rifiutato', approvato_da = ?
            WHERE id = ? AND stato = 'in_attesa'
        ");
        $stmt->bind_param("ii", $adminId, $eventoId);
        $stmt->execute();
        $msg = $stmt->affected_rows > 0 ? 'evento_rifiutato' : 'non_trovato';
    } else {
        $msg = 'errore';
    }
    header("Location: ./HomeAmministratori.php?msg=" . $msg);
    exit;
}

if (isset($_GET['accetta_societa']) && $adminId > 0) {
    $societaId = (int) $_GET['accetta_societa'];
    if ($societaId > 0) {
        $stmt = $conn->prepare("
            UPDATE societa
            SET stato = 'approvata', approvato_da = ?
            WHERE id = ? AND stato = 'in_attesa'
        ");
        $stmt->bind_param("ii", $adminId, $societaId);
        $stmt->execute();
        $msg = $stmt->affected_rows > 0 ? 'societa_approvata' : 'non_trovato';
    } else {
        $msg = 'errore';
    }
    header("Location: ./HomeAmministratori.php?msg=" . $msg);
    exit;
}

if (isset($_GET['rifiuta_societa']) && $adminId > 0) {
    $societaId = (int) $_GET['rifiuta_societa'];
    if ($societaId > 0) {
        $stmt = $conn->prepare("
            UPDATE societa
            SET stato = 'rifiutata', approvato_da = ?
            WHERE id = ? AND stato = 'in_attesa'
        ");
        $stmt->bind_param("ii", $adminId, $societaId);
        $stmt->execute();
        $msg = $stmt->affected_rows > 0 ? 'societa_rifiutata' : 'non_trovato';
    } else {
        $msg = 'errore';
    }
    header("Location: ./HomeAmministratori.php?msg=" . $msg);
    exit;
}

$leaflet = true;
include("./templates/header_riservata.php");
?>

<section>
    <h1>Area amministratori — <?php echo htmlspecialchars($_SESSION['nome'] . " " . $_SESSION['cognome']); ?></h1>
</section>

<?php
if (isset($_GET['msg'])) {
    $messages = [
        'evento_approvato'  => ['msg-success', 'Evento approvato. Ora è visibile a tutti.'],
        'evento_rifiutato'  => ['msg-error', 'Evento rifiutato.'],
        'societa_approvata' => ['msg-success', 'Organizzazione approvata. Può pubblicare eventi.'],
        'societa_rifiutata' => ['msg-error', 'Organizzazione rifiutata.'],
        'approvato'         => ['msg-success', 'Evento approvato. Ora è visibile a tutti.'],
        'non_trovato'       => ['msg-error', 'Elemento non trovato o già elaborato.'],
        'errore'            => ['msg-error', 'Operazione non riuscita.'],
    ];
    $m = $messages[$_GET['msg']] ?? null;
    if ($m !== null) {
        echo '<p class="' . htmlspecialchars($m[0]) . '">' . htmlspecialchars($m[1]) . '</p>';
    }
}

$stmt = $conn->prepare("
    SELECT e.*,
           l.nome AS localita_nome,
           l.latitudine AS loc_lat,
           l.longitudine AS loc_lng,
           s.nome_societa
    FROM eventi e
    LEFT JOIN localita l ON e.localita_id = l.id
    LEFT JOIN societa s ON e.societa_id = s.id
    WHERE (e.stato = 'approvato' OR e.stato = 'in_attesa')
      AND " . sqlEventiNonPassati() . "
    ORDER BY e.data_inizio ASC
");
$stmt->execute();
$rowsMap = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$mapEvents = buildMapEvents($rowsMap, 'amministratore');
$mapCities = caricaMapCities($conn);

$stmtSocieta = $conn->prepare("
    SELECT id, nome_societa, partita_iva, email, telefono, citta, stato
    FROM societa
    WHERE stato = 'in_attesa'
    ORDER BY nome_societa ASC
");
$stmtSocieta->execute();
$rowsSocieta = $stmtSocieta->get_result()->fetch_all(MYSQLI_ASSOC);

$stmtAttesa = $conn->prepare("
    SELECT e.*, l.nome AS localita_nome, s.nome_societa
    FROM eventi e
    LEFT JOIN localita l ON e.localita_id = l.id
    LEFT JOIN societa s ON e.societa_id = s.id
    WHERE e.stato = 'in_attesa'
    ORDER BY e.data_inizio ASC
");
$stmtAttesa->execute();
$rowsAttesa = $stmtAttesa->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
<?php
$mappaTitolo = 'Mappa eventi';
$mappaSottotitolo = 'Eventi futuri approvati e in attesa di approvazione.';
$mappaPopupTipo = 'admin';
include './templates/mappa_eventi.php';
?>
</div>

<?php
if (count($rowsSocieta) > 0) {
    echo "<div class=\"events-block\">
        <h2>Organizzazioni da accettare</h2>
        <div class=\"table-wrap\">
        <table class=\"table\">
        <thead><tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Partita IVA</th>
        <th>Città</th>
        <th>Telefono</th>
        <th class=\"table-actions\" colspan=\"2\">Azioni</th>
        </tr></thead><tbody>";

    foreach ($rowsSocieta as $row) {
        $id = (int) $row['id'];
        echo "<tr>
            <td>" . htmlspecialchars($row['nome_societa']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td>" . htmlspecialchars($row['partita_iva'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['citta'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['telefono'] ?? '—') . "</td>
            <td class=\"table-actions\" colspan=\"2\">
                <a href=\"./HomeAmministratori.php?accetta_societa={$id}\" class=\"btn btn-sm\">Accetta</a>
                <a href=\"./HomeAmministratori.php?rifiuta_societa={$id}\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Rifiutare questa organizzazione?');\">Rifiuta</a>
            </td>
            </tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div class=\"events-block\"><h2>Organizzazioni da accettare</h2><p style=\"color:var(--muted);\">Nessuna organizzazione in attesa di approvazione.</p></div>";
}

if (count($rowsAttesa) > 0) {
    echo "<div class=\"events-block\">
        <h2>Eventi da accettare</h2>
        <div class=\"table-wrap\">
        <table class=\"table\">
        <thead><tr>
        <th>Titolo</th>
        <th>Categoria</th>
        <th>Località</th>
        <th>Inizio</th>
        <th>Organizzatore</th>
        <th class=\"table-actions\" colspan=\"2\">Azioni</th>
        </tr></thead><tbody>";

    foreach ($rowsAttesa as $row) {
        $id = (int) $row['id'];
        echo "<tr>
            <td>" . htmlspecialchars($row['titolo']) . "</td>
            <td>" . htmlspecialchars($row['categoria'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['localita_nome'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['data_inizio']) . "</td>
            <td>" . htmlspecialchars($row['nome_societa'] ?? '—') . "</td>
            <td class=\"table-actions\" colspan=\"2\">
                <a href=\"./HomeAmministratori.php?accetta={$id}\" class=\"btn btn-sm\">Accetta</a>
                <a href=\"./HomeAmministratori.php?rifiuta={$id}\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Rifiutare questo evento?');\">Rifiuta</a>
            </td>
            </tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div class=\"events-block\"><h2>Eventi da accettare</h2><p style=\"color:var(--muted);\">Nessun evento in attesa di approvazione.</p></div>";
}
?>

<?php
include_once("./templates/footer_riservata.php");
?>
