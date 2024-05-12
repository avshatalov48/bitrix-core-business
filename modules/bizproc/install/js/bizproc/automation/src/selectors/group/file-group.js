import { Loc, Type } from 'main.core';
import { Field } from '../types';
import { Group } from './group';
import GroupId from './group-id';

export class FileGroup extends Group
{
	constructor(data: { fields: Array<Field> })
	{
		super(data);

		this.#fillGroups(data.fields);
	}

	#fillGroups(fields: Array<Field>)
	{
		const groupId = GroupId.FILES;

		this.addGroup(groupId, {
			id: groupId,
			title: Loc.getMessage('BIZPROC_AUTOMATION_CMP_FILES_LINKS'),
			searchable: false,
		});

		fields.forEach((field) => {
			let title = field.Name || field.Id;
			if (Type.isStringFilled(field.ObjectName))
			{
				title = `${field.ObjectName}: ${title}`;
			}

			this.addGroupItem(groupId, {
				id: field.SystemExpression, // Expression,
				title,
				customData: { field },
			});
		});
	}
}
