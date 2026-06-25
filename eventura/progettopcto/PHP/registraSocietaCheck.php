<?php
session_start();
include("../conf/db_config.php");

$nome_societa = trim($_POST['nome_societa'] ?? '');
$partita_iva = trim($_POST['partita_iva'] ?? '');
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$indirizzo = trim($_POST['indirizzo'] ?? '');
$citta = trim($_POST['citta'] ?? '');
$sito_web = trim($_POST['sito_web'] ?? '');

if ($nome_societa === '' || $partita_iva === '' || $email === '' || $psw === '') {
    header("Location: ../registratiSocieta.php?msg=errore");
    exit;
}

$check = $conn->prepare("SELECT id, email, partita_iva FROM societa WHERE email = ? OR partita_iva = ? LIMIT 1");
$check->bind_param("ss", $email, $partita_iva);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

if ($existing) {
    if (($existing['email'] ?? '') === $email) {
        header("Location: ../registratiSocieta.php?msg=emailesistente");
    } else {
        header("Location: ../registratiSocieta.php?msg=pivaesistente");
    }
    exit;
}

$stato = 'in_attesa';
$stmt = $conn->prepare("INSERT INTO societa (nome_societa, partita_iva, email, telefono, indirizzo, citta, sito_web, psw, stato) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $nome_societa, $partita_iva, $email, $telefono, $indirizzo, $citta, $sito_web, $psw, $stato);

if (!$stmt->execute()) {
    header("Location: ../registratiSocieta.php?msg=errore");
    exit;
}

$_SESSION['login'] = 'ok';
$_SESSION['tipo'] = 'societa';
$_SESSION['id_societa'] = $conn->insert_id;
$_SESSION['utente'] = $email;
$_SESSION['nome_societa'] = $nome_societa;
$_SESSION['stato_societa'] = $stato;
$_SESSION['nome'] = $nome_societa;
$_SESSION['nome_utente'] = $nome_societa;
$_SESSION['cognome'] = '';

header("Location: ../HomeSocieta.php");
exit;
