
    <table class="store_ft">
        <thead>
            <tr>
                <th style="width: 20%">Modifier Name</th>
                <th style="width: 20%">Modifier Option</th>
                <th style="width: 60%">Advanced Price Modifier</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($product['modifiers'])): ?>
            You need to enter the modifiers in the <strong>Product Details</strong> area and submit before they will become available here.
        <?php endif; ?>
        <?php foreach ($product['modifiers'] as $mod): ?>
            <?php if ($mod['mod_type'] === 'text') { continue; } ?>
            <tr>
                <td rowspan="<?php echo count($mod['options']) ?>">
                    <?php echo $mod['mod_name'] ?>
                </td>
                <?php foreach ($mod['options'] as $i => $opt): ?>
                <?php if ($i !== 1): ?><tr><?php endif ?>
                    <td style="border-left: 1px solid #d1d5de;"><?php echo $opt['opt_name'] ?></td>
                    <td class="store_ft_text"><?= form_input('$opt_prefix'.'[opt_price_mod]', $opt['opt_price_mod'], 'placeholder="None" autocomplete="off"') ?></td>
                </tr>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
