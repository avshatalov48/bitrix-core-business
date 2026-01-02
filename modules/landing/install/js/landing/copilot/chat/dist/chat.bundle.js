/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,ai_copilotChat_core,ai_copilotChat_ui) {
	'use strict';

	const CopilotChatEvents = ai_copilotChat_core.CopilotChatEvents;
	const CopilotChatMessageType = ai_copilotChat_ui.CopilotChatMessageType;
	class Chat {
	  static getCopilotChatInstance(options) {
	    var _options$chatId, _options$scenario, _options$isSiteEditCh;
	    const entityId = options.entityId;
	    const chatId = (_options$chatId = options.chatId) != null ? _options$chatId : null;
	    const scenario = (_options$scenario = options.scenario) != null ? _options$scenario : 'site_with_ai';
	    return new BX.AI.CopilotChat.Core.CopilotChat({
	      entityId,
	      chatId,
	      scenarioCode: scenario,
	      entityType: 'landing',
	      initChatExtraOptions: {
	        isSiteEditChat: (_options$isSiteEditCh = options.isSiteEditChat) != null ? _options$isSiteEditCh : false
	      },
	      chatOptions: {
	        popupOptions: {
	          cacheable: true,
	          width: this.getPopupWidth(),
	          height: this.getPopupHeight(options.showChatButtonElement),
	          bindElement: this.getPopupPosition(Boolean(options.showChatButtonElement)),
	          className: 'landing__create-site-copilot-chat-popup',
	          animation: {
	            showClassName: 'create-site-chat-show',
	            closeClassName: 'create-site-chat-hide',
	            closeAnimationType: 'animation'
	          },
	          events: {
	            onPopupFirstShow: popup => {
	              popup.subscribe('onBeforeAdjustPosition', () => {
	                popup.setHeight(this.getPopupHeight(options.showChatButtonElement));
	                popup.setBindElement(this.getPopupPosition(Boolean(options.showChatButtonElement)));
	              });
	            }
	          }
	        },
	        header: {
	          title: main_core.Loc.getMessage('LANDING_COPILOT_CHAT_TITLE'),
	          subtitle: main_core.Loc.getMessage('LANDING_COPILOT_CHAT_SUBTITLE'),
	          avatar: '/bitrix/js/landing/copilot/chat/images/avatar.png?v2'
	        },
	        botOptions: {
	          messageTitle: main_core.Loc.getMessage('LANDING_COPILOT_CHAT_BOT_TITLE'),
	          avatar: '/bitrix/js/landing/copilot/chat/images/avatar.png?v2'
	        },
	        slots: {
	          LOADER: null,
	          LOADER_ERROR: null
	        },
	        vueComponents: {},
	        showCopilotWarningMessage: false,
	        inputPlaceholder: main_core.Loc.getMessage('LANDING_COPILOT_CHAT_INPUT_PLACEHOLDER')
	      }
	    });
	  }
	  static getPopupPosition(usedWithShowPopupButton) {
	    return {
	      top: this.getTopPanelHeight() + this.getPopupVerticalOffset(),
	      left: document.body.clientWidth - this.getPopupWidth() - this.getPopupHorizontalOffset(usedWithShowPopupButton)
	    };
	  }
	  static getPopupWidth() {
	    return 375;
	  }
	  static getPopupHeight(showChatButton) {
	    const showChatButtonPos = main_core.Dom.getPosition(showChatButton);
	    return (showChatButtonPos.top || window.innerHeight) - this.getTopPanelHeight() - this.getPopupVerticalOffset() * 2;
	  }
	  static getPopupVerticalOffset() {
	    return 15;
	  }
	  static getPopupHorizontalOffset(usedWithShowPopupButton) {
	    return usedWithShowPopupButton ? 32 : 12;
	  }
	  static getTopPanelHeight() {
	    return 66;
	  }
	}
	Chat.CopilotChatEvents = ai_copilotChat_core.CopilotChatEvents;
	Chat.CopilotChatMessageType = ai_copilotChat_ui.CopilotChatMessageType;

	exports.CopilotChatEvents = CopilotChatEvents;
	exports.CopilotChatMessageType = CopilotChatMessageType;
	exports.Chat = Chat;

}((this.BX.Landing.Copilot = this.BX.Landing.Copilot || {}),BX,BX.AI.CopilotChat.Core,BX.AI.CopilotChat.UI));
//# sourceMappingURL=chat.bundle.js.map
