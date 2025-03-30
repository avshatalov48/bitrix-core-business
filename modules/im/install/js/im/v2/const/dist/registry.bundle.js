/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,imopenlines_v2_const,im_v2_const) {
	'use strict';

	const RestMethod = Object.freeze({
	  imV2ChatLoad: 'im.v2.Chat.load',
	  imV2ChatGetDialogId: 'im.v2.Chat.getDialogId',
	  imV2ChatShallowLoad: 'im.v2.Chat.shallowLoad',
	  imV2ChatLoadInContext: 'im.v2.Chat.loadInContext',
	  imV2ChatLoadContext: 'im.v2.Chat.loadInContext',
	  imV2ChatAdd: 'im.v2.Chat.add',
	  imV2ChatUpdate: 'im.v2.Chat.update',
	  imV2ChatDelete: 'im.v2.Chat.delete',
	  imV2ChatUpdateAvatar: 'im.v2.Chat.updateAvatar',
	  imV2ChatRead: 'im.v2.Chat.read',
	  imV2ChatReadAll: 'im.v2.Chat.readAll',
	  imV2ChatUnread: 'im.v2.Chat.unread',
	  imV2ChatJoin: 'im.v2.Chat.join',
	  imV2ChatDeleteUser: 'im.v2.Chat.deleteUser',
	  imV2ChatExtendPullWatch: 'im.v2.Chat.extendPullWatch',
	  imV2RecentChannelExtendPullWatch: 'im.v2.Recent.Channel.extendPullWatch',
	  imV2ChatMessageGetContext: 'im.v2.Chat.Message.getContext',
	  imV2ChatMessageSend: 'im.v2.Chat.Message.send',
	  imV2ChatMessageList: 'im.v2.Chat.Message.list',
	  imV2ChatMessageTail: 'im.v2.Chat.Message.tail',
	  imV2ChatMessageRead: 'im.v2.Chat.Message.read',
	  imV2ChatMessageMark: 'im.v2.Chat.Message.mark',
	  imV2ChatMessageDelete: 'im.v2.Chat.Message.delete',
	  imV2ChatMessageUpdate: 'im.v2.Chat.Message.update',
	  imV2ChatMessageReactionAdd: 'im.v2.Chat.Message.Reaction.add',
	  imV2ChatMessageReactionDelete: 'im.v2.Chat.Message.Reaction.delete',
	  imV2ChatMessageReactionTail: 'im.v2.Chat.Message.Reaction.tail',
	  imV2ChatMessagePin: 'im.v2.Chat.Message.pin',
	  imV2ChatMessageUnpin: 'im.v2.Chat.Message.unpin',
	  imV2ChatMessageTailViewers: 'im.v2.Chat.Message.tailViewers',
	  imV2ChatMessageDeleteRichUrl: 'im.v2.Chat.Message.deleteRichUrl',
	  imV2ChatMessageCommentInfoList: 'im.v2.Chat.Message.CommentInfo.list',
	  imV2ChatPinTail: 'im.v2.Chat.Pin.tail',
	  imV2ChatUserList: 'im.v2.Chat.User.list',
	  imV2ChatListShared: 'im.v2.Chat.listShared',
	  imV2ChatCommentSubscribe: 'im.v2.Chat.Comment.subscribe',
	  imV2ChatCommentUnsubscribe: 'im.v2.Chat.Comment.unsubscribe',
	  imV2ChatCommentReadAll: 'im.v2.Chat.Comment.readAll',
	  imV2ChatAddManagers: 'im.v2.Chat.addManagers',
	  imV2ChatDeleteManagers: 'im.v2.Chat.deleteManagers',
	  imV2SettingsGeneralUpdate: 'im.v2.Settings.General.update',
	  imV2SettingsNotifyUpdate: 'im.v2.Settings.Notify.update',
	  imV2SettingsNotifySwitchScheme: 'im.v2.Settings.Notify.switchScheme',
	  imV2DesktopLogout: 'im.v2.Desktop.logout',
	  imV2UpdateState: 'im.v2.UpdateState.getStateData',
	  imV2BetaEnable: 'im.v2.Beta.enable',
	  imV2BetaDisable: 'im.v2.Beta.disable',
	  imV2ChatTaskPrepare: 'im.v2.Chat.Task.prepare',
	  imV2RecentChannelTail: 'im.v2.Recent.Channel.Tail',
	  imV2RecentCollabTail: 'im.v2.Recent.Collab.Tail',
	  imV2ChatCopilotUpdateRole: 'im.v2.Chat.Copilot.updateRole',
	  imV2AccessCheck: 'im.v2.Access.check',
	  imV2ChatMemberEntitiesList: 'im.v2.Chat.MemberEntities.list',
	  imV2DiskFileSave: 'im.v2.Disk.File.save',
	  imV2CallZoomCreate: 'im.v2.Call.Zoom.create',
	  imCallBetaCreateRoom: 'im.call.beta.createRoom',
	  imMessageAdd: 'im.message.add',
	  imMessageCommand: 'im.message.command',
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
	  imBotDialogVote: 'imbot.dialog.vote',
	  imBotNetworkChatCount: 'imbot.Network.Chat.count',
	  imBotNetworkChatList: 'imbot.Network.Chat.list',
	  imBotNetworkChatAdd: 'imbot.Network.Chat.add',
	  linesDialogGet: 'imopenlines.dialog.get',
	  socialnetworkCollabCreate: 'socialnetwork.collab.Collab.add',
	  socialnetworkCollabUpdate: 'socialnetwork.collab.Collab.update',
	  socialnetworkCollabDelete: 'socialnetwork.collab.Collab.delete',
	  socialnetworkMemberAdd: 'socialnetwork.collab.Member.add',
	  socialnetworkMemberDelete: 'socialnetwork.collab.Member.delete',
	  socialnetworkMemberLeave: 'socialnetwork.collab.Member.leave',
	  intranetInviteGetLinkByCollabId: 'intranet.invite.getLinkByCollabId',
	  intranetInviteRegenerateLinkByCollabId: 'intranet.invite.regenerateLinkByCollabId'
	});

	const EventType = Object.freeze({
	  layout: {
	    onLayoutChange: 'IM.Layout:onLayoutChange',
	    onOpenChat: 'IM.Layout:onOpenChat',
	    onOpenNotifications: 'IM.Layout:onOpenNotifications'
	  },
	  header: {
	    openAddToChatPopup: 'IM.Header:openAddToChatPopup'
	  },
	  dialog: {
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
	    openBulkActionsMode: 'IM.Dialog:openBulkActionsMode',
	    closeBulkActionsMode: 'IM.Dialog:closeBulkActionsMode',
	    errors: {
	      accessDenied: 'IM.Dialog.errors:accessDenied'
	    }
	  },
	  textarea: {
	    editMessage: 'IM.Textarea:editMessage',
	    replyMessage: 'IM.Textarea:replyMessage',
	    forwardEntity: 'IM.Textarea:forwardEntity',
	    insertText: 'IM.Textarea:insertText',
	    insertMention: 'IM.Textarea:insertMention',
	    insertForward: 'IM.Textarea:insertForward',
	    sendMessage: 'IM.Textarea:sendMessage',
	    onAfterSendMessage: 'IM.Textarea:onAfterSendMessage',
	    openUploadPreview: 'IM.Textarea:openUploadPreview'
	  },
	  uploader: {
	    cancel: 'IM.Uploader:cancel'
	  },
	  call: {
	    onFold: 'CallController::onFold',
	    onViewStateChanged: 'IM.Call:onViewStateChanged',
	    onJoinFromRecentItem: 'IM.Call:onJoinFromRecentItem'
	  },
	  search: {
	    close: 'IM.Search:close',
	    keyPressed: 'IM.Search:keyPressed'
	  },
	  recent: {
	    openSearch: 'IM.Recent:openSearch'
	  },
	  sidebar: {
	    open: 'IM.Sidebar:open',
	    close: 'IM.Sidebar:close'
	  },
	  mention: {
	    selectItem: 'IM.Mention:selectItem'
	  },
	  counter: {
	    onNotificationCounterChange: 'onImUpdateCounterNotify',
	    onChatCounterChange: 'onImUpdateCounterMessage',
	    onLinesCounterChange: 'onImUpdateCounterLines',
	    onImUpdateCounter: 'onImUpdateCounter'
	  },
	  desktop: {
	    onInit: 'onDesktopInit',
	    onReload: 'onDesktopReload',
	    onSyncPause: 'onDesktopSyncPause',
	    onUserAway: 'BXUserAway',
	    onWakeUp: 'BXWakeAction',
	    onBxLink: 'BXProtocolUrl',
	    onExit: 'BXExitApplication',
	    onIconClick: 'BXApplicationClick',
	    onNewTabClick: 'BXNewTabClick'
	  },
	  lines: {
	    onInit: 'onLinesInit',
	    openChat: 'openLinesChat',
	    onChatOpen: 'onLinesChatOpen'
	  },
	  slider: {
	    onClose: 'onChatSliderClose'
	  },
	  request: {
	    onAuthError: 'IM.request:onAuthError'
	  },
	  audioPlayer: {
	    play: 'im:audioplayer:play',
	    pause: 'im:audioplayer:pause',
	    stop: 'im:audioplayer:stop',
	    preload: 'im:audioplayer:preload'
	  }
	});

	const ChatType = Object.freeze({
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
	  lines: 'lines',
	  copilot: 'copilot',
	  channel: 'channel',
	  openChannel: 'openChannel',
	  generalChannel: 'generalChannel',
	  comment: 'comment',
	  collab: 'collab',
	  openlinesV2: 'openlinesV2'
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
	const AudioPlaybackRate = Object.freeze({
	  1: 1,
	  1.5: 1.5,
	  2: 2
	});
	const AudioPlaybackState = Object.freeze({
	  play: 'play',
	  pause: 'pause',
	  stop: 'stop',
	  none: 'none'
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
	  zoomInvite: 'ZoomInviteMessage',
	  chatCreation: 'ChatCreationMessage',
	  ownChatCreation: 'OwnChatCreationMessage',
	  copilotCreation: 'ChatCopilotCreationMessage',
	  copilotMessage: 'CopilotMessage',
	  copilotAddedUsers: 'ChatCopilotAddedUsersMessage',
	  conferenceCreation: 'ConferenceCreationMessage',
	  supervisorUpdateFeature: 'SupervisorUpdateFeatureMessage',
	  supervisorEnableFeature: 'SupervisorEnableFeatureMessage',
	  sign: 'SignMessage',
	  checkIn: 'CheckInMessage',
	  supportVote: 'SupportVoteMessage',
	  supportSessionNumber: 'SupportSessionNumberMessage',
	  supportChatCreation: 'SupportChatCreationMessage',
	  system: 'SystemMessage',
	  channelPost: 'ChannelPost',
	  generalChatCreationMessage: 'GeneralChatCreationMessage',
	  generalChannelCreationMessage: 'GeneralChannelCreationMessage',
	  channelCreationMessage: 'ChannelCreationMessage',
	  callMessage: 'CallMessage',
	  voteMessage: 'VoteMessage',
	  ...imopenlines_v2_const.OpenLinesMessageComponent
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
	  viewed: 'viewed',
	  error: 'error'
	});
	const FakeMessagePrefix = 'temp';
	const FakeDraftMessagePrefix = 'temp-draft';

	const RecentCallStatus = {
	  waiting: 'waiting',
	  joined: 'joined'
	};

	const NotificationTypesCodes = Object.freeze({
	  confirm: 1,
	  simple: 3
	});
	const NotificationSettingsMode = {
	  simple: 'simple',
	  expert: 'expert'
	};

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
	  updateChat: {
	    name: 'updateChat',
	    list: 'RecentListContainer',
	    content: 'UpdateChatContent'
	  },
	  channel: {
	    name: 'channel',
	    list: 'ChannelListContainer',
	    content: 'ChatContent'
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
	  openlinesV2: {
	    name: 'openlinesV2',
	    list: 'OpenlineListContainer',
	    content: 'OpenlinesV2Content'
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
	  copilot: {
	    name: 'copilot',
	    list: 'CopilotListContainer',
	    content: 'CopilotContent'
	  },
	  collab: {
	    name: 'collab',
	    list: 'CollabListContainer',
	    content: 'ChatContent'
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
	const UserType = {
	  user: 'user',
	  bot: 'bot',
	  extranet: 'extranet',
	  collaber: 'collaber'
	};
	const UserRole = {
	  guest: 'guest',
	  member: 'member',
	  manager: 'manager',
	  owner: 'owner',
	  none: 'none'
	};
	const UserIdNetworkPrefix = 'network';

	const SidebarDetailBlock = Object.freeze({
	  main: 'main',
	  members: 'members',
	  link: 'link',
	  favorite: 'favorite',
	  task: 'task',
	  brief: 'brief',
	  media: 'media',
	  file: 'file',
	  audio: 'audio',
	  document: 'document',
	  fileUnsorted: 'fileUnsorted',
	  other: 'other',
	  meeting: 'meeting',
	  market: 'market',
	  messageSearch: 'messageSearch',
	  chatsWithUser: 'chatsWithUser',
	  multidialog: 'multidialog',
	  none: ''
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
	  transparent: 'transparent',
	  white: '#fff',
	  gray90: '#525c69',
	  collab70: '#00a94e',
	  collab60: '#19cc45',
	  collab50: '#6be860',
	  collab10: '#f2fee2',
	  orange50: '#ffa900'
	});
	const ColorToken = Object.freeze({
	  base: 'base',
	  primary: 'primary',
	  secondary: 'secondary',
	  alert: 'alert'
	});

	const AttachType = Object.freeze({
	  Delimiter: 'delimiter',
	  File: 'file',
	  Grid: 'grid',
	  Html: 'html',
	  Image: 'image',
	  Link: 'link',
	  Message: 'message',
	  Rich: 'richLink',
	  User: 'user'
	});
	const AttachDescription = Object.freeze({
	  firstMessage: 'FIRST_MESSAGE',
	  skipMessage: 'SKIP_MESSAGE'
	});

	const KeyboardButtonType = {
	  button: 'BUTTON',
	  newLine: 'NEWLINE'
	};
	const KeyboardButtonContext = {
	  all: 'ALL',
	  mobile: 'MOBILE',
	  desktop: 'DESKTOP'
	};
	const KeyboardButtonDisplay = {
	  block: 'BLOCK',
	  line: 'LINE'
	};
	const KeyboardButtonAction = {
	  put: 'PUT',
	  send: 'SEND',
	  copy: 'COPY',
	  call: 'CALL',
	  dialog: 'DIALOG'
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
	  timeManager: 'timemanpwt',
	  copilot: 'copilot',
	  collab: 'collab',
	  settings: 'settings',
	  openTab: 'openTab',
	  openPage: 'openPage',
	  chatCreation: 'chatCreation'
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
	  smileLastUpdateTime: 'smileLastUpdateTime',
	  sidebarOpened: 'sidebarOpened',
	  textareaMarketOpened: 'textareaMarketOpened',
	  textareaHeight: 'textareaHeight',
	  lastCallType: 'lastCallType',
	  lastNotificationId: 'lastNotificationId',
	  layoutConfig: 'layoutConfig',
	  audioPlaybackRate: 'audioPlaybackRate'
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
	  createChatManageUsersAddMenu: 'im-content-create-chat-manage-users-add',
	  createChatManageUsersDeleteMenu: 'im-content-create-chat-manage-users-delete',
	  createChatManageUiMenu: 'im-content-create-chat-manage-ui',
	  createChatManageMessagesMenu: 'im-content-create-chat-can-post',
	  messageBaseFileMenu: 'im-message-base-file-context-menu',
	  desktopItemMenu: 'im-navigation-desktop-item-context-menu',
	  messageHistoryLimit: 'im-message-history-limit-popup'
	});

	const Settings = Object.freeze({
	  appearance: {
	    background: 'backgroundImageId',
	    alignment: 'chatAlignment'
	  },
	  notification: {
	    enableSound: 'enableSound',
	    enableAutoRead: 'notifyAutoRead',
	    mode: 'notifyScheme',
	    enableWeb: 'notifySchemeSendSite',
	    enableMail: 'notifySchemeSendEmail',
	    enablePush: 'notifySchemeSendPush'
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
	  },
	  user: {
	    status: 'status'
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
	const NotificationSettingsType = {
	  web: 'site',
	  mail: 'mail',
	  push: 'push'
	};

	const SoundType = {
	  reminder: 'reminder',
	  newMessage1: 'newMessage1',
	  newMessage2: 'newMessage2',
	  send: 'send',
	  dialtone: 'dialtone',
	  ringtone: 'ringtone',
	  ringtoneModern: 'ringtoneModern',
	  start: 'start',
	  stop: 'stop',
	  error: 'error'
	};

	const PromoId = Object.freeze({
	  createGroupChat: 'im:group-chat-create:20062023:all',
	  createConference: 'im:conference-create:24082023:all',
	  createChannel: 'im:channel-create:04032024:all',
	  createCollabDescription: 'im:collab-create:12092024:all',
	  addUsersToCopilotChat: 'im:add-users-to-copilot-chat:09042024:all',
	  changeRoleCopilot: 'im:change-role-copilot-chat:09042024:all',
	  collabHelpdeskSidebar: 'im:collab-helpdesk-sidebar:30102024:all',
	  downloadSeveralFiles: 'im:download-several-files:22112024:all'
	});

	const ActionByRole = Object.freeze({
	  avatar: 'avatar',
	  call: 'call',
	  extend: 'extend',
	  leave: 'leave',
	  leaveOwner: 'leaveOwner',
	  kick: 'kick',
	  mute: 'mute',
	  rename: 'rename',
	  send: 'send',
	  deleteOthersMessage: 'deleteOthersMessage',
	  userList: 'userList',
	  changeOwner: 'changeOwner',
	  changeManagers: 'changeManagers',
	  update: 'update',
	  delete: 'delete',
	  readMessage: 'readMessage',
	  openComments: 'openComments',
	  subscribeToComments: 'subscribeToComments',
	  openSidebar: 'openSidebar',
	  pinMessage: 'pinMessage',
	  setReaction: 'setReaction',
	  createMeeting: 'createMeeting',
	  createTask: 'createTask',
	  openAvatarMenu: 'openAvatarMenu',
	  openMessageMenu: 'openMessageMenu',
	  openSidebarMenu: 'openSidebarMenu',
	  updateInviteLink: 'updateInviteLink',
	  createDocumentSign: 'createDocumentSign',
	  createCalendarSlots: 'createCalendarSlots'
	});
	const ChatActionGroup = Object.freeze({
	  manageSettings: 'manageSettings',
	  manageUi: 'manageUi',
	  manageUsersAdd: 'manageUsersAdd',
	  manageUsersDelete: 'manageUsersDelete',
	  manageMessages: 'manageMessages'
	});
	const ActionByUserType = Object.freeze({
	  getChannels: 'getChannels',
	  getMarket: 'getMarket',
	  getOpenlines: 'getOpenlines',
	  createCollab: 'createCollab',
	  createCopilot: 'createCopilot',
	  createChannel: 'createChannel',
	  createChat: 'createChat',
	  createConference: 'createConference',
	  leaveCollab: 'leaveCollab'
	});

	const RawBotType = Object.freeze({
	  bot: 'bot',
	  network: 'network',
	  support24: 'support24',
	  human: 'human',
	  openline: 'openline',
	  supervisor: 'supervisor'
	});
	const BotType = Object.freeze({
	  bot: 'bot',
	  network: 'network',
	  support24: 'support24'
	});
	const BotCode = Object.freeze({
	  marta: 'marta',
	  giphy: 'giphy',
	  copilot: 'copilot'
	});
	const BotCommand = Object.freeze({
	  activate: 'activate'
	});

	const GetParameter = {
	  openNotifications: 'IM_NOTIFY',
	  openHistory: 'IM_HISTORY',
	  openChat: 'IM_DIALOG',
	  openMessage: 'IM_MESSAGE',
	  openLines: 'IM_LINES',
	  openSettings: 'IM_SETTINGS',
	  openCopilotChat: 'IM_COPILOT',
	  openCollab: 'IM_COLLAB',
	  desktopChatTabMode: 'IM_TAB',
	  backgroundType: 'IM_BACKGROUND'
	};

	// noinspection ES6PreferShortImport
	const PathPlaceholder = {
	  dialog: `/online/?${GetParameter.openChat}=#DIALOG_ID#`,
	  lines: `/online/?${GetParameter.openLines}=#DIALOG_ID#`
	};

	const TextareaPanelType = {
	  edit: 'edit',
	  reply: 'reply',
	  forward: 'forward',
	  forwardEntity: 'forwardEntity',
	  market: 'market',
	  none: ''
	};

	const ChatEntityLinkType = Object.freeze({
	  tasks: 'TASKS',
	  sonetGroup: 'SONET_GROUP',
	  mail: 'MAIL',
	  calendar: 'CALENDAR',
	  contact: 'CONTACT',
	  deal: 'DEAL',
	  lead: 'LEAD',
	  dynamic: 'DYNAMIC'
	});

	const MultidialogStatus = Object.freeze({
	  new: 'new',
	  open: 'open',
	  close: 'close'
	});

	const SliderCode = {
	  copilotDisabled: 'limit_copilot_off',
	  historyLimited: 'limit_office_chating_history'
	};

	const CounterType = Object.freeze({
	  chat: 'chat',
	  comment: 'comment',
	  copilot: 'copilot',
	  openline: 'openline',
	  collab: 'collab'
	});

	const CollabEntityType = {
	  tasks: 'tasks',
	  files: 'files',
	  calendar: 'calendar'
	};

	exports.RestMethod = RestMethod;
	exports.EventType = EventType;
	exports.ChatType = ChatType;
	exports.DialogBlockType = DialogBlockType;
	exports.DialogScrollThreshold = DialogScrollThreshold;
	exports.DialogAlignment = DialogAlignment;
	exports.FileStatus = FileStatus;
	exports.FileType = FileType;
	exports.FileIconType = FileIconType;
	exports.AudioPlaybackRate = AudioPlaybackRate;
	exports.AudioPlaybackState = AudioPlaybackState;
	exports.MessageType = MessageType;
	exports.MessageComponent = MessageComponent;
	exports.MessageMentionType = MessageMentionType;
	exports.MessageStatus = MessageStatus;
	exports.OwnMessageStatus = OwnMessageStatus;
	exports.FakeMessagePrefix = FakeMessagePrefix;
	exports.FakeDraftMessagePrefix = FakeDraftMessagePrefix;
	exports.RecentCallStatus = RecentCallStatus;
	exports.NotificationTypesCodes = NotificationTypesCodes;
	exports.NotificationSettingsMode = NotificationSettingsMode;
	exports.Layout = Layout;
	exports.SearchEntityIdTypes = SearchEntityIdTypes;
	exports.UserStatus = UserStatus;
	exports.UserType = UserType;
	exports.UserRole = UserRole;
	exports.UserIdNetworkPrefix = UserIdNetworkPrefix;
	exports.SidebarDetailBlock = SidebarDetailBlock;
	exports.SidebarFileTabTypes = SidebarFileTabTypes;
	exports.SidebarFileTypes = SidebarFileTypes;
	exports.Color = Color;
	exports.ColorToken = ColorToken;
	exports.AttachType = AttachType;
	exports.AttachDescription = AttachDescription;
	exports.KeyboardButtonType = KeyboardButtonType;
	exports.KeyboardButtonAction = KeyboardButtonAction;
	exports.KeyboardButtonDisplay = KeyboardButtonDisplay;
	exports.KeyboardButtonContext = KeyboardButtonContext;
	exports.DesktopBxLink = DesktopBxLink;
	exports.LegacyDesktopBxLink = LegacyDesktopBxLink;
	exports.LocalStorageKey = LocalStorageKey;
	exports.PlacementType = PlacementType;
	exports.PopupType = PopupType;
	exports.Settings = Settings;
	exports.SettingsSection = SettingsSection;
	exports.NotificationSettingsType = NotificationSettingsType;
	exports.SoundType = SoundType;
	exports.PromoId = PromoId;
	exports.ActionByRole = ActionByRole;
	exports.ChatActionGroup = ChatActionGroup;
	exports.ActionByUserType = ActionByUserType;
	exports.BotType = BotType;
	exports.RawBotType = RawBotType;
	exports.BotCode = BotCode;
	exports.BotCommand = BotCommand;
	exports.PathPlaceholder = PathPlaceholder;
	exports.GetParameter = GetParameter;
	exports.TextareaPanelType = TextareaPanelType;
	exports.ChatEntityLinkType = ChatEntityLinkType;
	exports.MultidialogStatus = MultidialogStatus;
	exports.SliderCode = SliderCode;
	exports.CounterType = CounterType;
	exports.CollabEntityType = CollabEntityType;

}((this.BX.Messenger.v2.Const = this.BX.Messenger.v2.Const || {}),BX.OpenLines.v2.Const,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
