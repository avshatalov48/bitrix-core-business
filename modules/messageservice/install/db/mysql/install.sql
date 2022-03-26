CREATE TABLE b_messageservice_message (
	ID int(18) auto_increment,
	TYPE varchar(30) not null,
	SENDER_ID varchar(50) not null,
	AUTHOR_ID int(18) not null default 0,
	MESSAGE_FROM varchar(260) null,
	MESSAGE_TO varchar(50) not null,
	MESSAGE_HEADERS longtext null,
	MESSAGE_BODY longtext not null,
	DATE_INSERT datetime null,
	DATE_EXEC datetime null,
	NEXT_EXEC datetime null,
	SUCCESS_EXEC char(1) not null default 'N',
	EXEC_ERROR varchar(255) null,
	STATUS_ID int(18) not null default 0,
	EXTERNAL_ID varchar(128) null,
	EXTERNAL_STATUS varchar(128) null,
	PRIMARY KEY (ID),
	INDEX B_MESSAGESERVICE_MESSAGE_1(DATE_EXEC),
	INDEX B_MESSAGESERVICE_MESSAGE_2(SUCCESS_EXEC),
	INDEX B_MESSAGESERVICE_MESSAGE_3(SENDER_ID, EXTERNAL_ID)
);

CREATE TABLE b_messageservice_rest_app (
	ID int(18) not null auto_increment,
	APP_ID varchar(128) not null,
	CODE varchar(128) not null,
	TYPE varchar(30) not null,
	HANDLER varchar(1000) not null,
	DATE_ADD datetime null,
	AUTHOR_ID int(18) not null default 0,
	PRIMARY KEY (ID),
	UNIQUE INDEX B_MESSAGESERVICE_REST_APP_1(APP_ID, CODE)
);

CREATE TABLE b_messageservice_rest_app_lang (
	ID int(18) not null auto_increment,
	APP_ID int(18) not null,
	LANGUAGE_ID char(2) not null,
	NAME varchar(500) null,
	APP_NAME varchar(500) null,
	DESCRIPTION varchar(1000) null,
	PRIMARY KEY (ID)
);