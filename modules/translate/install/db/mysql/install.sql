
CREATE TABLE IF NOT EXISTS b_translate_path
(
	ID int(18) not null auto_increment,
	PARENT_ID int(18) not null default '0',
	DEPTH_LEVEL int(18) not null default '0',
	SORT int(18) not null default '0',
	PATH varchar(500) not null,
	NAME varchar(255) BINARY not null,
	IS_LANG enum('Y','N') not null default 'N',
	IS_DIR enum('Y','N') not null default 'N',
	OBLIGATORY_LANGS char(50) null default null,
	INDEXED enum('Y','N') not null default 'N',
	INDEXED_TIME datetime null default null,
	MODULE_ID varchar(50) null default null,
	ASSIGNMENT varchar(50) null default null,

	PRIMARY KEY (ID),
	UNIQUE KEY IX_TRNSL_PTH_NAME (PARENT_ID, NAME),
	KEY IX_TRNSL_PTH_PARENT (PARENT_ID, IS_DIR, IS_LANG),
	KEY IX_TRNSL_PTH_PATH (PATH(255))
);

CREATE TABLE IF NOT EXISTS b_translate_path_lang
(
	ID int(18) not null auto_increment,
	PATH varchar(500) not null,

	PRIMARY KEY (ID),
	KEY IX_TRNSL_LNG_PATH (PATH(255))
);

CREATE TABLE IF NOT EXISTS b_translate_file
(
	ID int(18) not null auto_increment,
	PATH_ID int(18) not null,
	LANG_ID char(2) not null,
	FULL_PATH varchar(500) not null,
	PHRASE_COUNT INT(18) not null default '0',
	INDEXED enum('Y','N') not null default 'N',
	INDEXED_TIME datetime null default null,

	PRIMARY KEY (ID),
	UNIQUE KEY IX_TRNSL_FL_PATH (PATH_ID, LANG_ID),
	KEY IX_TRNSL_FULL_PATH (FULL_PATH(255))
);

CREATE TABLE IF NOT EXISTS b_translate_phrase 
(
	ID int(18) not null auto_increment,
	FILE_ID int(18) not null,
	PATH_ID int(18) not null,
	LANG_ID char(2) not null,
	CODE varchar(255) BINARY not null,
	PHRASE text,

	PRIMARY KEY (ID),
	UNIQUE KEY IXU_TRNSL_PHR_PATH_CODE (PATH_ID, LANG_ID, CODE),
	KEY IX_TRNSL_PHR_PATH (PATH_ID, CODE),
	KEY IX_TRNSL_FILE (FILE_ID)
)
DELAY_KEY_WRITE=1;

CREATE TABLE IF NOT EXISTS b_translate_diff
(
	ID int(18) not null auto_increment,
	FILE_ID int(18) not null,
	PATH_ID int(18) not null,
	LANG_ID char(2) not null,
	AGAINST_LANG_ID char(2) not null,
	EXCESS_COUNT int(18) not null default '0',
	DEFICIENCY_COUNT int(18) null default '0',

	PRIMARY KEY (ID),
	UNIQUE KEY IXU_TRNSL_DIFF (FILE_ID, LANG_ID, AGAINST_LANG_ID),
	KEY IX_TRNSL_DIFF_PATH (PATH_ID, LANG_ID)
);

CREATE TABLE b_translate_path_tree
(
	ID INT(18) NOT NULL AUTO_INCREMENT,
	PARENT_ID INT(18) NOT NULL,
	PATH_ID INT(18) NOT NULL,
	DEPTH_LEVEL INT(18) NULL DEFAULT NULL,

	PRIMARY KEY (ID),
	UNIQUE INDEX IX_TRNSL_ANCESTOR (PARENT_ID, DEPTH_LEVEL, PATH_ID),
	UNIQUE INDEX IX_TRNSL_DESCENDANT (PATH_ID, PARENT_ID, DEPTH_LEVEL)
);