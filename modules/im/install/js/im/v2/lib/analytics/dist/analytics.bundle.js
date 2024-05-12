/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_analytics,main_core,im_v2_application_core) {
	'use strict';

	const CopilotChatType = Object.freeze({
	  private: 'chatType_private',
	  multiuser: 'chatType_multiuser'
	});
	const AnalyticsEvent = Object.freeze({
	  openChat: 'open_chat',
	  createNewChat: 'create_new_chat',
	  audioUse: 'audio_use',
	  openTab: 'open_tab'
	});
	const AnalyticsTool = Object.freeze({
	  ai: 'ai'
	});
	const AnalyticsCategory = Object.freeze({
	  chatOperations: 'chat_operations'
	});
	const AnalyticsType = Object.freeze({
	  ai: 'ai'
	});
	const AnalyticsSection = Object.freeze({
	  copilotTab: 'copilot_tab'
	});

	var _createdChats = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createdChats");
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	class Analytics {
	  constructor() {
	    Object.defineProperty(this, _createdChats, {
	      writable: true,
	      value: new Set()
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  createChat({
	    chatId,
	    dialogId
	  }) {
	    babelHelpers.classPrivateFieldLooseBase(this, _createdChats)[_createdChats].add(dialogId);
	    ui_analytics.sendData({
	      event: AnalyticsEvent.createNewChat,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab,
	      type: AnalyticsType.ai,
	      p3: CopilotChatType.private,
	      p5: `chatId_${chatId}`
	    });
	  }
	  openCopilotChat(dialogId) {
	    if (!main_core.Type.isStringFilled(dialogId)) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _createdChats)[_createdChats].has(dialogId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _createdChats)[_createdChats].delete(dialogId);
	      return;
	    }
	    const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const copilotChatType = dialog.userCounter <= 2 ? CopilotChatType.private : CopilotChatType.multiuser;
	    ui_analytics.sendData({
	      event: AnalyticsEvent.openChat,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab,
	      type: AnalyticsType.ai,
	      p3: copilotChatType,
	      p5: `chatId_${dialog.chatId}`
	    });
	  }
	  openCopilotTab() {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.openTab,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab
	    });
	  }
	  useAudioInput() {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.audioUse,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab
	    });
	  }
	}
	Object.defineProperty(Analytics, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.Analytics = Analytics;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.UI.Analytics,BX,BX.Messenger.v2.Application));
//# sourceMappingURL=analytics.bundle.js.map
