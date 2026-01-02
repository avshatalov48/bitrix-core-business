/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,ui_vue3_vuex,ui_notificationManager,im_v2_lib_soundNotification,im_v2_application_core,im_v2_lib_desktop,im_v2_lib_desktopApi,im_public,im_v2_provider_service_notification,im_v2_const,im_v2_lib_parser) {
	'use strict';

	const NotificationIdPrefix = {
	  chat: 'im_chat',
	  taskComments: 'im_task_comments',
	  lines: 'im_lines',
	  notify: 'im_notify'
	};
	const ID_SEPARATOR = '-';
	const NotificationId = {
	  build(payload) {
	    const {
	      prefix
	    } = payload;
	    if (prefix === NotificationIdPrefix.notify) {
	      const {
	        notifyId
	      } = payload;
	      return `${prefix}${ID_SEPARATOR}${notifyId}`;
	    }
	    const {
	      dialogId,
	      messageId
	    } = payload;
	    return `${prefix}${ID_SEPARATOR}${dialogId}${ID_SEPARATOR}${messageId}`;
	  },
	  parse(notificationId) {
	    const parts = notificationId.split(ID_SEPARATOR);
	    const [prefix] = parts;
	    if (prefix === NotificationIdPrefix.notify) {
	      const [, notifyId] = parts;
	      return {
	        prefix,
	        notifyId
	      };
	    }
	    const [, dialogId, messageId] = parts;
	    return {
	      prefix,
	      dialogId,
	      messageId
	    };
	  }
	};

	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _chat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chat");
	var _user = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("user");
	var _isLines = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLines");
	var _getId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getId");
	var _getAvatarUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAvatarUrl");
	var _getText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getText");
	class MessageOptionsBuilder {
	  constructor(context) {
	    Object.defineProperty(this, _getText, {
	      value: _getText2
	    });
	    Object.defineProperty(this, _getAvatarUrl, {
	      value: _getAvatarUrl2
	    });
	    Object.defineProperty(this, _getId, {
	      value: _getId2
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _chat, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _user, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isLines, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      message,
	      chat,
	      user,
	      isLines
	    } = context;
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	    babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat] = chat;
	    babelHelpers.classPrivateFieldLooseBase(this, _user)[_user] = user;
	    babelHelpers.classPrivateFieldLooseBase(this, _isLines)[_isLines] = isLines;
	  }
	  build() {
	    return {
	      id: babelHelpers.classPrivateFieldLooseBase(this, _getId)[_getId](),
	      title: babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].name,
	      icon: babelHelpers.classPrivateFieldLooseBase(this, _getAvatarUrl)[_getAvatarUrl](),
	      text: babelHelpers.classPrivateFieldLooseBase(this, _getText)[_getText]()
	    };
	  }
	}
	function _getId2() {
	  const prefixMap = [{
	    condition: () => babelHelpers.classPrivateFieldLooseBase(this, _isLines)[_isLines],
	    prefix: NotificationIdPrefix.lines
	  }, {
	    condition: () => babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].type === im_v2_const.ChatType.taskComments,
	    prefix: NotificationIdPrefix.taskComments
	  }];
	  let prefix = NotificationIdPrefix.chat;
	  const foundItem = prefixMap.find(record => record.condition() === true);
	  if (foundItem) {
	    prefix = foundItem.prefix;
	  }
	  return NotificationId.build({
	    prefix,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].dialogId,
	    messageId: babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id
	  });
	}
	function _getAvatarUrl2() {
	  var _babelHelpers$classPr;
	  return babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].avatar || ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _user)[_user]) == null ? void 0 : _babelHelpers$classPr.avatar);
	}
	function _getText2() {
	  let text = '';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _chat)[_chat].type !== im_v2_const.ChatType.user && babelHelpers.classPrivateFieldLooseBase(this, _user)[_user]) {
	    text += `${babelHelpers.classPrivateFieldLooseBase(this, _user)[_user].name}: `;
	  }
	  text += im_v2_lib_parser.Parser.purifyMessage(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]);
	  return text;
	}

	const ACTION_BUTTON_PREFIX = 'button_';
	const ButtonNumber = {
	  first: '1',
	  second: '2'
	};
	const NotifierShowMessageAction = {
	  skip: 'skip',
	  show: 'show'
	};
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _notificationService = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notificationService");
	var _prepareNotificationOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareNotificationOptions");
	var _subscribeToNotifierEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToNotifierEvents");
	var _onNotifierAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierAction");
	var _onNotifierQuickAnswer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierQuickAnswer");
	var _onNotifierButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNotifierButtonClick");
	var _sendButtonAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendButtonAction");
	var _isConfirmButtonAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isConfirmButtonAction");
	var _extractButtonNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractButtonNumber");
	var _extractButtonParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("extractButtonParams");
	var _isChatOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatOpened");
	var _playOpenedChatMessageSound = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playOpenedChatMessageSound");
	var _playMessageSound = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("playMessageSound");
	var _flashDesktopIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("flashDesktopIcon");
	var _shouldSkipNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldSkipNotification");
	class MessageNotifierManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  static init() {
	    MessageNotifierManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _shouldSkipNotification, {
	      value: _shouldSkipNotification2
	    });
	    Object.defineProperty(this, _flashDesktopIcon, {
	      value: _flashDesktopIcon2
	    });
	    Object.defineProperty(this, _playMessageSound, {
	      value: _playMessageSound2
	    });
	    Object.defineProperty(this, _playOpenedChatMessageSound, {
	      value: _playOpenedChatMessageSound2
	    });
	    Object.defineProperty(this, _isChatOpened, {
	      value: _isChatOpened2
	    });
	    Object.defineProperty(this, _extractButtonParams, {
	      value: _extractButtonParams2
	    });
	    Object.defineProperty(this, _extractButtonNumber, {
	      value: _extractButtonNumber2
	    });
	    Object.defineProperty(this, _isConfirmButtonAction, {
	      value: _isConfirmButtonAction2
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
	    babelHelpers.classPrivateFieldLooseBase(this, _notificationService)[_notificationService] = new im_v2_provider_service_notification.NotificationService();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToNotifierEvents)[_subscribeToNotifierEvents]();
	  }
	  async handleIncomingMessage(params) {
	    const {
	      isImportant,
	      dialogId
	    } = params;
	    if (await babelHelpers.classPrivateFieldLooseBase(this, _shouldSkipNotification)[_shouldSkipNotification](dialogId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _playOpenedChatMessageSound)[_playOpenedChatMessageSound](isImportant);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _playMessageSound)[_playMessageSound](isImportant);
	    babelHelpers.classPrivateFieldLooseBase(this, _flashDesktopIcon)[_flashDesktopIcon]();
	    MessageNotifierManager.getInstance().showMessage(params);
	  }
	  handleIncomingNotification(params) {
	    const {
	      notificationId,
	      userId,
	      isSilent
	    } = params;
	    const notification = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getById'](notificationId);
	    const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](userId);
	    if (!isSilent) {
	      im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.reminder);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _flashDesktopIcon)[_flashDesktopIcon]();
	    MessageNotifierManager.getInstance().showNotification(notification, user);
	  }
	  showMessage(params) {
	    const {
	      messageId,
	      dialogId,
	      isLines
	    } = params;
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/getById'](messageId);
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['chats/get'](dialogId, true);
	    const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](message.authorId);
	    const builder = new MessageOptionsBuilder({
	      message,
	      chat,
	      user,
	      isLines
	    });
	    const notificationOptions = builder.build();
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
	  onNotifierClick(params) {
	    const {
	      id
	    } = params;
	    const {
	      prefix,
	      dialogId
	    } = NotificationId.parse(id);
	    if (prefix === NotificationIdPrefix.chat) {
	      void im_public.Messenger.openChat(dialogId);
	    } else if (prefix === NotificationIdPrefix.taskComments) {
	      void im_public.Messenger.openTaskComments(dialogId);
	    } else if (prefix === NotificationIdPrefix.lines) {
	      void im_public.Messenger.openLines(dialogId);
	    } else if (prefix === NotificationIdPrefix.notify) {
	      void im_public.Messenger.openNotifications();
	    }
	  }
	}
	function _prepareNotificationOptions2(title, notification, user) {
	  var _notification$params;
	  const id = NotificationId.build({
	    prefix: NotificationIdPrefix.notify,
	    notifyId: notification.id
	  });
	  const notificationOptions = {
	    id,
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
	  ui_notificationManager.Notifier.subscribe('click', async event => {
	    if (!im_v2_lib_desktopApi.DesktopApi.isAirDesignEnabledInDesktop()) {
	      im_v2_lib_desktopApi.DesktopApi.activateWindow();
	      this.onNotifierClick(event.getData());
	      return;
	    }
	    await im_v2_lib_desktopApi.DesktopApi.showBrowserWindow();
	    if (im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.portalTabActivation.id)) {
	      await im_v2_lib_desktopApi.DesktopApi.handlePortalTabActivation();
	    }
	    im_v2_lib_desktop.DesktopBroadcastManager.getInstance().sendActionMessage({
	      action: im_v2_const.DesktopBroadcastAction.notification,
	      params: event.getData()
	    });
	  });
	  ui_notificationManager.Notifier.subscribe('action', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _onNotifierAction)[_onNotifierAction](event.getData());
	  });
	}
	function _onNotifierAction2(params) {
	  const {
	    id,
	    action,
	    userInput
	  } = params;
	  const {
	    prefix,
	    notifyId
	  } = NotificationId.parse(id);
	  if (prefix !== NotificationIdPrefix.notify) {
	    return;
	  }
	  const notification = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getById'](notifyId);
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
	function _isConfirmButtonAction2(action, notification) {
	  const notificationType = notification.sectionCode;
	  return action.startsWith(ACTION_BUTTON_PREFIX) && notificationType === im_v2_const.NotificationTypesCodes.confirm;
	}
	function _extractButtonNumber2(action) {
	  // 'button_1'
	  return action.split('_')[1];
	}
	function _extractButtonParams2(button) {
	  // '2568|Y'
	  return button.COMMAND_PARAMS.split('|');
	}
	function _isChatOpened2(dialogId) {
	  const isChatOpen = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['application/isChatOpen'](dialogId);
	  return Boolean(document.hasFocus() && isChatOpen);
	}
	function _playOpenedChatMessageSound2(isImportant) {
	  if (isImportant) {
	    im_v2_lib_soundNotification.SoundNotificationManager.getInstance().forcePlayOnce(im_v2_const.SoundType.newMessage2);
	    return;
	  }
	  im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage2);
	}
	function _playMessageSound2(isImportant) {
	  if (isImportant) {
	    im_v2_lib_soundNotification.SoundNotificationManager.getInstance().forcePlayOnce(im_v2_const.SoundType.newMessage1);
	    return;
	  }
	  im_v2_lib_soundNotification.SoundNotificationManager.getInstance().playOnce(im_v2_const.SoundType.newMessage1);
	}
	function _flashDesktopIcon2() {
	  if (!im_v2_lib_desktop.DesktopManager.isDesktop()) {
	    return;
	  }
	  im_v2_lib_desktopApi.DesktopApi.flashIcon();
	}
	async function _shouldSkipNotification2(dialogId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isChatOpened)[_isChatOpened](dialogId)) {
	    return true;
	  }
	  const eventResult = await main_core_events.EventEmitter.emitAsync(im_v2_const.EventType.notifier.onBeforeShowMessage, {
	    dialogId
	  });
	  return eventResult.includes(NotifierShowMessageAction.skip);
	}
	Object.defineProperty(MessageNotifierManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.NotifierShowMessageAction = NotifierShowMessageAction;
	exports.MessageNotifierManager = MessageNotifierManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Vue3.Vuex,BX.UI.NotificationManager,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=message-notifier.bundle.js.map
