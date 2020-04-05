CREATE TABLE b_lang
(
	LID char(2) not null,
	SORT int(18) not null default '100',
	DEF char(1) not null default 'N',
	ACTIVE char(1) not null default 'Y',
	NAME varchar(50) not null,
	DIR varchar(50) not null,
	FORMAT_DATE varchar(50) null,
	FORMAT_DATETIME varchar(50) null,
	FORMAT_NAME varchar(255) null,
	WEEK_START int null,
	CHARSET varchar(255) null,
	LANGUAGE_ID char(2) not null,
	DOC_ROOT varchar(255),
	DOMAIN_LIMITED char(1) not null default 'N',
	SERVER_NAME varchar(255),
	SITE_NAME varchar(255),
	EMAIL varchar(255),
	CULTURE_ID int,
	PRIMARY KEY (LID)
);

CREATE TABLE b_language
(
	LID char(2) not null,
	SORT int not null default '100',
	DEF char(1) not null default 'N',
	ACTIVE char(1) not null default 'Y',
	NAME varchar(50) not null,
	FORMAT_DATE varchar(50) null,
	FORMAT_DATETIME varchar(50) null,
	FORMAT_NAME varchar(255) null,
	WEEK_START int null,
	CHARSET varchar(255) null,
	DIRECTION char(1) null,
	CULTURE_ID int,
	PRIMARY KEY (LID)
);

create table b_culture
(
	ID int not null auto_increment,
	CODE varchar(255),
	NAME varchar(255),
	FORMAT_DATE varchar(255),
	FORMAT_DATETIME varchar(255),
	FORMAT_NAME varchar(255),
	WEEK_START int(1) null default 1,
	CHARSET varchar(255),
	DIRECTION char(1) null default 'Y',
	primary key (ID)
);

CREATE TABLE b_lang_domain
(
	LID char(2) not null,
	DOMAIN varchar(255) not null,
	PRIMARY KEY (LID, DOMAIN)
);

CREATE TABLE b_event_type
(
	ID INT(18) not null auto_increment,
	LID char(2) not null,
	EVENT_NAME varchar(255) not null,
	NAME varchar(100),
	DESCRIPTION text,
	SORT INT(18) not null default '150',
	PRIMARY KEY (ID),
	UNIQUE ux_1 (EVENT_NAME, LID)
);

CREATE TABLE b_event_message
(
	ID INT(18) not null auto_increment,
	TIMESTAMP_X timestamp,
	EVENT_NAME varchar(255) not null,
	LID char(2),
	ACTIVE char(1) not null default 'Y',
	EMAIL_FROM varchar(255) not null default '#EMAIL_FROM#',
	EMAIL_TO varchar(255) not null default '#EMAIL_TO#',
	SUBJECT varchar(255),
	MESSAGE longtext,
	MESSAGE_PHP longtext,
	BODY_TYPE varchar(4) not null default 'text',
	BCC text,
	REPLY_TO varchar(255),
	CC varchar(255),
	IN_REPLY_TO varchar(255),
	PRIORITY varchar(50),
	FIELD1_NAME varchar(50),
	FIELD1_VALUE varchar(255),
	FIELD2_NAME varchar(50),
	FIELD2_VALUE varchar(255),
	SITE_TEMPLATE_ID varchar(255) DEFAULT NULL,
	ADDITIONAL_FIELD text NULL,
	LANGUAGE_ID char(2) NULL,
	PRIMARY KEY (ID),
	INDEX ix_b_event_message_name (EVENT_NAME(50))
);

CREATE TABLE b_event_attachment
(
  EVENT_ID int(18) not null,
  FILE_ID int(18) not null,
  IS_FILE_COPIED char(1) not null default 'Y',
  PRIMARY KEY (EVENT_ID, FILE_ID)
);

CREATE TABLE b_event_message_attachment
(
  EVENT_MESSAGE_ID int(18) not null,
  FILE_ID int(18) not null,
  PRIMARY KEY (EVENT_MESSAGE_ID, FILE_ID)
);

CREATE TABLE b_event
(
	ID INT(18) not null auto_increment,
	EVENT_NAME varchar(255) not null,
	MESSAGE_ID int(18),
	LID varchar(255) not null,
	C_FIELDS longtext,
	DATE_INSERT datetime,
	DATE_EXEC datetime,
	SUCCESS_EXEC char(1) not null default 'N',
	DUPLICATE char(1) not null default 'Y',
	LANGUAGE_ID char(2) NULL,
	PRIMARY KEY (ID),
	INDEX ix_success (SUCCESS_EXEC),
	INDEX ix_b_event_date_exec (DATE_EXEC)
);

CREATE TABLE b_group
(
	ID int(18) not null auto_increment,
	TIMESTAMP_X timestamp,
	ACTIVE char(1) not null default 'Y',
	C_SORT int(18) not null default '100',
	ANONYMOUS char(1) not null default 'N',
	NAME varchar(255) not null,
	DESCRIPTION varchar(255),
	SECURITY_POLICY text,
	STRING_ID varchar(255),
	PRIMARY KEY (ID)
);

CREATE TABLE b_user
(
	ID int(18) not null auto_increment,
	TIMESTAMP_X timestamp,
	LOGIN varchar(50) not null,
	`PASSWORD` varchar(50) not null,
	CHECKWORD varchar(50),
	ACTIVE char(1) not null default 'Y',
	NAME varchar(50),
	LAST_NAME varchar(50),
	EMAIL varchar(255),
	LAST_LOGIN datetime,
	DATE_REGISTER datetime not null,
	LID char(2),
	PERSONAL_PROFESSION varchar(255),
	PERSONAL_WWW varchar(255),
	PERSONAL_ICQ varchar(255),
	PERSONAL_GENDER char(1),
	PERSONAL_BIRTHDATE varchar(50),
	PERSONAL_PHOTO int(18),
	PERSONAL_PHONE varchar(255),
	PERSONAL_FAX varchar(255),
	PERSONAL_MOBILE varchar(255),
	PERSONAL_PAGER varchar(255),
	PERSONAL_STREET text,
	PERSONAL_MAILBOX varchar(255),
	PERSONAL_CITY varchar(255),
	PERSONAL_STATE varchar(255),
	PERSONAL_ZIP varchar(255),
	PERSONAL_COUNTRY varchar(255),
	PERSONAL_NOTES text,
	WORK_COMPANY varchar(255),
	WORK_DEPARTMENT varchar(255),
	WORK_POSITION varchar(255),
	WORK_WWW varchar(255),
	WORK_PHONE varchar(255),
	WORK_FAX varchar(255),
	WORK_PAGER varchar(255),
	WORK_STREET text,
	WORK_MAILBOX varchar(255),
	WORK_CITY varchar(255),
	WORK_STATE varchar(255),
	WORK_ZIP varchar(255),
	WORK_COUNTRY varchar(255),
	WORK_PROFILE text,
	WORK_LOGO int(18),
	WORK_NOTES text,
	ADMIN_NOTES text,
	STORED_HASH varchar(32),
	XML_ID varchar(255),
	PERSONAL_BIRTHDAY date,
	EXTERNAL_AUTH_ID varchar(255),
	CHECKWORD_TIME datetime,
	SECOND_NAME varchar(50),
	CONFIRM_CODE varchar(8),
	LOGIN_ATTEMPTS int(18),
	LAST_ACTIVITY_DATE datetime,
	AUTO_TIME_ZONE char(1),
	TIME_ZONE varchar(50),
	TIME_ZONE_OFFSET int(18),
	TITLE varchar(255) null,
	BX_USER_ID varchar(32) null,
	LANGUAGE_ID char(2) null,
	PRIMARY KEY (ID),
	UNIQUE ix_login (LOGIN, EXTERNAL_AUTH_ID),
	INDEX ix_b_user_email (EMAIL),
	INDEX ix_b_user_activity_date (LAST_ACTIVITY_DATE),
	INDEX IX_B_USER_XML_ID (XML_ID)
);

