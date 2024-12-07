import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { EventType } from 'im.v2.const';
import { ChatSearchInput } from 'im.v2.component.search.chat-search-input';
import { ChatSearch } from 'im.v2.component.search.chat-search';

import type { JsonObject } from 'main.core';

import './forward-content.css';

const searchConfig = Object.freeze({
	chats: true,
	users: true,
});

// @vue/component
export const ForwardContent = {
	name: 'ForwardContent',
	components: { ChatSearch, ChatSearchInput },
	props:
	{
		messageId: {
			type: [Number, String],
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {
			searchQuery: '',
			isLoading: false,
		};
	},
	computed:
	{
		searchConfig: () => searchConfig,
	},
	methods:
	{
		onLoading(value: boolean)
		{
			this.isLoading = value;
		},
		onUpdateSearch(query: string)
		{
			this.searchQuery = query;
		},
		async onSelectItem(event)
		{
			const { dialogId } = event;

			await Messenger.openChat(dialogId);
			EventEmitter.emit(EventType.textarea.insertForward, {
				messageId: this.messageId,
				dialogId
			});
			this.$emit('close');
		},
	},
	template: `
		<div class="bx-im-entity-selector-forward__container">
			<div class="bx-im-entity-selector-forward__input">
				<ChatSearchInput 
					:searchMode="true" 
					:isLoading="isLoading" 
					:withIcon="false" 
					:delayForFocusOnStart="1"
					@updateSearch="onUpdateSearch"
				/>
			</div>
			<div class="bx-im-entity-selector-forward__search-result-container">
				<ChatSearch
					:searchMode="true"
					:searchQuery="searchQuery"
					:searchConfig="searchConfig"
					@clickItem="onSelectItem"
					@loading="onLoading"
				/>
			</div>
		</div>
	`,
};
