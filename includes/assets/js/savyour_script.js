jQuery(document).ready(function () {

    jQuery('body').on('click', '.afflt-stngs .alert .close', function() {
        jQuery(this).parent().hide();
    });

    jQuery(function () {
        setTimeout(function () {
            jQuery('.alert.alert-success').hide()
        }, 5000);
    });

    jQuery('form#affiliate_form').on('submit', function(e){
        jQuery("#affiliate_form .error_form").html("");

        var error_message = "";
        var auth_secret = jQuery("#affiliate_form #auth-secret").val();
        var secret_word = jQuery("#affiliate_form #work-secret").val();
        if(auth_secret=="" || secret_word==""){
            error_message = "All fields are required";
        }else if(auth_secret.indexOf(' ')>=0 || secret_word.indexOf(' ')>=0){
            error_message = "Whitespace is not allowed.";
        }else if(secret_word.length < 5 || secret_word.length > 30){
            error_message = "Invalid secret word length.";
        }
        if(error_message!=""){
            e.preventDefault();
            jQuery("#affiliate_form .error_form").html('<div class="alert alert-danger">' +
                '<a href="#" class="close" data-dismiss="alert" aria-label="Close" title="close">×</a>' +
                    error_message+
                '</div>');
        }
    });
})
