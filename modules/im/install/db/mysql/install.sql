CREATE TABLE `b_im_chat`
(
	`ID` int(18) not null auto_increment,
	`PARENT_ID` int(18) null DEFAULT 0,
	`PARENT_MID` int(18) null DEFAULT 0,
	`TITLE` varchar(255) null,
	`DESCRIPTION` text null,
	`COLOR` varchar(255) null,
	`TYPE` char(1) null,
	`EXTRANET` char(1) null,
	`AUTHOR_ID` int(18) not null,
	`AVATAR` int(18) null,
	`PIN_MESSAGE_ID` int(18) null DEFAULT 0,
	`CALL_TYPE` smallint(1) DEFAULT 0,
	`CALL_NUMBER` varchar(20) null,
	`ENTITY_TYPE` varchar(50) null,
	`ENTITY_ID` varchar(255) null,
	`ENTITY_DATA_1` varchar(255) null,
	`ENTITY_DATA_2` varchar(255) null,
	`ENTITY_DATA_3` varchar(255) null,
	`DISK_FOLDER_ID` int(18) null,
	`MESSAGE_COUNT` int(18) DEFAULT 0,
	`USER_COUNT` int(18) DEFAULT 0,
	`PREV_MESSAGE_ID` int(18) null,
	`LAST_MESSAGE_ID` int(18) null,
	`LAST_MESSAGE_STATUS` varchar(50) DEFAULT 'received',
	`DATE_CREATE` datetime null,
	`MANAGE_USERS` varchar(255) not null default 'ALL',
	`MANAGE_UI` varchar(255) not null default 'ALL',
	`MANAGE_SETTINGS` varchar(255) not null default 'OWNER',
	PRIMARY KEY (`ID`),
	KEY `IX_IM_CHAT_1` (`AUTHOR_ID`, `TYPE`),
	KEY `IX_IM_CHAT_2` (`ENTITY_TYPE`, `ENTITY_ID`, `AUTHOR_ID`),
	KEY `IX_IM_CHAT_3` (`CALL_NUMBER`, `AUTHOR_ID`),
	KEY `IX_IM_CHAT_4` (`TYPE`),
	KEY `IX_IM_CHAT_5` (`PARENT_ID`, `PARENT_MID`)
);

CREATE TABLE `b_im_chat_index`
(
	`CHAT_ID` int(11) not null,
	`SEARCH_CONTENT` mediumtext null,
	`SEARCH_TITLE` varchar(511) null,
	PRIMARY KEY (`CHAT_ID`),
	KEY `IX_IM_CHAT_INDEX_1` (`SEARCH_TITLE`)
);

CREATE TABLE `b_im_message`
(
	`ID` int(18) not null auto_increment,
	`CHAT_ID` int(18) not null,
	`AUTHOR_ID` int(18) not null,
	`MESSAGE` text null,
	`MESSAGE_OUT` text null,
	`DATE_CREATE` datetime not null,
	`EMAIL_TEMPLATE` varchar(255) null,
	`NOTIFY_TYPE` smallint(2) DEFAULT 0,
	`NOTIFY_MODULE` varchar(255) null,
	`NOTIFY_EVENT` varchar(255) null,
	`NOTIFY_TAG` varchar(255) null,
	`NOTIFY_SUB_TAG` varchar(255) null,
	`NOTIFY_TITLE` varchar(255) null,
	`NOTIFY_BUTTONS` text null,
	`NOTIFY_READ` char(1) DEFAULT 'N',
	`IMPORT_ID` int(18) null,
	PRIMARY KEY (`ID`),
	KEY `IX_IM_MESS_2` (`NOTIFY_TAG`, `AUTHOR_ID`),
	KEY `IX_IM_MESS_3` (`NOTIFY_SUB_TAG`, `AUTHOR_ID`),
	KEY `IX_IM_MESS_4` (`CHAT_ID`, `NOTIFY_READ`),
	KEY `IX_IM_MESS_5` (`CHAT_ID`, `DATE_CREATE`),
	KEY `IX_IM_MESS_6` (`AUTHOR_ID`),
	KEY `IX_IM_MESS_8` (`NOTIFY_TYPE`, `DATE_CREATE`),
	KEY `IX_IM_MESS_9` (`CHAT_ID`) -- perf. for use (CHAT_ID, PK) index
);

