<?php

/**
 * Evento ancora valido (non concluso): fine evento o, se assente, inizio >= adesso.
 */
function eventoNonPassato(array $row): bool
{
    $fine = $row['data_fine'] ?? null;
    $riferimento = ($fine !== null && $fine !== '') ? $fine : ($row['data_inizio'] ?? '');
    if ($riferimento === '') {
        return false;
    }
    $ts = strtotime($riferimento);
    return $ts !== false && $ts >= time();
}

/**
 * @param string $ruolo pubblico|cliente|societa|amministratore
 */
function eventoVisibileInMappa(array $row, string $ruolo, ?int $societaId = null): bool
{
    if (!eventoNonPassato($row)) {
        return false;
    }

    $stato = $row['stato'] ?? '';

    if ($stato === 'approvato') {
        return true;
    }

    if ($stato === 'in_attesa') {
        if ($ruolo === 'amministratore') {
            return true;
        }
        if ($ruolo === 'societa' && $societaId !== null && (int) ($row['societa_id'] ?? 0) === $societaId) {
            return true;
        }
        return false;
    }

    return false;
}

function rowToMapEvent(array $row, array $extra = []): ?array
{
    $lat = $row['latitudine'] ?? null;
    $lng = $row['longitudine'] ?? null;
    if ($lat === null || $lng === null) {
        $lat = $row['loc_lat'] ?? null;
        $lng = $row['loc_lng'] ?? null;
    }
    if ($lat === null || $lng === null) {
        return null;
    }

    return array_merge([
        'id'          => (int) ($row['id'] ?? 0),
        'title'       => $row['titolo'] ?? '',
        'city'        => $row['localita_nome'] ?? '',
        'categoria'   => $row['categoria'] ?? '',
        'data_inizio' => $row['data_inizio'] ?? '',
        'data_fine'   => $row['data_fine'] ?? '',
        'societa'     => $row['nome_societa'] ?? '',
        'stato'       => $row['stato'] ?? '',
        'coords'      => [(float) $lat, (float) $lng],
    ], $extra);
}

/**
 * @param string $ruolo pubblico|cliente|societa|amministratore
 */
function buildMapEvents(array $rows, string $ruolo, ?int $societaId = null, array $extraPerRow = []): array
{
    $mapEvents = [];
    foreach ($rows as $row) {
        if (!eventoVisibileInMappa($row, $ruolo, $societaId)) {
            continue;
        }
        $extra = $extraPerRow;
        if ($ruolo === 'societa' && $societaId !== null) {
            $extra['proprio'] = (int) ($row['societa_id'] ?? 0) === $societaId;
        }
        $event = rowToMapEvent($row, $extra);
        if ($event !== null) {
            $mapEvents[] = $event;
        }
    }
    return $mapEvents;
}

/** WHERE per mappa area società: tutti gli approvati futuri + propri in attesa */
function sqlEventiMappaSocieta(): string
{
    $nonPassati = sqlEventiNonPassati();
    return "(
        (e.stato = 'approvato' AND {$nonPassati})
        OR (e.stato = 'in_attesa' AND e.societa_id = ? AND {$nonPassati})
    )";
}

function caricaMapCities(mysqli $conn): array
{
    $mapCities = [];
    $locResult = $conn->query("
        SELECT nome, latitudine, longitudine
        FROM localita
        WHERE latitudine IS NOT NULL AND longitudine IS NOT NULL
    ");
    if ($locResult) {
        while ($loc = $locResult->fetch_assoc()) {
            $mapCities[] = [
                'name'   => $loc['nome'],
                'coords' => [(float) $loc['latitudine'], (float) $loc['longitudine']],
            ];
        }
    }
    return $mapCities;
}

/** Categorie ammesse in creazione/modifica evento */
function categorieEventoValide(): array
{
    return ['concerto', 'sagra', 'mostra', 'sport', 'cultura', 'altro'];
}

/** Opzioni complete per i menu a tendina (da database) */
function caricaOpzioniFiltriMappa(mysqli $conn): array
{
    $citta = [];
    $res = $conn->query('SELECT nome FROM localita ORDER BY nome ASC');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $nome = trim($row['nome'] ?? '');
            if ($nome !== '') {
                $citta[] = $nome;
            }
        }
    }

    $societa = [];
    $res = $conn->query('SELECT nome_societa FROM societa ORDER BY nome_societa ASC');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $nome = trim($row['nome_societa'] ?? '');
            if ($nome !== '') {
                $societa[] = $nome;
            }
        }
    }

    $categorie = categorieEventoValide();
    $res = $conn->query("
        SELECT DISTINCT categoria
        FROM eventi
        WHERE categoria IS NOT NULL AND TRIM(categoria) != ''
        ORDER BY categoria ASC
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $cat = trim($row['categoria'] ?? '');
            if ($cat !== '' && !in_array($cat, $categorie, true)) {
                $categorie[] = $cat;
            }
        }
    }

    return [
        'citta'     => $citta,
        'categorie' => $categorie,
        'societa'   => $societa,
    ];
}

/** Fallback: opzioni ricavate solo dagli eventi già in mappa */
function estraiOpzioniFiltriMappa(array $mapEvents): array
{
    $citta = [];
    $categorie = [];
    $societa = [];

    foreach ($mapEvents as $event) {
        if (!empty($event['city'])) {
            $citta[$event['city']] = true;
        }
        if (!empty($event['categoria'])) {
            $categorie[$event['categoria']] = true;
        }
        if (!empty($event['societa'])) {
            $societa[$event['societa']] = true;
        }
    }

    $cittaKeys = array_keys($citta);
    $catKeys = array_keys($categorie);
    $socKeys = array_keys($societa);
    sort($cittaKeys, SORT_NATURAL | SORT_FLAG_CASE);
    sort($catKeys, SORT_NATURAL | SORT_FLAG_CASE);
    sort($socKeys, SORT_NATURAL | SORT_FLAG_CASE);

    return [
        'citta'      => $cittaKeys,
        'categorie'  => $catKeys,
        'societa'    => $socKeys,
    ];
}

/** Condizione SQL: solo eventi non ancora conclusi */
function sqlEventiNonPassati(): string
{
    return "(
        (e.data_fine IS NOT NULL AND e.data_fine >= NOW())
        OR (e.data_fine IS NULL AND e.data_inizio >= NOW())
    )";
}
