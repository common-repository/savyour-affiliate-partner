<?php
if ( ! defined( 'SY_ENC_SECRET' ) ) {
    define( 'SY_ENC_SECRET', "6gQb{Y" );
}
if ( ! defined( 'SY_ENC_METHOD' ) ) {
    define( 'SY_ENC_METHOD', "aes128" );
}
// Add a new top level menu link to the ACP
function savap_admin_page()
{
    add_menu_page(
        'Savyour Affiliate Partner',
        'Savyour Affiliate Partner',
        'manage_options',
        basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) ).'/includes/savap-admin-template.php'
    );
}

add_action('admin_menu', 'savap_admin_page');

// adding script in head
function savap_init_javascript()
{
    $key = get_option('savap_auth_secret');
    if (!empty($key)) {
        ?>
        <!--savyour code start here-->
        <script type="text/javascript">
            !function () {
                "savyour" in window || (window.savyour = function () {
                    window.savyour.q.push(arguments)
                }, window.savyour.q = []);
                const n = document.createElement("script");
                n.src = "//affiliate.savyour.com.pk/sap.min.js", n.async = !0, n.defer = !0;
                const t = document.getElementsByTagName("script")[0];
                t.parentNode.insertBefore(n, t)
            }();
            savyour('init', '<?php echo $key;?>');
        </script>
        <!--savyour code ends here-->
        <?php
    }
}

add_action('wp_head', 'savap_init_javascript');

// adding script in thankyou page
function savap_thankyou_script($order_id)
{
    if (!$order_id)
        return;
    $key = get_option('savap_auth_secret');
    if (!empty($key)) {

        // Allow code execution only once
        if (!get_post_meta($order_id, '_thankyou_action_done', true)) {
            $order = wc_get_order($order_id);
            $couponNames = '';
            foreach ($order->get_coupon_codes() as $couponCode) {
                $couponNames .= $couponCode . ',';
            }

            if (!empty($couponNames)) {
                $couponNames = rtrim($couponNames, ",");
            }

            $savyourCode = get_post_meta($order_id, 'savap-' . $order->get_order_number(), true);
            if ($order->get_subtotal() > 0 && (empty($savyourCode))) {
                $cart = [];
                $paymentDetails = [];
                $j = 0;
                foreach ($order->get_items() as $item_id => $item) {
                    $product_cat_slug = '';
                    $product_id = $item->get_product_id();
                    $terms = get_the_terms($product_id, 'product_cat');
                    foreach ($terms as $term) {
                        $product_cat_slug .= $term->name . ',';
                    }
                    $cart[$j]['category_name'] = trim($product_cat_slug, ',');
                    $cart[$j]['product_id'] = $product_id;
                    $cart[$j]['product_name'] = $item->get_name();
                    $cart[$j]['product_amount'] = $item->get_subtotal() / $item->get_quantity();
                    $cart[$j]['product_quantity'] = $item->get_quantity();
                    $j++;
                }
                $shippingTotal = $order->get_shipping_total();
                $taxTotal = $order->get_total_tax();
                $cartTotal = $order->get_total() - $shippingTotal - $taxTotal;
                $orderTotal = $order->get_subtotal();
                $orderDiscountTotal = $order->get_discount_total();
                $orderDeliveryTotal = $shippingTotal;
                $orderTaxTotal = $taxTotal;
                $orderPayment = $order->get_payment_method();
                $paymentTitle = $order->get_payment_method_title();

                if ($paymentTitle) {
                    $paymentTitle = explode(" ", wc_strtolower($paymentTitle));
                    if ($paymentTitle[0]) {
                        $paymentDetails = [
                            'credit_card_bin' => '',
                            'credit_card_company' => $paymentTitle[0]
                        ];
                        $paymentDetails = json_encode($paymentDetails);
                    }
                }
                update_post_meta($order_id, 'savap-' . $order->get_order_number(), 1);
                $cart = json_encode($cart);

                ?>
                <script type="text/javascript">
                    savyour('orderPlace', {
                        "invoice_id": <?php echo $order_id;?>,
                        "order_id": <?php echo $order_id;?>,
                        "cart_total": <?php echo $cartTotal;?>,
                        "cart_items": <?php echo $cart;?>,
                        "gross_amount": <?php echo $orderTotal;?>,
                        "tax_amount": <?php echo $orderTaxTotal;?>,
                        "delivery_amount": <?php echo $orderDeliveryTotal;?>,
                        "discount_code": '<?php echo $couponNames;?>',
                        "discount_amount": <?php echo $orderDiscountTotal;?>,
                        "payment_option": '<?php echo $orderPayment;?>',
                        "payment_details": <?php echo $paymentDetails;?>
                    });
                </script>
                <?php
            }
        }
    }
}

