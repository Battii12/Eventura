<?php
session_start();
include("./templates/header_riservata.php");
include("./conf/db_config.php");

if (($_SESSION['tipo'] ?? null) !== 'cliente') {
    header("Location: ./index.php");
    exit;
}

$eventoId = (int) ($_GET['evento_id'] ?? 0);
$evento = null;
$error = null;

if ($eventoId <= 0) {
    $error = "Evento non valido.";
} else {
    $stmt = $conn->prepare("
        SELECT e.*,
               l.nome AS localita_nome,
               s.nome_societa
        FROM eventi e
        LEFT JOIN localita l ON e.localita_id = l.id
        LEFT JOIN societa s ON e.societa_id = s.id
        WHERE e.id = ? AND e.stato = 'approvato'
        LIMIT 1
    ");
    $stmt->bind_param("i", $eventoId);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();
    if ($evento === null) {
        $error = "Evento non trovato o non disponibile.";
    }
}

$maxBiglietti = 10;
?>

<section>
    <div style="max-width:520px; margin:0 auto;">
        <div class="page-title">
            <h1>Prenota biglietti</h1>
            <div class="gold-line"></div>
        </div>

        <?php
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'quantita_non_valida') {
                echo '<div class="msg-error" style="margin-bottom:12px;">Numero di biglietti non valido.</div>';
            } elseif ($_GET['msg'] === 'errore') {
                echo '<div class="msg-error" style="margin-bottom:12px;">Errore durante la prenotazione. Riprova.</div>';
            }
        }
        ?>
        <?php if ($error): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
            <div style="margin-top:16px; text-align:center;">
                <a class="btn btn-outline" href="./HomeClienti.php">Torna agli eventi</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2 style="margin-bottom:8px;"><?php echo htmlspecialchars($evento['titolo']); ?></h2>
                <p style="color:var(--muted); margin-bottom:4px;">
                    <?php echo htmlspecialchars($evento['localita_nome'] ?? ''); ?>
                    <?php if (!empty($evento['indirizzo'])): ?>
                        — <?php echo htmlspecialchars($evento['indirizzo']); ?>
                    <?php endif; ?>
                </p>
                <p style="color:var(--muted); margin-bottom:16px;">
                    <?php echo htmlspecialchars($evento['data_inizio']); ?>
                    <?php if (!empty($evento['data_fine'])): ?>
                        → <?php echo htmlspecialchars($evento['data_fine']); ?>
                    <?php endif; ?>
                </p>
                <p style="margin-bottom:20px;">
                    Organizzatore: <strong><?php echo htmlspecialchars($evento['nome_societa'] ?? '—'); ?></strong>
                </p>

                <form action="./PHP/prenotaEvento.php" method="POST">
                    <input type="hidden" name="evento_id" value="<?php echo (int) $evento['id']; ?>">
                    <div>
                        <label for="quantita">Numero di biglietti</label>
                        <input
                            type="number"
                            id="quantita"
                            name="quantita"
                            min="1"
                            max="<?php echo $maxBiglietti; ?>"
                            value="1"
                            required
                        >
                        <p style="font-size:0.8rem; color:var(--muted); margin-top:6px;">
                            Puoi prenotare più volte lo stesso evento. Massimo <?php echo $maxBiglietti; ?> biglietti per prenotazione.
                        </p>
                    </div>
                    <input type="submit" value="Conferma prenotazione" style="margin-top:16px;">
                </form>
            </div>
            <div style="margin-top:16px; text-align:center;">
                <a class="btn btn-outline" href="./HomeClienti.php">Annulla</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once("./templates/footer_riservata.php"); ?>
