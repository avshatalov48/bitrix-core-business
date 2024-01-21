/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const,im_v2_lib_utils) {
	'use strict';

	const sendMessageCombinations = {
	  enterMode: ['Enter', 'NumpadEnter'],
	  ctrlEnterMode: ['Ctrl+Enter', 'Ctrl+NumpadEnter']
	};
	// only for non-default hotkeys
	const newLineCombinations = {
	  enterMode: ['Ctrl+Enter'],
	  ctrlEnterMode: []
	};
	const isSendMessageCombination = event => {
	  return im_v2_lib_utils.Utils.key.isExactCombination(event, getSendMessageCombination());
	};
	const isNewLineCombination = event => {
	  return im_v2_lib_utils.Utils.key.isExactCombination(event, getNewLineCombination());
	};
	const getSendMessageCombination = () => {
	  const sendByEnter = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.hotkey.sendByEnter);
	  if (sendByEnter) {
	    return sendMessageCombinations.enterMode;
	  }
	  return sendMessageCombinations.ctrlEnterMode;
	};
	const getNewLineCombination = () => {
	  const sendByEnter = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.hotkey.sendByEnter);
	  if (sendByEnter) {
	    return newLineCombinations.enterMode;
	  }
	  return newLineCombinations.ctrlEnterMode;
	};

	exports.isSendMessageCombination = isSendMessageCombination;
	exports.isNewLineCombination = isNewLineCombination;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=hotkey.bundle.js.map
