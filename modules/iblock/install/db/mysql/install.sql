create table if not exists b_iblock_type
(
	ID VARCHAR(50) not null,
	SECTIONS CHAR(1) not null DEFAULT 'Y',
	EDIT_FILE_BEFORE varchar(255),
	EDIT_FILE_AFTER varchar(255),
	IN_RSS char(1) not null default 'N',
	SORT INT(18) NOT NULL DEFAULT 500,
	primary key (ID)
);

create table if not exists b_iblock_type_lang
(
	IBLOCK_TYPE_ID VARCHAR(50) not null,
	LID CHAR(2) not null,
	NAME VARCHAR(100) not null,
	SECTION_NAME VARCHAR(100),
	ELEMENT_NAME VARCHAR(100)
);

create table if not exists b_iblock
(
	ID int(11) not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	IBLOCK_TYPE_ID varchar(50) not null REFERENCES b_iblock_type(ID),
	LID char(2) not null REFERENCES b_lang(LID),
	CODE varchar(50) null,
	API_CODE varchar(50) null,
	REST_ON char(1) not null default 'N',
	NAME varchar(255) not null,
	ACTIVE char(1) not null DEFAULT 'Y',
	SORT int(11) not null DEFAULT 500,
	LIST_PAGE_URL varchar(255) null,
	DETAIL_PAGE_URL varchar(255) null,
	SECTION_PAGE_URL varchar(255) null,
	CANONICAL_PAGE_URL varchar(255) null,
	PICTURE int(18) null,
	DESCRIPTION text null,
	DESCRIPTION_TYPE char(4) not null DEFAULT 'text',
	RSS_TTL int(11) not null default '24',
	RSS_ACTIVE char(1) not null default 'Y',
	RSS_FILE_ACTIVE char(1) not null default 'N',
	RSS_FILE_LIMIT int null,
	RSS_FILE_DAYS int null,
	RSS_YANDEX_ACTIVE char(1) not null default 'N',
	XML_ID varchar(255) null,
	TMP_ID varchar(40) null,
	INDEX_ELEMENT char(1) not null default 'Y',
	INDEX_SECTION char(1) not null default 'N',
	WORKFLOW char(1) not null default 'Y',
	BIZPROC char(1) not null default 'N',
	SECTION_CHOOSER char(1) null,
	LIST_MODE char(1) null,
	RIGHTS_MODE char(1) null,
	SECTION_PROPERTY char(1) null,
	PROPERTY_INDEX char(1) null,
	VERSION int not null default 1,
	LAST_CONV_ELEMENT int(11) not null default 0,
	SOCNET_GROUP_ID int(18) NULL,
	EDIT_FILE_BEFORE varchar(255) null,
	EDIT_FILE_AFTER varchar(255) null,
	SECTIONS_NAME varchar(100) null,
	SECTION_NAME varchar(100) null,
	ELEMENTS_NAME varchar(100) null,
	ELEMENT_NAME varchar(100) null,
	PRIMARY KEY(ID),
	INDEX ix_iblock (IBLOCK_TYPE_ID, LID, ACTIVE),
	UNIQUE INDEX ix_iblock_api_code (API_CODE)
);

create table if not exists b_iblock_site
(
	IBLOCK_ID INT(18) NOT NULL,
	SITE_ID CHAR(2) NOT NULL,
	PRIMARY KEY PK_B_IBLOCK_SITE(IBLOCK_ID, SITE_ID)
);

create table if not exists b_iblock_messages
(
	IBLOCK_ID INT(18) NOT NULL,
	MESSAGE_ID varchar(50) NOT NULL,
	MESSAGE_TEXT varchar(255) NULL,
	PRIMARY KEY PK_B_IBLOCK_MESSAGES(IBLOCK_ID, MESSAGE_ID)
);

create table if not exists b_iblock_fields
(
	IBLOCK_ID int(18) NOT NULL,
	FIELD_ID varchar(50) NOT NULL,
	IS_REQUIRED char(1),
	DEFAULT_VALUE longtext,
	PRIMARY KEY PK_B_IBLOCK_FIELDS(IBLOCK_ID, FIELD_ID)
);

