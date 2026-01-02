/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_provider_pull,im_v2_css_classes,im_v2_css_icons,im_v2_css_tokens,im_v2_lib_logger,im_v2_provider_service_chat,im_v2_lib_theme,main_core_events,im_v2_component_textarea,im_v2_component_dialog_chat,im_v2_lib_messageNotifier,im_v2_component_elements_loader,im_v2_component_animation,im_v2_component_content_elements,im_v2_const,im_v2_lib_demo) {
	'use strict';

	// @vue/component
	const TaskChatHeader = {
	  name: 'TaskChatHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    UserCounter: im_v2_component_content_elements.UserCounter,
	    LineLoader: im_v2_component_elements_loader.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<ChatHeader
			:dialogId="dialogId"
			:withAddToChatButton="false"
		>
			<template #left>
				<div class="bx-im-task-chat-header__left_container">
					<div class="bx-im-task-chat-header__avatar"></div>
					<div class="bx-im-task-chat-header__info">
						<div class="bx-im-task-chat-header__title">{{ loc('IM_TASK_CHAT_HEADER_TITLE') }}</div>
						<LineLoader v-if="!isInited" :width="50" :height="16" />
						<FadeAnimation :duration="100">
							<UserCounter v-if="isInited" :dialogId="dialogId" />
						</FadeAnimation>
					</div>
				</div>
			</template>
		</ChatHeader>
	`
	};

	// @vue/component
	const TaskChatContent = {
	  name: 'TaskChatContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    TaskChatHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	  },
	  methods: {
	    onBeforeNotificationShow(event) {
	      const eventData = event.getData();
	      if (eventData.dialogId !== this.dialogId) {
	        return im_v2_lib_messageNotifier.NotifierShowMessageAction.show;
	      }
	      return im_v2_lib_messageNotifier.NotifierShowMessageAction.skip;
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" :withSidebar="withSidebar">
			<template #header>
				<TaskChatHeader :dialogId="dialogId" />
			</template>
			<template #dialog>
				<ChatDialog
					:dialogId="dialogId"
					:key="dialogId"
					:reloadOnExit="false"
				/>
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChatTextarea
					:dialogId="dialogId"
					:key="dialogId"
					:withMarket="false"
					:withAutoFocus="false"
					@mounted="onTextareaMount"
				/>
			</template>
		</BaseChatContent>
	`
	};

	const initDemoState = () => {
	  const chatBuilder = new im_v2_lib_demo.DemoChatBuilder();
	  const {
	    dialogId
	  } = chatBuilder.addChat();
	  chatBuilder.addMessage({
	    text: dialogId,
	    componentId: im_v2_const.MessageComponent.taskChatCreationMessage
	  });
	  chatBuilder.save();
	  return dialogId;
	};

	// @vue/component
	const TaskChatPlaceholder = {
	  name: 'TaskChatPlaceholder',
	  components: {
	    TaskChatContent
	  },
	  props: {
	    taskId: {
	      type: [Number, String],
	      required: true
	    }
	  },
	  data() {
	    return {
	      fakeDialogId: '',
	      chatMembersCount: 1
	    };
	  },
	  computed: {
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    }
	  },
	  created() {
	    this.fakeDialogId = initDemoState();
	    this.bindEvents();
	  },
	  beforeUnmount() {
	    this.unbindEvents();
	  },
	  methods: {
	    bindEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.task.onMembersCountChange, this.onMembersCountChange);
	    },
	    unbindEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.task.onMembersCountChange, this.onMembersCountChange);
	    },
	    onMembersCountChange(event) {
	      const {
	        taskId,
	        userCounter
	      } = event.getData();
	      if (taskId !== this.taskId) {
	        return;
	      }
	      this.$store.dispatch('chats/update', {
	        dialogId: this.fakeDialogId,
	        fields: {
	          userCounter
	        }
	      });
	    }
	  },
	  template: `
		<div class="bx-im-task-chat-placeholder__container bx-im-messenger__scope">
			<TaskChatContent :dialogId="fakeDialogId" :withSidebar="false" />
			<div class="bx-im-task-chat-placeholder__overlay"></div>
		</div>
	`
	};

	// @vue/component
	const TaskChatOpener = {
	  name: 'TaskChatOpener',
	  components: {
	    TaskChatContent,
	    TaskChatPlaceholder
	  },
	  props: {
	    chatId: {
	      type: Number,
	      required: true
	    },
	    chatType: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/getByChatId'](this.chatId, true);
	    },
	    dialogId() {
	      return this.dialog.dialogId;
	    }
	  },
	  created() {
	    return this.onChatOpen();
	  },
	  methods: {
	    async onChatOpen() {
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`TaskChatOpener: chat ${this.chatId} is already loaded`);
	        return;
	      }
	      await this.loadChat();
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`TaskChatOpener: loading chat ${this.chatId}`);
	      await this.getChatService().loadChatByChatId(this.chatId);
	      im_v2_lib_logger.Logger.warn(`TaskChatOpener: chat ${this.chatId} is loaded`);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    }
	  },
	  template: `
		<div class="bx-im-messenger__scope bx-im-task-chat-opener__container">
			<TaskChatContent :dialogId="dialogId" />
		</div>
	`
	};

	const APP_NAME = 'TaskChatApplication';
	const PLACEHOLDER_APP_NAME = 'TaskChatPlaceholderApplication';
	var _initPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPromise");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _initPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPull");
	class TaskApplication {
	  constructor() {
	    Object.defineProperty(this, _initPull, {
	      value: _initPull2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _initPromise, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise] = babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initPull)[_initPull]();
	  }
	  ready() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise];
	  }
	  async mount(payload) {
	    await this.ready();
	    const {
	      rootContainer,
	      chatId,
	      onError,
	      type
	    } = payload;
	    if (!rootContainer) {
	      return Promise.reject(new Error('Provide node or selector for root container'));
	    }
	    if (!type) {
	      return Promise.reject(new Error('Provide custom chat type name for task chat'));
	    }
	    const preparedChatType = main_core.Text.toCamelCase(type);
	    return im_v2_application_core.Core.createVue(this, {
	      name: APP_NAME,
	      el: rootContainer,
	      onError,
	      components: {
	        TaskChatOpener
	      },
	      template: `<TaskChatOpener :chatId="${chatId}" chatType="${preparedChatType}" />`
	    });
	  }
	  async mountPlaceholder(payload) {
	    await this.ready();
	    const {
	      rootContainer,
	      taskId
	    } = payload;
	    if (!rootContainer) {
	      return Promise.reject(new Error('Provide node or selector for root container'));
	    }
	    return im_v2_application_core.Core.createVue(this, {
	      name: PLACEHOLDER_APP_NAME,
	      el: rootContainer,
	      components: {
	        TaskChatPlaceholder
	      },
	      template: `<TaskChatPlaceholder :taskId="${taskId}" />`
	    });
	  }
	}
	async function _init2() {
	  await im_v2_application_core.Core.ready();
	  return this;
	}
	async function _initPull2() {
	  await this.ready();
	  im_v2_application_core.Core.getPullClient().subscribe(new im_v2_provider_pull.SidebarPullHandler());
	}

	exports.TaskApplication = TaskApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Provider.Pull,BX.Messenger.v2.Css,BX.Messenger.v2.Css,BX.Messenger.v2.Css,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Component,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=task.bundle.js.map
