create table if not exists b_landing
(
	ID int(18) not null auto_increment,
	CODE varchar(255) default null,
	INITIATOR_APP_CODE varchar(255) default null,
	RULE varchar(255) default null,
	ACTIVE char(1) not null default 'Y',
	DELETED char(1) not null default 'N',
	PUBLIC char(1) not null default 'Y',
	SYS char(1) not null default 'N',
	VIEWS int(18) not null default 0,
	TITLE varchar(255) not null,
	XML_ID varchar(255) default null,
	DESCRIPTION varchar(255) default null,
	TPL_ID int(18),
	TPL_CODE varchar(255) default null,
	SITE_ID int(18) not null,
	SITEMAP char(1) not null default 'N',
	FOLDER char(1) not null default 'N',
	FOLDER_ID int(18),
	SEARCH_CONTENT mediumtext default null,
	VERSION int(18) not null default 1,
    HISTORY_STEP int(18) not null default 0,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	DATE_PUBLIC timestamp null,
	PRIMARY KEY(ID),
	INDEX IX_B_LAND_CODE (CODE),
	INDEX IX_B_LAND_ACTIVE (ACTIVE),
	INDEX IX_B_LAND_DELETED (DELETED),
	INDEX IX_B_LAND_SYS (SYS),
	INDEX IX_B_LAND_XML_ID (XML_ID),
	INDEX IX_B_LAND_SITE_ID (SITE_ID),
	INDEX IX_B_LAND_SITEMAP (SITEMAP),
	INDEX IX_B_LAND_FOLDER (FOLDER),
	INDEX IX_B_LAND_FOLDER_ID (FOLDER_ID)
);

create table if not exists b_landing_block
(
	ID int(18) not null auto_increment,
	PARENT_ID int(18) default null,
	LID int(18) not null,
	CODE varchar(255) not null,
	TPL_CODE varchar(255) default null,
	XML_ID varchar(255) default null,
	INITIATOR_APP_CODE varchar(255) not null,
	ANCHOR varchar(255) null,
	SORT int(18) default 500,
	ACTIVE char(1) not null default 'Y',
	PUBLIC char(1) not null default 'Y',
	DELETED char(1) not null default 'N',
	DESIGNED char(1) not null default 'N',
	ACCESS char(1) not null default 'X',
	SOURCE_PARAMS mediumtext default null,
	CONTENT mediumtext not null,
	SEARCH_CONTENT mediumtext default null,
	ASSETS text default null,
	FAVORITE_META text default null,
	HISTORY_STEP_DESIGNER int(18) not null default 0,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_BLOCK_LID (LID),
	INDEX IX_B_BLOCK_LID_PUBLIC (LID, PUBLIC),
	INDEX IX_B_BLOCK_CODE (CODE),
	INDEX IX_B_BLOCK_ACTIVE (ACTIVE),
	INDEX IX_B_BLOCK_PUBLIC (PUBLIC, DATE_CREATE),
	INDEX IX_B_BLOCK_DELETED (DELETED)
);

create table if not exists b_landing_site
(
	ID int(18) not null auto_increment,
	CODE varchar(255) not null,
	ACTIVE char(1) not null default 'Y',
	DELETED char(1) not null default 'N',
	TITLE varchar(255) not null,
	XML_ID varchar(255) default null,
	DESCRIPTION varchar(255) default null,
	TYPE varchar(50) not null default 'PAGE',
	TPL_ID int(18),
	TPL_CODE varchar(255) default null,
	DOMAIN_ID int(18) not null,
	SMN_SITE_ID char(2) default null,
	LANDING_ID_INDEX int(18) default null,
	LANDING_ID_404 int(18) default null,
	LANDING_ID_503 int(18) default null,
	LANG char(2) default null,
	SPECIAL char(1) not null default 'N',
	VERSION int(18) default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_SITE_CODE (CODE),
	INDEX IX_B_SITE_ACTIVE (ACTIVE),
	INDEX IX_B_SITE_DELETED (DELETED),
	INDEX IX_B_SITE_XML_ID (XML_ID),
	INDEX IX_B_SITE_SPECIAL (SPECIAL)
);

