<?php
session_start();
include("../conf/db_config.php");

const MAX_BIGLIETTI = 10;

if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok') {
    header("Location: ../login.php");
    exit;
}

if (($_SESSION['tipo'] ?? null) !== 'cliente') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $eventoId = (int) ($_GET['evento_id'] ?? 0);
    if ($eventoId > 0) {
        header("Location: ../prenota.php?evento_id=" . $eventoId);
    } else {
        header("Location: ../HomeClienti.php");
    }
    exit;
}

$eventoId = (int) ($_POST['evento_id'] ?? 0);
$quantita = (int) ($_POST['quantita'] ?? 0);
$userId = (int) ($_SESSION['id'] ?? 0);

if ($eventoId <= 0 || $userId <= 0 || $quantita < 1 || $quantita > MAX_BIGLIETTI) {
    header("Location: ../HomeClienti.php?msg=prenotazione_errore");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM eventi WHERE id = ? AND stato = 'approvato' LIMIT 1");
$stmt->bind_param("i", $eventoId);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if ($evento === null) {
    header("Location: ../HomeClienti.php?msg=prenotazione_errore");
    exit;
}

$insert = $conn->prepare("INSERT INTO prenotazioni (utente_id, evento_id, quantita, stato) VALUES (?, ?, 1, 'in_attesa')");
$insert->bind_param("ii", $userId, $eventoId);

$conn->begin_transaction();
$ok = true;
for ($i = 0; $i < $quantita; $i++) {
    if (!$insert->execute()) {
        $ok = false;
        break;
    }
}

if ($ok) {
    $conn->commit();
    header("Location: ../eventiPrenotati.php?msg=prenotato");
} else {
    $conn->rollback();
    header("Location: ../prenota.php?evento_id=" . $eventoId . "&msg=errore");
}
exit;
