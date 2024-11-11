<?php

function getStoreWindowHTML(array $part, array $parts, $orderID)
{
    ob_start();
?>
    <div class="form" style="min-width: 800px">
        <h3 class="form__title" style="margin-top: 0"><?= $part['name']; ?></h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Склад</th>
                    <th>Место</th>
                    <th>Остаток</th>
                    <th>Количество</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parts as $p) : ?>
                    <tr data-part-row>

                        <td><?= $p['depot']['name']; ?></td>
                        <td><?= $p['place']; ?></td>
                        <td><?= $p['qty']; ?></td>
                        <td>
                            <input data-input="num" type="number" min="1" max="<?= $p['qty']; ?>" value="1" class="form__text">
                        </td>
                        <td><button type="button" data-on-click="replace-depot" data-part-id="<?= $part['id']; ?>" data-depot-id="<?= $p['depot']['id']; ?>" data-order-id="<?= $orderID; ?>" class="form__btn form__btn_small form__btn__primary">Выбрать</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
    return ob_get_clean();
}
