<?php
    // Use the items and categoryId passed from controller
    $categoryId = $categoryId ?? 1; // Default to 1 if not set
    
    foreach($items as $item) {
        $name = $item['name'];
        $price = $item['price'];
        
        echo '<div class="shop-item">';
        echo '<div class="category-image"></div>';
        echo '<div class="category-name">' . htmlspecialchars($name) . '</div>';
        
        // Different information display based on category
        if ($categoryId == 1) { // Seeds
            $minYield = $item['minYield'];
            $maxYield = $item['maxYield'];
            $growthTime = $item['growthTime'];
            $sellingPrice = $item['sellingPrice'];
            echo '<div class="category-information">Price: $' . $price . '<br>Yield: ' . $minYield . '-' . $maxYield . '<br>Growth: ' . $growthTime . 's<br>Sell: $' . $sellingPrice . '</div>';
        } else { // Tools (Plows, Cultivators, Tractors)
            $timeUse = $item['timeUse'];
            $energyUse = $item['energyUse'];
            echo '<div class="category-information">Price: $' . $price . '<br>Time: ' . $timeUse . 's<br>Energy: ' . $energyUse . '</div>';
        }
        
        echo '<div class="Buy-btn" data-item-id="' . $item['id'] . '" data-price="' . $price . '">$' . $price . '</div>';
        echo '</div>';
    }
?>