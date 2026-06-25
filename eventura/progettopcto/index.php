<?php
$leaflet = true;
include("./templates/header.php");
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

$mapEvents = buildMapEvents($rows, 'pubblico');
$mapCities = caricaMapCities($conn);
?>

<section>
    <?php
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] == 'loginerrato') {
            echo '<h3>Utente/password errati</h3>';
        }
        if ($_GET['msg'] == 'sessionescaduta') {
            echo '<h3>Sessione scaduta, effettua di nuovo il login</h3>';
        }
    }
    ?>
    <h1>Gli eventi più belli di Cuneo e dintorni</h1>
    <p>Scopri sagre, concerti, mostre e molto altro.</p>
</section>

<div class="container">
<?php
$mappaPopupTipo = 'pubblico';
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
        echo "<tr>
            <td>" . htmlspecialchars($row['titolo']) . "</td>
            <td>" . htmlspecialchars($row['categoria']) . "</td>
            <td>" . htmlspecialchars($row['localita_nome']) . "</td>
            <td>" . htmlspecialchars($row['indirizzo']) . "</td>
            <td>" . htmlspecialchars($row['data_inizio']) . "</td>
            <td>" . htmlspecialchars($row['data_fine']) . "</td>
            <td>" . htmlspecialchars($row['nome_societa']) . "</td>
            <td class=\"table-actions\"><a href=\"./login.php\" class=\"btn btn-sm\">Prenota</a></td>
            </tr>";
    }
    echo "</tbody></table></div></div>";
} else {
    echo "<div><h3>Nessun evento in voga al momento</h3></div>";
}
?>

<?php
include_once("./templates/footer.php");
?>
