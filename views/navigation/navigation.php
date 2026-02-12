<?php
require_once __DIR__ . '/../../config/config.php';
// require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/db.php';
session_start();
$user_name = $_SESSION['name'] ?? "User";
$cartCount = $_SESSION['cart_count'] ?? 0;


// Fetch categories from DB
$categories = [];
$res = $conn->query("SELECT id, name, parent_id FROM categories");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}



/**===================One More Times Clarification Is Remaining ==================**/
/*---------Build Tree ----------*/
function buildTree(array $items, $parentId = null){
    $branch = [];
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildTree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $branch[] = $item;
        }
    }
    return $branch;
}

$tree = buildTree($categories);

/*----------Render Tree as Dropdown Menu----------*/
function renderMenu($tree){
    echo "<ul class='dropdown'>";
    foreach ($tree as $node) {
        echo "<li>";
        echo "<a href='" . BASE_URL . "views/products/list.php?cat=" . (int)$node['id'] . "'>" . htmlspecialchars($node['name']) . "</a>";

        // print_r($node, true);
        // print("<br><br>");
        
        //debug end here

        if (!empty($node['children'])) {
            renderMenu($node['children']); // recursive call
        }

        echo "</li>";
    }
    echo "</ul>";   
}
/**===================One More Times Clarification Is Remaining For Above  Code==================**/


$_SESSION['usersflag'] = "";

/*---------------Cookies Banner Code---------------*/ 
if($_SERVER['REQUEST_METHOD'] === "POST"){
    //---> cookies use otherwise session
    if($_POST['submit'] == 1){
        $_SESSION['userflag'] = 'cookies';
    }
    else{
        $_SESSION['userflag'] = 'session';
    }


    print_r($_SESSION);
    exit;
}

?>


<!------- Html code write here ------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Page</title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>icon.png">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL ?>views/navigation/navigation.css">
</head>

<body>
    <div id="main">
        <!---------Navigation Bar---------->
        <nav>
            <div class="left">
                <h2>Dashboard</h2>
                <div class="category-menu">
                    <span>Categories ▾</span>
                    <!-- add tree function -->
                    <?= renderMenu($tree) ?>
                </div>
            </div>

            <div class="mid">
                <h2>Welcome :</h2>
                <p class="username"><?= htmlspecialchars($user_name) ?></p>
            </div>

            <!-- right nav bar -->
            <div class="right">
                <h3>
                    <a class="cart_func" href="<?= BASE_URL ?>views/products/cart.php">
                        <i class="ri-shopping-cart-2-line"></i>
                        Cart</a>
                </h3>
                <p class="cart_count"><?= $cartCount ?></p>

                <?php if (isset($_SESSION['id'])): ?>
                    <a href="<?= BASE_URL ?>auth/logout.php" id="logout_btn">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>auth/login.php" id="login_btn">Login</a>
                <?php endif; ?>

            </div>
        </nav>


        <!-- Cookies Box Start Here -->
        <div id="cookie-banner" class="cookie-banner hidden">
            <p>
                We use cookies to improve your experience. By using this site, you agree to our use of cookies.
            </p>
            <div class="cookie-actions">
               <form action="" method="POST">
                 <button name="submit" id="accept-cookies" value="1">Accept</button>
                 <button name="submit" id="reject-cookies" class="secondary" value="0">Reject</button>
               </form>
            </div>
        </div>
        <!-- Cookies Box Ended Here -->

    </div>

    <script src="script.js"></script>
</body>

</html>