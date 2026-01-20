$(function() {
    // Generate 29 inventory item cells (1-25 inventory, 26-29 equipment)
    for (let i = 1; i <= 29; i++) {
        if (i <= 25) {
            $('#inventoryItemContainer').append(`<div id="itemCell${i}" class="itemCell"></div>`);
        } else {
            // Equipment cells (26-29)
            const equipmentMap = {
                26: 'tractorSlot',
                27: 'plowSlot', 
                28: 'cultivatorSlot',
                29: 'seedSlot'
            };
            $(`#${equipmentMap[i]}`).attr('id', `itemCell${i}`).addClass('itemCell');
        }
    }
    
    // Load player inventory (assuming player ID 1 for now)
    loadPlayerInventory(1);
    
    // Make items draggable and droppable
    makeItemsDraggable();
});

function makeItemsDraggable() {
    $('.itemCell').draggable({
        containment: '#inventoryModal',
        revert: 'invalid',
        helper: function() {
            return $('<div>').text($(this).find('.item-name').text()).css({
                'background': 'linear-gradient(135deg, rgba(35, 65, 40, 0.9), rgba(70, 130, 180, 0.9))',
                'color': 'white',
                'padding': '8px 12px',
                'border-radius': '15px',
                'font-size': '12px',
                'font-weight': 'bold',
                'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
                'border': '1px solid rgba(255,255,255,0.3)',
                'white-space': 'nowrap'
            });
        }
    }).droppable({
        accept: '.itemCell',
        tolerance: 'pointer',
        over: function(event, ui) {
            const targetCell = $(this);
            if (targetCell.closest('#equipmentContainer').length > 0) {
                const requiredCategory = targetCell.attr('data-slot-type');
                const draggedCategory = ui.draggable.attr('data-category');
                
                if (requiredCategory && draggedCategory !== requiredCategory) {
                    targetCell.addClass('invalid-drop');
                }
            }
        },
        out: function(event, ui) {
            $(this).removeClass('invalid-drop');
        },
        drop: function(event, ui) {
            const draggedItem = ui.draggable;
            const targetCell = $(this);
            
            // Remove invalid-drop class
            targetCell.removeClass('invalid-drop');
            
            // Check if target is equipment slot and validate category
            if (targetCell.closest('#equipmentContainer').length > 0) {
                const requiredCategory = targetCell.attr('data-slot-type');
                const draggedCategory = draggedItem.attr('data-category');
                
                if (requiredCategory && draggedCategory !== requiredCategory) {
                    return false; // Reject drop if categories don't match
                }
            }
            
            if (draggedItem[0] !== targetCell[0]) {
                // Swap content
                const draggedContent = draggedItem.html();
                const targetContent = targetCell.html();
                const draggedCat = draggedItem.attr('data-category');
                const targetCat = targetCell.attr('data-category');
                
                draggedItem.html(targetContent);
                targetCell.html(draggedContent);
                
                // Swap category data properly
                if (targetCat) {
                    draggedItem.attr('data-category', targetCat);
                } else {
                    draggedItem.removeAttr('data-category');
                }
                
                if (draggedCat) {
                    targetCell.attr('data-category', draggedCat);
                } else {
                    targetCell.removeAttr('data-category');
                }
            }
        }
    }).on('mouseenter', function() {
        const $this = $(this);
        $this.attr('data-hovered', 'true');
        if ($this.html().trim() && window.shiftPressed) {
            window.highlightDestination($this);
        }
    }).on('mouseleave', function() {
        $(this).removeAttr('data-hovered');
        $('.itemCell').removeClass('shift-destination');
    }).on('click', function(e) {
        if (e.shiftKey && $(this).html().trim()) {
            handleShiftAction($(this));
        }
    });
}

