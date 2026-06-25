<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVENTURA — Area Riservata</title>
    <link rel="stylesheet" href="./css/style.css">
    <?php if (!empty($leaflet)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
</head>
<body>

<?php
// Controllo sessione: se l'utente non è loggato, reindirizza al login
if (!isset($_SESSION['login']) || $_SESSION['login'] != 'ok') {
    header("Location: ./index.php?msg=sessionescaduta");
    exit();
}
?>

<header class="header-riservata">
    <div class="header-container header-container--riservata">
        <div class="logo">
            <?php
            $homeHref = "./HomeClienti.php";
            if (($_SESSION['tipo'] ?? null) === 'societa') {
                $homeHref = "./HomeSocieta.php";
            } elseif (($_SESSION['tipo'] ?? null) === 'amministratore') {
                $homeHref = "./HomeAmministratori.php";
            }
            ?>
            <a href="<?php echo htmlspecialchars($homeHref); ?>">
                <img src="./css/eventura-logo.jpg" alt="Eventura" style="height:140px; width:auto; display:block;">
            </a>
        </div>
        <nav class="site-nav" aria-label="Menu area riservata">
            <ul class="nav-list">
                <?php if (($_SESSION['tipo'] ?? null) !== 'societa' && ($_SESSION['tipo'] ?? null) !== 'amministratore'): ?>
                    <li><a class="nav-link" href="./eventiPrenotati.php">Eventi prenotati</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-actions">
                <span class="utente-info" role="status">
                    ✦ <?php
                    $displayName =
                        ($_SESSION['nome_utente'] ?? null)
                        ?: (($_SESSION['nome'] ?? '') !== '' || ($_SESSION['cognome'] ?? '') !== ''
                            ? trim(($_SESSION['nome'] ?? '') . ' ' . ($_SESSION['cognome'] ?? ''))
                            : null);

                    echo htmlspecialchars($displayName ?: ($_SESSION['utente'] ?? ''));
                    ?>
                </span>
                <a href="./php/logout.php" class="nav-link nav-link--logout">Esci</a>
            </div>
        </nav>
    </div>
</header>

<main>
