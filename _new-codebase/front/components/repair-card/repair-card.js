'use strict';
/* global $ */


document.addEventListener('DOMContentLoaded', function () {
    var repairData = initRepairData();
    saveAndCloseSubmit();
    changeStatusInSummary(repairData);
    setHasQuestionsFlag(repairData);
});


function setHasQuestionsFlag(repairData){
    $('[data-has-questions-checkbox]').on('change', function(){
        const value = +this.checked;
        $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: '/repair-api.php',
            data: 'action=set-has-questions&has_questions=' + value + '&repair_id=' + repairData.repairID,
            error: function (jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
    });
}


function changeStatusInSummary(repairData) {
    let elem = document.getElementById('summary-status-select');
    if (!elem) {
        return;
    }
	let prevVal = elem.value;
    let isPending = false;
    elem.addEventListener('change', saveStatus);

    function saveStatus() {
        if (isPending) {
            alert('Запрос на изменение статуса выполняется, пожалуйста, дождитесь окончания.');
            return;
        }
        isPending = true;
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
						data: 'status=' + this.value + '&repair_id=' + repairData.repairID,
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
					data: 'status=' + this.value + '&repair_id=' + repairData.repairID,
					error: function(jqXHR) {
						console.log('Ошибка сервера');
						console.log(jqXHR.responseText);
					}
				});
			}
		}
    }
}

function saveAndCloseSubmit() {
    let triggers = document.querySelectorAll('[data-role=save-and-close-trigger]');
    for (let i = 0, len = triggers.length; i < len; i++) {
        triggers[i].addEventListener('click', saveFormAndGo);
    }

    $('[data-role=save-and-close-trigger-files]').on('click', function () {
        $('#submit-type').val('go-to-dashboard');
        if ($('.repair_form').valid()) {
            $('.repair_form').submit();
            return;
        }
        return false;
    });

    function saveFormAndGo() {
        $('#submit-type').val(1);
        if ($('.repair_form').valid()) {
            $.ajax({
                type: 'POST',
                url: document.location.href,
                data: $('.repair_form').serialize(),
                dataType: 'text'
            });
        } else {
            return false;
        }
    }
}

function initRepairData() {
    let res = window.location.href.match('/edit-repair/([0-9]+)/');
    if (!res) {
        return {
            repairID: ''
        };
    }
    return {
        repairID: res[1]
    };
}