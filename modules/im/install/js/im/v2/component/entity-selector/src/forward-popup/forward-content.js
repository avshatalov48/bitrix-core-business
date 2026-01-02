import { EventEmitter } from 'main.core.events';

import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { SendingService } from 'im.v2.provider.service.sending';
import { Messenger } from 'im.public';
import { EventType, SoundType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { ForwardSearch, ChatSearchInput } from 'im.v2.component.search';
import { Notifier } from 'im.v2.lib.notifier';

import type { JsonObject } from 'main.core';

import './forward-content.css';

// @vue/component
export const ForwardContent = {
	name: 'ForwardContent',
	components: { ForwardSearch, ChatSearchInput },
	props:
	{
		messagesIds: {
			type: Array,
			required: true,
		},
		dialogId: {
			type: String,
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
	beforeUnmount()
	{
		Analytics.getInstance().messageForward.onClosePopup();
	},
	methods:
	{
		onLoading(value: boolean)
		{
			this.isLoading = value;
		},
		onUpdateSearch(query: string)
		{
			Analytics.getInstance().messageForward.onStartSearch({ dialogId: this.dialogId });
			this.searchQuery = query.trim().toLowerCase();
		},
		isNotes(dialogId: string): boolean
		{
			return this.$store.getters['chats/isNotes'](dialogId);
		},
		async forwardToNotes(forwardDialogId: string)
		{
			await SendingService.getInstance().forwardMessages({
				forwardIds: this.messagesIds,
				dialogId: forwardDialogId,
			});

			Notifier.message.onForwardNotesComplete(this.messagesIds);

			SoundNotificationManager.getInstance().playOnce(SoundType.send);
		},
		async onSelectItem(event: {dialogId: string, nativeEvent: KeyboardEvent})
		{
			const { dialogId: forwardDialogId } = event;

			EventEmitter.emit(EventType.dialog.closeBulkActionsMode, {
				dialogId: this.dialogId,
			});

			const isNotesForward = this.isNotes(forwardDialogId);
			const isNotesOpen = this.isNotes(this.dialogId);
			if (isNotesForward && !isNotesOpen)
			{
				void this.forwardToNotes(forwardDialogId);
			}
			else
			{
				await Messenger.openChat(forwardDialogId);
				EventEmitter.emit(EventType.textarea.insertForward, {
					messagesIds: this.messagesIds,
					dialogId: forwardDialogId,
				});
			}

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
				<ForwardSearch
					:query="searchQuery"
					:dialogId="dialogId"
					@clickItem="onSelectItem"
					@loading="onLoading"
				/>
			</div>
		</div>
	`,
};
