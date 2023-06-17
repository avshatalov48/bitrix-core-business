create table if not exists b_sale_auxiliary
(
	ID int not null auto_increment,
	TIMESTAMP_X timestamp not null,
	ITEM varchar(255) not null,
	ITEM_MD5 varchar(32) not null,
	USER_ID int not null,
	DATE_INSERT datetime not null,
	primary key (ID),
	unique IX_STT_USER_ITEM(USER_ID, ITEM_MD5)
);

create table if not exists b_sale_lang
(
	LID char(2) not null,
	CURRENCY char(3) not null,
	primary key (LID)
);

create table if not exists b_sale_fuser
(
	ID int not null auto_increment,
	DATE_INSERT datetime not null,
	DATE_UPDATE datetime not null,
	USER_ID INT NULL,
	CODE varchar(32),
	primary key (ID),
	index IX_USER_ID(USER_ID),
	index IX_CODE(CODE(32))
);

create table if not exists b_sale_basket
(
	ID int not null auto_increment,
	FUSER_ID int not null,
	ORDER_ID int null,
	PRODUCT_ID int not null,
	PRODUCT_PRICE_ID int null,
	PRICE_TYPE_ID int null,
	PRICE decimal(18, 4) not null,
	CURRENCY char(3) not null,
	BASE_PRICE decimal(18, 4) null,
	VAT_INCLUDED char(1) not null default 'Y',
	DATE_INSERT datetime not null,
	DATE_UPDATE datetime not null,
	DATE_REFRESH datetime null,
	WEIGHT double(18, 2) null,
	QUANTITY double(18, 4) not null default '0',
	LID char(2) not null,
	DELAY char(1) not null default 'N',
	NAME varchar(255) not null,
	CAN_BUY char(1) not null default 'Y',
	MARKING_CODE_GROUP varchar(100) null,
	MODULE varchar(100) null,
	CALLBACK_FUNC varchar(100) null,
	NOTES varchar(250) null,
	ORDER_CALLBACK_FUNC varchar(100) null,
	DETAIL_PAGE_URL varchar(250) null,
	DISCOUNT_PRICE decimal(18,4) not null,
	CANCEL_CALLBACK_FUNC varchar(100) null,
	PAY_CALLBACK_FUNC varchar(100) null,
	PRODUCT_PROVIDER_CLASS varchar(100) null,
	CATALOG_XML_ID varchar(100) null,
	PRODUCT_XML_ID varchar(100) null,
	DISCOUNT_NAME varchar(255) null,
	DISCOUNT_VALUE char(32) null,
	DISCOUNT_COUPON char(32) null,
	VAT_RATE DECIMAL(18, 4) NULL default '0.00',
	SUBSCRIBE char(1) not null default 'N',
	DEDUCTED char(1) not null default 'N',
	RESERVED char(1) not null default 'N',
	BARCODE_MULTI char(1) not null default 'N',
	RESERVE_QUANTITY double null,
	CUSTOM_PRICE char(1) not null default 'N',
	DIMENSIONS varchar(255) null,
	TYPE int(11) null,
	SET_PARENT_ID int(11) null,
	MEASURE_CODE INT(11) NULL,
	MEASURE_NAME varchar(50) null,
	RECOMMENDATION varchar(40) null,
	XML_ID varchar(255) null,
	SORT INT(11) not null default '100',
	primary key (ID),
	index IXS_BASKET_LID(LID),
	index IXS_BASKET_USER_ID_LID_ORDER_ID(FUSER_ID, LID, ORDER_ID),
	index IXS_BASKET_ORDER_ID(ORDER_ID),
	index IXS_BASKET_PRODUCT_ID(PRODUCT_ID),
	index IXS_BASKET_PRODUCT_PRICE_ID(PRODUCT_PRICE_ID),
	index IXS_SBAS_XML_ID(PRODUCT_XML_ID, CATALOG_XML_ID),
	index IXS_BASKET_DATE_INSERT(DATE_INSERT),
	index IXS_BASKET_SET_PARENT_ID(SET_PARENT_ID)
);

create table if not exists b_sale_basket_props
(
	ID int not null auto_increment,
	BASKET_ID int not null,
	NAME varchar(255) not null,
	VALUE varchar(255) null,
	CODE varchar(255) null,
	SORT int not null default '100',
	XML_ID varchar(255) null,
	primary key (ID),
	index IXS_BASKET_PROPS_BASKET(BASKET_ID),
	index IXS_BASKET_PROPS_CODE(CODE)
);

create table if not exists b_sale_order
(
	ID int not null auto_increment,
	LID char(2) not null,
	PERSON_TYPE_ID int not null,
	PAYED char(1) not null default 'N',
	DATE_PAYED datetime null,
	EMP_PAYED_ID int null,
	CANCELED char(1) not null default 'N',
	DATE_CANCELED datetime null,
	EMP_CANCELED_ID int null,
	REASON_CANCELED varchar(255) null,
	STATUS_ID varchar(2) not null,
	DATE_STATUS datetime not null,
	EMP_STATUS_ID int null,
	PRICE_DELIVERY decimal(18,4) NOT NULL DEFAULT '0.0000',
	PRICE_PAYMENT decimal(18,4) NOT NULL DEFAULT '0.0000',
	ALLOW_DELIVERY char(1) not null default 'N',
	DATE_ALLOW_DELIVERY datetime null,
	EMP_ALLOW_DELIVERY_ID int null,
	DEDUCTED char(1) not null default 'N',
	DATE_DEDUCTED datetime null,
	EMP_DEDUCTED_ID int null,
	REASON_UNDO_DEDUCTED varchar(255) null,
	MARKED char(1) not null default 'N',
	DATE_MARKED datetime null,
	EMP_MARKED_ID int null,
	REASON_MARKED varchar(255) null,
	RESERVED char(1) not null default 'N',
	PRICE decimal(18, 4) not null,
	CURRENCY char(3) not null,
	DISCOUNT_VALUE decimal(18,4) NOT NULL DEFAULT '0.0000',
	USER_ID int not null,
	PAY_SYSTEM_ID int null,
	DELIVERY_ID varchar(50) null,
	DATE_INSERT datetime not null,
	DATE_UPDATE datetime not null,
	USER_DESCRIPTION varchar(2000) null,
	ADDITIONAL_INFO varchar(255) null,
	PS_STATUS char(1) null,
	PS_STATUS_CODE char(5) null,
	PS_STATUS_DESCRIPTION varchar(250) null,
	PS_STATUS_MESSAGE varchar(250) null,
	PS_SUM decimal(18,2) null,
	PS_CURRENCY char(3) null,
	PS_RESPONSE_DATE datetime null,
	COMMENTS text null,
	TAX_VALUE decimal(18,2) not null default '0.00',
	STAT_GID varchar(255) null,
	SUM_PAID decimal(18,2) not null default '0',
	IS_RECURRING char(1) not null default 'N',
	RECURRING_ID int null,
	PAY_VOUCHER_NUM varchar(20) null,
	PAY_VOUCHER_DATE date null,
	LOCKED_BY int null,
	DATE_LOCK datetime null,
	RECOUNT_FLAG char(1) not null default 'Y',
	AFFILIATE_ID int null,
	DELIVERY_DOC_NUM varchar(20) null,
	DELIVERY_DOC_DATE date null,
	UPDATED_1C CHAR(1) NOT NULL DEFAULT 'N',
	STORE_ID int null,
	ORDER_TOPIC varchar(255) null,
	CREATED_BY int(11) null,
	RESPONSIBLE_ID int(11) null,
	COMPANY_ID int(11) null,
	DATE_PAY_BEFORE datetime null,
	DATE_BILL datetime null,
	ACCOUNT_NUMBER varchar(100) null,
	TRACKING_NUMBER varchar(255) NULL,
	XML_ID varchar(255) null,
	ID_1C varchar(36) null,
	VERSION_1C varchar(15) null,
	VERSION INT(11) not null default '0',
	EXTERNAL_ORDER char(1) not null default 'N',
	RUNNING char(1) not null default 'N',
	BX_USER_ID varchar(32) null,
	SEARCH_CONTENT mediumtext null,
	IS_SYNC_B24 CHAR(1) not null default 'N',
	primary key (ID),
	index IXS_ORDER_PERSON_TYPE_ID(PERSON_TYPE_ID),
	index IXS_ORDER_STATUS_ID(STATUS_ID),
	index IXS_ORDER_REC_ID(RECURRING_ID),
	index IX_SOO_AFFILIATE_ID(AFFILIATE_ID),
	index IXS_ORDER_UPDATED_1C(UPDATED_1C),
	index IXS_SALE_COUNT(USER_ID,LID,PAYED,CANCELED),
	index IXS_DATE_UPDATE(DATE_UPDATE),
	index IXS_XML_ID(XML_ID),
	index IXS_ID_1C(ID_1C),
	index IX_BSO_DATE_ALLOW_DELIVERY(DATE_ALLOW_DELIVERY),
	index IX_BSO_ALLOW_DELIVERY(ALLOW_DELIVERY),
	index IX_BSO_DATE_CANCELED(DATE_CANCELED),
	index IX_BSO_CANCELED(CANCELED),
	index IX_BSO_DATE_PAYED(DATE_PAYED),
	index IX_BSO_DATE_INSERT(DATE_INSERT),
	index IX_BSO_DATE_PAY_BEFORE(DATE_PAY_BEFORE),
	unique IXS_ACCOUNT_NUMBER(ACCOUNT_NUMBER)
);


create table if not exists b_sale_person_type
(
	ID int not null auto_increment,
	LID char(2) not null,
	NAME varchar(255) not null,
	CODE varchar(255) null,
	SORT int not null default '150',
	ACTIVE VARCHAR(1) NOT NULL default 'Y',
	ENTITY_REGISTRY_TYPE varchar(255) null,
	XML_ID varchar(255) null,
	primary key (ID),
	index IXS_PERSON_TYPE_LID(LID)
);

create table if not exists b_sale_order_props_group
(
	ID int not null auto_increment,
	PERSON_TYPE_ID int not null,
	NAME varchar(255) not null,
	CODE varchar(50) default null,
	SORT int not null default '100',
	primary key (ID),
	index IXS_ORDER_PROPS_GROUP_PERSON_TYPE_ID(PERSON_TYPE_ID)
);

