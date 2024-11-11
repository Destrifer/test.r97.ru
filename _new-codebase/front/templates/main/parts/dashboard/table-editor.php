<?php

function getTableEditorHTML(array $allCols, array $curCols)
{
    ob_start();
?>
    <div class="table-editor">
        <header class="table-editor__edit-area">
            <div class="table-editor__select-col">
                <div class="table-editor__capt">Столбцы в дашборде:</div>
            </div>
            <div class="table-editor__controls-col"></div>
            <div class="table-editor__select-col">
                <div class="table-editor__capt">Все столбцы:</div>
            </div>
        </header>
        <div class="table-editor__edit-area">
            <div class="table-editor__select-col">
                <select id="current-cols-select" data-te-select="in" class="table-editor__select-input" multiple>
                    <?php
                    foreach ($curCols as $uri => $col) {
                        echo '<option value="' . $uri . '">' . $col['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <ul class="table-editor__controls-col">
                <li data-te-action="up" data-te-control="in" class="table-editor__controls-btn table-editor__controls-btn_up disable" title="Переместить столбец вверх"></li>
                <li data-te-action="down" data-te-control="in" class="table-editor__controls-btn table-editor__controls-btn_down disable" title="Переместить столбец вниз"></li>
                <li data-te-action="add" data-te-control="out" class="table-editor__controls-btn table-editor__controls-btn_add disable" title="Добавить столбец в дашборд" style="padding-top: 3px;"></li>
                <li data-te-action="del" data-te-control="in" class="table-editor__controls-btn table-editor__controls-btn_del disable" title="Добавить столбец из дашборда"></li>
            </ul>
            <div class="table-editor__select-col">
                <select data-te-select="out" class="table-editor__select-input" multiple>
                    <?php
                    foreach ($allCols as $uri => $col) {
                        echo '<option value="' . $uri . '">' . $col['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="table-editor__hint">Используйте CTRL + клик для выбора нескольких вариантов.</div>
        <footer class="table-editor__footer">
            <button data-te-action="cancel" class="table-editor__btn">Отмена</button>
            <button data-te-action="save" id="table-editor-save-btn" class="table-editor__btn table-editor__btn_main">Сохранить</button>
        </footer>
    </div>
<?php
    return ob_get_clean();
}
