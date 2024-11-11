'use strict';
/* global $, repairData */

document.addEventListener('DOMContentLoaded', function() {
    let elem = document.getElementById('summary-status-select');
    if (!elem) {
        return;
    }
	let prevVal = elem.value;
    elem.addEventListener('change', saveStatus);


    function saveStatus() {
		if ((prevVal == "Подтвержден" && this.value != "Выдан") || (prevVal == "Выдан" && this.value != "Подтвержден")) {
			var pass = prompt("Введи пароль");
				if (pass != "2308") {
					this.value = prevVal;
					return;
				} else {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						cache: false,
						url: '/repair-card/?ajax=save-status',
						data: 'status=' + this.value + '&repair_id=' + repairData.id,
						error: function(jqXHR) {
							console.log('Ошибка сервера');
							console.log(jqXHR.responseText);
						}
					});
				}
		} else {
			if (!confirm('Вы уверены, что хотите изменить статус ремонта?')) {
				this.value = prevVal;
				return;
			} else {
				$.ajax({
					type: 'POST',
					dataType: 'json',
					cache: false,
					url: '/repair-card/?ajax=save-status',
					data: 'status=' + this.value + '&repair_id=' + repairData.id,
					error: function(jqXHR) {
						console.log('Ошибка сервера');
						console.log(jqXHR.responseText);
					}
				});
			}
		}
    }
});