create table if not exists b_sale_order_props
(
	ID int not null auto_increment,
	PERSON_TYPE_ID int not null,
	NAME varchar(255) not null,
	TYPE varchar(20) not null,
	REQUIRED char(1) not null default 'N',
	DEFAULT_VALUE varchar(500) null,
	SORT int not null default '100',
	USER_PROPS char(1) not null default 'N',
	IS_LOCATION char(1) not null default 'N',
	PROPS_GROUP_ID int not null,
	DESCRIPTION varchar(255) null,
	IS_EMAIL char(1) not null default 'N',
	IS_PROFILE_NAME char(1) not null default 'N',
	IS_PAYER char(1) not null default 'N',
	IS_LOCATION4TAX char(1) not null default 'N',
	IS_FILTERED char(1) not null default 'N',
	CODE varchar(50) null,
	IS_ZIP char(1) not null default 'N',
	IS_PHONE char(1) not null default 'N',
	ACTIVE VARCHAR(1) NOT NULL default 'Y',
	UTIL VARCHAR(1) NOT NULL default 'N',
	INPUT_FIELD_LOCATION INT(11) NOT NULL default '0',
	MULTIPLE CHAR(1) NOT NULL default 'N',
	IS_ADDRESS char(1) not null default 'N',
	IS_ADDRESS_FROM char(1) not null default 'N',
	IS_ADDRESS_TO char(1) not null default 'N',
	SETTINGS varchar(500) null,
	ENTITY_REGISTRY_TYPE varchar(255) null,
	XML_ID varchar(255) null,
	ENTITY_TYPE varchar(255) not null,
	primary key (ID),
	index IXS_ORDER_PROPS_PERSON_TYPE_ID(PERSON_TYPE_ID),
	index IXS_ORDER_PROPS_TYPE(TYPE),
	index IXS_CODE_OPP(CODE)
);

create table if not exists b_sale_order_props_value
(
	ID int not null auto_increment,
	ORDER_ID int not null,
	ORDER_PROPS_ID int null,
	NAME varchar(255) not null,
	VALUE varchar(500) null,
	CODE varchar(50) null,
	XML_ID varchar(255) null,
	ENTITY_ID int not null,
	ENTITY_TYPE varchar(255) not null,
	primary key (ID),
	unique IX_SOPV_ENT_PROP_UNI(ENTITY_ID, ENTITY_TYPE(200), ORDER_PROPS_ID),
	index IX_SOPV_ORD_PROP_UNI(ORDER_ID, ORDER_PROPS_ID)
);

create table if not exists b_sale_order_props_variant
(
	ID int not null auto_increment,
	ORDER_PROPS_ID int not null,
	NAME varchar(255) not null,
	VALUE varchar(255) null,
	SORT int not null default '100',
	DESCRIPTION varchar(255) null,
	XML_ID varchar(255) null,
	primary key (ID),
	index IXS_ORDER_PROPS_VARIANT_ORDER_PROPS_ID(ORDER_PROPS_ID)
);

create table if not exists b_sale_order_props_relation
(
	PROPERTY_ID INT NOT NULL,
	ENTITY_ID VARCHAR(35) NOT NULL,
	ENTITY_TYPE CHAR(1) NOT NULL,
	PRIMARY KEY (PROPERTY_ID, ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_sale_pay_system_action
(
	ID int not null auto_increment,
	PAY_SYSTEM_ID int null,
	PERSON_TYPE_ID int null,
	NAME varchar(255) not null,
	PSA_NAME varchar(255) not null,
	CODE varchar(50) NULL,
	SORT int not null default '100',
	DESCRIPTION varchar(2000) null,
	ACTION_FILE varchar(255) null,
	RESULT_FILE varchar(255) null,
	NEW_WINDOW char(1) not null default 'Y',
	ACTIVE char(1) not null default 'Y',
	PS_MODE VARCHAR(20) NULL,
	PS_CLIENT_TYPE varchar(10) default null,
	PARAMS text null,
	TARIF text null,
	HAVE_PAYMENT char(1) not null default 'N',
	HAVE_ACTION char(1) not null default 'N',
	AUTO_CHANGE_1C char(1) not null default 'N',
	HAVE_RESULT char(1) not null default 'N',
	HAVE_PRICE char(1) not null default 'N',
	HAVE_PREPAY char(1) not null default 'N',
	HAVE_RESULT_RECEIVE char(1) not null default 'N',
	ALLOW_EDIT_PAYMENT char(1) not null default 'Y',
	ENCODING varchar(45) null,
	LOGOTIP int null,
	IS_CASH char(1) not null default 'N',
	CAN_PRINT_CHECK char(1) not null default 'N',
	ENTITY_REGISTRY_TYPE varchar(255) null,
	XML_ID varchar(255) null,
	primary key (ID),
	KEY B_SALE_PAY_SYSTEM_ACTION_PS_CLIENT_TYPE(PS_CLIENT_TYPE)
);

create table if not exists b_sale_pay_system_rest_handlers
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	CODE varchar(50) NULL,
	SORT int not null default '100',
	SETTINGS text null,
	APP_ID varchar(128) null,
	unique IX_SALE_PS_HANDLER_CODE(CODE),
	primary key (ID)
);

create table if not exists b_sale_location_country
(
	ID int not null auto_increment,
	NAME varchar(100) not null,
	SHORT_NAME varchar(100) null,
	primary key (ID),
	index IX_NAME(NAME)
);

create table if not exists b_sale_location_country_lang
(
	ID int not null auto_increment,
	COUNTRY_ID int not null,
	LID char(2) not null,
	NAME varchar(100) not null,
	SHORT_NAME varchar(100) null,
	primary key (ID),
	unique IXS_LOCAT_CNTR_LID(COUNTRY_ID, LID)
);

create table if not exists b_sale_location_region
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	SHORT_NAME varchar(100) null,
	primary key (ID)
);

create table if not exists b_sale_location_region_lang
(
	ID int not null auto_increment,
	REGION_ID int not null,
	LID char(2) not null,
	NAME varchar(100) not null,
	SHORT_NAME varchar(100) null,
	primary key (ID),
	unique IXS_LOCAT_REGION_LID(REGION_ID, LID),
	index IXS_NAME(NAME)
);

create table if not exists b_sale_location_city
(
	ID int not null auto_increment,
	NAME varchar(100) not null,
	SHORT_NAME varchar(100) null,
	REGION_ID int null,
	primary key (ID),
	index IXS_LOCAT_REGION_ID(REGION_ID)
);

create table if not exists b_sale_location_city_lang
(
	ID int not null auto_increment,
	CITY_ID int not null,
	LID char(2) not null,
	NAME varchar(100) not null,
	SHORT_NAME varchar(100) null,
	primary key (ID),
	unique IXS_LOCAT_CITY_LID(CITY_ID, LID),
	index IX_NAME(NAME)
);

create table if not exists b_sale_location_zip (
	ID int(11) NOT NULL auto_increment,
	LOCATION_ID int(11) NOT NULL default '0',
	ZIP varchar(10) NOT NULL default '',
	PRIMARY KEY  (ID),
	index IX_LOCATION_ID (LOCATION_ID),
	index IX_ZIP (ZIP)
);

create table if not exists b_sale_location
(
	ID int not null auto_increment,
	SORT int NOT NULL default '100',
	CODE varchar(100) not null,
	LEFT_MARGIN int,
	RIGHT_MARGIN int,
	PARENT_ID int default '0',
	DEPTH_LEVEL int default '1',
	TYPE_ID int,
	LATITUDE decimal(8,6),
	LONGITUDE decimal(9,6),
	COUNTRY_ID int,
	REGION_ID int,
	CITY_ID int,
	LOC_DEFAULT char(1) NOT NULL default 'N',
	primary key (ID),
	unique IX_SALE_LOCATION_CODE(CODE),
	index IX_SALE_LOCATION_MARGINS(LEFT_MARGIN, RIGHT_MARGIN),
	index IX_SALE_LOCATION_MARGINS_REV(RIGHT_MARGIN, LEFT_MARGIN),
	index IX_SALE_LOCATION_PARENT(PARENT_ID),
	index IX_SALE_LOCATION_DL(DEPTH_LEVEL),
	index IX_SALE_LOCATION_TYPE(TYPE_ID),
	index IXS_LOCATION_COUNTRY_ID(COUNTRY_ID),
	index IXS_LOCATION_REGION_ID(REGION_ID),
	index IXS_LOCATION_CITY_ID(CITY_ID),
	index IXS_LOCATION_SORT(SORT),
	index IX_SALE_LOCATION_TYPE_MARGIN (TYPE_ID, LEFT_MARGIN, RIGHT_MARGIN)
);

create table if not exists b_sale_loc_name
(
	ID int not null auto_increment,
	LANGUAGE_ID char(2) not null,
	LOCATION_ID int not null,
	NAME varchar(100) not null,
	NAME_UPPER varchar(100) not null,
	NAME_NORM varchar(100) null,
	SHORT_NAME varchar(100),
	primary key (ID),
	index IX_SALE_L_NAME_NAME_UPPER(NAME_UPPER),
	index IX_SALE_L_NAME_NAME_NORM(NAME_NORM),
	index IX_SALE_L_NAME_LID_LID(LOCATION_ID, LANGUAGE_ID)
);

create table if not exists b_sale_loc_ext_srv
(
	ID int not null auto_increment,
	CODE varchar(100) not null,
	primary key (ID)
);

create table if not exists b_sale_loc_ext
(
	ID int not null auto_increment,
	SERVICE_ID int not null,
	LOCATION_ID int not null,
	XML_ID varchar(100) not null,
	primary key (ID),
	index IX_B_SALE_LOC_EXT_LID_SID(LOCATION_ID, SERVICE_ID),
	index IX_B_SALE_LOC_EXT_XML_SID(XML_ID, SERVICE_ID)
);

create table if not exists b_sale_loc_type
(
	ID int not null auto_increment,
	CODE varchar(30) not null,
	SORT int default '100',
	DISPLAY_SORT int default '100',
	primary key (ID)
);

create table if not exists b_sale_loc_type_name
(
	ID int not null auto_increment,
	LANGUAGE_ID char(2) not null,
	NAME varchar(100) not null,
	TYPE_ID int not null,
	primary key (ID),
	index IX_SALE_L_TYPE_NAME_TID_LID(TYPE_ID, LANGUAGE_ID)
);

create table if not exists b_sale_loc_2site
(
	LOCATION_ID int not null,
	SITE_ID char(2) not null,
	LOCATION_TYPE char(1) not null default 'L',
	primary key (SITE_ID, LOCATION_ID, LOCATION_TYPE)
);

create table if not exists b_sale_loc_def2site(
	LOCATION_CODE varchar(100) not null,
	SITE_ID char(2) not null,
	SORT int default '100',
	primary key (LOCATION_CODE, SITE_ID)
);

create table if not exists b_sale_location_group
(
	ID int not null auto_increment,
	CODE varchar(100) not null,
	SORT int not null default '100',
	primary key (ID),
	unique IX_SALE_LOCATION_GROUP_CODE(CODE)
);

create table if not exists b_sale_location_group_lang
(
	ID int not null auto_increment,
	LOCATION_GROUP_ID int not null,
	LID char(2) not null,
	NAME varchar(250) not null,
	primary key (ID),
	unique IX_LOCATION_GROUP_LID(LOCATION_GROUP_ID, LID)
);

create table if not exists b_sale_location2location_group
(
	LOCATION_ID int not null,
	LOCATION_GROUP_ID int not null,
	primary key (LOCATION_ID, LOCATION_GROUP_ID)
);

