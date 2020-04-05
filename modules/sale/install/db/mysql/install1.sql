create table b_catalog_currency
(
	CURRENCY char(3) not null,
	AMOUNT_CNT int not null default 1,
	AMOUNT decimal(18, 4) null,
	SORT int not null default 100,
	DATE_UPDATE datetime not null,
	primary key (CURRENCY)
);

INSERT INTO b_catalog_currency (CURRENCY, AMOUNT_CNT, AMOUNT, SORT, DATE_UPDATE) VALUES('RUR', 1, 1.00, 10, curdate());
INSERT INTO b_catalog_currency (CURRENCY, AMOUNT_CNT, AMOUNT, SORT, DATE_UPDATE) VALUES('USD', 1, 30.2979, 20, curdate());

create table b_catalog_currency_lang
(
	CURRENCY char(3) not null,
	LID char(2) not null,
	FORMAT_STRING varchar(50) not null,
	FULL_NAME varchar(50) null,
	DEC_POINT varchar(5) not null default '.',
	THOUSANDS_SEP varchar(5) null default ' ',
	DECIMALS tinyint not null default 2,
	primary key (CURRENCY, LID)
);

INSERT INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES('USD', 'ru', '$#', 'Доллар США', '.', ',', 2);
INSERT INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES('USD', 'en', '$#', 'USA dollar', '.', ',', 2);
INSERT INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES('RUR', 'ru', '# руб', 'Рубль', '.', '\\xA0', 2);
INSERT INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES('RUR', 'en', '# rub', 'Rouble', '.', '\\xA0', 2);

create table b_catalog_currency_rate
(
	ID int not null auto_increment,
	CURRENCY char(3) not null,
	DATE_RATE date not null,
	RATE_CNT int not null default 1,
	RATE decimal(18, 4) not null default 0.00,
	primary key (ID),
	unique IX_CURRENCY_RATE(CURRENCY, DATE_RATE)
);

