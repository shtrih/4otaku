<?

/* Конфиг, ня. Заполняем подключение к БД и удаляем .sample из имени файла */

// Соединение с базой данных

$def['db']['host'] = 'localhost';
$def['db']['user'] = '';
$def['db']['pass'] = '';
$def['db']['main_db'] = '';
$def['db']['wiki_db'] = '';
$def['db']['chat_db'] = '';
$def['db']['tracker_db'] = '';

// Соединение с github

$def['db']['github_tracker'] = 0; // 1 - чтобы включить использование бактрекера github
$def['db']['github_repo'] = '4otaku/4otaku';
$def['db']['github_user'] = '';
$def['db']['github_pass'] = '';

// Трекер

$def['tracker']['announce'] = '';

// Уведомления

$def['notify']['mail'] = 'admin@4otaku.ru';

// Переменные сайта

$def['site']['dir'] = ''; // Адрес от корня домена, по которому проживает сайт. Если сайт занимает весь домен, оставьте пустым.
$def['site']['domain'] = ''; // можно оставить пустым. используется для cookies.
$def['site']['name'] = '4отаку. Материалы для отаку.'; // Заголовок, он же title
$def['site']['short_name'] = '4отаку.'; // Короткая версия заголовка, для некоторых конструкций.

// Загрузка файлов

$def['booru']['filesize'] = 10*1024*1024;
$def['booru']['packsize'] = 200*1024*1024;
$def['booru']['thumbsize'] = 150;
$def['booru']['largethumbsize'] = 250;
$def['booru']['resizewidth'] = 750;
$def['booru']['resizeweight'] = 1.5*1024*1024;
$def['booru']['resizestep'] = 1.1;
$def['board']['filesize'] = 5*1024*1024;
$def['board']['flashsize'] = 10*1024*1024;
$def['board']['thumbwidth'] = 240;
$def['board']['thumbheight'] = 180;
$def['board']['maxcontent'] = 10;
$def['post']['picturesize'] = 2*1024*1024;
$def['post']['filesize'] = 10*1024*1024;
$def['video']['filesize'] = 50*1024*1024;

// Максимальная длинна слова, после которой вставляется разделитель

$def['text']['word_length'] = 25;

// Размеры видеороликов

$sets['video']['thumb'] = '480x360';
$sets['video']['full'] = '720x540';

// Непотребства и переводы, 1  - показывать, 0 - не показывать

$sets['show']['nsfw'] = 0;
$sets['show']['yaoi'] = 0;
$sets['show']['guro'] = 0;
$sets['show']['furry'] = 0;
$sets['show']['translation'] = 1;

// Большие тамбнейлы, ресайз изображений, 1 - да, 0 - нет

$sets['art']['largethumbs'] = 0;
$sets['art']['resized'] = 1;

// Слайдшоу

$sets['slideshow']['resize'] = 1;
$sets['slideshow']['auto'] = 0;
$sets['slideshow']['delay'] = 5;

// Количество постов/комментов/прочего на страницу

$sets['pp']['post'] = 5;
$sets['pp']['search'] = 5;
$sets['pp']['comment_in_post'] = 7;
$sets['pp']['comment_in_line'] = 5;
$sets['pp']['updates_in_line'] = 5;
$sets['pp']['post_gouf'] = 10;
$sets['pp']['video'] = 5;
$sets['pp']['art'] = 30;
$sets['pp']['art_tags'] = 20;
$sets['pp']['art_pool'] = 40;
$sets['pp']['art_cg_pool'] = 20;
$sets['pp']['board'] = 10;
$sets['pp']['board_posts'] = 7;
$sets['pp']['tags'] = 40;
$sets['pp']['tags_admin'] = 40;
$sets['pp']['news'] = 5;
$sets['pp']['latest_comments'] = 3;
$sets['pp']['random_orders'] = 5;

// Свернутость/равзернутость блоков меню, направление комментов
// 1 - развернутый блок/инвертированное дерево, 0 - свернутый блок/обычное дерево

$sets['dir']['navi'] = 1;
$sets['dir']['settings'] = 0;
$sets['dir']['comment'] = 1;
$sets['dir']['update'] = 1;
$sets['dir']['gouf'] = 1;
$sets['dir']['order'] = 1;
$sets['dir']['quick'] = 1;
$sets['dir']['tag'] = 1;
$sets['dir']['art_tag'] = 1;
$sets['dir']['masstag'] = 0;
$sets['dir']['board_list'] = 1;
$sets['dir']['comments_tree'] = 1;

// Режимы для раздела с артом. 0 - выключено, 1 - включено

$sets['art']['blank_mode'] = 0;
$sets['art']['download_mode'] = 0;

// Режимы для борды. 0 - выключено, 1 - включено

$sets['board']['allthreads'] = 0; // Все треды на главной вместо приветствия
$sets['board']['embedvideo'] = 0; // Сразу вставлять ютуб в тело страницы

// Поиск

$def['search']['levenstein'] = 1; // Расстояние Левенштейна, используемое при определении возможных опечаток
$def['search']['variants'] = 10; // Максимальное количество вариантов опечаток, которое представить пользователю

// Имя/мыло пользователя по умолчанию

$def['user']['author'] = 'Анонимно';
$def['user']['name'] = 'Анонимно';
$def['user']['mail'] = 'default@avatar.mail';
$sets['user']['rights'] = 0;
$sets['user']['name'] = 'Анонимно';
$sets['user']['trip'] = '';
$sets['user']['mail'] = 'default@avatar.mail';

$sets['edit']['newtags'] = 1;

// Presets - fight the notices

$sets['visit']['post'] = 0;
$sets['visit']['board'] = 0;
$sets['visit']['video'] = 0;
$sets['visit']['art'] = 0;
$sets['news']['read'] = 0;

// Область rss по умолчанию

$sets['rss']['default'] = 'pvun';
$def['rss']['default'] = 'pvun';

// Области сайта

$def['area'][] = 'main';
$def['area'][] = 'workshop';
$def['area'][] = 'flea_market';
$def['area'][] = 'sprites';

// Разделы сайта

$def['type'][] = 'post';
$def['type'][] = 'video';
$def['type'][] = 'art';
