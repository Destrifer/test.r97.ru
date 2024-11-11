<?
header("HTTP/1.1 404 Not Found");
header("Status: 404 Not Found");
?>
<!doctype html>
<html>
<head>
<meta charset=Utf-8>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>HARPER.RU</title>
<link href="<?=$config['url'];?>css_harper/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css_harper/style.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css_harper/adapt.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css_harper/fancybox.css" rel="stylesheet" />
<link rel="icon" type="image/png" href="<?=$config['url'];?>HARPER_icon.png" sizes="16x16">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.mousewheel.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.jscrollpane.min.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.carouFredSel.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.touchSwipe.min.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.toShowHide.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.fancybox.pack.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js_harper/jquery.qtip.min.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script src="<?=$config['url'];?>js_harper/main.js"></script>
</head>
<body>

<div class="viewport-wrapper">

<div class="site-header">

  <div class="head">
    <div class="wrapper">

      <div class="logo">
        <a href="<?=$config['url'];?>"><img src="<?=$config['url'];?>i_harper/logo.png" alt=""/></a>
      </div>

      <div class="menu">
        <ul>
          <?=menu_header();?>
        </ul>
      </div>

      <div class="r">

        <div class="phone">
          <?=$config['phone'];?>
        </div>

        <div class="feedback">
          <a href="" class="open-callback">Написать нам</a>
        </div>

      </div>

    </div>
  </div>

  <div class="foot wrapper">

        <div class="site-catalog">
      <ul>
        <li><a href="/dlya-avtomobilya/">Для автомобилей</a>
          <?=menu_type(1);?>
        </li>
        <li><a href="/dlya-doma/">Для дома</a>
          <?=menu_type(2);?>
        </li>
        <li><a href="/zvuk/">Звук</a>
        <?=menu_type(3);?>
        </li>
        <li><a href="/aksessuary/">Аксессуары</a>
        <?=menu_type(4);?>
        </li>
      </ul>
    </div><!-- .site-catalog -->

    <div class="mobile-menu">

      <div class="bt">
        Навигация
      </div>

      <div class="box">

        <div class="catalog">

          <div class="title">
            Каталог товаров
          </div>

          <ul class="basic">
            <li data-id="1"><a href="/dlya-avtomobilya/">Для автомобиля</a></li>
            <li data-id="2"><a href="/dlya-doma/">Для дома</a></li>
            <li data-id="3"><a href="/zvuk/">Звук</a></li>
            <li data-id="4"><a href="/aksessuary/">Аксессуары</a></li>
          </ul>

          <ul class="hidden" data-id="1">
            <?=menu_type_mobile(1);?>
          </ul>

          <ul class="hidden" data-id="2">
            <?=menu_type_mobile(2);?>
          </ul>

          <ul class="hidden" data-id="3">
            <?=menu_type_mobile(3);?>
          </ul>

          <ul class="hidden" data-id="4">
            <?=menu_type_mobile(4);?>
          </ul>

        </div>

        <div class="list">
          <ul>
            <?=menu_header_mobile();?>
          </ul>
        </div>

        <div class="back">
          <a href="">Назад</a>
        </div>

      </div>

    </div><!-- .mobile-menu -->

    <div class="r">

      <div class="search">
        <a href=""></a>
        <div class="box">
          <form method="POST" action="/search/"><input type="text" name="search" placeholder="Искать по сайту ..."/> </form>
        </div>
      </div>


    </div>

  </div>

</div><!-- .site-header -->

<div class="head-box">
  <div class="wrapper">
    <div class="inner">

      <h1 class="title">Упс... мы привели вас куда-то не туда ...</h1>

      <div class="back">
        <a href="/">Вернуться на главную</a>
      </div>

    </div>
  </div>
</div>

<div class="inform-404 wrapper">

  <div class="img">
    <img src="/i_harper/inform-404.png" alt=""/>
  </div>

  <div class="entry">

    <div class="text">
      Если вы считаете, что ввели ссылку верно, свяжитесь с нами мы исправим ошибку
    </div>

    <ul>
      <li class="p-1"><a href="/news/">Новости <span>компании</span></a>
      <li class="p-2"><a href="/contacts/">Связаться <span>с нами</span></a>
      <li class="p-3"><a href="<?=$config['catalog_link'];?>">Каталог <span>продукции</span></a>
    </ul>

  </div>

</div><!-- .inform-404 -->

<div class="product-new wrapper">

  <div class="subtitle">
    НОВЫЕ ТОВАРЫ
    <div class="arr-l"></div>
    <div class="arr-r"></div>
  </div>

  <div class="slider">
    <div class="inner">
      <ul>
      <?=items_404(); ?>
      </ul>
    </div>
    <div class="arr-l"></div>
    <div class="arr-r"></div>
  </div>

