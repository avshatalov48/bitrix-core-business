create table if not exists b_landing
(
    ID int(18) not null auto_increment,
    CODE varchar(255) default null,
    RULE varchar(255) default null,
    ACTIVE char(1) not null default 'Y',
    DELETED char(1) not null default 'N',
    PUBLIC char(1) not null default 'Y',
    TITLE varchar(255) not null,
    XML_ID varchar(255) default null,
    DESCRIPTION varchar(255) default null,
    TPL_ID int(18),
    TPL_CODE varchar(255) default null,
    SITE_ID int(18) not null,
    SITEMAP char(1) not null default 'N',
    FOLDER char(1) not null default 'N',
    FOLDER_ID int(18),
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    DATE_PUBLIC timestamp null,
    PRIMARY KEY(ID),
    INDEX IX_B_LAND_CODE (CODE),
    INDEX IX_B_LAND_ACTIVE (ACTIVE),
    INDEX IX_B_LAND_DELETED (DELETED),
    INDEX IX_B_LAND_XML_ID (XML_ID),
    INDEX IX_B_LAND_SITEMAP (SITEMAP),
    INDEX IX_B_LAND_FOLDER (FOLDER),
    INDEX IX_B_LAND_FOLDER_ID (FOLDER_ID)
);

create table if not exists b_landing_block
(
    ID int(18) not null auto_increment,
    PARENT_ID int(18) default null,
    LID int(18) not null,
    CODE varchar(255) not null,
    ANCHOR varchar(255) null,
    SORT int(18) default 500,
    ACTIVE char(1) not null default 'Y',
    PUBLIC char(1) not null default 'Y',
    DELETED char(1) not null default 'N',
    ACCESS char(1) not null default 'X',
    CONTENT mediumtext not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_BLOCK_LID (LID),
    INDEX IX_B_BLOCK_CODE (CODE),
    INDEX IX_B_BLOCK_ACTIVE (ACTIVE),
    INDEX IX_B_BLOCK_PUBLIC (PUBLIC),
    INDEX IX_B_BLOCK_DELETED (DELETED)
);

create table if not exists b_landing_site
(
    ID int(18) not null auto_increment,
    CODE varchar(255) not null,
    ACTIVE char(1) not null default 'Y',
    DELETED char(1) not null default 'N',
    TITLE varchar(255) not null,
    XML_ID varchar(255) default null,
    DESCRIPTION varchar(255) default null,
    TYPE varchar(50) not null default 'PAGE',
    TPL_ID int(18),
    DOMAIN_ID int(18) not null,
    SMN_SITE_ID char(2) default null,
    LANDING_ID_INDEX int(18) default null,
    LANDING_ID_404 int(18) default null,
    LANDING_ID_503 int(18) default null,
    LANG char(2) default null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_SITE_CODE (CODE),
    INDEX IX_B_SITE_ACTIVE (ACTIVE),
    INDEX IX_B_SITE_DELETED (DELETED),
    INDEX IX_B_SITE_XML_ID (XML_ID)
);

create table if not exists b_landing_domain
(
    ID int(18) not null auto_increment,
    ACTIVE char(1) not null default 'Y',
    DOMAIN varchar(255) not null,
    XML_ID varchar(255) default null,
    PROTOCOL varchar(10) not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_DOMAIN_ACTIVE (ACTIVE),
    INDEX IX_B_DOMAIN_DOMAIN (DOMAIN),
    INDEX IX_B_DOMAIN_XML_ID (XML_ID)
);

create table if not exists b_landing_template
(
    ID int(18) not null auto_increment,
    ACTIVE char(1) not null default 'Y',
    TITLE varchar(255) not null,
    SORT int(18) default 100,
    XML_ID varchar(255) default null,
    CONTENT text not null,
    AREA_COUNT int(2) not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID)
);

