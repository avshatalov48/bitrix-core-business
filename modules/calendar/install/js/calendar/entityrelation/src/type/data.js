export type EntityData = {
	id: number,
	type: string,
	link: string,
};

export type OwnerData = {
	id: number,
	name: string,
	avatar: string,
	link: string,
};

export type RelationData = {
	eventId: number,
	entity: EntityData,
	owner: OwnerData,
};
