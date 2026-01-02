import { Core } from 'im.v2.application.core';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { BaseEvent } from 'main.core.events';
import {
	type Dialog as SelectorDialog,
	type EntityOptions,
	type Item as SelectorItem,
	TagSelector,
} from 'ui.entity-selector';

import './css/chat-members.css';

// @vue/component
export const ChatMembersSelector = {
	props:
	{
		chatMembers: {
			type: Array,
			required: true,
		},
		customElements: {
			type: Array,
			default: () => [],
		},
		undeselectedItems: {
			type: Array,
			default(): [[string, number | string]] {
				return [['user', Core.getUserId()]];
			},
		},
		allowTeamsSelect: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['membersChange'],
	created()
	{
		const addButtonCaption = this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS_V2');

		this.membersSelector = new TagSelector({
			maxHeight: 99,
			placeholder: '',
			addButtonCaption,
			addButtonCaptionMore: addButtonCaption,
			showCreateButton: false,
			items: this.customElements,
			dialogOptions: {
				enableSearch: true,
				alwaysShowLabels: true,
				context: 'IM_CHAT_CREATE',
				entities: this.getEntitiesConfig(),
				preselectedItems: this.chatMembers,
				undeselectedItems: this.undeselectedItems,
				events: {
					'Item:onSelect': this.onItemsChange,
					'Item:onDeselect': this.onItemsChange,
				},
			},
		});
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs.members);
	},
	methods:
	{
		getEntitiesConfig(): EntityOptions[]
		{
			const entitiesConfig = [{ id: 'user' }];
			const allowDepartments = FeatureManager.isFeatureAvailable(Feature.chatDepartments);
			entitiesConfig.push(this.getDepartmentEntityConfig(allowDepartments));

			if (this.allowTeamsSelect && FeatureManager.isFeatureAvailable(Feature.teamsInStructureAvailable))
			{
				entitiesConfig.push(this.getEntityTeamsConfig(allowDepartments));
			}

			return entitiesConfig;
		},
		getDepartmentEntityConfig(allowDepartments: boolean): EntityOptions
		{
			if (allowDepartments)
			{
				return {
					id: 'department',
					options: {
						selectMode: 'usersAndDepartments',
						allowFlatDepartments: true,
						allowSelectRootDepartment: true,
					},
				};
			}

			return { id: 'department' };
		},
		getEntityTeamsConfig(allowDepartments: boolean): EntityOptions
		{
			const baseOptionsConfig = {
				includedNodeEntityTypes: ['team'],
				useMultipleTabs: true,
				visual: {
					avatarMode: 'node',
					tagStyle: 'none',
				},
			};

			const departmentOptionsConfig = {
				...baseOptionsConfig,
				selectMode: 'usersAndDepartments',
				allowFlatDepartments: true,
				allowSelectRootDepartment: true,
			};

			return {
				id: 'structure-node',
				options: allowDepartments ? departmentOptionsConfig : baseOptionsConfig,
			};
		},
		onItemsChange(event: BaseEvent)
		{
			const dialog: SelectorDialog = event.getTarget();
			const selectedItems: SelectorItem[] = dialog.getSelectedItems();
			this.$emit('membersChange', selectedItems.map((item) => this.prepareTag(item)));
		},
		prepareTag(tag: SelectorItem): [string, number | string]
		{
			return [tag.getEntityId(), tag.getId()];
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-chat-forms-chat-members__container" ref="members"></div>
	`,
};
