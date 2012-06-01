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

        $this->_add_modifiers($product);

        // We need to re-evaluate the product's price with these new modifiers

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
     * Add Modifiers
     *
     * This method retrieves and attaches the advanced modifiers to a product
     * by reference.
     *
     * @param  &array    The product to add the modifiers to.
     */
    private function _add_modifiers(&$product)
    {
        $advanced_modifiers = $this->EE->advanced_modifiers_model->get_advanced_modifiers($product['entry_id']);

        foreach ($product['modifiers'] as &$mod) {
            if (!in_array($mod['mod_type'], array('var', 'var_single_sku')) { continue; }
            foreach ($mod['options'] as $opt_id => &$opt) {
                if (isset($advanced_modifiers[$opt_id])) {
                    $opt['adv_mod'] = $advanced_modifiers[$opt_id];
                }
            }
        }
    }
}
// END CLASS
