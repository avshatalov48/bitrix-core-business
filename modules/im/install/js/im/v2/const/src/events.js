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

		scrollToBottom: 'IM.Dialog:scrollToBottom',
		goToMessageContext: 'IM.Dialog:goToMessageContext',
		onClickMessageContextMenu: 'IM.Dialog:onClickMessageContextMenu',

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
		openChatInfo: 'IM.Mention:openChatInfo',
		selectFirstItem: 'IM.Mention:selectFirstItem',
	},
	counter:
	{
		onNotificationCounterChange: 'onImUpdateCounterNotify',
		onChatCounterChange: 'onImUpdateCounterMessage',
		onLinesCounterChange: 'onImUpdateCounterLines',
	},
	desktop:
	{
		onInit: 'onDesktopInit',
		onReload: 'onDesktopReload',
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
});
