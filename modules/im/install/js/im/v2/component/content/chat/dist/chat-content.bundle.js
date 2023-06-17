this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_logger,im_v2_lib_localStorage,im_v2_lib_theme,im_v2_provider_service,im_v2_component_entitySelector,im_v2_lib_utils,im_v2_lib_call,im_public,im_v2_component_elements,im_v2_const,im_v2_component_sidebar,main_core,main_core_events) {
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
	      if (this.dialog.extranet) {
	        return false;
	      }
	      return this.$store.getters['dialogues/getChatOption'](this.dialog.type, im_v2_const.ChatOption.rename);
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
	        this.$refs['titleInput'].focus();
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

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    EditableChatTitle,
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    sidebarOpened: {
	      type: Boolean,
	      required: true
	    }
	  },
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
	    chatCanBeCalled() {
	      return im_v2_lib_call.CallManager.getInstance().chatCanBeCalled(this.dialog.dialogId);
	    }
	  },
	  methods: {
	    toggleRightPanel() {
	      this.$emit('toggleRightPanel');
	    },
	    onMembersClick() {
	      if (this.isUser || !this.isInited) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        detailBlock: im_v2_const.SidebarDetailBlock.main
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
	    openInvitePopup() {
	      this.showAddToChatPopup = true;
	    },
	    startVideoCall() {
	      if (!this.chatCanBeCalled) {
	        return;
	      }
	      im_public.Messenger.startVideoCall(this.dialog.dialogId);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<div class="bx-im-chat-header__avatar">
					<Avatar v-if="isChat" :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					<a v-else :href="userLink" target="_blank">
						<Avatar :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					</a>
				</div>
				<div v-if="isChat" class="bx-im-chat-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div :title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')" @click="onMembersClick" class="bx-im-chat-header__subtitle --click" >
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
				<div class="bx-im-chat-header__button" :class="{'--disabled': !chatCanBeCalled}" @click="startVideoCall">
					{{ loc('IM_CONTENT_CHAT_HEADER_VIDEOCALL_HD') }}
				</div>
				<div 
					class="bx-im-chat-header__icon --add-people"
					:class="{'--active': showAddToChatPopup}"
					@click="openInvitePopup" 
					ref="add-members"
				></div>
				<!--<div class="bx-im-chat-header__icon --search"></div>-->
				<div @click="toggleRightPanel" class="bx-im-chat-header__icon --panel" :class="{'--active': sidebarOpened}"></div>
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

	const CHAT_HEADER_HEIGHT = 64;

	// @vue/component
	const ChatContent = {
	  name: 'ChatContent',
	  components: {
	    ChatHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    SidebarWrapper
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
	      sidebarDetailBlock: null,
	      textareaHeight: 0
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.entityId, true);
	    },
	    sidebarTransitionName() {
	      return this.needSidebarTransition ? 'sidebar-transition' : '';
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    },
	    dialogContainerStyle() {
	      return {
	        height: `calc(100% - ${CHAT_HEADER_HEIGHT}px - ${this.textareaHeight}px)`
	      };
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
	    this.chatService = new im_v2_provider_service.ChatService();
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
	    onChatChange() {
	      if (this.entityId === '') {
	        return true;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is already loaded`);
	        return true;
	      }
	      if (this.dialog.loading) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loading`);
	        return true;
	      }
	      if (this.layout.contextId) {
	        this.loadChatWithContext();
	        return;
	      }
	      this.loadChat().then(() => {
	        this.needSidebarTransition = true;
	      });
	    },
	    loadChatWithContext() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);
	      this.chatService.loadChatWithContext(this.entityId, this.layout.contextId).then(() => {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
	      }).catch(error => {
	        if (error.code === 'ACCESS_ERROR') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
	        }
	        console.error(error);
	        im_public.Messenger.openChat();
	      });
	    },
	    loadChat() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.entityId}`);
	      return this.chatService.loadChatWithMessages(this.entityId).then(() => {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.entityId} is loaded`);
	      }).catch(error => {
	        console.error(error);
	        im_public.Messenger.openChat();
	      });
	    },
	    toggleSidebar() {
	      this.needSidebarTransition = true;
	      this.sidebarOpened = !this.sidebarOpened;
	      this.resetSidebarDetailState();
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
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :style="backgroundStyle">
			<div class="bx-im-content-chat__content">
				<template v-if="entityId">
					<ChatHeader 
						:dialogId="entityId" 
						:key="entityId" 
						:sidebarOpened="sidebarOpened"
						@toggleRightPanel="toggleSidebar" 
					/>
					<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
						<div class="bx-im-content-chat__dialog_content">
							<ChatDialog :dialogId="entityId" :key="entityId" :textareaHeight="textareaHeight" />
						</div>
					</div>
					<div v-textarea-observer class="bx-im-content-chat__textarea_container">
						<ChatTextarea :dialogId="entityId" :key="entityId" />
					</div>
				</template>
				<div v-else class="bx-im-content-chat__start_message">
					<div class="bx-im-content-chat__start_message_icon"></div>
					<div class="bx-im-content-chat__start_message_text">
					  {{ loc('IM_CONTENT_CHAT_START_MESSAGE') }}
					</div>
				</div>
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

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Component,BX,BX.Event));
//# sourceMappingURL=chat-content.bundle.js.map
