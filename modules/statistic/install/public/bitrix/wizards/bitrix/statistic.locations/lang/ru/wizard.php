<?
$MESS ['STATWIZ_NO_MODULE_ERROR'] = "Модуль статистики не установлен. Продолжение работы мастера не возможно.";
$MESS ['STATWIZ_FILES_NOT_FOUND'] = "Не найдено ни одного подходящего файла. Загрузите файлы с сайта www.maxmind.com или ipgeobase.ru или ip-to-country.webhosting.info в указанный выше каталог и попробуйте запустить мастер еще раз.";
$MESS ['STATWIZ_STEP1_TITLE'] = "Мастер создания индекса";
$MESS ['STATWIZ_STEP1_CONTENT'] = "Вас приветствует Мастер создания индексов для определения страны и города по IP адресу.<br />Выберите одно из действий:";
$MESS ['STATWIZ_STEP1_COUNTRY'] = "Создание индекса для определения <b>страны</b> по IP адресу.";
$MESS ['STATWIZ_STEP1_CITY'] = "Создание индекса для определения <b>страны</b> и <b>города</b> по IP адресу.";
$MESS ['STATWIZ_STEP1_COUNTRY_NOTE_V2'] = "Поддерживаются следующие форматы:
<ul>
<li><a target=\"_blank\" href=\"#GEOIP_HREF#\">GeoIP Country</a>.</li>
<li><a target=\"_blank\" href=\"#GEOIPLITE_HREF#\">GeoLite Country</a>.</li>
</ul>
";
$MESS ['STATWIZ_STEP1_CITY_NOTE'] = "Поддерживаются следующие форматы:
<ul>
<li><a target=\"_blank\" href=\"#GEOIP_HREF#\">GeoIP City</a>.</li>
<li><a target=\"_blank\" href=\"#GEOIPLITE_HREF#\">GeoLite City</a>.</li>
<li><a target=\"_blank\" href=\"#IPGEOBASE_HREF#\">IpGeoBase</a>.</li>
</ul>";
$MESS ['STATWIZ_STEP1_COMMON_NOTE'] = "Загруженные и распакованные файлы следует разместить в каталоге #PATH#. Затем вы можете перейти к следующему шагу мастера.";
$MESS ['STATWIZ_STEP2_TITLE'] = "Выбор CSV файлов";
$MESS ['STATWIZ_STEP2_COUNTRY_CHOOSEN'] = "Было выбрано создание индекса для определения <b>страны</b> по IP адресу.";
$MESS ['STATWIZ_STEP2_CITY_CHOOSEN'] = "Было выбрано создание индекса для определения <b>страны</b> и <b>города</b> по IP адресу.";
$MESS ['STATWIZ_STEP2_CONTENT'] = "Поиск подходящих файлов был выполнен в каталоге /bitrix/modules/statistic/ip2country.";
$MESS ['STATWIZ_STEP2_FILE_NAME'] = "Имя файла";
$MESS ['STATWIZ_STEP2_FILE_SIZE'] = "Размер";
$MESS ['STATWIZ_STEP2_DESCRIPTION'] = "Описание";
$MESS ['STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_COUNTRY'] = "База данных GeoIP Country или GeoLite Country.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_IP_TO_COUNTRY'] = "База данных ip-to-country.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_LOCATION'] = "Вторая часть базы данных GeoIP City или GeoLite City. Содержит соответствия блоков IP адресов и местоположений. Должна быть загружена после первой части.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_MAXMIND_CITY_LOCATION'] = "Первая часть базы данных GeoIP City или GeoLite City. Содержит местоположения.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_IPGEOBASE'] = "База данных блоков IP адресов IpGeoBase (только Россия). Для определения страны сначала загрузите индекс стран.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_IPGEOBASE2'] = "Вторая часть базы данных блоков IP адресов IpGeoBase. Содержит соответствия блоков IP адресов и местоположений. Должна быть загружена после первой части.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_IPGEOBASE2_CITY'] = "Первая часть базы данных блоков IP адресов IpGeoBase.Содержит местоположения. Для определения страны сначала загрузите индекс стран.";
$MESS ['STATWIZ_STEP2_FILE_TYPE_UNKNOWN'] = "Неизвестный формат.";
$MESS ['STATWIZ_STEP2_FILE_ERROR'] = "Не указан файл для загрузки";
$MESS ['STATWIZ_STEP3_TITLE'] = "Идет создание индекса.";
$MESS ['STATWIZ_STEP3_LOADING'] = "Идет обработка...";
$MESS ['STATWIZ_FINALSTEP_TITLE'] = "Работа мастера завершена";
$MESS ['STATWIZ_FINALSTEP_BUTTONTITLE'] = "Готово";
$MESS ['STATWIZ_FINALSTEP_COUNTRIES'] = "Стран: #COUNT#.";
$MESS ['STATWIZ_FINALSTEP_CITIES'] = "Городов: #COUNT#.";
$MESS ['STATWIZ_FINALSTEP_CITY_IPS'] = "IP диапазонов: #COUNT#.";
$MESS ['STATWIZ_CANCELSTEP_TITLE'] = "Работа мастера прервана";
$MESS ['STATWIZ_CANCELSTEP_BUTTONTITLE'] = "Закрыть";
$MESS ['STATWIZ_CANCELSTEP_CONTENT'] = "Работа мастера была прервана.";
?>