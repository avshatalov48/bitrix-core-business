create table if not exists b_vote_channel (
	ID int(18) not null auto_increment,
	SYMBOLIC_NAME varchar(255) not null,
	C_SORT int(18) default '100',
	FIRST_SITE_ID char(2),
	ACTIVE char(1) not null default 'Y',
	HIDDEN char(1) not null default 'N',
	TIMESTAMP_X datetime not null,
	TITLE varchar(255) not null,
	VOTE_SINGLE char(1) not null default 'Y',
	USE_CAPTCHA char(1) not null default 'N',
	primary key (ID));

create table if not exists b_vote_channel_2_group (
	ID int(18) not null auto_increment,
	CHANNEL_ID int(18) not null default '0',
	GROUP_ID int(18) not null default '0',
	PERMISSION int(18) not null default '0',
	primary key (ID),
	index IX_VOTE_CHANNEL_ID_GROUP_ID (CHANNEL_ID, GROUP_ID));

create table if not exists b_vote_channel_2_site (
	CHANNEL_ID int(18) not null default '0',
	SITE_ID char(2) not null,
	primary key (CHANNEL_ID, SITE_ID));

create table if not exists b_vote (
	ID int(18) not null auto_increment,
	CHANNEL_ID int(18) not null default '0',
	C_SORT int(18) default '100',
	ACTIVE char(1) not null default 'Y',
	NOTIFY char(1) not null default 'N',
	AUTHOR_ID int(18),
	TIMESTAMP_X datetime not null,
	DATE_START datetime not null,
	DATE_END datetime not null,
	URL varchar(255) NULL,
	COUNTER int(11) not null default '0',
	TITLE varchar(255),
	DESCRIPTION text,
	DESCRIPTION_TYPE varchar(4) not null default 'html',
	IMAGE_ID int(18),
	EVENT1 varchar(255),
	EVENT2 varchar(255),
	EVENT3 varchar(255),
	UNIQUE_TYPE int(18) not null default '2',
	KEEP_IP_SEC int(18),
	TEMPLATE varchar(255),
	RESULT_TEMPLATE varchar(255),
	primary key (ID),
	index IX_CHANNEL_ID (CHANNEL_ID));

create table if not exists b_vote_question (
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	TIMESTAMP_X datetime not null,
	VOTE_ID int(18) not null default '0',
	C_SORT int(18) default '100',
	COUNTER int(11) not null default '0',
	QUESTION text not null,
	QUESTION_TYPE varchar(4) not null default 'html',
	IMAGE_ID int(18),
	DIAGRAM char(1) not null default 'Y',
	REQUIRED char(1) not null default 'N',
	DIAGRAM_TYPE varchar(10) not null default 'histogram',
	TEMPLATE varchar(255),
	TEMPLATE_NEW varchar(255),
	primary key (ID),
	index IX_VOTE_ID (VOTE_ID));

create table if not exists b_vote_answer (
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	TIMESTAMP_X datetime not null,
	QUESTION_ID int(18) not null default '0',
	C_SORT int(18) default '100',
	MESSAGE text,
	MESSAGE_TYPE varchar(4) not null default 'html',
	COUNTER int(18) not null default '0',
	FIELD_TYPE int(5) not null default '0',
	FIELD_WIDTH int(18),
	FIELD_HEIGHT int(18),
	FIELD_PARAM varchar(255),
	COLOR varchar(7),
	primary key (ID),
	index IX_QUESTION_ID (QUESTION_ID));

create table if not exists b_vote_event (
	ID int(18) not null auto_increment,
	VOTE_ID int(18) not null default '0',
	VOTE_USER_ID int(18) not null default '0',
	DATE_VOTE datetime not null,
	STAT_SESSION_ID int(18),
	IP varchar(15),
	VALID char(1) not null default 'Y',
	primary key (ID),
	index IX_USER_ID (VOTE_USER_ID),
	index IX_B_VOTE_EVENT_2 (VOTE_ID,IP)
);

create table if not exists b_vote_event_question (
	ID int(18) not null auto_increment,
	EVENT_ID int(18) not null default '0',
	QUESTION_ID int(18) not null default '0',
	primary key (ID),
	index IX_EVENT_ID (EVENT_ID));

create table if not exists b_vote_event_answer (
	ID int(18) not null auto_increment,
	EVENT_QUESTION_ID int(18) not null default '0',
	ANSWER_ID int(18) not null default '0',
	MESSAGE text,
	primary key (ID),
	index IX_EVENT_QUESTION_ID (EVENT_QUESTION_ID));

create table if not exists b_vote_user (
	ID int(18) not null auto_increment,
	STAT_GUEST_ID int(18),
	AUTH_USER_ID int(18),
	COUNTER int(18) not null default '0',
	DATE_FIRST datetime not null,
	DATE_LAST datetime not null,
	LAST_IP varchar(15),
	primary key (ID));

create table if not exists b_vote_attached_object (
	ID int(11) not null auto_increment,
	OBJECT_ID int(11) not null,

	MODULE_ID varchar(32) not null,
	ENTITY_TYPE varchar(100) not null,
	ENTITY_ID int(11) not null,

	CREATE_TIME datetime not null,
	CREATED_BY int(11),

	PRIMARY KEY (ID),

	KEY IX_VOTE_AO_1 (OBJECT_ID),
	KEY IX_VOTE_AO_2 (MODULE_ID, ENTITY_TYPE, ENTITY_ID));
