<?php
    class InstallController extends GameCore
    {
        public function index(): string
        {
            $this->install();  /*$this ir class InstallController*/

            return view('install/index', [
                'title' => 'Install GameCore',
            ]);
        }

        public function install(): void{
            $db = new Database();

            /////////////////////////Create Tables//////////////////////////////

            if (!$db->tableExists('players')) {
                $db->execute(
                    'CREATE TABLE players (
                    id INT NOT NULL AUTO_INCREMENT,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password CHAR(128) NOT NULL,
                    email VARCHAR(50) NOT NULL UNIQUE,
                    money INT DEFAULT 0,
                    PRIMARY KEY (id)
                    );' 
                );
                echo "Created players table.<br>";
            }

            if (!$db->tableExists('shopCategory')){
                $db->execute(
                    'CREATE TABLE shopCategory (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(50) NOT NULL UNIQUE,
                    description TEXT,
                    PRIMARY KEY (id)
                    );'
                );
                echo "Created shopCategory table.<br>";
            }

            if (!$db->tableExists('items')){
                $db->execute(
                    'CREATE TABLE items (
                    id INT NOT NULL AUTO_INCREMENT,
                    categoryId INT NOT NULL,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    price INT NOT NULL,
                    timeUse INT NOT NULL,
                    energyUse INT,
                    PRIMARY KEY (id)
                    );'
                );
                echo "Created items table.<br>";
            }

            if (!$db->tableExists('seeds')){
                $db->execute(
                    'CREATE TABLE seeds (
                    id INT NOT NULL AUTO_INCREMENT,
                    itemId INT NOT NULL,
                    minYield INT NOT NULL,
                    maxYield INT NOT NULL,
                    growthTime INT NOT NULL,
                    sellingPrice DECIMAL(10,2) NOT NULL,
                    PRIMARY KEY (id)
                    );'
                );
                echo "Created seeds table.<br>";
            };

            if (!$db->tableExists('playerInventory')){
                $db->execute(
                    'CREATE TABLE playerInventory (
                    id INT NOT NULL AUTO_INCREMENT,
                    playerId INT NOT NULL,
                    itemId INT NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    PRIMARY KEY (id)
                    );'
                );
                echo "Created playerInventory table.<br>";
            }

                if(!$db->tableExists('fields'))
            {
                $db->execute(
                    'CREATE TABLE fields (
                    cell_id INT PRIMARY KEY,
                    type VARCHAR(50) NOT NULL
                    );'
                );
                echo "Created fields table. <br>";
            }

            if (!$db->tableExists('playerSettings')){
            $db->execute(
                'CREATE TABLE playerSettings (
                id INT NOT NULL AUTO_INCREMENT,
                playerId INT NOT NULL,

                -- Navigation Keys --
                navigateLeft VARCHAR(10) NOT NULL,
                navigateRight VARCHAR(10) NOT NULL,
                navigateUp VARCHAR(10) NOT NULL,
                navigateDown VARCHAR(10) NOT NULL,
                comfirm VARCHAR(10) NOT NULL,

                -- Action Keys --
                openInventory VARCHAR(10) NOT NULL,
                openShop VARCHAR(10) NOT NULL,
                openSettings VARCHAR(10) NOT NULL,
                close VARCHAR(10) NOT NULL,

                -- Shop Categories --
                category1 VARCHAR(10) NOT NULL,
                category2 VARCHAR(10) NOT NULL,
                category3 VARCHAR(10) NOT NULL,
                category4 VARCHAR(10) NOT NULL,

                -- Movement Keys --
                moveUp VARCHAR(10) NOT NULL,
                moveDown VARCHAR(10) NOT NULL,
                moveLeft VARCHAR(10) NOT NULL,
                moveRight VARCHAR(10) NOT NULL,
                PRIMARY KEY (id)
                );'
            );
            echo "Created playerSettings table.<br>";
        }

            /////////////////////////Insert Data in Tables//////////////////////////////

            if($db->isTableEmpty('shopCategory')){
                $db->execute(
                    "INSERT INTO shopcategory (name, description)
                    VALUES
                    (
                        'Seeds',
                        'Choose the right seeds for your farm! Seeds come in different rarities, and higher rarity seeds produce greater yields, giving you more harvest per plant. The purchase price of seeds determines how much you can sell the harvest for.'
                    ),
                    (
                        'Plows',
                        'Plows are used to break the soil and create plowed fields. This step can be done at any time and is required before any further field work. Better plows reduce the energy and time needed to prepare the land.'
                    ),
                    (
                        'Cultivators',
                        'Cultivators can only be used on plowed fields and prepare the soil for seeding. Higher quality cultivators make cultivation faster and more energy-efficient, helping you keep up with larger farms.'
                    ),
                    (
                        'Tractors',
                        'Tractors enhance your tools by modifying the energy and time required to perform field work. Different tractors offer different bonuses, allowing you to customize your farming strategy for speed or efficiency.'
                    );"
                );
                echo "Added values to shopCategory. <br>";
            }
            
            if($db->isTableEmpty('items')){
                $db->execute(
                    "INSERT INTO items (categoryId, name, price, timeUse, energyUse)
                    VALUES
                    (1, 'Carrot Seeds', 7, 0, 0),
                    (1, 'Tomato Seeds', 20, 0, 0),
                    (1, 'Potato Seeds', 80, 0, 0),
                    (1, 'Corn Seeds', 120, 0, 0),
                    (1, 'Cucumber Seeds', 250, 0, 0),
                    
                    (2, 'Farmer\'s Starter Plow', 0, 10, 20),
                    (2, 'Rusty Hand Plow', 75, 7, 20),
                    (2, 'Heavy Soil Breaker', 200, 10, 10),
                    (2, 'Precision Furrow Plow', 750, 5, 15),
                    (2, 'Titan Earthsplitter', 2500, 1, 20),
                    
                    (3, 'Farmer\'s Starter Cultivator', 0, 10, 15),
                    (3, 'Rusty Hand Cultivator', 100, 7, 20),
                    (3, 'Basic Field Cultivator', 250, 5, 15),
                    (3, 'Deep-Till Cultivator', 1000, 5, 5),
                    (3, 'Industrial Soil Turner', 3500, 1, 15),
                    
                    (4, 'Farmer\'s Starter Tractor', 0, 0, 0),
                    (4, 'Rusty Field Tractor', 250, -35, 50),
                    (4, 'Heavy Duty Tractor', 1000, 35, -30),
                    (4, 'Industrial Workhorse', 2500, 35, 25),
                    (4, 'Mega Tractor 9000', 10000, 50, 40);"
                );
                echo "Added values to items. <br>";
            }
            
            if ($db->isTableEmpty('seeds')){
                $db->execute(
                    "INSERT INTO seeds (itemId, minYield, maxYield, sellingPrice, growthTime)
                    VALUES
                    (1, 1, 3,   5,  40),
                    (2, 2, 5,   8,  60),
                    (3, 3, 7,  20, 120),
                    (4, 2, 6,  40, 250),
                    (5, 1, 5, 100, 500)"
                );
                echo "Added values to seeds. <br>";
            }

            if ($db->isTableEmpty('playerSettings')){
                $db->execute(
                    "INSERT INTO playerSettings (
                        playerId, navigateLeft, navigateRight, navigateUp, navigateDown,
                        openInventory, openShop, openSettings, comfirm,
                        category1, category2, category3, category4,
                        moveUp, moveDown, moveLeft, moveRight,
                        close
                    )
                    VALUES
                    (
                        0, 'a', 'd', 'w', 's',
                        'e', 'q', 'p', 'enter',
                        '1', '2', '3', '4',
                        'w', 's', 'a', 'd',
                        'escape'
                    );"
                );
                echo "Added default key bindings to playerSettings. <br>";
            }
        }
    }
?>