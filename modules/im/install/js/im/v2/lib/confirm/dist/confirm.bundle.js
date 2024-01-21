/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_popup,ui_dialogs_messagebox) {
	'use strict';

	const CONTAINER_CLASS = 'im-confirm-container';
	const CONTAINER_MIN_HEIGHT = 110;
	class ChatConfirm extends ui_dialogs_messagebox.MessageBox {
	  // noinspection JSCheckFunctionSignatures
	  getPopupWindow() {
	    const popup = super.getPopupWindow();
	    main_core.Dom.addClass(popup.getPopupContainer(), CONTAINER_CLASS);
	    main_core.Dom.style(popup.getPopupContainer(), 'minHeight', `${CONTAINER_MIN_HEIGHT}px`);
	    return super.getPopupWindow();
	  }
	}

	const showKickUserConfirm = () => {
	  const kickText = main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK');
	  const yesCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES');
	  return showTwoButtonConfirm({
	    text: kickText,
	    firstButtonCaption: yesCaption
	  });
	};
	const showLeaveFromChatConfirm = () => {
	  const kickText = main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT');
	  const yesCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES');
	  return showTwoButtonConfirm({
	    text: kickText,
	    firstButtonCaption: yesCaption
	  });
	};
	const showDesktopConfirm = () => {
	  const restartText = main_core.Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP');
	  const okText = main_core.Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_OK');
	  return showSingleButtonConfirm({
	    text: restartText,
	    firstButtonCaption: okText
	  });
	};
	const showDesktopRestartConfirm = () => {
	  const restartText = main_core.Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP');
	  const restartCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_RESTART');
	  const laterCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_LATER');
	  return showTwoButtonConfirm({
	    text: restartText,
	    firstButtonCaption: restartCaption,
	    secondButtonCaption: laterCaption
	  });
	};
	const showDesktopDeleteConfirm = () => {
	  const deleteText = main_core.Loc.getMessage('IM_LIB_CONFIRM_DELETE_DESKTOP').replace('#BR#', '<br>');
	  const confirmCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_DELETE_DESKTOP_CONFIRM');
	  return showTwoButtonConfirm({
	    text: deleteText,
	    firstButtonCaption: confirmCaption
	  });
	};
	const showNotificationsModeSwitchConfirm = () => {
	  const kickText = main_core.Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE');
	  const yesCaption = main_core.Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE_YES');
	  return showTwoButtonConfirm({
	    text: kickText,
	    firstButtonCaption: yesCaption
	  });
	};
	const showTwoButtonConfirm = params => {
	  const {
	    text,
	    firstButtonCaption = '',
	    secondButtonCaption = ''
	  } = params;
	  return new Promise(resolve => {
	    const options = {
	      message: text,
	      modal: true,
	      buttons: ui_dialogs_messagebox.MessageBoxButtons.YES_CANCEL,
	      onYes: messageBox => {
	        resolve(true);
	        messageBox.close();
	      },
	      onCancel: messageBox => {
	        resolve(false);
	        messageBox.close();
	      }
	    };
	    if (main_core.Type.isStringFilled(firstButtonCaption)) {
	      options.yesCaption = firstButtonCaption;
	    }
	    if (main_core.Type.isStringFilled(secondButtonCaption)) {
	      options.cancelCaption = secondButtonCaption;
	    }
	    ChatConfirm.show(options);
	  });
	};
	const showSingleButtonConfirm = params => {
	  const {
	    text,
	    firstButtonCaption = ''
	  } = params;
	  return new Promise(resolve => {
	    const options = {
	      message: text,
	      modal: true,
	      buttons: ui_dialogs_messagebox.MessageBoxButtons.OK,
	      onOk: messageBox => {
	        resolve(true);
	        messageBox.close();
	      }
	    };
	    if (main_core.Type.isStringFilled(firstButtonCaption)) {
	      options.okCaption = firstButtonCaption;
	    }
	    ChatConfirm.show(options);
	  });
	};

	exports.showKickUserConfirm = showKickUserConfirm;
	exports.showLeaveFromChatConfirm = showLeaveFromChatConfirm;
	exports.showDesktopConfirm = showDesktopConfirm;
	exports.showDesktopRestartConfirm = showDesktopRestartConfirm;
	exports.showDesktopDeleteConfirm = showDesktopDeleteConfirm;
	exports.showNotificationsModeSwitchConfirm = showNotificationsModeSwitchConfirm;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Main,BX.UI.Dialogs));
//# sourceMappingURL=confirm.bundle.js.map
