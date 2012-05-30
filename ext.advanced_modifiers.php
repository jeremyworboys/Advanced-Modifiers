<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced Modifiers Extension class
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com
 * @copyright  Copyright (c) 2012, Jeremy Worboys
 */
class Advanced_modifiers_ext
{

    public $name            = 'Advanced Modifiers';
    public $version         = '1.0';
    public $description     = 'Extends the way you can define price modifiers in Exp:resso\'s Store Module.';
    public $settings_exist  = 'n';
    public $docs_url        = '';

    protected $settings     = array();


    /**
     * Constructor
     *
     * @param  mixed     Settings array or empty string if none exist.
     */
    public function __construct($settings='')
    {
        $this->EE =& get_instance();

        $this->settings = $settings;
    }


    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see    http://codeigniter.com/user_guide/database/index.html for more
     *         information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        $this->settings = array();

        $data = array(
            'class'     => __CLASS__,
            'method'    => 'store_process_product_tax',
            'hook'      => 'store_process_product_tax',
            'settings'  => serialize($this->settings),
            'priority'  => 10,
            'version'   => $this->version,
            'enabled'   => 'y'
        );

        $this->EE->db->insert('extensions', $data);
    }


    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    public function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }


    /**
     * Store Process Product Tax
     *
     * This method is run after the product price values are generated. This is
     * where we inject our code before the product is displayed to the user.
     *
     * @param  array     The product that we are modifying.
     *
     * @return array     The modified product.
     */
    public function store_process_product_tax($product)
    {
        return $product;
    }
}
// END CLASS
