<?php
include("./templates/header.php");
?>

<div class="container">
    <div class="lp">
        <h2 class="sr-only">Servizi per le organizzazioni — EVENTURA</h2>

        <div class="lp-hero">
            <div class="lp-hero-eyebrow">Per le società e le associazioni</div>
            <h1>Porta i tuoi eventi<br>a tutto il Cuneese</h1>
            <p>Raggiungi migliaia di persone nella tua zona con un solo clic. Gratis, semplice, locale.</p>
            <div class="lp-hero-actions">
                <a class="btn" href="./registratiSocieta.php">Registra la tua organizzazione</a>
                <a class="btn btn-outline" href="#come-funziona">Scopri come funziona</a>
            </div>
        </div>

        <div class="lp-section">
            <div class="lp-section-label">Perché sceglierci</div>
            <div class="lp-benefits-grid">
                <div class="lp-benefit-card card">
                    <div class="lp-benefit-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path d="M12 22s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/></svg>
                    </div>
                    <h3>Visibilità locale</h3>
                    <p>I tuoi eventi vengono visti da chi è davvero nella tua zona, non da un pubblico generico.</p>
                </div>
                <div class="lp-benefit-card card">
                    <div class="lp-benefit-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></svg>
                    </div>
                    <h3>Pubblicazione rapida</h3>
                    <p>Inserisci un evento in pochi minuti. Niente burocrazia, niente tecnicismi.</p>
                </div>
                <div class="lp-benefit-card card">
                    <div class="lp-benefit-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9a3 3 0 0 1 3-3h14v14a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9z"/><path d="M6 6V3h12v3M9 13h6M9 17h4"/></svg>
                    </div>
                    <h3>Gestione biglietti</h3>
                    <p>Vendi o distribuisci biglietti direttamente dalla piattaforma, tutto in un posto.</p>
                </div>
            </div>
        </div>

        <div class="lp-section" id="come-funziona">
            <div class="lp-section-label">Come funziona</div>
            <div class="lp-steps">
                <div class="lp-step">
                    <div class="lp-step-num">1</div>
                    <div class="lp-step-content">
                        <h3>Crea il profilo della tua organizzazione</h3>
                        <p>Registrazione gratuita in meno di 5 minuti. Ti chiediamo solo i dati essenziali.</p>
                    </div>
                </div>
                <div class="lp-step">
                    <div class="lp-step-num">2</div>
                    <div class="lp-step-content">
                        <h3>Inserisci il tuo evento</h3>
                        <p>Compila la scheda con data, luogo, descrizione e foto. Invialo per l'approvazione.</p>
                    </div>
                </div>
                <div class="lp-step">
                    <div class="lp-step-num">3</div>
                    <div class="lp-step-content">
                        <h3>Raggiungi il tuo pubblico</h3>
                        <p>Una volta approvato, il tuo evento è visibile a tutti gli utenti della piattaforma.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lp-section">
            <div class="lp-section-label">La piattaforma in numeri</div>
            <div class="lp-stats-grid">
                <div class="lp-stat-card card">
                    <div class="lp-stat-num">12+</div>
                    <div class="lp-stat-label">Comuni coperti</div>
                </div>
                <div class="lp-stat-card card">
                    <div class="lp-stat-num">80+</div>
                    <div class="lp-stat-label">Organizzazioni iscritte</div>
                </div>
                <div class="lp-stat-card card">
                    <div class="lp-stat-num">2.000+</div>
                    <div class="lp-stat-label">Utenti attivi</div>
                </div>
            </div>
        </div>

        <div class="lp-cta card">
            <h2>Pronto a far conoscere i tuoi eventi?</h2>
            <p>Unisciti alle associazioni e società del Cuneese che usano già la piattaforma.</p>
            <a class="btn" href="./registratiSocieta.php">Inizia ora — è gratis</a>
            <p class="lp-cta-back"><a href="./index.php">← Torna alla home eventi</a></p>
        </div>
    </div>
</div>

<?php
include_once("./templates/footer.php");
?>