create table if not exists b_iblock_property
(
	ID int(11) not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	NAME varchar(255) not null,
	ACTIVE char(1) not null default 'Y',
	SORT int(11) not null default 500,
	CODE varchar(50),
	DEFAULT_VALUE text,
	PROPERTY_TYPE char(1) not null default 'S',
	ROW_COUNT int(11) not null default 1,
	COL_COUNT int(11) not null default 30,
	LIST_TYPE char(1) not null default 'L',
	MULTIPLE char(1) not null default 'N',
	XML_ID varchar(100),
	FILE_TYPE varchar(200),
	MULTIPLE_CNT int(11),
	TMP_ID varchar(40),
	LINK_IBLOCK_ID INT(18),
	WITH_DESCRIPTION CHAR(1),
	SEARCHABLE char(1) not null default 'N',
	FILTRABLE char(1) not null default 'N',
	IS_REQUIRED CHAR(1),
	VERSION int not null default 1,
	USER_TYPE varchar(255) null,
	USER_TYPE_SETTINGS text,
	HINT varchar(255),
	PRIMARY KEY (ID),
	INDEX ix_iblock_property_1(IBLOCK_ID),
	INDEX ix_iblock_property_3(LINK_IBLOCK_ID),
	index ix_iblock_property_2(CODE)
);

create table if not exists b_iblock_property_feature
(
	ID int not null auto_increment,
	PROPERTY_ID int not null,
	MODULE_ID varchar(50) not null,
	FEATURE_ID varchar(100) not null,
	IS_ENABLED char(1) not null default 'N',
	PRIMARY KEY (ID),
	unique index ix_iblock_property_feature (PROPERTY_ID, MODULE_ID, FEATURE_ID)
);

create table if not exists b_iblock_section
(
	ID int(11) not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	MODIFIED_BY int(18),
	DATE_CREATE datetime,
	CREATED_BY int(18),
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	IBLOCK_SECTION_ID int(11) REFERENCES b_iblock_section(ID),
	ACTIVE char(1) not null DEFAULT 'Y',
	GLOBAL_ACTIVE char(1) not null DEFAULT 'Y',
	SORT int(11) not null DEFAULT 500,
	NAME varchar(255) not null,
	PICTURE int(18),
	LEFT_MARGIN int(18),
	RIGHT_MARGIN int(18),
	DEPTH_LEVEL int(18),
	DESCRIPTION text,
	DESCRIPTION_TYPE char(4) not null DEFAULT 'text',
	SEARCHABLE_CONTENT text,
	CODE varchar(255),
	XML_ID varchar(255),
	TMP_ID varchar(40),
	DETAIL_PICTURE int(18) NULL,
	SOCNET_GROUP_ID int(18) NULL,
	PRIMARY KEY (ID),
	INDEX ix_iblock_section_1 (IBLOCK_ID, IBLOCK_SECTION_ID),
	INDEX ix_iblock_section_depth_level (IBLOCK_ID, DEPTH_LEVEL),
	INDEX ix_iblock_section_code (IBLOCK_ID, CODE),
	INDEX ix_iblock_section_left_margin2 (IBLOCK_ID, LEFT_MARGIN),
	INDEX ix_iblock_section_right_margin2 (IBLOCK_ID, RIGHT_MARGIN)
);

create table if not exists b_iblock_section_property
(
	IBLOCK_ID int(11) not null,
	SECTION_ID int(11) not null,
	PROPERTY_ID int(11) not null,
	SMART_FILTER char(1),
	DISPLAY_TYPE char(1),
	DISPLAY_EXPANDED char(1),
	FILTER_HINT varchar(255),
	PRIMARY KEY pk_b_iblock_section_property (IBLOCK_ID, SECTION_ID, PROPERTY_ID),
	INDEX ix_b_iblock_section_property_1 (PROPERTY_ID),
	INDEX ix_b_iblock_section_property_2 (SECTION_ID)
);

