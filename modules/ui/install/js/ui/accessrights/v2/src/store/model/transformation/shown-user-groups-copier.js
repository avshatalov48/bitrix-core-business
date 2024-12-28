import { Type } from 'main.core';
import type { UserGroupsCollection } from '../user-groups-model';
import type { Transformer } from './transformer';

export class ShownUserGroupsCopier implements Transformer<UserGroupsCollection, UserGroupsCollection>
{
	#srcUserGroups: UserGroupsCollection;
	#maxVisibleUserGroups: ?number = null;

	constructor(srcUserGroups: UserGroupsCollection, maxVisibleUserGroups: ?number)
	{
		this.#srcUserGroups = srcUserGroups;
		if (Type.isInteger(maxVisibleUserGroups))
		{
			this.#maxVisibleUserGroups = maxVisibleUserGroups;
		}
	}

	/**
	 * WARNING! Mutates `externalSource`. Src is not copied for perf reasons, since we don't need it functionally
	 */
	transform(externalSource: UserGroupsCollection): UserGroupsCollection
	{
		for (const [userGroupId, userGroup] of externalSource)
		{
			const srcUserGroup = this.#srcUserGroups.get(userGroupId);
			if (srcUserGroup)
			{
				userGroup.isShown = srcUserGroup.isShown;
			}
			else
			{
				// likely it's a just created user group
				userGroup.isShown = true;
			}
		}

		if (this.#maxVisibleUserGroups > 0)
		{
			this.#ensureThatNoMoreUserGroupsThanMaxIsShown(externalSource);
		}

		return externalSource;
	}

	#ensureThatNoMoreUserGroupsThanMaxIsShown(userGroups: UserGroupsCollection): void
	{
		let shownCount = 0;
		for (const userGroup of userGroups.values())
		{
			if (!userGroup.isShown)
			{
				continue;
			}

			shownCount++;

			if (shownCount > this.#maxVisibleUserGroups)
			{
				userGroup.isShown = false;
			}
		}
	}
}
