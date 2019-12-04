<?php
session_start();

include ('config/db.php');
include ('config/register.php');
include ('config/captcha.php');
include ('config/login.php');
include ('config/logout.php');
include ('config/dbcreateifnone.php');

db_create_if_none();
setToSessionIfPosted('namemail', 'namemail');
setToSessionIfPosted('login_password', 'upass');

$_SESSION['isAuthorized'] = isset($_SESSION['displayedname']) ? 1 : 0;
$_SESSION['showLogin'] = true;
$_SESSION['showSignUp'] = false;
$_SESSION['showLogOut'] = false;
$_SESSION['LogPassError'] = null;
$_SESSION['nameExist'] = false;
$_SESSION['mailExist'] = false;

// Shows errors at registration attempt
function showErrorMsg() {
    if ($_SESSION['mailExist']) echo "Пользователь с таким e-mail<br>уже существует";
    elseif ($_SESSION['nameExist']) echo "Пользователь с таким именем<br>уже существует";
}

// errors at logIn attempt
function loggingInErr() {
    if (!empty($_SESSION['LogPassError']) != null) {
        echo $_SESSION['LogPassError'];
    }
    elseif ($_SESSION['isAuthorized'] == 1) {
        #echo "isAuthorized";
    }
}

// Puts $_post['$postkey'] to $_session['$sesskey']
function setToSessionIfPosted($postkey, $sesskey) {
    if (isset($_POST[$postkey]) && $_POST[$postkey] != '' && $_POST[$postkey] != null) {
        $_SESSION[$sesskey] = $_POST[$postkey];
        if ($_SESSION[$sesskey] == '') {
            unset($_SESSION[$sesskey]);
        }
    }
}

// Takes string $_SESSION[key]
function showForm($form) {
    switch ($form) {
        case 'LogOut':
//    $_COOKIE['logged_user_id'] = password_hash($_SESSION['displayedname'], PASSWORD_BCRYPT);
    $_SESSION['isAuthorized'] = true;
    $_SESSION['showLogOut'] = true;
    $_SESSION['showLogin'] = false;
    $_SESSION['showSignUp'] = false;
        break;
        case 'LogIn':
    $_SESSION['showLogin'] = true;
    $_SESSION['isAuthorized'] = false;
    $_SESSION['showLogOut'] = false;
    $_SESSION['showSignUp'] = false;
        break;
        case 'SignUp':
    $_SESSION['showSignUp'] = true;
    $_SESSION['isAuthorized'] = false;
    $_SESSION['showLogOut'] = false;
    $_SESSION['showLogin'] = false;
        break;
    }
}

// Takes boolean, echoes "block" if true
function blockNone($boolexp) {
    echo ($boolexp) ? "block" : "none";
}

if ((isset($_POST['login_password']) && isset($_POST['namemail']))) {
    if ($_POST['login_password'] != '' && $_POST['namemail'] != '') {
        echo "goLogin called";
        goLogin();
    }
}

if (isset($_POST['hidden']) && ($_POST['login_password'] == '' | $_POST['namemail'] == '')) {
    showForm('SignUp');
}

if (isset($_POST['login']) && isset($_POST['email']) && isset($_POST['user_password'])) {
    goSignUp();
}

if (isset($_COOKIE['logged_user_id']) && isset($_SESSION['displayedname'])) {
    showForm('LogOut');
}

if ($_SESSION['isAuthorized'] == 1 && $_SESSION['showLogOut'] == true && isset($_SESSION['displayedname']) && isset($_POST['hiddenlogout'])) {
    goLogout();
}

