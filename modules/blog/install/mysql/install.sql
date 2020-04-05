create table b_blog_site_path
(
  ID int not null auto_increment
,  SITE_ID char(2) not null
,  PATH varchar(255) not null
,  TYPE char(1) null
,  primary key (ID)
,  unique IX_BLOG_SITE_PATH_2(SITE_ID, TYPE)
);

create table b_blog
(
  ID int not null auto_increment
,  NAME varchar(255) not null
,  DESCRIPTION text null
,  DATE_CREATE datetime not null
,  DATE_UPDATE datetime not null
,  ACTIVE char(1) not null default 'Y'
,  OWNER_ID int null
,  SOCNET_GROUP_ID int null
,  URL varchar(255) not null
,  REAL_URL varchar(255) null
,  GROUP_ID int not null
,  ENABLE_COMMENTS char(1) not null default 'Y'
,  ENABLE_IMG_VERIF char(1) not null default 'N'
,  ENABLE_RSS char(1) not null default 'Y'
,  LAST_POST_ID int null
,  LAST_POST_DATE datetime null
,  AUTO_GROUPS varchar(255) null
,  EMAIL_NOTIFY char(1) not null default 'Y'
,  ALLOW_HTML CHAR( 1 ) NOT NULL DEFAULT 'N'
,  SEARCH_INDEX CHAR( 1 ) NOT NULL DEFAULT 'Y'
,  USE_SOCNET CHAR( 1 ) NOT NULL DEFAULT 'N'
,  primary key (ID)
,  index IX_BLOG_BLOG_1(GROUP_ID, ACTIVE)
,  index IX_BLOG_BLOG_2(OWNER_ID)
,  unique IX_BLOG_BLOG_4(URL)
,  index IX_BLOG_BLOG_5(LAST_POST_DATE)
,  index IX_BLOG_BLOG_6(SOCNET_GROUP_ID)
,  EDITOR_USE_FONT CHAR( 1 ) NULL DEFAULT 'N'
,  EDITOR_USE_LINK CHAR( 1 ) NULL DEFAULT 'N'
,  EDITOR_USE_IMAGE CHAR( 1 ) NULL DEFAULT 'N'
,  EDITOR_USE_VIDEO CHAR( 1 ) NULL DEFAULT 'N'
,  EDITOR_USE_FORMAT CHAR( 1 ) NULL DEFAULT 'N'
);

create table b_blog_group
(
  ID int not null auto_increment
,  NAME varchar(255) not null
,  SITE_ID char(2) not null
,  primary key (ID)
,  index IX_BLOG_GROUP_1(SITE_ID)
);

create table b_blog_post
(
  ID int not null auto_increment
,  TITLE varchar(255) not null
,  BLOG_ID int not null
,  AUTHOR_ID int not null
,  PREVIEW_TEXT text null
,  PREVIEW_TEXT_TYPE char(4) not null default 'text'
,  DETAIL_TEXT mediumtext not null
,  DETAIL_TEXT_TYPE char(4) not null default 'text'
,  DATE_CREATE datetime not null
,  DATE_PUBLISH datetime not null
,  KEYWORDS varchar(255) null
,  PUBLISH_STATUS char(1) not null default 'P'
,  CATEGORY_ID varchar(255) null
,  ATRIBUTE varchar(255) null
,  ENABLE_TRACKBACK char(1) not null default 'Y'
,  ENABLE_COMMENTS char(1) not null default 'Y'
,  ATTACH_IMG int null
,  NUM_COMMENTS int not null default '0'
,  NUM_COMMENTS_ALL int not null default '0'
,  NUM_TRACKBACKS int not null default '0'
,  VIEWS int null
,  FAVORITE_SORT int null
,  PATH varchar(255) null
,  CODE varchar(255) null
,  MICRO char(1) not null default 'N'
,  HAS_IMAGES VARCHAR(1) default NULL
,  HAS_PROPS VARCHAR(1) default NULL
,  HAS_TAGS VARCHAR(1) default NULL
,  HAS_COMMENT_IMAGES VARCHAR(1) default NULL
,  HAS_SOCNET_ALL VARCHAR(1) default NULL
,  SEO_TITLE varchar(255) null
,  SEO_TAGS varchar(255) null
,  SEO_DESCRIPTION text null
,  primary key (ID)
,  index IX_BLOG_POST_1(BLOG_ID, PUBLISH_STATUS, DATE_PUBLISH)
,  index IX_BLOG_POST_2(BLOG_ID, DATE_PUBLISH, PUBLISH_STATUS)
,  index IX_BLOG_POST_3(BLOG_ID, CATEGORY_ID)
,  index IX_BLOG_POST_4(PUBLISH_STATUS, DATE_PUBLISH)
,  index IX_BLOG_POST_5(DATE_PUBLISH, AUTHOR_ID)
,  index IX_BLOG_POST_CODE(BLOG_ID, CODE)
,  index IX_BLOG_POST_6(CODE)
);

create table b_blog_category
(
  ID int not null auto_increment
,  BLOG_ID int not null
,  NAME varchar(255) not null
,  primary key (ID)
,  unique IX_BLOG_CAT_1(BLOG_ID, NAME)
);

