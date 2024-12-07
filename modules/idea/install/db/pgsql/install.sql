create table if not exists b_idea_email_subscribe
(
	USER_ID int NOT NULL,
	SUBSCRIBE_TYPE varchar(32) NOT NULL,
	ENTITY_TYPE varchar(32) NOT NULL,
	ENTITY_CODE varchar(255) NOT NULL,
	PRIMARY KEY(USER_ID, ENTITY_TYPE, ENTITY_CODE)
);

CREATE INDEX ix_b_idea_email_subscribe ON b_idea_email_subscribe (SUBSCRIBE_TYPE, ENTITY_TYPE, ENTITY_CODE);