jQuery(document).ready(function($) {
    var $orderStatusSelect = $('#order_status');

    // Function to show or hide the custom text field
    function toggleCustomField() {
        var selectedStatus = $orderStatusSelect.val();
        var $customField = $('.savyour_cancellation_reason');
        var isCancelled = selectedStatus.toLowerCase().includes('cancelled');

        if (isCancelled) {
            $customField.show();
            setTimeout(function() { $('input[name="savyour_cancellation_reason"]').focus() }, 1000);
        } else {
            $customField.hide();
        }
    }

    // Initially toggle the custom field based on the selected order status
    toggleCustomField();

    // Toggle the custom field whenever the order status changes
    $orderStatusSelect.on('change', toggleCustomField);
});