create table if not exists b_sale_delivery2location
(
	DELIVERY_ID int not null,
	LOCATION_CODE varchar(100) not null,
	LOCATION_TYPE char(2) not null default 'L',
	primary key (DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)
);

create table if not exists b_sale_company2location
(
	COMPANY_ID int not null,
	LOCATION_CODE varchar(100) not null,
	LOCATION_TYPE char(1) not null default 'L',
	primary key (COMPANY_ID, LOCATION_CODE, LOCATION_TYPE)
);

create table if not exists b_sale_company2service
(
	COMPANY_ID int not null,
	SERVICE_ID int not null,
	SERVICE_TYPE int not null,
	primary key (COMPANY_ID, SERVICE_ID, SERVICE_TYPE)
);

create table if not exists b_sale_discount
(
	ID int not null auto_increment,
	XML_ID varchar(255) null,
	LID char(2) not null,
	NAME varchar(255) null,
	PRICE_FROM decimal(18, 2) null,	-- deprecated
	PRICE_TO decimal(18, 2) null,	-- deprecated
	CURRENCY char(3) null,
	DISCOUNT_VALUE decimal(18, 2) not null,	-- deprecated
	DISCOUNT_TYPE char(1) not null default 'P',	-- deprecated
	ACTIVE char(1) not null default 'Y',
	SORT int not null default '100',
	ACTIVE_FROM datetime null,
	ACTIVE_TO datetime null,
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	PRIORITY int(18) not null default 1,
	LAST_DISCOUNT char(1) not null default 'Y',
	LAST_LEVEL_DISCOUNT char(1) default 'N',
	VERSION int not null default 1,
	CONDITIONS mediumtext null,
	UNPACK mediumtext null,
	ACTIONS mediumtext null,
	APPLICATION mediumtext null,
	PREDICTION_TEXT text null,
	PREDICTIONS mediumtext null,
	PREDICTIONS_APP mediumtext null,
	USE_COUPONS char(1) not null default 'N',
	EXECUTE_MODULE varchar(50) not null default 'all',
	EXECUTE_MODE int DEFAULT 0,
	HAS_INDEX char(1) default 'N',
	PRESET_ID varchar(255) null,
	SHORT_DESCRIPTION text null,
	primary key (ID),
	index IXS_DISCOUNT_LID(LID),
	index IX_SSD_ACTIVE_DATE(ACTIVE_FROM, ACTIVE_TO),
	index IX_PRESET_ID(PRESET_ID)
);

create table if not exists b_sale_discount_coupon
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ACTIVE char(1) not null default 'Y',
	ACTIVE_FROM datetime null,
	ACTIVE_TO datetime null,
	COUPON varchar(32) not null,
	TYPE int not null default 0,
	MAX_USE int not null default 0,
	USE_COUNT int not null default 0,
	USER_ID int not null default 0,
	DATE_APPLY datetime null,
	TIMESTAMP_X datetime null,
	MODIFIED_BY int(18) null,
	DATE_CREATE datetime null,
	CREATED_BY int(18) null,
	DESCRIPTION text null,
	primary key (ID),
	index IX_S_D_COUPON(COUPON)
);

create table if not exists b_sale_discount_group
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ACTIVE char(1) null,
	GROUP_ID int not null,
	PRIMARY KEY (ID),
	UNIQUE IX_S_DISGRP (DISCOUNT_ID, GROUP_ID),
	UNIQUE IX_S_DISGRP_G (GROUP_ID, DISCOUNT_ID)
);

create table if not exists b_sale_discount_module
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	MODULE_ID varchar(50) not null,
	primary key (ID),
	index IX_SALE_DSC_MOD(DISCOUNT_ID)
);

create table if not exists b_sale_discount_entities
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	MODULE_ID varchar(50) not null,
	ENTITY varchar(255) not null,
	FIELD_ENTITY varchar(255) not null,
	FIELD_TABLE varchar(255) not null,
	primary key (ID),
	index IX_SALE_DSC_ENT_DISCOUNT_ID(DISCOUNT_ID)
);

create table if not exists b_sale_order_discount
(
	ID int not null auto_increment,
	MODULE_ID varchar(50) not null,
	DISCOUNT_ID int not null,
	NAME varchar(255) not null,
	DISCOUNT_HASH varchar(32) not null,
	CONDITIONS mediumtext null,
	UNPACK mediumtext null,
	ACTIONS mediumtext null,
	APPLICATION mediumtext null,
	USE_COUPONS char(1) not null,
	SORT int not null,
	PRIORITY int not null,
	LAST_DISCOUNT char(1) not null,
	ACTIONS_DESCR mediumtext null,
	primary key (ID),
	index IX_SALE_ORDER_DSC_HASH(DISCOUNT_HASH)
);

create table if not exists b_sale_order_coupons
(
	ID int not null auto_increment,
	ORDER_ID int not null,
	ORDER_DISCOUNT_ID int not null,
	COUPON varchar(32) not null,
	TYPE int not null,
	COUPON_ID int not null,
	DATA text null,
	primary key (ID),
	index IX_SALE_ORDER_CPN_ORDER(ORDER_ID)
);

create table if not exists b_sale_order_modules
(
	ID int not null auto_increment,
	ORDER_DISCOUNT_ID int not null,
	MODULE_ID varchar(50) not null,
	primary key (ID),
	index IX_SALE_ORDER_MDL_DSC(ORDER_DISCOUNT_ID)
);

create table if not exists b_sale_order_rules
(
	ID int not null auto_increment,
	MODULE_ID varchar(50) not null,
	ORDER_DISCOUNT_ID int not null,
	ORDER_ID int not null,
	ENTITY_TYPE int not null,
	ENTITY_ID int not null,
	ENTITY_VALUE varchar(255) null,
	COUPON_ID int not null,
	APPLY char(1) not null,
	ACTION_BLOCK_LIST text null,
	APPLY_BLOCK_COUNTER int not null default 0,
	primary key (ID),
	index IX_SALE_ORDER_RULES_ORD(ORDER_ID)
);

create table if not exists b_sale_order_rules_descr
(
	ID int not null auto_increment,
	MODULE_ID varchar(50) not null,
	ORDER_DISCOUNT_ID int not null,
	ORDER_ID int not null,
	RULE_ID int not null,
	DESCR text not null,
	primary key (ID),
	index IX_SALE_ORDER_RULES_DS_ORD(ORDER_ID),
	index IX_SALE_ORDER_RULES_DS_RULE(RULE_ID)
);

create table if not exists b_sale_order_discount_data
(
	ID int not null auto_increment,
	ORDER_ID int not null,
	ENTITY_TYPE int not null,
	ENTITY_ID int not null,
	ENTITY_VALUE varchar(255) null,
	ENTITY_DATA mediumtext not null,
	primary key (ID),
	index IX_SALE_DSC_DATA_CMX(ORDER_ID, ENTITY_TYPE)
);

create table if not exists b_sale_order_round
(
	ID int not null auto_increment,
	ORDER_ID int not null,
	APPLY_BLOCK_COUNTER int not null default 0,
	ORDER_ROUND char(1) not null,
	ENTITY_TYPE int not null,
	ENTITY_ID int not null,
	ENTITY_VALUE varchar(255) null,
	APPLY char(1) not null,
	ROUND_RULE mediumtext not null,
	primary key (ID),
	index IX_SALE_ORDER_ROUND_ORD(ORDER_ID)
);

create table if not exists b_sale_user_props
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	USER_ID int not null,
	PERSON_TYPE_ID int not null,
	DATE_UPDATE datetime not null,
	XML_ID varchar(50) null,
	VERSION_1C varchar(15) null,
	primary key (ID),
	index IXS_USER_PROPS_USER_ID(USER_ID),
	index IXS_USER_PROPS_PERSON_TYPE_ID(PERSON_TYPE_ID),
	index IXS_USER_PROPS_XML_ID(XML_ID)
);

create table if not exists b_sale_d_ix_element
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ELEMENT_ID int not null,
	primary key (ID),
	index IX_S_DIXE_O_1(ELEMENT_ID, DISCOUNT_ID)
);

create table if not exists b_sale_d_ix_section
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	SECTION_ID int not null,
	primary key (ID),
	index IX_S_DIXS_O_1(SECTION_ID, DISCOUNT_ID)
);

create table if not exists b_sale_user_props_value
(
	ID int not null auto_increment,
	USER_PROPS_ID int not null,
	ORDER_PROPS_ID int not null,
	NAME varchar(255) not null,
	VALUE varchar(255) null,
	primary key (ID),
	index IXS_USER_PROPS_VALUE_USER_PROPS_ID(USER_PROPS_ID),
	index IXS_USER_PROPS_VALUE_ORDER_PROPS_ID(ORDER_PROPS_ID)
);

create table if not exists b_sale_status
(
	ID varchar(2) not null,
	TYPE char(1) not null default 'O',
	SORT int not null default '100',
	NOTIFY char(1) not null default 'Y',
	COLOR varchar(10) null,
	XML_ID varchar(255) null,
	primary key (ID)
);

create table if not exists b_sale_status_lang
(
	STATUS_ID varchar(2) not null,
	LID char(2) not null,
	NAME varchar(100) not null,
	DESCRIPTION varchar(250) null,
	primary key (STATUS_ID, LID)
);

create table b_sale_status_group_task
(
	STATUS_ID varchar(2) not null,
	GROUP_ID  int(18) not null,
	TASK_ID   int(18) not null,
	primary key (STATUS_ID, GROUP_ID, TASK_ID)
);

create table if not exists b_sale_tax
(
	ID int not null auto_increment,
	LID char(2) not null,
	NAME varchar(250) not null,
	DESCRIPTION varchar(255) null,
	TIMESTAMP_X datetime not null,
	CODE varchar(50) null,
	primary key (ID),
	index itax_lid(LID)
);

create table if not exists b_sale_tax_rate
(
	ID int not null auto_increment,
	TAX_ID int not null,
	PERSON_TYPE_ID int null,
	VALUE decimal(18,4) not null,
	CURRENCY char(3) null,
	IS_PERCENT char(1) not null default 'Y',
	IS_IN_PRICE char(1) not null default 'N',
	APPLY_ORDER int not null default '100',
	TIMESTAMP_X datetime not null,
	ACTIVE char(1) not null default 'Y',
	primary key (ID),
	index itax_pers_type(PERSON_TYPE_ID),
	index itax_lid(TAX_ID),
	index itax_inprice(IS_IN_PRICE)
);

create table if not exists b_sale_tax2location
(
	TAX_RATE_ID int not null,
	LOCATION_CODE varchar(100) not null,
	LOCATION_TYPE char(1) not null default 'L',
	primary key (TAX_RATE_ID, LOCATION_CODE, LOCATION_TYPE)
);

create table if not exists b_sale_tax_exempt2group
(
	GROUP_ID int not null,
	TAX_ID int not null,
	primary key (GROUP_ID, TAX_ID)
);

