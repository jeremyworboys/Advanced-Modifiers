<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced Modifiers Extension class
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com
 * @copyright  Copyright (c) 2012, Jeremy Worboys
 */
class Advanced_modifiers_ft extends EE_Fieldtype
{

    public $info = array (
            'name'    => 'Advanced Modifiers',
            'version' => '1.0'
        );


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->EE->load->model('advanced_modifiers_model');
    }


    /**
     * Display Field
     *
     * This method runs when displaying the field on the publish page in the CP.
     *
     * @param  array     The data previously entered into this field.
     *
     * @return string    The HTML output to be displayed for this field.
     */
    public function display_field($field_data)
    {
        $data = array();

        $entry_id = (int)$this->EE->input->get('entry_id');

        $product = $this->EE->advanced_modifiers_model->find_product_by_id($entry_id);
        if (!empty($product)) {
            $data['product'] = $product;
        }

        return $this->EE->load->view('field', $data, TRUE);
    }
}
// END CLASS
