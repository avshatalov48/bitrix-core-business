import { Type } from 'main.core';
import { hint } from 'ui.vue3.directives.hint';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';
import { FeaturePromoter } from 'ui.info-helper';

import { Logger } from 'im.v2.lib.logger';
import { MessengerSlider } from 'im.v2.lib.slider';
import { CallManager } from 'im.v2.lib.call';
import { ActionByUserType, Layout, SliderCode } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { PhoneManager } from 'im.v2.lib.phone';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Analytics } from 'im.v2.lib.analytics';
import { PermissionManager } from 'im.v2.lib.permission';
import { Utils } from 'im.v2.lib.utils';

import { UserSettings } from './components/user-settings';
import { MarketApps } from './components/market-apps';

import './css/navigation.css';

import type { JsonObject } from 'main.core';

type MenuItem = {
	id: string,
	text: string,
	active?: boolean,
	counter?: number,
	clickHandler?: (clickTarget: HTMLElement) => void,
	showCondition?: () => boolean,
};

const LayoutToAction = Object.freeze({
	[Layout.market.name]: ActionByUserType.getMarket,
	[Layout.openlines.name]: ActionByUserType.getOpenlines,
	[Layout.channel.name]: ActionByUserType.getChannels,
});

// @vue/component
export const MessengerNavigation = {
	name: 'MessengerNavigation',
	directives: { hint },
	components: { UserSettings, MarketApps },
	props: {
		currentLayoutName: {
			type: String,
			required: true,
		},
	},
	emits: ['navigationClick'],
	data(): JsonObject
	{
		return {
			needTopShadow: false,
			needBottomShadow: false,
		};
	},
	computed:
	{
		menuItems(): MenuItem[]
		{
			return [
				{
					id: Layout.chat.name,
					text: this.prepareNavigationText('IM_NAVIGATION_CHATS'),
					counter: this.formatCounter(this.$store.getters['counters/getTotalChatCounter']),
					active: true,
				},
				{
					id: Layout.copilot.name,
					text: this.prepareNavigationText('IM_NAVIGATION_COPILOT'),
					counter: this.formatCounter(this.$store.getters['counters/getTotalCopilotCounter']),
					clickHandler: this.onCopilotClick,
					showCondition: () => FeatureManager.isFeatureAvailable(Feature.copilotAvailable),
					active: true,
				},
				{
					id: Layout.collab.name,
					text: this.prepareNavigationText('IM_NAVIGATION_COLLAB'),
					counter: this.formatCounter(this.$store.getters['counters/getTotalCollabCounter']),
					showCondition: () => FeatureManager.isFeatureAvailable(Feature.collabAvailable),
					active: true,
				},
				{
					id: Layout.channel.name,
					text: this.prepareNavigationText('IM_NAVIGATION_CHANNELS'),
					active: true,
				},
				{
					id: Layout.openlines.name,
					text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
					counter: this.formatCounter(this.$store.getters['counters/getTotalLinesCounter']),
					showCondition: () => {
						return !this.isOptionOpenLinesV2Activated();
					},
					active: true,
				},
				{
					id: Layout.openlinesV2.name,
					text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
					counter: this.formatCounter(this.$store.getters['counters/getTotalLinesCounter']),
					showCondition: this.isOptionOpenLinesV2Activated,
					active: true,
				},
				{
					id: Layout.notification.name,
					text: this.prepareNavigationText('IM_NAVIGATION_NOTIFICATIONS'),
					counter: this.formatCounter(this.$store.getters['notifications/getCounter']),
					active: true,
				},
				{
					id: Layout.call.name,
					text: this.prepareNavigationText('IM_NAVIGATION_CALLS_V2'),
					clickHandler: this.onCallClick,
					showCondition: PhoneManager.getInstance().canCall.bind(PhoneManager.getInstance()),
					active: true,
				},
				{
					id: 'timemanager',
					text: this.prepareNavigationText('IM_NAVIGATION_TIMEMANAGER'),
					clickHandler: this.onTimeManagerClick,
					showCondition: this.isTimeManagerActive,
					active: true,
				},
				{
					id: 'main-page',
					text: this.prepareNavigationText('IM_NAVIGATION_MAIN_PAGE'),
					clickHandler: this.onMainPageClick,
					showCondition: this.isMainPageActive,
					active: true,
				},
				{
					id: 'market',
				},
				{
					id: Layout.settings.name,
					text: this.prepareNavigationText('IM_NAVIGATION_SETTINGS'),
					active: true,
				},
			];
		},
		showCloseIcon(): boolean
		{
			return !DesktopApi.isChatTab();
		},
	},
	created()
	{
		Logger.warn('Navigation created');
	},
	mounted()
	{
		const container = this.$refs.navigation;
		this.needBottomShadow = container.scrollTop + container.clientHeight !== container.scrollHeight;
	},
	methods:
	{
		onMenuItemClick(item: MenuItem, event: PointerEvent)
		{
			if (!item.active)
			{
				return;
			}

			if (Type.isFunction(item.clickHandler))
			{
				item.clickHandler(event.target);

				return;
			}

			this.sendClickEvent({ layoutName: item.id });
		},
		sendClickEvent({ layoutName, layoutEntityId = '' })
		{
			this.$emit('navigationClick', { layoutName, layoutEntityId });
		},
		closeSlider()
		{
			const hasCall = CallManager.getInstance().hasCurrentCall();
			if (hasCall)
			{
				this.showExitConfirm();

				return;
			}

			MessengerSlider.getInstance().getCurrent().close();
		},
		getMenuItemClasses(item: MenuItem): Object<string, boolean>
		{
			return {
				'--selected': item.id === this.currentLayoutName,
				'--with-counter': item.counter && item.id !== this.currentLayoutName,
				'--active': item.active,
			};
		},
		formatCounter(counter: number): string
		{
			if (counter === 0)
			{
				return '';
			}

			return counter > 99 ? '99+' : String(counter);
		},
		getHintContent(item: MenuItem): ?{text: string, popupOptions: Object<string, any>}
		{
			if (item.active)
			{
				return null;
			}

			return {
				text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
				popupOptions: {
					angle: { position: 'left' },
					targetContainer: document.body,
					offsetLeft: 80,
					offsetTop: -54,
				},
			};
		},
		prepareNavigationText(phraseCode: string): string
		{
			return this.loc(phraseCode, {
				'#BR#': '</br>',
			});
		},
		showExitConfirm()
		{
			MessageBox.show({
				message: this.loc('IM_NAVIGATION_ACTIVE_CALL_CONFIRM'),
				modal: true,
				buttons: MessageBoxButtons.OK_CANCEL,
				onOk: (messageBox: MessageBox) => {
					CallManager.getInstance().leaveCurrentCall();
					MessengerSlider.getInstance().getCurrent().close();
					messageBox.close();
				},
				onCancel: (messageBox: MessageBox) => {
					messageBox.close();
				},
			});
		},
		needToShowMenuItem(item: MenuItem): boolean
		{
			if (!this.hasLayoutAccess(item))
			{
				return false;
			}

			if (!Type.isFunction(item.showCondition))
			{
				return true;
			}

			return item.showCondition() === true;
		},
		hasLayoutAccess(item: MenuItem): boolean
		{
			const action = LayoutToAction[item.id];

			return PermissionManager.getInstance().canPerformActionByUserType(action);
		},
		onScroll(event: Event)
		{
			const scrollPosition = Math.round(event.target.scrollTop + event.target.clientHeight);
			this.needBottomShadow = scrollPosition !== event.target.scrollHeight;

			if (event.target.scrollTop === 0)
			{
				this.needTopShadow = false;

				return;
			}

			this.needTopShadow = true;
		},
		onClickScrollDown()
		{
			this.$refs.navigation.scrollTo({
				top: this.$refs.navigation.scrollHeight,
				behavior: 'smooth',
			});
		},
		onClickScrollUp()
		{
			this.$refs.navigation.scrollTo({
				top: 0,
				behavior: 'smooth',
			});
		},
		onCallClick(clickTarget: HTMLElement)
		{
			const MENU_ITEM_CLASS = 'bx-im-navigation__item';
			const KEYPAD_OFFSET_TOP = -30;
			const KEYPAD_OFFSET_LEFT = 64;

			PhoneManager.getInstance().openKeyPad({
				bindElement: clickTarget.closest(`.${MENU_ITEM_CLASS}`),
				offsetTop: KEYPAD_OFFSET_TOP,
				offsetLeft: KEYPAD_OFFSET_LEFT,
			});
		},
		isTimeManagerActive(): boolean
		{
			return Boolean(BX.Timeman?.Monitor?.isEnabled());
		},
		async onTimeManagerClick()
		{
			BX.Timeman?.Monitor?.openReport();
		},
		onCopilotClick()
		{
			if (!FeatureManager.isFeatureAvailable(Feature.copilotActive))
			{
				const promoter = new FeaturePromoter({ code: SliderCode.copilotDisabled });
				promoter.show();
				Analytics.getInstance().copilot.onOpenTab({ isAvailable: false });

				return;
			}

			this.sendClickEvent({ layoutName: Layout.copilot.name });
		},
		isOptionOpenLinesV2Activated(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.openLinesV2);
		},
		onMainPageClick()
		{
			Utils.browser.openLink('/');
		},
		isMainPageActive(): boolean
		{
			return DesktopApi.isChatWindow();
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-navigation__scope bx-im-navigation__container">
			<div v-if="needTopShadow" class="bx-im-navigation__shadow --top">
				<div class="bx-im-navigation__scroll-button" @click="onClickScrollUp"></div>
			</div>
			<div class="bx-im-navigation__top" @scroll="onScroll" ref="navigation">
				<template v-if="showCloseIcon">
					<!-- Close -->
					<div class="bx-im-navigation__close_container" @click="closeSlider">
						<div class="bx-im-navigation__close"></div>
					</div>
					<!-- Separator -->
					<div class="bx-im-navigation__separator_container">
						<div class="bx-im-navigation__close_separator"></div>
					</div>
				</template>
				<!-- Menu items -->
				<template v-for="item in menuItems">
					<MarketApps v-if="needToShowMenuItem(item) && item.id === 'market'" @clickMarketItem="sendClickEvent"/>
					<div
						v-else-if="needToShowMenuItem(item)"
						:key="item.id"
						v-hint="getHintContent(item)"
						@click="onMenuItemClick(item, $event)"
						class="bx-im-navigation__item_container"
					>
						<div :class="getMenuItemClasses(item)" class="bx-im-navigation__item">
							<div :class="'--' + item.id" class="bx-im-navigation__item_icon"></div>
							<div class="bx-im-navigation__item_text" :title="item.text" v-html="item.text"></div>
							<div v-if="item.active && item.counter" class="bx-im-navigation__item_counter">
								<div class="bx-im-navigation__item_counter-text">
									{{ item.counter }}
								</div>
							</div>
						</div>
					</div>
				</template>
			</div>
			<div v-if="needBottomShadow" class="bx-im-navigation__shadow --bottom">
				<div class="bx-im-navigation__scroll-button --bottom" @click="onClickScrollDown"></div>
			</div>
			<!-- Avatar -->
			<div class="bx-im-navigation__user_container">
				<UserSettings />
			</div>
		</div>
	`,
};
