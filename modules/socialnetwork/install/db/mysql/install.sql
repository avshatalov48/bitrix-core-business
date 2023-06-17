create table b_sonet_group_subject
(
  ID int not null auto_increment,
  SITE_ID char(2) not null,
  NAME varchar(255) not null,
  SORT int(10) not null default '100',
  primary key (ID)
);

create table b_sonet_group_subject_site
(
  SUBJECT_ID int not null,
  SITE_ID char(2) not null,
  PRIMARY KEY PK_B_SONET_GROUP_SUBJECT_SITE(SUBJECT_ID, SITE_ID)
);

create table b_sonet_group
(
	ID int not null auto_increment,
	SITE_ID char(2) not null,
	NAME varchar(255) not null,
	DESCRIPTION text null,
	DATE_CREATE datetime not null,
	DATE_UPDATE datetime not null,
	ACTIVE char(1) not null default 'Y',
	VISIBLE char(1) not null default 'Y',
	OPENED char(1) not null default 'N',
	SUBJECT_ID int not null,
	OWNER_ID int not null,
	KEYWORDS varchar(255) null,
	IMAGE_ID int null,
	AVATAR_TYPE varchar(50) null,
	NUMBER_OF_MEMBERS int not null default 0,
	NUMBER_OF_MODERATORS int not null default 0,
	INITIATE_PERMS char(1) not null default 'K',
	DATE_ACTIVITY datetime not null,
	CLOSED char(1) not null default 'N',
	SPAM_PERMS char(1) not null default 'K',
	PROJECT char(1) not null default 'N',
	PROJECT_DATE_START datetime null,
	PROJECT_DATE_FINISH datetime null,
	SEARCH_INDEX mediumtext null,
	LANDING char(1) null,
	SCRUM_OWNER_ID int null,
	SCRUM_MASTER_ID int null,
	SCRUM_SPRINT_DURATION int null,
	SCRUM_TASK_RESPONSIBLE char(1) null,
	primary key (ID),
	index IX_SONET_GROUP_1(OWNER_ID)
);

create table b_sonet_group_tag (
	GROUP_ID int(11) NOT NULL,
	NAME varchar(255) NOT NULL,
	PRIMARY KEY (GROUP_ID,NAME),
	index IX_SONET_GROUP_TAG_2(`NAME`)
);

create table b_sonet_group_site
(
  GROUP_ID int not null,
  SITE_ID char(2) not null,
  PRIMARY KEY PK_B_SONET_GROUP_SITE(GROUP_ID, SITE_ID)
);

create table b_sonet_user2group
(
  ID int not null auto_increment,
  USER_ID int not null,
  GROUP_ID int not null,
  ROLE char(1) not null default 'U',
  AUTO_MEMBER char(1) not null default 'N',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  INITIATED_BY_TYPE char(1) not null default 'U',
  INITIATED_BY_USER_ID int not null,
  MESSAGE text null,
  primary key (ID),
  unique IX_SONET_USER2GROUP_1(USER_ID, GROUP_ID),
  index IX_SONET_USER2GROUP_2(USER_ID, GROUP_ID, ROLE),
  index IX_SONET_USER2GROUP_3(GROUP_ID, USER_ID, ROLE),
  index IX_SONET_USER2GROUP_4(USER_ID, ROLE)
);

create table b_sonet_features
(
  ID int not null auto_increment,
  ENTITY_TYPE char(1) not null default 'G',
  ENTITY_ID int not null,
  FEATURE varchar(50) not null,
  FEATURE_NAME varchar(250) null,
  ACTIVE char(1) not null default 'Y',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  primary key (ID),
  unique IX_SONET_GROUP_FEATURES_1(ENTITY_TYPE, ENTITY_ID, FEATURE),
  unique IX_SONET_GROUP_FEATURES_2 (ENTITY_TYPE, FEATURE, ACTIVE, ENTITY_ID)
);

