<?php
session_start();
$leaflet = true;
include("./templates/header_riservata.php");
include("./conf/db_config.php");
require_once("./includes/eventi_mappa.php");

$stmt = $conn->prepare("
    SELECT e.*,
           l.nome AS localita_nome,
           l.latitudine AS loc_lat,
           l.longitudine AS loc_lng,
           s.nome_societa
    FROM eventi e
    LEFT JOIN localita l ON e.localita_id = l.id
    LEFT JOIN societa s ON e.societa_id = s.id
    WHERE e.stato = 'approvato'
      AND " . sqlEventiNonPassati() . "
    ORDER BY e.data_inizio ASC
");
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

$mapEvents = buildMapEvents($rows, 'cliente');
$mapCities = caricaMapCities($conn);
?>

<section>
    <h1>Benvenuto, <?php echo htmlspecialchars($_SESSION['nome'] . " " . $_SESSION['cognome']); ?></h1>
    <p>Gli eventi più belli di Cuneo e dintorni</p>
    <p>Scopri sagre, concerti, mostre e molto altro.</p>
</section>

<div class="container">
<?php
$mappaPopupTipo = 'cliente';
include './templates/mappa_eventi.php';
?>
</div>

<?php
if ($rows != NULL) {
    echo "<div class=\"events-block\">
        <h2>Eventi in voga</h2>
        <div class=\"table-wrap\">
        <table class=\"table\">
        <thead><tr>
        <th>Titolo</th>
        <th>Categoria</th>
        <th>Località</th>
        <th>Indirizzo</th>
        <th>Inizio</th>
        <th>Fine</th>
        <th>Organizzatore</th>
        <th class=\"table-actions\"></th>
        </tr></thead><tbody>";

    foreach ($rows as $row) {
        $eventoId = (int) $row['id'];
        echo "<tr>
            <td>" . htmlspecialchars($row['titolo']) . "</td>
            <td>" . htmlspecialchars($row['categoria']) . "</td>
            <td>" . htmlspecialchars($row['localita_nome']) . "</td>
            <td>" . htmlspecialchars($row['indirizzo']) . "</td>
            <td>" . htmlspecialchars($row['data_inizio']) . "</td>
            <td>" . htmlspecialchars($row['data_fine']) . "</td>
            <td>" . htmlspecialchars($row['nome_societa']) . "</td>
            <td class=\"table-actions\"><a href=\"./prenota.php?evento_id={$eventoId}\" class=\"btn btn-sm\">Prenota</a></td>
            </tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div><h3>Nessun evento in voga al momento</h3></div>";
}
?>

<?php
include_once("./templates/footer_riservata.php");
?>
