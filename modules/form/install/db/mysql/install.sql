create table if not exists b_form (
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	NAME varchar(255) not null,
	SID varchar(50) not null,
	BUTTON varchar(255),
	C_SORT int(18) default '100',
	FIRST_SITE_ID char(2),
	IMAGE_ID int(18),
	USE_CAPTCHA char(1) null default 'N',
	DESCRIPTION text,
	DESCRIPTION_TYPE varchar(4) not null default 'html',
	FORM_TEMPLATE text null,
	USE_DEFAULT_TEMPLATE char(1) null default 'Y',
	SHOW_TEMPLATE varchar(255),
	MAIL_EVENT_TYPE varchar(255),
	SHOW_RESULT_TEMPLATE varchar(255),
	PRINT_RESULT_TEMPLATE varchar(255),
	EDIT_RESULT_TEMPLATE varchar(255),
	FILTER_RESULT_TEMPLATE text,
	TABLE_RESULT_TEMPLATE text,
	USE_RESTRICTIONS CHAR(1) NULL DEFAULT 'N',
	RESTRICT_USER INT(5) NULL DEFAULT '0',
	RESTRICT_TIME INT(10) NULL DEFAULT '0',
	RESTRICT_STATUS VARCHAR(255) NULL,
	STAT_EVENT1 varchar(255),
	STAT_EVENT2 varchar(255),
	STAT_EVENT3 varchar(255),
	primary key (ID),
	index IX_SID (SID)
);

create table if not exists b_form_2_mail_template (
	FORM_ID int(18) not null default '0',
	MAIL_TEMPLATE_ID int(18) not null default '0',
	primary key (FORM_ID, MAIL_TEMPLATE_ID)
);

create table if not exists b_form_2_site (
	FORM_ID int(18) not null default '0',
	SITE_ID char(2) not null,
	primary key (FORM_ID, SITE_ID)
);

create table if not exists b_form_answer (
	ID int(18) not null auto_increment,
	FIELD_ID int(18) not null default '0',
	TIMESTAMP_X datetime,
	MESSAGE text,
	C_SORT int(18) not null default '100',
	ACTIVE char(1) not null default 'Y',
	VALUE varchar(255),
	FIELD_TYPE varchar(255) not null default 'text',
	FIELD_WIDTH int(18),
	FIELD_HEIGHT int(18),
	FIELD_PARAM text,
	primary key (ID),
	index IX_FIELD_ID (FIELD_ID)
);

create table if not exists b_form_field (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default '0',
	TIMESTAMP_X datetime,
	ACTIVE char(1) not null default 'Y',
	TITLE text,
	TITLE_TYPE varchar(4) not null default 'text',
	SID varchar(50),
	C_SORT int(18) not null default '100',
	ADDITIONAL char(1) not null default 'N',
	REQUIRED char(1) not null default 'N',
	IN_FILTER char(1) not null default 'N',
	IN_RESULTS_TABLE char(1) not null default 'N',
	IN_EXCEL_TABLE char(1) not null default 'Y',
	FIELD_TYPE varchar(50),
	IMAGE_ID int(18),
	COMMENTS text,
	FILTER_TITLE text,
	RESULTS_TABLE_TITLE text,
	primary key (ID),
	index IX_FORM_ID (FORM_ID),
	index IX_SID (SID)
);

create table if not exists b_form_field_filter (
	ID int(18) not null auto_increment,
	FIELD_ID int(18) not null default '0',
	PARAMETER_NAME varchar(50) not null,
	FILTER_TYPE varchar(50) not null,
	primary key (ID),
	index IX_FIELD_ID (FIELD_ID)
);

create table if not exists b_form_field_validator (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default '0',
	FIELD_ID int(18) not null default '0',
	TIMESTAMP_X datetime null default null,
	ACTIVE char(1) null default 'y',
	C_SORT int(18) null default '100',
	VALIDATOR_SID varchar(255) not null default '',
	PARAMS text null,
	primary key  (ID),
	index IX_FORM_ID (FORM_ID),
	index IX_FIELD_ID (FIELD_ID)
);

create table if not exists b_form_menu (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default '0',
	LID char(2) not null,
	MENU varchar(50) null,
	primary key (ID),
	index IX_FORM_ID (FORM_ID)
);

