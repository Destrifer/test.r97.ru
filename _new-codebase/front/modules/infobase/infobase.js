/* global $ */

document.addEventListener('DOMContentLoaded', function() {

    let $infobase = $('#infobase');
    if (!$infobase.length) {
        return;
    }
    let $rowTpl = $('#row-tpl').detach();
    let curFileElem = null;


    $('[data-input="search"]').on('input', function() {
        if (!this.value.length) {
            $('[data-serial]').show();
            $('[data-serial] .highlight').removeClass('highlight');
            return;
        }
        let $serials = $('[data-serial]');
        let request = this.value.toLowerCase();
        if (!request.length) {
            $serials.show();
            $('[data-serial] .highlight').removeClass('highlight');
            return;
        }
        let keys = ['serial', 'order', 'provider'];
        $serials.each(function(index, el) {
            let matchFlag = false;
            for (let key of keys) {
                let elem = el.querySelector('[data-search="' + key + '"]');
                if (elem.innerText.toLowerCase().includes(request)) {
                    elem.classList.add('highlight');
                    matchFlag = true;
                } else {
                    elem.classList.remove('highlight');
                }
            }
            if (matchFlag) {
                el.style.display = '';
                return;
            }
            el.style.display = 'none';
        });
    });


    $('body').on('click', '[data-action]', function() {
        let $newRow, id;
        switch (this.dataset.action) {
            case 'add-file':
                $newRow = $rowTpl.clone();
                $newRow.css('display', 'none');
                $(this.closest('[data-serial]')).find('[data-name="' + this.dataset.target + '"]').prepend($newRow);
                $newRow.fadeIn();
                break;

            case 'edit-file':
                curFileElem = this.closest('[data-file-id]');
                $.fancybox.open({
                    src: curFileElem.querySelector('[data-edit-serial-modal]'),
                    type: 'inline',
                    clickSlide: false,
                    clickOutside: false,
                    touch: false
                });
                break;

            case 'del-file':
                delFile(this.closest('[data-file-id]'));
                break;

            case 'save':
                save(curFileElem, this.closest('[data-edit-serial-modal]'));
                parent.$.fancybox.close();
                break;

            case 'descr':
                $(this).toggleClass('active');
                $(this).next().slideToggle();
                break;

            case 'select-models':
                id = this.closest('[data-file-id]').dataset.fileId;
                if (!parseInt(id)) {
                    return;
                }
                $('#cur-file-id').val(id);
                $('#cur-serial-id').val(this.closest('[data-serial]').dataset.serial);
                $.fancybox.open({
                    src: '#select-models-modal',
                    type: 'inline',
                    clickSlide: false,
                    clickOutside: false,
                    touch: false,
                    afterLoad: function() {
                        $('body').trigger('SelectModelsOpen');
                    }
                });
                break;
        }
        return false;
    });


    function delFile(fileElem) {
        if (fileElem.classList.contains('loading')) {
            return;
        }
        if (fileElem.dataset.fileId != '' && fileElem.dataset.fileId != '0') {
            if (!confirm('Вы действительно хотите удалить файл?')) {
                return false;
            }
        }
        fileElem.classList.add('loading');
        let formData = new FormData();
        formData.append('file_id', fileElem.dataset.fileId);
        fetch('?ajax=del-file', {
            method: 'POST',
            body: formData
        }).then((resp) => {
            if (!resp.ok) {
                alert(resp.status);
            }
            return resp.json();
        }).then((data) => {
            if (data.error) {
                alert(data.error);
                return;
            }
            fileElem.classList.remove('loading');
            fileElem.classList.add('deleted');
        }).catch((e) => console.log(e));
    }


    $infobase.on('mouseenter', '[data-name="filename"]', function() {
        let text = this.innerText;
        if (!text.length) {
            return;
        }
        $(this).next().text(text).fadeIn(100);
    });


    $infobase.on('mouseleave', '[data-name="filename"]', function() {
        $(this).next().fadeOut(70);
    });


    function save(fileElem, formElem) {
        if (fileElem.classList.contains('loading')) {
            return;
        }
        fileElem.classList.add('loading');
        let files = formElem.querySelector('[data-input="upload-file"]').files;
        let params = {};
        let data = new FormData();
        if (typeof files != 'undefined') {
            $.each(files, function(key, value) {
                data.append(key, value);
            });
        }
        data.append('descr', formElem.querySelector('[data-input="descr"]').value);
        data.append('serial_id', fileElem.closest('[data-serial]').dataset.serial);
        data.append('cat_id', fileElem.closest('[data-cat]').dataset.cat);
        data.append('file_id', fileElem.dataset.fileId);
        data.append('name', formElem.querySelector('[data-input="name"]').value);
        params = {
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '?ajax=save',
            processData: false,
            contentType: false,
            cache: false,
            error: ajaxError
        };
        params.success = function(resp) {
            fileElem.classList.remove('loading');
            formElem.querySelector('[data-input="upload-file"]').value = '';
            if (resp.error) {
                alert('При сохранении произошла ошибка.');
                console.log(resp.error);
                return;
            }
            if (resp.id) {
                fileElem.classList.remove('file-row_empty');
                fileElem.querySelector('[data-name="name"]').innerText = resp.name;
                fileElem.querySelector('[data-name="filename"]').innerText = resp.filename;
                fileElem.querySelector('[data-name="size"]').innerText = resp.size;
                fileElem.querySelector('[data-name="descr"]').innerHTML = resp.descr;
                fileElem.querySelector('[data-name="upload-date"]').innerText = resp.upload_date;
                if (resp.url.length) {
                    $('[data-name="download-file"]', fileElem).attr('href', resp.url).show();
                }
            }
            fileElem.setAttribute('data-file-id', resp.id);
        };
        params.error = ajaxError;
        $.ajax(params);
    }


    function ajaxError(jqXHR) {
        alert(jqXHR.responseText);
        console.log('Ошибка сервера');
        console.log(jqXHR.responseText);
    }
});