create table if not exists b_ticket (
   ID int(11) not null auto_increment,
   SITE_ID char(2) not null,
   DATE_CREATE datetime,
   DAY_CREATE date,
   TIMESTAMP_X datetime,
   DATE_CLOSE datetime,
   AUTO_CLOSED char(1),
   AUTO_CLOSE_DAYS int(3),
   SLA_ID int(18) not null default '1',
   NOTIFY_AGENT_ID int(18),
   EXPIRE_AGENT_ID int(18),
   OVERDUE_MESSAGES int(18) not null default '0',
   IS_NOTIFIED char(1) not null default 'N',
   IS_OVERDUE char(1) not null default 'N',
   CATEGORY_ID int(18),
   CRITICALITY_ID int(18),
   STATUS_ID int(18),
   MARK_ID int(18),
   SOURCE_ID int(18),
   DIFFICULTY_ID int(18),
   TITLE varchar(255) not null,
   MESSAGES int(11) not null default '0',
   IS_SPAM char(1),
   OWNER_USER_ID int(11),
   OWNER_GUEST_ID int(11),
   OWNER_SID varchar(255),
   CREATED_USER_ID int(18),
   CREATED_GUEST_ID int(18),
   CREATED_MODULE_NAME varchar(255),
   RESPONSIBLE_USER_ID int(11),
   MODIFIED_USER_ID int(11),
   MODIFIED_GUEST_ID int(11),
   MODIFIED_MODULE_NAME varchar(255),
   LAST_MESSAGE_USER_ID int(18),
   LAST_MESSAGE_GUEST_ID int(18),
   LAST_MESSAGE_SID varchar(255),
   LAST_MESSAGE_BY_SUPPORT_TEAM char(1) not null default 'N',
   LAST_MESSAGE_DATE datetime,
   SUPPORT_COMMENTS varchar(255),
   PROBLEM_TIME int(18),
   HOLD_ON char(1) not null default 'N',
   REOPEN char(1) not null default 'N',
   COUPON varchar(255),
   SUPPORT_DEADLINE datetime,
   SUPPORT_DEADLINE_NOTIFY datetime,
   D_1_USER_M_AFTER_SUP_M datetime,
   ID_1_USER_M_AFTER_SUP_M int(18),
   DEADLINE_SOURCE_DATE datetime,
   primary key (ID),
   index IX_OWNER_ID (OWNER_USER_ID, TIMESTAMP_X),
   index IX_RESPONSIBLE_ID (RESPONSIBLE_USER_ID));

create table if not exists b_ticket_message (
   ID int(11) not null auto_increment,
   TIMESTAMP_X datetime,
   DATE_CREATE datetime,
   DAY_CREATE date,
   C_NUMBER int(11),
   TICKET_ID int(11) not null default '0',
   IS_HIDDEN char(1) not null default 'N',
   IS_LOG char(1) not null default 'N',
   IS_OVERDUE char(1) not null default 'N',
   CURRENT_RESPONSIBLE_USER_ID int(18),
   NOTIFY_AGENT_DONE char(1) not null default 'N',
   EXPIRE_AGENT_DONE char(1) not null default 'N',
   MESSAGE longtext,
   MESSAGE_SEARCH longtext,
   IS_SPAM char(1),
   EXTERNAL_ID int(18),
   EXTERNAL_FIELD_1 text,
   OWNER_USER_ID int(11),
   OWNER_GUEST_ID int(11),
   OWNER_SID text,
   SOURCE_ID int(18),
   CREATED_USER_ID int(18),
   CREATED_GUEST_ID int(18),
   CREATED_MODULE_NAME varchar(255),
   MODIFIED_USER_ID int(18),
   MODIFIED_GUEST_ID int(18),
   MESSAGE_BY_SUPPORT_TEAM char(1),
   TASK_TIME int(18),
   NOT_CHANGE_STATUS char(1) not null default 'N',
   primary key (ID),
   index IX_TICKET_ID (TICKET_ID));

create table if not exists b_ticket_message_2_file (
   ID int(18) not null auto_increment,
   HASH varchar(255),
   MESSAGE_ID int(18) not null default '0',
   FILE_ID int(18) not null default '0',
   TICKET_ID int(18) not null default '0',
   EXTENSION_SUFFIX varchar(255),
   primary key (ID),
   unique IX_HASH (HASH),
   index IX_MESSAGE_ID (MESSAGE_ID),
   index IX_TICKET_ID (TICKET_ID));

create table if not exists b_ticket_dictionary (
   ID int(11) not null auto_increment,
   FIRST_SITE_ID char(2),
   C_TYPE varchar(5) not null,
   SID varchar(255),
   SET_AS_DEFAULT char(1),
   C_SORT int(11) default '100',
   NAME varchar(255) not null,
   DESCR text,
   RESPONSIBLE_USER_ID int(11),
   EVENT1 varchar(255) default 'ticket',
   EVENT2 varchar(255),
   EVENT3 varchar(255),
   primary key (ID));

create table b_ticket_dictionary_2_site (
   DICTIONARY_ID int(18) not null default '0',
   SITE_ID char(2) not null,
   primary key (DICTIONARY_ID, SITE_ID));

create table if not exists b_ticket_online (
   ID int(18) not null auto_increment,
   TIMESTAMP_X datetime,
   TICKET_ID int(18),
   USER_ID int(18),
   CURRENT_MODE varchar(20),
   primary key (ID),
   index IX_TICKET_ID (TICKET_ID));

