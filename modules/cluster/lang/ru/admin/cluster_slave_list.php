<?
$MESS["CLU_SLAVE_LIST_TITLE"] = "Slave базы данных";
$MESS["CLU_SLAVE_LIST_ID"] = "ID";
$MESS["CLU_SLAVE_LIST_FLAG"] = "Состояние";
$MESS["CLU_SLAVE_NOCONNECTION"] = "нет подключения";
$MESS["CLU_SLAVE_UPTIME"] = "время работы";
$MESS["CLU_SLAVE_LIST_BEHIND"] = "Отставание (сек)";
$MESS["CLU_SLAVE_LIST_STATUS"] = "Статус";
$MESS["CLU_SLAVE_LIST_NAME"] = "Название";
$MESS["CLU_SLAVE_LIST_DB_HOST"] = "Сервер";
$MESS["CLU_SLAVE_LIST_DB_NAME"] = "База данных";
$MESS["CLU_SLAVE_LIST_DB_LOGIN"] = "Пользователь";
$MESS["CLU_SLAVE_LIST_WEIGHT"] = "Использовать (%)";
$MESS["CLU_SLAVE_LIST_DESCRIPTION"] = "Описание";
$MESS["CLU_SLAVE_LIST_ADD"] = "Добавить slave базу данных";
$MESS["CLU_SLAVE_LIST_ADD_TITLE"] = "Запустить мастер добавления новой slave базы данных";
$MESS["CLU_SLAVE_LIST_MASTER_ADD"] = "Добавить master-slave базу данных";
$MESS["CLU_SLAVE_LIST_MASTER_ADD_TITLE"] = "Запустить мастер добавления новой master-slave базы данных";
$MESS["CLU_SLAVE_LIST_EDIT"] = "Изменить";
$MESS["CLU_SLAVE_LIST_START_USING_DB"] = "Начать использовать";
$MESS["CLU_SLAVE_LIST_SKIP_SQL_ERROR"] = "Игнорировать ошибку";
$MESS["CLU_SLAVE_LIST_SKIP_SQL_ERROR_ALT"] = "Игнорировать одну ошибку SQL и продолжить репликацию";
$MESS["CLU_SLAVE_LIST_DELETE"] = "Удалить";
$MESS["CLU_SLAVE_LIST_DELETE_CONF"] = "Удалить подключение?";
$MESS["CLU_SLAVE_LIST_PAUSE"] = "Приостановить";
$MESS["CLU_SLAVE_LIST_RESUME"] = "Возобновить";
$MESS["CLU_SLAVE_LIST_REFRESH"] = "Обновить";
$MESS["CLU_SLAVE_LIST_STOP"] = "Прекратить использовать";
$MESS["CLU_SLAVE_BACKUP"] = "Резервное копирование";
$MESS["CLU_MAIN_LOAD"] = "Минимальная нагрузка";
$MESS["CLU_SLAVE_LIST_NOTE"] = "<p>Репликация базы данных - это процесс создания и поддержания в актуальном состоянии её копии.</p>
<p>Какие задачи решает:<br>
1) возможность переноса часть нагрузки с основной базы данных (master) на одну или несколько ее копий (slave).<br>
2) использовать копии в качестве горячего резерва.<br>
</p>
<p>Важно!<br>
- Использовать для репликации разные сервера с быстрой связью между собой.<br>
- Запуск репликации начинается с копирования содержимого базы данных. На время копирования публичная часть сайта будет закрыта, а административная нет. Любые неучтенные модификации данных в период копирования могут в дальнейшем повлиять на правильность работы сайта.<br>
</p>
<p>Инструкция по настройке:<br>
Шаг 1: Запустите мастер, нажав на кнопку \"Добавить slave базу данных\". На данном этапе происходит проверка правильности настройки сервера и добавление подключения в список slave баз данных.<br>
Шаг 2: В списке slave баз данных в меню действий выполните команду \"Начать использовать\".<br>
Шаг 3: Следуйте рекомендациям мастера.<br>
</p>
";
?>