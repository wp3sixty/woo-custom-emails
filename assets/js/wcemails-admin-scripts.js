var wcemails_admin;

(function($) {

    wcemails_admin = {

        init: function() {

            $(document).on( 'click', '#wcemails_submit', wcemails_admin.wcemails_save_email_details );

        },

        wcemails_save_email_details : function(e) {

            e.preventDefault();

            var title = $('#wcemails_title').val(),
                description = $('#wcemails_description').val(),
                heading = $('#wcemails_heading').val(),
                hook = $('#wcemails_hook').val(),
                html_template = $('#wcemails_html_template').val(),
                plain_template = $('#wcemails_plain_template').val();

            if( title == '' ||
                description == '' ||
                heading == '' ||
                hook == '' ||
                html_template == '' ||
                plain_template == '' ) {

                return false;

            }

            var data = {
                action : 'wcemails_save_email_details',
                title: title,
                description: description,
                heading: heading,
                hook: hook,
                html_template: html_template,
                plain_template: plain_template
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                success: function( response ){
                    alert('saved!');
                },
                fail: function(){

                },
                dataType: 'json'
            });

        },

    };

    jQuery( document).ready( function() { wcemails_admin.init(); } );

}(jQuery));
