$(function () {   
    $('#openInventoryButton').on('click', function () {
        if ($('#inventoryModal').is(':visible')) {
            $('#inventoryModal').hide();
            $(this).removeClass('active');
        } else {
            $('#inventoryModal').show();
            $(this).addClass('active');
        }
    });
});