CREATE TABLE `b_im_message_index`
(
	`MESSAGE_ID` int(11) not null,
	`SEARCH_CONTENT` mediumtext null,
	PRIMARY KEY (`MESSAGE_ID`)
);

CREATE TABLE `b_im_message_param`
(
	`ID` int(18) not null auto_increment,
	`MESSAGE_ID` INT(11) not null,
	`PARAM_NAME` varchar(100) not null,
	`PARAM_VALUE` varchar(100) null,
	`PARAM_JSON` text null,
	PRIMARY KEY (ID),
	KEY `IX_B_IM_MESSAGE_PARAM_1` (`MESSAGE_ID`, `PARAM_NAME`),
	KEY `IX_B_IM_MESSAGE_PARAM_2` (`PARAM_NAME`, `PARAM_VALUE`(50), `MESSAGE_ID`)
);

CREATE TABLE `b_im_message_favorite`
(
	`ID` int(18) not null auto_increment,
	`USER_ID` int(18) not null,
	`CHAT_ID` INT(11) not null,
	`MESSAGE_ID` INT(11) not null,
	`DATE_CREATE` datetime not null,
	UNIQUE KEY `pk_b_im_message_favorite` (`ID`),
	KEY `IX_B_IM_MESSAGE_FAVORITE_1` (`USER_ID`, `DATE_CREATE` DESC),
	KEY `IX_B_IM_MESSAGE_FAVORITE_2` (`CHAT_ID`, `DATE_CREATE` DESC)
);

CREATE TABLE `b_im_status`
(
	`USER_ID` int(18) not null,
	`COLOR` varchar(255) null,
	`STATUS` varchar(50) default 'online',
	`STATUS_TEXT` varchar(255) null,
	`IDLE` datetime null,
	`DESKTOP_LAST_DATE` datetime null,
	`MOBILE_LAST_DATE` datetime null,
	`EVENT_ID` int(18) null,
	`EVENT_UNTIL_DATE` datetime null,
	`INVITED` char(1) default 'N',
	PRIMARY KEY (`USER_ID`),
	KEY `IX_IM_STATUS_EUD` (`EVENT_UNTIL_DATE`)
);

CREATE TABLE `b_im_relation`
(
	`ID` int(18) not null auto_increment,
	`CHAT_ID` int(18) not null,
	`MESSAGE_TYPE` char(1) default 'P',
	`USER_ID` int(18) not null,
	`START_ID` int(18) DEFAULT 0,
	`UNREAD_ID` int(18) DEFAULT 0,
	`LAST_ID` int(18) DEFAULT 0,
	`LAST_SEND_ID` int(18) DEFAULT 0,
	`LAST_FILE_ID` int(18) DEFAULT 0,
	`LAST_READ` datetime null,
	`STATUS` smallint(1) DEFAULT 0,
	`CALL_STATUS` smallint(1) DEFAULT 0,
	`MESSAGE_STATUS` varchar(50) DEFAULT 'received',
	`NOTIFY_BLOCK` char(1) DEFAULT 'N',
	`MANAGER` char(1) DEFAULT 'N',
	`COUNTER` int(18) DEFAULT 0,
	`START_COUNTER` int(18) DEFAULT 0,
	PRIMARY KEY (`ID`),
	KEY `IX_IM_REL_2` (`USER_ID`, `MESSAGE_TYPE`, `STATUS`),
	KEY `IX_IM_REL_3` (`USER_ID`, `MESSAGE_TYPE`, `CHAT_ID`),
	KEY `IX_IM_REL_4` (`USER_ID`, `STATUS`),
	KEY `IX_IM_REL_5` (`MESSAGE_TYPE`, `STATUS`),
	KEY `IX_IM_REL_6` (`CHAT_ID`, `USER_ID`),
	KEY `IX_IM_REL_8` (`STATUS`, `COUNTER`)
);

