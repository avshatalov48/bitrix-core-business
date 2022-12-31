create table if not exists b_catalog_iblock
(
	IBLOCK_ID int not null,
	YANDEX_EXPORT char(1) not null default 'N',
	SUBSCRIPTION char(1) not null default 'N',
	VAT_ID int(11) NULL default '0',
	PRODUCT_IBLOCK_ID int not null default '0',
	SKU_PROPERTY_ID int not null default '0',
	primary key (IBLOCK_ID),
	index IXS_CAT_IB_PRODUCT(PRODUCT_IBLOCK_ID),
	index IXS_CAT_IB_SKU_PROP(SKU_PROPERTY_ID)
);

create table if not exists b_catalog_price
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	EXTRA_ID int null,
	CATALOG_GROUP_ID int not null,
	PRICE decimal(18,2) not null,
	CURRENCY char(3) not null,
	TIMESTAMP_X timestamp not null default NOW() on update NOW(),
	QUANTITY_FROM int null,
	QUANTITY_TO int null,
	TMP_ID varchar(40) null,
	PRICE_SCALE decimal(26,12) null,
	primary key (ID),
	index IXS_CAT_PRICE_PID(PRODUCT_ID, CATALOG_GROUP_ID),
	index IXS_CAT_PRICE_GID(CATALOG_GROUP_ID),
	index IXS_CAT_PRICE_SCALE(PRICE_SCALE)
);

create table if not exists b_catalog_product
(
	ID int not null,
	QUANTITY double not null,
	QUANTITY_TRACE char(1) not null default 'N',
	WEIGHT double not null default '0',
	TIMESTAMP_X timestamp not null default NOW() on update NOW(),
	PRICE_TYPE char(1) not null default 'S',
	RECUR_SCHEME_LENGTH int null,
	RECUR_SCHEME_TYPE char(1) not null default 'D',
	TRIAL_PRICE_ID int null,
	WITHOUT_ORDER char(1) not null default 'N',
	SELECT_BEST_PRICE char(1) not null default 'Y',
	VAT_ID int(11) null default '0',
	VAT_INCLUDED char(1) null default 'Y',
	CAN_BUY_ZERO char(1) not null default 'N',
	NEGATIVE_AMOUNT_TRACE char(1) not null default 'D',
	TMP_ID varchar(40) null,
	PURCHASING_PRICE decimal(18,2) null,
	PURCHASING_CURRENCY char(3) null,
	BARCODE_MULTI char(1) not null default 'N',
	QUANTITY_RESERVED double null default '0',
	SUBSCRIBE char(1) null,
	WIDTH double null,
	LENGTH double null,
	HEIGHT double null,
	MEASURE int null,
	TYPE int null,
	AVAILABLE char(1) null,
	BUNDLE char(1) null,
	primary key (ID)
);

create table if not exists b_catalog_product2group
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	GROUP_ID int not null,
	ACCESS_LENGTH int not null,
	ACCESS_LENGTH_TYPE char(1) not null default 'D',
	primary key (ID),
	unique IX_C_P2G_PROD_GROUP(PRODUCT_ID, GROUP_ID)
);

create table if not exists b_catalog_extra
(
	ID int not null auto_increment,
	NAME varchar(50) not null,
	PERCENTAGE decimal(18,2) not null,
	primary key (ID)
);

create table if not exists b_catalog_group
(
	ID int not null auto_increment,
	NAME varchar(100) not null,
	BASE char(1) not null default 'N',
	SORT int not null default '100',
	XML_ID varchar(255) null,
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	primary key (ID)
);

create table if not exists b_catalog_group_lang
(
	ID int not null auto_increment,
	CATALOG_GROUP_ID int not null,
	LANG char(2) not null,
	NAME varchar(100) null,
	primary key (ID),
	unique IX_CATALOG_GROUP_ID(CATALOG_GROUP_ID, LANG)
);

create table if not exists b_catalog_group2group
(
	ID int not null auto_increment,
	CATALOG_GROUP_ID int not null,
	GROUP_ID int not null,
	BUY char(1) not null default 'Y',
	primary key (ID),
	unique IX_CATG2G_UNI(CATALOG_GROUP_ID, GROUP_ID, BUY)
);

