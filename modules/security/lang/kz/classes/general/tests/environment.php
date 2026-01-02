<?php
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR"] = "Уақытша файлдар жобаның түбірлік директориясында сақталады";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_ADDITIONAL"] = "Ағымдағы директория: #DIR#";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_DETAIL"] = "СTempfile пайдалану кезінде жасалған уақытша файлдарды жобаның түбірлік директориясында сақтау ұсынылмайды және өзімен бірге бірқатар тәуекелдерді алып жүреді.";
$MESS["SECURITY_SITE_CHECKER_BITRIX_TMP_DIR_RECOMMENDATION"] = "\"bitrix/php_interface/dbconn.php\" ішінде қажет жолды көрсете отырып, \"BX_TEMPORARY_FILES_DIRECTORY\" тұрақтысын анықтау қажет.<br>
Келесі қадамдарды орындаңыз:<br>
1. Жобаның түбірінен тыс директорияны таңдаңыз. Мысалы, ол \"/home/bitrix/tmp/www\" болуы мүмкін<br>
2. Оны жасаңыз. Ол үшін келесі пәрменді орындаңыз:
<pre>
mkdir -p -m 700 /директорияға/толық/жол
</pre>
3. \"bitrix/php_interface/dbconn.php\" файлында жүйе осы директорияны қолдануы үшін сәйкес тұрақтыны анықтаңыз:
<pre>
define(\"BX_TEMPORARY_FILES_DIRECTORY\", \"/директорияға/толық/жол\");
</pre>";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION"] = "Болжам бойынша сессияларды сақтау каталогында басқа жобалардың сессиялары бар";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_ADDITIONAL_OWNER"] = "Себебі: файл иесі ағымдағы пайдаланушыдан өзгеше<br>
Файл: #FILE#<br>
Файл иесінің UID: #FILE_ONWER#<br>
Ағымдағы пайдаланушының UID: #CURRENT_OWNER#<br>";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_ADDITIONAL_SIGN"] = "Себебі: сессия файлында ағымдағы сайттың қолтаңбасы жоқ<br>
Файл: #FILE#<br>
Ағымдағы сайт қолтаңбасы: #SIGN#<br>
Файл мазмұны: <pre>#FILE_CONTENT#</pre>";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_DETAIL"] = "Бұл басқа виртуалды серверлердің скрипттері арқылы сессия деректерін оқуға/өзгертуге мүмкіндік береді";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_RECOMMENDATION"] = "Сақтау директориясын өзгерту немесе ДҚ-да сессияларды сақтауды қосу: <a href=\"/bitrix/admin/security_session.php\">Сессияларды қорғау</a>";
$MESS["SECURITY_SITE_CHECKER_EnvironmentTest_NAME"] = "Орта баптауларын тексеру";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER"] = "PHP артықшылықты пайдаланушы ретінде жұмыс істейді";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_ADDITIONAL"] = "#UID#/#GID#";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_DETAIL"] = "PHP-дің артықшылықты пайдаланушы ретінде жұмыс істеуі (мысалы, root) сіздің жобаңыздың қауіпсіздігіне әсер етуі мүмкін";
$MESS["SECURITY_SITE_CHECKER_PHP_PRIVILEGED_USER_RECOMMENDATION"] = "Серверді PHP артықшылықсыз пайдаланушы ретінде жұмыс істейтін етіп конфигурациялау";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR"] = "Сессия файлдарын сақтау директориясы барлық жүйелік пайдаланушылар үшін қолжетімді";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_ADDITIONAL"] = "Сессияларды сақтау директориясы: #DIR#<br>
Құқықтар: #PERMS#";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_DETAIL"] = "Бұл басқа виртуалды серверлердің скрипттері арқылы сессия деректерін оқуға/өзгертуге мүмкіндік береді";
$MESS["SECURITY_SITE_CHECKER_SESSION_DIR_RECOMMENDATION"] = "Файл құқықтарын дұрыс баптау, сақтау директориясын өзгерту немесе ДҚ-да сессияларды сақтауды қосу: <a href=\"/bitrix/admin/security_session.php\">Сессияларды қорғау</a>";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP"] = "PHP скрипттері жүктелетін файлдарды сақтау директориясында орындалады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DETAIL"] = "Әзірлеушілер кейде файл атауларын дұрыс сүзуді ұмытып кетеді, егер бұл орын алса, шабуылдаушы сіздің жобаңызды толық басқара алады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE"] = "Қос кеңейтімі бар PHP скрипттер (eg php.lala) жүктелетін файлдарды сақтау директориясында орындалады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_DETAIL"] = "Әзірлеушілер кейде файл атауларын дұрыс сүзуді ұмытып кетеді, егер бұл орын алса, шабуылдаушы сіздің жобаңызды толық басқара алады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_RECOMMENDATION"] = "Веб-серверді дұрыс баптау";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_RECOMMENDATION"] = "Веб-серверді дұрыс баптау";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY"] = "Py скрипттер жүктелетін файлдарды сақтау директориясында орындалады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_DETAIL"] = "Әзірлеушілер кейде файл атауларын дұрыс сүзуді ұмытып кетеді, егер бұл орын алса, шабуылдаушы сіздің жобаңызды толық басқара алады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_RECOMMENDATION"] = "Веб-серверді дұрыс баптау";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS"] = ".htaccess файлдарын Apache жүктелетін файлдарды сақтау директориясында өңдеуге болмайды";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_DETAIL"] = "Әзірлеушілер кейде файл атауларын дұрыс сүзуді ұмытып кетеді, егер бұл орын алса, шабуылдаушы сіздің жобаңызды толық басқара алады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_RECOMMENDATION"] = "Веб-серверді дұрыс баптау";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION"] = "Apache Content Negotiation жүктелетін файлдарды сақтау директориясында рұқсат етілген";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_DETAIL"] = "Apache Content Negotiation пайдалануға ұсынылмайды, себебі ол XSS шабуылының көзі бола алады";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_RECOMMENDATION"] = "Веб-серверді дұрыс баптау";
