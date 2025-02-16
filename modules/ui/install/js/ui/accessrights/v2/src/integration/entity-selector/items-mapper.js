import { Loc, Runtime } from 'main.core';
import { type ItemOptions } from 'ui.entity-selector';
import type { Variable, VariableCollection } from '../../store/model/access-rights-model';
import type { UserGroup, UserGroupsCollection } from '../../store/model/user-groups-model';
import { EntitySelectorEntities } from './dictionary';

export class ItemsMapper
{
	static mapUserGroups(userGroups: UserGroupsCollection): ItemOptions[]
	{
		const result: ItemOptions[] = [];

		for (const userGroup: UserGroup of userGroups.values())
		{
			result.push({
				id: userGroup.id,
				entityId: EntitySelectorEntities.ROLE,
				title: userGroup.title,
				supertitle: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE'),
				avatar: '/bitrix/js/ui/accessrights/v2/images/role-avatar.svg',
				tabs: [
					'recents',
				],
			});
		}

		return result;
	}

	static mapVariables(variables: VariableCollection): ItemOptions[]
	{
		const items: ItemOptions[] = [];

		for (const variable: Variable of variables.values())
		{
			const item = Runtime.clone(variable);
			item.entityId = item.entityId || EntitySelectorEntities.VARIABLE;
			item.tabs = 'recents';

			items.push(item);
		}

		return items;
	}
}
