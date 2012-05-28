<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced Modifiers for Exp:resso's Store
 * Copyright (c) 2012 Jeremy Worboys <jeremy@complexcompulsions.com>
 */

Class Advanced_modifiers_ext {

    public $name            = 'Advanced Modifiers';
    public $version         = '1.0';
    public $description     = 'Extends the way you can define price modifiers in Exp:resso\'s Store Module.';
    public $settings_exist  = 'n';
    public $docs_url        = ''; // 'http://expressionengine.com/user_guide/';

    protected $settings        = array();


    /**
     * Constructor
     *
     * @param  mixed   Settings array or empty string if none exist.
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
     * @see http://codeigniter.com/user_guide/database/index.html for more
     * information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        $this->settings = array();

        $data = array(
            'class'     => __CLASS__,
            'method'    => 'apply_modifier_price',
            'hook'      => 'store_cart_item_update_end',
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
}
// END CLASS