create table b_sonet_features2perms
(
  ID int not null auto_increment,
  FEATURE_ID int not null,
  OPERATION_ID varchar(50) not null,
  ROLE char(1) not null,
  primary key (ID),
  unique IX_SONET_GROUP_FEATURES2PERMS_1(FEATURE_ID, OPERATION_ID),
  index IX_SONET_GROUP_FEATURES2PERMS_2(FEATURE_ID, ROLE, OPERATION_ID)
);

create table b_sonet_user_relations
(
  ID int not null auto_increment,
  FIRST_USER_ID int not null,
  SECOND_USER_ID int not null,
  RELATION char(1) not null default 'N',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  MESSAGE text null,
  INITIATED_BY char(1) not null default 'F',
  primary key (ID),
  unique IX_SONET_RELATIONS_1(FIRST_USER_ID, SECOND_USER_ID),
  index IX_SONET_RELATIONS_2(FIRST_USER_ID, SECOND_USER_ID, RELATION),
  index IX_SONET_RELATIONS_3(SECOND_USER_ID)
);

create table b_sonet_messages
(
  ID int not null auto_increment,
  FROM_USER_ID int not null,
  TO_USER_ID int not null,
  TITLE varchar(250) null,
  MESSAGE text null,
  DATE_CREATE datetime not null,
  DATE_VIEW datetime null,
  MESSAGE_TYPE char(1) not null default 'P',
  FROM_DELETED char(1) not null default 'N',
  TO_DELETED char(1) not null default 'N',
  SEND_MAIL char(1) not null default 'N',
  EMAIL_TEMPLATE varchar(250) null,
  IS_LOG char(1) NULL,
  primary key (ID),
  index IX_SONET_MESSAGES_1(FROM_USER_ID),
  index IX_SONET_MESSAGES_2(TO_USER_ID)
);

create table b_sonet_smile (
   ID smallint(3) not null auto_increment,
   SMILE_TYPE char(1) not null default 'S',
   TYPING varchar(100) null,
   IMAGE varchar(128) not null,
   DESCRIPTION varchar(50),
   CLICKABLE char(1) not null default 'Y',
   SORT int(10) not null default '150',
   IMAGE_WIDTH int(11) not null default '0',
   IMAGE_HEIGHT int(11) not null default '0',
   primary key (ID));

create table b_sonet_smile_lang (
   ID int(11) not null auto_increment,
   SMILE_ID int(11) not null default '0',
   LID char(2) not null,
   NAME varchar(255) not null,
   primary key (ID),
   unique IX_SONET_SMILE_K (SMILE_ID, LID)
);

create table b_sonet_user_perms
(
  ID int not null auto_increment,
  USER_ID int not null,
  OPERATION_ID varchar(50) not null,
  RELATION_TYPE char(1) not null,
  primary key (ID),
  unique IX_SONET_USER_PERMS_2(USER_ID, OPERATION_ID)
);

create table b_sonet_user_events
(
  ID int not null auto_increment,
  USER_ID int not null,
  EVENT_ID varchar(50) not null,
  ACTIVE char(1) not null default 'Y',
  SITE_ID char(2) not null,
  primary key (ID),
  unique IX_SONET_USER_PERMS_2(USER_ID, EVENT_ID)
);

