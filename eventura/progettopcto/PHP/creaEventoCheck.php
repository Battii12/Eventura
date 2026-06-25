<?php
session_start();
include("../conf/db_config.php");
include("eventoValidazione.php");
require_once("../includes/evento_coordinate.php");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok' || ($_SESSION['tipo'] ?? '') !== 'societa') {
    header("Location: ../login.php");
    exit;
}

$statoSocieta = $_SESSION['stato_societa'] ?? 'in_attesa';
if ($statoSocieta !== 'approvata' && $statoSocieta !== 'approvato') {
    header("Location: ../HomeSocieta.php");
    exit;
}

$societaId = (int) ($_SESSION['id_societa'] ?? 0);
$titolo = trim($_POST['titolo'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$localitaId = (int) ($_POST['localita_id'] ?? 0);
$indirizzo = trim($_POST['indirizzo'] ?? '');
$descrizione = trim($_POST['descrizione'] ?? '');
$dataInizio = trim($_POST['data_inizio'] ?? '');
$dataFine = trim($_POST['data_fine'] ?? '');
$maxPartecipanti = trim($_POST['max_partecipanti'] ?? '');

if ($societaId <= 0 || $titolo === '' || $categoria === '' || $localitaId <= 0) {
    header("Location: ../creaEvento.php?msg=errore");
    exit;
}

$errore = validaDatiEvento($dataInizio, $dataFine, $maxPartecipanti);
if ($errore !== null) {
    header("Location: ../creaEvento.php?msg=" . urlencode($errore));
    exit;
}

$dataInizioDb = normalizzaDateTimeEvento($dataInizio);
$dataFineDb = null;
if ($dataFine !== '') {
    $dataFineDb = normalizzaDateTimeEvento($dataFine);
}

$maxPart = ($maxPartecipanti !== '') ? (int) $maxPartecipanti : null;

$ok = false;

if ($dataFineDb !== null && $maxPart !== null) {
    $stmt = $conn->prepare("
        INSERT INTO eventi (societa_id, localita_id, titolo, descrizione, indirizzo, data_inizio, data_fine, stato, max_partecipanti, categoria)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'in_attesa', ?, ?)
    ");
    $stmt->bind_param("iisssssis", $societaId, $localitaId, $titolo, $descrizione, $indirizzo, $dataInizioDb, $dataFineDb, $maxPart, $categoria);
    $ok = $stmt->execute();
} elseif ($dataFineDb !== null) {
    $stmt = $conn->prepare("
        INSERT INTO eventi (societa_id, localita_id, titolo, descrizione, indirizzo, data_inizio, data_fine, stato, max_partecipanti, categoria)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'in_attesa', NULL, ?)
    ");
    $stmt->bind_param("iissssss", $societaId, $localitaId, $titolo, $descrizione, $indirizzo, $dataInizioDb, $dataFineDb, $categoria);
    $ok = $stmt->execute();
} elseif ($maxPart !== null) {
    $stmt = $conn->prepare("
        INSERT INTO eventi (societa_id, localita_id, titolo, descrizione, indirizzo, data_inizio, data_fine, stato, max_partecipanti, categoria)
        VALUES (?, ?, ?, ?, ?, ?, NULL, 'in_attesa', ?, ?)
    ");
    $stmt->bind_param("iissssis", $societaId, $localitaId, $titolo, $descrizione, $indirizzo, $dataInizioDb, $maxPart, $categoria);
    $ok = $stmt->execute();
} else {
    $stmt = $conn->prepare("
        INSERT INTO eventi (societa_id, localita_id, titolo, descrizione, indirizzo, data_inizio, data_fine, stato, max_partecipanti, categoria)
        VALUES (?, ?, ?, ?, ?, ?, NULL, 'in_attesa', NULL, ?)
    ");
    $stmt->bind_param("iisssss", $societaId, $localitaId, $titolo, $descrizione, $indirizzo, $dataInizioDb, $categoria);
    $ok = $stmt->execute();
}

if ($ok) {
    $nuovoId = (int) $conn->insert_id;
    if ($nuovoId > 0) {
        salvaCoordinateEvento($conn, $nuovoId, $localitaId, $indirizzo);
    }
    header("Location: ../HomeSocieta.php?msg=evento_creato");
} else {
    header("Location: ../creaEvento.php?msg=errore");
}
exit;
