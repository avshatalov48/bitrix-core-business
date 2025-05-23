
CREATE TABLE b_calendar_type (
  XML_ID varchar(255) NOT NULL,
  NAME varchar(255),
  DESCRIPTION text,
  EXTERNAL_ID varchar(100),
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (XML_ID)
);

CREATE TABLE b_calendar_section (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  NAME varchar(255),
  XML_ID varchar(100),
  EXTERNAL_ID varchar(100),
  GAPI_CALENDAR_ID varchar(255),
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  DESCRIPTION text,
  COLOR varchar(10),
  TEXT_COLOR varchar(10),
  EXPORT varchar(255),
  SORT int NOT NULL DEFAULT 100,
  CAL_TYPE varchar(100),
  OWNER_ID int,
  CREATED_BY int NOT NULL,
  PARENT_ID int,
  DATE_CREATE timestamp,
  TIMESTAMP_X timestamp,
  DAV_EXCH_CAL varchar(255),
  DAV_EXCH_MOD varchar(255),
  CAL_DAV_CON varchar(255),
  CAL_DAV_CAL varchar(255),
  CAL_DAV_MOD varchar(255),
  IS_EXCHANGE char(1),
  SYNC_TOKEN varchar(255),
  PAGE_TOKEN varchar(255),
  EXTERNAL_TYPE varchar(20),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_section_cal_type_owner_id ON b_calendar_section (cal_type, owner_id);
CREATE INDEX ix_b_calendar_section_page_token ON b_calendar_section (page_token);

CREATE TABLE b_calendar_event (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  PARENT_ID int,
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  DELETED char(1) NOT NULL DEFAULT 'N',
  CAL_TYPE varchar(100),
  OWNER_ID int NOT NULL,
  NAME varchar(255),
  DATE_FROM timestamp,
  DATE_TO timestamp,
  ORIGINAL_DATE_FROM timestamp,
  TZ_FROM varchar(50),
  TZ_TO varchar(50),
  TZ_OFFSET_FROM int,
  TZ_OFFSET_TO int,
  DATE_FROM_TS_UTC int,
  DATE_TO_TS_UTC int,
  DT_SKIP_TIME char(1),
  DT_LENGTH int8,
  EVENT_TYPE varchar(50),
  CREATED_BY int NOT NULL,
  DATE_CREATE timestamp,
  TIMESTAMP_X timestamp,
  DESCRIPTION text,
  DT_FROM timestamp,
  DT_TO timestamp,
  PRIVATE_EVENT varchar(10),
  ACCESSIBILITY varchar(10),
  IMPORTANCE varchar(10),
  IS_MEETING char(1),
  MEETING_STATUS char(1),
  MEETING_HOST int,
  MEETING text,
  LOCATION varchar(255),
  REMIND text,
  COLOR varchar(10),
  TEXT_COLOR varchar(10),
  RRULE varchar(255),
  EXDATE text,
  DAV_XML_ID varchar(255),
  G_EVENT_ID varchar(255),
  DAV_EXCH_LABEL varchar(255),
  CAL_DAV_LABEL varchar(255),
  VERSION varchar(255),
  ATTENDEES_CODES text,
  RECURRENCE_ID int,
  RELATIONS varchar(255),
  SEARCHABLE_CONTENT text,
  SECTION_ID int,
  SYNC_STATUS varchar(20),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_event_date_from_ts_utc ON b_calendar_event (date_from_ts_utc);
CREATE INDEX ix_b_calendar_event_date_to_ts_utc ON b_calendar_event (date_to_ts_utc);
CREATE INDEX ix_b_calendar_event_owner_id_date_from_ts_utc_date_to_ts_utc ON b_calendar_event (owner_id, date_from_ts_utc, date_to_ts_utc);
CREATE INDEX ix_b_calendar_event_parent_id ON b_calendar_event (parent_id);
CREATE INDEX ix_b_calendar_event_created_by_access_date_to ON b_calendar_event (created_by, accessibility, date_to_ts_utc);
CREATE INDEX ix_b_calendar_event_accessibility_date_from_ts_utc_date_to_ts_u ON b_calendar_event (accessibility, date_from_ts_utc, date_to_ts_utc);
CREATE INDEX ix_b_calendar_event_recurrence_id ON b_calendar_event (recurrence_id);
CREATE INDEX ix_b_calendar_event_g_event_id ON b_calendar_event (g_event_id);
CREATE INDEX ix_b_calendar_event_dav_xml_id ON b_calendar_event (dav_xml_id);
CREATE INDEX ix_b_calendar_event_owner_id_deleted_date_to_ts_utc_date_from_t ON b_calendar_event (owner_id, deleted, date_to_ts_utc, date_from_ts_utc);
CREATE INDEX ix_b_calendar_event_cal_type_deleted_date_to_ts_utc_date_from_t ON b_calendar_event (cal_type, deleted, date_to_ts_utc, date_from_ts_utc);
CREATE INDEX ix_b_calendar_event_location ON b_calendar_event (location);
CREATE INDEX ix_b_calendar_event_section_id_deleted ON b_calendar_event (section_id, deleted);
CREATE INDEX ix_b_calendar_event_sync_status ON b_calendar_event (sync_status);
CREATE INDEX ix_b_calendar_event_section_id_deleted_date_to_ts_utc_date_from ON b_calendar_event (section_id, deleted, date_to_ts_utc, date_from_ts_utc);
CREATE INDEX tx_b_calendar_event_searchable_content ON b_calendar_event USING GIN (to_tsvector('english', searchable_content));

CREATE TABLE b_calendar_event_sect (
  EVENT_ID int NOT NULL,
  SECT_ID int NOT NULL,
  REL char(10),
  PRIMARY KEY (EVENT_ID, SECT_ID)
);
CREATE INDEX ix_b_calendar_event_sect_sect_id_event_id ON b_calendar_event_sect (sect_id, event_id);

CREATE TABLE b_calendar_push (
  ENTITY_TYPE varchar(24) NOT NULL,
  ENTITY_ID int NOT NULL,
  CHANNEL_ID varchar(128) NOT NULL,
  RESOURCE_ID varchar(128) NOT NULL,
  EXPIRES timestamp NOT NULL,
  NOT_PROCESSED varchar(1) NOT NULL DEFAULT 'N',
  FIRST_PUSH_DATE timestamp DEFAULT NULL,
  PRIMARY KEY (ENTITY_TYPE, ENTITY_ID)
);
CREATE INDEX ix_b_calendar_push_expires ON b_calendar_push (expires);

CREATE TABLE b_calendar_access (
  ACCESS_CODE varchar(100) NOT NULL,
  TASK_ID int NOT NULL,
  SECT_ID varchar(100) NOT NULL,
  PRIMARY KEY (ACCESS_CODE, TASK_ID, SECT_ID)
);
CREATE INDEX ix_b_calendar_access_sect_id ON b_calendar_access (sect_id);

CREATE TABLE b_calendar_resource (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  EVENT_ID int,
  CAL_TYPE varchar(100),
  RESOURCE_ID int NOT NULL,
  PARENT_TYPE varchar(100),
  PARENT_ID int NOT NULL,
  UF_ID int,
  DATE_FROM_UTC timestamp,
  DATE_TO_UTC timestamp,
  DATE_FROM timestamp,
  DATE_TO timestamp,
  DURATION int8,
  SKIP_TIME char(1),
  TZ_FROM varchar(50),
  TZ_TO varchar(50),
  TZ_OFFSET_FROM int,
  TZ_OFFSET_TO int,
  CREATED_BY int NOT NULL,
  DATE_CREATE timestamp,
  TIMESTAMP_X timestamp,
  SERVICE_NAME varchar(200),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_resource_uf_id_parent_type_parent_id ON b_calendar_resource (uf_id, parent_type, parent_id);

CREATE TABLE b_calendar_location (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  SECTION_ID int NOT NULL,
  NECESSITY char(1) DEFAULT 'N',
  CAPACITY int DEFAULT 0,
  CATEGORY_ID int DEFAULT null,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_location_section_id ON b_calendar_location (section_id);

CREATE TABLE b_calendar_log (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  TIMESTAMP_X timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  MESSAGE text,
  TYPE varchar(50) DEFAULT null,
  UUID varchar(255) DEFAULT null,
  USER_ID int DEFAULT null,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_log_uuid ON b_calendar_log (uuid);
CREATE INDEX ix_b_calendar_log_user_id ON b_calendar_log (user_id);

CREATE TABLE b_calendar_section_connection (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  SECTION_ID int NOT NULL,
  CONNECTION_ID int NOT NULL,
  VENDOR_SECTION_ID varchar(255) NOT NULL,
  SYNC_TOKEN text,
  PAGE_TOKEN text,
  ACTIVE char(1) DEFAULT 'Y',
  LAST_SYNC_DATE timestamp DEFAULT NULL,
  LAST_SYNC_STATUS varchar(10) DEFAULT NULL,
  VERSION_ID varchar(255) DEFAULT NULL,
  IS_PRIMARY char(1) DEFAULT 'N',
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_section_connection_section_id ON b_calendar_section_connection (section_id);
CREATE INDEX ix_b_calendar_section_connection_connection_id ON b_calendar_section_connection (connection_id);

CREATE TABLE b_calendar_event_connection (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  EVENT_ID int NOT NULL,
  CONNECTION_ID int NOT NULL,
  VENDOR_EVENT_ID varchar(255) DEFAULT NULL,
  SYNC_STATUS varchar(20) DEFAULT NULL,
  RETRY_COUNT int DEFAULT 0,
  ENTITY_TAG varchar(255) DEFAULT NULL,
  VERSION varchar(255) DEFAULT NULL,
  VENDOR_VERSION_ID varchar(255) DEFAULT NULL,
  RECURRENCE_ID varchar(255) DEFAULT NULL,
  DATA text DEFAULT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_event_connection_event_id ON b_calendar_event_connection (event_id);
CREATE INDEX ix_b_calendar_event_connection_connection_id ON b_calendar_event_connection (connection_id);
CREATE INDEX ix_b_calendar_event_connection_vendor_event_id ON b_calendar_event_connection (vendor_event_id);
CREATE INDEX ix_b_calendar_event_connection_recurrence_id ON b_calendar_event_connection (recurrence_id);

CREATE TABLE b_calendar_room_category (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  NAME varchar(255),
  PRIMARY KEY (ID)
);

CREATE TABLE b_calendar_queue_message (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MESSAGE text NOT NULL,
  DATE_CREATE timestamp,
  PRIMARY KEY (ID)
);

CREATE TABLE b_calendar_queue_handled_message (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MESSAGE_ID int NOT NULL,
  QUEUE_ID int NOT NULL,
  HASH varchar(255),
  DATE_CREATE timestamp,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_queue_handled_message_queue_id_hash ON b_calendar_queue_handled_message (queue_id, hash);

CREATE TABLE b_calendar_sharing_link (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  OBJECT_ID int NOT NULL,
  OBJECT_TYPE varchar(32) NOT NULL,
  HASH char(64) NOT NULL,
  OPTIONS text,
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  DATE_CREATE timestamp NOT NULL,
  DATE_EXPIRE timestamp DEFAULT NULL,
  HOST_ID int DEFAULT NULL,
  OWNER_ID int DEFAULT NULL,
  CONFERENCE_ID varchar(8) DEFAULT NULL,
  PARENT_LINK_HASH char(64) DEFAULT NULL,
  CONTACT_ID int DEFAULT NULL,
  CONTACT_TYPE int DEFAULT NULL,
  MEMBERS_HASH char(64) DEFAULT NULL,
  FREQUENT_USE int DEFAULT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_sharing_link_hash ON b_calendar_sharing_link (hash);
CREATE INDEX ix_b_calendar_sharing_link_object_id ON b_calendar_sharing_link (object_id);
CREATE INDEX ix_b_calendar_sharing_link_contact_id_contact_type ON b_calendar_sharing_link (contact_id, contact_type);
CREATE INDEX ix_b_calendar_sharing_link_members_hash ON b_calendar_sharing_link (members_hash);
CREATE INDEX ix_b_calendar_sharing_link_conference_id ON b_calendar_sharing_link (conference_id);

CREATE TABLE b_calendar_sharing_link_rule (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  LINK_ID int NOT NULL,
  WEEKDAYS varchar(32) DEFAULT NULL,
  SLOT_SIZE int NOT NULL,
  TIME_FROM int DEFAULT NULL,
  TIME_TO int DEFAULT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_sharing_link_rule_link_id ON b_calendar_sharing_link_rule (link_id);

CREATE TABLE b_calendar_sharing_object_rule (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  OBJECT_ID int NOT NULL,
  OBJECT_TYPE varchar(32) NOT NULL,
  SLOT_SIZE int NOT NULL,
  WEEKDAYS varchar(32) DEFAULT NULL,
  TIME_FROM int DEFAULT NULL,
  TIME_TO int DEFAULT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_sharing_object_rule_object_id_object_type ON b_calendar_sharing_object_rule (object_id, object_type);

CREATE TABLE b_calendar_event_original_recursion (
  PARENT_EVENT_ID int NOT NULL,
  ORIGINAL_RECURSION_EVENT_ID int NOT NULL,
  PRIMARY KEY (PARENT_EVENT_ID)
);
CREATE INDEX ix_b_calendar_event_original_recursion_original_recursion_event ON b_calendar_event_original_recursion (original_recursion_event_id);

CREATE TABLE b_calendar_sharing_link_member (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  LINK_ID int NOT NULL,
  MEMBER_ID int NOT NULL,
  PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_calendar_sharing_link_member_link_id_member_id ON b_calendar_sharing_link_member (link_id, member_id);
CREATE INDEX ix_b_calendar_sharing_link_member_link_id ON b_calendar_sharing_link_member (link_id);

CREATE TABLE b_calendar_event_attendee (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
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
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_event_attendee_owner_id_meeting_status_deleted ON b_calendar_event_attendee (owner_id, meeting_status, deleted);
CREATE INDEX ix_b_calendar_event_attendee_section_id ON b_calendar_event_attendee (section_id);
CREATE INDEX ix_b_calendar_event_attendee_event_id ON b_calendar_event_attendee (event_id);

CREATE TABLE b_calendar_open_event_category (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	NAME varchar(255) NOT NULL,
	CREATOR_ID int NOT NULL,
	CLOSED varchar(1) DEFAULT 'N',
	DESCRIPTION text DEFAULT NULL,
	ACCESS_CODES text DEFAULT NULL,
	DELETED varchar(1) DEFAULT 'N',
	CHANNEL_ID int NOT NULL,
	EVENTS_COUNT int NOT NULL DEFAULT 0,
	DATE_CREATE timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	LAST_ACTIVITY timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_open_event_category_creator_id ON b_calendar_open_event_category (creator_id);
CREATE INDEX ix_b_calendar_open_event_category_last_activity ON b_calendar_open_event_category (last_activity);
CREATE UNIQUE INDEX ux_b_calendar_open_event_category_channel_id ON b_calendar_open_event_category (channel_id);

CREATE TABLE b_calendar_open_event_option (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	EVENT_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	THREAD_ID int NOT NULL,
	OPTIONS text NOT NULL,
	ATTENDEES_COUNT int NOT NULL DEFAULT 0,
	PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_calendar_open_event_option_event_id ON b_calendar_open_event_option (event_id);
CREATE INDEX ix_b_calendar_open_event_option_category_id ON b_calendar_open_event_option (category_id);

CREATE TABLE b_calendar_open_event_category_attendee (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_calendar_open_event_category_attendee_user_id_category_id ON b_calendar_open_event_category_attendee (user_id, category_id);

CREATE TABLE b_calendar_open_event_category_muted (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_calendar_open_event_category_muted_category_id_user_id ON b_calendar_open_event_category_muted (category_id, user_id);

CREATE TABLE b_calendar_open_event_category_banned (
	ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	USER_ID int NOT NULL,
	CATEGORY_ID int NOT NULL,
	PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_calendar_open_event_category_banned_user_id_category_id ON b_calendar_open_event_category_banned (user_id, category_id);

CREATE TABLE b_calendar_scorer (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	USER_ID int NOT NULL DEFAULT 0,
	EVENT_ID int NOT NULL DEFAULT 0,
	PARENT_ID int NOT NULL DEFAULT 0,
	TYPE varchar(64) NOT NULL DEFAULT '',
	VALUE int8 NOT NULL DEFAULT 0,
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_calendar_scorer_parent_id ON b_calendar_scorer (parent_id);
CREATE INDEX ix_b_calendar_scorer_user_id_type_event_id ON b_calendar_scorer (user_id, type, event_id);
CREATE INDEX ix_b_calendar_scorer_user_id_event_id_type ON b_calendar_scorer (user_id, event_id, type);
CREATE INDEX ix_b_calendar_scorer_event_id_type ON b_calendar_scorer (event_id, type);
