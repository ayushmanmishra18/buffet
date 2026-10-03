"use strict";

$(document).on('click', '.delete', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var $form = $btn.closest('form');

    $('#delete-modal').addClass('active');
    $("#modalconfirm-btn").on('click', function(){
        $form.submit();
    });

    $("#modalcancel-btn").on('click', function(){
        $('#delete-modal').removeClass('active');   
    });
});

