<?php


function attentionHTML(array $attn, $editableFlag = false)
{
    if (!$attn) {
        return;
    }
?>
    <section class="attention">

        <h3 class="attention__title">Причины проверки</h3>

        <ul class="attention__items">
            <?php
            foreach ($attn['messages'] as $mess) {
                echo '<li class="attention__item">
                        <span class="attention__add-date" title="' . $mess['add_time'] . '">' . $mess['add_date'] . '</span>
                        <span data-attention-message="' . $mess['id'] . '" class="attention__message" '.(($editableFlag) ? 'contenteditable="true"' : '').'>' . nl2br($mess['message']) . '</span>
                    </li>';
            }
            ?>
        </ul>

        <?php if($editableFlag) : ?>
            <div id="save-attentions-trigger" style="display: none" class="attention__save-btn">Сохранить</div>
        <?php endif; ?>
        <!--    if(User::isAdmin()){
    echo '<br>
    <span class="edit-attention" data-action="edit-attention">Редактировать</span>
      <div style="display:none">
        <textarea id="attention-message-input">'.$attn['message'].'</textarea>
        <input type="hidden" id="attention-id" value="'.$attn['id'].'">
        <span class="save-attention" data-action="save-attention">Сохранить</span>
      </div>';
  }  -->
    </section>
<?php
}
