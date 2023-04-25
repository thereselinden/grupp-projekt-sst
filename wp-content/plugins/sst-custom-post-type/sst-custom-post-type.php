<?php

/* 
  Plugin Name: CPT_SST
 */

?>

<?php add_action('init', 'SST_register_CPT_store') ?>


<?php function SST_register_CPT_store()
{
  $store_args = array(
    'public' => true,
    'label' => 'Butik',
    'show_in_rest' => true,
    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
  );
  register_post_type('store', $store_args);
}
?>