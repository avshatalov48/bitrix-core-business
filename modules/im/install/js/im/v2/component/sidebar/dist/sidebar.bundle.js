/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_localStorage,im_v2_lib_channel,ui_vue3_directives_lazyload,ui_label,im_v2_lib_layout,main_date,ui_vue3_directives_hint,im_v2_lib_rest,ui_promoVideoPopup,ui_manual,im_v2_lib_promo,im_v2_lib_feature,ui_viewer,ui_icons,im_v2_model,ui_notification,rest_client,ui_vue3_vuex,im_v2_lib_market,im_v2_lib_entityCreator,im_v2_lib_analytics,im_v2_component_entitySelector,im_v2_lib_menu,im_v2_lib_call,im_v2_lib_permission,im_v2_lib_confirm,im_v2_provider_service,im_v2_lib_logger,im_v2_lib_parser,im_v2_lib_textHighlighter,main_core,im_v2_lib_utils,im_v2_lib_user,im_v2_application_core,im_public,im_v2_const,im_v2_component_elements,main_core_events,im_v2_lib_dateFormatter) {
	'use strict';

	function getChatId(dialogId) {
	  const dialog = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  return dialog.chatId;
	}

	function getLastElementId(collection, sort = 'ASC') {
	  if (collection.length === 0) {
	    return null;
	  }
	  collection.sort((a, b) => {
	    if (sort === 'ASC') {
	      return a.id - b.id;
	    }
	    return b.id - a.id;
	  });
	  const [lastCollectionItem] = collection;
	  if (main_core.Type.isNumber(lastCollectionItem.id)) {
	    return lastCollectionItem.id;
	  }
	  return null;
	}

	const REQUEST_ITEMS_LIMIT = 50;
	class Favorite {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imChatFavoriteCounterGet]: {
	        chat_id: this.chatId
	      },
	      [im_v2_const.RestMethod.imChatFavoriteGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imChatFavoriteCounterGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const favoriteCounterGetResponse = response[im_v2_const.RestMethod.imChatFavoriteCounterGet];
	      const setCounterResult = this.store.dispatch('sidebar/favorites/setCounter', {
	        chatId: this.chatId,
	        counter: favoriteCounterGetResponse.counter
	      });
	      const setFavoriteResult = this.handleResponse(response[im_v2_const.RestMethod.imChatFavoriteGet]);
	      return Promise.all([setCounterResult, setFavoriteResult]);
	    };
	  }
	  loadNextPage() {
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT
	    };
	    const lastId = this.store.getters['sidebar/favorites/getLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatFavoriteGet, queryParams).then(response => {
	      return this.handleResponse(response.data());
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    return this.updateModels(response);
	  }
	  updateModels(resultData) {
	    const {
	      list = [],
	      users = [],
	      files = [],
	      tariffRestrictions = {}
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const rawMessages = list.map(favorite => favorite.message);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
	    const lastId = getLastElementId(list);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
	    const setFavoritesPromise = this.store.dispatch('sidebar/favorites/set', {
	      chatId: this.chatId,
	      favorites: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise]);
	  }
	}

	const MainPanelType = {
	  user: [im_v2_const.ChatType.user],
	  chat: [im_v2_const.ChatType.chat],
	  copilot: [im_v2_const.ChatType.copilot],
	  support24Question: [im_v2_const.ChatType.support24Question],
	  channel: [im_v2_const.ChatType.channel],
	  openChannel: [im_v2_const.ChatType.openChannel],
	  comment: [im_v2_const.ChatType.comment],
	  generalChannel: [im_v2_const.ChatType.generalChannel],
	  collab: [im_v2_const.ChatType.collab],
	  lines: [im_v2_const.ChatType.lines]
	};
	const MainPanelBlock = Object.freeze({
	  support: 'support',
	  chat: 'chat',
	  user: 'user',
	  copilot: 'copilot',
	  copilotInfo: 'copilotInfo',
	  info: 'info',
	  post: 'post',
	  file: 'file',
	  fileUnsorted: 'fileUnsorted',
	  task: 'task',
	  meeting: 'meeting',
	  market: 'market',
	  multidialog: 'multidialog',
	  tariffLimit: 'tariffLimit',
	  collabHelpdesk: 'collabHelpdesk'
	});
	const MainPanels = {
	  [MainPanelType.user]: {
	    [MainPanelBlock.user]: 10,
	    [MainPanelBlock.tariffLimit]: 15,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.fileUnsorted]: 30,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50,
	    [MainPanelBlock.market]: 60
	  },
	  [MainPanelType.chat]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.tariffLimit]: 15,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.fileUnsorted]: 30,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50,
	    [MainPanelBlock.market]: 60
	  },
	  [MainPanelType.copilot]: {
	    [MainPanelBlock.copilot]: 10,
	    [MainPanelBlock.tariffLimit]: 15,
	    [MainPanelBlock.copilotInfo]: 20,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50
	  },
	  [MainPanelType.channel]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30
	  },
	  [MainPanelType.openChannel]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30
	  },
	  [MainPanelType.generalChannel]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30
	  },
	  [MainPanelType.comment]: {
	    [MainPanelBlock.post]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50
	  },
	  [MainPanelType.support24Question]: {
	    [MainPanelBlock.support]: 10,
	    [MainPanelBlock.tariffLimit]: 15,
	    [MainPanelBlock.multidialog]: 20,
	    [MainPanelBlock.info]: 30,
	    [MainPanelBlock.file]: 40
	  },
	  [MainPanelType.collab]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.fileUnsorted]: 30,
	    [MainPanelBlock.collabHelpdesk]: 40
	  },
	  [MainPanelType.lines]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30
	  }
	};

	class SettingsManager {
	  constructor() {
	    this.saveSettings();
	  }
	  async saveSettings() {
	    await im_v2_application_core.Core.ready();
	    const filesMigrated = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.sidebarFiles);
	    const linksAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.sidebarLinks);
	    void im_v2_application_core.Core.getStore().dispatch('sidebar/setFilesMigrated', filesMigrated);
	    void im_v2_application_core.Core.getStore().dispatch('sidebar/setLinksMigrated', linksAvailable);
	  }
	}

	function getMainBlocksForChat(dialogId) {
	  const panelType = getMainPanelType(dialogId);
	  return Object.entries(MainPanels[panelType]).sort(([, order1], [, order2]) => order1 - order2).map(([block]) => block);
	}
	function getMainPanelType(dialogId) {
	  var _MainPanelType$chatTy;
	  const chatType = getChatType(dialogId);
	  if (isSupportChat(dialogId)) {
	    return MainPanelType.support24Question;
	  }
	  return (_MainPanelType$chatTy = MainPanelType[chatType]) != null ? _MainPanelType$chatTy : MainPanelType.chat;
	}
	const isSupportChat = dialogId => {
	  return im_v2_application_core.Core.getStore().getters['sidebar/multidialog/isSupport'](dialogId);
	};
	const getChatType = dialogId => {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true).type;
	};

	function getAvailableBlocks(dialogId) {
	  const blocks = getMainBlocksForChat(dialogId);
	  return filterUnavailableBlocks(dialogId, blocks);
	}
	function filterUnavailableBlocks(dialogId, blocks) {
	  new SettingsManager().saveSettings();
	  const blocksSet = new Set(blocks);
	  if (isFileMigrationFinished()) {
	    blocksSet.delete(MainPanelBlock.fileUnsorted);
	  } else {
	    blocksSet.delete(MainPanelBlock.file);
	  }
	  if (!hasMarketApps(dialogId)) {
	    blocksSet.delete(MainPanelBlock.market);
	  }
	  if (!hasHistoryLimit(dialogId)) {
	    blocksSet.delete(MainPanelBlock.tariffLimit);
	  }
	  if (isBot(dialogId)) {
	    blocksSet.delete(MainPanelBlock.task);
	    blocksSet.delete(MainPanelBlock.meeting);
	  }
	  return [...blocksSet];
	}
	function isBot(dialogId) {
	  const user = im_v2_application_core.Core.getStore().getters['users/get'](dialogId);
	  return (user == null ? void 0 : user.type) === im_v2_const.UserType.bot;
	}
	function isFileMigrationFinished() {
	  return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.sidebarFiles);
	}
	function hasMarketApps(dialogId) {
	  return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.sidebar, dialogId).length > 0;
	}
	function hasHistoryLimit(dialogId) {
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	  const isChannelCommentsChat = im_v2_const.ChatType.comment === chat.type;
	  const isChannelChat = im_v2_lib_channel.ChannelManager.isChannel(dialogId);
	  if (isChannelChat || isChannelCommentsChat || im_v2_lib_feature.FeatureManager.chatHistory.isAvailable()) {
	    return false;
	  }
	  return im_v2_application_core.Core.getStore().getters['sidebar/hasHistoryLimit'](chat.chatId);
	}

	const REQUEST_ITEMS_LIMIT$1 = 50;
	class Link {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imChatUrlCounterGet]: {
	        chat_id: this.chatId
	      },
	      [im_v2_const.RestMethod.imChatUrlGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$1
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imChatUrlCounterGet] || !response[im_v2_const.RestMethod.imChatUrlGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const urlGetResult = this.handleUrlGetResponse(response[im_v2_const.RestMethod.imChatUrlGet]);
	      const counterGetResult = this.handleCounterGetResponse(response[im_v2_const.RestMethod.imChatUrlCounterGet]);
	      return Promise.all([urlGetResult, counterGetResult]);
	    };
	  }
	  loadNextPage() {
	    const linksCount = this.getLinksCountFromModel();
	    if (linksCount === 0) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams(linksCount);
	    return this.requestPage(queryParams);
	  }
	  getQueryParams(offset = 0) {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$1
	    };
	    if (main_core.Type.isNumber(offset) && offset > 0) {
	      queryParams.OFFSET = offset;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatUrlGet, queryParams).then(response => {
	      return this.handleUrlGetResponse(response.data());
	    }).catch(error => {
	      console.error('SidebarInfo: Im.chatUrlList: page request error', error);
	    });
	  }
	  handleUrlGetResponse(response) {
	    const {
	      list,
	      users,
	      tariffRestrictions = {}
	    } = response;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setLinksPromise = this.store.dispatch('sidebar/links/set', {
	      chatId: this.chatId,
	      links: list,
	      hasNextPage: list.length === REQUEST_ITEMS_LIMIT$1,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setLinksPromise, addUsersPromise]);
	  }
	  handleCounterGetResponse(response) {
	    const counter = response.counter;
	    return this.store.dispatch('sidebar/links/setCounter', {
	      chatId: this.chatId,
	      counter
	    });
	  }
	  getLinksCountFromModel() {
	    return this.store.getters['sidebar/links/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$2 = 50;
	class File {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imChatFileCollectionGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$2
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imChatFileCollectionGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      return this.updateModels(response[im_v2_const.RestMethod.imChatFileCollectionGet]);
	    };
	  }
	  updateModels(resultData, subType = '') {
	    const {
	      list,
	      users,
	      files,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const historyLimitPromise = this.store.dispatch('sidebar/files/setHistoryLimitExceeded', {
	      chatId: this.chatId,
	      isHistoryLimitExceeded
	    });
	    if (subType && !main_core.Type.isArrayFilled(list)) {
	      return this.store.dispatch('sidebar/files/setHasNextPage', {
	        chatId: this.chatId,
	        subType,
	        hasNextPage: false
	      });
	    }
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const sortedList = {};
	    list.forEach(file => {
	      if (!sortedList[file.subType]) {
	        sortedList[file.subType] = [];
	      }
	      sortedList[file.subType].push(file);
	    });
	    const setSidebarFilesPromises = [];
	    Object.keys(sortedList).forEach(subType => {
	      const listByType = sortedList[subType];
	      setSidebarFilesPromises.push(this.store.dispatch('sidebar/files/set', {
	        chatId: this.chatId,
	        files: listByType,
	        subType
	      }), this.store.dispatch('sidebar/files/setHasNextPage', {
	        chatId: this.chatId,
	        subType,
	        hasNextPage: listByType.length === REQUEST_ITEMS_LIMIT$2
	      }), this.store.dispatch('sidebar/files/setLastId', {
	        chatId: this.chatId,
	        subType,
	        lastId: getLastElementId(listByType)
	      }));
	    });
	    return Promise.all([setFilesPromise, addUsersPromise, historyLimitPromise, ...setSidebarFilesPromises]);
	  }
	  loadFirstPage(subType) {
	    return this.loadFirstPageBySubType(subType);
	  }
	  loadNextPage(subType) {
	    return this.loadNextPageBySubType(subType);
	  }
	  loadFirstPageBySubType(subType) {
	    const filesCount = this.getFilesCountFromModel(subType);
	    if (filesCount > REQUEST_ITEMS_LIMIT$2) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams(subType);
	    return this.requestPage(queryParams);
	  }
	  loadNextPageBySubType(subType) {
	    const queryParams = this.getQueryParams(subType);
	    return this.requestPage(queryParams);
	  }
	  getQueryParams(subType) {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      SUBTYPE: subType,
	      LIMIT: REQUEST_ITEMS_LIMIT$2
	    };
	    const lastId = this.store.getters['sidebar/files/getLastId'](this.chatId, subType);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatFileGet, queryParams).then(response => {
	      return this.updateModels(response.data(), queryParams.SUBTYPE);
	    }).catch(error => {
	      console.error('SidebarInfo: imChatFileGet: page request error', error);
	    });
	  }
	  getFilesCountFromModel(subType) {
	    return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	  }
	}

	const REQUEST_ITEMS_LIMIT$3 = 50;
	class Task {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imChatTaskGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$3
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imChatTaskGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      return this.updateModels(response[im_v2_const.RestMethod.imChatTaskGet]);
	    };
	  }
	  loadFirstPage() {
	    const tasksCount = this.getTasksCountFromModel();
	    if (tasksCount > REQUEST_ITEMS_LIMIT$3) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  loadNextPage() {
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$3
	    };
	    const lastId = this.store.getters['sidebar/tasks/getLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatTaskGet, queryParams).then(response => {
	      return this.updateModels(response.data());
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
	    });
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$3;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setTasksPromise = this.store.dispatch('sidebar/tasks/set', {
	      chatId: this.chatId,
	      tasks: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setTasksPromise, addUsersPromise]);
	  }
	  getTasksCountFromModel() {
	    return this.store.getters['sidebar/tasks/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$4 = 50;
	class Meeting {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imChatCalendarGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$4
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imChatCalendarGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      return this.updateModels(response[im_v2_const.RestMethod.imChatCalendarGet]);
	    };
	  }
	  loadFirstPage() {
	    const meetingsCount = this.getMeetingsCountFromState();
	    if (meetingsCount > REQUEST_ITEMS_LIMIT$4) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  loadNextPage() {
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$4
	    };
	    const lastId = this.store.getters['sidebar/meetings/getLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatCalendarGet, queryParams).then(response => {
	      return this.updateModels(response.data());
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatCalendarGet: page request error', error);
	    });
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$4;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setMeetingsPromise = this.store.dispatch('sidebar/meetings/set', {
	      chatId: this.chatId,
	      meetings: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setMeetingsPromise, addUsersPromise]);
	  }
	  getMeetingsCountFromState() {
	    return this.store.getters['sidebar/meetings/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$5 = 50;
	class MembersService {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imDialogUsersList]: {
	        dialog_id: this.dialogId,
	        limit: REQUEST_ITEMS_LIMIT$5,
	        LAST_ID: 0
	      }
	    };
	  }
	  loadFirstPage() {
	    const membersCount = this.getMembersCountFromModel();
	    if (membersCount > REQUEST_ITEMS_LIMIT$5) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  loadNextPage() {
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  getQueryParams() {
	    return {
	      DIALOG_ID: this.dialogId,
	      LIMIT: REQUEST_ITEMS_LIMIT$5,
	      LAST_ID: this.store.getters['sidebar/members/getLastId'](this.chatId)
	    };
	  }
	  async requestPage(queryParams) {
	    let users = [];
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imDialogUsersList, queryParams);
	      users = response.data();
	    } catch (error) {
	      console.error('SidebarMain: Im.DialogUsersList: page request error', error);
	    }
	    return this.updateModels(users);
	  }
	  getResponseHandler() {
	    return response => {
	      return this.updateModels(response[im_v2_const.RestMethod.imDialogUsersList]);
	    };
	  }
	  updateModels(users) {
	    const userIds = [];
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    users.forEach(user => {
	      userIds.push(user.id);
	    });
	    const setMembersPromise = this.store.dispatch('sidebar/members/set', {
	      chatId: this.chatId,
	      users: userIds,
	      lastId: getLastElementId(users, 'DESC'),
	      hasNextPage: users.length === REQUEST_ITEMS_LIMIT$5
	    });
	    return Promise.all([addUsersPromise, setMembersPromise]);
	  }
	  getMembersCountFromModel() {
	    return this.store.getters['sidebar/members/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$6 = 25;
	class Multidialog {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    if (this.isInitedMultidialogBlock()) {
	      return {};
	    }
	    return {
	      [im_v2_const.RestMethod.imBotNetworkChatCount]: {}
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (this.isInitedMultidialogBlock()) {
	        return Promise.resolve();
	      }
	      if (!response[im_v2_const.RestMethod.imBotNetworkChatCount]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const setInitedPromise = this.store.dispatch('sidebar/multidialog/setInited', true);
	      const updateModelsPromise = this.updateModels(response[im_v2_const.RestMethod.imBotNetworkChatCount]);
	      return Promise.all([setInitedPromise, updateModelsPromise]);
	    };
	  }
	  loadNextPage() {
	    const hasNextPage = this.store.getters['sidebar/multidialog/hasNextPage'];
	    if (!hasNextPage) {
	      return Promise.resolve();
	    }
	    const offset = this.store.getters['sidebar/multidialog/getNumberMultidialogs'];
	    const config = {
	      data: this.getQueryParams({
	        offset
	      })
	    };
	    return this.requestPage(config);
	  }
	  getQueryParams(params) {
	    const queryParams = {
	      offset: 0,
	      limit: REQUEST_ITEMS_LIMIT$6,
	      ...params
	    };
	    Object.keys(queryParams).forEach(key => {
	      const value = queryParams[key];
	      if (main_core.Type.isNumber(value) && value > 0) {
	        queryParams[key] = value;
	      }
	    });
	    return queryParams;
	  }
	  requestPage(config) {
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imBotNetworkChatList, config).then(response => {
	      return this.updateModels(response);
	    }).catch(error => {
	      console.error('SidebarInfo: imBotNetworkChatList: page request error', error);
	    });
	  }
	  createSupportChat() {
	    im_v2_lib_logger.Logger.warn('SidebarInfo: imBotNetworkChatAdd');
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imBotNetworkChatAdd).then(response => {
	      void this.updateModels({
	        chats: response
	      });
	      const {
	        dialogId
	      } = response;
	      im_v2_lib_logger.Logger.warn('SidebarInfo: createSupportChat result', response);
	      return dialogId;
	    }).catch(error => {
	      console.error('SidebarInfo: createSupportChat error:', error);
	    });
	  }
	  loadFirstPage() {
	    const isInitedDetail = this.store.getters['sidebar/multidialog/isInitedDetail'];
	    if (isInitedDetail) {
	      return Promise.resolve();
	    }
	    const numberMultidialogs = this.store.getters['sidebar/multidialog/getNumberMultidialogs'];
	    const limit = REQUEST_ITEMS_LIMIT$6 < numberMultidialogs ? numberMultidialogs : REQUEST_ITEMS_LIMIT$6;
	    const config = {
	      data: this.getQueryParams({
	        limit
	      })
	    };
	    return this.requestPage(config).then(() => {
	      return this.store.dispatch('sidebar/multidialog/setInitedDetail', true);
	    });
	  }
	  updateModels(resultData) {
	    const {
	      count,
	      chatIdsWithCounters,
	      multidialogs,
	      chats,
	      users,
	      openSessionsLimit
	    } = resultData;
	    const promises = [];
	    if (chats) {
	      const setChatsPromise = this.store.dispatch('chats/set', chats);
	      promises.push(setChatsPromise);
	    }
	    if (users) {
	      const setUsersPromise = this.userManager.setUsersToModel(users);
	      promises.push(setUsersPromise);
	    }
	    const setSupportTicketPromise = this.store.dispatch('sidebar/multidialog/set', {
	      chatsCount: count,
	      unreadChats: chatIdsWithCounters,
	      multidialogs,
	      openSessionsLimit
	    });
	    promises.push(setSupportTicketPromise);
	    return Promise.all(promises);
	  }
	  isInitedMultidialogBlock() {
	    return this.store.getters['sidebar/multidialog/isInited'];
	  }
	}

	const REQUEST_ITEMS_LIMIT$7 = 50;
	class FileUnsorted {
	  constructor({
	    dialogId
	  }) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = this.getChatId();
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  getInitialQuery() {
	    return {
	      [im_v2_const.RestMethod.imDiskFolderListGet]: {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$7
	      }
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response[im_v2_const.RestMethod.imDiskFolderListGet]) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      return this.updateModels(response[im_v2_const.RestMethod.imDiskFolderListGet]);
	    };
	  }
	  loadFirstPage() {
	    const filesCount = this.getFilesCountFromModel(im_v2_const.SidebarDetailBlock.fileUnsorted);
	    if (filesCount > REQUEST_ITEMS_LIMIT$7) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  loadNextPage() {
	    const queryParams = this.getQueryParams();
	    return this.requestPage(queryParams);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$7
	    };
	    const lastId = this.store.getters['sidebar/files/getLastId'](this.chatId, im_v2_const.SidebarDetailBlock.fileUnsorted);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imDiskFolderListGet, queryParams).then(response => {
	      return this.handleResponse(response.data());
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imDiskFolderListGet: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    const diskFolderListGetResult = response;
	    if (diskFolderListGetResult.files.length < REQUEST_ITEMS_LIMIT$7) {
	      this.hasMoreItemsToLoad = false;
	    }
	    const lastId = getLastElementId(diskFolderListGetResult.files);
	    if (lastId) {
	      this.lastId = lastId;
	    }
	    return this.updateModels(diskFolderListGetResult);
	  }
	  updateModels(resultData) {
	    const {
	      users,
	      files,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const historyLimitPromise = this.store.dispatch('sidebar/files/setHistoryLimitExceeded', {
	      chatId: this.chatId,
	      isHistoryLimitExceeded
	    });
	    const preparedFiles = files.map(file => {
	      return {
	        ...file,
	        subType: im_v2_const.SidebarDetailBlock.fileUnsorted
	      };
	    });
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setFilesPromise = this.store.dispatch('files/set', preparedFiles);
	    const setSidebarFilesPromise = this.store.dispatch('sidebar/files/set', {
	      chatId: this.chatId,
	      files: preparedFiles,
	      subType: im_v2_const.SidebarDetailBlock.fileUnsorted
	    });
	    const hasNextPagePromise = this.store.dispatch('sidebar/files/setHasNextPage', {
	      chatId: this.chatId,
	      subType: im_v2_const.SidebarDetailBlock.fileUnsorted,
	      hasNextPage: preparedFiles.length === REQUEST_ITEMS_LIMIT$7
	    });
	    const setLastIdPromise = this.store.dispatch('sidebar/files/setLastId', {
	      chatId: this.chatId,
	      subType: im_v2_const.SidebarDetailBlock.fileUnsorted,
	      lastId: getLastElementId(preparedFiles)
	    });
	    return Promise.all([setFilesPromise, setSidebarFilesPromise, addUsersPromise, hasNextPagePromise, setLastIdPromise, historyLimitPromise]);
	  }
	  getFilesCountFromModel(subType) {
	    return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	  }
	  getChatId() {
	    const dialog = this.store.getters['chats/get'](this.dialogId, true);
	    return dialog.chatId;
	  }
	}

	const MainPanelServiceClasses = {
	  Members: MembersService,
	  Favorite,
	  Link,
	  Task,
	  File,
	  Meeting,
	  FileUnsorted,
	  Multidialog
	};
	const BlockToServices = Object.freeze({
	  [MainPanelBlock.chat]: [im_v2_const.SidebarDetailBlock.members],
	  [MainPanelBlock.copilot]: [im_v2_const.SidebarDetailBlock.members],
	  [MainPanelBlock.copilotInfo]: [im_v2_const.SidebarDetailBlock.favorite],
	  [MainPanelBlock.info]: [im_v2_const.SidebarDetailBlock.favorite, im_v2_const.SidebarDetailBlock.link],
	  [MainPanelBlock.file]: [im_v2_const.SidebarDetailBlock.file],
	  [MainPanelBlock.fileUnsorted]: [im_v2_const.SidebarDetailBlock.fileUnsorted],
	  [MainPanelBlock.task]: [im_v2_const.SidebarDetailBlock.task],
	  [MainPanelBlock.meeting]: [im_v2_const.SidebarDetailBlock.meeting],
	  [MainPanelBlock.multidialog]: [im_v2_const.SidebarDetailBlock.multidialog]
	});
	class Main {
	  constructor({
	    dialogId
	  }) {
	    this.blockServices = [];
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.buildBlocks();
	  }

	  // region public methods
	  async requestInitialData() {
	    const query = this.getInitialQuery();
	    const response = await im_v2_lib_rest.callBatch(query);
	    return this.handleBatchRequestResult(response);
	  }
	  // endregion

	  buildBlocks() {
	    const classNames = this.getServiceClassesForBlocks();
	    this.blockServices = classNames.map(ClassName => {
	      const blockService = new MainPanelServiceClasses[ClassName]({
	        dialogId: this.dialogId
	      });
	      return {
	        initialQuery: blockService.getInitialQuery(),
	        responseHandler: blockService.getResponseHandler()
	      };
	    });
	  }
	  getServiceClassesForBlocks() {
	    const services = [];
	    const blockList = getAvailableBlocks(this.dialogId);
	    blockList.forEach(block => {
	      const blockServices = BlockToServices[block];
	      if (blockServices) {
	        services.push(...blockServices);
	      }
	    });
	    return services.map(service => main_core.Text.capitalize(service));
	  }
	  getInitialQuery() {
	    let query = {};
	    this.blockServices.forEach(block => {
	      query = Object.assign(query, block.initialQuery);
	    });
	    return query;
	  }
	  handleBatchRequestResult(response) {
	    const responseHandlersResult = [];
	    this.blockServices.forEach(block => {
	      responseHandlersResult.push(block.responseHandler(response));
	    });
	    return Promise.all(responseHandlersResult).then(() => {
	      return this.setInited();
	    }).catch(error => console.error(error));
	  }
	  setInited() {
	    return this.store.dispatch('sidebar/setInited', getChatId(this.dialogId));
	  }
	}

	// @vue/component
	const ChatLinks = {
	  name: 'ChatLinks',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      expanded: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    urlCounter() {
	      const counter = this.$store.getters['sidebar/links/getCounter'](this.chatId);
	      return this.getCounterString(counter);
	    },
	    isLinksAvailable() {
	      return this.$store.state.sidebar.isLinksMigrated;
	    },
	    hintDirectiveContent() {
	      return {
	        text: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_NOT_AVAILABLE'),
	        popupOptions: {
	          angle: true,
	          targetContainer: document.body,
	          offsetLeft: 141,
	          offsetTop: -10,
	          bindOptions: {
	            position: 'top'
	          }
	        }
	      };
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  methods: {
	    getCounterString(counter) {
	      const MAX_COUNTER = 100;
	      if (counter >= MAX_COUNTER) {
	        return '99+';
	      }
	      return counter.toString();
	    },
	    onLinkClick() {
	      if (!this.isLinksAvailable) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.link,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-chat-links__container" 
			:class="[isLinksAvailable ? '' : '--links-not-active']"
			@click="onLinkClick"
		>
			<div 
				v-if="!isLinksAvailable" 
				class="bx-im-sidebar-chat-links__hint-not-active" 
				v-hint="hintDirectiveContent"
			></div>
			<div class="bx-im-sidebar-chat-links__title-container">
				<div class="bx-im-sidebar-chat-links__icon"></div>
				<div class="bx-im-sidebar-chat-links__title-text">
					{{ loc('IM_SIDEBAR_LINK_DETAIL_TITLE') }}
				</div>
			</div>
			<div class="bx-im-sidebar-chat-links__counter-container">
				<span class="bx-im-sidebar-chat-links__counter">{{urlCounter}}</span>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatFavourites = {
	  name: 'ChatFavourites',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    favoriteCounter() {
	      const counter = this.$store.getters['sidebar/favorites/getCounter'](this.chatId);
	      return this.getCounterString(counter);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    }
	  },
	  methods: {
	    getCounterString(counter) {
	      const MAX_COUNTER = 100;
	      if (counter >= MAX_COUNTER) {
	        return '99+';
	      }
	      return counter.toString();
	    },
	    onFavouriteClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.favorite,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-chat-favourites__container" 
			:class="{'--copilot': isCopilotLayout}"
			@click="onFavouriteClick"
		>
			<div class="bx-im-sidebar-chat-favourites__title">
				<div class="bx-im-sidebar-chat-favourites__icon"></div>
				<div class="bx-im-sidebar-chat-favourites__title-text">
					{{ loc('IM_SIDEBAR_FAVORITE_DETAIL_TITLE') }}
				</div>
			</div>
			<div class="bx-im-sidebar-chat-favourites__counter-container">
				<span class="bx-im-sidebar-chat-favourites__counter">{{favoriteCounter}}</span>
			</div>
		</div>
	`
	};

	const MAX_DESCRIPTION_SYMBOLS = 25;
	const NEW_LINE_SYMBOL = '\n';
	const DescriptionByChatType = {
	  [im_v2_const.ChatType.user]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_USER'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.comment]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_COMMENTS'),
	  default: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2')
	};

	// @vue/component
	const ChatDescription = {
	  name: 'ChatDescription',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      expanded: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      const user = this.$store.getters['users/get'](this.dialogId, true);
	      return user.type === im_v2_const.UserType.bot;
	    },
	    isCollabChat() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    isLongDescription() {
	      const hasNewLine = this.dialog.description.includes(NEW_LINE_SYMBOL);
	      return this.dialog.description.length > MAX_DESCRIPTION_SYMBOLS || hasNewLine;
	    },
	    previewDescription() {
	      if (this.dialog.description.length === 0) {
	        return this.chatTypeText;
	      }
	      if (this.isLongDescription) {
	        return `${this.dialog.description.slice(0, MAX_DESCRIPTION_SYMBOLS)}...`;
	      }
	      return this.dialog.description;
	    },
	    descriptionToShow() {
	      return this.expanded ? this.dialog.description : this.previewDescription;
	    },
	    chatTypeText() {
	      var _DescriptionByChatTyp;
	      if (this.isCopilotLayout) {
	        return this.$store.getters['copilot/getProvider'];
	      }
	      if (this.isBot) {
	        return this.loc('IM_SIDEBAR_CHAT_TYPE_BOT');
	      }
	      if (this.isCollabChat) {
	        return this.loc('IM_SIDEBAR_CHAT_TYPE_COLLAB');
	      }
	      return (_DescriptionByChatTyp = DescriptionByChatType[this.dialog.type]) != null ? _DescriptionByChatTyp : DescriptionByChatType.default;
	    },
	    showExpandButton() {
	      if (this.expanded) {
	        return false;
	      }
	      return this.isLongDescription;
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-chat-description__container">
			<div class="bx-im-sidebar-chat-description__text-container" :class="[expanded ? '--expanded' : '']">
				<div class="bx-im-sidebar-chat-description__icon"></div>
				<div class="bx-im-sidebar-chat-description__text"> {{ descriptionToShow }}</div>
			</div>
			<button
				v-if="showExpandButton"
				class="bx-im-sidebar-chat-description__show-more-button"
				@click="expanded = !expanded"
			>
				{{ loc('IM_SIDEBAR_CHAT_DESCRIPTION_SHOW') }}
			</button>
		</div>
	`
	};

	// @vue/component
	const InfoPreview = {
	  name: 'InfoPreview',
	  components: {
	    ChatDescription,
	    ChatLinks,
	    ChatFavourites
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-info-preview__container">
			<ChatDescription :dialogId="dialogId" />
			<ChatFavourites :dialogId="dialogId" />
			<ChatLinks :dialogId="dialogId" />
		</div>
	`
	};

	// @vue/component
	const FilePreviewItem = {
	  name: 'FilePreviewItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  props: {
	    fileItem: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    sidebarFileItem() {
	      return this.fileItem;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
	    },
	    previewImageStyles() {
	      if (!this.hasPreview) {
	        return {};
	      }
	      return {
	        backgroundImage: `url('${this.file.urlPreview}')`
	      };
	    },
	    hasPreview() {
	      return this.file.urlPreview !== '';
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 22;
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    isImage() {
	      return this.file.type === 'image';
	    },
	    isVideo() {
	      return this.file.type === 'video';
	    },
	    isAudio() {
	      return this.file.type === 'audio';
	    },
	    fileIconClass() {
	      return `ui-icon ui-icon-file-${this.file.icon}`;
	    },
	    isViewerAvailable() {
	      return Object.keys(this.viewerAttributes).length > 0;
	    }
	  },
	  methods: {
	    download() {
	      if (this.isViewerAvailable) {
	        return;
	      }
	      const urlToOpen = this.file.urlShow ? this.file.urlShow : this.file.urlDownload;
	      window.open(urlToOpen, '_blank');
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-file-preview-item__container bx-im-sidebar-file-preview-item__scope" 
			v-bind="viewerAttributes" 
			@click="download" 
			:title="file.name"
		>
			<img
				v-if="isImage"
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="file.urlShow"
				:title="file.name"
				:alt="file.name"
				class="bx-im-sidebar-file-preview-item__preview-box"
			/>
			<div 
				v-else-if="isVideo" 
				class="bx-im-sidebar-file-preview-item__preview-box bx-im-sidebar-file-preview-item__preview-video-box"
				:style="previewImageStyles"
			>
				<video v-if="!hasPreview" class="bx-im-sidebar-file-preview-item__preview-video" preload="metadata" :src="file.urlDownload"></video>
				<div class="bx-im-sidebar-file-preview-item__preview-video-play-button"></div>
				<div class="bx-im-sidebar-file-preview-item__preview-video-play-icon"></div>
			</div>
			<div v-else-if="isAudio" class="bx-im-sidebar-file-preview-item__preview-box">
				<div class="bx-im-sidebar-file-preview-item__preview-audio-play-button"></div>
			</div>
			<div v-else class="bx-im-sidebar-file-preview-item__preview-box">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-sidebar-file-preview-item__text">{{ fileShortName }}</div>
		</div>
	`
	};

	// @vue/component
	const DetailEmptyState = {
	  name: 'DetailEmptyState',
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    iconType: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    iconClass() {
	      return `--${main_core.Text.toKebabCase(this.iconType)}`;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-detail-empty-state__container bx-im-sidebar-detail-empty-state__scope">
			<span class="bx-im-sidebar-detail-empty-state__icon" :class="[iconClass]"></span>
			<span class="bx-im-sidebar-detail-empty-state__text">{{ title }}</span>
		</div>
	`
	};

	// @vue/component
	const FilePreview = {
	  name: 'FilePreview',
	  components: {
	    DetailEmptyState,
	    FilePreviewItem
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      if (this.isMigrationFinished) {
	        return this.$store.getters['sidebar/files/getLatest'](this.chatId);
	      }
	      return this.$store.getters['sidebar/files/getLatestUnsorted'](this.chatId);
	    },
	    hasFiles() {
	      return this.files.length > 0;
	    },
	    isMigrationFinished() {
	      return this.$store.state.sidebar.isFilesMigrated;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  methods: {
	    onOpenDetail() {
	      if (!this.hasFiles) {
	        return;
	      }
	      const panel = this.isMigrationFinished ? im_v2_const.SidebarDetailBlock.file : im_v2_const.SidebarDetailBlock.fileUnsorted;
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel,
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-preview__scope">
			<div class="bx-im-sidebar-file-preview__container">
				<div 
					class="bx-im-sidebar-file-preview__header_container" 
					:class="[hasFiles ? '--active': '']" 
					@click="onOpenDetail"
				>
					<span class="bx-im-sidebar-file-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE') }}
					</span>
					<div v-if="hasFiles" class="bx-im-sidebar__forward-icon"></div>
				</div>
				<div v-if="hasFiles" class="bx-im-sidebar-file-preview__files-container">
					<FilePreviewItem v-for="file in files" :fileItem="file" />
				</div>
				<DetailEmptyState
					v-else
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_AND_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.media"
				/>
			</div>
		</div>
	`
	};

	class SidebarMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName()
	    };
	  }
	  getOpenContextMessageItem() {
	    if (!this.context.messageId || this.context.messageId === 0) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_GO_TO_CONTEXT_MESSAGE'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.goToMessageContext, {
	          messageId: this.context.messageId,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyLinkItem(title) {
	    if (!BX.clipboard.isCopySupported()) {
	      return null;
	    }
	    return {
	      text: title,
	      onclick: () => {
	        if (BX.clipboard.copy(this.context.source)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS')
	          });
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	}

	class TaskManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    id,
	    chatId
	  }) {
	    this.store.dispatch('sidebar/tasks/delete', {
	      chatId,
	      id
	    });
	    const queryParams = {
	      LINK_ID: id
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imChatTaskDelete, queryParams).catch(error => {
	      console.error('Im.Sidebar: error deleting task', error);
	    });
	  }
	}

	class TaskMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	    this.taskManager = new TaskManager();
	  }
	  getMenuItems() {
	    return [this.getOpenContextMessageItem(), this.getCopyLinkItem(main_core.Loc.getMessage('IM_SIDEBAR_MENU_COPY_TASK_LINK')), this.getDeleteItem()];
	  }
	  getDeleteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_TASK_CONNECTION'),
	      onclick: function () {
	        this.taskManager.delete(this.context.task);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}

	// @vue/component
	const TaskItem = {
	  name: 'TaskItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    AvatarSize: im_v2_component_elements.AvatarSize
	  },
	  props: {
	    task: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    taskItem() {
	      return this.task;
	    },
	    taskTitle() {
	      if (this.searchQuery.length === 0) {
	        return main_core.Text.encode(this.taskItem.task.title);
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(this.taskItem.task.title), this.searchQuery);
	    },
	    taskAuthorDialogId() {
	      return this.taskItem.task.creatorId.toString();
	    },
	    taskResponsibleDialogId() {
	      return this.taskItem.task.responsibleId.toString();
	    },
	    taskDeadlineText() {
	      const statusToShow = main_core.Type.isStringFilled(this.taskItem.task.state) ? this.taskItem.task.state : this.taskItem.task.statusTitle;
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(statusToShow);
	    },
	    taskBackgroundColorClass() {
	      if (this.taskItem.task.status === 5) {
	        return '--completed';
	      }
	      return '';
	    },
	    statusColorClass() {
	      if (!this.taskItem.task.color || !ui_label.LabelColor[this.taskItem.task.color.toUpperCase()]) {
	        return '';
	      }
	      return `ui-label-${this.taskItem.task.color.toLowerCase()}`;
	    }
	  },
	  methods: {
	    onTaskClick() {
	      BX.SidePanel.Instance.open(this.taskItem.task.source, {
	        cacheable: false
	      });
	    },
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        task: this.taskItem,
	        source: this.taskItem.task.source,
	        messageId: this.taskItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-task-item__container bx-im-sidebar-task-item__scope" 
			:class="taskBackgroundColorClass"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-task-item__content" @click="onTaskClick">
				<div class="bx-im-sidebar-task-item__header-text" :title="taskTitle" v-html="taskTitle"></div>
				<div class="bx-im-sidebar-task-item__detail-container">
					<ChatAvatar 
						:size="AvatarSize.XS"
						:avatarDialogId="taskAuthorDialogId"
						:contextDialogId="contextDialogId"
					/>
					<div class="bx-im-sidebar-task-item__forward-small-icon bx-im-sidebar__forward-small-icon"></div>
					<ChatAvatar 
						:avatarDialogId="taskResponsibleDialogId" 
						:contextDialogId="contextDialogId" 
						:size="AvatarSize.XS" 
					/>
					<div class="bx-im-sidebar-task-item__status-text" :class="statusColorClass">
						{{taskDeadlineText}}
					</div>
				</div>
			</div>
			<button 
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`
	};

	// @vue/component
	const TaskPreview = {
	  name: 'TaskPreview',
	  components: {
	    DetailEmptyState,
	    TaskItem,
	    MessengerButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    firstTask() {
	      return this.$store.getters['sidebar/tasks/get'](this.chatId)[0];
	    },
	    showAddButton() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createTask, this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    },
	    addButtonColor() {
	      if (this.isCopilotLayout) {
	        return this.ButtonColor.Copilot;
	      }
	      return this.ButtonColor.PrimaryLight;
	    }
	  },
	  created() {
	    this.contextMenu = new TaskMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    getEntityCreator() {
	      return new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	    },
	    onAddClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatEntities.onCreateTaskFromSidebarClick(this.dialogId);
	      void this.getEntityCreator().createTaskForChat();
	    },
	    onOpenDetail() {
	      if (!this.firstTask) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.task,
	        dialogId: this.dialogId
	      });
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-task-preview__scope">
			<div class="bx-im-sidebar-task-preview__container">
				<div 
					class="bx-im-sidebar-task-preview__header_container"
					:class="[firstTask ? '--active': '']"
					@click="onOpenDetail"
				>
					<div class="bx-im-sidebar-task-preview__title">
						<span class="bx-im-sidebar-task-preview__title-text">
							{{ loc('IM_SIDEBAR_TASK_DETAIL_TITLE') }}
						</span>
						<div v-if="firstTask" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							v-if="showAddButton"
							:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="addButtonColor"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-task-preview__title-button"
						/>
					</transition>
				</div>
				<TaskItem 
					v-if="firstTask"
					:contextDialogId="dialogId"
					:task="firstTask" @contextMenuClick="onContextMenuClick"
				/>
				<DetailEmptyState 
					v-else 
					:title="loc('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
			</div>
		</div>
	`
	};

	const NotEmptyCollabErrorCodes = new Set(['TASKS_NOT_EMPTY', 'DISK_NOT_EMPTY', 'CALENDAR_NOT_EMPTY']);
	var _deleteChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteChat");
	var _deleteCollab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteCollab");
	var _isDeletionCancelled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletionCancelled");
	var _handleDeleteCollabError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDeleteCollabError");
	var _showNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNotification");
	var _isPersonalChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPersonalChat");
	class MainMenu extends im_v2_lib_menu.RecentMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isPersonalChat, {
	      value: _isPersonalChat2
	    });
	    Object.defineProperty(this, _showNotification, {
	      value: _showNotification2
	    });
	    Object.defineProperty(this, _handleDeleteCollabError, {
	      value: _handleDeleteCollabError2
	    });
	    Object.defineProperty(this, _isDeletionCancelled, {
	      value: _isDeletionCancelled2
	    });
	    Object.defineProperty(this, _deleteCollab, {
	      value: _deleteCollab2
	    });
	    Object.defineProperty(this, _deleteChat, {
	      value: _deleteChat2
	    });
	    this.id = 'im-sidebar-context-menu';
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: false
	    };
	  }
	  getMenuItems() {
	    return [this.getPinMessageItem(), this.getEditItem(), this.getAddMembersToChatItem(), this.getOpenProfileItem(), this.getOpenUserCalendarItem(), this.getChatsWithUserItem(), this.getHideItem(), this.getLeaveItem(), this.getDeleteItem()];
	  }
	  getEditItem() {
	    if (!this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.update, this.context.dialogId)) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_UPDATE_CHAT'),
	      onclick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().chatEdit.onOpenForm(this.context.dialogId);
	        void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	          name: im_v2_const.Layout.updateChat.name,
	          entityId: this.context.dialogId
	        });
	      }
	    };
	  }
	  getDeleteItem() {
	    if (!this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.delete, this.context.dialogId)) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT'),
	      className: 'menu-popup-no-icon bx-im-sidebar__context-menu_delete',
	      onclick: async () => {
	        im_v2_lib_analytics.Analytics.getInstance().chatDelete.onClick(this.context.dialogId);
	        if (await babelHelpers.classPrivateFieldLooseBase(this, _isDeletionCancelled)[_isDeletionCancelled]()) {
	          return;
	        }
	        im_v2_lib_analytics.Analytics.getInstance().chatDelete.onConfirm(this.context.dialogId);
	        if (this.isCollabChat()) {
	          babelHelpers.classPrivateFieldLooseBase(this, _deleteCollab)[_deleteCollab]();
	          return;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _deleteChat)[_deleteChat]();
	      }
	    };
	  }
	  getOpenUserCalendarItem() {
	    if (!this.isUser()) {
	      return null;
	    }
	    if (this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getCalendarLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR_V2'),
	      onclick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getAddMembersToChatItem() {
	    if (this.isBot()) {
	      return null;
	    }
	    const hasCreateChatAccess = this.permissionManager.canPerformActionByUserType(im_v2_const.ActionByUserType.createChat);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isPersonalChat)[_isPersonalChat]() && !hasCreateChatAccess) {
	      return null;
	    }
	    const hasAccessByRole = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.extend, this.context.dialogId);
	    if (!hasAccessByRole) {
	      return null;
	    }
	    const text = this.isChannel() ? main_core.Loc.getMessage('IM_SIDEBAR_MENU_INVITE_SUBSCRIBERS') : main_core.Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS_V2');
	    return {
	      text,
	      onclick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatSidebarClick(this.dialogId);
	        this.emit(MainMenu.events.onAddToChatShow);
	        this.menuInstance.close();
	      }
	    };
	  }
	}
	async function _deleteChat2() {
	  try {
	    await new im_v2_provider_service.ChatService().deleteChat(this.context.dialogId);
	    void im_v2_lib_layout.LayoutManager.getInstance().clearCurrentLayoutEntityId();
	  } catch {
	    babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT_ERROR'));
	  }
	}
	async function _deleteCollab2() {
	  try {
	    babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_NOTIFICATION'));
	    await new im_v2_provider_service.ChatService().deleteCollab(this.context.dialogId);
	    void im_v2_lib_layout.LayoutManager.getInstance().clearCurrentLayoutEntityId();
	    void im_v2_lib_layout.LayoutManager.getInstance().deleteLastOpenedElementById(this.context.dialogId);
	  } catch (errors) {
	    babelHelpers.classPrivateFieldLooseBase(this, _handleDeleteCollabError)[_handleDeleteCollabError](errors);
	  }
	}
	async function _isDeletionCancelled2() {
	  const {
	    dialogId
	  } = this.context;
	  const confirmResult = await im_v2_lib_confirm.showDeleteChatConfirm(dialogId);
	  if (!confirmResult) {
	    im_v2_lib_analytics.Analytics.getInstance().chatDelete.onCancel(dialogId);
	    return true;
	  }
	  return false;
	}
	function _handleDeleteCollabError2(errors) {
	  if (!main_core.Type.isArrayFilled(errors)) {
	    return;
	  }
	  const [firstError] = errors;
	  if (NotEmptyCollabErrorCodes.has(firstError.code)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_WITH_ENTITIES_ERROR'));
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _showNotification)[_showNotification](main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_ERROR'));
	}
	function _showNotification2(content) {
	  BX.UI.Notification.Center.notify({
	    content
	  });
	}
	function _isPersonalChat2() {
	  const chat = this.getChat(this.context.dialogId);
	  return chat.type === im_v2_const.ChatType.user;
	}
	MainMenu.events = {
	  onAddToChatShow: 'onAddToChatShow'
	};

	const HeaderTitleByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_SIDEBAR_CHANNEL_HEADER_TITLE'),
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHANNEL_HEADER_TITLE'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHANNEL_HEADER_TITLE'),
	  [im_v2_const.ChatType.comment]: main_core.Loc.getMessage('IM_SIDEBAR_COMMENTS_HEADER_TITLE'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_SIDEBAR_COLLAB_HEADER_TITLE'),
	  default: main_core.Loc.getMessage('IM_SIDEBAR_HEADER_TITLE')
	};
	const ChatTypesWithMenuDisabled = new Set([im_v2_const.ChatType.comment, im_v2_const.ChatType.lines]);

	// @vue/component
	const MainHeader = {
	  name: 'MainHeader',
	  components: {
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    AddToCollab: im_v2_component_entitySelector.AddToCollab
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    recentItem() {
	      return this.$store.getters['recent/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    headerTitle() {
	      var _HeaderTitleByChatTyp;
	      return (_HeaderTitleByChatTyp = HeaderTitleByChatType[this.dialog.type]) != null ? _HeaderTitleByChatTyp : HeaderTitleByChatType.default;
	    },
	    showMenuIcon() {
	      return this.canOpenMenu && this.isMenuEnabledForType;
	    },
	    canOpenMenu() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.openSidebarMenu, this.dialogId);
	    },
	    isMenuEnabledForType() {
	      return !ChatTypesWithMenuDisabled.has(this.dialog.type);
	    },
	    addMembersPopupComponent() {
	      return this.dialog.type === im_v2_const.ChatType.collab ? im_v2_component_entitySelector.AddToCollab : im_v2_component_entitySelector.AddToChat;
	    }
	  },
	  created() {
	    this.contextMenu = new MainMenu();
	    this.contextMenu.subscribe(MainMenu.events.onAddToChatShow, this.onAddChatShow);
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	    this.contextMenu.unsubscribe(MainMenu.events.onAddToChatShow, this.onAddChatShow);
	  },
	  methods: {
	    onAddChatShow() {
	      this.showAddToChatPopup = true;
	    },
	    onContextMenuClick(event) {
	      const item = {
	        dialogId: this.dialogId,
	        ...this.recentItem
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onSidebarCloseClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-header__container bx-im-sidebar-header__scope">
			<div class="bx-im-sidebar-header__title-container">
				<button 
					class="bx-im-sidebar-header__cross-icon bx-im-messenger__cross-icon" 
					@click="onSidebarCloseClick"
				></button>
				<div class="bx-im-sidebar-header__title">{{ headerTitle }}</div>
			</div>
			<button
				v-if="showMenuIcon"
				class="bx-im-sidebar-header__context-menu-icon bx-im-messenger__context-menu-icon"
				@click="onContextMenuClick"
				ref="context-menu"
			></button>
			<component
				v-if="showAddToChatPopup"
				:is="addMembersPopupComponent"
				:bindElement="$refs['context-menu'] || {}"
				:dialogId="dialogId"
				:popupConfig="{offsetTop: 0, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const MarketItem = {
	  name: 'MarketItem',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    marketItem() {
	      return this.item;
	    },
	    iconClass() {
	      return `fa ${this.marketItem.options.iconName}`;
	    },
	    iconColor() {
	      return this.marketItem.options.color;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-market-preview-item__container bx-im-sidebar-market-preview-item__scope">
			<div class="bx-im-sidebar-market-preview-item__icon-container" :style="{backgroundColor: iconColor}">
				<i :class="iconClass" aria-hidden="true"></i>
			</div>
			<div class="bx-im-sidebar-market-preview-item__title-container" :title="marketItem.title">
				{{ marketItem.title }}
			</div>
		</div>
	`
	};

	// @vue/component
	const MarketPreview = {
	  name: 'MarketPreview',
	  components: {
	    MarketItem
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['openDetail'],
	  computed: {
	    marketMenuItems() {
	      return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.sidebar, this.dialogId);
	    }
	  },
	  methods: {
	    onMarketItemClick(entityId) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.market,
	        dialogId: this.dialogId,
	        entityId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-market-preview__scope bx-im-sidebar-market-preview__container">
			<div class="bx-im-sidebar-market-preview__header_container">
				<div class="bx-im-sidebar-market-preview__title">
					<span class="bx-im-sidebar-market-preview__title-text">
						{{ loc('IM_SIDEBAR_MARKET_DETAIL_TITLE') }}
					</span>
				</div>
			</div>
			<MarketItem 
				v-for="item in marketMenuItems" 
				:key="item.id"
				:item="item"
				@click="onMarketItemClick(item.id)"
			/>
		</div>
	`
	};

	class MeetingManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    id,
	    chatId
	  }) {
	    this.store.dispatch('sidebar/meetings/delete', {
	      chatId,
	      id
	    });
	    const queryParams = {
	      LINK_ID: id
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imChatCalendarDelete, queryParams).catch(error => {
	      console.error('Im.Sidebar: error deleting meeting', error);
	    });
	  }
	}

	class MeetingMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	    this.meetingManager = new MeetingManager();
	  }
	  getMenuItems() {
	    return [this.getOpenContextMessageItem(), this.getCopyLinkItem(main_core.Loc.getMessage('IM_SIDEBAR_MENU_COPY_MEETING_LINK')), this.getDeleteItem()];
	  }
	  getDeleteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_MEETING_CONNECTION'),
	      onclick: function () {
	        this.meetingManager.delete(this.context.meeting);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}

	// @vue/component
	const MeetingItem = {
	  name: 'MeetingItem',
	  props: {
	    meeting: {
	      type: Object,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    meetingItem() {
	      return this.meeting;
	    },
	    title() {
	      if (this.searchQuery.length === 0) {
	        return main_core.Text.encode(this.meetingItem.meeting.title);
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(this.meetingItem.meeting.title), this.searchQuery);
	    },
	    date() {
	      const meetingDate = this.meetingItem.meeting.dateFrom;
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(meetingDate, im_v2_lib_dateFormatter.DateTemplate.meeting);
	    },
	    day() {
	      return this.meetingItem.meeting.dateFrom.getDate().toString();
	    },
	    monthShort() {
	      return main_date.DateTimeFormat.format('M', this.meetingItem.meeting.dateFrom);
	    },
	    isActive() {
	      return this.meetingItem.meeting.dateFrom.getTime() > Date.now();
	    }
	  },
	  methods: {
	    onMeetingClick() {
	      // todo replace this call to something
	      new (window.top.BX || window.BX).Calendar.SliderLoader(this.meetingItem.meeting.id).show();
	    },
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        meeting: this.meetingItem,
	        source: this.meetingItem.meeting.source,
	        messageId: this.meetingItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-meeting-item__container bx-im-sidebar-meeting-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div 
				class="bx-im-sidebar-meeting-item__icon-container"
				:class="[isActive ? '--active' : '--inactive']"
			>
				<div class="bx-im-sidebar-meeting-item__day-text">{{ day }}</div>
				<div class="bx-im-sidebar-meeting-item__month-text">{{ monthShort }}</div>
			</div>
			<div class="bx-im-sidebar-meeting-item__content-container" @click="onMeetingClick">
				<div class="bx-im-sidebar-meeting-item__content">
					<div class="bx-im-sidebar-meeting-item__title" :title="title" v-html="title"></div>
					<div class="bx-im-sidebar-meeting-item__date">{{ date }}</div>
				</div>
			</div>
			<button 
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`
	};

	// @vue/component
	const MeetingPreview = {
	  name: 'MeetingPreview',
	  components: {
	    MeetingItem,
	    DetailEmptyState,
	    MessengerButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    firstMeeting() {
	      return this.$store.getters['sidebar/meetings/get'](this.chatId)[0];
	    },
	    showAddButton() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createMeeting, this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    },
	    addButtonColor() {
	      if (this.isCopilotLayout) {
	        return this.ButtonColor.Copilot;
	      }
	      return this.ButtonColor.PrimaryLight;
	    }
	  },
	  created() {
	    this.contextMenu = new MeetingMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    getEntityCreator() {
	      return new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	    },
	    onAddClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatEntities.onCreateEventFromSidebarClick(this.dialogId);
	      void this.getEntityCreator().createMeetingForChat();
	    },
	    onOpenDetail() {
	      if (!this.firstMeeting) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.meeting,
	        dialogId: this.dialogId
	      });
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-meeting-preview__scope">
			<div class="bx-im-sidebar-meeting-preview__container">
				<div
					class="bx-im-sidebar-meeting-preview__header_container"
					:class="[firstMeeting ? '--active': '']"
					@click="onOpenDetail"
				>
					<div class="bx-im-sidebar-meeting-preview__title">
						<span class="bx-im-sidebar-meeting-preview__title-text">
							{{ loc('IM_SIDEBAR_MEETING_DETAIL_TITLE') }}
						</span>
						<div v-if="firstMeeting" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							v-if="showAddButton"
							:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="addButtonColor"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-meeting-preview__title-button"
						/>
					</transition>
				</div>
				<MeetingItem v-if="firstMeeting" :meeting="firstMeeting" @contextMenuClick="onContextMenuClick"/>
				<DetailEmptyState
					v-else
					:title="loc('IM_SIDEBAR_MEETINGS_EMPTY')"
					:iconType="SidebarDetailBlock.meeting"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const CopilotInfoPreview = {
	  name: 'CopilotInfoPreview',
	  components: {
	    ChatDescription,
	    ChatLinks,
	    ChatFavourites
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-info-preview__container">
			<ChatDescription :dialogId="dialogId" />
			<ChatFavourites :dialogId="dialogId" />
		</div>
	`
	};

	// @vue/component
	const MuteChat = {
	  name: 'MuteChat',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    Toggle: im_v2_component_elements.Toggle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements.ToggleSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isGroupChat() {
	      return this.dialogId.startsWith('chat');
	    },
	    canBeMuted() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.mute, this.dialogId);
	    },
	    isChatMuted() {
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return Boolean(isMuted);
	    },
	    hintMuteNotAvailable() {
	      if (this.canBeMuted) {
	        return null;
	      }
	      return {
	        text: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MUTE_NOT_AVAILABLE'),
	        popupOptions: {
	          angle: true,
	          targetContainer: document.body,
	          offsetLeft: 141,
	          offsetTop: -10,
	          bindOptions: {
	            position: 'top'
	          }
	        }
	      };
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    }
	  },
	  methods: {
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    muteActionHandler() {
	      if (!this.canBeMuted) {
	        return;
	      }
	      if (this.isChatMuted) {
	        this.getChatService().unmuteChat(this.dialogId);
	      } else {
	        this.getChatService().muteChat(this.dialogId);
	      }
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			v-if="isGroupChat"
			class="bx-im-sidebar-mute-chat__container"
			:class="{'--not-active': !canBeMuted, '--copilot': isCopilotLayout}"
			v-hint="hintMuteNotAvailable"
		>
			<div class="bx-im-sidebar-mute-chat__title">
				<div class="bx-im-sidebar-mute-chat__title-text bx-im-sidebar-mute-chat__icon">
					{{ loc('IM_SIDEBAR_ENABLE_NOTIFICATION_TITLE_2') }}
				</div>
				<Toggle :size="ToggleSize.M" :isEnabled="!isChatMuted" @click="muteActionHandler" />
			</div>
		</div>
	`
	};

	// @vue/component
	const AutoDelete = {
	  name: 'AutoDelete',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    Toggle: im_v2_component_elements.Toggle
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements.ToggleSize,
	    hintAutoDeleteNotAvailable() {
	      return {
	        text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
	        popupOptions: {
	          bindOptions: {
	            position: 'top'
	          },
	          angle: true,
	          targetContainer: document.body,
	          offsetLeft: 125,
	          offsetTop: -10
	        }
	      };
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-auto-delete__container --not-active" v-hint="hintAutoDeleteNotAvailable">
			<div class="bx-im-sidebar-auto-delete__title">
				<div class="bx-im-sidebar-auto-delete__title-text bx-im-sidebar-auto-delete__icon">
					{{ loc('IM_SIDEBAR_ENABLE_AUTODELETE_TITLE') }}
				</div>
				<Toggle :size="ToggleSize.M" :isEnabled="false" />
			</div>
			<div class="bx-im-sidebar-auto-delete__status">
				{{ loc('IM_SIDEBAR_AUTODELETE_STATUS_OFF') }}
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatMembersAvatars = {
	  name: 'ChatMembersAvatars',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    MessengerButton: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    showMembers: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogIds() {
	      const PREVIEW_USERS_COUNT = 4;
	      const userIds = this.$store.getters['sidebar/members/get'](this.chatId);
	      return userIds.map(id => id.toString()).slice(0, PREVIEW_USERS_COUNT);
	    },
	    canSeeMembers() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.userList, this.dialogId);
	    },
	    canInviteMembers() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.extend, this.dialogId);
	    },
	    usersInChatCount() {
	      return this.dialog.userCounter;
	    },
	    moreUsersCount() {
	      return Math.max(this.usersInChatCount - this.dialogIds.length, 0);
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    },
	    isCollab() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    addUsersButtonColor() {
	      if (this.isCopilotLayout) {
	        return this.ButtonColor.Copilot;
	      }
	      if (this.isCollab) {
	        return this.ButtonColor.Collab;
	      }
	      return this.ButtonColor.PrimaryLight;
	    },
	    addMembersPopupComponent() {
	      return this.dialog.type === im_v2_const.ChatType.collab ? im_v2_component_entitySelector.AddToCollab : im_v2_component_entitySelector.AddToChat;
	    }
	  },
	  methods: {
	    onOpenUsers() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    },
	    onOpenInvitePopup() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatSidebarClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-chat-members-avatars__container">
			<div v-if="canSeeMembers && showMembers" class="bx-im-sidebar-chat-members-avatars__members" @click="onOpenUsers">
				<div class="bx-im-sidebar-chat-members-avatars__avatars" >
					<ChatAvatar
						v-for="id in dialogIds"
						:size="AvatarSize.S"
						:avatarDialogId="id"
						:contextDialogId="dialogId"
						class="bx-im-sidebar-chat-members-avatars__avatar"
					/>
				</div>
				<div v-if="moreUsersCount > 0" class="bx-im-sidebar-chat-members-avatars__text">
					+{{ moreUsersCount }}
				</div>
			</div>
			<div ref="add-members">
				<MessengerButton
					v-if="canInviteMembers"
					:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
					:size="ButtonSize.S"
					:color="addUsersButtonColor"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onOpenInvitePopup"
				/>
			</div>
			<component
				v-if="showAddToChatPopup"
				:is="addMembersPopupComponent"
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:popupConfig="{offsetTop: -220, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const ChatPreview = {
	  name: 'ChatPreview',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    MuteChat,
	    ChatMembersAvatars,
	    AutoDelete
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<ChatAvatar 
						:avatarDialogId="dialogId" 
						:contextDialogId="dialogId" 
						:size="AvatarSize.XXXL" 
					/>
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__chat-members">
				<ChatMembersAvatars :dialogId="dialogId" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__settings">
				<MuteChat :dialogId="dialogId" />
				<AutoDelete />
			</div>
		</div>
	`
	};

	// @vue/component
	const PostPreview = {
	  name: 'PostPreview',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    MuteChat,
	    ChatMembersAvatars,
	    AutoDelete
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    postDialog() {
	      return this.$store.getters['chats/getByChatId'](this.dialog.parentChatId);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview-post__scope">
			<div class="bx-im-sidebar-main-preview-post__avatar-container">
				<div class="bx-im-sidebar-main-preview-post__avatar">
					<ChatAvatar
						:avatarDialogId="postDialog.dialogId"
						:contextDialogId="postDialog.dialogId"
						:size="AvatarSize.XXXL" 
					/>
				</div>
				<div class="bx-im-sidebar-main-preview-post__title">{{ loc('IM_SIDEBAR_COMMENTS_POST_PREVIEW_TITLE') }}</div>
				<div class="bx-im-sidebar-main-preview-post__subtitle">{{ postDialog.name }}</div>
			</div>
			<div class="bx-im-sidebar-main-preview-post__settings">
				<!-- TODO: follow toggle -->
			</div>
		</div>
	`
	};

	// @vue/component
	const UserPreview = {
	  name: 'UserPreview',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    MessengerButton: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    AutoDelete
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    userPosition() {
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    canInviteMembers() {
	      const canCreateChat = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createChat);
	      const canExtendChat = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.extend, this.dialogId);
	      return canCreateChat && canExtendChat;
	    },
	    showInviteButton() {
	      if (this.isBot) {
	        return false;
	      }
	      return this.canInviteMembers;
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    },
	    isBot() {
	      return this.user.type === im_v2_const.UserType.bot;
	    }
	  },
	  methods: {
	    onAddClick() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatSidebarClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-personal-chat__avatar-container">
				<ChatAvatar
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.XXXL"
					class="bx-im-sidebar-main-preview-personal-chat__avatar"
				/>
				<a :href="userLink" target="_blank">
					<ChatTitle :dialogId="dialogId" class="bx-im-sidebar-main-preview-personal-chat__user-name" />
				</a>
				<div class="bx-im-sidebar-main-preview-personal-chat__user-position" :title="userPosition">
					{{ userPosition }}
				</div>
			</div>
			<div 
				v-if="showInviteButton" 
				class="bx-im-sidebar-main-preview-personal-chat__invite-button-container" 
				ref="add-members"
			>
				<MessengerButton
					v-if="canInviteMembers"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_CREATE_GROUP_CHAT')"
					:size="ButtonSize.S"
					:color="ButtonColor.PrimaryLight"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onAddClick"
				/>
			</div>
			<div class="bx-im-sidebar-main-preview-personal-chat__auto-delete-container">
				<AutoDelete :dialogId="dialogId" />
			</div>
			<AddToChat
				v-if="showAddToChatPopup"
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:popupConfig="{offsetTop: -220, offsetLeft: -320}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _sendRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendRequest");
	class CopilotService {
	  constructor() {
	    Object.defineProperty(this, _sendRequest, {
	      value: _sendRequest2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  updateRole({
	    dialogId,
	    role
	  }) {
	    im_v2_lib_logger.Logger.warn('CopilotService: update role', dialogId);
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('copilot/chats/add', {
	      dialogId,
	      role: role.code
	    });
	    void babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('copilot/roles/add', [role]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _sendRequest)[_sendRequest]({
	      dialogId,
	      role: role.code
	    });
	  }
	}
	function _sendRequest2({
	  dialogId,
	  role
	}) {
	  const requestParams = {
	    data: {
	      dialogId,
	      role
	    }
	  };
	  return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatCopilotUpdateRole, requestParams);
	}

	// @vue/component
	const ChangeRolePromo = {
	  name: 'ChangeRolePromo',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['hide', 'accept'],
	  computed: {
	    text() {
	      return main_core.Loc.getMessage('IM_SIDEBAR_COPILOT_CHANGE_ROLE_PROMO_TEXT', {
	        '[copilot_color]': '<em class="bx-im-copilot-change-role-promo__copilot">',
	        '[/copilot_color]': '</em>'
	      });
	    },
	    videoSource() {
	      const basePath = '/bitrix/js/im/v2/component/sidebar/src/components/elements/copilot-role/css/videos/';
	      const sources = {
	        ru: 'copilot-roles-promo-ru.webm',
	        en: 'copilot-roles-promo-en.webm'
	      };
	      const language = main_core.Loc.getMessage('LANGUAGE_ID');
	      return language === 'ru' ? `${basePath}${sources.ru}` : `${basePath}${sources.en}`;
	    }
	  },
	  created() {
	    this.promoPopup = new ui_promoVideoPopup.PromoVideoPopup({
	      videoSrc: this.videoSource,
	      title: 'Copilot',
	      text: this.text,
	      targetOptions: this.bindElement,
	      angleOptions: {
	        position: BX.UI.AnglePosition.RIGHT,
	        offset: 98
	      },
	      colors: {
	        iconBackground: '#8e52ec',
	        title: '#b095dc'
	      },
	      icon: BX.UI.IconSet.Main.COPILOT_AI,
	      offset: {
	        top: -125,
	        left: -510
	      }
	    });
	    this.promoPopup.subscribe(ui_promoVideoPopup.PromoVideoPopupEvents.ACCEPT, this.onAccept);
	    this.promoPopup.subscribe(ui_promoVideoPopup.PromoVideoPopupEvents.HIDE, this.onHide);
	  },
	  mounted() {
	    this.promoPopup.show();
	  },
	  beforeUnmount() {
	    if (!this.promoPopup) {
	      return;
	    }
	    this.promoPopup.hide();
	    this.promoPopup.unsubscribe(ui_promoVideoPopup.PromoVideoPopupEvents.ACCEPT, this.onAccept);
	    this.promoPopup.unsubscribe(ui_promoVideoPopup.PromoVideoPopupEvents.HIDE, this.onHide);
	  },
	  methods: {
	    onHide() {
	      this.$emit('hide');
	      this.promoPopup.hide();
	    },
	    onAccept() {
	      this.$emit('accept');
	      this.promoPopup.hide();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<template></template>
	`
	};

	// @vue/component
	const CopilotRole = {
	  name: 'CopilotRole',
	  components: {
	    ChangeRolePromo,
	    CopilotRolesDialog: im_v2_component_elements.CopilotRolesDialog
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      shouldShowChangeRolePromo: false,
	      showRolesDialog: false
	    };
	  },
	  computed: {
	    chatRole() {
	      const chatRole = this.$store.getters['copilot/chats/getRole'](this.dialogId);
	      if (!chatRole) {
	        return this.$store.getters['copilot/roles/getDefault'];
	      }
	      return chatRole;
	    },
	    roleName() {
	      return this.chatRole.name;
	    },
	    canShowChangeRolePromo() {
	      // we don't want to show change role promo if we are still showing first promo (add users to copilot chat)
	      const needToShowAddUsersToChatHint = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.addUsersToCopilotChat);
	      const needToShowChangeRolePromo = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.changeRoleCopilot);
	      return !needToShowAddUsersToChatHint && needToShowChangeRolePromo;
	    }
	  },
	  mounted() {
	    // Show promo after sidebar animation is over.
	    setTimeout(() => {
	      this.shouldShowChangeRolePromo = this.canShowChangeRolePromo;
	    }, 300);
	  },
	  beforeUnmount() {
	    this.showRolesDialog = false;
	    this.shouldShowChangeRolePromo = false;
	  },
	  methods: {
	    handleChangeRole() {
	      this.showRolesDialog = true;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onChangeRolePromoAccept() {
	      this.shouldShowChangeRolePromo = false;
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.changeRoleCopilot);
	    },
	    onCopilotDialogSelectRole(role) {
	      void new CopilotService().updateRole({
	        dialogId: this.dialogId,
	        role
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-copilot-role__container" @click="handleChangeRole" ref="change-role">
			<div class="bx-im-sidebar-copilot-role__title">
				<div class="bx-im-sidebar-copilot-role__title-icon"></div>
				<div class="bx-im-sidebar-copilot-role__title-text">
					{{ roleName }}
				</div>
			</div>
			<div class="bx-im-sidebar-copilot-role__arrow-icon"></div>
			<ChangeRolePromo 
				v-if="shouldShowChangeRolePromo"
				:bindElement="$refs['change-role']"
				@accept="onChangeRolePromoAccept"
				@hide="shouldShowChangeRolePromo = false"
			/>
			<CopilotRolesDialog
				v-if="showRolesDialog"
				:title="loc('IM_SIDEBAR_COPILOT_CHANGE_ROLE_DIALOG_TITLE')"
				@selectRole="onCopilotDialogSelectRole"
				@close="showRolesDialog = false"
			/>
		</div>
	`
	};

	// @vue/component
	const CopilotPreview = {
	  name: 'CopilotPreview',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    MuteChat,
	    ChatMembersAvatars,
	    CopilotRole
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    showMembers() {
	      return this.dialog.userCounter > 2;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-copilot-preview__scope">
			<div class="bx-im-sidebar-copilot-preview-group-chat__avatar-container">
				<ChatAvatar
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.XXXL"
					:withSpecialTypes="false"
				/>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-copilot-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-copilot-preview-group-chat__chat-members">
				<ChatMembersAvatars :showMembers="showMembers" :dialogId="dialogId" />
			</div>
			<div class="bx-im-sidebar-copilot-preview-group-chat__settings">
				<CopilotRole :dialogId="dialogId" />
				<MuteChat :dialogId="dialogId" />
			</div>
		</div>
	`
	};

	// @vue/component
	const SupportPreview = {
	  name: 'SupportPreview',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    AutoDelete
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<ChatAvatar :size="AvatarSize.XXXL" :avatarDialogId="dialogId" :contextDialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__settings">
				<AutoDelete />
			</div>
		</div>
	`
	};

	// @vue/component
	const MultidialogPreview = {
	  name: 'MultidialogPreview',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    chatId() {
	      return this.$store.getters['chats/get'](this.dialogId, true).chatId;
	    },
	    numberRequests() {
	      const chatsCount = this.$store.getters['sidebar/multidialog/getChatsCount'];
	      return chatsCount > 999 ? '999+' : chatsCount;
	    },
	    totalChatCounter() {
	      const counter = this.$store.getters['sidebar/multidialog/getTotalChatCounter'];
	      return counter > 99 ? '99+' : counter;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onOpenDetail() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.multidialog,
	        dialogId: this.dialogId,
	        standalone: true
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-multidialog-preview__scope">
			<div class="bx-im-sidebar-multidialog-preview__container" @click="onOpenDetail">
				<div class="bx-im-sidebar-multidialog-preview__questions-container">
					<div class="bx-im-sidebar-multidialog-preview__questions-text">
						{{ loc('IM_SIDEBAR_SUPPORT_TICKET_TITLE') }}
					</div>
					<div class="bx-im-sidebar-multidialog-preview__questions-count">
						{{ numberRequests }}
					</div>
				</div>
				<div class="bx-im-sidebar-multidialog-preview__new-message-container">
					<div v-if="totalChatCounter" class="bx-im-sidebar-multidialog-preview__new-message-counter">
						{{ totalChatCounter }}
					</div>
					<div class="bx-im-sidebar__forward-icon" />
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const TariffLimit = {
	  name: 'TariffLimit',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    panel: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    title() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getLimitTitle();
	    },
	    preparedDescription() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getLimitSubtitle(true).replace('[action_emphasis]', '<em class="bx-im-sidebar-elements-tariff-limit__description-accent">').replace('[/action_emphasis]', '</em>');
	    },
	    tooltipText() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getTooltipText();
	    }
	  },
	  watch: {
	    dialogId() {
	      this.sendAnalyticsOnCreate();
	    },
	    panel() {
	      this.sendAnalyticsOnCreate();
	    }
	  },
	  created() {
	    this.sendAnalyticsOnCreate();
	  },
	  methods: {
	    onDetailClick() {
	      this.sendAnalyticsOnClick();
	      im_v2_lib_feature.FeatureManager.chatHistory.openFeatureSlider();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    sendAnalyticsOnClick() {
	      im_v2_lib_analytics.Analytics.getInstance().historyLimit.onSidebarBannerClick({
	        dialogId: this.dialogId,
	        panel: this.panel
	      });
	    },
	    sendAnalyticsOnCreate() {
	      im_v2_lib_analytics.Analytics.getInstance().historyLimit.onSidebarLimitExceeded({
	        dialogId: this.dialogId,
	        panel: this.panel
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-sidebar-elements-tariff-limit__container"
			:title="tooltipText"
			@click="onDetailClick"
		>
			<div class="bx-im-sidebar-elements-tariff-limit__header">
				<div class="bx-im-sidebar-elements-tariff-limit__title-container">
					<div class="bx-im-sidebar-elements-tariff-limit__icon"></div>
					<div class="bx-im-sidebar-elements-tariff-limit__title --line-clamp-2">{{ title }}</div>
				</div>
				<div class="bx-im-sidebar-elements-tariff-limit__arrow bx-im-sidebar__forward-green-icon"></div>
			</div>
			<div class="bx-im-sidebar-elements-tariff-limit__delimiter"></div>
			<div class="bx-im-sidebar-elements-tariff-limit__content">
				<div class="bx-im-sidebar-elements-tariff-limit__description" v-html="preparedDescription"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const TariffLimitPreview = {
	  name: 'TariffLimitPreview',
	  components: {
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock
	  },
	  template: `
		<TariffLimit :dialogId="dialogId" :panel="SidebarDetailBlock.main" />
	`
	};

	const INTRANET_MANUAL_CODE = 'collab';
	const COLLABER_MANUAL_CODE = 'collab_guest';

	// @vue/component
	const CollabHelpdeskPreview = {
	  name: 'CollabHelpdeskPreview',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      needToShow: im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.collabHelpdeskSidebar)
	    };
	  },
	  computed: {
	    isCurrentUserCollaber() {
	      const currentUser = this.$store.getters['users/get'](im_v2_application_core.Core.getUserId(), true);
	      return currentUser.type === im_v2_const.UserType.collaber;
	    }
	  },
	  methods: {
	    close() {
	      this.needToShow = false;
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.collabHelpdeskSidebar);
	    },
	    openHelpdesk() {
	      const manualCode = this.isCurrentUserCollaber ? COLLABER_MANUAL_CODE : INTRANET_MANUAL_CODE;
	      const urlParams = {
	        utm_source: 'portal',
	        utm_content: 'widget'
	      };
	      ui_manual.Manual.show(manualCode, urlParams);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div v-if="needToShow" class="bx-im-sidebar-collab-helpdesk__container" @click="openHelpdesk">
			<div class="bx-im-sidebar-collab-helpdesk__icon"></div>
			<div class="bx-im-sidebar-collab-helpdesk__content">
				<div class="bx-im-sidebar-collab-helpdesk__title">
					{{ loc('IM_SIDEBAR_COLLAB_HELPDESK_TITLE') }}
				</div>
				<div class="bx-im-sidebar-collab-helpdesk__description --line-clamp-3">
					{{ loc('IM_SIDEBAR_COLLAB_HELPDESK_DESCRIPTION') }}
				</div>
			</div>
			<div class="bx-im-sidebar-collab-helpdesk__close" @click.stop="close"></div>
		</div>
	`
	};

	// @vue/component
	const SidebarSkeleton = {
	  name: 'SidebarSkeleton',
	  template: `
		<div class="bx-im-sidebar-skeleton__container">
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__avatar"></div>
				<div class="bx-im-sidebar-skeleton__invite-button"></div>
				<div class="bx-im-sidebar-skeleton__settings"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__info"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__files"></div>
			</div>
			<div class="bx-im-sidebar-skeleton__block">
				<div class="bx-im-sidebar-skeleton__tasks"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MainPanel = {
	  name: 'MainPanel',
	  components: {
	    MainHeader,
	    ChatPreview,
	    PostPreview,
	    UserPreview,
	    SupportPreview,
	    InfoPreview,
	    FilePreview,
	    TaskPreview,
	    MeetingPreview,
	    FileUnsortedPreview: FilePreview,
	    MarketPreview,
	    MultidialogPreview,
	    SidebarSkeleton,
	    CopilotPreview,
	    CopilotInfoPreview,
	    TariffLimitPreview,
	    CollabHelpdeskPreview
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: true
	    };
	  },
	  computed: {
	    blocks() {
	      return getAvailableBlocks(this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    hasInitialData() {
	      return this.$store.getters['sidebar/isInited'](this.chatId);
	    }
	  },
	  watch: {
	    dialogId() {
	      this.initializeSidebar();
	    },
	    dialogInited() {
	      this.initializeSidebar();
	    }
	  },
	  created() {
	    this.initializeSidebar();
	  },
	  methods: {
	    getPreviewComponentName(block) {
	      return `${block}Preview`;
	    },
	    initializeSidebar() {
	      if (!this.dialogInited) {
	        return;
	      }
	      if (this.hasInitialData) {
	        this.isLoading = false;
	        return;
	      }
	      this.sidebarService = new Main({
	        dialogId: this.dialogId
	      });
	      this.isLoading = true;
	      this.sidebarService.requestInitialData().then(() => {
	        this.isLoading = false;
	      }).catch(error => {
	        im_v2_lib_logger.Logger.warn('Sidebar: request initial data error:', error);
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-panel__container">
			<MainHeader :dialogId="dialogId" />
			<SidebarSkeleton v-if="isLoading || !dialogInited" />
			<div v-else class="bx-im-sidebar-main-panel__blocks">
				<component
					v-for="block in blocks"
					:key="block"
					class="bx-im-sidebar-main-panel__block"
					:is="getPreviewComponentName(block)"
					:dialogId="dialogId"
				/>
			</div>
		</div>
	`
	};

	function concatAndSortSearchResult(concatArrayFirst, concatArraySecond) {
	  return [...concatArrayFirst, ...concatArraySecond].sort((a, z) => z - a);
	}

	const REQUEST_ITEMS_LIMIT$8 = 50;
	var _query = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _processSearchResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	class TaskSearch {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _processSearchResponse, {
	      value: _processSearchResponse2
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _query, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query)[_query] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query)[_query] = query;
	      this.hasMoreItemsToLoad = true;
	    }
	    return this.request();
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _query)[_query] = '';
	    this.hasMoreItemsToLoad = true;
	    void this.store.dispatch('sidebar/tasks/clearSearch', {});
	  }
	  async request() {
	    const queryParams = this.getQueryParams();
	    let responseData = {};
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imChatTaskGet, queryParams);
	      responseData = response.data();
	    } catch (error) {
	      console.error('SidebarSearch: Im.imChatTaskGet: page request error', error);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse)[_processSearchResponse](responseData);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$8,
	      SEARCH_TASK_NAME: babelHelpers.classPrivateFieldLooseBase(this, _query)[_query]
	    };
	    const lastId = this.store.getters['sidebar/tasks/getSearchResultCollectionLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$8;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setTasksPromise = this.store.dispatch('sidebar/tasks/setSearch', {
	      chatId: this.chatId,
	      tasks: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setTasksPromise, addUsersPromise]);
	  }
	}
	function _processSearchResponse2(response) {
	  return this.updateModels(response).then(() => {
	    return response.list.map(message => message.messageId);
	  });
	}

	// @vue/component
	const DateGroup = {
	  name: 'DateGroup',
	  props: {
	    dateText: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-date-group__container bx-im-sidebar-date-group__scope">
			<div class="bx-im-sidebar-date-group__text">
				{{ dateText }}
			</div>
		</div>
	`
	};

	// @vue/component
	const DetailHeader = {
	  name: 'DetailHeader',
	  components: {
	    ChatButton: im_v2_component_elements.Button,
	    SearchInput: im_v2_component_elements.SearchInput
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    },
	    withAddButton: {
	      type: Boolean,
	      default: false
	    },
	    withSearch: {
	      type: Boolean,
	      default: false
	    },
	    isSearchHeaderOpened: {
	      type: Boolean,
	      default: false
	    },
	    delayForFocusOnStart: {
	      type: Number || null,
	      default: null
	    }
	  },
	  emits: ['back', 'addClick', 'changeQuery', 'toggleSearchPanelOpened'],
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isCollab() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    addButtonColor() {
	      if (this.isCopilotLayout) {
	        return this.ButtonColor.Copilot;
	      }
	      if (this.isCollab) {
	        return this.ButtonColor.Collab;
	      }
	      return this.ButtonColor.PrimaryLight;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				/>
				<div v-if="!isSearchHeaderOpened" class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<slot name="action">
					<div v-if="withAddButton && !isSearchHeaderOpened" class="bx-im-sidebar-detail-header__add-button" ref="add-button">
						<ChatButton
							:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="addButtonColor"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="$emit('addClick', {target: $refs['add-button']})"
						/>
					</div>
				</slot>
				<div v-if="withSearch" class="bx-im-sidebar-detail-header__search">
					<SearchInput
						v-if="isSearchHeaderOpened"
						:placeholder="loc('IM_SIDEBAR_SEARCH_MESSAGE_PLACEHOLDER')"
						:withIcon="false"
						:delayForFocusOnStart="delayForFocusOnStart"
						@queryChange="$emit('changeQuery', $event)"
						@close="$emit('toggleSearchPanelOpened', $event)"
						class="bx-im-sidebar-search-header__input"
					/>
					<div v-else @click="$emit('toggleSearchPanelOpened', $event)" class="bx-im-sidebar-detail-header__search__icon --search"></div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const DetailEmptySearchState = {
	  name: 'DetailEmptySearchState',
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    subTitle: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  template: `
		<div class="bx-im-detail-empty-search-state__container">
			<div class="bx-im-detail-empty-search-state__icon"></div>
			<div class="bx-im-detail-empty-search-state__title">
				{{ title }}
			</div>
			<div class="bx-im-detail-empty-search-state__subtitle">
				{{ subTitle }}
			</div>
		</div>
	`
	};

	class SidebarCollectionFormatter {
	  constructor() {
	    this.cachedDateGroups = {};
	  }
	  format(collection) {
	    const dateGroups = {};
	    collection.forEach(item => {
	      const dateGroup = this.getDateGroup(item.date);
	      if (!dateGroups[dateGroup.title]) {
	        dateGroups[dateGroup.title] = {
	          dateGroupTitle: dateGroup.title,
	          items: []
	        };
	      }
	      dateGroups[dateGroup.title].items.push(item);
	    });
	    return Object.values(dateGroups);
	  }
	  getDateGroup(date) {
	    const INDEX_BETWEEN_DATE_AND_TIME = 10;
	    // 2022-10-25T14:58:44.000Z => 2022-10-25
	    const shortDate = date.toJSON().slice(0, INDEX_BETWEEN_DATE_AND_TIME);
	    if (this.cachedDateGroups[shortDate]) {
	      return this.cachedDateGroups[shortDate];
	    }
	    this.cachedDateGroups[shortDate] = {
	      id: shortDate,
	      title: im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.dateGroup)
	    };
	    return this.cachedDateGroups[shortDate];
	  }
	  destroy() {
	    this.cachedDateGroups = {};
	  }
	}

	const DEFAULT_MIN_TOKEN_SIZE = 3;

	// @vue/component
	const TaskPanel = {
	  name: 'TaskPanel',
	  components: {
	    TaskItem,
	    DateGroup,
	    DetailHeader,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      isSearchHeaderOpened: false,
	      searchQuery: '',
	      searchResult: [],
	      currentServerQueries: 0,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    tasks() {
	      if (this.isSearchHeaderOpened) {
	        return this.$store.getters['sidebar/tasks/getSearchResultCollection'](this.chatId);
	      }
	      return this.$store.getters['sidebar/tasks/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.tasks);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    showAddButton() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createTask, this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isSearchQueryMinimumSize() {
	      return this.preparedQuery.length < this.minTokenSize;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/tasks/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.cleanSearchResult();
	      this.startSearch();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new TaskMenu();
	    this.service = new Task({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new TaskSearch({
	      dialogId: this.dialogId
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE);
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.serviceSearch.searchOnServer(query).then(messageIds => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	          return;
	        }
	        this.searchResult = concatAndSortSearchResult(this.searchResult, messageIds);
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	        if (this.isSearchQueryMinimumSize) {
	          this.cleanSearchResult();
	        }
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    startSearch() {
	      if (this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	      } else {
	        this.isLoading = true;
	        this.searchOnServerDelayed(this.preparedQuery);
	      }
	    },
	    cleanSearchResult() {
	      this.serviceSearch.resetSearchState();
	      this.searchResult = [];
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    toggleSearchPanelOpened() {
	      this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.task
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/tasks/hasNextPageSearch' : 'sidebar/tasks/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage();
	      } else {
	        await this.serviceSearch.request();
	      }
	      this.isLoading = false;
	    },
	    onAddClick() {
	      new im_v2_lib_entityCreator.EntityCreator(this.chatId).createTaskForChat();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-task-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_TASK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="showAddButton"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				withSearch
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-task-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-task-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<TaskItem
						v-for="task in dateGroup.items"
						:task="task"
						:searchQuery="searchQuery"
						:contextDialogId="dialogId"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.task"
					class="bx-im-sidebar-task-detail__tariff-limit-container"
				/>
				<template v-if="!isLoading">
					<template v-if="isSearchHeaderOpened">
						<StartState
							v-if="preparedQuery.length === 0"
							:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
							:iconType="SidebarDetailBlock.messageSearch"
						/>
						<DetailEmptySearchState
							v-else-if="isEmptyState"
							:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
							:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
						/>
					</template>
					<DetailEmptyState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_TASKS_EMPTY')"
						:iconType="SidebarDetailBlock.task"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	const ARROW_CONTROL_SIZE = 50;

	// @vue/component
	const DetailTabs = {
	  name: 'DetailTabs',
	  props: {
	    tabs: {
	      type: Array,
	      default: () => []
	    }
	  },
	  emits: ['tabSelect'],
	  data() {
	    return {
	      hasLeftControl: false,
	      hasRightControl: false,
	      currentElementIndex: 0,
	      highlightOffsetLeft: 0,
	      highlightWidth: 0
	    };
	  },
	  computed: {
	    highlightStyle() {
	      return {
	        left: `${this.highlightOffsetLeft}px`,
	        width: `${this.highlightWidth}px`
	      };
	    }
	  },
	  watch: {
	    currentElementIndex(newIndex) {
	      this.updateHighlightPosition(newIndex);
	      this.$emit('tabSelect', this.tabs[newIndex]);
	      this.scrollToElement(newIndex);
	    }
	  },
	  mounted() {
	    if (this.$refs.tabs.scrollWidth > this.$refs.tabs.offsetWidth) {
	      this.hasRightControl = true;
	    }
	    this.updateHighlightPosition(this.currentElementIndex);
	  },
	  methods: {
	    getElementNodeByIndex(index) {
	      return [...this.$refs.tabs.children].filter(node => {
	        return !main_core.Dom.hasClass(node, 'bx-sidebar-tabs-highlight');
	      })[index];
	    },
	    updateHighlightPosition(index) {
	      const element = this.getElementNodeByIndex(index);
	      this.highlightOffsetLeft = element.offsetLeft;
	      this.highlightWidth = element.offsetWidth;
	    },
	    scrollToElement(elementIndex) {
	      const element = this.getElementNodeByIndex(elementIndex);
	      this.$refs.tabs.scroll({
	        left: element.offsetLeft - ARROW_CONTROL_SIZE,
	        behavior: 'smooth'
	      });
	    },
	    onTabClick(event) {
	      this.currentElementIndex = event.index;
	    },
	    getTabTitle(tab) {
	      const langPhraseCode = `IM_SIDEBAR_FILES_${tab.toUpperCase()}_TAB`;
	      return this.$Bitrix.Loc.getMessage(langPhraseCode);
	    },
	    isSelectedTab(index) {
	      return index === this.currentElementIndex;
	    },
	    onLeftClick() {
	      if (this.currentElementIndex <= 0) {
	        return;
	      }
	      this.currentElementIndex--;
	    },
	    onRightClick() {
	      if (this.currentElementIndex >= this.tabs.length - 1) {
	        return;
	      }
	      this.currentElementIndex++;
	    },
	    updateControlsVisibility() {
	      this.hasRightControl = this.$refs.tabs.scrollWidth > this.$refs.tabs.scrollLeft + this.$refs.tabs.clientWidth;
	      this.hasLeftControl = this.$refs.tabs.scrollLeft > 0;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-detail-tabs__container bx-im-sidebar-detail-tabs__scope">
			<div v-if="hasLeftControl" @click.stop="onLeftClick" class="bx-im-sidebar-ears__control --left">
				<div class="bx-im-sidebar__forward-icon"></div>
			</div>
			<div v-if="hasRightControl" @click.stop="onRightClick" class="bx-im-sidebar-ears__control --right">
				<div class="bx-im-sidebar__forward-icon"></div>
			</div>
			<div class="bx-im-sidebar-ears__elements" ref="tabs" @scroll.passive="updateControlsVisibility">
				<div class="bx-sidebar-tabs-highlight" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab"
					class="bx-im-sidebar-detail-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
				>
					<div class="bx-im-sidebar-detail-tabs__item-title">{{ getTabTitle(tab) }}</div>
				</div>
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$9 = 50;
	var _query$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _processSearchResponse$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	class FileSearch {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _processSearchResponse$1, {
	      value: _processSearchResponse2$1
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _query$1, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query, subType) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1] = query;
	      this.hasMoreItemsToLoad = true;
	    }
	    return this.request(subType);
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1] = '';
	    this.hasMoreItemsToLoad = true;
	    void this.store.dispatch('sidebar/files/clearSearch', {});
	  }
	  async request(subType) {
	    const queryParams = this.getQueryParams(subType);
	    let responseData = {};
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imChatFileGet, queryParams);
	      responseData = response.data();
	    } catch (error) {
	      console.error('SidebarSearch: Im.imChatFileGet: page request error', error);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse$1)[_processSearchResponse$1](responseData);
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      files,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const historyLimitPromise = this.store.dispatch('sidebar/files/setHistoryLimitExceeded', {
	      chatId: this.chatId,
	      isHistoryLimitExceeded
	    });
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const sortedList = {};
	    list.forEach(file => {
	      if (!sortedList[file.subType]) {
	        sortedList[file.subType] = [];
	      }
	      sortedList[file.subType].push(file);
	    });
	    const setSidebarFilesPromises = [];
	    Object.keys(sortedList).forEach(subType => {
	      const listByType = sortedList[subType];
	      setSidebarFilesPromises.push(this.store.dispatch('sidebar/files/setSearch', {
	        chatId: this.chatId,
	        files: listByType,
	        subType
	      }), this.store.dispatch('sidebar/files/setHasNextPageSearch', {
	        chatId: this.chatId,
	        subType,
	        hasNextPage: listByType.length === REQUEST_ITEMS_LIMIT$9
	      }), this.store.dispatch('sidebar/files/setLastIdSearch', {
	        chatId: this.chatId,
	        subType,
	        lastId: getLastElementId(listByType)
	      }));
	    });
	    return Promise.all([setFilesPromise, addUsersPromise, historyLimitPromise, ...setSidebarFilesPromises]);
	  }
	  loadNextPage(subType, searchQuery) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1] !== searchQuery) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1] = searchQuery;
	    }
	    return this.request(subType);
	  }
	  getQueryParams(subType) {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      SEARCH_FILE_NAME: babelHelpers.classPrivateFieldLooseBase(this, _query$1)[_query$1],
	      SUBTYPE: subType.toUpperCase(),
	      LIMIT: REQUEST_ITEMS_LIMIT$9
	    };
	    const lastId = this.store.getters['sidebar/files/getSearchResultCollectionLastId'](this.chatId, subType);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	}
	function _processSearchResponse2$1(response) {
	  return this.updateModels(response).then(() => {
	    return response.files.map(file => file.id);
	  });
	}

	// @vue/component
	const MediaDetailItem = {
	  name: 'MediaDetailItem',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar
	  },
	  props: {
	    fileItem: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false,
	      videoDuration: 0
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    sidebarFileItem() {
	      return this.fileItem;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
	    },
	    previewPicture() {
	      if (!this.hasPreview) {
	        return {};
	      }
	      return {
	        backgroundImage: `url('${this.file.urlPreview}')`
	      };
	    },
	    hasPreview() {
	      return this.file.urlPreview !== '';
	    },
	    isImage() {
	      return this.file.type === 'image';
	    },
	    isVideo() {
	      return this.file.type === 'video';
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    videoDurationText() {
	      if (this.videoDuration === 0) {
	        return '--:--';
	      }
	      return this.formatTime(this.videoDuration);
	    }
	  },
	  methods: {
	    formatTime(rawSeconds) {
	      rawSeconds = Math.floor(rawSeconds);
	      const durationHours = Math.floor(rawSeconds / 60 / 60);
	      if (durationHours > 0) {
	        rawSeconds -= durationHours * 60 * 60;
	      }
	      const durationMinutes = Math.floor(rawSeconds / 60);
	      if (durationMinutes > 0) {
	        rawSeconds -= durationMinutes * 60;
	      }
	      const hours = durationHours > 0 ? `${durationHours}:` : '';
	      const minutes = hours > 0 ? `${durationMinutes.toString().padStart(2, '0')}:` : `${durationMinutes}:`;
	      const seconds = rawSeconds.toString().padStart(2, '0');
	      return hours + minutes + seconds;
	    },
	    handleVideoEvent() {
	      if (!this.$refs.video) {
	        return;
	      }
	      this.videoDuration = this.$refs.video.duration;
	    },
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        sidebarFile: this.sidebarFileItem,
	        file: this.file,
	        messageId: this.sidebarFileItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-file-media-detail-item__container bx-im-sidebar-file-media-detail-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-file-media-detail-item__header-container">
				<div class="bx-im-sidebar-file-media-detail-item__avatar-container">
					<MessageAvatar 
						:messageId="sidebarFileItem.messageId" 
						:authorId="sidebarFileItem.authorId"
						:size="AvatarSize.S" 
					/>
				</div>
				<button
					v-if="showContextButton"
					class="bx-im-sidebar-file-media-detail-item__context-menu bx-im-messenger__context-menu-icon"
					@click="onContextMenuClick"
				></button>
			</div>
			<div 
				v-if="isImage"
				class="bx-im-sidebar-file-media-detail-item__content --image" 
				:style="previewPicture"
				v-bind="viewerAttributes"
				:title="file.name"
			>
			</div>
			<div
				v-if="isVideo"
				class="bx-im-sidebar-file-media-detail-item__content --video"
				:style="previewPicture"
				v-bind="viewerAttributes"
				:title="file.name"
			>
				<video 
					v-show="!hasPreview"
					ref="video"
					class="bx-im-sidebar-file-media-detail-item__video" 
					preload="metadata" :src="file.urlDownload"
					@durationchange="handleVideoEvent"
					@loadeddata="handleVideoEvent"
					@loadedmetadata="handleVideoEvent"
				></video>
			</div>
			<div v-if="isVideo" class="bx-im-sidebar-file-media-detail-item__video-controls">
				<span class="bx-im-sidebar-file-media-detail-item__video-controls-icon"></span>
				<span class="bx-im-sidebar-file-media-detail-item__video-controls-time">{{ videoDurationText }}</span>
			</div>
		</div>
	`
	};

	class FileManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.diskService = new im_v2_provider_service.DiskService();
	  }
	  delete(sidebarFile) {
	    this.store.dispatch('sidebar/files/delete', {
	      dialogId: sidebarFile.chatId,
	      id: sidebarFile.id
	    });
	    this.diskService.delete({
	      chatId: sidebarFile.chatId,
	      fileId: sidebarFile.fileId
	    });
	  }
	  saveOnDisk(fileIds) {
	    return this.diskService.save(fileIds);
	  }
	}

	class FileMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	    this.mediaManager = new FileManager();
	  }
	  getMenuItems() {
	    return [this.getOpenContextMessageItem(), this.getDownloadFileItem(), this.getSaveFileOnDiskItem(), this.getDeleteFileItem()];
	  }
	  getViewFileItem() {
	    const viewerAttributes = im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.context.file.viewerAttrs);
	    if (!viewerAttributes || this.context.file.type === 'audio') {
	      return null;
	    }
	    return {
	      html: this.getViewHtml(viewerAttributes),
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getDownloadFileItem() {
	    if (!this.context.file.urlDownload) {
	      return null;
	    }
	    return {
	      html: this.getDownloadHtml(this.context.file.urlDownload, this.context.file.name),
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getSaveFileOnDiskItem() {
	    if (!this.context.sidebarFile.fileId) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_SAVE_FILE_ON_DISK_MSGVER_1'),
	      onclick: function () {
	        void this.mediaManager.saveOnDisk([this.context.sidebarFile.fileId]).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_SERVICE_FILE_SAVED_ON_DISK_SUCCESS_MSGVER_1')
	          });
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getDeleteFileItem() {
	    if (this.getCurrentUserId() !== this.context.sidebarFile.authorId) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FILE'),
	      onclick: function () {
	        this.mediaManager.delete(this.context.sidebarFile);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getViewHtml(viewerAttributes) {
	    const div = main_core.Dom.create('div', {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_VIEW_FILE')
	    });
	    Object.entries(viewerAttributes).forEach(attribute => {
	      const [attributeName, attributeValue] = attribute;
	      div.setAttribute(attributeName, attributeValue);
	    });
	    return div;
	  }
	  getDownloadHtml(urlDownload, fileName) {
	    const a = main_core.Dom.create('a', {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DOWNLOAD_FILE')
	    });
	    main_core.Dom.style(a, 'display', 'block');
	    main_core.Dom.style(a, 'color', 'inherit');
	    main_core.Dom.style(a, 'text-decoration', 'inherit');
	    a.setAttribute('href', urlDownload);
	    a.setAttribute('download', fileName);
	    return a;
	  }
	}

	const DEFAULT_MIN_TOKEN_SIZE$1 = 3;

	// @vue/component
	const MediaTab = {
	  name: 'MediaTab',
	  components: {
	    DateGroup,
	    MediaDetailItem,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchResult: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    isSearch: {
	      type: Boolean,
	      required: false
	    },
	    isLoadingSearch: {
	      type: Boolean,
	      required: false
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$1
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    files() {
	      if (this.isSearch) {
	        return this.$store.getters['sidebar/files/getSearchResultCollection'](this.chatId, im_v2_const.SidebarFileTypes.media);
	      }
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.media);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    isSearchQueryMinimumSize() {
	      return this.searchQuery.length < this.minTokenSize;
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$1);
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/files/hasNextPageSearch' : 'sidebar/files/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId, im_v2_const.SidebarFileTypes.media);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage(im_v2_const.SidebarFileTypes.media);
	      } else {
	        await this.serviceSearch.loadNextPage(im_v2_const.SidebarFileTypes.media, this.searchQuery);
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-media-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-media-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<div class="bx-im-sidebar-file-media-detail__items-group">
					<MediaDetailItem
						v-for="file in dateGroup.items"
						:fileItem="file"
						:contextDialogId="dialogId"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
			</div>
			<template v-if="!isLoading && !isLoadingSearch">
				<template v-if="isSearch">
					<StartState
						v-if="searchQuery.length === 0"
						:title="loc('IM_SIDEBAR_SEARCH_RESULT_START_TITLE')"
						:iconType="SidebarDetailBlock.messageSearch"
					/>
					<DetailEmptySearchState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
						:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
					/>
				</template>
				<DetailEmptyState
					v-else-if="isEmptyState"
					:title="loc('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.media"
				/>
			</template>
			<Loader v-if="isLoading || isLoadingSearch" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const AudioDetailItem = {
	  name: 'AudioDetailItem',
	  components: {
	    AudioPlayer: im_v2_component_elements.AudioPlayer
	  },
	  props: {
	    id: {
	      type: Number,
	      required: true
	    },
	    fileItem: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      timelineType: 0
	    };
	  },
	  computed: {
	    sidebarFileItem() {
	      return this.fileItem;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
	    },
	    audioUrl() {
	      return this.file.urlDownload;
	    }
	  },
	  created() {
	    this.timelineType = Math.floor(Math.random() * 5);
	  },
	  methods: {
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        sidebarFile: this.sidebarFileItem,
	        file: this.file,
	        messageId: this.sidebarFileItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-audio-detail-item__container bx-im-sidebar-file-audio-detail-item__scope">
			<AudioPlayer 
				:id="id"
				:src="audioUrl" 
				:file="file" 
				:messageId="sidebarFileItem.messageId"
				:timelineType="timelineType" 
				:authorId="sidebarFileItem.authorId"
				:withPlaybackRateControl="true"
				@contextMenuClick="onContextMenuClick"
			/>
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$2 = 3;

	// @vue/component
	const AudioTab = {
	  name: 'AudioTab',
	  components: {
	    DetailEmptyState,
	    AudioDetailItem,
	    DateGroup,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchResult: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    isSearch: {
	      type: Boolean,
	      required: false
	    },
	    isLoadingSearch: {
	      type: Boolean,
	      required: false
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$2
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      if (this.isSearch) {
	        return this.$store.getters['sidebar/files/getSearchResultCollection'](this.chatId, im_v2_const.SidebarFileTypes.audio);
	      }
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.audio);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isSearchQueryMinimumSize() {
	      return this.searchQuery.length < this.minTokenSize;
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$2);
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/files/hasNextPageSearch' : 'sidebar/files/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId, im_v2_const.SidebarFileTypes.audio);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage(im_v2_const.SidebarFileTypes.audio);
	      } else {
	        await this.serviceSearch.loadNextPage(im_v2_const.SidebarFileTypes.audio, this.searchQuery);
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-audio-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-audio-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<AudioDetailItem
					v-for="file in dateGroup.items"
					:id="file.id"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<template v-if="!isLoading && !isLoadingSearch">
				<template v-if="isSearch">
					<StartState
						v-if="searchQuery.length === 0"
						:title="loc('IM_SIDEBAR_SEARCH_RESULT_START_TITLE')"
						:iconType="SidebarDetailBlock.messageSearch"
					/>
					<DetailEmptySearchState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
						:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
					/>
				</template>
				<DetailEmptyState
					v-else-if="isEmptyState"
					:title="loc('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.audio"
				/>
			</template>
			<Loader v-if="isLoading || isLoadingSearch" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const BriefItem = {
	  name: 'BriefItem',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    brief: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: '',
	      required: false
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    sidebarFileItem() {
	      return this.brief;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 15;
	      const shortName = im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
	      if (this.searchQuery.length === 0) {
	        return main_core.Text.encode(shortName);
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(shortName), this.searchQuery);
	    },
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    isViewerAvailable() {
	      return Object.keys(this.viewerAttributes).length > 0;
	    }
	  },
	  methods: {
	    download() {
	      if (this.isViewerAvailable) {
	        return;
	      }
	      const urlToOpen = this.file.urlShow ? this.file.urlShow : this.file.urlDownload;
	      window.open(urlToOpen, '_blank');
	    },
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        sidebarFile: this.sidebarFileItem,
	        file: this.file,
	        messageId: this.sidebarFileItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-brief-item__container bx-im-sidebar-brief-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-brief-item__icon-container"></div>
			<div class="bx-im-sidebar-brief-item__content-container">
				<div class="bx-im-sidebar-brief-item__content">
					<div class="bx-im-sidebar-brief-item__title" @click="download" v-bind="viewerAttributes">
						<span class="bx-im-sidebar-brief-item__title-text" :title="file.name" v-html="fileShortName"></span>
						<span class="bx-im-sidebar-brief-item__size-text">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-brief-item__author-container">
						<MessageAvatar 
							:messageId="sidebarFileItem.messageId"
							:authorId="sidebarFileItem.authorId"
							:size="AvatarSize.XS"
							class="bx-im-sidebar-brief-item__author-avatar" 
						/>
						<ChatTitle :dialogId="sidebarFileItem.authorId" :showItsYou="false" />
					</div>
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon bx-im-sidebar-brief-item__context-menu-button"
				@click="onContextMenuClick"
			></button>
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$3 = 3;

	// @vue/component
	const BriefTab = {
	  name: 'BriefTab',
	  components: {
	    DateGroup,
	    BriefItem,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchResult: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    isSearch: {
	      type: Boolean,
	      required: false
	    },
	    isLoadingSearch: {
	      type: Boolean,
	      required: false
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$3
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      if (this.isSearch) {
	        return this.$store.getters['sidebar/files/getSearchResultCollection'](this.chatId, im_v2_const.SidebarFileTypes.brief);
	      }
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.brief);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isSearchQueryMinimumSize() {
	      return this.searchQuery.length < this.minTokenSize;
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$3);
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/files/hasNextPageSearch' : 'sidebar/files/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId, im_v2_const.SidebarFileTypes.brief);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage(im_v2_const.SidebarFileTypes.brief);
	      } else {
	        await this.serviceSearch.loadNextPage(im_v2_const.SidebarFileTypes.brief, this.searchQuery);
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-brief-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-brief-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle"/>
				<BriefItem
					v-for="file in dateGroup.items"
					:brief="file"
					:contextDialogId="dialogId"
					:searchQuery="searchQuery"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<template v-if="!isLoading && !isLoadingSearch">
				<template v-if="isSearch">
					<StartState
						v-if="searchQuery.length === 0"
						:title="loc('IM_SIDEBAR_SEARCH_RESULT_START_TITLE')"
						:iconType="SidebarDetailBlock.messageSearch"
					/>
					<DetailEmptySearchState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
						:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
					/>
				</template>
				<DetailEmptyState
					v-else-if="isEmptyState"
					:title="loc('IM_SIDEBAR_BRIEFS_EMPTY')"
					:iconType="SidebarDetailBlock.other"
				/>
			</template>
			<Loader v-if="isLoading || isLoadingSearch" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const DocumentDetailItem = {
	  name: 'DocumentDetailItem',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    fileItem: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: '',
	      required: false
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    sidebarFileItem() {
	      return this.fileItem;
	    },
	    file() {
	      return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
	    },
	    fileIconClass() {
	      return `ui-icon ui-icon-file-${this.file.icon}`;
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 15;
	      const shortName = im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
	      if (this.searchQuery.length === 0) {
	        return main_core.Text.encode(shortName);
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(shortName), this.searchQuery);
	    },
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    },
	    isViewerAvailable() {
	      return Object.keys(this.viewerAttributes).length > 0;
	    },
	    authorId() {
	      return this.sidebarFileItem.authorId;
	    }
	  },
	  methods: {
	    download() {
	      if (this.isViewerAvailable) {
	        return;
	      }
	      const urlToOpen = this.file.urlShow ? this.file.urlShow : this.file.urlDownload;
	      window.open(urlToOpen, '_blank');
	    },
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        sidebarFile: this.sidebarFileItem,
	        file: this.file,
	        messageId: this.sidebarFileItem.messageId
	      }, event.currentTarget);
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-file-document-detail-item__container bx-im-sidebar-file-document-detail-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"		
		>
			<div class="bx-im-sidebar-file-document-detail-item__icon-container">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-sidebar-file-document-detail-item__content-container" v-bind="viewerAttributes">
				<div class="bx-im-sidebar-file-document-detail-item__content">
					<div class="bx-im-sidebar-file-document-detail-item__document-title" @click="download" :title="file.name">
						<span class="bx-im-sidebar-file-document-detail-item__document-title-text" v-html="fileShortName"></span>
						<span class="bx-im-sidebar-file-document-detail-item__document-size">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-file-document-detail-item__author-container">
						<template v-if="authorId > 0">
							<MessageAvatar
								:messageId="sidebarFileItem.messageId"
								:authorId="sidebarFileItem.authorId"
								:size="AvatarSize.XS"
								class="bx-im-sidebar-file-document-detail-item__author-avatar"
							/>
							<ChatTitle :dialogId="authorId" :showItsYou="false" />
						</template>
						<span v-else class="bx-im-sidebar-file-document-detail-item__system-author-text">
							{{$Bitrix.Loc.getMessage('IM_SIDEBAR_SYSTEM_USER')}}
						</span>
					</div>
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$4 = 3;

	// @vue/component
	const OtherTab = {
	  name: 'OtherTab',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchResult: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    isSearch: {
	      type: Boolean,
	      required: false
	    },
	    isLoadingSearch: {
	      type: Boolean,
	      required: false
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$4
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      if (this.isSearch) {
	        return this.$store.getters['sidebar/files/getSearchResultCollection'](this.chatId, im_v2_const.SidebarFileTypes.other);
	      }
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.other);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isSearchQueryMinimumSize() {
	      return this.searchQuery.length < this.minTokenSize;
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$4);
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/files/hasNextPageSearch' : 'sidebar/files/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId, im_v2_const.SidebarFileTypes.other);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage(im_v2_const.SidebarFileTypes.other);
	      } else {
	        await this.serviceSearch.loadNextPage(im_v2_const.SidebarFileTypes.other, this.searchQuery);
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-other-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-other-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					:contextDialogId="dialogId"
					:searchQuery="searchQuery"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<template v-if="!isLoading && !isLoadingSearch">
				<template v-if="isSearch">
					<StartState
						v-if="searchQuery.length === 0"
						:title="loc('IM_SIDEBAR_SEARCH_RESULT_START_TITLE')"
						:iconType="SidebarDetailBlock.messageSearch"
					/>
					<DetailEmptySearchState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
						:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
					/>
				</template>
				<DetailEmptyState
					v-else-if="isEmptyState"
					:title="loc('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.other"
				/>
			</template>
			<Loader v-if="isLoading || isLoadingSearch" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$5 = 3;

	// @vue/component
	const DocumentTab = {
	  name: 'DocumentTab',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchResult: {
	      type: Array,
	      required: false,
	      default: () => []
	    },
	    isSearch: {
	      type: Boolean,
	      required: false
	    },
	    isLoadingSearch: {
	      type: Boolean,
	      required: false
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$5
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      if (this.isSearch) {
	        return this.$store.getters['sidebar/files/getSearchResultCollection'](this.chatId, im_v2_const.SidebarFileTypes.document);
	      }
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.document);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    isSearchQueryMinimumSize() {
	      return this.searchQuery.length < this.minTokenSize;
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$5);
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/files/hasNextPageSearch' : 'sidebar/files/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId, im_v2_const.SidebarFileTypes.document);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage(im_v2_const.SidebarFileTypes.document);
	      } else {
	        await this.serviceSearch.loadNextPage(im_v2_const.SidebarFileTypes.document, this.searchQuery);
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-document-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-document-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					:searchQuery="searchQuery"
					:contextDialogId="dialogId"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<template v-if="!isLoading && !isLoadingSearch">
				<template v-if="isSearch">
					<StartState
						v-if="searchQuery.length === 0"
						:title="loc('IM_SIDEBAR_SEARCH_RESULT_START_TITLE')"
						:iconType="SidebarDetailBlock.messageSearch"
					/>
					<DetailEmptySearchState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
						:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
					/>
				</template>
				<DetailEmptyState
					v-else-if="isEmptyState"
					:title="loc('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.document"
				/>
			</template>
			<Loader v-if="isLoading || isLoadingSearch" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$6 = 3;

	// @vue/component
	const FilePanel = {
	  name: 'FilePanel',
	  components: {
	    DetailHeader,
	    DetailTabs,
	    MediaTab,
	    AudioTab,
	    DocumentTab,
	    BriefTab,
	    OtherTab,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      tab: im_v2_const.SidebarFileTabTypes.media,
	      isSearchHeaderOpened: false,
	      searchQuery: '',
	      searchResult: [],
	      currentServerQueries: 0,
	      isLoading: false,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$6
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    tabComponentName() {
	      return `${main_core.Text.capitalize(this.tab)}Tab`;
	    },
	    tabs() {
	      const tabTypes = Object.values(im_v2_const.SidebarFileTabTypes);
	      const canShowBriefs = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.sidebarBriefs);
	      if (!canShowBriefs) {
	        return tabTypes.filter(tab => tab !== im_v2_const.SidebarDetailBlock.brief);
	      }
	      return tabTypes;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isSearchQueryMinimumSize() {
	      return this.preparedQuery.length < this.minTokenSize;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/files/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.cleanSearchResult();
	      this.startSearch();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.service = new File({
	      dialogId: this.dialogId,
	      tab: this.tab
	    });
	    this.serviceSearch = new FileSearch({
	      dialogId: this.dialogId,
	      tab: this.tab
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$6);
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.serviceSearch.searchOnServer(query, this.tab).then(messageIds => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	          return;
	        }
	        this.searchResult = concatAndSortSearchResult(this.searchResult, messageIds);
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	        if (this.isSearchQueryMinimumSize) {
	          this.cleanSearchResult();
	        }
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    startSearch() {
	      if (this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	      } else {
	        this.isLoading = true;
	        this.searchOnServerDelayed(this.preparedQuery);
	      }
	    },
	    cleanSearchResult() {
	      this.serviceSearch.resetSearchState();
	      this.searchResult = [];
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.file
	      });
	    },
	    onTabSelect(tabName) {
	      this.tab = tabName;
	      if (!this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	        this.startSearch();
	      }
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    toggleSearchPanelOpened() {
	      this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div>
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_MEDIA_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<TariffLimit
				v-if="hasHistoryLimit"
				:dialogId="dialogId"
				:panel="SidebarDetailBlock.file"
				class="bx-im-sidebar-file__tariff-limit-container" 
			/>
			<DetailTabs :tabs="tabs" @tabSelect="onTabSelect" />
			<KeepAlive>
				<component 
					:is="tabComponentName" 
					:dialogId="dialogId" 
					:searchResult="searchResult" 
					:isSearch="isSearchHeaderOpened" 
					:searchQuery="searchQuery" 
					:isLoadingSearch="isLoading"
				/>
			</KeepAlive>
		</div>
	`
	};

	// @vue/component
	const FileUnsortedPanel = {
	  name: 'FileUnsortedPanel',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    DetailHeader,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    files() {
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.fileUnsorted);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/files/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  created() {
	    this.service = new FileUnsorted({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.fileUnsorted);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage();
	      this.isLoading = false;
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.fileUnsorted
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-unsorted-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILEUNSORTED_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-file-unsorted-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-unsorted-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<DocumentDetailItem
						v-for="file in dateGroup.items"
						:fileItem="file"
						:contextDialogId="dialogId"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.fileUnsorted"
					class="bx-im-sidebar-file-unsorted-detail__tariff-limit-container"
				/>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
					:iconType="SidebarDetailBlock.document"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const LinkItem = {
	  name: 'LinkItem',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    link: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    linkItem() {
	      return this.link;
	    },
	    source() {
	      return this.linkItem.source;
	    },
	    shortDescription() {
	      let hostName = '';
	      try {
	        hostName = new URL(this.source).hostname;
	      } catch (error) {
	        hostName = this.source;
	        console.error(error);
	      }
	      return hostName;
	    },
	    description() {
	      const {
	        name,
	        description
	      } = this.linkItem.richData;
	      const descriptionToShow = description || name || this.source;
	      if (this.searchQuery.length === 0) {
	        return im_v2_lib_utils.Utils.text.convertHtmlEntities(descriptionToShow);
	      }
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(descriptionToShow), this.searchQuery);
	    },
	    authorDialogId() {
	      return this.linkItem.authorId.toString();
	    },
	    hasPreview() {
	      var _this$linkItem$richDa;
	      return Boolean((_this$linkItem$richDa = this.linkItem.richData) == null ? void 0 : _this$linkItem$richDa.previewUrl);
	    },
	    previewStyles() {
	      var _this$linkItem$richDa2;
	      return {
	        backgroundImage: `url('${(_this$linkItem$richDa2 = this.linkItem.richData) == null ? void 0 : _this$linkItem$richDa2.previewUrl}')`,
	        backgroundSize: 'cover',
	        backgroundRepeat: 'no-repeat'
	      };
	    },
	    iconTypeClass() {
	      var _this$linkItem$richDa3;
	      switch ((_this$linkItem$richDa3 = this.linkItem.richData) == null ? void 0 : _this$linkItem$richDa3.type) {
	        case 'TASKS':
	          return '--task';
	        case 'LANDING':
	          return '--landing';
	        case 'POST':
	          return '--post';
	        case 'CALENDAR':
	          return '--calendar';
	        default:
	          return '--common';
	      }
	    }
	  },
	  methods: {
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        id: this.linkItem.id,
	        authorId: this.linkItem.authorId,
	        messageId: this.linkItem.messageId,
	        source: this.source,
	        target: event.currentTarget
	      });
	    }
	  },
	  template: `
		<div 
			class="bx-im-link-item__container bx-im-link-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<template v-if="hasPreview">
				<div class="bx-im-link-item__icon-container" :style="previewStyles"></div>
			</template>
			<template v-else>
				<div class="bx-im-link-item__icon-container" :class="iconTypeClass">
					<div class="bx-im-link-item__icon" :class="iconTypeClass" ></div>
				</div>
			</template>
			<div class="bx-im-link-item__content">
				<div class="bx-im-link-item__short-description-text">{{ shortDescription }}</div>
				<a :href="source" :title="source" target="_blank" class="bx-im-link-item__description-text" v-html="description"></a>
				<div class="bx-im-link-item__author-container">
					<MessageAvatar 
						:messageId="linkItem.messageId" 
						:authorId="linkItem.authorId"
						:size="AvatarSize.XS"
						class="bx-im-link-item__author-avatar" 
					/>
					<ChatTitle :dialogId="authorDialogId" :showItsYou="false" class="bx-im-link-item__author-text" />
				</div>
			</div>
			<div v-if="showContextButton" class="bx-im-link-item__context-menu">
				<button class="bx-im-messenger__context-menu-icon" @click="onContextMenuClick"></button>
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$a = 50;
	var _query$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _processSearchResponse$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	class LinkSearch {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _processSearchResponse$2, {
	      value: _processSearchResponse2$2
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _query$2, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$2)[_query$2] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$2)[_query$2] = query;
	      this.hasMoreItemsToLoad = true;
	    }
	    return this.request();
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _query$2)[_query$2] = '';
	    this.hasMoreItemsToLoad = true;
	    void this.store.dispatch('sidebar/links/clearSearch', {});
	  }
	  async request() {
	    const queryParams = this.getQueryParams();
	    let responseData = {};
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imChatUrlGet, queryParams);
	      responseData = response.data();
	    } catch (error) {
	      console.error('SidebarSearch: Im.imChatUrlGet: page request error', error);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse$2)[_processSearchResponse$2](responseData);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$a,
	      SEARCH_URL: babelHelpers.classPrivateFieldLooseBase(this, _query$2)[_query$2]
	    };
	    const linksCount = this.getLinksCountFromModel();
	    if (main_core.Type.isNumber(linksCount) && linksCount > 0) {
	      queryParams.OFFSET = linksCount;
	    }
	    return queryParams;
	  }
	  getLinksCountFromModel() {
	    return this.store.getters['sidebar/links/getSearchResultCollectionSize'](this.chatId);
	  }
	}
	function _processSearchResponse2$2(response) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](response).then(() => {
	    return response.list.map(message => message.messageId);
	  });
	}
	function _updateModels2(resultData) {
	  const {
	    list,
	    users,
	    tariffRestrictions = {}
	  } = resultData;
	  const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	  const addUsersPromise = this.userManager.setUsersToModel(users);
	  const setLinksPromise = this.store.dispatch('sidebar/links/setSearch', {
	    chatId: this.chatId,
	    links: list,
	    hasNextPage: list.length === REQUEST_ITEMS_LIMIT$a,
	    isHistoryLimitExceeded
	  });
	  return Promise.all([setLinksPromise, addUsersPromise]);
	}

	class LinkManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  delete(link) {
	    this.store.dispatch('sidebar/links/delete', {
	      chatId: link.chatId,
	      id: link.id
	    });
	    const queryParams = {
	      LINK_ID: link.id
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imChatUrlDelete, queryParams).catch(error => {
	      console.error('Im.Sidebar: error deleting link', error);
	    });
	  }
	}

	class LinkMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.linkManager = new LinkManager();
	  }
	  getMenuItems() {
	    return [this.getOpenContextMessageItem(), this.getCopyLinkItem(main_core.Loc.getMessage('IM_SIDEBAR_MENU_COPY_LINK')), this.getDeleteLinkItem()];
	  }
	  getDeleteLinkItem() {
	    if (this.context.authorId !== this.getCurrentUserId()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FROM_LINKS'),
	      onclick: function () {
	        this.linkManager.delete(this.context);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}

	const DEFAULT_MIN_TOKEN_SIZE$7 = 3;

	// @vue/component
	const LinkPanel = {
	  name: 'LinkPanel',
	  components: {
	    DetailHeader,
	    LinkItem,
	    DateGroup,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      isSearchHeaderOpened: false,
	      searchQuery: '',
	      searchResult: [],
	      currentServerQueries: 0,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$7
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    links() {
	      if (this.isSearchHeaderOpened) {
	        return this.$store.getters['sidebar/links/getSearchResultCollection'](this.chatId);
	      }
	      return this.$store.getters['sidebar/links/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.links);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isSearchQueryMinimumSize() {
	      return this.preparedQuery.length < this.minTokenSize;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/links/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.cleanSearchResult();
	      this.startSearch();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new LinkMenu();
	    this.service = new Link({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new LinkSearch({
	      dialogId: this.dialogId
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	    this.collectionFormatter.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$7);
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.serviceSearch.searchOnServer(query).then(messageIds => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	          return;
	        }
	        this.searchResult = concatAndSortSearchResult(this.searchResult, messageIds);
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	        if (this.isSearchQueryMinimumSize) {
	          this.cleanSearchResult();
	        }
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    startSearch() {
	      if (this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	      } else {
	        this.isLoading = true;
	        this.searchOnServerDelayed(this.preparedQuery);
	      }
	    },
	    cleanSearchResult() {
	      this.searchResult = [];
	      this.serviceSearch.resetSearchState();
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    toggleSearchPanelOpened() {
	      this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
	    },
	    onContextMenuClick(event) {
	      const item = {
	        id: event.id,
	        messageId: event.messageId,
	        dialogId: this.dialogId,
	        chatId: this.chatId,
	        source: event.source,
	        authorId: event.authorId
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.link
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/links/hasNextPageSearch' : 'sidebar/links/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage();
	      } else {
	        await this.serviceSearch.request();
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-link-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_LINK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-link-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<template v-for="link in dateGroup.items">
						<LinkItem
							:contextDialogId="dialogId"
							:searchQuery="searchQuery"
							:link="link" 
							@contextMenuClick="onContextMenuClick"
						/>
					</template>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.link"
					class="bx-im-sidebar-link-detail__tariff-limit-container"
				/>
				<template v-if="!isLoading">
					<template v-if="isSearchHeaderOpened">
						<StartState
							v-if="preparedQuery.length === 0"
							:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
							:iconType="SidebarDetailBlock.messageSearch"
						/>
						<DetailEmptySearchState
							v-else-if="isEmptyState"
							:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
							:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
						/>
					</template>
					<DetailEmptyState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_LINKS_EMPTY')"
						:iconType="SidebarDetailBlock.link"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const MarketPanel = {
	  name: 'MarketPanel',
	  components: {
	    Spinner: im_v2_component_elements.Spinner,
	    DetailHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    entityId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: true
	    };
	  },
	  computed: {
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize,
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    placement() {
	      const placementId = Number.parseInt(this.entityId, 10);
	      return this.$store.getters['market/getById'](placementId);
	    },
	    title() {
	      if (this.placement && main_core.Type.isStringFilled(this.placement.title)) {
	        return this.placement.title;
	      }
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MARKET_DETAIL_TITLE');
	    }
	  },
	  created() {
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  },
	  async mounted() {
	    const context = {
	      dialogId: this.dialogId
	    };
	    const response = await this.marketManager.loadPlacement(this.entityId, context);
	    this.isLoading = false;
	    main_core.Runtime.html(this.$refs['im-messenger-sidebar-placement'], response);
	  },
	  methods: {
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.market
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="title"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-market-detail__container">
				<div v-if="isLoading" class="bx-im-sidebar-market-detail__loader-container">
					<Spinner :size="SpinnerSize.S" />
				</div>
				<div 
					class="bx-im-sidebar-market-detail__placement-container" 
					ref="im-messenger-sidebar-placement"
				></div>
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$b = 50;
	var _query$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _processSearchResponse$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	class MeetingSearch {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _processSearchResponse$3, {
	      value: _processSearchResponse2$3
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _query$3, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$3)[_query$3] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$3)[_query$3] = query;
	      this.hasMoreItemsToLoad = true;
	    }
	    return this.request();
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _query$3)[_query$3] = '';
	    this.hasMoreItemsToLoad = true;
	    void this.store.dispatch('sidebar/meetings/clearSearch', {});
	  }
	  async request() {
	    const queryParams = this.getQueryParams();
	    let responseData = {};
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imChatCalendarGet, queryParams);
	      responseData = response.data();
	    } catch (error) {
	      console.error('SidebarSearch: Im.imChatCalendarGet: page request error', error);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse$3)[_processSearchResponse$3](responseData);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$b,
	      SEARCH_TITLE: babelHelpers.classPrivateFieldLooseBase(this, _query$3)[_query$3]
	    };
	    const lastId = this.store.getters['sidebar/meetings/getSearchResultCollectionLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      tariffRestrictions = {}
	    } = resultData;
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$b;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setMeetingsPromise = this.store.dispatch('sidebar/meetings/setSearch', {
	      chatId: this.chatId,
	      meetings: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setMeetingsPromise, addUsersPromise]);
	  }
	}
	function _processSearchResponse2$3(response) {
	  return this.updateModels(response).then(() => {
	    return response.list.map(message => message.messageId);
	  });
	}

	const DEFAULT_MIN_TOKEN_SIZE$8 = 3;

	// @vue/component
	const MeetingPanel = {
	  name: 'MeetingPanel',
	  components: {
	    MeetingItem,
	    DateGroup,
	    DetailEmptyState,
	    StartState: DetailEmptyState,
	    DetailHeader,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      isSearchHeaderOpened: false,
	      searchQuery: '',
	      searchResult: [],
	      currentServerQueries: 0,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$8
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    meetings() {
	      if (this.isSearchHeaderOpened) {
	        return this.$store.getters['sidebar/meetings/getSearchResultCollection'](this.chatId);
	      }
	      return this.$store.getters['sidebar/meetings/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.meetings);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    showAddButton() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.createMeeting, this.dialogId);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isSearchQueryMinimumSize() {
	      return this.preparedQuery.length < this.minTokenSize;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/meetings/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.cleanSearchResult();
	      this.startSearch();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new MeetingMenu();
	    this.service = new Meeting({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new MeetingSearch({
	      dialogId: this.dialogId
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$8);
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.serviceSearch.searchOnServer(query).then(messageIds => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	          return;
	        }
	        this.searchResult = concatAndSortSearchResult(this.searchResult, messageIds);
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	        if (this.isSearchQueryMinimumSize) {
	          this.cleanSearchResult();
	        }
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    startSearch() {
	      if (this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	      } else {
	        this.isLoading = true;
	        this.searchOnServerDelayed(this.preparedQuery);
	      }
	    },
	    cleanSearchResult() {
	      this.serviceSearch.resetSearchState();
	      this.searchResult = [];
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    toggleSearchPanelOpened() {
	      this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
	    },
	    onContextMenuClick(event, target) {
	      const item = {
	        ...event,
	        dialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, target);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.meeting
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/meetings/hasNextPageSearch' : 'sidebar/meetings/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage();
	      } else {
	        await this.serviceSearch.request();
	      }
	      this.isLoading = false;
	    },
	    onAddClick() {
	      new im_v2_lib_entityCreator.EntityCreator(this.chatId).createMeetingForChat();
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-meeting-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_MEETING_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="showAddButton"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				withSearch
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-meeting-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-meeting-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<MeetingItem
						v-for="meeting in dateGroup.items"
						:meeting="meeting"
						:searchQuery="searchQuery"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.meeting"
					class="bx-im-sidebar-meeting-detail__tariff-limit-container"
				/>
				<template v-if="!isLoading">
					<template v-if="isSearchHeaderOpened">
						<StartState
							v-if="preparedQuery.length === 0"
							:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
							:iconType="SidebarDetailBlock.messageSearch"
						/>
						<DetailEmptySearchState
							v-else-if="isEmptyState"
							:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
							:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
						/>
					</template>
					<DetailEmptyState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_MEETINGS_EMPTY')"
						:iconType="SidebarDetailBlock.meeting"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const DetailUser = {
	  name: 'DetailUser',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    isOwner: {
	      type: Boolean,
	      default: false
	    },
	    isManager: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    position() {
	      if (this.isCopilot) {
	        return this.$store.getters['copilot/getProvider'];
	      }
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    },
	    needContextMenu() {
	      const bot = this.$store.getters['users/bots/getByUserId'](this.dialogId);
	      if (!bot) {
	        return true;
	      }
	      return bot.code !== 'copilot';
	    },
	    isCopilot() {
	      const userId = Number.parseInt(this.dialogId, 10);
	      return this.$store.getters['users/bots/isCopilot'](userId);
	    },
	    hasLink() {
	      return !this.isCopilot;
	    }
	  },
	  methods: {
	    onClickContextMenu(event) {
	      this.$emit('contextMenuClick', {
	        userDialogId: this.dialogId,
	        target: event.currentTarget
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-sidebar-main-detail__user"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-main-detail__avatar-container">
				<ChatAvatar 
					:size="AvatarSize.L"
					:avatarDialogId="dialogId"
					:contextDialogId="contextDialogId"
				/>
				<span v-if="isOwner" class="bx-im-sidebar-main-detail__avatar-owner-icon"></span>
				<span v-else-if="isManager" class="bx-im-sidebar-main-detail__avatar-manager-icon"></span>
			</div>
			<div class="bx-im-sidebar-main-detail__user-info-container">
				<div class="bx-im-sidebar-main-detail__user-title-container">
					<a v-if="hasLink" :href="userLink" target="_blank" class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" :withLeftIcon="!isCopilot" />
					</a>
					<div v-else class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" :withLeftIcon="!isCopilot" />
					</div>
					<div
						v-if="needContextMenu && showContextButton"
						class="bx-im-sidebar-main-detail__context-menu-icon bx-im-messenger__context-menu-icon"
						@click="onClickContextMenu"
					></div>
				</div>
				<div class="bx-im-sidebar-main-detail__position-text" :title="position">
					{{ position }}
				</div>
			</div>
		</div>	
	`
	};

	class MembersMenu extends im_v2_lib_menu.UserMenu {
	  constructor() {
	    super();
	    this.chatService = new im_v2_provider_service.ChatService();
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuItems() {
	    if (this.context.user.id === im_v2_application_core.Core.getUserId()) {
	      return [this.getProfileItem(), this.getOpenUserCalendarItem(), this.getLeaveItem()];
	    }
	    return [this.getMentionItem(), this.getSendItem(), this.getManagerItem(), this.getCallItem(), this.getProfileItem(), this.getOpenUserCalendarItem(), this.getKickItem()];
	  }
	  getManagerItem() {
	    const isOwner = this.context.user.id === this.context.dialog.ownerId;
	    const canChangeManagers = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.changeManagers, this.context.dialog.dialogId);
	    const isCollabType = this.context.dialog.type === im_v2_const.ChatType.collab;
	    if (isOwner || !canChangeManagers || isCollabType) {
	      return null;
	    }
	    const isManager = this.context.dialog.managerList.includes(this.context.user.id);
	    return {
	      text: isManager ? main_core.Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_REMOVE') : main_core.Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_ADD'),
	      onclick: () => {
	        if (isManager) {
	          this.chatService.removeManager(this.context.dialog.dialogId, this.context.user.id);
	        } else {
	          this.chatService.addManager(this.context.dialog.dialogId, this.context.user.id);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCallItem() {
	    const userDialogId = this.context.user.id.toString();
	    const chatCanBeCalled = this.callManager.chatCanBeCalled(userDialogId);
	    const chatIsAllowedToCall = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.call, userDialogId);
	    if (!chatCanBeCalled || !chatIsAllowedToCall) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL_2'),
	      onclick: () => {
	        this.callManager.startCall(userDialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenUserCalendarItem() {
	    if (this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getCalendarLink(this.context.user.id);
	    const isCurrentUser = this.context.user.id === im_v2_application_core.Core.getUserId();
	    const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_CALENDAR' : 'IM_LIB_MENU_OPEN_CALENDAR_V2';
	    return {
	      text: main_core.Loc.getMessage(phraseCode),
	      onclick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getLeaveItem() {
	    if (this.isCollabChat() && !this.canLeaveCollab()) {
	      return null;
	    }
	    const canLeaveChat = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.leave, this.context.dialog.dialogId);
	    if (!canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showLeaveChatConfirm(this.context.dialog.dialogId);
	        if (!userChoice) {
	          return;
	        }
	        if (this.isCollabChat()) {
	          this.chatService.leaveCollab(this.context.dialog.dialogId);
	        } else {
	          this.chatService.leaveChat(this.context.dialog.dialogId);
	        }
	      }
	    };
	  }
	  isBot() {
	    return this.context.user.type === im_v2_const.UserType.bot;
	  }
	  canLeaveCollab() {
	    return this.permissionManager.canPerformActionByUserType(im_v2_const.ActionByUserType.leaveCollab);
	  }
	}

	const MemberTitleByChatType = {
	  [im_v2_const.ChatType.channel]: 'IM_SIDEBAR_MEMBERS_CHANNEL_DETAIL_TITLE',
	  [im_v2_const.ChatType.openChannel]: 'IM_SIDEBAR_MEMBERS_CHANNEL_DETAIL_TITLE',
	  [im_v2_const.ChatType.generalChannel]: 'IM_SIDEBAR_MEMBERS_CHANNEL_DETAIL_TITLE',
	  default: 'IM_SIDEBAR_MEMBERS_DETAIL_TITLE'
	};

	// @vue/component
	const MembersPanel = {
	  name: 'MembersPanel',
	  components: {
	    DetailUser,
	    ChatButton: im_v2_component_elements.Button,
	    DetailHeader,
	    Loader: im_v2_component_elements.Loader,
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      showAddToChatPopup: false,
	      showAddToChatTarget: null
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    userDialogIds() {
	      const users = this.$store.getters['sidebar/members/get'](this.chatId);
	      return users.map(userId => userId.toString());
	    },
	    chatLink() {
	      const isCopilot = this.dialog.type === im_v2_const.ChatType.copilot;
	      const chatGetParameter = isCopilot ? im_v2_const.GetParameter.openCopilotChat : im_v2_const.GetParameter.openChat;
	      return `${im_v2_application_core.Core.getHost()}/online/?${chatGetParameter}=${this.dialogId}`;
	    },
	    hasNextPage() {
	      return this.$store.getters['sidebar/members/hasNextPage'](this.chatId);
	    },
	    panelInited() {
	      return this.$store.getters['sidebar/members/getInited'](this.chatId);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    title() {
	      var _MemberTitleByChatTyp;
	      let usersInChatCount = this.dialog.userCounter;
	      if (usersInChatCount >= 1000) {
	        usersInChatCount = `${Math.floor(usersInChatCount / 1000)}k`;
	      }
	      const phrase = (_MemberTitleByChatTyp = MemberTitleByChatType[this.dialog.type]) != null ? _MemberTitleByChatTyp : MemberTitleByChatType.default;
	      return this.loc(phrase, {
	        '#NUMBER#': usersInChatCount
	      });
	    },
	    needAddButton() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.extend, this.dialogId);
	    },
	    needCopyLinkButton() {
	      return this.dialog.type !== im_v2_const.ChatType.collab;
	    },
	    addMembersPopupComponent() {
	      return this.dialog.type === im_v2_const.ChatType.collab ? im_v2_component_entitySelector.AddToCollab : im_v2_component_entitySelector.AddToChat;
	    }
	  },
	  watch: {
	    dialogId(dialogId) {
	      this.service = new MembersService({
	        dialogId
	      });
	      void this.loadFirstPage();
	    }
	  },
	  created() {
	    this.contextMenu = new MembersMenu();
	    this.service = new MembersService({
	      dialogId: this.dialogId
	    });
	    void this.loadFirstPage();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    async loadFirstPage() {
	      if (this.panelInited || this.isLoading) {
	        return;
	      }
	      this.isLoading = true;
	      this.chats = await this.service.loadFirstPage();
	      this.isLoading = false;
	    },
	    isOwner(userDialogId) {
	      const userId = Number.parseInt(userDialogId, 10);
	      return this.dialog.ownerId === userId;
	    },
	    isManager(userDialogId) {
	      const userId = Number.parseInt(userDialogId, 10);
	      return this.dialog.managerList.includes(userId);
	    },
	    onContextMenuClick(event) {
	      const user = this.$store.getters['users/get'](event.userDialogId, true);
	      const item = {
	        user,
	        dialog: this.dialog
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onCopyInviteClick() {
	      if (BX.clipboard.copy(this.chatLink)) {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_SIDEBAR_COPIED_SUCCESS')
	        });
	      }
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.members
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      return isAtThreshold && this.hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage();
	      this.isLoading = false;
	    },
	    onAddClick(event) {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatSidebarClick(this.dialogId);
	      this.showAddToChatPopup = true;
	      this.showAddToChatTarget = event.target;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="title"
				:secondLevel="secondLevel"
				:withAddButton="needAddButton"
				@addClick="onAddClick"
				@back="onBackClick" 
			/>
			<div class="bx-im-sidebar-detail__container bx-im-sidebar-main-detail__container" @scroll="onScroll">
				<div v-if="needCopyLinkButton" class="bx-im-sidebar-main-detail__invitation-button-container">
					<ChatButton
						:text="loc('IM_SIDEBAR_COPY_INVITE_LINK')"
						:size="ButtonSize.M"
						:color="ButtonColor.PrimaryBorder"
						:isRounded="true"
						:isUppercase="false"
						icon="link"
						@click="onCopyInviteClick"
					/>
				</div>
				<DetailUser
					v-for="userDialogId in userDialogIds"
					:dialogId="userDialogId"
					:contextDialogId="dialogId"
					:isOwner="isOwner(userDialogId)"
					:isManager="isManager(userDialogId)"
					@contextMenuClick="onContextMenuClick"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
			<component
				v-if="showAddToChatPopup"
				:is="addMembersPopupComponent"
				:bindElement="showAddToChatTarget || {}"
				:dialogId="dialogId"
				:popupConfig="{offsetTop: 0, offsetLeft: 0}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	class FavoriteMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	  }
	  getMenuItems() {
	    return [this.getOpenContextMessageItem(), this.getDeleteFromFavoriteItem()];
	  }
	  getDeleteFromFavoriteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_REMOVE_FROM_SAVED_V2'),
	      onclick: function () {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.removeMessageFromFavorite(this.context.messageId);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}

	const REQUEST_ITEMS_LIMIT$c = 50;
	var _query$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _processSearchResponse$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	class FavoriteSearch {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _processSearchResponse$4, {
	      value: _processSearchResponse2$4
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _query$4, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$4)[_query$4] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$4)[_query$4] = query;
	      this.hasMoreItemsToLoad = true;
	    }
	    return this.request();
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _query$4)[_query$4] = '';
	    this.hasMoreItemsToLoad = true;
	    void this.store.dispatch('sidebar/favorites/clearSearch', {});
	  }
	  async request() {
	    const queryParams = this.getQueryParams();
	    let responseData = {};
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imChatFavoriteGet, queryParams);
	      responseData = response.data();
	    } catch (error) {
	      console.error('SidebarSearch: Im.imChatFavoriteGet: page request error', error);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse$4)[_processSearchResponse$4](responseData);
	  }
	  getQueryParams() {
	    const queryParams = {
	      CHAT_ID: this.chatId,
	      LIMIT: REQUEST_ITEMS_LIMIT$c,
	      SEARCH_MESSAGE: babelHelpers.classPrivateFieldLooseBase(this, _query$4)[_query$4]
	    };
	    const lastId = this.store.getters['sidebar/favorites/getSearchResultCollectionLastId'](this.chatId);
	    if (lastId > 0) {
	      queryParams.LAST_ID = lastId;
	    }
	    return queryParams;
	  }
	  updateModels(resultData) {
	    const {
	      list = [],
	      users = [],
	      files = [],
	      tariffRestrictions = {}
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	    const rawMessages = list.map(favorite => favorite.message);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$c;
	    const lastId = getLastElementId(list);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
	    const setFavoritesPromise = this.store.dispatch('sidebar/favorites/setSearch', {
	      chatId: this.chatId,
	      favorites: list,
	      hasNextPage,
	      lastId,
	      isHistoryLimitExceeded
	    });
	    return Promise.all([setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise]);
	  }
	}
	function _processSearchResponse2$4(response) {
	  return this.updateModels(response).then(() => {
	    return response.list.map(message => message.messageId);
	  });
	}

	// @vue/component
	const FavoriteItem = {
	  name: 'FavoriteItem',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar,
	    MessageAuthorTitle: im_v2_component_elements.MessageAuthorTitle
	  },
	  props: {
	    favorite: {
	      type: Object,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    searchQuery: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['contextMenuClick'],
	  data() {
	    return {
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    favoriteItem() {
	      return this.favorite;
	    },
	    favoriteMessage() {
	      return this.$store.getters['messages/getById'](this.favoriteItem.messageId);
	    },
	    authorDialogId() {
	      return this.favoriteMessage.authorId.toString();
	    },
	    messageText() {
	      const purifiedMessage = im_v2_lib_parser.Parser.purifyMessage(this.favoriteMessage);
	      const textToShow = main_core.Text.encode(purifiedMessage);
	      if (this.searchQuery.length === 0) {
	        return textToShow;
	      }
	      return im_v2_lib_textHighlighter.highlightText(textToShow, this.searchQuery);
	    },
	    isCopilot() {
	      return this.$store.getters['users/bots/isCopilot'](this.favoriteMessage.authorId);
	    }
	  },
	  methods: {
	    onContextMenuClick(event) {
	      this.$emit('contextMenuClick', {
	        id: this.favoriteItem.id,
	        messageId: this.favorite.messageId,
	        target: event.currentTarget
	      });
	    },
	    onItemClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.goToMessageContext, {
	        messageId: this.favorite.messageId,
	        dialogId: this.dialogId
	      });
	    }
	  },
	  template: `
		<div 
			class="bx-im-favorite-item__container bx-im-favorite-item__scope" 
			@click.stop="onItemClick"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-favorite-item__header-container">
				<div class="bx-im-favorite-item__author-container">
					<MessageAvatar
						:messageId="favoriteItem.messageId"
						:authorId="authorDialogId"
						:size="AvatarSize.XS"
						class="bx-im-favorite-item__author-avatar"
					/>
					<MessageAuthorTitle 
						:dialogId="authorDialogId"
						:messageId="favoriteItem.messageId"
						:withLeftIcon="!isCopilot"
						:showItsYou="false" 
						class="bx-im-favorite-item__author-text"
					/>
				</div>
				<button 
					v-if="showContextButton"
					class="bx-im-messenger__context-menu-icon"
					@click.stop="onContextMenuClick"
				></button>
			</div>
			<div class="bx-im-favorite-item__message-text" v-html="messageText"></div>
		</div>
	`
	};

	const DEFAULT_MIN_TOKEN_SIZE$9 = 3;

	// @vue/component
	const FavoritePanel = {
	  name: 'FavoritePanel',
	  components: {
	    FavoriteItem,
	    DateGroup,
	    StartState: DetailEmptyState,
	    DetailEmptyState,
	    DetailHeader,
	    DetailEmptySearchState,
	    Loader: im_v2_component_elements.Loader,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      isSearchHeaderOpened: false,
	      searchQuery: '',
	      searchResult: [],
	      currentServerQueries: 0,
	      minTokenSize: DEFAULT_MIN_TOKEN_SIZE$9
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    favorites() {
	      if (this.isSearchHeaderOpened) {
	        return this.$store.getters['sidebar/favorites/getSearchResultCollection'](this.chatId);
	      }
	      return this.$store.getters['sidebar/favorites/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.favorites);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isSearchQueryMinimumSize() {
	      return this.preparedQuery.length < this.minTokenSize;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/favorites/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.cleanSearchResult();
	      this.startSearch();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FavoriteMenu();
	    this.service = new Favorite({
	      dialogId: this.dialogId
	    });
	    this.serviceSearch = new FavoriteSearch({
	      dialogId: this.dialogId
	    });
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	    this.collectionFormatter.destroy();
	  },
	  methods: {
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      this.minTokenSize = settings.get('minSearchTokenSize', DEFAULT_MIN_TOKEN_SIZE$9);
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.serviceSearch.searchOnServer(query).then(() => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	        }
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	        if (this.isSearchQueryMinimumSize) {
	          this.cleanSearchResult();
	        }
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    startSearch() {
	      if (this.isSearchQueryMinimumSize) {
	        this.cleanSearchResult();
	      } else {
	        this.isLoading = true;
	        this.searchOnServerDelayed(this.preparedQuery);
	      }
	    },
	    cleanSearchResult() {
	      this.searchResult = [];
	      this.serviceSearch.resetSearchState();
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    toggleSearchPanelOpened() {
	      this.isSearchHeaderOpened = !this.isSearchHeaderOpened;
	    },
	    onContextMenuClick(event) {
	      const item = {
	        id: event.id,
	        messageId: event.messageId,
	        dialogId: this.dialogId,
	        chatId: this.chatId
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.favorite
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const nameGetter = this.searchQuery.length > 0 ? 'sidebar/favorites/hasNextPageSearch' : 'sidebar/favorites/hasNextPage';
	      const hasNextPage = this.$store.getters[nameGetter](this.chatId);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      if (this.isSearchQueryMinimumSize) {
	        await this.service.loadNextPage();
	      } else {
	        await this.serviceSearch.request();
	      }
	      this.isLoading = false;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_FAVORITE_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:isSearchHeaderOpened="isSearchHeaderOpened"
				:delayForFocusOnStart="0"
				@changeQuery="onChangeQuery"
				@toggleSearchPanelOpened="toggleSearchPanelOpened"
				withSearch
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-favorite-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div
					v-for="dateGroup in formattedCollection"
					class="bx-im-sidebar-favorite-detail__date-group_container"
				>
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<FavoriteItem
						v-for="favorite in dateGroup.items"
						:favorite="favorite"
						:chatId="chatId"
						:dialogId="dialogId"
						:searchQuery="searchQuery"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.favorite"
					class="bx-im-sidebar-favorite-detail__tariff-limit-container"
				/>
				<template v-if="!isLoading">
					<template v-if="isSearchHeaderOpened">
						<StartState
							v-if="preparedQuery.length === 0"
							:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
							:iconType="SidebarDetailBlock.messageSearch"
						/>
						<DetailEmptySearchState
							v-else-if="isEmptyState"
							:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_EXTENDED')"
							:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION_EXTENDED')"
						/>
					</template>
					<DetailEmptyState
						v-else-if="isEmptyState"
						:title="loc('IM_SIDEBAR_FAVORITES_EMPTY')"
						:iconType="SidebarDetailBlock.favorite"
					/>
				</template>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$d = 50;
	var _lastMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastMessageId");
	var _query$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _request = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("request");
	var _processSearchResponse$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	class MessageSearch {
	  // eslint-disable-next-line no-unused-private-class-members

	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _updateModels$1, {
	      value: _updateModels2$1
	    });
	    Object.defineProperty(this, _processSearchResponse$5, {
	      value: _processSearchResponse2$5
	    });
	    Object.defineProperty(this, _request, {
	      value: _request2
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _lastMessageId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _query$5, {
	      writable: true,
	      value: ''
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.chatId = getChatId(dialogId);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  searchOnServer(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _query$5)[_query$5] !== query) {
	      babelHelpers.classPrivateFieldLooseBase(this, _query$5)[_query$5] = query;
	      this.hasMoreItemsToLoad = true;
	      babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] = 0;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _request)[_request]();
	  }
	  loadNextPage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _request)[_request]();
	  }
	  loadFirstPage() {
	    return Promise.resolve();
	  }
	  resetSearchState() {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _query$5)[_query$5] = '';
	    this.hasMoreItemsToLoad = true;
	  }
	}
	function _request2() {
	  const config = {
	    SEARCH_MESSAGE: babelHelpers.classPrivateFieldLooseBase(this, _query$5)[_query$5],
	    CHAT_ID: this.chatId
	  };
	  if (babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] > 0) {
	    config.LAST_ID = babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId];
	  }
	  return new Promise((resolve, reject) => {
	    this.restClient.callMethod(im_v2_const.RestMethod.imDialogMessagesSearch, config).then(response => {
	      const responseData = response.data();
	      resolve(babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse$5)[_processSearchResponse$5](responseData));
	    }).catch(error => reject(error));
	  });
	}
	function _processSearchResponse2$5(response) {
	  babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] = getLastElementId(response.messages);
	  if (response.messages.length < REQUEST_ITEMS_LIMIT$d) {
	    this.hasMoreItemsToLoad = false;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](response).then(() => {
	    return response.messages.map(message => message.id);
	  });
	}
	function _updateModels2$1(rawData) {
	  const {
	    files,
	    users,
	    usersShort,
	    reactions,
	    additionalMessages,
	    messages,
	    tariffRestrictions = {}
	  } = rawData;
	  const isHistoryLimitExceeded = Boolean(tariffRestrictions.isHistoryLimitExceeded);
	  const historyLimitPromise = this.store.dispatch('sidebar/messageSearch/setHistoryLimitExceeded', {
	    chatId: this.chatId,
	    isHistoryLimitExceeded
	  });
	  const usersPromise = Promise.all([this.userManager.setUsersToModel(users), this.userManager.addUsersToModel(usersShort)]);
	  const filesPromise = this.store.dispatch('files/set', files);
	  const reactionsPromise = this.store.dispatch('messages/reactions/set', reactions);
	  const additionalMessagesPromise = this.store.dispatch('messages/store', additionalMessages);
	  const messagesPromise = this.store.dispatch('messages/store', messages);
	  return Promise.all([filesPromise, usersPromise, reactionsPromise, additionalMessagesPromise, messagesPromise, historyLimitPromise]);
	}

	// @vue/component
	const SearchItem = {
	  name: 'SearchItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    messageId: {
	      type: [String, Number],
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    query: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    authorDialogId() {
	      return this.message.authorId.toString();
	    },
	    isSystem() {
	      return this.message.authorId === 0;
	    },
	    messageText() {
	      const purifiedMessage = im_v2_lib_parser.Parser.purifyMessage(this.message);
	      return im_v2_lib_textHighlighter.highlightText(main_core.Text.encode(purifiedMessage), this.query);
	    }
	  },
	  methods: {
	    onItemClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.goToMessageContext, {
	        messageId: this.messageId,
	        dialogId: this.dialogId
	      });
	    },
	    onMessageBodyClick(event) {
	      if (event.target.tagName === 'A') {
	        event.stopPropagation();
	      }
	    }
	  },
	  template: `
		<div 
			class="bx-im-message-search-item__container bx-im-message-search-item__scope" 
			@click.stop="onItemClick"
		>
			<div class="bx-im-message-search-item__header-container">
				<div class="bx-im-message-search-item__author-container">
					<template v-if="!isSystem">
						<ChatAvatar
							:size="AvatarSize.XS"
							:avatarDialogId="authorDialogId"
							:contextDialogId="dialogId"
							class="bx-im-message-search-item__author-avatar"
						/>
						<ChatTitle 
							:dialogId="authorDialogId" 
							:showItsYou="false" 
							class="bx-im-message-search-item__author-text" 
						/>
					</template>
					<template v-else>
						<span class="bx-im-message-search-item__system-author">
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_SYSTEM_MESSAGE') }}
						</span>
					</template>
				</div>
			</div>
			<div class="bx-im-message-search-item__message-text" v-html="messageText" @click="onMessageBodyClick"></div>
		</div>
	`
	};

	// @vue/component
	const SearchHeader = {
	  name: 'SearchHeader',
	  components: {
	    SearchInput: im_v2_component_elements.SearchInput
	  },
	  props: {
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['back', 'changeQuery'],
	  template: `
		<div class="bx-im-sidebar-search-header__container bx-im-sidebar-search-header__scope">
			<div class="bx-im-sidebar-search-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				></button>
				<SearchInput
					:placeholder="$Bitrix.Loc.getMessage('IM_SIDEBAR_SEARCH_MESSAGE_PLACEHOLDER')"
					:withIcon="false"
					:delayForFocusOnStart="300"
					@queryChange="$emit('changeQuery', $event)"
					class="bx-im-sidebar-search-header__input"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const MessageSearchPanel = {
	  name: 'MessageSearchPanel',
	  components: {
	    DateGroup,
	    SearchItem,
	    Loader: im_v2_component_elements.Loader,
	    StartState: DetailEmptyState,
	    SearchHeader,
	    DetailEmptySearchState,
	    TariffLimit
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      searchQuery: '',
	      isLoading: false,
	      searchResult: [],
	      currentServerQueries: 0
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    formattedCollection() {
	      const messages = this.searchResult.map(messageId => {
	        return this.$store.getters['messages/getById'](messageId);
	      }).filter(item => Boolean(item));
	      return this.collectionFormatter.format(messages);
	    },
	    isEmptyState() {
	      return this.preparedQuery.length > 0 && this.formattedCollection.length === 0;
	    },
	    preparedQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    hasHistoryLimit() {
	      return this.$store.getters['sidebar/messageSearch/isHistoryLimitExceeded'](this.chatId);
	    }
	  },
	  watch: {
	    preparedQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.service.resetSearchState();
	      this.searchResult = [];
	      this.startSearch(newQuery);
	    }
	  },
	  created() {
	    this.service = new MessageSearch({
	      dialogId: this.dialogId
	    });
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 500, this);
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	  },
	  methods: {
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.service.searchOnServer(query).then(messageIds => {
	        if (query !== this.preparedQuery) {
	          this.isLoading = false;
	          return;
	        }
	        this.searchResult = this.mergeResult(messageIds);
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	      });
	    },
	    startSearch(query) {
	      if (query.length < 3) {
	        return;
	      }
	      if (query.length >= 3) {
	        this.isLoading = true;
	        this.searchOnServerDelayed(query);
	      }
	      if (query.length === 0) {
	        this.cleanSearchResult();
	      }
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isLoading = false;
	    },
	    cleanSearchResult() {
	      this.searchResult = [];
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      return target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	    },
	    onScroll(event) {
	      if (this.isLoading || this.preparedQuery.length === 0) {
	        return;
	      }
	      if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoading = true;
	      this.service.loadNextPage().then(messageIds => {
	        this.searchResult = this.mergeResult(messageIds);
	        this.isLoading = false;
	      }).catch(error => {
	        im_v2_lib_logger.Logger.warn('Message Search: loadNextPage error', error);
	      });
	    },
	    mergeResult(messageIds) {
	      return [...this.searchResult, ...messageIds].sort((a, z) => z - a);
	    },
	    onChangeQuery(query) {
	      this.searchQuery = query;
	    },
	    onClickBack() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.messageSearch
	      });
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-message-search-detail__scope">
			<SearchHeader :secondLevel="secondLevel" @changeQuery="onChangeQuery" @back="onClickBack" />
			<div class="bx-im-message-search-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<StartState 
					v-if="!isLoading && preparedQuery.length === 0"
					:title="loc('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
					:iconType="SidebarDetailBlock.messageSearch"
				/>
				<DetailEmptySearchState
					v-if="!isLoading && isEmptyState"
					:title="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND')"
					:subTitle="loc('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION')"
				/>
				<Loader v-if="isLoading && isEmptyState" class="bx-im-message-search-detail__loader" />
				<div v-for="dateGroup in formattedCollection" class="bx-im-message-search-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<SearchItem
						v-for="item in dateGroup.items"
						:messageId="item.id"
						:dialogId="dialogId"
						:query="preparedQuery"
					/>
				</div>
				<TariffLimit
					v-if="hasHistoryLimit"
					:dialogId="dialogId"
					:panel="SidebarDetailBlock.messageSearch"
					class="bx-im-message-search-detail__tariff-limit-container"
				/>
			</div>
		</div>
	`
	};

	const ItemTextByChatType = {
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_COLLAB'),
	  default: main_core.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2')
	};

	// @vue/component
	const ChatItem = {
	  name: 'ChatItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    dateMessage: {
	      type: String,
	      default: ''
	    }
	  },
	  emits: ['clickItem'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatItemText() {
	      var _ItemTextByChatType$t;
	      return (_ItemTextByChatType$t = ItemTextByChatType[this.dialog.type]) != null ? _ItemTextByChatType$t : ItemTextByChatType.default;
	    },
	    formattedDate() {
	      if (!this.dateMessage) {
	        return '';
	      }
	      const date = im_v2_lib_utils.Utils.date.cast(this.dateMessage);
	      return this.formatDate(date);
	    }
	  },
	  methods: {
	    onClick(event) {
	      this.$emit('clickItem', {
	        dialogId: this.dialogId,
	        nativeEvent: event
	      });
	    },
	    formatDate(date) {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.recent);
	    }
	  },
	  template: `
		<div 
			@click="onClick"
			class="bx-im-chat-with-user-item__container bx-im-chat-with-user-item__scope"
		>
			<div class="bx-im-chat-with-user-item__avatar-container">
				<ChatAvatar 
					:avatarDialogId="dialogId" 
					:contextDialogId="dialogId" 
					:size="AvatarSize.XL" 
				/>
			</div>
			<div class="bx-im-chat-with-user-item__content-container">
				<div class="bx-im-chat-with-user-item__content_header">
					<ChatTitle :dialogId="dialogId" />
					<div v-if="formattedDate.length > 0" class="bx-im-chat-with-user-item__date">
						<span>{{ formattedDate }}</span>
					</div>
				</div>
				<div class="bx-im-chat-with-user-item__item-text" :title="chatItemText">
					{{ chatItemText }}
				</div>
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$e = 50;
	var _chatsCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatsCount");
	var _getRequestParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRequestParams");
	var _requestPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestPage");
	var _handleResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleResponse");
	var _updateModels$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _setDialoguesPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialoguesPromise");
	class ChatsWithUser {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _setDialoguesPromise, {
	      value: _setDialoguesPromise2
	    });
	    Object.defineProperty(this, _updateModels$2, {
	      value: _updateModels2$2
	    });
	    Object.defineProperty(this, _handleResponse, {
	      value: _handleResponse2
	    });
	    Object.defineProperty(this, _requestPage, {
	      value: _requestPage2
	    });
	    Object.defineProperty(this, _getRequestParams, {
	      value: _getRequestParams2
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _chatsCount, {
	      writable: true,
	      value: 0
	    });
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.dialogId = dialogId;
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  loadFirstPage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestPage)[_requestPage]();
	  }
	  loadNextPage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestPage)[_requestPage]();
	  }
	}
	function _getRequestParams2() {
	  const userId = Number.parseInt(this.dialogId, 10);
	  const requestParams = {
	    filter: {
	      userId
	    },
	    limit: REQUEST_ITEMS_LIMIT$e
	  };
	  if (babelHelpers.classPrivateFieldLooseBase(this, _chatsCount)[_chatsCount] > 0) {
	    requestParams.offset = babelHelpers.classPrivateFieldLooseBase(this, _chatsCount)[_chatsCount];
	  }
	  return requestParams;
	}
	async function _requestPage2() {
	  const requestParams = babelHelpers.classPrivateFieldLooseBase(this, _getRequestParams)[_getRequestParams]();
	  const response = await this.restClient.callMethod(im_v2_const.RestMethod.imV2ChatListShared, requestParams);
	  return babelHelpers.classPrivateFieldLooseBase(this, _handleResponse)[_handleResponse](response.data());
	}
	async function _handleResponse2(response) {
	  const {
	    chats
	  } = response;
	  babelHelpers.classPrivateFieldLooseBase(this, _chatsCount)[_chatsCount] += chats.length;
	  if (chats.length < REQUEST_ITEMS_LIMIT$e) {
	    this.hasMoreItemsToLoad = false;
	  }
	  await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$2)[_updateModels$2](chats);
	  return chats.map(chat => {
	    return {
	      dialogId: chat.dialogId,
	      dateMessage: chat.dateMessage
	    };
	  });
	}
	function _updateModels2$2(chats) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _setDialoguesPromise)[_setDialoguesPromise](chats);
	}
	function _setDialoguesPromise2(chats) {
	  return this.store.dispatch('chats/set', chats);
	}

	// @vue/component
	const ChatsWithUserPanel = {
	  name: 'ChatsWithUserPanel',
	  components: {
	    DetailHeader,
	    ChatItem,
	    DetailEmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      chats: []
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    isEmptyState() {
	      return !this.isLoading && this.chats.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  watch: {
	    dialogId() {
	      this.chats = [];
	      this.service = new ChatsWithUser({
	        dialogId: this.dialogId
	      });
	      void this.loadFirstPage();
	    }
	  },
	  created() {
	    this.service = new ChatsWithUser({
	      dialogId: this.dialogId
	    });
	    void this.loadFirstPage();
	  },
	  methods: {
	    onClick(event) {
	      const {
	        dialogId
	      } = event;
	      void im_public.Messenger.openChat(dialogId);
	    },
	    async loadFirstPage() {
	      this.isLoading = true;
	      this.chats = await this.service.loadFirstPage();
	      this.isLoading = false;
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      return target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	    },
	    async onScroll(event) {
	      if (this.isLoading) {
	        return;
	      }
	      if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoading = true;
	      const nextPageChats = await this.service.loadNextPage();
	      this.chats = [...this.chats, ...nextPageChats];
	      this.isLoading = false;
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.chatsWithUser
	      });
	    },
	    loc(phrase) {
	      return this.$Bitrix.Loc.getMessage(phrase);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-chats-with-user-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_CHATSWITHUSER_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div 
				class="bx-im-sidebar-chats-with-user-detail__container" 
				@scroll="onScroll"
			>
				<ChatItem
					v-for="chat in chats"
					:dialogId="chat.dialogId"
					:dateMessage="chat.dateMessage"
					@clickItem="onClick"
				/>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="loc('IM_SIDEBAR_CHATS_WITH_USER_EMPTY')"
					:iconType="SidebarDetailBlock.messageSearch"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-chats-with-user-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const MultidialogItem = {
	  name: 'MultidialogItem',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    multidialogItem() {
	      return this.item;
	    },
	    dialogId() {
	      return this.multidialogItem.dialogId;
	    },
	    chatId() {
	      return this.multidialogItem.chatId;
	    },
	    title() {
	      const chat = this.$store.getters['chats/get'](this.dialogId);
	      return chat.name;
	    },
	    status() {
	      return this.multidialogItem.status;
	    },
	    transferredStatus() {
	      const code = `IM_SIDEBAR_SUPPORT_TICKET_STATUS_${this.status.toUpperCase()}`;
	      return this.loc(code);
	    },
	    containerClasses() {
	      const status = `--${this.status}`;
	      const chatIsOpened = this.$store.getters['application/isChatOpen'](this.dialogId);
	      return [status, {
	        '--selected': chatIsOpened
	      }];
	    },
	    counter() {
	      var _this$$store$getters$;
	      const counter = (_this$$store$getters$ = this.$store.getters['counters/getChatCounterByChatId'](this.chatId)) != null ? _this$$store$getters$ : 0;
	      return counter > 99 ? '99+' : counter;
	    },
	    formatDate() {
	      const date = this.multidialogItem.date;
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.recent);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-multidialog-item__container bx-im-sidebar-multidialog-preview__scope"
		 	:class="containerClasses"
			:title="title"
		>
			<span class="bx-im-multidialog-item__title">{{ title }}</span>
			<span class="bx-im-multidialog-item__date">
				{{ formatDate }}
			</span>
			<div class="bx-im-multidialog-item__status">
				{{ transferredStatus }}
			</div>
			<div v-show="counter" class="bx-im-multidialog-item__count bx-im-sidebar-multidialog-preview__new-message-counter">
				{{ counter }}
			</div>
		</div>
	`
	};

	// @vue/component
	const MultidialogPanel = {
	  name: 'MultidialogPanel',
	  components: {
	    DetailHeader,
	    MultidialogItem,
	    ChatButton: im_v2_component_elements.Button,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      isLoading: false,
	      isCreating: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    activeMultidialogs() {
	      const multidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([im_v2_const.MultidialogStatus.new, im_v2_const.MultidialogStatus.open]);
	      return multidialogs.sort((a, b) => b.date - a.date);
	    },
	    closedMultidialogs() {
	      const multidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([im_v2_const.MultidialogStatus.close]);
	      return multidialogs.sort((a, b) => b.date - a.date);
	    },
	    limitReached() {
	      const openMultidialogs = this.$store.getters['sidebar/multidialog/getMultidialogsByStatus']([im_v2_const.MultidialogStatus.open]);
	      const openSessionsLimit = this.$store.getters['sidebar/multidialog/getOpenSessionsLimit'];
	      return openSessionsLimit <= openMultidialogs.length;
	    },
	    isInitedDetail() {
	      return this.$store.getters['sidebar/multidialog/isInitedDetail'];
	    },
	    isDisabledButtonCreate() {
	      return this.limitReached || !this.isInitedDetail;
	    },
	    buttonCreateTitle() {
	      if (!this.limitReached || !this.isInitedDetail) {
	        return '';
	      }
	      return this.loc('IM_SIDEBAR_SUPPORT_TICKET_LIMIT');
	    }
	  },
	  created() {
	    this.service = new Multidialog();
	  },
	  mounted() {
	    void this.loadFirstPage();
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.multidialog
	      });
	    },
	    needToLoadNextPage(event) {
	      const target = event.target;
	      const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
	      const hasNextPage = this.$store.getters['sidebar/multidialog/hasNextPage'];
	      return isAtThreshold && hasNextPage;
	    },
	    async loadFirstPage() {
	      if (this.isLoading) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadFirstPage();
	      this.isLoading = false;
	    },
	    async onScroll(event) {
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage();
	      this.isLoading = false;
	    },
	    async onAddSupport() {
	      if (this.isCreating) {
	        return;
	      }
	      this.isCreating = true;
	      const newDialogId = await this.service.createSupportChat();
	      if (newDialogId) {
	        this.openChat(newDialogId);
	      }
	      this.isCreating = false;
	    },
	    openChat(dialogId) {
	      void im_public.Messenger.openChat(dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-multidialog-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="loc('IM_SIDEBAR_SUPPORT_TICKET_DETAIL_TITLE')"
				:secondLevel="true"
				@back="onBackClick"
			>
				<template #action>
					<div :title="buttonCreateTitle" class="bx-im-sidebar-detail-header__add-button">
						<ChatButton
							:text="loc('IM_SIDEBAR_SUPPORT_TICKET_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
							:isLoading="isCreating"
							:isDisabled="isDisabledButtonCreate"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddSupport"
						/>
					</div>
				</template>
			</DetailHeader>
			<div class="bx-im-sidebar-multidialog-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<MultidialogItem
					v-for="multidialog in activeMultidialogs"
					:key="multidialog.chatId"
					:item="multidialog"
					@click="openChat(multidialog.dialogId)"
				/>
				<MultidialogItem
					v-for="multidialog in closedMultidialogs"
					:key="multidialog.chatId"
					:item="multidialog"
					@click="openChat(multidialog.dialogId)"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const SidebarPanel = {
	  name: 'SidebarPanel',
	  components: {
	    MainPanel,
	    ChatsWithUserPanel,
	    MembersPanel,
	    FavoritePanel,
	    LinkPanel,
	    FilePanel,
	    TaskPanel,
	    MeetingPanel,
	    MarketPanel,
	    MessageSearchPanel,
	    FileUnsortedPanel,
	    MultidialogPanel
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    panel: {
	      type: String,
	      required: true
	    },
	    secondLevel: {
	      type: Boolean,
	      default: false
	    },
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    panelComponentName() {
	      return `${main_core.Text.capitalize(this.panel)}Panel`;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-panel__container" :class="{'--second-level': secondLevel}">
			<KeepAlive>
				<component
					:is="panelComponentName"
					:dialogId="dialogId"
					:entityId="entityId"
					:secondLevel="secondLevel"
					class="bx-im-sidebar-panel__component"
				/>
			</KeepAlive>
		</div>
	`
	};

	// @vue/component
	const ChatSidebar = {
	  name: 'ChatSidebar',
	  components: {
	    SidebarPanel
	  },
	  props: {
	    originDialogId: {
	      type: String,
	      required: true
	    },
	    isActive: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['changePanel'],
	  data() {
	    return {
	      needTopLevelTransition: true,
	      needSecondLevelTransition: true,
	      topLevelPanelType: '',
	      topLevelPanelDialogId: '',
	      topLevelPanelStandalone: false,
	      secondLevelPanelType: '',
	      secondLevelPanelDialogId: '',
	      secondLevelPanelEntityId: '',
	      secondLevelPanelStandalone: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    topLevelTransitionName() {
	      return this.needTopLevelTransition ? 'top-level-panel' : '';
	    },
	    secondLevelTransitionName() {
	      return this.needSecondLevelTransition ? 'second-level-panel' : '';
	    },
	    canShowTopPanel() {
	      const membersPanel = this.topLevelPanelType === im_v2_const.SidebarDetailBlock.members;
	      const personalChat = !this.originDialogId.startsWith('chat');
	      if (membersPanel && personalChat) {
	        return false;
	      }
	      const messageSearchPanel = this.topLevelPanelType === im_v2_const.SidebarDetailBlock.messageSearch;
	      return !messageSearchPanel;
	    }
	  },
	  watch: {
	    originDialogId(newValue, oldValue) {
	      const chatSwitched = Boolean(newValue && oldValue);
	      if (chatSwitched) {
	        this.needTopLevelTransition = false;
	      }
	      if (!this.topLevelPanelStandalone) {
	        this.updateTopPanelOriginDialogId(newValue);
	      }
	      const isSecondLevelPanelOpened = this.secondLevelPanelType.length > 0;
	      if (isSecondLevelPanelOpened && !this.secondLevelPanelStandalone) {
	        this.closeSecondLevelPanel();
	      }
	      if (!this.canShowTopPanel) {
	        this.closeTopPanel();
	      }
	    },
	    topLevelPanelType(newValue, oldValue) {
	      this.needTopLevelTransition = oldValue.length === 0 || newValue.length === 0;
	      const isMainPanelOpened = newValue === im_v2_const.SidebarDetailBlock.main;
	      this.saveSidebarOpenedState(isMainPanelOpened);
	    },
	    secondLevelPanelType(newValue, oldValue) {
	      this.needSecondLevelTransition = !(newValue && oldValue);
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('ChatSidebar: created');
	    this.restoreOpenState();
	  },
	  mounted() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.sidebar.open, this.onSidebarOpen);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.sidebar.close, this.onSidebarClose);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.sidebar.open, this.onSidebarOpen);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.sidebar.close, this.onSidebarClose);
	  },
	  methods: {
	    onSidebarOpen(event) {
	      if (!this.isActive) {
	        return;
	      }
	      const {
	        panel = '',
	        standalone = false,
	        dialogId,
	        entityId = ''
	      } = event.getData();
	      const needToCloseSecondLevelPanel = !standalone && panel && this.secondLevelPanelType === panel;
	      if (needToCloseSecondLevelPanel) {
	        this.closeSecondLevelPanel();
	        return;
	      }
	      const needToOpenSecondLevelPanel = this.topLevelPanelType && this.topLevelPanelType !== panel;
	      if (needToOpenSecondLevelPanel) {
	        this.openSecondLevelPanel(panel, dialogId, standalone, entityId);
	      } else {
	        this.openTopPanel(panel, dialogId, standalone);
	      }
	    },
	    onSidebarClose(event) {
	      if (!this.isActive) {
	        return;
	      }
	      this.needTopLevelTransition = true;
	      const {
	        panel = ''
	      } = event.getData();
	      const needToCloseSecondLevelPanel = panel && this.secondLevelPanelType === panel;
	      if (needToCloseSecondLevelPanel) {
	        this.closeSecondLevelPanel();
	      } else {
	        this.closeSecondLevelPanel();
	        this.closeTopPanel();
	      }
	    },
	    restoreOpenState() {
	      const sidebarOpenState = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.sidebarOpened);
	      if (!sidebarOpenState) {
	        return;
	      }
	      this.openTopPanel(im_v2_const.SidebarDetailBlock.main, this.originDialogId, false);
	    },
	    saveSidebarOpenedState(sidebarOpened) {
	      const WRITE_TO_STORAGE_TIMEOUT = 200;
	      clearTimeout(this.saveSidebarStateTimeout);
	      this.saveSidebarStateTimeout = setTimeout(() => {
	        im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.sidebarOpened, sidebarOpened);
	      }, WRITE_TO_STORAGE_TIMEOUT);
	    },
	    openTopPanel(type, dialogId, standalone = false) {
	      this.topLevelPanelType = type;
	      this.topLevelPanelDialogId = dialogId;
	      this.topLevelPanelStandalone = standalone;
	      this.$emit('changePanel', {
	        panel: this.topLevelPanelType
	      });
	    },
	    updateTopPanelOriginDialogId(dialogId) {
	      this.topLevelPanelDialogId = dialogId;
	    },
	    openSecondLevelPanel(type, dialogId, standalone = false, entityId = '') {
	      this.secondLevelPanelType = type;
	      this.secondLevelPanelDialogId = dialogId;
	      this.secondLevelPanelStandalone = standalone;
	      this.secondLevelPanelEntityId = entityId;
	      this.$emit('changePanel', {
	        panel: this.secondLevelPanelType
	      });
	    },
	    closeTopPanel() {
	      this.topLevelPanelType = '';
	      this.topLevelPanelDialogId = '';
	      this.topLevelPanelStandalone = false;
	      this.$emit('changePanel', {
	        panel: ''
	      });
	    },
	    closeSecondLevelPanel() {
	      this.secondLevelPanelType = '';
	      this.secondLevelPanelDialogId = '';
	      this.secondLevelPanelStandalone = false;
	      this.$emit('changePanel', {
	        panel: this.topLevelPanelType
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar__container">
			<Transition :name="topLevelTransitionName">
				<SidebarPanel
					v-if="topLevelPanelType"
					:dialogId="topLevelPanelDialogId"
					:panel="topLevelPanelType"
				/>
			</Transition>
			<Transition :name="secondLevelTransitionName">
				<SidebarPanel
					v-if="secondLevelPanelType"
					:dialogId="secondLevelPanelDialogId" 
					:panel="secondLevelPanelType"
					:entityId="secondLevelPanelEntityId"
					:secondLevel="true"
				/>
			</Transition>
		</div>
	`
	};

	exports.ChatSidebar = ChatSidebar;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.UI,BX.Messenger.v2.Lib,BX.Main,BX.Vue3.Directives,BX.Messenger.v2.Lib,BX.UI,BX.UI.Manual,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI.Viewer,BX,BX.Messenger.v2.Model,BX,BX,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements,BX.Event,BX.Messenger.v2.Lib));
//# sourceMappingURL=sidebar.bundle.js.map