?><!DOCTYPE html><html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poiret+One">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Andika&subset=cyrillic">
    <link href="https://fonts.googleapis.com/css?family=Forum&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/authnav.scss">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/photoswipe/photoswipe.css">
    <link rel="stylesheet" href="css/photoswipe/default-skin/default-skin.css">

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="./js/wow.min.js"></script>
    <script type="text/javascript" src="./js/authnav.js"></script>
    <script type="text/javascript" src="./js/photoswipe/photoswipe.min.js"></script>
    <script type="text/javascript" src="./js/photoswipe/ui/photoswipe-ui-default.min.js"></script>
    <!-- my friend wrote this JS part -->
    <script type="text/javascript" >
        var initPhotoSwipeFromDOM = function(gallerySelector) {
            // parse slide data (url, title, size ...) from DOM elements 
            // (children of gallerySelector)
            var parseThumbnailElements = function(el) {
                var thumbElements = el.childNodes,
                    numNodes = thumbElements.length,
                    items = [],
                    figureEl,
                    linkEl,
                    size,
                    item;

                for(var i = 0; i < numNodes; i++) {
                    figureEl = thumbElements[i]; // <figure> element

                    // include only element nodes 
                    if(figureEl.nodeType !== 1) {
                        continue;
                    }

                    linkEl = figureEl.children[0]; // <a> element
                    size = linkEl.getAttribute('data-size').split('x');

                    // create slide object
                    item = {
                        src: linkEl.getAttribute('href'),
                        w: parseInt(size[0], 10),
                        h: parseInt(size[1], 10)
                    };

                    if(figureEl.children.length > 1) {
                        // <figcaption> content
                        item.title = figureEl.children[1].innerHTML; 
                    }

                    if(linkEl.children.length > 0) {
                        // <img> thumbnail element, retrieving thumbnail url
                        item.msrc = linkEl.children[0].getAttribute('src');
                    } 

                    item.el = figureEl; // save link to element for getThumbBoundsFn
                    items.push(item);
                }
                return items;
            };

            // find nearest parent element
            var closest = function closest(el, fn) {
                return el && ( fn(el) ? el : closest(el.parentNode, fn) );
            };

            // triggers when user clicks on thumbnail
            var onThumbnailsClick = function(e) {
                e = e || window.event;
                e.preventDefault ? e.preventDefault() : e.returnValue = false;

                var eTarget = e.target || e.srcElement;

                // find root element of slide
                var clickedListItem = closest(eTarget, function(el) {
                    return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
                });

                if(!clickedListItem) {
                    return;
                }

                // find index of clicked item by looping through all child nodes
                // alternatively, you may define index via data- attribute
                var clickedGallery = clickedListItem.parentNode;
                var childNodes = clickedListItem.parentNode.childNodes;
                var numChildNodes = childNodes.length;
                var nodeIndex = 0;
                var index;

                for (var i = 0; i < numChildNodes; i++) {
                    if (childNodes[i].nodeType !== 1) { 
                        continue; 
                    }

                    if (childNodes[i] === clickedListItem) {
                        index = nodeIndex;
                        break;
                    }
                    nodeIndex++;
                }

                if (index >= 0) {
                    // open PhotoSwipe if valid index found
                    openPhotoSwipe( index, clickedGallery );
                }

                return false;
            };

            // parse picture index and gallery index from URL (#&pid=1&gid=2)
            var photoswipeParseHash = function() {
                var hash = window.location.hash.substring(1),
                params = {};

                if (hash.length < 5) {
                    return params;
                }

                var vars = hash.split('&');
                for (var i = 0; i < vars.length; i++) {
                    if (!vars[i]) {
                        continue;
                    }

                    var pair = vars[i].split('=');  
                    if (pair.length < 2) {
                        continue;
                    }

                    params[pair[0]] = pair[1];
                }

                if (params.gid) {
                    params.gid = parseInt(params.gid, 10);
                }

                return params;
            };

            var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
                var pswpElement = document.querySelectorAll('.pswp')[0];
                var gallery;

                var items = parseThumbnailElements(galleryElement);

                // define options (if needed)
                var options = {

                    // define gallery index (for URL)
                    galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                    getThumbBoundsFn: function(index) {
                        // See Options -> getThumbBoundsFn section of documentation for more info
                        var thumbnail = items[index].el.getElementsByTagName('img')[0]; // find thumbnail
                        var pageYScroll = window.pageYOffset || document.documentElement.scrollTop;
                        var rect = thumbnail.getBoundingClientRect();

                        return { x:rect.left, y:rect.top + pageYScroll, w:rect.width };
                    }
                };

                // PhotoSwipe opened from URL
                if (fromURL) {
                    if (options.galleryPIDs) {
                        // parse real index when custom PIDs are used 
                        // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                        for (var j = 0; j < items.length; j++) {
                            if (items[j].pid == index) {
                                options.index = j;
                                break;
                            }
                        }
                    } else {
                        // in URL indexes start from 1
                        options.index = parseInt(index, 10) - 1;
                    }
                } else {
                    options.index = parseInt(index, 10);
                }

                // exit if index not found
                if (isNaN(options.index) ) {
                    return;
                }

                if (disableAnimation) {
                    options.showAnimationDuration = 0;
                }

                // Pass data to PhotoSwipe and initialize it
                gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
                gallery.init();
            };

            // loop through all gallery elements and bind events
            var galleryElements = document.querySelectorAll(gallerySelector);

            for (var i = 0, l = galleryElements.length; i < l; i++) {
                galleryElements[i].setAttribute('data-pswp-uid', i+1);
                galleryElements[i].onclick = onThumbnailsClick;
            }

            // Parse URL and open gallery if it contains #&pid=3&gid=1
            var hashData = photoswipeParseHash();
            if (hashData.pid && hashData.gid) {
                openPhotoSwipe(hashData.pid, galleryElements[hashData.gid - 1], true, true);
            }
        };

        $(function() {
            var pswpElement = document.querySelectorAll('.pswp')[0];
            var items = [{src: 'img/weddings1.jpg', w: 1280, h: 960}, {src: 'img/weddings2.jpg', w: 960, h: 1280}, {src: 'img/weddings3.jpg', w: 1210, h: 1280}, {src: 'img/weddings4.jpg', w: 854, h: 854}];

            var options = { index: 0 };
            var wow = new WOW();
            wow.init();
            
            initPhotoSwipeFromDOM('.my-gallery');
        });
    </script>
    
    <title>Мастер-класс</title>
