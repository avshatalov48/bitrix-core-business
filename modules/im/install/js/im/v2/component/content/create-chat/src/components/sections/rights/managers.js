import { TagSelector } from 'ui.entity-selector';

import { Core } from 'im.v2.application.core';

// @vue/component
export const ManagersSelector = {
	props:
	{
		managerIds: {
			type: Array,
			required: true,
		},
	},
	emits: ['managersChange'],
	data()
	{
		return {};
	},
	computed:
	{
		currentUserId(): number
		{
			return Core.getUserId();
		},
	},
	created()
	{
		const preselectedItems = this.managerIds.map((userId: number) => {
			return ['user', userId];
		});

		this.membersSelector = new TagSelector({
			maxHeight: 99,
			placeholder: '',
			addButtonCaption: this.loc('IM_CREATE_CHAT_RIGHTS_SECTION_ADD_MANAGERS'),
			addButtonCaptionMore: this.loc('IM_CREATE_CHAT_RIGHTS_SECTION_ADD_MANAGERS'),
			showCreateButton: false,
			dialogOptions: {
				enableSearch: false,
				context: 'IM_CHAT_CREATE',
				entities: [
					{ id: 'user' },
					{ id: 'department' },
				],
				preselectedItems,
			},
			events: {
				onAfterTagAdd: (event) => {
					const selector = event.getTarget();
					this.$emit('managersChange', selector.getTags().map((tag) => tag.id));
				},
				onAfterTagRemove: (event) => {
					const selector = event.getTarget();
					this.$emit('managersChange', selector.getTags().map((tag) => tag.id));
				},
			},
		});
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs.managers);
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-create-chat__managers" ref="managers"></div>
	`,
};
