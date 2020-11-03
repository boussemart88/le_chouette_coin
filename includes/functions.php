<?php
require 'includes/config.php';

function inscription($email, $username, $password1, $password2)
{
    global $conn;

    try {
        $sql1 = "SELECT * FROM users WHERE email = '{$email}'";
        $sql2 = "SELECT * FROM users WHERE username = '{$username}'";
        $res1 = $conn->query($sql1);
        $count_email = $res1->fetchColumn();
        if (!$count_email) {
            $res2 = $conn->query($sql2);
            $count_user = $res2->fetchColumn();
            if (!$count_user) {
                if ($password1 === $password2) {
                    $password1 = password_hash($password1, PASSWORD_DEFAULT);
                    $sth = $conn->prepare('INSERT INTO users (email,username, password) VALUES (:email,:username,:password)');
                    $sth->bindValue(':email', $email);
                    $sth->bindValue(':username', $username);
                    $sth->bindValue(':password', $password1);
                    $sth->execute();
                    echo "<div class='alert alert-success mt-2'> L'utilisateur a bien été enregistré, vous pouvez désormais vous connecter</div>";
                } else {
                    echo 'Les mots de passe ne concordent pas !';
                    unset($_POST);
                }
            } elseif ($count_user > 0) {
                echo "Ce nom d'utilisateur est déja pris !";
                unset($_POST);
            }
        } elseif ($count_email > 0) {
            echo 'Cette adresse mail existe déja !';
            unset($_POST);
        }
    } catch (PDOException $e) {
        echo 'Error: '.$e->getMessage();
    }
}

function connexion($email, $password)
{
    global $conn;

    try {
        $sql = "SELECT * FROM users WHERE email = '{$email}'";
        $res = $conn->query($sql);
        $user = $res->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $db_password = $user['password'];
            if (password_verify($password, $db_password)) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];

                echo 'Vous êtes désormais connectés !';
                header('Location: index.php');
            } else {
                echo 'Le mot de passe est erroné !';
                unset($_POST);
            }
        } else {
            echo "L'email utilisé n'est pas connu !";
            unset($_POST);
        }
    } catch (PDOException $e) {
        echo 'Error: '.$e->getMessage();
    }
}

function affichage()
{
    global $conn;
    $sth = $conn->prepare('SELECT * FROM users');
    $sth->execute();

    $users = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        ?>
<tr>
    <th scope="row"><?php echo $user['id']; ?>
    </th>
    <td><?php echo $user['email']; ?>
    </td>
    <td><?php echo $user['username']; ?>
    </td>
    <td><?php echo $user['password']; ?>
    </td>
</tr>
<?php
    }
}
