/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_lib_copilot,im_v2_application_core,im_v2_const,im_v2_lib_dateFormatter,im_v2_lib_parser) {
	'use strict';

	const QUOTE_DELIMITER = '-'.repeat(54);
	const Quote = {
	  sendQuoteEvent(message, text, dialogId) {
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	      text: this.prepareQuoteText(message, text),
	      dialogId,
	      withNewLine: true,
	      replace: false
	    });
	  },
	  prepareQuoteText(message, text) {
	    const dialog = im_v2_application_core.Core.getStore().getters['chats/getByChatId'](message.chatId);
	    let quoteTitle = main_core.Loc.getMessage('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
	    if (message.authorId) {
	      quoteTitle = getName(message);
	    }
	    const quoteDate = im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(message.date, im_v2_lib_dateFormatter.DateTemplate.notification);
	    const quoteText = im_v2_lib_parser.Parser.prepareQuote(message, text);
	    let quoteContext = '';
	    if (dialog && dialog.type === im_v2_const.ChatType.user) {
	      quoteContext = `#${dialog.dialogId}:${im_v2_application_core.Core.getUserId()}/${message.id}`;
	    } else {
	      quoteContext = `#${dialog.dialogId}/${message.id}`;
	    }
	    return `${QUOTE_DELIMITER}\n` + `${quoteTitle} [${quoteDate}] ${quoteContext}\n` + `${quoteText}\n` + `${QUOTE_DELIMITER}\n`;
	  }
	};
	const getName = message => {
	  let name = '';
	  const copilotManager = new im_v2_lib_copilot.CopilotManager();
	  if (copilotManager.isCopilotBot(message.authorId)) {
	    name = copilotManager.getNameWithRole({
	      dialogId: message.authorId,
	      messageId: message.id
	    });
	  } else {
	    const user = im_v2_application_core.Core.getStore().getters['users/get'](message.authorId);
	    name = user.name;
	  }
	  return name;
	};

	exports.Quote = Quote;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=quote.bundle.js.map
