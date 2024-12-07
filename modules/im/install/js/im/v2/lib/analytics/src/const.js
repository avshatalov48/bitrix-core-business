export const CopilotChatType = Object.freeze({
	private: 'chatType_private',
	multiuser: 'chatType_multiuser',
});

export const AnalyticsEvent = Object.freeze({
	openMessenger: 'open_messenger',
	openChat: 'open_chat',
	createNewChat: 'create_new_chat',
	audioUse: 'audio_use',
	openTab: 'open_tab',
	popupOpen: 'popup_open',
	openPrices: 'open_prices',
	openSettings: 'open_settings',
	clickCreateNew: 'click_create_new',
	openExisting: 'open_existing',
	clickDelete: 'click_delete',
	cancelDelete: 'cancel_delete',
	delete: 'delete',
	view: 'view',
	click: 'click',
	clickEdit: 'click_edit',
	submitEdit: 'submit_edit',
	clickCallButton: 'click_call_button',
	clickStartConf: 'click_start_conf',
	clickJoin: 'click_join',
});

export const AnalyticsTool = Object.freeze({
	ai: 'ai',
	checkin: 'checkin',
	im: 'im',
	infoHelper: 'InfoHelper',
});

export const AnalyticsCategory = Object.freeze({
	chatOperations: 'chat_operations',
	shift: 'shift',
	messenger: 'messenger',
	chat: 'chat',
	channel: 'channel',
	videoconf: 'videoconf',
	copilot: 'copilot',
	limit: 'limit',
	limitBanner: 'limit_banner',
	toolOff: 'tool_off',
	message: 'message',
	chatPopup: 'chat_popup',
	call: 'call',
});

export const AnalyticsType = Object.freeze({
	ai: 'ai',
	chat: 'chat',
	channel: 'channel',
	videoconf: 'videoconf',
	copilot: 'copilot',
	deletedMessage: 'deleted_message',
	limitOfficeChatingHistory: 'limit_office_chating_history',
	privateCall: 'private',
	groupCall: 'group',
});

export const AnalyticsSection = Object.freeze({
	copilotTab: 'copilot_tab',
	chat: 'chat',
	chatStart: 'chat_start',
	chatHistory: 'chat_history',
	sidebar: 'sidebar',
	popup: 'popup',
	activeChat: 'active_chat',
	comments: 'comments',
});

export const AnalyticsSubSection = Object.freeze({
	contextMenu: 'context_menu',
	sidebar: 'sidebar',
	chatWindow: 'chat_window',
	messageLink: 'message_link',
	chatSidebar: 'chat_sidebar',
	chatList: 'chat_list',
	window: 'window',
});

export const AnalyticsElement = Object.freeze({
	initialBanner: 'initial_banner',
	videocall: 'videocall',
	audiocall: 'audiocall',
	startButton: 'start_button',
});

export const AnalyticsStatus = Object.freeze({
	success: 'success',
	errorTurnedOff: 'error_turnedoff',
});
