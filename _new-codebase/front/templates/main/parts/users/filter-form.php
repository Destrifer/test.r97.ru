<?php


function filterFormHTML(array $data, array $roles, array $services)
{
    $roleIDs = (!empty($data['role-id'])) ? explode(',', $data['role-id']) : [];
?>
    <div class="container gutters form">

        <div class="row">
            <?php if ($services) : ?>
                <div class="col-4">
                    <div class="form__field">
                        <select style="width:100%" class="select2" name="service-id" data-filter>
                            <option value="">-- любой СЦ --</option>
                            <?php
                            foreach ($services as $id => $service) {
                                $sel = (!empty($data['service-id']) && $data['service-id'] == $id) ? 'selected' : '';
                                echo '<option value="' . $id . '" ' . $sel . '>' . $service . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-2">
                <div class="form__field">
                    <select style="width: 100%; display: none;" class="fselect" name="role-id[]" multiple data-filter>
                        <?php
                        foreach ($roles as $id => $role) {
                            $sel = in_array($id, $roleIDs) ? 'selected' : '';
                            echo '<option value="' . $id . '" ' . $sel . '>' . $role . '</option>';
                        } ?>
                    </select>
                </div>
            </div>

            <div class="col-2">
                <div class="form__field">
                    <label><input type="checkbox" name="show-inactive" <?= (!empty($data['show-inactive']) ? 'checked' : ''); ?> value="1" data-filter> Только неактивные</label>
                </div>
            </div>

            <div class="col-2">
                <div class="form__field form__field_row">
                    <button class="form__btn filter-form__btn" data-action="apply">Применить</button>
                    <button class="form__btn form__btn_secondary filter-form__btn" data-action="reset">Сброс</button>
                </div>
            </div>

        </div>

    </div>
<?php
}
