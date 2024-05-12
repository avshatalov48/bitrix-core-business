import { Type } from 'main.core';
import { Field } from '../types';
import { Group } from './group';
import GroupId from './group-id';

export class DocumentGroup extends Group
{
	constructor(data: {
		fields: Array<Field>,
		title: string,
	})
	{
		super(data);

		if (!Type.isStringFilled(data.title))
		{
			throw new TypeError('title must be filled string');
		}

		this.#fillGroups(data.fields, data.title);
	}

	#fillGroups(fields: Array<Field>, title: string)
	{
		const rootGroupId = GroupId.DOCUMENT;
		this.addGroup(rootGroupId, {
			id: rootGroupId,
			title,
			searchable: false,
		});

		fields.forEach((field) => {
			let groupKey = field.Id.includes('.') ? field.Id.split('.')[0] : rootGroupId;
			let groupName = '';
			let fieldName = field.Name;

			if (field.Name && groupKey !== rootGroupId && field.Name.includes(': '))
			{
				const names = field.Name.split(': ');
				groupName = names.shift();
				fieldName = names.join(': ');
			}

			if (field.Id.startsWith('ASSIGNED_BY_') && field.Id !== 'ASSIGNED_BY_ID' && field.Id !== 'ASSIGNED_BY_PRINTABLE')
			{
				groupKey = 'ASSIGNED_BY';
				const names = field.Name.split(' ');
				groupName = names.shift();
				fieldName = names.join(' ').replace('(', '').replace(')', '');
			}

			if (!this.hasGroup(groupKey))
			{
				this.addGroup(groupKey, {
					id: groupKey,
					title: groupName,
					searchable: false,
				});
			}

			this.addGroupItem(groupKey, {
				id: field.SystemExpression,
				title: fieldName || field.Id,
				customData: { field },
			});
		});
	}
}
