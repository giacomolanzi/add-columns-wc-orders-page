<?php
// Add column for customer's order note (from checkout)
add_filter('manage_edit-shop_order_columns', 'add_customer_note_column_header');
function add_customer_note_column_header($columns) {
  $new_columns = (is_array($columns)) ? $columns : array();
  $new_columns['order_customer_note'] = "Note cliente all'acquisto";
  return $new_columns;
}
// Style of the column
add_action('admin_print_styles', 'add_customer_note_column_style');
function add_customer_note_column_style() {
  $css = '.widefat .column-order_customer_note { width: 15%; }';
  wp_add_inline_style('woocommerce_admin_styles', $css);
}
// Add said notes into the column
add_action('manage_shop_order_posts_custom_column', 'add_customer_note_column_content');
function add_customer_note_column_content($column) {
  global $post, $the_order;
  if(empty($the_order) || $the_order->get_id() != $post->ID) {
    $the_order = wc_get_order($post->ID);
  }
  $customer_note = $the_order->get_customer_note();
  if($column == 'order_customer_note') {
    echo('<span class="order-customer-note">' . $customer_note . '</span>');
  }
}

// Add column for administrative order's notes
add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 90 );
function custom_shop_order_column( $columns ){
    $ordered_columns = array();
    foreach( $columns as $key => $column ){
        $ordered_columns[$key] = $column;
        if( 'order_customer_note' == $key ){
            $ordered_columns['order_notes'] = "Note all'ordine";
        }
    }
    return $ordered_columns;
}
add_action('admin_print_styles', 'custom_shop_order_column_style');
function custom_shop_order_column_style() {
  $css = '.widefat .column-order_notes { width: 15%; text-align:left;}';
  wp_add_inline_style('woocommerce_admin_styles', $css);
}
// Search notes and add them
add_action( 'manage_shop_order_posts_custom_column' , 'custom_shop_order_list_column_content', 10, 1 );
function custom_shop_order_list_column_content( $column ){
    global $post;
    if ( $column == 'order_notes' ) {
        if ( $post->comment_count ) {
            $latest_notes = wc_get_order_notes( array(
                'order_id' => $post->ID,
                'limit'    => 1,
                'orderby'  => 'date_created_gmt',
            ) );
            $latest_note = current( $latest_notes );
            if ( isset( $latest_note->content ) && 1 == $post->comment_count ) {
                echo '<span class="order-note" style="text-align:left;">' . $latest_note->content . '</span>';
            } elseif ( isset( $latest_note->content ) ) {
                echo '<span class="order-note" style="text-align:left;">' . $latest_note->content . '<br/><small style="display:block; color:#009015;">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $post->comment_count - 1 ), 'woocommerce' ), $post->comment_count - 1 ) . '</small></span>';
            } else {
                echo '<span class="order-note" style="text-align:left;">' . sprintf( _n( '%d note', '%d notes', $post->comment_count, 'woocommerce' ), $post->comment_count ) . '</span>';
            }
        }
    }
}
