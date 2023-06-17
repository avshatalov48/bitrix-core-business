import {EventEmitter, BaseEvent} from 'main.core.events';

import {MessengerNavigation} from 'im.v2.component.navigation';
import {RecentListContainer} from 'im.v2.component.list.container.recent';
import {OpenlineListContainer} from 'im.v2.component.list.container.openline';
import {ChatContent} from 'im.v2.component.content.chat';
import {CreateChatContent} from 'im.v2.component.content.create-chat';
import {OpenlineContent} from 'im.v2.component.content.openline';
import {NotificationContent} from 'im.v2.component.content.notification';
import {MarketContent} from 'im.v2.component.content.market';

import {Logger} from 'im.v2.lib.logger';
import {InitManager} from 'im.v2.lib.init';
import {EventType, Layout} from 'im.v2.const';
import {CallManager} from 'im.v2.lib.call';
import {ThemeManager} from 'im.v2.lib.theme';

import 'ui.fonts.opensans';
import './css/messenger.css';
import './css/tokens.css';
import './css/icons.css';

import type {ImModelDialog, ImModelLayout} from 'im.v2.model';

// @vue/component
export const Messenger = {
	name: 'MessengerRoot',
	components: {MessengerNavigation, RecentListContainer, OpenlineListContainer, ChatContent, CreateChatContent, OpenlineContent, NotificationContent, MarketContent},
	data()
	{
		return {
			contextMessageId: 0
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		currentLayout(): {name: string, list: string, content: string}
		{
			return Layout[this.layout.name];
		},
		entityId(): string
		{
			return this.layout.entityId;
		},
		currentDialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.entityId, true);
		},
		isChat(): boolean
		{
			return this.layout.name === Layout.chat.name;
		},
		isNotification(): boolean
		{
			return this.layout.name === Layout.notification.name;
		},
		containerClasses(): string[]
		{
			return {
				'--dark-theme': ThemeManager.isDarkTheme(),
				'--light-theme': ThemeManager.isLightTheme()
			};
		},
		callContainerClass(): string[]
		{
			return [CallManager.viewContainerClass];
		}
	},
	created()
	{
		InitManager.start();
		Logger.warn('MessengerRoot created');
	},
	mounted()
	{
		EventEmitter.subscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	},
	methods:
	{
		onNavigationClick({layoutName, layoutEntityId}: {layoutName: string, layoutEntityId: string | number})
		{
			let entityId = '';
			const isChatNext = layoutName === Layout.chat.name;
			const isMarketNext = layoutName === Layout.market.name;

			if (isChatNext)
			{
				entityId = this.previouslySelectedChat;
			}
			else if (isMarketNext)
			{
				entityId = layoutEntityId;
			}

			this.$store.dispatch('application/setLayout', {layoutName, entityId});
		},
		onEntitySelect({layoutName, entityId})
		{
			this.saveLastOpenedChat(entityId);
			this.$store.dispatch('application/setLayout', {layoutName, entityId});
		},
		onGoToMessageContext(event: BaseEvent)
		{
			const {dialogId, messageId} = event.getData();
			if (this.currentDialog.dialogId === dialogId)
			{
				return;
			}

			this.$store.dispatch('application/setLayout', {
				layoutName: Layout.chat.name,
				entityId: dialogId,
				contextId: messageId
			});
		},
		saveLastOpenedChat(dialogId: string)
		{
			this.previouslySelectedChat = dialogId || '';
		}
	},
	template: `
		<div class="bx-im-messenger__scope bx-im-messenger__container" :class="containerClasses">
			<div class="bx-im-messenger__navigation_container">
				<MessengerNavigation :currentLayoutName="currentLayout.name" @navigationClick="onNavigationClick" />
			</div>
			<div class="bx-im-messenger__layout_container">
				<div class="bx-im-messenger__layout_content">
					<div v-if="currentLayout.list" class="bx-im-messenger__list_container">
						<KeepAlive>
							<component :is="currentLayout.list" @selectEntity="onEntitySelect" />
						</KeepAlive>
					</div>
					<div class="bx-im-messenger__content_container">
						<component :is="currentLayout.content" :entityId="entityId" :contextMessageId="contextMessageId" />
					</div>
				</div>
			</div>
		</div>
		<div :class="callContainerClass"></div>
	`
};