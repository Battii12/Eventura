<?php
session_start();
include("./templates/header.php");
?>

<section>
    <div style="max-width:560px; margin:0 auto;">
        <div class="page-title">
            <h1>Registra la tua società</h1>
            <div class="gold-line"></div>
            <p>Crea l’account della tua organizzazione per pubblicare eventi su EVENTURA</p>
        </div>

        <div class="card">
            <form action="./php/registraSocietaCheck.php" method="POST">
                <div>
                    <label>Nome società</label>
                    <input type="text" id="nome_societa" name="nome_societa" placeholder="Es. Pro Loco Cuneo" required>
                </div>

                <div>
                    <label>Partita IVA</label>
                    <input type="text" id="partita_iva" name="partita_iva" placeholder="Es. IT01234567890" required>
                </div>

                <div>
                    <label>Email</label>
                    <input type="email" id="email" name="email" placeholder="info@azienda.it" required>
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" id="psw" name="psw" placeholder="Minimo 8 caratteri" required>
                </div>

                <div>
                    <label>Telefono (opzionale)</label>
                    <input type="text" id="telefono" name="telefono" placeholder="Es. 0171 123456">
                </div>

                <div>
                    <label>Indirizzo (opzionale)</label>
                    <input type="text" id="indirizzo" name="indirizzo" placeholder="Es. Piazza Galimberti 1">
                </div>

                <div>
                    <label>Città (opzionale)</label>
                    <input type="text" id="citta" name="citta" placeholder="Es. Cuneo">
                </div>

                <div>
                    <label>Sito web (opzionale)</label>
                    <input type="text" id="sito_web" name="sito_web" placeholder="Es. https://www.azienda.it">
                </div>

                <input type="submit" value="Invia richiesta">

                <p style="text-align:center; font-size:0.875rem; color:var(--muted); margin-top:4px;">
                    Hai già un account? <a href="./login.php">Accedi</a>
                </p>
            </form>
        </div>

        <?php
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'emailesistente') {
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Email già registrata.</div>';
            }
            if ($_GET['msg'] === 'pivaesistente') {
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Partita IVA già registrata.</div>';
            }
            if ($_GET['msg'] === 'errore') {
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Errore durante la registrazione. Riprova.</div>';
            }
            if ($_GET['msg'] === 'ok') {
                echo '<div class="msg-success" style="margin-top:12px;">✓ Richiesta inviata! Attendi l’approvazione di un admin.</div>';
            }
        }
        ?>

        <div style="margin-top:16px; text-align:center;">
            <a href="./servizi-societa.php" class="btn btn-outline">Torna ai servizi</a>
        </div>
    </div>
</section>

<?php
include("./templates/footer.php");
?>