function highlightDestination(cell) {
    $('.itemCell').removeClass('shift-destination');
    
    const cellId = cell.attr('id');
    const cellNumber = parseInt(cellId.replace('itemCell', ''));
    
    if (cellNumber >= 26 && cellNumber <= 29) {
        // Equipment to inventory - highlight first empty slot
        for (let i = 1; i <= 25; i++) {
            const inventoryCell = $(`#itemCell${i}`);
            if (!inventoryCell.html().trim()) {
                inventoryCell.addClass('shift-destination');
                break;
            }
        }
    } else if (cellNumber >= 1 && cellNumber <= 25) {
        // Inventory to equipment - highlight matching equipment slot
        const itemCategory = cell.attr('data-category');
        if (itemCategory) {
            const equipmentSlotMap = {
                '1': 29, // seeds -> seedSlot (cell 29)
                '2': 27, // plow -> plowSlot (cell 27) 
                '3': 28, // cultivator -> cultivatorSlot (cell 28)
                '4': 26  // tractor -> tractorSlot (cell 26)
            };
            
            const targetCellNumber = equipmentSlotMap[itemCategory];
            if (targetCellNumber) {
                $(`#itemCell${targetCellNumber}`).addClass('shift-destination');
            }
        }
    }
}

// Make function globally accessible
window.highlightDestination = highlightDestination;

function handleShiftAction(cell) {
    const cellId = cell.attr('id');
    const cellNumber = parseInt(cellId.replace('itemCell', ''));
    
    if (cellNumber >= 26 && cellNumber <= 29) {
        // Equipment to inventory
        for (let i = 1; i <= 25; i++) {
            const inventorySlot = $(`#itemCell${i}`);
            if (!inventorySlot.html().trim()) {
                const equipmentContent = cell.html();
                const equipmentCat = cell.attr('data-category');
                
                inventorySlot.html(equipmentContent);
                inventorySlot.attr('data-category', equipmentCat);
                
                cell.html('');
                cell.removeAttr('data-category');
                break;
            }
        }
    } else if (cellNumber >= 1 && cellNumber <= 25) {
        // Inventory to equipment
        const itemCategory = cell.attr('data-category');
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
            
            if (!equipmentSlot.html().trim()) {
                // Move to empty equipment slot
                const inventoryContent = cell.html();
                const inventoryCat = cell.attr('data-category');
                
                equipmentSlot.html(inventoryContent);
                equipmentSlot.attr('data-category', inventoryCat);
                
                cell.html('');
                cell.removeAttr('data-category');
            } else {
                // Swap items
                const equipmentContent = equipmentSlot.html();
                const inventoryContent = cell.html();
                const equipmentCat = equipmentSlot.attr('data-category');
                const inventoryCat = cell.attr('data-category');
                
                equipmentSlot.html(inventoryContent);
                cell.html(equipmentContent);
                
                equipmentSlot.attr('data-category', inventoryCat);
                cell.attr('data-category', equipmentCat || '');
            }
        }
    }
}

function loadPlayerInventory(playerId) {
    $.get('/assets/php/getInventory.php', { playerId: playerId })
        .done(function(response) {
            if (response.success) {
                displayInventoryItems(response.inventory);
            } else {
                console.error('Failed to load inventory:', response.error);
            }
        })
        .fail(function() {
            console.error('Error loading inventory');
        });
}

function displayInventoryItems(inventory) {
    inventory.forEach((item) => {
        const slotsNeeded = Math.ceil(item.quantity / 99);
        
        for (let slot = 0; slot < slotsNeeded; slot++) {
            let targetCell;
            const remainingQuantity = item.quantity - (slot * 99);
            const slotQuantity = Math.min(remainingQuantity, 99);
            
            if (item.cell === 0 || slot > 0) {
                // Find first empty cell for items with cell = 0 or additional slots
                for (let i = 1; i <= 29; i++) {
                    const cell = $(`#itemCell${i}`);
                    if (!cell.html().trim()) {
                        targetCell = cell;
                        break;
                    }
                }
            } else {
                // Use designated cell for first slot
                targetCell = $(`#itemCell${item.cell}`);
            }
            
            if (targetCell) {
                const quantityDisplay = slotQuantity > 1 ? `<div class="item-quantity">${slotQuantity}</div>` : '';
                targetCell.html(`
                    <div class="item-name">${item.name}</div>
                    ${quantityDisplay}
                `).attr('data-category', item.categoryId);
            }
        }
    });
    
    // Re-initialize draggable and shift-click functionality after loading items
    makeItemsDraggable();
}