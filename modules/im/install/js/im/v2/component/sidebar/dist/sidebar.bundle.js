/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_localStorage,im_v2_lib_rest,ui_vue3_directives_lazyload,ui_label,im_v2_lib_menu,main_date,ui_vue3_directives_hint,ui_vue3_components_socialvideo,ui_viewer,ui_icons,im_v2_model,ui_notification,rest_client,ui_vue3_vuex,im_v2_lib_market,im_v2_lib_entityCreator,im_v2_component_entitySelector,im_v2_lib_call,im_v2_lib_permission,im_v2_lib_confirm,im_v2_provider_service,im_v2_lib_logger,main_core,im_v2_lib_parser,im_v2_lib_textHighlighter,main_core_events,im_public,im_v2_lib_utils,im_v2_lib_dateFormatter,im_v2_component_elements,im_v2_const,im_v2_lib_user,im_v2_application_core) {
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
	      files = []
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const rawMessages = list.map(favorite => favorite.message);
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT;
	    const lastId = getLastElementId(list);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
	    const setFavoritesPromise = this.store.dispatch('sidebar/favorites/set', {
	      chatId: this.chatId,
	      favorites: list,
	      hasNextPage,
	      lastId
	    });
	    return Promise.all([setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise]);
	  }
	}

	const MainPanelSpecialType = {
	  support24: 'support24'
	};
	const MainPanelType = {
	  user: [im_v2_const.ChatType.user],
	  chat: [im_v2_const.ChatType.chat],
	  copilot: [im_v2_const.ChatType.copilot],
	  support24: [MainPanelSpecialType.support24]
	};
	const MainPanelBlock = Object.freeze({
	  chat: 'chat',
	  user: 'user',
	  info: 'info',
	  file: 'file',
	  fileUnsorted: 'fileUnsorted',
	  task: 'task',
	  meeting: 'meeting',
	  market: 'market'
	});
	const MainPanels = {
	  [MainPanelType.user]: {
	    [MainPanelBlock.user]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.fileUnsorted]: 30,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50,
	    [MainPanelBlock.market]: 60
	  },
	  [MainPanelType.chat]: {
	    [MainPanelBlock.chat]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.file]: 30,
	    [MainPanelBlock.fileUnsorted]: 30,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50,
	    [MainPanelBlock.market]: 60
	  },
	  [MainPanelType.copilot]: {
	    [MainPanelBlock.user]: 10,
	    [MainPanelBlock.info]: 20,
	    [MainPanelBlock.task]: 40,
	    [MainPanelBlock.meeting]: 50
	  }
	};

	class SettingsManager {
	  constructor() {
	    this.settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	    this.saveSettings();
	  }
	  async saveSettings() {
	    await im_v2_application_core.Core.ready();
	    void im_v2_application_core.Core.getStore().dispatch('sidebar/setFilesMigrated', this.settings.get('filesMigrated', false));
	    void im_v2_application_core.Core.getStore().dispatch('sidebar/setLinksMigrated', this.settings.get('linksAvailable', false));
	  }
	  canShowBriefs() {
	    return this.settings.get('canShowBriefs', false);
	  }
	  isLinksMigrationFinished() {
	    return im_v2_application_core.Core.getStore().state.sidebar.isLinksMigrated;
	  }
	  isFileMigrationFinished() {
	    return im_v2_application_core.Core.getStore().state.sidebar.isFilesMigrated;
	  }
	}

	function getMainBlocksForChat(dialogId) {
	  const panelType = getMainPanelType(dialogId);
	  return Object.entries(MainPanels[panelType]).sort(([, order1], [, order2]) => order1 - order2).map(([block]) => block);
	}
	function getMainPanelType(dialogId) {
	  var _MainPanelType$chatTy;
	  const chatType = getChatType(dialogId);
	  return (_MainPanelType$chatTy = MainPanelType[chatType]) != null ? _MainPanelType$chatTy : MainPanelType.chat;
	}
	const getChatType = dialogId => {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId).type;
	};

	const settingsManager = new SettingsManager();
	function getAvailableBlocks(dialogId) {
	  const blocks = getMainBlocksForChat(dialogId);
	  return filterUnavailableBlocks(dialogId, blocks);
	}
	function filterUnavailableBlocks(dialogId, blocks) {
	  const blocksSet = new Set(blocks);
	  if (isFileMigrationFinished()) {
	    blocksSet.delete(MainPanelBlock.fileUnsorted);
	  } else {
	    blocksSet.delete(MainPanelBlock.file);
	  }
	  if (!hasMarketApps(dialogId)) {
	    blocksSet.delete(MainPanelBlock.market);
	  }
	  if (isBot(dialogId)) {
	    blocksSet.delete(MainPanelBlock.task);
	    blocksSet.delete(MainPanelBlock.meeting);
	  }
	  return [...blocksSet];
	}
	function isBot(dialogId) {
	  const user = im_v2_application_core.Core.getStore().getters['users/get'](dialogId);
	  return (user == null ? void 0 : user.bot) === true;
	}
	function isFileMigrationFinished() {
	  return settingsManager.isFileMigrationFinished();
	}
	function hasMarketApps(dialogId) {
	  return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.sidebar, dialogId).length > 0;
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
	      users
	    } = response;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setLinksPromise = this.store.dispatch('sidebar/links/set', {
	      chatId: this.chatId,
	      links: list,
	      hasNextPage: list.length === REQUEST_ITEMS_LIMIT$1
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
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      files
	    } = resultData;
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
	    return Promise.all([setFilesPromise, addUsersPromise, ...setSidebarFilesPromises]);
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
	      return this.updateModels(response.data());
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
	      users
	    } = resultData;
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$3;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setTasksPromise = this.store.dispatch('sidebar/tasks/set', {
	      chatId: this.chatId,
	      tasks: list,
	      hasNextPage,
	      lastId
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
	      users
	    } = resultData;
	    const hasNextPage = list.length === REQUEST_ITEMS_LIMIT$4;
	    const lastId = getLastElementId(list);
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setMeetingsPromise = this.store.dispatch('sidebar/meetings/set', {
	      chatId: this.chatId,
	      meetings: list,
	      hasNextPage,
	      lastId
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
	        limit: REQUEST_ITEMS_LIMIT$5
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

	const REQUEST_ITEMS_LIMIT$6 = 50;
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
	        limit: REQUEST_ITEMS_LIMIT$6
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
	    if (filesCount > REQUEST_ITEMS_LIMIT$6) {
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
	      LIMIT: REQUEST_ITEMS_LIMIT$6
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
	    if (diskFolderListGetResult.files.length < REQUEST_ITEMS_LIMIT$6) {
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
	      files
	    } = resultData;
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
	      hasNextPage: preparedFiles.length === REQUEST_ITEMS_LIMIT$6
	    });
	    const setLastIdPromise = this.store.dispatch('sidebar/files/setLastId', {
	      chatId: this.chatId,
	      subType: im_v2_const.SidebarDetailBlock.fileUnsorted,
	      lastId: getLastElementId(preparedFiles)
	    });
	    return Promise.all([setFilesPromise, setSidebarFilesPromise, addUsersPromise, hasNextPagePromise, setLastIdPromise]);
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
	  FileUnsorted
	};
	const BlockToServices = Object.freeze({
	  [MainPanelBlock.chat]: [im_v2_const.SidebarDetailBlock.members],
	  [MainPanelBlock.info]: [im_v2_const.SidebarDetailBlock.favorite, im_v2_const.SidebarDetailBlock.link],
	  [MainPanelBlock.file]: [im_v2_const.SidebarDetailBlock.file],
	  [MainPanelBlock.fileUnsorted]: [im_v2_const.SidebarDetailBlock.fileUnsorted],
	  [MainPanelBlock.task]: [im_v2_const.SidebarDetailBlock.task],
	  [MainPanelBlock.meeting]: [im_v2_const.SidebarDetailBlock.meeting]
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
		<div class="bx-im-sidebar-chat-favourites__container" @click="onFavouriteClick">
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
	      return user.bot === true;
	    },
	    previewDescription() {
	      if (this.dialog.description.length === 0) {
	        return this.chatTypeText;
	      }
	      if (this.dialog.description.length > MAX_DESCRIPTION_SYMBOLS) {
	        return `${this.dialog.description.slice(0, MAX_DESCRIPTION_SYMBOLS)}...`;
	      }
	      return this.dialog.description;
	    },
	    descriptionToShow() {
	      const rawText = this.expanded ? this.dialog.description : this.previewDescription;
	      return im_v2_lib_parser.Parser.purifyText(rawText);
	    },
	    chatTypeText() {
	      if (this.isBot) {
	        return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_BOT');
	      }
	      if (this.isUser) {
	        return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_USER');
	      }
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
	    },
	    showExpandButton() {
	      if (this.expanded) {
	        return false;
	      }
	      return this.dialog.description.length >= MAX_DESCRIPTION_SYMBOLS;
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
				<div class="bx-im-sidebar-chat-description__text">
					{{ descriptionToShow }}
				</div>
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
	    Avatar: im_v2_component_elements.Avatar,
	    AvatarSize: im_v2_component_elements.AvatarSize
	  },
	  props: {
	    task: {
	      type: Object,
	      required: true
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
	      return this.taskItem.task.title;
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
				<div class="bx-im-sidebar-task-item__header-text" :title="taskTitle">
					{{ taskTitle }}
				</div>
				<div class="bx-im-sidebar-task-item__detail-container">
					<Avatar :size="AvatarSize.XS" :dialogId="taskAuthorDialogId" />
					<div class="bx-im-sidebar-task-item__forward-small-icon bx-im-sidebar__forward-small-icon"></div>
					<Avatar :size="AvatarSize.XS" :dialogId="taskResponsibleDialogId" />
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
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
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
	      if (!this.entityCreator) {
	        this.entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	      }
	      return this.entityCreator;
	    },
	    onAddClick() {
	      this.getEntityCreator().createTaskForChat();
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
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_TASK_DETAIL_TITLE') }}
						</span>
						<div v-if="firstTask" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-task-preview__title-button"
						/>
					</transition>
				</div>
				<TaskItem v-if="firstTask" :task="firstTask" @contextMenuClick="onContextMenuClick"/>
				<DetailEmptyState 
					v-else 
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
			</div>
		</div>
	`
	};

	class MainMenu extends im_v2_lib_menu.RecentMenu {
	  constructor() {
	    super();
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
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getCallItem(), this.getOpenProfileItem(), this.getOpenUserCalendarItem(), this.getChatsWithUserItem(), this.getAddMembersToChatItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getOpenUserCalendarItem() {
	    const isUser = this.store.getters['chats/isUser'](this.context.dialogId);
	    if (!isUser) {
	      return null;
	    }
	    const user = this.store.getters['users/get'](this.context.dialogId, true);
	    if (user.bot) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getCalendarLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR'),
	      onclick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getAddMembersToChatItem() {
	    const user = this.store.getters['users/get'](this.context.dialogId);
	    if ((user == null ? void 0 : user.bot) === true) {
	      return null;
	    }
	    const canExtend = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.extend, this.context.dialogId);
	    if (!canExtend) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS'),
	      onclick: () => {
	        this.emit(MainMenu.events.onAddToChatShow);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getJoinChatItem() {
	    const dialog = this.store.getters['chats/get'](this.context.dialogId);
	    const isUser = dialog.type === im_v2_const.ChatType.user;
	    if (isUser) {
	      return null;
	    }

	    // todo: check if user is in chat already

	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_JOIN_CHAT'),
	      onclick: () => {
	        console.warn('sidebar menu: join chat is not implemented');
	        this.menuInstance.close();
	      }
	    };
	  }
	  canShowFullMenu(dialogId) {
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    return Boolean(recentItem);
	  }
	}
	MainMenu.events = {
	  onAddToChatShow: 'onAddToChatShow'
	};

	// @vue/component
	const MainHeader = {
	  name: 'MainHeader',
	  components: {
	    AddToChat: im_v2_component_entitySelector.AddToChat
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
				<div class="bx-im-sidebar-header__title">{{ loc('IM_SIDEBAR_HEADER_TITLE') }}</div>
			</div>
			<button
				class="bx-im-sidebar-header__context-menu-icon bx-im-messenger__context-menu-icon"
				@click="onContextMenuClick"
				ref="context-menu"
			></button>
			<AddToChat
				:bindElement="$refs['context-menu'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
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
	      return this.meetingItem.meeting.title;
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
					<div class="bx-im-sidebar-meeting-item__title" :title="title">{{ title }}</div>
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
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
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
	      if (!this.entityCreator) {
	        this.entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	      }
	      return this.entityCreator;
	    },
	    onAddClick() {
	      this.getEntityCreator().createMeetingForChat();
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
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MEETING_DETAIL_TITLE') }}
						</span>
						<div v-if="firstMeeting" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
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
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
					:iconType="SidebarDetailBlock.meeting"
				/>
			</div>
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
	  data() {
	    return {
	      autoDeleteEnabled: false
	    };
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
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.mute, this.dialogId);
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
			:class="[canBeMuted ? '' : '--not-active']"
			v-hint="hintMuteNotAvailable"
		>
			<div class="bx-im-sidebar-mute-chat__title">
				<div class="bx-im-sidebar-mute-chat__title-text bx-im-sidebar-mute-chat__icon">
					{{ loc('IM_SIDEBAR_ENABLE_NOTIFICATION_TITLE_2') }}
				</div>
				<Toggle :size="ToggleSize.M" :isEnabled="!isChatMuted" @change="muteActionHandler" />
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
	    Avatar: im_v2_component_elements.Avatar,
	    MessengerButton: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat
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
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.userList, this.dialogId);
	    },
	    canInviteMembers() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.extend, this.dialogId);
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
	    addUsersButtonColor() {
	      if (this.isCopilotLayout) {
	        return this.ButtonColor.Copilot;
	      }
	      return this.ButtonColor.PrimaryLight;
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
	      this.showAddToChatPopup = true;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-chat-members-avatars__container">
			<div v-if="canSeeMembers" class="bx-im-sidebar-chat-members-avatars__members" @click="onOpenUsers">
				<div class="bx-im-sidebar-chat-members-avatars__avatars" >
					<Avatar
						class="bx-im-sidebar-chat-members-avatars__avatar"
						v-for="id in dialogIds"
						:size="AvatarSize.S"
						:dialogId="id"
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
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
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
	    Avatar: im_v2_component_elements.Avatar,
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
					<Avatar :size="AvatarSize.XXXL" :dialogId="dialogId" />
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
	const UserPreview = {
	  name: 'UserPreview',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
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
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.extend, this.dialogId);
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
	      return this.user.bot === true;
	    }
	  },
	  methods: {
	    onAddClick() {
	      this.showAddToChatPopup = true;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-personal-chat__avatar-container">
				<Avatar
					:size="AvatarSize.XXXL"
					:dialogId="dialogId"
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
				:bindElement="$refs['add-members'] || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -320}"
				@close="showAddToChatPopup = false"
			/>
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
	    UserPreview,
	    InfoPreview,
	    FilePreview,
	    TaskPreview,
	    MeetingPreview,
	    FileUnsortedPreview: FilePreview,
	    MarketPreview,
	    SidebarSkeleton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: false
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
	    chatId(newValue) {
	      if (newValue > 0) {
	        this.initializeSidebar();
	      }
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
			<SidebarSkeleton v-if="isLoading" />
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
	    ChatButton: im_v2_component_elements.Button
	  },
	  props: {
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
	    }
	  },
	  emits: ['back', 'addClick'],
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				></button>
				<div class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<div v-if="withAddButton" class="bx-im-sidebar-detail-header__add-button" ref="add-button">
					<ChatButton
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="$emit('addClick', {target: $refs['add-button']})"
					/>
				</div>
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

	// @vue/component
	const TaskPanel = {
	  name: 'TaskPanel',
	  components: {
	    TaskItem,
	    DateGroup,
	    DetailHeader,
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
	      isLoading: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    tasks() {
	      return this.$store.getters['sidebar/tasks/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.tasks);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new TaskMenu();
	    this.service = new Task({
	      dialogId: this.dialogId
	    });
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
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
	      const hasNextPage = this.$store.getters['sidebar/tasks/hasNextPage'](this.chatId);
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
	    onAddClick() {
	      new im_v2_lib_entityCreator.EntityCreator(this.chatId).createTaskForChat();
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-task-detail__scope">
			<DetailHeader
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="true"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-task-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-task-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<TaskItem
						v-for="task in dateGroup.items"
						:task="task"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
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

	// @vue/component
	const MediaDetailItem = {
	  name: 'MediaDetailItem',
	  components: {
	    SocialVideo: ui_vue3_components_socialvideo.SocialVideo,
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    fileItem: {
	      type: Object,
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
					<Avatar :dialogId="sidebarFileItem.authorId" :size="AvatarSize.S"></Avatar>
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
	  saveOnDisk(fileId) {
	    return this.diskService.save(fileId);
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
	        this.mediaManager.saveOnDisk(this.context.sidebarFile.fileId).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_SIDEBAR_FILE_SAVE_ON_DISK_SUCCESS')
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

	// @vue/component
	const MediaTab = {
	  name: 'MediaTab',
	  components: {
	    DateGroup,
	    MediaDetailItem,
	    DetailEmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isLoading: false
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
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.media);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.files);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    }
	  },
	  created() {
	    this.service = new File({
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
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.media);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage(im_v2_const.SidebarFileTypes.media);
	      this.isLoading = false;
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
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
			</div>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.media"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const AudioDetailItem = {
	  name: 'AudioDetailItem',
	  components: {
	    AudioPlayer: im_v2_component_elements.AudioPlayer,
	    Avatar: im_v2_component_elements.Avatar
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
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
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
				:timelineType="timelineType" 
				:authorId="sidebarFileItem.authorId" 
				@contextMenuClick="onContextMenuClick"
			/>
		</div>
	`
	};

	// @vue/component
	const AudioTab = {
	  name: 'AudioTab',
	  components: {
	    DetailEmptyState,
	    AudioDetailItem,
	    DateGroup,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
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
	    }
	  },
	  created() {
	    this.service = new File({
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
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.audio);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage(im_v2_const.SidebarFileTypes.audio);
	      this.isLoading = false;
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
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.audio"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const BriefItem = {
	  name: 'BriefItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    brief: {
	      type: Object,
	      required: true
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
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
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
						<span class="bx-im-sidebar-brief-item__title-text" :title="file.name">{{fileShortName}}</span>
						<span class="bx-im-sidebar-brief-item__size-text">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-brief-item__author-container">
						<Avatar 
							:dialogId="sidebarFileItem.authorId" 
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

	// @vue/component
	const BriefTab = {
	  name: 'BriefTab',
	  components: {
	    DateGroup,
	    BriefItem,
	    DetailEmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
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
	    }
	  },
	  created() {
	    this.service = new File({
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
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.brief);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage(im_v2_const.SidebarFileTypes.brief);
	      this.isLoading = false;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-brief-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-brief-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle"/>
				<BriefItem
					v-for="file in dateGroup.items"
					:brief="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEFS_EMPTY')"
				:iconType="SidebarDetailBlock.brief"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const DocumentDetailItem = {
	  name: 'DocumentDetailItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    fileItem: {
	      type: Object,
	      required: true
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
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
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
						<span class="bx-im-sidebar-file-document-detail-item__document-title-text">{{fileShortName}}</span>
						<span class="bx-im-sidebar-file-document-detail-item__document-size">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-file-document-detail-item__author-container">
						<template v-if="authorId > 0">
							<Avatar
								:dialogId="authorId"
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

	// @vue/component
	const OtherTab = {
	  name: 'OtherTab',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
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
	    }
	  },
	  created() {
	    this.service = new File({
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
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.other);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage(im_v2_const.SidebarFileTypes.other);
	      this.isLoading = false;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-other-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-other-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.other"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

	// @vue/component
	const DocumentTab = {
	  name: 'DocumentTab',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
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
	    }
	  },
	  created() {
	    this.service = new File({
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
	      const hasNextPage = this.$store.getters['sidebar/files/hasNextPage'](this.chatId, im_v2_const.SidebarFileTypes.document);
	      return isAtThreshold && hasNextPage;
	    },
	    async onScroll(event) {
	      this.contextMenu.destroy();
	      if (this.isLoading || !this.needToLoadNextPage(event)) {
	        return;
	      }
	      this.isLoading = true;
	      await this.service.loadNextPage(im_v2_const.SidebarFileTypes.document);
	      this.isLoading = false;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-document-detail__scope bx-im-sidebar-detail__container" @scroll="onScroll">
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-file-document-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<DetailEmptyState
				v-if="!isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.document"
			/>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
		</div>
	`
	};

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
	    OtherTab
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
	      tab: im_v2_const.SidebarFileTabTypes.media
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    tabComponentName() {
	      return `${main_core.Text.capitalize(this.tab)}Tab`;
	    },
	    tabs() {
	      const tabTypes = Object.values(im_v2_const.SidebarFileTabTypes);
	      const settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	      const canShowBriefs = settings.get('canShowBriefs', false);
	      if (!canShowBriefs) {
	        return tabTypes.filter(tab => tab !== im_v2_const.SidebarDetailBlock.brief);
	      }
	      return tabTypes;
	    }
	  },
	  methods: {
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	        panel: im_v2_const.SidebarDetailBlock.file
	      });
	    },
	    onTabSelect(tabName) {
	      this.tab = tabName;
	    }
	  },
	  template: `
		<div>
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<DetailTabs :tabs="tabs" @tabSelect="onTabSelect" />
			<KeepAlive>
				<component :is="tabComponentName" :dialogId="dialogId" />
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
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
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
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    link: {
	      type: Object,
	      required: true
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
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(descriptionToShow);
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
				<a :href="source" :title="description" target="_blank" class="bx-im-link-item__description-text">
					{{ description }}
				</a>
				<div class="bx-im-link-item__author-container">
					<Avatar 
						:size="AvatarSize.XS"
						:dialogId="authorDialogId" 
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
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FROM_LINKS'),
	      onclick: function () {
	        this.linkManager.delete(this.context);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	}

	// @vue/component
	const LinkPanel = {
	  name: 'LinkPanel',
	  components: {
	    DetailHeader,
	    LinkItem,
	    DateGroup,
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
	      isLoading: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    links() {
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new LinkMenu();
	    this.service = new Link({
	      dialogId: this.dialogId
	    });
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	    this.collectionFormatter.destroy();
	  },
	  methods: {
	    onContextMenuClick(event) {
	      const item = {
	        id: event.id,
	        messageId: event.messageId,
	        dialogId: this.dialogId,
	        chatId: this.chatId,
	        source: event.source
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
	      const hasNextPage = this.$store.getters['sidebar/links/hasNextPage'](this.chatId);
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
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-link-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-link-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<template v-for="link in dateGroup.items">
						<LinkItem :link="link" @contextMenuClick="onContextMenuClick" />
					</template>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_EMPTY')"
					:iconType="SidebarDetailBlock.link"
				/>
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

	// @vue/component
	const MeetingPanel = {
	  name: 'MeetingPanel',
	  components: {
	    MeetingItem,
	    DateGroup,
	    DetailEmptyState,
	    DetailHeader,
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
	      isLoading: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    meetings() {
	      return this.$store.getters['sidebar/meetings/get'](this.chatId);
	    },
	    formattedCollection() {
	      return this.collectionFormatter.format(this.meetings);
	    },
	    isEmptyState() {
	      return this.formattedCollection.length === 0;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new MeetingMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
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
	      const hasNextPage = this.$store.getters['sidebar/meetings/hasNextPage'](this.chatId);
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
	    onAddClick() {
	      new im_v2_lib_entityCreator.EntityCreator(this.chatId).createMeetingForChat();
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-meeting-detail__scope">
			<DetailHeader
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETING_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="true"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-meeting-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-meeting-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<MeetingItem
						v-for="meeting in dateGroup.items"
						:meeting="meeting"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
					:iconType="SidebarDetailBlock.meeting"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	// @vue/component
	const DetailUser = {
	  name: 'DetailUser',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    isOwner: {
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
	      const authorId = Number.parseInt(this.dialogId, 10);
	      const copilotUserId = this.$store.getters['users/bots/getCopilotUserId'];
	      return copilotUserId === authorId;
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
				<Avatar :size="AvatarSize.L" :dialogId="dialogId" />
				<span v-if="isOwner" class="bx-im-sidebar-main-detail__avatar-owner-icon"></span>
			</div>
			<div class="bx-im-sidebar-main-detail__user-info-container">
				<div class="bx-im-sidebar-main-detail__user-title-container">
					<a v-if="hasLink" :href="userLink" target="_blank" class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" />
					</a>
					<div v-else class="bx-im-sidebar-main-detail__user-title-link">
						<ChatTitle :dialogId="dialogId" />
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

	class MembersMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.chatService = new im_v2_provider_service.ChatService();
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuItems() {
	    return [this.getInsertNameItem(), this.getSendMessageItem(), this.getCallItem(), this.getOpenProfileItem(), this.getOpenUserCalendarItem(), this.getKickItem(), this.getLeaveItem()];
	  }
	  getInsertNameItem() {
	    const user = this.store.getters['users/get'](this.context.dialogId, true);
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_INSERT_NAME'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.context.dialogId, user.name)
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getSendMessageItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_WRITE'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCallItem() {
	    const chatCanBeCalled = this.callManager.chatCanBeCalled(this.context.dialogId);
	    const chatIsAllowedToCall = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.call, this.context.dialogId);
	    if (!chatCanBeCalled || !chatIsAllowedToCall) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL_2'),
	      onclick: () => {
	        this.callManager.startCall(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenProfileItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getProfileLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE'),
	      href: profileUri,
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenUserCalendarItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getCalendarLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR'),
	      onclick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getKickItem() {
	    const userIdToKick = Number.parseInt(this.context.dialogId, 10);
	    const isSelfKick = userIdToKick === this.getCurrentUserId();
	    const canKick = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.kick, this.context.contextDialogId);
	    if (isSelfKick || !canKick) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_KICK_FROM_CHAT'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showKickUserConfirm();
	        if (userChoice === true) {
	          this.chatService.kickUserFromChat(this.context.contextDialogId, this.context.dialogId);
	        }
	      }
	    };
	  }
	  getLeaveItem() {
	    const userIdToKick = Number.parseInt(this.context.dialogId, 10);
	    const isSelfKick = userIdToKick === this.getCurrentUserId();
	    const canLeaveChat = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.leave, this.context.contextDialogId);
	    if (!isSelfKick || !canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showLeaveFromChatConfirm();
	        if (userChoice === true) {
	          this.chatService.leaveChat(this.context.contextDialogId);
	        }
	      }
	    };
	  }
	  isUser() {
	    return this.store.getters['chats/isUser'](this.context.dialogId);
	  }
	  isBot() {
	    if (!this.isUser()) {
	      return false;
	    }
	    const user = this.store.getters['users/get'](this.context.dialogId);
	    return user.bot === true;
	  }
	}

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
	    dialogIds() {
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
	      let usersInChatCount = this.dialog.userCounter;
	      if (usersInChatCount >= 1000) {
	        usersInChatCount = `${Math.floor(usersInChatCount / 1000)}k`;
	      }
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MEMBERS_DETAIL_TITLE').replace('#NUMBER#', usersInChatCount);
	    },
	    needAddButton() {
	      if (this.isCopilotLayout && !this.isAddToCopilotChatAvailable) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.extend, this.dialogId);
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
	    },
	    isAddToCopilotChatAvailable() {
	      const settings = main_core.Extension.getSettings('im.v2.component.content.copilot');
	      return settings.isAddToChatAvailable === 'Y';
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
	    onContextMenuClick(event) {
	      const item = {
	        dialogId: event.userDialogId,
	        contextDialogId: this.dialogId
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onCopyInviteClick() {
	      if (BX.clipboard.copy(this.chatLink)) {
	        BX.UI.Notification.Center.notify({
	          content: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS')
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
	      this.showAddToChatPopup = true;
	      this.showAddToChatTarget = event.target;
	    },
	    loc(key) {
	      return this.$Bitrix.Loc.getMessage(key);
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
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div class="bx-im-sidebar-main-detail__invitation-button-container">
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
					v-for="dialogId in dialogIds"
					:dialogId="dialogId"
					:isOwner="isOwner(dialogId)"
					@contextMenuClick="onContextMenuClick"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
			<AddToChat
				:bindElement="showAddToChatTarget || {}"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
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

	// @vue/component
	const FavoriteItem = {
	  name: 'FavoriteItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
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
	      return im_v2_lib_parser.Parser.purifyMessage(this.favoriteMessage);
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
	    },
	    onMessageBodyClick(event) {
	      if (event.target.tagName === 'A') {
	        event.stopPropagation();
	      }
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
					<Avatar
						:size="AvatarSize.XS"
						:dialogId="authorDialogId"
						class="bx-im-favorite-item__author-avatar"
					/>
					<ChatTitle :dialogId="authorDialogId" :showItsYou="false" class="bx-im-favorite-item__author-text" />
				</div>
				<button 
					v-if="showContextButton"
					class="bx-im-messenger__context-menu-icon"
					@click.stop="onContextMenuClick"
				></button>
			</div>
			<div class="bx-im-favorite-item__message-text" v-html="messageText" @click="onMessageBodyClick"></div>
		</div>
	`
	};

	// @vue/component
	const FavoritePanel = {
	  name: 'FavoritePanel',
	  components: {
	    FavoriteItem,
	    DateGroup,
	    DetailEmptyState,
	    DetailHeader,
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
	      isLoading: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    favorites() {
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FavoriteMenu();
	    this.service = new Favorite({
	      dialogId: this.dialogId
	    });
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	    this.collectionFormatter.destroy();
	  },
	  methods: {
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
	      const hasNextPage = this.$store.getters['sidebar/favorites/hasNextPage'](this.chatId);
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
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITE_DETAIL_TITLE')"
				:secondLevel="secondLevel"
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
						@contextMenuClick="onContextMenuClick" 
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITES_EMPTY')"
					:iconType="SidebarDetailBlock.favorite"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`
	};

	const REQUEST_ITEMS_LIMIT$7 = 50;
	var _lastMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastMessageId");
	var _query = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _request = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("request");
	var _processSearchResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processSearchResponse");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	class MessageSearch {
	  // eslint-disable-next-line no-unused-private-class-members

	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _processSearchResponse, {
	      value: _processSearchResponse2
	    });
	    Object.defineProperty(this, _request, {
	      value: _request2
	    });
	    this.hasMoreItemsToLoad = true;
	    Object.defineProperty(this, _lastMessageId, {
	      writable: true,
	      value: 0
	    });
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
	    babelHelpers.classPrivateFieldLooseBase(this, _query)[_query] = '';
	    this.hasMoreItemsToLoad = true;
	  }
	}
	function _request2() {
	  const config = {
	    SEARCH_MESSAGE: babelHelpers.classPrivateFieldLooseBase(this, _query)[_query],
	    CHAT_ID: this.chatId
	  };
	  if (babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] > 0) {
	    config.LAST_ID = babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId];
	  }
	  return new Promise((resolve, reject) => {
	    this.restClient.callMethod(im_v2_const.RestMethod.imDialogMessagesSearch, config).then(response => {
	      const responseData = response.data();
	      resolve(babelHelpers.classPrivateFieldLooseBase(this, _processSearchResponse)[_processSearchResponse](responseData));
	    }).catch(error => reject(error));
	  });
	}
	function _processSearchResponse2(response) {
	  babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] = getLastElementId(response.messages);
	  if (response.messages.length < REQUEST_ITEMS_LIMIT$7) {
	    this.hasMoreItemsToLoad = false;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](response).then(() => {
	    return response.messages.map(message => message.id);
	  });
	}
	function _updateModels2(rawData) {
	  const {
	    files,
	    users,
	    usersShort,
	    reactions,
	    additionalMessages,
	    messages
	  } = rawData;
	  const usersPromise = Promise.all([this.userManager.setUsersToModel(users), this.userManager.addUsersToModel(usersShort)]);
	  const filesPromise = this.store.dispatch('files/set', files);
	  const reactionsPromise = this.store.dispatch('messages/reactions/set', reactions);
	  const additionalMessagesPromise = this.store.dispatch('messages/store', additionalMessages);
	  const messagesPromise = this.store.dispatch('messages/store', messages);
	  return Promise.all([filesPromise, usersPromise, reactionsPromise, additionalMessagesPromise, messagesPromise]);
	}

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  computed: {
	    title() {
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND');
	    },
	    subTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION');
	    }
	  },
	  template: `
		<div class="bx-im-message-search-empty-state__container bx-im-message-search-empty-state__scope">
			<div class="bx-im-message-search-empty-state__icon"></div>
			<div class="bx-im-message-search-empty-state__title">
				{{ title }}
			</div>
			<div class="bx-im-message-search-empty-state__subtitle">
				{{ subTitle }}
			</div>
		</div>
	`
	};

	// @vue/component
	const SearchItem = {
	  name: 'SearchItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
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
						<Avatar
							:size="AvatarSize.XS"
							:dialogId="authorDialogId"
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
	    EmptyState,
	    SearchItem,
	    Loader: im_v2_component_elements.Loader,
	    StartState: DetailEmptyState,
	    SearchHeader
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
	    }
	  },
	  template: `
		<div class="bx-im-message-search-detail__scope">
			<SearchHeader :secondLevel="secondLevel" @changeQuery="onChangeQuery" @back="onClickBack" />
			<div class="bx-im-message-search-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<StartState 
					v-if="!isLoading && preparedQuery.length === 0"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_SEARCH_MESSAGE_START_TITLE')"
					:iconType="SidebarDetailBlock.messageSearch"
				/>
				<EmptyState v-if="!isLoading && isEmptyState" />
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
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatItem = {
	  name: 'ChatItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
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
	    chatItemText() {
	      return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
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
				<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
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

	const REQUEST_ITEMS_LIMIT$8 = 50;
	var _chatsCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatsCount");
	var _getRequestParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRequestParams");
	var _requestPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestPage");
	var _handleResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleResponse");
	var _updateModels$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _setDialoguesPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialoguesPromise");
	class ChatsWithUser {
	  constructor({
	    dialogId
	  }) {
	    Object.defineProperty(this, _setDialoguesPromise, {
	      value: _setDialoguesPromise2
	    });
	    Object.defineProperty(this, _updateModels$1, {
	      value: _updateModels2$1
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
	    limit: REQUEST_ITEMS_LIMIT$8
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
	  if (chats.length < REQUEST_ITEMS_LIMIT$8) {
	    this.hasMoreItemsToLoad = false;
	  }
	  await babelHelpers.classPrivateFieldLooseBase(this, _updateModels$1)[_updateModels$1](chats);
	  return chats.map(chat => {
	    return {
	      dialogId: chat.dialogId,
	      dateMessage: chat.dateMessage
	    };
	  });
	}
	function _updateModels2$1(chats) {
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
				:title="loc('IM_SIDEBAR_CHATSWITHUSER_DETAIL_TITLE')"
				:dialogId="dialogId"
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
	    FileUnsortedPanel
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
	    },
	    isCopilotLayout() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.copilot.name;
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
	      const {
	        panel = '',
	        standalone = false,
	        dialogId,
	        entityId = ''
	      } = event.getData();
	      const needToCloseSecondLevelPanel = panel && this.secondLevelPanelType === panel;
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
	      if (!sidebarOpenState || this.isCopilotLayout) {
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

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.UI,BX.Messenger.v2.Lib,BX.Main,BX.Vue3.Directives,BX.Vue3.Components,BX.UI.Viewer,BX,BX.Messenger.v2.Model,BX,BX,BX.Vue3.Vuex,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Application));
//# sourceMappingURL=sidebar.bundle.js.map