create table if not exists b_catalog_load
(
	NAME varchar(250) not null,
	VALUE text not null,
	TYPE char(1) not null default 'I',
	LAST_USED char(1) not null default 'N',
	primary key (NAME, TYPE)
);

create table if not exists b_catalog_export
(
	ID int not null auto_increment,
	FILE_NAME varchar(100) not null,
	NAME varchar(250) not null,
	DEFAULT_PROFILE char(1) not null default 'N',
	IN_MENU char(1) not null default 'N',
	IN_AGENT char(1) not null default 'N',
	IN_CRON char(1) not null default 'N',
	SETUP_VARS mediumtext null,
	LAST_USE datetime null,
	IS_EXPORT char(1) not null default 'Y',
	NEED_EDIT char(1) not null default 'N',
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	primary key (ID),
	index BCAT_EX_FILE_NAME(FILE_NAME),
	index IX_CAT_IS_EXPORT(IS_EXPORT)
);

create table if not exists b_catalog_discount
(
	ID int not null auto_increment,
	XML_ID varchar(255) null,
	SITE_ID char(2) not null,
	TYPE int not null default '0',
	ACTIVE char(1) not null default 'Y',
	ACTIVE_FROM datetime null,
	ACTIVE_TO datetime null,
	RENEWAL char(1) not null default 'N',
	NAME varchar(255) null,
	MAX_USES int not null default '0',
	COUNT_USES int not null default '0',
	COUPON varchar(20) null,
	SORT int not null default '100',
	MAX_DISCOUNT decimal(18,4) null,
	VALUE_TYPE char(1) not null default 'P',
	VALUE decimal(18,4) not null default '0.0',
	CURRENCY char(3) not null,
	MIN_ORDER_SUM decimal(18,4) null default '0.0',
	TIMESTAMP_X timestamp not null default NOW() on update NOW(),
	COUNT_PERIOD char(1) not null default 'U',
	COUNT_SIZE int not null default '0',
	COUNT_TYPE char(1) not null default 'Y',
	COUNT_FROM datetime null,
	COUNT_TO datetime null,
	ACTION_SIZE int not null default '0',
	ACTION_TYPE char(1) not null default 'Y',
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	PRIORITY int(18) not null default 1,
	LAST_DISCOUNT char(1) not null default 'Y',
	VERSION int not null default 1,
	NOTES varchar(255) null,
	CONDITIONS text null,
	UNPACK text null,
	USE_COUPONS char(1) not null default 'N',
	SALE_ID int null,
	primary key (ID),
	index IX_C_D_ACT(ACTIVE, ACTIVE_FROM, ACTIVE_TO),
	index IX_C_D_ACT_B(SITE_ID, RENEWAL, ACTIVE, ACTIVE_FROM, ACTIVE_TO),
	index IX_B_CAT_DISCOUNT_COUPON(USE_COUPONS)
);

create table if not exists b_catalog_discount_cond
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ACTIVE char(1) null,
	USER_GROUP_ID int not null default -1,
	PRICE_TYPE_ID int not null default -1,
	primary key (ID)
);

create table if not exists b_catalog_discount_module
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	MODULE_ID varchar(50) not null,
	primary key (ID),
	index IX_CAT_DSC_MOD(DISCOUNT_ID)
);

create table if not exists b_catalog_discount_entity
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	MODULE_ID varchar(50) not null,
	ENTITY varchar(255) not null,
	ENTITY_ID int null,
	ENTITY_VALUE varchar(255) null,
	FIELD_ENTITY varchar(255) not null,
	FIELD_TABLE varchar(255) not null,
	primary key (ID),
	index IX_CAT_DSC_ENT_SEARCH(DISCOUNT_ID, MODULE_ID, ENTITY)
);

create table if not exists b_catalog_discount2product
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	PRODUCT_ID int not null,
	primary key (ID),
	unique IX_C_D2P_PRODIS(PRODUCT_ID, DISCOUNT_ID),
	unique IX_C_D2P_PRODIS_B(DISCOUNT_ID, PRODUCT_ID)
);

