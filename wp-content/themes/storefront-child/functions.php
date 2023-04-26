<?php

/*
 * Check if woocommerce plugin is active
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  add_action('storefront_before_header', 'sst_display_coupons');
} else {
  return;
}

/*
 * Echo string before storefront header
 */
function sst_display_coupons()
{
  echo 'Detta är en rabatt kupong';
}

/*
 * 
 */

function sst_remove_sidebar_product_pages()
{
  if (is_woocommerce() || is_checkout()) {
    remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
  }
}
add_action('get_header', 'sst_remove_sidebar_product_pages');


function sst_remove_header_from_cart()
{
  if (is_checkout()) {
    remove_action('storefront_page', 'storefront_page_header', 10);
    remove_action('storefront_before_content', 'storefront_header_widget_region', 10);
    remove_all_actions('storefront_header');
  }
}
add_action('wp_head', 'sst_remove_header_from_cart');

function sst_remove_footer_from_cart()
{
  if (is_checkout()) {
    remove_action('storefront_footer', 'storefront_footer_widgets', 10);
    remove_action('storefront_footer', 'storefront_credit', 20);
  }
}
add_action('wp_head', 'sst_remove_footer_from_cart');

/*
  * Function to render phonenumber under checkout btn -> checkout page 
*/
function sst_add_custom_checkout_content()
{
  $phone_number = '123-456-7890';
  echo '<p class="phone-checkout">Behöver du hjälp? Ring oss på <a href="tel:' . $phone_number . '">' . $phone_number . '</a></p>';
}
add_action('woocommerce_review_order_after_submit', 'sst_add_custom_checkout_content', 10);


/**
 * Move coupons from top to bottom on checkout page 
 */
function sst_change_coupon_location()
{
  remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
  add_action('woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form');
}
add_action('init', 'sst_change_coupon_location');


function sst_back_to_cart()
{ ?>

  <a href="<?php echo wc_get_cart_url(); ?>" class="button back-to-cart-btn">
    Tillbaka till varukorgen
  </a>

<?php };
add_action('woocommerce_review_order_before_payment', 'sst_back_to_cart', 99);
