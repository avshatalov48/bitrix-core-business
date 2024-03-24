CREATE TABLE b_mobileapp_app
(
	CODE           varchar(50) NOT NULL,
	SHORT_NAME     varchar(50) NOT NULL,
	NAME           varchar(50) NOT NULL,
	DESCRIPTION    text        NOT NULL,
	FILES          text        NOT NULL,
	LAUNCH_ICONS   text        NOT NULL,
	LAUNCH_SCREENS text        NOT NULL,
	FOLDER         varchar(50) NOT NULL,
	DATE_CREATE    timestamp   NOT NULL,
	PRIMARY KEY (CODE)
);

CREATE TABLE b_mobileapp_config
(
	APP_CODE    varchar(150) NOT NULL,
	PLATFORM    varchar(150) NOT NULL,
	PARAMS      text         NOT NULL,
	DATE_CREATE timestamp    NOT NULL,
	PRIMARY KEY (APP_CODE, PLATFORM)
);
