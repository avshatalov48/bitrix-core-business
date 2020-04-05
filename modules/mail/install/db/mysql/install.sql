create table b_mail_mailbox
(
   ID int(18) not null auto_increment,
   TIMESTAMP_X timestamp,
   LID char(2) not null,
   ACTIVE char(1) not null default 'Y',
   SERVICE_ID int(11) NOT NULL DEFAULT 0,
   NAME varchar(255),
   SERVER varchar(255) null,
   PORT int(18) not null default '110',
   LINK varchar(255) null,
   LOGIN varchar(255),
   CHARSET varchar(255),
   `PASSWORD` varchar(255),
   DESCRIPTION text,
   USE_MD5 char(1) not null default 'N',
   DELETE_MESSAGES char(1) not null default 'N',
   PERIOD_CHECK int(15),
   MAX_MSG_COUNT int(11) default '0',
   MAX_MSG_SIZE int(11) default '0',
   MAX_KEEP_DAYS int(11) default '0',
   USE_TLS char(1) not null default 'N',
   SERVER_TYPE varchar(10) NOT NULL DEFAULT 'pop3',
   DOMAINS varchar(255) null,
   RELAY char(1) NOT NULL DEFAULT 'Y',
   AUTH_RELAY char(1) NOT NULL DEFAULT 'Y',
   USER_ID int(11) NOT NULL DEFAULT 0,
   SYNC_LOCK INT NULL,
   OPTIONS TEXT NULL,
   primary key (ID),
   index IX_B_MAIL_MAILBOX_USER_ID (USER_ID)
);


create table b_mail_filter
(
   ID int(18) not null auto_increment,
   TIMESTAMP_X timestamp,
   MAILBOX_ID int(18) not null,
   PARENT_FILTER_ID int(18),
   NAME varchar(255),
   DESCRIPTION text,
   SORT int(18) not null default '500',
   ACTIVE char(1) not null default 'Y',
   PHP_CONDITION text,
   WHEN_MAIL_RECEIVED char(1) not null default 'N',
   WHEN_MANUALLY_RUN char(1) not null default 'N',
   SPAM_RATING decimal(9,4),
   SPAM_RATING_TYPE char(1) default '<',
   MESSAGE_SIZE int(18),
   MESSAGE_SIZE_TYPE char(1) default '<',
   MESSAGE_SIZE_UNIT char(1),
   ACTION_STOP_EXEC char(1) not null default 'N',
   ACTION_DELETE_MESSAGE char(1) not null default 'N',
   ACTION_READ char(1) not null default '-',
   ACTION_PHP text,
   ACTION_TYPE varchar(50),
   ACTION_VARS text,
   ACTION_SPAM char(1) not null default '-',
   primary key (ID),
   index IX_MAIL_FILTER_MAILBOX (MAILBOX_ID)
);


create table b_mail_filter_cond
(
   ID int(11) not null auto_increment,
   FILTER_ID int(11) not null,
   `TYPE` varchar(50) not null,
   STRINGS text not null,
   COMPARE_TYPE varchar(30) not null default 'CONTAIN',
   primary key (ID)
);


create table b_mail_message
(
   ID int(18) not null auto_increment,
   MAILBOX_ID int(18) not null,
   DATE_INSERT datetime not null,
   FULL_TEXT longtext,
   MESSAGE_SIZE int(18) not null,
   HEADER text,
   FIELD_DATE datetime,
   FIELD_FROM varchar(255),
   FIELD_REPLY_TO varchar(255),
   FIELD_TO varchar(255),
   FIELD_CC varchar(255),
   FIELD_BCC varchar(255),
   FIELD_PRIORITY int(18) not null default '3',
   SUBJECT varchar(255),
   BODY longtext,
   ATTACHMENTS int(18) default '0',
   NEW_MESSAGE char(1) default 'Y',
   SPAM char(1) not null default '?',
   SPAM_RATING decimal(18,4),
   SPAM_WORDS varchar(255),
   SPAM_LAST_RESULT char(1) not null default 'N',
   FOR_SPAM_TEST mediumtext,
   EXTERNAL_ID varchar(255),
   MSG_ID varchar(255) NULL,
   IN_REPLY_TO varchar(255) NULL,
   primary key (ID),
   index IX_MAIL_MESSAGE (MAILBOX_ID),
   index IX_MAIL_MESSAGE_DATE (DATE_INSERT, MAILBOX_ID)
);


