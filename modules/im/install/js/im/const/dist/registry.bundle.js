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
	 * @copyright 2001-2019 Bitrix
	 */
	var DateFormat = Object.freeze({
	  groupTitle: 'groupTitle',
	  message: 'message',
	  recentTitle: 'recentTitle',
	  recentLinesTitle: 'recentLinesTitle',
	  default: 'default'
	});

	/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
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
	 * @copyright 2001-2019 Bitrix
	 */
	var InsertType = Object.freeze({
	  after: 'after',
	  before: 'before'
	});

	/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var RestMethod = Object.freeze({
	  imMessageAdd: 'im.message.add',
	  imMessageUpdate: 'im.message.update',
	  imMessageDelete: 'im.message.delete',
	  imMessageLike: 'im.message.like',
	  imChatGet: 'im.chat.get',
	  imChatSendTyping: 'im.chat.sendTyping',
	  imDialogMessagesGet: 'im.dialog.messages.get',
	  imDialogMessagesUnread: 'im.dialog.messages.unread',
	  imDialogRead: 'im.dialog.read',
	  imDiskFolderGet: 'im.disk.folder.get',
	  imDiskFileUpload: 'disk.folder.uploadfile',
	  imDiskFileCommit: 'im.disk.file.commit'
	});

	exports.DateFormat = DateFormat;
	exports.DeviceType = DeviceType;
	exports.DeviceOrientation = DeviceOrientation;
	exports.InsertType = InsertType;
	exports.RestMethod = RestMethod;

}((this.BX.Messenger.Const = this.BX.Messenger.Const || {})));
//# sourceMappingURL=registry.bundle.js.map
