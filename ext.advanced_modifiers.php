<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Advanced_modifiers_ext {

    var $name            = 'Advanced Modifiers';
    var $version         = '1.0';
    var $description     = 'Extends the way you can define price modifiers in Exp:resso\'s Store Module.';
    var $settings_exist  = 'n';
    var $docs_url        = ''; // 'http://expressionengine.com/user_guide/';

    var $settings        = array();


    /**
     * Constructor
     *
     * @param  mixed   Settings array or empty string if none exist.
     */
    function __construct($settings='')
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
    function activate_extension()
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
}
// END CLASS
