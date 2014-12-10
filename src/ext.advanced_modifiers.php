<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require PATH_THIRD.'advanced_modifiers/config.php';

/**
 * Advanced Modifiers
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com/add-ons/advanced-modifiers/
 * @copyright  Copyright (c) 2012 Jeremy Worboys
 * @license    Licensed under the ☺ license.
 */
class Advanced_modifiers_ext {

    public $name            = ADVANCED_MODIFIERS_NAME;
    public $version         = ADVANCED_MODIFIERS_VERSION;
    public $description     = 'Extends the way you can define price modifiers in Exp:resso\'s Store Module.';
    public $settings_exist  = 'n';
    public $docs_url        = 'http://complexcompulsions.com/add-ons/advanced-modifiers/';

    protected $settings     = array();

    private $hooks          = array(
                                'store_process_product_tax',
                                'template_post_parse'
                            );


    /**
     * Constructor
     *
     * @param mixed Settings array or empty string if none exist.
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
     */
    public function activate_extension()
    {
        $this->settings = array();

        foreach ($this->hooks as $hook) {
            $this->EE->db->insert('extensions', array(
                'class'     => __CLASS__,
                'method'    => $hook,
                'hook'      => $hook,
                'settings'  => serialize($this->settings),
                'priority'  => 10,
                'version'   => $this->version,
                'enabled'   => 'y'
            ));
        }
    }


    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     */
    public function disable_extension()
    {
        $this->EE->db
            ->where('class', __CLASS__)
            ->delete('extensions');
    }


    /**
     * Store Process Product Tax
     *
     * This method is run after the product price values are generated. This is
     * where we inject our code before the product is displayed to the user.
     *
     * @param  array     The product that we are modifying.
     * @return array     The modified product.
     */
    public function store_process_product_tax($product)
    {
        $this->EE->load->library('advanced_modifiers_parser');
        $product = $this->EE->advanced_modifiers_parser->update_product($product);
        return $product;
    }


    /**
     * Template Post Parse
     *
     * Modifies template after tag parsing to swap out the standard store
     * JavaScript for one that handles advanced modifiers.
     *
     * @param  string    The template string after template tags have been
     *                   parsed.
     * @param  boolean   Whether or not the current template contains an embed.
     * @param  string    The site_id of the current template
     * @return string    The template string after modification.
     */
    public function template_post_parse($final_template, $sub, $site_id)
    {
        $time = filemtime(APPPATH . '/../../themes/third_party/advanced_modifiers/advanced_modifiers.js');

        $final_template = str_ireplace('store/store.min.js', "advanced_modifiers/advanced_modifiers.{$time}.js", $final_template);
        $final_template = str_ireplace('store/store.js', "advanced_modifiers/advanced_modifiers.{$time}.js", $final_template);

        return $final_template;
    }
}
// END CLASS