create table b_mail_message_uid
(
   ID varchar(32) not null,
   MAILBOX_ID int(18) not null,
   HEADER_MD5 VARCHAR(32) NULL,
   IS_SEEN CHAR(1) NOT NULL DEFAULT 'N',
   SESSION_ID varchar(32) not null,
   TIMESTAMP_X timestamp,
   DATE_INSERT datetime not null,
   MESSAGE_ID int(18) not null,
   primary key (ID, MAILBOX_ID),
   index IX_MAIL_MSG_UID (MAILBOX_ID),
   index IX_MAIL_MSG_UID_2 (HEADER_MD5)
);

create table b_mail_msg_attachment
(
   ID int(18) not null auto_increment,
   MESSAGE_ID int(18) not null,
   FILE_ID int(18) not null default '0',
   FILE_NAME varchar(255),
   FILE_SIZE int(11) not null default '0',
   FILE_DATA longblob,
   CONTENT_TYPE varchar(255),
   IMAGE_WIDTH int(18),
   IMAGE_HEIGHT int(18),
   primary key (ID),
   index IX_MAIL_MESSATTACHMENT (MESSAGE_ID)
);

create table b_mail_spam_weight
(
   WORD_ID varchar(32) not null,
   WORD_REAL varchar(50) not null,
   GOOD_CNT int(18) not null default '0',
   BAD_CNT int(18) not null default '0',
   TOTAL_CNT int(18) not null default '0',
   TIMESTAMP_X timestamp,
   primary key (WORD_ID)
);

create table b_mail_log
(
   ID int(18) not null auto_increment,
   MAILBOX_ID int(18) not null default '0',
   FILTER_ID int(18),
   MESSAGE_ID int(18),
   LOG_TYPE varchar(50),
   DATE_INSERT datetime not null,
   STATUS_GOOD char(1) not null default 'Y',
   MESSAGE varchar(255) null,
   primary key (ID),
   index IX_MAIL_MSGLOG_1 (MAILBOX_ID),
   index IX_MAIL_MSGLOG_2 (MESSAGE_ID)
);

CREATE TABLE IF NOT EXISTS `b_mail_mailservices` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `SITE_ID` VARCHAR(255) NOT NULL,
  `ACTIVE` CHAR(1) NOT NULL DEFAULT 'Y',
  `SERVICE_TYPE` VARCHAR(10) NOT NULL DEFAULT 'imap',
  `NAME` VARCHAR(255) NOT NULL,
  `SERVER` VARCHAR(255) NULL,
  `PORT` INT NULL,
  `ENCRYPTION` CHAR(1) NULL,
  `LINK` VARCHAR(255) NULL,
  `ICON` INT NULL,
  `TOKEN` VARCHAR(255) NULL,
  `FLAGS` INT NOT NULL DEFAULT 0,
  `SORT` INT NOT NULL DEFAULT 100,
  PRIMARY KEY (`ID`),
  INDEX IX_B_MAIL_MAILSERVICE_ACTIVE (ACTIVE)
);

CREATE TABLE IF NOT EXISTS `b_mail_user_relations` (
  `TOKEN` VARCHAR(32) NOT NULL,
  `SITE_ID` CHAR(2) NULL,
  `USER_ID` INT NOT NULL,
  `ENTITY_TYPE` VARCHAR(255) NOT NULL,
  `ENTITY_ID` VARCHAR(255) NULL,
  `ENTITY_LINK` VARCHAR(255) NULL,
  `BACKURL` VARCHAR(255) NULL,
  PRIMARY KEY (`TOKEN`),
  UNIQUE UX_B_MAIL_USER_RELATION (USER_ID, ENTITY_TYPE(50), ENTITY_ID(50), SITE_ID)
);

CREATE TABLE IF NOT EXISTS `b_mail_blacklist` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `SITE_ID` CHAR(2) NOT NULL,
  `MAILBOX_ID` INT NOT NULL DEFAULT 0,
  `ITEM_TYPE` INT NOT NULL,
  `ITEM_VALUE` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`),
  INDEX IX_B_MAIL_BLACKLIST (MAILBOX_ID, SITE_ID)
);

CREATE TABLE IF NOT EXISTS `b_mail_domain_email` (
  `DOMAIN` VARCHAR(255) NOT NULL,
  `LOGIN` VARCHAR(255) NOT NULL,
  PRIMARY KEY (LOGIN(50), DOMAIN(50)),
  INDEX IX_B_MAIL_DOMAIN_EMAIL (DOMAIN(50))
);

CREATE INDEX mail_spam_good ON b_mail_spam_weight(GOOD_CNT);
CREATE INDEX mail_spam_bad ON b_mail_spam_weight(BAD_CNT);
