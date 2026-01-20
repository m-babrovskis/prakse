<div id="shopContainer" class="shopContainer">
    <div class="shopUI">
        <div class="category-board-container" id="category-board-container">
            <div class="category-text">Welcome to the Farmer's Shop!
                From seeds to powerful tools and tractors, everything here helps you work smarter and grow more. 
                Better equipment means faster work and less energy spent.</div>

            <!--
                <div id="seedsCategory" class="category-board" data-categoryId="seedsCategory">
                    <div class="category-image"></div>
                    <div class="category-name">Seeds</div>
                    <div class="category-information">Choose the right seeds for your farm! 
                        Seeds come in different rarities, and higher rarity seeds produce greater yields, 
                        giving you more harvest per plant. The purchase price of seeds  
                        determines how much you can sell the harvest for.</div>
                    <div class="browse-btn" id="seedButton"></div>
                </div>

                <div id="plowCategory" class="category-board" data-categoryId="plowCategory">
                    <div class="category-image"></div>
                    <div class="category-name">Plows</div>
                    <div class="category-information">Plows are used to break the soil and create plowed fields. 
                        This step can be done at any time and is required before any further field work. 
                        Better plows reduce the energy and time needed to prepare the land.</div>
                    <div class="browse-btn" id="plowButton"></div>
                </div>
            -->
            <?php
                $db = new database();
                $categories = $db->getArray("SELECT * FROM shopCategory ORDER BY id");
                
                foreach($categories as $category) {
                    $name = $category['name'];
                    $description = $category['description'];
                    $categoryId = strtolower($name) . 'Category';
                    $buttonId = strtolower($name) . 'Button';

                    echo '<div id="' . $categoryId . '" class="category-board" data-categoryId="' . $categoryId . '">';
                    echo '<div class="category-image"></div>';
                    echo '<div class="category-name">' . htmlspecialchars($name) . '</div>';
                    echo '<div class="category-information">' . htmlspecialchars($description) . '</div>';
                    echo '<div class="browse-btn" id="' . $buttonId . '"></div>';
                    echo '</div>';
                }
            ?>

        </div>
        
        <div class="shop-table-container" id="shop-table-container">
            <div class="shop-item-container">
                <div class="SeedShop shopBox" id="ShopItemContainer"> 
                    
                </div>
            </div>

            <div class="closeItems", id="closeItems"></div>
        </div>
    </div>

    <div class="shop-title"></div>

    <div class="shop-table"></div>

    <div id="closeShopButton" class="closeShopButton"></div>
</div>