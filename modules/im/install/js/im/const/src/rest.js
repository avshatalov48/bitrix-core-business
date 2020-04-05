/**
 * Bitrix Messenger
 * Device constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

const RestMethod = Object.freeze({

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
	imDiskFileCommit: 'im.disk.file.commit',

});

export {
	RestMethod,
};