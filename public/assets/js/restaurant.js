$().ready(function(){
    let id_photo_to_delete=0;

    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })


    showHideSpinner()
    showLargePhoto()
    showModalDeletePhoto()
    closeModal()

    function showHideSpinner(){
        $( "#formAddResto" ).submit(function() {
            $('.js-check-save').addClass('d-none');
            $('.js-spinner-save').removeClass('d-none');
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