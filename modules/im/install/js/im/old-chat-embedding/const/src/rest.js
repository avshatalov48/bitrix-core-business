export const RestMethod = Object.freeze({
	imMessageAdd: 'im.message.add',
	imMessageUpdate: 'im.message.update',
	imMessageDelete: 'im.message.delete',
	imMessageLike: 'im.message.like',
	imMessageCommand: 'im.message.command',
	imMessageShare: 'im.message.share',

	imChatAdd: 'im.chat.add',
	imChatGet: 'im.chat.get',
	imChatLeave: 'im.chat.leave',
	imChatMute: 'im.chat.mute',
	imChatUpdateTitle: 'im.chat.updateTitle',
	imChatParentJoin: 'im.chat.parent.join',
	imChatFileCollectionGet: 'im.chat.file.collection.get',
	imChatFileGet: 'im.chat.file.get',
	imChatUrlGet: 'im.chat.url.get',
	imChatUrlDelete: 'im.chat.url.delete',
	imChatTaskGet: 'im.chat.task.get',
	imChatTaskDelete: 'im.chat.task.delete',
	imChatCalendarGet: 'im.chat.calendar.get',
	imChatFavoriteAdd: 'im.chat.favorite.add',
	imChatFavoriteDelete: 'im.chat.favorite.delete',
	imChatFavoriteGet: 'im.chat.favorite.get',
	imChatFavoriteCounterGet: 'im.chat.favorite.counter.get',
	imChatUrlCounterGet: 'im.chat.url.counter.get',
	imChatPinGet: 'im.chat.pin.get',
	imChatPinAdd: 'im.chat.pin.add',
	imChatPinDelete: 'im.chat.pin.delete',
	imChatTaskPrepare: 'im.chat.task.prepare',
	imChatCalendarPrepare: 'im.chat.calendar.prepare',
	imChatCalendarAdd: 'im.chat.calendar.add',
	imChatCalendarDelete: 'im.chat.calendar.delete',
	imChatUserDelete: 'im.chat.user.delete',
	imChatUserAdd: 'im.chat.user.add',

	imDialogGet: 'im.dialog.get',
	imDialogMessagesGet: 'im.dialog.messages.get',
	imDialogRead: 'im.dialog.read',
	imDialogUnread: 'im.dialog.unread',
	imDialogWriting: 'im.dialog.writing',
	imDialogRestrictionsGet: 'im.dialog.restrictions.get',
	imDialogReadAll: 'im.dialog.read.all',
	imDialogContextGet: 'im.dialog.context.get',
	imDialogUsersList: 'im.dialog.users.list',

	imUserGet: 'im.user.get',
	imUserListGet: 'im.user.list.get',
	imUserStatusSet: 'im.user.status.set',

	imDiskFolderGet: 'im.disk.folder.get',
	imDiskFolderListGet: 'im.disk.folder.list.get',
	imDiskFileUpload: 'disk.folder.uploadfile',
	imDiskFileCommit: 'im.disk.file.commit',
	imDiskFileDelete: 'im.disk.file.delete',
	imDiskFileSave: 'im.disk.file.save',

	mobileBrowserConstGet: 'mobile.browser.const.get',

	imRecentGet: 'im.recent.get',
	imRecentList: 'im.recent.list',
	imRecentPin: 'im.recent.pin',
	imRecentUnread: 'im.recent.unread',
	imRecentHide: 'im.recent.hide',

	imCallGetCallLimits: 'im.call.getCallLimits',

	imNotifyGet: 'im.notify.get',
	imNotifyRead: 'im.notify.read',
	imNotifySchemaGet: 'im.notify.schema.get',
	imNotifyHistorySearch: 'im.notify.history.search',
	imNotifyAnswer: 'im.notify.answer',

	imSmilesGet: 'smile.get'
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
