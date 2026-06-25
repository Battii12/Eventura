<?php
session_start();
session_unset();
session_destroy();
include("./templates/header.php");
?>

<section>
    <div style="max-width:440px;margin:0 auto;">
        <div class="card">
            <form action="./PHP/loginCheck.php" method="POST">
                <p>Accedi</p>

                <div>
                    <label>Email</label>
                    <input type="text" id="email" name="email" placeholder="Inserisci la tua email" required>
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" id="psw" name="psw" placeholder="Inserisci Password" required>
                </div>

                <input type="submit" id="registra" value="Accedi">

                <p style="text-align:center; font-size:0.875rem; color:var(--muted); margin-top:4px;">
                    Non hai un account? <a href="./registra.php">Registrati</a>
                </p>
            </form>
        </div>

        <?php
        if (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'loginerrato') {
                echo '<div class="msg-error" style="margin-top:12px;">⚠ Utente o password errati. Riprova.</div>';
            }
            if ($_GET['msg'] === 'registrato') {
                echo '<div class="msg-success" style="margin-top:12px;">✓ Registrazione completata. Accedi con email e password.</div>';
            }
        }
        ?>
    </div>
</section>

<?php
include("./templates/footer.php")
?>