create table if not exists b_sale_order_tax
(
	ID int not null auto_increment,
	ORDER_ID int not null,
	TAX_NAME varchar(255) not null,
	VALUE decimal(18,4) null,
	VALUE_MONEY decimal(18,4) not null,
	APPLY_ORDER int not null,
	CODE varchar(50) null,
	IS_PERCENT char(1) not null default 'Y',
	IS_IN_PRICE char(1) not null default 'N',
	primary key (ID),
	index ixs_sot_order_id(ORDER_ID)
);

create table if not exists b_sale_order_flags2group
(
	ID int not null auto_increment,
	GROUP_ID int not null,
	ORDER_FLAG char(1) not null,
	primary key (ID),
	unique ix_sale_ordfla2group(GROUP_ID, ORDER_FLAG)
);

create table if not exists b_sale_site2group
(
	ID int not null auto_increment,
	GROUP_ID int not null,
	SITE_ID char(2) not null,
	primary key (ID),
	unique ix_sale_site2group(GROUP_ID, SITE_ID)
);

create table if not exists b_sale_user_account
(
	ID int not null auto_increment,
	USER_ID int not null,
	TIMESTAMP_X timestamp not null,
	CURRENT_BUDGET decimal(18,4) not null default '0.0',
	CURRENCY char(3) not null,
	LOCKED char(1) not null default 'N',
	DATE_LOCKED datetime null,
	NOTES text null,
	primary key (ID),
	unique IX_S_U_USER_ID(USER_ID, CURRENCY)
);

create table if not exists b_sale_recurring
(
	ID int not null auto_increment,
	USER_ID int not null,
	TIMESTAMP_X timestamp not null,
	MODULE varchar(100) null,
	PRODUCT_ID int null,
	PRODUCT_NAME varchar(255) null,
	PRODUCT_URL varchar(255) null,
	PRODUCT_PRICE_ID int null,
	PRICE_TYPE char(1) not null default 'R',
	RECUR_SCHEME_TYPE char(1) not null default 'M',
	RECUR_SCHEME_LENGTH int not null default '0',
	WITHOUT_ORDER char(1) not null default 'N',
	PRICE decimal not null default '0.0',
	CURRENCY char(3) null,
	CANCELED char(1) not null default 'N',
	DATE_CANCELED datetime null,
	PRIOR_DATE datetime null,
	NEXT_DATE datetime not null,
	CALLBACK_FUNC varchar(100) null,
	PRODUCT_PROVIDER_CLASS varchar(100) null,
	DESCRIPTION varchar(255) null,
	CANCELED_REASON varchar(255) null,
	ORDER_ID int not null,
	REMAINING_ATTEMPTS int not null default '0',
	SUCCESS_PAYMENT char(1) not null default 'Y',
	primary key (ID),
	index IX_S_R_USER_ID(USER_ID),
	index IX_S_R_NEXT_DATE(NEXT_DATE, CANCELED, REMAINING_ATTEMPTS),
	index IX_S_R_PRODUCT_ID(MODULE, PRODUCT_ID, PRODUCT_PRICE_ID)
);

create table if not exists b_sale_user_cards
(
	ID int not null auto_increment,
	USER_ID int not null,
	ACTIVE char(1) not null default 'Y',
	SORT int not null default '100',
	TIMESTAMP_X timestamp not null,
	PAY_SYSTEM_ACTION_ID int not null,
	CURRENCY char(3) null,
	CARD_TYPE varchar(20) not null,
	CARD_NUM text not null,
	CARD_CODE varchar(5) null,
	CARD_EXP_MONTH int not null,
	CARD_EXP_YEAR int not null,
	DESCRIPTION varchar(255) null,
	SUM_MIN decimal(18,4) null,
	SUM_MAX decimal(18,4) null,
	SUM_CURRENCY char(3) null,
	LAST_STATUS char(1) null,
	LAST_STATUS_CODE varchar(5) null,
	LAST_STATUS_DESCRIPTION varchar(250) null,
	LAST_STATUS_MESSAGE varchar(255) null,
	LAST_SUM decimal(18,4) null,
	LAST_CURRENCY char(3) null,
	LAST_DATE datetime null,
	primary key (ID),
	index IX_S_U_C_USER_ID(USER_ID, ACTIVE, CURRENCY)
);


create table if not exists b_sale_user_transact
(
	ID int not null auto_increment,
	USER_ID int not null,
	TIMESTAMP_X timestamp not null,
	TRANSACT_DATE datetime not null,
	AMOUNT decimal(18,4) not null default '0.0',
	CURRENCY char(3) not null,
	DEBIT char(1) not null default 'N',
	ORDER_ID int null,
	DESCRIPTION varchar(255) not null,
	NOTES text null,
	PAYMENT_ID int null,
	EMPLOYEE_ID int(11) null,
	primary key (ID),
	index IX_S_U_T_USER_ID_CURRENCY(USER_ID, CURRENCY),
	index IX_S_U_T_ORDER_ID(ORDER_ID),
	index IX_S_U_T_PAYMENT_ID(PAYMENT_ID)
);

create table if not exists b_sale_affiliate_plan
(
	ID int not null auto_increment,
	SITE_ID char(2) not null,
	NAME varchar(250) not null,
	DESCRIPTION text null,
	TIMESTAMP_X timestamp not null,
	ACTIVE char(1) not null default 'Y',
	BASE_RATE decimal(18,4) not null default '0',
	BASE_RATE_TYPE char(1) not null default 'P',
	BASE_RATE_CURRENCY char(3) null,
	MIN_PAY decimal(18,4) not null default '0',
	MIN_PLAN_VALUE decimal(18,4) null,
	VALUE_CURRENCY char(3) null,
	primary key (ID)
);

create table if not exists b_sale_affiliate
(
	ID int not null auto_increment,
	SITE_ID char(2) not null,
	USER_ID int not null,
	AFFILIATE_ID int null,
	PLAN_ID int not null,
	ACTIVE char(1) not null default 'Y',
	TIMESTAMP_X timestamp not null,
	DATE_CREATE datetime not null,
	PAID_SUM decimal(18,4) not null default '0',
	APPROVED_SUM decimal(18,4) not null default '0',
	PENDING_SUM decimal(18,4) not null default '0',
	ITEMS_NUMBER int not null default '0',
	ITEMS_SUM decimal(18,4) not null default '0',
	LAST_CALCULATE datetime null,
	AFF_SITE varchar(200) null,
	AFF_DESCRIPTION text null,
	FIX_PLAN char(1) not null default 'N',
	primary key (ID),
	unique IX_SAA_USER_ID(USER_ID, SITE_ID),
	index IX_SAA_AFFILIATE_ID(AFFILIATE_ID)
);

create table if not exists b_sale_affiliate_plan_section
(
	ID int not null auto_increment,
	PLAN_ID int not null,
	MODULE_ID varchar(50) not null default 'catalog',
	SECTION_ID varchar(255) not null,
	RATE decimal(18,4) not null default '0',
	RATE_TYPE char(1) not null default 'P',
	RATE_CURRENCY char(3) null,
	primary key (ID),
	unique IX_SAP_PLAN_ID(PLAN_ID, MODULE_ID, SECTION_ID)
);

create table if not exists b_sale_affiliate_tier
(
	ID int not null auto_increment,
	SITE_ID char(2) not null,
	RATE1 decimal(18,4) not null default '0',
	RATE2 decimal(18,4) not null default '0',
	RATE3 decimal(18,4) not null default '0',
	RATE4 decimal(18,4) not null default '0',
	RATE5 decimal(18,4) not null default '0',
	primary key (ID),
	unique IX_SAT_SITE_ID(SITE_ID)
);

create table if not exists b_sale_affiliate_transact
(
	ID int not null auto_increment,
	AFFILIATE_ID int not null,
	TIMESTAMP_X timestamp not null,
	TRANSACT_DATE datetime not null,
	AMOUNT decimal(18,4) not null,
	CURRENCY char(3) not null,
	DEBIT char(1) not null default 'N',
	DESCRIPTION varchar(100) not null,
	EMPLOYEE_ID int null,
	primary key (ID),
	index IX_SAT_AFFILIATE_ID(AFFILIATE_ID)
);

create table if not exists b_sale_export
(
	ID int not null auto_increment,
	PERSON_TYPE_ID int not null,
	VARS text null,
	primary key (ID)
);

create table if not exists b_sale_order_delivery (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	ORDER_ID INT(11) NOT NULL,
	ACCOUNT_NUMBER varchar(100) null,
	DATE_INSERT DATETIME NOT NULL,
	DATE_REQUEST DATETIME NULL DEFAULT NULL,
	DATE_UPDATE DATETIME NULL DEFAULT NULL,
	DELIVERY_LOCATION VARCHAR(50) NULL DEFAULT NULL,
	PARAMS TEXT NULL,
	STATUS_ID VARCHAR(2) NOT NULL,
	PRICE_DELIVERY DECIMAL(18,4) NULL DEFAULT NULL,
	DISCOUNT_PRICE DECIMAL(18,4) NULL DEFAULT NULL,
	BASE_PRICE_DELIVERY DECIMAL(18,4) NULL DEFAULT NULL,
	CUSTOM_PRICE_DELIVERY CHAR(1) NULL DEFAULT NULL,
	ALLOW_DELIVERY CHAR(1) NULL DEFAULT 'N',
	DATE_ALLOW_DELIVERY DATETIME NULL DEFAULT NULL,
	EMP_ALLOW_DELIVERY_ID INT(11) NULL DEFAULT NULL,
	DEDUCTED CHAR(1) NULL DEFAULT 'N',
	DATE_DEDUCTED DATETIME NULL DEFAULT NULL,
	EMP_DEDUCTED_ID INT(11) NULL DEFAULT NULL,
	REASON_UNDO_DEDUCTED VARCHAR(255) NULL DEFAULT NULL,
	RESERVED CHAR(1) NULL DEFAULT NULL,
	DELIVERY_ID INT(11) NOT NULL,
	DELIVERY_DOC_NUM VARCHAR(20) NULL DEFAULT NULL,
	DELIVERY_DOC_DATE DATETIME NULL DEFAULT NULL,
	TRACKING_NUMBER VARCHAR(255) NULL DEFAULT NULL,
	XML_ID VARCHAR(255) NULL DEFAULT NULL,
	DELIVERY_NAME VARCHAR(128) NULL DEFAULT NULL,
	CANCELED CHAR(1) NULL DEFAULT 'N',
	DATE_CANCELED DATETIME NULL DEFAULT NULL,
	EMP_CANCELED_ID INT(11) NULL DEFAULT NULL,
	REASON_CANCELED VARCHAR(255) NULL DEFAULT '',
	MARKED CHAR(1) NULL DEFAULT NULL,
	DATE_MARKED DATETIME NULL DEFAULT NULL,
	EMP_MARKED_ID INT(11) NULL DEFAULT NULL,
	REASON_MARKED VARCHAR(255) NULL DEFAULT NULL,
	CURRENCY VARCHAR(3) NULL DEFAULT NULL,
	`SYSTEM` CHAR(1) NOT NULL DEFAULT 'N',
	WEIGHT double(18, 4) default 0,
	RESPONSIBLE_ID int(11) DEFAULT NULL,
	EMP_RESPONSIBLE_ID int(11) DEFAULT NULL,
	DATE_RESPONSIBLE_ID datetime DEFAULT NULL,
	COMMENTS text,
	COMPANY_ID int(11) DEFAULT NULL,
	TRACKING_STATUS INT(11) NULL,
	TRACKING_DESCRIPTION VARCHAR(255) NULL,
	TRACKING_LAST_CHECK DATETIME NULL,
	TRACKING_LAST_CHANGE DATETIME NULL,
	ID_1C VARCHAR(36) NULL DEFAULT NULL,
	VERSION_1C VARCHAR(15) NULL DEFAULT NULL,
	EXTERNAL_DELIVERY CHAR(1) NOT NULL DEFAULT 'N',
	UPDATED_1C CHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID),
	INDEX IX_BSOD_ORDER_ID (ORDER_ID),
	INDEX IX_BSOD_DATE_ALLOW_DELIVERY (DATE_ALLOW_DELIVERY),
	INDEX IX_BSOD_ALLOW_DELIVERY (ALLOW_DELIVERY),
	INDEX IX_BSOD_DATE_CANCELED (DATE_CANCELED),
	INDEX IX_BSOD_CANCELED (CANCELED),
	unique IXS_DLV_ACCOUNT_NUMBER(ACCOUNT_NUMBER)
);