add_action('woocommerce_thankyou', 'savap_thankyou_script', 10, 1);

function savap_store_data()
{
    $error = "";
    if(empty( $_POST['savap_auth_secret']) || empty($_POST['savap_secret_work'])){
        $error = "All fields are required.";
    }elseif(strpos($_POST['savap_auth_secret'], ' ') !== false ||
        strpos($_POST['savap_secret_work'], ' ') !== false ){
        $error = "Whitespace is not allowed in all fields.";
    }elseif(strlen($_POST['savap_secret_work']) < 5 || strlen($_POST['savap_secret_work']) > 30){
        $error = "Invalid secret word length.";
    }
    if($error!=""){
        $_SESSION['error_savyour'] = $error;
    }else{

        $_SESSION['success_savyour'] = "Data Store Successfully.";
        $secret_key = SY_ENC_SECRET;
        $method = SY_ENC_METHOD;
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $auth_secret = sanitize_text_field($_POST['savap_auth_secret']);
        $secret_word = sanitize_text_field($_POST['savap_secret_work']);
        $message_to_encrypt = $auth_secret .$secret_word;

        $encrypted_message = openssl_encrypt($message_to_encrypt, $method, $secret_key, 0, $iv);
        update_option('savap_auth_secret', $auth_secret);
        update_option('savap_secret_work', $secret_word);
        update_option('savap_api_key', $encrypted_message);

    }
    wp_redirect('admin.php?page='.urlencode(basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) ).'/includes/savap-admin-template.php'));
    exit;
}
add_action('wp_ajax_savap_store_data', 'savap_store_data');

///Api Hook//
if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $arh = array();
        $rx_http = '/\AHTTP_/';

        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }

                    $arh_key = implode('-', $rx_matches);
                }

                $arh[$arh_key] = $val;
            }
        }

        return ($arh);
    }
}

//update order status
function savap_update_order( ) {

    $headers = apache_request_headers();
    $headers = array_change_key_case($headers, CASE_LOWER);

    $auth_key = get_option('savap_auth_secret');
    $api_key = get_option('savap_api_key');

    $headers_auth_key = isset($headers['auth-key']) ? $headers['auth-key'] : '';
    $headers_api_key = isset($headers['api-key']) ? $headers['api-key'] : '';

    $result = json_encode(['error' => 'You are not authorized']);

    if (!empty(($headers_auth_key) && $headers_auth_key == $auth_key)) {
        if (empty($headers_api_key) || $headers_api_key == $api_key) {
            $orderIds = json_decode(file_get_contents('php://input'), true);
            if ($orderIds) {
                $orders = [];
                $paymentDetails = [];
                $i = 0;
                foreach ($orderIds['order_ids'] as $order_id) {
                    // get an instance of the WC_Order object
                    try {
                        $order = new WC_Order($order_id);

                        if ($order) {

                            $couponNames = '';
                            foreach ($order->get_coupon_codes() as $couponCode) {
                                $couponNames .= $couponCode . ',';
                            }

                            if (!empty($couponNames)) {
                                $couponNames = rtrim($couponNames, ",");
                            }
                            $shippingTotal = $order->get_shipping_total();
                            $taxTotal =  $order->get_total_tax();
                            $orders[$i]['order_id'] = $order_id;
                            $orders[$i]['invoice_id'] = $order_id;
                            $orders[$i]['status'] = $order->get_status();
                            $orders[$i]['cancel_reason'] = get_post_meta($order_id, 'savyour_cancellation_reason', true);
                            $orders[$i]['cart_total'] = $order->get_total() - $shippingTotal -  $taxTotal;
                            $orders[$i]['gross_amount'] = $order->get_subtotal();
                            $orders[$i]['tax_amount'] = $taxTotal;
                            $orders[$i]['delivery_amount'] = $shippingTotal;
                            $orders[$i]['discount_code'] = $couponNames;
                            $orders[$i]['discount_amount'] = $order->get_discount_total();
                            $orders[$i]['payment_option'] = $order->get_payment_method();

                            $cart = [];
                            $j = 0;
                            $product_cat_slug = '';
                            foreach ($order->get_items() as $item_id => $item) {
                                $product_id = $item->get_product_id();
                                $terms = get_the_terms($product_id, 'product_cat');
                                $product_cat_slug = '';
                                foreach ($terms as $term) {
                                    $product_cat_slug .= $term->name . ',';
                                }
                                $quantity = $item->get_quantity();
                                $cart[$j]['category_name'] = trim($product_cat_slug, ',');
                                $cart[$j]['product_id'] = $product_id;
                                $cart[$j]['product_name'] = $item->get_name();
                                $cart[$j]['product_amount'] = $item->get_subtotal() / $quantity;
                                $cart[$j]['product_quantity'] = $quantity;
                                $j++;
                            }

                            $orders[$i]['cart_items'] = $cart;
                            $paymentTitle = $order->get_payment_method_title();

                            if ($paymentTitle) {
                                $paymentTitle = explode(" ", wc_strtolower($paymentTitle));
                                if ($paymentTitle[0]) {
                                    $paymentDetails = [
                                        'credit_card_bin' => '',
                                        'credit_card_company' => $paymentTitle[0]
                                    ];
                                    $orders[$i]['payment_details'] = $paymentDetails;
                                }
                            }

                            $i++;
                        }
                    } catch (\Exception $ex) {
                        //echo $ex->getMessage();
                    }
                }
                $result = json_encode($orders);
            }
        }
    }
    echo $result;
}

