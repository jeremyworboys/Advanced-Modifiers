
    <p style="margin-bottom:0.5em;">You need to enter the modifiers in the <strong>Product Details</strong> area and submit before they will become available here.</p>

    <?php if (isset($product) && !empty($product['modifiers'])): ?>
    <table class="store_ft">
        <thead>
            <tr>
                <th style="width: 20%">Modifier Name</th>
                <th style="width: 20%">Modifier Option</th>
                <th style="width: 60%">Advanced Price Modifier</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($product['modifiers'] as $mod): ?>
            <?php if ($mod['mod_type'] === 'text') { continue; } ?>
            <tr>
                <td rowspan="<?= count($mod['options']) ?>"><?= $mod['mod_name'] ?></td>
                <?php $first_line = true; ?>
                <?php foreach ($mod['options'] as $opt_id => $opt): ?>
                <?= $first_line ? '' : '<tr>' ?>
                    <td style="border-left: 1px solid #d1d5de;"><?= $opt['opt_name'] ?></td>
                    <td class="store_ft_text"><?= form_input("advanced_modifiers_field[{$opt_id}]", $advanced_modifiers[$opt_id], 'placeholder="Use Default" autocomplete="off"') ?></td>
                </tr>
                <?php $first_line = false; ?>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    <?php endif ?>
