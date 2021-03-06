<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Advanced Modifiers Model class
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com
 * @copyright  Copyright (c) 2012, Jeremy Worboys
 */
class Advanced_modifiers_model extends CI_Model
{

    /**
     * Find Product by ID
     *
     * This method accesses the store_products table and retrieves a product.
     *
     * @param  int       The entry ID of the product to retrieve.
     * @return mixed     The product or false if one is not found.
     */
    public function find_product_by_id($entry_id)
    {
        // store_products_model::find_by_id
        $product = $this->db->select('p.*, sum(s.stock_level) as total_stock')
            ->from('store_products p')
            ->join('channel_titles t', 'p.entry_id = t.entry_id')
            ->join('store_stock s', 's.entry_id = p.entry_id')
            ->where('p.entry_id', (int)$entry_id)
            ->group_by('p.entry_id')
            ->get()->row_array();

        if (empty($product)) {
            return false;
        }

        $product['modifiers'] = $this->get_product_modifiers($entry_id);

        return $product;
    }


    /**
     * Get Product Modifiers
     *
     * This method accesses the store_product_modifiers table and retrieves a
     * product's standard modifiers.
     *
     * @param  int       The entry ID of the product to retrieve.
     * @return array     The modifiers associated with the product.
     */
    public function get_product_modifiers($entry_id)
    {
        // store_products_model::get_product_modifiers
        $this->db->select('m.*, o.product_opt_id, o.opt_name, o.opt_price_mod, o.opt_order')
            ->from('store_product_modifiers m')
            ->join('store_product_options o', 'm.product_mod_id = o.product_mod_id', 'left')
            ->order_by('mod_order', 'asc')
            ->order_by('m.product_mod_id', 'asc')
            ->order_by('opt_order', 'asc')
            ->order_by('o.product_opt_id', 'asc')
            ->where('m.entry_id', (int)$entry_id);
        $query = $this->db->get()->result_array();

        // convert to multi dimensional array
        $result = array();
        foreach ($query as $row) {
            $mod_id = (int)$row['product_mod_id'];
            if ( ! isset($result[$mod_id])) {
                $result[$mod_id] = array(
                    'product_mod_id' => $mod_id,
                    'entry_id' => $row['entry_id'],
                    'mod_type' => $row['mod_type'],
                    'mod_name' => $row['mod_name'],
                    'mod_instructions' => $row['mod_instructions'],
                    'mod_order' => intval($row['mod_order']),
                    'options' => array()
                );
            }

            if ( ! empty($row['product_opt_id'])) {
                $opt_id = (int)$row['product_opt_id'];
                $opt_data = array(
                    'product_opt_id' => $opt_id,
                    'opt_name' => $row['opt_name'],
                    'opt_order' => $row['opt_order']
                );

                $opt_data['opt_price_mod_val'] = store_round_currency($row['opt_price_mod'], true);
                $opt_data['opt_price_mod'] = empty($opt_data['opt_price_mod_val']) ? '' : store_format_currency($opt_data['opt_price_mod_val'], TRUE);

                $result[$mod_id]['options'][$opt_id] = $opt_data;
            }
        }
        return $result;
    }


    /**
     * Get Advanced Modifiers
     *
     * This method gets the advanced modifiers for a product.
     *
     * @param  int       The entry ID of the product to retrieve.
     * @return array     The modifiers associated with the product.
     */
    public function get_advanced_modifiers($entry_id)
    {
        $field_id = $this->db
            ->select('field_id')
            ->from('channel_fields')
            ->where('field_type', 'advanced_modifiers')
            ->get()->row();

        if (empty($field_id)) { return ''; }

        $field_id = $field_id->field_id;

        $advanced_modifiers = $this->db
            ->select('field_id_'.$field_id)
            ->from('channel_data')
            ->where('entry_id', $entry_id)
            ->get()->row_array();

        return unserialize($advanced_modifiers['field_id_'.$field_id]);
    }
}
// END CLASS
