<?php

$title = 'Produits - Le Chouette Coin';
require 'includes/header.php';

$sql = 'SELECT * FROM categories';
$res = $conn->query($sql);
$categories = $res->fetchAll();

if (isset($_POST['search_form'])) {
    $category = intval(strip_tags($_POST['product_category']));
    $search_text = strip_tags($_POST['search_text']);

    $sql2 = "SELECT * FROM products WHERE category_id LIKE '%{$category}%' AND products_name LIKE '%{$search_text}%'";
    $res2 = $conn->query($sql2);
    $search = $res2->fetchAll();
}
?>

<form action="products.php" method="post">
    <div class="form-inline">
        <div class="form-group">
            <!-- <label for="InputCategory">Rechercher par nom</label> -->
            <input type="text" name="search_text" id="InputText" placeholder="Rechercher par nom"
                class="form-control mb-2 mx-2">
        </div>
        <div class="form-group">
            <select class="form-control mb-2 mx-2" id="InputCategory" name="product_category">
                <option value="" selected> -- Filtrer par catégorie -- </option>
                <?php foreach ($categories as $category) { ?>
                <option
                    value="<?php echo $category['categories_id']; ?>">
                    <?php echo $category['categories_name']; ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <input type="submit" value="Recherche" name="search_form" class=" mb-2 btn btn-info">
        <?php if (isset($search)) {
    echo '<a href="products.php" class="btn btn-danger mx-2 mb-2">Reset</a>';
} ?>
    </div>
</form>

<div class="row">
    <?php
        if (isset($search)) {
            foreach ($search as $product) {?>
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
            </ul>
            <a href="product.php?id=<?php echo $product['products_id']; ?>"
                class="card-link btn btn-primary">Afficher article</a>
        </div>
    </div>
    <?php
            }
        } else {
            affichageProduits();
        }
        ?>
</div>
<?php
require 'includes/footer.php';
