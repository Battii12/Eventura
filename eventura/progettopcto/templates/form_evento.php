<?php
function datetimeLocalValue(?string $dbDate): string
{
    if ($dbDate === null || $dbDate === '') {
        return '';
    }
    return str_replace(' ', 'T', substr($dbDate, 0, 16));
}

$evento = $evento ?? [];
$categorie = ['concerto', 'sagra', 'mostra', 'sport', 'cultura', 'altro'];
$minDataInizio = date('Y-m-d\TH:i', strtotime('+1 minute'));
?>

<form action="<?php echo htmlspecialchars($formAction); ?>" method="POST">
    <?php if (!empty($eventoId)): ?>
        <input type="hidden" name="evento_id" value="<?php echo (int) $eventoId; ?>">
    <?php endif; ?>

    <div>
        <label for="titolo">Titolo</label>
        <input type="text" id="titolo" name="titolo" value="<?php echo htmlspecialchars($evento['titolo'] ?? ''); ?>" required>
    </div>

    <div>
        <label for="categoria">Categoria</label>
        <select id="categoria" name="categoria" required>
            <option value="">— Seleziona —</option>
            <?php foreach ($categorie as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (($evento['categoria'] ?? '') === $cat) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst($cat)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="localita_id">Località</label>
        <select id="localita_id" name="localita_id" required>
            <option value="">— Seleziona comune —</option>
            <?php foreach ($localitaList as $loc): ?>
                <option value="<?php echo (int) $loc['id']; ?>" <?php echo ((int)($evento['localita_id'] ?? 0) === (int)$loc['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($loc['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="indirizzo">Indirizzo</label>
        <input type="text" id="indirizzo" name="indirizzo" value="<?php echo htmlspecialchars($evento['indirizzo'] ?? ''); ?>" placeholder="Via, numero, CAP">
    </div>

    <div>
        <label for="descrizione">Descrizione</label>
        <textarea id="descrizione" name="descrizione" rows="4" placeholder="Descrivi l'evento"><?php echo htmlspecialchars($evento['descrizione'] ?? ''); ?></textarea>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div>
            <label for="data_inizio">Data e ora inizio</label>
            <input type="datetime-local" id="data_inizio" name="data_inizio" min="<?php echo $minDataInizio; ?>" value="<?php echo htmlspecialchars(datetimeLocalValue($evento['data_inizio'] ?? null)); ?>" required>
        </div>
        <div>
            <label for="data_fine">Data e ora fine</label>
            <input type="datetime-local" id="data_fine" name="data_fine" min="<?php echo $minDataInizio; ?>" value="<?php echo htmlspecialchars(datetimeLocalValue($evento['data_fine'] ?? null)); ?>">
        </div>
    </div>

    <div>
        <label for="max_partecipanti">Posti massimi (opzionale)</label>
        <input type="number" id="max_partecipanti" name="max_partecipanti" min="1" max="10000" step="1" value="<?php echo htmlspecialchars((string)($evento['max_partecipanti'] ?? '')); ?>">
        <p style="font-size:0.8rem; color:var(--muted); margin-top:6px;">Se indicato, deve essere almeno 1.</p>
    </div>

    <input type="submit" value="<?php echo htmlspecialchars($submitLabel ?? 'Salva'); ?>" style="margin-top:16px;">
</form>

<script>
(function () {
    const inizio = document.getElementById('data_inizio');
    const fine = document.getElementById('data_fine');
    if (!inizio || !fine) return;

    inizio.addEventListener('change', function () {
        if (inizio.value) {
            fine.min = inizio.value;
            if (fine.value && fine.value < inizio.value) {
                fine.value = inizio.value;
            }
        }
    });
})();
</script>
