import { Loc } from 'main.core';
import { Field } from '../types';
import { Group } from './group';
import GroupId from './group-id';

export class ConstantGroup extends Group
{
	constructor(data: {
		fields: Array<Field>,
	})
	{
		super(data);

		this.#fillGroups(data.fields);
	}

	#fillGroups(fields: Array<Field>)
	{
		const groupId = GroupId.CONSTANTS;
		this.addGroup(groupId, {
			id: groupId,
			title: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_LIST'),
			searchable: false,
		});

		fields.forEach((field) => {
			this.addGroupItem(groupId, {
				id: field.SystemExpression,
				title: field.Name || field.Id,
				supertitle: field.SuperTitle || '',
				customData: { field },
			});
		});
	}
}