create table if not exists b_iblock_element
(
	ID int(11) not null auto_increment,
	TIMESTAMP_X datetime,
	MODIFIED_BY int(18),
	DATE_CREATE datetime,
	CREATED_BY int(18),
	IBLOCK_ID int(11) not null default '0',
	IBLOCK_SECTION_ID int(11),
	ACTIVE char(1) not null default 'Y',
	ACTIVE_FROM datetime,
	ACTIVE_TO datetime,
	SORT int(11) not null default '500',
	NAME varchar(255) not null,
	PREVIEW_PICTURE int(18),
	PREVIEW_TEXT text,
	PREVIEW_TEXT_TYPE varchar(4) not null default 'text',
	DETAIL_PICTURE int(18),
	DETAIL_TEXT longtext,
	DETAIL_TEXT_TYPE varchar(4) not null default 'text',
	SEARCHABLE_CONTENT text,
	WF_STATUS_ID int(18) default '1',
	WF_PARENT_ELEMENT_ID int(11),
	WF_NEW char(1),
	WF_LOCKED_BY int(18),
	WF_DATE_LOCK datetime,
	WF_COMMENTS text,
	IN_SECTIONS char(1) not null default 'N',
	XML_ID varchar(255),
	CODE varchar(255),
	TAGS varchar(255),
	TMP_ID varchar(40),
	WF_LAST_HISTORY_ID int(11),
	SHOW_COUNTER INT(18) NULL,
	SHOW_COUNTER_START DATETIME NULL,
	primary key (ID),
	index ix_iblock_element_1 (IBLOCK_ID, IBLOCK_SECTION_ID),
	index ix_iblock_element_4 (IBLOCK_ID, XML_ID, WF_PARENT_ELEMENT_ID),
	index ix_iblock_element_3 (WF_PARENT_ELEMENT_ID),
	index ix_iblock_element_code (IBLOCK_ID, CODE)
);

create table if not exists b_iblock_element_property
(
	ID int(11) not null  auto_increment,
	IBLOCK_PROPERTY_ID int(11) not null REFERENCES b_iblock_property(ID),
	IBLOCK_ELEMENT_ID int(11) not null REFERENCES b_iblock_element(ID),
	VALUE text not null,
	VALUE_TYPE char(4) not null DEFAULT 'text',
	VALUE_ENUM int(11),
	VALUE_NUM numeric(18,4),
	DESCRIPTION VARCHAR(255) NULL,
	PRIMARY KEY (ID),
	INDEX ix_iblock_element_property_1(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID),
	INDEX ix_iblock_element_property_2(IBLOCK_PROPERTY_ID),
	INDEX ix_iblock_element_prop_enum (VALUE_ENUM,IBLOCK_PROPERTY_ID),
	INDEX ix_iblock_element_prop_num (VALUE_NUM,IBLOCK_PROPERTY_ID),
	INDEX ix_iblock_element_prop_val(VALUE(50), IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID)
);

create table if not exists b_iblock_property_enum
(
	ID int not null auto_increment,
	PROPERTY_ID int not null,
	VALUE varchar(255) not null,
	DEF char(1) not null default 'N',
	SORT int not null default '500',
	XML_ID varchar(200) not null,
	TMP_ID varchar(40),
	primary key (ID),
	unique ux_iblock_property_enum(PROPERTY_ID, XML_ID)
);

create table if not exists b_iblock_group
(
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	GROUP_ID int(11) not null REFERENCES b_group(ID),
	PERMISSION char(1) not null,
	UNIQUE ux_iblock_group_1(IBLOCK_ID, GROUP_ID)
);

create table if not exists b_iblock_right
(
	ID int(11) not null auto_increment,
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	GROUP_CODE varchar(50) not null,
	ENTITY_TYPE varchar(32) not null,
	ENTITY_ID int(11) not null,
	DO_INHERIT char(1) not null,
	TASK_ID int(11) not null REFERENCES b_task(ID),
	OP_SREAD char(1) not null,
	OP_EREAD char(1) not null,
	XML_ID varchar(32),
	primary key (ID),
	KEY ix_b_iblock_right_iblock_id(IBLOCK_ID, ENTITY_TYPE, ENTITY_ID),
	KEY ix_b_iblock_right_group_code(GROUP_CODE, IBLOCK_ID),
	KEY ix_b_iblock_right_entity(ENTITY_ID, ENTITY_TYPE),
	KEY ix_b_iblock_right_op_eread(ID, OP_EREAD, GROUP_CODE),
	KEY ix_b_iblock_right_op_sread(ID, OP_SREAD, GROUP_CODE),
	KEY ix_b_iblock_right_task_id(TASK_ID)
);

create table if not exists b_iblock_section_right
(
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	SECTION_ID int(11) not null,
	RIGHT_ID int(11) not null REFERENCES b_iblock_right(ID),
	IS_INHERITED char(1) not null,
	primary key (RIGHT_ID, SECTION_ID),
	KEY ix_b_iblock_section_right_1(SECTION_ID, IBLOCK_ID),
	KEY ix_b_iblock_section_right_2(IBLOCK_ID, RIGHT_ID)
);

