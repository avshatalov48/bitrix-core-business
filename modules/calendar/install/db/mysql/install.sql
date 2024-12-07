create table b_calendar_type
(
	XML_ID varchar(255) not null,
	NAME  varchar(255) null,
	DESCRIPTION text null,
	EXTERNAL_ID varchar(100) null,
	ACTIVE char(1) not null default 'Y',
	primary key (XML_ID)
);

create table b_calendar_section
(
	ID int not null auto_increment,
	NAME varchar(255) null,
	XML_ID varchar(100) null,
	EXTERNAL_ID varchar(100) null,
	GAPI_CALENDAR_ID varchar(255) null,
	ACTIVE char(1) not null default 'Y',
	DESCRIPTION text null,
	COLOR varchar(10) null,
	TEXT_COLOR varchar(10) null,
	EXPORT varchar(255) null,
	SORT int not null default 100,
	CAL_TYPE varchar(100) null,
	OWNER_ID int null,
	CREATED_BY int not null,
	PARENT_ID int null,
	DATE_CREATE  datetime null,
	TIMESTAMP_X  datetime null,
	DAV_EXCH_CAL varchar(255) null,
	DAV_EXCH_MOD varchar(255) null,
	CAL_DAV_CON varchar(255) null,
	CAL_DAV_CAL varchar(255) null,
	CAL_DAV_MOD varchar(255) null,
	IS_EXCHANGE char(1) null,
	SYNC_TOKEN varchar(255) null,
	PAGE_TOKEN varchar(255) null,
	EXTERNAL_TYPE varchar(20) null,
	primary key (ID),
	INDEX ix_cal_sect_owner (CAL_TYPE, OWNER_ID),
	INDEX ix_cal_section_page_token (PAGE_TOKEN)
);

create table b_calendar_event
(
  ID int not null auto_increment,
  PARENT_ID int null,
  ACTIVE char(1) not null default 'Y',
  DELETED char(1) not null default 'N',
  CAL_TYPE varchar(100) null,
  OWNER_ID int not null,
  NAME varchar(255) null,
  DATE_FROM datetime null,
  DATE_TO datetime null,
  ORIGINAL_DATE_FROM datetime null,
  TZ_FROM varchar(50) null,
  TZ_TO varchar(50) null,
  TZ_OFFSET_FROM int null,
  TZ_OFFSET_TO int null,
  DATE_FROM_TS_UTC int(18) null,
  DATE_TO_TS_UTC int(18) null,
  DT_SKIP_TIME char(1) null,
  DT_LENGTH bigint null,
  EVENT_TYPE varchar(50) null,
  CREATED_BY int not null,
  DATE_CREATE  datetime null,
  TIMESTAMP_X  datetime null,
  DESCRIPTION text null,
  DT_FROM datetime null, /* deprecated */
  DT_TO datetime null, /* deprecated */
  PRIVATE_EVENT varchar(10) null,
  ACCESSIBILITY varchar(10) null,
  IMPORTANCE varchar(10) null,
  IS_MEETING char(1) null,
  MEETING_STATUS char(1) null, /* H - host, Y-yes, N-no, Q-not answered, M-maybe */
  MEETING_HOST int null,
  MEETING text null,
  LOCATION varchar(255) null,
  REMIND text null,
  COLOR varchar(10) null,
  TEXT_COLOR varchar(10) null,
  RRULE varchar(255) null,
  EXDATE text null,
  DAV_XML_ID varchar(255) null,
  G_EVENT_ID varchar(255) null,
  DAV_EXCH_LABEL varchar(255) null,
  CAL_DAV_LABEL varchar(255) null,
  VERSION varchar(255) null,
  ATTENDEES_CODES text null,
  RECURRENCE_ID int null,
  RELATIONS varchar(255) null,
  SEARCHABLE_CONTENT text null,
  SECTION_ID int null,
  SYNC_STATUS varchar(20) null,
  primary key (ID),
  INDEX ix_cal_event_date_from_utc (DATE_FROM_TS_UTC),
  INDEX ix_cal_event_date_to_utc (DATE_TO_TS_UTC),
  INDEX ix_cal_event_owner_id_date (OWNER_ID, DATE_FROM_TS_UTC, DATE_TO_TS_UTC),
  INDEX ix_cal_event_parent_id (PARENT_ID),
  INDEX ix_cal_event_created_by_access_date_to (CREATED_BY, ACCESSIBILITY, DATE_TO_TS_UTC),
  INDEX ix_cal_event_owner_id_accessibility (ACCESSIBILITY, DATE_FROM_TS_UTC, DATE_TO_TS_UTC),
  INDEX ix_cal_event_recurrence_id (RECURRENCE_ID),
  INDEX ix_cal_google_event_id (G_EVENT_ID),
  INDEX ix_cal_dav_xml_id (DAV_XML_ID),
  INDEX ix_cal_owner_del_date (OWNER_ID, DELETED, DATE_TO_TS_UTC, DATE_FROM_TS_UTC),
  INDEX ix_cal_type_del_date (CAL_TYPE, DELETED, DATE_TO_TS_UTC, DATE_FROM_TS_UTC),
  INDEX ix_event_location (LOCATION),
  INDEX ix_event_section_del (SECTION_ID,DELETED),
  INDEX ix_cal_google_sync_status (SYNC_STATUS),
  INDEX ix_cal_event_section_del_date (SECTION_ID, DELETED, DATE_TO_TS_UTC, DATE_FROM_TS_UTC),
  FULLTEXT INDEX IXF_B_CALENDAR_EVENT_SEARCHABLE_CONTENT (SEARCHABLE_CONTENT)
);

