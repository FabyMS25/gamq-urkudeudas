(function($){
    $(document).on('click', '.showModalButton', function () {
        //check if the modal is open. if it's open just reload content not whole modal
        //also this allows you to nest buttons inside of modals to reload the content it is in
        //the if else are intentionally separated instead of put into a function to get the 
        //button since it is using a class not an #id so there are many of them and we need
        //to ensure we get the right button and content. 
        if ($('#modal').data('bs.modal').isShown) {
            $('#modal').find('#modalContent')
                    .load($(this).attr('value'));
            //dynamiclly set the header for the modal
            document.getElementById('modalHeaderTitle').innerHTML = '<h4>' + $(this).attr('title') + '</h4>';
        } else {
            //if modal isn't open; open it and load content
            $('#modal').modal('show')
                    .find('#modalContent')
                    .load($(this).attr('value'));
            //dynamiclly set the header for the modal
            document.getElementById('modalHeaderTitle').innerHTML = '<h4>' + $(this).attr('title') + '</h4>';
        }
    });
 })(jQuery);
 
    // Catch click event on all buttons that want to open a modal
    // with bulk action
 $( document ).ready(function() {
    $(document).on('click', '[role="modal-remote-bulk"]', function (event) {
           event.preventDefault();
           var selectedIds = [];
           $('input:checkbox[name="selection[]"]').each(function () {
               if (this.checked) selectedIds.push($(this).val());
           });

           if (selectedIds.length == 0) {
               modal.show();
               modal.setTitle('Ningún registro fue seleccionado');
               modal.setContent('Usted debe seleccionar uno o varios registros para continuar con la operación.');
               modal.addFooterButton("Cerrar", 'button', 'btn btn-default', function (button, event) { this.hide(); } );
           } else { modal.open(this, selectedIds); }
    });
});