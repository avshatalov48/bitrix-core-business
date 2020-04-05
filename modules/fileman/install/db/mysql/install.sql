create table b_medialib_collection
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	DESCRIPTION text null,
	ACTIVE char(1) not null default 'Y',
	DATE_UPDATE datetime not null,
	OWNER_ID int null,
	PARENT_ID int null,
	SITE_ID char(2) null,
	KEYWORDS varchar(255) null,
	ITEMS_COUNT int null,
	ML_TYPE int not null default 0,
	primary key (ID)
);

create table b_medialib_collection_item
(
	COLLECTION_ID int not null,
	ITEM_ID int not null,
	primary key (ITEM_ID,COLLECTION_ID)
);

create table b_medialib_item
(
	ID int not null auto_increment,
	NAME varchar(255) not null,
	ITEM_TYPE char(30) null,
	DESCRIPTION text null,
	DATE_CREATE datetime not null,
	DATE_UPDATE datetime not null,
	SOURCE_ID int not null,
	KEYWORDS varchar(255) null,
	SEARCHABLE_CONTENT text null,
	primary key (ID)
);

create table b_group_collection_task
(
	GROUP_ID int not null,
	TASK_ID int not null,
	COLLECTION_ID int not null,
	primary key (GROUP_ID,TASK_ID,COLLECTION_ID)
);

create table b_medialib_type
(
	ID int not null auto_increment,
	NAME varchar(255) null,
	CODE varchar(255) not null,
	EXT varchar(255) not null,
	SYSTEM char(1) not null default 'N',
	DESCRIPTION text null,
	primary key (ID)
);

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('image_name', 'image', 'jpg,jpeg,gif,png', 'Y', 'image_desc');

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('video_name','video','flv,mp4,wmv','Y','video_desc');

INSERT INTO b_medialib_type (NAME,CODE,EXT,SYSTEM,DESCRIPTION)
VALUES ('sound_name','sound','mp3,wma,aac','Y','sound_desc');

create table b_file_search
(
	ID int not null auto_increment,
	SESS_ID varchar(255) not null,
	TIMESTAMP_X timestamp not null,

	F_PATH varchar(255) null,
	B_DIR int not null default 0,
	F_SIZE int not null default 0,
	F_TIME int not null default 0,
	primary key (ID)
);


create table b_sticker
(
	ID int not null auto_increment,
	SITE_ID char(2) null,
	PAGE_URL  varchar(255) not null ,
	PAGE_TITLE  varchar(255) not null ,
	DATE_CREATE datetime not null,
	DATE_UPDATE datetime not null,
	MODIFIED_BY int(18) not null ,
	CREATED_BY int(18) not null ,
	PERSONAL  char(1) not null default 'N',
	CONTENT  text,
	POS_TOP int,
	POS_LEFT int,
	WIDTH int,
	HEIGHT int,
	COLOR int,
	COLLAPSED  char(1) not null default 'N',
	COMPLETED char(1) not null default 'N',
	CLOSED char(1) not null default 'N',
	DELETED char(1) not null default 'N',
	MARKER_TOP int,
	MARKER_LEFT int,
	MARKER_WIDTH int,
	MARKER_HEIGHT int,
	MARKER_ADJUST  text,
	primary key (ID)
);

create table b_sticker_group_task
(
	GROUP_ID int not null,
	TASK_ID int not null,
	primary key (GROUP_ID,TASK_ID)
);

