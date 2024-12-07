import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';

import '../css/empty-state.css';

import type { JsonObject } from 'main.core';

const defaultMessages = [
	Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_1'),
	Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_2'),
	Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_3'),
	Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_4'),
	Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_5'),
];

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		defaultMessages: () => defaultMessages,
	},
	methods:
	{
		onMessageClick(text: string): void
		{
			EventEmitter.emit(EventType.textarea.insertText, {
				text,
				dialogId: this.dialogId,
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-list-empty-state__container">
			<div class="bx-im-message-list-empty-state__content">
				<div class="bx-im-message-list-empty-state__icon"></div>
				<div class="bx-im-message-list-empty-state__title">{{ loc('IM_MESSAGE_LIST_EMPTY_STATE_TITLE') }}</div>
				<div class="bx-im-message-list-empty-state__action-list">
					<div
						v-for="(message, index) in defaultMessages"
						:key="index"
						@click="onMessageClick(message)"
						class="bx-im-message-list-empty-state__action-list_item"
					>
						{{ message }}
					</div>
				</div>
			</div>
		</div>
	`,
};
