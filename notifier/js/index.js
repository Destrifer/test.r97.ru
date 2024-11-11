$( document ).ready(function() {

  var today = new Date();
  var items;
  var jsonResult = [];

  $.ajax({
    dataType: 'json',
    url: '/get-notify/',
    success: function(data) {

        data.forEach(function(result) {
        jsonResult.push(result);
        });

        var items = jsonResult;
        items.counter = 0;

        refreshNotifications(items, today);


        items.forEach(function(item) {
          item.isExpired = item.read;
          if (item.read == false) {
          items.counter += 1;
          }
        });

        if (items.counter == 0) {
        $('.js-notifications').addClass('empty');
        $('.js-show-notifications').removeClass('animated');
         $('.js-show-notifications').removeClass('swing');
        } else {



        }

        $(".notifications-list").mCustomScrollbar({theme: 'dark', scrollInertia: 0}); 
    },
    error: function(jqXHR) {
      console.log('Ошибка сервера');
      console.log(jqXHR.responseText);
  }
  });





function refreshNotifications(items, today) {
  var items = items || [];
  today = today || newDate();
  items.counter = 0;
  var cssTransitionEnd = getTransitionEnd();
  var container = $('.not-container');

  items.forEach(function(item) {
    item.isExpired = item.read;
    if (item.read == false) {
   //   alert(1);
    items.counter += 1;
    }
   /* item.isToday = (item.date.getFullYear() === today.getFullYear()) &&
      (item.date.getMonth() === today.getMonth()) &&
      (item.date.getDate() === today.getDate());

    item.formattedDate = function() {
      if (this.isToday) {
        return timeToString(this.date);
      } else {
        return this.date.getFullYear() + '-' +
          strpad(this.date.getMonth() + 1) + '-' +
          strpad(this.date.getDate());
      }
    };  */
  });
 // console.log(items);
  /*items.sort(function(a, b) {
    if (a.isExpired === b.isExpired) {
      return a.date - b.date;
    } else {
      return (b.isExpired ? 0 : 1) - (a.isExpired ? 0 : 1);
    }
  }); */

  var template =
      '<div class="notifications js-notifications" >' +
        '<h3>Уведомления <span class="notifications-read-all" data-read-all-trig>Прочитать все</span></h3>' +
        '<ul class="notifications-list" style="max-height:550px;">' +
          '<li class="item no-data">Новых уведомлений нет.</li>' +
          '{{#items}}' +
            '<li class="item js-item {{#isExpired}}expired{{/isExpired}}" data-id="{{id}}">' +
              '<div class="details">' +
                '<span class="date">{{date}}</span>' +
                '<span class="title">{{#link}}<a href="{{link}}">{{/link}}{{title}}{{#link}}</a>{{/link}}</span>' +
                '<span class="text">{{text}}</span>' +
              '</div>' +
			  '<button type="button" class="zzz" {{#isExpired}}style="display:none"{{/isExpired}} data-id="{{id}}">-</button>' +
              '{{#isExpired}}<button type="button" class="button-default button-dismiss js-app" data-id="{{id}}">+</button>{{/isExpired}}' +
            '</li>' +
          '{{/items}}' +
        '</ul>' +
      '</div>';
  /*'{{^link}}<button type="button" class="button-default button-dismiss" data-id="{{id}}">×</button>{{/link}}' + */
  container
    .append(Mustache.render(template, { items: items }))
    .find('.js-count').attr('data-count', items.counter).html(items.counter).end()
    .on('click', '.js-show-notifications', function(event) {

    if (items.counter > 0) {

      if ($(event.currentTarget).closest('.js-show-notifications').hasClass('active')) {
      $(event.currentTarget).closest('.js-show-notifications').toggleClass('animated');
      $(event.currentTarget).closest('.js-show-notifications').toggleClass('swing');
      } else {
      $(event.currentTarget).closest('.js-show-notifications').toggleClass('animated');
      $(event.currentTarget).closest('.js-show-notifications').toggleClass('swing');
      }

      }



      $(event.currentTarget).closest('.js-show-notifications').toggleClass('active').blur();
      return true;
    })
    .on('click', '.js-dismiss', function(event) {


    $.ajax({
    dataType: "json",
    data: 'update-id='+$(this).data('id'),
    type: "POST",
    url: '/update-notify/',
    success: function(data) {}
    });

      var item = $(event.currentTarget).parents('.js-item');

      var removeItem = function() {
        item[0].removeEventListener(cssTransitionEnd, removeItem, false);
        item.remove();

        /* update notifications' counter */
        var countElement = container.find('.js-count');
        var prevCount = +countElement.attr('data-count');
        var newCount = prevCount - 1;
        countElement
          .attr('data-count', newCount)
          .html(newCount);

        if(newCount === 0) {
          countElement.remove();
          container.find('.js-notifications').addClass('empty');
        }
      };

      item[0].addEventListener(cssTransitionEnd, removeItem, false);
      item.addClass('dismissed');
      return true;
	  
    }).on('click', '.js-app', function(event) {


    $.ajax({
    dataType: "json",
    data: 'update-app-id='+$(this).data('id'),
    type: "POST",
    url: '/update-notify/',
    success: function(data) {}
    });

        $(this).hide();
        var countElement2 = container.find('.js-count');
        var prevCount2 = +countElement2.attr('data-count');
        console.log(prevCount2);
        var newCount2 = prevCount2 + 1;
        countElement2
          .attr('data-count', newCount2)
          .html(newCount2);

      var item = $(event.currentTarget).parents('.js-item');
      item.removeClass('expired');
      return true;
	  
    }).on('click', '.zzz', function(event) {

    $.ajax({
    dataType: "json",
    data: 'update-id='+$(this).data('id')+'&mark-read=1',
    type: "POST",
    url: '/update-notify/',
    success: function(data) {}
    });
	
	$(this).hide();
	var countElement3 = container.find('.js-count');
        var prevCount3 = +countElement3.attr('data-count');
        var newCount3 = prevCount3 - 1;
        countElement3
          .attr('data-count', newCount3)
          .html(newCount3);
	
	var item = $(event.currentTarget).parents('.js-item');
    item.addClass('expired');
	return true;
	
	}).on('click', '[data-read-all-trig]', function() {
		
		
      if(!confirm('Вы уверены?')){
        return;
      }
        deactivateAll();
        this.style.visibility = 'hidden';
    });


    function deactivateAll() {
        $.ajax({
            dataType: 'json',
            data: 'update-id=-1&mark-read=1',
            type: 'POST',
            url: '/update-notify/'
        });
        $('.js-item').addClass('expired');
        container.find('.js-count').text('0');
    }
    


}




function generateItems(today) {
  today = today || newDate();
  var jsonResult = [];

  $.ajax({
    dataType: "json",
    url: '/get-notify/',
    async: false,
    success: function(data) {

        data.forEach(function(result) {
        //console.log(result);
        jsonResult.push(result);
        });

         //jsonResult = data.result;
    }
  });
  console.log(jsonResult);
  return jsonResult;

 /* var array = [
    { id: 1, title: 'Meeting with Ben\'s agent.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate() },
    { id: 2, title: 'Papers review with Tonny.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate(addMinutes(today, -60), addMinutes(today, 60)) },
    { id: 3, title: 'Annual party at Eric\'s house.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate() },
    { id: 4, title: 'Last day to pay off auto credit.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate() },
    { id: 5, title: 'Call and schedule another meeting with Amanda.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate(addMinutes(today, -360), addMinutes(today, 360)) },
    { id: 6, title: 'Don\'t forget to send in financial reports.', text: 'yrdy yrdydr ydryrdydry ry rr yryreyrdy r rytryry', date: randomDate() }
  ];

  console.log(array);  */

  //return array;

}

function randomDate(start, end) {
  start = start || (new Date(2017, 0, 1));
  end = end || (new Date(2015, 0, 1));
  return new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
}

function addMinutes(date, minutes) {
  return new Date(date.getTime() + minutes * 60000);
}

function timeToString(date) {
  if (date) {
    var hh = date.getHours();
    var mm = date.getMinutes();
    var ap = hh >= 12 ? 'PM' : 'AM';

    hh = (hh >= 12) ? (hh - 12) : hh;
    hh = (hh === 0) ? 12 : hh;

    return (hh < 10 ? '0' : '') + hh.toString() + ':' +
      (mm < 10 ? '0' : '') + mm.toString() + ' ' + ap;
  }
  return null;
}

function strpad(num) {
  if (parseInt(num) < 10) {
    return '0' + parseInt(num);
  } else {
    return parseInt(num);
  }
}

function getTransitionEnd() {
  var supportedStyles = window.document.createElement('fake').style;
  var properties = {
    'webkitTransition': { 'end': 'webkitTransitionEnd' },
    'oTransition': { 'end': 'oTransitionEnd' },
    'msTransition': { 'end': 'msTransitionEnd' },
    'transition': { 'end': 'transitionend' }
  };

  var match = null;
  Object.getOwnPropertyNames(properties).forEach(function(name) {
    if (!match && name in supportedStyles) {
      match = name;
      return;
    }
  });

  return (properties[match] || {}).end;
}

});