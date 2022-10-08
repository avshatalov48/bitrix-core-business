CREATE TABLE b_learn_exceptions_log (
  DATE_REGISTERED datetime NOT NULL,
  CODE int(11) NOT NULL,
  MESSAGE text NOT NULL,
  FFILE text NOT NULL,
  LINE int(11) NOT NULL,
  BACKTRACE text NOT NULL
);

CREATE TABLE b_learn_publish_prohibition
(
	COURSE_LESSON_ID INT UNSIGNED NOT NULL ,
	PROHIBITED_LESSON_ID INT UNSIGNED NOT NULL ,
	PRIMARY KEY ( COURSE_LESSON_ID , PROHIBITED_LESSON_ID )
);

CREATE TABLE b_learn_rights
(
	LESSON_ID INT UNSIGNED NOT NULL ,
	SUBJECT_ID VARCHAR( 100 ) NOT NULL ,
	TASK_ID INT NOT NULL ,
	PRIMARY KEY ( LESSON_ID , SUBJECT_ID )
);

CREATE TABLE b_learn_rights_all
(
	SUBJECT_ID VARCHAR( 100 ) NOT NULL ,
	TASK_ID INT NOT NULL ,
	PRIMARY KEY ( SUBJECT_ID )
);

CREATE TABLE b_learn_lesson_edges
(
	SOURCE_NODE INT NOT NULL ,
	TARGET_NODE INT NOT NULL ,
	SORT INT NOT NULL DEFAULT '500',
	PRIMARY KEY ( SOURCE_NODE , TARGET_NODE )
);

CREATE INDEX TARGET_NODE ON b_learn_lesson_edges(TARGET_NODE);

CREATE TABLE b_learn_course
(
	ID int(11) unsigned not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	ACTIVE char(1) not null default 'Y',
	CODE varchar(50),
	NAME varchar(255) not null default 'name',
	SORT int(11) not null default '500',
	PREVIEW_PICTURE int(18),
	PREVIEW_TEXT text,
	PREVIEW_TEXT_TYPE char(4) not null default 'text',
	DESCRIPTION text,
	DESCRIPTION_TYPE char(4) not null default 'text',
	ACTIVE_FROM datetime,
	ACTIVE_TO datetime,
	RATING CHAR(1) null,
	RATING_TYPE varchar(50) null,
	SCORM char(1) NOT NULL default 'N',
	LINKED_LESSON_ID INT NULL DEFAULT NULL,
	JOURNAL_STATUS INT NOT NULL DEFAULT '0',
	PRIMARY KEY(ID),
	INDEX ix_learn_course_lesson(LINKED_LESSON_ID)
);

CREATE TABLE b_learn_course_site
(
	COURSE_ID int(11) unsigned not null REFERENCES b_learn_course(ID),
	SITE_ID char(2) not null REFERENCES b_lang(LID),
	PRIMARY KEY(COURSE_ID, SITE_ID)
);

CREATE TABLE b_learn_chapter
(
	ID int(11) unsigned not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	ACTIVE char(1) not null default 'Y',
	COURSE_ID int(11) unsigned not null,
	CHAPTER_ID int(11),
	NAME varchar(255) not null,
	CODE varchar(50),
	SORT int(11) not null default '500',
	PREVIEW_PICTURE int(18),
	PREVIEW_TEXT text,
	PREVIEW_TEXT_TYPE char(4) not null default 'text',
	DETAIL_PICTURE int(18),
	DETAIL_TEXT longtext,
	DETAIL_TEXT_TYPE char(4) not null default 'text',
	JOURNAL_STATUS INT NOT NULL DEFAULT '0',
	PRIMARY KEY(ID)
);

CREATE TABLE b_learn_lesson
(
	ID int(11) unsigned not null auto_increment,
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	DATE_CREATE datetime,
	CREATED_BY int(18),
	ACTIVE char(1) not null default 'Y',
	COURSE_ID int(11) unsigned not null default '0',
	CHAPTER_ID int(11) unsigned,
	NAME varchar(255) not null default 'name',
	SORT int(11) not null default '500',
	PREVIEW_PICTURE int(18),
	KEYWORDS text,
	PREVIEW_TEXT text,
	PREVIEW_TEXT_TYPE char(4) not null default 'text',
	DETAIL_PICTURE int(18),
	DETAIL_TEXT longtext,
	DETAIL_TEXT_TYPE char(4) not null DEFAULT 'text',
	LAUNCH text,

	CODE varchar(50) NULL DEFAULT NULL,
	WAS_CHAPTER_ID int NULL DEFAULT NULL,
	WAS_PARENT_CHAPTER_ID int NULL DEFAULT NULL,
	WAS_PARENT_COURSE_ID int NULL DEFAULT NULL,
	WAS_COURSE_ID int NULL DEFAULT NULL,
	JOURNAL_STATUS int NOT NULL DEFAULT '0',

	PRIMARY KEY(ID)
);


