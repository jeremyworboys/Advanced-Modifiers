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

    private $mod_map;
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
     * Parse
     *
     * This method interprets the advanced modifier and returns a product with
     * all values updated.
     *
     * @param  array     The product to parse.
     * @return float     The value to modify the item price by.
     */
    public function parse($product)
    {
        if (!isset($product['modifiers'])) {
            return $product;
        }

        $interpret = $this->_add_advanced_modifiers($product);

        if ($interpret) {
            return $product;
        }

        $this->_update_modifier_map($product);
        $this->_update_modifiers($product);

        $product['regular_price_val'] = (float)$product['regular_price'];
        $product['regular_price']     = store_format_currency($product['regular_price_val']);
        $product['sale_price_val']    = (float)$product['sale_price'];
        $product['sale_price']        = store_format_currency($product['sale_price_val']);
        $product['handling_val']      = (float)$product['handling'];
        $product['handling']          = store_format_currency($product['handling_val']);
        $product['price_val']         = $product['regular_price_val'];
        $product['price']             = $product['regular_price'];
        $product['free_shipping']     = $product['free_shipping'] == 'y';
        $product['tax_exempt']        = $product['tax_exempt'] == 'y';
        $product['on_sale']           = FALSE;

        return $product;
    }


    /**
     * Add Advanced Modifiers
     *
     * This method retrieves and attaches the advanced modifiers to a product
     * by reference.
     *
     * @param  &array    The product to add the modifiers to.
     * @return bool      Whether the product needs to be interpreted.
     */
    private function _add_advanced_modifiers(&$product)
    {
        $interpret = false;
        $advanced_modifiers = $this->EE->advanced_modifiers_model->get_advanced_modifiers($product['entry_id']);

        foreach ($product['modifiers'] as &$mod) {
            if (!in_array($mod['mod_type'], $this->modifier_types)) { continue; }
            $interpret = true;
            foreach ($mod['options'] as $opt_id => &$opt) {
                if (isset($advanced_modifiers[$opt_id])) {
                    $opt['adv_mod'] = $advanced_modifiers[$opt_id];
                }
            }
        }

        return $interpret;
    }


    /**
     * Update Modifier Map
     *
     * This method updates the modifier map used to find modifier ID's and other
     * details by name.
     *
     * @param  array     The product to update the modifier map from.
     */
    private function _update_modifier_map($product)
    {
        foreach ($product['modifiers'] as $index => $mod) {
            $map = new stdClass();

            $map->id    = $mod['product_mod_id'];
            $map->name  = $mod['mod_name'];
            $map->index = $index;

            $s_name = $this->_standardize_name($mod['mod_name']);
            $this->mod_map->$s_name = $map;
        }
    }


    /**
     * Update Modifiers
     *
     * This method uses the advanced modifiers to update the value applies to
     * the standard modifiers.
     *
     * @param  &array    The product to update the modifiers on.
     */
    private function _update_modifiers(&$product)
    {
        foreach ($product['modifiers'] as &$mod) {
            if (!in_array($mod['mod_type'], $this->modifier_types)) { continue; }
            // THIS IS WHERE SOMETHING NEEDS TO HAPPEN
        }
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
}
// END CLASS
