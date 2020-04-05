create table if not exists b_lists_permission
(
	IBLOCK_TYPE_ID VARCHAR(50) not null,
	GROUP_ID INT(11) not null,
	primary key PK_B_LISTS_PERMISSION (IBLOCK_TYPE_ID, GROUP_ID)
);

create table if not exists b_lists_field
(
	IBLOCK_ID int(11) not null,
	FIELD_ID varchar(100) not null,
	SORT int not null,
	NAME varchar(100) not null,
	SETTINGS text,
	primary key pk_b_lists_field (IBLOCK_ID, FIELD_ID)
);

create table if not exists b_lists_socnet_group
(
	IBLOCK_ID int(11) not null,
	SOCNET_ROLE char(1),
	PERMISSION char(1) not null,
	UNIQUE ux_b_lists_socnet_group_1(IBLOCK_ID, SOCNET_ROLE)
);

create table if not exists b_lists_url
(
	IBLOCK_ID int(11) not null,
	URL varchar(500),
	LIVE_FEED tinyint(1) DEFAULT 0,
	primary key pk_b_lists_url(IBLOCK_ID)
);
