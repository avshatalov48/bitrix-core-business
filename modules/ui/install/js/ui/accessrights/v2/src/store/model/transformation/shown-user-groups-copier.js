import { Type } from 'main.core';
import { SELECTED_ALL_USER_ID } from '../user-groups-model';
import type { UserGroupsCollection } from '../user-groups-model';
import type { Transformer } from './transformer';

export class ShownUserGroupsCopier implements Transformer<UserGroupsCollection, UserGroupsCollection>
{
	#srcUserGroups: UserGroupsCollection;
	#maxVisibleUserGroups: ?number = null;
	#sortConfig: Record<string, Record<string, number>>;

	constructor(srcUserGroups: UserGroupsCollection, maxVisibleUserGroups: ?number, sortConfig: Record<string, Record<string, number>>)
	{
		this.#srcUserGroups = srcUserGroups;
		if (Type.isInteger(maxVisibleUserGroups))
		{
			this.#maxVisibleUserGroups = maxVisibleUserGroups;
		}
		this.#sortConfig = sortConfig;
	}

	/**
	 * WARNING! Mutates `externalSource`. Src is not copied for perf reasons, since we don't need it functionally
	 */
	transform(externalSource: UserGroupsCollection): UserGroupsCollection
	{
		for (const [userGroupId, userGroup] of externalSource)
		{
			const srcUserGroup = this.#srcUserGroups.get(userGroupId);
			if (!srcUserGroup)
			{
				// likely it's a just created user group
				this.#addUserGroupInSortConfig(userGroup);
			}
		}

		return externalSource;
	}

	#addUserGroupInSortConfig(userGroup)
	{
		const updateUserSortConfig = (userId) => {
			if (!this.#sortConfig[userId])
			{
				return;
			}

			const values = Object.values(this.#sortConfig[userId]);
			const maxSortValue = values.length > 0 ? Math.max(...values) : 0;
			this.#sortConfig[userId][userGroup.id] = maxSortValue + 1;
		};

		for (const [memberId] of userGroup.members)
		{
			updateUserSortConfig(memberId);
		}
		updateUserSortConfig(SELECTED_ALL_USER_ID);
	}

	getSortConfig(): Record<string, Record<string, number>>
	{
		return this.#sortConfig;
	}
}
