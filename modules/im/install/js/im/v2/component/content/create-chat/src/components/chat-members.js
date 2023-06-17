import {TagSelector} from 'ui.entity-selector';

import {Core} from 'im.v2.application.core';

// @vue/component
export const ChatMembersSelector = {
	emits: ['membersChange'],
	data()
	{
		return {};
	},
	computed:
	{
		currentUserId(): number
		{
			return Core.getUserId();
		}
	},
	created()
	{
		this.membersSelector = new TagSelector({
			maxHeight: 99,
			placeholder: '',
			addButtonCaption: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
			addButtonCaptionMore: this.loc('IM_CREATE_CHAT_USER_SELECTOR_ADD_MEMBERS'),
			showCreateButton: false,
			dialogOptions: {
				enableSearch: false,
				context: 'IM_CHAT_CREATE',
				entities: [
					{id: 'user'},
					{id: 'department'},
				],
				preselectedItems: [['user', this.currentUserId]],
				undeselectedItems: [['user', this.currentUserId]]
			},
			events: {
				onAfterTagAdd: (event) => {
					const selector = event.getTarget();
					this.$emit('membersChange', selector.getTags().map(tag => tag.id));
				},
				onAfterTagRemove: (event) => {
					const selector = event.getTarget();
					this.$emit('membersChange', selector.getTags().map(tag => tag.id));
				}
			}
		});
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs['members']);
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-content-create-chat__members" ref="members"></div>
	`
};