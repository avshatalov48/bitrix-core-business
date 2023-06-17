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

	imV2ChatAdd: 'im.v2.Chat.add',
	imV2ChatRead: 'im.v2.Chat.read',
	imV2ChatReadAll: 'im.v2.Chat.readAll',
	imV2ChatUnread: 'im.v2.Chat.unread',
	imV2ChatMessageGetContext: 'im.v2.Chat.Message.getContext',
	imV2ChatMessageList: 'im.v2.Chat.Message.list',
	imV2ChatMessageTail: 'im.v2.Chat.Message.tail',
	imV2ChatMessageRead: 'im.v2.Chat.Message.read',
	imV2ChatMessageMark: 'im.v2.Chat.Message.mark',
	imV2ChatMessageReactionAdd: 'im.v2.Chat.Message.Reaction.add',
	imV2ChatMessageReactionDelete: 'im.v2.Chat.Message.Reaction.delete',
	imV2ChatMessageReactionTail: 'im.v2.Chat.Message.Reaction.tail',
	imV2ChatMessagePin: 'im.v2.Chat.Message.pin',
	imV2ChatMessageUnpin: 'im.v2.Chat.Message.unpin',
	imV2ChatMessageTailViewers: 'im.v2.Chat.Message.tailViewers',
	imV2ChatPinTail: 'im.v2.Chat.Pin.tail',
	imV2SettingsGeneralUpdate: 'im.v2.Settings.General.update',

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

	imVersionV2Enable: 'im.version.v2.enable',
	imVersionV2Disable: 'im.version.v2.disable',

	imCallBackgroundGet: 'im.v2.Call.Background.get',
	imCallBackgroundCommit: 'im.v2.Call.Background.commit',
	imCallBackgroundDelete: 'im.v2.Call.Background.delete',
	imCallMaskGet: 'im.v2.Call.Mask.get',
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
