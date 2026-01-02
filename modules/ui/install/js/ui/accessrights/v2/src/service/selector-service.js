import { Type } from 'main.core';
import { Dialog, type DialogOptions, type EntityOptions, type Item, type ItemId } from 'ui.entity-selector';
import { EntitySelectorContext } from '../integration/entity-selector/dictionary';
import type { AdditionalMembersParams } from '../store/model/application-model';
import type { Member } from '../store/model/user-groups-model';

declare type StructureNodeEntityTypeInfo = {
	commonPrefix: string,
	recursivePrefix: string,
	memberType: string,
};

const STRUCTURE_NODE_ENTITY_TYPE = Object.freeze({
	TEAM: 'team',
	DEPARTMENT: 'department',
});

const STRUCTURE_NODE_ENTITY_TYPE_INFO: Object<string, StructureNodeEntityTypeInfo> = Object.freeze({
	[STRUCTURE_NODE_ENTITY_TYPE.DEPARTMENT]: {
		commonPrefix: 'SND',
		recursivePrefix: 'SNDR',
		memberType: 'structuredepartments',
	},
	[STRUCTURE_NODE_ENTITY_TYPE.TEAM]: {
		commonPrefix: 'SNT',
		recursivePrefix: 'SNTR',
		memberType: 'structureteams',
	},
});

export class SelectorService
{
	#options: AdditionalMembersParams;

	constructor(options: AdditionalMembersParams)
	{
		this.#options = options;
	}

	createDialog(dialogOptions: DialogOptions): Dialog
	{
		return new Dialog({
			...dialogOptions,
			context: EntitySelectorContext.MEMBER,
			enableSearch: true,
			alwaysShowLabels: true,
			cacheable: false,
			entities: dialogOptions.entities ?? this.entities(),
		});
	}