</head>

<body>
<div class="wrapper">
<!-- login: -->

<div id="logindiv">
    <form id="loginf" method="POST" action="" style="display: <?php blockNone($_SESSION['showLogin'] == true); ?>">

    <p id="p_auth" class="header"><?php loggingInErr(); ?></p>
    <input type="submit" name="GoToSign" value="Регистрация">
    <input type="text" name="namemail" placeholder="Имя или e-mail">
    <input type="password" name="login_password" placeholder="Пароль">
    <input type="submit" value="Войти">
    <input type="text" name="hidden" style="display: none" value="val">
    </form>

    <form id="signupf" method="POST" action="" style="display: <?php echo ($_SESSION['isAuthorized'] == 0 && $_SESSION['showSignUp'] == true) ? "block" : "none" ?>">

        <input type="text" name="login" placeholder="Имя" required>
        <input type="text" name="email" placeholder="e-mail" required>
        <input type="password" name="user_password" placeholder="Пароль" required>
        <input type="submit" value="Зарегистрироваться" style="font-size: 18px">
        <p id="p_auth"><?php showErrorMsg();?></p>
    </form>
    
    <form id="logoutf" action="" method="POST" style="display:
       <?php blockNone($_SESSION['showLogOut'] == true); ?>">
        <p id="p_auth_out"><?php
         echo empty($_SESSION['displayedname']) ? "" : "Вы вошли как<br></p><p id=\"p_auth_out\" style=\"margin-top: -15px\">".$_SESSION['displayedname'];
        ?></p>
        <input type="submit" value="Выйти">
        <input type="text" name="hiddenlogout" value="GoLogin" style="display: none">
    </form>
</div>
<!-- logout: -->
   
    <div class='box'>Мастер-класс по большим цветам в Иваново</div>
    <div class='adv'>За 3 часа создай свой уникальный цветок</div>
    <div class="adv">Мастер-класс будет проходить 8 июня по адресу ____________, __</div>
    <div class="filler" style="height: 240px"></div>
    <div class='spacer'>Запишись на мастер-класс до 22 июня и получи скидку 20%</div>
    <div class="section wow fadeInUpBig" data-wow-duration="1400ms" data-wow-offset ="-50">работы нашей студии</div>