create table if not exists b_sale_order_dlv_basket(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	ORDER_DELIVERY_ID INT(11) NOT NULL,
	BASKET_ID INT(11) NOT NULL,
	DATE_INSERT DATETIME NOT NULL,
	QUANTITY DECIMAL(18,4) NOT NULL,
	RESERVED_QUANTITY DECIMAL(18,4) NOT NULL,
	XML_ID varchar(255) null,
	PRIMARY KEY (ID),
	INDEX IX_BSODB_ORDER_DELIVERY_ID (ORDER_DELIVERY_ID),
	INDEX IX_S_O_DB_BASKET_ID (BASKET_ID)
);

create table if not exists b_sale_order_delivery_req (
	ID int not null auto_increment,
	ORDER_ID int not null,
	DATE_REQUEST datetime null,
	DELIVERY_LOCATION VARCHAR(50) NULL DEFAULT NULL,
	PARAMS TEXT NULL,
	SHIPMENT_ID int null,
	PRIMARY KEY  (ID),
	index IX_ORDER_ID (ORDER_ID),
	index IX_SHIPMENT_ID (SHIPMENT_ID)
);


create table if not exists b_sale_order_payment(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	ORDER_ID INT(11) NOT NULL,
	ACCOUNT_NUMBER varchar(100) null,
	PAID CHAR(1) NOT NULL DEFAULT 'N',
	DATE_PAID DATETIME NULL DEFAULT NULL,
	EMP_PAID_ID INT(11) NULL DEFAULT NULL,
	PAY_SYSTEM_ID INT(11) NOT NULL,
	PS_STATUS CHAR(1) NULL DEFAULT NULL,
	PS_INVOICE_ID VARCHAR(250) NULL,
	PS_STATUS_CODE VARCHAR(255) NULL DEFAULT NULL,
	PS_STATUS_DESCRIPTION VARCHAR(512) NULL DEFAULT NULL,
	PS_STATUS_MESSAGE VARCHAR(250) NULL DEFAULT NULL,
	PS_SUM DECIMAL(18,4) NULL DEFAULT NULL,
	PS_CURRENCY CHAR(3) NULL DEFAULT NULL,
	PS_RESPONSE_DATE DATETIME NULL DEFAULT NULL,
	PS_RECURRING_TOKEN VARCHAR(255) NULL DEFAULT NULL,
	PS_CARD_NUMBER VARCHAR(64) NULL DEFAULT NULL,
	PAY_VOUCHER_NUM VARCHAR(20) NULL DEFAULT NULL,
	PAY_VOUCHER_DATE DATE NULL DEFAULT NULL,
	DATE_PAY_BEFORE DATETIME NULL DEFAULT NULL,
	DATE_BILL DATETIME NULL DEFAULT NULL,
	XML_ID VARCHAR(255) NULL DEFAULT NULL,
	SUM DECIMAL(18,4) NOT NULL,
	PRICE_COD DECIMAL(18,4) NOT NULL DEFAULT 0,
	CURRENCY CHAR(3) NOT NULL,
	PAY_SYSTEM_NAME VARCHAR(128) NOT NULL,
	RESPONSIBLE_ID int(11) DEFAULT NULL,
	DATE_RESPONSIBLE_ID datetime DEFAULT NULL,
	EMP_RESPONSIBLE_ID int(11) DEFAULT NULL,
	COMMENTS text,
	COMPANY_ID int(11) DEFAULT NULL,
	PAY_RETURN_DATE date DEFAULT NULL,
	EMP_RETURN_ID INT(11) NULL DEFAULT NULL,
	PAY_RETURN_NUM VARCHAR(20) DEFAULT NULL,
	PAY_RETURN_COMMENT text,
	IS_RETURN CHAR(1) NOT NULL DEFAULT 'N',
	MARKED CHAR(1) NULL DEFAULT NULL,
	DATE_MARKED DATETIME NULL DEFAULT NULL,
	EMP_MARKED_ID INT(11) NULL DEFAULT NULL,
	REASON_MARKED VARCHAR(255) NULL DEFAULT NULL,
	ID_1C VARCHAR(36) NULL DEFAULT NULL,
	VERSION_1C VARCHAR(15) NULL DEFAULT NULL,
	EXTERNAL_PAYMENT CHAR(1) NOT NULL DEFAULT 'N',
	UPDATED_1C CHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID),
	INDEX IX_BSOP_ORDER_ID (ORDER_ID),
	INDEX IX_BSOP_DATE_PAID (DATE_PAID),
	INDEX IX_BSOP_PAID (PAID),
	unique IXS_PAY_ACCOUNT_NUMBER(ACCOUNT_NUMBER)
);

create table if not exists b_sale_order_payment_item(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	PAYMENT_ID INT(11) NOT NULL,
	ENTITY_ID INT(11) NOT NULL,
	ENTITY_TYPE varchar(15) NOT NULL,
	DATE_INSERT DATETIME NOT NULL,
	QUANTITY DECIMAL(18,4) NOT NULL,
	XML_ID varchar(255) null,
	PRIMARY KEY (ID),
	INDEX IX_S_O_PI_ENTITY_ID_TYPE (ENTITY_ID, ENTITY_TYPE),
	INDEX IX_S_O_PI_PAYMENT_ID (PAYMENT_ID)
);

create table if not exists b_sale_product2product
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	PARENT_PRODUCT_ID int not null,
	CNT int not null,
	primary key (ID),
	index IXS_PRODUCT2PRODUCT_PRODUCT_ID(PRODUCT_ID)
);

create table if not exists b_sale_order_product_stat
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	RELATED_PRODUCT_ID int not null,
	ORDER_DATE datetime not null,
	CNT int not null DEFAULT 1,
	primary key (ID),
	unique IXS_PRODUCT2PRODUCT_ON_DATE (PRODUCT_ID, RELATED_PRODUCT_ID, ORDER_DATE),
	index IXS_ORDER_DATE (ORDER_DATE)
);

create table if not exists b_sale_person_type_site (
	PERSON_TYPE_ID int(18) NOT NULL default '0',
	SITE_ID char(2) NOT NULL default '',
	PRIMARY KEY  (PERSON_TYPE_ID, SITE_ID)
);

create table if not exists b_sale_viewed_product (
	ID int(11) unsigned NOT NULL AUTO_INCREMENT,
	FUSER_ID int(11) unsigned NOT NULL DEFAULT '0',
	DATE_VISIT datetime NOT NULL,
	PRODUCT_ID int(11) unsigned NOT NULL DEFAULT '0',
	MODULE varchar(100) NULL,
	LID char(2) NOT NULL,
	NAME varchar(255) NOT NULL,
	DETAIL_PAGE_URL varchar(255) NULL,
	CURRENCY char(3) NULL,
	PRICE decimal(18,2) NOT NULL DEFAULT '0.00',
	NOTES varchar(255) NULL,
	PREVIEW_PICTURE int(11) NULL,
	DETAIL_PICTURE int(11) NULL,
	CALLBACK_FUNC varchar(45) NULL,
	PRODUCT_PROVIDER_CLASS varchar(100) NULL,
	PRIMARY KEY (ID),
	index ixLID (FUSER_ID, LID),
	index ixPRODUCT_ID (PRODUCT_ID),
	index ixDATE_VISIT (DATE_VISIT)
);

create table if not exists b_sale_order_history (
	ID int(11) unsigned not null auto_increment,
	H_USER_ID int(11) unsigned not null,
	H_DATE_INSERT datetime not null,
	H_ORDER_ID int(11) unsigned not null,
	H_CURRENCY char(3) not null,
	PERSON_TYPE_ID int(11) unsigned null,
	PAYED char(1) null,
	DATE_PAYED datetime null,
	EMP_PAYED_ID int(11) unsigned null,
	CANCELED char(1) null,
	DATE_CANCELED datetime null,
	REASON_CANCELED varchar(255) null,
	STATUS_ID varchar(2) not null,
	DATE_STATUS datetime null,
	PRICE_DELIVERY decimal(18,2) null,
	ALLOW_DELIVERY char(1) null,
	DATE_ALLOW_DELIVERY datetime null,
	RESERVED char(1) null,
	DEDUCTED char(1) null,
	DATE_DEDUCTED datetime null,
	REASON_UNDO_DEDUCTED varchar(255) null,
	MARKED char(1) null,
	DATE_MARKED datetime null,
	REASON_MARKED varchar(255) null,
	PRICE decimal(18, 2) null,
	CURRENCY char(3) null,
	DISCOUNT_VALUE decimal(18,2) null,
	USER_ID int(11) unsigned null,
	PAY_SYSTEM_ID int(11) unsigned null,
	DELIVERY_ID varchar(50) null,
	PS_STATUS char(1) null,
	PS_STATUS_CODE char(5) null,
	PS_STATUS_DESCRIPTION varchar(250) null,
	PS_STATUS_MESSAGE varchar(250) null,
	PS_SUM decimal(18,2) null,
	PS_CURRENCY char(3) null,
	PS_RESPONSE_DATE datetime null,
	TAX_VALUE decimal(18,2) null,
	STAT_GID varchar(255) null,
	SUM_PAID decimal(18,2) null,
	PAY_VOUCHER_NUM varchar(20) null,
	PAY_VOUCHER_DATE date null,
	AFFILIATE_ID int(11) unsigned null,
	DELIVERY_DOC_NUM varchar(20) null,
	DELIVERY_DOC_DATE date null,
	primary key (ID),
	index ixH_ORDER_ID(H_ORDER_ID)
);

