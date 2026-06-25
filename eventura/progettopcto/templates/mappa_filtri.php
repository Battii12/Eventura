<?php
$filtriMappa = $filtriMappa ?? ['citta' => [], 'categorie' => [], 'societa' => []];
?>
<div class="mappa-filtri card">
    <h3 class="mappa-filtri-titolo">Filtra eventi sulla mappa</h3>
    <div class="mappa-filtri-grid">
        <div>
            <label for="filtro-citta">Città</label>
            <select id="filtro-citta">
                <option value="">Tutte</option>
                <?php foreach ($filtriMappa['citta'] as $citta): ?>
                    <option value="<?php echo htmlspecialchars($citta); ?>"><?php echo htmlspecialchars($citta); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filtro-titolo">Nome evento</label>
            <input type="text" id="filtro-titolo" placeholder="Cerca per titolo…">
        </div>
        <div>
            <label for="filtro-data-inizio">Data inizio da</label>
            <input type="date" id="filtro-data-inizio">
        </div>
        <div>
            <label for="filtro-data-fine">Data fine entro</label>
            <input type="date" id="filtro-data-fine">
        </div>
        <div>
            <label for="filtro-categoria">Categoria</label>
            <select id="filtro-categoria">
                <option value="">Tutte</option>
                <?php foreach ($filtriMappa['categorie'] as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars(ucfirst($cat)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="filtro-societa">Società</label>
            <select id="filtro-societa">
                <option value="">Tutte</option>
                <?php foreach ($filtriMappa['societa'] as $soc): ?>
                    <option value="<?php echo htmlspecialchars($soc); ?>"><?php echo htmlspecialchars($soc); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="button" id="filtro-reset" class="btn btn-outline mappa-filtri-reset">Azzera filtri</button>
</div>
