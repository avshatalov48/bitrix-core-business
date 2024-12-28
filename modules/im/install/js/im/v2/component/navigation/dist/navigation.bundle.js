/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3_directives_hint,ui_dialogs_messagebox,ui_infoHelper,im_v2_lib_slider,im_v2_lib_call,im_v2_lib_phone,im_v2_lib_feature,im_v2_lib_analytics,im_v2_lib_permission,im_v2_component_elements,im_v2_lib_utils,im_v2_lib_logger,main_core,main_popup,im_v2_lib_menu,im_v2_lib_desktopApi,im_v2_lib_confirm,im_v2_lib_desktop,ui_buttons,ui_feedback_form,ui_fontawesome4,im_v2_application_core,im_v2_const,im_v2_lib_market) {
	'use strict';

	// @vue/component
	const ButtonPanel = {
	  name: 'ButtonPanel',
	  components: {
	    ChatButton: im_v2_component_elements.Button
	  },
	  emits: ['openProfile', 'logout'],
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    },
	    profileUri() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.currentUserId);
	    },
	    isDesktop() {
	      return im_v2_lib_desktopApi.DesktopApi.isDesktop();
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onLogoutClick() {
	      void im_v2_lib_desktopApi.DesktopApi.logout();
	    }
	  },
	  template: `
		<div class="bx-im-user-settings-popup__button-panel">
			<a :href="profileUri" target="_blank" class="bx-im-user-settings-popup__user_link">
				<ChatButton
					:color="ButtonColor.PrimaryBorder"
					:size="ButtonSize.M"
					:isUppercase="false"
					:isRounded="true"
					:text="loc('IM_USER_SETTINGS_OPEN_PROFILE')"
					@click="$emit('openProfile')"
				/>
			</a>
			<ChatButton
				v-if="isDesktop" 
				:color="ButtonColor.DangerBorder"
				:size="ButtonSize.M"
				:isUppercase="false"
				:isRounded="true"
				:text="loc('IM_USER_SETTINGS_LOGOUT')"
				@click="onLogoutClick"
			/>
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
	    this.store.dispatch('application/settings/set', {
	      status
	    });
	    return this.restClient.callMethod(im_v2_const.RestMethod.imUserStatusSet, {
	      STATUS: status
	    }).catch(error => {
	      // eslint-disable-next-line no-console
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
	      return [im_v2_const.UserStatus.online, im_v2_const.UserStatus.dnd];
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

	var _getConnectItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getConnectItem");
	var _getDeleteItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDeleteItem");
	var _connect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("connect");
	var _disconnect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disconnect");
	class DesktopItemContextMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _disconnect, {
	      value: _disconnect2
	    });
	    Object.defineProperty(this, _connect, {
	      value: _connect2
	    });
	    Object.defineProperty(this, _getDeleteItem, {
	      value: _getDeleteItem2
	    });
	    Object.defineProperty(this, _getConnectItem, {
	      value: _getConnectItem2
	    });
	    this.id = im_v2_const.PopupType.desktopItemMenu;
	  }
	  getMenuItems() {
	    return [babelHelpers.classPrivateFieldLooseBase(this, _getConnectItem)[_getConnectItem](), babelHelpers.classPrivateFieldLooseBase(this, _getDeleteItem)[_getDeleteItem]()];
	  }
	  close() {
	    var _PopupManager$getPopu;
	    (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.userProfile)) == null ? void 0 : _PopupManager$getPopu.setAutoHide(true);
	    super.close();
	  }
	}
	function _getConnectItem2() {
	  const title = this.context.connected ? main_core.Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_DISCONNECT_V2') : main_core.Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_CONNECT_V2');
	  return {
	    text: title,
	    onclick: function () {
	      var _PopupManager$getPopu2;
	      if (this.context.connected) {
	        babelHelpers.classPrivateFieldLooseBase(this, _disconnect)[_disconnect]();
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _connect)[_connect]();
	      }
	      this.menuInstance.close();
	      (_PopupManager$getPopu2 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.userProfile)) == null ? void 0 : _PopupManager$getPopu2.close();
	    }.bind(this)
	  };
	}
	function _getDeleteItem2() {
	  return {
	    text: main_core.Loc.getMessage('IM_USER_SETTINGS_DESKTOP_CONTEXT_MENU_DELETE_V2'),
	    onclick: async function () {
	      const userChoice = await im_v2_lib_confirm.showDesktopDeleteConfirm();
	      if (userChoice === true) {
	        var _PopupManager$getPopu3;
	        im_v2_lib_desktopApi.DesktopApi.deleteAccount(this.context.host, this.context.login);
	        (_PopupManager$getPopu3 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.userProfile)) == null ? void 0 : _PopupManager$getPopu3.close();
	      }
	    }.bind(this)
	  };
	}
	function _connect2() {
	  const {
	    host,
	    login,
	    protocol
	  } = this.context;
	  const userLang = navigator.language;
	  im_v2_lib_desktopApi.DesktopApi.connectAccount(host, login, protocol, userLang);
	}
	function _disconnect2() {
	  const {
	    host
	  } = this.context;
	  im_v2_lib_desktopApi.DesktopApi.disconnectAccount(host);
	}

	// @vue/component
	const DesktopAccountItem = {
	  name: 'DesktopAccountItem',
	  props: {
	    account: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      errorLoadAvatar: false
	    };
	  },
	  computed: {
	    accountItem() {
	      return this.account;
	    },
	    avatarUrl() {
	      if (this.errorLoadAvatar || !this.hasAvatar) {
	        return '';
	      }
	      if (this.accountItem.avatar.startsWith('http')) {
	        return this.accountItem.avatar;
	      }
	      return `${this.accountItem.protocol}://${this.accountItem.host}${this.accountItem.avatar}`;
	    },
	    isConnected() {
	      return this.accountItem.connected;
	    },
	    hasAvatar() {
	      return this.accountItem.avatar && this.accountItem.avatar !== '/bitrix/js/im/images/blank.gif';
	    }
	  },
	  methods: {
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        account: this.account,
	        target: event.target
	      });
	    },
	    onDomainClick() {
	      if (!this.isConnected) {
	        return;
	      }
	      im_v2_lib_desktop.DesktopManager.getInstance().openAccountTab(this.accountItem.portal);
	    },
	    onError() {
	      this.errorLoadAvatar = true;
	    }
	  },
	  template: `
		<div class="bx-im-desktop-connection-list-item__container bx-im-desktop-connection-list-item__scope">
			<div class="bx-im-desktop-connection-list-item__content" :class="{'--disconnected': !isConnected}">
				<img 
					v-if="avatarUrl" 
					:src="avatarUrl"
					:alt="accountItem.portal"
					@error="onError"
					class="bx-im-desktop-connection-list-item__avatar" 
				/>
				<span v-else class="bx-im-desktop-connection-list-item__avatar-default"></span>
				<div class="bx-im-desktop-connection-list-item__title-container">
					<span class="bx-im-desktop-connection-list-item__title" @click="onDomainClick">
						{{ accountItem.portal }}
					</span>
					<span class="bx-im-desktop-connection-list-item__login">{{ accountItem.login }}</span>
				</div>
			</div>
			<button
				class="bx-im-messenger__context-menu-icon bx-im-desktop-connection-list-item__context-menu"
				@click="onContextMenuClick"
			></button>
		</div>
	`
	};

	// @vue/component
	const DesktopAccountList = {
	  name: 'DesktopAccountList',
	  components: {
	    DesktopAccountItem
	  },
	  emits: ['openContextMenu'],
	  data() {
	    return {
	      accounts: []
	    };
	  },
	  computed: {
	    isEmptyState() {
	      return this.accounts.length === 0;
	    }
	  },
	  created() {
	    this.contextMenu = new DesktopItemContextMenu();
	    this.accounts = im_v2_lib_desktopApi.DesktopApi.getAccountList();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    openLoginTab() {
	      var _PopupManager$getPopu;
	      this.contextMenu.destroy();
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.userProfile)) == null ? void 0 : _PopupManager$getPopu.close();
	      im_v2_lib_desktopApi.DesktopApi.openAddAccountTab();
	    },
	    onContextMenuClick(event) {
	      const {
	        account,
	        target
	      } = event;
	      this.contextMenu.openMenu(account, target);
	      this.$emit('openContextMenu');
	    }
	  },
	  template: `
		<div class="bx-im-desktop-connection-list__container bx-im-desktop-connection-list__scope">
			<div class="bx-im-desktop-connection-list__header">
				<span class="bx-im-desktop-connection-list__title">
					{{ $Bitrix.Loc.getMessage('IM_USER_SETTINGS_CONNECTED_BITRIX24') }}
				</span>
				<span class="bx-im-desktop-connection-list__add" @click="openLoginTab">
					{{ $Bitrix.Loc.getMessage('IM_USER_SETTINGS_CONNECT_BITRIX24') }}
				</span>
			</div>
			<div class="bx-im-desktop-connection-list__items">
				<DesktopAccountItem 
					v-for="account in accounts" 
					:account="account" 
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const UserSettingsContent = {
	  name: 'UserSettingsContent',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    UserStatus: im_v2_component_elements.UserStatus,
	    ButtonPanel,
	    UserStatusPopup,
	    DesktopAccountList,
	    ScrollWithGradient: im_v2_component_elements.ScrollWithGradient
	  },
	  emits: ['closePopup', 'enableAutoHide', 'disableAutoHide'],
	  data() {
	    return {
	      showStatusPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    UserStatusSize: () => im_v2_component_elements.UserStatusSize,
	    currentUserId() {
	      return im_v2_application_core.Core.getUserId();
	    },
	    currentUserDialogId() {
	      return this.currentUserId.toString();
	    },
	    currentUser() {
	      return this.$store.getters['users/get'](this.currentUserId, true);
	    },
	    currentUserPosition() {
	      return this.$store.getters['users/getPosition'](this.currentUserId);
	    },
	    userStatus() {
	      const status = this.$store.getters['application/settings/get'](im_v2_const.Settings.user.status);
	      if (status) {
	        return status;
	      }
	      return im_v2_const.UserStatus.online;
	    },
	    currentHost() {
	      return location.hostname;
	    },
	    userStatusText() {
	      return im_v2_lib_utils.Utils.user.getStatusText(this.userStatus);
	    },
	    isDesktopAccountManagementAvailable() {
	      return im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.accountManagement.id);
	    }
	  },
	  methods: {
	    onStatusClick() {
	      this.showStatusPopup = true;
	      this.$emit('disableAutoHide');
	    },
	    onStatusPopupClose() {
	      this.showStatusPopup = false;
	      this.$emit('enableAutoHide');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onScroll() {
	      var _MenuManager$getMenuB;
	      (_MenuManager$getMenuB = main_popup.MenuManager.getMenuById(im_v2_const.PopupType.desktopItemMenu)) == null ? void 0 : _MenuManager$getMenuB.close();
	    }
	  },
	  template: `
		<div class="bx-im-user-settings-popup__scope bx-im-user-settings-popup__container">
			<div class="bx-im-user-settings-popup__header">
				<div class="bx-im-user-settings-popup__header_left">
					<ChatAvatar 
						:avatarDialogId="currentUserDialogId" 
						:contextDialogId="currentUserDialogId" 
						:size="AvatarSize.XL" 
					/>
				</div>
				<div class="bx-im-user-settings-popup__header_right">
					<div class="bx-im-user-settings-popup__domain">{{ currentHost }}</div>
					<div class="bx-im-user-settings-popup__user_name" :title="currentUser.name">{{ currentUser.name }}</div>
					<div class="bx-im-user-settings-popup__user_title" :title="currentUserPosition">{{ currentUserPosition }}</div>
					<ButtonPanel @openProfile="$emit('closePopup')" />
				</div>
			</div>
			<ScrollWithGradient :containerMaxHeight="328" :gradientHeight="24" @scroll="onScroll">
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
				</div>
				<div class="bx-im-user-settings-popup__separator"></div>
				<DesktopAccountList 
					v-if="isDesktopAccountManagementAvailable"
					@openContextMenu="$emit('disableAutoHide')"
				/>
			</ScrollWithGradient>
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
			<UserSettingsContent 
				@closePopup="$emit('close')" 
				@enableAutoHide="enableAutoHide" 
				@disableAutoHide="disableAutoHide" 
			/>
		</MessengerPopup>
	`
	};

	// @vue/component
	const UserSettings = {
	  name: 'UserSettings',
	  components: {
	    UserSettingsPopup,
	    UserStatusPopup,
	    ChatAvatar: im_v2_component_elements.ChatAvatar
	  },
	  data() {
	    return {
	      showSettingsPopup: false,
	      showStatusPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    currentUserDialogId() {
	      return im_v2_application_core.Core.getUserId().toString();
	    },
	    userStatus() {
	      const status = this.$store.getters['application/settings/get'](im_v2_const.Settings.user.status);
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
			<div @click="onAvatarClick" class="bx-im-navigation__user_avatar" ref="avatar">
				<ChatAvatar 
					:avatarDialogId="currentUserDialogId"
					:contextDialogId="currentUserDialogId" 
					:size="AvatarSize.M" 
				/>
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

	const LayoutToAction = Object.freeze({
	  [im_v2_const.Layout.market.name]: im_v2_const.ActionByUserType.getMarket,
	  [im_v2_const.Layout.openlines.name]: im_v2_const.ActionByUserType.getOpenlines,
	  [im_v2_const.Layout.channel.name]: im_v2_const.ActionByUserType.getChannels
	});

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
	        counter: this.formatCounter(this.$store.getters['counters/getTotalChatCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.copilot.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_COPILOT'),
	        counter: this.formatCounter(this.$store.getters['counters/getTotalCopilotCounter']),
	        clickHandler: this.onCopilotClick,
	        showCondition: () => im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotAvailable),
	        active: true
	      }, {
	        id: im_v2_const.Layout.collab.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_COLLAB'),
	        counter: this.formatCounter(this.$store.getters['counters/getTotalCollabCounter']),
	        showCondition: () => im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabAvailable),
	        active: true
	      }, {
	        id: im_v2_const.Layout.channel.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_CHANNELS'),
	        active: true
	      }, {
	        id: im_v2_const.Layout.openlines.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
	        counter: this.formatCounter(this.$store.getters['counters/getTotalLinesCounter']),
	        showCondition: () => {
	          return !this.isOptionOpenLinesV2Activated();
	        },
	        active: true
	      }, {
	        id: im_v2_const.Layout.openlinesV2.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_OPENLINES'),
	        counter: this.formatCounter(this.$store.getters['counters/getTotalLinesCounter']),
	        showCondition: this.isOptionOpenLinesV2Activated,
	        active: true
	      }, {
	        id: im_v2_const.Layout.notification.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_NOTIFICATIONS'),
	        counter: this.formatCounter(this.$store.getters['notifications/getCounter']),
	        active: true
	      }, {
	        id: im_v2_const.Layout.call.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_CALLS_V2'),
	        clickHandler: this.onCallClick,
	        showCondition: im_v2_lib_phone.PhoneManager.getInstance().canCall.bind(im_v2_lib_phone.PhoneManager.getInstance()),
	        active: true
	      }, {
	        id: 'timemanager',
	        text: this.prepareNavigationText('IM_NAVIGATION_TIMEMANAGER'),
	        clickHandler: this.onTimeManagerClick,
	        showCondition: this.isTimeManagerActive,
	        active: true
	      }, {
	        id: 'main-page',
	        text: this.prepareNavigationText('IM_NAVIGATION_MAIN_PAGE'),
	        clickHandler: this.onMainPageClick,
	        showCondition: this.isMainPageActive,
	        active: true
	      }, {
	        id: 'market'
	      }, {
	        id: im_v2_const.Layout.settings.name,
	        text: this.prepareNavigationText('IM_NAVIGATION_SETTINGS'),
	        active: true
	      }];
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
	      this.sendClickEvent({
	        layoutName: item.id
	      });
	    },
	    sendClickEvent({
	      layoutName,
	      layoutEntityId = ''
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
	    needToShowMenuItem(item) {
	      if (!this.hasLayoutAccess(item)) {
	        return false;
	      }
	      if (!main_core.Type.isFunction(item.showCondition)) {
	        return true;
	      }
	      return item.showCondition() === true;
	    },
	    hasLayoutAccess(item) {
	      const action = LayoutToAction[item.id];
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(action);
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
	    },
	    onCopilotClick() {
	      if (!im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotActive)) {
	        const promoter = new ui_infoHelper.FeaturePromoter({
	          code: im_v2_const.SliderCode.copilotDisabled
	        });
	        promoter.show();
	        im_v2_lib_analytics.Analytics.getInstance().copilot.onOpenTab({
	          isAvailable: false
	        });
	        return;
	      }
	      this.sendClickEvent({
	        layoutName: im_v2_const.Layout.copilot.name
	      });
	    },
	    isOptionOpenLinesV2Activated() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.openLinesV2);
	    },
	    onMainPageClick() {
	      im_v2_lib_utils.Utils.browser.openLink('/');
	    },
	    isMainPageActive() {
	      return im_v2_lib_desktopApi.DesktopApi.isChatWindow();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
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
	`
	};

	exports.MessengerNavigation = MessengerNavigation;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Vue3.Directives,BX.UI.Dialogs,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI,BX.UI.Feedback,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=navigation.bundle.js.map
