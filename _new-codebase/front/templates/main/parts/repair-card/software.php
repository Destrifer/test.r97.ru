<?php


function getSoftwareHTML(array $infobase, $allowDownloadFlag = true)
{
    if (!$infobase) {
        return '<p style="text-align: center;padding: 32px 0">Для данной модели файлы отсутствуют.</p>';
    }
    ob_start();
    foreach ($infobase as $cat) {
        if (empty($cat['items'])) {
            continue;
        }
        echo '<div class="software__cat">
        <h3 class="software__title">' . $cat['name'] . '</h3>';
        foreach ($cat['items'] as $item) {
            $d = ($item['cat_id'] == 1) ? 'data-download-btn' : '';
            echo '<ul class="software__row"> 
                <div class="software__col1">
                  <div class="software__data"><b>' . $item['name'] . '</b></div>
                  <div class="software__data">Дата загрузки: <i>' . $item['upload_date'] . '</i></div>';
            if (!empty($item['descr'])) {
                echo '<div style="width:100%">
                    <div class="software__descr-link" data-action="descr">Подробное описание</div>
                    <div class="software__descr" style="display: none">' . $item['descr'] . '</div>
                  </div>';
            }
            echo '</div> 
                <div class="software__col2">';
            if ($allowDownloadFlag) {
                echo '<a href="' . $item['url'] . '" ' . $d . ' class="software__download-btn">Скачать (' . $item['size'] . ')</a>';
                if (in_array($item['ext'], ['jpg', 'jpeg', 'png'])) {
                    echo '<a href="' . $item['url'] . '" data-fancybox class="software__preview" style="background-image: url(' . $item['url'] . ')">
                        <span class="software__preview-capt" style="text-decoration: none">Просмотр</span>
                    </a>';
                }
            }
            echo '</div> 
              </ul>';
        }
        echo '</div>';
    }
    return ob_get_clean();
}


function getRequestSoftwareForm($sendedFlag, array $types)
{
    ob_start();
?>
    <div class="container gutters">
        <div class="row">
            <div class="col-12">
                <h4 class="form__title_2">Требуется ПО</h4>
            </div>
            <div class="col-5">
                <div class="form__cell">
                    <input type="text" class="form__text" placeholder="E-mail для получения ПО" <?= $sendedFlag; ?> id="req-software-email">
                </div>
            </div>
            <div class="col-4">
                <div class="form__cell">
                    <select class="form__select" <?= $sendedFlag; ?> id="req-software-type">
                        <option value="">- Выберите запрос -</option>
                        <?php
                        foreach ($types as $key => $name) {
                            echo '<option value="' . $key . '">' . $name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form__cell form__cell_flex">
                    <?php
                    if (!$sendedFlag) {
                        echo '<button class="form__btn software__request-btn" id="req-software-btn" ' . $sendedFlag . ' >Отправить запрос</button>';
                    } else {
                        echo '<button class="form__btn software__request-btn software__request-btn_disabled" ' . $sendedFlag . ' onclick="javascript:;">Запрос отправлен</button>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
