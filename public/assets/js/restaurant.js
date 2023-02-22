$().ready(function(){
    $( "#formAddResto" ).submit(function() {
        $('.js-check-save').toggleClass('d-none');
        $('.js-spinner-save').toggleClass('d-none');
        $('.btn-save-resto').prop('disabled',true);
    });

    $('.img-resto-min').on('click', function(){
        $('.img-resto-large').attr('src',$(this).attr("src"));
    })
});