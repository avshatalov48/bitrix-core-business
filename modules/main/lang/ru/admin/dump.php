<?
$MESS["MAIN_DUMP_FILE_CNT"] = "Файлов в архиве:";
$MESS["MAIN_DUMP_FILE_SIZE"] = "Размер данных:";
$MESS["MAIN_DUMP_FILE_FINISH"] = "Создание резервной копии завершено";
$MESS["MAIN_DUMP_FILE_MAX_SIZE"] = "Исключить из архива файлы размером более (0 - без ограничения):";
$MESS["MAIN_DUMP_FILE_STEP_SLEEP"] = "интервал:";
$MESS["MAIN_DUMP_FILE_STEP_sec"] = "сек.";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_b"] = "б ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_kb"] = "кб ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_mb"] = "Мб ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_gb"] = "Гб ";
$MESS["MAIN_DUMP_FILE_DUMP_BUTTON"] = "Создать резервную копию";
$MESS["MAIN_DUMP_FILE_STOP_BUTTON"] = "Прервать";
$MESS["MAIN_DUMP_FILE_KERNEL"] = "Архивировать ядро:";
$MESS["MAIN_DUMP_FILE_NAME"] = "Имя";
$MESS["FILE_SIZE"] = "Размер файла";
$MESS["MAIN_DUMP_FILE_TIMESTAMP"] = "Изменен";
$MESS["MAIN_DUMP_FILE_PUBLIC"] = "Архивировать публичную часть:";
$MESS["MAIN_DUMP_BASE_STAT"] = "статистику";
$MESS["MAIN_DUMP_BASE_SINDEX"] = "поисковый индекс";
$MESS["MAIN_DUMP_BASE_SIZE"] = "МБ";
$MESS["MAIN_DUMP_PAGE_TITLE"] = "Резервное копирование";
$MESS["MAIN_DUMP_LIST_PAGE_TITLE"] = "Список резервных копий";
$MESS["MAIN_DUMP_AUTO_PAGE_TITLE"] = "Регулярное резервное копирование";
$MESS["MAIN_DUMP_AUTO_BUTTON"] = "Регулярное резервное копирование";
$MESS["MAIN_DUMP_SITE_PROC"] = "Архивация файлов";
$MESS["MAIN_DUMP_ARC_SIZE"] = "Размер архива:";
$MESS["MAIN_DUMP_TABLE_FINISH"] = "Сохранено таблиц:";
$MESS["MAIN_DUMP_ACTION_DOWNLOAD"] = "Скачать";
$MESS["MAIN_DUMP_DELETE"] = "Удалить";
$MESS["MAIN_DUMP_ALERT_DELETE"] = "Вы уверены, что хотите удалить файл?";
$MESS["MAIN_DUMP_FILE_PAGES"] = "Резервные копии";
$MESS["MAIN_RIGHT_CONFIRM_EXECUTE"] = "Внимание! Восстановление резервной копии на действующем сайте может привести к повреждению сайта! Продолжить?";
$MESS["MAIN_DUMP_RESTORE"] = "Восстановить";
$MESS["MAIN_DUMP_MYSQL_ONLY"] = "Система резервного копирования работает только с базой данных MySQL. Пожалуйста, используйте внешние инструменты для создания резервной копии базы данных.";
$MESS["MAIN_DUMP_HEADER_MSG1"] = "Для переноса резервной копии сайта на другой хостинг поместите в корневой папке нового сайта скрипт для восстановления <a href=\"#EXPORT#\">restore.php</a>, затем наберите в строке браузера &quot;&lt;имя сайта&gt;/restore.php&quot; и следуйте инструкциям по распаковке.<br>Подробная инструкция доступна в <a href=\"https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=35&CHAPTER_ID=04833\" target=_blank>разделе справки</a>.";
$MESS["MAIN_DUMP_SKIP_SYMLINKS"] = "Пропускать символические ссылки на директории:";
$MESS["MAIN_DUMP_MASK"] = "Исключить из архива файлы и директории по маске:";
$MESS["MAIN_DUMP_MORE"] = "Ещё...";
$MESS["MAIN_DUMP_FOOTER_MASK"] = "Для маски исключения действуют следующие правила:
	<p>
	<li>шаблон маски может содержать символы &quot;*&quot;, которые соответствуют любому количеству любых символов в имени файла или папки;</li>
	<li>если в начале стоит косая черта (&quot;/&quot; или &quot;\\&quot;), путь считается от корня сайта;</li>
	<li>в противном случае шаблон применяется к каждому файлу или папке;</li>
	<p>Примеры шаблонов:</p>
	<li>/content/photo - исключить целиком папку /content/photo;</li>
	<li>*.zip - исключить файлы с расширением &quot;zip&quot;;</li>
	<li>.access.php - исключить все файлы &quot;.access.php&quot;;</li>
	<li>/files/download/*.zip - исключить файлы с расширением &quot;zip&quot; в директории /files/download;</li>
	<li>/files/d*/*.ht* - исключить файлы из директорий, начинающихся на &quot;/files/d&quot;  с расширениями, начинающимися на &quot;ht&quot;.</li>
	";
