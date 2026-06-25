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
$eventoId = (int) ($_POST['evento_id'] ?? 0);
$titolo = trim($_POST['titolo'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$localitaId = (int) ($_POST['localita_id'] ?? 0);
$indirizzo = trim($_POST['indirizzo'] ?? '');
$descrizione = trim($_POST['descrizione'] ?? '');
$dataInizio = trim($_POST['data_inizio'] ?? '');
$dataFine = trim($_POST['data_fine'] ?? '');
$maxPartecipanti = trim($_POST['max_partecipanti'] ?? '');

if ($societaId <= 0 || $eventoId <= 0 || $titolo === '' || $categoria === '' || $localitaId <= 0) {
    header("Location: ../modificaEvento.php?u=" . $eventoId . "&msg=errore");
    exit;
}

$check = $conn->prepare("SELECT id FROM eventi WHERE id = ? AND societa_id = ? LIMIT 1");
$check->bind_param("ii", $eventoId, $societaId);
$check->execute();
if ($check->get_result()->fetch_assoc() === null) {
    header("Location: ../HomeSocieta.php?msg=non_trovato");
    exit;
}

$errore = validaDatiEvento($dataInizio, $dataFine, $maxPartecipanti);
if ($errore !== null) {
    header("Location: ../modificaEvento.php?u=" . $eventoId . "&msg=" . urlencode($errore));
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
        UPDATE eventi
        SET titolo = ?, categoria = ?, localita_id = ?, indirizzo = ?, descrizione = ?,
            data_inizio = ?, data_fine = ?, max_partecipanti = ?
        WHERE id = ? AND societa_id = ?
    ");
    $stmt->bind_param("ssissssiii", $titolo, $categoria, $localitaId, $indirizzo, $descrizione, $dataInizioDb, $dataFineDb, $maxPart, $eventoId, $societaId);
    $ok = $stmt->execute();
} elseif ($dataFineDb !== null) {
    $stmt = $conn->prepare("
        UPDATE eventi
        SET titolo = ?, categoria = ?, localita_id = ?, indirizzo = ?, descrizione = ?,
            data_inizio = ?, data_fine = ?, max_partecipanti = NULL
        WHERE id = ? AND societa_id = ?
    ");
    $stmt->bind_param("ssissssii", $titolo, $categoria, $localitaId, $indirizzo, $descrizione, $dataInizioDb, $dataFineDb, $eventoId, $societaId);
    $ok = $stmt->execute();
} elseif ($maxPart !== null) {
    $stmt = $conn->prepare("
        UPDATE eventi
        SET titolo = ?, categoria = ?, localita_id = ?, indirizzo = ?, descrizione = ?,
            data_inizio = ?, data_fine = NULL, max_partecipanti = ?
        WHERE id = ? AND societa_id = ?
    ");
    $stmt->bind_param("ssisssiii", $titolo, $categoria, $localitaId, $indirizzo, $descrizione, $dataInizioDb, $maxPart, $eventoId, $societaId);
    $ok = $stmt->execute();
} else {
    $stmt = $conn->prepare("
        UPDATE eventi
        SET titolo = ?, categoria = ?, localita_id = ?, indirizzo = ?, descrizione = ?,
            data_inizio = ?, data_fine = NULL, max_partecipanti = NULL
        WHERE id = ? AND societa_id = ?
    ");
    $stmt->bind_param("ssisssii", $titolo, $categoria, $localitaId, $indirizzo, $descrizione, $dataInizioDb, $eventoId, $societaId);
    $ok = $stmt->execute();
}

if ($ok) {
    salvaCoordinateEvento($conn, $eventoId, $localitaId, $indirizzo, $eventoId);
    header("Location: ../HomeSocieta.php?msg=evento_aggiornato");
} else {
    header("Location: ../modificaEvento.php?u=" . $eventoId . "&msg=errore");
}
exit;