<!-- frame -->
    <div class="frame" id="frametop">
    <div class="lable wow fadeInUpBig" data-wow-duration="1500ms" data-wow-offset ="-30">Мы украшали большие пионы на праздник 9-го мая</div>
    <img id="imgtop" class="wow fadeInUp" data-wow-duration="900ms" data-wow-delay="100ms" data-wow-offset ="-50" style="height: 600px; width: 600px;" src="img/Decor-2019-05-11.jpg" alt="may9" >
    </div>
<!-- /frame -->


<div class="frame" id="frameleft">
<div class="lable lable_left wow fadeIn" data-wow-duration="1900ms">Оформляли праздники, свадьбы, торжественные мероприятия</div>

    <div class="my-gallery gallery_first wow fadeInRight" data-wow-duration="900ms" data-wow-delay="100ms" itemscope itemtype="http://schema.org/ImageGallery">

    <figure id="fig0" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall1/weddings1.jpg" itemprop="contentUrl" data-size="960x1280">
            <img src="img/gall1/weddings1thmb.jpg" itemprop="thumbnail" alt="Свадебные украшения" />
        </a>
        <figcaption itemprop="caption description">Image caption 1</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall1/weddings2.jpg" itemprop="contentUrl" data-size="1280x960">
            <img src="img/gall1/weddings2thmb.jpg" itemprop="thumbnail" alt="Бумажные цветы" />
        </a>
        <figcaption itemprop="caption description">Image caption 2</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall1/weddings3.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall1/weddings3thmb.jpg" itemprop="thumbnail" alt="Праздничные украшения" />
        </a>
        <figcaption itemprop="caption description">Image caption 3</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall1/weddings4.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall1/weddings4thmb.jpg" itemprop="thumbnail" alt="Большие цветы из бумаги" />
        </a>
        <figcaption itemprop="caption description">Image caption 4</figcaption>
    </figure>
    
    <figure id="fig2" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall1/mini-rose1.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall1/mini-rose1thmb.jpg" itemprop="thumbnail" alt="Большие декоративные цветы" />
        </a>
        <figcaption itemprop="caption description">Image caption 5</figcaption>
    </figure>    
</div>

<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="frame" id="frame_second">
<div class="lable lable_left wow fadeIn" data-wow-duration="1900ms">Украшали лаунж-площадки, витрины, фото-зоны</div>
    <div id="gallery_second" class="my-gallery wow fadeInRight" data-wow-duration="900ms" data-wow-delay="100ms" itemscope itemtype="http://schema.org/ImageGallery">
    <figure id="fig0" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/artificial_flower.jpg" itemprop="contentUrl" data-size="1080x1080">
            <img src="img/gall2/1thmb.jpg" itemprop="thumbnail" alt="Украшения для помещений" />
        </a>
        <figcaption itemprop="caption description">Image caption 1</figcaption>
    </figure>
    
    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/decorative_rose.jpg" itemprop="contentUrl" data-size="890x890">
            <img src="img/gall2/2thmb.jpg" itemprop="thumbnail" alt="Бумажные пионы" />
        </a>
        <figcaption itemprop="caption description">Image caption 2</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/red_rose.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall2/3thmb.jpg" itemprop="thumbnail" alt="Праздничные handmade украшения" />
        </a>
        <figcaption itemprop="caption description">Image caption 3</figcaption>
    </figure>
    
    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/room_flowers.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall2/4thmb.jpg" itemprop="thumbnail" alt="Цветы из бумаги" />
        </a>
        <figcaption itemprop="caption description">Image caption 4</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/common_peony.jpg" itemprop="contentUrl" data-size="1080x1080">
            <img src="img/gall2/5thmb.jpg" itemprop="thumbnail" alt="Большие бумажные цветы" />
        </a>
        <figcaption itemprop="caption description">Image caption 5</figcaption>
    </figure>
    
    <figure id="fig2_1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall2/tea_rose.jpg" itemprop="contentUrl" data-size="1240x1240">
            <img src="img/gall2/6thmb.jpg" itemprop="thumbnail" alt="Большие цветы" />
        </a>
        <figcaption itemprop="caption description">Image caption 6</figcaption>
    </figure>    