create table b_sonet_log
(
  ID int not null auto_increment,
  ENTITY_TYPE varchar(50) not null default 'G',
  ENTITY_ID int not null,
  EVENT_ID varchar(50) not null,
  USER_ID int null,
  LOG_DATE datetime not null,
  SITE_ID char(2) null,
  TITLE_TEMPLATE varchar(250) null,
  TITLE varchar(250) not null,
  MESSAGE mediumtext null,
  TEXT_MESSAGE text null,
  URL varchar(500) null,
  MODULE_ID varchar(50) null,
  CALLBACK_FUNC varchar(250) null,
  EXTERNAL_ID varchar(250) null,
  PARAMS text,
  TMP_ID int(11) default NULL,
  SOURCE_ID int(11) default NULL,
  LOG_UPDATE datetime not null,
  COMMENTS_COUNT int(11) default NULL,
  ENABLE_COMMENTS char(1) default 'Y',
  RATING_TYPE_ID varchar(50) default NULL,
  RATING_ENTITY_ID int(11) default NULL,
  SOURCE_TYPE varchar(50) default NULL,
  TRANSFORM char(1) default NULL,
  INACTIVE char(1) default NULL,
  primary key (ID),
  index IX_SONET_LOG_1(ENTITY_TYPE, ENTITY_ID, EVENT_ID),
  index IX_SONET_LOG_2(USER_ID, LOG_DATE, EVENT_ID),
  index IX_SONET_LOG_3(SOURCE_ID),
  index IX_SONET_LOG_4(LOG_UPDATE),
  index IX_SONET_LOG_5(USER_ID, ENTITY_TYPE, LOG_UPDATE),
  index IX_SONET_LOG_6(MODULE_ID),
  index IX_SONET_LOG_7(ENTITY_ID, EVENT_ID),
  index IX_SONET_LOG_8(RATING_ENTITY_ID, RATING_TYPE_ID),
  index IX_SONET_LOG_9(EXTERNAL_ID)
);

create table b_sonet_log_site
(
  LOG_ID int not null,
  SITE_ID char(2) not null,
  PRIMARY KEY PK_B_SONET_LOG_SITE(LOG_ID, SITE_ID)
);

create table b_sonet_log_comment (
  ID int not null auto_increment,
  LOG_ID int not null,
  ENTITY_TYPE varchar(50) not null default 'G',
  ENTITY_ID int not null,
  EVENT_ID varchar(50) not null,
  USER_ID int(11) default NULL,
  LOG_DATE datetime not null,
  MESSAGE text,
  TEXT_MESSAGE text,
  MODULE_ID varchar(50) default NULL,
  SOURCE_ID int default NULL,
  URL varchar(500) default NULL,
  RATING_TYPE_ID varchar(50) default NULL,
  RATING_ENTITY_ID int(11) default NULL,
  SHARE_DEST text default NULL,
  primary key (ID),
  index IX_SONET_LOG_COMMENT_1(ENTITY_TYPE, ENTITY_ID, EVENT_ID),
  index IX_SONET_LOG_COMMENT_2(USER_ID, LOG_DATE, EVENT_ID),
  index IX_SONET_LOG_COMMENT_3(LOG_ID),
  index IX_SONET_LOG_COMMENT_4(SOURCE_ID),
  index IX_SONET_LOG_COMMENT_5(RATING_TYPE_ID, RATING_ENTITY_ID)
);

create table b_sonet_log_events
(
  ID int not null auto_increment,
  USER_ID int not null,
  ENTITY_TYPE varchar(50) not null default 'G',
  ENTITY_ID int not null,
  ENTITY_CB char(1) NOT NULL default 'N',
  ENTITY_MY char(1) NOT NULL default 'N',
  EVENT_ID varchar(50) not null,
  SITE_ID char(2) null,
  MAIL_EVENT char(1) not null default 'N',
  TRANSPORT char(1) NOT NULL default 'N',
  VISIBLE char(1) NOT NULL default 'Y',
  primary key (ID),
  index IX_SONET_LOG_EVENTS_2(ENTITY_TYPE, ENTITY_ID, EVENT_ID),
  unique IX_SONET_LOG_EVENTS_3(USER_ID, ENTITY_TYPE, ENTITY_ID, ENTITY_CB, ENTITY_MY, EVENT_ID, SITE_ID),
  index IX_SONET_LOG_EVENTS_4(USER_ID, ENTITY_CB, ENTITY_ID),
  index IX_SONET_LOG_EVENTS_5(USER_ID, ENTITY_MY, ENTITY_TYPE, ENTITY_ID)
);

