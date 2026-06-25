const EventuraMap = (function () {
    let map = null;
    let markersLayer = null;
    let allEvents = [];

    function parseDateOnly(value) {
        if (!value) return null;
        const s = String(value).substring(0, 10);
        const d = new Date(s + 'T00:00:00');
        return isNaN(d.getTime()) ? null : d;
    }

    function eventEndDate(event) {
        return parseDateOnly(event.data_fine) || parseDateOnly(event.data_inizio);
    }

    function eventStartDate(event) {
        return parseDateOnly(event.data_inizio);
    }

    function passesFilters(event, f) {
        if (f.citta && event.city !== f.citta) return false;

        if (f.titolo) {
            const q = f.titolo.toLowerCase();
            if (!(event.title || '').toLowerCase().includes(q)) return false;
        }

        if (f.categoria && event.categoria !== f.categoria) return false;
        if (f.societa && event.societa !== f.societa) return false;

        if (f.dataInizio) {
            const start = eventStartDate(event);
            const filterStart = parseDateOnly(f.dataInizio);
            if (!start || !filterStart || start < filterStart) return false;
        }

        if (f.dataFine) {
            const end = eventEndDate(event);
            const filterEnd = parseDateOnly(f.dataFine);
            if (!end || !filterEnd || end > filterEnd) return false;
        }

        return true;
    }

    function readFilters() {
        return {
            citta: document.getElementById('filtro-citta')?.value || '',
            titolo: document.getElementById('filtro-titolo')?.value.trim() || '',
            dataInizio: document.getElementById('filtro-data-inizio')?.value || '',
            dataFine: document.getElementById('filtro-data-fine')?.value || '',
            categoria: document.getElementById('filtro-categoria')?.value || '',
            societa: document.getElementById('filtro-societa')?.value || ''
        };
    }

    function buildPopup(event, tipo) {
        const inAttesa = event.stato === 'in_attesa' ? ' <em>(in attesa di approvazione)</em>' : '';
        const inAttesaAdmin = event.stato === 'in_attesa' ? ' <em>(in attesa)</em>' : '';
        const org = event.societa ? `<br>Organizzatore: ${event.societa}` : '';

        switch (tipo) {
            case 'cliente':
                return `<b>${event.title}</b><br>${event.city}<br><a href="./prenota.php?evento_id=${event.id}" class="btn btn-sm">Prenota</a>`;
            case 'societa': {
                const modifica = event.proprio
                    ? `<br><a href="./modificaEvento.php?u=${event.id}" class="btn btn-sm btn-outline">Modifica</a>`
                    : '';
                return `<b>${event.title}</b><br>${event.city}${inAttesa}${org}<br>Stato: ${event.stato}${modifica}`;
            }
            case 'admin':
                return `<b>${event.title}</b><br>${event.city}${inAttesaAdmin}${org}`;
            case 'pubblico':
            default:
                return `<b>${event.title}</b><br>${event.city}<br><a href="./login.php" class="btn btn-sm">Prenota</a>`;
        }
    }

    function renderMarkers(tipo) {
        if (!markersLayer) return;
        markersLayer.clearLayers();
        const f = readFilters();
        const filtered = allEvents.filter((e) => passesFilters(e, f));

        filtered.forEach((event) => {
            L.marker(event.coords)
                .addTo(markersLayer)
                .bindPopup(buildPopup(event, tipo));
        });
    }

    function bindFilterListeners(tipo) {
        const ids = ['filtro-citta', 'filtro-titolo', 'filtro-data-inizio', 'filtro-data-fine', 'filtro-categoria', 'filtro-societa'];
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => renderMarkers(tipo));
            el.addEventListener('change', () => renderMarkers(tipo));
        });

        const reset = document.getElementById('filtro-reset');
        if (reset) {
            reset.addEventListener('click', () => {
                ids.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                renderMarkers(tipo);
            });
        }
    }

    function init(config) {
        allEvents = config.events || [];
        const cities = config.cities || [];
        const tipo = config.popupTipo || 'pubblico';

        map = L.map('map').setView([44.40, 7.55], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const bounds = [[44.15, 7.20], [44.65, 7.95]];
        map.setMaxBounds(bounds);
        map.options.maxBoundsViscosity = 1.0;

        cities.forEach((city) => {
            L.circle(city.coords, {
                color: 'red',
                fillColor: '#ff6666',
                fillOpacity: 0.2,
                radius: 2000
            }).addTo(map);
        });

        markersLayer = L.layerGroup().addTo(map);
        renderMarkers(tipo);
        bindFilterListeners(tipo);
    }

    return { init };
})();
