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
     * Display Field
     *
     * This method runs when displaying the field on the publish page in the CP.
     *
     * @param  array     The data previously entered into this field.
     *
     * @return strong    The HTML output to be displayed for this field.
     */
    public function display_field($field_data)
    {
        return $this->EE->load->view('field', $field_data, TRUE);
    }
}
// END CLASS