</div>

<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="frame" id="frame_last">
<div class="lable lable_left wow fadeIn" data-wow-duration="1900ms">Наш опыт - это hand-made, небольшие радующие глаз вещи, броши, заколки, просто красивые боксы для подарка или сувенира</div>

    <div id="gallery_last" class="my-gallery wow fadeInRight" data-wow-duration="900ms" data-wow-delay="100ms" itemscope itemtype="http://schema.org/ImageGallery">

    <figure id="fig0" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall3/handmade_flower.jpg" itemprop="contentUrl" data-size="1080x1080">
          <img src="img/gall3/1thmb.jpg" itemprop="thumbnail" alt="Украшения для помещений" />
        </a>
        <figcaption itemprop="caption description">Image caption 1</figcaption>
    </figure>
    
    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall3/2thmb.jpg" itemprop="contentUrl" data-size="890x890">
            <img src="img/gall3/2thmb.jpg" itemprop="thumbnail" alt="Бумажные пионы" />
        </a>
        <figcaption itemprop="caption description">Image caption 2</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall3/handmade_pink_rose.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall3/3thmb.jpg" itemprop="thumbnail" alt="Праздничные handmade украшения" />
        </a>
        <figcaption itemprop="caption description">Image caption 3</figcaption>
    </figure>

    <figure id="fig1" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall3/handmade_flower_box.jpg" itemprop="contentUrl" data-size="960x960">
            <img src="img/gall3/4thmb.jpg" itemprop="thumbnail" alt="Цветы из бумаги" />
        </a>
        <figcaption itemprop="caption description">Image caption 4</figcaption>
    </figure>
    
    <figure id="fig2" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
        <a href="img/gall3/handmade_flowers.jpg" itemprop="contentUrl" data-size="1240x1240">
            <img src="img/gall3/5thmb.jpg" itemprop="thumbnail" alt="Большие цветы" />
        </a>
        <figcaption itemprop="caption description">Image caption 5</figcaption>
    </figure>
</div>

<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>

</div>
<!-- last gallery -->

<!-- captcha -->
<?php
captcha_handler();
?>

<div id="feedback" <?php echo $_SESSION['isCommented'] ? "style=\"display: none\"" : "style=\"display: block\"";?>>
<p id="p_feedb">Оставьте комментарий</p>
<form id="feedbf" action="" method="POST">
    <input type="text" name="user_name" placeholder="Имя" <?php echo $_SESSION['isAuthorized'] == 0 ? "style=\"display: inline-block\" required" : "style=\"display: none\"";?>>
    <input type="email" name="user_email" placeholder="e-mail" <?php echo $_SESSION['isAuthorized'] == 0 ? "style=\"display: inline-block\" required" : "style=\"display: none\"";?>>
    <textarea id="comment" name="comment" cols="40" rows="8" required></textarea>
    <input type="text" name="ucaptcha" placeholder="Введите числа с картинки" <?php echo $_SESSION['isAuthorized'] == 0 ? "style=\"display: inline-block\" required" : "style=\"display: none\""; ?>>
    <input type="submit" value="Отправить">
</form>
<?php
if ($_SESSION['isAuthorized'] == 0) {
    for ($ic = 1; $ic < 6; $ic++) echo $_SESSION['$captPictures'][$ic];
}
?>
</div>
<p id="thanks" <?php echo !$_SESSION['isCommented'] ? "style=\"display: none\"" : "style=\"color: black; display: block\""?>><i>Спасибо за Ваш интерес! Мы свяжемся с Вами в ближайшее время.</i></p>
<!-- /captcha -->

</div>
    <div class="phone">Контакты:<br>
    VK: vk.com/__________<br>
    Instagram: @_________________<br>
    Телефон: +7-___-___-__-__</div>
<footer>
    <div class="last">
        Научись делать декорации, творить искусство и создавать неповторимый декор без усилий с интересом и наслаждением!
    </div>
</footer>
<div class="low">
</div>
</body>
</html>