CREATE TABLE b_learn_question
(
	ID int(11) unsigned not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	LESSON_ID int(11) unsigned not null REFERENCES b_learn_lesson(ID),
	QUESTION_TYPE char(1) not null default 'S',
	NAME varchar(255) not null,
	SORT int(11) not null default '500',
	DESCRIPTION text,
	DESCRIPTION_TYPE char(4) not null default 'text',
	COMMENT_TEXT text,
	FILE_ID int(18),
	SELF char(1) not null default 'N',
	POINT int(11) not null default '10',
	DIRECTION char(1) not null default 'V',
	CORRECT_REQUIRED char(1) not null default 'N',
	EMAIL_ANSWER char(1) not null default 'N',
	INCORRECT_MESSAGE text,
	PRIMARY KEY(ID),
	INDEX IX_B_LEARN_QUESTION1(LESSON_ID)
);


CREATE TABLE b_learn_answer
(
	ID int(11) unsigned not null auto_increment,
	QUESTION_ID int(11) unsigned not null REFERENCES b_learn_question(ID),
	SORT int(11) not null default '10',
	ANSWER text not null,
	CORRECT char(1) not null,
	FEEDBACK text,
	MATCH_ANSWER text,
	PRIMARY KEY(ID),
	INDEX IX_B_LEARN_ANSWER1(QUESTION_ID)
);


CREATE TABLE b_learn_test
(
	ID int(11) not null AUTO_INCREMENT,
	COURSE_ID int(11) unsigned not null REFERENCES b_learn_course(ID),
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	SORT int(11) not null default '500',
	ACTIVE char(1) not null default 'Y',
	NAME varchar(255) not null,
	DESCRIPTION text,
	DESCRIPTION_TYPE char(4) not null default 'text',
	ATTEMPT_LIMIT int(11) not null default '0',
	TIME_LIMIT int(11) default '0',
	COMPLETED_SCORE int(11),
	QUESTIONS_FROM char(1) not null default 'A',
	QUESTIONS_FROM_ID int(11) not null default '0',
	QUESTIONS_AMOUNT int(11) not null default '0',
	RANDOM_QUESTIONS char(1) not null default 'Y',
	RANDOM_ANSWERS char(1) not null default 'Y',
	APPROVED char(1) not null default 'Y',
	INCLUDE_SELF_TEST char(1) not null default 'N',
	PASSAGE_TYPE char(1) not null default '0',
	PREVIOUS_TEST_ID int(11)  REFERENCES b_learn_test(ID),
	PREVIOUS_TEST_SCORE int(11) default 0,
	INCORRECT_CONTROL char(1) not null default 'N',
	CURRENT_INDICATION int(11) not null default '0',
	FINAL_INDICATION int(11) not null default '0',
	MIN_TIME_BETWEEN_ATTEMPTS int(11) not null default '0',
	SHOW_ERRORS char(1) not null default 'N',
	NEXT_QUESTION_ON_ERROR char(1) not null default 'Y',
	PRIMARY KEY (ID),
	INDEX IX_B_LEARN_TEST1(COURSE_ID),
	INDEX IX_B_LEARN_TEST2(PREVIOUS_TEST_ID)
);

CREATE TABLE b_learn_attempt
(
	ID int(11) unsigned not null AUTO_INCREMENT,
	TEST_ID int(11) not null REFERENCES b_learn_test(ID),
	STUDENT_ID int(18) not null,
	DATE_START datetime not null,
	DATE_END datetime,
	STATUS char(1) not null default 'B',
	COMPLETED char(1) not null default 'N',
	SCORE int default 0,
	MAX_SCORE int default 0,
	QUESTIONS int(11) not null default '0',
	PRIMARY KEY (ID),
	INDEX IX_B_LEARN_ATTEMPT1(STUDENT_ID, TEST_ID)
);