create table if not exists b_form_result (
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	DATE_CREATE datetime,
	STATUS_ID int(18) not null default '0',
	FORM_ID int(18) not null default '0',
	USER_ID int(18),
	USER_AUTH char(1) not null default 'N',
	STAT_GUEST_ID int(18),
	STAT_SESSION_ID int(18),
	SENT_TO_CRM char(1) null default 'N',
	primary key (ID),
	index IX_FORM_ID (FORM_ID),
	index IX_STATUS_ID (STATUS_ID),
	index IX_SENT_TO_CRM (SENT_TO_CRM)
);

create table if not exists b_form_result_answer (
	ID int(18) not null auto_increment,
	RESULT_ID int(18) not null default '0',
	FORM_ID int(18) not null default '0',
	FIELD_ID int(18) not null default '0',
	ANSWER_ID int(18),
	ANSWER_TEXT text,
	ANSWER_TEXT_SEARCH longtext,
	ANSWER_VALUE varchar(255),
	ANSWER_VALUE_SEARCH longtext,
	USER_TEXT longtext,
	USER_TEXT_SEARCH longtext,
	USER_DATE datetime,
	USER_FILE_ID int(18),
	USER_FILE_NAME varchar(255),
	USER_FILE_IS_IMAGE char(1),
	USER_FILE_HASH varchar(255),
	USER_FILE_SUFFIX varchar(255),
	USER_FILE_SIZE int(18),
	primary key (ID),
	index IX_RESULT_ID (RESULT_ID),
	index IX_FIELD_ID (FIELD_ID),
	index IX_ANSWER_ID (ANSWER_ID)
);

create table if not exists b_form_2_group (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default '0',
	GROUP_ID int(18) not null default '0',
	PERMISSION int(5) not null default '1',
	primary key (ID),
	index IX_FORM_ID (FORM_ID));

create table if not exists b_form_status (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default '0',
	TIMESTAMP_X datetime,
	ACTIVE char(1) not null default 'Y',
	C_SORT int(18) not null default '100',
	TITLE varchar(255) not null,
	DESCRIPTION text,
	DEFAULT_VALUE char(1) not null default 'N',
	CSS varchar(255) default 'statusgreen',
	HANDLER_OUT varchar(255),
	HANDLER_IN varchar(255),
	MAIL_EVENT_TYPE varchar(255) NULL DEFAULT NULL,
	primary key (ID),
	index IX_FORM_ID (FORM_ID)
);

create table if not exists b_form_status_2_group (
	ID int(18) not null auto_increment,
	STATUS_ID int(18) not null default '0',
	GROUP_ID int(18) not null default '0',
	PERMISSION varchar(50) not null,
	primary key (ID),
	index IX_FORM_STATUS_GROUP (STATUS_ID, GROUP_ID)
);

create table if not exists b_form_status_2_mail_template (
	STATUS_ID int(18) not null default '0',
	MAIL_TEMPLATE_ID int(18) not null default '0',
	primary key (STATUS_ID, MAIL_TEMPLATE_ID)
);

create table if not exists b_form_crm (
	ID int(18) not null auto_increment,
	NAME varchar (255) not null default '',
	ACTIVE char(1) null default 'Y',
	URL varchar(255) not null default '',
	AUTH_HASH varchar(32) null default '',
	PRIMARY KEY (ID)
);

create table if not exists b_form_crm_link (
	ID int(18) not null auto_increment,
	FORM_ID int(18) not null default 0,
	CRM_ID int(18) not null default 0,
	LINK_TYPE char(1) not null default 'M',
	PRIMARY KEY (ID),
	UNIQUE INDEX ux_b_form_crm_link_1 (FORM_ID,CRM_ID)
);

create table if not exists b_form_crm_field (
	ID int(18) not null auto_increment,
	LINK_ID int(18) not null default 0,
	FIELD_ID int(18) null default 0,
	FIELD_ALT varchar(100) null default '',
	CRM_FIELD varchar(255) not null default '',
	PRIMARY KEY (ID),
	INDEX ix_b_form_crm_field_1 (LINK_ID)
);