create table if not exists b_sale_delivery2paysystem (
	DELIVERY_ID int(11) NOT NULL,
	LINK_DIRECTION char(1) NOT NULL,
	PAYSYSTEM_ID int(11) NOT NULL,
	index IX_DELIVERY (DELIVERY_ID),
	index IX_PAYSYSTEM (PAYSYSTEM_ID),
	index LINK_DIRECTION (LINK_DIRECTION)
);

create table if not exists b_sale_store_barcode (
	ID INT NOT NULL AUTO_INCREMENT,
	BASKET_ID INT NOT NULL,
	BARCODE VARCHAR(100) NULL,
	MARKING_CODE VARCHAR(200) NULL,
	STORE_ID INT NULL,
	QUANTITY DOUBLE NOT NULL,
	DATE_CREATE DATETIME NULL,
	DATE_MODIFY DATETIME NULL,
	CREATED_BY INT NULL,
	MODIFIED_BY INT NULL,
	ORDER_DELIVERY_BASKET_ID INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (ID),
	INDEX IX_BSSB_O_DLV_BASKET_ID (ORDER_DELIVERY_BASKET_ID)
);

create table if not exists b_sale_order_change
(
	ID INT NOT NULL AUTO_INCREMENT,
	ORDER_ID INT NOT NULL,
	TYPE VARCHAR(255) NOT NULL,
	DATA VARCHAR(512) NULL,
	DATE_CREATE datetime NOT NULL,
	DATE_MODIFY datetime NOT NULL,
	USER_ID INT NOT NULL,
	ENTITY VARCHAR(50) NULL DEFAULT NULL,
	ENTITY_ID INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (ID),
	index `IXS_ORDER_ID_CHANGE` (`ORDER_ID`),
	index `IXS_TYPE_CHANGE` (`TYPE`)
);

create table if not exists b_sale_order_processing (
	ORDER_ID int(11) DEFAULT '0',
	PRODUCTS_ADDED char(1) DEFAULT 'N',
	PRODUCTS_REMOVED char(1) DEFAULT 'N',
	index IX_ORDER_ID(ORDER_ID)
);

create table if not exists b_sale_tp
(
	ID int NOT NULL AUTO_INCREMENT,
	CODE varchar(20) NOT NULL,
	ACTIVE char(1) NOT NULL,
	NAME varchar(500) NOT NULL,
	DESCRIPTION text NULL,
	SETTINGS text NULL,
	CATALOG_SECTION_TAB_CLASS_NAME varchar(255) NULL,
	CLASS varchar(255) NULL,
	XML_ID varchar(255) null,
	primary key (ID),
	unique IX_CODE(CODE)
);

create table if not exists b_sale_delivery_srv
(
	ID int NOT NULL AUTO_INCREMENT,
	CODE varchar(50) NULL,
	PARENT_ID int NULL,
	NAME varchar(255) NOT NULL,
	ACTIVE char(1) NOT NULL,
	DESCRIPTION text NULL,
	SORT int NOT NULL,
	LOGOTIP int NULL,
	CONFIG longtext NULL,
	CLASS_NAME varchar(255) NOT NULL,
	CURRENCY char(3) NOT NULL,
	TRACKING_PARAMS VARCHAR(255) NULL,
	ALLOW_EDIT_SHIPMENT char(1) NOT NULL DEFAULT 'Y',
	VAT_ID INT NULL,
	XML_ID varchar(255) null,
	primary key (ID),
	index IX_BSD_SRV_CODE(CODE),
	index IX_BSD_SRV_PARENT_ID(PARENT_ID)
);

create table if not exists b_sale_service_rstr
(
	ID int NOT NULL AUTO_INCREMENT,
	SERVICE_ID int NOT NULL,
	SERVICE_TYPE int NOT NULL,
	SORT int DEFAULT 100,
	CLASS_NAME varchar(255) NOT NULL,
	PARAMS text,
	primary key (ID),
	INDEX IX_BSSR_SERVICE_ID(SERVICE_ID)
);

create table if not exists b_sale_delivery_es
(
	ID int NOT NULL AUTO_INCREMENT,
	CODE varchar(50) NULL,
	NAME varchar(255) NOT NULL,
	DESCRIPTION varchar(255) NULL,
	CLASS_NAME varchar(255) NOT NULL,
	PARAMS text NULL,
	RIGHTS char(3) NOT NULL,
	DELIVERY_ID int NOT NULL,
	INIT_VALUE varchar(255) NULL,
	ACTIVE char(1) NOT NULL,
	SORT int DEFAULT 100,
	primary key (ID),
	index IX_BSD_ES_DELIVERY_ID(DELIVERY_ID)
);

create table if not exists b_sale_company
(
	ID int not null auto_increment,
	NAME varchar(128) not null,
	LOCATION_ID varchar(128) null,
	CODE varchar(45) null,
	SORT int default 100,
	XML_ID varchar(45) null,
	ACTIVE char(1) not null default 'Y',
	DATE_CREATE datetime null,
	DATE_MODIFY datetime null,
	CREATED_BY int null,
	MODIFIED_BY int null,
	ADDRESS VARCHAR(255) NULL,
	primary key(ID)
);

create table if not exists b_sale_bizval
(
	CODE_KEY varchar(50) not null,
	CONSUMER_KEY varchar(50) not null,
	PERSON_TYPE_ID int not null,
	PROVIDER_KEY varchar(50) not null,
	PROVIDER_VALUE text null,
	primary key(CODE_KEY, CONSUMER_KEY, PERSON_TYPE_ID)
);

create table if not exists b_sale_bizval_persondomain
(
	PERSON_TYPE_ID int not null,
	DOMAIN char(1) not null,
	primary key(PERSON_TYPE_ID, DOMAIN)
);

create table b_sale_bizval_code_1c
(
	PERSON_TYPE_ID int not null,
	CODE_INDEX int not null,
	NAME varchar(255) not null,
	primary key(PERSON_TYPE_ID, CODE_INDEX)
);

create table if not exists b_sale_order_delivery_es
(
	ID INT NOT NULL AUTO_INCREMENT,
	SHIPMENT_ID INT NOT NULL,
	EXTRA_SERVICE_ID INT NOT NULL,
	VALUE VARCHAR (255) NULL,
	PRIMARY KEY (ID),
	INDEX IX_BSOD_ES_SHIPMENT_ID(SHIPMENT_ID),
	INDEX IX_BSOD_ES_EXTRA_SERVICE_ID(EXTRA_SERVICE_ID)
);

create table if not exists b_sale_tp_map
(
	ID int NOT NULL AUTO_INCREMENT,
	ENTITY_ID INT NOT NULL,
	VALUE_EXTERNAL VARCHAR(100) NOT NULL,
	VALUE_INTERNAL VARCHAR(100) NOT NULL,
	PARAMS TEXT NULL,
	PRIMARY KEY (ID),
	UNIQUE IX_BSTPM_E_V_V(ENTITY_ID, VALUE_EXTERNAL, VALUE_INTERNAL)
);

create table if not exists b_sale_tp_map_entity
(
	ID int NOT NULL AUTO_INCREMENT,
	TRADING_PLATFORM_ID INT NOT NULL,
	CODE VARCHAR (255) NOT NULL,
	PRIMARY KEY (ID),
	unique IX_CODE_TRADING_PLATFORM_ID(TRADING_PLATFORM_ID, CODE)
);