create table b_blog_comment
(
  ID int not null auto_increment
,  BLOG_ID int not null
,  POST_ID int not null
,  PARENT_ID int null
,  AUTHOR_ID int null
,  ICON_ID int null
,  AUTHOR_NAME varchar(255) null
,  AUTHOR_EMAIL varchar(255) null
,  AUTHOR_IP varchar(20) null
,  AUTHOR_IP1 varchar(20) null
,  DATE_CREATE datetime not null
,  TITLE varchar(255) null
,  POST_TEXT text not null
,  PUBLISH_STATUS char(1) not null default 'P'
,  HAS_PROPS varchar(1) default null
,  SHARE_DEST varchar(255) null
,  PATH varchar(255) null
,  primary key (ID)
,  index IX_BLOG_COMM_1(BLOG_ID, POST_ID)
,  index IX_BLOG_COMM_2(AUTHOR_ID)
,  index IX_BLOG_COMM_3(DATE_CREATE, AUTHOR_ID)
,  index IX_BLOG_COMM_4(POST_ID)
);

create table b_blog_user
(
  ID int not null auto_increment
,  USER_ID int not null
,  ALIAS varchar(255) null
,  DESCRIPTION text null
,  AVATAR int null
,  INTERESTS varchar(255) null
,  LAST_VISIT datetime null
,  DATE_REG datetime not null
,  ALLOW_POST char(1) not null default 'Y'
,  primary key (ID)
,  unique IX_BLOG_USER_1(USER_ID)
,  index IX_BLOG_USER_2(ALIAS)
);

create table b_blog_user_group
(
  ID int not null auto_increment
,  BLOG_ID int null
,  NAME varchar(255) not null
,  primary key (ID)
,  index IX_BLOG_USER_GROUP_1(BLOG_ID)
);

INSERT INTO b_blog_user_group(ID, BLOG_ID, NAME) VALUES(1, null, "all");
INSERT INTO b_blog_user_group(ID, BLOG_ID, NAME) VALUES(2, null, "registered");

create table b_blog_user2user_group
(
  ID int not null auto_increment
,  USER_ID int not null
,  BLOG_ID int not null
,  USER_GROUP_ID int not null
,  primary key (ID)
,  unique IX_BLOG_USER2GROUP_1(USER_ID, BLOG_ID, USER_GROUP_ID)
);

create table b_blog_user_group_perms
(
  ID int not null auto_increment
,  BLOG_ID int not null
,  USER_GROUP_ID int not null
,  PERMS_TYPE char(1) not null default 'P'
,  POST_ID int null
,  PERMS char(1) not null default 'D'
,  AUTOSET char(1) not null default 'N'
,  primary key (ID)
,  unique IX_BLOG_UG_PERMS_1(BLOG_ID, USER_GROUP_ID, PERMS_TYPE, POST_ID)
,  index IX_BLOG_UG_PERMS_2(USER_GROUP_ID, PERMS_TYPE, POST_ID)
,  index IX_BLOG_UG_PERMS_3(POST_ID, USER_GROUP_ID, PERMS_TYPE)
);

create table b_blog_user2blog
(
  ID int not null auto_increment
,  USER_ID int not null
,  BLOG_ID int not null
,  primary key (ID)
,  unique IX_BLOG_USER2GROUP_1(BLOG_ID, USER_ID)
);

create table b_blog_trackback
(
  ID int not null auto_increment
,  TITLE varchar(255) not null
,  URL varchar(255) not null
,  PREVIEW_TEXT text not null
,  BLOG_NAME varchar(255) null
,  POST_DATE datetime not null
,  BLOG_ID int not null
,  POST_ID int not null
,  primary key (ID)
,  index IX_BLOG_TRBK_1(BLOG_ID, POST_ID)
,  index IX_BLOG_TRBK_2(POST_ID)
);

CREATE TABLE `b_blog_image` (
  ID int(11) NOT NULL auto_increment,
  FILE_ID int(11) NOT NULL default '0',
  BLOG_ID int(11) NOT NULL default '0',
  POST_ID int(11) NOT NULL default '0',
  USER_ID int(11) NOT NULL default '0',
  TIMESTAMP_X datetime NOT NULL default '1970-01-01 00:00:01',
  TITLE varchar(255),
  IMAGE_SIZE int(11) NOT NULL default '0',
  IS_COMMENT VARCHAR(1) NOT NULL DEFAULT 'N',
  COMMENT_ID INTEGER DEFAULT NULL,
  PRIMARY KEY  (`ID`),
  index IX_BLOG_IMAGE_1 (POST_ID, BLOG_ID)
);

create table b_blog_post_category (  
	ID int not null auto_increment,  
	BLOG_ID int not null,  
	POST_ID int not null,  
	CATEGORY_ID int not null,  
	primary key (ID),  
	unique IX_BLOG_POST_CATEGORY(POST_ID, CATEGORY_ID),
	index IX_BLOG_POST_CATEGORY_CAT_ID(CATEGORY_ID)
);

CREATE TABLE b_blog_socnet (
  ID int(11) NOT NULL AUTO_INCREMENT,
  BLOG_ID int(11) NOT NULL,
  PRIMARY KEY (ID),
  unique IX_BLOG_SOCNET(BLOG_ID)
);

CREATE TABLE b_blog_socnet_rights (
  ID int(11) NOT NULL AUTO_INCREMENT,
  POST_ID int(11) NOT NULL,
  ENTITY_TYPE varchar(45) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  ENTITY varchar(45) NOT NULL,
  PRIMARY KEY (ID),
  index IX_BLOG_SR_1(POST_ID)
);

CREATE TABLE b_blog_post_param (
  ID int NOT NULL AUTO_INCREMENT,
  POST_ID int,
  USER_ID int,
  NAME varchar(50) NOT NULL,
  VALUE varchar(255) NOT NULL,
  PRIMARY KEY (ID),
  index IX_BLOG_PP_1(POST_ID, USER_ID),
  index IX_BLOG_PP_2(USER_ID)
);