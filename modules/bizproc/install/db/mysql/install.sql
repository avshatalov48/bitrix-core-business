CREATE TABLE b_bp_workflow_template (
	ID int NOT NULL auto_increment,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_TYPE varchar(128) NOT NULL,
	DOCUMENT_STATUS VARCHAR(50) NULL,
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
	ORIGINATOR_ID VARCHAR(255) NULL,
	ORIGIN_ID VARCHAR(255) NULL,
	IS_SYSTEM char(1) NOT NULL default 'N',
	`SORT` INT(10) NOT NULL DEFAULT 10,
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
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_ID varchar(128) NOT NULL,
	WORKFLOW_TEMPLATE_ID int NOT NULL,
	WORKFLOW mediumblob NULL,
	WORKFLOW_RO mediumblob NULL,
	STARTED datetime NULL,
	STARTED_BY int NULL,
	STARTED_EVENT_TYPE tinyint NOT NULL DEFAULT 0,
	STATUS int NULL,
	MODIFIED datetime NOT NULL,
	OWNER_ID varchar(32) NULL,
	OWNED_UNTIL datetime NULL,
	primary key (ID),
	index ix_bp_wi_document(DOCUMENT_ID, ENTITY, MODULE_ID, STARTED_EVENT_TYPE),
	index ix_bp_wi_started_by(STARTED_BY)
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
	COMPLETED char(1) NOT NULL default 'N',
	primary key (ID),
	index ix_bp_tracking_wft(WORKFLOW_ID, TYPE),
	index ix_bp_tracking_md(MODIFIED),
	index ix_bp_tracking_ctm(COMPLETED, TYPE, MODIFIED)
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
	index ix_bp_tasks_wf(WORKFLOW_ID),
	index ix_bp_tasks_modified (MODIFIED)
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
	index ix_bp_task_user_2(TASK_ID),
	index ix_bp_task_user_3(USER_ID,STATUS)
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
	USE_PLACEMENT char(1) NOT NULL default 'N',
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
	EVENT_PARAMETERS mediumtext NULL,
	primary key (ID),
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

CREATE TABLE b_bp_automation_trigger (
		ID int(18) NOT NULL AUTO_INCREMENT,
		NAME varchar(255) NOT NULL,
		CODE varchar(30) NOT NULL,
		MODULE_ID varchar(32) NOT NULL,
		ENTITY varchar(64) NOT NULL,
		DOCUMENT_TYPE varchar(128) NOT NULL,
		DOCUMENT_STATUS varchar(50) NOT NULL,
		APPLY_RULES text,
		PRIMARY KEY (ID),
		index ix_bp_atm_trigger_1(DOCUMENT_TYPE, DOCUMENT_STATUS)
);

CREATE TABLE b_bp_global_const (
	ID varchar(50) NOT NULL,
	NAME text NOT NULL,
	DESCRIPTION text NULL,
	PROPERTY_TYPE varchar(30) NOT NULL,
	IS_REQUIRED char(1) NOT NULL default 'N',
	IS_MULTIPLE char(1) NOT NULL default 'N',
	PROPERTY_OPTIONS text NULL,
	PROPERTY_SETTINGS text NULL,
	PROPERTY_VALUE text NULL,
	CREATED_DATE datetime,
	CREATED_BY int NULL,
	VISIBILITY varchar(30) DEFAULT 'GLOBAL',
	MODIFIED_DATE datetime NULL,
	MODIFIED_BY int NULL,
	primary key (ID),
	index ix_bp_gc_visibility(VISIBILITY)
);

CREATE TABLE b_bp_script (
	ID int NOT NULL auto_increment,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_TYPE varchar(128) NOT NULL,
	NAME varchar(255) NULL,
	DESCRIPTION text NULL,
	WORKFLOW_TEMPLATE_ID int NOT NULL,
	CREATED_DATE datetime NOT NULL,
	CREATED_BY INT NOT NULL,
	MODIFIED_DATE datetime NOT NULL,
	MODIFIED_BY int NOT NULL,
	ORIGINATOR_ID VARCHAR(255) NULL,
	ORIGIN_ID VARCHAR(255) NULL,
	`SORT` INT NOT NULL DEFAULT 10,
	ACTIVE char(1) NOT NULL default 'Y',
	primary key (ID),
	index ix_bp_script_mo(MODULE_ID, ENTITY, DOCUMENT_TYPE)
);

CREATE TABLE b_bp_script_queue (
		ID int NOT NULL auto_increment,
		SCRIPT_ID INT NOT NULL,
		STARTED_DATE datetime NULL,
		STARTED_BY int NULL,
		STATUS TINYINT UNSIGNED NOT NULL DEFAULT 0,
		MODIFIED_DATE datetime NOT NULL,
		MODIFIED_BY int NOT NULL,
		WORKFLOW_PARAMETERS mediumtext NULL,
		primary key (ID),
		index ix_bp_sq_script_id(SCRIPT_ID),
		index ix_bp_sq_started_by(STARTED_BY)
);

CREATE TABLE b_bp_script_queue_document (
	ID int NOT NULL auto_increment,
	QUEUE_ID INT NOT NULL,
	DOCUMENT_ID varchar(128) NOT NULL,
	WORKFLOW_ID varchar(32) NOT NULL DEFAULT '',
	STATUS TINYINT UNSIGNED NOT NULL DEFAULT 0,
	STATUS_MESSAGE varchar(255) NULL,
	primary key (ID),
	index ix_bp_sqd_queue_id(QUEUE_ID),
	index ix_bp_sqd_wf(WORKFLOW_ID),
	index ix_bp_sqd_queue_wf(QUEUE_ID, WORKFLOW_ID)
);

CREATE TABLE b_bp_storage_activity (
	ID int UNSIGNED NOT NULL auto_increment,
	WORKFLOW_TEMPLATE_ID INT UNSIGNED NOT NULL,
	ACTIVITY_NAME varchar(128) NOT NULL,
	KEY_ID varchar(128) NOT NULL,
	KEY_VALUE text NULL,
	primary key (ID),
	index ix_bp_st_act_1(WORKFLOW_TEMPLATE_ID, ACTIVITY_NAME)
);

CREATE TABLE b_bp_global_var (
	ID varchar(50) NOT NULL,
	NAME text NOT NULL,
	DESCRIPTION text NULL,
	PROPERTY_TYPE varchar(30) NOT NULL,
	IS_REQUIRED char(1) NOT NULL default 'N',
	IS_MULTIPLE char(1) NOT NULL default 'N',
	PROPERTY_OPTIONS text NULL,
	PROPERTY_SETTINGS text NULL,
	PROPERTY_VALUE text NULL,
	CREATED_DATE datetime,
	CREATED_BY int NULL,
	VISIBILITY varchar(30) DEFAULT 'GLOBAL',
	MODIFIED_DATE datetime NULL,
	MODIFIED_BY int NULL,
	primary key (ID),
	index ix_bp_gv_visibility(VISIBILITY)
);

CREATE TABLE b_bp_debugger_session (
	ID varchar(32) NOT NULL,
	MODULE_ID varchar(32) NULL,
	ENTITY varchar(64) NOT NULL,
	DOCUMENT_TYPE varchar(128) NOT NULL,
	DOCUMENT_CATEGORY_ID int NULL,
	MODE tinyint unsigned NOT NULL,
	TITLE varchar(256) NULL,
	STARTED_BY int NOT NULL,
	STARTED_DATE datetime NOT NULL,
	FINISHED_DATE datetime,
	ACTIVE char(1) NOT NULL,
	FIXED char(1) NOT NULL,
	DEBUGGER_STATE tinyint NOT NULL default -1,
	primary key (ID)
);

CREATE TABLE b_bp_debugger_session_document (
	ID int unsigned NOT NULL auto_increment,
	SESSION_ID varchar(32) NOT NULL,
	DOCUMENT_ID varchar(128) NOT NULL,
	DATE_EXPIRE datetime,
	primary key (ID)
);

CREATE TABLE `b_bp_debugger_session_workflow_context` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`SESSION_ID` varchar(32) NOT NULL,
	`WORKFLOW_ID` varchar(32) NOT NULL,
	`TEMPLATE_SHARDS_ID` int NULL,
	PRIMARY KEY(`ID`)
);

CREATE TABLE `b_bp_debugger_session_template_shards` (
	`ID` int NOT NULL AUTO_INCREMENT,
	`TEMPLATE_ID` int NOT NULL,
	`SHARDS` mediumblob NULL,
	`TEMPLATE_TYPE` tinyint unsigned,
	`MODIFIED` datetime NOT NULL,
	PRIMARY KEY(`ID`)
);
