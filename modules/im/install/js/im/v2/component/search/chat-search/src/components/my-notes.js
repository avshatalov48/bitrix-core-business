import { Core } from 'im.v2.application.core';

import { SearchContextMenu } from '../classes/search-context-menu';

import '../css/my-notes.css';

// @vue/component
export const MyNotes = {
	name: 'MyNotes',
	emits: ['clickItem'],
	computed:
	{
		dialogId(): number
		{
			return Core.getUserId().toString();
		},
		name(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SEARCH_MY_NOTES');
		},
	},
	created()
	{
		this.contextMenuManager = new SearchContextMenu();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		onClick(event)
		{
			this.$emit('clickItem', {
				dialogId: this.dialogId,
				nativeEvent: event,
			});
		},
	},
	template: `
		<div 
			class="bx-im-search-my-notes__container bx-im-search-my-notes__scope"
			@click="onClick" 
			@click.right.prevent
		>
			<div class="bx-im-search-my-notes__avatar"></div>
			<div class="bx-im-search-my-notes__title" :title="name">
				{{ name }}
			</div>
		</div>
	`,
};
