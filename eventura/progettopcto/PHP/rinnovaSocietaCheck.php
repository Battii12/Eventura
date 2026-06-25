<?php
session_start();
include("../conf/db_config.php");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'ok' || ($_SESSION['tipo'] ?? '') !== 'societa') {
    header("Location: ../login.php");
    exit;
}

$societaId = (int) ($_SESSION['id_societa'] ?? 0);
if ($societaId <= 0) {
    header("Location: ../HomeSocieta.php?msg=errore");
    exit;
}

$checkStato = $conn->prepare("SELECT stato FROM societa WHERE id = ? LIMIT 1");
$checkStato->bind_param("i", $societaId);
$checkStato->execute();
$rowStato = $checkStato->get_result()->fetch_assoc();

if ($rowStato === null || ($rowStato['stato'] ?? '') !== 'rifiutata') {
    header("Location: ../HomeSocieta.php?msg=non_rifiutata");
    exit;
}

$nome_societa = trim($_POST['nome_societa'] ?? '');
$partita_iva = trim($_POST['partita_iva'] ?? '');
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$indirizzo = trim($_POST['indirizzo'] ?? '');
$citta = trim($_POST['citta'] ?? '');
$sito_web = trim($_POST['sito_web'] ?? '');

if ($nome_societa === '' || $partita_iva === '' || $email === '') {
    header("Location: ../HomeSocieta.php?msg=errore_richiesta");
    exit;
}

$dup = $conn->prepare("SELECT id, email, partita_iva FROM societa WHERE (email = ? OR partita_iva = ?) AND id != ? LIMIT 1");
$dup->bind_param("ssi", $email, $partita_iva, $societaId);
$dup->execute();
$existing = $dup->get_result()->fetch_assoc();

if ($existing) {
    if (($existing['email'] ?? '') === $email) {
        header("Location: ../HomeSocieta.php?msg=emailesistente");
    } else {
        header("Location: ../HomeSocieta.php?msg=pivaesistente");
    }
    exit;
}

if ($psw !== '') {
    $stmt = $conn->prepare("
        UPDATE societa
        SET nome_societa = ?, partita_iva = ?, email = ?, telefono = ?, indirizzo = ?,
            citta = ?, sito_web = ?, psw = ?, stato = 'in_attesa', approvato_da = NULL
        WHERE id = ? AND stato = 'rifiutata'
    ");
    $stmt->bind_param("ssssssssi", $nome_societa, $partita_iva, $email, $telefono, $indirizzo, $citta, $sito_web, $psw, $societaId);
} else {
    $stmt = $conn->prepare("
        UPDATE societa
        SET nome_societa = ?, partita_iva = ?, email = ?, telefono = ?, indirizzo = ?,
            citta = ?, sito_web = ?, stato = 'in_attesa', approvato_da = NULL
        WHERE id = ? AND stato = 'rifiutata'
    ");
    $stmt->bind_param("sssssssi", $nome_societa, $partita_iva, $email, $telefono, $indirizzo, $citta, $sito_web, $societaId);
}

if (!$stmt->execute() || $stmt->affected_rows === 0) {
    header("Location: ../HomeSocieta.php?msg=errore_richiesta");
    exit;
}

$_SESSION['stato_societa'] = 'in_attesa';
$_SESSION['nome_societa'] = $nome_societa;
$_SESSION['utente'] = $email;
$_SESSION['nome'] = $nome_societa;
$_SESSION['nome_utente'] = $nome_societa;

header("Location: ../HomeSocieta.php?msg=richiesta_inviata");
exit;
