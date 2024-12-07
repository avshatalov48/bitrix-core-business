import type { RelationData } from '../type/data';

export default class RelationCollection
{
	static map = new Map();

	static getRelation(eventId: number): RelationData | false
	{
		return RelationCollection.map.get(eventId) ?? false;
	}

	static setRelation(relationData: RelationData): void
	{
		RelationCollection.map.set(relationData.eventId, relationData);
	}
}