CREATE TABLE `b_im_recent`
(
	`USER_ID` int(18) not null,
	`ITEM_TYPE` char(1) default 'P' not null,
	`ITEM_ID` int(18) not null,
	`ITEM_MID` int(18) not null,
	`ITEM_CID` int(18) DEFAULT 0,
	`ITEM_RID` int(18) DEFAULT 0,
	`ITEM_OLID` int(18) DEFAULT 0,
	`PINNED` char(1) DEFAULT 'N',
	`UNREAD` char(1) DEFAULT 'N',
	`DATE_MESSAGE` datetime null,
	`DATE_UPDATE` datetime null,
	`MARKED_ID` int(18) null,
	PRIMARY KEY (`USER_ID`, `ITEM_TYPE`, `ITEM_ID`),
	KEY `IX_IM_REC_1` (`ITEM_TYPE`, `ITEM_ID`),
	KEY `IX_IM_REC_2` (`DATE_UPDATE`),
	KEY `IX_IM_REC_3` (`ITEM_RID`),
	KEY `IX_IM_REC_4` (`ITEM_MID`)
);

CREATE TABLE `b_im_last_search`
(
	`ID` int(18) not null auto_increment,
	`USER_ID` int(18) not null,
	`DIALOG_ID` varchar(50) not null,
	`ITEM_CID` int(18) not null DEFAULT 0,
	`ITEM_RID` int(18) not null DEFAULT 0,
	PRIMARY KEY `PK_B_IM_LAST_SEARCH` (`ID` DESC),
	KEY `IX_IM_LS_2` (`USER_ID`, `DIALOG_ID`)
);

CREATE TABLE `b_im_bot`
(
	`BOT_ID` int(18) not null,
	`MODULE_ID` varchar(50) not null,
	`CODE` varchar(50) not null,
	`TYPE` char(1) default 'B' not null,
	`CLASS` varchar(255),
	`LANG` varchar(50) default '',
	`METHOD_BOT_DELETE` varchar(255),
	`METHOD_MESSAGE_ADD` varchar(255),
	`METHOD_MESSAGE_UPDATE` varchar(255),
	`METHOD_MESSAGE_DELETE` varchar(255),
	`METHOD_WELCOME_MESSAGE` varchar(255),
	`TEXT_PRIVATE_WELCOME_MESSAGE` text,
	`TEXT_CHAT_WELCOME_MESSAGE` text,
	`COUNT_COMMAND` int(18) DEFAULT 0,
	`COUNT_MESSAGE` int(18) DEFAULT 0,
	`COUNT_CHAT` int(18) DEFAULT 0,
	`COUNT_USER` int(18)  DEFAULT 0,
	`APP_ID` varchar(128) null,
	`VERIFIED` char(1) DEFAULT 'N',
	`OPENLINE` char(1) DEFAULT 'N',
	PRIMARY KEY `PK_B_IM_BOT` (`BOT_ID`)
);

CREATE TABLE `b_im_bot_chat`
(
	`ID` int(18) not null auto_increment,
	`BOT_ID` int(18) not null,
	`CHAT_ID` int(18) not null,
	PRIMARY KEY `PK_B_IM_BOT_CHAT` (`ID`),
	KEY `IX_IM_BC_1` (`BOT_ID`, `CHAT_ID`)
);

CREATE TABLE `b_im_bot_token`
(
	`ID` int(18) not null auto_increment,
	`TOKEN` varchar(32) null,
	`DATE_CREATE` datetime not null,
	`DATE_EXPIRE` datetime null,
	`BOT_ID` int(18) default 0,
	`DIALOG_ID` varchar(255) not null,
	PRIMARY KEY `PK_B_IM_BOT_TOKEN` (`ID`),
	KEY `IX_IM_BOT_TOKEN_1` (`DATE_EXPIRE`, `BOT_ID`),
	KEY `IX_IM_BOT_TOKEN_2` (`TOKEN`)
);

CREATE TABLE `b_im_command`
(
	`ID` int(18) not null auto_increment,
	`MODULE_ID` varchar(50) not null,
	`BOT_ID` int(18) default 0,
	`APP_ID` varchar(128) null,
	`COMMAND` varchar(255) not null,
	`COMMON` char(1) default 'N',
	`HIDDEN` char(1) default 'N',
	`EXTRANET_SUPPORT` char(1) default 'N',
	`SONET_SUPPORT` char(1) default 'N',
	`CLASS` varchar(255) null,
	`METHOD_COMMAND_ADD` varchar(255) null,
	`METHOD_LANG_GET` varchar(255) null,
	PRIMARY KEY `PK_B_IM_COMMAND` (`ID`),
	KEY `IX_IM_COMMAND_1` (`BOT_ID`)
);

