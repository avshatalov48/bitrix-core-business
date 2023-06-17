import {hint} from 'ui.vue3.directives.hint';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

import {Logger} from 'im.v2.lib.logger';
import {MessengerSlider} from 'im.v2.lib.slider';
import {CallManager} from 'im.v2.lib.call';
import {Layout} from 'im.v2.const';

import {UserSettings} from './components/user-settings';
import {MarketApps} from './components/market-apps';

import './css/navigation.css';

type MenuItem = {
	id: string,
	text: string,
	counter: number,
	active: boolean
};

// @vue/component
export const MessengerNavigation = {
	name: 'MessengerNavigation',
	directives: {hint},
	components: {UserSettings, MarketApps},
	props: {
		currentLayoutName: {
			type: String,
			required: true
		}
	},
	emits: ['navigationClick'],
	data()
	{
		return {
			needTopShadow: false,
			needBottomShadow: false
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
					counter: this.formatCounter(this.$store.getters['recent/getTotalCounter']),
					active: true
				},
				{
					id: Layout.notification.name,
					text: this.prepareNavigationText('IM_NAVIGATION_NOTIFICATIONS'),
					counter: this.formatCounter(this.$store.getters['notifications/getCounter']),
					active: true
				},
				{
					id: Layout.openline.name,
					text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
					counter: 0,
					active: false
				},
				{
					id: Layout.call.name,
					text: this.prepareNavigationText('IM_NAVIGATION_CALLS'),
					counter: 0,
					active: false
				},
				{
					id: 'settings',
					text: this.prepareNavigationText('IM_NAVIGATION_SETTINGS'),
					counter: 0,
					active: false
				},
			];
		},
	},
	created()
	{
		Logger.warn('Navigation created');
	},
	mounted()
	{
		const container = this.$refs['navigation'];
		this.needBottomShadow = container.scrollTop + container.clientHeight !== container.scrollHeight;
	},
	methods:
	{
		onMenuItemClick(item: MenuItem)
		{
			if (!item.active)
			{
				return;
			}

			this.$emit('navigationClick', {layoutName: item.id, layoutEntityId: ''});
		},
		onMarketMenuItemClick({layoutName, layoutEntityId})
		{
			this.$emit('navigationClick', {
				layoutName: layoutName,
				layoutEntityId: layoutEntityId
			});
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
		getMenuItemClasses(item: MenuItem)
		{
			return {
				'--selected': item.id === this.currentLayoutName,
				'--with-counter': item.counter && item.id !== this.currentLayoutName,
				'--active': item.active
			};
		},
		formatCounter(counter: number): string
		{
			if (counter === 0)
			{
				return '';
			}

			return counter > 99 ? '99+' : `${counter}`;
		},
		getHintContent(item: MenuItem)
		{
			if (item.active)
			{
				return null;
			}

			return {
				text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
				popupOptions: {
					angle: {position: 'left'},
					targetContainer: document.body,
					offsetLeft: 80,
					offsetTop: -54
				}
			};
		},
		prepareNavigationText(phraseCode: string): string
		{
			return this.loc(phraseCode, {
				'#BR#': '</br>'
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
				}
			});
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
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
			this.$refs['navigation'].scrollTo({
				top: this.$refs['navigation'].scrollHeight,
				behavior: 'smooth',
			});
		},
		onClickScrollUp()
		{
			this.$refs['navigation'].scrollTo({
				top: 0,
				behavior: 'smooth',
			});
		},
	},
	template: `
		<div class="bx-im-navigation__scope bx-im-navigation__container">
			<div v-if="needTopShadow" class="bx-im-navigation__shadow --top">
				<div class="bx-im-navigation__scroll-button" @click="onClickScrollUp"></div>
			</div>
			<div class="bx-im-navigation__top" @scroll="onScroll" ref="navigation">
				<!-- Close -->
				<div class="bx-im-navigation__close_container" @click="closeSlider">
					<div class="bx-im-navigation__close"></div>
				</div>
				<!-- Separator -->
				<div class="bx-im-navigation__separator_container">
					<div class="bx-im-navigation__close_separator"></div>
				</div>
				<!-- Menu items -->
				<div
					v-for="item in menuItems"
					v-hint="getHintContent(item)"
					@click="onMenuItemClick(item)"
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
				<MarketApps @clickMarketItem="onMarketMenuItemClick"/>
			</div>
			<div v-if="needBottomShadow" class="bx-im-navigation__shadow --bottom">
				<div class="bx-im-navigation__scroll-button --bottom" @click="onClickScrollDown"></div>
			</div>
			<!-- Avatar -->
			<div class="bx-im-navigation__user_container">
				<UserSettings />
			</div>
		</div>
	`
};