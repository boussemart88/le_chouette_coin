<?php
$title = 'Identification - Le Chouette Coin';
require 'includes/header.php';

if (!empty($_POST['email_signup']) && !empty($_POST['password1_signup']) && !empty($_POST['username_signup']) && isset($_POST['submit_signup'])) {
    $email = htmlspecialchars($_POST['email_signup']);
    $password1 = htmlspecialchars($_POST['password1_signup']);
    $password2 = htmlspecialchars($_POST['password2_signup']);
    $username = htmlspecialchars($_POST['username_signup']);

    inscription($email, $username, $password1, $password2);
} elseif (!empty($_POST['email_login']) && !empty($_POST['password_login']) && isset($_POST['submit_login'])) {
    $email = strip_tags($_POST['email_login']);
    $password = strip_tags($_POST['password_login']);

    connexion($email, $password);
} else {
    if (isset($_POST)) {
        unset($_POST);
    }
}
?>
<div class="row">
    <div class="col-6">
        <h3>S'inscrire</h3>
        <form
            action="<?php $_SERVER['REQUEST_URI']; ?>"
            method="POST">
            <div class="form-group">
                <label for="InputEmail1">Adresse mail</label>
                <input type="email" class="form-control" id="InputEmail1" aria-describedby="emailHelp"
                    name="email_signup" required>
                <small id="emailHelp" class="form-text text-muted">Nous ne partagerons jamais votre email avec qui que
                    ce soit.</small>
            </div>
            <div class="form-group">
                <label for="InputUsername1">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="InputUsername1" aria-describedby="userHelp"
                    name="username_signup" required>
                <small id="userHelp" class="form-text text-muted">Choisissez un nom d'utilisateur, il doit être unique
                    !</small>
            </div>
            <div class="form-group">
                <label for="InputPassword1">Choisissez un mot de passe</label>
                <input type="password" class="form-control" id="InputPassword1" name="password1_signup" required>
            </div>
            <div class="form-group">
                <label for="InputPassword2">Entrez votre mot de passe de nouveau</label>
                <input type="password" class="form-control" id="InputPassword2" name="password2_signup" required>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="Check1" required>
                <label class="form-check-label" for="Check1">Accepter les <a href="#">termes et conditions</a></label>
            </div>
            <button type="submit" class="btn btn-primary" name="submit_signup" value="inscription">S'inscrire</button>
        </form>
    </div>
    <div class="col-6">
        <h3>Se connecter</h3>
        <form
            action="<?php $_SERVER['REQUEST_URI']; ?>"
            method="POST">
            <div class="form-group">
                <label for="InputEmail2">Adresse e-mail</label>
                <input type="email" class="form-control" id="InputEmail2" name="email_login" required>
            </div>
            <div class="form-group">
                <label for="InputPassword">Mot de passe</label>
                <input type="password" class="form-control" id="InputPassword" name="password_login" required>
            </div>
            <button type="submit" class="btn btn-success" name="submit_login" value="connexion">Se connecter</button>
        </form>
    </div>
</div>


<?php
require 'includes/footer.php';