create table b_calendar_event_sect
(
	EVENT_ID int not null,
	SECT_ID int not null,
	REL  char(10) null,
	primary key (EVENT_ID, SECT_ID),
	INDEX ix_cal_event_sect (SECT_ID, EVENT_ID)
);

CREATE TABLE b_calendar_push (
  ENTITY_TYPE varchar(24) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  CHANNEL_ID varchar(128) NOT NULL,
  RESOURCE_ID varchar(128) NOT NULL,
  EXPIRES datetime NOT NULL,
  NOT_PROCESSED varchar(1) NOT NULL DEFAULT 'N',
  FIRST_PUSH_DATE datetime DEFAULT NULL,
  PRIMARY KEY (ENTITY_TYPE,ENTITY_ID),
  INDEX ix_cal_google_push_expires (EXPIRES)
);

create table b_calendar_access
(
	ACCESS_CODE varchar(100) not null,
	TASK_ID int not null,
	SECT_ID varchar(100) not null,
	PRIMARY KEY (ACCESS_CODE, TASK_ID, SECT_ID),
	INDEX ix_access_sect_id (SECT_ID)
);

create table b_calendar_resource
(
  ID int not null auto_increment,
  EVENT_ID int null,
  CAL_TYPE varchar(100) null,
  RESOURCE_ID int not null,
  PARENT_TYPE varchar(100) null,
  PARENT_ID int not null,
  UF_ID int null,
  DATE_FROM_UTC datetime null,
  DATE_TO_UTC datetime null,
  DATE_FROM datetime null,
  DATE_TO datetime null,
  DURATION bigint null,
  SKIP_TIME char(1) null,
  TZ_FROM varchar(50) null,
  TZ_TO varchar(50) null,
  TZ_OFFSET_FROM int null,
  TZ_OFFSET_TO int null,
  CREATED_BY int not null,
  DATE_CREATE  datetime null,
  TIMESTAMP_X  datetime null,
  SERVICE_NAME varchar(200) null,
  primary key (ID),
  INDEX ix_ufid_parenttype_parentid (UF_ID, PARENT_TYPE, PARENT_ID)
);

create table b_calendar_location
(
  ID int not null auto_increment,
  SECTION_ID int not null,
  NECESSITY char(1) default 'N',
  CAPACITY int default 0,
  CATEGORY_ID int default null,
  PRIMARY KEY(ID),
  INDEX ix_location_section(SECTION_ID)
);

create table b_calendar_log
(
  ID int not null auto_increment,
  TIMESTAMP_X TIMESTAMP NOT NULL DEFAULT current_timestamp,
  MESSAGE MEDIUMTEXT NULL,
  TYPE varchar(50) default null,
  UUID varchar(255) default null,
  USER_ID int default null,
  PRIMARY KEY(ID),
  INDEX ix_cal_log_uuid(UUID),
  INDEX ix_cal_log_user_id(USER_ID)
);

create table b_calendar_section_connection
(
	ID int NOT NULL AUTO_INCREMENT,
	SECTION_ID int NOT NULL,
	CONNECTION_ID int NOT NULL,
	VENDOR_SECTION_ID varchar(255) NOT NULL,
	SYNC_TOKEN text,
	PAGE_TOKEN text,
	ACTIVE char(1) DEFAULT 'Y',
	LAST_SYNC_DATE datetime DEFAULT NULL,
	LAST_SYNC_STATUS varchar(10) DEFAULT NULL,
	VERSION_ID varchar(255) DEFAULT NULL,
	IS_PRIMARY char(1) DEFAULT 'N',
	PRIMARY KEY (ID),
	INDEX ix_cal_section_con_section_id (SECTION_ID),
	INDEX ix_cal_section_con_connection_id (CONNECTION_ID)
);

