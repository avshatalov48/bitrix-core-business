/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

	const RestMethod = Object.freeze({
	  imV2ChatLoad: 'im.v2.Chat.load',
	  imV2ChatGetDialogId: 'im.v2.Chat.getDialogId',
	  imV2ChatShallowLoad: 'im.v2.Chat.shallowLoad',
	  imV2ChatLoadInContext: 'im.v2.Chat.loadInContext',
	  imV2ChatLoadContext: 'im.v2.Chat.loadInContext',
	  imV2ChatAdd: 'im.v2.Chat.add',
	  imV2ChatUpdate: 'im.v2.Chat.update',
	  imV2ChatRead: 'im.v2.Chat.read',
	  imV2ChatReadAll: 'im.v2.Chat.readAll',
	  imV2ChatUnread: 'im.v2.Chat.unread',
	  imV2ChatJoin: 'im.v2.Chat.join',
	  imV2ChatDeleteUser: 'im.v2.Chat.deleteUser',
	  imV2ChatExtendPullWatch: 'im.v2.Chat.extendPullWatch',
	  imV2ChatMessageGetContext: 'im.v2.Chat.Message.getContext',
	  imV2ChatMessageList: 'im.v2.Chat.Message.list',
	  imV2ChatMessageTail: 'im.v2.Chat.Message.tail',
	  imV2ChatMessageRead: 'im.v2.Chat.Message.read',
	  imV2ChatMessageMark: 'im.v2.Chat.Message.mark',
	  imV2ChatMessageDelete: 'im.v2.Chat.Message.delete',
	  imV2ChatMessageReactionAdd: 'im.v2.Chat.Message.Reaction.add',
	  imV2ChatMessageReactionDelete: 'im.v2.Chat.Message.Reaction.delete',
	  imV2ChatMessageReactionTail: 'im.v2.Chat.Message.Reaction.tail',
	  imV2ChatMessagePin: 'im.v2.Chat.Message.pin',
	  imV2ChatMessageUnpin: 'im.v2.Chat.Message.unpin',
	  imV2ChatMessageTailViewers: 'im.v2.Chat.Message.tailViewers',
	  imV2ChatMessageDeleteRichUrl: 'im.v2.Chat.Message.deleteRichUrl',
	  imV2ChatPinTail: 'im.v2.Chat.Pin.tail',
	  imV2SettingsGeneralUpdate: 'im.v2.Settings.General.update',
	  imV2DesktopLogout: 'im.v2.Desktop.logout',
	  imV2UpdateState: 'im.v2.UpdateState.getStateData',
	  imV2BetaEnable: 'im.v2.Beta.enable',
	  imV2BetaDisable: 'im.v2.Beta.disable',
	  imCallBetaCreateRoom: 'im.call.beta.createRoom',
	  imMessageAdd: 'im.message.add',
	  imMessageUpdate: 'im.message.update',
	  imChatMute: 'im.chat.mute',
	  imChatUpdateTitle: 'im.chat.updateTitle',
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
	  imDialogWriting: 'im.dialog.writing',
	  imDialogUsersList: 'im.dialog.users.list',
	  imDialogMessagesSearch: 'im.dialog.messages.search',
	  imUserGet: 'im.user.get',
	  imUserListGet: 'im.user.list.get',
	  imUserStatusSet: 'im.user.status.set',
	  imUserStatusIdleStart: 'im.user.status.idle.start',
	  imUserStatusIdleEnd: 'im.user.status.idle.end',
	  imDiskFolderGet: 'im.disk.folder.get',
	  imDiskFolderListGet: 'im.disk.folder.list.get',
	  imDiskFilePreviewUpload: 'disk.api.file.attachPreview',
	  imDiskFileCommit: 'im.disk.file.commit',
	  imDiskFileDelete: 'im.disk.file.delete',
	  imDiskFileSave: 'im.disk.file.save',
	  imRecentGet: 'im.recent.get',
	  imRecentList: 'im.recent.list',
	  imRecentPin: 'im.recent.pin',
	  imRecentHide: 'im.recent.hide',
	  imNotifyGet: 'im.notify.get',
	  imNotifyRead: 'im.notify.read',
	  imNotifySchemaGet: 'im.notify.schema.get',
	  imNotifyHistorySearch: 'im.notify.history.search',
	  imNotifyAnswer: 'im.notify.answer',
	  imCallBackgroundGet: 'im.v2.Call.Background.get',
	  imCallBackgroundCommit: 'im.v2.Call.Background.commit',
	  imCallBackgroundDelete: 'im.v2.Call.Background.delete',
	  imCallMaskGet: 'im.v2.Call.Mask.get',
	  imSmilesGet: 'smile.get',
	  imPromotionRead: 'im.promotion.read',
	  imBotGiphyListPopular: 'imbot.Giphy.listPopular',
	  imBotGiphyList: 'imbot.Giphy.list',
	  linesDialogGet: 'imopenlines.dialog.get'
	});

	const EventType = Object.freeze({
	  layout: {
	    onLayoutChange: 'IM.Layout:onLayoutChange',
	    onOpenChat: 'IM.Layout:onOpenChat',
	    onOpenNotifications: 'IM.Layout:onOpenNotifications'
	  },
	  dialog: {
	    onDialogInited: 'IM.Dialog:onDialogInited',
	    scrollToBottom: 'IM.Dialog:scrollToBottom',
	    goToMessageContext: 'IM.Dialog:goToMessageContext',
	    onClickMessageContextMenu: 'IM.Dialog:onClickMessageContextMenu',
	    errors: {
	      accessDenied: 'IM.Dialog.errors:accessDenied'
	    }
	  },
	  textarea: {
	    editMessage: 'IM.Textarea:editMessage',
	    replyMessage: 'IM.Textarea:replyMessage',
	    insertText: 'IM.Textarea:insertText',
	    insertMention: 'IM.Textarea:insertMention'
	  },
	  uploader: {
	    cancel: 'IM.Uploader:cancel'
	  },
	  call: {
	    onFold: 'CallController::onFold',
	    onViewStateChanged: 'IM.Call:onViewStateChanged'
	  },
	  search: {
	    close: 'IM.Search:close',
	    keyPressed: 'IM.Search:keyPressed',
	    openContextMenu: 'IM.Search:openContextMenu'
	  },
	  recent: {
	    openSearch: 'IM.Recent:openSearch'
	  },
	  sidebar: {
	    open: 'IM.Sidebar:open',
	    close: 'IM.Sidebar:close'
	  },
	  mention: {
	    openChatInfo: 'IM.Mention:openChatInfo',
	    selectFirstItem: 'IM.Mention:selectFirstItem'
	  },
	  counter: {
	    onNotificationCounterChange: 'onImUpdateCounterNotify',
	    onChatCounterChange: 'onImUpdateCounterMessage',
	    onLinesCounterChange: 'onImUpdateCounterLines'
	  },
	  desktop: {
	    onInit: 'onDesktopInit',
	    onReload: 'onDesktopReload',
	    onUserAway: 'BXUserAway',
	    onWakeUp: 'BXWakeAction',
	    onBxLink: 'BXProtocolUrl',
	    onExit: 'BXExitApplication',
	    onIconClick: 'BXApplicationClick'
	  },
	  lines: {
	    onInit: 'onLinesInit',
	    openChat: 'openLinesChat',
	    onChatOpen: 'onLinesChatOpen'
	  },
	  slider: {
	    onClose: 'onChatSliderClose'
	  }
	});

	const DialogType = Object.freeze({
	  user: 'user',
	  chat: 'chat',
	  open: 'open',
	  general: 'general',
	  videoconf: 'videoconf',
	  announcement: 'announcement',
	  call: 'call',
	  support24Notifier: 'support24Notifier',
	  support24Question: 'support24Question',
	  crm: 'crm',
	  sonetGroup: 'sonetGroup',
	  calendar: 'calendar',
	  tasks: 'tasks',
	  thread: 'thread',
	  mail: 'mail',
	  lines: 'lines'
	});
	const DialogScrollThreshold = Object.freeze({
	  none: 'none',
	  nearTheBottom: 'nearTheBottom',
	  halfScreenUp: 'halfScreenUp'
	});
	const DialogBlockType = Object.freeze({
	  dateGroup: 'dateGroup',
	  authorGroup: 'authorGroup',
	  newMessages: 'newMessages',
	  markedMessages: 'markedMessages'
	});
	const DialogAlignment = Object.freeze({
	  left: 'left',
	  center: 'center'
	});

	const FileStatus = Object.freeze({
	  upload: 'upload',
	  wait: 'wait',
	  progress: 'progress',
	  done: 'done',
	  error: 'error'
	});
	const FileType = Object.freeze({
	  image: 'image',
	  video: 'video',
	  audio: 'audio',
	  file: 'file'
	});
	const FileIconType = Object.freeze({
	  file: 'file',
	  image: 'image',
	  audio: 'audio',
	  video: 'video',
	  code: 'code',
	  call: 'call',
	  attach: 'attach',
	  quote: 'quote'
	});

	const MessageType = Object.freeze({
	  self: 'self',
	  opponent: 'opponent',
	  system: 'system'
	});
	const MessageComponent = Object.freeze({
	  default: 'DefaultMessage',
	  file: 'FileMessage',
	  smile: 'SmileMessage',
	  unsupported: 'UnsupportedMessage',
	  deleted: 'DeletedMessage',
	  callInvite: 'CallInviteMessage',
	  chatCreation: 'ChatCreationMessage',
	  conferenceCreation: 'ConferenceCreationMessage',
	  system: 'SystemMessage'
	});
	const MessageMentionType = Object.freeze({
	  user: 'USER',
	  chat: 'CHAT',
	  lines: 'LINES',
	  context: 'CONTEXT',
	  call: 'CALL'
	});
	const MessageStatus = {
	  received: 'received',
	  delivered: 'delivered',
	  error: 'error'
	};
	const OwnMessageStatus = Object.freeze({
	  sending: 'sending',
	  sent: 'sent',
	  viewed: 'viewed'
	});

	const RecentCallStatus = {
	  waiting: 'waiting',
	  joined: 'joined'
	};

	const NotificationTypesCodes = Object.freeze({
	  confirm: 1,
	  simple: 3
	});

	const Layout = Object.freeze({
	  chat: {
	    name: 'chat',
	    list: 'RecentListContainer',
	    content: 'ChatContent'
	  },
	  createChat: {
	    name: 'createChat',
	    list: 'RecentListContainer',
	    content: 'CreateChatContent'
	  },
	  notification: {
	    name: 'notification',
	    list: 'RecentListContainer',
	    content: 'NotificationContent'
	  },
	  openlines: {
	    name: 'openlines',
	    list: '',
	    content: 'OpenlineContent'
	  },
	  conference: {
	    name: 'conference',
	    list: 'RecentListContainer',
	    content: 'ChatContent'
	  },
	  call: {
	    name: 'call',
	    list: 'RecentListContainer',
	    content: 'ChatContent'
	  },
	  settings: {
	    name: 'settings',
	    list: '',
	    content: 'SettingsContent'
	  },
	  market: {
	    name: 'market',
	    list: '',
	    content: 'MarketContent'
	  }
	});

	const SearchEntityIdTypes = {
	  user: 'user',
	  imUser: 'im-user',
	  bot: 'im-bot',
	  chat: 'im-chat',
	  chatUser: 'im-chat-user',
	  department: 'department',
	  network: 'imbot-network'
	};

	const UserStatus = {
	  offline: 'offline',
	  online: 'online',
	  mobileOnline: 'mobile-online',
	  away: 'away',
	  idle: 'idle',
	  dnd: 'dnd',
	  break: 'break'
	};
	const UserExternalType = {
	  default: 'default',
	  bot: 'bot',
	  call: 'call'
	};
	const UserRole = {
	  guest: 'guest',
	  member: 'member',
	  manager: 'manager',
	  owner: 'owner',
	  none: 'none'
	};

	const SidebarBlock = Object.freeze({
	  main: 'main',
	  info: 'info',
	  task: 'task',
	  brief: 'brief',
	  file: 'file',
	  fileUnsorted: 'fileUnsorted',
	  sign: 'sign',
	  meeting: 'meeting',
	  market: 'market',
	  messageSearch: 'messageSearch'
	});
	const SidebarDetailBlock = Object.freeze({
	  main: 'main',
	  link: 'link',
	  favorite: 'favorite',
	  task: 'task',
	  brief: 'brief',
	  media: 'media',
	  audio: 'audio',
	  document: 'document',
	  fileUnsorted: 'fileUnsorted',
	  other: 'other',
	  sign: 'sign',
	  meeting: 'meeting',
	  market: 'market',
	  messageSearch: 'messageSearch'
	});
	const SidebarFileTypes = Object.freeze({
	  media: 'media',
	  audio: 'audio',
	  document: 'document',
	  other: 'other',
	  brief: 'brief',
	  fileUnsorted: 'fileUnsorted'
	});
	const SidebarFileTabTypes = Object.freeze({
	  [SidebarFileTypes.media]: SidebarFileTypes.media,
	  [SidebarFileTypes.audio]: SidebarFileTypes.audio,
	  [SidebarFileTypes.document]: SidebarFileTypes.document,
	  [SidebarFileTypes.brief]: SidebarFileTypes.brief,
	  [SidebarFileTypes.other]: SidebarFileTypes.other
	});

	const Color = Object.freeze({
	  base: '#17a3ea',
	  transparent: 'transparent'
	});

	const AttachType = Object.freeze({
	  Delimiter: 'DELIMITER',
	  File: 'FILE',
	  Grid: 'GRID',
	  Html: 'HTML',
	  Image: 'IMAGE',
	  Link: 'LINK',
	  Message: 'MESSAGE',
	  Rich: 'RICH_LINK',
	  User: 'USER'
	});
	const AttachDescription = Object.freeze({
	  FIRST_MESSAGE: 'FIRST_MESSAGE',
	  SKIP_MESSAGE: 'SKIP_MESSAGE'
	});

	const DesktopFeature = {
	  mask: {
	    id: 'mask',
	    availableFromVersion: 72
	  }
	};
	const DesktopBxLink = {
	  chat: 'chat',
	  lines: 'lines',
	  call: 'call',
	  phone: 'phone',
	  conference: 'conference',
	  callList: 'callList',
	  notifications: 'notifications',
	  recentSearch: 'recentSearch',
	  timeManager: 'timemanpwt'
	};
	const LegacyDesktopBxLink = {
	  messenger: 'messenger',
	  chat: 'chat',
	  videoconf: 'videoconf',
	  notify: 'notify',
	  callTo: 'callto',
	  callList: 'calllist'
	};

	const LocalStorageKey = Object.freeze({
	  draft: 'draft',
	  smileLastUpdateTime: 'smileLastUpdateTime',
	  sidebarOpened: 'sidebarOpened',
	  textareaMarketOpened: 'textareaMarketOpened',
	  textareaHeight: 'textareaHeight',
	  lastCallType: 'lastCallType'
	});

	const PlacementType = Object.freeze({
	  contextMenu: 'IM_CONTEXT_MENU',
	  navigation: 'IM_NAVIGATION',
	  textarea: 'IM_TEXTAREA',
	  sidebar: 'IM_SIDEBAR',
	  smilesSelector: 'IM_SMILES_SELECTOR'
	});

	const PopupType = Object.freeze({
	  userProfile: 'im-user-settings-popup',
	  userStatus: 'im-user-status-popup',
	  backgroundSelect: 'im-background-select-popup',
	  recentContextMenu: 'im-recent-context-menu',
	  recentHeaderMenu: 'im-recent-header-menu',
	  createChatMenu: 'im-create-chat-menu',
	  dialogMessageMenu: 'bx-im-message-context-menu',
	  dialogAvatarMenu: 'bx-im-avatar-context-menu',
	  dialogReactionUsers: 'bx-im-message-reaction-users',
	  dialogReadUsers: 'bx-im-dialog-read-users',
	  createChatManageUsersMenu: 'im-content-create-chat-manage-users',
	  createChatManageUiMenu: 'im-content-create-chat-manage-ui',
	  createChatCanPostMenu: 'im-content-create-chat-can-post',
	  messageBaseFileMenu: 'im-message-base-file-context-menu'
	});

	const Settings = Object.freeze({
	  appearance: {
	    background: 'backgroundImageId',
	    alignment: 'chatAlignment'
	  },
	  notification: {
	    enableSound: 'enableSound'
	  },
	  hotkey: {
	    sendByEnter: 'sendByEnter'
	  },
	  message: {
	    bigSmiles: 'enableBigSmile'
	  },
	  recent: {
	    showBirthday: 'viewBirthday',
	    showInvited: 'viewCommonUsers',
	    showLastMessage: 'viewLastMessage'
	  },
	  desktop: {
	    enableRedirect: 'openDesktopFromPanel'
	  }
	});
	const SettingsSection = Object.freeze({
	  appearance: 'appearance',
	  notification: 'notification',
	  hotkey: 'hotkey',
	  message: 'message',
	  recent: 'recent',
	  desktop: 'desktop'
	});

	const SoundType = {
	  reminder: 'reminder',
	  newMessage1: 'newMessage1',
	  newMessage2: 'newMessage2',
	  send: 'send',
	  dialtone: 'dialtone',
	  ringtone: 'ringtone',
	  start: 'start',
	  stop: 'stop',
	  error: 'error'
	};

	const PromoId = Object.freeze({
	  copilot: 'im:ai:15062023:all',
	  createGroupChat: 'im:group-chat-create:20062023:all',
	  createConference: 'im:conference-create:24082023:all'
	});

	const ChatActionType = Object.freeze({
	  avatar: 'avatar',
	  call: 'call',
	  extend: 'extend',
	  leave: 'leave',
	  leaveOwner: 'leaveOwner',
	  kick: 'kick',
	  mute: 'mute',
	  rename: 'rename',
	  send: 'send',
	  userList: 'userList'
	});
	const ChatActionGroup = Object.freeze({
	  manageSettings: 'manageSettings',
	  manageUi: 'manageUi',
	  manageUsers: 'manageUsers',
	  canPost: 'canPost'
	});

	const BotType = Object.freeze({
	  bot: 'bot',
	  network: 'network',
	  support24: 'support24'
	});

	const GetParameter = {
	  openNotifications: 'IM_NOTIFY',
	  openHistory: 'IM_HISTORY',
	  openChat: 'IM_DIALOG',
	  openLines: 'IM_LINES',
	  desktopChatTabMode: 'IM_TAB',
	  backgroundType: 'IM_BACKGROUND'
	};

	// noinspection ES6PreferShortImport
	const PathPlaceholder = {
	  dialog: `/online/?${GetParameter.openChat}=#DIALOG_ID#`,
	  lines: `/online/?${GetParameter.openLines}=#DIALOG_ID#`
	};

	const CallViewState = {
	  opened: 'Opened',
	  closed: 'Closed',
	  folded: 'Folded'
	};

	exports.RestMethod = RestMethod;
	exports.EventType = EventType;
	exports.DialogType = DialogType;
	exports.DialogBlockType = DialogBlockType;
	exports.DialogScrollThreshold = DialogScrollThreshold;
	exports.DialogAlignment = DialogAlignment;
	exports.FileStatus = FileStatus;
	exports.FileType = FileType;
	exports.FileIconType = FileIconType;
	exports.MessageType = MessageType;
	exports.MessageComponent = MessageComponent;
	exports.MessageMentionType = MessageMentionType;
	exports.MessageStatus = MessageStatus;
	exports.OwnMessageStatus = OwnMessageStatus;
	exports.RecentCallStatus = RecentCallStatus;
	exports.NotificationTypesCodes = NotificationTypesCodes;
	exports.Layout = Layout;
	exports.SearchEntityIdTypes = SearchEntityIdTypes;
	exports.UserStatus = UserStatus;
	exports.UserExternalType = UserExternalType;
	exports.UserRole = UserRole;
	exports.SidebarDetailBlock = SidebarDetailBlock;
	exports.SidebarBlock = SidebarBlock;
	exports.SidebarFileTabTypes = SidebarFileTabTypes;
	exports.SidebarFileTypes = SidebarFileTypes;
	exports.Color = Color;
	exports.AttachType = AttachType;
	exports.AttachDescription = AttachDescription;
	exports.DesktopFeature = DesktopFeature;
	exports.DesktopBxLink = DesktopBxLink;
	exports.LegacyDesktopBxLink = LegacyDesktopBxLink;
	exports.LocalStorageKey = LocalStorageKey;
	exports.PlacementType = PlacementType;
	exports.PopupType = PopupType;
	exports.Settings = Settings;
	exports.SettingsSection = SettingsSection;
	exports.SoundType = SoundType;
	exports.PromoId = PromoId;
	exports.ChatActionType = ChatActionType;
	exports.ChatActionGroup = ChatActionGroup;
	exports.BotType = BotType;
	exports.PathPlaceholder = PathPlaceholder;
	exports.GetParameter = GetParameter;
	exports.CallViewState = CallViewState;

}((this.BX.Messenger.v2.Const = this.BX.Messenger.v2.Const || {})));
//# sourceMappingURL=registry.bundle.js.map