create table if not exists b_catalog_discount2group
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	GROUP_ID int not null,
	primary key (ID),
	unique IX_C_D2G_GRDIS(GROUP_ID, DISCOUNT_ID),
	unique IX_C_D2G_GRDIS_B(DISCOUNT_ID, GROUP_ID)
);

create table if not exists b_catalog_discount2cat
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	CATALOG_GROUP_ID int not null,
	primary key (ID),
	unique IX_C_D2C_CATDIS(CATALOG_GROUP_ID, DISCOUNT_ID),
	unique IX_C_D2C_CATDIS_B(DISCOUNT_ID, CATALOG_GROUP_ID)
);

create table if not exists b_catalog_discount2section
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	SECTION_ID int not null,
	primary key (ID),
	unique IX_C_D2S_SECDIS(SECTION_ID, DISCOUNT_ID),
	unique IX_C_D2S_SECDIS_B(DISCOUNT_ID, SECTION_ID)
);

create table if not exists b_catalog_discount2iblock
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	IBLOCK_ID int not null,
	primary key (ID),
	unique IX_C_D2I_IBDIS(IBLOCK_ID, DISCOUNT_ID),
	unique IX_C_D2I_IBDIS_B(DISCOUNT_ID, IBLOCK_ID)
);

create table if not exists b_catalog_discount_coupon
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ACTIVE char(1) not null default 'Y',
	COUPON varchar(32) not null,
	DATE_APPLY datetime null,
	ONE_TIME char(1) not null default 'Y',
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	DESCRIPTION text null,
	primary key (ID),
	unique ix_cat_dc_index1(DISCOUNT_ID, COUPON),
	index ix_cat_dc_index2(COUPON, ACTIVE)
);

create table if not exists b_catalog_vat
(
	ID int(11) NOT NULL auto_increment,
	TIMESTAMP_X timestamp not null default NOW() on update NOW(),
	ACTIVE char(1) NOT NULL default 'Y',
	C_SORT int(18) NOT NULL default 100,
	NAME varchar(50) NOT NULL default '',
	RATE decimal(18,2) null default '0.00',
	EXCLUDE_VAT char(1) not null default 'N',
	XML_ID varchar(255) null,
	primary key (ID),
	index IX_CAT_VAT_ACTIVE (ACTIVE)
);

create table if not exists b_catalog_disc_save_range
(
	ID int NOT NULL auto_increment,
	DISCOUNT_ID int not null,
	RANGE_FROM double not null,
	TYPE char(1) default 'P' not null,
	VALUE double not null,
	primary key (ID),
	index IX_CAT_DSR_DISCOUNT2(DISCOUNT_ID, RANGE_FROM)
);

create table if not exists b_catalog_disc_save_group
(
	ID int NOT NULL auto_increment,
	DISCOUNT_ID int not null,
	GROUP_ID int not null,
	primary key (ID),
	index IX_CAT_DSG_DISCOUNT(DISCOUNT_ID),
	index IX_CAT_DSG_GROUP(GROUP_ID)
);

create table if not exists b_catalog_disc_save_user
(
	ID int NOT NULL auto_increment,
	DISCOUNT_ID int not null,
	USER_ID int not null,
	ACTIVE_FROM datetime not null,
	ACTIVE_TO datetime not null,
	RANGE_FROM double not null,
	primary key (ID),
	index IX_CAT_DSU_USER(DISCOUNT_ID,USER_ID)
);

