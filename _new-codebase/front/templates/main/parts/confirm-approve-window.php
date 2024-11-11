<?php
function showConfirmApproveWindow(array $settings, $repairID) {
?>
  <!-- Approve modal window -->
  <div style="display: none" id="approve-modal-window">
    <div class="form" style="min-width: 400px">
        <h3 class="form__title" style="margin: 0 0 44px 0;">Подтвердить ремонт?</h3>
        <div class="form__cell-panel" style="justify-content: space-between;">
        <button class="form__btn" id="approve-confirm-control">Подтвердить</button>
        <div class="form__btn form__btn_secondary" onclick="$.fancybox.close()">Отмена</div>
        </div>
        <?php if(models\Repair::isANRP($repairID)) : ?>
        <div class="form__cell" style="margin-top: 32px;">
          <p style="margin-bottom: 16px;font-weight:600">Местонахождение товара при АНРП:</p>
          <label class="form__label" style="margin-bottom: 7px;"><input type="radio" data-one-choice name="anrp_param" <?= (!$settings || $settings['anrp_value'] == 0) ? 'checked' : ''; ?> value="0"> По умолчанию</label>
          <label class="form__label" style="margin-bottom: 7px;"><input type="radio" data-one-choice name="anrp_param" <?= ($settings && $settings['anrp_value'] == 1) ? 'checked' : ''; ?> value="1"> Оставлен на ответственное хранение</label>
          <label class="form__label"><input type="radio" data-one-choice name="anrp_param" <?= ($settings && $settings['anrp_value'] == 2) ? 'checked' : ''; ?> value="2"> Выдан на руки клиенту</label>
          <input type="hidden" id="cur-anrp-param" value="<?= (empty($settings['anrp_value'])) ? 0 : $settings['anrp_value']; ?>">
        </div>
        <?php endif; ?>
    </div>
  </div>
  <!-- / Approve modal window -->
  <?php
  }
  ?>