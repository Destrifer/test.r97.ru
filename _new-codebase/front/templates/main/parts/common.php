<?php

function secNavHTML(array $secNav)
{
?>
    <ul class="sec-nav">
        <?php foreach ($secNav as $item) : ?>
            <li class="sec-nav__item">
                <a href="<?= $item['url']; ?>" class="sec-nav__link <?= (empty($item['class'])) ? '' : $item['class']; ?>" <?= (empty($item['action'])) ? '' : 'data-action="' . $item['action'] . '"'; ?>><?= $item['name']; ?>
                    <?= (!empty($item['cnt'])) ? '<span class="sec-nav__cnt">' . $item['cnt'] . '</span>' : ''; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
}


function getPaginationHTML(array $pagination)
{
    $html = '';
    foreach ($pagination as $p) {
        if ($p['value'] == '...') {
            $html .= '<div class="pagination__num_dot">' . $p['value'] . '</div>';
            continue;
        }
        $activeFlag = ($p['active_flag']) ? 'pagination__num_active' : '';
        $html .= '<a href="' . $p['url'] . '" class="pagination__num ' . $activeFlag . '">' . $p['value'] . '</a>';
    }
    return '<nav class="pagination" style="padding-top: 0">' . $html . '</nav>';
}