create table if not exists b_ticket_sla (
   ID int(18) not null auto_increment,
   PRIORITY int(18) not null default '0',
   FIRST_SITE_ID varchar(5),
   NAME varchar(255) not null,
   DESCRIPTION text,
   RESPONSE_TIME int(18),
   RESPONSE_TIME_UNIT varchar(10) not null default 'hour',
   NOTICE_TIME int(18),
   NOTICE_TIME_UNIT varchar(10) not null default 'hour',
   RESPONSIBLE_USER_ID int(18),
   DATE_CREATE datetime,
   CREATED_USER_ID int(18),
   CREATED_GUEST_ID int(18),
   DATE_MODIFY datetime,
   MODIFIED_USER_ID int(18),
   MODIFIED_GUEST_ID int(18),
   TIMETABLE_ID int(18),
   DEADLINE_SOURCE varchar(50) null,
   primary key (ID));

create table b_ticket_sla_2_site (
  SLA_ID int(18) not null,  
  SITE_ID varchar(5) not null,  
  primary key (SLA_ID, SITE_ID));

create table if not exists b_ticket_sla_2_category (
   SLA_ID int(18) not null default '0',
   CATEGORY_ID int(18) not null default '0',
   primary key (SLA_ID, CATEGORY_ID));

create table if not exists b_ticket_sla_2_criticality (
   SLA_ID int(18) not null default '0',
   CRITICALITY_ID int(18) not null default '0',
   primary key (SLA_ID, CRITICALITY_ID));

create table if not exists b_ticket_sla_2_mark (
   SLA_ID int(18) not null default '0',
   MARK_ID int(18) not null default '0',
   primary key (SLA_ID, MARK_ID));

create table if not exists b_ticket_sla_2_user_group (
   SLA_ID int(18) not null default '0',
   GROUP_ID int(18) not null default '0',
   primary key (SLA_ID, GROUP_ID));

create table if not exists b_ticket_sla_shedule (
   ID int(18) not null auto_increment,
   SLA_ID int(18) not null default '0',
   WEEKDAY_NUMBER int(2) not null default '0',
   OPEN_TIME varchar(10) not null default '24H',
   MINUTE_FROM int(18),
   MINUTE_TILL int(18),
   TIMETABLE_ID int(18),
   primary key (ID),
   index IX_SLA_ID (SLA_ID));

create table if not exists b_ticket_ugroups (
  ID int(11) NOT NULL auto_increment,
  NAME varchar(255) NOT NULL default '',
  XML_ID varchar(255) default NULL,
  SORT int(11) NOT NULL default '100',
  IS_TEAM_GROUP char(1) NOT NULL default 'N',
  PRIMARY KEY  (ID));

create table if not exists b_ticket_user_ugroup (
  USER_ID int(11) NOT NULL default '0',
  GROUP_ID int(11) NOT NULL default '0',
  CAN_VIEW_GROUP_MESSAGES char(1) NOT NULL default 'N',
  CAN_MAIL_GROUP_MESSAGES char(1) NOT NULL default 'N',
  CAN_MAIL_UPDATE_GROUP_MESSAGES char(1) NOT NULL default 'N',
  PRIMARY KEY  (GROUP_ID,USER_ID));

create table if not exists b_ticket_supercoupons (
  ID int(10) unsigned NOT NULL auto_increment,
  COUNT_TICKETS int(11) NOT NULL default '0',
  COUPON varchar(255) NOT NULL default '',
  TIMESTAMP_X datetime NOT NULL default '0000-00-00 00:00:00',
  DATE_CREATE datetime NOT NULL default '0000-00-00 00:00:00',
  CREATED_USER_ID int(11) default NULL,
  UPDATED_USER_ID int(11) default NULL,
  ACTIVE char(1) NOT NULL default 'Y',
  ACTIVE_FROM date default NULL,
  ACTIVE_TO date default NULL,
  SLA_ID int(11) default NULL,
  COUNT_USED int(11) NOT NULL default '0',
  PRIMARY KEY  (ID),
  UNIQUE KEY IX_COUPON (COUPON)
);

create table if not exists b_ticket_supercoupons_log (
  TIMESTAMP_X datetime NOT NULL default '0000-00-00 00:00:00',
  COUPON_ID int(11) NOT NULL default '0',
  USER_ID int(11) default NULL,
  SUCCESS char(1) NOT NULL default 'N',
  AFTER_COUNT int(11) NOT NULL default '0',
  SESSION_ID int(11) default NULL,
  GUEST_ID int(11) default NULL,
  AFFECTED_ROWS int(11) default NULL,
  COUPON varchar(255) default NULL,
  KEY IX_COUPON_ID (COUPON_ID)
);

CREATE TABLE b_ticket_timetable
(
ID INT(18) not null auto_increment,
NAME varchar(255) not null,
DESCRIPTION text,
PRIMARY KEY (ID)
);
	
CREATE TABLE b_ticket_holidays
(
ID INT(18) not null auto_increment,
NAME varchar(255) not null,
DESCRIPTION text,
OPEN_TIME  varchar(10) not null default 'HOLIDAY',
DATE_FROM datetime not null,
DATE_TILL datetime not null,
PRIMARY KEY (ID)
);

CREATE TABLE b_ticket_sla_2_holidays
(
SLA_ID INT(18) not null,
HOLIDAYS_ID INT(18) not null
);

CREATE TABLE b_ticket_search
(
TICKET_ID INT(18) not null,
SEARCH_WORD varchar(70) not null,
index IX_B_TICKET_SEARCH (SEARCH_WORD, TICKET_ID),
index IX_B_TICKET_SEARCH_T (TICKET_ID)
);

CREATE TABLE b_ticket_timetable_cache
(
ID INT(18) not null auto_increment,
SLA_ID INT(18) not null,
DATE_FROM datetime not null,
DATE_TILL datetime not null,
W_TIME INT(18) not null,
W_TIME_INC INT(18) not null,
PRIMARY KEY (ID),
index IX_B_TICKET_TIMETABLE_CACHE_S (SLA_ID)
);
