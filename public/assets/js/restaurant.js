$().ready(function(){
    let id_photo_to_delete=0;

    showHideSpinner()
    showLargePhoto()
    showModalDeletePhoto()
    closeModal()

    function showHideSpinner(){
        $( "#formAddResto" ).submit(function() {
            $('.js-check-save').toggleClass('d-none');
            $('.js-spinner-save').toggleClass('d-none');
            $('.btn-save-resto').prop('disabled',true);
        });
    }


    function showLargePhoto(){
        $('.img-resto-min').on('click', function(){
            $('.img-resto-large').attr('src',$(this).attr("src"));
        })
    }


    function showModalDeletePhoto(){
        $(".btn-delete-photo").on('click', function(){
            id_photo_to_delete=$(this).attr('id');
            if(id_photo_to_delete===0)
                return

            $('#formDeletePhoto').attr("action",'/restaurateur/restaurant/photos/delete/'+id_photo_to_delete)
            $('#confirmDeletePhotoModal').modal('show')
        })
    }

    function closeModal(){
        $(".btn-close-modal").on('click', function(){
            $('#confirmDeletePhotoModal').modal('hide')
        })
    }


});