CREATE TABLE b_learn_test_result
(
	ID int(11) unsigned not null AUTO_INCREMENT,
	ATTEMPT_ID int(11) unsigned not null REFERENCES b_learn_attempt(ID),
	QUESTION_ID int(11) unsigned not null REFERENCES b_learn_question(ID),
	RESPONSE text,
	POINT int not null default 0,
	CORRECT char(1) not null default 'N',
	ANSWERED char(1) not null default 'N',
	PRIMARY KEY (ID),
	INDEX IX_B_LEARN_TEST_RESULT1(ATTEMPT_ID,QUESTION_ID),
	INDEX IX_B_LEARN_TEST_RESULT2(QUESTION_ID, ANSWERED, CORRECT)
);

CREATE TABLE b_learn_gradebook
(
	ID int(11) unsigned not null AUTO_INCREMENT,
	STUDENT_ID int(18) not null,
	TEST_ID int(11) not null REFERENCES b_learn_test(ID),
	RESULT int,
	MAX_RESULT int,
	ATTEMPTS int(11) not null default '1',
	COMPLETED char(1) not null default 'N',
	EXTRA_ATTEMPTS int(11) not null default '0',
	PRIMARY KEY (ID),
	UNIQUE UX_B_LEARN_GRADEBOOK1(STUDENT_ID,TEST_ID)
);

CREATE TABLE b_learn_student
(
	USER_ID int(18) not null,
	TRANSCRIPT int(11) NOT NULL,
	PUBLIC_PROFILE char(1) not null default 'N',
	RESUME text,
	PRIMARY KEY(USER_ID)
);

CREATE TABLE b_learn_certification
(
	ID int(11) unsigned not null auto_increment,
	STUDENT_ID int(18) not null,
	COURSE_ID int(11) unsigned not null REFERENCES b_learn_course(ID),
	TIMESTAMP_X timestamp not null default current_timestamp on update current_timestamp,
	DATE_CREATE datetime,
	ACTIVE char(1) not null default 'Y',
	SORT int(11) not null default '500',
	FROM_ONLINE char(1) not null default 'Y',
	PUBLIC_PROFILE char(1) not null default 'Y',
	SUMMARY int(11) not null default '0',
	MAX_SUMMARY int(11) not null default '0',
	PRIMARY KEY(ID),
	INDEX IX_B_LEARN_CERTIFICATION1(STUDENT_ID, COURSE_ID)
);

CREATE TABLE b_learn_site_path
(
  ID int(11) NOT NULL AUTO_INCREMENT,
  SITE_ID char(2) NOT NULL,
  PATH varchar(255) NOT NULL,
  TYPE char(1) DEFAULT NULL,
  PRIMARY KEY (ID),
  UNIQUE KEY IX_LEARN_SITE_PATH_2 (SITE_ID,TYPE)
);

CREATE TABLE b_learn_test_mark (
  ID int(11) not null auto_increment,
  TEST_ID int(11) not null REFERENCES b_learn_test (ID),
  SCORE int(11) not null,
  MARK varchar(50) not null,
  DESCRIPTION text null,
  PRIMARY KEY (ID),
  INDEX IX_B_LEARN_TEST_MARK1(TEST_ID)
);

CREATE TABLE b_learn_groups
(
	ID int(11) unsigned not null auto_increment,
	ACTIVE char(1) not null default 'Y',
	TITLE varchar(255) not null default ' ',
	CODE varchar(50),
	SORT int(11) not null default '500',
	ACTIVE_FROM datetime,
	ACTIVE_TO datetime,
	COURSE_LESSON_ID INT NOT NULL,
	PRIMARY KEY(ID)
);

CREATE TABLE b_learn_groups_member (
	LEARNING_GROUP_ID int(11) NOT NULL DEFAULT '0',
	USER_ID int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (LEARNING_GROUP_ID, USER_ID),
	KEY USER_ID (USER_ID)
);

CREATE TABLE b_learn_groups_lesson (
	LEARNING_GROUP_ID int(11) NOT NULL DEFAULT '0',
	LESSON_ID int(11) NOT NULL DEFAULT '0',
	DELAY int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (LEARNING_GROUP_ID, LESSON_ID),
	KEY LESSON_ID (LESSON_ID)
);