create table if not exists b_catalog_store
(
	ID INT NOT NULL AUTO_INCREMENT,
	TITLE VARCHAR(75) NULL,
	ACTIVE CHAR(1) NOT NULL DEFAULT 'Y',
	ADDRESS VARCHAR(245) NOT NULL,
	DESCRIPTION TEXT NULL,
	GPS_N VARCHAR(15) NULL DEFAULT 0,
	GPS_S VARCHAR(15) NULL DEFAULT 0,
	IMAGE_ID VARCHAR(45) NULL,
	LOCATION_ID INT NULL,
	DATE_MODIFY TIMESTAMP DEFAULT NOW() on update NOW(),
	DATE_CREATE DATETIME NULL,
	USER_ID INT NULL,
	MODIFIED_BY INT NULL,
	PHONE VARCHAR(45) NULL,
	SCHEDULE VARCHAR(255) NULL,
	XML_ID VARCHAR(255) NULL,
	SORT INT NOT NULL DEFAULT '100',
	EMAIL VARCHAR(255) NULL,
	ISSUING_CENTER CHAR(1) NOT NULL DEFAULT 'Y',
	SHIPPING_CENTER CHAR(1) NOT NULL DEFAULT 'Y',
	SITE_ID CHAR(2) NULL,
	CODE VARCHAR(255) NULL,
	IS_DEFAULT char(1) not null default 'N',
	PRIMARY KEY (ID)
);

create table if not exists b_catalog_store_product
(
	ID INT NOT NULL AUTO_INCREMENT,
	PRODUCT_ID INT NOT NULL,
	AMOUNT DOUBLE NOT NULL DEFAULT 0,
	STORE_ID INT NOT NULL,
	QUANTITY_RESERVED DOUBLE NOT NULL DEFAULT 0,
	PRIMARY KEY (ID),
	INDEX IX_CATALOG_STORE_PRODUCT1 (STORE_ID ASC),
	UNIQUE INDEX IX_CATALOG_STORE_PRODUCT2 (PRODUCT_ID ASC, STORE_ID ASC)
);

create table if not exists b_catalog_store_barcode
(
	ID INT NOT NULL AUTO_INCREMENT,
	PRODUCT_ID INT NOT NULL,
	BARCODE VARCHAR(100) NULL,
	STORE_ID INT NULL,
	ORDER_ID INT NULL,
	DATE_MODIFY DATETIME NULL,
	DATE_CREATE DATETIME NULL,
	CREATED_BY INT NULL,
	MODIFIED_BY INT NULL,
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_B_CATALOG_STORE_BARCODE1(BARCODE)
);