create table if not exists b_iblock_element_right
(
	IBLOCK_ID int(11) not null REFERENCES b_iblock(ID),
	SECTION_ID int(11) not null,
	ELEMENT_ID int(11) not null,
	RIGHT_ID int(11) not null REFERENCES b_iblock_right(ID),
	IS_INHERITED char(1) not null,
	primary key (RIGHT_ID, ELEMENT_ID, SECTION_ID),
	KEY ix_b_iblock_element_right_1(ELEMENT_ID, IBLOCK_ID),
	KEY ix_b_iblock_element_right_2(IBLOCK_ID, RIGHT_ID)
);

create table if not exists b_iblock_section_element
(
	IBLOCK_SECTION_ID int not null,
	IBLOCK_ELEMENT_ID int not null,
	ADDITIONAL_PROPERTY_ID INT(18) NULL,
	unique ux_iblock_section_element(IBLOCK_SECTION_ID, IBLOCK_ELEMENT_ID, ADDITIONAL_PROPERTY_ID),
	index UX_IBLOCK_SECTION_ELEMENT2(IBLOCK_ELEMENT_ID)
);

create table if not exists b_iblock_rss
(
	ID int not null auto_increment,
	IBLOCK_ID int not null,
	NODE varchar(50) not null,
	NODE_VALUE varchar(250) null,
	primary key (ID)
);

create table if not exists b_iblock_cache
(
	CACHE_KEY varchar(35) not null,
	CACHE longtext not null,
	CACHE_DATE datetime not null,
	primary key (CACHE_KEY)
);

create table if not exists b_iblock_element_lock
(
	IBLOCK_ELEMENT_ID int(11) not null REFERENCES b_iblock_element(ID),
	DATE_LOCK datetime,
	LOCKED_BY varchar(32),
	primary key PK_B_IBLOCK_ELEMENT_LOCK (IBLOCK_ELEMENT_ID)
);

create table if not exists b_iblock_sequence
(
	IBLOCK_ID INT(18) NOT NULL,
	CODE varchar(50) NOT NULL,
	SEQ_VALUE int,
	primary key pk_b_iblock_sequence(IBLOCK_ID, CODE)
);

create table if not exists b_iblock_offers_tmp
(
	ID int(11) unsigned not null auto_increment,
	PRODUCT_IBLOCK_ID int(11) unsigned not null,
	OFFERS_IBLOCK_ID int(11) unsigned not null,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	PRIMARY KEY (ID)
);

create table if not exists b_iblock_iproperty
(
	ID int(11) not null auto_increment,
	IBLOCK_ID int(11) not null,
	CODE varchar(50) not null,
	ENTITY_TYPE char(1) not null,
	ENTITY_ID int(11) not null,
	TEMPLATE text not null,
	primary key pk_b_iblock_iproperty (ID),
	KEY ix_b_iblock_iprop_0(IBLOCK_ID, ENTITY_TYPE, ENTITY_ID)
);

create table if not exists b_iblock_iblock_iprop
(
	IBLOCK_ID int(11) not null,
	IPROP_ID int(11) not null,
	VALUE text not null,
	primary key pk_b_iblock_iblock_iprop (IBLOCK_ID, IPROP_ID),
	KEY ix_b_iblock_iblock_iprop_0(IPROP_ID)
);

create table if not exists b_iblock_section_iprop
(
	IBLOCK_ID int(11) not null,
	SECTION_ID int(11) not null,
	IPROP_ID int(11) not null,
	VALUE text not null,
	primary key pk_b_iblock_section_iprop (SECTION_ID, IPROP_ID),
	KEY ix_b_iblock_section_iprop_0(IPROP_ID),
	KEY ix_b_iblock_section_iprop_1(IBLOCK_ID)
);

create table if not exists b_iblock_element_iprop
(
	IBLOCK_ID int(11) not null,
	SECTION_ID int(11) not null,
	ELEMENT_ID int(11) not null,
	IPROP_ID int(11) not null,
	VALUE text not null,
	primary key pk_b_iblock_element_iprop (ELEMENT_ID, IPROP_ID),
	KEY ix_b_iblock_element_iprop_0(IPROP_ID),
	KEY ix_b_iblock_element_iprop_1(IBLOCK_ID)
);