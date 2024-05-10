/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

$(document).ready(function(){

    function formatDate(inputDate) {
        // Parse the input date string
        var parsedDate = new Date(inputDate);

        // Format the date as 'd-m-Y'
        var formattedDate = ('0' + parsedDate.getDate()).slice(-2) + '-' + ('0' + (parsedDate.getMonth() + 1)).slice(-2) + '-' + parsedDate.getFullYear();

        return formattedDate;
    }

    /*
     * Product Page
     * ----------------------*/

    // Product Details
    $('.prod-name').click(function(e){
        e.preventDefault();
        var url = $(this).data('product-url');
        console.log(url)
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response){
                console.log(response)
                var created = formatDate(response['created']);
                var content = '\
                    <div class="modal-body">\
                        <ul class="list-group list-group-flush">\
                            <li class="list-group-item"><span>ID        : </span>' + response["id"]+ '</li>\
                            <li class="list-group-item"><span>Created   : </span>' + created + '</li>\
                            <li class="list-group-item"><span>Name      : </span>' + response["name"] + '</li>\
                            <li class="list-group-item"><span>Reference : </span>' + response["ref"] + '</li>\
                            <li class="list-group-item"><span>Packaging : </span>' + response["packaging"] + '</li>\
                            <li class="list-group-item"><span>Quantity  : </span>' + response["qte"] + '</li>\
                            <li class="list-group-item"><span>Price     : </span>' + response["price"] + '</li>\
                            <li class="list-group-item"><span>Total     : </span>' + response["total"] + '</li>\
                        </ul>\
                    </div>\
                ';
                $('#modal-title').text('Product Details');
                $('#modal-body').html(content);
                $('#my-modal').modal('show');
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error('Error:', error);
            }
        });
    });

    // Delete Product
    $('.delete-product').click(function(e){
        e.preventDefault();
        $('#confirm-delete').remove();      // Remove button

        var url = $(this).data('delete-prod-url');
        var prod_id = $(this).data('prod_id');
        var prod_name = $(this).data('prod_name');

        var content = '<h5>Are you sure to delete: <b>' + prod_name + '</b></h5>';

        var confirm_btn = $('<button>', {
            "id": "confirm-delete",
            "class": "btn btn-danger",
            "data-delete-url": url,
            "text": "Delete",
        }).appendTo('.modal-footer');

        $('#modal-title').text('Delete Product')
        $('#modal-body').html(content);
        //$('.modal-footer').append(button);
        $('#my-modal').modal('show');

    });

    // confirm delete
    $('#my-modal').on('click', '#confirm-delete', function(e){
        e.preventDefault();
        var url = $(this).data('delete-url');
        $.ajax({
            url: url,
            type: 'POST',
            success: function(response) {
                window.location = '/products';
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });

    // Search Product
    $('#searchProduct').keyup(function(){
        var searchWord = $(this).val();
        console.log(searchWord)
        $.ajax({
            url: '/products/search-product',
            type: 'GET',
            data: {search_word: searchWord},
            success: function(response){
                //console.log(response);
                location.href = '/products';
            },
            error: function(xhr, status, error) {
                console.error('Error: ', error);
            }
        });
    });

});
