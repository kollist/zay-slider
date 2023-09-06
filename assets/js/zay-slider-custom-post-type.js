jQuery(document).ready(function($) {


    $('.bgcolor_field').wpColorPicker();



    var theDialog = $('.delete-dialog').dialog({
        autoOpen: false,
        model: true,
        draggable: false,
        resizable: false,
    });

    $('.deleteBtn #closeDeleteDialogBtn').click(function (e) {
        theDialog.dialog('close');
    });
    

    const editFillUp = (informations) => {

        $('.icon-delete').click(function () {
            $('.thumb').removeAttr('src');
            $('.uploader-image').hide();
            $('.uploader-upload').show();
        })

        $('#itemTitle').val(informations[0]);
        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get('my_custom_editor');
            if (editor) {
              editor.setContent(informations[1]);
            }
          }

        if ( informations[2] !== "" || informations[3] !== ""){
            $('.item-price').slideDown();
            $('.add-price').fadeOut();
            $("#zay_slider_item_price").val( informations[2])
            $("#zay_slider_item_price_name").val( informations[3])
        }
        $('.thumb').attr('src',  informations[4])
        if ($('.thumb').attr('src')){
            $('.uploader-upload').hide();
            $('.uploader-image').show()
            
        }else {
            $('.uploader-upload').show();
            $('.uploader-image').hide();
        }
    }


    $(".edit-btn-displayer").click(function (e) {
        e.preventDefault();
        let post = $(e.target).closest("li");
        $('.dialogMenuItem').slideDown(
            100,
            function () {
                $('.model-dialog').slideDown();
                let itemTitle = post.find('.item-info .item-show-title h4').text();
                let itemDesc = post.find(".item-info .item-show-desc").html();
                let itemPrice = post.find(".item-info .item-show-title .item-show-price").find('span:first').text();
                let itemPriceName = post.find(".item-info .item-show-title .item-show-price span").last().text();
                let itemPicture = post.find('.show-data img').attr('src');

                $(".edit-btn").show();
                $(".save-btn").hide();
                $(".modal-dialog").data("id", post.attr("id"));
                editFillUp([itemTitle, itemDesc, itemPrice, itemPriceName, itemPicture]);
            }
            );
            $('.dialogMenuItem').removeClass('hide')
    })

    $(".edit-btn").click(function (e) {
        if (!$('.priceAmount').val() && $('.priceName').val()){
            $('.errorMessage').removeClass('hide');
            e.preventDefault();
            return
        }else {
            $('.errorMessage').addClass('hide');
        }
        let editedData = new FormData();
        editedData.append("itemEditedID", $(".modal-dialog").data("id"))
        editedData.append("itemEditedTitle", $('.item-title').val());
        editedData.append("itemEditedContent", tinymce.get('my_custom_editor').getContent());
        editedData.append("itemEditedPosttype" , ' zay-slider-item');
        editedData.append("itemEditedThumbnail" , $('.thumb').attr('src'));
        editedData.append("itemEditedSecurity",  $('.edit-btn-displayer').data('nonce'));
        editedData.append("itemEditedParent_ID", $('#post_ID').val());
        editedData.append("itemEditedPriceAmount", $('.item-price').attr('.hidePrice') ? '' : $('.priceAmount').val());
        editedData.append("itemEditedPriceName",  $('.item-price').attr('.hidePrice') ? '' : $('.priceName').val());
        editedData.append("action", 'edit_item_post');

        $.ajax({
            url: ZAY_FRONT.ajaxurl,
            action: 'zay-slider-jq',
            method: 'POST',
            data: editedData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (res) {
                console.log(res);
            },
            error: function (xhr) {
                console.log(xhr)
            }
        });
        


    });


    $('.confirmDeleteBtn').click(function (e) {
        let postID = theDialog.data('deletedID');
        let deleteNonce = $(this).data('nonce');
        let data =  new FormData();
        let post = $('li#'+postID);
        data.append('security', deleteNonce);
        data.append('postID', postID);
        data.append('action', 'delete_item_post');
        data.append('post_type', 'zay-slider-item');
        $.ajax({
            url: ZAY_FRONT.ajaxurl,
            action: 'zay-slider-jq',
            method: 'POST',
            data: data,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (res) {
                theDialog.dialog('close');
                post.fadeOut( function(){
                    post.remove();
                });
            },
            error: function (xhr) {
                console.log(xhr)
            }
        });
    })

    $('.cancelDeleteBtn').click(function (e) {
        
        theDialog.dialog('close')
    })

    $('.delete-btn').click(function (e){
        e.preventDefault()
        let id = $(e.target).closest("li").attr('id');
        theDialog.data('deletedID', id).dialog('open');

    });

    $('#sortable').sortable({
        start: function (e, ui) {
            ui.item.addClass('custom-cursor')
        },
        stop: function(event, ui) {
            var sortedData = $(this).sortable('toArray', {attribute: 'id'});
            $.ajax({
                url: ZAY_FRONT.ajaxurl, // Replace with your AJAX endpoint
                type: 'POST',
                data: {
                    action: 'save_sortable_data',
                    order: sortedData
                },
                success: function(response) {
                    if (response.status == 'success'){
                        console.log(response.message);
                    }
                }
            });
            ui.item.removeClass('custom-cursor');
        }
    });
    
    if ($('.thumb').attr('src')){
        $('.uploader-upload').hide();
    }else {
        $('.uploader-image').hide();
    }
    $('.rmvPrice').click(function () {
        $('.item-price').slideUp()
        $('.add-price').fadeIn();
    });
    $('.add-price').click(function (e){
        e.preventDefault();
        $('.item-price').slideDown();
        $('.add-price').fadeOut();
    })
    $('.itemBtn').on('click', function(e) {
        e.preventDefault();
        $('.dialogMenuItem').slideDown(
            100,
            function () {
                $('.model-dialog').slideDown();
            }
            );
            $('.dialogMenuItem').removeClass('hide')
            
        });
        function doClick(e){
            e.preventDefault();
            $('.dialogMenuItem').slideUp(100,
            function () {
                $('.model-dialog').slideUp(100);
            });
            $('.dialogMenuItem').hide(100);
            $('.dialogMenuItem').addClass('hide');

           location.reload()
    }
    $('.cancel-btn').click(doClick);
    $('.modal-button').click(doClick);
    var custom_uploader;
    $('.uploader-upload').click(reactOnClick);
    $('#upload_image_button').click(reactOnClick);
    function reactOnClick(e) {
        e.preventDefault();
        if (custom_uploader){
            custom_uploader.open();
            return;
        }
        custom_uploader = wp.media.frames.file_frame = wp.media({
            multiple: false,
            library: {type: 'image'},
            button: {text: 'Select Image'},
            title: 'Select an Image or enter an image URL.'
        });
        custom_uploader.on('select', function(){
            // console.log(custom_uploader.state().get('selection').toJSON());
            attachement =custom_uploader.state().get('selection').first().toJSON();
            // console.log(attachement.url);
            $('.thumb').attr('src', attachement.url);
            if ($('.thumb').attr('src')){
                $('.uploader-upload').hide();
                $('.uploader-image').show();
            }else {
                $('.uploader-image').hide();
                $('.uploader-upload').show();
            }
        })
        $('.icon-delete').click(function () {
            $('.thumb').removeAttr('src');
            $('.uploader-image').hide();
            $('.uploader-upload').show();
        })
    }
    $(".save-btn").click(function (e) {
        if (!$('.priceAmount').val() && $('.priceName').val()){
            $('.errorMessage').removeClass('hide');
            e.preventDefault();
            return
        }else {
            $('.errorMessage').addClass('hide');
        }
        postData = new FormData();
        postData.append("itemTitle", $('.item-title').val());
        postData.append("itemContent", tinymce.get('my_custom_editor').getContent());
        postData.append("itemPosttype",'zay-slider-item');
        postData.append("itemThumbnail", $('.thumb').attr('src') ? $('.thumb').attr('src') : 'https://wordpress.store/wp-content/uploads/woocommerce-placeholder-300x300.png');
        postData.append("itemSecurity", $('.zay_slider_nonce').val());
        postData.append("itemParent_ID", $('#post_ID').val());
        postData.append("itemPriceAmount", $('.item-price').attr('.hidePrice') ? '' : $('.priceAmount').val());
        postData.append("itemPriceName",  $('.item-price').attr('.hidePrice') ? '' : $('.priceName').val());
        postData.append("action", 'submit_cpt');
        $.ajax({
            url: ZAY_FRONT.ajaxurl,
            action: 'zay-slider-jq',
            method: 'POST',
            data: postData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (res) {
                console.log(res);
            },
            error: function (xhr) {
                console.log(xhr)
            }
        });
    })
    
});