CREATE TABLE `b_im_command_lang`
(
	`ID` int(18) not null auto_increment,
	`COMMAND_ID` int(18) not null,
	`LANGUAGE_ID` char(2) not null,
	`TITLE` varchar(255) null,
	`PARAMS` varchar(255) null,
	PRIMARY KEY `PK_B_IM_COMMAND_LANG` (`ID`),
	UNIQUE `UX_B_IM_COMMAND_LANG` (`COMMAND_ID`, `LANGUAGE_ID`)
);

CREATE TABLE `b_im_app`
(
	`ID` int(18) not null auto_increment,
	`MODULE_ID` varchar(50) not null,
	`BOT_ID` int(18) default 0,
	`APP_ID` varchar(128) null,
	`CODE` varchar(255) not null,
	`HASH` varchar(32) null,
	`REGISTERED` varchar(32) default 'N',
	`ICON_FILE_ID` int(18) null,
	`CONTEXT` varchar(128) null,
	`IFRAME` varchar(255) null,
	`IFRAME_WIDTH` int(18) null,
	`IFRAME_HEIGHT` int(18) null,
	`IFRAME_POPUP` char(1) default 'N',
	`JS` varchar(255) null,
	`HIDDEN` char(1) default 'N',
	`EXTRANET_SUPPORT` char(1) default 'N',
	`LIVECHAT_SUPPORT` char(1) default 'N',
	`CLASS` varchar(255) null,
	`METHOD_LANG_GET` varchar(255) null,
	PRIMARY KEY `PK_B_IM_APP` (`ID`),
	KEY `IX_IM_APP_1` (`BOT_ID`)
);

CREATE TABLE `b_im_app_lang`
(
	`ID` int(18) not null auto_increment,
	`APP_ID` int(18) not null,
	`LANGUAGE_ID` char(2) not null,
	`TITLE` varchar(255) null,
	`DESCRIPTION` varchar(255) null,
	`COPYRIGHT` varchar(255) null,
	PRIMARY KEY `PK_B_APP_LANG` (`ID`),
	UNIQUE `UX_B_APP_LANG` (`APP_ID`, `LANGUAGE_ID`)
);

CREATE TABLE `b_im_alias`
(
	`ID` int(18) not null auto_increment,
	`ALIAS` varchar(255) not null,
	`DATE_CREATE` datetime,
	`ENTITY_TYPE` varchar(255) not null,
	`ENTITY_ID` varchar(255) not null,
	PRIMARY KEY `PK_B_IM_ALIAS` (`ID`),
	UNIQUE `UX_B_IM_ALIAS` (`ALIAS`),
	INDEX `IX_IM_ALIAS_2` (`ENTITY_TYPE`(100), `ENTITY_ID`(100))
);

CREATE TABLE `b_im_external_avatar`
(
	`ID` int(11) not null auto_increment,
	`LINK_MD5` varchar(32) not null,
	`AVATAR_ID` int(11) not null,
	PRIMARY KEY `PK_B_IM_EXTERNAL_AVATAR` (`ID`),
	KEY `IX_IMOL_NA_1` (`LINK_MD5`)
);

CREATE TABLE `b_im_no_relation_permission_disk`
(
	`ID` int(11) not null auto_increment,
	`CHAT_ID` int(18) null,
	`USER_ID` int(18) null,
	`ACTIVE_TO` datetime null,
	PRIMARY KEY `PK_B_IM_NO_RELATION_PERMISSION_DISK` (`ID`),
	KEY `IX_IM_USER_ID_CHAT_ID` (`USER_ID`, `CHAT_ID`)
);

