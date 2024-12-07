export type RecentActivityModel = {
	typeId: string,
	entityId: number | null,
	description: string,
	date: Date,
	timestamp: number,
	secondaryEntityId: number | null,
};
