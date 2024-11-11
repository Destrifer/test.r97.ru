/* global $, Settings, Table, Filter, Repair */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const user = JSON.parse(document.getElementById('user-data').innerText);
    const settings = new Settings(user);
    const url = new URL(location.href);
    const table = new Table(url);
    const filter = new Filter(url);
    const repair = new Repair();
    const $attnCommentForm = $('#attention-comment-form');
    let timeoutID = null; // для ввода при поиске
    let prevVal = ''; // для отмены изменения поля
    const $mastersSelect = $('#masters-select'); // меню выбора для назначения мастера
    const $tableHeader = $(table.header); // фиксирующаяся шапка таблицы
    let tableHeaderTop = $tableHeader.offset().top; // координаты шапки
    const toTopBtn = document.getElementById('to-top-btn'); // кнопка "Наверх"
    const scrollBody = document.querySelector('[data-scroll-sync="body"]');
    const scrollHeader = document.querySelector('[data-scroll-sync="header"]');

    initSearch(); // запрос из строки поиска
    initNumPerPage();
    filter.init();
    updateTabs(); // добавление текущего url к href вкладок


    /* Изменение количества на странице */
    function initNumPerPage() {
        const key = 'num_per_page';
        const $input = $('[data-num-per-page-input]');
        settings.load(key, (val) => {
            if (+val) {
                $input.val(val);
            }
            table.load();
        });
        $input.on('blur', function () {
            settings.save(key, this.value);
            table.load();
        });
    }


    /* Фиксированная шапка */
    $(window).on('scroll', function () {
        if (window.pageYOffset > tableHeaderTop) {
            if (!$tableHeader.hasClass('fixed')) {
                $tableHeader.width($tableHeader.parent().width() + 'px');
                $tableHeader.addClass('fixed');
                toTopBtn.style.display = '';
                toTopBtn.classList.remove('active'); // старая позиция больше не нужна
            }
        } else {
            $tableHeader.removeClass('fixed');
            if (!toTopBtn.classList.contains('active')) {
                toTopBtn.style.display = 'none';
            }
        }
    });


    /* Синхронная прокрутка шапки и тела таблицы */
    scrollHeader.addEventListener('scroll', function () {
        scrollBody.scrollLeft = this.scrollLeft;
    }, { passive: true });

    /* scrollBody.addEventListener('scroll', function () {
        scrollHeader.scrollLeft = this.scrollLeft;
    }, { passive: true }); */


    /* Различные триггеры действий */
    $('body').on('click', '[data-action]', function (event) {
        event.preventDefault();
        switch (this.dataset.action) {
            case 'to-top':
                toTop(this);
                break;
            case 'add-filter':
                filter.add();
                break;
            case 'check-all':
                if (this.classList.contains('active')) {
                    $('[data-control="check"]').removeClass('active');
                    this.classList.remove('active');
                } else {
                    $('[data-control="check"]').addClass('active');
                    this.classList.add('active');
                }
                break;
            case 'del-filter':
                filter.del($(this).closest('[data-filter-item]'));
                filter.update();
                table.load();
                break;
            case 'clear-filters':
                $('[data-filter-item]').each(function () {
                    filter.del($(this));
                });
                filter.update();
                table.load();
                break;
            case 'combine-labels':
            case 'combine-receipts':
                getCombinedDocs(this.dataset.action);
                break;
            case 'mass-change':
                massChange();
                break;
            case 'toggle-mass-change':
                if (this.classList.contains('active')) {
                    $('#mass-change-ctr').slideUp(200);
                    this.classList.remove('active');
                } else {
                    $('#mass-change-ctr').slideDown(200);
                    this.classList.add('active');
                }
                break;
        }
    });

    /* Кнопка Наверх/обратно */
    function toTop(btn) {
        let newPos;
        if (btn.classList.contains('active')) { // обратно
            newPos = btn.dataset.lastPos;
        } else { // наверх, с сохранением позиции
            newPos = tableHeaderTop - 500;
            btn.dataset.lastPos = window.pageYOffset;
            btn.classList.add('active');
        }
        $('body, html').animate({
            scrollTop: newPos
        }, 400);
    }

    /* Массовое изменение */
    function massChange() {
        let ids = [];
        $('.active[data-control="check"]').each(function () {
            ids.push($(this).closest('[data-repair-id]').data('repair-id'));
        });
        if (!ids.length) {
            return;
        }
        let masterID = '';
        let status = '';
        const masterElem = document.getElementById('mass-change-master');
        if (masterElem) {
            masterID = masterElem.value;
        }
        const statusElem = document.getElementById('mass-change-status');
        if (statusElem) {
            status = statusElem.value;
        }
        repair.massChange(status, masterID, ids, function () {
            table.load();
        });
    }


    /* Объединение наклеек/квитанций */
    function getCombinedDocs(action) {
        let ids = [];
        let query = (action == 'combine-labels') ? 'create_super_nak' : 'create_super_kvit';
        $('.active[data-control="check"]').each(function () {
            ids.push($(this).closest('[data-repair-id]').data('repair-id'));
        });
        if (!ids.length) {
            return;
        }
        location.href = '/?query=' + query + '&value=' + JSON.stringify(ids);
    }


    /* Операции над ремонтом (строкой таблицы) */
    $('body').on('click', '[data-control]', function (event) {
        event.preventDefault();
        let $curRow = $(this).closest('[data-repair-id]');
        const repairID = $curRow.data('repair-id');
        let $this = $(this);
        switch (this.dataset.control) {
            case 'del-repair':
                if (confirm('Удалить карточку?')) {
                    repair.del(repairID);
                    $curRow.fadeOut(() => {
                        $curRow.remove();
                        table.resize();
                    });
                }
                break;
            case 'del-repair-perm':
                if (confirm('Карточка будет удалена без возможности восстановления. Удалить?')) {
                    repair.del(repairID, true);
                    $curRow.fadeOut(() => {
                        $curRow.remove();
                        table.resize();
                    });
                }
                break;
            case 'prototype':
                if (confirm('Вы уверены, что хотите создать карточку с теми же данными, кроме серийного номера?')) {
                    createPrototype(repairID);
                }
                break;
            case 'take':
                takeRepair(repairID);
                break;
            case 'appoint-master':
                showMastersSelect($this, $curRow.data('master-id'));
                break;
            case 'attention':
                changeAttentionStatus($this, $curRow.find('[data-tag-id="1"]'), repairID);
                break;
            case 'check':
                this.classList.toggle('active');
                break;
            case 'show-repeated-repaires':
                repair.getRepeated(repairID, $curRow.find('[data-col="serial"]').text());
                break;
        }
        return false;
    });

    /* Поменять статус "внимание" */
    function changeAttentionStatus($btn, $tag, repairID) {
        if ($btn.hasClass('active')) {
            if (user.role == 'admin' || user.role == 'slave-admin') {
                $btn.removeClass('active');
                $tag.hide();
                repair.setAttentionFlag(0, repairID);
            }
        } else {
            $.fancybox.open($attnCommentForm);
            $attnCommentForm.$btn = $btn;
            $attnCommentForm.$tag = $tag;
            $attnCommentForm.find('[name="repair_id"]').val(repairID);
        }
    }

    $attnCommentForm.on('submit', function () {
        $attnCommentForm.$btn.addClass('active');
        $attnCommentForm.$tag.show();
        repair.setAttentionFlag(1, $attnCommentForm.find('[name="repair_id"]').val(), $attnCommentForm.find('[name="message"]').val());
        $attnCommentForm[0].reset();
        $.fancybox.close();
        return false;
    });

    /* Создать новый ремонт на основе текущего */
    function createPrototype(repairID) {
        repair.createPrototype(repairID, (resp) => {
            if (+resp.new_repair_id) {
                location.href = `/edit-repair/${resp.new_repair_id}/`;
            } else {
                alert('К сожалению, произошла ошибка, пожалуйста, обратитесь к администратору.');
            }
        });
    }

    /* Взять в работу */
    function takeRepair(repairID) {
        repair.needToConfirmMaster(user.id, repairID, function (resp) {
            if (+resp.need_to_confirm_flag && !confirm(resp.message)) {
                return;
            }
            repair.take(user.id, repairID);
        });
    }

    /* Показать меню мастеров */
    function showMastersSelect($btn, $masterID) {
        let offset = $btn.offset();
        $btn.after($mastersSelect);
        $mastersSelect.fadeIn(200);
        $mastersSelect.offset({
            top: offset.top + 20,
            left: offset.left + 20
        });
        $mastersSelect.find('select').val($masterID);
    }

    /* Выбран мастер */
    $('select', $mastersSelect).on('change', function () {
        let newMasterID = $(this).val();
        let $curRow = $(this).closest('[data-master-id]');
        $curRow.data('master-id', newMasterID).attr('data-master-id', newMasterID);
        repair.setMaster(newMasterID, $curRow.data('repair-id'));
        $curRow.find('[data-col="master"]').html($('option:selected', this).text());
        $mastersSelect.fadeOut(200);
    });


    /* Сохранение первоначальной опции select */
    $('body').on('focus', '[data-status-select], [data-approve-date-input]', function () {
        prevVal = this.value;
    });
	
	$('body').on('click', '.controls__item_back', function (event) {
		let days = moment($(this).closest('tr[data-repair-id]').find("td[data-col='ready_date']").text(), "DD.MM.YYYY");
		let mow = moment().daysInMonth();
		let end = moment().endOf('month');
		let dur = moment.duration({ from: days, to: end });
		if (dur.asDays() > mow) {
			if (!confirm('Карточка из предыдущего периода. Вы уверены, что хотите вернуть карточку на доработку?')) {
				event.preventDefault();
			} else {
				var pass = prompt("Введи пароль");
				if (pass != "2308") {
					event.preventDefault();
				}
			}
		} else {
			if (!confirm('Вы уверены, что хотите вернуть карточку на доработку?')) {
				event.preventDefault();
			}
		}
    });
	
	
	$('body').on('click', '.controls__item_recover', function () {
		if (!confirm('Вы хотите восстановить карточку?')) {
			event.preventDefault();
			} else {
				var pass = prompt("Введи пароль");
				if (pass != "2308") {
					event.preventDefault();
				}
			}
    });

    /* Выбран новый статус для ремонта */
    $('body').on('change', '[data-status-select]', function () {
		if ((prevVal == "Подтвержден" && this.value != "Выдан") || (prevVal == "Выдан" && this.value != "Подтвержден") || (prevVal == "Отклонен")){
			var pass = prompt("Введи пароль");
				if (pass != "2308") {
					this.value = prevVal;
					return;
				}
		} else {
			if (!confirm('Вы уверены, что хотите изменить статус ремонта?')) {
				this.value = prevVal;
				return;
			}
		}
        let $curRow = $(this).closest('[data-repair-id]');
        repair.changeStatus($(this).val(), $curRow.data('repair-id'));
    });

    /* Выбрана новая дата подтверждения */
    $(document).on('dateSelected', function (e) {
		var pass = prompt("Введи пароль");
			if (pass != "2308") {
				e.detail.inst.$el.val(prevVal);
				return;
			}
        /*if (!confirm('Вы уверены, что хотите изменить дату подтверждения?')) {
            e.detail.inst.$el.val(prevVal);
            return;
        }*/
        let $curRow = e.detail.inst.$el.closest('[data-repair-id]');
        repair.changeApproveDate(e.detail.formattedDate, $curRow.data('repair-id'));
    });


    /* Выбран столбец для фильтрации */
    $('body').on('change', '[data-filter-select]', function () {
        filter.loadInput($(this));
    });


    /* Ввод в поле значения фильтра */
    $('body').on('change', '[data-filter-input]', function () {
        filter.toURL();
        table.load();
    });



    $(document).on('filter:update search:update', updateTabs);

    /* Обновление навигации вкладок url с фильтром */
    function updateTabs() {
        const tabs = document.querySelectorAll('[data-tab]');
        let link;
        for (let i = 0, len = tabs.length; i < len; i++) {
            link = new URL(location.href);
            if (tabs[i].dataset.tab) {
                link.searchParams.set('tab', tabs[i].dataset.tab);
            } else {
                link.searchParams.delete('tab');
            }
            link.searchParams.delete('page');
            tabs[i].setAttribute('href', link.href);
        }
    }


    /* Поиск по ремонтам */
    function initSearch() {
        const $input = $('[data-input="search"]');
        const val = sessionStorage.getItem('dashboard:search');
        if (val) {
            $input.val(val);
            apply(val);
        }

        $input.on('input', function () {
            if (timeoutID) {
                clearTimeout(timeoutID);
            }
            const val = this.value;
            timeoutID = setTimeout(function () {
                timeoutID = null;
                url.searchParams.delete('page'); // при новом поиске номер страницы не нужен
                apply(val);
                table.load();
            }, 1000);
        });

        function apply(request) {
            request = request.trim();
            if (request.length) {
                url.searchParams.set('search', request);
                sessionStorage.setItem('dashboard:search', request);
            } else {
                url.searchParams.delete('search');
                sessionStorage.removeItem('dashboard:search');
            }
            history.pushState(null, null, url.href);
            document.dispatchEvent(new CustomEvent('search:update'));
        }
    }


    /* Сортировка по столбцам */
    $(table.headerCols).on('dblclick', function () {
        const sortField = this.dataset.sortCol;
        if (!sortField) {
            return;
        }
        table.sortDir.value = (table.sortDir.value == 'desc') ? 'asc' : 'desc';
        table.sortField.value = sortField;
        table.load();
    });


    /* Страницы */
    $('body').on('click', '[data-page-num]', function (event) {
        event.preventDefault();
        if (this.dataset.pageNum == '1') {
            url.searchParams.delete('page');
        } else {
            url.searchParams.set('page', this.dataset.pageNum);
        }
        history.pushState(null, null, url.href);
        table.load();
    });


    /* Закрыть всплывающие элементы */
    $('body').on('click', function (event) {
        if (event.target.closest('[data-popup]')) {
            return;
        }
        $('[data-popup]').fadeOut(200);
    });

});