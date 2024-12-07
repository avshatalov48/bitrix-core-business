/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,im_v2_component_entitySelector,im_v2_lib_localStorage,im_v2_lib_menu,im_v2_lib_rest,im_v2_lib_feature,im_v2_lib_analytics,im_public,im_v2_lib_call,ui_vue3_directives_hint,im_v2_component_animation,im_v2_lib_utils,ui_vue3,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_theme,im_v2_lib_permission,im_v2_lib_textarea,im_v2_component_sidebar,ui_uploader_core,main_core,main_core_events,im_v2_lib_channel,im_v2_application_core,im_v2_const,im_v2_component_elements,im_v2_provider_service) {
	'use strict';

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
	var _analyticsOnStartCallClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analyticsOnStartCallClick");
	var _getPersonalPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPersonalPhoneItem");
	var _getWorkPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWorkPhoneItem");
	var _getInnerPhoneItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getInnerPhoneItem");
	var _getZoomItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getZoomItem");
	var _getUserPhoneHtml = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserPhoneHtml");
	var _isCallAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCallAvailable");
	var _getUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUser");
	var _isUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUser");
	var _requestCreateZoomConference = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestCreateZoomConference");
	class CallMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _requestCreateZoomConference, {
	      value: _requestCreateZoomConference2
	    });
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
	    Object.defineProperty(this, _getZoomItem, {
	      value: _getZoomItem2
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
	    Object.defineProperty(this, _analyticsOnStartCallClick, {
	      value: _analyticsOnStartCallClick2
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
	    return [babelHelpers.classPrivateFieldLooseBase(this, _getVideoCallItem)[_getVideoCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getAudioCallItem)[_getAudioCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getZoomItem)[_getZoomItem](), babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter](), babelHelpers.classPrivateFieldLooseBase(this, _getPersonalPhoneItem)[_getPersonalPhoneItem](), babelHelpers.classPrivateFieldLooseBase(this, _getWorkPhoneItem)[_getWorkPhoneItem](), babelHelpers.classPrivateFieldLooseBase(this, _getInnerPhoneItem)[_getInnerPhoneItem]()];
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
	      babelHelpers.classPrivateFieldLooseBase(this, _analyticsOnStartCallClick)[_analyticsOnStartCallClick](CallTypes.video.id);
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
	      babelHelpers.classPrivateFieldLooseBase(this, _analyticsOnStartCallClick)[_analyticsOnStartCallClick](CallTypes.audio.id);
	      CallTypes.audio.start(this.context.dialogId);
	      this.emit(CallMenu.events.onMenuItemClick, CallTypes.audio);
	      this.menuInstance.close();
	    },
	    disabled: !isAvailable
	  };
	}
	function _analyticsOnStartCallClick2(callType) {
	  im_v2_lib_analytics.Analytics.getInstance().onStartCallClick({
	    type: babelHelpers.classPrivateFieldLooseBase(this, _isUser)[_isUser]() ? im_v2_lib_analytics.Analytics.AnalyticsType.privateCall : im_v2_lib_analytics.Analytics.AnalyticsType.groupCall,
	    section: im_v2_lib_analytics.Analytics.AnalyticsSection.chatWindow,
	    subSection: im_v2_lib_analytics.Analytics.AnalyticsSubSection.contextMenu,
	    element: callType === CallTypes.video.id ? im_v2_lib_analytics.Analytics.AnalyticsElement.videocall : im_v2_lib_analytics.Analytics.AnalyticsElement.audiocall,
	    chatId: this.context.chatId
	  });
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
	  const title = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_INNER_PHONE_MSGVER_1');
	  return {
	    className: 'menu-popup-no-icon bx-im-chat-header-call-button-menu__item',
	    html: babelHelpers.classPrivateFieldLooseBase(this, _getUserPhoneHtml)[_getUserPhoneHtml](title, phones.innerPhone),
	    onclick: () => {
	      im_public.Messenger.startPhoneCall(phones.innerPhone);
	      this.menuInstance.close();
	    }
	  };
	}
	function _getZoomItem2() {
	  const isActive = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.zoomActive);
	  if (!isActive) {
	    return null;
	  }
	  const classNames = ['bx-im-chat-header-call-button-menu__zoom', 'menu-popup-no-icon'];
	  const isFeatureAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.zoomAvailable);
	  if (!isFeatureAvailable) {
	    classNames.push('--disabled');
	  }
	  return {
	    className: classNames.join(' '),
	    text: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM'),
	    onclick: () => {
	      if (!isFeatureAvailable) {
	        BX.UI.InfoHelper.show('limit_video_conference_zoom');
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _requestCreateZoomConference)[_requestCreateZoomConference](this.context.dialogId);
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
	function _requestCreateZoomConference2(dialogId) {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2CallZoomCreate, {
	    data: {
	      dialogId
	    }
	  }).catch(errors => {
	    let errorText = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM_CREATE_ERROR');
	    const notConnected = errors.some(error => error.code === 'ZOOM_CONNECTED_ERROR');
	    if (notConnected) {
	      const userProfileUri = `/company/personal/user/${im_v2_application_core.Core.getUserId()}/social_services/`;
	      errorText = main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_ZOOM_CONNECT_ERROR').replace('#HREF_START#', `<a href=${userProfileUri}>`).replace('#HREF_END#', '</>');
	    }
	    BX.UI.Notification.Center.notify({
	      content: errorText
	    });
	  });
	}
	CallMenu.events = {
	  onMenuItemClick: 'onMenuItemClick'
	};

	// @vue/component
	const CallButton = {
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
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
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isConference() {
	      return this.dialog.type === im_v2_const.ChatType.videoconf;
	    },
	    callButtonText() {
	      const locCode = CallTypes[this.lastCallType].locCode;
	      return this.loc(locCode);
	    },
	    hasActiveCurrentCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasActiveCurrentCall(this.dialogId);
	    },
	    hasActiveAnotherCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasActiveAnotherCall(this.dialogId);
	    },
	    isActive() {
	      if (this.hasActiveCurrentCall) {
	        return true;
	      }
	      if (this.hasActiveAnotherCall) {
	        return false;
	      }
	      return im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialogId);
	    },
	    userLimit() {
	      return im_v2_lib_call.CallManager.getInstance().getCallUserLimit();
	    },
	    isChatUserLimitExceeded() {
	      return im_v2_lib_call.CallManager.getInstance().isChatUserLimitExceeded(this.dialogId);
	    },
	    hintContent() {
	      if (this.isChatUserLimitExceeded) {
	        return {
	          text: this.loc('IM_LIB_CALL_USER_LIMIT_EXCEEDED_TOOLTIP', {
	            '#USER_LIMIT#': this.userLimit
	          }),
	          popupOptions: {
	            bindOptions: {
	              position: 'bottom'
	            },
	            angle: {
	              position: 'top'
	            },
	            targetContainer: document.body,
	            offsetLeft: 63,
	            offsetTop: 0
	          }
	        };
	      }
	      return null;
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
	      im_v2_lib_analytics.Analytics.getInstance().onStartCallClick({
	        type: this.dialog.type === im_v2_const.ChatType.user ? im_v2_lib_analytics.Analytics.AnalyticsType.privateCall : im_v2_lib_analytics.Analytics.AnalyticsType.groupCall,
	        section: im_v2_lib_analytics.Analytics.AnalyticsSection.chatWindow,
	        subSection: im_v2_lib_analytics.Analytics.AnalyticsSubSection.window,
	        element: this.lastCallType === CallTypes.video.id ? im_v2_lib_analytics.Analytics.AnalyticsElement.videocall : im_v2_lib_analytics.Analytics.AnalyticsElement.audiocall,
	        chatId: this.chatId
	      });
	      CallTypes[this.lastCallType].start(this.dialogId);
	    },
	    onMenuClick() {
	      if (!this.shouldShowMenu()) {
	        return;
	      }
	      this.getCallMenu().openMenu(this.dialog, this.$refs.menu);
	    },
	    onStartConferenceClick() {
	      if (!this.isActive) {
	        return;
	      }
	      im_v2_lib_analytics.Analytics.getInstance().onStartConferenceClick({
	        element: im_v2_lib_analytics.Analytics.AnalyticsElement.startButton,
	        chatId: this.chatId
	      });
	      im_public.Messenger.openConference({
	        code: this.dialog.public.code
	      });
	    },
	    getLastCallChoice() {
	      const result = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.lastCallType, CallTypes.video.id);
	      if (result === CallTypes.beta.id) {
	        return CallTypes.video.id;
	      }
	      return result;
	    },
	    saveLastCallChoice(callTypeId) {
	      this.lastCallType = callTypeId;
	      im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.lastCallType, callTypeId);
	    },
	    shouldShowMenu() {
	      return this.isActive;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div
			v-if="isConference"
			class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container --conference"
			:class="{'--disabled': !isActive}"
			@click="onStartConferenceClick"
		>
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_START_CONFERENCE') }}
			</div>
		</div>
		<div
			v-else
			class="bx-im-chat-header-call-button__scope bx-im-chat-header-call-button__container"
			:class="{'--disabled': !isActive}"
			v-hint="hintContent"
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

	const ParamsByLinkType = {
	  [im_v2_const.ChatEntityLinkType.tasks]: {
	    className: '--task',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_TASK')
	  },
	  [im_v2_const.ChatEntityLinkType.calendar]: {
	    className: '--calendar',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MEETING_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.sonetGroup]: {
	    className: '--group',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_GROUP_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.mail]: {
	    className: '--mail',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MAIL_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.contact]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CONTACT')
	  },
	  [im_v2_const.ChatEntityLinkType.deal]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DEAL')
	  },
	  [im_v2_const.ChatEntityLinkType.lead]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_LEAD')
	  },
	  [im_v2_const.ChatEntityLinkType.dynamic]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DYNAMIC_ELEMENT')
	  }
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
	    entityType() {
	      return this.dialog.entityLink.type;
	    },
	    entityUrl() {
	      return this.dialog.entityLink.url;
	    },
	    containerClassName() {
	      var _ParamsByLinkType$thi, _ParamsByLinkType$thi2;
	      return (_ParamsByLinkType$thi = (_ParamsByLinkType$thi2 = ParamsByLinkType[this.entityType]) == null ? void 0 : _ParamsByLinkType$thi2.className) != null ? _ParamsByLinkType$thi : '';
	    },
	    linkText() {
	      var _ParamsByLinkType$thi3, _ParamsByLinkType$thi4;
	      return (_ParamsByLinkType$thi3 = (_ParamsByLinkType$thi4 = ParamsByLinkType[this.entityType]) == null ? void 0 : _ParamsByLinkType$thi4.loc) != null ? _ParamsByLinkType$thi3 : 'Open entity';
	    }
	  },
	  template: `
		<a :href="entityUrl" class="bx-im-chat-header-entity-link__container" :class="containerClassName" target="_blank">
			<div class="bx-im-chat-header-entity-link__icon"></div>
			<div class="bx-im-chat-header-entity-link__text">{{ linkText }}</div>
			<div class="bx-im-chat-header-entity-link__arrow"></div>
		</a>
	`
	};

	const UserCounterPhraseCodeByChatType = {
	  [im_v2_const.ChatType.openChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  [im_v2_const.ChatType.channel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  [im_v2_const.ChatType.generalChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  default: 'IM_CONTENT_CHAT_HEADER_USER_COUNT'
	};

	// @vue/component
	const GroupChatTitle = {
	  name: 'GroupChatTitle',
	  components: {
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    EntityLink,
	    LineLoader: im_v2_component_elements.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation
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
	    hasEntityLink() {
	      var _this$dialog$entityLi;
	      return Boolean((_this$dialog$entityLi = this.dialog.entityLink) == null ? void 0 : _this$dialog$entityLi.url);
	    },
	    userCounterPhraseCode() {
	      var _UserCounterPhraseCod;
	      return (_UserCounterPhraseCod = UserCounterPhraseCodeByChatType[this.dialog.type]) != null ? _UserCounterPhraseCod : UserCounterPhraseCodeByChatType.default;
	    },
	    userCounterText() {
	      return main_core.Loc.getMessagePlural(this.userCounterPhraseCode, this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
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
			<LineLoader v-if="!dialog.inited" :width="50" :height="16" />
			<FadeAnimation :duration="100">
				<div v-if="dialog.inited" class="bx-im-chat-header__subtitle_container">
					<div
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
						@click="$emit('membersClick')"
						class="bx-im-chat-header__subtitle_content --click"
					>
						{{ userCounterText }}
					</div>
					<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
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
	const HeaderAvatar = {
	  name: 'HeaderAvatar',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['avatarClick'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    canChangeAvatar() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.avatar, this.dialogId);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  methods: {
	    onAvatarClick() {
	      if (this.isUser || !this.canChangeAvatar) {
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
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    }
	  },
	  // language=Vue
	  template: `
		<div class="bx-im-chat-header__avatar" :class="{'--can-change': canChangeAvatar}" @click="onAvatarClick">
			<a v-if="isUser" :href="userLink" target="_blank">
				<ChatAvatar :avatarDialogId="dialogId" :contextDialogId="dialogId" :size="AvatarSize.L" />
			</a>
			<ChatAvatar v-else :avatarDialogId="dialogId" :contextDialogId="dialogId" :size="AvatarSize.L" />
		</div>
		<input
			type="file"
			accept="image/*"
			class="bx-im-chat-header__avatar_input"
			ref="avatarInput"
			@change="onAvatarSelect"
		>
	`
	};

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    CallButton,
	    GroupChatTitle,
	    UserChatTitle: UserTitle,
	    LineLoader: im_v2_component_elements.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation,
	    HeaderAvatar
	  },
	  inject: ['currentSidebarPanel'],
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withCallButton: {
	      type: Boolean,
	      default: true
	    },
	    withSearchButton: {
	      type: Boolean,
	      default: true
	    },
	    withSidebarButton: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['toggleRightPanel', 'toggleSearchPanel', 'toggleMembersPanel', 'buttonPanelReady'],
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
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
	    showCallButton() {
	      if (this.isBot || !this.withCallButton) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.call, this.dialogId);
	    },
	    showInviteButton() {
	      if (this.isBot) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.extend, this.dialogId);
	    },
	    showSearchButton() {
	      return this.withSearchButton;
	    },
	    showSidebarButton() {
	      if (!this.withSidebarButton) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.openSidebar, this.dialogId);
	    },
	    isSidebarOpened() {
	      return main_core.Type.isStringFilled(this.currentSidebarPanel);
	    },
	    isMessageSearchActive() {
	      return this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.messageSearch;
	    },
	    isMembersPanelActive() {
	      return this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.members;
	    },
	    chatTitleComponent() {
	      return this.isUser ? UserTitle : GroupChatTitle;
	    }
	  },
	  methods: {
	    toggleRightPanel() {
	      if (this.isSidebarOpened) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: ''
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.main,
	        dialogId: this.dialogId
	      });
	    },
	    toggleSearchPanel() {
	      if (this.isMessageSearchActive) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.messageSearch
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.messageSearch,
	        dialogId: this.dialogId
	      });
	    },
	    onMembersClick() {
	      if (!this.isInited) {
	        return;
	      }
	      if (this.isMembersPanelActive) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.members
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
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
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<slot name="left">
					<HeaderAvatar :dialogId="dialogId" />
					<slot name="title" :onNewTitleHandler="onNewTitleSubmit">
						<component
							:is="chatTitleComponent"
							:dialogId="dialogId"
							@membersClick="onMembersClick"
							@newTitle="onNewTitleSubmit"
						/>
					</slot>
				</slot>
			</div>
			<LineLoader v-if="!isInited" :width="45" :height="22" />
			<FadeAnimation @afterEnter="$emit('buttonPanelReady')" :duration="100">
				<div v-if="isInited" class="bx-im-chat-header__right">
					<slot name="before-actions"></slot>
					<CallButton v-if="showCallButton" :dialogId="dialogId" />
					<div
						v-if="showInviteButton"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
						:class="{'--active': showAddToChatPopup}"
						class="bx-im-chat-header__icon --add-people"
						@click="showAddToChatPopup = true" 
						ref="add-members"
					>
						<slot name="invite-hint" :inviteButtonRef="$refs['add-members']"></slot>
					</div>
					<div
						v-if="showSearchButton"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SEARCH')"
						:class="{'--active': isMessageSearchActive}"
						class="bx-im-chat-header__icon --search" 
						@click="toggleSearchPanel"
					></div>
					<div
						v-if="showSidebarButton"
						class="bx-im-chat-header__icon --panel"
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SIDEBAR')"
						:class="{'--active': isSidebarOpened}"
						@click="toggleRightPanel" 
					></div>
				</div>
			</FadeAnimation>
			<AddToChat
				:bindElement="$refs['add-members'] ?? {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{ offsetTop: 15, offsetLeft: -300 }"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	const Height = {
	  chatHeader: 64,
	  pinnedMessages: 53,
	  blockedTextarea: 50,
	  dropAreaOffset: 16
	};

	// @vue/component
	const DropArea = {
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    container: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showDropArea: false,
	      lastDropAreaEnterTarget: null
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    hasPinnedMessages() {
	      return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId).length > 0;
	    },
	    dropAreaStyles() {
	      let offset = Height.dropAreaOffset + Height.chatHeader;
	      if (this.hasPinnedMessages) {
	        offset += Height.pinnedMessages;
	      }
	      return {
	        top: `${offset}px`
	      };
	    }
	  },
	  watch: {
	    container: {
	      immediate: true,
	      handler(newValue) {
	        if (!main_core.Type.isElementNode(newValue)) {
	          return;
	        }
	        this.bindEvents();
	      }
	    }
	  },
	  beforeUnmount() {
	    this.unbindEvents();
	  },
	  methods: {
	    bindEvents() {
	      main_core.Event.bind(this.container, 'dragenter', this.onDragEnter);
	      main_core.Event.bind(this.container, 'dragleave', this.onDragLeave);
	      main_core.Event.bind(this.container, 'dragover', this.onDragOver);
	      main_core.Event.bind(this.container, 'drop', this.onDrop);
	    },
	    unbindEvents() {
	      main_core.Event.unbind(this.container, 'dragenter', this.onDragEnter);
	      main_core.Event.unbind(this.container, 'dragleave', this.onDragLeave);
	      main_core.Event.unbind(this.container, 'dragover', this.onDragOver);
	      main_core.Event.unbind(this.container, 'drop', this.onDrop);
	    },
	    async onDragEnter(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      const success = await ui_uploader_core.hasDataTransferOnlyFiles(event.dataTransfer, false);
	      if (!success) {
	        return;
	      }
	      this.lastDropAreaEnterTarget = event.target;
	      this.showDropArea = true;
	    },
	    onDragLeave(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      if (this.lastDropAreaEnterTarget !== event.target) {
	        return;
	      }
	      this.showDropArea = false;
	    },
	    onDragOver(event) {
	      event.preventDefault();
	    },
	    async onDrop(event) {
	      event.preventDefault();
	      const isChannelType = im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	      const uploaderId = await this.getUploadingService().uploadFromDragAndDrop({
	        event,
	        dialogId: this.dialogId,
	        sendAsFile: false,
	        autoUpload: !isChannelType
	      });
	      if (main_core.Type.isStringFilled(uploaderId) && isChannelType) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.openUploadPreview, {
	          uploaderId
	        });
	      }
	      this.showDropArea = false;
	    },
	    getUploadingService() {
	      if (!this.uploadingService) {
	        this.uploadingService = im_v2_provider_service.UploadingService.getInstance();
	      }
	      return this.uploadingService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<Transition name="drop-area-fade">
			<div v-if="showDropArea" :style="dropAreaStyles" class="bx-im-content-chat-drop-area__container bx-im-content-chat-drop-area__scope">
				<div class="bx-im-content-chat-drop-area__box">
					<span class="bx-im-content-chat-drop-area__icon"></span>
					<label class="bx-im-content-chat-drop-area__label-text">
						{{ loc('IM_CONTENT_BASE_CHAT_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
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

	// @vue/component
	const LoadingBar = {
	  name: 'LoadingBar',
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-content-chat__loading-bar"></div>
	`
	};

	const TextareaObserverDirective = {
	  mounted(element, binding) {
	    binding.instance.textareaResizeManager.observeTextarea(element);
	  },
	  beforeUnmount(element, binding) {
	    binding.instance.textareaResizeManager.unobserveTextarea(element);
	  }
	};

	// @vue/component
	const BaseChatContent = {
	  name: 'BaseChatContent',
	  components: {
	    ChatHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    ChatSidebar: im_v2_component_sidebar.ChatSidebar,
	    DropArea,
	    MutePanel,
	    JoinPanel,
	    LoadingBar
	  },
	  directives: {
	    'textarea-observer': TextareaObserverDirective
	  },
	  provide() {
	    return {
	      currentSidebarPanel: ui_vue3.computed(() => this.currentSidebarPanel)
	    };
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    backgroundId: {
	      type: [Number, null],
	      default: null
	    }
	  },
	  data() {
	    return {
	      textareaHeight: 0,
	      showLoadingBar: false,
	      currentSidebarPanel: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    canSend() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.send, this.dialog.dialogId);
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    hasCommentsOnTop() {
	      return this.$store.getters['messages/comments/areOpenedForChannel'](this.dialogId);
	    },
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      if (this.backgroundId) {
	        return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(this.backgroundId);
	      }
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    },
	    dialogContainerStyle() {
	      let textareaHeight = this.textareaHeight;
	      if (!this.canSend) {
	        textareaHeight = Height.blockedTextarea;
	      }
	      return {
	        height: `calc(100% - ${Height.chatHeader}px - ${textareaHeight}px)`
	      };
	    }
	  },
	  watch: {
	    textareaHeight(newValue, oldValue) {
	      if (!this.dialog.inited || oldValue === 0) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	        chatId: this.dialog.chatId,
	        animation: false
	      });
	    }
	  },
	  created() {
	    this.initTextareaResizeManager();
	    this.bindEvents();
	  },
	  beforeUnmount() {
	    this.unbindEvents();
	  },
	  methods: {
	    initTextareaResizeManager() {
	      this.textareaResizeManager = new im_v2_lib_textarea.ResizeManager();
	      this.textareaResizeManager.subscribe(im_v2_lib_textarea.ResizeManager.events.onHeightChange, this.onTextareaHeightChange);
	    },
	    onTextareaMount() {
	      const textareaContainer = this.$refs['textarea-container'];
	      this.textareaHeight = textareaContainer.clientHeight;
	    },
	    onTextareaHeightChange(event) {
	      const {
	        newHeight
	      } = event.getData();
	      this.textareaHeight = newHeight;
	    },
	    onShowLoadingBar(event) {
	      const {
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.showLoadingBar = true;
	    },
	    onHideLoadingBar(event) {
	      const {
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.showLoadingBar = false;
	    },
	    onChangeSidebarPanel({
	      panel
	    }) {
	      this.currentSidebarPanel = panel;
	    },
	    bindEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.showLoadingBar, this.onShowLoadingBar);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
	    },
	    unbindEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.showLoadingBar, this.onShowLoadingBar);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :class="containerClasses" :style="backgroundStyle">
			<div class="bx-im-content-chat__content" ref="content">
				<slot name="header">
					<ChatHeader :dialogId="dialogId" :key="dialogId" />
				</slot>
				<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
					<Transition name="loading-bar-transition">
						<LoadingBar v-if="showLoadingBar" />
					</Transition>
					<div class="bx-im-content-chat__dialog_content">
						<slot name="dialog">
							<ChatDialog :dialogId="dialogId" :key="dialogId" />
						</slot>
					</div>
				</div>
				<!-- Textarea -->
				<div v-if="canSend" v-textarea-observer class="bx-im-content-chat__textarea_container" ref="textarea-container">
					<slot name="textarea" :onTextareaMount="onTextareaMount">
						<ChatTextarea 
							:dialogId="dialogId" 
							:key="dialogId" 
							:withAudioInput="false" 
							@mounted="onTextareaMount" 
						/>
					</slot>
				</div>
				<slot v-else-if="isGuest" name="join-panel">
					<JoinPanel :dialogId="dialogId" />
				</slot>
				<MutePanel v-else :dialogId="dialogId" />
				<!-- End textarea -->
				<DropArea :dialogId="dialogId" :container="$refs.content || {}" :key="dialogId" />
			</div>
			<ChatSidebar
				v-if="dialogId.length > 0" 
				:originDialogId="dialogId"
				:isActive="!hasCommentsOnTop"
				@changePanel="onChangeSidebarPanel"
			/>
		</div>
	`
	};

	exports.ChatHeader = ChatHeader;
	exports.BaseChatContent = BaseChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Lib,BX.Vue3,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX.UI.Uploader,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service));
//# sourceMappingURL=registry.bundle.js.map
