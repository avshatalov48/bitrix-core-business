import { Type } from 'main.core';
import type { AccessRightValue, UserGroupsCollection } from '../../user-groups-model';
import { Member, UserGroup } from '../../user-groups-model';
import type { Transformer } from '../transformer';

export type ExternalUserGroup = {
	id: any,
	title: any,
	accessRights: ExternalAccessRightValue[],
	members: {[accessCode: string]: ExternalMember},
}

export type ExternalAccessRightValue = {
	id: any,
	value: any | any[],
};

export type ExternalMember = {
	type: any,
	id: any,
	name: any,
	avatar: any,
};

export class UserGroupsInternalizer implements Transformer<ExternalUserGroup[], UserGroupsCollection>
{
	#maxVisibleUserGroups: ?number = null;

	constructor(maxVisibleUserGroups: ?number)
	{
		if (Type.isInteger(maxVisibleUserGroups))
		{
			this.#maxVisibleUserGroups = maxVisibleUserGroups;
		}
	}

	transform(externalSource: ExternalUserGroup[]): UserGroupsCollection
	{
		const result = new Map();

		for (const externalGroup of externalSource)
		{
			const internalGroup = this.#internalizeExternalGroup(externalGroup);
			if (this.#maxVisibleUserGroups > 0 && result.size >= this.#maxVisibleUserGroups)
			{
				internalGroup.isShown = false;
			}

			result.set(internalGroup.id, internalGroup);
		}

		return result;
	}

	#internalizeExternalGroup(externalGroup: ExternalUserGroup): UserGroup
	{
		const internalizedGroup: UserGroup = {
			id: String(externalGroup.id),
			isNew: false,
			isModified: false,
			isShown: true,
			title: String(externalGroup.title),
			accessRights: new Map(),
			members: new Map(),
		};

		for (const externalValue: ExternalAccessRightValue of externalGroup.accessRights)
		{
			const internalizedValue = this.#internalizeExternalAccessRightsValue(externalValue);

			if (internalizedGroup.accessRights.has(internalizedValue.id))
			{
				for (const previousValue of internalizedGroup.accessRights.get(internalizedValue.id).values)
				{
					internalizedValue.values.add(previousValue);
				}
			}

			internalizedGroup.accessRights.set(internalizedValue.id, internalizedValue);
		}

		for (const [accessCode: string, externalMember: ExternalMember] of Object.entries(externalGroup.members))
		{
			const internalizedAccessCode = this.#internalizeExternalAccessCode(accessCode);

			internalizedGroup.members.set(internalizedAccessCode, this.#internalizeExternalMember(externalMember));
		}

		return internalizedGroup;
	}

	#internalizeExternalAccessRightsValue(externalAccessRightsValue: ExternalAccessRightValue): AccessRightValue
	{
		const valueId = String(externalAccessRightsValue.id);

		const internalized: AccessRightValue = {
			id: valueId,
			isModified: false,
		};

		const values: Array<any> = Type.isArray(externalAccessRightsValue.value)
			? externalAccessRightsValue.value
			: [externalAccessRightsValue.value];

		internalized.values = new Set(values.map((x) => String(x)));

		return internalized;
	}

	#internalizeExternalAccessCode(accessCode: any): string
	{
		let stringAccessCode = String(accessCode);

		if (/^IU(\d+)$/.test(stringAccessCode))
		{
			// `IU` and `U` are basically the same in this extension. differentiation between them is not supported
			// for data consistency, force `U`
			stringAccessCode = stringAccessCode.replace('IU', 'U');
		}

		return stringAccessCode;
	}

	#internalizeExternalMember(externalMember: ExternalMember): Member
	{
		return {
			type: String(externalMember.type),
			id: String(externalMember.id),
			name: String(externalMember.name),
			avatar: Type.isStringFilled(externalMember.avatar) ? externalMember.avatar : null,
		};
	}
}
