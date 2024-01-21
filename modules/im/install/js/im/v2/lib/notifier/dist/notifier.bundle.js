/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_notificationManager,ui_vue3_vuex,main_core,main_core_events,im_v2_application_core,im_v2_lib_parser,im_v2_lib_desktop,im_public,im_v2_const,im_v2_provider_service) {
	'use strict';

	const CHAT_MESSAGE_PREFIX = 'im-chat';
	const COPILOT_MESSAGE_PREFIX = 'im-copilot';
	const LINES_MESSAGE_PREFIX = 'im-lines';
	const NOTIFICATION_PREFIX = 'im-notify';
	const ACTION_BUTTON_PREFIX = 'button_';
	const ButtonNumber = {
	  first: '1',
	  second: '2'
	};
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _notificationService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notificationService");
	var _prepareNotificationOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareNotificationOptions");
	var _subscribeToNotifierEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToNotifierEvents");
	var _onNotifierClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierClick");
	var _onNotifierAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierAction");
	var _onNotifierQuickAnswer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierQuickAnswer");
	var _onNotifierButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierButtonClick");
	var _sendButtonAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendButtonAction");
	var _isChatMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatMessage");
	var _isCopilotMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCopilotMessage");
	var _isLinesMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLinesMessage");
	var _isNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNotification");
	var _isConfirmButtonAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isConfirmButtonAction");
	var _extractDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractDialogId");
	var _extractNotificationId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractNotificationId");
	var _extractButtonNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractButtonNumber");
	var _extractButtonParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractButtonParams");
	class NotifierManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  static init() {
	    NotifierManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _extractButtonParams, {
	      value: _extractButtonParams2
	    });
	    Object.defineProperty(this, _extractButtonNumber, {
	      value: _extractButtonNumber2
	    });
	    Object.defineProperty(this, _extractNotificationId, {
	      value: _extractNotificationId2
	    });
	    Object.defineProperty(this, _extractDialogId, {
	      value: _extractDialogId2
	    });
	    Object.defineProperty(this, _isConfirmButtonAction, {
	      value: _isConfirmButtonAction2
	    });
	    Object.defineProperty(this, _isNotification, {
	      value: _isNotification2
	    });
	    Object.defineProperty(this, _isLinesMessage, {
	      value: _isLinesMessage2
	    });
	    Object.defineProperty(this, _isCopilotMessage, {
	      value: _isCopilotMessage2
	    });
	    Object.defineProperty(this, _isChatMessage, {
	      value: _isChatMessage2
	    });
	    Object.defineProperty(this, _sendButtonAction, {
	      value: _sendButtonAction2
	    });
	    Object.defineProperty(this, _onNotifierButtonClick, {
	      value: _onNotifierButtonClick2
	    });
	    Object.defineProperty(this, _onNotifierQuickAnswer, {
	      value: _onNotifierQuickAnswer2
	    });
	    Object.defineProperty(this, _onNotifierAction, {
	      value: _onNotifierAction2
	    });
	    Object.defineProperty(this, _onNotifierClick, {
	      value: _onNotifierClick2
	    });
	    Object.defineProperty(this, _subscribeToNotifierEvents, {
	      value: _subscribeToNotifierEvents2
	    });
	    Object.defineProperty(this, _prepareNotificationOptions, {
	      value: _prepareNotificationOptions2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _notificationService, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _notificationService)[_notificationService] = new im_v2_provider_service.NotificationService();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToNotifierEvents)[_subscribeToNotifierEvents]();
	  }
	  showMessage(params) {
	    const {
	      message,
	      dialog,
	      user,
	      lines
	    } = params;
	    let text = '';
	    if (user && dialog.type !== im_v2_const.ChatType.user) {
	      text += `${user.name}: `;
	    }
	    text += im_v2_lib_parser.Parser.purifyMessage(message);
	    let id = `${CHAT_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
	    if (dialog.type === im_v2_const.ChatType.copilot) {
	      id = `${COPILOT_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
	    } else if (lines) {
	      id = `${LINES_MESSAGE_PREFIX}-${dialog.dialogId}-${message.id}`;
	    }
	    const notificationOptions = {
	      id,
	      title: dialog.name,
	      icon: dialog.avatar || (user == null ? void 0 : user.avatar),
	      text
	    };
	    const isDesktopFocused = im_v2_lib_desktop.DesktopManager.isChatWindow() && document.hasFocus();
	    if (isDesktopFocused) {
	      ui_notificationManager.Notifier.notifyViaBrowserProvider(notificationOptions);
	    } else {
	      ui_notificationManager.Notifier.notify(notificationOptions);
	    }
	  }
	  showNotification(notification, user) {
	    let title = main_core.Loc.getMessage('IM_LIB_NOTIFIER_NOTIFY_SYSTEM_TITLE');
	    if (notification.title) {
	      title = notification.title;
	    } else if (user) {
	      title = user.name;
	    }
	    const notificationOptions = babelHelpers.classPrivateFieldLooseBase(this, _prepareNotificationOptions)[_prepareNotificationOptions](title, notification, user);
	    const isDesktopFocused = im_v2_lib_desktop.DesktopManager.isChatWindow() && document.hasFocus();
	    if (isDesktopFocused) {
	      ui_notificationManager.Notifier.notifyViaBrowserProvider(notificationOptions);
	    } else {
	      ui_notificationManager.Notifier.notify(notificationOptions);
	    }
	  }
	}
	function _prepareNotificationOptions2(title, notification, user) {
	  var _notification$params;
	  const notificationOptions = {
	    id: `${NOTIFICATION_PREFIX}-${notification.id}`,
	    title,
	    icon: user ? user.avatar : '',
	    text: im_v2_lib_parser.Parser.purifyNotification(notification)
	  };
	  if (notification.sectionCode === im_v2_const.NotificationTypesCodes.confirm) {
	    const [firstButton, secondButton] = notification.notifyButtons;
	    notificationOptions.button1Text = firstButton.TEXT;
	    notificationOptions.button2Text = secondButton.TEXT;
	  } else if (((_notification$params = notification.params) == null ? void 0 : _notification$params.canAnswer) === 'Y') {
	    notificationOptions.inputPlaceholderText = main_core.Loc.getMessage('IM_LIB_NOTIFIER_NOTIFY_REPLY_PLACEHOLDER');
	  }
	  return notificationOptions;
	}
	function _subscribeToNotifierEvents2() {
	  ui_notificationManager.Notifier.subscribe('click', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _onNotifierClick)[_onNotifierClick](event.getData());
	  });
	  ui_notificationManager.Notifier.subscribe('action', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _onNotifierAction)[_onNotifierAction](event.getData());
	  });
	}
	function _onNotifierClick2(params) {
	  const {
	    id
	  } = params;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isChatMessage)[_isChatMessage](id)) {
	    const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _extractDialogId)[_extractDialogId](id);
	    im_public.Messenger.openChat(dialogId);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isCopilotMessage)[_isCopilotMessage](id)) {
	    const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _extractDialogId)[_extractDialogId](id);
	    im_public.Messenger.openCopilot(dialogId);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isLinesMessage)[_isLinesMessage](id)) {
	    const dialogId = babelHelpers.classPrivateFieldLooseBase(this, _extractDialogId)[_extractDialogId](id);
	    im_public.Messenger.openLines(dialogId);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isNotification)[_isNotification](id)) {
	    im_public.Messenger.openNotifications();
	  }
	}
	function _onNotifierAction2(params) {
	  const {
	    id,
	    action,
	    userInput
	  } = params;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isNotification)[_isNotification](id)) {
	    return;
	  }
	  const notificationId = babelHelpers.classPrivateFieldLooseBase(this, _extractNotificationId)[_extractNotificationId](id);
	  const notification = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getById'](notificationId);
	  if (userInput) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onNotifierQuickAnswer)[_onNotifierQuickAnswer](notification, userInput);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isConfirmButtonAction)[_isConfirmButtonAction](action, notification)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onNotifierButtonClick)[_onNotifierButtonClick](action, notification);
	  }
	}
	function _onNotifierQuickAnswer2(notification, text) {
	  babelHelpers.classPrivateFieldLooseBase(this, _notificationService)[_notificationService].sendQuickAnswer({
	    id: notification.id,
	    text
	  });
	}
	function _onNotifierButtonClick2(action, notification) {
	  const [firstButton, secondButton] = notification.notifyButtons;
	  const actionButtonNumber = babelHelpers.classPrivateFieldLooseBase(this, _extractButtonNumber)[_extractButtonNumber](action);
	  if (actionButtonNumber === ButtonNumber.first) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendButtonAction)[_sendButtonAction](notification, firstButton);
	  } else if (actionButtonNumber === ButtonNumber.second) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendButtonAction)[_sendButtonAction](notification, secondButton);
	  }
	}
	function _sendButtonAction2(notification, button) {
	  const [notificationId, value] = babelHelpers.classPrivateFieldLooseBase(this, _extractButtonParams)[_extractButtonParams](button);
	  babelHelpers.classPrivateFieldLooseBase(this, _notificationService)[_notificationService].sendConfirmAction(notificationId, value);
	}
	function _isChatMessage2(id) {
	  return id.startsWith(CHAT_MESSAGE_PREFIX);
	}
	function _isCopilotMessage2(id) {
	  return id.startsWith(COPILOT_MESSAGE_PREFIX);
	}
	function _isLinesMessage2(id) {
	  return id.startsWith(LINES_MESSAGE_PREFIX);
	}
	function _isNotification2(id) {
	  return id.startsWith(NOTIFICATION_PREFIX);
	}
	function _isConfirmButtonAction2(action, notification) {
	  const notificationType = notification.sectionCode;
	  return action.startsWith(ACTION_BUTTON_PREFIX) && notificationType === im_v2_const.NotificationTypesCodes.confirm;
	}
	function _extractDialogId2(id) {
	  // 'im-chat-1-2565'
	  return id.split('-')[2];
	}
	function _extractNotificationId2(id) {
	  // 'im-notify-2558'
	  return id.split('-')[2];
	}
	function _extractButtonNumber2(action) {
	  // 'button_1'
	  return action.split('_')[1];
	}
	function _extractButtonParams2(button) {
	  // '2568|Y'
	  return button.COMMAND_PARAMS.split('|');
	}
	Object.defineProperty(NotifierManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.NotifierManager = NotifierManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.UI.NotificationManager,BX.Vue3.Vuex,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Provider.Service));
//# sourceMappingURL=notifier.bundle.js.map
