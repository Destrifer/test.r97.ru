/* global $ */

document.addEventListener('DOMContentLoaded', function() {


    $('#cols-settings-trig').on('click', function() {
        $.fancybox.open({
            src: `?action=get-table-editor&tab=${getTab()}`
        }, {
            type: 'ajax',
            clickSlide: false,
            clickOutside: false
        });
    });


    $(document)
        .on('click', '[data-te-action]', function() {
            if (this.classList.contains('disable')) {
                return;
            }
            switch (this.dataset.teAction) {
                case 'save':
                    save();
                    break;
                case 'cancel':
                    $.fancybox.close();
                    break;
                case 'up':
                    moveCols('up');
                    break;
                case 'down':
                    moveCols('down');
                    break;
                case 'add':
                    addCol();
                    break;
                case 'del':
                    delCol();
                    break;
            }
        })
        .on('click', '[data-te-select]', function() {
            const cols = $(this).val();
            const $controls = $('[data-te-control]');
            $controls.addClass('disable');
            if (cols.length) {
                $('[data-te-control="' + this.dataset.teSelect + '"]').removeClass('disable');
            }
        });


    function getTab() {
        const url = new URL(location.href);
        let tab = url.searchParams.get('tab');
        if (!tab) {
            tab = 'all';
        }
        return tab;
    }


    function save() {
        const cols = document.querySelectorAll('#current-cols-select option');
        const vals = [];
        cols.forEach(function(option) {
            vals.push(option.value);
        });
        const saveBtn = document.getElementById('table-editor-save-btn');
        saveBtn.classList.add('disable');
        $.ajax({
            type: 'POST',
            url: location.href,
            data: `ajax=save-table-editor&cols=${vals.join(',')}&tab=${getTab()}`,
            dataType: 'json',
            complete: function() {
                location.reload();
                $.fancybox.close();
            },
            error: function(jqXHR) {
                console.log('Ошибка сервера');
                console.log(jqXHR.responseText);
            }
        });
    }


    function addCol() {
        const $cols = $('[data-te-select="out"] option:selected');
        if (!$cols.length) {
            return;
        }
        $('[data-te-select="in"]').append($cols);
    }


    function delCol() {
        const $cols = $('[data-te-select="in"] option:selected');
        if (!$cols.length) {
            return;
        }
        $('[data-te-select="out"]').append($cols);
    }


    const getSibling = (direction) => (direction == 'down') ? (elem) => elem.nextElementSibling : (elem) => elem.previousElementSibling;
    const move = (direction) => (direction == 'down') ? (elem1, elem2) => elem1.after(elem2) : (elem1, elem2) => elem1.before(elem2);
    const scrollSelect = ($cols, direction) => {
        const col = (direction == 'down') ? $cols.last()[0] : $cols.first()[0];
        col.scrollIntoView({block: 'center'});
    };

    function moveCols(direction) {
        const $select = $('[data-te-select="in"]');
        let $cols = $('option:selected', $select);
        if (!$cols.length) {
            return;
        }
        scrollSelect($cols, direction);
        if (direction == 'down') {
            $cols = $($cols.get().reverse());
        }
        const sibling = getSibling(direction);
        const swap = move(direction);
        $cols.each(function() {
            if (!sibling(this)) {
                return;
            }
            swap(sibling(this), this);
        });
    }




});