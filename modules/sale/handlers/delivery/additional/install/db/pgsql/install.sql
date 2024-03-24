create table if not exists b_sale_hdale (
	CODE varchar(100) NOT NULL,
	ID int NOT NULL,
	NAME varchar(100) NOT NULL,
	PCITY varchar(100) NULL,
	PSUBREGION varchar(100) NULL,
	PREGION varchar(100) NULL,
	PCOUNTRY varchar(100) NULL,
	LOCATION_ID int NULL,
	LOCATION_EXT_ID int NULL,
	PRIMARY KEY (CODE)
);
CREATE INDEX ix_b_sale_hdale_location_id ON b_sale_hdale (location_id);

create table if not exists b_sale_ruspost_reliability (
	HASH char(32) NOT NULL,
	RELIABILITY int NULL,
	UPDATED_AT timestamp DEFAULT CURRENT_TIMESTAMP,
	ADDRESS text NULL,
	FULL_NAME varchar(255) NULL,
	PHONE varchar(30) NULL,
	PRIMARY KEY (HASH)
);
