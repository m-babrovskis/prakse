$('#shopContainer').show();
$(function () {
    $('#openShopButton').on('click', function () {
        if ($('#shopContainerLoader').html() === '') {
            $('#gameContainer').hide();

            $.post('/shop', {}, function (html) {
                $('#shopContainerLoader').html(html);
            }).fail(function () {
                $('#shopContainerLoader').html('<p>Kļūda ielādējot veikalu</p>');
            });
        } else {
            $('#shopContainerLoader').html('');
            $('#gameContainer').show();
        }
    });

    $(document).on('click', '#closeShopButton', function () {
        $('#shopContainerLoader').html('');
        $('#gameContainer').show();
    });

    $(document).on('click', '.category-board', function (){
        const categoryId = $(this).data('categoryid');
        console.log('Izvēlētā kategorija:', categoryId);
        console.log('Element:', this);
        console.log('Data attribute:', $(this).data());
        $.post('/shop/category', { categoryId: categoryId }, function (html) {
            $('#shop-table-container').show();
            $('#category-board-container').hide();
            $('#ShopItemContainer').html(html);
        }).fail(function () {
            $('#ShopItemContainer').html('<p>Kļūda ielādējot kategoriju</p>');
        });
    })

    $(document).on('click', '.closeItems', function (){
        $('#shop-table-container').hide();
        $('#category-board-container').show();
        $('#ShopItemContainer').html('');
    });
});