<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced Modifiers Parser Library class
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com
 * @copyright  Copyright (c) 2012, Jeremy Worboys
 */
class Advanced_modifiers_parser
{

    private $modifier_types = array('var', 'var_single_sku');

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EE =& get_instance();

        $this->EE->load->model('advanced_modifiers_model');
    }


    /**
     * Update Product
     *
     * This method updates the prices and display values for the product.
     *
     * @param  array     The product to update.
     * @return array     The product after bing updated.
     */
    public function update_product($product)
    {
        // If the product doesn't have any modifiers end early
        if (!isset($product['modifiers'])) {
            return $product;
        }

        // Attach advanced modifiers to the product
        $advanced_modifiers = $this->EE->advanced_modifiers_model->get_advanced_modifiers($product['entry_id']);
        $product['stock'][0]['advanced_modifiers'] = $advanced_modifiers;

        // Calculate price with advanced modifiers
        $price = $this->calculate_price($product);

        // Adjust price values on product object
        $product = $this->update_prices($product, $price);

        // Return the product
        return $product;
    }


    /**
     * Calculate Price
     *
     * This method uses the advanced modifiers to calculate the price of the
     * product based on selected modifiers.
     *
     * @param  array     The product whose price to calculate.
     * @return float     The calculated price of the product.
     */
    private function calculate_price($product)
    {
        // Get starting price
        $price = $product['price_val'];

        $p = array();
        // Find the advanced modifier id. $product['mod_values'] is only set if
        //  the product is being added to the cart.
        if (isset($product['mod_values'])) {
            // Product is being added to the cart; use the values selected by
            //  the customer
            foreach ($product['modifiers'] as $mod) {
                $p[] = $mod['option_id'];
            }
        }
        else {
            // The product is being displayed, use the first options available
            foreach ($product['modifiers'] as $mod) {
                $p[] = $mod['options'][key($mod['options'])]['product_opt_id'];
            }
        }

        // Add the value of the advanced modifier to the starting price
        $id = implode('-', $p);
        if (isset($product['stock'][0]['advanced_modifiers'][$id])) {
            $price += $product['stock'][0]['advanced_modifiers'][$id];
        }

        // Return the final price.
        return $price;
    }


    /**
     * Update Prices
     *
     * This method updates all of the price related values on a product with a
     * new price.
     *
     * @param  array     The product whose prices to update.
     * @param  float     The price to update the product with.
     * @return array     The updated product.
     */
    private function update_prices($product, $price)
    {
        // Calculate the tax rate as it is not stored in the product object
        $tax_rate = $product['tax_exempt'] ? 0 : ($product['price_inc_tax_val'] / $product['price_val']) - 1;

        // Update prices
        $product['price_val']                 = $price;
        $product['price_inc_tax_val']         = store_round_currency($product['price_val'] * (1 + $tax_rate));
        $product['price_inc_tax']             = store_format_currency($product['price_inc_tax_val']);
        $product['regular_price_inc_tax_val'] = store_round_currency($product['regular_price_val'] * (1 + $tax_rate));
        $product['regular_price_inc_tax']     = store_format_currency($product['regular_price_inc_tax_val']);
        $product['sale_price_inc_tax_val']    = store_round_currency($product['sale_price_val'] * (1 + $tax_rate));
        $product['sale_price_inc_tax']        = store_format_currency($product['sale_price_inc_tax_val']);

        $product['you_save_val']              = $product['regular_price_val'] - $product['price_val'];
        $product['you_save_inc_tax_val']      = $product['regular_price_inc_tax_val'] - $product['price_inc_tax_val'];
        $product['you_save']                  = store_format_currency($product['you_save_val']);
        $product['you_save_inc_tax']          = store_format_currency($product['you_save_inc_tax_val']);
        $product['you_save_percent']          = empty($product['regular_price_val']) ? 0 : round(($product['regular_price_val'] - $product['price_val']) / $product['regular_price_val'] * 100);

        // Return updated product
        return $product;
    }
}
// END CLASS
