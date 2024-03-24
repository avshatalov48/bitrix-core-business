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

		scrollToBottom: 'IM.Dialog:scrollToBottom',
		goToMessageContext: 'IM.Dialog:goToMessageContext',
		onClickMessageContextMenu: 'IM.Dialog:onClickMessageContextMenu',
		showForwardPopup: 'IM.Dialog:showForwardPopup',

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
		openContextMenu: 'IM.Search:openContextMenu',
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
