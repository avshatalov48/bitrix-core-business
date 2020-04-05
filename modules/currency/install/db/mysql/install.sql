create table if not exists b_catalog_currency
(
	CURRENCY char(3) not null,
	AMOUNT_CNT int not null default 1,
	AMOUNT decimal(18, 4) null,
	SORT int not null default 100,
	DATE_UPDATE datetime not null,
	NUMCODE char(3) null,
	BASE char(1) not null default 'N',
	CREATED_BY int(18) null,
	DATE_CREATE datetime null,
	MODIFIED_BY int(18) null,
	CURRENT_BASE_RATE decimal(26, 12) null,
	primary key (CURRENCY)
);

create table if not exists b_catalog_currency_lang
(
	CURRENCY char(3) not null,
	LID char(2) not null,
	FORMAT_STRING varchar(50) not null,
	FULL_NAME varchar(50) null,
	DEC_POINT varchar(16) null default '.',
	THOUSANDS_SEP varchar(16) null default ' ',
	DECIMALS tinyint not null default 2,
	THOUSANDS_VARIANT char(1) null,
	HIDE_ZERO char(1) not null default 'N',
	CREATED_BY int(18) null,
	DATE_CREATE datetime null,
	MODIFIED_BY int(18) null,
	TIMESTAMP_X datetime null,
	primary key (CURRENCY, LID)
);

create table if not exists b_catalog_currency_rate
(
	ID int not null auto_increment,
	CURRENCY char(3) not null,
	BASE_CURRENCY char(3) null,
	DATE_RATE date not null,
	RATE_CNT int not null default 1,
	RATE decimal(18, 4) not null default 0,
	CREATED_BY int(18) null,
	DATE_CREATE datetime null,
	MODIFIED_BY int(18) null,
	TIMESTAMP_X datetime null,
	primary key (ID),
	unique IX_CURRENCY_RATE(CURRENCY, DATE_RATE)
);