CREATE TABLE b_user_index
(
	USER_ID int(11) not null,
	SEARCH_USER_CONTENT text null,
	SEARCH_DEPARTMENT_CONTENT text null,
	SEARCH_ADMIN_CONTENT text null,
	NAME varchar(50),
	LAST_NAME varchar(50),
	SECOND_NAME varchar(50),
	WORK_POSITION varchar(255),
	UF_DEPARTMENT_NAME varchar(255),
	PRIMARY KEY (USER_ID)
);

CREATE TABLE b_user_group
(
	USER_ID INT(18) not null,
	GROUP_ID INT(18) not null,
	DATE_ACTIVE_FROM datetime,
	DATE_ACTIVE_TO datetime,
	UNIQUE ix_user_group (USER_ID, GROUP_ID),
	INDEX ix_user_group_group (GROUP_ID)
);

CREATE TABLE b_user_field_confirm
(
	ID INT(18) not null auto_increment,
	USER_ID INT(18) not null,
	DATE_CHANGE timestamp,
	FIELD varchar(255) not null,
	FIELD_VALUE varchar(255) not null,
	CONFIRM_CODE varchar(32) not null,
	PRIMARY KEY (ID),
	INDEX ix_b_user_field_confirm1 (USER_ID, CONFIRM_CODE)
);

CREATE TABLE b_module
(
	ID VARCHAR(50) not null,
	DATE_ACTIVE timestamp,
	PRIMARY KEY (ID)
);

CREATE TABLE b_option
(
	MODULE_ID VARCHAR(50),
	NAME VARCHAR(50) not null,
	VALUE TEXT,
	DESCRIPTION VARCHAR(255),
	SITE_ID CHAR(2),
	UNIQUE ix_option(MODULE_ID, NAME, SITE_ID),
	INDEX ix_option_name(NAME)
);

CREATE TABLE b_module_to_module
(
	ID int not null auto_increment,
	TIMESTAMP_X TIMESTAMP,
	SORT INT(18) not null default '100',
	FROM_MODULE_ID VARCHAR(50) not null,
	MESSAGE_ID VARCHAR(255) not null,
	TO_MODULE_ID VARCHAR(50) not null,
	TO_PATH VARCHAR(255),
	TO_CLASS VARCHAR(255),
	TO_METHOD VARCHAR(255),
	TO_METHOD_ARG varchar(255),
	VERSION int(18) null,
	PRIMARY KEY (ID),
	INDEX ix_module_to_module(FROM_MODULE_ID(20), MESSAGE_ID(20), TO_MODULE_ID(20), TO_CLASS(20), TO_METHOD(20))
);

CREATE TABLE b_agent
(
	ID INT(18) not null auto_increment,
	MODULE_ID varchar(50),
	SORT INT(18) not null default '100',
	NAME text null,
	ACTIVE char(1) not null default 'Y',
	LAST_EXEC datetime,
	NEXT_EXEC datetime not null,
	DATE_CHECK datetime,
	AGENT_INTERVAL INT(18) default '86400',
	IS_PERIOD char(1) default 'Y',
	USER_ID INT(18),
	RUNNING char(1) not null default 'N',
	PRIMARY KEY (ID),
	INDEX ix_act_next_exec(ACTIVE, NEXT_EXEC),
	INDEX ix_agent_user_id(USER_ID),
	INDEX ix_agent_name(NAME(100))
);

CREATE TABLE b_file
(
	ID INT(18) not null auto_increment,
	TIMESTAMP_X timestamp,
	MODULE_ID varchar(50),
	HEIGHT INT(18),
	WIDTH INT(18),
	FILE_SIZE BIGINT null,
	CONTENT_TYPE VARCHAR(255) default 'IMAGE',
	SUBDIR VARCHAR(255),
	FILE_NAME VARCHAR(255) not null,
	ORIGINAL_NAME VARCHAR(255),
	DESCRIPTION VARCHAR(255),
	HANDLER_ID VARCHAR(50),
	EXTERNAL_ID VARCHAR(50),
	INDEX IX_B_FILE_EXTERNAL_ID(EXTERNAL_ID),
	PRIMARY KEY (ID)
);

CREATE TABLE b_module_group
(
	ID int(11) not null auto_increment,
	MODULE_ID varchar(50) not null,
	GROUP_ID int(11) not null,
	G_ACCESS varchar(255) not null,
	SITE_ID char(2),
	PRIMARY KEY (ID),
	UNIQUE UK_GROUP_MODULE(MODULE_ID, GROUP_ID, SITE_ID)
);

CREATE TABLE b_favorite
(
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	DATE_CREATE datetime,
	C_SORT int(18) not null default '100',
	MODIFIED_BY int(18),
	CREATED_BY int(18),
	MODULE_ID varchar(50),
	NAME varchar(255),
	URL text,
	COMMENTS text,
	LANGUAGE_ID char(2),
	USER_ID int null,
	CODE_ID int(18),
	COMMON char(1) not null default 'Y',
	MENU_ID varchar(255),
	PRIMARY KEY (ID)
);

CREATE TABLE b_user_stored_auth
(
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	DATE_REG datetime not null,
	LAST_AUTH datetime not null,
	STORED_HASH varchar(32) not null,
	TEMP_HASH char(1) not null default 'N',
	IP_ADDR int(10) unsigned not null,
	PRIMARY KEY (ID),
	INDEX ux_user_hash (USER_ID)
);

CREATE TABLE b_site_template
(
	ID int not null auto_increment,
	SITE_ID char(2) not null,
	`CONDITION` varchar(255),
	SORT int not null default '500',
	TEMPLATE varchar(255) not null,
	PRIMARY KEY (ID),
	INDEX ix_site_template_site (SITE_ID)
);