CREATE TABLE `b_im_call`
(
	`ID` int not null auto_increment,
	`TYPE` int,
	`INITIATOR_ID` int,
	`IS_PUBLIC` char(1) not null default 'N',
	`PUBLIC_ID` varchar(32),
	`PROVIDER` varchar(32),
	`ENTITY_TYPE` varchar(32),
	`ENTITY_ID` varchar(32),
	`PARENT_ID` int,
	`STATE` varchar(50),
	`START_DATE` datetime,
	`END_DATE` datetime,
	`CHAT_ID` int,
	`LOG_URL` varchar(2000),
	PRIMARY KEY `PK_B_IM_CALL`(`ID`),
	UNIQUE KEY `IX_B_IM_CALL_PID`(`PUBLIC_ID`),
	INDEX `IX_B_IM_CALL_ENT_ID_2`(`ENTITY_TYPE`, `ENTITY_ID`, `TYPE`, `PROVIDER`, `END_DATE`),
	INDEX `IX_B_IM_CALL_CHAT_ID`(`CHAT_ID`)
);

CREATE TABLE `b_im_call_user`
(
	`CALL_ID` int not null,
	`USER_ID` int not null,
	`STATE` varchar(50),
	`FIRST_JOINED` datetime,
	`LAST_SEEN` datetime,
	`IS_MOBILE` char(1),
	`SHARED_SCREEN` char(1),
	`RECORDED` char(1),
	PRIMARY KEY `PK_B_IM_CALL_USER`(`CALL_ID`, `USER_ID`)
);

CREATE TABLE `b_im_permission`
(
	`ID` int(18) not null auto_increment,
	`CHAT_ID` int(18) DEFAULT 0,
	`USER_ID` int(18) DEFAULT 0,
	`DATE_CREATE` datetime not null,
	`AUTHOR_ID` int(18) DEFAULT 0,
	`PERM_USER_PROMOTE` char(1) DEFAULT 'N',
	`PERM_CHAT_INFO` char(1) DEFAULT 'N',
	`PERM_USER_ADD` char(1) DEFAULT 'N',
	`PERM_USER_REMOVE` char(1) DEFAULT 'N',
	`PERM_MESSAGE_SEND` char(1) DEFAULT 'N',
	`PERM_MESSAGE_EDIT` char(1) DEFAULT 'N',
	`PERM_MESSAGE_DELETE` char(1) DEFAULT 'N',
	`PERM_MESSAGE_RICH` char(1) DEFAULT 'N',
	`PERM_MESSAGE_PIN` char(1) DEFAULT 'N',
	`PERM_MESSAGE_POLL` char(1) DEFAULT 'N',
	PRIMARY KEY `PK_B_IM_PERMISSION` (`ID`),
	KEY `IX_IM_PERM_1` (`CHAT_ID`, `USER_ID`)
);

CREATE TABLE `b_im_permission_duration`
(
	`ID` int(18) not null auto_increment,
	`PERMISSION_ID` int(18) DEFAULT 0,
	`DATE_REMOVE` datetime not null,
	PRIMARY KEY `PK_B_IM_PERMISSION_DURATION` (`ID`),
	KEY `IX_IM_PERM_DUR_1` (`PERMISSION_ID`),
	KEY `IX_IM_PERM_DUR_2` (`DATE_REMOVE`)
);

CREATE TABLE `b_im_permission_log`
(
	`ID` int(18) not null auto_increment,
	`CHAT_ID` int(18) DEFAULT 0,
	`USER_ID` int(18) not null,
	`TEXT` char(1) DEFAULT 'N',
	`DATE_CREATE` datetime not null,
	PRIMARY KEY `PK_B_IM_PERMISSION_LOG` (`ID`),
	KEY `IX_IM_PERM_LOG_1` (`CHAT_ID`, `USER_ID`),
	KEY `IX_IM_PERM_LOG_2` (`DATE_CREATE`)
);

CREATE TABLE `b_im_block_user`
(
	`ID` int(18) not null auto_increment,
	`CHAT_ID` int(18) not null,
	`USER_ID` int(18) not null,
	`BLOCK_DATE` datetime default null,
	PRIMARY KEY `PK_B_IM_BLOCK_USER` (`ID`)
);

CREATE TABLE `b_im_conference`
(
	`ID` int(18) not null auto_increment,
	`ALIAS_ID` int(18) not null,
	`PASSWORD` text,
	`INVITATION` text,
	`IS_BROADCAST` char(1) not null default 'N',
	`CONFERENCE_START` datetime,
	`CONFERENCE_END` datetime,
	PRIMARY KEY `PK_B_IM_CONFERENCE`(`ID`)
);

