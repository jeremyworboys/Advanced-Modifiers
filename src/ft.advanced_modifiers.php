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
class Advanced_modifiers_ft extends EE_Fieldtype {

    public $info = array(
        'name'    = ADVANCED_MODIFIERS_NAME,
        'version' = ADVANCED_MODIFIERS_VERSION
    )


    /**
     * Replace Tag
     *
     * This method replaces the field tag on the front-end.
     *
     * @param  array  The field data (or prepped data, if using pre_process).
     * @param  array  The field parameters (if any)
     * @param  string The data between tag (for tag pairs)
     * @return string The text/HTML to replace the tag.
     */
    public function replace_tag($data, $params=array(), $tagdata=FALSE)
    {
        return '';
    }


    /**
     * Display Field
     *
     * This method runs when displaying the field on the publish page in the CP.
     *
     * @param  array  The data previously entered into this field.
     * @return string The HTML output to be displayed for this field.
     */
    public function display_field($field_data)
    {
        $this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.URL_THIRD_THEMES.'advanced_modifiers/advanced_modifiers.css" />');

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
     * This method prepares the data to be saved to the entries table in the
     * database.
     *
     * @param  array  The data entered into this field.
     * @return string The data to be stored in the database.
     */
    public function save($data)
    {
        $field_data = $this->EE->input->post('advanced_modifiers', TRUE);

        if (is_array($field_data)) {
            foreach ($field_data as $key => $value) {
                $field_data[$key] = floatval($value);
            }
        }

        return serialize($field_data);
    }
}
// END CLASS