CREATE TABLE b_event_message_site
(
	EVENT_MESSAGE_ID int not null,
	SITE_ID char(2) not null,
	PRIMARY KEY (EVENT_MESSAGE_ID, SITE_ID)
);

CREATE TABLE b_user_option
(
	ID int not null auto_increment,
	USER_ID int not null,
	CATEGORY varchar(50) not null,
	NAME varchar(255) not null,
	VALUE mediumtext null,
	COMMON char(1) not null default 'N',
	PRIMARY KEY (ID),
	UNIQUE INDEX ux_user_category_name(USER_ID, CATEGORY, NAME)
);

CREATE TABLE b_captcha
(
	ID varchar(32) not null,
	CODE varchar(20) not null,
	IP varchar(15) not null,
	DATE_CREATE datetime not null,
	UNIQUE UX_B_CAPTCHA(ID)
);

CREATE TABLE b_user_field
(
	ID int(11) not null auto_increment,
	ENTITY_ID varchar(20),
	FIELD_NAME varchar(20),
	USER_TYPE_ID varchar(50),
	XML_ID varchar(255),
	SORT int,
	MULTIPLE char(1) not null default 'N',
	MANDATORY char(1) not null default 'N',
	SHOW_FILTER char(1) not null default 'N',
	SHOW_IN_LIST char(1) not null default 'Y',
	EDIT_IN_LIST char(1) not null default 'Y',
	IS_SEARCHABLE char(1) not null default 'N',
	SETTINGS text,
	PRIMARY KEY (ID),
	UNIQUE ux_user_type_entity(ENTITY_ID, FIELD_NAME)
);

CREATE TABLE b_user_field_lang
(
	USER_FIELD_ID int(11),
	LANGUAGE_ID char(2),
	EDIT_FORM_LABEL varchar(255),
	LIST_COLUMN_LABEL varchar(255),
	LIST_FILTER_LABEL varchar(255),
	ERROR_MESSAGE varchar(255),
	HELP_MESSAGE varchar(255),
	PRIMARY KEY (USER_FIELD_ID, LANGUAGE_ID)
);

CREATE TABLE if not exists b_user_field_enum
(
	ID int(11) not null auto_increment,
	USER_FIELD_ID int(11),
	VALUE varchar(255) not null,
	DEF char(1) not null default 'N',
	SORT int(11) not null default 500,
	XML_ID varchar(255) not null,
	PRIMARY KEY (ID),
	UNIQUE ux_user_field_enum(USER_FIELD_ID, XML_ID)
);

CREATE TABLE b_task
(
	ID int(18) not null auto_increment,
	NAME varchar(100) not null,
	LETTER char(1),
	MODULE_ID varchar(50) not null,
	SYS char(1) not null,
	DESCRIPTION varchar(255),
	BINDING varchar(50) default 'module',
	PRIMARY KEY (ID),
	INDEX ix_task(MODULE_ID, BINDING, LETTER, SYS)
);

CREATE TABLE b_group_task
(
	GROUP_ID int(18) not null,
	TASK_ID int(18) not null,
	EXTERNAL_ID varchar(50) default '',
	PRIMARY KEY (GROUP_ID,TASK_ID)
);

CREATE TABLE b_operation
(
	ID int(18) not null auto_increment,
	NAME varchar(50) not null,
	MODULE_ID varchar(50) not null,
	DESCRIPTION varchar(255),
	BINDING varchar(50) default 'module',
	PRIMARY KEY (ID)
);

CREATE TABLE b_task_operation
(
	TASK_ID int(18) not null,
	OPERATION_ID int(18) not null,
	PRIMARY KEY (TASK_ID,OPERATION_ID)
);

CREATE TABLE b_group_subordinate(
	ID int(18) not null,
	AR_SUBGROUP_ID text not null,
	PRIMARY KEY (ID)
);

CREATE TABLE b_rating
(
	ID int(11) not null auto_increment,
	ACTIVE char(1) not null,
	NAME varchar(512) not null,
	ENTITY_ID varchar(50) not null,
	CALCULATION_METHOD varchar(3) not null default 'SUM',
	CREATED datetime,
	LAST_MODIFIED datetime,
	LAST_CALCULATED datetime,
	POSITION char(1) null default 'N',
	AUTHORITY char(1) null default 'N',
	CALCULATED char(1) not null default 'N',
	CONFIGS text,
	PRIMARY KEY (ID)
);

CREATE TABLE b_rating_component
(
	ID int(11) not null auto_increment,
	RATING_ID int(11) not null,
	ACTIVE char(1) not null default 'N',
	ENTITY_ID varchar(50) not null,
	MODULE_ID varchar(50) not null,
	RATING_TYPE varchar(50) not null,
	NAME varchar(50) not null,
	COMPLEX_NAME varchar(200) not null,
	CLASS varchar(255) not null,
	CALC_METHOD varchar(255) not null,
	EXCEPTION_METHOD varchar(255),
	LAST_MODIFIED datetime,
	LAST_CALCULATED datetime,
	NEXT_CALCULATION datetime,
	REFRESH_INTERVAL int(11) not null,
	CONFIG text,
	PRIMARY KEY (ID),
	KEY IX_RATING_ID_1 (RATING_ID, ACTIVE, NEXT_CALCULATION)
);

CREATE TABLE b_rating_component_results
(
	ID int(11) not null auto_increment,
	RATING_ID int(11) not null,
	ENTITY_TYPE_ID varchar(50) not null,
	ENTITY_ID int(11) not null,
	MODULE_ID varchar(50) not null,
	RATING_TYPE varchar(50) not null,
	NAME varchar(50) not null,
	COMPLEX_NAME varchar(200) not null,
	CURRENT_VALUE decimal(18,4),
	PRIMARY KEY (ID),
	KEY IX_ENTITY_TYPE_ID (ENTITY_TYPE_ID),
	KEY IX_COMPLEX_NAME (COMPLEX_NAME),
	KEY IX_RATING_ID_2 (RATING_ID, COMPLEX_NAME)
);

CREATE TABLE b_rating_results
(
	ID int(11) not null auto_increment,
	RATING_ID int(11) not null,
	ENTITY_TYPE_ID varchar(50) not null,
	ENTITY_ID int(11) not null,
	CURRENT_VALUE decimal(18,4),
	PREVIOUS_VALUE decimal(18,4),
	CURRENT_POSITION int(11) null default '0',
	PREVIOUS_POSITION int(11) null default '0',
	PRIMARY KEY (ID),
	KEY IX_RATING_3 (RATING_ID, ENTITY_TYPE_ID, ENTITY_ID),
	KEY IX_RATING_4 (RATING_ID, ENTITY_ID)
);