CREATE TABLE `b_im_conference_user_role`
(
	`CONFERENCE_ID` int not null,
	`USER_ID` int not null,
	`ROLE` varchar(64),
	PRIMARY KEY `PK_B_IM_CONFERENCE_USER_ROLE`(`CONFERENCE_ID`, `USER_ID`)
);

CREATE TABLE `b_im_option_group`
(
	`ID` INT UNSIGNED AUTO_INCREMENT,
	`NAME` varchar(255) null,
	`USER_ID` INT UNSIGNED null,
	`SORT` INT UNSIGNED not null,
	`DATE_CREATE` DATETIME not null,
	`CREATE_BY_ID` INT UNSIGNED not null,
	`DATE_MODIFY` DATETIME null,
	`MODIFY_BY_ID` INT UNSIGNED null,
	PRIMARY KEY `PK_B_IM_OPTION_GROUP` (`ID`)
);

CREATE TABLE `b_im_option_state`
(
	`GROUP_ID` INT UNSIGNED not null,
	`NAME` varchar(64) not null ,
	`VALUE` varchar(255) null,
	PRIMARY KEY `PK_B_IM_OPTION_STATE` (`GROUP_ID`, `NAME`)
);

CREATE TABLE `b_im_option_access`
(
	`ID` INT UNSIGNED AUTO_INCREMENT,
	`GROUP_ID` INT UNSIGNED not null,
	`ACCESS_CODE` varchar(100),
	PRIMARY KEY `PK_B_IM_OPTION_ACCESS` (`ID`),
	UNIQUE INDEX `UX_B_IM_OPTION_ACCESS_1` (`GROUP_ID`, `ACCESS_CODE`)
);

CREATE TABLE `b_im_option_user`
(
	`USER_ID` INT UNSIGNED not null,
	`NOTIFY_GROUP_ID` INT UNSIGNED not null,
	`GENERAL_GROUP_ID` INT UNSIGNED not null,
	PRIMARY KEY `PK_B_IM_OPTION_USER` (`USER_ID`)
);

CREATE TABLE `b_im_message_uuid`
(
	`UUID` varchar(36) not null,
	`MESSAGE_ID` INT UNSIGNED null,
	`DATE_CREATE` DATETIME not null,
	PRIMARY KEY `PK_B_IM_MESSAGE_UUID` (`UUID`),
	INDEX `UX_B_IM_MESSAGE_DATE_CREATE` (`DATE_CREATE`),
	INDEX `UX_B_IM_MESSAGE_UUID_MESSAGE_ID` (`MESSAGE_ID`)
);



CREATE TABLE `b_im_message_viewed`
(
	`ID` INT UNSIGNED AUTO_INCREMENT,
	`USER_ID` INT not null,
	`CHAT_ID` INT not null,
	`MESSAGE_ID` INT not null,
	`DATE_CREATE` DATETIME not null DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ID`),
	UNIQUE INDEX `UX_IM_MESS_VIEWED` (`USER_ID`, `CHAT_ID`, `MESSAGE_ID`),
	INDEX `IX_IM_MESS_VIEWED_1` (`MESSAGE_ID`, `USER_ID`, `DATE_CREATE`),
	INDEX `IX_IM_MESS_VIEWED_2` (`MESSAGE_ID`, `ID`),
	INDEX `IX_IM_MESS_VIEWED_3` (`DATE_CREATE`)
);

CREATE TABLE `b_im_message_unread`
(
	`ID` INT UNSIGNED AUTO_INCREMENT,
	`USER_ID` INT not null,
	`CHAT_ID` INT not null,
	`MESSAGE_ID` INT not null,
	`IS_MUTED` CHAR(1) not null,
	`CHAT_TYPE` CHAR(1) not null,
	`DATE_CREATE` DATETIME not null DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ID`),
	UNIQUE INDEX `UX_IM_MESS_UNREAD` (`USER_ID`, `CHAT_ID`, `MESSAGE_ID`),
	INDEX `IX_IM_MESS_UNREAD_1` (`DATE_CREATE`),
	INDEX `IX_IM_MESS_UNREAD_2` (`USER_ID`, `CHAT_ID`, `CHAT_TYPE`, `IS_MUTED`),
	INDEX `IX_IM_MESS_UNREAD_3` (`CHAT_ID`, `CHAT_TYPE`, `IS_MUTED`),
	INDEX `IX_IM_MESS_UNREAD_4` (`CHAT_ID`, `USER_ID`, `MESSAGE_ID`),
	INDEX `IX_IM_MESS_UNREAD_5` (`MESSAGE_ID`, `USER_ID`),
	INDEX `IX_IM_MESS_UNREAD_6` (`CHAT_TYPE`, `MESSAGE_ID`)
);

