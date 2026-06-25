<?php
session_start();
include("./templates/header_riservata.php");
include("./conf/db_config.php");
require_once("./PHP/eventoValidazione.php");

if (($_SESSION['tipo'] ?? '') !== 'societa') {
    header("Location: ./index.php");
    exit;
}

$statoSocieta = $_SESSION['stato_societa'] ?? 'in_attesa';
if ($statoSocieta !== 'approvata' && $statoSocieta !== 'approvato') {
    header("Location: ./HomeSocieta.php");
    exit;
}

$localitaList = [];
$locResult = $conn->query("SELECT id, nome FROM localita ORDER BY nome ASC");
if ($locResult) {
    $localitaList = $locResult->fetch_all(MYSQLI_ASSOC);
}

$formAction = './PHP/creaEventoCheck.php';
$submitLabel = 'Crea evento';
$evento = [];
?>

<section>
    <div style="max-width:640px; margin:0 auto;">
        <div class="page-title">
            <h1>Crea un nuovo evento</h1>
            <div class="gold-line"></div>
            <p>L'evento sarà inviato agli amministratori per l'approvazione.</p>
        </div>

        <?php if (!empty($_GET['msg'])): ?>
            <div class="msg-error" style="margin-bottom:12px;"><?php echo htmlspecialchars(messaggioErroreEvento($_GET['msg'])); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php include './templates/form_evento.php'; ?>
        </div>

        <div style="margin-top:16px; text-align:center;">
            <a class="btn btn-outline" href="./HomeSocieta.php">Annulla</a>
        </div>
    </div>
</section>

<?php include_once("./templates/footer_riservata.php"); ?>
