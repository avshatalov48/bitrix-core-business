import { Type } from 'main.core';
import { Dialog, type Item, type ItemId } from 'ui.entity-selector';
import { EntitySelectorContext } from '../../../integration/entity-selector/dictionary';

export const Selector = {
	name: 'Selector',
	emits: ['close'],
	props: {
		userGroup: {
			/** @type UserGroup */
			type: Object,
			required: true,
		},
		bindNode: {
			type: HTMLElement,
			required: true,
		},
	},
	computed: {
		selectedItems(): ItemId[] {
			const result = [];
			for (const accessCode of this.userGroup.members.keys())
			{
				result.push(this.getItemIdByAccessCode(accessCode));
			}

			return result;
		},
	},
	mounted()
	{
		(new Dialog({
			enableSearch: true,
			context: EntitySelectorContext.MEMBER,
			alwaysShowLabels: true,
			entities: [
				{
					id: 'user',
					options: {
						intranetUsersOnly: true,
						emailUsers: false,
						inviteEmployeeLink: false,
						inviteGuestLink: false,
					},
				},
				{
					id: 'department',
					options: {
						selectMode: 'usersAndDepartments',
						allowSelectRootDepartment: true,
						allowFlatDepartments: true,
					},
				},
				{
					id: 'project',
					dynamicLoad: true,
					options: {
						addProjectMetaUsers: true,
					},
					itemOptions: {
						default: {
							link: '',
							linkTitle: '',
						},
					},
				},
				{
					id: 'site-groups',
					dynamicLoad: true,
					dynamicSearch: true,
				},
			],
			targetNode: this.bindNode,
			preselectedItems: this.selectedItems,
			cacheable: false,
			events: {
				'Item:onSelect': this.onMemberAdd,
				'Item:onDeselect': this.onMemberRemove,
				onHide: () => {
					this.$emit('close');
				},
			},
		})).show();
	},
	methods: {
		// eslint-disable-next-line sonarjs/cognitive-complexity
		getItemIdByAccessCode(accessCode: string): ItemId {
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

			if (/^SG(\d+)_([AEK])$/.test(accessCode))
			{
				const match = accessCode.match(/^SG(\d+)_([AEK])$/) || null;

				const projectId = match ? match[1] : null;
				const postfix = match ? match[2] : null;

				return ['project', `${projectId}:${postfix}`];
			}

			return ['unknown', accessCode];
		},
		onMemberAdd(event: BaseEvent): void {
			const member = this.getMemberFromEvent(event);

			this.$store.dispatch('userGroups/addMember', {
				userGroupId: this.userGroup.id,
				accessCode: member.id,
				member,
			});
		},
		onMemberRemove(event: BaseEvent): void {
			const member = this.getMemberFromEvent(event);

			this.$store.dispatch('userGroups/removeMember', {
				userGroupId: this.userGroup.id,
				accessCode: member.id,
			});
		},
		getMemberFromEvent(event: BaseEvent): ?Member {
			const { item } = event.getData();

			return {
				id: this.getAccessCodeByItem(item),
				type: this.getMemberTypeByItem(item),
				name: item.title.text,
				avatar: Type.isStringFilled(item.avatar) ? item.avatar : null,
			};
		},
		// eslint-disable-next-line sonarjs/cognitive-complexity
		getAccessCodeByItem(item: Item): string {
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

			if (entityId === 'site-groups')
			{
				return `G${item.id}`;
			}

			if (entityId === 'project')
			{
				const subType = item.customData.get('metauser');
				const originalId = item.customData.get('projectId');
				if (subType === 'owner')
				{
					return `SG${originalId}_A`;
				}

				if (subType === 'moderator')
				{
					return `SG${originalId}_E`;
				}

				if (subType === 'all')
				{
					return `SG${originalId}_K`;
				}
			}

			return '';
		},
		getMemberTypeByItem(item: Item): string {
			switch (item.entityId)
			{
				case 'user':
					return 'users';
				case 'intranet':
				case 'department':
					return 'departments';
				case 'socnetgroup':
				case 'project':
					return 'sonetgroups';
				case 'group':
					return 'groups';
				case 'site-groups':
					return 'usergroups';
				default:
					return '';
			}
		},
	},
	// just a template stub
	template: '<div hidden></div>',
};
