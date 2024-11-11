<?php

function commentHTML(array $part, $userRole)
{
    if ($userRole == 'admin') {
        echo '<textarea name="parts[' . $part['id'] . '][comment]" ' . ((!$part['approve_disposal_flag']) ? '' : 'readonly') . ' data-input="comment" class="disposal-request__comment-input form__text" rows="2" placeholder="Сообщение для СЦ">' . $part['comment'] . '</textarea>';
    } else {
        echo $part['comment'];
    }
}


function disposeFlagHTML(array $part, $userRole)
{
    if ($userRole == 'admin') {
        echo '<input data-input="dispose-flag" name="parts[' . $part['id'] . '][is_checked]" value="1" type="checkbox" class="form__flag form__flag_big" ' . ((!$part['approve_disposal_flag']) ? '' : 'checked') . '>
              <input type="hidden" name="parts[' . $part['id'] . '][part_id]" value="' . $part['part_id'] . '">';
    } else {
        echo ($part['approve_disposal_flag']) ? 'Да' : 'Нет';
    }
}


function disposedNumHTML(array $part, $userRole)
{
    if ($userRole == 'admin') {
        echo '<input data-input="disposed-num" type="number" ' . (($part['approve_disposal_flag']) ? '' : 'readonly') . ' min="0" max="' . $part['num'] . '" name="parts[' . $part['id'] . '][disposed_num]" value="' . $part['disposed_num'] . '" class="form__text">';
    } else {
        echo $part['disposed_num'];
    }
}