CREATE TABLE `b_im_link_url`
(
	`ID` INT not null AUTO_INCREMENT,
	`MESSAGE_ID` INT(11) DEFAULT null,
	`CHAT_ID` INT(11) DEFAULT null,
	`URL` varchar(2000) DEFAULT null,
	`PREVIEW_URL_ID` INT(11) DEFAULT null,
	`DATE_CREATE` DATETIME not null,
	`AUTHOR_ID` INT(11) not null,
	`IS_INDEXED` CHAR(1) not null DEFAULT 'N',
	PRIMARY KEY (`ID`),
	KEY `IX_B_IM_LINK_URL_1` (`CHAT_ID`, `AUTHOR_ID`, `DATE_CREATE`, `MESSAGE_ID`),
	KEY `IX_B_IM_LINK_URL_2` (`CHAT_ID`, `DATE_CREATE`, `MESSAGE_ID`),
	KEY `IX_B_IM_LINK_URL_3` (`MESSAGE_ID`),
	KEY `IX_B_IM_LINK_URL_4` (`IS_INDEXED`)
);

CREATE TABLE `b_im_link_url_index`
(
	`URL_ID` INT not null,
	`SEARCH_CONTENT` TEXT null,
	PRIMARY KEY (`URL_ID`)
);

CREATE TABLE `b_im_link_file`
(
	`ID` INT not null AUTO_INCREMENT,
	`MESSAGE_ID` INT(11) DEFAULT null,
	`CHAT_ID` INT(11) DEFAULT null,
	`SUBTYPE` varchar(50) null,
	`DISK_FILE_ID` INT(11) DEFAULT null,
	`DATE_CREATE` DATETIME not null,
	`AUTHOR_ID` INT(11) not null,
	PRIMARY KEY (`ID`),
	KEY `IX_B_IM_LINK_FILE_1` (`CHAT_ID`, `SUBTYPE`, `ID`),
	KEY `IX_B_IM_LINK_FILE_2` (`CHAT_ID`, `SUBTYPE`, `AUTHOR_ID`, `ID`),
	KEY `IX_B_IM_LINK_FILE_3` (`MESSAGE_ID`, `ID`),
	KEY `IX_B_IM_LINK_FILE_4` (`DISK_FILE_ID`),
	KEY `IX_B_IM_LINK_FILE_5` (`CHAT_ID`, `SUBTYPE`, `DATE_CREATE`, `ID`)
);

CREATE TABLE `b_im_link_task`
(
	`ID` INT AUTO_INCREMENT,
	`MESSAGE_ID` INT null,
	`CHAT_ID` INT null,
	`TASK_ID` INT null,
	`AUTHOR_ID` INT null,
	`DATE_CREATE` DATETIME not null,
	PRIMARY KEY (`ID`),
	KEY `IX_B_IM_LINK_TASK_1` (`CHAT_ID`, `TASK_ID`),
	UNIQUE KEY `UIX_B_IM_LINK_TASK_1` (`TASK_ID`)
);

CREATE TABLE `b_im_link_favorite`
(
	`ID` INT AUTO_INCREMENT,
	`MESSAGE_ID` INT null,
	`CHAT_ID` INT null,
	`AUTHOR_ID` INT null,
	`DATE_CREATE` DATETIME not null,
	PRIMARY KEY (`ID`),
	KEY `IX_B_IM_LINK_FAVORITE_1` (`CHAT_ID`, `AUTHOR_ID`, `ID`),
	UNIQUE KEY  `UIX_B_IM_LINK_FAVORITE_1`  (`MESSAGE_ID`, `AUTHOR_ID`)
);

