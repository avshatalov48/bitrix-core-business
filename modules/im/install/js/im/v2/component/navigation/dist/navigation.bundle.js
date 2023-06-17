this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3_directives_hint,ui_dialogs_messagebox,im_v2_lib_slider,im_v2_lib_call,im_v2_lib_utils,im_v2_component_elements,im_v2_lib_theme,im_v2_lib_rest,im_v2_lib_logger,ui_buttons,ui_feedback_form,ui_fontawesome4,im_v2_application_core,im_v2_const,im_v2_lib_market) {
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

	const BackgroundService = {
	  changeBackground(backgroundId) {
	    im_v2_lib_logger.Logger.warn('Navigation: BackgroundService: changeBackground', backgroundId);
	    const preparedBackgroundId = Number.parseInt(backgroundId, 10);
	    im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      [im_v2_const.Settings.dialog.background]: preparedBackgroundId
	    });
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsGeneralUpdate, {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        name: im_v2_const.Settings.dialog.background,
	        value: preparedBackgroundId
	      }
	    }).catch(error => {
	      console.error('Navigation: BackgroundService: error changing background', error);
	    });
	  }
	};

	// @vue/component
	const BackgroundContent = {
	  name: 'BackgroundContent',
	  emits: ['close'],
	  computed: {
	    currentBackgroundId() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.dialog.background).toString();
	    },
	    backgroundIdList() {
	      return Object.keys(im_v2_lib_theme.ThemeColorScheme);
	    }
	  },
	  methods: {
	    getBackgroundStyleById(backgroundId) {
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(backgroundId);
	    },
	    onBackgroundClick(backgroundId) {
	      BackgroundService.changeBackground(backgroundId);
	    }
	  },
	  template: `
		<div class="bx-im-background-select-popup__container">
			<!-- <div class="bx-im-background-select-popup__title">Chat background</div> -->
			<div class="bx-im-background-select-popup__list">
				<div
					v-for="id in backgroundIdList"
					:key="id"
					:style="getBackgroundStyleById(id)"
					class="bx-im-background-select-popup__item"
					:class="{'--active': id === currentBackgroundId}"
					@click="onBackgroundClick(id)"
				></div>
			</div>
			<!-- <div @click="$emit('close')" class="bx-im-background-select-popup__close bx-im-messenger__cross-icon"></div> -->
		</div>
	`
	};

	const POPUP_ID$1 = 'im-background-select-popup';

	// @vue/component
	const BackgroundPopup = {
	  name: 'BackgroundPopup',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup,
	    BackgroundContent
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
			<BackgroundContent @close="$emit('close')" />
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
	  disableV2Version() {
	    im_v2_lib_logger.Logger.warn('VersionService: disable v2');
	    return this.restClient.callMethod(im_v2_const.RestMethod.imVersionV2Disable).catch(error => {
	      console.error('VersionService: disable v2 error', error);
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
	    UserStatusPopup,
	    BackgroundPopup
	  },
	  emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	  data() {
	    return {
	      showStatusPopup: false,
	      showBackgroundPopup: false,
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
	    currentUserStatus() {
	      const status = this.$store.getters['users/getStatus'](this.currentUserId);
	      if (status) {
	        return status;
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
	    }
	  },
	  methods: {
	    onBackToOldChatClick() {
	      this.isChangingVersion = true;
	      this.getVersionService().disableV2Version().then(() => {
	        window.location.replace('/online/');
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
	    onBackgroundSelectClick() {
	      this.showBackgroundPopup = true;
	      this.$emit('disableAutoHide');
	    },
	    onBackgroundPopupClose() {
	      this.showBackgroundPopup = false;
	      this.$emit('enableAutoHide');
	    },
	    onHelpClick() {
	      var _BX$Helper;
	      const ARTICLE_CODE = 17373696;
	      (_BX$Helper = BX.Helper) == null ? void 0 : _BX$Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
	      this.$emit('closePopup');
	    },
	    onFeedbackClick() {
	      BX.UI.Feedback.Form.open({
	        id: 'im-v2-feedback',
	        forms: [{
	          zones: ['ru'],
	          id: 550,
	          sec: '50my2x',
	          lang: 'ru'
	        }, {
	          zones: ['en'],
	          id: 560,
	          sec: '621lbr',
	          lang: 'ru'
	        }],
	        presets: {
	          sender_page: 'profile'
	        }
	      });
	      this.$emit('closePopup');
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
					<a :href="profileUri" target="_blank">
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
							<UserStatus :status="currentUserStatus" :size="UserStatusSize.M" />
						</div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ userStatusText }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="status-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Background select -->
				<div @click="onBackgroundSelectClick" class="bx-im-user-settings-popup__list-item --with-icon">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --background"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_CHAT_BACKGROUND') }}</div>
					</div>
					<div class="bx-im-user-settings-popup__list-item_icon --chevron" ref="background-select"></div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Help -->
				<div @click="onHelpClick" class="bx-im-user-settings-popup__list-item">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --help"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_HELP') }}</div>
					</div>
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<!-- Feedback -->
				<div @click="onFeedbackClick" class="bx-im-user-settings-popup__list-item">
					<div class="bx-im-user-settings-popup__list-item_left">
						<div class="bx-im-user-settings-popup__list-item_icon --feedback"></div>
						<div class="bx-im-user-settings-popup__list-item_text">{{ loc('IM_USER_SETTINGS_FEEDBACK') }}</div>
					</div>
				</div>
			</div>
			<!-- Back to old chat -->
			<div :class="{'--loading': isChangingVersion}" class="bx-im-user-settings-popup__old-chat">
				<div class="bx-im-user-settings-popup__list-item_icon --arrow-left"></div>
				<div @click="onBackToOldChatClick" class="bx-im-user-settings-popup__old-chat_text">
					{{ loc('IM_USER_SETTINGS_OLD_CHAT') }}
				</div>
			</div>
		</div>
		<UserStatusPopup
			v-if="showStatusPopup"
			:bindElement="$refs['status-select'] || {}"
			@close="onStatusPopupClose"
		/>
		<BackgroundPopup
			v-if="showBackgroundPopup"
			:bindElement="$refs['background-select'] || {}"
			@close="onBackgroundPopupClose"
		/>
	`
	};

	const POPUP_ID$2 = 'im-user-settings-popup';

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
	    POPUP_ID: () => POPUP_ID$2,
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
	    currentUserStatus() {
	      const status = this.$store.getters['users/getStatus'](this.currentUserId);
	      if (status) {
	        return status;
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
			<div @click="onAvatarClick" :class="{'--active': showSettingsPopup || showStatusPopup}" class="bx-im-navigation__user_avatar" ref="avatar">
				<Avatar :dialogId="currentUserId.toString()" :size="AvatarSize.M" />
				<div @click.stop="onStatusClick" :class="'--' + currentUserStatus" class="bx-im-navigation__user_status" ref="status"></div>
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
	      return [{
	        id: im_v2_const.Layout.chat.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_CHATS'),
	        counter: this.formatCounter(this.$store.getters['recent/getTotalCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.notification.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_NOTIFICATIONS'),
	        counter: this.formatCounter(this.$store.getters['notifications/getCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.openline.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
	        counter: 0,
	        active: false
	      }, {
	        id: im_v2_const.Layout.call.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_CALLS'),
	        counter: 0,
	        active: false
	      }, {
	        id: 'settings',
	        text: this.prepareNavigationText('IM_NAVIGATION_SETTINGS'),
	        counter: 0,
	        active: false
	      }];
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Navigation created');
	  },
	  mounted() {
	    const container = this.$refs['navigation'];
	    this.needBottomShadow = container.scrollTop + container.clientHeight !== container.scrollHeight;
	  },
	  methods: {
	    onMenuItemClick(item) {
	      if (!item.active) {
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
	        layoutName: layoutName,
	        layoutEntityId: layoutEntityId
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
	      return counter > 99 ? '99+' : `${counter}`;
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
	      this.$refs['navigation'].scrollTo({
	        top: this.$refs['navigation'].scrollHeight,
	        behavior: 'smooth'
	      });
	    },
	    onClickScrollUp() {
	      this.$refs['navigation'].scrollTo({
	        top: 0,
	        behavior: 'smooth'
	      });
	    }
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

	exports.MessengerNavigation = MessengerNavigation;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Vue3.Directives,BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI,BX,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=navigation.bundle.js.map