CREATE TABLE b_sonet_event_user_view
(
	ENTITY_TYPE varchar(50) NOT NULL default 'G',
	ENTITY_ID int(11) NOT NULL,
	EVENT_ID varchar(50) NOT NULL,
	USER_ID int(11) NOT NULL default 0,
	USER_IM_ID int(11) NOT NULL default 0,
	USER_ANONYMOUS char(1) NOT NULL default 'N',
	PRIMARY KEY (ENTITY_TYPE,ENTITY_ID,EVENT_ID,USER_ID,USER_IM_ID),
	index IX_SONET_EVENT_USER_VIEW_1(USER_ID, EVENT_ID, ENTITY_TYPE, USER_ANONYMOUS),
	index IX_SONET_EVENT_USER_VIEW_2(ENTITY_TYPE, EVENT_ID)
);

create table if not exists b_sonet_log_right
(
	ID int(11) not null auto_increment,
	LOG_ID int(11) not null,
	GROUP_CODE varchar(50) not null,
	LOG_UPDATE datetime null,
	primary key (ID),
	unique ix_b_sonet_log_right_group_code(LOG_ID, GROUP_CODE),
	index ix_b_sonet_log_right_group_log(GROUP_CODE, LOG_ID),
	index ix_b_sonet_log_right_logupdate (LOG_UPDATE)
);

create table b_sonet_log_counter
(
	USER_ID int(11) not null,
	SITE_ID char(2) not null default '**',
	CODE varchar(50) not null default '**',
	CNT int(11) not null default 0,
	LAST_DATE datetime,
	PAGE_SIZE int(11) default null,
	PAGE_LAST_DATE_1 datetime default null,
	primary key (USER_ID, SITE_ID, CODE)
);

create table b_sonet_log_page
(
	USER_ID int(11) not null,
	SITE_ID char(2) not null default '**',
	GROUP_CODE varchar(50) not null default '**',
	PAGE_SIZE int(11) not null,
	PAGE_NUM int(11) not null default 1,
	PAGE_LAST_DATE datetime default null,
	TRAFFIC_AVG int(11) default null,
	TRAFFIC_CNT int(11) default null,
	TRAFFIC_LAST_DATE datetime default null,
	primary key (USER_ID, SITE_ID, GROUP_CODE, PAGE_SIZE, PAGE_NUM)
);

create table b_sonet_log_follow
(
	USER_ID int(11) not null,
	CODE varchar(50) not null default '**',
	REF_ID int(11) not null,
	TYPE char(1) not null default 'Y',
	FOLLOW_DATE datetime,
	BY_WF char(1) null,
	primary key (USER_ID, CODE),
	index IX_SONET_FOLLOW_1(`USER_ID`, `REF_ID`),
	index IX_SONET_FOLLOW_2(`USER_ID`, `CODE`, `TYPE`, `FOLLOW_DATE`),
	index IX_SONET_FOLLOW_3(`CODE`, `TYPE`, `USER_ID`)
);

create table b_sonet_log_subscribe
(
	USER_ID int(11) not null,
	LOG_ID int(11) not null,
	TYPE char(3) not null,
	END_DATE datetime,
	primary key (USER_ID, LOG_ID, TYPE),
	index IX_SONET_LOG_SUBSCRIBE_1(`LOG_ID`)
);

create table b_sonet_log_smartfilter
(
	USER_ID int(11) not null,
	TYPE char(1) not null default 'N',
	primary key (USER_ID)
);

create table b_sonet_log_favorites
(
	USER_ID int(11) not null,
	LOG_ID int(11) not null,
	primary key (USER_ID, LOG_ID),
	index IX_SONET_LOG_FAVORITES_1(LOG_ID)
);

create table b_sonet_log_view
(
  USER_ID int(11) not null,
  EVENT_ID varchar(50) not null,
  TYPE char(1) not null default 'Y',
  primary key (USER_ID, EVENT_ID)
);

create table b_sonet_subscription
(
	ID int(11) not null auto_increment,
	USER_ID int(11) not null,
	CODE varchar(50) not null,
	primary key (ID),
	unique IX_SONET_SUBSCRIPTION_1(USER_ID, CODE)
);