	entities(): EntityOptions[]
	{
		const entities: EntityOptions[] = [
			{
				id: 'user',
				options: {
					intranetUsersOnly: true,
					emailUsers: false,
					inviteEmployeeLink: false,
					inviteGuestLink: false,
				},
			},
		];

		const includedNodeEntityTypes = [];
		if (this.#options.useStructureDepartmentsProviderTab)
		{
			includedNodeEntityTypes.push('department');
		}

		if (this.#options.addStructureTeamsProviderTab)
		{
			includedNodeEntityTypes.push('team');
		}

		if (includedNodeEntityTypes.length > 0)
		{
			entities.push({
				id: 'structure-node',
				options: {
					selectMode: 'usersAndDepartments',
					allowSelectRootDepartment: true,
					allowFlatDepartments: true,
					includedNodeEntityTypes,
					useMultipleTabs: true,
					visual: {
						avatarMode: 'node',
						tagStyle: 'none',
					},
				},
			});
		}

		if (!this.#options.useStructureDepartmentsProviderTab)
		{
			entities.push({
				id: 'department',
				options: {
					selectMode: 'usersAndDepartments',
					allowSelectRootDepartment: true,
					allowFlatDepartments: true,
				},
			});
		}

		entities.push({
			id: 'site-groups',
			dynamicLoad: true,
			dynamicSearch: true,
		});

		if (this.#options.addStructureRolesProviderTab)
		{
			entities.push({
				id: 'structure-role',
				options: {
					includedNodeEntityTypes: ['team', 'department'],
				},
				dynamicLoad: true,
				dynamicSearch: true,
			});
		}

		if (this.#options.addProjectsProviderTab)
		{
			entities.push({
				id: 'project-access-codes',
			});
		}

		if (this.#options.addUserGroupsProviderTab)
		{
			entities.push({
				id: 'user-groups',
				dynamicLoad: true,
				options: {},
			});
		}

		return entities;
	}

	// eslint-disable-next-line sonarjs/cognitive-complexity
	getItemIdByAccessCode(accessCode: string): ItemId
	{
		if (/^I?U(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^I?U(\d+)$/) || null;
			const userId = match ? match[1] : null;

			return ['user', userId];
		}

		if (/^DR(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^DR(\d+)$/) || null;
			const departmentId = match ? match[1] : null;

			return ['department', departmentId];
		}

		if (/^D(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^D(\d+)$/) || null;
			const departmentId = match ? match[1] : null;

			return ['department', `${departmentId}:F`];
		}

		if (/^G(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^G(\d+)$/) || null;
			const groupId = match ? match[1] : null;

			return ['site-groups', groupId];
		}

		if (/^(?:ATD|ATE|ATT|AD|AE|AT)[1-9]\d*$/.test(accessCode))
		{
			return ['structure-role', accessCode];
		}

		if (accessCode.at(0) === 'A')
		{
			return ['user-groups', accessCode];
		}

		if (/^SG(\d+)_([AEK])$/.test(accessCode))
		{
			return ['project-access-codes', accessCode];
		}

		if (/^SNT(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SNT(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', `${structureNodeId}:F`];
		}

		if (/^SNTR(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SNTR(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', structureNodeId];
		}

		if (/^SND(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SND(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', `${structureNodeId}:F`];
		}

		if (/^SNDR(\d+)$/.test(accessCode))
		{
			const match = accessCode.match(/^SNDR(\d+)$/) || null;
			const structureNodeId = match ? match[1] : null;

			return ['structure-node', structureNodeId];
		}

		return ['unknown', accessCode];
	}

	getMemberTypeByItem(item: Item): string
	{
		switch (item.entityId)
		{
			case 'user':
				return 'users';
			case 'intranet':
			case 'department':
				return 'departments';
			case 'socnetgroup':
			case 'project-access-codes':
				return 'sonetgroups';
			case 'group':
				return 'groups';
			case 'structure-node':
				return this.getItemStructureNodeEntityTypeInfo(item, STRUCTURE_NODE_ENTITY_TYPE.TEAM).memberType;
			case 'site-groups':
			case 'user-groups':
			case 'structure-role':
				return 'usergroups';
			default:
				return '';
		}
	}

	getItemStructureNodeEntityTypeInfo(
		item: Item,
		defaultNodeEntityType: $Values<typeof STRUCTURE_NODE_ENTITY_TYPE>,
	): StructureNodeEntityTypeInfo
	{
		const availableNodeEntityTypes = Object.values(STRUCTURE_NODE_ENTITY_TYPE);

		let itemNodeEntityType = item.getCustomData().get('nodeEntityType') ?? '';
		if (availableNodeEntityTypes.includes(itemNodeEntityType))
		{
			return STRUCTURE_NODE_ENTITY_TYPE_INFO[itemNodeEntityType];
		}

		const accessCode: string = item.getCustomData().get('accessCode') ?? '';
		Object.entries(STRUCTURE_NODE_ENTITY_TYPE_INFO)
			.forEach(([nodeEntityType, info]) => {
				if (accessCode.startsWith(info.commonPrefix))
				{
					itemNodeEntityType = nodeEntityType;
				}
			});

		if (!availableNodeEntityTypes.includes(itemNodeEntityType))
		{
			itemNodeEntityType = defaultNodeEntityType;
		}

		return STRUCTURE_NODE_ENTITY_TYPE_INFO[itemNodeEntityType];
	}

	// eslint-disable-next-line sonarjs/cognitive-complexity
	getAccessCodeByItem(item: Item): string
	{
		const entityId = item.entityId;

		if (entityId === 'user')
		{
			return `U${item.id}`;
		}

		if (entityId === 'department')
		{
			if (Type.isString(item.id) && item.id.endsWith(':F'))
			{
				const match = item.id.match(/^(\d+):F$/);
				const originalId = match ? match[1] : null;

				// only members of the department itself
				return `D${originalId}`;
			}

			// whole department recursively
			return `DR${item.id}`;
		}

		if (entityId === 'structure-node')
		{
			const itemNodeEntityType = this.getItemStructureNodeEntityTypeInfo(item, STRUCTURE_NODE_ENTITY_TYPE.TEAM);
			if (Type.isString(item.id) && item.id.endsWith(':F'))
			{
				const match = item.id.match(/^(\d+):F$/);
				const originalId = match ? match[1] : null;
				const prefix = itemNodeEntityType.commonPrefix;

				return `${prefix}${originalId}`;
			}

			const prefix = itemNodeEntityType.recursivePrefix;

			return `${prefix}${item.id}`;
		}

		if (entityId === 'site-groups')
		{
			return `G${item.id}`;
		}

		if (entityId === 'structure-role')
		{
			return item.id;
		}

		if (entityId === 'user-groups')
		{
			return item.id;
		}

		if (entityId === 'project-access-codes')
		{
			return item.id;
		}

		return '';
	}

	getItemIdsByAccessCodes(accessCodes: string[]): Map<string, ItemId>
	{
		const map = new Map();
		accessCodes.forEach((accessCode: string) => {
			map.set(accessCode, this.getItemIdByAccessCode(accessCode));
		});

		return map;
	}

	getMemberByItem(item: Item): Member
	{
		return {
			id: this.getAccessCodeByItem(item),
			type: this.getMemberTypeByItem(item),
			name: item.title.text,
			avatar: Type.isStringFilled(item.avatar) ? item.avatar : null,
		};
	}
}
