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
    }


    /**
     * Parse
     *
     * This method interprets the advanced modifier and returns a value to be
     * applied to the product
     *
     * @param  string    The modifier to parse.
     * @param  array     The current details of the product.
     * @return float     The value to modify the item price by.
     */
    public function parse($product) //$modifier, $selected_details)
    {
        return $product;
    }
}
// END CLASS