create table if not exists b_landing_domain
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	DOMAIN varchar(255) not null,
	PREV_DOMAIN varchar(255) default null,
	XML_ID varchar(255) default null,
	PROTOCOL varchar(10) not null,
	PROVIDER varchar(50) default null,
	FAIL_COUNT int(2) default null,CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_DOMAIN_ACTIVE (ACTIVE),
	INDEX IX_B_DOMAIN_DOMAIN (DOMAIN),
	INDEX IX_B_DOMAIN_PROVIDER (PROVIDER),
	INDEX IX_B_DOMAIN_XML_ID (XML_ID)
);

create table if not exists b_landing_template
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	TITLE varchar(255) not null,
	SORT int(18) default 100,
	XML_ID varchar(255) default null,
	CONTENT text not null,
	AREA_COUNT int(2) not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID)
);

create table if not exists b_landing_template_ref
(
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	AREA int(2) not null,
	LANDING_ID int(18) not null,
	PRIMARY KEY(ID),
	INDEX K_LANDING_ID (LANDING_ID),
	INDEX K_ENTITY (ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_landing_repo
(
	ID int(18) not null auto_increment,
	XML_ID varchar(255) not null,
	APP_CODE varchar(255) default null,
	ACTIVE char(1) not null default 'Y',
	NAME varchar(255) not null,
	DESCRIPTION varchar(255) default null,
	SECTIONS varchar(255) default null,
	SITE_TEMPLATE_ID varchar(255) default null,
	PREVIEW varchar(255) default null,
	MANIFEST text default null,
	CONTENT text not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_REPO_ACTIVE (ACTIVE),
	INDEX IX_B_REPO_XML_ID (XML_ID),
	INDEX IX_B_REPO_APP_CODE (APP_CODE),
	INDEX IX_B_REPO_TEMPLATE_ID (SITE_TEMPLATE_ID)
);

create table if not exists b_landing_hook_data
(
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	HOOK varchar(50) not null,
	CODE varchar(50) not null,
	VALUE text default null,
	PUBLIC char(1) not null default 'N',
	PRIMARY KEY(ID),
	INDEX K_ENTITY (ENTITY_ID, ENTITY_TYPE),
	INDEX K_HOOK_CODE (HOOK,CODE)
);

create table if not exists b_landing_file
(
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	FILE_ID int(18) not null,
	TEMP char(1) not null default 'N',
	PRIMARY KEY(ID),
	INDEX IX_ENTITY (ENTITY_ID, ENTITY_TYPE),
	INDEX IX_FILE (FILE_ID),
	INDEX IX_TEMP (TEMP)
);

create table if not exists b_landing_syspage
(
	ID int(18) not null auto_increment,
	SITE_ID int(18) not null,
	TYPE varchar(50) not null,
	LANDING_ID int(18) not null,
	PRIMARY KEY(ID),
	INDEX IX_SITE_ID (SITE_ID),
	INDEX IX_LANDING_ID (LANDING_ID)
);

create table if not exists b_landing_demo
(
	ID int(18) not null auto_increment,
	XML_ID varchar(255) not null,
	APP_CODE varchar(255) default null,
	ACTIVE char(1) not null  default 'Y',
	TYPE varchar(10) not null,
	TPL_TYPE char(1) not null,
	SHOW_IN_LIST char(1) not null  default 'N',
	TITLE varchar(255) not null,
	DESCRIPTION varchar(255) default null,
	PREVIEW_URL varchar(255) default null,
	PREVIEW varchar(255) default null,
	PREVIEW2X varchar(255) default null,
	PREVIEW3X varchar(255) default null,
	MANIFEST mediumtext default null,
	LANG text default null,
	SITE_TEMPLATE_ID varchar(255) default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_DEMO_ACTIVE (ACTIVE),
	INDEX IX_B_DEMO_SHOW_IN_LIST (SHOW_IN_LIST),
	INDEX IX_B_DEMO_XML_ID (XML_ID),
	INDEX IX_B_DEMO_APP_CODE (APP_CODE),
	INDEX IX_B_DEMO_TEMPLATE_ID (SITE_TEMPLATE_ID)
);

create table if not exists b_landing_placement
(
	ID int(18) not null auto_increment,
	APP_ID int(18) null,
	PLACEMENT varchar(255) not null,
	PLACEMENT_HANDLER varchar(255) not null,
	TITLE varchar(255) null default '',
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY (ID)
);

create table if not exists b_landing_update_block
(
	ID int(18) not null auto_increment,
	CODE varchar(255) not null,
	LAST_BLOCK_ID int(18) default 0,
	PARAMS text default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_CODE (CODE)
);

create table if not exists b_landing_urlrewrite
(
	ID int(18) not null auto_increment,
	SITE_ID int(18) not null,
	RULE varchar(255) not null,
	LANDING_ID int(18) not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_SITE_RULE (SITE_ID, RULE),
	INDEX IX_LANDING_ID (LANDING_ID)
);

create table if not exists b_landing_entity_rights (
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	TASK_ID int(11) not null,
	ACCESS_CODE varchar(50) not null,
	ROLE_ID int(18) default 0,
	PRIMARY KEY (ID),
	INDEX IX_ENTITY (ENTITY_ID, ENTITY_TYPE),
	INDEX IX_ROLE (ROLE_ID)
);

create table if not exists b_landing_role (
	ID int(18) not null auto_increment,
	TITLE varchar(255) default null,
	XML_ID varchar(255) default null,
	TYPE varchar(255) default null,
	ACCESS_CODES text default null,
	ADDITIONAL_RIGHTS text default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null default CURRENT_TIMESTAMP,
	PRIMARY KEY(ID),
	INDEX IX_B_ROLE_TYPE (TYPE)
);

create table if not exists b_landing_filter_entity (
	ID int(18) not null auto_increment,
	SOURCE_ID varchar(255) not null,
	FILTER_HASH char(32) not null,
	FILTER text default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	UNIQUE IX_B_FILTER_HASH (FILTER_HASH)
);

create table if not exists b_landing_filter_block (
	ID int(18) not null auto_increment,
	FILTER_ID int(18) not null,
	BLOCK_ID int(18) not null,
	PRIMARY KEY(ID),
	UNIQUE IX_B_FILTER_BLOCK (FILTER_ID, BLOCK_ID)
);

create table if not exists b_landing_view (
	ID int(18) not null auto_increment,
	LID int(18) not null,
	USER_ID int(18) not null,
	VIEWS int(18) not null,
	FIRST_VIEW datetime not null,
	LAST_VIEW datetime not null,
	PRIMARY KEY(ID),
	INDEX IX_B_VIEW_LIDUID (LID, USER_ID)
);

create table if not exists b_landing_binding
(
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	BINDING_ID varchar(50) not null,
	BINDING_TYPE char(1) not null,
	PRIMARY KEY(ID),
	INDEX IX_B_BINDING (BINDING_ID, BINDING_TYPE),
	INDEX IX_B_ENTITY (ENTITY_ID, ENTITY_TYPE),
	INDEX IX_B_BINDING_TYPE (BINDING_TYPE)
);

create table if not exists b_landing_chat
(
	ID int(18) not null auto_increment,
	CHAT_ID int(18) not null,
	TITLE varchar(255) not null,
	AVATAR int(18) default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_CHAT (CHAT_ID)
);

create table if not exists b_landing_chat_binding
(
	ID int(18) not null auto_increment,
	INTERNAL_CHAT_ID int(18) not null,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	PRIMARY KEY(ID),
	INDEX IX_B_CHAT (INTERNAL_CHAT_ID),
	INDEX IX_B_ENTITY (ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_landing_cookies_agreement
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	SITE_ID int(18) not null,
	CODE varchar(50) not null,
	TITLE varchar(255) default null,
	CONTENT mediumtext not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_SITE (SITE_ID, CODE)
);

create table if not exists b_landing_designer_repo
(
	ID int(18) not null auto_increment,
	XML_ID varchar(255) not null,
	TITLE varchar(255) default null,
	SORT int(18) default 100,
	HTML text not null,
	MANIFEST text not null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_XML_ID (XML_ID)
);

create table if not exists b_landing_entity_lock (
	ID int(18) not null auto_increment,
	ENTITY_ID int(18) not null,
	ENTITY_TYPE char(1) not null,
	LOCK_TYPE char(1) not null,
	PRIMARY KEY (ID),
	INDEX IX_ENTITY (ENTITY_ID, ENTITY_TYPE),
	INDEX IX_TYPE (LOCK_TYPE)
);

create table if not exists b_landing_folder
(
	ID int(18) not null auto_increment,
	PARENT_ID int(18) default null,
	SITE_ID int(18) not null,
	INDEX_ID int(18) default null,
	ACTIVE char(1) not null default 'N',
	DELETED char(1) not null default 'N',
	TITLE varchar(255) not null,
	CODE varchar(255) default null,
	CREATED_BY_ID int(18) not null,
	MODIFIED_BY_ID int(18) not null,
	DATE_CREATE timestamp null,
	DATE_MODIFY timestamp not null,
	PRIMARY KEY(ID),
	INDEX IX_B_FOLDER_SITE_ID (SITE_ID),
	INDEX IX_B_FOLDER_ACTIVE (ACTIVE),
	INDEX IX_B_FOLDER_DELETED (DELETED),
	INDEX IX_B_FOLDER_PARENT_ID (PARENT_ID)
);

create table if not exists b_landing_urlchecker_whitelist
(
	ID int(18) not null auto_increment,
	DOMAIN varchar(255) not null,
	DATE_MODIFY timestamp not null default CURRENT_TIMESTAMP,
	PRIMARY KEY(ID),
	INDEX IX_B_CHECKER_DOMAIN (DOMAIN)
);

create table if not exists b_landing_urlchecker_status
(
	ID int(18) not null auto_increment,
	URL varchar(255) not null,
	HASH char(32) not null,
	STATUS varchar(255) default null,
	DATE_MODIFY timestamp not null default CURRENT_TIMESTAMP,
	PRIMARY KEY(ID),
	INDEX IX_B_CHECKER_HASH (HASH)
);

create table if not exists b_landing_urlchecker_host
(
	ID int(18) not null auto_increment,
	STATUS_ID int(18) not null,
	HOST varchar(255) not null,
	DATE_MODIFY timestamp not null default CURRENT_TIMESTAMP,
	PRIMARY KEY(ID),
	INDEX IX_B_CHECKER_STATUS_HOST (STATUS_ID, HOST)
);

create table if not exists b_landing_block_last_used
(
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	CODE varchar(255) not null,
	DATE_CREATE timestamp not null default CURRENT_TIMESTAMP,
	PRIMARY KEY(ID),
	INDEX IX_B_BLOCK_LU_USER (USER_ID),
	INDEX IX_B_BLOCK_LU_CODE (CODE),
	INDEX IX_B_BLOCK_LU_USER_CODE (USER_ID, CODE)
);

create table if not exists b_landing_history
(
    ID int(18) not null auto_increment,
    ENTITY_TYPE char(1) not null default 'L',
    ENTITY_ID int(18) not null,
    `ACTION` text not null,
    ACTION_PARAMS text not null,
    MULTIPLY_ID int(18),
    CREATED_BY_ID int(18) not null,
    DATE_CREATE timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_LAND_HISTORY_ENTITY (ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_landing_history_step
(
    ID int(18) not null auto_increment,
    ENTITY_TYPE char(1) not null default 'L',
    ENTITY_ID int(18) not null,
    STEP int(18) not null,
    PRIMARY KEY(ID),
    INDEX IX_HISTORY_STEP (ENTITY_ID, ENTITY_TYPE)
);
