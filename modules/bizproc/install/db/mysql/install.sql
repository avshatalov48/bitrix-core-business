CREATE TABLE b_bp_workflow_template (
	ID int NOT NULL auto_increment,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_TYPE varchar(128) NOT NULL,
	AUTO_EXECUTE int NOT NULL DEFAULT 0,
	NAME varchar(255) NULL,
	DESCRIPTION text NULL,
	TEMPLATE mediumblob NULL,
	PARAMETERS blob NULL,
	VARIABLES blob NULL,
	CONSTANTS mediumblob NULL,
	MODIFIED datetime NOT NULL,
	IS_MODIFIED char(1) NOT NULL default 'N',
	USER_ID int NULL,
	SYSTEM_CODE varchar(50),
	ACTIVE char(1) NOT NULL default 'Y',
	primary key (ID),
	index ix_bp_wf_template_mo(MODULE_ID, ENTITY, DOCUMENT_TYPE)
);

CREATE TABLE b_bp_workflow_state (
	ID varchar(32) NOT NULL,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_ID varchar(128) NOT NULL,
	DOCUMENT_ID_INT int NOT NULL,
	WORKFLOW_TEMPLATE_ID int NOT NULL,
	STATE varchar(128) NULL,
	STATE_TITLE varchar(255) NULL,
	STATE_PARAMETERS text NULL,
	MODIFIED datetime NOT NULL,
	STARTED datetime NULL,
	STARTED_BY int NULL,
	primary key (ID),
	index ix_bp_ws_document_id(DOCUMENT_ID, ENTITY, MODULE_ID),
	index ix_bp_ws_document_id1(DOCUMENT_ID_INT, ENTITY, MODULE_ID, STATE),
	index ix_bp_ws_started_by (STARTED_BY)
);

CREATE TABLE b_bp_workflow_permissions (
	ID int NOT NULL auto_increment,
	WORKFLOW_ID varchar(32) NOT NULL,
	OBJECT_ID varchar(64) NOT NULL,
	PERMISSION varchar(64) NOT NULL,
	primary key (ID),
	index ix_bp_wf_permissions_wt(WORKFLOW_ID)
);

CREATE TABLE b_bp_workflow_instance (
	ID varchar(32) NOT NULL,
	WORKFLOW mediumblob NULL,
	STATUS int NULL,
	MODIFIED datetime NOT NULL,
	OWNER_ID varchar(32) NULL,
	OWNED_UNTIL datetime NULL,
	primary key (ID)
);

CREATE TABLE b_bp_tracking (
	ID int NOT NULL auto_increment,
	WORKFLOW_ID varchar(32) NOT NULL,
	TYPE int NOT NULL,
	MODIFIED datetime NOT NULL,
	ACTION_NAME varchar(128) NOT NULL,
	ACTION_TITLE varchar(255) NULL,
	EXECUTION_STATUS int NOT NULL default 0,
	EXECUTION_RESULT int NOT NULL default 0,
	ACTION_NOTE text NULL,
	MODIFIED_BY int NULL,
	primary key (ID),
	index ix_bp_tracking_wf(WORKFLOW_ID)
);

CREATE TABLE b_bp_task (
	ID int NOT NULL auto_increment,
	WORKFLOW_ID varchar(32) NOT NULL,
	ACTIVITY varchar(128) NOT NULL,
	ACTIVITY_NAME varchar(128) NOT NULL,
	MODIFIED datetime NOT NULL,
	OVERDUE_DATE datetime NULL,
	NAME varchar(128) NOT NULL,
	DESCRIPTION text NULL,
	PARAMETERS text NULL,
	STATUS int NOT NULL default 0,
	IS_INLINE char(1) NOT NULL default 'N',
	DELEGATION_TYPE int NOT NULL default 0,
	DOCUMENT_NAME varchar(255) null,
	primary key (ID),
	index ix_bp_tasks_sort(OVERDUE_DATE, MODIFIED),
	index ix_bp_tasks_wf(WORKFLOW_ID)
);

CREATE TABLE b_bp_task_user (
	ID int NOT NULL auto_increment,
	USER_ID int NOT NULL,
	TASK_ID int NOT NULL,
	STATUS int NOT NULL default 0,
	DATE_UPDATE datetime NULL,
	ORIGINAL_USER_ID int NOT NULL default 0,
	primary key (ID),
	unique ix_bp_task_user(USER_ID, TASK_ID),
	index ix_bp_task_user_2(TASK_ID)
);

CREATE TABLE b_bp_history (
	ID int NOT NULL auto_increment,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_ID varchar(128) NOT NULL,
	NAME varchar(255) NOT NULL,
	DOCUMENT blob NULL,
	MODIFIED datetime NOT NULL,
	USER_ID int NULL,
	primary key (ID),
	index ix_bp_history_doc(DOCUMENT_ID, ENTITY, MODULE_ID)
);

CREATE TABLE b_bp_workflow_state_identify (
	ID int NOT NULL auto_increment,
	WORKFLOW_ID varchar(32) NOT NULL,
	primary key (ID),
	unique ix_bp_wsi_wf(WORKFLOW_ID)
);

CREATE TABLE b_bp_rest_activity (
	ID int NOT NULL auto_increment,
	APP_ID varchar(128) NOT NULL,
	APP_NAME text NULL,
	CODE varchar(128) NOT NULL,
	INTERNAL_CODE varchar(32) NOT NULL,
	HANDLER varchar(1000) NOT NULL,
	AUTH_USER_ID int NOT NULL default 0,
	USE_SUBSCRIPTION char(1) NOT NULL default '',
	NAME text NULL,
	DESCRIPTION text NULL,
	PROPERTIES text NULL,
	RETURN_PROPERTIES text NULL,
	DOCUMENT_TYPE text NULL,
	FILTER text NULL,
	IS_ROBOT char(1) NOT NULL default 'N',
	primary key (ID),
	unique ix_bp_ra_ic(INTERNAL_CODE)
);

CREATE TABLE b_bp_scheduler_event (
	ID int NOT NULL auto_increment,
	WORKFLOW_ID varchar(32) NOT NULL,
	HANDLER varchar(128) NOT NULL,
	EVENT_MODULE VARCHAR(32) NOT NULL,
	EVENT_TYPE VARCHAR(100) NOT NULL,
	ENTITY_ID VARCHAR(100) NULL,
	primary key (ID),
	index ix_b_bp_se_1(EVENT_MODULE, EVENT_TYPE),
	index ix_b_bp_se_2(EVENT_MODULE, EVENT_TYPE, ENTITY_ID),
	index ix_b_bp_se_3(WORKFLOW_ID)
);

CREATE TABLE b_bp_rest_provider (
	ID int NOT NULL auto_increment,
	APP_ID varchar(128) NOT NULL,
	APP_NAME text NULL,
	CODE varchar(128) NOT NULL,
	TYPE varchar(30) NOT NULL,
	HANDLER varchar(1000) NOT NULL,
	NAME text NULL,
	DESCRIPTION text NULL,
	primary key (ID)
);