CREATE TABLE b_rating_vote
(
	ID int(11) not null auto_increment,
	RATING_VOTING_ID int(11) not null,
	ENTITY_TYPE_ID varchar(50) not null,
	ENTITY_ID int(11) not null,
	OWNER_ID int(11) not null,
	VALUE decimal(18,4) not null,
	ACTIVE char(1) not null,
	CREATED datetime not null,
	USER_ID int(11) not null,
	USER_IP varchar(64) not null,
	PRIMARY KEY (ID),
	KEY IX_RAT_VOTE_ID (RATING_VOTING_ID, USER_ID),
	KEY IX_RAT_VOTE_ID_2 (ENTITY_TYPE_ID, ENTITY_ID, USER_ID),
	KEY IX_RAT_VOTE_ID_3 (OWNER_ID, CREATED),
	KEY IX_RAT_VOTE_ID_4 (USER_ID),
	KEY IX_RAT_VOTE_ID_5 (CREATED, VALUE),
	KEY IX_RAT_VOTE_ID_6 (ACTIVE),
	KEY IX_RAT_VOTE_ID_7 (RATING_VOTING_ID, CREATED),
	KEY IX_RAT_VOTE_ID_8 (ENTITY_TYPE_ID, CREATED),
	KEY IX_RAT_VOTE_ID_9 (CREATED, USER_ID)
);

CREATE TABLE b_rating_voting
(
	ID int(11) not null auto_increment,
	ENTITY_TYPE_ID varchar(50) not null,
	ENTITY_ID int(11) not null,
	OWNER_ID int(11) not null,
	ACTIVE char(1) not null,
	CREATED datetime,
	LAST_CALCULATED datetime,
	TOTAL_VALUE decimal(18,4) not null,
	TOTAL_VOTES int(11) not null,
	TOTAL_POSITIVE_VOTES int(11) not null,
	TOTAL_NEGATIVE_VOTES int(11) not null,
	PRIMARY KEY (ID),
	KEY IX_ENTITY_TYPE_ID_2 (ENTITY_TYPE_ID, ENTITY_ID, ACTIVE),
	KEY IX_ENTITY_TYPE_ID_4 (TOTAL_VALUE)
);

CREATE TABLE b_rating_voting_prepare
(
	ID int(11) not null auto_increment,
	RATING_VOTING_ID int(11) not null,
	TOTAL_VALUE decimal(18,4) not null,
	TOTAL_VOTES int(11) not null,
	TOTAL_POSITIVE_VOTES int(11) not null,
	TOTAL_NEGATIVE_VOTES int(11) not null,
	PRIMARY KEY (ID),
	KEY IX_RATING_VOTING_ID (RATING_VOTING_ID)
);

CREATE TABLE b_rating_prepare
(
	ID int(11) NULL
);

CREATE TABLE b_rating_rule
(
	ID int(11) not null auto_increment,
	ACTIVE char(1) not null default 'N',
	NAME varchar(256) not null,
	ENTITY_TYPE_ID varchar(50) not null,
	CONDITION_NAME varchar(200) not null,
	CONDITION_MODULE varchar(50),
	CONDITION_CLASS varchar(255) not null,
	CONDITION_METHOD varchar(255) not null,
	CONDITION_CONFIG text,
	ACTION_NAME varchar(200) not null,
	ACTION_CONFIG text,
	ACTIVATE char(1) not null default 'N',
	ACTIVATE_CLASS varchar(255) not null,
	ACTIVATE_METHOD varchar(255) not null,
	DEACTIVATE char(1) not null default 'N',
	DEACTIVATE_CLASS varchar(255) not null,
	DEACTIVATE_METHOD varchar(255) not null,
	CREATED datetime,
	LAST_MODIFIED datetime,
	LAST_APPLIED datetime,
	PRIMARY KEY (ID)
);

CREATE TABLE b_rating_rule_vetting
(
	ID int(11) not null auto_increment,
	RULE_ID int(11) not null,
	ENTITY_TYPE_ID varchar(50) not null,
	ENTITY_ID int(11) not null,
	ACTIVATE char(1) not null default 'N',
	APPLIED char(1) not null default 'N',
	PRIMARY KEY (ID),
	KEY RULE_ID (RULE_ID,ENTITY_TYPE_ID,ENTITY_ID)
);

CREATE TABLE b_rating_user
(
	ID int(11) not null auto_increment,
	RATING_ID int(11) not null,
	ENTITY_ID int(11) not null,
	BONUS decimal(18,4) null default '0.0000',
	VOTE_WEIGHT decimal(18,4) null default '0.0000',
	VOTE_COUNT int(11) null default '0',
	PRIMARY KEY (ID),
	KEY RATING_ID (RATING_ID, ENTITY_ID),
	KEY IX_B_RAT_USER_2 (ENTITY_ID)
);

CREATE TABLE b_rating_vote_group
(
	ID int(11) not null auto_increment,
	GROUP_ID int(11) not null,
	TYPE char(1) not null,
	PRIMARY KEY (ID),
	KEY RATING_ID (GROUP_ID, TYPE)
);

CREATE TABLE b_rating_weight
(
	ID int(11) not null auto_increment,
	RATING_FROM decimal(18,4) not null,
	RATING_TO decimal(18,4) not null,
	WEIGHT decimal(18,4) default '0',
	COUNT int(11) default '0',
	PRIMARY KEY (ID)
);
insert into b_rating_weight (RATING_FROM, RATING_TO, WEIGHT, COUNT) VALUES (-1000000, 1000000, 1, 10);

CREATE TABLE b_event_log
(
	/*SYSTEM GENERATED*/
	ID INT(18) not null auto_increment,
	TIMESTAMP_X timestamp,

	/*CALLER INFO*/
	SEVERITY VARCHAR(50) not null, /*SECURITY, WARNING, NOTICE*/
	AUDIT_TYPE_ID VARCHAR(50) not null, /*LOGIN_OK, LOGIN_WRONG_PASSWORD*/
	MODULE_ID VARCHAR(50) not null, /*main, iblock, main.register */
	ITEM_ID VARCHAR(255) not null, /*user login, element id*/

	/*FROM $_SERVER*/
	REMOTE_ADDR VARCHAR(40),
	USER_AGENT TEXT, /*2000 for oracle and mssql*/
	REQUEST_URI TEXT, /*2000 for oracle and mssql*/

	/*FROM CONSTANTS AND VARIABLES*/
	SITE_ID CHAR(2), /*if defined*/
	USER_ID INT(18), /*if logged in*/
	GUEST_ID INT(18), /* if statistics installed*/

	/*ADDITIONAL*/
	DESCRIPTION MEDIUMTEXT,
	PRIMARY KEY (ID),
	INDEX ix_b_event_log_time(TIMESTAMP_X),
	INDEX ix_b_event_log_audit_type(AUDIT_TYPE_ID)
);

CREATE TABLE b_cache_tag
(
	SITE_ID char(2),
	CACHE_SALT char(4),
	RELATIVE_PATH varchar(255),
	TAG varchar(100),
	INDEX ix_b_cache_tag_0 (SITE_ID, CACHE_SALT, RELATIVE_PATH(50)),
	INDEX ix_b_cache_tag_1 (TAG)
);

