<?
$MESS["CLU_AFTER_CONNECT_MSG"] = "Главная база данных и окружение продукта должны быть настроены так, чтобы не было файла php_interface/after_connect.php";
$MESS["CLU_AFTER_CONNECT_WIZREC"] = "Выполните необходимые настройки. Убедитесь в правильности работы продукта. Удалите файл и запустите мастер еще раз.";
$MESS["CLU_CHARSET_MSG"] = "Кодировка для сервера, базы данных, подключения и клиента должны совпадать.";
$MESS["CLU_CHARSET_WIZREC"] = "Настройте параметры MySQL:<br />
&nbsp;character_set_server (текущее значение: #character_set_server#),<br />
&nbsp;character_set_database (текущее значение: #character_set_database#),<br />
&nbsp;character_set_connection (текущее значение: #character_set_connection#),<br />
&nbsp;character_set_client (текущее значение: #character_set_client#).<br />
Убедитесь в правильности работы продукта и запустите мастер еще раз.";
$MESS["CLU_COLLATION_MSG"] = "Правила сортировки для сервера, базы данных и подключения должны совпадать.";
$MESS["CLU_COLLATION_WIZREC"] = "Настройте параметры MySQL:<br />
&nbsp;collation_server (текущее значение: #collation_server#),<br />
&nbsp;collation_database (текущее значение: #collation_database#),<br />
&nbsp;collation_connection (текущее значение: #collation_connection#).<br />
Убедитесь в правильности работы продукта и запустите мастер еще раз.";
$MESS["CLU_SERVER_ID_MSG"] = "Каждый узел кластера должен иметь уникальный идентификатор (текущее значение server-id: #server-id#).";
$MESS["CLU_LOG_BIN_MSG"] = "У главного сервера должно быть включено журналирование (текущее значение log-bin: #log-bin#).";
$MESS["CLU_LOG_BIN_NODE_MSG"] = "У добавляемого сервера должно быть включено журналирование (текущее значение log-bin: #log-bin#).";
$MESS["CLU_LOG_BIN_WIZREC"] = "В файле my.cnf добавьте параметр log-bin=mysql-bin. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_SKIP_NETWORKING_MSG"] = "Необходимо разрешить подключение в главному серверу по сети (текущее значение skip-networking: #skip-networking#).";
$MESS["CLU_SKIP_NETWORKING_NODE_MSG"] = "Необходимо разрешить подключение в добавляемому серверу по сети (текущее значение skip-networking: #skip-networking#).";
$MESS["CLU_SKIP_NETWORKING_WIZREC"] = "В файле my.cnf удалите или закомментируйте параметр skip-networking. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_FLUSH_ON_COMMIT_MSG"] = "При использовании InnoDB для увеличения надежности репликации желательно установить параметр innodb_flush_log_at_trx_commit = 1 (текущее значение: #innodb_flush_log_at_trx_commit#).";
$MESS["CLU_SYNC_BINLOG_MSG"] = "При использовании InnoDB для увеличения надежности репликации желательно установить параметр sync_binlog = 1 (текущее значение: #sync_binlog#).";
$MESS["CLU_SYNC_BINLOGDODB_MSG"] = "Должна быть настроена репликация только одной базы данных.";
$MESS["CLU_SYNC_BINLOGDODB_WIZREC"] = "В файле my.cnf добавьте параметр binlog-do-db=#database#. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_MASTER_CHARSET_MSG"] = "Кодировка и правила сортировки главного сервера и нового подключения должны совпадать.";
$MESS["CLU_MASTER_CHARSET_WIZREC"] = "Настройте параметры MySQL:<br />
&nbsp;character_set_server (текущее значение: #character_set_server#),<br />
&nbsp;collation_server (текущее значение: #collation_server#).<br />
Убедитесь в правильности работы продукта и запустите мастер еще раз.";
$MESS["CLU_SERVER_ID_WIZREC1"] = "Параметр server-id не задан.";
$MESS["CLU_SERVER_ID_WIZREC2"] = "Сервер базы данных с таким server-id уже зарегистрирован в модуле.";
$MESS["CLU_SERVER_ID_WIZREC"] = "В файле my.cnf задайте значение параметра server-id. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_SQL_MSG"] = "Пользователь должен иметь права на создание и удаление таблиц, а также на вставку, выборку, изменение и удаление данных.";
$MESS["CLU_SQL_WIZREC"] = "Не достаточно прав. Не удалось выполнить следующие SQL запросы:#sql_erorrs_list#";
$MESS["CLU_RUNNING_SLAVE"] = "В указанной базе данных уже запущен процесс репликации. Подключение не возможно.";
$MESS["CLU_SAME_DATABASE"] = "Эта база данных та же самая, что и главная. Подключение не возможно.";
$MESS["CLU_MASTER_CONNECT_ERROR"] = "Ошибка подключения к главной базе данных:";
$MESS["CLU_NOT_MASTER"] = "Указанная в качестве главной база данных не является таковой.";
$MESS["CLU_MAX_ALLOWED_PACKET_MSG"] = "Значение параметра max_allowed_packet у slave базы данных должно быть не меньше чем у главной.";
$MESS["CLU_MAX_ALLOWED_PACKET_WIZREC"] = "В файле my.cnf задайте значение параметра max_allowed_packet и перезапустите MySQL.";
$MESS["CLU_SLAVE_VERSION_MSG"] = "Версия MySQL slave базы данных (#slave-version#) должна быть не ниже, чем #required-version#.";
$MESS["CLU_VERSION_MSG"] = "Версия MySQL slave базы данных (#slave-version#) должна быть не ниже, чем версия главной (#master-version#).";
$MESS["CLU_SLAVE_RELAY_LOG_MSG"] = "Не задано значение параметра relay-log. При смене имени хоста сервера репликация будет нарушена.";
$MESS["CLU_RELAY_LOG_WIZREC"] = "В файле my.cnf задайте значение параметра relay-log (например: mysqld-relay-bin) и перезапустите MySQL.";
$MESS["CLU_VERSION_WIZREC"] = "Обновите MySQL и запустите мастер еще раз.";
$MESS["CLU_MASTER_STATUS_MSG"] = "Недостаточно привилегий для проверки статуса репликации.";
$MESS["CLU_MASTER_STATUS_WIZREC"] = "Выполните запрос: #sql#.";
$MESS["CLU_AUTO_INCREMENT_INCREMENT_ERR_MSG"] = "У сервера с ID равным #node_id# неверное значение параметра auto_increment_increment. Оно должно быть равным #value# (текущее значение auto_increment_increment: #current#).";
$MESS["CLU_AUTO_INCREMENT_INCREMENT_NODE_ERR_MSG"] = "У добавляемого сервера неверное значение параметра auto_increment_increment. Оно должно быть равным #value# (текущее значение auto_increment_increment: #current#).";
$MESS["CLU_AUTO_INCREMENT_INCREMENT_WIZREC"] = "В файле my.cnf задайте значение параметра auto_increment_increment равным #value#. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_AUTO_INCREMENT_INCREMENT_OK_MSG"] = "У серверов кластера значение параметра auto_increment_increment должно быть равным #value#.";
$MESS["CLU_AUTO_INCREMENT_OFFSET_ERR_MSG"] = "У сервера с ID равным #node_id# неверное значение параметра auto_increment_offset. Оно не должно быть равным #current#.";
$MESS["CLU_AUTO_INCREMENT_OFFSET_NODE_ERR_MSG"] = "У добавляемого сервера неверное значение параметра auto_increment_offset. Оно не должно быть равным #current#.";
$MESS["CLU_AUTO_INCREMENT_OFFSET_WIZREC"] = "В файле my.cnf задайте значение параметра auto_increment_offset отличным от других серверов. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_AUTO_INCREMENT_OFFSET_OK_MSG"] = "У серверов кластера значение параметра auto_increment_offset не должно приводить к коллизиям.";
$MESS["CLU_RELAY_LOG_ERR_MSG"] = "У сервера с ID равным #node_id#  не включено чтение журнала (текущее значение relay-log: #relay-log#).";
$MESS["CLU_RELAY_LOG_OK_MSG"] = "У серверов кластера должно быть включено чтение журнала (параметр relay-log).";
$MESS["CLU_LOG_SLAVE_UPDATES_MSG"] = "У сервера с ID равным #node_id#  не включено журналирование запросов пришедших от master базы данных. Это понадобится, если к нему будут подключены slave базы данных. Текущее значение log-slave-updates: #log-slave-updates#.";
$MESS["CLU_LOG_SLAVE_UPDATES_WIZREC"] = "В файле my.cnf задайте значение параметра log-slave-updates равным #value#. Перезапустите MySQL и нажмите кнопку \"Далее\".";
$MESS["CLU_LOG_SLAVE_UPDATES_OK_MSG"] = "У master серверов кластера должно быть включено журналирование запросов пришедших от другой master базы данных.";
?>