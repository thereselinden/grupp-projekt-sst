<?php

/*
 * Check if woocommerce plugin is active
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  add_action('storefront_before_header', 'sst_display_coupons');
} else {
  return;
}

function sst_display_coupons()
{
  echo 'Detta är en rabatt kupong';
}


function sst_remove_sidebar_product_pages()
{
  if (is_woocommerce() || is_checkout()) {
    remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
  }
}
add_action('get_header', 'sst_remove_sidebar_product_pages');
