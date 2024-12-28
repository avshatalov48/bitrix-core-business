import type { AccessRightValue, Member, UserGroup, UserGroupsCollection } from '../../../user-groups-model';
import { NEW_USER_GROUP_ID_PREFIX } from '../../../user-groups-model';
import type { ExternalAccessRightValue, ExternalUserGroup } from '../../internalizer/user-groups-internalizer';

export type UserGroupSaveData = ExternalUserGroup & {
	members: null,
	accessCodes: { [accessCode: string]: string },
};

/**
 * @abstract
 */
export class BaseUserGroupsExporter implements Transformer<UserGroupsCollection, UserGroupSaveData[]>
{
	transform(source: UserGroupsCollection): UserGroupSaveData[]
	{
		const result: UserGroupSaveData[] = [];

		for (const userGroup of source.values())
		{
			result.push({
				id: userGroup.id.startsWith(NEW_USER_GROUP_ID_PREFIX) ? '0' : userGroup.id,
				title: userGroup.title,
				accessCodes: this.#transformAccessCodes(userGroup.members),
				accessRights: this.#transformAccessRightValues(userGroup),
			});
		}

		return result;
	}

	#transformAccessCodes(members: Map<string, Member>): { [accessCode: string]: string }
	{
		const result = {};

		for (const [accessCode, member] of members)
		{
			result[accessCode] = member.type;
		}

		return result;
	}

	#transformAccessRightValues(userGroup: UserGroup): ExternalAccessRightValue[]
	{
		const result: ExternalAccessRightValue[] = [];

		for (const accessRightValue of userGroup.accessRights.values())
		{
			if (!this.shouldBeIncludedInExport(userGroup, accessRightValue))
			{
				continue;
			}

			for (const singleValue of accessRightValue.values)
			{
				result.push({
					id: accessRightValue.id,
					value: singleValue,
				});
			}
		}

		return result;
	}

	/**
	 * @abstract
	 * @protected
	 */
	shouldBeIncludedInExport(userGroup: UserGroup, accessRightValue: AccessRightValue): boolean
	{
		throw new Error('Not implemented');
	}
}
