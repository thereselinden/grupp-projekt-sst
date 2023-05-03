<?php

/*
Plugin Name: SST Shipping Method
Description: SST shipping method plugin
Version: 1.0.0
Author: SST
*/

/**
 * Check if WooCommerce plugin is active
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function sst_shipping_method()
  {
    if (!class_exists('WC_SST_SHIPPING_METHOD')) {
      class WC_SST_SHIPPING_METHOD extends WC_Shipping_Method
      {
        public $min_amount = 0;
        public $title_free;
        public $cost;

        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct($instance_id = 0)
        {
          $this->id                 = 'sst_shipping_method'; // id for shipping method
          $this->instance_id        = absint($instance_id);
          $this->method_title       = __('SST Shipping Method'); // title shown in admin
          $this->method_description = __('Description of SST shipping method'); // description shown in admin
          $this->supports           = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
            // 'settings', // Slår på en extra sida i shipping fliken 
          );

          $this->init();
        }


        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init()
        {
          // Load the settings API
          $this->init_instance_form_settings();
          $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

          // user defined values goes here, not in construct 
          $this->enabled            = $this->get_option('enabled');
          $this->title              = $this->get_option('title');
          //$this->cost = $this->get_option('cost');
          $this->title_free         = $this->get_option('title_free');
          $this->cost               = $this->get_option('cost', 0);
          $this->min_amount         = $this->get_option('min_amount', 0);
          $this->tax_status         = $this->get_option('tax_status');
          $this->type                 = $this->get_option('type', 'class');


          // Save settings in admin if you have any defined
          add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Initialise Gateway Settings Instance Form Fields
         */
        function init_instance_form_settings()
        {
          $this->instance_form_fields = array(
            'title' => array(
              'title' => __('Shipping Title', 'woocommerce'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
              'default' => __('Express', 'woocommerce'),
              'desc_tip'    => true, // gives question mark with description text on hover next to title admin view
            ),
            'title_free' => array(
              'title' => __('Free Shipping Title', 'woocommerce'),
              'type' => 'text',
              'description' => __('The title which the user sees when free shipping amount is reached.', 'woocommerce'),
              'default' => __('Free shipping', 'woocommerce'),
              'desc_tip'    => true,
            ),
            'description' => array(
              'title' => __('Description', 'woocommerce'),
              'type' => 'textarea',
              'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
              'default' => __("Express Delivery", 'woocommerce'),
              'desc_tip' => true // gives qustion mark next to decription and hides description if not hover over questionmark
            ),
            'tax_status' => array(
              'title'   => __('Tax status', 'woocommerce'),
              'type'    => 'select',
              'class'   => 'wc-enhanced-select',
              'default' => 'taxable',
              'options' => array(
                'taxable' => __('Taxable', 'woocommerce'),
                'none'    => _x('None', 'Tax status', 'woocommerce'),
              ),
            ),
            'cost'       => array(
              'title'       => __('Cost', 'woocommerce'),
              'type'        => 'number',
              'placeholder' => 0,
              'description' => __('Optional cost for shipping method.', 'woocommerce'),
              'default'     => 0,
              'desc_tip'    => true,
            ),
            'min_amount' => array(
              'title' => __('Minimum amount free shipping', 'woocommerce'),
              'type' => 'number',
              'description' => __('This controls the minimum amount for free shipping', 'woocommerce'),
              'default' => '0'
            ),
          );

          $shipping_classes = WC()->shipping()->get_shipping_classes();
          $cost_desc = 'Enter cost for this shipping class';



          if (!empty($shipping_classes)) {
            $this->instance_form_fields['class_costs'] = array(
              'title'       => __('Shipping class costs', 'woocommerce'),
              'type'        => 'title',
              'default'     => '',
              /* translators: %s: URL for link. */
              'description' => sprintf(__('These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=shipping&section=classes')),
            );
            foreach ($shipping_classes as $shipping_class) {
              if (!isset($shipping_class->term_id)) {
                continue;
              }
              $this->instance_form_fields['class_cost_' . $shipping_class->term_id] = array(
                /* translators: %s: shipping class name */
                'title'             => sprintf(__('"%s" shipping class cost', 'woocommerce'), esc_html($shipping_class->name)),
                'type'              => 'text',
                'placeholder'       => __('N/A', 'woocommerce'),
                'description'       => $cost_desc,
                'default'           => $this->get_option('class_cost_' . $shipping_class->slug), // Before 2.5.0, we used slug here which caused issues with long setting names.
                'desc_tip'          => true,
                //'sanitize_callback' => array($this, 'sanitize_cost'),
              );
            }

            $this->instance_form_fields['no_class_cost'] = array(
              'title'             => __('No shipping class cost', 'woocommerce'),
              'type'              => 'text',
              'placeholder'       => __('N/A', 'woocommerce'),
              'description'       => $cost_desc,
              'default'           => '',
              'desc_tip'          => true,
              // 'sanitize_callback' => array($this, 'sanitize_cost'),
            );

            $this->instance_form_fields['type'] = array(
              'title'   => __('Calculation type', 'woocommerce'),
              'type'    => 'select',
              'class'   => 'wc-enhanced-select',
              'default' => 'class',
              'options' => array(
                'class' => __('Per class: Charge shipping for each shipping class individually', 'woocommerce'),
                'order' => __('Per order: Charge shipping for the most expensive shipping class', 'woocommerce'),
              ),
            );
          }
        } // End instance_init_form_fields()


        /**
         * calculate_shipping function.
         *
         * @access public
         * @param array $package 
         * @return void
         */
        public function calculate_shipping($package = array())
        {
          $rate = array(
            'label' => $this->title,
            'cost' => 0,
            'package' => $package,
          );

          // Calculate the costs.
          // $has_costs = false; // True when a cost is set. False if all costs are blank strings.
          $cost      = $this->get_option('cost');

          if ('' !== $cost) {
            // $has_costs    = true;
            $rate['cost'] =  $cost;
          }

          // TODO Hämta fraktklass och sätt pris på frakt baserat på om produkt har klassen eller ej
          $shipping_classes = WC()->shipping()->get_shipping_classes();

          if (!empty($shipping_classes)) {
            $found_shipping_classes = $this->find_shipping_classes($package);
            $highest_class_cost     = 0;

            foreach ($found_shipping_classes as $shipping_class => $products) {
              // Also handles BW compatibility when slugs were used instead of ids.
              $shipping_class_term = get_term_by('slug', $shipping_class, 'product_shipping_class');
              $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option('class_cost_' . $shipping_class_term->term_id, $this->get_option('class_cost_' . $shipping_class, '')) : $this->get_option('no_class_cost', '');

              if ('' === $class_cost_string) {
                continue;
              }

              //$has_costs  = true;
              $class_cost =  $class_cost_string;

              if ('class' === $this->type) {
                $rate['cost'] += $class_cost;
              } else {
                $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
              }
            }

            if ('order' === $this->type && $highest_class_cost) {
              $rate['cost'] += $highest_class_cost;
            }
          }

          // TODO Hämta varukorgens vikt för att indexera ett pris baserat på totala vikten
          // Get cart total weight
          // $total_weight =  WC()->cart->get_cart_contents_weight . ' ' . get_option('woocommerce_weight_unit');
          // var_dump($total_weight);
          // $total_weight =  WC()->cart->get_cart_contents_weight();
          // print_r($total_weight);


          // define the differents costs based on weight
          // $weight_cost_1 = 1.1; // Up to 5 Kg
          // $weight_cost_2 = 1.15; // Above 5 Kg and below 10 kg
          // $weight_cost_3 = 1.3; // Above 10 kg

          // if ($total_weight < 5) {
          //   $rate['cost'] += $cost * 1.1;
          // }

          // if($total_weight < 5 { $cost * $weight_cost_1})
          // if($total_weight > 5 || $total_weight < 10 { $cost * $weight_cost_2})
          // if($total_weight > 10 { $cost * $weight_cost_3})

          // TODO Hämta fraktzon kund har och räkna ut pris baserat på indexerat avstånd 
          // Get shipping zone name
          //$shipping_zone = WC_Shipping_Zones::get_zone_matching_package($package);
          //$zone = $shipping_zone->get_zone_name();
          // print_r($zone);

          // if(zone === 'syd sverige') { $cost = $cost * 1.1}
          // if(zone === 'mellan sverige') { $cost = $cost * 1.15}
          // if(zone === 'nord sverige') { $cost = $cost * 1.4}



          $total = WC()->cart->get_displayed_subtotal();
          $cost = $total > $this->min_amount ? 0 : $this->get_option('cost');
          $label = $cost === 0 ? __($this->title_free, "Woocommerce") : __($this->title, "Woocommerce");




          $this->add_rate($rate);
        }

        /**
         * Finds and returns shipping classes and the products with said class.
         *
         * @param mixed $package Package of items from cart.
         * @return array
         */
        public function find_shipping_classes($package)
        {
          $found_shipping_classes = array();


          foreach ($package['contents'] as $item_id => $values) {;

            if ($values['data']->needs_shipping()) {
              $found_class = $values['data']->get_shipping_class();

              if (!isset($found_shipping_classes[$found_class])) {
                $found_shipping_classes[$found_class] = array();
              }

              $found_shipping_classes[$found_class][$item_id] = $values;
            }
          }

          return $found_shipping_classes;
        }

        public function get_cart_weight()
        {
          $cart_weight = 0;

          foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            if ($product->has_weight()) {
              $cart_weight += floatval($product->get_weight() * $cart_item['quantity']);
            }
          }

          return $cart_weight;
        }
      }
    }
  }




  add_action('woocommerce_shipping_init', 'sst_shipping_method');


  function add_sst_shipping_method($methods)
  {

    $methods['sst_shipping_method'] = 'WC_SST_SHIPPING_METHOD';
    return $methods;
  }

  add_filter('woocommerce_shipping_methods', 'add_sst_shipping_method');
}