create table b_sonet_group_view
(
	USER_ID int(11) not null,
	GROUP_ID int(11) not null,
	DATE_VIEW datetime DEFAULT NULL,
	primary key (USER_ID, GROUP_ID)
);

create table b_sonet_group_favorites
(
	USER_ID int(11) not null,
	GROUP_ID int(11) not null,
	DATE_ADD datetime DEFAULT NULL,
	primary key (USER_ID, GROUP_ID)
);

create table b_sonet_group_pin
(
	ID int(11) not null auto_increment,
	USER_ID int(11) not null,
	GROUP_ID int(11) not null,
	CONTEXT varchar(100) default null,
	primary key (ID),
	unique IX_SONET_GROUP_PIN_1(USER_ID, GROUP_ID, CONTEXT)
);

create table b_sonet_log_index
(
	LOG_ID int(11) not null,
	ITEM_TYPE varchar(10) not null default 'L',
	ITEM_ID int(11) not null,
	CONTENT text null,
	LOG_UPDATE datetime null,
	DATE_CREATE datetime null,
	primary key (ITEM_TYPE, ITEM_ID),
	index IX_SONET_LOG_INDEX_1(LOG_ID),
	index IX_SONET_LOG_INDEX_2(LOG_UPDATE),
	index IX_SONET_LOG_INDEX_3(DATE_CREATE)
);

create table b_sonet_user_content_view
(
	USER_ID int(11) not null,
	RATING_TYPE_ID varchar(50) not null,
	RATING_ENTITY_ID int(11) not null,
	CONTENT_ID varchar(50) not null,
	DATE_VIEW datetime DEFAULT NULL,
	primary key (USER_ID, RATING_TYPE_ID, RATING_ENTITY_ID),
	index IX_SONET_USER_CONTENT_VIEW_1(CONTENT_ID),
	index IX_SONET_USER_CONTENT_VIEW_2(RATING_TYPE_ID, RATING_ENTITY_ID)
);

create table b_sonet_log_tag (
	LOG_ID int(11) NOT NULL,
	ITEM_TYPE varchar(10) not null default 'L',
	ITEM_ID int(11) not null,
	NAME varchar(255) NOT NULL,
	PRIMARY KEY (ITEM_TYPE,ITEM_ID,NAME),
	index IX_SONET_LOG_TAG_1(`LOG_ID`),
	index IX_SONET_LOG_TAG_2(`NAME`)
);

create table b_sonet_user_tag (
	USER_ID int(11) NOT NULL,
	NAME varchar(255) NOT NULL,
	PRIMARY KEY (`USER_ID`,`NAME`),
	index IX_SONET_USER_TAG_1(`NAME`)
);

create table b_sonet_user_welltory (
	ID int(11) not null auto_increment,
	USER_ID int(11) NOT NULL,
	STRESS tinyint NOT NULL,
	STRESS_TYPE varchar(100) DEFAULT NULL,
	STRESS_COMMENT varchar(255) DEFAULT NULL,
	DATE_MEASURE datetime not null,
	HASH varchar(100) DEFAULT NULL,
	PRIMARY KEY (`ID`),
	index IX_SONET_USER_STRESSLEVEL_1(`USER_ID`,`DATE_MEASURE`)
);

create table b_sonet_user_welltory_disclaimer (
	ID int(11) not null auto_increment,
	USER_ID int(11) NOT NULL,
	DATE_SIGNED datetime not null,
	PRIMARY KEY (`ID`),
	index IX_SONET_USER_STRESSLEVEL_DISCLAIMER_1(`USER_ID`)
);

create table b_sonet_log_pinned
(
	LOG_ID int(11) not null,
	USER_ID int(11) not null,
	PINNED_DATE datetime default null,
	primary key (LOG_ID, USER_ID),
	index IX_SONET_LOG_PINNED_1(`PINNED_DATE`)
);
