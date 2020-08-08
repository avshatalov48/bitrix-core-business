this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
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
	var DateFormat = Object.freeze({
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
	var DeviceType = Object.freeze({
	  mobile: 'mobile',
	  desktop: 'desktop'
	});
	var DeviceOrientation = Object.freeze({
	  horizontal: 'horizontal',
	  portrait: 'portrait'
	});

	/**
	 * Bitrix Messenger
	 * Common constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var MutationType = Object.freeze({
	  none: 'none',
	  add: 'delete',
	  update: 'update',
	  delete: 'delete',
	  set: 'set',
	  setAfter: 'after',
	  setBefore: 'before'
	});
	var StorageLimit = Object.freeze({
	  dialogues: 50,
	  messages: 20
	});

	/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var RestMethod = Object.freeze({
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
	  imUserGet: 'im.user.get',
	  imUserListGet: 'im.user.list.get',
	  imDiskFolderGet: 'im.disk.folder.get',
	  imDiskFileUpload: 'disk.folder.uploadfile',
	  imDiskFileCommit: 'im.disk.file.commit',
	  mobileBrowserConstGet: 'mobile.browser.const.get',
	  imRecentGet: 'im.recent.get',
	  imRecentList: 'im.recent.list'
	});
	var RestMethodHandler = Object.freeze({
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
	  imRecentList: 'im.recent.list'
	});

	/**
	 * Bitrix Messenger
	 * Event names constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var EventType = Object.freeze({
	  dialog: {
	    scrollToBottom: 'EventType.dialog.scrollToBottom',
	    requestHistoryResult: 'EventType.dialog.requestHistoryResult',
	    requestUnreadResult: 'EventType.dialog.requestUnreadResult',
	    sendReadMessages: 'EventType.dialog.sendReadMessages'
	  },
	  textarea: {
	    insertText: 'EventType.textarea.insertText',
	    focus: 'EventType.textarea.focus',
	    blur: 'EventType.textarea.blur'
	  }
	});

	/**
	 * Bitrix Messenger
	 * Event names constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var DialogType = Object.freeze({
	  private: 'private',
	  chat: 'chat',
	  open: 'open',
	  call: 'call',
	  crm: 'crm'
	});
	var DialogCrmType = Object.freeze({
	  lead: 'lead',
	  company: 'company',
	  contact: 'contact',
	  deal: 'deal',
	  none: 'none'
	});
	var DialogReferenceClassName = Object.freeze({
	  listBody: 'bx-im-dialog-list',
	  listItem: 'bx-im-dialog-list-item-reference',
	  listItemName: 'bx-im-dialog-list-item-name-reference',
	  listItemBody: 'bx-im-dialog-list-item-content-reference',
	  listUnreadLoader: 'bx-im-dialog-list-unread-loader-reference'
	});

	/**
	 * Bitrix Messenger
	 * File constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var FileStatus = Object.freeze({
	  upload: 'upload',
	  wait: 'wait',
	  done: 'done',
	  error: 'error'
	});
	var FileType = Object.freeze({
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
	var MessageType = Object.freeze({
	  self: 'self',
	  opponent: 'opponent',
	  system: 'system'
	});

	/**
	 * Bitrix Messenger
	 * Call constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var CallLimit = Object.freeze({
	  userLimitForHd: 5
	});
	var CallStateType = Object.freeze({
	  preparation: 'preparation',
	  call: 'call'
	});
	var CallErrorCode = Object.freeze({
	  noSignalFromCamera: 'noSignalFromCamera'
	});
	var CallApplicationErrorCode = Object.freeze({
	  userLimitReached: 'userLimitReached',
	  detectIntranetUser: 'detectIntranetUser',
	  bitrix24only: 'bitrix24only',
	  kickedFromCall: 'kickedFromCall',
	  unsupportedBrowser: 'unsupportedBrowser',
	  missingMicrophone: 'missingMicrophone',
	  unsafeConnection: 'unsafeConnection'
	});

	exports.DateFormat = DateFormat;
	exports.DeviceType = DeviceType;
	exports.DeviceOrientation = DeviceOrientation;
	exports.MutationType = MutationType;
	exports.StorageLimit = StorageLimit;
	exports.RestMethod = RestMethod;
	exports.RestMethodHandler = RestMethodHandler;
	exports.EventType = EventType;
	exports.DialogType = DialogType;
	exports.DialogCrmType = DialogCrmType;
	exports.DialogReferenceClassName = DialogReferenceClassName;
	exports.FileStatus = FileStatus;
	exports.FileType = FileType;
	exports.MessageType = MessageType;
	exports.CallStateType = CallStateType;
	exports.CallLimit = CallLimit;
	exports.CallErrorCode = CallErrorCode;
	exports.CallApplicationErrorCode = CallApplicationErrorCode;

}((this.BX.Messenger.Const = this.BX.Messenger.Const || {})));
//# sourceMappingURL=registry.bundle.js.map