create table b_calendar_event_connection
(
	ID int NOT NULL AUTO_INCREMENT,
	EVENT_ID int NOT NULL,
	CONNECTION_ID int NOT NULL,
	VENDOR_EVENT_ID varchar(255) DEFAULT NULL,
	SYNC_STATUS varchar(20) DEFAULT NULL,
	RETRY_COUNT int DEFAULT 0 COMMENT 'Retry count of sending event to vendor, if sync status is not success',
	ENTITY_TAG varchar(255) DEFAULT NULL COMMENT 'Version of vendor event',
	VERSION varchar(255) DEFAULT NULL COMMENT 'Version of internal event',
	VENDOR_VERSION_ID varchar(255) DEFAULT NULL,
	RECURRENCE_ID varchar(255) DEFAULT NULL,
	DATA text DEFAULT NULL,
	PRIMARY KEY (ID),
	INDEX ix_cal_event_con_event_id (EVENT_ID),
	INDEX ix_cal_event_con_connection_id (CONNECTION_ID),
	INDEX ix_cal_event_con_vendor_event_id (VENDOR_EVENT_ID),
	INDEX ix_cal_event_con_recurrence_id (RECURRENCE_ID)
);

CREATE TABLE b_calendar_room_category (
	ID int NOT NULL AUTO_INCREMENT,
	NAME  varchar(255) NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE b_calendar_queue_message (
	ID int NOT NULL AUTO_INCREMENT,
	MESSAGE text NOT NULL,
	DATE_CREATE datetime NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE b_calendar_queue_handled_message(
	ID int NOT NULL AUTO_INCREMENT,
	MESSAGE_ID int NOT NULL,
	QUEUE_ID int NOT NULL,
	HASH varchar(255) NULL,
	DATE_CREATE datetime NULL,
	PRIMARY KEY (ID),
	INDEX ix_cal_queue_handled_id_hash (QUEUE_ID, HASH)
);

CREATE TABLE b_calendar_sharing_link (
	ID int NOT NULL AUTO_INCREMENT,
	OBJECT_ID int NOT NULL,
	OBJECT_TYPE varchar(32) NOT NULL,
	HASH char(64) NOT NULL,
	OPTIONS text NULL,
	ACTIVE char(1) NOT NULL DEFAULT 'Y',
	DATE_CREATE datetime NOT NULL,
	DATE_EXPIRE datetime DEFAULT NULL,
	HOST_ID int DEFAULT NULL,
	OWNER_ID int DEFAULT NULL,
	CONFERENCE_ID varchar(8) DEFAULT NULL,
	PARENT_LINK_HASH char(64) DEFAULT NULL,
	CONTACT_ID int DEFAULT NULL,
	CONTACT_TYPE int DEFAULT NULL,
	MEMBERS_HASH char(64) DEFAULT NULL,
	FREQUENT_USE int DEFAULT NULL,
	PRIMARY KEY (ID),
	INDEX ix_calendar_sharing_link_hash(HASH),
	INDEX ix_calendar_sharing_link_object_id(OBJECT_ID),
	INDEX ix_calendar_sharing_link_contact_id_contact_type(CONTACT_ID, CONTACT_TYPE),
	INDEX ix_calendar_sharing_link_members_hash(MEMBERS_HASH),
	INDEX ix_calendar_sharing_link_conference_id(CONFERENCE_ID)
);

CREATE TABLE b_calendar_sharing_link_rule (
	ID int NOT NULL AUTO_INCREMENT,
	LINK_ID int NOT NULL,
	WEEKDAYS varchar(32) DEFAULT NULL,
	SLOT_SIZE int NOT NULL,
	TIME_FROM int DEFAULT NULL,
	TIME_TO int DEFAULT NULL,
	PRIMARY KEY (ID),
	INDEX ix_calendar_sharing_link_rule_link_id(LINK_ID)
);

CREATE TABLE b_calendar_sharing_object_rule (
	ID int NOT NULL AUTO_INCREMENT,
	OBJECT_ID int NOT NULL,
	OBJECT_TYPE varchar(32) NOT NULL,
	SLOT_SIZE int NOT NULL,
	WEEKDAYS varchar(32) DEFAULT NULL,
	TIME_FROM int DEFAULT NULL,
	TIME_TO int DEFAULT NULL,
	PRIMARY KEY (ID),
	INDEX ix_calendar_sharing_object_rule_object_id_object_type(OBJECT_ID, OBJECT_TYPE)
);

CREATE TABLE b_calendar_event_original_recursion (
	PARENT_EVENT_ID int NOT NULL,
	ORIGINAL_RECURSION_EVENT_ID int NOT NULL,
	PRIMARY KEY (PARENT_EVENT_ID),
	INDEX ix_calendar_event_original_recursion_original_recursion_event_id(ORIGINAL_RECURSION_EVENT_ID)
);

CREATE TABLE b_calendar_sharing_link_member (
    ID int NOT NULL AUTO_INCREMENT,
	LINK_ID int NOT NULL,
	MEMBER_ID int NOT NULL,
	PRIMARY KEY (ID),
	UNIQUE KEY (LINK_ID, MEMBER_ID),
	INDEX ix_calendar_sharing_link_member_link_id(LINK_ID)
);

CREATE TABLE b_calendar_event_attendee (
	ID int NOT NULL AUTO_INCREMENT,
	OWNER_ID int NOT NULL,
	CREATED_BY int NOT NULL,
	MEETING_STATUS varchar(1) NOT NULL,
	DELETED varchar(1) NOT NULL,
	SECTION_ID int NOT NULL,
	COLOR varchar(10) DEFAULT NULL,
	REMIND text DEFAULT NULL,
	DAV_EXCH_LABEL varchar(255) DEFAULT NULL,
	SYNC_STATUS varchar(20) DEFAULT NULL,
	EVENT_ID int NOT NULL,
	PRIMARY KEY(ID),
	INDEX b_calendar_event_attendee__ix_owner_status_deleted(OWNER_ID, MEETING_STATUS, DELETED),
	INDEX b_calendar_event_attendee__ix_section_id(SECTION_ID),
	INDEX b_calendar_event_attendee__ix_event_id(EVENT_ID)
);

CREATE TABLE b_calendar_open_event_category (
	ID int NOT NULL AUTO_INCREMENT,
	NAME varchar(255) NOT NULL,
	CREATOR_ID int NOT NULL,
	CLOSED varchar(1) DEFAULT 'N',
	DESCRIPTION text DEFAULT NULL,
	ACCESS_CODES text DEFAULT NULL,
	DELETED varchar(1) DEFAULT 'N',
	CHANNEL_ID int NOT NULL,
	EVENTS_COUNT int NOT NULL DEFAULT 0,
	DATE_CREATE datetime NOT NULL DEFAULT NOW(),
	LAST_ACTIVITY datetime NOT NULL DEFAULT NOW(),
	PRIMARY KEY(ID),
	INDEX b_calendar_open_event_category__ix_creator_id(CREATOR_ID),
	INDEX b_calendar_open_event_category__ix_last_activity(LAST_ACTIVITY),
	UNIQUE INDEX b_calendar_open_event_category__ux_channel_id(CHANNEL_ID)
);

CREATE TABLE b_calendar_open_event_option (
	ID int NOT NULL AUTO_INCREMENT,
	EVENT_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	THREAD_ID int NOT NULL,
	OPTIONS text NOT NULL,
	ATTENDEES_COUNT int NOT NULL DEFAULT 0,
	PRIMARY KEY(ID),
	UNIQUE INDEX b_calendar_open_event_option__ux_event_id(EVENT_ID),
	INDEX b_calendar_open_event_option__ix_category_id(CATEGORY_ID)
);

CREATE TABLE b_calendar_open_event_category_attendee (
	ID int NOT NULL AUTO_INCREMENT,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY(ID),
	UNIQUE INDEX b_calendar_open_event_category_attendee__ux_user_category(USER_ID, CATEGORY_ID)
);

CREATE TABLE b_calendar_open_event_category_muted (
	ID int NOT NULL AUTO_INCREMENT,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY(ID),
	UNIQUE INDEX b_calendar_open_event_category_muted__ux_category_user(CATEGORY_ID, USER_ID)
);

CREATE TABLE b_calendar_open_event_category_banned (
	ID int NOT NULL AUTO_INCREMENT,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY(ID),
	UNIQUE INDEX b_calendar_open_event_category_banned__ux_user_category(USER_ID, CATEGORY_ID)
);

CREATE TABLE b_calendar_scorer (
	`ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`USER_ID` INT(11) NOT NULL DEFAULT 0,
	`EVENT_ID` INT(11) NOT NULL DEFAULT 0,
	`PARENT_ID` INT(11) NOT NULL DEFAULT 0,
	`TYPE` VARCHAR(64) NOT NULL DEFAULT '',
	`VALUE` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`ID`),
	INDEX `ix_calendar_scorer_parent` (`PARENT_ID`),
	INDEX `ix_calendar_scorer_utype` (`USER_ID`, `TYPE`, `EVENT_ID`),
	INDEX `ix_calendar_scorer_utype2` (`USER_ID`, `EVENT_ID`, `TYPE`),
	INDEX `ix_calendar_scorer_type` (`EVENT_ID`, `TYPE`)
);
