<?php
$zapchast = '';

/* Окно для отправки запчастей на склад "Разбор" */
function showSavePartsWindow(array $parts, $userRole)
{
   if ($userRole != 'service') {
      $message = 'Просим выбрать ИСПРАВНЫЕ модули и аксессуары перед отправкой карточки на проверку.';
   } else {
      $message = 'Просим снять галочки с отсутствующих модулей и аксессуаров перед отправкой карточки на проверку.';
   }
?>
   <form class="form" id="save-parts-window">
      <p style="font-size: 1.1em; color:red;margin-bottom:32px"><?= $message; ?></p>
      <p><label title="Выбрать/сбросить все"><input type="checkbox" id="check-all-parts"> Выбрать/сбросить все</label></p>
	  <p><label title="Скрыть стандартные"><input type="checkbox" id="check-all-stand"> Скрыть стандартные</label></p>
      <table class="save-parts-table">
         <thead>
            <tr>
				<th>Код</th>
               <th></th>
               <th>Запчасть</th>
               <th>Количество</th>
            </tr>
         </thead>
         <tbody>
			<?  
			function cmp_function($a, $b){
				return ($a['part_code'] > $b['part_code']);
			}
			uasort($parts, 'cmp_function');
			?>
			
            <?php foreach ($parts as $part) : ?>
				<?php
					if ($part['attr'] == 'Стандартная') {
						$zapchast = 1;
						} else {
						$zapchast = 0;
					} 
				?>
               <tr id="<?= $zapchast; ?>">
				<td>
					<?= $part['part_code']; ?>
                  </td>
                  <td class="save-parts-table__flag-td"><input id="p<?= $part['id']; ?>" name="checked[<?= $part['id']; ?>]" type="checkbox" value="<?= $part['id']; ?>" <?= ($part['saved_flag']) ? 'checked' : '' ?>></td>
                  <td>
                     <label for="p<?= $part['id']; ?>"><?= $part['name']; ?></label>
                     <br>
                     <span class="grey-font"><?= $part['attr']; ?></span>
                  </td>
                  <td class="save-parts-qty-td"><input type="number" min="1" required value="<?= $part['saved_qty']; ?>" name="part_qty[<?= $part['id']; ?>]" class="form__text"></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>

      <!-- Общее количество наименований запчастей -->
      <input type="hidden" name="parts_total_cnt" value="<?= count($parts); ?>">

      <div class="save-parts-controls">
         <div class="form__btn form__btn_secondary" onclick="$.fancybox.close()">Отмена</div>
         <button class="form__btn" type="submit">Подтвердить</button>
      </div>
   </form>
<?php
}
