document.addEventListener('DOMContentLoaded', function() {
    let keyBindings = {};
    let controlsLoaded = false;
    
    console.log('Starting to fetch controls from server...');
    // Fetch controls from server
    fetch('/assets/php/get-controls.php')
        .then(response => {
            console.log('Fetch response received - Status:', response.status);
            return response.text(); // Get text first to see what's actually returned
        })
        .then(text => {
            console.log('Raw response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON data:', data);
                if (data && !data.error && !controlsLoaded) {
                    keyBindings = data;
                    controlsLoaded = true;
                    console.log('KeyBindings set to:', keyBindings);
                    initializeControls();
                } else {
                    console.error('Failed to load controls from database:', data.error || 'Unknown error');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response was not valid JSON:', text);
            }
        })
        .catch(error => {
            console.error('Error loading controls:', error);
            console.error('Error stack:', error.stack);
        });
    
    function initializeControls() {
        console.log('=== INITIALIZING CONTROLS ===');
        console.log('KeyBindings object:', keyBindings);
        console.log('KeyBindings keys:', Object.keys(keyBindings));
        console.log('Sample controls:');
        console.log('  - openShop:', keyBindings.openShop);
        console.log('  - openInventory:', keyBindings.openInventory);
        console.log('  - moveUp:', keyBindings.moveUp);
        console.log('  - navigateLeft:', keyBindings.navigateLeft);
    
    // ========== VARIABLES ==========
    const keys = {};
    const scrollAmount = 30;
    let selectedCategoryIndex = 0;
    let selectedInventoryIndex = 0;
    let selectedInventoryItem = null;
    
    // ========== SHIFT KEY TRACKING ==========
    window.shiftPressed = false;
    $(document).on('keydown', function(e) {
        if (e.key === 'Shift') {
            window.shiftPressed = true;
            // Trigger highlight for currently hovered item
            $('.itemCell[data-hovered="true"]').each(function() {
                if ($(this).html().trim() && typeof window.highlightDestination === 'function') {
                    window.highlightDestination($(this));
                }
            });
        }
    }).on('keyup', function(e) {
        if (e.key === 'Shift') {
            window.shiftPressed = false;
            $('.itemCell').removeClass('shift-destination');
        }
    });
    
    // ========== INVENTORY FUNCTIONS ==========
    function highlightKeyboardDestination(cell) {
        $('.itemCell').removeClass('shift-destination');
        
        if (cell.closest('#equipmentContainer').length > 0) {
            $('#inventoryItemContainer .itemCell').each(function() {
                if (!$(this).html().trim()) {
                    $(this).addClass('shift-destination');
                    return false;
                }
            });
        } else {
            const itemCategory = cell.attr('data-category');
            if (itemCategory) {
                $('#equipmentContainer .itemCell').each(function() {
                    if ($(this).attr('data-category') === itemCategory) {
                        $(this).addClass('shift-destination');
                        return false;
                    }
                });
            }
        }
    }
    
    function navigateInventory(direction) {
        const inventoryCells = $('#inventoryItemContainer .itemCell');
        const equipmentCells = $('#equipmentContainer .itemCell');
        const allCells = $('.itemCell');
        
        if (allCells.length === 0) return;
        
        $('.itemCell').removeClass('selected');
        
        // Clear all invalid-drop indicators first
        $('.itemCell').removeClass('invalid-drop');
        
        const currentCell = allCells.eq(selectedInventoryIndex);
        const isInEquipment = currentCell.closest('#equipmentContainer').length > 0;
        
        if (isInEquipment) {
            const equipmentIndex = equipmentCells.index(currentCell);
            
            // If holding item, only allow navigation to matching slot or back to inventory
            if (selectedInventoryItem && selectedInventoryItem.category) {
                switch(direction) {
                    case 'left':
                        selectedInventoryIndex = 4; // go to inventory top right
                        break;
                    case 'right':
                        selectedInventoryIndex = 0; // go to inventory first cell
                        break;
                    case 'up':
                    case 'down':
                        const targetSlot = $(`#equipmentContainer .itemCell[data-slot-type="${selectedInventoryItem.category}"]`);
                        if (targetSlot.length) {
                            selectedInventoryIndex = allCells.index(targetSlot);
                        }
                        break;
                }
            } else {
                switch(direction) {
                    case 'left':
                        selectedInventoryIndex = equipmentIndex > 0 ? 
                            allCells.index(equipmentCells.eq(equipmentIndex - 1)) : 
                            4; // go to inventory top right (cell 5)
                        break;
                    case 'right':
                        selectedInventoryIndex = equipmentIndex < 3 ? 
                            allCells.index(equipmentCells.eq(equipmentIndex + 1)) : 
                            0; // go to inventory first cell
                        break;
                    case 'up':
                    case 'down':
                        selectedInventoryIndex = allCells.index(equipmentCells.eq(equipmentIndex));
                        break;
                }
            }
        } else {
            const inventoryIndex = inventoryCells.index(currentCell);
            const row = Math.floor(inventoryIndex / 5);
            const col = inventoryIndex % 5;
            
            switch(direction) {
                case 'left':
                    if (col === 0) {
                        // If holding item, go to matching equipment slot
                        if (selectedInventoryItem && selectedInventoryItem.category) {
                            const targetSlot = $(`#equipmentContainer .itemCell[data-slot-type="${selectedInventoryItem.category}"]`);
                            if (targetSlot.length) {
                                selectedInventoryIndex = allCells.index(targetSlot);
                                break;
                            }
                        }
                        selectedInventoryIndex = allCells.index(equipmentCells.eq(3));
                    } else {
                        selectedInventoryIndex--;
                    }
                    break;
                case 'right':
                    if (col === 4) {
                        // If holding item, go to matching equipment slot
                        if (selectedInventoryItem && selectedInventoryItem.category) {
                            const targetSlot = $(`#equipmentContainer .itemCell[data-slot-type="${selectedInventoryItem.category}"]`);
                            if (targetSlot.length) {
                                selectedInventoryIndex = allCells.index(targetSlot);
                                break;
                            }
                        }
                        selectedInventoryIndex = allCells.index(equipmentCells.eq(0));
                    } else {
                        selectedInventoryIndex++;
                    }
                    break;
                case 'up':
                    if (row === 0) {
                        selectedInventoryIndex = inventoryIndex + 20;
                    } else {
                        selectedInventoryIndex -= 5;
                    }
                    break;
                case 'down':
                    if (row === 4) {
                        selectedInventoryIndex = col;
                    } else {
                        selectedInventoryIndex += 5;
                    }
                    break;
            }
        }
        
        allCells.eq(selectedInventoryIndex).addClass('selected');
        
        // Check if selected cell should show invalid drop indicator
        if (selectedInventoryItem) {
            const selectedCell = allCells.eq(selectedInventoryIndex);
            if (selectedCell.closest('#equipmentContainer').length > 0) {
                const requiredCategory = selectedCell.attr('data-category');
                if (requiredCategory && selectedInventoryItem.category !== requiredCategory) {
                    selectedCell.addClass('invalid-drop');
                } else {
                    selectedCell.removeClass('invalid-drop');
                }
            } else {
                selectedCell.removeClass('invalid-drop');
            }
        } else {
            $('.itemCell').removeClass('invalid-drop');
        }
        
        // Highlight shift destination for keyboard navigation
        if (window.shiftPressed && allCells.eq(selectedInventoryIndex).html().trim()) {
            highlightKeyboardDestination(allCells.eq(selectedInventoryIndex));
        } else {
            $('.itemCell').removeClass('shift-destination');
        }
    }
    
    function handleInventorySelect() {
        const selectedCell = $('.itemCell.selected');
        if (!selectedCell.length) return;
        
        if (!selectedInventoryItem) {
            if (selectedCell.html().trim()) {
                selectedInventoryItem = {
                    content: selectedCell.html(),
                    category: selectedCell.attr('data-category')
                };
                selectedCell.css('opacity', '0.5');
            }
        } else {
            const targetCell = selectedCell;
            
            if (targetCell.closest('#equipmentContainer').length > 0) {
                const requiredCategory = targetCell.attr('data-category');
                if (requiredCategory && selectedInventoryItem.category !== requiredCategory) {
                    return;
                }
            }
            
            const targetContent = targetCell.html();
            const targetCategory = targetCell.attr('data-category');
            
            targetCell.html(selectedInventoryItem.content);
            targetCell.attr('data-category', selectedInventoryItem.category);
            
            $('.itemCell').each(function() {
                if ($(this).css('opacity') === '0.5') {
                    $(this).html(targetContent);
                    $(this).attr('data-category', targetCategory || '');
                    $(this).css('opacity', '1');
                    return false;
                }
            });
            
            selectedInventoryItem = null;
        }
    }
    
    function handleShiftSelect() {
        const selectedCell = $('.itemCell.selected');
        if (!selectedCell.length || !selectedCell.html().trim()) return;
        
        const cellId = selectedCell.attr('id');
        const cellNumber = parseInt(cellId.replace('itemCell', ''));
        
        if (cellNumber >= 26 && cellNumber <= 29) {
            // Equipment to inventory
            for (let i = 1; i <= 25; i++) {
                const inventorySlot = $(`#itemCell${i}`);
                if (!inventorySlot.html().trim()) {
                    const equipmentContent = selectedCell.html();
                    const equipmentCat = selectedCell.attr('data-category');
                    
                    inventorySlot.html(equipmentContent);
                    inventorySlot.attr('data-category', equipmentCat);
                    
                    selectedCell.html('');
                    selectedCell.removeAttr('data-category');
                    break;
                }
            }
        } else if (cellNumber >= 1 && cellNumber <= 25) {
            // Inventory to equipment
            const itemCategory = selectedCell.attr('data-category');
            if (!itemCategory) return;
            
            const equipmentSlotMap = {
                '1': 29, // seeds -> seedSlot (cell 29)
                '2': 27, // plow -> plowSlot (cell 27)
                '3': 28, // cultivator -> cultivatorSlot (cell 28)
                '4': 26  // tractor -> tractorSlot (cell 26)
            };
            
            const targetCellNumber = equipmentSlotMap[itemCategory];
            if (targetCellNumber) {
                const equipmentSlot = $(`#itemCell${targetCellNumber}`);
                const equipmentContent = equipmentSlot.html();
                const inventoryContent = selectedCell.html();
                const equipmentCat = equipmentSlot.attr('data-category');
                const inventoryCat = selectedCell.attr('data-category');
                
                equipmentSlot.html(inventoryContent);
                selectedCell.html(equipmentContent);
                
                equipmentSlot.attr('data-category', inventoryCat);
                selectedCell.attr('data-category', equipmentCat || '');
            }
        }
    }
    
    // ========== KEYBOARD SHORTCUTS ==========
    const shortcuts = {};
    
    // Open/Close Shortcuts
    shortcuts[keyBindings.openShop] = () => {
        $('#openShopButton').trigger('click');
    };
    
    shortcuts[keyBindings.openInventory] = () => {
        if ($('#gameContainer').is(':visible') && !$('.inventoryModal').is(':visible')) {
            document.getElementById('openInventoryButton')?.click();
            setTimeout(() => {
                selectedInventoryIndex = 0;
                $('.itemCell').removeClass('selected');
                $('.itemCell').eq(0).addClass('selected');
            }, 100);
        } else if ($('.inventoryModal').is(':visible')) {
            $('.inventoryModal').hide();
            $('#openInventoryButton').removeClass('active');
        }
    };
    
    shortcuts[keyBindings.openSettings] = () => {
        if ($('#settingsModal').is(':visible')) {
            $('#settingsModal').hide();
        } else {
            $('#settingsModal').show();
        }
    };
    
    shortcuts[keyBindings.close] = () => {
        if ($('.category-board-container').is(':visible')) {
            document.getElementById('closeShopButton')?.click();
        } else if ($('#shop-table-container').is(':visible')) {
            document.querySelector('.closeItems')?.click();
        }
    };
    
    // Confirm Shortcut
    shortcuts[keyBindings.confirm] = () => {
        const categories = document.querySelectorAll('.category-board');
        if (categories[selectedCategoryIndex]) {
            categories[selectedCategoryIndex].querySelector('.browse-btn')?.click();
        }
    };
    
    // Category Shortcuts
    shortcuts[keyBindings.category1] = () => {
        if ($('.category-board-container').is(':visible')) {
            selectCategory(0);
            const categories = document.querySelectorAll('.category-board');
            categories[0]?.querySelector('.browse-btn')?.click();
        }
    };
    
    shortcuts[keyBindings.category2] = () => {
        if ($('.category-board-container').is(':visible')) {
            selectCategory(1);
            const categories = document.querySelectorAll('.category-board');
            categories[1]?.querySelector('.browse-btn')?.click();
        }
    };
    
    shortcuts[keyBindings.category3] = () => {
        if ($('.category-board-container').is(':visible')) {
            selectCategory(2);
            const categories = document.querySelectorAll('.category-board');
            categories[2]?.querySelector('.browse-btn')?.click();
        }
    };
    
    shortcuts[keyBindings.category4] = () => {
        if ($('.category-board-container').is(':visible')) {
            selectCategory(3);
            const categories = document.querySelectorAll('.category-board');
            categories[3]?.querySelector('.browse-btn')?.click();
        }
    };
    
    // ========== SHOP FUNCTIONS ==========
    function selectCategory(index) {
        const categories = document.querySelectorAll('.category-board');
        if (index >= 0 && index < categories.length) {
            // Remove hover from all
            categories.forEach(cat => cat.classList.remove('keyboard-selected'));
            // Add hover to selected
            selectedCategoryIndex = index;
            categories[selectedCategoryIndex].classList.add('keyboard-selected');
        }
    }
    
    function navigateCategories(direction) {
        const categories = document.querySelectorAll('.category-board');
        if (categories.length === 0) return;
        
        if (direction === 'left') {
            selectedCategoryIndex = selectedCategoryIndex > 0 ? selectedCategoryIndex - 1 : categories.length - 1;
        } else {
            selectedCategoryIndex = selectedCategoryIndex < categories.length - 1 ? selectedCategoryIndex + 1 : 0;
        }
        selectCategory(selectedCategoryIndex);
    }
    
    // ========== SMOOTH SCROLL ==========
    function smoothScroll() {
        let x = 0, y = 0;
        
        if (keys[keyBindings.moveUp] || keys['arrowup']) y -= scrollAmount;
        if (keys[keyBindings.moveDown] || keys['arrowdown']) y += scrollAmount;
        if (keys[keyBindings.moveLeft] || keys['arrowleft']) x -= scrollAmount;
        if (keys[keyBindings.moveRight] || keys['arrowright']) x += scrollAmount;
        
        if (x !== 0 || y !== 0) {
            window.scrollBy(x, y);
        }
        
        requestAnimationFrame(smoothScroll);
    }
    
    // ========== EVENT LISTENERS ==========
    document.addEventListener('keydown', function(e) {
        if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
            return;
        }
        
        const key = e.key.toLowerCase();
        const inShop = $('.category-board-container').is(':visible');
        const inInventory = $('#inventoryModal').is(':visible');
        const inSettings = $('#settingsModal').is(':visible');
        
        // Settings navigation (priority check)
        if (inSettings) {
            if (key === keyBindings.close || key === 'escape') {
                $('#settingsModal').hide();
                e.preventDefault();
                return;
            }
            // Block other navigation when in settings
            e.preventDefault();
            return;
        }
        
        // Shop navigation
        if (inShop) {
            if (key === keyBindings.navigateLeft || key === 'arrowleft') {
                navigateCategories('left');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateRight || key === 'arrowright') {
                navigateCategories('right');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateUp || key === 'arrowup') {
                // Add vertical shop navigation if needed
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateDown || key === 'arrowdown') {
                // Add vertical shop navigation if needed
                e.preventDefault();
                return;
            }
        }
        
        // Inventory navigation
        if (inInventory) {
            if (key === keyBindings.navigateLeft || key === 'arrowleft') {
                navigateInventory('left');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateRight || key === 'arrowright') {
                navigateInventory('right');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateUp || key === 'arrowup') {
                navigateInventory('up');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.navigateDown || key === 'arrowdown') {
                navigateInventory('down');
                e.preventDefault();
                return;
            }
            if (key === keyBindings.confirm || key === 'enter') {
                if (e.shiftKey) {
                    handleShiftSelect();
                } else {
                    handleInventorySelect();
                }
                e.preventDefault();
                return;
            }
        }
        
        // Movement keys (only when not in shop)
        if (!inShop && [keyBindings.moveUp, keyBindings.moveLeft, keyBindings.moveDown, keyBindings.moveRight, 'arrowup', 'arrowdown', 'arrowleft', 'arrowright'].includes(key)) {
            keys[key] = true;
            e.preventDefault();
            return;
        }
        
        // UI shortcuts
        if (shortcuts[key]) {
            if (typeof shortcuts[key] === 'function') {
                shortcuts[key]();
            } else {
                document.getElementById(shortcuts[key])?.click();
            }
            e.preventDefault();
        }
    });
    
    document.addEventListener('keyup', function(e) {
        const key = e.key.toLowerCase();
        if ([keyBindings.moveUp, keyBindings.moveLeft, keyBindings.moveDown, keyBindings.moveRight, 'arrowup', 'arrowdown', 'arrowleft', 'arrowright'].includes(key)) {
            keys[key] = false;
        }
    });
    
    smoothScroll();
    } // End of initializeControls function
});