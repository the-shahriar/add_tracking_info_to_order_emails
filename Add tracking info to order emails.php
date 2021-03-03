// 
// 
// create the metabox
add_action( 'add_meta_boxes', 'bdev_add_postnord_meta_box' );
if ( ! function_exists( 'bdev_add_gls_meta_box' ) )
{
    function bdev_add_postnord_meta_box()
    {
        add_meta_box( 'gls_field', __('GLS Parcel ID','woocommerce'), 'bdev_add_gls_for_tracking', 'shop_order', 'side', 'core' );
    }
}

if ( ! function_exists( 'bdev_add_gls_for_tracking' ) )
{
    function bdev_add_gls_for_tracking()
    {
        global $post;

        $postnord_field_data = get_post_meta( $post->ID, '_gls_field_data', true ) ? get_post_meta( $post->ID, '_gls_field_data', true ) : '';

        echo '<input type="hidden" name="gls_meta_field_nonce" value="' . wp_create_nonce() . '">
        <p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
            <input type="text" style="width:250px;";" name="gls_data_name" placeholder="' . $gls_field_data . '" value="' . $gls_field_data . '"></p>';

    }
}

// save input from metabox
add_action( 'save_post', 'bdev_save_gls_data_to_order', 10, 1 );
if ( ! function_exists( 'bdev_save_gls_data_to_order' ) )
{

    function bdev_save_gls_data_to_order( $post_id ) {

        if ( ! isset( $_POST[ 'gls_meta_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'gls_meta_field_nonce' ];

        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        if ( 'page' == $_POST[ 'post_type' ] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        update_post_meta( $post_id, '_gls_field_data', $_POST[ 'gls_data_name' ] );
    }
}


// print tracking info under shipping address
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'gls_custom_field_display_admin_order_meta', 10, 1 );
function gls_custom_field_display_admin_order_meta($order){
    $gls_id_field = get_post_meta( $order->id, '_gls_field_data', true );
    if ( ! empty( $gls_id_field ) ) {
        echo '<p><strong>'. __("GLS Tracking ID", "woocommerce").':</strong> ' . get_post_meta( $order->id, '_gls_field_data', true ) . '</p>';
    }
}


// add information to order email
add_action( 'woocommerce_email_before_order_table', 'add_gls_tracking_to_customer_complete_order_email', 20, 2 );
function add_gls_tracking_to_customer_complete_order_email( $order, $sent_to_admin ) {

if ( ! $sent_to_admin ) {
echo '<h2>Track Your Order</h2>';
echo '<p><strong>'. __("GLS Tracking ID", "woocommerce").':</strong> ' . get_post_meta( $order->id, '_gls_field_data', true ) . '</p>';
echo '<p>You can track your parcel on the <a href="https://www.postnord.se/en/online-tools/tools/track/track-and-trace" target="_blank" rel="">GLS website</a> or directly from <a href="#" target="_blank" rel="">Our website</a>.<br><br>';
}
}