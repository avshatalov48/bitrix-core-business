
create table if not exists b_conv_context
(
	ID       int unsigned not null auto_increment,
	SNAPSHOT char(64)     not null,
	primary key (ID),
	unique key IX_B_CONV_CONTEXT_SNAPSHOT (SNAPSHOT)
);

create table if not exists b_conv_context_attribute
(
	CONTEXT_ID int unsigned not null,
	NAME       varchar(30)  not null,
	VALUE      varchar(30)  not null,
	primary key (CONTEXT_ID, NAME)
);

create table if not exists b_conv_context_counter_day
(
	DAY        date         not null,
	CONTEXT_ID int unsigned not null,
	NAME       varchar(30)  not null,
	VALUE      float        not null,
	primary key (DAY, CONTEXT_ID, NAME)
);

create table if not exists b_conv_context_entity_item
(
	ENTITY     varchar(30)  not null,
	ITEM       varchar(30)  not null,
	CONTEXT_ID int unsigned not null,
	primary key (ENTITY, ITEM)
);
