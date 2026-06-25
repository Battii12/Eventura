<?php
include("../conf/db_config.php");

$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$data_nascita = $_POST['data_nascita'] ?? '';
$nome_utente = trim($_POST['nome_utente'] ?? '');

if ($nome === '' || $cognome === '' || $email === '' || $nome_utente === '' || $psw === '' || $telefono === '' || $data_nascita === '') {
    header("Location: ../registra.php?msg=errore");
    exit;
}

$check = $conn->prepare("SELECT id FROM utenti WHERE email = ? OR nome_utente = ? LIMIT 1");
$check->bind_param("ss", $email, $nome_utente);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    if ($existing) {
        $check2 = $conn->prepare("SELECT id FROM utenti WHERE email = ? LIMIT 1");
        $check2->bind_param("s", $email);
        $check2->execute();
        $r2 = $check2->get_result();
        if ($r2 && $r2->num_rows > 0) {
            header("Location: ../registra.php?msg=emailesistente");
        } else {
            header("Location: ../registra.php?msg=nomeutentesistente");
        }
    } else {
        header("Location: ../registra.php?msg=errore");
    }
    exit;
}

$stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, email, nome_utente, psw, telefono, data_nascita) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $nome, $cognome, $email, $nome_utente, $psw, $telefono, $data_nascita);

if ($stmt->execute()) {
    header("Location: ../login.php?msg=registrato");
} else {
    header("Location: ../registra.php?msg=errore");
}
exit;
