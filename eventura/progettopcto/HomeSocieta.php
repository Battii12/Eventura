<?php
session_start();
$leaflet = true;
include("./templates/header_riservata.php");
include("./conf/db_config.php");
require_once("./includes/eventi_mappa.php");

if (($_SESSION['tipo'] ?? '') !== 'societa') {
    header("Location: ./index.php");
    exit;
}

$societaId = (int) ($_SESSION['id_societa'] ?? 0);
$societa = null;

if ($societaId > 0) {
    $stmtSoc = $conn->prepare("
        SELECT id, nome_societa, partita_iva, email, telefono, indirizzo, citta, sito_web, stato
        FROM societa WHERE id = ? LIMIT 1
    ");
    $stmtSoc->bind_param("i", $societaId);
    $stmtSoc->execute();
    $societa = $stmtSoc->get_result()->fetch_assoc();
}

if ($societa === null) {
    header("Location: ./php/logout.php");
    exit;
}

$_SESSION['stato_societa'] = $societa['stato'];
$statoSocieta = $societa['stato'];
?>

<section>
    <h1>Area società — <?php echo htmlspecialchars($societa['nome_societa']); ?></h1>
    <?php if ($statoSocieta === 'approvata' || $statoSocieta === 'approvato'): ?>
        <div class="section-actions">
            <a href="./creaEvento.php" class="btn">Crea evento</a>
        </div>
    <?php endif; ?>
</section>

<?php
if (isset($_GET['msg'])) {
    $msgs = [
        'evento_creato'      => ['msg-success', 'Evento creato e inviato in attesa di approvazione.'],
        'evento_aggiornato'  => ['msg-success', 'Evento aggiornato con successo.'],
        'evento_eliminato'   => ['msg-success', 'Evento eliminato.'],
        'richiesta_inviata'  => ['msg-success', 'Nuova richiesta inviata. Attendi l’approvazione di un amministratore.'],
        'errore_richiesta'   => ['msg-error', 'Impossibile inviare la richiesta. Controlla i dati e riprova.'],
        'emailesistente'     => ['msg-error', 'Email già usata da un’altra organizzazione.'],
        'pivaesistente'      => ['msg-error', 'Partita IVA già registrata da un’altra organizzazione.'],
        'non_rifiutata'      => ['msg-error', 'Operazione non consentita nello stato attuale.'],
    ];
    if (isset($msgs[$_GET['msg']])) {
        [$cls, $text] = $msgs[$_GET['msg']];
        echo '<p class="' . htmlspecialchars($cls) . '">' . htmlspecialchars($text) . '</p>';
    }
}

if ($statoSocieta === 'rifiutata') {
    ?>
    <div class="container" style="max-width:560px; margin-top:24px;">
        <div class="card">
            <h2 style="margin-bottom:12px; color:#ff9090;">Richiesta non accettata</h2>
            <p style="color:var(--muted); margin-bottom:20px; line-height:1.7;">
                La registrazione della tua organizzazione è stata <strong>rifiutata</strong> dagli amministratori.
                Puoi aggiornare i dati e inviare una <strong>nuova richiesta</strong> di approvazione.
            </p>
            <form action="./PHP/rinnovaSocietaCheck.php" method="POST">
                <div>
                    <label for="nome_societa">Nome società</label>
                    <input type="text" id="nome_societa" name="nome_societa" value="<?php echo htmlspecialchars($societa['nome_societa']); ?>" required>
                </div>
                <div>
                    <label for="partita_iva">Partita IVA</label>
                    <input type="text" id="partita_iva" name="partita_iva" value="<?php echo htmlspecialchars($societa['partita_iva'] ?? ''); ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($societa['email']); ?>" required>
                </div>
                <div>
                    <label for="psw">Nuova password (opzionale)</label>
                    <input type="password" id="psw" name="psw" placeholder="Lascia vuoto per mantenere la password attuale">
                </div>
                <div>
                    <label for="telefono">Telefono (opzionale)</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($societa['telefono'] ?? ''); ?>">
                </div>
                <div>
                    <label for="indirizzo">Indirizzo (opzionale)</label>
                    <input type="text" id="indirizzo" name="indirizzo" value="<?php echo htmlspecialchars($societa['indirizzo'] ?? ''); ?>">
                </div>
                <div>
                    <label for="citta">Città (opzionale)</label>
                    <input type="text" id="citta" name="citta" value="<?php echo htmlspecialchars($societa['citta'] ?? ''); ?>">
                </div>
                <div>
                    <label for="sito_web">Sito web (opzionale)</label>
                    <input type="text" id="sito_web" name="sito_web" value="<?php echo htmlspecialchars($societa['sito_web'] ?? ''); ?>">
                </div>
                <input type="submit" value="Invia nuova richiesta">
            </form>
        </div>
    </div>
    <?php
    include_once("./templates/footer_riservata.php");
    exit;
}

if ($statoSocieta !== 'approvata' && $statoSocieta !== 'approvato') {
    echo '<div class="card" style="max-width:560px; margin:24px auto 0;"><h3>La tua società è in attesa di essere accettata</h3>';
    echo '<p style="color:var(--muted); margin-top:10px;">Riceverai accesso completo alla piattaforma dopo l’approvazione di un amministratore.</p></div>';
    include_once("./templates/footer_riservata.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT e.*,
           l.nome AS localita_nome,
           l.latitudine AS loc_lat,
           l.longitudine AS loc_lng
    FROM eventi e
    LEFT JOIN localita l ON e.localita_id = l.id
    WHERE e.societa_id = ?
    ORDER BY e.data_inizio ASC
");
$stmt->bind_param("i", $societaId);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$stmtMap = $conn->prepare("
    SELECT e.*,
           l.nome AS localita_nome,
           l.latitudine AS loc_lat,
           l.longitudine AS loc_lng,
           s.nome_societa
    FROM eventi e
    LEFT JOIN localita l ON e.localita_id = l.id
    LEFT JOIN societa s ON e.societa_id = s.id
    WHERE " . sqlEventiMappaSocieta() . "
    ORDER BY e.data_inizio ASC
");
$stmtMap->bind_param("i", $societaId);
$stmtMap->execute();
$rowsMap = $stmtMap->get_result()->fetch_all(MYSQLI_ASSOC);

$mapEvents = buildMapEvents($rowsMap, 'societa', $societaId);
$mapCities = caricaMapCities($conn);
?>

<div class="container">
<?php
$mappaTitolo = 'Mappa eventi';
$mappaSottotitolo = 'Tutti gli eventi approvati; in attesa solo i tuoi.';
$mappaPopupTipo = 'societa';
include './templates/mappa_eventi.php';
?>
</div>

<?php
if ($rows !== null && count($rows) > 0) {
    echo "<div class=\"events-block\">
        <h2>I miei eventi</h2>
        <div class=\"table-wrap\">
        <table class=\"table\">
        <thead><tr>
        <th>Titolo</th>
        <th>Categoria</th>
        <th>Località</th>
        <th>Indirizzo</th>
        <th>Inizio</th>
        <th>Fine</th>
        <th>Stato</th>
        <th class=\"table-actions\" colspan=\"2\">Azioni</th>
        </tr></thead><tbody>";

    foreach ($rows as $row) {
        $id = (int) $row['id'];
        echo "<tr>
            <td>" . htmlspecialchars($row['titolo']) . "</td>
            <td>" . htmlspecialchars($row['categoria'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['localita_nome'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['indirizzo'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['data_inizio']) . "</td>
            <td>" . htmlspecialchars($row['data_fine'] ?? '—') . "</td>
            <td>" . htmlspecialchars($row['stato']) . "</td>
            <td class=\"table-actions\" colspan=\"2\">
                <a href=\"./modificaEvento.php?u={$id}\" class=\"btn btn-sm btn-outline\">Modifica</a>
                <a href=\"./modificaEvento.php?d={$id}\" class=\"btn btn-sm btn-danger\" onclick=\"return confirm('Eliminare questo evento?');\">Elimina</a>
            </td>
            </tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div><h3>Non hai ancora creato alcun evento</h3></div>";
}
?>

<?php
include_once("./templates/footer_riservata.php");
?>
