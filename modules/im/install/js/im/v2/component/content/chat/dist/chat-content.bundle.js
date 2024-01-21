/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_uploader_core,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_theme,im_v2_lib_textarea,im_v2_lib_layout,im_v2_lib_logger,im_v2_component_entitySelector,main_core_events,im_v2_lib_localStorage,im_v2_lib_permission,im_v2_lib_menu,im_public,im_v2_lib_call,main_core,im_v2_lib_utils,im_v2_component_sidebar,im_v2_application_core,im_v2_const,im_v2_component_elements,im_v2_provider_service) {
	'use strict';

	class UserService {
	  async updateLastActivityDate(userId) {
	    if (this.isPullServerWithUserStatusSupport()) {
	      const lastActivityDate = await this.getUserActivityFromPull(userId);
	      if (!lastActivityDate) {
	        return Promise.resolve();
	      }
	      return this.updateUserModel(userId, {
	        lastActivityDate
	      });
	    }
	    const userData = await this.requestUserData(userId);
	    return this.updateUserModel(userId, userData);
	  }
	  async getUserActivityFromPull(userId) {
	    const result = await im_v2_application_core.Core.getPullClient().getUsersLastSeen([userId]).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error getting user activity from P&P', error);
	    });
	    if (!main_core.Type.isNumber(result[userId])) {
	      return null;
	    }
	    const activityDateAgo = result[userId] * 1000;
	    return new Date(Date.now() - activityDateAgo);
	  }
	  async requestUserData(userId) {
	    im_v2_lib_logger.Logger.warn(`UserService: get actual user data for - ${userId}`);
	    const answer = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imUserGet, {
	      ID: userId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error getting user data', error);
	    });
	    return answer.data();
	  }
	  async updateUserModel(userId, userFields) {
	    im_v2_lib_logger.Logger.warn('UserService: update user data', userFields);
	    return im_v2_application_core.Core.getStore().dispatch('users/update', {
	      id: userId,
	      fields: userFields
	    });
	  }
	  isPullServerWithUserStatusSupport() {
	    return im_v2_application_core.Core.getPullClient().isJsonRpc();
	  }
	}

	const CallTypes = {
	  video: {
	    id: 'video',
	    locCode: 'IM_CONTENT_CHAT_HEADER_VIDEOCALL',
	    start: dialogId => {
	      im_public.Messenger.startVideoCall(dialogId);
	    }
	  },
	  audio: {
	    id: 'audio',
	    locCode: 'IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO',
	    start: dialogId => {
	      im_public.Messenger.startVideoCall(dialogId, false);
	    }
	  },
	  beta: {
	    id: 'beta',
	    locCode: 'IM_CONTENT_CHAT_HEADER_CALL_MENU_BETA_2',
	    start: dialogId => {
	      const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	      im_v2_lib_call.CallManager.getInstance().createBetaCallRoom(dialog.chatId);
	    }
	  }
	};

	let _ = t => t,
	  _t;
	var _getDelimiter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDelimiter");
	var _getVideoCallItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getVideoCallItem");
	var _getAudioCallItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAudioCallItem");
	var _getPersonalPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPersonalPhoneItem");
	var _getWorkPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWorkPhoneItem");
	var _getInnerPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getInnerPhoneItem");
	var _getUserPhoneHtml = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserPhoneHtml");
	var _isCallAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCallAvailable");
	var _getUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUser");
	var _isUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUser");
	class CallMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isUser, {
	      value: _isUser2
	    });
	    Object.defineProperty(this, _getUser, {
	      value: _getUser2
	    });
	    Object.defineProperty(this, _isCallAvailable, {
	      value: _isCallAvailable2
	    });
	    Object.defineProperty(this, _getUserPhoneHtml, {
	      value: _getUserPhoneHtml2
	    });
	    Object.defineProperty(this, _getInnerPhoneItem, {
	      value: _getInnerPhoneItem2
	    });
	    Object.defineProperty(this, _getWorkPhoneItem, {
	      value: _getWorkPhoneItem2
	    });
	    Object.defineProperty(this, _getPersonalPhoneItem, {
	      value: _getPersonalPhoneItem2
	    });
	    Object.defineProperty(this, _getAudioCallItem, {
	      value: _getAudioCallItem2
	    });
	    Object.defineProperty(this, _getVideoCallItem, {
	      value: _getVideoCallItem2
	    });
	    Object.defineProperty(this, _getDelimiter, {
	      value: _getDelimiter2
	    });
	    this.id = 'bx-im-chat-header-call-menu';
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 4,
	      offsetTop: 5
	    };
	  }
	  getMenuClassName() {
	    return 'bx-im-messenger__scope bx-im-chat-header-call-button__scope';
	  }
	  getMenuItems() {
	    return [babelHelpers.classPrivateFieldLooseBase(this, _getVideoCallItem)[_getVideoCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getAudioCallItem)[_getAudioCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter](), babelHelpers.classPrivateFieldLooseBase(this, _getPersonalPhoneItem)[_getPersonalPhoneItem](), babelHelpers.classPrivateFieldLooseBase(this, _getWorkPhoneItem)[_getWorkPhoneItem](), babelHelpers.classPrivateFieldLooseBase(this, _getInnerPhoneItem)[_getInnerPhoneItem]()];
	  }
	}
	function _getDelimiter2() {
	  return {
	    delimiter: true
	  };
	}
	function _getVideoCallItem2() {
	  const isAvailable = babelHelpers.classPrivateFieldLooseBase(this, _isCallAvailable)[_isCallAvailable](this.context.dialogId);
	  return {
	    text: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_VIDEOCALL'),
	    onclick: () => {
	      if (!isAvailable) {
	        return;
	      }
	      CallTypes.video.start(this.context.dialogId);
	      this.emit(CallMenu.events.onMenuItemClick, CallTypes.video);
	      this.menuInstance.close();
	    },
	    disabled: !isAvailable
	  };
	}
	function _getAudioCallItem2() {
	  const isAvailable = babelHelpers.classPrivateFieldLooseBase(this, _isCallAvailable)[_isCallAvailable](this.context.dialogId);
	  return {
	    text: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO'),
	    onclick: () => {
	      if (!isAvailable) {
	        return;
	      }
	      CallTypes.audio.start(this.context.dialogId);
	      this.emit(CallMenu.events.onMenuItemClick, CallTypes.audio);
	      this.menuInstance.close();
	    },
	    disabled: !isAvailable
	  };
	}
	function _getPersonalPhoneItem2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUser)[_isUser]()) {
	    return null;
	  }
	  const {
	    phones
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getUser)[_getUser]();
	  if (!phones.personalMobile) {
	    return null;
	  }
	  const title = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_PERSONAL_PHONE');
	  return {
	    className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
	    html: babelHelpers.classPrivateFieldLooseBase(this, _getUserPhoneHtml)[_getUserPhoneHtml](title, phones.personalMobile),
	    onclick: () => {
	      im_public.Messenger.startPhoneCall(phones.personalMobile);
	      this.menuInstance.close();
	    }
	  };
	}
	function _getWorkPhoneItem2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUser)[_isUser]()) {
	    return null;
	  }
	  const {
	    phones
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getUser)[_getUser]();
	  if (!phones.workPhone) {
	    return null;
	  }
	  const title = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_WORK_PHONE');
	  return {
	    className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
	    html: babelHelpers.classPrivateFieldLooseBase(this, _getUserPhoneHtml)[_getUserPhoneHtml](title, phones.workPhone),
	    onclick: () => {
	      im_public.Messenger.startPhoneCall(phones.workPhone);
	      this.menuInstance.close();
	    }
	  };
	}
	function _getInnerPhoneItem2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUser)[_isUser]()) {
	    return null;
	  }
	  const {
	    phones
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getUser)[_getUser]();
	  if (!phones.innerPhone) {
	    return null;
	  }
	  const title = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_INNER_PHONE');
	  return {
	    className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
	    html: babelHelpers.classPrivateFieldLooseBase(this, _getUserPhoneHtml)[_getUserPhoneHtml](title, phones.innerPhone),
	    onclick: () => {
	      im_public.Messenger.startPhoneCall(phones.innerPhone);
	      this.menuInstance.close();
	    }
	  };
	}
	function _getUserPhoneHtml2(title, phoneNumber) {
	  return main_core.Tag.render(_t || (_t = _`
			<span class="bx-im-chat-header-call-button-menu__phone_container">
				<span class="bx-im-chat-header-call-button-menu__phone_title">${0}</span>
				<span class="bx-im-chat-header-call-button-menu__phone_number">${0}</span>
			</span>
		`), title, phoneNumber);
	}
	function _isCallAvailable2(dialogId) {
	  if (im_v2_application_core.Core.getStore().getters['recent/calls/hasActiveCall'](dialogId) && im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId() === dialogId) {
	    return true;
	  }
	  if (im_v2_application_core.Core.getStore().getters['recent/calls/hasActiveCall']()) {
	    return false;
	  }
	  const chatCanBeCalled = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(dialogId);
	  const chatIsAllowedToCall = im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.call, dialogId);
	  return chatCanBeCalled && chatIsAllowedToCall;
	}
	function _getUser2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUser)[_isUser]()) {
	    return null;
	  }
	  return im_v2_application_core.Core.getStore().getters['users/get'](this.context.dialogId);
	}
	function _isUser2() {
	  return this.context.type === im_v2_const.ChatType.user;
	}
	CallMenu.events = {
	  onMenuItemClick: 'onMenuItemClick'
	};

	// @vue/component
	const CallButton = {
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: [],
	  data() {
	    return {
	      lastCallType: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isActive() {
	      if (this.$store.getters['recent/calls/hasActiveCall'](this.dialogId) && im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId() === this.dialogId) {
	        return true;
	      }
	      if (this.$store.getters['recent/calls/hasActiveCall']()) {
	        return false;
	      }
	      const chatCanBeCalled = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialogId);
	      const chatIsAllowedToCall = im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.call, this.dialogId);
	      return chatCanBeCalled && chatIsAllowedToCall;
	    },
	    isConference() {
	      return this.dialog.type === im_v2_const.ChatType.videoconf;
	    },
	    callButtonText() {
	      const locCode = CallTypes[this.lastCallType].locCode;
	      return this.loc(locCode);
	    }
	  },
	  created() {
	    this.lastCallType = this.getLastCallChoice();
	    this.subscribeToMenuItemClick();
	  },
	  methods: {
	    startVideoCall() {
	      if (!this.isActive) {
	        return;
	      }
	      im_public.Messenger.startVideoCall(this.dialogId);
	    },
	    subscribeToMenuItemClick() {
	      this.getCallMenu().subscribe(CallMenu.events.onMenuItemClick, event => {
	        const {
	          id: callTypeId
	        } = event.getData();
	        this.saveLastCallChoice(callTypeId);
	      });
	    },
	    getCallMenu() {
	      if (!this.callMenu) {
	        this.callMenu = new CallMenu();
	      }
	      return this.callMenu;
	    },
	    onButtonClick() {
	      if (!this.isActive) {
	        return;
	      }
	      CallTypes[this.lastCallType].start(this.dialogId);
	    },
	    onMenuClick() {
	      if (!this.shouldShowMenu()) {
	        return;
	      }
	      this.getCallMenu().openMenu(this.dialog, this.$refs.menu);
	    },
	    onStartConferenceClick() {
	      im_public.Messenger.openConference({
	        code: this.dialog.public.code
	      });
	    },
	    getLastCallChoice() {
	      const result = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.lastCallType, CallTypes.video.id);
	      if (result === CallTypes.beta.id && !this.isCallBetaAvailable()) {
	        return CallTypes.video.id;
	      }
	      return result;
	    },
	    saveLastCallChoice(callTypeId) {
	      this.lastCallType = callTypeId;
	      im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.lastCallType, callTypeId);
	    },
	    shouldShowMenu() {
	      return this.isActive || this.isCallBetaAvailable();
	    },
	    isCallBetaAvailable() {
	      // TODO remove this after release call beta
	      // const settings = Extension.getSettings('im.v2.component.content.chat');
	      // return settings.get('isCallBetaAvailable');

	      return false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div v-if="isConference" class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container --conference" @click="onStartConferenceClick">
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_START_CONFERENCE') }}
			</div>
		</div>
		<div
			v-else
			class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container"
			:class="{'--disabled': !isActive}"
			@click="onButtonClick"
		>
			<div class="bx-im-chat-header-call-button__text">
				{{ callButtonText }}
			</div>
			<div class="bx-im-chat-header-call-button__separator"></div>
			<div class="bx-im-chat-header-call-button__chevron_container" @click.stop="onMenuClick">
				<div class="bx-im-chat-header-call-button__chevron" ref="menu"></div>
			</div>
		</div>
	`
	};

	const ParamsByChatType = {
	  [im_v2_const.ChatType.tasks]: {
	    className: '--task',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_TASK')
	  },
	  [im_v2_const.ChatType.calendar]: {
	    className: '--calendar',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MEETING')
	  },
	  [im_v2_const.ChatType.sonetGroup]: {
	    className: '--group',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_GROUP')
	  },
	  [im_v2_const.ChatType.crm]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CRM')
	  },
	  [im_v2_const.ChatType.mail]: {
	    className: '--mail',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MAIL')
	  }
	};
	const CrmEntityType = {
	  lead: 'LEAD',
	  deal: 'DEAL',
	  contact: 'CONTACT',
	  company: 'COMPANY'
	};
	const CrmLinkTextByEntity = {
	  [CrmEntityType.lead]: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_LEAD'),
	  [CrmEntityType.deal]: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DEAL'),
	  [CrmEntityType.contact]: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CONTACT'),
	  [CrmEntityType.company]: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_COMPANY')
	};

	// @vue/component
	const EntityLink = {
	  name: 'EntityLink',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    entityId() {
	      return this.dialog.entityLink.id;
	    },
	    entityType() {
	      return this.dialog.entityLink.type;
	    },
	    entityUrl() {
	      return this.dialog.entityLink.url;
	    },
	    containerClassName() {
	      var _ParamsByChatType$thi, _ParamsByChatType$thi2;
	      return (_ParamsByChatType$thi = (_ParamsByChatType$thi2 = ParamsByChatType[this.dialog.type]) == null ? void 0 : _ParamsByChatType$thi2.className) != null ? _ParamsByChatType$thi : '';
	    },
	    linkText() {
	      var _ParamsByChatType$thi3, _ParamsByChatType$thi4;
	      if (this.dialog.type === im_v2_const.ChatType.crm) {
	        return this.getCrmLinkText();
	      }
	      return (_ParamsByChatType$thi3 = (_ParamsByChatType$thi4 = ParamsByChatType[this.dialog.type]) == null ? void 0 : _ParamsByChatType$thi4.loc) != null ? _ParamsByChatType$thi3 : 'Open entity';
	    }
	  },
	  methods: {
	    getCrmLinkText() {
	      const [entityType] = this.entityId.split('|');
	      if (!entityType) {
	        return '';
	      }
	      return CrmLinkTextByEntity[entityType];
	    }
	  },
	  template: `
		<a :href="entityUrl" class="bx-im-chat-header-entity-link__container" :class="containerClassName">
			<div class="bx-im-chat-header-entity-link__icon"></div>
			<div class="bx-im-chat-header-entity-link__text">{{ linkText }}</div>
			<div class="bx-im-chat-header-entity-link__arrow"></div>
		</a>
	`
	};

	// @vue/component
	const GroupChatTitle = {
	  name: 'GroupChatTitle',
	  components: {
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    EntityLink
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['membersClick', 'newTitle'],
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    userCounter() {
	      return main_core.Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    },
	    hasEntityLink() {
	      var _this$dialog$entityLi;
	      return Boolean((_this$dialog$entityLi = this.dialog.entityLink) == null ? void 0 : _this$dialog$entityLi.url);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__info">
			<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="$emit('newTitle', $event)" />
			<div class="bx-im-chat-header__subtitle_container">
				<div
					:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
					@click="$emit('membersClick')"
					class="bx-im-chat-header__subtitle_content --click"
				>
					{{ userCounter }}
				</div>
				<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
			</div>
		</div>
	`
	};

	const ONE_MINUTE = 60 * 1000;

	// @vue/component
	const UserTitle = {
	  name: 'UserTitle',
	  components: {
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      userLastOnlineText: ''
	    };
	  },
	  computed: {
	    userPosition() {
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    userLastOnline() {
	      return this.$store.getters['users/getLastOnline'](this.dialogId);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  watch: {
	    userLastOnline(value) {
	      this.userLastOnlineText = value;
	    }
	  },
	  created() {
	    this.updateUserOnline();
	    this.userLastOnlineInterval = setInterval(this.updateUserOnline, ONE_MINUTE);
	  },
	  beforeUnmount() {
	    clearInterval(this.userLastOnlineInterval);
	  },
	  methods: {
	    updateUserOnline() {
	      this.userLastOnlineText = this.$store.getters['users/getLastOnline'](this.dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__info">
			<div class="bx-im-chat-header__title --user">
				<a :href="userLink" target="_blank" class="bx-im-chat-header__title_container">
					<ChatTitle :dialogId="dialogId" />
				</a>
				<span class="bx-im-chat-header__user-status">{{ userLastOnlineText }}</span>
			</div>
			<div class="bx-im-chat-header__subtitle_container">
				<div class="bx-im-chat-header__subtitle_content">{{ userPosition }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    CallButton,
	    GroupChatTitle,
	    UserTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    sidebarOpened: {
	      type: Boolean,
	      required: true
	    },
	    sidebarSearchOpened: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['toggleRightPanel', 'toggleSearchPanel', 'toggleMembersPanel'],
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      if (!this.isUser) {
	        return false;
	      }
	      return this.user.bot === true;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    },
	    showCallButton() {
	      return !this.isBot;
	    },
	    showInviteButton() {
	      if (this.isBot) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.extend, this.dialogId);
	    },
	    canChangeAvatar() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.avatar, this.dialogId);
	    }
	  },
	  methods: {
	    toggleRightPanel() {
	      this.$emit('toggleRightPanel');
	    },
	    toggleSearchPanel() {
	      this.$emit('toggleSearchPanel');
	    },
	    onMembersClick() {
	      if (!this.isInited) {
	        return;
	      }
	      this.$emit('toggleMembersPanel');
	    },
	    onNewTitleSubmit(newTitle) {
	      this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR')
	        });
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    openInvitePopup() {
	      this.showAddToChatPopup = true;
	    },
	    onAvatarClick() {
	      if (!this.isChat || !this.canChangeAvatar) {
	        return;
	      }
	      this.$refs.avatarInput.click();
	    },
	    async onAvatarSelect(event) {
	      const input = event.target;
	      const file = input.files[0];
	      if (!file) {
	        return;
	      }
	      const preparedAvatar = await this.getChatService().prepareAvatar(file);
	      if (!preparedAvatar) {
	        return;
	      }
	      void this.getChatService().changeAvatar(this.dialog.chatId, preparedAvatar);
	    },
	    onContainerClick(event) {
	      if (this.isGuest) {
	        event.stopPropagation();
	      }
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div @click.capture="onContainerClick" class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<div class="bx-im-chat-header__avatar" :class="{'--can-change': canChangeAvatar}" @click="onAvatarClick">
					<Avatar v-if="isChat" :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					<a v-else :href="userLink" target="_blank">
						<Avatar :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					</a>
				</div>
				<input 
					type="file" 
					@change="onAvatarSelect" 
					accept="image/*" 
					class="bx-im-chat-header__avatar_input" 
					ref="avatarInput"
				>
				<GroupChatTitle
					v-if="isChat"
					:dialogId="dialogId"
					@membersClick="onMembersClick"
					@newTitle="onNewTitleSubmit"
				/>
				<UserTitle v-else :dialogId="dialogId" />
			</div>
			<div class="bx-im-chat-header__right">
				<CallButton v-if="showCallButton" :dialogId="dialogId" />
				<div
					v-if="showInviteButton"
					class="bx-im-chat-header__icon --add-people"
					:class="{'--active': showAddToChatPopup}"
					@click="openInvitePopup" 
					ref="add-members"
				></div>
				<div 
					@click="toggleSearchPanel" 
					class="bx-im-chat-header__icon --search" 
					:class="{'--active': sidebarSearchOpened}"
				></div>
				<div 
					@click="toggleRightPanel" 
					class="bx-im-chat-header__icon --panel" 
					:class="{'--active': sidebarOpened}"
				></div>
			</div>
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const SidebarWrapper = {
	  name: 'SidebarWrapper',
	  components: {
	    ChatSidebar: im_v2_component_sidebar.ChatSidebar
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    sidebarDetailBlock: {
	      type: String,
	      default: null
	    }
	  },
	  emits: ['back'],
	  methods: {
	    onClickBack() {
	      this.$emit('back');
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-wrapper__scope bx-im-sidebar-wrapper__container">
			<ChatSidebar
				:dialogId="dialogId" 
				:key="dialogId" 
				:sidebarDetailBlock="sidebarDetailBlock"
				@back="onClickBack"
			/>
		</div>
	`
	};

	// @vue/component
	const DropArea = {
	  props: {
	    show: {
	      type: Boolean,
	      required: true
	    }
	  },
	  template: `
		<Transition name="drop-area-fade">
			<div v-if="show" class="bx-im-content-chat-drop-area__container bx-im-content-chat-drop-area__scope">
				<div class="bx-im-content-chat-drop-area__box">
					<span class="bx-im-content-chat-drop-area__icon"></span>
					<label class="bx-im-content-chat-drop-area__label-text">
						{{ $Bitrix.Loc.getMessage('IM_CONTENT_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
	`
	};

	// @vue/component
	const EmptyState = {
	  data() {
	    return {};
	  },
	  computed: {
	    iconClass() {
	      return this.isEmptyRecent ? '--empty' : '--default';
	    },
	    text() {
	      if (this.isEmptyRecent) {
	        return this.loc('IM_CONTENT_CHAT_NO_CHATS_START_MESSAGE');
	      }
	      return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
	    },
	    subtext() {
	      return '';
	    },
	    isEmptyRecent() {
	      return im_v2_provider_service.RecentService.getInstance().getCollection().length === 0;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-start__container">
			<div class="bx-im-content-chat-start__content">
				<div class="bx-im-content-chat-start__icon" :class="iconClass"></div>
				<div class="bx-im-content-chat-start__title">
					{{ text }}
				</div>
				<div v-if="subtext" class="bx-im-content-chat-start__subtitle">
					{{ subtext }}
				</div>
			</div>
		</div>
	`
	};

	const BUTTON_BACKGROUND_COLOR = 'rgba(0, 0, 0, 0.1)';
	const BUTTON_HOVER_COLOR = 'rgba(0, 0, 0, 0.2)';
	const BUTTON_TEXT_COLOR = '#fff';

	// @vue/component
	const MutePanel = {
	  components: {
	    ChatButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isMuted() {
	      return this.dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	    },
	    buttonText() {
	      const mutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_ENABLE_NOTIFICATIONS');
	      const unmutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_DISABLE_NOTIFICATIONS');
	      return this.isMuted ? mutedCode : unmutedCode;
	    },
	    buttonColorScheme() {
	      return {
	        borderColor: im_v2_const.Color.transparent,
	        backgroundColor: BUTTON_BACKGROUND_COLOR,
	        iconColor: BUTTON_TEXT_COLOR,
	        textColor: BUTTON_TEXT_COLOR,
	        hoverColor: BUTTON_HOVER_COLOR
	      };
	    }
	  },
	  methods: {
	    onButtonClick() {
	      if (this.isMuted) {
	        this.getChatService().unmuteChat(this.dialogId);
	        return;
	      }
	      this.getChatService().muteChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__textarea_placeholder">
			<ChatButton
				:size="ButtonSize.XL"
				:customColorScheme="buttonColorScheme"
				:text="buttonText"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	// @vue/component
	const JoinPanel = {
	  components: {
	    ChatButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  methods: {
	    onButtonClick() {
	      this.getChatService().joinChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__textarea_placeholder">
			<ChatButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Primary"
				:text="loc('IM_CONTENT_BLOCKED_TEXTAREA_JOIN_CHAT')"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	const CHAT_HEADER_HEIGHT = 64;

	// @vue/component
	const ChatContent = {
	  name: 'ChatContent',
	  components: {
	    ChatHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    SidebarWrapper,
	    DropArea,
	    EmptyState,
	    MutePanel,
	    JoinPanel
	  },
	  directives: {
	    'textarea-observer': {
	      mounted(element, binding) {
	        binding.instance.textareaResizeManager.observeTextarea(element);
	      },
	      beforeUnmount(element, binding) {
	        binding.instance.textareaResizeManager.unobserveTextarea(element);
	      }
	    }
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    },
	    contextMessageId: {
	      type: Number,
	      default: 0
	    }
	  },
	  data() {
	    return {
	      needSidebarTransition: false,
	      sidebarOpened: true,
	      searchSidebarOpened: false,
	      sidebarDetailBlock: null,
	      textareaHeight: 0,
	      showDropArea: false,
	      lastDropAreaEnterTarget: null
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.entityId, true);
	    },
	    hasPinnedMessages() {
	      return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId).length > 0;
	    },
	    canPost() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.send, this.dialog.dialogId);
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    sidebarTransitionName() {
	      return this.needSidebarTransition ? 'sidebar-transition' : '';
	    },
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    },
	    dialogContainerStyle() {
	      const TEXTAREA_PLACEHOLDER_HEIGHT = 50;
	      let textareaHeight = this.textareaHeight;
	      if (!this.canPost) {
	        textareaHeight = TEXTAREA_PLACEHOLDER_HEIGHT;
	      }
	      return {
	        height: `calc(100% - ${CHAT_HEADER_HEIGHT}px - ${textareaHeight}px)`
	      };
	    },
	    dropAreaStyles() {
	      const PINNED_MESSAGES_HEIGHT = 53;
	      const DROP_AREA_OFFSET = 16 + CHAT_HEADER_HEIGHT;
	      const dropAreaTopOffset = this.hasPinnedMessages ? PINNED_MESSAGES_HEIGHT + DROP_AREA_OFFSET : DROP_AREA_OFFSET;
	      return {
	        top: `${dropAreaTopOffset}px`
	      };
	    },
	    isSearchSidebarOpened() {
	      return this.sidebarDetailBlock === im_v2_const.SidebarDetailBlock.messageSearch;
	    }
	  },
	  watch: {
	    entityId(newValue, oldValue) {
	      im_v2_lib_logger.Logger.warn(`ChatContent: switching from ${oldValue || 'empty'} to ${newValue}`);
	      if (newValue === '') {
	        im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar, because entityId is empty');
	        this.sidebarOpened = false;
	      }
	      this.onChatChange();
	      this.resetSidebarDetailState();
	    },
	    sidebarOpened(newValue) {
	      this.saveSidebarOpenedState(newValue);
	    }
	  },
	  created() {
	    this.restoreSidebarOpenState();
	    if (this.entityId) {
	      this.onChatChange();
	    }
	    this.initTextareaResizeManager();
	  },
	  mounted() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.sidebar.open, this.onSidebarOpen);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.sidebar.close, this.onSidebarClose);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.sidebar.open, this.onSidebarOpen);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.sidebar.close, this.onSidebarClose);
	  },
	  methods: {
	    async onChatChange() {
	      if (this.entityId === '') {
	        return;
	      }
	      if (im_v2_lib_utils.Utils.dialog.isExternalId(this.entityId)) {
	        const realDialogId = await this.getChatService().prepareDialogId(this.entityId);
	        void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	          name: im_v2_const.Layout.chat.name,
	          entityId: realDialogId,
	          contextId: this.layout.contextId
	        });
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is already loaded`);
	        if (this.isUser) {
	          const userId = parseInt(this.dialog.dialogId, 10);
	          void this.getUserService().updateLastActivityDate(userId);
	        }
	        return;
	      }
	      if (this.dialog.loading) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loading`);
	        return;
	      }
	      if (this.layout.contextId) {
	        await this.loadChatWithContext();
	        return;
	      }
	      await this.loadChat();
	      this.needSidebarTransition = true;
	    },
	    loadChatWithContext() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);
	      return this.getChatService().loadChatWithContext(this.entityId, this.layout.contextId).then(() => {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
	      }).catch(error => {
	        this.handleChatLoadError(error);
	        im_v2_lib_logger.Logger.error(error);
	        im_public.Messenger.openChat();
	      });
	    },
	    loadChat() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.entityId}`);
	      return this.getChatService().loadChatWithMessages(this.entityId).then(() => {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loaded`);
	      }).catch(error => {
	        this.handleChatLoadError(error);
	        im_v2_lib_logger.Logger.error(error);
	        im_public.Messenger.openChat();
	      });
	    },
	    handleChatLoadError(error) {
	      const [firstError] = error;
	      if (firstError.code === 'ACCESS_DENIED') {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
	      } else if (firstError.code === 'MESSAGE_NOT_FOUND') {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
	      }
	    },
	    toggleSidebar() {
	      this.needSidebarTransition = true;
	      this.sidebarOpened = !this.sidebarOpened;
	      if (!this.sidebarOpened) {
	        im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar, because if was closed by toggle');
	      }
	      this.resetSidebarDetailState();
	    },
	    toggleSearchPanel() {
	      this.needSidebarTransition = true;
	      if (this.sidebarDetailBlock === im_v2_const.SidebarDetailBlock.messageSearch) {
	        this.sidebarDetailBlock = null;
	        this.sidebarOpened = false;
	        im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar, because message search was closed');
	      } else {
	        this.sidebarOpened = true;
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	          detailBlock: im_v2_const.SidebarDetailBlock.messageSearch
	        });
	      }
	    },
	    toggleMembersPanel() {
	      this.needSidebarTransition = true;
	      if (this.sidebarDetailBlock === im_v2_const.SidebarDetailBlock.main) {
	        this.sidebarDetailBlock = null;
	        this.sidebarOpened = false;
	        im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar, because chat members panel was closed');
	      } else {
	        this.sidebarOpened = true;
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	          detailBlock: im_v2_const.SidebarDetailBlock.main
	        });
	      }
	    },
	    onClickBack() {
	      this.resetSidebarDetailState();
	    },
	    onSidebarOpen({
	      data: eventData
	    }) {
	      this.sidebarOpened = true;
	      if (eventData.detailBlock && im_v2_const.SidebarDetailBlock[eventData.detailBlock]) {
	        this.sidebarDetailBlock = eventData.detailBlock;
	      }
	    },
	    onSidebarClose() {
	      im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar, because of sidebar close event');
	      this.sidebarOpened = false;
	    },
	    resetSidebarDetailState() {
	      this.sidebarDetailBlock = null;
	    },
	    restoreSidebarOpenState() {
	      const sidebarOpenState = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.sidebarOpened);
	      if (sidebarOpenState === null) {
	        return;
	      }
	      if (this.sidebarOpened && Boolean(sidebarOpenState)) {
	        im_v2_lib_logger.Logger.warn('ChatContent: closing sidebar after restoring state from LS');
	      }
	      this.sidebarOpened = Boolean(sidebarOpenState);
	    },
	    saveSidebarOpenedState(sidebarOpened) {
	      const WRITE_TO_STORAGE_TIMEOUT = 200;
	      clearTimeout(this.saveSidebarStateTimeout);
	      this.saveSidebarStateTimeout = setTimeout(() => {
	        im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.sidebarOpened, sidebarOpened);
	      }, WRITE_TO_STORAGE_TIMEOUT);
	    },
	    initTextareaResizeManager() {
	      this.textareaResizeManager = new im_v2_lib_textarea.ResizeManager();
	      this.textareaResizeManager.subscribe(im_v2_lib_textarea.ResizeManager.events.onHeightChange, event => {
	        const {
	          newHeight
	        } = event.getData();
	        this.textareaHeight = newHeight;
	      });
	    },
	    showNotification(text) {
	      BX.UI.Notification.Center.notify({
	        content: text
	      });
	    },
	    getUserService() {
	      if (!this.userService) {
	        this.userService = new UserService();
	      }
	      return this.userService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onDragEnter(event) {
	      void ui_uploader_core.hasDataTransferOnlyFiles(event.dataTransfer, false).then(success => {
	        if (!success) {
	          return;
	        }
	        this.lastDropAreaEnterTarget = event.target;
	        this.showDropArea = true;
	      });
	    },
	    onDragLeave(event) {
	      if (this.lastDropAreaEnterTarget === event.target) {
	        this.showDropArea = false;
	      }
	    },
	    onDrop(event) {
	      void ui_uploader_core.getFilesFromDataTransfer(event.dataTransfer).then(files => {
	        this.getUploadingService().addFilesFromInput(files, this.entityId);
	      });
	      this.showDropArea = false;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    getUploadingService() {
	      if (!this.uploadingService) {
	        this.uploadingService = im_v2_provider_service.UploadingService.getInstance();
	      }
	      return this.uploadingService;
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :class="containerClasses" :style="backgroundStyle">
			<div 
				class="bx-im-content-chat__content"
				@drop.prevent="onDrop"
				@dragleave.stop.prevent="onDragLeave"
				@dragenter.stop.prevent="onDragEnter"
				@dragover.prevent
			>
				<template v-if="entityId">
					<ChatHeader 
						:dialogId="entityId" 
						:key="entityId" 
						:sidebarOpened="sidebarOpened"
						:sidebarSearchOpened="isSearchSidebarOpened"
						@toggleRightPanel="toggleSidebar" 
						@toggleSearchPanel="toggleSearchPanel" 
						@toggleMembersPanel="toggleMembersPanel" 
					/>
					<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
						<div class="bx-im-content-chat__dialog_content">
							<ChatDialog :dialogId="entityId" :key="entityId" :textareaHeight="textareaHeight" />
						</div>
					</div>
					<!-- Textarea -->
					<div v-if="canPost" v-textarea-observer class="bx-im-content-chat__textarea_container">
						<ChatTextarea :dialogId="entityId" :key="entityId" />
					</div>
					<JoinPanel v-else-if="isGuest" :dialogId="entityId" />
					<MutePanel v-else :dialogId="entityId" />
					<!-- End textarea -->
					<DropArea :show="showDropArea" :style="dropAreaStyles" />
				</template>
				<EmptyState v-else />
			</div>
			<transition :name="sidebarTransitionName">
				<SidebarWrapper 
					v-if="entityId && sidebarOpened"
					:dialogId="entityId" 
					:sidebarDetailBlock="sidebarDetailBlock"
					@back="onClickBack"
				/>
			</transition>
		</div>
	`
	};

	exports.ChatContent = ChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.UI.Uploader,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=chat-content.bundle.js.map
