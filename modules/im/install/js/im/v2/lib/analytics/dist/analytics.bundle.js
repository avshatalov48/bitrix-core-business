/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_analytics,im_v2_lib_messageComponentManager,ui_analytics,im_v2_const,im_v2_application_core) {
	'use strict';

	const CopilotChatType = Object.freeze({
	  private: 'chatType_private',
	  multiuser: 'chatType_multiuser'
	});
	const AnalyticsEvent = Object.freeze({
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
	  clickAddUser: 'click_add_user',
	  openCalendar: 'open_calendar',
	  openTasks: 'open_tasks',
	  openFiles: 'open_files',
	  clickCreateTask: 'click_create_task',
	  clickCreateEvent: 'click_create_event',
	  clickAttach: 'click_attach',
	  downloadFile: 'download_file',
	  saveToDisk: 'save_to_disk'
	});
	const AnalyticsTool = Object.freeze({
	  ai: 'ai',
	  checkin: 'checkin',
	  im: 'im',
	  infoHelper: 'InfoHelper'
	});
	const AnalyticsCategory = Object.freeze({
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
	  collab: 'collab'
	});
	const AnalyticsType = Object.freeze({
	  ai: 'ai',
	  chat: 'chat',
	  channel: 'channel',
	  videoconf: 'videoconf',
	  copilot: 'copilot',
	  deletedMessage: 'deleted_message',
	  limitOfficeChatingHistory: 'limit_office_chating_history',
	  privateCall: 'private',
	  groupCall: 'group'
	});
	const AnalyticsSection = Object.freeze({
	  copilotTab: 'copilot_tab',
	  chat: 'chat',
	  chatStart: 'chat_start',
	  chatHistory: 'chat_history',
	  sidebar: 'sidebar',
	  popup: 'popup',
	  activeChat: 'active_chat',
	  comments: 'comments',
	  chatHeader: 'chat_header',
	  chatSidebar: 'chat_sidebar',
	  chatTextarea: 'chat_textarea',
	  editor: 'editor',
	  chatWindow: 'chat_window'
	});
	const AnalyticsSubSection = Object.freeze({
	  contextMenu: 'context_menu',
	  sidebar: 'sidebar',
	  chatWindow: 'chat_window',
	  messageLink: 'message_link',
	  chatSidebar: 'chat_sidebar',
	  chatList: 'chat_list',
	  window: 'window'
	});
	const AnalyticsElement = Object.freeze({
	  initialBanner: 'initial_banner',
	  videocall: 'videocall',
	  audiocall: 'audiocall',
	  startButton: 'start_button'
	});
	const AnalyticsStatus = Object.freeze({
	  success: 'success',
	  errorTurnedOff: 'error_turnedoff'
	});
	const CreateChatContext = Object.freeze({
	  collabEmptyState: 'collab_empty_state'
	});

	function getCollabId(chatId) {
	  const collabInfo = im_v2_application_core.Core.getStore().getters['chats/collabs/getByChatId'](chatId);
	  if (!collabInfo) {
	    return null;
	  }
	  return `collabId_${collabInfo.collabId}`;
	}

	const AnalyticUserType = Object.freeze({
	  userIntranet: 'user_intranet',
	  userExtranet: 'user_extranet',
	  userCollaber: 'user_collaber'
	});
	function getUserType() {
	  const user = im_v2_application_core.Core.getStore().getters['users/get'](im_v2_application_core.Core.getUserId(), true);
	  switch (user.type) {
	    case im_v2_const.UserType.user:
	      return AnalyticUserType.userIntranet;
	    case im_v2_const.UserType.extranet:
	      return AnalyticUserType.userExtranet;
	    case im_v2_const.UserType.collaber:
	      return AnalyticUserType.userCollaber;
	    default:
	      return AnalyticUserType.userIntranet;
	  }
	}

	function getCategoryByChatType(type) {
	  switch (type) {
	    case im_v2_const.ChatType.channel:
	    case im_v2_const.ChatType.openChannel:
	    case im_v2_const.ChatType.comment:
	    case im_v2_const.ChatType.generalChannel:
	      return AnalyticsCategory.channel;
	    case im_v2_const.ChatType.copilot:
	      return AnalyticsCategory.copilot;
	    case im_v2_const.ChatType.videoconf:
	      return AnalyticsCategory.videoconf;
	    case im_v2_const.ChatType.collab:
	      return AnalyticsCategory.collab;
	    default:
	      return AnalyticsCategory.chat;
	  }
	}

	const CUSTOM_CHAT_TYPE = 'custom';
	function getChatType(chat) {
	  var _ChatType$chat$type;
	  return (_ChatType$chat$type = im_v2_const.ChatType[chat.type]) != null ? _ChatType$chat$type : CUSTOM_CHAT_TYPE;
	}

	const AnalyticsAmountFilesType = {
	  single: 'files_single',
	  many: 'files_all'
	};
	const AnalyticsFileType = {
	  ...im_v2_const.FileType,
	  media: 'media',
	  any: 'any'
	};
	class MessageFiles {
	  onClickDownload({
	    messageId,
	    dialogId
	  }) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.downloadFile,
	      type: getAnalyticsFileType(messageId),
	      c_section: AnalyticsSection.chatWindow,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p2: getUserType(),
	      p3: getFilesAmountParam(messageId),
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = im_v2_lib_analytics.getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	  onClickSaveOnDisk({
	    messageId,
	    dialogId
	  }) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.saveToDisk,
	      type: getAnalyticsFileType(messageId),
	      c_section: AnalyticsSection.chatWindow,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p2: getUserType(),
	      p3: getFilesAmountParam(messageId),
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = im_v2_lib_analytics.getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	}
	function getFilesAmountParam(messageId) {
	  const message = im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	  if (message.files.length === 1) {
	    return AnalyticsAmountFilesType.single;
	  }
	  return AnalyticsAmountFilesType.many;
	}
	function getAnalyticsFileType(messageId) {
	  const message = im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	  const fileTypes = message.files.map(fileId => {
	    return im_v2_application_core.Core.getStore().getters['files/get'](fileId).type;
	  });
	  const uniqueTypes = [...new Set(fileTypes)];
	  if (uniqueTypes.length === 1) {
	    return uniqueTypes[0];
	  }
	  if (uniqueTypes.length === 2 && uniqueTypes.includes(im_v2_const.FileType.image) && uniqueTypes.includes(im_v2_const.FileType.video)) {
	    return AnalyticsFileType.media;
	  }
	  return AnalyticsFileType.any;
	}

	const EntityToEventMap = {
	  [im_v2_const.CollabEntityType.tasks]: AnalyticsEvent.openTasks,
	  [im_v2_const.CollabEntityType.calendar]: AnalyticsEvent.openCalendar,
	  [im_v2_const.CollabEntityType.files]: AnalyticsEvent.openFiles
	};
	class CollabEntities {
	  onClick(dialogId, type) {
	    const event = EntityToEventMap[type];
	    if (!event) {
	      return;
	    }
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.collab,
	      event,
	      c_section: AnalyticsSection.chatHeader,
	      p2: getUserType(),
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	}

	var _onClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClick");
	class ChatEntities {
	  constructor() {
	    Object.defineProperty(this, _onClick, {
	      value: _onClick2
	    });
	  }
	  onCreateTaskFromSidebarClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick]({
	      dialogId,
	      event: AnalyticsEvent.clickCreateTask,
	      section: AnalyticsSection.chatSidebar
	    });
	  }
	  onCreateTaskFromTextareaClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick]({
	      dialogId,
	      event: AnalyticsEvent.clickCreateTask,
	      section: AnalyticsSection.chatTextarea
	    });
	  }
	  onCreateEventFromSidebarClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick]({
	      dialogId,
	      event: AnalyticsEvent.clickCreateEvent,
	      section: AnalyticsSection.chatSidebar
	    });
	  }
	  onCreateEventFromTextareaClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick]({
	      dialogId,
	      event: AnalyticsEvent.clickCreateEvent,
	      section: AnalyticsSection.chatTextarea
	    });
	  }
	}
	function _onClick2({
	  dialogId,
	  event,
	  section
	}) {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  const params = {
	    tool: AnalyticsTool.im,
	    category: getCategoryByChatType(chat.type),
	    event,
	    c_section: section,
	    p1: `chatType_${getChatType(chat)}`,
	    p2: getUserType(),
	    p5: `chatId_${chat.chatId}`
	  };
	  if (chat.type === im_v2_const.ChatType.collab) {
	    params.p4 = getCollabId(chat.chatId);
	  }
	  ui_analytics.sendData(params);
	}

	class ChatDelete {
	  onClick(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.clickDelete,
	      type: getChatType(chat),
	      c_section: AnalyticsSection.sidebar,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onCancel(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.cancelDelete,
	      type: getChatType(chat),
	      c_section: AnalyticsSection.popup,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onConfirm(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.delete,
	      type: getChatType(chat),
	      c_section: AnalyticsSection.popup,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onChatDeletedNotification(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const category = getCategoryByChatType(chat);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.chatPopup,
	      event: AnalyticsEvent.view,
	      type: `deleted_${category}`,
	      c_section: AnalyticsSection.activeChat,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	}

	class MessageDelete {
	  onClickDelete({
	    messageId,
	    dialogId
	  }) {
	    const message = im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	    const type = new im_v2_lib_messageComponentManager.MessageComponentManager(message).getName();
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.message,
	      event: AnalyticsEvent.clickDelete,
	      type,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onCancel({
	    messageId,
	    dialogId
	  }) {
	    const message = im_v2_application_core.Core.getStore().getters['messages/getById'](messageId);
	    const type = new im_v2_lib_messageComponentManager.MessageComponentManager(message).getName();
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.message,
	      event: AnalyticsEvent.cancelDelete,
	      type,
	      c_section: AnalyticsSection.popup,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onNotFoundNotification({
	    dialogId
	  }) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.chatPopup,
	      event: AnalyticsEvent.view,
	      type: AnalyticsType.deletedMessage,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    });
	  }
	  onDeletedPostNotification({
	    messageId,
	    dialogId
	  }) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const commentInfo = im_v2_application_core.Core.getStore().getters['messages/comments/getByMessageId'](messageId);
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.chatPopup,
	      event: AnalyticsEvent.view,
	      type: AnalyticsType.deletedMessage,
	      c_section: AnalyticsSection.comments,
	      p1: `chatType_${chat.type}`,
	      p4: `parentChatId_${chat.chatId}`,
	      p5: `chatId_${commentInfo.chatId}`
	    });
	  }
	}

	var _onBannerClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onBannerClick");
	var _getSidebarPanelNameForAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSidebarPanelNameForAnalytics");
	class HistoryLimit {
	  constructor() {
	    Object.defineProperty(this, _getSidebarPanelNameForAnalytics, {
	      value: _getSidebarPanelNameForAnalytics2
	    });
	    Object.defineProperty(this, _onBannerClick, {
	      value: _onBannerClick2
	    });
	  }
	  onDialogLimitExceeded({
	    dialogId,
	    noMessages
	  }) {
	    const sectionValue = noMessages ? AnalyticsSection.chatStart : AnalyticsSection.chatHistory;
	    const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const chatType = getChatType(dialog);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.limitBanner,
	      event: AnalyticsEvent.view,
	      type: AnalyticsType.limitOfficeChatingHistory,
	      c_section: sectionValue,
	      p1: `chatType_${chatType}`
	    };
	    ui_analytics.sendData(params);
	  }
	  onSidebarLimitExceeded({
	    dialogId,
	    panel
	  }) {
	    const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const chatType = getChatType(dialog);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.limitBanner,
	      event: AnalyticsEvent.view,
	      type: AnalyticsType.limitOfficeChatingHistory,
	      c_section: AnalyticsSection.sidebar,
	      c_element: babelHelpers.classPrivateFieldLooseBase(this, _getSidebarPanelNameForAnalytics)[_getSidebarPanelNameForAnalytics](panel),
	      p1: `chatType_${chatType}`
	    };
	    ui_analytics.sendData(params);
	  }
	  onDialogBannerClick({
	    dialogId
	  }) {
	    const section = AnalyticsSection.chatWindow;
	    babelHelpers.classPrivateFieldLooseBase(this, _onBannerClick)[_onBannerClick]({
	      dialogId,
	      section
	    });
	  }
	  onSidebarBannerClick({
	    dialogId,
	    panel
	  }) {
	    const section = AnalyticsSection.sidebar;
	    const element = babelHelpers.classPrivateFieldLooseBase(this, _getSidebarPanelNameForAnalytics)[_getSidebarPanelNameForAnalytics](panel);
	    babelHelpers.classPrivateFieldLooseBase(this, _onBannerClick)[_onBannerClick]({
	      dialogId,
	      section,
	      element
	    });
	  }
	  onGoToContextLimitExceeded({
	    dialogId
	  }) {
	    const section = AnalyticsSection.messageLink;
	    babelHelpers.classPrivateFieldLooseBase(this, _onBannerClick)[_onBannerClick]({
	      dialogId,
	      section
	    });
	  }
	}
	function _onBannerClick2({
	  dialogId,
	  section,
	  element
	}) {
	  const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	  const chatType = getChatType(dialog);
	  const params = {
	    tool: AnalyticsTool.im,
	    category: AnalyticsCategory.limitBanner,
	    event: AnalyticsEvent.click,
	    type: AnalyticsType.limitOfficeChatingHistory,
	    c_section: section,
	    p1: `chatType_${chatType}`
	  };
	  if (element) {
	    params.c_element = element;
	  }
	  ui_analytics.sendData(params);
	}
	function _getSidebarPanelNameForAnalytics2(panel) {
	  switch (panel) {
	    case im_v2_const.SidebarDetailBlock.main:
	      return 'main';
	    case im_v2_const.SidebarDetailBlock.file:
	    case im_v2_const.SidebarDetailBlock.fileUnsorted:
	    case im_v2_const.SidebarDetailBlock.audio:
	    case im_v2_const.SidebarDetailBlock.brief:
	    case im_v2_const.SidebarDetailBlock.document:
	    case im_v2_const.SidebarDetailBlock.media:
	    case im_v2_const.SidebarDetailBlock.other:
	      return 'docs';
	    case im_v2_const.SidebarDetailBlock.messageSearch:
	      return 'message_search';
	    case im_v2_const.SidebarDetailBlock.favorite:
	      return 'favs';
	    case im_v2_const.SidebarDetailBlock.link:
	      return 'links';
	    case im_v2_const.SidebarDetailBlock.task:
	      return 'task';
	    case im_v2_const.SidebarDetailBlock.meeting:
	      return 'event';
	    default:
	      return 'unknown';
	  }
	}

	var _onAddUserClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAddUserClick");
	class UserAdd {
	  constructor() {
	    Object.defineProperty(this, _onAddUserClick, {
	      value: _onAddUserClick2
	    });
	  }
	  onChatSidebarClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onAddUserClick)[_onAddUserClick](dialogId, AnalyticsSection.chatSidebar);
	  }
	  onChatHeaderClick(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onAddUserClick)[_onAddUserClick](dialogId, AnalyticsSection.chatHeader);
	  }
	}
	function _onAddUserClick2(dialogId, element) {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  const params = {
	    tool: AnalyticsTool.im,
	    category: getCategoryByChatType(chat.type),
	    event: AnalyticsEvent.clickAddUser,
	    c_section: element,
	    p2: getUserType(),
	    p5: `chatId_${chat.chatId}`
	  };
	  if (chat.type === im_v2_const.ChatType.collab) {
	    params.p4 = getCollabId(chat.chatId);
	  }
	  ui_analytics.sendData(params);
	}

	class ChatEdit {
	  onOpenForm(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.clickEdit,
	      c_section: AnalyticsSection.sidebar,
	      c_sub_section: AnalyticsSubSection.contextMenu,
	      p1: `chatType_${chat.type}`,
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	  onSubmitForm(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      c_section: AnalyticsSection.editor,
	      event: AnalyticsEvent.submitEdit,
	      p1: `chatType_${chat.type}`,
	      p2: getUserType(),
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	}

	class ChatCreate {
	  onStartClick(type) {
	    const currentLayout = im_v2_application_core.Core.getStore().getters['application/getLayout'].name;
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(type),
	      event: AnalyticsEvent.clickCreateNew,
	      type,
	      c_section: `${currentLayout}_tab`,
	      p2: getUserType()
	    });
	  }
	  onCollabEmptyStateCreateClick() {
	    ui_analytics.sendData({
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(im_v2_const.ChatType.collab),
	      event: AnalyticsEvent.clickCreateNew,
	      type: im_v2_const.ChatType.collab,
	      c_section: CreateChatContext.collabEmptyState,
	      p2: getUserType()
	    });
	  }
	}

	class Supervisor {
	  onOpenPriceTable(featureId) {
	    ui_analytics.sendData({
	      tool: AnalyticsTool.infoHelper,
	      category: AnalyticsCategory.limit,
	      event: AnalyticsEvent.openPrices,
	      type: featureId,
	      c_section: AnalyticsSection.chat
	    });
	  }
	  onOpenToolsSettings(toolId) {
	    ui_analytics.sendData({
	      tool: AnalyticsTool.infoHelper,
	      category: AnalyticsCategory.toolOff,
	      event: AnalyticsEvent.openSettings,
	      type: toolId,
	      c_section: AnalyticsSection.chat
	    });
	  }
	}

	class CheckIn {
	  onOpenCheckInPopup() {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.popupOpen,
	      tool: AnalyticsTool.checkin,
	      category: AnalyticsCategory.shift,
	      c_section: AnalyticsSection.chat
	    });
	  }
	}

	class Copilot {
	  onCreateChat(chatId) {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.createNewChat,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab,
	      type: AnalyticsType.ai,
	      p3: CopilotChatType.private,
	      p5: `chatId_${chatId}`
	    });
	  }
	  onOpenChat(dialogId) {
	    const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const copilotChatType = dialog.userCounter <= 2 ? CopilotChatType.private : CopilotChatType.multiuser;
	    ui_analytics.sendData({
	      event: AnalyticsEvent.openChat,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab,
	      type: AnalyticsType.ai,
	      p3: copilotChatType,
	      p5: `chatId_${dialog.chatId}`
	    });
	  }
	  onOpenTab({
	    isAvailable = true
	  } = {}) {
	    const payload = {
	      event: AnalyticsEvent.openTab,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab,
	      status: isAvailable ? AnalyticsStatus.success : AnalyticsStatus.errorTurnedOff
	    };
	    ui_analytics.sendData(payload);
	  }
	  onUseAudioInput() {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.audioUse,
	      tool: AnalyticsTool.ai,
	      category: AnalyticsCategory.chatOperations,
	      c_section: AnalyticsSection.copilotTab
	    });
	  }
	}

	class AttachMenu {
	  onOpenUploadMenu(dialogId) {
	    const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    const chatType = getChatType(chat);
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chat.type),
	      event: AnalyticsEvent.clickAttach,
	      c_section: AnalyticsSection.chatTextarea,
	      p1: `chatType_${chatType}`,
	      p2: getUserType(),
	      p5: `chatId_${chat.chatId}`
	    };
	    if (chat.type === im_v2_const.ChatType.collab) {
	      params.p4 = getCollabId(chat.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	}

	var _excludedChats = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("excludedChats");
	var _currentTab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentTab");
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	class Analytics {
	  constructor() {
	    Object.defineProperty(this, _excludedChats, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _currentTab, {
	      writable: true,
	      value: im_v2_const.Layout.chat.name
	    });
	    this.chatCreate = new ChatCreate();
	    this.chatEdit = new ChatEdit();
	    this.chatDelete = new ChatDelete();
	    this.messageDelete = new MessageDelete();
	    this.historyLimit = new HistoryLimit();
	    this.userAdd = new UserAdd();
	    this.collabEntities = new CollabEntities();
	    this.chatEntities = new ChatEntities();
	    this.supervisor = new Supervisor();
	    this.checkIn = new CheckIn();
	    this.copilot = new Copilot();
	    this.attachMenu = new AttachMenu();
	    this.messageFiles = new MessageFiles();
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  ignoreNextChatOpen(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _excludedChats)[_excludedChats].add(dialogId);
	  }
	  onOpenMessenger() {
	    ui_analytics.sendData({
	      event: AnalyticsEvent.openMessenger,
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.messenger
	    });
	  }
	  onOpenTab(tabName) {
	    const existingTabs = [im_v2_const.Layout.chat.name, im_v2_const.Layout.copilot.name, im_v2_const.Layout.collab.name, im_v2_const.Layout.channel.name, im_v2_const.Layout.notification.name, im_v2_const.Layout.settings.name, im_v2_const.Layout.openlines.name];
	    if (!existingTabs.includes(tabName)) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentTab)[_currentTab] === tabName) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _currentTab)[_currentTab] = tabName;
	    ui_analytics.sendData({
	      event: AnalyticsEvent.openTab,
	      tool: AnalyticsTool.im,
	      category: AnalyticsCategory.messenger,
	      type: tabName,
	      p2: getUserType()
	    });
	  }
	  onOpenChat(dialog) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _excludedChats)[_excludedChats].has(dialog.dialogId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _excludedChats)[_excludedChats].delete(dialog.dialogId);
	      return;
	    }
	    const chatType = getChatType(dialog);
	    if (chatType === im_v2_const.ChatType.copilot) {
	      this.copilot.onOpenChat(dialog.dialogId);
	    }
	    const currentLayout = im_v2_application_core.Core.getStore().getters['application/getLayout'].name;
	    const isMember = dialog.role === im_v2_const.UserRole.guest ? 'N' : 'Y';
	    const params = {
	      tool: AnalyticsTool.im,
	      category: getCategoryByChatType(chatType),
	      event: AnalyticsEvent.openExisting,
	      type: chatType,
	      c_section: `${currentLayout}_tab`,
	      p2: getUserType(),
	      p3: `isMember_${isMember}`,
	      p5: `chatId_${dialog.chatId}`
	    };
	    if (chatType === im_v2_const.ChatType.comment) {
	      const parentChat = im_v2_application_core.Core.getStore().getters['chats/getByChatId'](dialog.parentChatId);
	      params.p1 = `chatType_${parentChat.type}`;
	      params.p4 = `parentChatId_${dialog.parentChatId}`;
	    }
	    if (chatType === im_v2_const.ChatType.collab) {
	      params.p4 = getCollabId(dialog.chatId);
	    }
	    ui_analytics.sendData(params);
	  }
	}
	Object.defineProperty(Analytics, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.Analytics = Analytics;
	exports.CreateChatContext = CreateChatContext;
	exports.getCollabId = getCollabId;
	exports.getUserType = getUserType;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI.Analytics,BX.Messenger.v2.Const,BX.Messenger.v2.Application));
//# sourceMappingURL=analytics.bundle.js.map