create table if not exists b_sale_tp_ebay_cat
(
	ID int NOT NULL AUTO_INCREMENT,
	NAME varchar (255) NOT NULL,
	CATEGORY_ID int NOT NULL,
	PARENT_ID int NOT NULL,
	LEVEL int NOT NULL,
	LAST_UPDATE datetime NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_tp_ebay_cat_var
(
	ID int NOT NULL AUTO_INCREMENT,
	CATEGORY_ID int NOT NULL,
	NAME varchar(255) NOT NULL,
	VALUE text NULL,
	REQUIRED char(1) NOT NULL,
	MIN_VALUES int NOT NULL,
	MAX_VALUES int NOT NULL,
	SELECTION_MODE varchar (255) NOT NULL,
	ALLOWED_AS_VARIATION char(1) NOT NULL,
	HELP_URL varchar(255) NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_tp_ebay_fq
(
	ID int NOT NULL AUTO_INCREMENT,
	FEED_TYPE varchar(50) NOT NULL,
	DATA LONGTEXT NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_tp_ebay_fr
(
	ID int NOT NULL AUTO_INCREMENT,
	FILENAME varchar(255) NOT NULL,
	FEED_TYPE varchar(50) NOT NULL,
	UPLOAD_TIME datetime NOT NULL,
	PROCESSING_REQUEST_ID varchar(50) NULL,
	PROCESSING_RESULT varchar(100) NULL,
	RESULTS LONGTEXT NULL,
	IS_SUCCESS varchar(1) NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_tp_order
(
	ID int NOT NULL AUTO_INCREMENT,
	ORDER_ID INT NOT NULL,
	TRADING_PLATFORM_ID int NOT NULL,
	EXTERNAL_ORDER_ID varchar(100) NOT NULL,
	PARAMS text NULL,
	XML_ID varchar(255) null,
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_UNIQ_NUMBERS (ORDER_ID, TRADING_PLATFORM_ID, EXTERNAL_ORDER_ID)
);

create table if not exists b_sale_gift_related_data
(
	ID INT NOT NULL AUTO_INCREMENT,
	DISCOUNT_ID INT NOT NULL,
	ELEMENT_ID INT,
	SECTION_ID INT,
	MAIN_PRODUCT_SECTION_ID INT,
	PRIMARY KEY (ID),
	KEY IX_S_GRD_O_1 (DISCOUNT_ID),
	KEY IX_S_GRD_O_2 (MAIN_PRODUCT_SECTION_ID)
);

create table if not exists b_sale_pay_system_err_log
(
	ID int NOT NULL AUTO_INCREMENT,
	MESSAGE TEXT NOT NULL,
	DATE_INSERT datetime NOT NULL,
	primary key (ID)
);

create table if not exists b_sale_yandex_settings
(
	SHOP_ID INT NOT NULL,
	CSR TEXT NULL,
	SIGN TEXT NULL,
	CERT TEXT NULL,
	PKEY TEXT NULL,
	PUB_KEY TEXT NULL,
	primary key (SHOP_ID)
);

create table if not exists b_sale_hdaln (
	LOCATION_ID INT NOT NULL,
	LEFT_MARGIN INT NOT NULL,
	RIGHT_MARGIN INT NOT NULL,
	NAME varchar(100) NOT NULL,
	PRIMARY KEY (`LOCATION_ID`),
	INDEX IX_BSHDALN_NAME(NAME)
);

create table if not exists b_sale_entity_marker (
	ID int(11) not null auto_increment,
	ORDER_ID int(11) not null,
	ENTITY_TYPE varchar(25) not null,
	ENTITY_ID int(11) not null,
	TYPE varchar(10) not null,
	CODE varchar(255) not null,
	MESSAGE varchar(255) not null,
	COMMENT varchar(500) null default null,
	USER_ID int(11) null default null,
	DATE_CREATE datetime null default null,
	DATE_UPDATE datetime null default null,
	SUCCESS char(1) not null default 'N',
	primary key (ID),
	index IX_BSEM_TYPE (TYPE),
	index IX_BSEM_ENTITY_TYPE (ENTITY_TYPE)
);

create table if not exists b_sale_tp_vk_profile (
	ID int NOT NULL AUTO_INCREMENT,
	DESCRIPTION VARCHAR(255) NOT NULL,
	PLATFORM_ID INT NOT NULL,
	VK_SETTINGS text NULL,
	EXPORT_SETTINGS text NULL,
	OAUTH text NULL,
	PROCESS text NULL,
	JOURNAL text NULL,
	primary key (ID)
);

create table if not exists b_sale_tp_vk_log (
	ID int NOT NULL AUTO_INCREMENT,
	EXPORT_ID INT NOT NULL,
	ERROR_CODE VARCHAR(255) NULL,
	ITEM_ID VARCHAR(255) NULL,
	TIME datetime NULL,
	ERROR_PARAMS text NULL,
	PRIMARY KEY (ID),
	UNIQUE IX_BSTPVKL_I_E_E(ID, EXPORT_ID, ERROR_CODE)
);

create table if not exists b_sale_order_archive(
	ID int NOT NULL AUTO_INCREMENT,
	LID char(2) NOT NULL,
	ORDER_ID int NOT NULL,
	ACCOUNT_NUMBER varchar(100) NOT NULL,
	DATE_INSERT datetime NOT NULL,
	PERSON_TYPE_ID int NOT NULL,
	USER_ID int NOT NULL,
	STATUS_ID varchar(2) NOT NULL,
	PAYED char(1) NOT NULL DEFAULT 'N',
	DEDUCTED char(1) NOT NULL DEFAULT 'N',
	CANCELED char(1) NOT NULL DEFAULT 'N',
	PRICE decimal(18, 4) NOT NULL,
	SUM_PAID decimal(18,2) NULL,
	CURRENCY char(3) NOT NULL,
	XML_ID varchar(255) NULL,
	ID_1C varchar(36) NULL,
	ORDER_DATA mediumtext NULL,
	RESPONSIBLE_ID int(11) NULL,
	COMPANY_ID int(11) NULL,
	VERSION int NOT NULL,
	DATE_ARCHIVED datetime NOT NULL,
	primary key (ID),
	index IXS_USER_ID(USER_ID),
	index IXS_STATUS_ID(STATUS_ID),
	index IXS_DATE_INSERT(DATE_INSERT),
	index IXS_DATE_ARCHIVED(DATE_ARCHIVED),
	index IXS_XML_ID(XML_ID),
	index IXS_ID_1C(ID_1C),
	index IXS_RESPONSIBLE_ID(RESPONSIBLE_ID),
	index IXS_COMPANY_ID(COMPANY_ID),
	index IXS_ORDER_ID(ORDER_ID),
	index IXS_ACCOUNT_NUMBER(ACCOUNT_NUMBER)
);

create table if not exists b_sale_order_archive_packed(
	ORDER_ARCHIVE_ID int NOT NULL,
	ORDER_DATA mediumtext NULL,
	PRIMARY KEY (ORDER_ARCHIVE_ID)
);

create table if not exists b_sale_basket_archive(
	ID int NOT NULL AUTO_INCREMENT,
	ARCHIVE_ID int NOT NULL,
	PRODUCT_ID int NOT NULL,
	PRODUCT_PRICE_ID int NULL,
	NAME varchar(255) NOT NULL,
	PRICE decimal(18, 4) NULL,
	CURRENCY char(3) NULL,
	QUANTITY double(18, 4) NULL,
	WEIGHT double(18, 4) NULL,
	DATE_INSERT datetime NOT NULL,
	MODULE varchar(100) NULL,
	PRODUCT_XML_ID varchar(100) NULL,
	TYPE int(11) NULL,
	SET_PARENT_ID int(11) NULL,
	MEASURE_CODE int(11) NULL,
	MEASURE_NAME varchar(50),
	BASKET_DATA mediumtext NULL,
	primary key (ID),
	index IXS_PRODUCT_ID(PRODUCT_ID),
	index IXS_ARCHIVE_ID(ARCHIVE_ID)
);

create table if not exists b_sale_basket_archive_packed(
	BASKET_ARCHIVE_ID int NOT NULL,
	BASKET_DATA mediumtext NULL,
	PRIMARY KEY (BASKET_ARCHIVE_ID)
);

create table if not exists b_sale_company_group (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	COMPANY_ID INT(11) NOT NULL,
	GROUP_ID INT(11) NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_B_SALE_COMP_GRP_CMP_ID (COMPANY_ID)
);

create table if not exists b_sale_company_resp_grp (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	COMPANY_ID INT(11) NOT NULL,
	GROUP_ID INT(11) NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_B_SALE_COMP_RESP_GRP_CMP_ID (COMPANY_ID)
);

create table if not exists b_sale_cashbox (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	NAME varchar(255) NOT NULL,
	HANDLER varchar(255) NOT NULL,
	EMAIL varchar(255) NOT NULL,
	DATE_CREATE datetime NOT NULL,
	DATE_LAST_CHECK datetime NULL,
	SORT int default 100,
	ACTIVE char(1) not null default 'Y',
	USE_OFFLINE char(1) not null default 'N',
	ENABLED char(1) not null default 'N',
	KKM_ID varchar(255) NULL,
	OFD varchar(255) NULL,
	OFD_SETTINGS text NULL,
	NUMBER_KKM varchar(64) NULL,
	SETTINGS text NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_cashbox_err_log (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	CASHBOX_ID INT(11) NULL,
	DATE_INSERT datetime NOT NULL,
	MESSAGE TEXT,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_cashbox_check (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	CASHBOX_ID INT(11) NULL,
	EXTERNAL_UUID varchar(100) NULL,
	PAYMENT_ID INT(11) NULL,
	SHIPMENT_ID INT(11) NULL,
	CNT_FAIL_PRINT INT default 0,
	ORDER_ID INT(11) NULL,
	DATE_CREATE datetime NOT NULL,
	DATE_PRINT_START datetime NULL,
	DATE_PRINT_END datetime NULL,
	`SUM` decimal(18, 4) NULL,
	CURRENCY char(3) NULL,
	STATUS char(1) not null default 'N',
	`TYPE` varchar(255) not null,
	ENTITY_REGISTRY_TYPE varchar(255) not null,
	LINK_PARAMS text NULL,
	ERROR_MESSAGE text default NULL,
	PRIMARY KEY (ID),
	INDEX IX_SALE_CHECK_ORDER_ID (ORDER_ID),
	INDEX IX_SALE_CHECK_PAYMENT_ID (PAYMENT_ID),
	INDEX IX_SALE_CHECK_SHIPMENT_ID (SHIPMENT_ID),
	INDEX IX_SALE_CHECK_STATUS (STATUS)
);

create table if not exists b_sale_cashbox_check_correction (
	ID int(11) unsigned not null auto_increment,
	CHECK_ID int(11) not null,
	CORRECTION_TYPE varchar(50) not null,
	DOCUMENT_NUMBER varchar(35) not null,
	DOCUMENT_DATE date not null,
	DESCRIPTION varchar(255) default '',
	CORRECTION_PAYMENT text default '',
	CORRECTION_VAT text default '',
	PRIMARY KEY (ID)
);

create table if not exists b_sale_check2cashbox(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	CHECK_ID INT(11) NOT NULL,
	CASHBOX_ID INT(11) NOT NULL,
	PRIMARY KEY (ID),
	UNIQUE IX_SALE_CHECK2CB_UNI(CHECK_ID, CASHBOX_ID)
);

create table if not exists b_sale_cashbox_z_report (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	CASHBOX_ID INT(11) NOT NULL,
	DATE_CREATE datetime NOT NULL,
	DATE_PRINT_START datetime NULL,
	DATE_PRINT_END datetime NULL,
	CASH_SUM decimal(18, 4) NULL,
	CASHLESS_SUM decimal(18, 4) NULL,
	CUMULATIVE_SUM decimal(18, 4) NULL,
	RETURNED_SUM decimal(18, 4) NULL,
	STATUS char(1) not null default 'N',
	CNT_FAIL_PRINT INT default 0,
	LINK_PARAMS text NULL,
	CURRENCY char(3) NULL,
	PRIMARY KEY (ID),
	INDEX IX_SALE_Z_REPORT_CASHBOX_ID (CASHBOX_ID)
);

create table if not exists b_sale_cashbox_connect (
	HASH VARCHAR(100) NOT NULL,
	ACTIVE char(1) not null default 'Y',
	DATE_CREATE datetime NOT NULL,
	PRIMARY KEY (HASH)
);

create table if not exists b_sale_buyer_stat (
	ID INT(11) NOT NULL AUTO_INCREMENT,
	USER_ID int not null,
	LID char(2) not null,
	CURRENCY char(3) not null,
	LAST_ORDER_DATE datetime not null,
	COUNT_FULL_PAID_ORDER int null,
	COUNT_PART_PAID_ORDER int null,
	SUM_PAID decimal(18, 4) null,
	PRIMARY KEY (ID),
	index IXS_CURRENCY_LID_SELECTOR(CURRENCY, LID, USER_ID),
	index IXS_ORDER_USER_ID(USER_ID),
	index IXS_LAST_ORDER_DATE(LAST_ORDER_DATE),
	index IXS_COUNT_FULL_PAID_ORDER(COUNT_FULL_PAID_ORDER),
	index IXS_COUNT_PART_PAID_ORDER(COUNT_PART_PAID_ORDER),
	index IXS_SUM_PAID(SUM_PAID)
);

create table if not exists b_sale_delivery_req(
	ID INT NOT NULL AUTO_INCREMENT,
	DATE TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	DELIVERY_ID INT NOT NULL,
	STATUS INT NULL,
	CREATED_BY INT NULL,
	EXTERNAL_ID VARCHAR(100) NOT NULL,
    EXTERNAL_STATUS VARCHAR(255) DEFAULT NULL,
    EXTERNAL_STATUS_SEMANTIC VARCHAR(50) DEFAULT NULL,
	EXTERNAL_PROPERTIES longtext NULL,
	PRIMARY KEY (ID),
	index IX_SALE_DELIVERY_REQUEST_DELIVERY_ID_EXTERNAL_ID(DELIVERY_ID, EXTERNAL_ID)
);

create table if not exists b_sale_delivery_req_shp(
	ID INT NOT NULL AUTO_INCREMENT,
	SHIPMENT_ID INT NOT NULL,
	REQUEST_ID INT NULL,
	EXTERNAL_ID VARCHAR(50) NULL,
	ERROR_DESCRIPTION VARCHAR(2048) NULL,
	PRIMARY KEY (ID),
	index IX_SALE_DELIVERY_REQ_SHP_ID_REQUEST_ID_EXTERNAL_ID(REQUEST_ID, EXTERNAL_ID)
);

create table if not exists b_sale_check_related_entities (
	ID int not null AUTO_INCREMENT,
	CHECK_ID int not null,
	ENTITY_ID int not null,
	ENTITY_TYPE char(1) not null,
	ENTITY_CHECK_TYPE varchar(50) null,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_exchange_log (
	ID INT NOT NULL AUTO_INCREMENT,
	ENTITY_ID INT NOT NULL,
	ENTITY_TYPE_ID INT NOT NULL,
	PARENT_ID INT NULL DEFAULT NULL,
	OWNER_ENTITY_ID INT NULL DEFAULT NULL,
	ENTITY_DATE_UPDATE DATETIME NULL DEFAULT NULL,
	XML_ID VARCHAR(50) NULL DEFAULT NULL,
	MARKED VARCHAR(1) NULL DEFAULT NULL,
	DESCRIPTION TEXT NULL,
	MESSAGE LONGTEXT NULL,
	DATE_INSERT DATETIME NULL DEFAULT NULL,
	DIRECTION VARCHAR(1) NOT NULL,
	PROVIDER VARCHAR(50) NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_EXCHANGE_LOG1 (ENTITY_ID, ENTITY_TYPE_ID),
	INDEX IX_EXCHANGE_LOG2 (ENTITY_DATE_UPDATE),
	INDEX IX_EXCHANGE_LOG3 (DATE_INSERT)
);

create table if not exists b_sale_synchronizer_log (
	ID INT NOT NULL AUTO_INCREMENT,
	MESSAGE_ID TEXT NULL,
	MESSAGE LONGTEXT NULL,
	DATE_INSERT DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (ID),
	INDEX IX_SYNCHRONIZER_LOG1 (DATE_INSERT)
);

create table if not exists b_sale_order_converter_crm_error (
	ID INT NOT NULL AUTO_INCREMENT,
	ORDER_ID INT NOT NULL,
	ERROR TEXT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_usergroup_restr(
	ID INT NOT NULL AUTO_INCREMENT,
	ENTITY_ID INT NOT NULL,
	ENTITY_TYPE_ID INT NOT NULL,
	GROUP_ID INT NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_BSUG_ETI_EI_GI(ENTITY_TYPE_ID, ENTITY_ID, GROUP_ID)
);

create table if not exists b_sale_documentgenerator_callback_registry(
	ID INT NOT NULL AUTO_INCREMENT,
	DATE_INSERT datetime not null,
	MODULE_ID VARCHAR(50) NOT NULL,
	DOCUMENT_ID INT NOT NULL,
	CALLBACK_CLASS VARCHAR(100) NOT NULL,
	CALLBACK_METHOD VARCHAR(100) NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_order_entities_custom_fields(
	ID INT NOT NULL AUTO_INCREMENT,
	ENTITY_ID INT not null,
	ENTITY_TYPE varchar(25) not null,
	ENTITY_REGISTRY_TYPE varchar(15) not null,
	FIELD varchar(25) not null,
	INDEX IX_SALE_ENTITY_CUSTOM_FIELDS(ENTITY_ID, ENTITY_TYPE, ENTITY_REGISTRY_TYPE),
	PRIMARY KEY (ID)
);

create table if not exists b_sale_domain_verification
(
	ID int not null auto_increment,
	DOMAIN varchar(255) not null,
	PATH varchar(255) not null,
	CONTENT text,
	ENTITY varchar(1024) not null,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_b24integration_bind(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	SRC_ENTITY_TYPE_ID INT(11) UNSIGNED NOT NULL,
	SRC_ENTITY_ID INT(11) UNSIGNED NOT NULL,
	DST_ENTITY_TYPE_ID INT(11) UNSIGNED NOT NULL,
	DST_ENTITY_ID INT(11) UNSIGNED NOT NULL,
	CREATED_TIME DATETIME NULL,
	LAST_UPDATED_TIME DATETIME NULL,
	PRIMARY KEY (SRC_ENTITY_TYPE_ID, SRC_ENTITY_ID, DST_ENTITY_TYPE_ID, DST_ENTITY_ID),
	INDEX IX_BSIB_ID (ID)
);

create table if not exists b_sale_b24integration_relation(
	SRC_ENTITY_TYPE_ID INT(11) UNSIGNED NOT NULL,
	SRC_ENTITY_ID INT(11) UNSIGNED NOT NULL,
	DST_ENTITY_TYPE_ID INT(11) UNSIGNED NOT NULL,
	DST_ENTITY_ID INT(11) UNSIGNED NOT NULL,
	CREATED_TIME DATETIME NULL DEFAULT NULL,
	LAST_UPDATED_TIME DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (SRC_ENTITY_ID, DST_ENTITY_TYPE_ID, SRC_ENTITY_TYPE_ID)
);

create table if not exists b_sale_b24integration_token(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	GUID VARCHAR(100) NOT NULL,
	ACCESS_TOKEN VARCHAR(100) NOT NULL,
	REFRESH_TOKEN VARCHAR(100) NOT NULL,
	REST_ENDPOINT VARCHAR(255) NOT NULL,
	PORTAL_ID VARCHAR(100) NOT NULL,
	CREATED DATETIME NOT NULL,
	CHANGED DATETIME NOT NULL,
	EXPIRES DATETIME NOT NULL,
	PRIMARY KEY (ID)
);

create table if not exists b_sale_delivery_rest_handler
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	CODE varchar(50) not null,
	SORT int not null default '100',
	DESCRIPTION text null,
	SETTINGS text not null,
	PROFILES text not null,
	APP_ID varchar(128) null,
	unique IX_SALE_DELIVERY_HANDLER_CODE(CODE),
	primary key (ID)
);

create table if not exists b_sale_b24integration_stat_provider(
	ID INT(11) NOT NULL AUTO_INCREMENT,
	NAME VARCHAR(255) NOT NULL DEFAULT '',
	EXTERNAL_SERVER_HOST VARCHAR(255) NOT NULL DEFAULT '',
	XML_ID VARCHAR(255) NOT NULL DEFAULT '',
	TIMESTAMP_X TIMESTAMP NOT NULL,
	SETTINGS TEXT NULL DEFAULT NULL,
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_BICS_XML_ID (XML_ID)
);

create table if not exists b_sale_b24integration_stat(
	ID BIGINT(20) NOT NULL AUTO_INCREMENT,
	ENTITY_TYPE_ID INT(11) NOT NULL,
	ENTITY_ID INT(11) NOT NULL,
	DATE_UPDATE DATETIME NOT NULL,
	PROVIDER_ID INT(11) NOT NULL,
	CURRENCY CHAR(3) NOT NULL,
	STATUS CHAR(1) NOT NULL,
	XML_ID VARCHAR(255) NOT NULL DEFAULT '',
	AMOUNT DECIMAL(18,4) NOT NULL,
	TIMESTAMP_X DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_BSIS_ID_TYPE_ID (ENTITY_ID, ENTITY_TYPE_ID, PROVIDER_ID)
);

create table if not exists b_sale_cashbox_rest_handler
(
    ID int not null auto_increment,
    NAME varchar(255) not null,
    CODE varchar(50) not null,
    SORT int not null default '100',
    SETTINGS text not null,
    APP_ID varchar(128) null,
    unique IX_CASHBOX_HANDLER_CODE(CODE),
    primary key (ID)
);

create table if not exists b_sale_delivery_yandex_taxi_claims(
    ID INT NOT NULL AUTO_INCREMENT,
    CREATED_AT DATETIME NOT NULL,
    UPDATED_AT DATETIME NOT NULL,
    FURTHER_CHANGES_EXPECTED char(1) NOT NULL DEFAULT 'Y',
    SHIPMENT_ID INT NOT NULL,
    INITIAL_CLAIM TEXT NOT NULL,
    EXTERNAL_ID VARCHAR(255) NOT NULL,
    EXTERNAL_STATUS VARCHAR(255) NOT NULL,
    EXTERNAL_RESOLUTION VARCHAR(20) DEFAULT NULL,
    EXTERNAL_CREATED_TS VARCHAR(255) NOT NULL,
    EXTERNAL_UPDATED_TS VARCHAR(255) NOT NULL,
    EXTERNAL_CURRENCY char(3) DEFAULT NULL,
    EXTERNAL_FINAL_PRICE decimal(19,4) DEFAULT NULL,
    IS_SANDBOX_ORDER char(1) NOT NULL DEFAULT 'N',
    UNIQUE IX_UNIQUE_EXTERNAL_ID (EXTERNAL_ID),
    KEY IX_FURTHER_CHANGES_EXPECTED (FURTHER_CHANGES_EXPECTED),
    KEY `IX_REPORT_DATE` (`CREATED_AT`,`IS_SANDBOX_ORDER`),
    PRIMARY KEY (ID)
);

create table if not exists b_sale_basket_reservation(
	ID int unsigned NOT NULL AUTO_INCREMENT,
	QUANTITY double(18, 4) NOT NULL,
	DATE_RESERVE DATETIME NOT NULL,
	DATE_RESERVE_END DATETIME NOT NULL,
	RESERVED_BY INT(18) unsigned NULL,
	BASKET_ID INT unsigned NOT NULL,
	STORE_ID INT unsigned NULL,
	INDEX IX_SALE_BASKET_RESERVATION_BASKET_ID(BASKET_ID),
	PRIMARY KEY (ID)
);

create table if not exists b_sale_facebook_conversion_params(
	ID INT unsigned NOT NULL AUTO_INCREMENT,
	EVENT_NAME VARCHAR(50) NOT NULL,
	LID CHAR(2) NOT NULL,
	ENABLED CHAR(1) NOT NULL,
	PARAMS VARCHAR(500) NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_FACEBOOK_CONVERSION_EVENT_NAME_LID(EVENT_NAME, LID)
);

create table if not exists b_sale_analytics(
	ID INT unsigned NOT NULL AUTO_INCREMENT,
	CODE VARCHAR(255) NOT NULL,
	CREATED_AT DATETIME NOT NULL,
	PAYLOAD TEXT NULL,
	PRIMARY KEY (ID),
	INDEX IX_SALE_ANALYTICS_CREATED_AT(CREATED_AT),
	INDEX IX_SALE_ANALYTICS_CODE_CREATED_AT(CODE, CREATED_AT)
);

create table if not exists b_sale_order_payment_ps_available(
	ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	PAYMENT_ID INT NOT NULL,
	PAY_SYSTEM_ID INT NOT NULL,
	KEY B_SALE_ORDER_PAYMENT_PS_AVAIABLE_PAYMENT_ID(PAYMENT_ID)
);

create table if not exists b_sale_basket_reservation_history (
	ID int(11) NOT NULL AUTO_INCREMENT,
	RESERVATION_ID int(11) NOT NULL,
	DATE_RESERVE datetime NOT NULL,
	QUANTITY float NOT NULL,
	PRIMARY KEY (ID),
	KEY B_SALE_BASKET_RESERVATION_HISTORY_RESERVATION_ID (RESERVATION_ID)
);
