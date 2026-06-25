<?php
session_start();
include("../conf/db_config.php");

function ensureAdminsDemo(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(80) NOT NULL,
            `cognome` varchar(80) NOT NULL,
            `email` varchar(180) NOT NULL,
            `nome_utente` varchar(80) NOT NULL,
            `psw` varchar(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_admin_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $countResult = $conn->query("SELECT COUNT(*) AS totale FROM admins");
    $count = $countResult ? (int) $countResult->fetch_assoc()['totale'] : 0;

    if ($count === 0) {
        $conn->query("
            INSERT INTO `admins` (`id`, `nome`, `cognome`, `email`, `nome_utente`, `psw`) VALUES
            (1, 'Arianna', 'Dutto', 'arianna.dutto@eventicuneo.it', 'arianna.dutto@eventicuneo.it', '12345678'),
            (2, 'Samuele', 'Fenoglio', 'samuele.fenoglio@eventicuneo.it', 'samuele.fenoglio@eventicuneo.it', '12345678')
        ");
    }

    // Tutte le password admin in chiaro (demo)
    $conn->query("UPDATE admins SET psw = '12345678'");
}

function trovaAdmin(mysqli $conn, string $login, string $psw): ?array
{
    $result = $conn->query("SELECT id, nome, cognome, email, nome_utente, psw FROM admins");
    if (!$result) {
        return null;
    }

    $loginNorm = strtolower(trim($login));
    $pswNorm = trim($psw);

    while ($row = $result->fetch_assoc()) {
        $emailDb = strtolower(trim((string) $row['email']));
        $utenteDb = strtolower(trim((string) $row['nome_utente']));
        $pswDb = trim((string) $row['psw']);

        $loginOk = ($loginNorm === $emailDb || $loginNorm === $utenteDb);
        if ($loginOk && $pswDb === $pswNorm) {
            return $row;
        }
    }

    return null;
}

ensureAdminsDemo($conn);

$email = trim($_POST['email'] ?? '');
$psw = trim($_POST['psw'] ?? '');

if ($email === '' || $psw === '') {
    header("location: ../login.php?msg=loginerrato");
    exit;
}


// 1) Login amministratore
$stmtAdmin = $conn->prepare("SELECT * FROM admins WHERE email = ? AND psw = ? LIMIT 1");
$stmtAdmin->bind_param("ss", $email, $psw);
$stmtAdmin->execute();
$admin = $stmtAdmin->get_result()->fetch_assoc();

if ($admin === null) {
    $stmtAdmin = $conn->prepare("SELECT * FROM admins WHERE LOWER(email) = LOWER(?) AND psw = ? LIMIT 1");
    $stmtAdmin->bind_param("ss", $email, $psw);
    $stmtAdmin->execute();
    $admin = $stmtAdmin->get_result()->fetch_assoc();
}

if ($admin !== null) {
    $_SESSION['login'] = 'ok';
    $_SESSION['tipo'] = 'amministratore';
    $_SESSION['id'] = $admin['id'];
    $_SESSION['nome'] = $admin['nome'];
    $_SESSION['cognome'] = $admin['cognome'];
    $_SESSION['utente'] = $admin['email'];
    $_SESSION['nome_utente'] = $admin['nome_utente'];
    $_SESSION['id_societa'] = null;
    $_SESSION['stato_societa'] = null;

    header("location: ../HomeAmministratori.php");
    exit;
}

// 2) Login società
$stmtSocieta = $conn->prepare("SELECT * FROM societa WHERE email = ? AND psw = ? LIMIT 1");
$stmtSocieta->bind_param("ss", $email, $psw);
$stmtSocieta->execute();
$societa = $stmtSocieta->get_result()->fetch_assoc();

if ($societa === null) {
    $stmtSocieta = $conn->prepare("SELECT * FROM societa WHERE LOWER(email) = LOWER(?) AND psw = ? LIMIT 1");
    $stmtSocieta->bind_param("ss", $email, $psw);
    $stmtSocieta->execute();
    $societa = $stmtSocieta->get_result()->fetch_assoc();
}

if ($societa !== null) {
    $_SESSION['login'] = 'ok';
    $_SESSION['tipo'] = 'societa';
    $_SESSION['id'] = null;
    $_SESSION['id_societa'] = $societa['id'];
    $_SESSION['utente'] = $societa['email'];
    $_SESSION['nome_societa'] = $societa['nome_societa'];
    $_SESSION['stato_societa'] = $societa['stato'];
    $_SESSION['nome'] = $societa['nome_societa'];
    $_SESSION['cognome'] = '';
    $_SESSION['nome_utente'] = $societa['nome_societa'];

    header("location: ../HomeSocieta.php");
    exit;
}

// 3) Login utente cliente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE email = ? AND psw = ? LIMIT 1");
$stmt->bind_param("ss", $email, $psw);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row === null) {
    $stmt = $conn->prepare("SELECT * FROM utenti WHERE LOWER(email) = LOWER(?) AND psw = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $psw);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
}

if ($row !== null) {
    $_SESSION['login'] = 'ok';
    $_SESSION['tipo'] = 'cliente';
    $_SESSION['id'] = $row['id'];
    $_SESSION['nome'] = $row['nome'];
    $_SESSION['cognome'] = $row['cognome'];
    $_SESSION['utente'] = $row['email'];
    $_SESSION['nome_utente'] = $row['nome_utente'] ?? null;
    $_SESSION['id_societa'] = null;
    $_SESSION['stato_societa'] = null;

    header("location: ../HomeClienti.php");
    exit;
}

header("location: ../login.php?msg=loginerrato");
exit;
