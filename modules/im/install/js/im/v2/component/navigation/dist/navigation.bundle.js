/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3_directives_hint,ui_dialogs_messagebox,im_v2_lib_slider,im_v2_lib_call,im_v2_lib_desktopApi,im_v2_lib_phone,im_v2_lib_desktop,main_core,im_v2_component_elements,im_v2_lib_utils,im_v2_lib_logger,im_v2_lib_rest,ui_buttons,ui_feedback_form,ui_fontawesome4,im_v2_application_core,im_v2_const,im_v2_lib_market) {
	'use strict';

	// @vue/component
	const ButtonPanel = {
	  components: {
	    Button: im_v2_component_elements.Button
	  },
	  emits: ['openProfile', 'logout'],
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-user-settings-popup__button-panel">
			<div class="bx-im-user-settings-popup__button-panel_button">
				<Button
					:color="ButtonColor.PrimaryBorder"
					:size="ButtonSize.M"
					:isUppercase="false"
					:isRounded="true"
					:text="loc('IM_USER_SETTINGS_OPEN_PROFILE')"
					@click="$emit('openProfile')"
				/>
			</div>
<!--			<div class="bx-im-user-settings-popup__button-panel_button">-->
<!--				<Button-->
<!--					:color="ButtonColor.DangerBorder"-->
<!--					:size="ButtonSize.M"-->
<!--					:isUppercase="false"-->
<!--					:isRounded="true"-->
<!--					:text="loc('IM_USER_SETTINGS_LOGOUT')"-->
<!--					@click="$emit('logout')"-->
<!--				/>-->
<!--			</div>-->
		</div>
	`
	};

	class StatusService {
	  constructor() {
	    this.store = null;
	    this.restClient = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  changeStatus(status) {
	    if (!im_v2_const.UserStatus[status]) {
	      return false;
	    }
	    im_v2_lib_logger.Logger.warn(`StatusService: change current user status to ${status}`);
	    this.store.dispatch('users/setStatus', {
	      status
	    });
	    return this.restClient.callMethod(im_v2_const.RestMethod.imUserStatusSet, {
	      'STATUS': status
	    }).catch(error => {
	      console.error('StatusService: changeStatus error', error);
	    });
	  }
	}

	// @vue/component
	const UserStatusContent = {
	  name: 'UserStatusContent',
	  components: {
	    UserStatus: im_v2_component_elements.UserStatus
	  },
	  emits: ['close'],
	  computed: {
	    UserStatusSize: () => im_v2_component_elements.UserStatusSize,
	    UserStatusType: () => im_v2_const.UserStatus,
	    statusList() {
	      return [im_v2_const.UserStatus.online, im_v2_const.UserStatus.break, im_v2_const.UserStatus.away, im_v2_const.UserStatus.dnd];
	    }
	  },
	  methods: {
	    onStatusClick(statusName) {
	      this.getStatusService().changeStatus(statusName);
	      this.$emit('close');
	    },
	    getStatusService() {
	      if (!this.statusService) {
	        this.statusService = new StatusService();
	      }
	      return this.statusService;
	    },
	    getStatusText(status) {
	      return im_v2_lib_utils.Utils.user.getStatusText(status);
	    }
	  },
	  template: `
		<div class="bx-im-user-status-popup__scope bx-im-user-status-popup__container">
			<div
				v-for="status in statusList"
				:key="status"
				@click="onStatusClick(status)"
				class="bx-im-user-status-popup__item"
			>
				<UserStatus :status="status" :size="UserStatusSize.M" />
				<div class="bx-im-user-status-popup__text">{{ getStatusText(status) }}</div>
			</div>
		</div>
	`
	};

	const POPUP_ID = 'im-user-status-popup';

	// @vue/component
	const UserStatusPopup = {
	  name: 'UserStatusPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    UserStatusContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        width: 190,
	        bindElement: this.bindElement,
	        offsetTop: 4,
	        padding: 0
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UserStatusContent @close="$emit('close')" />
		</MessengerPopup>
	`
	};

	class VersionService {
	  constructor() {
	    this.store = null;
	    this.restClient = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  disableBeta() {
	    im_v2_lib_logger.Logger.warn('VersionService: disable v2');
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2BetaDisable).catch(error => {
	      im_v2_lib_logger.Logger.error('VersionService: disable v2 error', error);
	    });
	  }
	}

	// @vue/component
	const UserSettingsContent = {
	  name: 'UserSettingsContent',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    UserStatus: im_v2_component_elements.UserStatus,
	    ButtonPanel,
	    UserStatusPopup
	  },
	  emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	  data() {
	    return {
	      showStatusPopup: false,
	      isChangingVersion: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    UserStatusSize: () => im_v2_component_elements.UserStatusSize,
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    },
	    currentUser() {
	      return this.$store.getters['users/get'](this.currentUserId, true);
	    },
	    currentUserPosition() {
	      return this.$store.getters['users/getPosition'](this.currentUserId);
	    },
	    userStatus() {
	      const user = this.$store.getters['users/get'](this.currentUserId, true);
	      if (user) {
	        return user.status;
	      }
	      return im_v2_const.UserStatus.online;
	    },
	    currentHost() {
	      return location.hostname;
	    },
	    userStatusText() {
	      return im_v2_lib_utils.Utils.user.getStatusText(this.currentUser.status);
	    },
	    profileUri() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.currentUserId);
	    },
	    showOldChatButton() {
	      const settings = main_core.Extension.getSettings('im.v2.component.navigation');
	      return Boolean(settings.get('force_beta')) === false;
	    }
	  },
	  methods: {
	    onBackToOldChatClick() {
	      this.isChangingVersion = true;
	      this.getVersionService().disableBeta().then(() => {
	        if (im_v2_lib_desktop.DesktopManager.isDesktop()) {
	          window.location.reload();
	        } else {
	          window.location.replace('/online/');
	        }
	      }).catch(error => {
	        im_v2_lib_logger.Logger.error('Error while switching version', error);
	      });
	    },
	    onStatusClick() {
	      this.showStatusPopup = true;
	      this.$emit('disableAutoHide');
	    },
	    onStatusPopupClose() {
	      this.showStatusPopup = false;
	      this.$emit('enableAutoHide');
	    },
	    getVersionService() {
	      if (!this.versionService) {
	        this.versionService = new VersionService();
	      }
	      return this.versionService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-user-settings-popup__scope bx-im-user-settings-popup__container">
			<div class="bx-im-user-settings-popup__header">
				<div class="bx-im-user-settings-popup__header_left">
					<Avatar :dialogId="currentUserId" :size="AvatarSize.XL" />
				</div>
				<div class="bx-im-user-settings-popup__header_right">
					<div class="bx-im-user-settings-popup__domain">{{ currentHost }}</div>
					<div class="bx-im-user-settings-popup__user_name" :title="currentUser.name">{{ currentUser.name }}</div>
					<div class="bx-im-user-settings-popup__user_title" :title="currentUserPosition">{{ currentUserPosition }}</div>
					<a :href="profileUri" target="_blank" class="bx-im-user-settings-popup__user_link">
						<ButtonPanel @openProfile="$emit('closePopup')" />
					</a>
				</div>
			</div>
			<div class="bx-im-user-settings-popup__list">
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Status select -->
				<div @click="onStatusClick" class="bx-im-user-settings-popup__list-item --with-icon">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_status">
							<UserStatus :status="userStatus" :size="UserStatusSize.M" />
						</div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ userStatusText }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="status-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
			</div>
			<!-- Back to old chat -->
			<div v-if="showOldChatButton" class="bx-im-user-settings-popup__old-chat" :class="{'--loading': isChangingVersion}" @click="onBackToOldChatClick">
				<div class="bx-im-user-settings-popup__list-item_icon --arrow-left"></div>
				<div class="bx-im-user-settings-popup__old-chat_text">
					{{ loc('IM_USER_SETTINGS_OLD_CHAT') }}
				</div>
			</div>
		</div>
		<UserStatusPopup
			v-if="showStatusPopup"
			:bindElement="$refs['status-select'] || {}"
			@close="onStatusPopupClose"
		/>
	`
	};

	const POPUP_ID$1 = 'im-user-settings-popup';

	// @vue/component
	const UserSettingsPopup = {
	  name: 'UserSettingsPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    UserSettingsContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID$1,
	    config() {
	      return {
	        width: 313,
	        bindElement: this.bindElement,
	        offsetTop: 4,
	        padding: 0
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UserSettingsContent @closePopup="$emit('close')" @enableAutoHide="enableAutoHide" @disableAutoHide="disableAutoHide" />
		</MessengerPopup>
	`
	};

	// @vue/component
	const UserSettings = {
	  name: 'UserSettings',
	  components: {
	    UserSettingsPopup,
	    UserStatusPopup,
	    Avatar: im_v2_component_elements.Avatar
	  },
	  data() {
	    return {
	      showSettingsPopup: false,
	      showStatusPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    },
	    userStatus() {
	      const user = this.$store.getters['users/get'](this.currentUserId, true);
	      if (user) {
	        return user.status;
	      }
	      return im_v2_const.UserStatus.online;
	    }
	  },
	  methods: {
	    onAvatarClick() {
	      this.showSettingsPopup = true;
	    },
	    onStatusClick() {
	      this.showStatusPopup = true;
	    }
	  },
	  template: `
		<div class="bx-im-navigation__user">
			<div @click="onAvatarClick" class="bx-im-navigation__user_avatar" ref="avatar">
				<Avatar :dialogId="currentUserId.toString()" :size="AvatarSize.M" />
				<div @click.stop="onStatusClick" :class="'--' + userStatus" class="bx-im-navigation__user_status" ref="status"></div>
			</div>
			<UserStatusPopup
				v-if="showStatusPopup"
				:bindElement="$refs['status'] || {}"
				@close="showStatusPopup = false"
			/>
			<UserSettingsPopup
				v-if="showSettingsPopup"
				:bindElement="$refs['avatar'] || {}"
				@close="showSettingsPopup = false" 
			/>
		</div>
	`
	};

	// @vue/component
	const MarketApps = {
	  name: 'MarketApps',
	  emits: ['clickMarketItem'],
	  computed: {
	    marketMenuItems() {
	      const navigationApps = im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.navigation);
	      return navigationApps.map(item => {
	        return {
	          id: item.id,
	          text: item.title,
	          counter: 0,
	          active: true,
	          iconName: item.options.iconName ? item.options.iconName : '',
	          loadConfiguration: item.loadConfiguration
	        };
	      });
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    canShowMarket() {
	      return im_v2_application_core.Core.isCloud();
	    }
	  },
	  methods: {
	    onMarketClick() {
	      im_v2_lib_market.MarketManager.openMarketplace();
	    },
	    onMarketItemClick(item) {
	      this.$emit('clickMarketItem', {
	        layoutName: im_v2_const.Layout.market.name,
	        layoutEntityId: item.id
	      });
	    },
	    getMenuItemClasses(item) {
	      return {
	        '--selected': this.isItemSelected(item.id),
	        '--active': item.active
	      };
	    },
	    isItemSelected(itemId) {
	      return this.layout.name === im_v2_const.Layout.market.name && this.layout.entityId === itemId;
	    },
	    getIconClassNames(item) {
	      return item.iconName.toString();
	    }
	  },
	  template: `
		<div
			v-if="canShowMarket"
			@click="onMarketClick"
			class="bx-im-navigation__item_container"
		>
			<div class="bx-im-navigation__item --active">
				<div class="bx-im-navigation__item_icon --market"></div>
				<div class="bx-im-navigation__item_text" :title="$Bitrix.Loc.getMessage('IM_NAVIGATION_MARKET_TITLE')">
					{{ $Bitrix.Loc.getMessage('IM_NAVIGATION_MARKET_TITLE') }}
				</div>
			</div>
		</div>
		<div
			v-for="item in marketMenuItems"
			@click="onMarketItemClick(item)"
			class="bx-im-navigation__item_container"
		>
			<div :class="getMenuItemClasses(item)" class="bx-im-navigation__item">
				<div class="bx-im-navigation__market-item_icon-container">
					<i 
						class="bx-im-navigation__market-item_icon fa" 
						:class="getIconClassNames(item)" 
						aria-hidden="true"
					></i>
				</div>
				<div class="bx-im-navigation__item_text" :title="item.text">{{item.text}}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MessengerNavigation = {
	  name: 'MessengerNavigation',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    UserSettings,
	    MarketApps
	  },
	  props: {
	    currentLayoutName: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['navigationClick'],
	  data() {
	    return {
	      needTopShadow: false,
	      needBottomShadow: false
	    };
	  },
	  computed: {
	    menuItems() {
	      const menuItems = [{
	        id: im_v2_const.Layout.chat.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_CHATS'),
	        counter: this.formatCounter(this.$store.getters['recent/getTotalChatCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.notification.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_NOTIFICATIONS'),
	        counter: this.formatCounter(this.$store.getters['notifications/getCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.openlines.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
	        counter: this.formatCounter(this.$store.getters['recent/getTotalLinesCounter']),
	        active: true
	      }];
	      if (im_v2_lib_phone.PhoneManager.getInstance().canCall()) {
	        menuItems.push({
	          id: im_v2_const.Layout.call.name,
	          text: this.prepareNavigationText('IM_NAVIGATION_CALLS'),
	          active: true,
	          clickHandler: this.onCallClick
	        });
	      }
	      menuItems.push({
	        id: im_v2_const.Layout.settings.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_SETTINGS'),
	        active: true
	      });
	      if (this.isTimeManagerActive()) {
	        menuItems.push({
	          id: 'timemanager',
	          text: this.prepareNavigationText('IM_NAVIGATION_TIMEMANAGER'),
	          counter: 0,
	          active: true,
	          clickHandler: this.onTimeManagerClick
	        });
	      }
	      return menuItems;
	    },
	    showCloseIcon() {
	      return !im_v2_lib_desktopApi.DesktopApi.isChatTab();
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Navigation created');
	  },
	  mounted() {
	    const container = this.$refs.navigation;
	    this.needBottomShadow = container.scrollTop + container.clientHeight !== container.scrollHeight;
	  },
	  methods: {
	    onMenuItemClick(item, event) {
	      if (!item.active) {
	        return;
	      }
	      if (main_core.Type.isFunction(item.clickHandler)) {
	        item.clickHandler(event.target);
	        return;
	      }
	      this.$emit('navigationClick', {
	        layoutName: item.id,
	        layoutEntityId: ''
	      });
	    },
	    onMarketMenuItemClick({
	      layoutName,
	      layoutEntityId
	    }) {
	      this.$emit('navigationClick', {
	        layoutName,
	        layoutEntityId
	      });
	    },
	    closeSlider() {
	      const hasCall = im_v2_lib_call.CallManager.getInstance().hasCurrentCall();
	      if (hasCall) {
	        this.showExitConfirm();
	        return;
	      }
	      im_v2_lib_slider.MessengerSlider.getInstance().getCurrent().close();
	    },
	    getMenuItemClasses(item) {
	      return {
	        '--selected': item.id === this.currentLayoutName,
	        '--with-counter': item.counter && item.id !== this.currentLayoutName,
	        '--active': item.active
	      };
	    },
	    formatCounter(counter) {
	      if (counter === 0) {
	        return '';
	      }
	      return counter > 99 ? '99+' : String(counter);
	    },
	    getHintContent(item) {
	      if (item.active) {
	        return null;
	      }
	      return {
	        text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
	        popupOptions: {
	          angle: {
	            position: 'left'
	          },
	          targetContainer: document.body,
	          offsetLeft: 80,
	          offsetTop: -54
	        }
	      };
	    },
	    prepareNavigationText(phraseCode) {
	      return this.loc(phraseCode, {
	        '#BR#': '</br>'
	      });
	    },
	    showExitConfirm() {
	      ui_dialogs_messagebox.MessageBox.show({
	        message: this.loc('IM_NAVIGATION_ACTIVE_CALL_CONFIRM'),
	        modal: true,
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	        onOk: messageBox => {
	          im_v2_lib_call.CallManager.getInstance().leaveCurrentCall();
	          im_v2_lib_slider.MessengerSlider.getInstance().getCurrent().close();
	          messageBox.close();
	        },
	        onCancel: messageBox => {
	          messageBox.close();
	        }
	      });
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onScroll(event) {
	      const scrollPosition = Math.round(event.target.scrollTop + event.target.clientHeight);
	      this.needBottomShadow = scrollPosition !== event.target.scrollHeight;
	      if (event.target.scrollTop === 0) {
	        this.needTopShadow = false;
	        return;
	      }
	      this.needTopShadow = true;
	    },
	    onClickScrollDown() {
	      this.$refs.navigation.scrollTo({
	        top: this.$refs.navigation.scrollHeight,
	        behavior: 'smooth'
	      });
	    },
	    onClickScrollUp() {
	      this.$refs.navigation.scrollTo({
	        top: 0,
	        behavior: 'smooth'
	      });
	    },
	    onCallClick(clickTarget) {
	      const MENU_ITEM_CLASS = 'bx-im-navigation__item';
	      const KEYPAD_OFFSET_TOP = -30;
	      const KEYPAD_OFFSET_LEFT = 64;
	      im_v2_lib_phone.PhoneManager.getInstance().openKeyPad({
	        bindElement: clickTarget.closest(`.${MENU_ITEM_CLASS}`),
	        offsetTop: KEYPAD_OFFSET_TOP,
	        offsetLeft: KEYPAD_OFFSET_LEFT
	      });
	    },
	    isTimeManagerActive() {
	      var _BX$Timeman, _BX$Timeman$Monitor;
	      return Boolean((_BX$Timeman = BX.Timeman) == null ? void 0 : (_BX$Timeman$Monitor = _BX$Timeman.Monitor) == null ? void 0 : _BX$Timeman$Monitor.isEnabled());
	    },
	    async onTimeManagerClick() {
	      var _BX$Timeman2, _BX$Timeman2$Monitor;
	      (_BX$Timeman2 = BX.Timeman) == null ? void 0 : (_BX$Timeman2$Monitor = _BX$Timeman2.Monitor) == null ? void 0 : _BX$Timeman2$Monitor.openReport();
	    }
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
				<div
					v-for="item in menuItems"
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

	exports.MessengerNavigation = MessengerNavigation;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Vue3.Directives,BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=navigation.bundle.js.map
