<?php
session_start();
include("./templates/header_riservata.php");
include("./conf/db_config.php");
require_once("./PHP/eventoValidazione.php");

if (($_SESSION['tipo'] ?? '') !== 'societa') {
    header("Location: ./index.php");
    exit;
}

$societaId = (int) ($_SESSION['id_societa'] ?? 0);

if (isset($_GET['d'])) {
    $eventoId = (int) $_GET['d'];
    if ($eventoId > 0 && $societaId > 0) {
        $stmt = $conn->prepare("DELETE FROM eventi WHERE id = ? AND societa_id = ?");
        $stmt->bind_param("ii", $eventoId, $societaId);
        $stmt->execute();
    }
    header("Location: ./HomeSocieta.php?msg=evento_eliminato");
    exit;
}

$eventoId = (int) ($_GET['u'] ?? 0);
$evento = null;
$error = null;

if ($eventoId <= 0 || $societaId <= 0) {
    $error = "Evento non valido.";
} else {
    $stmt = $conn->prepare("SELECT * FROM eventi WHERE id = ? AND societa_id = ? LIMIT 1");
    $stmt->bind_param("ii", $eventoId, $societaId);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();
    if ($evento === null) {
        $error = "Evento non trovato.";
    }
}

$localitaList = [];
$locResult = $conn->query("SELECT id, nome FROM localita ORDER BY nome ASC");
if ($locResult) {
    $localitaList = $locResult->fetch_all(MYSQLI_ASSOC);
}

$formAction = './PHP/modificaEventoCheck.php';
$submitLabel = 'Salva modifiche';
?>

<section>
    <div style="max-width:640px; margin:0 auto;">
        <div class="page-title">
            <h1>Modifica evento</h1>
            <div class="gold-line"></div>
        </div>

        <?php if ($error): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
            <div style="margin-top:16px; text-align:center;">
                <a class="btn btn-outline" href="./HomeSocieta.php">Torna alla home</a>
            </div>
        <?php else: ?>
            <?php if (!empty($_GET['msg'])): ?>
                <div class="msg-error" style="margin-bottom:12px;"><?php echo htmlspecialchars(messaggioErroreEvento($_GET['msg'])); ?></div>
            <?php endif; ?>

            <p style="text-align:center; color:var(--muted); margin-bottom:16px;">
                Stato attuale: <strong><?php echo htmlspecialchars($evento['stato']); ?></strong>
            </p>

            <div class="card">
                <?php include './templates/form_evento.php'; ?>
            </div>

            <div style="margin-top:16px; text-align:center;">
                <a class="btn btn-outline" href="./HomeSocieta.php">Annulla</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once("./templates/footer_riservata.php"); ?>
