<?php

function normalizzaDateTimeEvento(string $input): string
{
    $normalized = str_replace('T', ' ', trim($input));
    if (strlen(trim($input)) === 16) {
        $normalized .= ':00';
    }
    return $normalized;
}

/**
 * @return string|null Codice errore oppure null se valido
 */
function validaDatiEvento(string $dataInizio, string $dataFine, string $maxPartecipanti): ?string
{
    if ($dataInizio === '') {
        return 'errore';
    }

    $dataInizioDb = normalizzaDateTimeEvento($dataInizio);
    $inizioTs = strtotime($dataInizioDb);
    if ($inizioTs === false) {
        return 'errore';
    }

    if ($inizioTs <= time()) {
        return 'data_passata';
    }

    if ($maxPartecipanti !== '') {
        if (!ctype_digit($maxPartecipanti) && !preg_match('/^\d+$/', $maxPartecipanti)) {
            return 'posti_invalidi';
        }
        $posti = (int) $maxPartecipanti;
        if ($posti < 1) {
            return 'posti_invalidi';
        }
    }

    if ($dataFine !== '') {
        $dataFineDb = normalizzaDateTimeEvento($dataFine);
        $fineTs = strtotime($dataFineDb);
        if ($fineTs === false) {
            return 'errore';
        }
        if ($fineTs < $inizioTs) {
            return 'data_fine_invalida';
        }
    }

    return null;
}

function messaggioErroreEvento(string $codice): string
{
    return match ($codice) {
        'data_passata' => 'La data di inizio deve essere successiva a oggi.',
        'data_fine_invalida' => 'La data di fine non può essere precedente alla data di inizio.',
        'posti_invalidi' => 'I posti massimi devono essere un numero positivo (almeno 1).',
        default => 'Compila tutti i campi obbligatori correttamente e riprova.',
    };
}
