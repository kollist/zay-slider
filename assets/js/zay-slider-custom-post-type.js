jQuery(document).ready(function($) {

    $( "#tabs" ).tabs();

    $("button.visibility-btn").click(function (e) {
        e.preventDefault();
        $(e.target).parent().children("button").each((i, ele) => {
            if (e.target === ele){
                $(ele).hide();
            }else {
                $(ele).show();
            }
        });
    })

    $( "#accordion" ).accordion({
        collapsible: true,
        active: false,
    });

    $("li.nav-tab").click(function (e){
        let ul = $('.nav-tab-wrapper li ')
        ul.each(function(index, li) {
            $(li).removeClass("nav-tab-active");
          });
        $(e.target).closest("li").addClass("nav-tab-active");
    })

    let anchorID = getQueryVariable('scroll_to')
    
    function getQueryVariable(variable) {
        let query = window.location.search.substring(1);
        let vars = query.split("&").map(ele => ele.split("="))
        for(let i = 0; i < vars.length; i++){
            if (vars[i][0] === variable){
                return vars[i][1];            
            }
        }
        
    }

    if (anchorID) {
        var target = $('#' + anchorID);
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top
          }, 800);
        }
    }


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


        $(".show-thumb").data("hide");

        let itemID = $(".modal-dialog").data("id");

        $(".hide-title").css("display", $("li#"+itemID+" .item-show-info h4").data("hide") == "1" ? "block" : "none");
        $(".hide-image").css("display", $("li#"+itemID+" .show-thumb").data("hide") == "1" ? "block" : "none");
        $(".hide-name").css("display", $("li#"+itemID+" .item-show-info .item-author").data("hide") == "1" ? "block" : "none");
        $(".hide-price").css("display", $("li#"+itemID+" .item-show-price").data("hide") == "1" ? "block" : "none");

        $(".show-title").css("display", $("li#"+itemID+" .item-show-info h4").data("hide") == "1" ? "none" : "block");
        $(".show-image").css("display", $("li#"+itemID+" .show-thumb").data("hide") == "1" ? "none" : "block");
        $(".show-name").css("display", $("li#"+itemID+" .item-show-info .item-author").data("hide") == "1" ? "none" : "block");
        $(".show-price").css("display", $("li#"+itemID+" .item-show-price").data("hide") == "1" ? "none" : "block");


        let storedData = JSON.parse(localStorage.getItem("item_"+itemID));


        if (storedData){
            $("#_title_style").text(storedData.title_style);
            $("#_author_style").text(storedData.author_style);
            $("#_price_style").text(storedData.price_style);
            $("#_image_style").text(storedData.image_style);
            $("#_description_style").text(storedData.description_style);
            $("#_css_class").val(storedData._css_class);
            $("#_css_id").val(storedData._css_id);
        }


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
            $("#zay_slider_item_price").val(informations[2])
            $("#zay_slider_item_price_name").val(informations[3])
        }

        if (informations[5] != ""){
            $('.item-creator-name').slideDown();
            $(".add-creator-name").fadeOut();
            $("#zay_slider_item_creator_name").val(informations[5])
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


    $("#sortable").on('click' ,".edit-btn-displayer", {}, function (e) {
        e.preventDefault();
        let post = $(e.target).closest("li");
        $('.dialogMenuItem').slideDown(
            100,
            function () {
                $('.model-dialog').slideDown();
                let itemTitle = post.find('.item-info .item-show-info h4').text();
                let itemDesc = post.find(".item-info .item-show-desc").html();
                let itemPrice = post.find(".item-info .item-show-info .item-show-price").find('span:first').text();
                let itemPriceName = post.find(".item-info .item-show-info .item-show-price span").last().text();
                let itemPicture = post.find('.show-data img').attr('src');
                let itemCreator = post.find(".show-data .item-author span").text();

                $(".edit-btn").show();
                $(".save-btn").hide();
                $(".modal-dialog").data("id", post.attr("id"));
                editFillUp([itemTitle, itemDesc, itemPrice, itemPriceName, itemPicture, itemCreator]);
            }
            );
            $('.dialogMenuItem').removeClass('hide')
    })

    $(".add-creator-name").click(function (e){
        e.preventDefault();
        $('.item-creator-name').slideDown();
        $(this).fadeOut();
    })
    $('.rmvCreatorName').click(function (e){
        e.preventDefault();
        $('.item-creator-name').slideUp();
        $('.add-creator-name').fadeIn();
    })

    $(".edit-btn").click(function (e) {
        e.preventDefault();
        if (!$('.priceAmount').val() && $('.priceName').val()){
            $('.errorMessage').removeClass('hide');
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
        editedData.append("itemEditedSecurity",  $('.edit-btn').data('nonce'));
        editedData.append("itemEditedParent_ID", $('#post_ID').val());
        editedData.append("itemEditedPriceAmount", $('.item-price').attr('.hidePrice') ? '' : $('.priceAmount').val());
        editedData.append("itemEditedPriceName",  $('.item-price').attr('.hidePrice') ? '' : $('.priceName').val());
        editedData.append("itemEditedCreatorName",  $('.item-creator-name').attr('.hideCreator') ? '' : $('#zay_slider_item_creator_name').val());
        editedData.append("action", 'edit_item_post');
        editedData.append("itemEditedHideTitle", $(".hide-title#hide").css("display") == 'block' && "1")
        editedData.append("itemEditedHideImage", $(".hide-image#hide").css("display") == "block"  && "1")
        editedData.append("itemEditedHideName", $(".hide-name#hide").css("display") == "block" && "1")
        editedData.append("itemEditedHidePrice", $(".hide-price#hide").css("display") == "block" && "1")
        editedData.append("_title_style", $("#_title_style").val());
        editedData.append("_author_style", $("#_author_style").val());
        editedData.append("_price_style", $("#_price_style").val());
        editedData.append("_image_style", $("#_image_style").val());
        editedData.append("_description_style", $("#_description_style").val());
        editedData.append("_slide_custom_classes", $("#_css_class").val());
        editedData.append("_slide_custom_id", $("#_css_id").val());
        $.ajax({
            url: ZAY_FRONT.ajaxurl,
            action: 'zay-slider-jq',
            method: 'POST',
            data: editedData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success){
                    doClick(e);
                    $("#"+editedData.get("itemEditedID")+' .item-show-info h4').html(editedData.get('itemEditedTitle'));  
                    $("#"+editedData.get("itemEditedID")+' .item-show-info h4').data("hide", editedData.get("itemEditedHideTitle"))
                    $("#"+editedData.get("itemEditedID")+" .item-show-desc").html(editedData.get('itemEditedContent'))
                    $("#"+editedData.get("itemEditedID")+' .item-show-price span:first').html(editedData.get('itemEditedPriceAmount'));
                    $("#"+editedData.get("itemEditedID")+' .item-show-price span:last').html(editedData.get('itemEditedPriceName'));
                    $("#"+editedData.get("itemEditedID")+' .item-show-price').data("hide", editedData.get("itemEditedHidePrice"));
                    $("#"+editedData.get("itemEditedID")+' .item-author span').html(editedData.get('itemEditedCreatorName'));
                    $("#"+editedData.get("itemEditedID")+' .item-author').data("hide", editedData.get("itemEditedHideName"))
                    $("#"+editedData.get("itemEditedID")+" .show-thumb").data("hide", editedData.get("itemEditedHideImage"))
                    let src = editedData.get('itemEditedThumbnail') ? editedData.get('itemEditedThumbnail') : `${document.location.origin}/wp-content/uploads/zayslider-default-100x100.jpeg`;
                    let img = $("#"+editedData.get("itemEditedID")+" img");
                    img.attr('src', src);
                    img.attr('srcset', "");
                    $("#"+$(".modal-dialog").attr("id")+" .hide-title").css("display", editedData.get("itemEditedHideTitle") == "1" ? "block" : "none");
                    $("#"+$(".modal-dialog").attr("id")+" .hide-image").css("display", editedData.get("itemEditedHideImage") == "1" ? "block" : "none");
                    $("#"+$(".modal-dialog").attr("id")+" .hide-name").css("display", editedData.get("itemEditedHideName") == "1" ? "block" : "none");
                    $("#"+$(".modal-dialog").attr("id")+" .hide-price").css("display", editedData.get("itemEditedHidePrice") == "1" ? "block" : "none");
            
                    $("#"+$(".modal-dialog").attr("id")+" .show-title").css("display", editedData.get("itemEditedHideTitle") == "1" ? "none" : "block");
                    $("#"+$(".modal-dialog").attr("id")+" .show-image").css("display", editedData.get("itemEditedHideImage") == "1" ? "none" : "block");
                    $("#"+$(".modal-dialog").attr("id")+" .show-name").css("display", editedData.get("itemEditedHideName") == "1" ? "none" : "block");
                    $("#"+$(".modal-dialog").attr("id")+" .show-price").css("display", editedData.get("itemEditedHidePrice") == "1" ? "none" : "block");
                }
            },
            error: function (xhr) {
                console.log(xhr)
            }
        });
        


    });


    $('.confirmDeleteBtn').click(function (e) {
        e.preventDefault();
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
                console.log(res);
                localStorage.removeItem("item_"+postID);
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

    $("#sortable").on('click' ,'.delete-btn', {},  function (e){
        e.preventDefault()
        let id = $(e.target).closest("li").attr('id');
        theDialog.data('deletedID', id).dialog('open');
    });

    $('#sortable').sortable({
        start: function (e, ui) {
            ui.item.addClass('custom-cursor');
            $("#ac");
            
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

            $('.item-title').val("Item Title");
            if (typeof tinymce !== 'undefined') {
                var editor = tinymce.get('my_custom_editor');
                if (editor) {
                  editor.setContent("Your Item Description Goes Here");
                }
            }

            $('.thumb').removeAttr('src');
            $('.item-price').slideUp();  
            $('.add-price').fadeIn();
            $('.priceAmount').val("");
            $('.priceName').val("");
            $('.uploader-upload').show();
            $('.uploader-image').hide();
            $("#zay_slider_item_creator_name").val("");
            $('.item-creator-name').slideUp();
            $('.add-creator-name').fadeIn();

            $('.dialogMenuItem').slideUp(100,
            function () {
                $('.model-dialog').slideUp(100);
            });
            $('.dialogMenuItem').hide(100);
            $('.dialogMenuItem').addClass('hide');

            $(".edit-btn").hide();
            $(".save-btn").show();

            $(".hide-title").css("display", "none");
            $(".hide-image").css("display", "none");
            $(".hide-name").css("display", "none");
            $(".hide-price").css("display", "none");
            $(".show-title").css("display", "block");
            $(".show-image").css("display", "block");
            $(".show-name").css("display", "block");
            $(".show-price").css("display", "block");


            $(".nav-tab-wrapper li").first().addClass("nav-tab-active")
            $(".nav-tab-wrapper li").last().removeClass("nav-tab-active")

            // $("#tabs").tabs("option", "active", "#new_item");

            $("#_title_style").val("");
            $("#_author_style").val("");
            $("#_price_style").val("");
            $("#_image_style").val("");
            $("#_description_style").val("");
            $("#_css_class").val("");
            $("#_css_id").val("");
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
            attachement =custom_uploader.state().get('selection').first().toJSON();
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
        e.preventDefault();
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
        postData.append("itemThumbnail", $('.thumb').attr('src'));
        postData.append("itemSecurity", $('.zay_slider_nonce').val());
        postData.append("itemParent_ID", $('#post_ID').val());
        postData.append("itemPriceAmount", $('.item-price').attr('.hidePrice') ? '' : $('.priceAmount').val());
        postData.append("itemPriceName",  $('.item-price').attr('.hidePrice') ? '' : $('.priceName').val());
        postData.append('creatorName', $('item-creator-name').attr("hideCreator") ? "" : $('#zay_slider_item_creator_name').val())
        postData.append("action", 'submit_cpt');
        postData.append("_hide_title", $(".hide-title#hide").css("display") == 'block' && "1")
        postData.append("_hide_image", $(".hide-image#hide").css("display") == "block"  && "1")
        postData.append("_hide_name", $(".hide-price#hide").css("display") == "block" && "1")
        postData.append("_hide_price", $(".hide-name#hide").css("display") == "block" && "1")
        postData.append("_title_style", $("#_title_style").val());
        postData.append("_author_style", $("#_author_style").val());
        postData.append("_price_style", $("#_price_style").val());
        postData.append("_image_style", $("#_image_style").val());
        postData.append("_description_style", $("#_description_style").val());
        postData.append("_slide_custom_classes", $("#_css_class").val());
        postData.append("_slide_custom_id", $("#_css_id").val());
        $.ajax({
            url: ZAY_FRONT.ajaxurl,
            action: 'zay-slider-jq',
            method: 'POST',
            data: postData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.status == 'success'){
                    let dataStored = JSON.stringify({
                        "title_style": $("#_title_style").val(),
                        "author_style": $("#_author_style").val(),
                        "price_style": $("#_price_style").val(),
                        "image_style": $("#_image_style").val(),
                        "description_style": $("#_description_style").val(),
                        "_css_class": $("#_css_class").val(),
                        "_css_id": $("#_css_id").val()
                    });
                    localStorage.setItem("item_"+res.id, dataStored);                    
                    let element = $('<li>', {
                        id: res.id,
                        class: 'menu-item ui-sortable-handle',
                      }).html(`
                        <div class="show-data">      
                          <div class="show-thumb" data-hide="${postData.get("_hide_image")}">
                            <img width="70" height="70" style="height: 70px" src="${ $('.thumb').attr('src') ? $('.thumb').attr('src') : `${document.location.origin}/wp-content/uploads/zayslider-default-100x100.jpeg` }" />
                          </div>
                          <div class="item-info">
                            <div class="item-show-info">
                              <h4 data-hide="${postData.get("_hide_title")}">${$('.item-title').val()}</h4>
                              <div class="item-author" data-hide="${postData.get("_hide_name")}" >
                                    <span>${postData.get("creatorName")}</span>
                                </div>
                              <div class="item-show-price" data-hide="${postData.get("_hide_price")}">
                                <span>${$('#zay_slider_item_price').val()}</span>
                                <span>${$('#zay_slider_item_price_name').val()}</span>
                              </div>
                            </div>
                            <div class="item-show-desc">
                              ${tinymce.get('my_custom_editor').getContent()}
                            </div>
                          </div>
                        </div>
                        <div class="action-data">
                          <button class="delete-btn">
                            <span class="deleteIcon dashicons dashicons-trash"></span>
                          </button>
                          <button class="edit-btn-displayer" >
                            <span class="editIcon dashicons dashicons-edit"></span>
                          </button>
                        </div>
                    `);

                    $("#sortable").append(element)

                    $('.dialogMenuItem').slideUp(100,
                    function () {
                        $('.model-dialog').slideUp(100);
                    });
                    $('.dialogMenuItem').hide(100);
                    $('.dialogMenuItem').addClass('hide');
                    doClick(e)
                }
            },
            error: function (xhr) {
                console.log(xhr)
            }
        });
    })
    
});
