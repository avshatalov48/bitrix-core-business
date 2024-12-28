/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_popup,ui_dialogs_messagebox,im_v2_lib_channel,main_core,im_v2_application_core,im_v2_const) {
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

	const showTwoButtonConfirm = params => {
	  const {
	    text = '',
	    firstButtonCaption = '',
	    secondButtonCaption = '',
	    title = ''
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
	    if (main_core.Type.isStringFilled(title)) {
	      options.title = title;
	    }
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
	    firstButtonCaption = '',
	    title = ''
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
	    if (main_core.Type.isStringFilled(title)) {
	      options.title = title;
	    }
	    if (main_core.Type.isStringFilled(firstButtonCaption)) {
	      options.okCaption = firstButtonCaption;
	    }
	    ChatConfirm.show(options);
	  });
	};

	const showDeleteChatConfirm = dialogId => {
	  const {
	    title,
	    text,
	    firstButtonCaption
	  } = getPhrases(dialogId);
	  return showTwoButtonConfirm({
	    title,
	    text,
	    firstButtonCaption
	  });
	};
	const getPhrases = dialogId => {
	  const isChannel = im_v2_lib_channel.ChannelManager.isChannel(dialogId);
	  if (isChannel) {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_TITLE'),
	      text: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_TEXT'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM')
	    };
	  }
	  if (isCollab(dialogId)) {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_CONFIRM_DELETE_COLLAB_TITLE'),
	      text: main_core.Loc.getMessage('IM_LIB_CONFIRM_DELETE_COLLAB_TEXT'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM')
	    };
	  }
	  return {
	    title: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TITLE'),
	    text: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT'),
	    firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM')
	  };
	};
	const isCollab = dialogId => {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  return chat.type === im_v2_const.ChatType.collab;
	};

	const showLeaveChatConfirm = dialogId => {
	  const {
	    title,
	    text,
	    firstButtonCaption
	  } = getPhrases$1(dialogId);
	  return showTwoButtonConfirm({
	    text,
	    title,
	    firstButtonCaption
	  });
	};
	const getPhrases$1 = dialogId => {
	  if (isCollab$1(dialogId)) {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_COLLAB_TITLE'),
	      text: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_COLLAB_TEXT'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES_MSGVER_1')
	    };
	  }
	  if (im_v2_lib_channel.ChannelManager.isChannel(dialogId)) {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHANNEL_TEXT'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHANNEL_YES')
	    };
	  }
	  return {
	    text: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_MSGVER_1'),
	    firstButtonCaption: main_core.Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES_MSGVER_1')
	  };
	};
	const isCollab$1 = dialogId => {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  return chat.type === im_v2_const.ChatType.collab;
	};

	const showExitUpdateChatConfirm = dialogId => {
	  const {
	    title,
	    firstButtonCaption
	  } = getPhrases$2(dialogId);
	  return showTwoButtonConfirm({
	    title,
	    firstButtonCaption
	  });
	};
	const getPhrases$2 = dialogId => {
	  const isChannel = im_v2_lib_channel.ChannelManager.isChannel(dialogId);
	  if (isChannel) {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_EXIT_UPDATE_CHANNEL_TITLE'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TEXT_CONFIRM')
	    };
	  }
	  return {
	    title: main_core.Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TITLE'),
	    firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TEXT_CONFIRM')
	  };
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

	const showDeleteChannelPostConfirm = () => {
	  return showTwoButtonConfirm({
	    title: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TITLE'),
	    text: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TEXT'),
	    firstButtonCaption: main_core.Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TEXT_CONFIRM')
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

	const showKickUserConfirm = dialogId => {
	  const {
	    title,
	    text,
	    firstButtonCaption
	  } = getPhrases$3(dialogId);
	  return showTwoButtonConfirm({
	    title,
	    text,
	    firstButtonCaption
	  });
	};
	const getPhrases$3 = dialogId => {
	  if (isCollab$2(dialogId)) {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_FROM_COLLAB_TITLE'),
	      text: main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_FROM_COLLAB_TEXT'),
	      firstButtonCaption: main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES')
	    };
	  }
	  return {
	    text: main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK'),
	    firstButtonCaption: main_core.Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES')
	  };
	};
	const isCollab$2 = dialogId => {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  return chat.type === im_v2_const.ChatType.collab;
	};

	exports.showDeleteChatConfirm = showDeleteChatConfirm;
	exports.showLeaveChatConfirm = showLeaveChatConfirm;
	exports.showExitUpdateChatConfirm = showExitUpdateChatConfirm;
	exports.showDesktopRestartConfirm = showDesktopRestartConfirm;
	exports.showDesktopConfirm = showDesktopConfirm;
	exports.showDesktopDeleteConfirm = showDesktopDeleteConfirm;
	exports.showDeleteChannelPostConfirm = showDeleteChannelPostConfirm;
	exports.showNotificationsModeSwitchConfirm = showNotificationsModeSwitchConfirm;
	exports.showKickUserConfirm = showKickUserConfirm;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Main,BX.UI.Dialogs,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