create table if not exists b_catalog_contractor
(
	ID INT NOT NULL AUTO_INCREMENT,
	PERSON_TYPE CHAR(1) NOT NULL,
	PERSON_NAME VARCHAR(100) NULL,
	PERSON_LASTNAME VARCHAR(100) NULL,
	PERSON_MIDDLENAME VARCHAR(100) NULL,
	EMAIL VARCHAR(100) NULL,
	PHONE VARCHAR(45) NULL,
	POST_INDEX VARCHAR(45) NULL,
	COUNTRY VARCHAR(45) NULL,
	CITY VARCHAR(45) NULL,
	COMPANY VARCHAR(145) NULL,
	INN VARCHAR(145) NULL,
	KPP VARCHAR(145) NULL,
	ADDRESS VARCHAR(255) NULL,
	DATE_MODIFY TIMESTAMP DEFAULT NOW() on update NOW(),
	DATE_CREATE DATETIME NULL,
	CREATED_BY INT NULL,
	MODIFIED_BY INT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_catalog_store_docs
(
	ID INT NOT NULL AUTO_INCREMENT,
	DOC_TYPE CHAR(1) NOT NULL,
	SITE_ID CHAR(2) NULL,
	CONTRACTOR_ID INT NULL,
	DATE_MODIFY DATETIME NULL,
	DATE_CREATE DATETIME NULL,
	CREATED_BY INT NULL,
	MODIFIED_BY INT NULL,
	CURRENCY CHAR(3) NULL,
	STATUS CHAR(1) NOT NULL DEFAULT 'N',
	DATE_STATUS DATETIME NULL,
	DATE_DOCUMENT DATETIME NULL,
	STATUS_BY INT NULL,
	TOTAL DOUBLE NULL,
	COMMENTARY VARCHAR(1000) NULL,
	TITLE VARCHAR(255) NULL,
	RESPONSIBLE_ID INT NULL,
	ITEMS_ORDER_DATE DATETIME NULL,
	ITEMS_RECEIVED_DATE DATETIME NULL,
	DOC_NUMBER VARCHAR(64) NULL,
	WAS_CANCELLED CHAR(1) DEFAULT 'N',
	PRIMARY KEY (ID)
);

create table if not exists b_catalog_store_document_file
(
	ID INT NOT NULL AUTO_INCREMENT,
	DOCUMENT_ID INT NOT NULL,
	FILE_ID INT NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_B_CATALOG_STORE_DOCUMENT_FILE_DOC_ID(DOCUMENT_ID)
);

create table if not exists b_catalog_docs_element
(
	ID INT NOT NULL AUTO_INCREMENT,
	DOC_ID INT NOT NULL,
	STORE_FROM INT NULL,
	STORE_TO INT NULL,
	ELEMENT_ID INT NULL,
	AMOUNT DOUBLE NULL,
	PURCHASING_PRICE DOUBLE NULL,
    BASE_PRICE DECIMAL(18,2) NULL,
	BASE_PRICE_EXTRA DECIMAL(18,2) NULL,
	BASE_PRICE_EXTRA_RATE INT NULL,
	PRIMARY KEY (ID),
	INDEX IX_B_CATALOG_DOCS_ELEMENT1 (DOC_ID ASC)
);

create table if not exists b_catalog_docs_barcode
(
	ID INT NOT NULL AUTO_INCREMENT,
	DOC_ID INT NOT NULL,
	DOC_ELEMENT_ID INT NOT NULL,
	BARCODE VARCHAR(100) NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_B_CATALOG_DOCS_BARCODE1 (DOC_ELEMENT_ID ASC),
	index IX_B_CATALOG_DOCS_BARCODE_OWNER (DOC_ID)
);

create table if not exists b_catalog_measure
(
	ID INT NOT NULL AUTO_INCREMENT,
	CODE INT NOT NULL,
	MEASURE_TITLE VARCHAR(500) NULL,
	SYMBOL_RUS VARCHAR(20) NULL,
	SYMBOL_INTL VARCHAR(20) NULL,
	SYMBOL_LETTER_INTL VARCHAR(20) NULL,
	IS_DEFAULT CHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_B_CATALOG_MEASURE1(CODE)
);

create table if not exists b_catalog_measure_ratio
(
	ID INT NOT NULL AUTO_INCREMENT,
	PRODUCT_ID INT NOT NULL,
	RATIO DOUBLE NOT NULL DEFAULT '1',
	IS_DEFAULT CHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_B_CATALOG_MEASURE_RATIO(PRODUCT_ID, RATIO)
);

create table if not exists b_catalog_product_sets
(
	ID INT NOT NULL AUTO_INCREMENT,
	TYPE INT NOT NULL,
	SET_ID INT NOT NULL,
	ACTIVE CHAR(1) NOT NULL,
	OWNER_ID INT NOT NULL,
	ITEM_ID INT NOT NULL,
	QUANTITY DOUBLE NULL,
	MEASURE INT NULL,
	DISCOUNT_PERCENT DOUBLE NULL,
	SORT INT NOT NULL DEFAULT 100,
	CREATED_BY INT(18) NULL,
	DATE_CREATE DATETIME NULL,
	MODIFIED_BY INT(18) NULL,
	TIMESTAMP_X DATETIME NULL,
	XML_ID VARCHAR(255) NULL,
	PRIMARY KEY(ID),
	INDEX IX_CAT_PR_SET_TYPE(TYPE),
	INDEX IX_CAT_PR_SET_OWNER_ID(OWNER_ID),
	INDEX IX_CAT_PR_SET_SET_ID(SET_ID),
	INDEX IX_CAT_PR_SET_ITEM_ID(ITEM_ID)
);

create table if not exists b_catalog_viewed_product
(
	ID INT NOT NULL AUTO_INCREMENT,
	FUSER_ID INT NOT NULL,
	DATE_VISIT DATETIME NOT NULL,
	PRODUCT_ID INT NOT NULL,
	ELEMENT_ID INT NOT NULL DEFAULT 0,
	SITE_ID CHAR(2) NOT NULL,
	VIEW_COUNT INT NOT NULL DEFAULT 1,
	RECOMMENDATION VARCHAR(40) NULL,
	PRIMARY KEY (ID),
	INDEX IX_CAT_V_PR_VISIT(FUSER_ID, SITE_ID, DATE_VISIT DESC),
	INDEX IX_CAT_V_PR_PRODUCT(FUSER_ID, SITE_ID, ELEMENT_ID),
	INDEX IX_CAT_V_PR_PRODUCT_VISIT(ELEMENT_ID, DATE_VISIT)
);

create table if not exists b_catalog_subscribe (
	ID int unsigned not null auto_increment,
	DATE_FROM datetime not null,
	DATE_TO datetime null,
	USER_CONTACT varchar(255) not null,
	CONTACT_TYPE smallint unsigned not null,
	USER_ID int unsigned,
	ITEM_ID int unsigned not null,
	NEED_SENDING char(1) not null default 'N',
	SITE_ID char(2) not null,
	LANDING_SITE_ID int(18) null,
	primary key (ID),
	INDEX IX_CAT_SUB_USER_CONTACT (USER_CONTACT),
	INDEX IX_CAT_SUB_USER_ID (USER_ID),
	INDEX IX_CAT_SUB_ITEM_ID (ITEM_ID)
);

create table if not exists b_catalog_subscribe_access (
	ID int unsigned not null auto_increment,
	DATE_FROM datetime not null,
	USER_CONTACT varchar(255) not null,
	TOKEN char(6) not null,
	primary key (ID),
	INDEX IX_CAT_SUB_ACS_USER_CONTACT (USER_CONTACT)
);

create table if not exists b_catalog_rounding
(
	ID int not null auto_increment,
	CATALOG_GROUP_ID int not null,
	PRICE decimal(18, 4) not null,
	ROUND_TYPE int not null,
	ROUND_PRECISION decimal(18, 4) not null,
	CREATED_BY int(18) null,
	DATE_CREATE datetime null,
	MODIFIED_BY int(18) null,
	DATE_MODIFY datetime null,
	primary key (ID),
	index IX_CAT_RND_CATALOG_GROUP(CATALOG_GROUP_ID)
);

create table if not exists b_catalog_product_compilation
(
	ID int not null auto_increment,
	DEAL_ID int not null,
	PRODUCT_IDS text not null,
	CREATION_DATE datetime not null,
	CHAT_ID int null,
	QUEUE_ID int null,
	primary key (ID),
	index IX_CAT_COMPILATION_DEAL_ID(DEAL_ID)
);

create table if not exists b_catalog_exported_product
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	SERVICE_ID varchar(100) not null,
	TIMESTAMP_X timestamp not null default NOW() on update NOW(),
	ERROR text null,
	primary key (ID),
	index IX_CAT_PR_EXP_PRID_SVID(PRODUCT_ID, SERVICE_ID)
);

create table if not exists b_catalog_exported_product_queue
(
	QUEUE_ID int not null,
	PRODUCT_IDS text not null,
	primary key (QUEUE_ID)
);

CREATE TABLE IF NOT EXISTS b_catalog_role
(
	ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	NAME VARCHAR(250) NOT NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE IF NOT EXISTS b_catalog_role_relation
(
	ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	ROLE_ID INT(10) UNSIGNED NOT NULL,
	RELATION VARCHAR(8) NOT NULL DEFAULT '',
	PRIMARY KEY (ID),
	INDEX ROLE_ID (ROLE_ID),
	INDEX RELATION (RELATION)
);

CREATE TABLE IF NOT EXISTS b_catalog_permission
(
	ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
	ROLE_ID INT UNSIGNED NOT NULL,
	PERMISSION_ID VARCHAR(32) NOT NULL DEFAULT '0',
	VALUE INT NOT NULL DEFAULT '0',
	PRIMARY KEY (ID),
	INDEX ROLE_ID (ROLE_ID),
	INDEX PERMISSION_ID (PERMISSION_ID)
);