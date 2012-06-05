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
    }


    /**
     * Display Field
     *
     * This method runs when displaying the field on the publish page in the CP.
     *
     * @param  array     The data previously entered into this field.
     * @return string    The HTML output to be displayed for this field.
     */
    public function display_field($field_data)
    {
        $this->EE->load->model('advanced_modifiers_model');

        $entry_id = (int)$this->EE->input->get('entry_id');

        $data = array();
        $data['advanced_modifiers'] = unserialize(htmlspecialchars_decode($field_data));

        $product = $this->EE->advanced_modifiers_model->find_product_by_id($entry_id);
        if (!empty($product)) {
            $data['product'] = $product;
        }

        return $this->EE->load->view('field', $data, TRUE);
    }


    /**
     * Prepare for Saving the Field
     *
     * This method runs when displaying the field on the publish page in the CP.
     *
     * @param  array     The data entered into this field.
     * @return string    The data to be stored in the database.
     */
    public function save($data)
    {
        $field_data = $this->EE->input->post('advanced_modifiers_field', TRUE);
        return serialize($field_data);
    }
}
// END CLASS
