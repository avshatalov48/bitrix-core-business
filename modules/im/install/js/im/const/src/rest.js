/**
 * Bitrix Messenger
 * Device constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const RestMethod = Object.freeze({

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
	imRecentList: 'im.recent.list',

	imCallGetCallLimits: 'im.call.getCallLimits',

	imNotifyGet: 'im.notify.get',
	imNotifySchemaGet: 'im.notify.schema.get'
});

export const RestMethodHandler = Object.freeze({
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
	imNotifySchemaGet: 'im.notify.schema.get',
});
