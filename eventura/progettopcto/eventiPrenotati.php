<?php
session_start();
include("./templates/header_riservata.php");
include("./conf/db_config.php");

$userId = (int) (($_SESSION['id'] ?? null) ?? ($_SESSION['id_utente'] ?? 0));

$biglietti = [];
$error = null;

if ($userId <= 0) {
    $error = "Sessione non valida.";
} else {
    $stmt = $conn->prepare("
        SELECT p.*,
               e.titolo,
               e.categoria,
               e.data_inizio,
               e.data_fine,
               l.nome AS localita_nome,
               s.nome_societa
        FROM prenotazioni p
        LEFT JOIN eventi e ON p.evento_id = e.id
        LEFT JOIN localita l ON e.localita_id = l.id
        LEFT JOIN societa s ON e.societa_id = s.id
        WHERE p.utente_id = ?
          AND DATE(e.data_inizio) > CURDATE()
        ORDER BY e.data_inizio ASC, p.id ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($rows as $row) {
            $qty = max(1, (int) ($row['quantita'] ?? 1));
            $prenId = (int) ($row['id'] ?? 0);
            for ($i = 1; $i <= $qty; $i++) {
                $biglietti[] = [
                    'titolo'         => $row['titolo'],
                    'localita_nome'  => $row['localita_nome'],
                    'data_inizio'    => $row['data_inizio'],
                    'nome_societa'   => $row['nome_societa'],
                    'stato'          => $row['stato'],
                    'qr_code'        => $qty > 1 ? "EVENTURA-PRN-{$prenId}-{$i}" : "EVENTURA-PRN-{$prenId}",
                ];
            }
        }
    } else {
        $error = "Errore nella query prenotazioni.";
    }
}
?>

<section>
    <h1>I miei biglietti prenotati</h1>
    <p>Biglietti per eventi con data successiva a oggi. Mostra il QR code all'ingresso.</p>
    <?php
    if (isset($_GET['msg']) && $_GET['msg'] === 'prenotato') {
        echo '<p class="msg-success">Prenotazione registrata con successo.</p>';
    }
    ?>
</section>

<section style="margin-top:24px;">
<?php if ($error): ?>
    <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
<?php elseif (!$biglietti): ?>
    <div class="card">
        <h3>Nessun biglietto attivo</h3>
        <p style="color:var(--muted); margin-top:8px;">Qui compaiono solo i biglietti per eventi futuri. Gli eventi passati non vengono mostrati.</p>
        <div style="margin-top:16px;">
            <a class="btn btn-outline" href="./HomeClienti.php">Torna alla home</a>
        </div>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Evento</th>
                <th>Località</th>
                <th>Data</th>
                <th>Organizzatore</th>
                <th>Stato</th>
                <th>Ingresso</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($biglietti as $biglietto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($biglietto['titolo'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($biglietto['localita_nome'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($biglietto['data_inizio'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($biglietto['nome_societa'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($biglietto['stato'] ?? '—'); ?></td>
                    <td>
                        <div class="qr-ingresso" data-code="<?php echo htmlspecialchars($biglietto['qr_code']); ?>"></div>
                        <span class="qr-codice"><?php echo htmlspecialchars($biglietto['qr_code']); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</section>

<?php if ($biglietti): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.querySelectorAll('.qr-ingresso').forEach(function (el) {
    new QRCode(el, {
        text: el.dataset.code,
        width: 96,
        height: 96,
        colorDark: '#1a1a1a',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
});
</script>
<?php endif; ?>

<?php include_once("./templates/footer_riservata.php"); ?>
