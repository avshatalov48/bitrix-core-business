CREATE TABLE b_pull_stack (
	ID int(18) not null auto_increment,
	CHANNEL_ID varchar(50) not null,
	MESSAGE text not null,
	DATE_CREATE datetime not null,
	PRIMARY KEY (ID),
	KEY IX_PULL_STACK_CID (CHANNEL_ID),
	KEY IX_PULL_STACK_D (DATE_CREATE)
);

CREATE TABLE b_pull_channel (
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	CHANNEL_TYPE varchar(50) null,
	CHANNEL_ID varchar(50) not null,
	CHANNEL_PUBLIC_ID varchar(50) null,
	LAST_ID int(18) null,
	DATE_CREATE datetime not null,
	PRIMARY KEY (ID),
	UNIQUE IX_PULL_CN_UID (USER_ID, CHANNEL_TYPE),
	KEY IX_PULL_CN_CID (CHANNEL_ID),
	KEY IX_PULL_CN_CPID (CHANNEL_PUBLIC_ID),
	KEY IX_PULL_CN_D (DATE_CREATE)
);

CREATE TABLE b_pull_push (
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	DEVICE_TYPE varchar(50) null,
	APP_ID varchar(50) null,
	UNIQUE_HASH varchar(50) null,
	DEVICE_ID varchar(255) null,
	DEVICE_NAME varchar(50) null,
	DEVICE_TOKEN varchar(255) not null,
	DATE_CREATE datetime not null,
	DATE_AUTH datetime null,
	PRIMARY KEY (ID),
	KEY IX_PULL_PSH_UID (USER_ID),
	KEY IX_PULL_PSH_UH (UNIQUE_HASH)
);

CREATE TABLE b_pull_push_queue (
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	TAG varchar(255) null,
	SUB_TAG varchar(255) null,
	MESSAGE text null,
	PARAMS text null,
	ADVANCED_PARAMS text null,
	BADGE int(11) null,
	DATE_CREATE datetime null,
  APP_ID VARCHAR(50) NULL,
	PRIMARY KEY (ID),
	KEY IX_PULL_PSHQ_UT (USER_ID, TAG),
	KEY IX_PULL_PSHQ_UST (USER_ID, SUB_TAG),
	KEY IX_PULL_PSHQ_DC (DATE_CREATE),
	KEY IX_PULL_PSHQ_AID (APP_ID)
);

CREATE TABLE b_pull_watch (
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	CHANNEL_ID varchar(50) not null,
	TAG varchar(255) not null,
	DATE_CREATE datetime not null,
	PRIMARY KEY (ID),
	KEY IX_PULL_W_UT (USER_ID, TAG),
	KEY IX_PULL_W_D (DATE_CREATE),
	KEY IX_PULL_W_T (TAG)
);