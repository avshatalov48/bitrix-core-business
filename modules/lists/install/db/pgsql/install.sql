CREATE TABLE b_lists_permission (
  IBLOCK_TYPE_ID varchar(50) NOT NULL,
  GROUP_ID int NOT NULL,
  PRIMARY KEY (IBLOCK_TYPE_ID, GROUP_ID)
);

CREATE TABLE b_lists_field (
  IBLOCK_ID int NOT NULL,
  FIELD_ID varchar(100) NOT NULL,
  SORT int NOT NULL,
  NAME varchar(100) NOT NULL,
  SETTINGS text,
  PRIMARY KEY (IBLOCK_ID, FIELD_ID)
);

CREATE TABLE b_lists_socnet_group (
  IBLOCK_ID int NOT NULL,
  SOCNET_ROLE char(1),
  PERMISSION char(1) NOT NULL
);
CREATE UNIQUE INDEX ux_b_lists_socnet_group_iblock_id_socnet_role ON b_lists_socnet_group (iblock_id, socnet_role);

CREATE TABLE b_lists_url (
  IBLOCK_ID int NOT NULL,
  URL varchar(500),
  LIVE_FEED smallint DEFAULT 0,
  PRIMARY KEY (IBLOCK_ID)
);
