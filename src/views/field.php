
    <p style="margin-bottom:0.5em;">You need to enter the modifiers in the <strong>Product Details</strong> area and click <strong>Submit</strong> or <strong>Save Revision</strong> before they will become available here.</p>
    <p style="margin-bottom:0.5em;">The price entered in the value field will be added to the price in the <strong>Product Details</strong> area.</p>


    <?php if (isset($product) && !empty($product['modifiers'])): ?>
    <table class="store_ft adv_mod">
        <thead>
            <tr>
            <?php foreach ($product['modifiers'] as $mod): ?>
                <?php if ($mod['mod_type'] === 'text') { continue; } ?>
                <th><?= $mod['mod_name'] ?></th>
            <?php endforeach ?>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <?= display_rows($product['modifiers'], $advanced_modifiers) ?>
        </tbody>
    </table>
    <?php endif ?>

<?php

function display_rows($modifiers, $advanced_modifiers, $p=array())
{
    $out = '';
    $mod = array_shift($modifiers);
    $rowcount = row_count($modifiers);
    foreach ($mod['options'] as $opt_id => $opt) {
        $p[] = $opt_id;
        $id = implode('-', $p);
        $out .= '<tr>';
        $out .=     '<td rowspan="'.$rowcount.'" class="adv_mod_bl">';
        $out .=         $opt['opt_name'];
        $out .=     '</td>';
    if ($rowcount > 1) {
        $out .=         display_rows($modifiers, $advanced_modifiers, $p);
    } else {
        $out .=     '<td class="store_ft_text">';
        $out .=         form_input(array(
                            'name' => "advanced_modifiers[$id]",
                            'value' => (isset($advanced_modifiers[$id])) ? $advanced_modifiers[$id] : 0
                        ));
        $out .=     '</td>';
    }
        $out .= '</tr>';
        array_pop($p);
    }
    return $out;
}


function row_count($modifiers)
{
    // Map ( row_order ) => number_of_options
    $map = array();
    foreach ($modifiers as $mod) {
        $map[$mod['mod_order']] = count($mod['options']);
    }
    // Sort by row_order
    krsort($map);

    // Calculate rowspan
    $count = 1;
    foreach ($map as $rows) {
        $count = $rows * $count + 1;
    }

    return $count;
}
