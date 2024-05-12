import { EventEmitter } from 'main.core.events';
import { hint } from 'ui.vue3.directives.hint';

import { SidebarDetailBlock, EventType } from 'im.v2.const';

import './chat-links.css';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const ChatLinks = {
	name: 'ChatLinks',
	directives: { hint },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			expanded: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		urlCounter(): string
		{
			const counter = this.$store.getters['sidebar/links/getCounter'](this.chatId);

			return this.getCounterString(counter);
		},
		isLinksAvailable(): boolean
		{
			return this.$store.state.sidebar.isLinksMigrated;
		},
		hintDirectiveContent(): Object
		{
			return {
				text: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_NOT_AVAILABLE'),
				popupOptions: {
					angle: true,
					targetContainer: document.body,
					offsetLeft: 141,
					offsetTop: -10,
					bindOptions: {
						position: 'top',
					},
				},
			};
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	methods:
	{
		getCounterString(counter: number): string
		{
			const MAX_COUNTER = 100;
			if (counter >= MAX_COUNTER)
			{
				return '99+';
			}

			return counter.toString();
		},
		onLinkClick()
		{
			if (!this.isLinksAvailable)
			{
				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.link,
				dialogId: this.dialogId,
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div 
			class="bx-im-sidebar-chat-links__container" 
			:class="[isLinksAvailable ? '' : '--links-not-active']"
			@click="onLinkClick"
		>
			<div 
				v-if="!isLinksAvailable" 
				class="bx-im-sidebar-chat-links__hint-not-active" 
				v-hint="hintDirectiveContent"
			></div>
			<div class="bx-im-sidebar-chat-links__title-container">
				<div class="bx-im-sidebar-chat-links__icon"></div>
				<div class="bx-im-sidebar-chat-links__title-text">
					{{ loc('IM_SIDEBAR_LINK_DETAIL_TITLE') }}
				</div>
			</div>
			<div class="bx-im-sidebar-chat-links__counter-container">
				<span class="bx-im-sidebar-chat-links__counter">{{urlCounter}}</span>
			</div>
		</div>
	`,
};
