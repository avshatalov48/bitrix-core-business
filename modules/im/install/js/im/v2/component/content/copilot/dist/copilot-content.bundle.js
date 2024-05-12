/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_logger,im_v2_lib_theme,im_v2_lib_textarea,im_v2_component_sidebar,ui_notification,im_v2_component_entitySelector,im_public,im_v2_const,im_v2_provider_service,im_v2_lib_analytics,im_v2_lib_draft,im_v2_component_textarea,main_core,main_core_events,im_v2_lib_desktopApi,im_v2_component_dialog_chat,im_v2_component_messageList,im_v2_component_elements,ui_vue3) {
	'use strict';

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    currentSidebarPanel: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    userCounter() {
	      return main_core.Loc.getMessagePlural('IM_CONTENT_COPILOT_HEADER_USER_COUNT', this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isGroupCopilotChat() {
	      return this.dialog.userCounter > 2;
	    },
	    isAddToChatAvailable() {
	      const settings = main_core.Extension.getSettings('im.v2.component.content.copilot');
	      return settings.isAddToChatAvailable === 'Y';
	    }
	  },
	  methods: {
	    onNewTitleSubmit(newTitle) {
	      this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CONTENT_COPILOT_HEADER_RENAME_ERROR')
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
	    },
	    openAddToChatPopup() {
	      this.showAddToChatPopup = true;
	    },
	    onMembersClick() {
	      if (!this.isInited) {
	        return;
	      }
	      if (this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.members) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.members
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div class="bx-im-copilot-header__container">
			<div class="bx-im-copilot-header__left">
				<div class="bx-im-copilot-header__avatar">
					<div class="bx-im-copilot-header__avatar_default"></div>
				</div>
				<div class="bx-im-copilot-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div 
						v-if="isGroupCopilotChat"
						:title="loc('IM_CONTENT_COPILOT_HEADER_OPEN_MEMBERS_TITLE')"
						@click="onMembersClick"
						class="bx-im-copilot-header__subtitle --click"
					>
						{{ userCounter }}
					</div>
					<div v-else class="bx-im-copilot-header__subtitle">
						{{ loc('IM_CONTENT_COPILOT_HEADER_SUBTITLE') }}
					</div>
				</div>
			</div>
			<div class="bx-im-copilot-header__right">
				<div
					v-if="isAddToChatAvailable"
					:title="loc('IM_CONTENT_COPILOT_HEADER_OPEN_INVITE_POPUP_TITLE')"
					:class="{'--active': showAddToChatPopup}"
					class="bx-im-copilot-header__icon --add-users"
					@click="openAddToChatPopup"
					ref="add-users"
				></div>
			</div>
			<AddToChat
				:bindElement="$refs['add-users'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	const BUTTON_BACKGROUND_COLOR = '#fff';
	const BUTTON_HOVER_COLOR = '#eee';
	const BUTTON_TEXT_COLOR = 'rgba(82, 92, 105, 0.9)';

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  components: {
	    ChatButton: im_v2_component_elements.Button
	  },
	  data() {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    preparedText() {
	      return this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_MESSAGE', {
	        '#BR#': '\n'
	      });
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
	    async onButtonClick() {
	      this.isLoading = true;
	      const newDialogId = await this.getCopilotService().createChat().catch(() => {
	        this.isLoading = false;
	        this.showCreateChatError();
	      });
	      this.isLoading = false;
	      void im_public.Messenger.openCopilot(newDialogId);
	    },
	    showCreateChatError() {
	      BX.UI.Notification.Center.notify({
	        content: this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_ERROR_CREATING_CHAT')
	      });
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service.CopilotService();
	      }
	      return this.copilotService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-copilot-empty-state__container">
			<div class="bx-im-content-copilot-empty-state__content">
				<div class="bx-im-content-copilot-empty-state__icon"></div>
				<div class="bx-im-content-copilot-empty-state__text">{{ preparedText }}</div>
				<div class="bx-im-content-copilot-empty-state__button">
					<ChatButton
						class="--black-loader"
						:size="ButtonSize.XL"
						:customColorScheme="buttonColorScheme"
						:text="loc('IM_CONTENT_COPILOT_EMPTY_STATE_ASK_QUESTION')"
						:isRounded="true"
						:isLoading="isLoading"
						@click="onButtonClick"
					/>
				</div>
			</div>
		</div>
	`
	};

	const RecognizerEvent = {
	  audioend: 'audioend',
	  audiostart: 'audiostart',
	  end: 'end',
	  error: 'error',
	  nomatch: 'nomatch',
	  result: 'result',
	  soundend: 'soundend',
	  soundstart: 'soundstart',
	  speechend: 'speechend',
	  speechstart: 'speechstart',
	  start: 'start'
	};
	const EVENT_NAMESPACE = 'BX.Messenger.v2.CopilotAudioManager';
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getRecognizedText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecognizedText");
	var _getNewText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNewText");
	var _initSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initSettings");
	class AudioManager extends main_core_events.EventEmitter {
	  static isAvailable() {
	    if (im_v2_lib_desktopApi.DesktopApi.isDesktop()) {
	      return im_v2_lib_desktopApi.DesktopApi.getApiVersion() > 74;
	    }
	    return Boolean(window.SpeechRecognition || window.webkitSpeechRecognition);
	  }
	  constructor() {
	    super();
	    Object.defineProperty(this, _initSettings, {
	      value: _initSettings2
	    });
	    Object.defineProperty(this, _getNewText, {
	      value: _getNewText2
	    });
	    Object.defineProperty(this, _getRecognizedText, {
	      value: _getRecognizedText2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    this.recognizer = null;
	    this.setEventNamespace(EVENT_NAMESPACE);
	    this.recognizer = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
	    babelHelpers.classPrivateFieldLooseBase(this, _initSettings)[_initSettings]();
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  startRecognition() {
	    this.recognizer.start();
	  }
	  stopRecognition() {
	    this.recognizer.stop();
	  }
	}
	function _bindEvents2() {
	  main_core.Event.bind(this.recognizer, RecognizerEvent.start, () => {
	    this.lastRecognizedText = '';
	    this.emit(AudioManager.events.recognitionStart);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.error, event => {
	    this.emit(AudioManager.events.recognitionError, event.error);
	    // eslint-disable-next-line no-console
	    console.error('Copilot: AudioManager: error', event.error);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.end, () => {
	    this.lastRecognizedText = '';
	    this.emit(AudioManager.events.recognitionEnd);
	  });
	  main_core.Event.bind(this.recognizer, RecognizerEvent.result, event => {
	    const recognizedText = babelHelpers.classPrivateFieldLooseBase(this, _getRecognizedText)[_getRecognizedText](event);
	    const newText = babelHelpers.classPrivateFieldLooseBase(this, _getNewText)[_getNewText](recognizedText);
	    if (newText !== '') {
	      this.emit(AudioManager.events.recognitionResult, newText);
	    }
	    this.lastRecognizedText = recognizedText;
	  });
	}
	function _getRecognizedText2(event) {
	  let recognizedChunk = '';
	  Object.values(event.results).forEach(result => {
	    if (result.isFinal) {
	      return;
	    }
	    const [alternative] = result;
	    const {
	      transcript
	    } = alternative;
	    recognizedChunk += transcript;
	  });
	  return recognizedChunk;
	}
	function _getNewText2(fullText) {
	  let additionalText = '';
	  const lastChunkLength = this.lastRecognizedText.length;
	  if (fullText.length > lastChunkLength) {
	    additionalText = fullText.slice(lastChunkLength);
	  }
	  return additionalText;
	}
	function _initSettings2() {
	  this.recognizer.continuous = true;
	  this.recognizer.interimResults = true;
	}
	AudioManager.events = {
	  recognitionStart: 'recognitionStart',
	  recognitionError: 'recognitionError',
	  recognitionEnd: 'recognitionEnd',
	  recognitionResult: 'recognitionResult'
	};

	// @vue/component
	const AudioInput = {
	  name: 'AudioInput',
	  props: {
	    audioMode: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['start', 'stop', 'inputStart', 'inputResult', 'error'],
	  data() {
	    return {};
	  },
	  watch: {
	    audioMode(newValue, oldValue) {
	      if (oldValue === false && newValue === true) {
	        this.startAudio();
	      }
	      if (oldValue === true && newValue === false) {
	        this.stopAudio();
	      }
	    }
	  },
	  methods: {
	    onClick() {
	      if (this.audioMode) {
	        this.$emit('stop');
	        return;
	      }
	      this.$emit('start');
	    },
	    startAudio() {
	      this.getAudioManager().startRecognition();
	      this.bindAudioEvents();
	    },
	    stopAudio() {
	      this.getAudioManager().stopRecognition();
	      this.unbindAudioEvents();
	    },
	    bindAudioEvents() {
	      this.getAudioManager().subscribe(AudioManager.events.recognitionResult, event => {
	        const text = event.getData();
	        this.$emit('inputResult', text);
	      });
	      this.getAudioManager().subscribe(AudioManager.events.recognitionStart, () => {
	        this.$emit('inputStart');
	      });
	      this.getAudioManager().subscribe(AudioManager.events.recognitionError, () => {
	        this.$emit('error');
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CONTENT_COPILOT_TEXTAREA_AUDIO_INPUT_ERROR')
	        });
	      });
	    },
	    unbindAudioEvents() {
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionResult);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionStart);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionEnd);
	      this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionError);
	    },
	    isAudioModeAvailable() {
	      return AudioManager.isAvailable();
	    },
	    getAudioManager() {
	      if (!this.audioManager) {
	        this.audioManager = new AudioManager();
	      }
	      return this.audioManager;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			v-if="isAudioModeAvailable()"
			@click="onClick"
			class="bx-im-copilot-audio-input__container"
			:class="{'--active': audioMode}"
		></div>
	`
	};

	// noinspection JSUnresolvedReference
	// @vue/component
	const CopilotTextarea = ui_vue3.BitrixVue.cloneComponent(im_v2_component_textarea.ChatTextarea, {
	  name: 'CopilotTextarea',
	  components: {
	    AudioInput
	  },
	  data() {
	    return {
	      ...this.parentData(),
	      audioMode: false,
	      audioUsed: false
	    };
	  },
	  computed: {
	    isEmptyText() {
	      return this.text === '';
	    },
	    showMentionForCopilotChat() {
	      return this.showMention && this.dialog.userCounter > 2;
	    }
	  },
	  methods: {
	    onAudioInputStart() {
	      if (this.isEmptyText) {
	        return;
	      }
	      this.text += ' ';
	    },
	    onAudioInputResult(inputText) {
	      if (!this.audioMode) {
	        return;
	      }
	      this.text += inputText;
	      this.audioUsed = true;
	    },
	    onAudioError() {
	      this.audioMode = false;
	    },
	    openEditPanel() {},
	    getDraftManager() {
	      if (!this.draftManager) {
	        this.draftManager = im_v2_lib_draft.CopilotDraftManager.getInstance();
	      }
	      return this.draftManager;
	    },
	    sendMessage() {
	      this.parentSendMessage();
	      if (this.audioUsed) {
	        im_v2_lib_analytics.Analytics.getInstance().useAudioInput();
	        this.audioUsed = false;
	      }
	      this.audioMode = false;
	    }
	  },
	  template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container bx-im-copilot-send-panel__container">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<div class="bx-im-textarea__content" ref="textarea-content">
					<div class="bx-im-textarea__left">
						<textarea
							v-model="text"
							:style="textareaStyle"
							:placeholder="loc('IM_CONTENT_COPILOT_TEXTAREA_PLACEHOLDER')"
							:maxlength="textareaMaxLength"
							@keydown="onKeyDown"
							@paste="onPaste"
							class="bx-im-textarea__element"
							ref="textarea"
							rows="1"
						></textarea>
						<AudioInput
							:audioMode="audioMode"
							@start="audioMode = true"
							@stop="audioMode = false"
							@inputStart="onAudioInputStart"
							@inputResult="onAudioInputResult"
							@error="onAudioError"
						/>
					</div>
				</div>
			</div>
			<SendButton :editMode="editMode" :isDisabled="isDisabled" @click="sendMessage" />
			<MentionPopup
				v-if="showMentionForCopilotChat"
				:bindElement="$refs['textarea-content']"
				:dialogId="dialogId"
				:query="mentionQuery"
				:searchChats="false"
				@close="closeMentionPopup"
			/>
		</div>
	`
	});

	// @vue/component
	const CopilotDialogStatus = ui_vue3.BitrixVue.cloneComponent(im_v2_component_elements.DialogStatus, {
	  template: `
		<div class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
		</div>
	`
	});

	// @vue/component
	const CopilotMessageList = ui_vue3.BitrixVue.cloneComponent(im_v2_component_messageList.MessageList, {
	  name: 'CopilotMessageList',
	  components: {
	    CopilotDialogStatus
	  },
	  computed: {
	    statusComponent() {
	      return CopilotDialogStatus;
	    }
	  },
	  methods: {
	    onMessageContextMenuClick() {},
	    onAvatarClick(params) {
	      const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];
	      if (copilotUserId.toString() === params.dialogId) {
	        return;
	      }
	      // noinspection JSUnresolvedReference
	      this.parentOnAvatarClick(params);
	    }
	  }
	});

	// @vue/component
	const CopilotDialog = ui_vue3.BitrixVue.cloneComponent(im_v2_component_dialog_chat.ChatDialog, {
	  name: 'CopilotDialog',
	  computed: {
	    messageListComponent() {
	      return CopilotMessageList;
	    }
	  }
	});

	// @vue/component
	const CopilotContent = {
	  name: 'CopilotContent',
	  components: {
	    EmptyState,
	    ChatHeader,
	    CopilotDialog,
	    CopilotTextarea,
	    ChatSidebar: im_v2_component_sidebar.ChatSidebar
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
	      textareaHeight: 0,
	      currentSidebarPanel: ''
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.entityId, true);
	    },
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      const COPILOT_BACKGROUND_ID = 4;
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(COPILOT_BACKGROUND_ID);
	    },
	    dialogContainerStyle() {
	      const CHAT_HEADER_HEIGHT = 64;
	      return {
	        height: `calc(100% - ${CHAT_HEADER_HEIGHT}px - ${this.textareaHeight}px)`
	      };
	    }
	  },
	  watch: {
	    entityId(newValue, oldValue) {
	      im_v2_lib_logger.Logger.warn(`CopilotContent: switching from ${oldValue || 'empty'} to ${newValue}`);
	      this.onChatChange();
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.members
	      });
	    }
	  },
	  created() {
	    if (this.entityId) {
	      this.onChatChange();
	    }
	    this.initTextareaResizeManager();
	  },
	  methods: {
	    async onChatChange() {
	      if (this.entityId === '') {
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is already loaded`);
	        im_v2_lib_analytics.Analytics.getInstance().openCopilotChat(this.entityId);
	        return;
	      }
	      if (this.dialog.loading) {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loading`);
	        return;
	      }
	      if (this.layout.contextId) {
	        await this.loadChatWithContext();
	        return;
	      }
	      await this.loadChat();
	    },
	    loadChatWithContext() {
	      im_v2_lib_logger.Logger.warn(`CopilotContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);
	      return this.getChatService().loadChatWithContext(this.entityId, this.layout.contextId).then(() => {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
	      }).catch(error => {
	        if (error.code === 'ACCESS_ERROR') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
	        }
	        im_v2_lib_logger.Logger.error(error);
	        im_public.Messenger.openCopilot();
	      });
	    },
	    loadChat() {
	      im_v2_lib_logger.Logger.warn(`CopilotContent: loading chat ${this.entityId}`);
	      return this.getChatService().loadChatWithMessages(this.entityId).then(() => {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loaded`);
	        im_v2_lib_analytics.Analytics.getInstance().openCopilotChat(this.entityId);
	      }).catch(error => {
	        const [firstError] = error;
	        if (firstError.code === 'ACCESS_DENIED') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
	        }
	        im_public.Messenger.openCopilot();
	      });
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
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    onChangeSidebarPanel({
	      panel
	    }) {
	      this.currentSidebarPanel = panel;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__container bx-im-content-copilot__container" :class="containerClasses" :style="backgroundStyle">
			<div v-if="entityId" class="bx-im-content-copilot__content">
				<ChatHeader :dialogId="entityId" :key="entityId" :currentSidebarPanel="currentSidebarPanel" />
				<div :style="dialogContainerStyle" class="bx-im-content-copilot__dialog_container">
					<div class="bx-im-content-copilot__dialog_content">
						<CopilotDialog :dialogId="entityId" :key="entityId" :textareaHeight="textareaHeight" />
					</div>
				</div>
				<div v-textarea-observer class="bx-im-content-copilot__textarea_container">
					<CopilotTextarea :dialogId="entityId" :key="entityId" />
				</div>
			</div>
			<EmptyState v-else />
			<ChatSidebar
				v-if="entityId.length > 0"
				:originDialogId="entityId"
				@changePanel="onChangeSidebarPanel"
			/>
		</div>
	`
	};

	exports.CopilotContent = CopilotContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Component.Elements,BX.Vue3));
//# sourceMappingURL=copilot-content.bundle.js.map
