import { Loc } from 'main.core';
import { Field } from '../types';
import { Group } from './group';
import GroupId from './group-id';

export class TriggerResultGroup extends Group
{
	constructor(data: { fields: Array<{id: string, title: string, fields: Array<Field>}> })
	{
		super(data);

		this.#fillGroups(data.fields);
	}

	#fillGroups(groups: Array<{id: string, title: string, fields: Array<Field>}>)
	{
		const groupId = GroupId.TRIGGER_RESULT;
		this.addGroup(groupId, {
			id: groupId,
			title: Loc.getMessage('BIZPROC_JS_AUTOMATION_SELECTOR_GROUP_MANAGER_TRIGGER_LIST'),
			searchable: false,
		});

		groups.forEach((group) => {
			this.addGroupItem(groupId, {
				id: group.id,
				title: group.title,
				searchable: false,
				children: group.fields.map((field) => ({
					id: field.SystemExpression,
					title: field.Name,
					customData: { field },
				})),
			});
		});
	}
}
