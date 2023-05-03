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
        // public $min_amount = 0;
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
          $this->cost_weight        = $this->get_option('cost_weight', 0);
          $this->cost_distance      = $this->get_option('cost_distance', 0);
          //$this->min_amount         = $this->get_option('min_amount', 0);
          $this->tax_status         = $this->get_option('tax_status');
          $this->type               = $this->get_option('type', 'class');



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
            // 'title_free' => array(
            //   'title' => __('Free Shipping Title', 'woocommerce'),
            //   'type' => 'text',
            //   'description' => __('The title which the user sees when free shipping amount is reached.', 'woocommerce'),
            //   'default' => __('Free shipping', 'woocommerce'),
            //   'desc_tip'    => true,
            // ),
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
            'cost_weight'       => array(
              'title'       => __('Cost Weight', 'woocommerce'),
              'type'        => 'number',
              'placeholder' => 0,
              'description' => __('Optional weight cost per kg', 'woocommerce'),
              'default'     => 0,
              'desc_tip'    => true,
            ),
            'cost_distance'       => array(
              'title'       => __('Cost Distance', 'woocommerce'),
              'type'        => 'number',
              'placeholder' => 0,
              'description' => __('Optional distance cost per km.', 'woocommerce'),
              'default'     => 0,
              'desc_tip'    => true,
            ),
            // 'min_amount' => array(
            //   'title' => __('Minimum amount free shipping', 'woocommerce'),
            //   'type' => 'number',
            //   'description' => __('This controls the minimum amount for free shipping', 'woocommerce'),
            //   'default' => '0'
            // ),
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
              );
            }

            $this->instance_form_fields['no_class_cost'] = array(
              'title'             => __('No shipping class cost', 'woocommerce'),
              'type'              => 'text',
              'placeholder'       => __('N/A', 'woocommerce'),
              'description'       => $cost_desc,
              'default'           => '',
              'desc_tip'          => true,
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

          // Totalvikt kundvagnen
          $cart_total_weight =  WC()->cart->get_cart_contents_weight();
          // Hämta kostnad per kg från admin
          $weight_km_cost = $this->get_option('cost_weight');
          // Extra kostnad för vikten 
          $weight_extra_cost = $cart_total_weight * $weight_km_cost;
          // Uppdatera kostnaden genom att addera kostnad för vikt
          $rate['cost'] = $cost + $weight_extra_cost;

          //Koordinater från kunden som anger informationen i varukorgen 
          $city = $package['destination']['city'];
          $zip = $package['destination']['postcode'];
          $country = $package['destination']['country'];
          $api_key = 'AIzaSyAgPkfhYPLSbfysdE1B6uZYZ9vED89m-Es';
          $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $zip . '+' . $city . '+' . $country . '&key=' . $api_key;
          $to_json_data = file_get_contents($url);

          // Decode JSON data into PHP array
          $response_data = json_decode($to_json_data);

          // Kundens koordinater
          $to_lat = $response_data->results[0]->geometry->location->lat;
          $to_lon = $response_data->results[0]->geometry->location->lng;


          //Koordinater för huvudkontoret 
          $store_city = get_option('woocommerce_store_city');
          $store_postcode = get_option('woocommerce_store_postcode');
          $store_country = get_option('woocommerce_default_country');
          $address =  $store_postcode . '+' . $store_city . '+' . $store_country;
          $storeUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $api_key;

          $from_json_data = file_get_contents($storeUrl);

          // Decode JSON data into PHP array
          $response_data_store = json_decode($from_json_data);

          // Huvudbutikens koordinater (Woocommerce -> Inställningar -> Allmänt addressen)
          $from_lat = $response_data_store->results[0]->geometry->location->lat;
          $from_lon = $response_data_store->results[0]->geometry->location->lng;

          // Räkna ut distans i km med funktioen distance()
          $distance = $this->distance($to_lat, $to_lon, $from_lat, $from_lon, 'K');
          // Extra kostnad för distance per km från admin 
          $distance_km_cost = $this->get_option('cost_distance');
          // Extra kostnad att betala baserat på distancen från huvudkontoret 
          $distance_extra_cost = round($distance * $distance_km_cost);
          // Uppdatera kostnad med diance kostnad
          $rate['cost'] = $cost +  $distance_extra_cost;


          // $total = WC()->cart->get_displayed_subtotal();
          // $cost = $total > $this->min_amount ? 0 : $this->get_option('cost');
          // $label = $cost === 0 ? __($this->title_free, "Woocommerce") : __($this->title, "Woocommerce");


          $rate['label'] = 'Distance:' . $distance_extra_cost . 'Weight:' . $weight_extra_cost;
          //$rate['label'] = $label; // TO PRINT DATA TO DOM USED LABEL 

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

        // https://www.geodatasource.com/developers/php
        public function distance($lat1, $lon1, $lat2, $lon2, $unit)
        {
          if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
          } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
              return ($miles * 1.609344);
            } else if ($unit == "N") {
              return ($miles * 0.8684);
            } else {
              return $miles;
            }
          }
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
