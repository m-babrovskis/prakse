<?php
class ShopController extends GameCore
{
    public function index(): string
    {
        return renderView('shop/index', [
            'title' => 'Veikals',
            'lead' => 'Šis ir veikala moduļa skats, kas tiek ielādēts caur Router + layout.',
            'player' => $this->playerAuth(),
        ]);
    }

    public function category(): string
    {
        // Check if player is logged in
        // if (!isset($_SESSION['player_id'])) {
        //     header('Location: /auth');
        //     exit;
        // }
        
        $categoryId = $_POST['categoryId'] ?? '';
        
        // Extract category name and get corresponding category ID
        $categoryName = str_replace('Category', '', $categoryId);
        
        // Get the actual category ID from database
        $categoryRow = $this->db->getRow(
            "SELECT id FROM shopCategory WHERE LOWER(name) = ?",
            [strtolower($categoryName)]
        );
        
        $dbCategoryId = $categoryRow ? $categoryRow['id'] : 1;
        
        // Debug output
        error_log("Category ID received: " . $categoryId);
        error_log("Category name extracted: " . $categoryName);
        error_log("Database category ID: " . $dbCategoryId);

        // For seeds (categoryId = 1), join with seeds table
        
        if ($dbCategoryId == 1) {
            $items = $this->db->getArray(
                "SELECT i.*, s.minYield, s.maxYield, s.growthTime, s.sellingPrice 
                 FROM items i 
                 JOIN seeds s ON i.id = s.itemId 
                 WHERE i.categoryId = ? ORDER BY i.id",
                [$dbCategoryId]
            );
        } else {
            // For other categories, just get items
            $items = $this->db->getArray(
                "SELECT * FROM items WHERE categoryId = ? ORDER BY id",
                [$dbCategoryId]
            );
        }
        
        error_log("Items found: " . count($items));
    
        return renderView('shop/items', [
            'items' => $items,
            'categoryId' => $dbCategoryId
        ]);
    }

}
