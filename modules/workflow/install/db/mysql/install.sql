create table if not exists b_workflow_document (
   ID int(18) not null auto_increment,
   STATUS_ID int(18) not null default '0',
   DATE_ENTER datetime,
   DATE_MODIFY datetime,
   DATE_LOCK datetime,
   ENTERED_BY int(18),
   MODIFIED_BY int(18),
   LOCKED_BY int(18),
   FILENAME varchar(255) not null,
   SITE_ID char(2),
   TITLE varchar(255),
   BODY longtext,
   BODY_TYPE varchar(4) not null default 'html',
   PROLOG longtext,
   EPILOG longtext,
   COMMENTS text,
   primary key (ID));

create table if not exists b_workflow_file (
   ID int(18) not null auto_increment,
   DOCUMENT_ID int(18) default '0',
   TIMESTAMP_X datetime,
   MODIFIED_BY int(18),
   TEMP_FILENAME varchar(255),
   FILENAME varchar(255),
   FILESIZE int(18),
   primary key (ID),
   unique IX_TEMP_FILENAME (TEMP_FILENAME));

create table if not exists b_workflow_log (
   ID int(18) not null auto_increment,
   DOCUMENT_ID int(18) not null default '0',
   TIMESTAMP_X datetime,
   MODIFIED_BY int(18),
   TITLE varchar(255),
   FILENAME varchar(255),
   SITE_ID char(2),
   BODY longtext,
   BODY_TYPE varchar(4) not null default 'html',
   STATUS_ID int(18) not null default '0',
   COMMENTS text,
   primary key (ID),
   index IX_DOCUMENT_ID (DOCUMENT_ID));

create table if not exists b_workflow_move (
   ID int(18) not null auto_increment,
   TIMESTAMP_X datetime not null,
   DOCUMENT_ID int(18),
   IBLOCK_ELEMENT_ID int(18),
   OLD_STATUS_ID int(18) not null default '0',
   STATUS_ID int(18) not null default '0',
   LOG_ID int(18),
   USER_ID int(18),
   primary key (ID),
   index IX_DOCUMENT_ID (DOCUMENT_ID),
   index IX_B_WORKFLOW_MOVE_2 (IBLOCK_ELEMENT_ID)
);

create table if not exists b_workflow_preview (
   ID int(18) not null auto_increment,
   DOCUMENT_ID int(18) not null default '0',
   TIMESTAMP_X datetime,
   FILENAME varchar(255),
   primary key (ID));

create table if not exists b_workflow_status (
   ID int(18) not null auto_increment,
   TIMESTAMP_X timestamp,
   C_SORT int(18) default '100',
   ACTIVE char(1) not null default 'Y',
   TITLE varchar(255) not null,
   DESCRIPTION text,
   IS_FINAL char(1) not null default 'N',
   NOTIFY char(1) not null default 'Y',
   primary key (ID));

create table if not exists b_workflow_status2group (
   ID int(18) not null auto_increment,
   STATUS_ID int(18) not null default '0',
   GROUP_ID int(18) not null default '0',
   PERMISSION_TYPE int(18) not null default '0',
   primary key (ID),
   index IX_STATUS_ID (STATUS_ID));
