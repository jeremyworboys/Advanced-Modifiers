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

    private $hooks          = array(
                                'store_process_product_tax',
                                'template_post_parse'
                            );


    /**
     * Constructor
     *
     * @param  mixed     Settings array or empty string if none exist.
     */
    public function __construct($settings='')
    {
        $this->EE =& get_instance();

        $this->EE->load->library('advanced_modifiers_parser');

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
            ->where('class', __CLASS__);
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
        return $this->EE->advanced_modifiers_parser->parse($product);
    }


    /**
     * Template Post Parse
     *
     * Tests to see if there is a product on this page and if so injects the
     * JavaScript needed to override the updating of live classes.
     *
     * @param  string    The template string after template tags have been
     *                   parsed.
     * @param  bool      Whether or not the current template is an embed.
     * @param  int       The site_id of the current template.
     * @return string    The template after modification.
     */
    public function template_post_parse($template, $sub, $site_id)
    {
        return $template;
    }
}
// END CLASS
