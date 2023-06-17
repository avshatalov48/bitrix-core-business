this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_logger,im_v2_lib_user,im_v2_lib_call,im_public,ui_vue3_directives_hint,im_v2_provider_service,im_v2_lib_parser,ui_vue3_components_socialvideo,ui_vue3,ui_vue3_components_audioplayer,ui_viewer,ui_label,ui_icons,im_v2_model,ui_notification,rest_client,ui_vue3_vuex,im_v2_application_core,main_date,im_v2_lib_dateFormatter,im_v2_lib_market,main_core_events,im_v2_lib_menu,im_v2_lib_utils,im_v2_component_elements,im_v2_const,im_v2_lib_entityCreator,im_v2_component_entitySelector,main_core) {
	'use strict';

	const NOT_IMPLEMENTED_ERROR = 'Not implemented';
	class Base {
	  constructor(chatId, dialogId) {
	    this.store = null;
	    this.dialogId = '';
	    this.chatId = 0;
	    this.userManager = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.chatId = chatId;
	    this.dialogId = dialogId;
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  loadFirstPage() {
	    throw new Error(NOT_IMPLEMENTED_ERROR);
	  }
	  loadNextPage() {
	    throw new Error(NOT_IMPLEMENTED_ERROR);
	  }
	  getInitialRequest() {
	    throw new Error(NOT_IMPLEMENTED_ERROR);
	  }
	  getResponseHandler() {
	    throw new Error(NOT_IMPLEMENTED_ERROR);
	  }
	  getCurrentUserId() {
	    return im_v2_application_core.Core.getUserId();
	  }
	  getLastElementId(collection) {
	    var _collection;
	    const lastId = (_collection = collection[collection.length - 1]) == null ? void 0 : _collection.id;
	    if (main_core.Type.isNumber(lastId)) {
	      return lastId;
	    }
	    return null;
	  }
	}

	const REQUEST_ITEMS_LIMIT = 50;
	class BaseFile extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	    this.lastId = 0;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imChatFileCollectionGet]: [im_v2_const.RestMethod.imChatFileCollectionGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadFileError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      const fileResult = response[im_v2_const.RestMethod.imChatFileCollectionGet].data();
	      return this.updateModels(fileResult);
	    };
	  }
	  extractLoadFileError(response) {
	    const fileGetResponse = response[im_v2_const.RestMethod.imChatFileCollectionGet];
	    if (fileGetResponse != null && fileGetResponse.error()) {
	      return `Sidebar service error: ${im_v2_const.RestMethod.imChatFileCollectionGet}: ${fileGetResponse == null ? void 0 : fileGetResponse.error()}`;
	    }
	    return null;
	  }
	  handleResponse(response) {
	    const fileResult = response.data();
	    if (fileResult.list.length < REQUEST_ITEMS_LIMIT) {
	      this.hasMoreItemsToLoad = false;
	    }
	    this.lastId = this.getLastElementId(fileResult.list);
	    return this.updateModels(fileResult);
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users,
	      files
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const setSidebarFilesPromise = this.store.dispatch('sidebar/files/set', {
	      chatId: this.chatId,
	      files: list
	    });
	    return Promise.all([setFilesPromise, setSidebarFilesPromise, addUsersPromise]);
	  }
	  loadFirstPageBySubType(subType) {
	    const filesCount = this.getFilesCountFromModel(subType);
	    if (filesCount > REQUEST_ITEMS_LIMIT) {
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
	      'CHAT_ID': this.chatId,
	      'SUBTYPE': subType,
	      'LIMIT': REQUEST_ITEMS_LIMIT
	    };
	    if (this.lastId > 0) {
	      queryParams['LAST_ID'] = this.lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatFileGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: imChatFileGet: page request error', error);
	    });
	  }
	  getFilesCountFromModel(subType) {
	    return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	  }
	}

	class Media extends BaseFile {
	  loadFirstPage() {
	    return this.loadFirstPageBySubType(im_v2_const.SidebarFileTabTypes.media);
	  }
	  loadNextPage() {
	    return this.loadNextPageBySubType(im_v2_const.SidebarFileTabTypes.media);
	  }
	}

	class Audio extends BaseFile {
	  loadFirstPage() {
	    return this.loadFirstPageBySubType(im_v2_const.SidebarFileTabTypes.audio);
	  }
	  loadNextPage() {
	    return this.loadNextPageBySubType(im_v2_const.SidebarFileTabTypes.audio);
	  }
	}

	class Document extends BaseFile {
	  loadFirstPage() {
	    return this.loadFirstPageBySubType(im_v2_const.SidebarFileTabTypes.document);
	  }
	  loadNextPage() {
	    return this.loadNextPageBySubType(im_v2_const.SidebarFileTabTypes.document);
	  }
	}

	class Other extends BaseFile {
	  loadFirstPage() {
	    return this.loadFirstPageBySubType(im_v2_const.SidebarFileTabTypes.other);
	  }
	  loadNextPage() {
	    return this.loadNextPageBySubType(im_v2_const.SidebarFileTabTypes.other);
	  }
	}

	const REQUEST_ITEMS_LIMIT$1 = 50;
	class Favorite extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	    this.lastId = 0;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imChatFavoriteCounterGet]: [im_v2_const.RestMethod.imChatFavoriteCounterGet, {
	        chat_id: this.chatId
	      }],
	      [im_v2_const.RestMethod.imChatFavoriteGet]: [im_v2_const.RestMethod.imChatFavoriteGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$1
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadCountersError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      const favoriteCounterGetResponse = response[im_v2_const.RestMethod.imChatFavoriteCounterGet].data();
	      const setCounterResult = this.store.dispatch('sidebar/favorites/setCounter', {
	        chatId: this.chatId,
	        counter: favoriteCounterGetResponse.counter
	      });
	      const setFavoriteResult = this.handleResponse(response[im_v2_const.RestMethod.imChatFavoriteGet]);
	      return Promise.all([setCounterResult, setFavoriteResult]);
	    };
	  }
	  extractLoadCountersError(response) {
	    const favoriteCounterGetResult = response[im_v2_const.RestMethod.imChatFavoriteCounterGet];
	    if (favoriteCounterGetResult != null && favoriteCounterGetResult.error()) {
	      return `SidebarInfo service error: ${im_v2_const.RestMethod.imChatFavoriteCounterGet}: ${favoriteCounterGetResult == null ? void 0 : favoriteCounterGetResult.error()}`;
	    }
	    const favoriteGetResult = response[im_v2_const.RestMethod.imChatFavoriteGet];
	    if (favoriteGetResult != null && favoriteGetResult.error()) {
	      return `SidebarInfo service error: ${im_v2_const.RestMethod.imChatFavoriteGet}: ${favoriteGetResult == null ? void 0 : favoriteGetResult.error()}`;
	    }
	    return null;
	  }
	  loadFirstPage() {
	    const favoritesCount = this.getFavoritesCountFromModel();
	    if (favoritesCount > REQUEST_ITEMS_LIMIT$1) {
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
	      'CHAT_ID': this.chatId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$1
	    };
	    if (this.lastId > 0) {
	      queryParams.LAST_ID = this.lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatFavoriteGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    const favoriteMessagesResult = response.data();
	    if (favoriteMessagesResult.list.length < REQUEST_ITEMS_LIMIT$1) {
	      this.hasMoreItemsToLoad = false;
	    }
	    const lastId = this.getLastElementId(favoriteMessagesResult.list);
	    if (lastId) {
	      this.lastId = lastId;
	    }
	    return this.updateModels(favoriteMessagesResult);
	  }
	  updateModels(resultData) {
	    const {
	      list = [],
	      users = [],
	      files = []
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const rawMessages = list.map(favorite => favorite.message);
	    const setFilesPromise = this.store.dispatch('files/set', files);
	    const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
	    const setFavoritesPromise = this.store.dispatch('sidebar/favorites/set', {
	      chatId: this.chatId,
	      favorites: list
	    });
	    return Promise.all([setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise]);
	  }
	  getFavoritesCountFromModel() {
	    return this.store.getters['sidebar/favorites/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$2 = 50;
	class Link extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imChatUrlCounterGet]: [im_v2_const.RestMethod.imChatUrlCounterGet, {
	        chat_id: this.chatId
	      }],
	      [im_v2_const.RestMethod.imChatUrlGet]: [im_v2_const.RestMethod.imChatUrlGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$2
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadCountersError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      const linkCounterGetResponse = response[im_v2_const.RestMethod.imChatUrlCounterGet].data();
	      const setCounterResult = this.store.dispatch('sidebar/links/setCounter', {
	        chatId: this.chatId,
	        counter: linkCounterGetResponse.counter
	      });
	      const setLinksResult = this.handleResponse(response[im_v2_const.RestMethod.imChatUrlGet]);
	      return Promise.all([setCounterResult, setLinksResult]);
	    };
	  }
	  extractLoadCountersError(response) {
	    const linkCounterGetResult = response[im_v2_const.RestMethod.imChatUrlCounterGet];
	    if (linkCounterGetResult != null && linkCounterGetResult.error()) {
	      return `SidebarInfo service error: ${im_v2_const.RestMethod.imChatUrlCounterGet}: ${linkCounterGetResult == null ? void 0 : linkCounterGetResult.error()}`;
	    }
	    const linkGetResult = response[im_v2_const.RestMethod.imChatUrlGet];
	    if (linkGetResult != null && linkGetResult.error()) {
	      return `SidebarInfo service error: ${im_v2_const.RestMethod.imChatUrlGet}: ${linkGetResult == null ? void 0 : linkGetResult.error()}`;
	    }
	    return null;
	  }
	  loadFirstPage() {
	    const linksCount = this.getLinksCountFromModel();
	    if (linksCount > REQUEST_ITEMS_LIMIT$2) {
	      return Promise.resolve();
	    }
	    const queryParams = this.getQueryParams(linksCount);
	    return this.requestPage(queryParams);
	  }
	  loadNextPage() {
	    const linksCount = this.getLinksCountFromModel();
	    const queryParams = this.getQueryParams(linksCount);
	    return this.requestPage(queryParams);
	  }
	  getQueryParams(offset = 0) {
	    const queryParams = {
	      'CHAT_ID': this.chatId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$2
	    };
	    if (main_core.Type.isNumber(offset) && offset > 0) {
	      queryParams.OFFSET = offset;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatUrlGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: Im.chatUrlList: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    const resultData = response.data();
	    if (resultData.list.length < REQUEST_ITEMS_LIMIT$2) {
	      this.hasMoreItemsToLoad = false;
	    }
	    return this.updateModels(resultData);
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setLinksPromise = this.store.dispatch('sidebar/links/set', {
	      chatId: this.chatId,
	      links: list
	    });
	    return Promise.all([setLinksPromise, addUsersPromise]);
	  }
	  getLinksCountFromModel() {
	    return this.store.getters['sidebar/links/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$3 = 50;
	class Task extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	    this.lastId = 0;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imChatTaskGet]: [im_v2_const.RestMethod.imChatTaskGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$3
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadTaskError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      return this.handleResponse(response[im_v2_const.RestMethod.imChatTaskGet]);
	    };
	  }
	  extractLoadTaskError(response) {
	    const taskGetResponse = response[im_v2_const.RestMethod.imChatTaskGet];
	    if (taskGetResponse != null && taskGetResponse.error()) {
	      return `Sidebar service error: ${im_v2_const.RestMethod.imChatTaskGet}: ${taskGetResponse == null ? void 0 : taskGetResponse.error()}`;
	    }
	    return null;
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
	      'CHAT_ID': this.chatId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$3
	    };
	    if (this.lastId > 0) {
	      queryParams['LAST_ID'] = this.lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatTaskGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    const tasksResult = response.data();
	    if (tasksResult.list.length < REQUEST_ITEMS_LIMIT$3) {
	      this.hasMoreItemsToLoad = false;
	    }
	    this.firstPageReceived = true;
	    this.lastId = this.getLastElementId(tasksResult.list);
	    return this.updateModels(tasksResult);
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setTasksPromise = this.store.dispatch('sidebar/tasks/set', {
	      chatId: this.chatId,
	      tasks: list
	    });
	    return Promise.all([setTasksPromise, addUsersPromise]);
	  }
	  getTasksCountFromModel() {
	    return this.store.getters['sidebar/tasks/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$4 = 50;
	class Main extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	    this.lastId = 0;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imDialogUsersList]: [im_v2_const.RestMethod.imDialogUsersList, {
	        dialog_id: this.dialogId,
	        limit: REQUEST_ITEMS_LIMIT$4
	      }]
	    };
	  }
	  loadFirstPage() {
	    const membersCount = this.getMembersCountFromModel();
	    if (membersCount > REQUEST_ITEMS_LIMIT$4) {
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
	      'DIALOG_ID': this.dialogId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$4,
	      'LAST_ID': this.lastId
	    };
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imDialogUsersList, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarMain: Im.DialogUsersList: page request error', error);
	    });
	  }
	  getResponseHandler() {
	    return response => {
	      return this.handleResponse(response[im_v2_const.RestMethod.imDialogUsersList]);
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
	      users: userIds
	    });
	    return Promise.all([addUsersPromise, setMembersPromise]);
	  }
	  getMembersCountFromModel() {
	    return this.store.getters['sidebar/members/getSize'](this.chatId);
	  }
	  handleResponse(response) {
	    const users = response.data();
	    if (users.length < REQUEST_ITEMS_LIMIT$4) {
	      this.hasMoreItemsToLoad = false;
	    }
	    const lastId = this.getLastElementId(users);
	    if (lastId) {
	      this.lastId = lastId;
	    }
	    return this.updateModels(users);
	  }
	}

	class Brief extends BaseFile {
	  loadFirstPage() {
	    return this.loadFirstPageBySubType(im_v2_const.SidebarFileTypes.brief);
	  }
	  loadNextPage() {
	    return this.loadNextPageBySubType(im_v2_const.SidebarFileTypes.brief);
	  }
	}

	const REQUEST_ITEMS_LIMIT$5 = 50;
	class Meeting extends Base {
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imChatCalendarGet]: [im_v2_const.RestMethod.imChatCalendarGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$5
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadTaskError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      return this.handleResponse(response[im_v2_const.RestMethod.imChatCalendarGet]);
	    };
	  }
	  extractLoadTaskError(response) {
	    const calendarGetResponse = response[im_v2_const.RestMethod.imChatCalendarGet];
	    if (calendarGetResponse != null && calendarGetResponse.error()) {
	      return `Sidebar service error: ${im_v2_const.RestMethod.imChatCalendarGet}: ${calendarGetResponse == null ? void 0 : calendarGetResponse.error()}`;
	    }
	    return null;
	  }
	  loadFirstPage() {
	    const meetingsCount = this.getMeetingsCountFromState();
	    if (meetingsCount > REQUEST_ITEMS_LIMIT$5) {
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
	      'CHAT_ID': this.chatId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$5
	    };
	    if (this.lastId > 0) {
	      queryParams['LAST_ID'] = this.lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imChatCalendarGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imChatCalendarGet: page request error', error);
	    });
	  }
	  updateModels(resultData) {
	    const {
	      list,
	      users
	    } = resultData;
	    const addUsersPromise = this.userManager.setUsersToModel(users);
	    const setMeetingsPromise = this.store.dispatch('sidebar/meetings/set', {
	      chatId: this.chatId,
	      meetings: list
	    });
	    return Promise.all([setMeetingsPromise, addUsersPromise]);
	  }
	  handleResponse(response) {
	    const meetingsResult = response.data();
	    if (meetingsResult.list.length < REQUEST_ITEMS_LIMIT$5) {
	      this.hasMoreItemsToLoad = false;
	    }
	    this.lastId = this.getLastElementId(meetingsResult.list);
	    return this.updateModels(meetingsResult);
	  }
	  getMeetingsCountFromState() {
	    return this.store.getters['sidebar/meetings/getSize'](this.chatId);
	  }
	}

	const REQUEST_ITEMS_LIMIT$6 = 50; // temporary value. Should be 50

	class FileUnsorted extends Base {
	  constructor(...args) {
	    super(...args);
	    this.hasMoreItemsToLoad = true;
	    this.lastId = 0;
	  }
	  getInitialRequest() {
	    return {
	      [im_v2_const.RestMethod.imDiskFolderListGet]: [im_v2_const.RestMethod.imDiskFolderListGet, {
	        chat_id: this.chatId,
	        limit: REQUEST_ITEMS_LIMIT$6
	      }]
	    };
	  }
	  getResponseHandler() {
	    return response => {
	      if (!response) {
	        return Promise.reject(new Error('SidebarInfo service error: no response'));
	      }
	      const requestError = this.extractLoadFileError(response);
	      if (requestError) {
	        return Promise.reject(new Error(requestError));
	      }
	      const fileResult = response[im_v2_const.RestMethod.imDiskFolderListGet].data();
	      return this.updateModels(fileResult);
	    };
	  }
	  extractLoadFileError(response) {
	    const diskFolderListGetResult = response[im_v2_const.RestMethod.imDiskFolderListGet];
	    if (diskFolderListGetResult != null && diskFolderListGetResult.error()) {
	      return `SidebarInfo service error: ${im_v2_const.RestMethod.imDiskFolderListGet}: ${diskFolderListGetResult == null ? void 0 : diskFolderListGetResult.error()}`;
	    }
	    return null;
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
	      'CHAT_ID': this.chatId,
	      'LIMIT': REQUEST_ITEMS_LIMIT$6
	    };
	    if (this.lastId > 0) {
	      queryParams.LAST_ID = this.lastId;
	    }
	    return queryParams;
	  }
	  requestPage(queryParams) {
	    return this.restClient.callMethod(im_v2_const.RestMethod.imDiskFolderListGet, queryParams).then(response => {
	      return this.handleResponse(response);
	    }).catch(error => {
	      console.error('SidebarInfo: Im.imDiskFolderListGet: page request error', error);
	    });
	  }
	  handleResponse(response) {
	    const diskFolderListGetResult = response.data();
	    if (diskFolderListGetResult.files.length < REQUEST_ITEMS_LIMIT$6) {
	      this.hasMoreItemsToLoad = false;
	    }
	    const lastId = this.getLastElementId(diskFolderListGetResult.files);
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
	      files: preparedFiles
	    });
	    return Promise.all([setFilesPromise, setSidebarFilesPromise, addUsersPromise]);
	  }
	  getFilesCountFromModel(subType) {
	    return this.store.getters['sidebar/files/getSize'](this.chatId, subType);
	  }
	}

	class SettingsManager {
	  constructor() {
	    this.store = null;
	    this.settings = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.settings = main_core.Extension.getSettings('im.v2.component.sidebar');
	    this.saveSettings();
	  }
	  saveSettings() {
	    this.store.dispatch('sidebar/setFilesMigrated', this.settings.get('filesMigrated', false));
	    this.store.dispatch('sidebar/setLinksMigrated', this.settings.get('linksAvailable', false));
	  }
	  getBlocks() {
	    return this.settings.get('blocks', []);
	  }
	  isLinksMigrationFinished() {
	    return this.store.state.sidebar.isLinksMigrated;
	  }
	  canShowBriefs() {
	    return this.settings.get('canShowBriefs', false);
	  }
	  isFileMigrationFinished() {
	    return this.store.state.sidebar.isFilesMigrated;
	  }
	}

	const BLOCKS_ORDER = {
	  [im_v2_const.SidebarBlock.main]: 10,
	  [im_v2_const.SidebarBlock.info]: 20,
	  [im_v2_const.SidebarBlock.file]: 30,
	  [im_v2_const.SidebarBlock.brief]: 40,
	  [im_v2_const.SidebarBlock.sign]: 50,
	  [im_v2_const.SidebarBlock.task]: 60,
	  [im_v2_const.SidebarBlock.meeting]: 70
	};
	var _settingsManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsManager");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _filterUnavailableBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterUnavailableBlocks");
	var _isFileMigrationFinished = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFileMigrationFinished");
	var _canShowBriefs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canShowBriefs");
	var _hasMarketApps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasMarketApps");
	var _sortBlocks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortBlocks");
	class AvailabilityManager {
	  constructor(settingsManager, dialogId) {
	    Object.defineProperty(this, _sortBlocks, {
	      value: _sortBlocks2
	    });
	    Object.defineProperty(this, _hasMarketApps, {
	      value: _hasMarketApps2
	    });
	    Object.defineProperty(this, _canShowBriefs, {
	      value: _canShowBriefs2
	    });
	    Object.defineProperty(this, _isFileMigrationFinished, {
	      value: _isFileMigrationFinished2
	    });
	    Object.defineProperty(this, _filterUnavailableBlocks, {
	      value: _filterUnavailableBlocks2
	    });
	    Object.defineProperty(this, _settingsManager, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsManager)[_settingsManager] = settingsManager;
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	  }
	  getBlocks() {
	    const blocksFromSetting = babelHelpers.classPrivateFieldLooseBase(this, _settingsManager)[_settingsManager].getBlocks();
	    const availableBlocks = babelHelpers.classPrivateFieldLooseBase(this, _filterUnavailableBlocks)[_filterUnavailableBlocks](blocksFromSetting);
	    return babelHelpers.classPrivateFieldLooseBase(this, _sortBlocks)[_sortBlocks](availableBlocks);
	  }
	}
	function _filterUnavailableBlocks2(blocks) {
	  const blocksSet = new Set(blocks);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFileMigrationFinished)[_isFileMigrationFinished]()) {
	    blocksSet.delete(im_v2_const.SidebarBlock.fileUnsorted);
	  } else {
	    blocksSet.delete(im_v2_const.SidebarBlock.brief);
	    blocksSet.delete(im_v2_const.SidebarBlock.file);
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _canShowBriefs)[_canShowBriefs]()) {
	    blocksSet.delete(im_v2_const.SidebarBlock.brief);
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasMarketApps)[_hasMarketApps]()) {
	    blocksSet.delete(im_v2_const.SidebarBlock.market);
	  }
	  return [...blocksSet];
	}
	function _isFileMigrationFinished2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settingsManager)[_settingsManager].isFileMigrationFinished();
	}
	function _canShowBriefs2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settingsManager)[_settingsManager].canShowBriefs();
	}
	function _hasMarketApps2() {
	  return im_v2_lib_market.MarketManager.getInstance().getAvailablePlacementsByType(im_v2_const.PlacementType.sidebar, babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]).length > 0;
	}
	function _sortBlocks2(availableBlocks) {
	  return [...availableBlocks].sort((block1, block2) => {
	    return BLOCKS_ORDER[block1] - BLOCKS_ORDER[block2];
	  });
	}

	const BlockClasses = {
	  Main,
	  Favorite,
	  Link,
	  Task,
	  Media,
	  Audio,
	  Document,
	  Other,
	  Brief,
	  Meeting,
	  FileUnsorted
	};
	const BlockToServices = Object.freeze({
	  [im_v2_const.SidebarBlock.main]: [im_v2_const.SidebarDetailBlock.main],
	  [im_v2_const.SidebarBlock.info]: [im_v2_const.SidebarDetailBlock.favorite, im_v2_const.SidebarDetailBlock.link],
	  [im_v2_const.SidebarBlock.task]: [im_v2_const.SidebarDetailBlock.task],
	  [im_v2_const.SidebarBlock.meeting]: [im_v2_const.SidebarDetailBlock.meeting],
	  [im_v2_const.SidebarBlock.brief]: [im_v2_const.SidebarDetailBlock.brief],
	  [im_v2_const.SidebarBlock.file]: [im_v2_const.SidebarDetailBlock.media, im_v2_const.SidebarDetailBlock.audio, im_v2_const.SidebarDetailBlock.document, im_v2_const.SidebarDetailBlock.other],
	  [im_v2_const.SidebarBlock.fileUnsorted]: [im_v2_const.SidebarDetailBlock.fileUnsorted]
	});
	class SidebarService {
	  constructor(availabilityManager) {
	    this.blockServices = [];
	    this.dialogId = '';
	    this.chatId = 0;
	    this.store = null;
	    this.restClient = null;
	    this.availabilityManager = null;
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.availabilityManager = availabilityManager;
	  }

	  //region public methods
	  requestInitialData() {
	    if (!main_core.Type.isArrayFilled(this.blockServices)) {
	      this.buildBlocks();
	    }
	    return new Promise((resolve, reject) => {
	      this.restClient.callBatch(this.getInitialRequestQuery(), result => resolve(this.handleBatchRequestResult(result)), error => reject(error));
	    });
	  }
	  getBlockInstance(blockName) {
	    var _this$blockServices$f;
	    if (!main_core.Type.isArrayFilled(this.blockServices)) {
	      this.buildBlocks();
	    }
	    return (_this$blockServices$f = this.blockServices.find(block => block.type === blockName.toLowerCase())) == null ? void 0 : _this$blockServices$f.blockManager;
	  }
	  setChatId(chatId) {
	    this.chatId = chatId;
	  }
	  setDialogId(dialogId) {
	    this.dialogId = dialogId;
	  }
	  //endregion

	  buildBlocks() {
	    const classNames = this.getServiceClassesForBlocks();
	    this.blockServices = classNames.map(className => {
	      const blockManager = new BlockClasses[className](this.chatId, this.dialogId);
	      return {
	        type: className.toLowerCase(),
	        blockManager: blockManager,
	        initialRequest: blockManager.getInitialRequest(),
	        responseHandler: blockManager.getResponseHandler()
	      };
	    });
	  }
	  getServiceClassesForBlocks() {
	    const services = [];
	    const blockList = this.availabilityManager.getBlocks();
	    blockList.forEach(block => {
	      const blockServices = BlockToServices[block];
	      if (blockServices) {
	        services.push(...blockServices);
	      }
	    });
	    return services.map(service => main_core.Text.capitalize(service));
	  }
	  getInitialRequestQuery() {
	    let query = {};
	    this.blockServices.forEach(block => {
	      query = Object.assign(query, block.initialRequest);
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
	    return this.store.dispatch('sidebar/setInited', this.chatId);
	  }
	}

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
	    isModerator: {
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
				<span v-if="isModerator" class="bx-im-sidebar-main-detail__avatar-moderator-icon"></span>
			</div>
			<div class="bx-im-sidebar-main-detail__user-info-container">
				<div class="bx-im-sidebar-main-detail__user-title-container">
					<ChatTitle :dialogId="dialogId" class="bx-im-sidebar-main-detail__user-title-text" />
					<div
						v-if="showContextButton"
						class="bx-im-sidebar-main-detail__context-menu-icon bx-im-messenger__context-menu-icon"
						@click="onClickContextMenu"
					></div>
				</div>
				<div class="bx-im-sidebar-main-detail__position-text">
					{{ position }}
				</div>
			</div>
		</div>	
	`
	};

	// @vue/component
	const SidebarDetail = {
	  name: 'SidebarDetail',
	  components: {
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['onScroll'],
	  data() {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    hasInitialData() {
	      return this.$store.getters['sidebar/isInited'](this.chatId);
	    }
	  },
	  created() {
	    this.loadFirstPage();
	  },
	  methods: {
	    loadFirstPage() {
	      if (this.hasInitialData) {
	        return;
	      }
	      this.isLoading = true;
	      this.service.loadFirstPage().then(() => {
	        this.isLoading = false;
	      });
	    },
	    needToLoadNextPage(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    },
	    onScroll(event) {
	      this.$emit('onScroll');
	      if (this.isLoading) {
	        return;
	      }
	      if (!this.needToLoadNextPage(event) || !this.service.hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoading = true;
	      this.service.loadNextPage().then(() => {
	        this.isLoading = false;
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-detail__container bx-im-sidebar-detail__scope" @scroll="onScroll">
			<slot :isLoading="isLoading" :chatId="chatId" :dialogId="dialogId"></slot>
			<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
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

	class MembersMenu extends SidebarMenu {
	  constructor() {
	    super();
	    this.chatService = new im_v2_provider_service.ChatService();
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
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
	          mentionReplacement: im_v2_lib_utils.Utils.user.getMentionBbCode(this.context.dialogId, user.name)
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
	    if (!this.callManager.chatCanBeCalled(this.context.dialogId)) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.call, this.context);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenProfileItem() {
	    const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
	    if (!isUser) {
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
	    const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
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
	  getKickItem() {
	    const userIdToKick = Number.parseInt(this.context.dialogId, 10);
	    const isSelfKick = userIdToKick === this.getCurrentUserId();
	    const canLeaveChat = this.store.getters['dialogues/canLeave'](this.context.contextDialogId);
	    if (isSelfKick || !this.isCurrentUserManager(this.context.contextDialogId) || !canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_KICK_FROM_CHAT'),
	      onclick: () => {
	        this.chatService.kickUserFromChat(this.context.contextDialogId, this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getLeaveItem() {
	    const userIdToKick = Number.parseInt(this.context.dialogId, 10);
	    const isSelfKick = userIdToKick === this.getCurrentUserId();
	    const canLeaveChat = this.store.getters['dialogues/canLeave'](this.context.contextDialogId);
	    if (!isSelfKick || !canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE'),
	      onclick: () => {
	        this.chatService.leaveChat(this.context.contextDialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  isCurrentUserManager(dialogId) {
	    const dialog = this.store.getters['dialogues/get'](dialogId);
	    if (!dialog) {
	      return false;
	    }
	    return dialog.managerList.includes(this.getCurrentUserId());
	  }
	}

	// @vue/component
	const MainDetail = {
	  name: 'MainDetail',
	  components: {
	    DetailUser,
	    SidebarDetail,
	    Button: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    dialogManagers() {
	      return this.dialog.managerList;
	    },
	    dialogIds() {
	      const users = this.$store.getters['sidebar/members/get'](this.chatId);
	      return users.map(userId => userId.toString());
	    },
	    showCopyInviteButton() {
	      // todo
	      return true;
	    },
	    chatLink() {
	      return `${im_v2_application_core.Core.getHost()}/online/?IM_DIALOG=${this.dialogId}`;
	    }
	  },
	  created() {
	    this.contextMenu = new MembersMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    isModerator(userDialogId) {
	      const userId = Number.parseInt(userDialogId, 10);
	      return this.dialogManagers.includes(userId);
	    },
	    onContextMenuClick(event) {
	      const item = {
	        dialogId: event.userDialogId,
	        contextDialogId: this.dialogId,
	        contextChatId: this.chatId
	      };
	      this.contextMenu.openMenu(item, event.target);
	    },
	    onScroll() {
	      this.contextMenu.destroy();
	    },
	    onCopyInviteClick() {
	      if (BX.clipboard.copy(this.chatLink)) {
	        BX.UI.Notification.Center.notify({
	          content: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_COPIED_SUCCESS')
	        });
	      }
	    }
	  },
	  template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-main-detail__scope"
		>
			<div class="bx-im-sidebar-main-detail__invitation-button-container">
				<Button
					v-if="showCopyInviteButton"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_COPY_INVITE_LINK')"
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
				:isModerator="isModerator(dialogId)"
				@contextMenuClick="onContextMenuClick"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const Settings = {
	  name: 'MainPreviewSettings',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    Toggle: im_v2_component_elements.Toggle
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    isModerator: {
	      type: Boolean,
	      default: false
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isGroupChat() {
	      return this.dialogId.startsWith('chat');
	    },
	    canBeMuted() {
	      return this.$store.getters['dialogues/canMute'](this.dialogId);
	    },
	    isChatMuted() {
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return !!isMuted;
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
	    hintAutoDeleteNotAvailable() {
	      return {
	        text: this.$Bitrix.Loc.getMessage('IM_MESSENGER_NOT_AVAILABLE'),
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
	    },
	    chatTypeClass() {
	      return this.isGroupChat ? '--group-chat' : '--personal';
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
	    }
	  },
	  template: `
		<div v-if="isLoading" class="bx-im-sidebar-main-settings__skeleton" :class="chatTypeClass"></div>
		<div v-else class="bx-im-sidebar-main-settings__container bx-im-sidebar-main-settings__scope" :class="chatTypeClass">
			<div
				v-if="isGroupChat"
				class="bx-im-sidebar-main-settings__notification-container"
				:class="[canBeMuted ? '' : '--not-active']"
				v-hint="hintMuteNotAvailable"
			>
				<div class="bx-im-sidebar-main-settings__notification-title">
					<div class="bx-im-sidebar-main-settings__title-text bx-im-sidebar-main-settings__title-icon --notification">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_ENABLE_NOTIFICATION_TITLE') }}
					</div>
					<Toggle :size="ToggleSize.M" :isEnabled="!isChatMuted" @change="muteActionHandler" />
				</div>
			</div>
			<div class="bx-im-sidebar-main-settings__autodelete-container --not-active" v-hint="hintAutoDeleteNotAvailable">
				<div class="bx-im-sidebar-main-settings__autodelete-title">
					<div class="bx-im-sidebar-main-settings__title-text bx-im-sidebar-main-settings__title-icon --autodelete">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_ENABLE_AUTODELETE_TITLE') }}
					</div>
					<Toggle :size="ToggleSize.M" :isEnabled="autoDeleteEnabled" />
				</div>
				<div class="bx-im-sidebar-main-settings__autodelete-status">
					{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_AUTODELETE_STATUS_OFF') }}
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const PersonalChatPreview = {
	  name: 'PersonalChatPreview',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    Button: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    Settings
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    canInviteMembers() {
	      return this.$store.getters['dialogues/getChatOption'](this.dialog.type, im_v2_const.ChatOption.extend);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  methods: {
	    onAddClick() {
	      this.showAddToChatPopup = true;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="!dialogInited" class="bx-im-sidebar-main-preview-personal-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-personal-chat__avatar-container">
				<Avatar
					:size="AvatarSize.XXXL"
					:withStatus="false"
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
			
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-personal-chat__invite-button-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-personal-chat__invite-button-container" ref="add-members">
				<Button
					v-if="canInviteMembers"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_INVITE_BUTTON_TEXT')"
					:size="ButtonSize.S"
					:color="ButtonColor.PrimaryLight"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onAddClick"
				/>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -320}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const GroupChatPreview = {
	  name: 'GroupChatPreview',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    Button: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    Settings
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['openDetail'],
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    dialogIds() {
	      const PREVIEW_USERS_COUNT = 4;
	      const userIds = this.$store.getters['sidebar/members/get'](this.chatId);
	      return userIds.map(id => id.toString()).slice(0, PREVIEW_USERS_COUNT);
	    },
	    usersInChatCount() {
	      return this.dialog.userCounter;
	    },
	    moreUsersCount() {
	      return Math.max(this.usersInChatCount - this.dialogIds.length, 0);
	    },
	    canSeeMembers() {
	      return this.$store.getters['dialogues/getChatOption'](this.dialog.type, im_v2_const.ChatOption.userList);
	    },
	    canInviteMembers() {
	      return this.$store.getters['dialogues/getChatOption'](this.dialog.type, im_v2_const.ChatOption.extend);
	    }
	  },
	  methods: {
	    onOpenUsers() {
	      this.$emit('openDetail');
	    },
	    onOpenInvitePopup() {
	      this.showAddToChatPopup = true;
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="!dialogInited" class="bx-im-sidebar-main-preview-group-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<Avatar :size="AvatarSize.XXXL" :withStatus="false" :dialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-group-chat__members-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__members-container">
				<div v-if="canSeeMembers" class="bx-im-sidebar-main-preview-group-chat__members" @click="onOpenUsers">
					<div class="bx-im-sidebar-main-preview-group-chat__members-avatars" >
						<Avatar
							class="bx-im-sidebar-main-preview-group-chat__chat-user-avatar"
							v-for="id in dialogIds"
							:size="AvatarSize.S"
							:withStatus="false"
							:dialogId="id"
						/>
					</div>
					<div v-if="moreUsersCount > 0" class="bx-im-sidebar-main-preview-group-chat__more-users-count-text">
						+{{ moreUsersCount }}
					</div>
				</div>
				<div ref="add-members">
					<Button
						v-if="canInviteMembers"
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_INVITE_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="onOpenInvitePopup"
					/>
				</div>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const MainPreview = {
	  name: 'MainPreview',
	  components: {
	    GroupChatPreview,
	    PersonalChatPreview
	  },
	  inheritAttrs: false,
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    isGroupChat() {
	      return this.dialogId.startsWith('chat');
	    }
	  },
	  methods: {
	    onOpenDetail() {
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.main,
	        detailBlock: im_v2_const.SidebarDetailBlock.main
	      });
	    }
	  },
	  template: `
		<GroupChatPreview 
			v-if="isGroupChat" 
			:dialogId="dialogId"
			:isLoading="isLoading" 
			@openDetail="onOpenDetail"
			class="bx-im-sidebar__box"
		/>
		<PersonalChatPreview 
			v-else 
			:dialogId="dialogId"
			:isLoading="isLoading"
			class="bx-im-sidebar__box"
		/>
	`
	};

	const MAX_DESCRIPTION_SYMBOLS = 25;

	// @vue/component
	const InfoPreview = {
	  name: 'InfoPreview',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['openDetail'],
	  data() {
	    return {
	      expanded: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
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
	    },
	    favoriteCounter() {
	      const counter = this.$store.getters['sidebar/favorites/getCounter'](this.chatId);
	      return this.getCounterString(counter);
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
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    isLoadingState() {
	      return !this.dialogInited || this.isLoading;
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
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.info,
	        detailBlock: im_v2_const.SidebarDetailBlock.favorite
	      });
	    },
	    onLinkClick() {
	      if (!this.isLinksAvailable) {
	        return;
	      }
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.info,
	        detailBlock: im_v2_const.SidebarDetailBlock.link
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-info-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-info-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-info-preview__container" :class="[expanded ? '--expanded' : '']">
				<div class="bx-im-sidebar-info-preview__description-container">
					<div class="bx-im-sidebar-info-preview__description-text-container" :class="[expanded ? '--expanded' : '']">
						<div class="bx-im-sidebar-info-preview__description-icon bx-im-sidebar-info-preview__item-icon"></div>
						<div class="bx-im-sidebar-info-preview__description-text">
							{{descriptionToShow}}
						</div>
					</div>
					<button
						v-if="showExpandButton"
						class="bx-im-sidebar-info-preview__show-description-button"
						@click="expanded = !expanded"
					>
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_DESCRIPTION_SHOW') }}
					</button>
				</div>
				<div class="bx-im-sidebar-info-preview__items-container">
					<div class="bx-im-sidebar-info-preview__item-container" @click="onFavouriteClick">
						<div class="bx-im-sidebar-info-preview__title-container">
							<div class="bx-im-sidebar-info-preview__favorite-icon bx-im-sidebar-info-preview__item-icon"></div>
							<div class="bx-im-sidebar-info-preview__title-text">
								{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITE_DETAIL_TITLE') }}
							</div>
						</div>
						<div class="bx-im-sidebar-info-preview__counter-container">
							<span class="bx-im-sidebar-info-preview__counter">{{favoriteCounter}}</span>
						</div>
					</div>
					<div 
						class="bx-im-sidebar-info-preview__item-container" 
						:class="[isLinksAvailable ? '' : '--links-not-active']"
						@click="onLinkClick"
					>
						<div 
							v-if="!isLinksAvailable" 
							class="bx-im-sidebar-info-preview__hint-not-active" 
							v-hint="hintDirectiveContent"
						></div>
						<div class="bx-im-sidebar-info-preview__title-container">
							<div class="bx-im-sidebar-info-preview__link-icon bx-im-sidebar-info-preview__item-icon"></div>
							<div class="bx-im-sidebar-info-preview__title-text">
								{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_LINK_DETAIL_TITLE') }}
							</div>
						</div>
						<div class="bx-im-sidebar-info-preview__counter-container">
							<span class="bx-im-sidebar-info-preview__counter">{{urlCounter}}</span>
						</div>
					</div>
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
	      'LINK_ID': link.id
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
	      return !!((_this$linkItem$richDa = this.linkItem.richData) != null && _this$linkItem$richDa.previewUrl);
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
				<a :href="source" :title="description" class="bx-im-link-item__description-text">
					{{ description }}
				</a>
				<div class="bx-im-link-item__author-container">
					<Avatar 
						:size="AvatarSize.XS" 
						:withStatus="false" 
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
	      return `--${this.iconType}`;
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
	const LinkDetail = {
	  name: 'LinkDetail',
	  components: {
	    LinkItem,
	    SidebarDetail,
	    DateGroup,
	    DetailEmptyState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new LinkMenu();
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
	    onScroll() {
	      this.contextMenu.destroy();
	    }
	  },
	  template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-link-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<template v-for="link in dateGroup.items">
					<LinkItem :link="link" @contextMenuClick="onContextMenuClick" />
				</template>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_EMPTY')"
				:iconType="SidebarDetailBlock.link"
			/>
		</SidebarDetail>
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
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_REMOVE_FROM_SAVED'),
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
						:withStatus="false"
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
	const FavoriteDetail = {
	  name: 'FavoriteDetail',
	  components: {
	    FavoriteItem,
	    DateGroup,
	    DetailEmptyState,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FavoriteMenu();
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
	    onScroll() {
	      this.contextMenu.destroy();
	    }
	  },
	  template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-favorite-detail__scope bx-im-sidebar-favorite-detail__container"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<FavoriteItem 
					v-for="favorite in dateGroup.items" 
					:favorite="favorite"
					:chatId="chatId"
					:dialogId="dialogId"
					@contextMenuClick="onContextMenuClick" 
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITES_EMPTY')"
				:iconType="SidebarDetailBlock.favorite"
			/>
		</SidebarDetail>
	`
	};

	class FileManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }
	  delete(sidebarFile) {
	    this.store.dispatch('sidebar/files/delete', {
	      chatId: sidebarFile.chatId,
	      id: sidebarFile.id
	    });
	    const queryParams = {
	      chat_id: sidebarFile.chatId,
	      file_id: sidebarFile.fileId
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imDiskFileDelete, queryParams).catch(error => {
	      console.error('Im.Sidebar: error deleting file', error);
	    });
	  }
	  saveOnDisk(fileId) {
	    const queryParams = {
	      file_id: fileId
	    };
	    return this.restClient.callMethod(im_v2_const.RestMethod.imDiskFileSave, queryParams).catch(error => {
	      console.error('Im.Sidebar: error saving file on disk', error);
	    });
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
	      text: main_core.Loc.getMessage('IM_SIDEBAR_MENU_SAVE_FILE_ON_DISK'),
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
	      if (!this.$refs['video']) {
	        return;
	      }
	      this.videoDuration = this.$refs['video'].duration;
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
					<Avatar :dialogId="sidebarFileItem.authorId" :withStatus="false" :size="AvatarSize.S"></Avatar>
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
			>
			</div>
			<div
				v-if="isVideo"
				class="bx-im-sidebar-file-media-detail-item__content --video"
				:style="previewPicture"
				v-bind="viewerAttributes"
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

	// @vue/component
	const MediaDetail = {
	  name: 'MediaDetail',
	  components: {
	    DateGroup,
	    MediaDetailItem,
	    DetailEmptyState,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
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
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-file-media-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<div class="bx-im-sidebar-file-media-detail__items-group">
					<MediaDetailItem
						v-for="file in dateGroup.items"
						:fileItem="file"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.media"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const SidebarAudioPlayer = ui_vue3.BitrixVue.cloneComponent(ui_vue3_components_audioplayer.AudioPlayer, {
	  name: 'SidebarAudioPlayer',
	  components: {
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    authorId: {
	      type: Number,
	      required: true
	    },
	    timelineType: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {
	      ...this.parentData(),
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    fileAuthorDialogId() {
	      return this.authorId.toString();
	    },
	    progressPosition() {
	      if (!this.loaded || this.state === ui_vue3_components_audioplayer.AudioPlayerState.none) {
	        return {
	          width: '100%'
	        };
	      }
	      return {
	        width: `${this.progressInPixel}px`
	      };
	    },
	    activeTimelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const ACTIVE_TIMELINE_VERTICAL_SHIFT = 19;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT + ACTIVE_TIMELINE_VERTICAL_SHIFT;
	      return {
	        ...this.progressPosition,
	        'background-position-y': `-${shift}px`
	      };
	    },
	    timelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT;
	      return {
	        'background-position-y': `-${shift}px`
	      };
	    }
	  },
	  template: `
		<div 
			class="bx-im-sidebar-audio-player__container bx-im-sidebar-audio-player__scope" 
			ref="body"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-audio-player__control-container">
				<button :class="['bx-im-sidebar-audio-player__control-button', {
					'bx-im-sidebar-audio-player__control-loader': loading,
					'bx-im-sidebar-audio-player__control-play': !loading && state !== State.play,
					'bx-im-sidebar-audio-player__control-pause': !loading && state === State.play,
				}]" @click="clickToButton"></button>
				<div class="bx-im-sidebar-audio-player__author-avatar-container">
					<Avatar :dialogId="fileAuthorDialogId" :withStatus="false" :size="AvatarSize.XS"></Avatar>
				</div>
			</div>
			<div class="bx-im-sidebar-audio-player__timeline-container">
				<div class="bx-im-sidebar-audio-player__track-container" @click="setPosition" ref="track">
					<div class="bx-im-sidebar-audio-player__track-mask" :style="timelineStyles"></div>
					<div class="bx-im-sidebar-audio-player__track-mask --active" :style="activeTimelineStyles"></div>
					<div class="bx-im-sidebar-audio-player__track-seek" :style="seekPosition"></div>
					<div class="bx-im-sidebar-audio-player__track-event" @mousemove="seeking"></div>
				</div>
				<div class="bx-im-sidebar-audio-player__timer-container">
					{{fileSize}}, {{labelTime}}
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon bx-im-sidebar-audio-player__context-menu-button"
				@click="$emit('contextMenuClick', $event)"
			></button>
			<audio 
				v-if="src" 
				:src="src" 
				class="bx-im-sidebar-audio-player__audio-source" 
				ref="source" 
				:preload="preload"
				@abort="audioEventRouter('abort', $event)"
				@error="audioEventRouter('error', $event)"
				@suspend="audioEventRouter('suspend', $event)"
				@canplay="audioEventRouter('canplay', $event)"
				@canplaythrough="audioEventRouter('canplaythrough', $event)"
				@durationchange="audioEventRouter('durationchange', $event)"
				@loadeddata="audioEventRouter('loadeddata', $event)"
				@loadedmetadata="audioEventRouter('loadedmetadata', $event)"
				@timeupdate="audioEventRouter('timeupdate', $event)"
				@play="audioEventRouter('play', $event)"
				@playing="audioEventRouter('playing', $event)"
				@pause="audioEventRouter('pause', $event)"
			></audio>
		</div>
	`
	});

	// @vue/component
	const AudioDetailItem = {
	  name: 'AudioDetailItem',
	  components: {
	    SidebarAudioPlayer,
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
			<SidebarAudioPlayer 
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
	const AudioDetail = {
	  name: 'AudioDetail',
	  components: {
	    DetailEmptyState,
	    AudioDetailItem,
	    DateGroup,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-file-audio-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<AudioDetailItem
					v-for="file in dateGroup.items"
					:id="file.id"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.audio"
			/>
		</SidebarDetail>
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
	      const NAME_MAX_LENGTH = 21;
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
					<div class="bx-im-sidebar-file-document-detail-item__document-title" @click="download">
						<span class="bx-im-sidebar-file-document-detail-item__document-title-text">{{fileShortName}}</span>
						<span class="bx-im-sidebar-file-document-detail-item__document-size">{{fileSize}}</span>
					</div>
					<div class="bx-im-sidebar-file-document-detail-item__author-container">
						<template v-if="authorId > 0">
							<Avatar
								:dialogId="authorId"
								:size="AvatarSize.XS"
								:withStatus="false"
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
	const DocumentDetail = {
	  name: 'DocumentDetail',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-file-document-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.document"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const OtherDetail = {
	  name: 'OtherDetail',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-file-other-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.other"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const FilePreviewItem = {
	  name: 'FilePreviewItem',
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
	      const NAME_MAX_LENGTH = 25;
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
			<div v-if="isImage" class="bx-im-sidebar-file-preview-item__preview-box" :style="previewImageStyles"></div>
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
	const FilePreview = {
	  name: 'FilePreview',
	  components: {
	    DetailEmptyState,
	    FilePreviewItem
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    isLoadingState() {
	      return !this.dialogInited || this.isLoading;
	    }
	  },
	  methods: {
	    onOpenDetail() {
	      if (!this.hasFiles) {
	        return;
	      }
	      const block = this.isMigrationFinished ? im_v2_const.SidebarBlock.file : im_v2_const.SidebarBlock.fileUnsorted;
	      const detailBlock = this.isMigrationFinished ? im_v2_const.SidebarDetailBlock.media : im_v2_const.SidebarDetailBlock.fileUnsorted;
	      this.$emit('openDetail', {
	        block,
	        detailBlock
	      });
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-file-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-file-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-file-preview__container">
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

	// @vue/component
	const FileUnsortedDetail = {
	  name: 'FileUnsortedDetail',
	  components: {
	    DateGroup,
	    DocumentDetailItem,
	    DetailEmptyState,
	    SidebarDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-file-unsorted-detail__container bx-im-sidebar-file-unsorted-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<DocumentDetailItem
					v-for="file in dateGroup.items"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.document"
			/>
		</SidebarDetail>
	`
	};

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
	      chatId: chatId,
	      id: id
	    });
	    const queryParams = {
	      'LINK_ID': id
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
				<div class="bx-im-sidebar-task-item__header-text">
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
	    Button: im_v2_component_elements.Button
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['openDetail'],
	  data() {
	    return {
	      showAddButton: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    firstTask() {
	      return this.$store.getters['sidebar/tasks/get'](this.chatId)[0];
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    isLoadingState() {
	      return !this.dialogInited || this.isLoading;
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
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.task,
	        detailBlock: im_v2_const.SidebarDetailBlock.task
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
			<div v-if="isLoadingState" class="bx-im-sidebar-task-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-task-preview__container">
				<div 
					class="bx-im-sidebar-task-preview__header_container"
					@mouseover="showAddButton = true"
					@mouseleave="showAddButton = false"
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
						<Button
							v-if="showAddButton"
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

	// @vue/component
	const TaskDetail = {
	  name: 'TaskDetail',
	  components: {
	    TaskItem,
	    DateGroup,
	    SidebarDetail,
	    DetailEmptyState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new TaskMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-task-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<TaskItem
					v-for="task in dateGroup.items"
					:task="task"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
				:iconType="SidebarDetailBlock.task"
			/>
		</SidebarDetail>
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
	      const NAME_MAX_LENGTH = 21;
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
							:withStatus="false" 
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
	const BriefDetail = {
	  name: 'BriefDetail',
	  components: {
	    DateGroup,
	    BriefItem,
	    SidebarDetail,
	    DetailEmptyState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    }
	  },
	  created() {
	    this.collectionFormatter = new SidebarCollectionFormatter();
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.collectionFormatter.destroy();
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-brief-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle"/>
				<BriefItem
					v-for="file in dateGroup.items"
					:brief="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEFS_EMPTY')"
				:iconType="SidebarDetailBlock.brief"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const BriefPreview = {
	  name: 'BriefPreview',
	  components: {
	    DetailEmptyState,
	    BriefItem
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['openDetail'],
	  data() {
	    return {
	      showAddButton: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    firstBrief() {
	      return this.$store.getters['sidebar/files/get'](this.chatId, im_v2_const.SidebarFileTypes.brief)[0];
	    },
	    isLoadingState() {
	      return !this.dialogInited || this.isLoading;
	    }
	  },
	  created() {
	    this.contextMenu = new FileMenu();
	  },
	  beforeUnmount() {
	    this.contextMenu.destroy();
	  },
	  methods: {
	    onOpenDetail() {
	      if (!this.firstBrief) {
	        return;
	      }
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.brief,
	        detailBlock: im_v2_const.SidebarDetailBlock.brief
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
		<div class="bx-im-sidebar-brief-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-brief-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-brief-preview__container">
				<div 
					class="bx-im-sidebar-brief-preview__header_container" 
					:class="[firstBrief ? '--active': '']" 
					@click="onOpenDetail"
				>
					<span class="bx-im-sidebar-brief-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEF_DETAIL_TITLE') }}
					</span>
					<div v-if="firstBrief" class="bx-im-sidebar__forward-icon"></div>
				</div>
				<BriefItem 
					v-if="firstBrief" 
					:brief="firstBrief"
					@contextMenuClick="onContextMenuClick"
				/>
				<DetailEmptyState 
					v-else 
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEFS_EMPTY')"
					:iconType="SidebarDetailBlock.brief"
				/>
			</div>
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
	      chatId: chatId,
	      id: id
	    });
	    const queryParams = {
	      'LINK_ID': id
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
					<div class="bx-im-sidebar-meeting-item__title">{{ title }}</div>
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
	    Button: im_v2_component_elements.Button
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showAddButton: false
	    };
	  },
	  computed: {
	    SidebarDetailBlock: () => im_v2_const.SidebarDetailBlock,
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    firstMeeting() {
	      return this.$store.getters['sidebar/meetings/get'](this.chatId)[0];
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    isLoadingState() {
	      return !this.dialogInited || this.isLoading;
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
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.meeting,
	        detailBlock: im_v2_const.SidebarDetailBlock.meeting
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
			<div v-if="isLoadingState" class="bx-im-sidebar-meeting-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-meeting-preview__container">
				<div
					class="bx-im-sidebar-meeting-preview__header_container"
					@mouseover="showAddButton = true"
					@mouseleave="showAddButton = false"
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
						<Button
							v-if="showAddButton"
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
	const MeetingDetail = {
	  name: 'MeetingDetail',
	  components: {
	    MeetingItem,
	    DateGroup,
	    SidebarDetail,
	    DetailEmptyState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    },
	    service: {
	      type: Object,
	      required: true
	    }
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
	    onScroll() {
	      this.contextMenu.destroy();
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
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-meeting-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<MeetingItem
					v-for="meeting in dateGroup.items"
					:meeting="meeting"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
				:iconType="SidebarDetailBlock.meeting"
			/>
		</SidebarDetail>
	`
	};

	// @vue/component
	const SignPreview = {
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-sign-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-sign-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-sign-preview__container" >
				Signed documents. Work in progress
			</div>
		</div>
	`
	};

	// @vue/component
	const SignDetail = {
	  emits: ['back'],
	  data() {
	    return {};
	  },
	  template: `
		<div>
		<div @click="$emit('back')" style="margin-bottom: 20px; cursor: pointer">&lt;- Back</div>
			<div v-for="i in 50">Sign {{ i }}</div>
		</div>
	`
	};

	// @vue/component
	const MarketPreviewItem = {
	  name: 'MarketPreviewItem',
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
	    MarketPreviewItem
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
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
	      this.$emit('openDetail', {
	        block: im_v2_const.SidebarBlock.meeting,
	        detailBlock: im_v2_const.SidebarDetailBlock.market,
	        entityId: entityId
	      });
	    }
	  },
	  template: `
		<div v-if="!isLoading" class="bx-im-sidebar-market-preview__scope bx-im-sidebar-market-preview__container">
			<div class="bx-im-sidebar-market-preview__header_container">
				<div class="bx-im-sidebar-market-preview__title">
					<span class="bx-im-sidebar-market-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MARKET_DETAIL_TITLE') }}
					</span>
				</div>
			</div>
			<MarketPreviewItem 
				v-for="item in marketMenuItems" 
				:key="item.id"
				:item="item"
				@click="onMarketItemClick(item.id)"
			/>
		</div>
	`
	};

	// @vue/component
	const MarketDetail = {
	  name: 'MarketDetail',
	  components: {
	    Spinner: im_v2_component_elements.Spinner
	  },
	  props: {
	    detailBlockEntityId: {
	      type: String,
	      required: true
	    },
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
	    SpinnerSize: () => im_v2_component_elements.SpinnerSize
	  },
	  created() {
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  },
	  mounted() {
	    const context = {
	      dialogId: this.dialogId
	    };
	    this.marketManager.loadPlacement(this.detailBlockEntityId, context).then(response => {
	      this.isLoading = false;
	      main_core.Runtime.html(this.$refs['im-messenger-sidebar-placement'], response);
	    });
	  },
	  template: `
		<div class="bx-im-sidebar-market-detail__container">
			<div v-if="isLoading" class="bx-im-sidebar-market-detail__loader-container">
				<Spinner :size="SpinnerSize.S" />
			</div>
			<div ref="im-messenger-sidebar-placement"></div>
		</div>
		
	`
	};

	class MainMenu extends im_v2_lib_menu.RecentMenu {
	  constructor() {
	    super();
	    this.id = 'im-sidebar-context-menu';
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: false
	    };
	  }
	  getMenuItems() {
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getCallItem(), this.getOpenProfileItem(), this.getOpenUserCalendarItem(), this.getAddMembersToChatItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getOpenUserCalendarItem() {
	    const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
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
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const canInviteMembers = this.store.getters['dialogues/getChatOption'](dialog.type, im_v2_const.ChatOption.extend);
	    if (!canInviteMembers) {
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
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    if (isUser) {
	      return null;
	    }

	    //todo: check if user is in chat already

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
	const SidebarHeader = {
	  name: 'SidebarHeader',
	  components: {
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
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
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-header__container bx-im-sidebar-header__scope">
			<div class="bx-im-sidebar-header__title-container">
				<button 
					class="bx-im-sidebar-header__cross-icon bx-im-messenger__cross-icon" 
					@click="onSidebarCloseClick"
				></button>
				<div class="bx-im-sidebar-header__title">{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_HEADER_TITLE') }}</div>
			</div>
			<button
				class="bx-im-sidebar-header__context-menu-icon bx-im-messenger__context-menu-icon"
				@click="onContextMenuClick"
				ref="context-menu"
			></button>
			<AddToChat
				:bindElement="$refs['context-menu'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 0, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
	};

	// @vue/component
	const DetailHeader = {
	  name: 'DetailHeader',
	  components: {
	    ChatButton: im_v2_component_elements.Button,
	    AddToChat: im_v2_component_entitySelector.AddToChat
	  },
	  props: {
	    detailBlock: {
	      type: String,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    chatId: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['back'],
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    needAddButton() {
	      const detailsWithAddButton = [im_v2_const.SidebarDetailBlock.main, im_v2_const.SidebarDetailBlock.task, im_v2_const.SidebarDetailBlock.meeting];
	      if (this.detailBlock === im_v2_const.SidebarDetailBlock.main) {
	        return this.$store.getters['dialogues/getChatOption'](this.dialog.type, im_v2_const.ChatOption.extend);
	      }
	      return detailsWithAddButton.includes(this.detailBlock);
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    title() {
	      if (this.detailBlock === im_v2_const.SidebarDetailBlock.main) {
	        let usersInChatCount = this.dialog.userCounter;
	        if (usersInChatCount >= 1000) {
	          usersInChatCount = `${Math.floor(usersInChatCount / 1000)}k`;
	        }
	        return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MAIN_DETAIL_TITLE').replace('#NUMBER#', usersInChatCount);
	      }
	      if (Object.values(im_v2_const.SidebarFileTabTypes).includes(this.detailBlock)) {
	        return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE');
	      }
	      const phrase = `IM_SIDEBAR_${this.detailBlock.toUpperCase()}_DETAIL_TITLE`;
	      return this.$Bitrix.Loc.getMessage(phrase);
	    }
	  },
	  created() {
	    this.entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.chatId);
	  },
	  methods: {
	    onSearchClick() {
	      console.warn('onSearchClick');
	    },
	    onAddClick() {
	      if (!this.needAddButton) {
	        return;
	      }
	      switch (this.detailBlock) {
	        case im_v2_const.SidebarDetailBlock.meeting:
	          {
	            this.entityCreator.createMeetingForChat();
	            break;
	          }
	        case im_v2_const.SidebarDetailBlock.task:
	          {
	            this.entityCreator.createTaskForChat();
	            break;
	          }
	        case im_v2_const.SidebarDetailBlock.main:
	          {
	            this.showAddToChatPopup = true;
	            break;
	          }
	        default:
	          break;
	      }
	    }
	  },
	  template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button class="bx-im-sidebar__back-icon" @click="$emit('back')"></button>
				<div class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<div class="bx-im-sidebar-detail-header__add-button" ref="add-members">
					<ChatButton
						v-if="needAddButton"
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="onAddClick"
					/>
				</div>
				
			</div>
<!--			<button-->
<!--				class="bx-im-sidebar-detail-header__search-button bx-im-sidebar__search-icon"-->
<!--				@click="onSearchClick"-->
<!--			>-->
<!--			</button>-->
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
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
	      return [...this.$refs.tabs.children].filter(node => !main_core.Dom.hasClass(node, 'bx-sidebar-tabs-highlight'))[index];
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
	const ChatSidebar = {
	  name: 'ChatSidebar',
	  components: {
	    DetailHeader,
	    DetailTabs,
	    SidebarHeader,
	    MainDetail,
	    MainPreview,
	    InfoPreview,
	    LinkDetail,
	    FavoriteDetail,
	    MediaDetail,
	    AudioDetail,
	    DocumentDetail,
	    OtherDetail,
	    FilePreview,
	    TaskPreview,
	    TaskDetail,
	    BriefDetail,
	    BriefPreview,
	    MeetingPreview,
	    MeetingDetail,
	    SignPreview,
	    SignDetail,
	    FileUnsortedDetail,
	    FileUnsortedPreview: FilePreview,
	    MarketPreview,
	    MarketDetail
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    sidebarDetailBlock: {
	      type: String,
	      default: null
	    }
	  },
	  emits: ['back'],
	  data() {
	    return {
	      isLoading: false,
	      detailBlock: null,
	      detailBlockEntityId: null,
	      detailTransition: 'right-panel-detail-transition'
	    };
	  },
	  computed: {
	    blocks() {
	      return this.availabilityManager.getBlocks();
	    },
	    hasInitialData() {
	      return this.$store.getters['sidebar/isInited'](this.chatId);
	    },
	    detailComponent() {
	      if (!this.detailBlock) {
	        return null;
	      }
	      return `${this.detailBlock}Detail`;
	    },
	    getBlockServiceInstance() {
	      return this.sidebarService.getBlockInstance(this.detailBlock);
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    tabs() {
	      if (im_v2_const.SidebarFileTabTypes[this.detailBlock]) {
	        return Object.values(im_v2_const.SidebarFileTabTypes);
	      }
	      return [];
	    }
	  },
	  watch: {
	    sidebarDetailBlock(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        this.detailBlock = newValue;
	      }
	    },
	    dialogInited(newValue, oldValue) {
	      if (newValue === true && oldValue === false) {
	        this.initializeSidebar();
	      }
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Sidebar: Chat Sidebar created');
	    this.settingsManager = new SettingsManager();
	    this.availabilityManager = new AvailabilityManager(this.settingsManager, this.dialogId);
	    this.sidebarService = new SidebarService(this.availabilityManager);
	    this.initializeSidebar();
	  },
	  mounted() {
	    if (this.sidebarDetailBlock) {
	      this.detailBlock = this.sidebarDetailBlock;
	      this.detailTransition = '';
	    }
	  },
	  methods: {
	    initializeSidebar() {
	      this.isLoading = true;
	      if (!this.dialogInited) {
	        return;
	      }
	      this.sidebarService.setChatId(this.chatId);
	      this.sidebarService.setDialogId(this.dialogId);
	      if (this.hasInitialData) {
	        this.isLoading = false;
	        return;
	      }
	      this.sidebarService.requestInitialData().then(() => {
	        this.isLoading = false;
	      });
	    },
	    onOpenDetail(data) {
	      const {
	        detailBlock,
	        entityId = ''
	      } = data;
	      this.detailBlock = detailBlock;
	      this.detailBlockEntityId = entityId.toString();
	    },
	    getPreviewComponentName(block) {
	      return `${block}Preview`;
	    },
	    onClickBack() {
	      this.detailBlock = null;
	      this.detailTransition = 'right-panel-detail-transition';
	      this.$emit('back');
	    },
	    onTabSelect(tab) {
	      this.detailBlock = tab;
	    }
	  },
	  template: `
		<SidebarHeader :isLoading="isLoading" :dialogId="dialogId" :chatId="chatId" />
		<div class="bx-im-sidebar__container bx-im-sidebar__scope">
			<component
				v-for="block in blocks"
				:key="block"
				class="bx-im-sidebar__box"
				:is="getPreviewComponentName(block)"
				:isLoading="isLoading"
				:dialogId="dialogId"
				@openDetail="onOpenDetail"
			/>
		</div>
		<transition :name="detailTransition">
			<div v-if="detailComponent && dialogInited" class="bx-im-sidebar__detail_container bx-im-sidebar__scope">
				<DetailHeader :detailBlock="detailBlock" :dialogId="dialogId" :chatId="chatId" @back="onClickBack"/>
				<DetailTabs v-if="tabs.length > 0" :tabs="tabs" @tabSelect="onTabSelect" />
				<component
					:is="detailComponent"
					:dialogId="dialogId"
					:chatId="chatId"
					:detailBlock="detailBlock"
					:detailBlockEntityId="detailBlockEntityId"
					:service="getBlockServiceInstance"
					@back="onClickBack"
				/>
			</div>
		</transition> 
	`
	};

	exports.ChatSidebar = ChatSidebar;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3.Directives,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Vue3.Components,BX.Vue3,BX.Vue3.Components,BX.UI.Viewer,BX.UI,BX,BX.Messenger.v2.Model,BX,BX,BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Main,BX.Im.V2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX));
//# sourceMappingURL=sidebar.bundle.js.map
