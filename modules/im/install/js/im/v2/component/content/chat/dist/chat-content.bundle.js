/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_uploader_core,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_logger,im_v2_lib_theme,im_v2_component_entitySelector,im_v2_lib_utils,im_v2_lib_permission,im_v2_lib_localStorage,im_v2_lib_menu,im_public,im_v2_lib_call,im_v2_component_sidebar,main_core,main_core_events,im_v2_application_core,im_v2_const,im_v2_component_elements,im_v2_provider_service) {
	'use strict';

	const INPUT_PADDING = 5;

	// @vue/component
	const EditableChatTitle = {
	  name: 'EditableChatTitle',
	  components: {
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['newTitleSubmit'],
	  data() {
	    return {
	      isEditing: false,
	      inputWidth: 0,
	      showEditIcon: false,
	      chatTitle: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    canBeRenamed() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.rename, this.dialogId);
	    },
	    inputStyle() {
	      return {
	        width: `calc(${this.inputWidth}ch + ${INPUT_PADDING}px)`
	      };
	    }
	  },
	  watch: {
	    chatTitle() {
	      this.inputWidth = this.chatTitle.length;
	    }
	  },
	  mounted() {
	    this.chatTitle = this.dialog.name;
	  },
	  methods: {
	    onTitleClick() {
	      if (!this.canBeRenamed) {
	        return;
	      }
	      if (!this.chatTitle) {
	        this.chatTitle = this.dialog.name;
	      }
	      this.isEditing = true;
	      this.$nextTick().then(() => {
	        this.$refs.titleInput.focus();
	      });
	    },
	    onNewTitleSubmit() {
	      if (!this.isEditing) {
	        return;
	      }
	      this.isEditing = false;
	      const nameNotChanged = this.chatTitle === this.dialog.name;
	      if (nameNotChanged || this.chatTitle === '') {
	        return;
	      }
	      this.$emit('newTitleSubmit', this.chatTitle);
	    },
	    onEditCancel() {
	      this.isEditing = false;
	      this.chatTitle = this.dialog.name;
	    }
	  },
	  template: `
		<div
			v-if="!isEditing"
			@click="onTitleClick"
			@mouseover="showEditIcon = true"
			@mouseleave="showEditIcon = false"
			class="bx-im-chat-header__title --chat"
			:class="{'--can-rename': canBeRenamed}"
		>
			<div class="bx-im-chat-header__title_container">
				<ChatTitle :dialogId="dialogId" :withMute="true" />
			</div>
			<div class="bx-im-chat-header__edit-icon_container">
				<div v-if="showEditIcon && canBeRenamed" class="bx-im-chat-header__edit-icon"></div>
			</div>
		</div>
		<div v-else class="bx-im-chat-header__title-input_container">
			<input
				v-model="chatTitle"
				:style="inputStyle"
				@focus="$event.target.select()"
				@blur="onNewTitleSubmit"
				@keyup.enter="onNewTitleSubmit"
				@keyup.esc="onEditCancel"
				type="text"
				class="bx-im-chat-header__title-input"
				ref="titleInput"
			/>
		</div>
	`
	};

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
	      const dialog = im_v2_application_core.Core.getStore().getters['dialogues/get'](dialogId);
	      im_v2_lib_call.CallManager.getInstance().createBetaCallRoom(dialog.chatId);
	    }
	  }
	};

	var _getVideoCallItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getVideoCallItem");
	var _getAudioCallItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAudioCallItem");
	var _getBetaCallItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBetaCallItem");
	var _isCallBetaAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCallBetaAvailable");
	class CallMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isCallBetaAvailable, {
	      value: _isCallBetaAvailable2
	    });
	    Object.defineProperty(this, _getBetaCallItem, {
	      value: _getBetaCallItem2
	    });
	    Object.defineProperty(this, _getAudioCallItem, {
	      value: _getAudioCallItem2
	    });
	    Object.defineProperty(this, _getVideoCallItem, {
	      value: _getVideoCallItem2
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
	  getMenuItems() {
	    return [babelHelpers.classPrivateFieldLooseBase(this, _getVideoCallItem)[_getVideoCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getAudioCallItem)[_getAudioCallItem](), babelHelpers.classPrivateFieldLooseBase(this, _getBetaCallItem)[_getBetaCallItem]()];
	  }
	}
	function _getVideoCallItem2() {
	  const isAvailable = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.context.dialogId);
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
	  const isAvailable = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.context.dialogId);
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
	function _getBetaCallItem2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isCallBetaAvailable)[_isCallBetaAvailable]()) {
	    return null;
	  }
	  return {
	    text: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_BETA_2'),
	    onclick: () => {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _isCallBetaAvailable)[_isCallBetaAvailable]()) {
	        return;
	      }
	      CallTypes.beta.start(this.context.dialogId);
	      this.emit(CallMenu.events.onMenuItemClick, CallTypes.beta);
	      this.menuInstance.close();
	    }
	  };
	}
	function _isCallBetaAvailable2() {
	  const settings = main_core.Extension.getSettings('im.v2.component.content.chat');
	  return settings.get('isCallBetaAvailable');
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isActive() {
	      const chatCanBeCalled = im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialogId);
	      const chatIsAllowedToCall = im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.call, this.dialogId);
	      return chatCanBeCalled && chatIsAllowedToCall;
	    },
	    isConference() {
	      return this.dialog.type === im_v2_const.DialogType.videoconf;
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
	      return im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.lastCallType, CallTypes.video.id);
	    },
	    saveLastCallChoice(callTypeId) {
	      this.lastCallType = callTypeId;
	      im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.lastCallType, callTypeId);
	    },
	    shouldShowMenu() {
	      return this.isActive || this.isCallBetaAvailable();
	    },
	    isCallBetaAvailable() {
	      const settings = main_core.Extension.getSettings('im.v2.component.content.chat');
	      return settings.get('isCallBetaAvailable');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div v-if="isConference" class="bx-im-chat-header-call-button__container --conference" @click="onStartConferenceClick">
			<div class="bx-im-chat-header-call-button__text">
				{{ loc('IM_CONTENT_CHAT_HEADER_START_CONFERENCE') }}
			</div>
		</div>
		<div
			v-else
			class="bx-im-chat-header-call-button__container"
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

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    EditableChatTitle,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    CallButton
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    avatarStyle() {
	      return {
	        backgroundImage: `url('${this.dialog.avatar}')`
	      };
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogDescription() {
	      if (this.isUser) {
	        return this.$store.getters['users/getPosition'](this.dialogId);
	      }
	      return main_core.Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    },
	    userLastOnline() {
	      return this.$store.getters['users/getLastOnline'](this.dialogId);
	    },
	    showInviteButton() {
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
	      if (this.isUser || !this.isInited) {
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
				<div v-if="isChat" class="bx-im-chat-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div 
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')" 
						@click="onMembersClick" 
						class="bx-im-chat-header__subtitle --click"
					>
						{{ dialogDescription }}
					</div>
				</div>
				<div v-else class="bx-im-chat-header__info">
					<div class="bx-im-chat-header__title --user">
						<a :href="userLink" target="_blank" class="bx-im-chat-header__title_container">
							<ChatTitle :dialogId="dialogId" />
						</a>
						<span class="bx-im-chat-header__user-status">{{ userLastOnline }}</span>
					</div>
					<div class="bx-im-chat-header__subtitle">{{ dialogDescription }}</div>
				</div>
			</div>
			<div class="bx-im-chat-header__right">
				<CallButton :dialogId="dialogId" />
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

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Content.Chat.ResizeManager';
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _textareaHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textareaHeight");
	var _initObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initObserver");
	class ResizeManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _initObserver, {
	      value: _initObserver2
	    });
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _textareaHeight, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace(EVENT_NAMESPACE);
	    babelHelpers.classPrivateFieldLooseBase(this, _initObserver)[_initObserver]();
	  }
	  observeTextarea(element) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(element);
	    babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = element.clientHeight;
	  }
	  unobserveTextarea(element) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(element);
	    babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = 0;
	  }
	}
	function _initObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new ResizeObserver(entries => {
	    entries.forEach(entry => {
	      var _entry$borderBoxSize;
	      const height = (_entry$borderBoxSize = entry.borderBoxSize) == null ? void 0 : _entry$borderBoxSize[0].blockSize;
	      if (main_core.Type.isNumber(height) && height !== babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight]) {
	        this.emit(ResizeManager.events.onHeightChange, {
	          newHeight: height
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _textareaHeight)[_textareaHeight] = height;
	      }
	    });
	  });
	}
	ResizeManager.events = {
	  onHeightChange: 'onHeightChange'
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
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__start_message">
			<div class="bx-im-content-chat__start_message_icon"></div>
			<div class="bx-im-content-chat__start_message_text">
				{{ loc('IM_CONTENT_CHAT_START_MESSAGE') }}
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
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
	      sidebarOpened: false,
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
	      return this.$store.getters['dialogues/get'](this.entityId, true);
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
	        this.$store.dispatch('application/setLayout', {
	          layoutName: im_v2_const.Layout.chat.name,
	          entityId: realDialogId,
	          contextId: this.layout.contextId
	        });
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is already loaded`);
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
	        if (error.code === 'ACCESS_ERROR') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
	        }
	        im_v2_lib_logger.Logger.error(error);
	        im_public.Messenger.openChat();
	      });
	    },
	    loadChat() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.entityId}`);
	      return this.getChatService().loadChatWithMessages(this.entityId).then(() => {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loaded`);
	      }).catch(() => {
	        im_public.Messenger.openChat();
	      });
	    },
	    toggleSidebar() {
	      this.needSidebarTransition = true;
	      this.sidebarOpened = !this.sidebarOpened;
	      this.resetSidebarDetailState();
	    },
	    toggleSearchPanel() {
	      this.needSidebarTransition = true;
	      if (this.sidebarDetailBlock === im_v2_const.SidebarDetailBlock.messageSearch) {
	        this.sidebarDetailBlock = null;
	        this.sidebarOpened = false;
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
	      this.sidebarOpened = false;
	    },
	    resetSidebarDetailState() {
	      this.sidebarDetailBlock = null;
	    },
	    restoreSidebarOpenState() {
	      const sidebarOpenState = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.sidebarOpened);
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
	      this.textareaResizeManager = new ResizeManager();
	      this.textareaResizeManager.subscribe(ResizeManager.events.onHeightChange, event => {
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

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.UI.Uploader,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=chat-content.bundle.js.map
