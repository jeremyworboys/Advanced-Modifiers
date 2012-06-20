
    <p style="margin-bottom:0.5em;">You need to enter the modifiers in the <strong>Product Details</strong> area and submit before they will become available here.</p>


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
                            'value' => number_format((isset($advanced_modifiers[$id])) ? $advanced_modifiers[$id] : 0, 2)
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
    $count = 1;
    foreach ($modifiers as $mod) {
        $count *= count($mod['options']);
    }
    return $count + floor($count / 2); // Don't ask, seriously
}
