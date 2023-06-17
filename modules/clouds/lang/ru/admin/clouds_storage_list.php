<?
$MESS["CLO_STORAGE_LIST_ADD"] = "Добавить";
$MESS["CLO_STORAGE_LIST_ADD_TITLE"] = "Добавить новое подключение к облачному хранилищу";
$MESS["CLO_STORAGE_LIST_ID"] = "ID";
$MESS["CLO_STORAGE_LIST_ACTIVE"] = "Активность";
$MESS["CLO_STORAGE_LIST_MODE"] = "Режим";
$MESS["CLO_STORAGE_LIST_READ_ONLY"] = "Чтение";
$MESS["CLO_STORAGE_LIST_READ_WRITE"] = "Чтение/Запись";
$MESS["CLO_STORAGE_LIST_SORT"] = "Сортировка";
$MESS["CLO_STORAGE_LIST_SERVICE"] = "Сервис";
$MESS["CLO_STORAGE_LIST_BUCKET"] = "Контейнер";
$MESS["CLO_STORAGE_LIST_FILE_COUNT"] = "Файлов";
$MESS["CLO_STORAGE_LIST_FILE_SIZE"] = "Объем";
$MESS["CLO_STORAGE_LIST_EDIT"] = "Изменить";
$MESS["CLO_STORAGE_LIST_MOVE_FILE_ERROR"] = "Ошибка перемещения файла в облачное хранилище";
$MESS["CLO_STORAGE_LIST_START_MOVE_FILES"] = "Переместить файлы в облачное хранилище";
$MESS["CLO_STORAGE_LIST_CONT_MOVE_FILES"] = "Продолжить перемещение файлов в облачное хранилище";
$MESS["CLO_STORAGE_LIST_MOVE_LOCAL"] = "Вернуть файлы из облачного хранилища";
$MESS["CLO_STORAGE_LIST_MOVE_LOCAL_CONF"] = "Вы уверены, что хотите вернуть файлы из облачного хранилища на диски сервера?";
$MESS["CLO_STORAGE_ESTIMATE_DUPLICATES"] ="Оценить объём и количество дубликатов";
$MESS["CLO_STORAGE_LIST_DEACTIVATE"] = "Деактивировать";
$MESS["CLO_STORAGE_LIST_DEACTIVATE_CONF"] = "Деактивировать подключение к облачному хранилищу?";
$MESS["CLO_STORAGE_LIST_ACTIVATE"] = "Активировать";
$MESS["CLO_STORAGE_LIST_DELETE"] = "Удалить";
$MESS["CLO_STORAGE_LIST_DELETE_CONF"] = "Удалить подключение к облачному хранилищу?";
$MESS["CLO_STORAGE_LIST_CANNOT_DELETE"] = "Ошибка удаления: #error_msg#.";
$MESS["CLO_STORAGE_LIST_NOT_EMPTY"] = "в хранилище есть файлы";
$MESS["CLO_STORAGE_LIST_TITLE"] = "Облачные хранилища";
$MESS["CLO_STORAGE_LIST_MOVE_IN_PROGRESS"] = "Идет перенос файлов в облачное хранилище.";
$MESS["CLO_STORAGE_LIST_MOVE_DONE"] = "Перенос файлов в облачное хранилище завершен.";
$MESS["CLO_STORAGE_LIST_MOVE_PROGRESS"] = "
Всего обработано <b>#total#</b> файлов.<br>
Из них перемещено <b>#moved# (#bytes#)</b> и пропущено <b>#skiped#</b>.
";
$MESS["CLO_STORAGE_LIST_STOP"] = "Остановить";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_IN_PROGRESS"] = "Идет выгрузка файлов из облачного хранилища.";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_PROGRESS"] = "
Осталось выгрузить <b>#remain# (#bytes#)</b>.
";
$MESS["CLO_STORAGE_LIST_DOWNLOAD_DONE"] = "Возврат файлов из облачного хранилища завершен.";
$MESS["CLO_STORAGE_LIST_LISTING"] = "Получение списка файлов из облачного хранилища";
$MESS["CLO_STORAGE_LIST_COPY"] = "Копирование информации в главный модуль";
$MESS["CLO_STORAGE_LIST_DUPLICATES_RESULT"] = "Результат поиска дубликатов";
$MESS["CLO_STORAGE_LIST_DUPLICATES_INFO"] = "
Количество дубликатов: <b>#count#</b><br>
Объём дубликатов:<b> #size#</b><br>
Посмотреть <a href=\"#list_link#\">список</a> файлов.<br>
<b>Внимание!</b><br>
1. Не все дубликаты можно будет безопасно удалить, а только те которые находятся под управлением модулей и зарегистрированы в базе данных.<br>
Это значит, что дубликаты во временных каталогах или специальных папках (таких как resize_cache, bizproc и другие) не могут быть удалены.<br>
2. Для поиска дубликатов используется сравнение файлов по размеру и контрольной сумме без учёта их содержимого.<br>
Вполне возможна ситуация, когда два разных по содержанию файла совпадают по размеру и имеют одинаковую контрольную сумму, поэтому будут считаться дубликатами.
";
?>
