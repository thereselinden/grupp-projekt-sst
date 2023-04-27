<?php

/* 
  Plugin Name: CPT_SST
 */

?>

<?php add_action('init', 'SST_register_CPT_store') ?>
<?php function SST_register_CPT_store()
{
  // $labels = [
  //   'name' => 'Butiker',
  //   'single_name' => 'Butik'
  // ];

  $store_args = array(
    'public' => true,
    'label' => 'Butiker',
    'show_in_rest' => true,
    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    'has_archive' => true, //säg att vi har en arkiv sida
  );
  register_post_type('butik', $store_args);
}
?>