CREATE TABLE b_user_hit_auth
(
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	HASH varchar(32) not null,
	URL varchar(255) not null,
	SITE_ID char(2),
	TIMESTAMP_X timestamp,
	PRIMARY KEY (ID),
	INDEX IX_USER_HIT_AUTH_1(HASH),
	INDEX IX_USER_HIT_AUTH_2(USER_ID),
	INDEX IX_USER_HIT_AUTH_3(TIMESTAMP_X)
);

CREATE TABLE b_undo
(
	ID varchar(255) not null,
	MODULE_ID varchar(50),
	UNDO_TYPE varchar(50),
	UNDO_HANDLER varchar(255),
	CONTENT mediumtext,
	USER_ID int,
	TIMESTAMP_X int,
	PRIMARY KEY (ID)
);

CREATE TABLE b_user_digest
(
	USER_ID int not null,
	DIGEST_HA1 varchar(32) not null,
	PRIMARY KEY (USER_ID)
);

CREATE TABLE b_checklist
(
	ID int(11) not null AUTO_INCREMENT,
	DATE_CREATE varchar(255),
	TESTER varchar(255),
	COMPANY_NAME varchar(255),
	PICTURE int(11),
	TOTAL int(11),
	SUCCESS int(11),
	FAILED int(11),
	PENDING int(11),
	SKIP int(11),
	STATE longtext,
	REPORT_COMMENT text,
	REPORT char(1) default 'Y',
	EMAIL varchar(50),
	PHONE varchar(50),
	SENDED_TO_BITRIX char(1) null default 'N',
	HIDDEN char(1) null default 'N',
	PRIMARY KEY (ID)
);

CREATE TABLE b_short_uri
(
	ID int(18) not null auto_increment,
	URI varchar(2000) not null,
	URI_CRC int(18) not null,
	SHORT_URI varbinary(250) not null,
	SHORT_URI_CRC int(18) not null,
	STATUS int(18) not null default 301,
	MODIFIED datetime not null,
	LAST_USED datetime null,
	NUMBER_USED int(18) not null default 0,
	PRIMARY KEY (ID),
	INDEX ux_b_short_uri_1 (SHORT_URI_CRC),
	INDEX ux_b_short_uri_2 (URI_CRC)
);

CREATE TABLE b_user_access
(
	USER_ID int(11),
	PROVIDER_ID varchar(50),
	ACCESS_CODE varchar(100),
	INDEX ix_ua_user_provider (USER_ID, PROVIDER_ID),
	INDEX ix_ua_user_access (USER_ID, ACCESS_CODE),
	INDEX ix_ua_access (ACCESS_CODE)
);

insert into b_user_access (USER_ID, PROVIDER_ID, ACCESS_CODE) values (0, 'group', 'G2');

CREATE TABLE b_user_access_check
(
	USER_ID int(11),
	PROVIDER_ID varchar(50),
	INDEX ix_uac_user_provider (USER_ID, PROVIDER_ID)
);

CREATE TABLE b_user_counter
(
	USER_ID int(18) not null,
	SITE_ID char(2) not null default '**',
	CODE varchar(50) not null,
	CNT int(18) not null default 0,
	LAST_DATE datetime,
	TIMESTAMP_X datetime not null default '3000-01-01 00:00:00',
	TAG varchar(255),
	PARAMS text,
	SENT char(1) null default '0',
	PRIMARY KEY (USER_ID, SITE_ID, CODE),
	INDEX ix_buc_tag (TAG),
	INDEX ix_buc_code (CODE),
	INDEX ix_buc_ts (TIMESTAMP_X),
	INDEX ix_buc_sent_userid (SENT, USER_ID)
);

CREATE TABLE b_hot_keys_code
(
	ID int(18) not null AUTO_INCREMENT,
	CLASS_NAME varchar(50),
	CODE varchar(255),
	NAME varchar(255),
	COMMENTS varchar(255),
	TITLE_OBJ varchar(50),
	URL varchar(255),
	IS_CUSTOM tinyint(1) not null default '1',
	PRIMARY KEY (ID),
	INDEX ix_hot_keys_code_cn (CLASS_NAME),
	INDEX ix_hot_keys_code_url (URL)
);

INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(3, 'CAdminTabControl', 'NextTab();', 'HK_DB_CADMINTC', 'HK_DB_CADMINTC_C', 'tab-container', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(5, 'btn_new', 'var d=BX (''btn_new''); if (d) location.href = d.href;', 'HK_DB_BUT_ADD', 'HK_DB_BUT_ADD_C', 'btn_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(6, 'btn_excel', 'var d=BX(''btn_excel''); if (d) location.href = d.href;', 'HK_DB_BUT_EXL', 'HK_DB_BUT_EXL_C', 'btn_excel', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(7, 'btn_settings', 'var d=BX(''btn_settings''); if (d) location.href = d.href;', 'HK_DB_BUT_OPT', 'HK_DB_BUT_OPT_C', 'btn_settings', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(8, 'btn_list', 'var d=BX(''btn_list''); if (d) location.href = d.href;', 'HK_DB_BUT_LST', 'HK_DB_BUT_LST_C', 'btn_list', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(9, 'Edit_Save_Button', 'var d=BX .findChild(document, {attribute: {''name'': ''save''}}, true );  if (d) d.click();', 'HK_DB_BUT_SAVE', 'HK_DB_BUT_SAVE_C', 'Edit_Save_Button', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(10, 'btn_delete', 'var d=BX(''btn_delete''); if (d) location.href = d.href;', 'HK_DB_BUT_DEL', 'HK_DB_BUT_DEL_C', 'btn_delete', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(12, 'CAdminFilter', 'var d=BX .findChild(document, {attribute: {''name'': ''find''}}, true ); if (d) d.focus();', 'HK_DB_FLT_FND', 'HK_DB_FLT_FND_C', 'find', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(13, 'CAdminFilter', 'var d=BX .findChild(document, {attribute: {''name'': ''set_filter''}}, true );  if (d) d.click();', 'HK_DB_FLT_BUT_F', 'HK_DB_FLT_BUT_F_C', 'set_filter', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(14, 'CAdminFilter', 'var d=BX .findChild(document, {attribute: {''name'': ''del_filter''}}, true );  if (d) d.click();', 'HK_DB_FLT_BUT_CNL', 'HK_DB_FLT_BUT_CNL_C', 'del_filter', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(15, 'bx-panel-admin-button-help-icon-id', 'var d=BX(''bx-panel-admin-button-help-icon-id''); if (d) location.href = d.href;', 'HK_DB_BUT_HLP', 'HK_DB_BUT_HLP_C', 'bx-panel-admin-button-help-icon-id', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(17, 'Global', 'BXHotKeys.ShowSettings();', 'HK_DB_SHW_L', 'HK_DB_SHW_L_C', 'bx-panel-hotkeys', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(19, 'Edit_Apply_Button', 'var d=BX .findChild(document, {attribute: {''name'': ''apply''}}, true );  if (d) d.click();', 'HK_DB_BUT_APPL', 'HK_DB_BUT_APPL_C', 'Edit_Apply_Button', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(20, 'Edit_Cancel_Button', 'var d=BX .findChild(document, {attribute: {''name'': ''cancel''}}, true );  if (d) d.click();', 'HK_DB_BUT_CANCEL', 'HK_DB_BUT_CANCEL_C', 'Edit_Cancel_Button', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(78, 'top_panel_templ_site', '', '-=AUTONAME=-', NULL, 'top_panel_templ_site', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(77, 'top_panel_templ_templ_css', '', '-=AUTONAME=-', NULL, 'top_panel_templ_templ_css', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(76, 'top_panel_templ_site_css', '', '-=AUTONAME=-', NULL, 'top_panel_templ_site_css', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(74, 'top_panel_cache_not', '', '-=AUTONAME=-', NULL, 'top_panel_cache_not', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(75, 'top_panel_edit_mode', '', '-=AUTONAME=-', NULL, 'top_panel_edit_mode', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(73, 'top_panel_cache_comp', '', '-=AUTONAME=-', NULL, 'top_panel_cache_comp', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(72, 'top_panel_cache_page', '', '-=AUTONAME=-', NULL, 'top_panel_cache_page', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(71, 'main_top_panel_struct_panel', '', '-=AUTONAME=-', NULL, 'main_top_panel_struct_panel', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(70, 'top_panel_access_folder_new', '', '-=AUTONAME=-', NULL, 'top_panel_access_folder_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(68, 'top_panel_del_page', '', '-=AUTONAME=-', NULL, 'top_panel_del_page', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(69, 'top_panel_folder_prop', '', '-=AUTONAME=-', NULL, 'top_panel_folder_prop', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(82, 'top_panel_debug_incl', '', '-=AUTONAME=-', NULL, 'top_panel_debug_incl', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(67, 'top_panel_edit_page_php', '', '-=AUTONAME=-', NULL, 'top_panel_edit_page_php', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(65, 'top_panel_edit_page_html', '', '-=AUTONAME=-', NULL, 'top_panel_edit_page_html', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(63, 'top_panel_edit_page', '', '-=AUTONAME=-', NULL, 'top_panel_edit_page', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(64, 'top_panel_page_prop', '', '-=AUTONAME=-', NULL, 'top_panel_page_prop', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(62, 'top_panel_create_folder', '', '-=AUTONAME=-', NULL, 'top_panel_create_folder', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(60, 'top_panel_create_page', '', '-=AUTONAME=-', NULL, 'top_panel_create_page', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(58, 'top_panel_bizproc_tasks', '', '-=AUTONAME=-', NULL, 'top_panel_bizproc_tasks', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(57, 'top_panel_help', '', '-=AUTONAME=-', NULL, 'top_panel_help', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(59, 'top_panel_add_fav', '', '-=AUTONAME=-', NULL, 'top_panel_add_fav', NULL, 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(56, 'top_panel_interface_settings', '', '-=AUTONAME=-', NULL, 'top_panel_interface_settings', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(54, 'top_panel_org_fav', '', '-=AUTONAME=-', NULL, 'top_panel_org_fav', NULL, 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(55, 'top_panel_module_settings', '', '-=AUTONAME=-', NULL, 'top_panel_module_settings', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(83, 'top_panel_debug_sql', '', '-=AUTONAME=-', NULL, 'top_panel_debug_sql', NULL, 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(81, 'top_panel_debug_time', '', '-=AUTONAME=-', NULL, 'top_panel_debug_time', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(84, 'top_panel_debug_compr', '', '-=AUTONAME=-', NULL, 'top_panel_debug_compr', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(85, 'MTP_SHORT_URI1', '', '-=AUTONAME=-', NULL, 'MTP_SHORT_URI1', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(86, 'MTP_SHORT_URI_LIST', '', '-=AUTONAME=-', NULL, 'MTP_SHORT_URI_LIST', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(87, 'FMST_PANEL_STICKER_ADD', '', '-=AUTONAME=-', NULL, 'FMST_PANEL_STICKER_ADD', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(88, 'FMST_PANEL_STICKERS_SHOW', '', '-=AUTONAME=-', NULL, 'FMST_PANEL_STICKERS_SHOW', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(89, 'FMST_PANEL_CUR_STICKER_LIST', '', '-=AUTONAME=-', NULL, 'FMST_PANEL_CUR_STICKER_LIST', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(90, 'FMST_PANEL_ALL_STICKER_LIST', '', '-=AUTONAME=-', NULL, 'FMST_PANEL_ALL_STICKER_LIST', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(91, 'top_panel_menu', 'var d=BX("bx-panel-menu"); if (d) d.click();', '-=AUTONAME=-', NULL, 'bx-panel-menu', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(92, 'top_panel_admin', 'var d=BX(''bx-panel-admin-tab''); if (d) location.href = d.href;', '-=AUTONAME=-', NULL, 'bx-panel-admin-tab', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(93, 'admin_panel_site', 'var d=BX(''bx-panel-view-tab''); if (d) location.href = d.href;', '-=AUTONAME=-', NULL, 'bx-panel-view-tab', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(94, 'admin_panel_admin', 'var d=BX(''bx-panel-admin-tab''); if (d) location.href = d.href;', '-=AUTONAME=-', NULL, 'bx-panel-admin-tab', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(111, 'top_panel_create_new', '', '-=AUTONAME=-', NULL, 'top_panel_create_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(96, 'top_panel_folder_prop_new', '', '-=AUTONAME=-', NULL, 'top_panel_folder_prop_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(97, 'main_top_panel_structure', '', '-=AUTONAME=-', NULL, 'main_top_panel_structure', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(98, 'top_panel_clear_cache', '', '-=AUTONAME=-', NULL, 'top_panel_clear_cache', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(99, 'top_panel_templ', '', '-=AUTONAME=-', NULL, 'top_panel_templ', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(100, 'top_panel_debug', '', '-=AUTONAME=-', NULL, 'top_panel_debug', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(101, 'MTP_SHORT_URI', '', '-=AUTONAME=-', NULL, 'MTP_SHORT_URI', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(102, 'FMST_PANEL_STICKERS', '', '-=AUTONAME=-', NULL, 'FMST_PANEL_STICKERS', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(103, 'top_panel_settings', '', '-=AUTONAME=-', NULL, 'top_panel_settings', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(104, 'top_panel_fav', '', '-=AUTONAME=-', NULL, 'top_panel_fav', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(106, 'Global', 'location.href=''/bitrix/admin/hot_keys_list.php?lang=ru'';', 'HK_DB_SHW_HK', '', '', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(107, 'top_panel_edit_new', '', '-=AUTONAME=-', NULL, 'top_panel_edit_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(108, 'FLOW_PANEL_CREATE_WITH_WF', '', '-=AUTONAME=-', NULL, 'FLOW_PANEL_CREATE_WITH_WF', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(109, 'FLOW_PANEL_EDIT_WITH_WF', '', '-=AUTONAME=-', NULL, 'FLOW_PANEL_EDIT_WITH_WF', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(110, 'FLOW_PANEL_HISTORY', '', '-=AUTONAME=-', NULL, 'FLOW_PANEL_HISTORY', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(112, 'top_panel_create_folder_new', '', '-=AUTONAME=-', NULL, 'top_panel_create_folder_new', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(139, 'Global', 'location.href=''/bitrix/admin/user_admin.php?lang=''+phpVars.LANGUAGE_ID;', 'HK_DB_SHW_U', '', '', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(116, 'bx-panel-toggle', '', '-=AUTONAME=-', NULL, 'bx-panel-toggle', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(118, 'bx-panel-expander', 'var d=BX(''bx-panel-expander''); if (d) BX.fireEvent(d, ''click'');', '-=AUTONAME=-', NULL, 'bx-panel-expander', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(117, 'bx-panel-small-toggle', '', '-=AUTONAME=-', NULL, 'bx-panel-small-toggle', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(119, 'bx-panel-hider', 'var d=BX(''bx-panel-hider''); if (d) d.click();', '-=AUTONAME=-', NULL, 'bx-panel-hider', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(120, 'search-textbox-input', 'var d=BX(''search-textbox-input''); if (d) { d.click(); d.focus();}', '-=AUTONAME=-', '', 'search', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(121, 'bx-search-input', 'var d=BX(''bx-search-input''); if (d) { d.click(); d.focus(); }', '-=AUTONAME=-', '', 'bx-search-input', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(133, 'bx-panel-logout', 'var d=BX(''bx-panel-logout''); if (d) location.href = d.href;', '-=AUTONAME=-', '', 'bx-panel-logout', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(138, 'CDialog', 'var d=BX(''btn_popup_save''); if (d) d.click();', 'HK_DB_D_EDIT_SAVE', '', 'btn_popup_save', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(135, 'CDialog', 'var d=BX(''cancel''); if (d) d.click();', 'HK_DB_D_CANCEL', '', 'cancel', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(136, 'CDialog', 'var d=BX(''close''); if (d) d.click();', 'HK_DB_D_CLOSE', '', 'close', '', 0);
INSERT INTO b_hot_keys_code (ID, CLASS_NAME, CODE, `NAME`, COMMENTS, TITLE_OBJ, URL, IS_CUSTOM) VALUES(137, 'CDialog', 'var d=BX(''savebtn''); if (d) d.click();', 'HK_DB_D_SAVE', '', 'savebtn', '', 0);

CREATE TABLE b_hot_keys
(
	ID int(18) not null AUTO_INCREMENT,
	KEYS_STRING varchar(20) not null,
	CODE_ID int(18) not null,
	USER_ID int(18) not null,
	PRIMARY KEY (ID),
	UNIQUE ix_b_hot_keys_co_u (CODE_ID,USER_ID),
	INDEX ix_hot_keys_code (CODE_ID),
	INDEX ix_hot_keys_user (USER_ID)
);


INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+85', 139, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+80', 17, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+70', 120, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+68', 117, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+81', 3, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+75', 106, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+79', 133, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+70', 121, 0);
INSERT INTO b_hot_keys (KEYS_STRING, CODE_ID, USER_ID) VALUES('Ctrl+Alt+69', 118, 0);

CREATE TABLE b_admin_notify
(
	ID int(18) not null AUTO_INCREMENT,
	MODULE_ID VARCHAR(50),
	TAG VARCHAR(255),
	MESSAGE text,
	ENABLE_CLOSE char(1) NULL default 'Y',
	PUBLIC_SECTION char(1) NOT NULL default 'N',
	NOTIFY_TYPE char(1) NOT NULL default 'M',
	PRIMARY KEY (ID),
	KEY IX_AD_TAG (TAG)
);

CREATE TABLE b_admin_notify_lang
(
	ID int(18) not null AUTO_INCREMENT,
	NOTIFY_ID int(18) not null,
	LID char(2) not null,
	MESSAGE text,
	primary key (ID),
	index IX_ADM_NTFY_LID (LID),
	unique IX_ADM_NTFY_LANG(NOTIFY_ID, LID)
);

CREATE TABLE b_filters
(
	ID int(18) not null auto_increment,
	USER_ID int(18),
	FILTER_ID varchar(255) not null,
	NAME varchar(255) not null,
	FIELDS text not null,
	COMMON char(1),
	PRESET char(1),
	LANGUAGE_ID char(2),
	PRESET_ID varchar(255) null,
	SORT int(18) null,
	SORT_FIELD varchar(255) null,
	PRIMARY KEY (ID)
);

CREATE TABLE b_component_params
(
	ID int NOT NULL AUTO_INCREMENT,
	SITE_ID char(2) not null,
	COMPONENT_NAME varchar(255) NOT NULL,
	TEMPLATE_NAME varchar(255),
	REAL_PATH varchar(255) NOT NULL,
	SEF_MODE char(1) DEFAULT 'Y' NOT NULL,
	SEF_FOLDER varchar(255),
	START_CHAR int NOT NULL,
	END_CHAR int NOT NULL,
	PARAMETERS text,
	PRIMARY KEY (ID),
	index ix_comp_params_name(COMPONENT_NAME),
	index ix_comp_params_path(SITE_ID, REAL_PATH),
	index ix_comp_params_sname(SITE_ID, COMPONENT_NAME)
);

CREATE TABLE b_smile
(
	ID int(18) not null auto_increment,
	TYPE char(1) not null default 'S',
	SET_ID int(18) not null default 0,
	SORT int(10) not null default '150',
	TYPING varchar(100) null,
	CLICKABLE char(1) not null default 'Y',
	HIDDEN char(1) not null default 'N',
	IMAGE varchar(255) not null,
	IMAGE_DEFINITION VARCHAR (10) not null default 'SD',
	IMAGE_WIDTH int not null default 0,
	IMAGE_HEIGHT int not null default 0,
	primary key (ID)
);

CREATE TABLE b_smile_set
(
	ID int(18) not null auto_increment,
	TYPE char(1) not null default 'G',
	PARENT_ID int(18) not null default 0,
	STRING_ID varchar(255) null,
	SORT int(10) not null default '150',
	primary key (ID)
);

CREATE TABLE b_smile_lang
(
	ID int(18) not null auto_increment,
	TYPE char(1) not null default 'S',
	SID int not null,
	LID char(2) not null,
	NAME varchar(255) not null,
	primary key (ID),
	unique UX_SMILE_SL (TYPE, SID, LID)
);

CREATE TABLE `b_app_password` 
(
	`ID` INT NOT NULL AUTO_INCREMENT,
	`USER_ID` INT NOT NULL,
	`APPLICATION_ID` VARCHAR(255) NOT NULL,
	`PASSWORD` VARCHAR(255) NOT NULL,
	`DIGEST_PASSWORD` VARCHAR(255) NOT NULL,
	`DATE_CREATE` DATETIME NULL,
	`DATE_LOGIN` DATETIME NULL,
	`LAST_IP` VARCHAR(255) NULL,
	`COMMENT` VARCHAR(255) NULL,
	`SYSCOMMENT` VARCHAR(255) NULL,
	`CODE` VARCHAR(255) NULL,
	PRIMARY KEY (`ID`),
	INDEX `ix_app_password_user` (`USER_ID`)
);

CREATE TABLE b_counter_data
(
  ID varchar(16) NOT NULL,
  TYPE varchar(30) NOT NULL,
  DATA text NOT NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE b_finder_dest
(
	`USER_ID` INT NOT NULL,
	`CODE` varchar(30) NOT NULL,
	`CODE_USER_ID` INT NULL,
	`CODE_TYPE` varchar(10) NULL,
	`CONTEXT` varchar(50) NOT NULL,
	`LAST_USE_DATE` DATETIME NULL,
	PRIMARY KEY (`USER_ID`, `CODE`, `CONTEXT`),
	INDEX IX_FINDER_DEST (`CODE_TYPE`)
);

CREATE TABLE b_urlpreview_metadata
(
	ID int(11) NOT NULL AUTO_INCREMENT,
	URL varchar(200) NOT NULL,
	TYPE char(1) NOT NULL DEFAULT 'S',
	DATE_INSERT datetime NOT NULL,
	DATE_EXPIRE datetime NULL,
	TITLE varchar(200) NULL,
	DESCRIPTION text,
	IMAGE_ID int(11) NULL,
	IMAGE varchar(255) NULL,
	EMBED mediumtext,
	EXTRA text,
	PRIMARY KEY (ID),
	INDEX IX_URLPREVIEW_METADATA_URL (URL)
);

CREATE TABLE b_urlpreview_route
(
	ID int(11) NOT NULL AUTO_INCREMENT,
	ROUTE varchar(200) NOT NULL,
	MODULE varchar(50) NOT NULL,
	CLASS varchar(150) NOT NULL,
	PARAMETERS mediumtext,
	PRIMARY KEY (ID),
	UNIQUE KEY UX_URLPREVIEW_ROUTE_ROUTE (ROUTE)
);

CREATE TABLE b_geoip_handlers
(
  ID INT(11) NOT NULL AUTO_INCREMENT,
  SORT INT(10) not null default 100,
  ACTIVE CHAR(1) NOT NULL DEFAULT 'Y',
  CLASS_NAME VARCHAR(255) NOT NULL,
  CONFIG text NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE b_consent_user_consent
(
  ID INT(18) NOT NULL AUTO_INCREMENT,
  DATE_INSERT DATETIME NOT NULL,
  AGREEMENT_ID INT(18) NOT NULL,
  USER_ID INT(18) DEFAULT NULL,
  IP VARCHAR(15) NOT NULL,
  URL VARCHAR(4000) DEFAULT NULL,
  ORIGIN_ID VARCHAR(30) DEFAULT NULL,
  ORIGINATOR_ID VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (ID),
  INDEX IX_B_CONSENT_USER_CONSENT (AGREEMENT_ID)
);

CREATE TABLE b_consent_agreement
(
  ID INT(18) NOT NULL AUTO_INCREMENT,
  CODE VARCHAR(45) DEFAULT NULL,
  DATE_INSERT DATETIME not null,
  ACTIVE CHAR(1) NOT NULL DEFAULT 'Y',
  NAME VARCHAR(255) NOT NULL,
  TYPE CHAR(1) DEFAULT NULL,
  LANGUAGE_ID CHAR(2) DEFAULT NULL,
  DATA_PROVIDER VARCHAR(45) DEFAULT NULL,
  AGREEMENT_TEXT LONGTEXT DEFAULT NULL,
  LABEL_TEXT VARCHAR(4000) DEFAULT NULL,
  SECURITY_CODE varchar(32) DEFAULT NULL,
  PRIMARY KEY (ID),
  INDEX IX_B_CONSENT_AGREEMENT_CODE (CODE)
);

CREATE TABLE b_consent_field
(
  ID INT(18) NOT NULL AUTO_INCREMENT,
  AGREEMENT_ID INT(18) NOT NULL,
  CODE VARCHAR(100) DEFAULT NULL,
  VALUE TEXT NOT NULL,
  PRIMARY KEY (ID),
  INDEX IX_B_CONSENT_FIELD_AG_ID (AGREEMENT_ID)
);

CREATE TABLE b_composite_page
(
	ID int(18) NOT NULL AUTO_INCREMENT,
	CACHE_KEY varchar(2000) NOT NULL,
	HOST varchar(100) NOT NULL,
	URI varchar(2000) NOT NULL,
	TITLE varchar(250) NULL,
	CREATED datetime NOT NULL,
	CHANGED datetime NOT NULL,
	LAST_VIEWED datetime NOT NULL,
	VIEWS int(18) NOT NULL DEFAULT 0,
	REWRITES int(18) NOT NULL DEFAULT 0,
	SIZE int(18) NOT NULL DEFAULT 0,
	PRIMARY KEY (ID),
	INDEX IX_B_COMPOSITE_PAGE_CACHE_KEY(CACHE_KEY(100)),
	INDEX IX_B_COMPOSITE_PAGE_VIEWED(LAST_VIEWED),
	INDEX IX_B_COMPOSITE_PAGE_HOST(HOST)

);

CREATE TABLE b_composite_log
(
	ID int(18) NOT NULL AUTO_INCREMENT,
	HOST varchar(100) NOT NULL,
	URI varchar(2000) NOT NULL,
	TITLE varchar(250) NULL,
	CREATED datetime NOT NULL,
	TYPE varchar(50) NOT NULL,
	MESSAGE longtext,
	AJAX char(1) NOT NULL DEFAULT 'N',
	USER_ID int(18) NOT NULL DEFAULT 0,
	PAGE_ID int(18) NOT NULL DEFAULT 0,
	PRIMARY KEY (ID),
	INDEX IX_B_COMPOSITE_LOG_PAGE_ID(PAGE_ID),
	INDEX IX_B_COMPOSITE_LOG_HOST(HOST),
	INDEX IX_B_COMPOSITE_LOG_TYPE(TYPE)
);

create table b_user_auth_action
(
	ID int NOT NULL AUTO_INCREMENT,
	USER_ID int NOT NULL,
	PRIORITY int NOT NULL DEFAULT 100,
	ACTION varchar(20),
	ACTION_DATE datetime NOT NULL,
	PRIMARY KEY (ID),
	index ix_auth_action_user(USER_ID, PRIORITY),
	index ix_auth_action_date(ACTION_DATE)
);
