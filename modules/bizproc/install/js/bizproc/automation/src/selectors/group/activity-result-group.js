import { Type } from 'main.core';
import { Field } from '../types';
import { Group } from './group';
import GroupId from './group-id';

export class ActivityResultGroup extends Group
{
	constructor(data: {
		fields: Array<{id: string, title: string, fields: Array<Field>}>,
		title: string
	})
	{
		super(data);

		if (!Type.isStringFilled(data.title))
		{
			throw new TypeError('title must be filled string');
		}

		this.#fillGroups(data.fields, data.title);
	}

	#fillGroups(activities: Array<{id: string, title: string, fields: Array<Field>}>, title: string)
	{
		const groupId = GroupId.ACTIVITY_RESULT;
		this.addGroup(groupId, {
			id: groupId,
			title,
			searchable: false,
		});

		activities.forEach((activity) => {
			this.addGroupItem(groupId, {
				id: activity.id,
				title: activity.title,
				searchable: false,
				children: activity.fields.map((field) => ({
					id: field.SystemExpression, // Expression
					title: field.Name,
					customData: { field },
				})),
			});
		});
	}
}
