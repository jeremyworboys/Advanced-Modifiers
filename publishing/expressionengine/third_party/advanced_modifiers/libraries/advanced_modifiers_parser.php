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
        if (!isset($product['modifiers'])) {
            return $product;
        }

        $cart = isset($product['mod_values']);
        $this->_add_advanced_modifiers($product, $cart);

        $price = $this->_calculate_price($product);

        $tax_rate = $product['tax_exempt'] ? 0 : ($product['price_inc_tax_val'] / $product['price_val']) - 1;

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

        return $product;
    }


    /**
     * Add Advanced Modifiers
     *
     * This method retrieves and attaches the advanced modifiers to a product
     * by reference.
     *
     * @param  &array    The product to add the modifiers to.
     * @param  boolean   Whether the product is in the cart.
     */
    private function _add_advanced_modifiers(&$product, $cart)
    {
        $advanced_modifiers = $this->EE->advanced_modifiers_model->get_advanced_modifiers($product['entry_id']);

        if ($cart) {
            foreach ($product['modifiers'] as &$mod) {
                if (!in_array($mod['modifier_type'], $this->modifier_types)) { continue; }
                $mod['options'] = array();
                foreach ($advanced_modifiers as $opt_id => $adv_mod) {
                    if ($advanced_modifiers[$opt_id]) {
                        $mod['options'][$opt_id]['adv_mod'] = $advanced_modifiers[$opt_id];
                    }
                    elseif ($mod['price_mod_val']) {
                        $mod['options'][$opt_id]['adv_mod'] = (string)$opt['opt_price_mod_val'];
                    }
                    else {
                        $mod['options'][$opt_id]['adv_mod'] = '0';
                    }
                }
            }
        }
        else {
            foreach ($product['modifiers'] as $mod_id => &$mod) {
                $first_opt = true;
                $mod['modifier_name'] = $mod['mod_name'];
                $mod['modifier_type'] = $mod['mod_type'];
                if (!in_array($mod['mod_type'], $this->modifier_types)) { continue; }
                foreach ($mod['options'] as $opt_id => &$opt) {
                    if ($first_opt) {
                        $mod['option_id'] = $opt_id;
                        $mod['modifier_value'] = $opt['opt_name'];
                        $first_opt = false;
                    }
                    if (isset($advanced_modifiers[$opt_id])) {
                        if ($advanced_modifiers[$opt_id]) {
                            $opt['adv_mod'] = $advanced_modifiers[$opt_id];
                        }
                        elseif ($opt['opt_price_mod_val']) {
                            $opt['adv_mod'] = (string)$opt['opt_price_mod_val'];
                        }
                        else {
                            $opt['adv_mod'] = '0';
                        }
                    }
                }
            }
        }
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
    private function _calculate_price($product)
    {
        $price = $product['price_val'];
        $mod_map = array();
        foreach ($product['modifiers'] as $mod_id => $mod) {
            if (!in_array($mod['modifier_type'], $this->modifier_types)) { continue; }
            $opt_id = $mod['option_id'];
            $option = $mod['options'][$opt_id];
            if ($option) {
                $s_name = $this->_standardize_name($mod['modifier_name']);
                $mod_map[$s_name] = $mod['modifier_value'];
            }
        }
        foreach ($product['modifiers'] as $mod_id => $mod) {
            if (!in_array($mod['modifier_type'], $this->modifier_types)) { continue; }
            $opt_id = $mod['option_id'];
            $option = $mod['options'][$opt_id];
            if ($option) {
                $price += $this->_evaluate_advanced_modifier($option['adv_mod'], $mod_map);
            }
        }

        return $price;
    }


    /**
     * Standardize Name
     *
     * This method takes the name of a modifier and turns it into a string
     * usable as an identifier.
     *
     * @param  string    The modifier's original name.
     * @return string    The standardized name.
     */
    private function _standardize_name($name)
    {
        return preg_replace('/[^A-Za-z0-9-]+/', '_', strtolower($name));
    }


    /**
     * Evaluate Advanced Modifier
     *
     * This method evaluates an advanced modifier expression and returns a price
     * value.
     *
     * @param  string    The advanced modifier to evaluate.
     * @param  array     A map of modifier_name => modifier_value.
     * @return float     The evaluated price of the advanced modifier.
     */
    private function _evaluate_advanced_modifier($adv_mod, $mod_map)
    {
        $var_names = array_keys($mod_map);
        $var_vals  = array_map(
            create_function('$value', 'return "\"$value\"";'),
            array_values($mod_map));
        $adv_mod = str_replace($var_names, $var_vals, $adv_mod);
        $adv_mod = (substr($adv_mod, -1) === ';') ? $adv_mod : $adv_mod.';';

        return eval('return '.$adv_mod);
    }
}
// END CLASS