</div><!-- .product-new -->

<div class="step-4 wrapper">
  <div class="inner">
    <ul>

      <li>
        <div class="icon">
          <img src="/i_harper/step4-ic-1.png" alt=""/>
        </div>
        <div class="entry">
          <div class="title">
            Где купить?
          </div>
          <div class="text">
            Регионы и города, где можно приобрести наш товар
          </div>
        </div>
        <a href="/gde-kupit/"></a>

      <li>
        <div class="icon">
          <img src="/i_harper/step4-ic-2.png" alt=""/>
        </div>
        <div class="entry">
          <div class="title">
            Официальный сервис
          </div>
          <div class="text">
            Мы даем гарантию на все наши товары
          </div>
        </div>
        <a href="/support/"></a>

      <li>
        <div class="icon">
          <img src="/i_harper/step4-ic-3.png" alt=""/>
        </div>
        <div class="entry">
          <div class="title">
            Поддержка
          </div>
          <div class="text">
            Возникают вопросы? мы ответим на них в короткие сроки
          </div>
        </div>
        <a href="/support/"></a>

    </ul>
  </div>
  <div class="arr-l"></div>
  <div class="arr-r"></div>
</div><!-- .step-4 -->

<div class="step-5">
  <div class="wrapper">

    <div class="item">

      <div class="title">
        Для автомобилей
      </div>

      <ul>
        <?=footer_categories(1);?>
      </ul>

    </div>

    <div class="item">

      <div class="title">
        Для дома
      </div>

      <ul>
        <?=footer_categories(2);?>
      </ul>

    </div>

    <div class="item">

      <div class="title">
        Звук
      </div>

      <ul>
        <?=footer_categories(3);?>
      </ul>

    </div>

    <div class="item">

      <div class="title">
        Аксессуары
      </div>

      <ul>
        <?=footer_categories(4);?>
      </ul>

    </div>

    <div class="clear_fix"></div>

    <div class="img">
      <img src="" alt=""/>
    </div>

  </div>
</div><!-- .step-5 -->

<div class="site-footer">
  <div class="wrapper">

    <div class="body">

      <div class="logo">
        <a href=""><img src="<?=$config['url'];?>i_harper/logo.png" alt=""/></a>
        <span>HARPER - компания по производству современной электроники</span>
      </div>

      <div class="menu">

        <div class="item">

          <div class="title">
            Информация о компании
          </div>

          <ul>
            <li><a href="/about/">О Компании</a></li>
            <li><a href="/news/">Новости компании</a></li>
            <li><a href="/partneram/">Вакансии</a></li>
            <li><a href="/contacts/">Контакты</a></li>
          </ul>

        </div>

        <div class="item">

          <div class="title">
            Поддержка и вопросы
          </div>

          <ul>
            <li><a href="/support/">Поддержка</a></li>
            <li><a href="/support/">Часто задаваемые вопросы</a></li>
            <li><a href="/support/">Задать вопрос</a></li>
            <li><a href="/support/">Инструкции</a></li>
          </ul>

        </div>

      </div>

      <div class="r">

        <div class="phone">

          <div class="title">
            Горячая линия
          </div>

          <div class="box">
            +7 [495] 133 02 10
          </div>

        </div>

        <div class="callback">
          <a href="" class="open-callback">Написать нам</a>
        </div>

        <div class="social">
          <a href="https://twitter.com/HarperRussia" class="tw"></a>
          <a href="http://vk.com/harperrussia" class="vk"></a>
          <a href="https://www.facebook.com/harper.russia" class="fb"></a>
          <a href="http://instagram.com/harper_russia" class="in"></a>
          <a href="http://www.youtube.com/channel/UC71NkNcIUz0G2xf3wb2dD5w/" class="yt"></a>
        </div>

      </div>

      <div class="clear_fix"></div>
    </div>

    <div class="foot">

      <div class="copy">
        &copy; 2014-2016 HARPER Все права защищены. Копирование материала запрещено
      </div>

      <div class="clear_fix"></div>
    </div>

  </div>
</div><!-- .site-footer -->

</div><!-- .viewport-wrapper -->

