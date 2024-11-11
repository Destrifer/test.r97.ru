<?php

function staffCatsHTML(array $staffCats)
{
    $c = ceil(count($staffCats) / 2) + 3;
    $n = 0;
    echo '<div class="cats-window"> <div class="cats-window__col">';
    foreach ($staffCats as $l => $list) {
        if ($n == $c) {
            echo '</div>
                     <div class="cats-window__col">';
        }
        echo '<div class="cats-window__block">
                    <h3 class="cats-window__title">' . $l . '</h3>';
        foreach ($list as $cat) {
            echo '<label class="cats-window__item">
                        <input type="checkbox" style="margin-right: 8px" name="cat_id[]" ' . (($cat['is_checked']) ? 'checked' : '') . ' value="' . $cat['cat_id'] . '"> 
                        <div class="cats-window__name">' . $cat['name'] . '</div>
                  </label>';
        }
        echo '</div>';
        $n++;
    }
    echo '</div></div>';
}
