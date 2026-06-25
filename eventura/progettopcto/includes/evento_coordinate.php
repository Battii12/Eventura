<?php

/**
 * Geocoding e posizionamento marker eventi sulla mappa.
 */

function distanzaMetri(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earth = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

    return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

function caricaCentroLocalita(mysqli $conn, int $localitaId): ?array
{
    $stmt = $conn->prepare('SELECT nome, provincia, latitudine, longitudine FROM localita WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $localitaId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row === null || $row['latitudine'] === null || $row['longitudine'] === null) {
        return null;
    }

    return [
        'nome'      => $row['nome'],
        'provincia' => $row['provincia'] ?? 'CN',
        'lat'       => (float) $row['latitudine'],
        'lng'       => (float) $row['longitudine'],
    ];
}

function geocodificaIndirizzoEvento(string $indirizzo, string $nomeComune, string $provincia = 'CN'): ?array
{
    $indirizzo = trim($indirizzo);
    if ($indirizzo === '') {
        return null;
    }

    $query = $indirizzo . ', ' . $nomeComune . ', ' . $provincia . ', Italia';
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q'              => $query,
        'format'         => 'json',
        'limit'          => 1,
        'countrycodes'   => 'it',
        'addressdetails' => 0,
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: EVENTURA/1.0 (eventi Cuneo; info@eventura.it)\r\nAccept-Language: it\r\n",
            'timeout' => 10,
        ],
    ]);

    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        return null;
    }

    $data = json_decode($body, true);
    if (!is_array($data) || count($data) === 0) {
        return null;
    }

    $lat = isset($data[0]['lat']) ? (float) $data[0]['lat'] : null;
    $lng = isset($data[0]['lon']) ? (float) $data[0]['lon'] : null;
    if ($lat === null || $lng === null) {
        return null;
    }

    return [$lat, $lng];
}

function coordinateVicineAlComune(float $lat, float $lng, float $centerLat, float $centerLng, float $maxMetri = 18000): bool
{
    return distanzaMetri($lat, $lng, $centerLat, $centerLng) <= $maxMetri;
}

/** @return list<array{0: float, 1: float}> */
function coordinateEventiNelComune(mysqli $conn, int $localitaId, ?int $excludeEventoId = null): array
{
    $sql = '
        SELECT latitudine, longitudine
        FROM eventi
        WHERE localita_id = ?
          AND latitudine IS NOT NULL
          AND longitudine IS NOT NULL
    ';
    if ($excludeEventoId !== null) {
        $sql .= ' AND id != ?';
    }

    $stmt = $conn->prepare($sql);
    if ($excludeEventoId !== null) {
        $stmt->bind_param('ii', $localitaId, $excludeEventoId);
    } else {
        $stmt->bind_param('i', $localitaId);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $punti = [];
    while ($row = $result->fetch_assoc()) {
        $punti[] = [(float) $row['latitudine'], (float) $row['longitudine']];
    }

    return $punti;
}

function troppoVicinoAdAltri(float $lat, float $lng, array $altriPunti, float $minMetri = 150): bool
{
    foreach ($altriPunti as $punto) {
        if (distanzaMetri($lat, $lng, $punto[0], $punto[1]) < $minMetri) {
            return true;
        }
    }

    return false;
}

function offsetSpiraleNelComune(float $centerLat, float $centerLng, int $indice): array
{
    $indice = max(1, $indice);
    $angolo = $indice * 2.399963229728653;
    $anello = 1 + (int) floor(sqrt($indice));
    $metri = 300 * $anello;

    $latOff = ($metri * cos($angolo)) / 111320.0;
    $lngOff = ($metri * sin($angolo)) / (111320.0 * cos(deg2rad($centerLat)));

    return [$centerLat + $latOff, $centerLng + $lngOff];
}

function coordinateSpostataNelComune(mysqli $conn, int $localitaId, float $centerLat, float $centerLng, ?int $excludeEventoId = null): array
{
    $occupate = coordinateEventiNelComune($conn, $localitaId, $excludeEventoId);

    $stmt = $conn->prepare('SELECT COUNT(*) AS tot FROM eventi WHERE localita_id = ?' . ($excludeEventoId !== null ? ' AND id != ?' : ''));
    if ($excludeEventoId !== null) {
        $stmt->bind_param('ii', $localitaId, $excludeEventoId);
    } else {
        $stmt->bind_param('i', $localitaId);
    }
    $stmt->execute();
    $countRow = $stmt->get_result()->fetch_assoc();
    $baseIndex = (int) ($countRow['tot'] ?? 0);

    for ($i = 0; $i < 60; $i++) {
        $coords = offsetSpiraleNelComune($centerLat, $centerLng, $baseIndex + $i);
        if (!troppoVicinoAdAltri($coords[0], $coords[1], $occupate, 140)) {
            return $coords;
        }
    }

    return offsetSpiraleNelComune($centerLat, $centerLng, $baseIndex + 60);
}

/**
 * Risolve lat/lng: geocoding indirizzo se possibile, altrimenti punto distinto nel comune.
 *
 * @return array{0: float, 1: float}|null
 */
function risolviCoordinateEvento(mysqli $conn, int $localitaId, string $indirizzo, ?int $excludeEventoId = null): ?array
{
    $centro = caricaCentroLocalita($conn, $localitaId);
    if ($centro === null) {
        return null;
    }

    $indirizzo = trim($indirizzo);
    if ($indirizzo !== '') {
        $geo = geocodificaIndirizzoEvento($indirizzo, $centro['nome'], $centro['provincia']);
        if ($geo !== null && coordinateVicineAlComune($geo[0], $geo[1], $centro['lat'], $centro['lng'])) {
            return $geo;
        }
    }

    return coordinateSpostataNelComune($conn, $localitaId, $centro['lat'], $centro['lng'], $excludeEventoId);
}

function salvaCoordinateEvento(mysqli $conn, int $eventoId, int $localitaId, string $indirizzo, ?int $excludeEventoId = null): void
{
    $coords = risolviCoordinateEvento($conn, $localitaId, $indirizzo, $excludeEventoId);
    if ($coords === null) {
        return;
    }

    $lat = $coords[0];
    $lng = $coords[1];
    $stmt = $conn->prepare('UPDATE eventi SET latitudine = ?, longitudine = ? WHERE id = ?');
    $stmt->bind_param('ddi', $lat, $lng, $eventoId);
    $stmt->execute();
}
