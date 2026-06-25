<?php
session_start();
include("./templates/header.php");
?>

<section>
    <div style="max-width:520px; margin:0 auto;">
        <div class="page-title">
            <h1>Crea un account</h1>
            <div class="gold-line"></div>
            <p>Unisciti a noi per scoprire e gestire eventi</p>
        </div>

        <div class="card">
            <form action="./php/registraCheck.php" method="POST">

                <!-- Nome e Cognome affiancati -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label>Nome</label>
                        <input type="text" id="nome" name="nome" placeholder="Mario" required>
                    </div>
                    <div>
                        <label>Cognome</label>
                        <input type="text" id="cognome" name="cognome" placeholder="Rossi" required>
                    </div>
                </div>

                <div>
                    <label>Email</label>
                    <input type="email" id="email" name="email" placeholder="mario.rossi@email.it" required>
                </div>

                <div>
                    <label>Nome utente</label>
                    <input type="text" id="nome_utente" name="nome_utente" placeholder="Es. mario.rossi" required>
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" id="psw" name="psw" placeholder="Minimo 8 caratteri" required>
                </div>

                <div>
                    <label>Telefono</label>
                    <input type="text" id="telefono" name="telefono" placeholder="Es. 333 1234567" required>
                </div>

                <div>
                    <label>Data di nascita</label>
                    <input type="date" id="data_nascita" name="data_nascita" required>
                </div>

                <input type="submit" value="Registrati">

                <p style="text-align:center; font-size:0.875rem; color:var(--muted); margin-top:4px;">
                    Hai già un account? <a href="index.php">Accedi</a>
                </p>

            </form>
        </div>

        <?php
        if(isset($_GET['msg'])){
            if($_GET['msg'] == 'emailesistente'){
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Email già registrata. Prova ad accedere.</div>';
            }
            if($_GET['msg'] == 'nomeutentesistente'){
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Nome utente già in uso. Scegline un altro.</div>';
            }
            if($_GET['msg'] == 'errore'){
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Errore durante la registrazione. Riprova.</div>';
            }
            if($_GET['msg'] == 'ok'){
                echo '<div class="msg-success" style="margin-top:12px;">✓ Registrazione completata! Ora puoi accedere.</div>';
            }
        }
        ?>
    </div>
</section>

<?php
include("./templates/footer.php");
?>