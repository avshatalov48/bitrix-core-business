this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Date constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const DateFormat = Object.freeze({
	  groupTitle: 'groupTitle',
	  message: 'message',
	  recentTitle: 'recentTitle',
	  recentLinesTitle: 'recentLinesTitle',
	  readedTitle: 'readedTitle',
	  default: 'default',
	  vacationTitle: 'vacationTitle'
	});

	/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const DeviceType = Object.freeze({
	  mobile: 'mobile',
	  desktop: 'desktop'
	});
	const DeviceOrientation = Object.freeze({
	  horizontal: 'horizontal',
	  portrait: 'portrait'
	});

	const MutationType = Object.freeze({
	  none: 'none',
	  add: 'delete',
	  update: 'update',
	  delete: 'delete',
	  set: 'set',
	  setAfter: 'after',
	  setBefore: 'before'
	});
	const StorageLimit = Object.freeze({
	  dialogues: 50,
	  messages: 100
	});
	const Settings = Object.freeze({
	  darkTheme: 'darkTheme'
	});

	// old chat names -> new model names
	const SettingsMap = Object.freeze({
	  enableDarkTheme: 'darkTheme'
	});
	const AvatarSize = Object.freeze({
	  S: 'S',
	  M: 'M',
	  L: 'L'
	});
	const OpenTarget = Object.freeze({
	  current: 'current',
	  auto: 'auto'
	});
	const BotType = Object.freeze({
	  bot: 'bot',
	  network: 'network',
	  support24: 'support24'
	});

	/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const RestMethod = Object.freeze({
	  imMessageAdd: 'im.message.add',
	  imMessageUpdate: 'im.message.update',
	  imMessageDelete: 'im.message.delete',
	  imMessageLike: 'im.message.like',
	  imMessageCommand: 'im.message.command',
	  imMessageShare: 'im.message.share',
	  imChatGet: 'im.chat.get',
	  imChatLeave: 'im.chat.leave',
	  imChatMute: 'im.chat.mute',
	  imChatParentJoin: 'im.chat.parent.join',
	  imDialogGet: 'im.dialog.get',
	  imDialogMessagesGet: 'im.dialog.messages.get',
	  imDialogRead: 'im.dialog.read',
	  imDialogUnread: 'im.dialog.unread',
	  imDialogWriting: 'im.dialog.writing',
	  imDialogRestrictionsGet: 'im.dialog.restrictions.get',
	  imUserGet: 'im.user.get',
	  imUserListGet: 'im.user.list.get',
	  imDiskFolderGet: 'im.disk.folder.get',
	  imDiskFileUpload: 'disk.folder.uploadfile',
	  imDiskFileCommit: 'im.disk.file.commit',
	  mobileBrowserConstGet: 'mobile.browser.const.get',
	  imRecentGet: 'im.recent.get',
	  imRecentList: 'im.recent.list',
	  imRecentPin: 'im.recent.pin',
	  imRecentUnread: 'im.recent.unread',
	  imCallGetCallLimits: 'im.call.getCallLimits',
	  imNotifyGet: 'im.notify.get',
	  imNotifySchemaGet: 'im.notify.schema.get',
	  imCallBackgroundGet: 'im.v2.Call.Background.get',
	  imCallBackgroundCommit: 'im.v2.Call.Background.commit',
	  imCallBackgroundDelete: 'im.v2.Call.Background.delete',
	  imCallMaskGet: 'im.v2.Call.Mask.get'
	});
	const RestMethodHandler = Object.freeze({
	  imChatGet: 'im.chat.get',
	  imMessageAdd: 'im.message.add',
	  imDialogRead: 'im.dialog.read',
	  imDialogMessagesGet: 'im.dialog.messages.get',
	  imDialogMessagesGetInit: 'im.dialog.messages.get.init',
	  imDialogMessagesGetUnread: 'im.dialog.messages.get.unread',
	  imDiskFolderGet: 'im.disk.folder.get',
	  imDiskFileUpload: 'disk.folder.uploadfile',
	  imDiskFileCommit: 'im.disk.file.commit',
	  imUserGet: 'im.user.get',
	  imUserListGet: 'im.user.list.get',
	  mobileBrowserConstGet: 'mobile.browser.const.get',
	  imRecentGet: 'im.recent.get',
	  imRecentList: 'im.recent.list',
	  imCallGetCallLimits: 'im.call.getCallLimits',
	  imNotifyGet: 'im.notify.get',
	  imNotifySchemaGet: 'im.notify.schema.get'
	});

	const PullCommand = Object.freeze({
	  messageUpdate: 'messageUpdate',
	  messageDelete: 'messageDelete'
	});
	const PullHandlers = Object.freeze({
	  recent: 'recent'
	});

	/**
	 * Bitrix Messenger
	 * Event names constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const EventType = Object.freeze({
	  dialog: {
	    open: 'IM.Dialog:open',
	    call: 'IM.Dialog:call',
	    openHistory: 'IM.Dialog:openHistory',
	    clearHistory: 'IM.Dialog:clearHistory',
	    hide: 'IM.Dialog:hide',
	    leave: 'IM.Dialog:leave',
	    newMessage: 'EventType.dialog.newMessage',
	    scrollOnStart: 'IM.Dialog:scrollOnStart',
	    scrollToBottom: 'IM.Dialog:scrollToBottom',
	    readVisibleMessages: 'IM.Dialog.readVisibleMessages',
	    requestUnread: 'IM.Dialog.requestUnread',
	    readMessage: 'IM.Dialog:readMessage',
	    quoteMessage: 'IM.Dialog:quoteMessage',
	    clickOnCommand: 'IM.Dialog:clickOnCommand',
	    clickOnMention: 'IM.Dialog:clickOnMention',
	    clickOnUserName: 'IM.Dialog:clickOnUserName',
	    clickOnMessageMenu: 'IM.Dialog:clickOnMessageMenu',
	    clickOnMessageRetry: 'IM.Dialog:clickOnMessageRetry',
	    clickOnUploadCancel: 'IM.Dialog:clickOnUploadCancel',
	    clickOnReadList: 'IM.Dialog:clickOnReadList',
	    setMessageReaction: 'IM.Dialog:setMessageReaction',
	    openMessageReactionList: 'IM.Dialog:openMessageReactionList',
	    clickOnKeyboardButton: 'IM.Dialog:clickOnKeyboardButton',
	    clickOnChatTeaser: 'IM.Dialog:clickOnChatTeaser',
	    clickOnDialog: 'IM.Dialog:clickOnDialog',
	    quotePanelClose: 'IM.Dialog:quotePanelClose',
	    beforeMobileKeyboard: 'IM.Dialog:beforeMobileKeyboard',
	    messagesSet: 'IM.Dialog:messagesSet',
	    settingsChange: 'IM.Dialog:settingsChange',
	    closePopup: 'IM.Dialog:closePopup',
	    errors: {
	      accessDenied: 'IM.Dialog.errors:accessDenied'
	    }
	  },
	  textarea: {
	    focus: 'IM.Textarea:focus',
	    setFocus: 'IM.Textarea:setFocus',
	    blur: 'IM.Textarea:blur',
	    setBlur: 'IM.Textarea:setBlur',
	    keyUp: 'IM.Textarea:keyUp',
	    edit: 'IM.Textarea:edit',
	    insertText: 'IM.Textarea:insertText',
	    sendMessage: 'IM.Textarea:sendMessage',
	    fileSelected: 'IM.Textarea:fileSelected',
	    startWriting: 'IM.Textarea:startWriting',
	    stopWriting: 'IM.Textarea:stopWriting',
	    appButtonClick: 'IM.Textarea:appButtonClick'
	  },
	  uploader: {
	    addMessageWithFile: 'IM.Uploader:addMessageWithFile'
	  },
	  conference: {
	    setPasswordFocus: 'IM.Conference:setPasswordFocus',
	    hideSmiles: 'IM.Conference:hideSmiles',
	    requestPermissions: 'IM.Conference:requestPermissions',
	    waitForStart: 'IM.Conference:waitForStart',
	    userRenameFocus: 'IM.Conference:userRenameFocus',
	    userRenameBlur: 'IM.Conference:userRenameBlur'
	  },
	  notification: {
	    updateState: 'IM.Notifications:restoreConnection'
	  },
	  mobile: {
	    textarea: {
	      setText: 'IM.Mobile.Textarea:setText',
	      setFocus: 'IM.Mobile.Textarea:setFocus'
	    },
	    openUserList: 'IM.Mobile:openUserList'
	  },
	  search: {
	    selectItem: 'IM.Search:selectItem',
	    openContextMenu: 'IM.Search:openContextMenu',
	    openNetworkItem: 'IM.Search:openNetworkItem'
	  },
	  recent: {
	    openSearch: 'IM.Recent:openSearch',
	    updateSearch: 'IM.Recent:updateSearch',
	    closeSearch: 'IM.Recent:closeSearch',
	    requestUser: 'IM.Recent:requestUser',
	    // compatibility with old chat
	    setCounter: 'IM.Recent:setCounter',
	    setMessage: 'IM.Recent:setMessage',
	    hideChat: 'IM.Recent:hideChat',
	    leaveChat: 'IM.Recent:leaveChat',
	    updateState: 'IM.Recent:updateState',
	    clearLike: 'IM.Recent:clearLike',
	    setDraftMessage: 'IM.Recent:setDraftMessage'
	  }
	});

	const DialogType = Object.freeze({
	  private: 'private',
	  chat: 'chat',
	  open: 'open',
	  call: 'call',
	  crm: 'crm',
	  announcement: 'announcement'
	});
	const DialogCrmType = Object.freeze({
	  lead: 'lead',
	  company: 'company',
	  contact: 'contact',
	  deal: 'deal',
	  none: 'none'
	});
	const DialogReferenceClassName = Object.freeze({
	  listBody: 'bx-im-dialog-list',
	  listItem: 'bx-im-dialog-list-item-reference',
	  listItemName: 'bx-im-dialog-list-item-name-reference',
	  listItemBody: 'bx-im-dialog-list-item-content-reference',
	  listUnreadLoader: 'bx-im-dialog-list-unread-loader-reference'
	});
	const DialogTemplateType = Object.freeze({
	  message: 'message',
	  delimiter: 'delimiter',
	  group: 'group',
	  historyLoader: 'historyLoader',
	  unreadLoader: 'unreadLoader',
	  button: 'button',
	  placeholder: 'placeholder'
	});
	const DialogState = Object.freeze({
	  loading: 'loading',
	  empty: 'empty',
	  show: 'show'
	});

	/**
	 * Bitrix Messenger
	 * File constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const FileStatus = Object.freeze({
	  upload: 'upload',
	  wait: 'wait',
	  done: 'done',
	  error: 'error'
	});
	const FileType = Object.freeze({
	  image: 'image',
	  video: 'video',
	  audio: 'audio',
	  file: 'file'
	});

	/**
	 * Bitrix Messenger
	 * Message constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const MessageType = Object.freeze({
	  self: 'self',
	  opponent: 'opponent',
	  system: 'system'
	});

	/**
	 * Bitrix Messenger
	 * Conference constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	const ConferenceFieldState = Object.freeze({
	  view: 'view',
	  edit: 'edit',
	  create: 'create'
	});
	const ConferenceStateType = Object.freeze({
	  preparation: 'preparation',
	  call: 'call'
	});
	const ConferenceErrorCode = Object.freeze({
	  userLimitReached: 'userLimitReached',
	  detectIntranetUser: 'detectIntranetUser',
	  bitrix24only: 'bitrix24only',
	  kickedFromCall: 'kickedFromCall',
	  unsupportedBrowser: 'unsupportedBrowser',
	  missingMicrophone: 'missingMicrophone',
	  unsafeConnection: 'unsafeConnection',
	  wrongAlias: 'wrongAlias',
	  notStarted: 'notStarted',
	  finished: 'finished',
	  userLeftCall: 'userLeftCall',
	  noSignalFromCamera: 'noSignalFromCamera'
	});
	const ConferenceRightPanelMode = Object.freeze({
	  hidden: 'hidden',
	  chat: 'chat',
	  users: 'users',
	  split: 'split'
	});

	//BX.Call.UserState sync
	const ConferenceUserState = Object.freeze({
	  Idle: 'Idle',
	  Busy: 'Busy',
	  Calling: 'Calling',
	  Unavailable: 'Unavailable',
	  Declined: 'Declined',
	  Ready: 'Ready',
	  Connecting: 'Connecting',
	  Connected: 'Connected',
	  Failed: 'Failed'
	});

	const ChatTypes = {
	  user: 'user',
	  chat: 'chat',
	  open: 'open',
	  general: 'general',
	  videoconf: 'videoconf',
	  announcement: 'announcement',
	  call: 'call',
	  support24: {
	    notifier: 'support24Notifier',
	    question: 'support24Question'
	  },
	  crm: 'crm',
	  group: 'sonetGroup',
	  calendar: 'calendar',
	  task: 'tasks'
	};
	const UserStatus = {
	  online: 'online',
	  mobileOnline: 'mobile-online',
	  idle: 'idle',
	  dnd: 'dnd',
	  away: 'away',
	  break: 'break'
	};
	const RecentSection = {
	  general: 'general',
	  pinned: 'pinned'
	};
	const MessageStatus = {
	  received: 'received',
	  delivered: 'delivered',
	  error: 'error'
	};
	const RecentCallStatus = {
	  waiting: 'waiting',
	  joined: 'joined'
	};
	const RecentSettings = {
	  showBirthday: 'showBirthday',
	  showInvited: 'showInvited',
	  showLastMessage: 'showLastMessage'
	};

	// old chat names -> new model names
	const RecentSettingsMap = {
	  'viewBirthday': 'showBirthday',
	  'viewCommonUsers': 'showInvited',
	  'viewLastMessage': 'showLastMessage'
	};

	const NotificationTypesCodes = Object.freeze({
	  confirm: 1,
	  simple: 3,
	  placeholder: 5
	});

	const ChatOption = Object.freeze({
	  avatar: 'avatar',
	  call: 'call',
	  extend: 'extend',
	  leave: 'leave',
	  leaveOwner: 'leaveOwner',
	  mute: 'mute',
	  rename: 'rename',
	  send: 'send',
	  userList: 'userList'
	});

	const DesktopFeature = {
	  mask: {
	    id: 'mask',
	    availableFromVersion: 72
	  }
	};

	exports.DateFormat = DateFormat;
	exports.DeviceType = DeviceType;
	exports.DeviceOrientation = DeviceOrientation;
	exports.MutationType = MutationType;
	exports.StorageLimit = StorageLimit;
	exports.Settings = Settings;
	exports.SettingsMap = SettingsMap;
	exports.AvatarSize = AvatarSize;
	exports.OpenTarget = OpenTarget;
	exports.BotType = BotType;
	exports.RestMethod = RestMethod;
	exports.RestMethodHandler = RestMethodHandler;
	exports.PullCommand = PullCommand;
	exports.PullHandlers = PullHandlers;
	exports.EventType = EventType;
	exports.DialogType = DialogType;
	exports.DialogCrmType = DialogCrmType;
	exports.DialogReferenceClassName = DialogReferenceClassName;
	exports.DialogTemplateType = DialogTemplateType;
	exports.DialogState = DialogState;
	exports.FileStatus = FileStatus;
	exports.FileType = FileType;
	exports.MessageType = MessageType;
	exports.ConferenceFieldState = ConferenceFieldState;
	exports.ConferenceStateType = ConferenceStateType;
	exports.ConferenceErrorCode = ConferenceErrorCode;
	exports.ConferenceRightPanelMode = ConferenceRightPanelMode;
	exports.ConferenceUserState = ConferenceUserState;
	exports.ChatTypes = ChatTypes;
	exports.RecentSection = RecentSection;
	exports.MessageStatus = MessageStatus;
	exports.RecentCallStatus = RecentCallStatus;
	exports.RecentSettings = RecentSettings;
	exports.RecentSettingsMap = RecentSettingsMap;
	exports.UserStatus = UserStatus;
	exports.NotificationTypesCodes = NotificationTypesCodes;
	exports.ChatOption = ChatOption;
	exports.DesktopFeature = DesktopFeature;

}((this.BX.Messenger.v2.Const = this.BX.Messenger.v2.Const || {})));
//# sourceMappingURL=registry.bundle.js.map
