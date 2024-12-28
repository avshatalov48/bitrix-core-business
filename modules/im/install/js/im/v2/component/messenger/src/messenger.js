import 'ui.design-tokens';
import 'ui.fonts.opensans';
import 'im.v2.css.tokens';
import 'im.v2.css.icons';
import 'im.v2.css.classes';

import { MessengerNavigation } from 'im.v2.component.navigation';
import { RecentListContainer } from 'im.v2.component.list.container.recent';
import { OpenlineListContainer } from 'im.v2.component.list.container.openline';
import { ChannelListContainer } from 'im.v2.component.list.container.channel';
import { CollabListContainer } from 'im.v2.component.list.container.collab';
import { ChatContent } from 'im.v2.component.content.chat';
import { CreateChatContent, UpdateChatContent } from 'im.v2.component.content.chat-forms.forms';
import { OpenlinesContent } from 'im.v2.component.content.openlines';
import { OpenlinesV2Content } from 'im.v2.component.content.openlinesV2';
import { NotificationContent } from 'im.v2.component.content.notification';
import { MarketContent } from 'im.v2.component.content.market';
import { SettingsContent } from 'im.v2.component.content.settings';
import { CopilotListContainer } from 'im.v2.component.list.container.copilot';
import { CopilotContent } from 'im.v2.component.content.copilot';
import { Analytics } from 'im.v2.lib.analytics';

import { Logger } from 'im.v2.lib.logger';
import { InitManager } from 'im.v2.lib.init';
import { Layout } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { ThemeManager } from 'im.v2.lib.theme';
import { DesktopManager } from 'im.v2.lib.desktop';
import { LayoutManager } from 'im.v2.lib.layout';

import './css/messenger.css';

import type { JsonObject } from 'main.core';
import type { ImModelLayout } from 'im.v2.model';

// @vue/component
export const Messenger = {
	name: 'MessengerRoot',
	components: {
		MessengerNavigation,
		RecentListContainer,
		ChannelListContainer,
		CollabListContainer,
		OpenlineListContainer,
		ChatContent,
		CreateChatContent,
		UpdateChatContent,
		OpenlinesContent,
		NotificationContent,
		OpenlinesV2Content,
		MarketContent,
		SettingsContent,
		CopilotListContainer,
		CopilotContent,
	},
	data(): JsonObject
	{
		return {
			openlinesContentOpened: false,
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		layoutName(): string
		{
			return this.layout?.name;
		},
		currentLayout(): {name: string, list: string, content: string}
		{
			return Layout[this.layout.name];
		},
		entityId(): string
		{
			return this.layout.entityId;
		},
		isOpenline(): boolean
		{
			return this.layout.name === Layout.openlines.name;
		},
		hasList(): boolean
		{
			return Boolean(this.currentLayout.list);
		},
		containerClasses(): string[]
		{
			return {
				'--dark-theme': ThemeManager.isDarkTheme(),
				'--light-theme': ThemeManager.isLightTheme(),
				'--desktop': DesktopManager.isDesktop(),
			};
		},
		callContainerClass(): string[]
		{
			return [CallManager.viewContainerClass];
		},
	},
	watch:
	{
		layoutName:
		{
			handler(newLayoutName)
			{
				if (newLayoutName !== Layout.openlines.name)
				{
					return;
				}

				this.openlinesContentOpened = true;
			},
			immediate: true,
		},
	},
	created()
	{
		InitManager.start();
		LayoutManager.init();
		Logger.warn('MessengerRoot created');

		this.getLayoutManager().restoreLastLayout();
		this.sendAnalytics();
	},
	beforeUnmount()
	{
		this.getLayoutManager().destroy();
	},
	methods:
	{
		onNavigationClick({ layoutName, layoutEntityId }: {layoutName: string, layoutEntityId: string | number})
		{
			let entityId = layoutEntityId;

			const lastOpenedElement = this.getLayoutManager().getLastOpenedElement(layoutName);
			if (!entityId && lastOpenedElement)
			{
				entityId = lastOpenedElement;
			}

			this.getLayoutManager().setLayout({ name: layoutName, entityId });
		},
		onEntitySelect({ layoutName, entityId })
		{
			this.getLayoutManager().setLayout({ name: layoutName, entityId });
		},
		getLayoutManager(): LayoutManager
		{
			return LayoutManager.getInstance();
		},
		sendAnalytics()
		{
			Analytics.getInstance().onOpenMessenger();
		},
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
					<div class="bx-im-messenger__content_container" :class="{'--with-list': hasList}">
						<div v-if="openlinesContentOpened" class="bx-im-messenger__openlines_container" :class="{'--hidden': !isOpenline}">
							<OpenlinesContent v-show="isOpenline" :entityId="entityId" />
						</div>
						<component v-if="!isOpenline" :is="currentLayout.content" :entityId="entityId" />
					</div>
				</div>
			</div>
		</div>
		<div :class="callContainerClass"></div>
	`,
};