<div class="dialog-callback">

  <div class="title">
    Написать нам
  </div>

  <div class="form-box">
    <form id="feedback_form">
    <div class="l">

      <div class="item item-ic item-name">
        <div class="level">Ваше имя <span>обязательно</span></div>
        <div class="value">
          <input type="text" name="name" />
        </div>
      </div>

      <div class="item item-ic item-city ">
        <div class="level">Ваш город <span>обязательно</span></div>
        <div class="value">
          <input type="text" name="city"/>
        </div>
      </div>

      <div class="item item-ic item-mail">
        <div class="level">Ваш e-mail <span>обязательно</span></div>
        <div class="value">
          <input type="text" name="email"/>
        </div>
      </div>

      <div class="item item-ic item-phone">
        <div class="level">Ваш номер телефона</div>
        <div class="value">
          <input type="text" name="phone" value=""/>
        </div>
      </div>

    </div>

    <div class="r">

      <div class="item">
        <div class="level">Категория вопроса</div>
        <div class="value">
          <select name="cat">
            <option value="Общие вопросы">Общие вопросы</option>
            <option value="Вопросы сотрудничества">Вопросы сотрудничества</option>
            <option value="Реклама">Реклама</option>
            <option value="Жалоба на товар">Жалоба на товар</option>
            <option value="Предложение">Предложение</option>
          </select>
        </div>
      </div>

      <div class="item">
        <div class="level">Текст сообщения <span>обязательно</span></div>
        <div class="value">
          <textarea name="text"></textarea>
        </div>
      </div>

    </div>

    <div class="clear_fix"></div>

    <div class="item recabdcha">
    <div class="g-recaptcha"  data-sitekey="6LfwJw4UAAAAAI5HOkWP_nAvMeK2bf_lU985qsfS"></div>
    <input type="hidden" class="hiddenRecaptcha required" name="hiddenRecaptcha" id="hiddenRecaptcha">
    </div>

    <div class="bt">
      <button type="submit">Отправить</button>
    </div>

    </form>
  </div><!-- .form-box -->

</div><!-- .dialog-callback -->

<div class="dialog-login">

  <div class="title">
    Авторизация
  </div>

  <div class="form-box">

    <div class="item item-ic item-mail">
      <div class="level">Ваш e-mail</div>
      <div class="value">
        <input type="text" name=""/>
      </div>
    </div>

    <div class="item item-ic item-passw">
      <div class="level">Пароль</div>
      <div class="value">
        <input type="password" name=""/>
      </div>
    </div>

    <div class="bt">
      <button type="submit">Войти</button>
    </div>

  </div>

</div><!-- .dialog-login -->

<div class="dialog-register">

  <div class="title">
    Стать партнером
  </div>

  <div class="form-box">

    <div class="l">

      <div class="item item-ic item-name">
        <div class="level">Ваше имя</div>
        <div class="value">
          <input type="text" name="" value="floesdesign"/>
        </div>
      </div>

      <div class="item item-ic item-city error">
        <div class="level">Ваш город</div>
        <div class="value">
          <input type="text" name="" placeholder="Обязательно к заполнению"/>
        </div>
      </div>

      <div class="item item-ic item-mail">
        <div class="level">Ваш e-mail</div>
        <div class="value">
          <input type="text" name=""/>
        </div>
      </div>

      <div class="item item-ic item-phone">
        <div class="level">Ваш номер телефона</div>
        <div class="value">
          <input type="text" name="" value="+7"/>
        </div>
      </div>

    </div>

    <div class="r">

      <div class="item">
        <div class="level">Расскажите о своей компании</div>
        <div class="value">
          <textarea name=""></textarea>
        </div>
      </div>

    </div>

    <div class="clear_fix"></div>

    <div class="bt">
      <button type="submit">Регистрация</button>
    </div>

  </div><!-- .form-box -->

</div><!-- .dialog-register -->

<div class="dialog-thank">
  <div class="text"></div>
</div><!-- .dialog-thank -->

<div class="floating-menu">
  <div class="wrapper">

    <div class="logo">
      <a href=""><img src="<?=$config['url'];?>i_harper/logo.png" alt=""/></a>
    </div>

    <div class="site-catalog">
      <ul>
        <li><a href="">Для автомобилей</a><?=menu_type(1);?></li>
        <li><a href="">Для дома</a><?=menu_type(2);?></li>
        <li><a href="">Звук</a><?=menu_type(3);?></li>
        <li><a href="">Аксессуары</a><?=menu_type(4);?></li>
      </ul>
    </div><!-- .site-catalog -->

    <div class="r">

      <div class="search">
        <a href=""></a>
        <div class="box">
        <form method="POST" action="/search/"><input type="text" name="search" placeholder="Искать по сайту ..."/> </form>
        </div>
      </div>


    </div>

  </div>
</div><!-- .floating-menu -->
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter41532829 = new Ya.Metrika({
                    id:41532829,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/41532829" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</body>
</html>