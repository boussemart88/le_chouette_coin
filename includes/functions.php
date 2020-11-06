<?php
require 'includes/config.php';

function shorten_text($text, $max = 60, $append = '&hellip;')
{
    if (strlen($text) <= $max) {
        return $text;
    }
    $return = substr($text, 0, $max);
    if (false === strpos($text, ' ')) {
        return $return.$append;
    }

    return preg_replace('/\w+$/', '', $return).$append;
}

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

function affichageProduits()
{
    global $conn;
    $sth = $conn->prepare('SELECT p.*,c.categories_name,u.username FROM products AS p LEFT JOIN categories AS c ON p.category_id = c.categories_id LEFT JOIN users AS u ON p.user_id = u.id');
    $sth->execute();

    $products = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        ?>
<div class="card mx-2" style="width: 18rem;">
    <div class="card-body">
        <h5 class="card-title"><?php echo $product['products_name']; ?>
        </h5>
        <h6 class="card-subtitle mb-2 text-muted"><?php echo $product['city']; ?>
        </h6>
        <p class="card-text"><?php echo $product['description']; ?>
        </p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><?php echo $product['price']; ?>
                €</li>
            <li class="list-group-item"><?php echo $product['city']; ?>
            </li>
            <li class="list-group-item"><?php echo $product['categories_name']; ?>
            </li>
        </ul>
        <a href="product.php?id=<?php echo $product['products_id']; ?>"
            class="card-link btn btn-primary">Afficher article</a>
    </div>
</div>
<?php
    }
}

function affichageProduitsByUser($user_id)
{
    global $conn;
    $sth = $conn->prepare("SELECT p.*,c.categories_name FROM products AS p LEFT JOIN categories AS c ON p.category_id = c.categories_id WHERE p.user_id = {$user_id}");
    $sth->execute();

    $products = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        ?>
<tr>
    <th scope="row"><?php echo $product['products_id']; ?>
    </th>
    <td><?php echo $product['products_name']; ?>
    </td>
    <td><?php echo shorten_text($product['description']); ?>
    </td>
    <td><?php echo $product['price']; ?> €
    </td>
    <td><?php echo $product['city']; ?>
    </td>
    <td><?php echo $product['categories_name']; ?>
    </td>
    <td> <a href="product.php?id=<?php echo $product['products_id']; ?>"
            class="fa btn btn-outline-primary"><i class="fas fa-eye"></i></a>
    </td>
    <td> <a href="editproducts.php?id=<?php echo $product['products_id']; ?>"
            class="fa btn btn-outline-warning"><i class="fas fa-pen"></i></a>
    </td>
    <td>
        <form action="process.php" method="post">
            <input type="hidden" name="product_id"
                value="<?php echo $product['products_id']; ?>">
            <input type="submit" name="product_delete" class="fa btn btn-outline-danger" value="&#xf2ed;"></input>
        </form>
    </td>
</tr>
<?php
    }
}

function affichageProduit($id)
{
    global $conn;
    $sth = $conn->prepare("SELECT p.*,c.categories_name,u.username FROM products AS p LEFT JOIN categories AS c ON p.category_id = c.categories_id LEFT JOIN users AS u ON p.user_id = u.id WHERE p.products_id = {$id}");
    $sth->execute();

    $product = $sth->fetch(PDO::FETCH_ASSOC); ?>
<div class="row">
    <div class="col-12">
        <h1><?php echo $product['products_name']; ?>
        </h1>
        <p><?php echo $product['description']; ?>
        </p>
        <p><?php echo $product['city']; ?>
        </p>
        <button class="btn btn-danger"><?php echo $product['price']; ?> € </button>
    </div>
</div>
<?php
}

function ajoutProduits($name, $description, $price, $city, $category, $user_id)
{
    global $conn;
    // Vérification du prix (doit être un entier, et inférieur à 1 million d'euros)
    if (is_int($price) && $price > 0 && $price < 1000000) {
        // Utilisation du try/catch pour capturer les erreurs PDO/SQL
        try {
            // Création de la requête avec tous les champs du formulaire
            $sth = $conn->prepare('INSERT INTO products (products_name,description,price,city,category_id,user_id) VALUES (:products_name, :description, :price, :city, :category_id, :user_id)');
            $sth->bindValue(':products_name', $name, PDO::PARAM_STR);
            $sth->bindValue(':description', $description, PDO::PARAM_STR);
            $sth->bindValue(':price', $price, PDO::PARAM_INT);
            $sth->bindValue(':city', $city, PDO::PARAM_STR);
            $sth->bindValue(':category_id', $category, PDO::PARAM_INT);
            $sth->bindValue(':user_id', $user_id, PDO::PARAM_INT);

            // Affichage conditionnel du message de réussite
            if ($sth->execute()) {
                echo "<div class='alert alert-success'> Votre article a été ajouté à la base de données </div>";
                header('Location: product.php?id='.$conn->lastInsertId());
            }
        } catch (PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }
    }
}

function modifProduits($name, $description, $price, $city, $category, $id, $user_id)
{
    global $conn;
    if (is_int($price) && $price > 0 && $price < 1000000) {
        try {
            $sth = $conn->prepare('UPDATE products SET products_name=:products_name, description=:description, price=:price,city=:city, category_id=:category_id WHERE products_id=:products_id AND user_id=:user_id');
            $sth->bindValue(':products_name', $name);
            $sth->bindValue(':description', $description);
            $sth->bindValue(':price', $price);
            $sth->bindValue(':city', $city);
            $sth->bindValue(':category_id', $category);
            $sth->bindValue(':products_id', $id);
            $sth->bindValue(':user_id', $user_id);
            if ($sth->execute()) {
                echo "<div class='alert alert-success'> Votre modification a bien été prise en compte </div>";
                header("Location: product.php?id={$id}");
            }
        } catch (PDOException $e) {
            echo 'Error: '.$e->getMessage();
        }
    }
}

function modifPhone($user_id, $numDePhone)
{
    global $conn;

    try {
        $sth = $conn->prepare('UPDATE users SET phone=:phone WHERE id=:user_id');
        $sth->bindValue(':phone', $numDePhone);
        $sth->bindValue(':user_id', $user_id);
        if ($sth->execute()) {
            header('Location:profile.php?p');
        }
    } catch (PDOException $e) {
        echo 'Error: '.$e->getMessage();
    }
}

// Fonction de suppression des produits. Les arguments renseignés sont des placeholders étant donné qu'ils seront remplacés par les véritables variables une fois la fonction appelée;
function suppProduits($user_id, $produit_id)
{
    // Récupération de la connexion à la BDD à partir de l'espace global.
    global $conn;

    // Tentative de la requête de suppression.
    try {
        $sth = $conn->prepare('DELETE FROM products WHERE products_id = :products_id AND user_id =:user_id');
        $sth->bindValue(':products_id', $produit_id);
        $sth->bindValue(':user_id', $user_id);
        if ($sth->execute()) {
            header('Location:profile.php?s');
        }
    } catch (PDOException $e) {
        echo 'Error: '.$e->getMessage();
    }
}
