import { MessengerNavigation } from 'im.v2.component.navigation';
import { RecentListContainer } from 'im.v2.component.list.container.recent';
import { OpenlineListContainer } from 'im.v2.component.list.container.openline';
import { ChatContent } from 'im.v2.component.content.chat';
import { CreateChatContent } from 'im.v2.component.content.create-chat';
import { OpenlinesContent } from 'im.v2.component.content.openlines';
import { NotificationContent } from 'im.v2.component.content.notification';
import { MarketContent } from 'im.v2.component.content.market';
import { SettingsContent } from 'im.v2.component.content.settings';
import { CopilotListContainer } from 'im.v2.component.list.container.copilot';
import { CopilotContent } from 'im.v2.component.content.copilot';

import { Logger } from 'im.v2.lib.logger';
import { InitManager } from 'im.v2.lib.init';
import { Layout } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { ThemeManager } from 'im.v2.lib.theme';
import { DesktopManager } from 'im.v2.lib.desktop';
import { LayoutManager } from 'im.v2.lib.layout';

import 'ui.fonts.opensans';
import './css/messenger.css';
import './css/tokens.css';
import './css/icons.css';

import type { JsonObject } from 'main.core';
import type { ImModelLayout } from 'im.v2.model';

// @vue/component
export const Messenger = {
	name: 'MessengerRoot',
	components: {
		MessengerNavigation,
		RecentListContainer,
		OpenlineListContainer,
		ChatContent,
		CreateChatContent,
		OpenlinesContent,
		NotificationContent,
		MarketContent,
		SettingsContent,
		CopilotListContainer,
		CopilotContent,
	},
	data(): JsonObject
	{
		return {
			contextMessageId: 0,
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
			if (lastOpenedElement)
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
						<div v-if="openlinesContentOpened" class="bx-im-messenger__openlines_container" :class="{'--hidden': !isOpenline}">
							<OpenlinesContent v-show="isOpenline" :entityId="entityId" :contextMessageId="contextMessageId" />
						</div>
						<component v-if="!isOpenline" :is="currentLayout.content" :entityId="entityId" :contextMessageId="contextMessageId" />
					</div>
				</div>
			</div>
		</div>
		<div :class="callContainerClass"></div>
	`,
};