CREATE TABLE `b_im_link_pin`
(
	`ID` INT AUTO_INCREMENT,
	`MESSAGE_ID` INT not null,
	`CHAT_ID` INT not null,
	`DATE_CREATE` DATETIME not null,
	`AUTHOR_ID` INT not null,
	PRIMARY KEY (`ID`),
	UNIQUE KEY `UIX_B_IM_LINK_PIN_1` (`MESSAGE_ID`, `CHAT_ID`),
	KEY `IX_B_IM_LINK_PIN_1` (`CHAT_ID`, `ID`)
);

CREATE TABLE `b_im_link_calendar`
(
	`ID` INT AUTO_INCREMENT,
	`MESSAGE_ID` INT null,
	`CHAT_ID` INT null,
	`AUTHOR_ID` INT null,
	`DATE_CREATE` DATETIME null,
	`CALENDAR_ID` INT null,
	`CALENDAR_TITLE` varchar(255) null,
	`CALENDAR_DATE_FROM` DATETIME null,
	`CALENDAR_DATE_TO` DATETIME null,
	PRIMARY KEY (`ID`),
	UNIQUE KEY `UIX_B_IM_LINK_CALENDAR_1` (`CALENDAR_ID`),
	KEY `IX_B_IM_LINK_CALENDAR_1` (`CHAT_ID`, `AUTHOR_ID`, `ID`),
	KEY `IX_B_IM_LINK_CALENDAR_2` (`CHAT_ID`, `DATE_CREATE`, `ID`),
	KEY `IX_B_IM_LINK_CALENDAR_3` (`CHAT_ID`, `CALENDAR_DATE_FROM`, `CALENDAR_DATE_TO`, `ID`),
	KEY `IX_B_IM_LINK_CALENDAR_4` (`CHAT_ID`, `ID`)
);

CREATE TABLE `b_im_link_calendar_index`
(
	`ID` INT not null,
	`SEARCH_CONTENT` TEXT null,
	PRIMARY KEY (`ID`)
);

CREATE TABLE `b_im_link_reminder`
(
	`ID` INT AUTO_INCREMENT,
	`MESSAGE_ID` INT not null,
	`CHAT_ID` INT not null,
	`DATE_CREATE` DATETIME not null,
	`AUTHOR_ID` INT not null,
	`DATE_REMIND` DATETIME,
	`IS_REMINDED` char(1) DEFAULT 'N',
	PRIMARY KEY (`ID`),
	UNIQUE KEY `UIX_B_IM_LINK_REMINDER_2` (`AUTHOR_ID`, `MESSAGE_ID`),
	KEY `IX_B_IM_LINK_REMINDER_1` (`DATE_REMIND`, `IS_REMINDED`),
	KEY `IX_B_IM_LINK_REMINDER_2` (`CHAT_ID`, `AUTHOR_ID`, `IS_REMINDED`),
	KEY `IX_B_IM_LINK_REMINDER_3` (`CHAT_ID`, `AUTHOR_ID`, `ID`)
);

CREATE TABLE `b_im_file_temporary`
(
	`ID` INT AUTO_INCREMENT,
	`DISK_FILE_ID` INT not null,
	`DATE_CREATE` DATETIME not null,
	`SOURCE` varchar(50) not null,
	PRIMARY KEY (`ID`),
	UNIQUE KEY `UIX_B_IM_FILE_TEMPORARY_1` (`DISK_FILE_ID`),
	KEY `IX_B_IM_FILE_TEMPORARY_1` (`DATE_CREATE`, `SOURCE`)
);

CREATE TABLE `b_im_reaction`
(
	`ID` INT AUTO_INCREMENT,
	`CHAT_ID` INT not null,
	`MESSAGE_ID` INT not null,
	`USER_ID` INT not null,
	`REACTION` varchar(50) not null,
	`DATE_CREATE` DATETIME not null,
	PRIMARY KEY (`ID`),
	UNIQUE KEY `UIX_B_IM_REACTION_1` (`MESSAGE_ID`, `REACTION`, `USER_ID`),
	KEY `IX_B_IM_REACTION_1` (`MESSAGE_ID`, `REACTION`, `ID`),
	KEY `IX_B_IM_REACTION_2` (`USER_ID`, `MESSAGE_ID`, `REACTION`)
);