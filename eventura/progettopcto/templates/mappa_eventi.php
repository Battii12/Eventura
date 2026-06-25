<?php
/**
 * Blocco mappa con filtri.
 * Variabili richieste: $mapEvents, $mapCities, $mappaPopupTipo (pubblico|cliente|societa|admin)
 * Opzionali: $mappaTitolo, $mappaSottotitolo
 */
if (!isset($filtriMappa)) {
    if (isset($conn) && $conn instanceof mysqli) {
        $filtriMappa = caricaOpzioniFiltriMappa($conn);
    } else {
        $filtriMappa = estraiOpzioniFiltriMappa($mapEvents ?? []);
    }
}
$mappaPopupTipo = $mappaPopupTipo ?? 'pubblico';
?>
<?php if (!empty($mappaTitolo)): ?>
    <h2><?php echo htmlspecialchars($mappaTitolo); ?></h2>
<?php endif; ?>
<?php if (!empty($mappaSottotitolo)): ?>
    <p style="color:var(--muted); margin-bottom:12px;"><?php echo htmlspecialchars($mappaSottotitolo); ?></p>
<?php endif; ?>

<?php include __DIR__ . '/mappa_filtri.php'; ?>

<div id="map"></div>

<script src="./js/mappa-eventi.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    EventuraMap.init({
        events: <?php echo json_encode($mapEvents ?? [], JSON_UNESCAPED_UNICODE); ?>,
        cities: <?php echo json_encode($mapCities ?? [], JSON_UNESCAPED_UNICODE); ?>,
        popupTipo: <?php echo json_encode($mappaPopupTipo, JSON_UNESCAPED_UNICODE); ?>
    });
});
</script>
