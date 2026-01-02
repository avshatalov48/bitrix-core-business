/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_lib_rest,im_v2_const) {
	'use strict';

	var _sendBotContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendBotContext");
	class BotContextService {
	  constructor() {
	    Object.defineProperty(this, _sendBotContext, {
	      value: _sendBotContext2
	    });
	  }
	  scheduleContextRequest(dialogId, context) {
	    const eventHandler = event => {
	      const {
	        dialogId: eventDialogId
	      } = event.getData();
	      if (eventDialogId !== dialogId) {
	        return;
	      }
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onDialogInited, eventHandler);
	      void babelHelpers.classPrivateFieldLooseBase(this, _sendBotContext)[_sendBotContext](dialogId, context);
	    };
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onDialogInited, eventHandler);
	  }
	}
	function _sendBotContext2(dialogId, context) {
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatBotSendContext, {
	    data: {
	      dialogId,
	      context
	    }
	  }).catch(([error]) => {
	    console.error('BotContextService: send context error', error);
	  });
	}

	exports.BotContextService = BotContextService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=context.bundle.js.map
