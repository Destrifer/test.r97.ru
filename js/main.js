/* global $ */

$(document).ready(function() {

    if ($.fn.placeholder) {
        $('input[placeholder], textarea[placeholder]').placeholder();

        $('input, textarea').focus(function() {
            $(this).data('placeholder', $(this).attr('placeholder'));
            $(this).attr('placeholder', '');
        });
        $('input, textarea').blur(function() {
            $(this).attr('placeholder', $(this).data('placeholder'));
        });
    }

    if ($.fn.styler) {
        $('input[type="checkbox"]:not(.nomenu), input[type="radio"]:not(.nomenu)').styler();
    }

    if ($.fn.selectmenu) {
        $('select:not(.nomenu)').selectmenu({
            open: function() {
                $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            },
            change: function() {
                var selValue = $(this).val();
                if ($('.validate_form').length) {
                    $('.validate_form').validate().element(this);
                    if (selValue.length > 0) {
                        $(this).next('div').removeClass('input-validation-error');
                    } else {
                        $(this).next('div').addClass('input-validation-error');
                    }
                }

            }
        }).addClass('selected_menu');
    }

    var $totop = $('<div/>', { 'class': 'totop' }).appendTo('body');

    $totop.hide();

    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $totop.fadeIn();
        } else {
            $totop.fadeOut();
        }
    }).scroll();

    $totop.click(function() {
        $('body,html').animate({ scrollTop: 0 }, 400);
        return false;
    });

    $('[data-close-top-message-trig]').on('click', function() {
        $(this).closest('.top-message').slideUp();
        document.cookie = 'close_top_message=1 expires=Thu, 2 Aug 2001 20:47:11 UTC; path=/; ;domain=.crm.r97.ru';
    });
	
	/*$('body').on('focus', '#summary-status-select, #summary-date', function () {
        prevVal = this.value;
    });
	
	$('body').on('change', '#summary-status-select', function() {
		if ((prevVal == "Подтвержден" && this.value != "Выдан") || (prevVal == "Выдан" && this.value != "Подтвержден")) {
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
    });*/

});



//$(window).load(function() {});


