<?php


function filterFormArrivalsHTML(array $data, array $cats, array $codes, array $models, array $depots, array $parts, array $attrs, array $types, array $vendors, array $countries, array $arrivals)
{
    filterFormHTML(true, $data, $cats, $codes, $models, $depots, $parts, $attrs, $types, $vendors, $countries, $arrivals);
}


function filterFormPartsHTML(array $data, array $cats, array $codes, array $models, array $depots, array $parts, array $attrs, array $types, array $vendors, array $countries)
{
    filterFormHTML(false, $data, $cats, $codes, $models, $depots, $parts, $attrs, $types, $vendors, $countries, []);
}



function filterFormHTML($isArrivals, array $data, array $cats, array $codes, array $models, array $depots, array $parts, array $attrs, array $types, array $vendors, array $countries, array $arrivals)
{
?>
    <div class="container gutters form">
        <div class="row">

            <div class="col-2">
                <div class="form__field">
                    <select class="fselect-cat" style="display: none" name="cat_id[]" multiple data-filter>
                        <option value="">-- любая категория --</option>
                        <?php
                        $cur = (!empty($data['cat_id'])) ? explode(',', $data['cat_id']) : null;
                        foreach ($cats as $cat) {
                            echo '<option value="' . $cat['id'] . '" ' . ((in_array($cat['id'], $cur)) ? 'selected' : '') . '>' . $cat['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php if ($vendors) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <select class="form__select select2" name="vendor_id" data-filter>
                            <option value="">-- любой производитель --</option>
                            <?php
                            $cur = (!empty($data['vendor_id'])) ? $data['vendor_id'] : null;
                            foreach ($vendors as $vendor) {
                                echo '<option value="' . $vendor['id'] . '" ' . (($cur == $vendor['id']) ? 'selected' : '') . '>' . $vendor['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($attrs) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <select class="form__select" name="attr_id" data-filter>
                            <option value="">-- любой признак --</option>
                            <?php
                            $cur = (!empty($data['attr_id'])) ? $data['attr_id'] : null;
                            foreach ($attrs as $id => $name) {
                                echo '<option value="' . $id . '" ' . (($cur == $id) ? 'selected' : '') . '>' . $name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($types) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <select class="form__select" name="type_id" data-filter>
                            <option value="">-- любая принадлежность --</option>
                            <?php
                            $cur = (!empty($data['type_id'])) ? $data['type_id'] : null;
                            foreach ($types as $id => $name) {
                                echo '<option value="' . $id . '" ' . (($cur == $id) ? 'selected' : '') . '>' . $name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-2">
                <div class="form__field">
                    <select class="form__select select2" name="code" data-filter>
                        <option value="">-- любой код --</option>
                        <?php
                        $cur = (!empty($data['code'])) ? $data['code'] : null;
                        foreach ($codes as $code) {
                            echo '<option value="' . $code['code'] . '" ' . (($cur == $code['code']) ? 'selected' : '') . '>' . $code['code'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-2">
                <div class="form__field">
                    <input type="text" name="part_num" data-filter class="form__text" value="<?= (isset($data['part_num']) ? $data['part_num'] : ''); ?>" placeholder="Партномер...">
                </div>
            </div>

            <div class="col-2">
                <div class="form__field">
                    <?php if (!$isArrivals) : ?>
                        <input type="text" placeholder="Дата поступления" class="form__text" name="collect_dates" data-filter data-datepicker-interval data-range="true" data-multiple-dates-separator=" - " value="<?= (!empty($data['collect_dates'])) ? $data['collect_dates'] : ''; ?>">
                    <?php else : ?>
                        <input type="text" placeholder="Дата прихода" class="form__text" name="arrival_dates" data-filter data-datepicker-interval data-range="true" data-multiple-dates-separator=" - " value="<?= (!empty($data['arrival_dates'])) ? $data['arrival_dates'] : ''; ?>">
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($countries) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <select class="form__select" name="country_id" data-filter>
                            <option value="">-- все страны --</option>
                            <?php
                            $cur = (!empty($data['country_id'])) ? $data['country_id'] : null;
                            foreach ($countries as $country) {
                                echo '<option value="' . $country['id'] . '" ' . (($cur == $country['id']) ? 'selected' : '') . '>' . $country['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($depots) : ?>
                <div class="col-2">
                    <div class="form__field" id="depot-filter">
                        <select class="form__select fselect-depot" name="depot_id" data-filter>
                            <option value="all">-- любой склад --</option>
                            <?php foreach ($depots as $country => $depotsList) : ?>
                                <optgroup label="<?= $country; ?>">
                                    <?php
                                    $cur = (!empty($data['depot_id'])) ? $data['depot_id'] : null;
                                    foreach ($depotsList as $depot) {
                                        echo '<option value="' . $depot['id'] . '" ' . (($depot['id'] === $cur) ? 'selected' : '') . '>' . $depot['name'] . '</option>';
                                    }
                                    ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-2">
                <div class="form__field">
                    <select class="form__select select2" name="model_id" data-filter>
                        <option value="">-- любая модель --</option>
                        <?php
                        $cur = (!empty($data['model_id'])) ? $data['model_id'] : null;
                        foreach ($models as $model) {
                            echo '<option value="' . $model['id'] . '" ' . (($cur == $model['id']) ? 'selected' : '') . '>' . $model['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="<?= (($isArrivals) ? 'col-2' : 'col-4'); ?>">
                <div class="form__field">
                    <select class="form__select select2" name="id" data-filter>
                        <option value="">-- любая запчасть --</option>
                        <?php
                        $cur = (!empty($data['id'])) ? $data['id'] : null;
                        foreach ($parts as $part) {
                            echo '<option value="' . $part['id'] . '" ' . (($cur == $part['id']) ? 'selected' : '') . '>' . $part['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php if ($isArrivals) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <select class="form__select select2" name="arrival_id" data-filter>
                            <option value="">-- любой № прихода --</option>
                            <?php
                            $cur = (!empty($data['arrival_id'])) ? $data['arrival_id'] : null;
                            foreach ($arrivals as $arrival) {
                                echo '<option value="' . $arrival['id'] . '" ' . (($cur == $arrival['id']) ? 'selected' : '') . '>' . $arrival['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="row">

            <div class="col-2">
                <div class="form__field">
                    <button class="form__btn filter-form__btn" data-action="apply">Применить</button>
                </div>
            </div>

            <div class="col-2">
                <div class="form__field">
                    <button class="form__btn form__btn_secondary filter-form__btn" data-action="reset">Сброс</button>
                </div>
            </div>

            <?php if (!$isArrivals) : ?>
                <div class="col-2">
                    <div class="form__field">
                        <label><input type="checkbox" name="hide-empty" <?= (!empty($data['hide-empty']) ? 'checked' : ''); ?> value="1" data-filter> Скрыть 0 шт.</label>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form__field">
                        <label><input type="checkbox" name="show-disposals" <?= (!empty($data['show-disposals']) ? 'checked' : ''); ?> value="1" data-filter> Готовые к утилизации</label>
                    </div>
                </div>

                <?php if ($depots) : ?>
                    <div class="col-2">
                        <div class="form__field">
                            <label><input type="checkbox" name="have-no-tpl" <?= (!empty($data['have-no-tpl']) ? 'checked' : ''); ?> value="1" data-filter> Еще нет шаблона</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($depots) : ?>
                    <div class="col-2">
                        <div class="form__field">
                            <label><input type="checkbox" name="is_deleted" <?= (!empty($data['is_deleted']) ? 'checked' : ''); ?> value="1" data-filter> Удаленные</label>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </div>
<?php
}
