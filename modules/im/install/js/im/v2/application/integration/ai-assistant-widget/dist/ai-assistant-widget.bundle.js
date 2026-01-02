/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_css_classes,im_v2_lib_logger,im_v2_provider_service_chat,im_v2_component_content_chat,im_v2_lib_messageNotifier,ui_iconSet_api_vue,main_core_events,im_v2_component_content_elements,im_v2_const) {
	'use strict';

	const MINIMIZE_EVENT_NAME = 'IM.AiAssistantWidget:minimize';

	// @vue/component
	const AiAssistantWidgetChatHeader = {
	  name: 'AiAssistantWidgetChatHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    Color: () => im_v2_const.Color
	  },
	  methods: {
	    onMinimize() {
	      main_core_events.EventEmitter.emit(MINIMIZE_EVENT_NAME);
	    },
	    loc(phrase) {
	      return this.$Bitrix.Loc.getMessage(phrase);
	    }
	  },
	  template: `
		<ChatHeader
			:dialogId="dialogId"
			:withCallButton="false"
			:withSearchButton="false"
			:withAddToChatButton="false"
		>
			<template #before-actions>
				<BIcon
					:name="OutlineIcons.CROSS_L"
					:hoverable="true"
					:color="Color.base4"
					:title="loc('IM_AI_ASSISTANT_WIDGET_MINIMIZE')"
					class="bx-im-ai-assistant-chat-header__icon"
					@click="onMinimize"
				/>
			</template>
		</ChatHeader>
	`
	};

	// @vue/component
	const AiAssistantWidgetChatContent = {
	  name: 'AiAssistantWidgetChatContent',
	  components: {
	    AiAssistantWidgetChatHeader,
	    AiAssistantBotContent: im_v2_component_content_chat.AiAssistantBotContent
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
		<AiAssistantBotContent :dialogId="dialogId" :withSidebar="withSidebar">
			<template #header>
				<AiAssistantWidgetChatHeader :dialogId="dialogId"/>
			</template>
		</AiAssistantBotContent>
	`
	};

	// @vue/component
	const AiAssistantWidgetChatOpener = {
	  name: 'AiAssistantWidgetChatOpener',
	  components: {
	    AiAssistantWidgetChatContent
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  created() {
	    return this.onChatOpen();
	  },
	  methods: {
	    async onChatOpen() {
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is already loaded`);
	        return;
	      }
	      await this.loadChat();
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: loading chat ${this.chatId}`);
	      await this.getChatService().loadChatWithMessages(this.dialogId);
	      im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is loaded`);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatSerivce = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatSerivce;
	    }
	  },
	  template: `
		<div class="bx-im-messenger__scope bx-im-ai-assistant-chat-opener__container">
			<AiAssistantWidgetChatContent :dialogId="dialogId" :withSidebar="false"/>
		</div>
	`
	};

	const APP_NAME = 'AiAssistantWidgetApplication';
	var _initPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPromise");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class AiAssistantWidgetApplication {
	  constructor() {
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _initPromise, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise] = babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	  }
	  ready() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise];
	  }
	  async mount(payload) {
	    await this.ready();
	    const {
	      rootContainer,
	      aiAssistantBotId,
	      onError
	    } = payload;
	    if (!rootContainer) {
	      return Promise.reject(new Error('Provide node or selector for root container'));
	    }
	    const dialogId = aiAssistantBotId.toString();
	    return im_v2_application_core.Core.createVue(this, {
	      name: APP_NAME,
	      el: rootContainer,
	      onError,
	      components: {
	        AiAssistantWidgetChatOpener
	      },
	      template: `<AiAssistantWidgetChatOpener dialogId="${dialogId}" />`
	    });
	  }
	}
	async function _init2() {
	  await im_v2_application_core.Core.ready();
	  return this;
	}

	exports.AiAssistantWidgetApplication = AiAssistantWidgetApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Css,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.UI.IconSet,BX.Event,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Const));
//# sourceMappingURL=ai-assistant-widget.bundle.js.map