//generating api hook route for update order
add_action( 'rest_api_init', function () {
    //site_url/wp-json/savyour/savyour-affiliate-partner
    $namespace = 'savyour';
    $route     = '/savyour-affiliate-partner';
    register_rest_route( $namespace, $route, array(
        'methods' => 'POST',
        'callback' => 'savap_update_order',
        'permission_callback' => '__return_true'
    ) );
} );

// css and js inclusion in admin form
function savap_selectively_enqueue_admin_script( $hook ) {

    if (  basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) ).'/includes/savap-admin-template.php' != $hook ) {
        return;
    }
    wp_enqueue_style( 'savyour_custom_csst', plugin_dir_url( __FILE__ ) . 'assets/css/savyour_style.css', array(), '1.0','all' );
    wp_enqueue_script( 'savyour_custom_script', plugin_dir_url( __FILE__ ) . 'assets/js/savyour_script.js', array(), '1.0',true );
}
add_action( 'admin_enqueue_scripts', 'savap_selectively_enqueue_admin_script' );

//session init
function savap_start_session() {
    if(!session_id()) {
        session_start();
    }
}
add_action('init', 'savap_start_session', 1);

// Add custom text field cancellation_reason after order details
function add_custom_text_field_after_order_details($order) {
    echo '<p class="form-field custom-field savyour_cancellation_reason form-field-wide">
           <label for="savyour_cancellation_reason">Cancellation Reason:</label>
           <input type="text" name="savyour_cancellation_reason" id="savyour_cancellation_reason" value="' . get_post_meta($order->get_id(), 'savyour_cancellation_reason', true) . '">
         </p>';
}
add_action('woocommerce_admin_order_data_after_order_details', 'add_custom_text_field_after_order_details');

// Enqueue JavaScript file
function savap_order_enqueue_custom_script() {
    global $pagenow;

    if ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'shop_order') {
        wp_enqueue_script('savyour_admin_order_script', plugin_dir_url(__FILE__) . 'assets/js/savyour_admin_order_script.js', array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'savap_order_enqueue_custom_script');

// Save custom field data
function save_custom_field_data($order_id) {
    if (!empty($_POST['savyour_cancellation_reason'])) {
        $custom_field_value = sanitize_text_field($_POST['savyour_cancellation_reason']);
        update_post_meta($order_id, 'savyour_cancellation_reason', $custom_field_value);
    }
}
add_action('woocommerce_process_shop_order_meta', 'save_custom_field_data');