$MESS["MAIN_DUMP_ERROR"] = "Ошибка";
$MESS["ERR_EMPTY_RESPONSE"] = "Произошла ошибка на стороне сервера: получен пустой ответ. Обратитесь к хостеру для уточнения проблемы в журнале ошибок по текущей дате";
$MESS["DUMP_NO_PERMS"] = "Закончилось свободное место или нет прав на сервере на создание резервной копии";
$MESS["DUMP_NO_PERMS_READ"] = "Ошибка открытия архива на чтение";
$MESS["DUMP_DB_CREATE"] = "Создание дампа базы данных";
$MESS["DUMP_CUR_PATH"] = "Текущий путь:";
$MESS["INTEGRITY_CHECK"] = "Проверка целостности";
$MESS["CURRENT_POS"] = "Текущая позиция:";
$MESS["TIME_LEFT"] = ", осталось примерно #TIME#";
$MESS["STEP_LIMIT"] = "Длительность шага:";
$MESS["DISABLE_GZIP"] = "Отключить компрессию архива (снижение нагрузки на процессор):";
$MESS["INTEGRITY_CHECK_OPTION"] = "Проверить целостность архива после завершения:";
$MESS["MAIN_DUMP_DB_PROC"] = "Помещение в архив дампа базы данных";
$MESS["TIME_SPENT"] = "Время создания резервной копии:";
$MESS["TIME_H"] = "ч.";
$MESS["TIME_M"] = "мин.";
$MESS["TIME_S"] = "сек.";
$MESS["MAIN_DUMP_FOLDER_ERR"] = "Папка #FOLDER# недоступна на запись";
$MESS["MAIN_DUMP_NO_CLOUDS_MODULE"] = "Модуль облачных хранилищ не установлен";
$MESS["MAIN_DUMP_INT_CLOUD_ERR"] = "Ошибка инициализации облачного хранилища. Попробуйте повторить отправку позднее.";
$MESS["MAIN_DUMP_ERR_FILE_SEND"] = "Не удалось отправить файл в облако: ";
$MESS["MAIN_DUMP_ERR_OPEN_FILE"] = "Не удалось открыть файл на чтение: ";
$MESS["MAIN_DUMP_SUCCESS_SENT"] = "Резервная копия успешно передана в облачное хранилище";
$MESS["MAIN_DUMP_SUCCESS_SAVED"] = "Изменения сохранены";
$MESS["MAIN_DUMP_SUCCESS_SAVED_DETAILS"] = "Автоматическое создание резервных копий начнется после настройки планировщика cron";
$MESS["MAIN_DUMP_AUTO_NOTE"] = "Через панель хостинга настройте задачу на cron на выполнение скрипта <b>#SCRIPT#</b>. Рекомендуемая периодичность: еженедельно.";
$MESS["MAIN_DUMP_CLOUDS_DOWNLOAD"] = "Загрузка файлов из облачных хранилищ";
$MESS["MAIN_DUMP_FILES_DOWNLOADED"] = "Загружено файлов";
$MESS["MAIN_DUMP_FILES_SIZE"] = "Размер загруженных файлов";
$MESS["MAIN_DUMP_DOWN_ERR_CNT"] = "Пропущено при загрузке";
$MESS["MAIN_DUMP_FILE_SENDING"] = "Передача резервной копии в облако";
$MESS["MAIN_DUMP_USE_THIS_LINK"] = "Используйте эту ссылку для переноса на другой сервер через";
$MESS["MAIN_DUMP_ERR_COPY_FILE"] = "Не удалось скопировать файл: ";
$MESS["MAIN_DUMP_ERR_INIT_CLOUD"] = "Не удалось подключить облачное хранилище";
$MESS["MAIN_DUMP_ERR_FILE_RENAME"] = "Ошибка переименования файла: ";
$MESS["MAIN_DUMP_ERR_NAME"] = "Имя файла может содержать только латинские буквы, цифры, дефис и точку";
$MESS["MAIN_DUMP_FILE_SIZE1"] = "Размер архива";
$MESS["MAIN_DUMP_LOCATION"] = "Размещение";
$MESS["MAIN_DUMP_PARTS"] = "частей: ";
$MESS["MAIN_DUMP_LOCAL"] = "локально";
$MESS["MAIN_DUMP_GET_LINK"] = "Получить ссылку для переноса";
$MESS["MAIN_DUMP_SEND_CLOUD"] = "Отправить в облако ";
$MESS["MAIN_DUMP_SEND_FILE_CLOUD"] = "Отправить резервную копию в облачное хранилище";
$MESS["MAIN_DUMP_RENAME"] = "Переименовать";
$MESS["MAIN_DUMP_ARC_NAME_W_O_EXT"] = "Имя файла без расширения";
$MESS["MAIN_DUMP_ARC_NAME"] = "Имя архива";
$MESS["MAIN_DUMP_ARC_LOCATION"] = "Размещение резервной копии: ";
$MESS["MAIN_DUMP_LOCAL_DISK"] = "в папке сайта";
$MESS["MAIN_DUMP_EVENT_LOG"] = "журнал событий";
$MESS["MAIN_DUMP_ENC_PASS_DESC"] = "С целью безопасности пароль для шифрования архива должен быть не менее 6 символов";
$MESS["MAIN_DUMP_EMPTY_PASS"] = "Не задан пароль для шифрования архива";
$MESS["MAIN_DUMP_NOT_INSTALLED1"] = "Не установлен PHP-модуль Openssl.";
$MESS["MAIN_DUMP_NOT_INSTALLED_HASH"] = "Не установлен PHP модуль Hash.";
$MESS["MAIN_DUMP_NO_ENC_FUNCTIONS"] = "Функции шифрования недоступны, использование облачного хранилища 1С-Битрикс невозможно. Обратитесь к системному администратору для решения проблемы";
$MESS["MAIN_DUMP_ENABLE_ENC"] = "Шифровать данные резервной копии:";
$MESS["MAIN_DUMP_ENC_PASS"] = "Пароль для шифрования архива (не менее 6 символов):";
$MESS["MAIN_DUMP_SAVE_PASS"] = "Внимание! Пароль нигде не сохраняется. Запишите его в надежном месте, без знания этого пароля восстановить резервную копию не удастся.";
$MESS["MAIN_DUMP_SAVE_PASS_AUTO"] = "Введенный пароль будет сохранен в локальной базе данных в зашифрованном виде. Для шифрования используется ваш лицензионный ключ. Меняйте пароль для шифрования не реже одного раза в месяц.";
$MESS["MAIN_DUMP_MAX_ARCHIVE_SIZE"] = "Максимальный размер несжатых данных в одной части архива (МБ):";
$MESS["MAIN_DUMP_MAX_ARCHIVE_SIZE_VALUES"] = "допустимые значения: 11 - 2047";
$MESS["MAIN_DUMP_MAX_ARCHIVE_SIZE_INFO"] = "Системные ограничения php не позволяют делать размер одной части архива более 2 Гб. Не устанавливайте это значение больше 200 Мб т.к. это существенно увеличивает время архивации и распаковки, оптимальное значение: 100 Мб.";
$MESS["DUMP_MAIN_SESISON_ERROR"] = "Ваша сессия истекла. Перезагрузите страницу.";
$MESS["DUMP_MAIN_ERROR"] = "Ошибка! ";
$MESS["DUMP_MAIN_REGISTERED"] = "Зарегистрировано";
$MESS["DUMP_MAIN_EDITION"] = "Редакция";
$MESS["DUMP_MAIN_ACTIVE_FROM"] = "Начало активности";
$MESS["DUMP_MAIN_ACTIVE_TO"] = "Окончание активности";
$MESS["DUMP_MAIN_ERR_GET_INFO"] = "Не удалось получить информацию о ключе с сервера обновлений";
$MESS["DUMP_MAIN_BITRIX_CLOUD"] = "облако 1С-Битрикс";
$MESS["DUMP_MAIN_BITRIX_CLOUD_DESC"] = "Облачное хранилище &quot;1С-Битрикс&quot;";
$MESS["DUMP_MAIN_ERR_PASS_CONFIRM"] = "Введённые пароли не совпадают";
$MESS["DUMP_MAIN_PASSWORD_CONFIRM"] = "Повтор пароля:";
$MESS["DUMP_MAIN_MAKE_ARC"] = "Резервное копирование";
$MESS["MAKE_DUMP_FULL"] = "Создание полной резервной копии";
$MESS["DUMP_MAIN_PARAMETERS"] = "Параметры";
$MESS["DUMP_MAIN_EXPERT_SETTINGS"] = "Экспертные настройки";
$MESS["DUMP_MAIN_ENC_ARC"] = "Шифрование архива";
$MESS["DUMP_MAIN_SITE"] = "Сайт:";
$MESS["DUMP_MAIN_IN_THE_CLOUD"] = "в облаке";
$MESS["DUMP_MAIN_IN_THE_BXCLOUD"] = "в облаке &quot;1С-Битрикс&quot;";
$MESS["DUMP_MAIN_ENABLE_EXPERT"] = "Включить экспертные настройки создания резервной копии";
$MESS["DUMP_MAIN_CHANGE_SETTINGS"] = "Изменение экспертных настроек может привести к созданию нецелостного архива и невозможности его восстановления. Вы должны хорошо понимать, что делаете.";
$MESS["DUMP_MAIN_ARC_CONTENTS"] = "Содержимое резервной копии";
$MESS["DUMP_MAIN_DOWNLOAD_CLOUDS"] = "Скачать и поместить в архив данные облачных хранилищ:";
$MESS["DUMP_MAIN_ARC_DATABASE"] = "Архивировать базу данных";
$MESS["DUMP_MAIN_DB_EXCLUDE"] = "Исключить из базы данных:";
$MESS["DUMP_MAIN_ARC_MODE"] = "Режим архивации";
$MESS["DUMP_MAIN_MULTISITE_INFO"] = "Если выбрано несколько сайтов для помещения в архив, в корне архива будет лежать первый по списку сайт, а публичные части остальных сайтов будут помещены в папку <b>/bitrix/backup/sites</b>. При восстановлении нужно будет вручную скопировать их в нужные папки и создать символьные ссылки.";
$MESS["BCL_BACKUP_USAGE"] = "Использовано места: #USAGE# из #QUOTA#.";
$MESS["DUMP_BXCLOUD_NA"] = "Облачное хранилище &quot;1С-Битрикс&quot; недоступно";
$MESS["DUMP_ERR_NON_ASCII"] = "Во избежание проблем с восстановлением резервной копии в пароле не допускаются символы национального алфавита";
$MESS["DUMP_MAIN_BXCLOUD_INFO"] = "Компания &quot;1С-Битрикс&quot; бесплатно предоставляет место в облаке для хранения трех резервных копий на каждую активную лицензию. Объём пространства в облаке зависит от лицензии. Доступ к резервным копиям осуществляется по лицензионному ключу и паролю. Без знания пароля никто, включая сотрудников &quot;1С-Битрикс&quot;, не сможет получить доступ к вашим данным.";
$MESS["MAIN_DUMP_BXCLOUD_ENC"] = "При размещении резервной копии в облачном хранилище &quot;1С-Битрикс&quot; отключить шифрование нельзя.";
$MESS["MAIN_DUMP_FROM"] = "из";
$MESS["DUMP_ERR_BIG_BACKUP"] = "Размер резервной копии превышает вашу квоту в облаке &quot;1С-Битрикс&quot;. Архив сохранен локально.";
$MESS["DUMP_RETRY"] = "Попытаться еще раз";
$MESS["MAIN_DUMP_ERR_DELETE"] = "Нельзя вручную удалить файлы из облачного хранилища &quot;1С-Битрикс&quot;. Старые копии автоматически удаляются после загрузки новых.";
$MESS["ERR_NO_BX_CLOUD"] = "Не установлен модуль облачных сервисов &quot;1С-Битрикс&quot;";
$MESS["ERR_NO_CLOUDS"] = "Не установлен модуль облачных хранилищ";
$MESS["DUMP_DELETE_ERROR"] = "Не удалось удалить файл #FILE#";
$MESS["DUMP_MAIN_AUTO_PARAMETERS"] = "Настройка скрипта периодического запуска";
$MESS["DUMP_MAIN_SAVE"] = "Сохранить";
$MESS["DUMP_ADDITIONAL"] = "Дополнительные параметры";
$MESS["DUMP_DELETE"] = "Удалять локальные резервные копии";
$MESS["DUMP_NOT_DELETE"] = "никогда не удалять";
$MESS["DUMP_CLOUD_DELETE"] = "после успешной передачи в облако";
$MESS["DUMP_RM_BY_TIME"] = "если прошло #TIME# дней с момента создания";
$MESS["DUMP_RM_BY_CNT"] = "если общее число копий больше #CNT#";
$MESS["DUMP_RM_BY_SIZE"] = "если суммарный размер резервных копий больше #SIZE# Гб";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME"] = "Ближайший запуск запланирован на: ";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME_TODAY"] = "Ближайший запуск запланирован на сегодня: ";
$MESS["MAIN_DUMP_SHED_CLOSEST_TIME_TOMORROW"] = "Ближайший запуск запланирован на завтра: ";
$MESS["MAIN_DUMP_SHED"] = "Расписание";
$MESS["MAIN_DUMP_PERIODITY"] = "Периодичность:";
$MESS["MAIN_DUMP_PER_1"] = "каждый день";
$MESS["MAIN_DUMP_PER_2"] = "через день";
$MESS["MAIN_DUMP_PER_3"] = "каждые 3 дня";
$MESS["MAIN_DUMP_PER_5"] = "каждые 5 дней";
$MESS["MAIN_DUMP_PER_7"] = "еженедельно";
$MESS["MAIN_DUMP_PER_14"] = "каждые две недели";
$MESS["MAIN_DUMP_PER_21"] = "каждые три недели";
$MESS["MAIN_DUMP_PER_30"] = "ежемесячно";
$MESS["MAIN_DUMP_DELETE_OLD"] = "Удаление старых копий";
$MESS["MAIN_DUMP_SHED_TIME_SET"] = "Настройка времени создания резервной копии доступна в случае если системные агенты выполняются на cron (неважно, только непериодические или все). Иначе для автоматического создания резервных копий необходимо настроить на определенное время выполнение php скрипта <b>/bitrix/modules/main/tools/backup.php</b> через панель хостинга.";
$MESS["MAIN_DUMP_AUTO_LOCK"] = "Запущен процесс автоматического резервного копирования";
$MESS["MAIN_DUMP_AUTO_LOCK_TIME"] = "С момента запуска прошло: #TIME#";
$MESS["AUTO_LOCK_EXISTS_ERR"] = "Автоматического резервное копирование, запущенное #DATETIME#, завершилось необрабатываемой ошибкой. Посмотрите логи сервера, чтобы найти причину.";
$MESS["AUTO_EXEC_METHOD"] = "Метод запуска:";
$MESS["AUTO_EXEC_FROM_BITRIX"] = "через облачный сервис &quot;1С-Битрикс&quot;";
$MESS["AUTO_EXEC_FROM_CRON"] = "с агентами на cron";
$MESS["AUTO_EXEC_FROM_MAN"] = "через прямой запуск #SCRIPT#";
$MESS["AUTO_URL"] = "адрес сайта";
$MESS["DUMP_AUTO_TAB"] = "Регулярный запуск";
$MESS["MAIN_DUMP_AUTO_WARN"] = "Включите <a href=\"#LINK#\">автоматическое резервное копирование</a>, чтобы иметь актуальные данные на случай восстановления.";
$MESS["DUMP_LOCAL_TIME"] = "(локальное время сайта)";
$MESS["DUMP_CHECK_BITRIXCLOUD"] = "Состояние задания можно проверить на <a href=\"#LINK#\">странице</a> облачного сервиса &quot;1С-Битрикс&quot;";
$MESS["DUMP_WARN_NO_BITRIXCLOUD"] = "Невозможно включить автоматическое резервное копирование: необходим модуль облачных сервисов &quot;1С-Битрикс&quot; или агенты должны выполняться на cron.";
$MESS["DUMP_SAVED_DISABLED"] = "Автоматический запуск резервного копирования выключен.<br>Резервная копия будет создаваться только при прямом запуске скрипта /bitrix/modules/main/tools/backup.php.";
$MESS["DUMP_AUTO_INFO_OFF"] = "Регулярное резервное копирование выключено";
$MESS["DUMP_AUTO_INFO_ON"] = "Регулярное резервное копирование включено";
$MESS["DUMP_BTN_AUTO_DISABLE"] = "Выключить регулярное резервное копирование";
$MESS["DUMP_BTN_AUTO_ENABLE"] = "Включить регулярное резервное копирование";
$MESS["DUMP_AUTO_INFO_TEXT"] = "<b>Регулярное резервное копирование</b>