create table if not exists b_landing_template_ref
(
    ID int(18) not null auto_increment,
    ENTITY_ID int(18) not null,
    ENTITY_TYPE char(1) not null,
    AREA int(2) not null,
    LANDING_ID int(18) not null,
    PRIMARY KEY(ID),
    INDEX K_LANDING_ID (LANDING_ID),
    INDEX K_ENTITY (ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_landing_repo
(
    ID int(18) not null auto_increment,
    XML_ID varchar(255) not null,
    APP_CODE varchar(255) default null,
    ACTIVE char(1) not null default 'Y',
    NAME varchar(255) not null,
    DESCRIPTION varchar(255) default null,
    SECTIONS varchar(255) default null,
    SITE_TEMPLATE_ID varchar(255) default null,
    PREVIEW varchar(255) default null,
    MANIFEST text default null,
    CONTENT text not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_REPO_ACTIVE (ACTIVE),
    INDEX IX_B_REPO_XML_ID (XML_ID),
    INDEX IX_B_REPO_APP_CODE (APP_CODE),
    INDEX IX_B_REPO_TEMPLATE_ID (SITE_TEMPLATE_ID)
);

create table if not exists b_landing_hook_data
(
    ID int(18) not null auto_increment,
    ENTITY_ID int(18) not null,
    ENTITY_TYPE char(1) not null,
    HOOK varchar(50) not null,
    CODE varchar(50) not null,
    VALUE text default null,
    PRIMARY KEY(ID),
    INDEX K_ENTITY (ENTITY_ID, ENTITY_TYPE)
);

create table if not exists b_landing_file
(
    ID int(18) not null auto_increment,
    ENTITY_ID int(18) not null,
    ENTITY_TYPE char(1) not null,
    FILE_ID int(18) not null,
    PRIMARY KEY(ID),
    INDEX IX_ENTITY (ENTITY_ID, ENTITY_TYPE),
    INDEX IX_FILE (FILE_ID)
);

create table if not exists b_landing_syspage
(
    ID int(18) not null auto_increment,
    SITE_ID int(18) not null,
    TYPE varchar(50) not null,
    LANDING_ID int(18) not null,
    PRIMARY KEY(ID),
    INDEX IX_SITE_ID (SITE_ID),
    INDEX IX_LANDING_ID (LANDING_ID)
);

create table if not exists b_landing_demo
(
    ID int(18) not null auto_increment,
    XML_ID varchar(255) not null,
    APP_CODE varchar(255) default null,
    ACTIVE char(1) not null  default 'Y',
    TYPE varchar(10) not null,
    TPL_TYPE char(1) not null,
    SHOW_IN_LIST char(1) not null  default 'N',
    TITLE varchar(255) not null,
    DESCRIPTION varchar(255) default null,
    PREVIEW_URL varchar(255) default null,
    PREVIEW varchar(255) default null,
    PREVIEW2X varchar(255) default null,
    PREVIEW3X varchar(255) default null,
    MANIFEST mediumtext default null,
    LANG text default null,
    SITE_TEMPLATE_ID varchar(255) default null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_B_DEMO_ACTIVE (ACTIVE),
    INDEX IX_B_DEMO_SHOW_IN_LIST (SHOW_IN_LIST),
    INDEX IX_B_DEMO_XML_ID (XML_ID),
    INDEX IX_B_DEMO_APP_CODE (APP_CODE),
    INDEX IX_B_DEMO_TEMPLATE_ID (SITE_TEMPLATE_ID)
);

create table if not exists b_landing_manifest
(
    ID int(18) not null auto_increment,
    CODE varchar(255) not null,
    MANIFEST text not null,
    CONTENT text not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    UNIQUE IX_B_MANIFEST_CODE (CODE)
);

create table if not exists b_landing_placement
(
    ID int(18) not null auto_increment,
    APP_ID int(18) null,
    PLACEMENT varchar(255) not null,
    PLACEMENT_HANDLER varchar(255) not null,
    TITLE varchar(255) null default '',
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY (ID)
);

create table if not exists b_landing_update_block
(
    ID int(18) not null auto_increment,
    CODE varchar(255) not null,
    LAST_BLOCK_ID int(18) default 0,
    PARAMS text default null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_CODE (CODE)
);

create table if not exists b_landing_urlrewrite
(
    ID int(18) not null auto_increment,
    SITE_ID int(18) not null,
    RULE varchar(255) not null,
    LANDING_ID int(18) not null,
    CREATED_BY_ID int(18) not null,
    MODIFIED_BY_ID int(18) not null,
    DATE_CREATE timestamp null,
    DATE_MODIFY timestamp not null,
    PRIMARY KEY(ID),
    INDEX IX_SITE_RULE (SITE_ID, RULE),
    INDEX IX_SITE_ID (SITE_ID),
    INDEX IX_LANDING_ID (LANDING_ID)
);