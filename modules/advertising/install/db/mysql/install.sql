create table if not exists b_adv_banner
(
	ID int(18) not null auto_increment,
	CONTRACT_ID int(18) not null default '1',
	TYPE_SID varchar(255) not null,
	STATUS_SID varchar(255) not null default 'PUBLISHED',
	STATUS_COMMENTS text,
	NAME varchar(255),
	GROUP_SID varchar(255),
	FIRST_SITE_ID char(2),
	ACTIVE char(1) not null default 'Y',
	WEIGHT int(18) not null default '100',
	MAX_SHOW_COUNT int(18),
	SHOW_COUNT int(18) not null default '0',
	FIX_CLICK char(1) not null default 'Y',
	FIX_SHOW char(1) not null default 'Y',
	MAX_CLICK_COUNT int(18),
	CLICK_COUNT int(18) not null default '0',
	MAX_VISITOR_COUNT int(18),
	VISITOR_COUNT int(18) not null default '0',
	SHOWS_FOR_VISITOR int(18),
	DATE_LAST_SHOW datetime,
	DATE_LAST_CLICK datetime,
	DATE_SHOW_FROM datetime,
	DATE_SHOW_TO datetime,
	IMAGE_ID int(18),
	IMAGE_ALT varchar(255),
	URL text,
	URL_TARGET varchar(255),
	CODE text,
	CODE_TYPE varchar(5) not null default 'html',
	STAT_EVENT_1 varchar(255),
	STAT_EVENT_2 varchar(255),
	STAT_EVENT_3 varchar(255),
	FOR_NEW_GUEST char(1),
	KEYWORDS text,
	COMMENTS text,
	DATE_CREATE datetime,
	CREATED_BY int(18),
	DATE_MODIFY datetime,
	MODIFIED_BY int(18),
	SHOW_USER_GROUP char(1) not null default 'N',
	NO_URL_IN_FLASH char(1) not null default 'N',
	FLYUNIFORM CHAR( 1 ) NOT NULL DEFAULT 'N',
	DATE_SHOW_FIRST DATETIME NULL,
	AD_TYPE VARCHAR( 20 ),
	FLASH_TRANSPARENT VARCHAR( 20 ),
	FLASH_IMAGE int( 18 ),
	FLASH_JS CHAR( 1 ) NOT NULL DEFAULT 'N',
	FLASH_VER VARCHAR( 20 ),
	STAT_TYPE varchar(20),
	STAT_COUNT int(18),
	TEMPLATE text,
	TEMPLATE_FILES varchar(1000),
	primary key (ID),
	index IX_ACTIVE_TYPE_SID (ACTIVE, TYPE_SID),
	index IX_CONTRACT_TYPE (CONTRACT_ID, TYPE_SID)
);

create table if not exists b_adv_banner_2_country
(
	BANNER_ID int(18) not null default '0',
	COUNTRY_ID char(2) not null,
	REGION varchar(200),
	CITY_ID int(18)
);
create index ix_b_adv_banner_2_country_1 on b_adv_banner_2_country (COUNTRY_ID, REGION(50), BANNER_ID);
create index ix_b_adv_banner_2_country_2 on b_adv_banner_2_country (CITY_ID, BANNER_ID);
create index ix_b_adv_banner_2_country_3 on b_adv_banner_2_country (BANNER_ID);

create table if not exists b_adv_banner_2_day
(
	DATE_STAT date not null,
	BANNER_ID int(18) not null default '0',
	SHOW_COUNT int(18) not null default '0',
	CLICK_COUNT int(18) not null default '0',
	VISITOR_COUNT int(18) not null default '0',
	primary key (BANNER_ID, DATE_STAT)
);

create table if not exists b_adv_banner_2_site
(
	BANNER_ID int(18) not null default '0',
	SITE_ID char(2) not null,
	primary key (BANNER_ID, SITE_ID)
);

create table if not exists b_adv_banner_2_page
(
	ID int(18) not null auto_increment,
	BANNER_ID int(18) not null default '0',
	PAGE varchar(255) not null,
	SHOW_ON_PAGE char(1) not null default 'Y',
	primary key (ID),
	index IX_BANNER_ID (BANNER_ID)
);

create table if not exists b_adv_banner_2_stat_adv
(
	BANNER_ID int(18) not null default '0',
	STAT_ADV_ID int(18) not null default '0',
	primary key (BANNER_ID, STAT_ADV_ID)
);

create table if not exists b_adv_banner_2_weekday
(
	BANNER_ID int(18) not null default '0',
	C_WEEKDAY varchar(10) not null,
	C_HOUR int(2) not null default '0',
	primary key (BANNER_ID, C_WEEKDAY, C_HOUR)
);

create table if not exists b_adv_contract
(
	ID int(18) not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	NAME varchar(255),
	DESCRIPTION text,
	KEYWORDS text,
	ADMIN_COMMENTS text,
	WEIGHT int(18) not null default '100',
	SORT int(18),
	MAX_SHOW_COUNT int(18),
	SHOW_COUNT int(18) not null default '0',
	MAX_CLICK_COUNT int(18) null,
	CLICK_COUNT int(18) not null default '0',
	MAX_VISITOR_COUNT int(18),
	VISITOR_COUNT int(18) not null default '0',
	DATE_SHOW_FROM datetime,
	DATE_SHOW_TO datetime,
	DEFAULT_STATUS_SID varchar(255) not null default 'PUBLISHED',
	EMAIL_COUNT int(18) not null default '0',
	DATE_CREATE datetime,
	CREATED_BY int(18),
	DATE_MODIFY datetime,
	MODIFIED_BY int(18),
	primary key (ID)
);

create table if not exists b_adv_contract_2_site
(
	CONTRACT_ID int(18) not null default '0',
	SITE_ID char(2) not null,
	primary key (CONTRACT_ID, SITE_ID)
);

create table if not exists b_adv_contract_2_page
(
	ID int(18) not null auto_increment,
	CONTRACT_ID int(18) not null default '0',
	PAGE varchar(255) not null,
	SHOW_ON_PAGE char(1) not null default 'Y',
	primary key (ID),
	index IX_CONTRACT_ID (CONTRACT_ID)
);

create table if not exists b_adv_contract_2_type
(
	CONTRACT_ID int(18) not null default '0',
	TYPE_SID varchar(255) not null,
	primary key (CONTRACT_ID, TYPE_SID)
);

create table if not exists b_adv_contract_2_user
(
	ID int(18) not null auto_increment,
	CONTRACT_ID int(18) not null default '0',
	USER_ID int(18) not null default '1',
	PERMISSION varchar(255) not null,
	primary key (ID),
	index IX_CONTRACT_ID(CONTRACT_ID)
);

create table if not exists b_adv_contract_2_weekday
(
	CONTRACT_ID int(18) not null default '0',
	C_WEEKDAY varchar(10) not null,
	C_HOUR int(2) not null default '0',
	primary key (CONTRACT_ID, C_WEEKDAY, C_HOUR)
);

create table if not exists b_adv_type
(
	SID varchar(255) not null,
	ACTIVE char(1) not null default 'Y',
	SORT int(18) not null default '100',
	NAME varchar(255),
	DESCRIPTION text,
	DATE_CREATE datetime,
	CREATED_BY int(18),
	DATE_MODIFY datetime,
	MODIFIED_BY int(18),
	primary key (SID)
);

create table if not exists b_adv_banner_2_group
(
  	BANNER_ID int(18) not null default '0',
	GROUP_ID int(18) not null default '0',
	primary key(BANNER_ID, GROUP_ID)
);