Рекомендуется включить регулярное автоматическое резервное копирование, чтобы всегда иметь актуальные данные в случае необходимости восстановления. 

Облачный мониторинг &quot;1С-Битрикс&quot; откроет специальную ссылку на вашем сайте в указанное время, чтобы создать резервную копию. Ссылка содержит секретный код, который позволяет создать резервную копию, но не дает доступ к данным сайта. Доступ к административной части не требуется, может быть закрыт по IP.

По умолчанию резервная копия отправляется в облако &quot;1С-Битрикс&quot;, где в зашифрованном виде хранится в нескольких экземплярах. Это надежный и безопасный способ сохранить ваши данные.

Если облачные сервисы &quot;1С-Битрикс&quot; недоступны, а агенты выполняются на cron, будет создаваться локальная копия через cron.
";
$MESS["DUMP_TABLE_BROKEN"] = "Таблица #TABLE# разрушена в результате внутренней ошибки MySQL. Восстановите ее целиком из резервной копии или только структуру через <a href=\"/bitrix/admin/site_checker.php?tabControl_active_tab=edit1\" target=_blank>проверку системы</a>";
$MESS["DUMP_ERR_AUTO"] = "В процессе создания резервной копии произошла ошибка. Подробности в <a href=\"#LINK#\">системном журнале</a>";
?>
