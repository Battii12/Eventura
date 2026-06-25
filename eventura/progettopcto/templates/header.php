<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVENTURA</title>
    <link rel="stylesheet" href="./css/style.css">
    <?php if (!empty($leaflet)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
</head>
<body>

<header>
    <div class="header-container header-container--pubblico">
        <div class="logo">
            <a href="./index.php">
                <img src="./css/eventura-logo.jpg" alt="Eventura" style="height:100px; width:auto; display:block;">
            </a>
        </div>
        <nav class="site-nav site-nav--pubblico" aria-label="Menu principale">
            <div class="nav-actions nav-actions--pubblico">
                <a href="./login.php" class="btn btn-sm">Accedi / Registrati</a>
                <a href="./servizi-societa.php" class="btn btn-sm btn-outline">Sei una società? Scopri i nostri servizi</a>
            </div>
        </nav>
    </div>
</header>

<main>
