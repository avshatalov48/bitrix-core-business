
CREATE TABLE b_ui_entity_editor_config (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  CATEGORY varchar(20) NOT NULL,
  ENTITY_TYPE_ID varchar(60) NOT NULL,
  NAME varchar(100) NOT NULL,
  CONFIG text NOT NULL,
  COMMON varchar(1) NOT NULL,
  AUTO_APPLY_SCOPE varchar(1) NOT NULL DEFAULT 'N',
  OPTION_CATEGORY varchar(50) NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_entity_editor_config_entity_type_id ON b_ui_entity_editor_config (entity_type_id);
CREATE INDEX ix_b_ui_entity_editor_config_category ON b_ui_entity_editor_config (category);

CREATE TABLE b_ui_entity_editor_config_ac (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ACCESS_CODE varchar(10) NOT NULL,
  CONFIG_ID int NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_entity_editor_config_ac_access_code ON b_ui_entity_editor_config_ac (access_code);
CREATE INDEX ix_b_ui_entity_editor_config_ac_config_id ON b_ui_entity_editor_config_ac (config_id);

CREATE TABLE b_ui_file_uploader_temp_file (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  GUID char(36) NOT NULL,
  FILE_ID int,
  FILENAME varchar(255) NOT NULL,
  SIZE int8 NOT NULL,
  PATH varchar(255) NOT NULL,
  MIMETYPE varchar(255) NOT NULL,
  RECEIVED_SIZE int8 NOT NULL DEFAULT 0,
  WIDTH int NOT NULL DEFAULT 0,
  HEIGHT int NOT NULL DEFAULT 0,
  BUCKET_ID int,
  MODULE_ID varchar(50) NOT NULL,
  CONTROLLER varchar(255) NOT NULL,
  CLOUD smallint NOT NULL DEFAULT 0,
  UPLOADED smallint NOT NULL DEFAULT 0,
  DELETED smallint NOT NULL DEFAULT 0,
  CREATED_BY int NOT NULL DEFAULT 0,
  CREATED_AT timestamp NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_file_uploader_temp_file_file_id ON b_ui_file_uploader_temp_file (file_id);
CREATE INDEX ix_b_ui_file_uploader_temp_file_created_at ON b_ui_file_uploader_temp_file (created_at);
CREATE UNIQUE INDEX ux_b_ui_file_uploader_temp_file_guid ON b_ui_file_uploader_temp_file (guid);

CREATE TABLE b_ui_avatar_mask_group (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  OWNER_TYPE varchar(100) NOT NULL,
  OWNER_ID varchar(20) NOT NULL,
  TITLE varchar(255) NOT NULL,
  DESCRIPTION varchar(255),
  SORT int NOT NULL DEFAULT 100,
  TIMESTAMP_X timestamp NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_avatar_mask_group_owner_id_owner_type ON b_ui_avatar_mask_group (owner_id, owner_type);

CREATE TABLE b_ui_avatar_mask_item (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  OWNER_TYPE varchar(100) NOT NULL,
  OWNER_ID varchar(20) NOT NULL,
  FILE_ID int NOT NULL,
  GROUP_ID int,
  TITLE varchar(255),
  DESCRIPTION varchar(255),
  SORT int NOT NULL DEFAULT 100,
  TIMESTAMP_X timestamp NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_avatar_mask_item_owner_type_owner_id ON b_ui_avatar_mask_item (owner_type, owner_id);
CREATE INDEX ix_b_ui_avatar_mask_item_file_id ON b_ui_avatar_mask_item (file_id);

CREATE TABLE b_ui_avatar_mask_access (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ITEM_ID int NOT NULL,
  ACCESS_CODE varchar(50) NOT NULL,
  PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_ui_avatar_mask_access_item_id_access_code ON b_ui_avatar_mask_access (item_id, access_code);
CREATE INDEX ix_b_ui_avatar_mask_access_item_id ON b_ui_avatar_mask_access (item_id);
CREATE INDEX ix_b_ui_avatar_mask_access_access_code ON b_ui_avatar_mask_access (access_code);

CREATE TABLE b_ui_avatar_mask_recently_used (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ITEM_ID int NOT NULL,
  USER_ID int NOT NULL,
  TIMESTAMP_X timestamp,
  PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_ui_avatar_mask_recently_used_item_id_user_id ON b_ui_avatar_mask_recently_used (item_id, user_id);

CREATE TABLE b_ui_avatar_mask_item_applied_to (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ORIGINAL_FILE_ID int NOT NULL,
  FILE_ID int NOT NULL,
  ITEM_ID int NOT NULL,
  USER_ID int NOT NULL,
  TIMESTAMP_X timestamp NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_avatar_mask_item_applied_to_file_id_item_id ON b_ui_avatar_mask_item_applied_to (file_id, item_id);
CREATE INDEX ix_b_ui_avatar_mask_item_applied_to_item_id ON b_ui_avatar_mask_item_applied_to (item_id);
CREATE INDEX ix_b_ui_avatar_mask_item_applied_to_user_id ON b_ui_avatar_mask_item_applied_to (user_id);

CREATE TABLE b_ui_avatar_mask_file_deleted (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ENTITY varchar(50) NOT NULL,
  ORIGINAL_FILE_ID int NOT NULL,
  FILE_ID int NOT NULL,
  ITEM_ID int NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_ui_avatar_mask_file_deleted_entity ON b_ui_avatar_mask_file_deleted (entity);
CREATE INDEX ix_b_ui_avatar_mask_file_deleted_item_id ON b_ui_avatar_mask_file_deleted (item_id);
CREATE INDEX ix_b_ui_avatar_mask_file_deleted_file_id ON b_ui_avatar_mask_file_deleted (file_id);