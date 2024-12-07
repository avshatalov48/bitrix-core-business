export const EventType = Object.freeze({
	layout:
	{
		onLayoutChange: 'IM.Layout:onLayoutChange',
		onOpenChat: 'IM.Layout:onOpenChat',
		onOpenNotifications: 'IM.Layout:onOpenNotifications',
	},
	dialog:
	{
		onDialogInited: 'IM.Dialog:onDialogInited',
		onMessageDeleted: 'IM.Dialog:onMessageDeleted',
		onMessageIsVisible: 'IM.Dialog:onMessageIsVisible',
		onMessageIsNotVisible: 'IM.Dialog:onMessageIsNotVisible',

		scrollToBottom: 'IM.Dialog:scrollToBottom',
		goToMessageContext: 'IM.Dialog:goToMessageContext',
		onClickMessageContextMenu: 'IM.Dialog:onClickMessageContextMenu',
		showForwardPopup: 'IM.Dialog:showForwardPopup',
		openComments: 'IM.Dialog:openComments',
		closeComments: 'IM.Dialog:closeComments',
		showLoadingBar: 'IM.Dialog:showLoadingBar',
		hideLoadingBar: 'IM.Dialog:hideLoadingBar',
		showQuoteButton: 'IM.Dialog:showQuoteButton',

		errors: {
			accessDenied: 'IM.Dialog.errors:accessDenied',
		},
	},
	textarea:
	{
		editMessage: 'IM.Textarea:editMessage',
		replyMessage: 'IM.Textarea:replyMessage',
		insertText: 'IM.Textarea:insertText',
		insertMention: 'IM.Textarea:insertMention',
		insertForward: 'IM.Textarea:insertForward',
		sendMessage: 'IM.Textarea:sendMessage',
		onAfterSendMessage: 'IM.Textarea:onAfterSendMessage',
		openUploadPreview: 'IM.Textarea:openUploadPreview',
	},
	uploader:
	{
		cancel: 'IM.Uploader:cancel',
	},
	call:
	{
		onFold: 'CallController::onFold',
		onViewStateChanged: 'IM.Call:onViewStateChanged',
	},
	search:
	{
		close: 'IM.Search:close',
		keyPressed: 'IM.Search:keyPressed',
	},
	recent:
	{
		openSearch: 'IM.Recent:openSearch',
	},
	sidebar:
	{
		open: 'IM.Sidebar:open',
		close: 'IM.Sidebar:close',
	},
	mention:
	{
		selectItem: 'IM.Mention:selectItem',
	},
	counter:
	{
		onNotificationCounterChange: 'onImUpdateCounterNotify',
		onChatCounterChange: 'onImUpdateCounterMessage',
		onLinesCounterChange: 'onImUpdateCounterLines',
		onImUpdateCounter: 'onImUpdateCounter',
	},
	desktop:
	{
		onInit: 'onDesktopInit',
		onReload: 'onDesktopReload',
		onSyncPause: 'onDesktopSyncPause',
		onUserAway: 'BXUserAway',
		onWakeUp: 'BXWakeAction',
		onBxLink: 'BXProtocolUrl',
		onExit: 'BXExitApplication',
		onIconClick: 'BXApplicationClick',
		onNewTabClick: 'BXNewTabClick',
	},
	lines:
	{
		onInit: 'onLinesInit',
		openChat: 'openLinesChat',
		onChatOpen: 'onLinesChatOpen',
	},
	slider:
	{
		onClose: 'onChatSliderClose',
	},
	request:
	{
		onAuthError: 'IM.request:onAuthError',
	},
});
