<?
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_NAME'] = "Превышение лимита активности";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_DESC'] = "#ACTIVITY_TIME_LIMIT# - тестовый интервал времени
#ACTIVITY_HITS# - кол-во хитов за тестовый интервал времени
#ACTIVITY_HITS_LIMIT# - максимальное кол-во хитов за тестовый интервал времени (лимит активности)
#ACTIVITY_EXCEEDING# - превышение кол-ва хитов
#CURRENT_TIME# - момент блокировки (время на сервере)
#DELAY_TIME# - длительность блокировки
#USER_AGENT# - UserAgent
#SESSION_ID# - ID сессии
#SESSION_LINK# - ссылка на сессию
#SERACHER_ID# - ID поисковика
#SEARCHER_NAME# - наименование поисковика
#SEARCHER_LINK# - ссылка на список хитов поисковика
#VISITOR_ID# - ID посетителя
#VISITOR_LINK# - ссылка на профайл посетителя
#STOPLIST_LINK# - ссылка для добавления посетителя в стоп-лист
";
$MESS ['STATISTIC_DAILY_REPORT_NAME'] = "Ежедневная статистика сайта";
$MESS ['STATISTIC_DAILY_REPORT_DESC'] = "#EMAIL_TO# - email администратора сайта
#SERVER_TIME# - время на сервере в момент отсылки отчета
#HTML_HEADER# - открытие тэга HTML + CSS стили
#HTML_COMMON# - таблица посещаемости сайта (хиты, сессии, хосты, посетители, события) (HTML)
#HTML_ADV# - таблица рекламных кампаний (TOP 10) (HTML)
#HTML_EVENTS# - таблица типов событий (TOP 10) (HTML)
#HTML_REFERERS# - таблица ссылающихся сайтов (TOP 10) (HTML)
#HTML_PHRASES# - таблица поисковых фраз (TOP 10) (HTML)
#HTML_SEARCHERS# - таблица индексации сайта (TOP 10) (HTML)
#HTML_FOOTER# - закрытие тэга HTML
";
$MESS ['STATISTIC_DAILY_REPORT_SUBJECT'] = "#SERVER_NAME#: Статистика сайта (#SERVER_TIME#)";
$MESS ['STATISTIC_DAILY_REPORT_MESSAGE'] = "#HTML_HEADER#
<font class='h2'>Обобщенная статистика сайта <font color='#A52929'>#SITE_NAME#</font><br>
Данные на <font color='#0D716F'>#SERVER_TIME#</font></font>
<br><br>
<a class='tablebodylink' href='http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#'>http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#</a>
<br>
<hr><br>
#HTML_COMMON#
<br>
#HTML_ADV#
<br>
#HTML_REFERERS#
<br>
#HTML_PHRASES#
<br>
#HTML_SEARCHERS#
<br>
#HTML_EVENTS#
<br>
<hr>
<a class='tablebodylink' href='http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#'>http://#SERVER_NAME#/bitrix/admin/stat_list.php?lang=#LANGUAGE_ID#</a>
#HTML_FOOTER#
";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_SUBJECT'] = "#SERVER_NAME#: Превышен лимит активности";
$MESS ['STATISTIC_ACTIVITY_EXCEEDING_MESSAGE'] = "На сайте #SERVER_NAME# посетитель превысил установленный лимит активности.

Начиная с #CURRENT_TIME# посетитель заблокирован на #DELAY_TIME# сек.

Активность  - #ACTIVITY_HITS# хитов за #ACTIVITY_TIME_LIMIT# сек. (лимит - #ACTIVITY_HITS_LIMIT#)
Посетитель  - #VISITOR_ID#
Сессия      - #SESSION_ID#
Поисковик   - [#SERACHER_ID#] #SEARCHER_NAME#
UserAgent   - #USER_AGENT#

>===============================================================================================
Для добавления в стоп-лист воспользуйтесь нижеследующей ссылкой:
http://#SERVER_NAME##STOPLIST_LINK#
Для просмотра сессии посетителя воспользуйтесь нижеследующей ссылкой:
http://#SERVER_NAME##SESSION_LINK#
Для просмотра профайла посетителя воспользуйтесь нижеследующей ссылкой:
http://#SERVER_NAME##VISITOR_LINK#
Для просмотра статистики хитов поисковика воспользуйтесь нижеследующей ссылкой:
http://#SERVER_NAME##SEARCHER_LINK#
";
?>