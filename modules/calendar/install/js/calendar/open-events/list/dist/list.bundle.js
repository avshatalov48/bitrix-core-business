/* eslint-disable */
this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3,ui_entitySelector,ui_switcher,calendar_openEvents_filter,main_loader,main_polyfill_intersectionobserver,main_core_events,im_public_iframe,im_v2_const,main_date,ui_iconSet_main,ui_buttons,ui_iconSet_actions,main_core,main_popup,ui_cnt,ui_vue3_vuex) {
	'use strict';

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _closed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closed");
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _description = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("description");
	var _eventsCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventsCount");
	var _permissions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("permissions");
	var _channelId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("channelId");
	var _isMuted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMuted");
	var _isBanned = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isBanned");
	var _newCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("newCount");
	var _updatedAt = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updatedAt");
	var _channel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("channel");
	var _isSelected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSelected");
	class CategoryModel {
	  constructor(fields = {}) {
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _closed, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _description, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _eventsCount, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _permissions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _channelId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isMuted, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isBanned, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _newCount, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _updatedAt, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _channel, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isSelected, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = fields.id;
	    babelHelpers.classPrivateFieldLooseBase(this, _closed)[_closed] = fields.closed;
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = fields.name;
	    babelHelpers.classPrivateFieldLooseBase(this, _description)[_description] = fields.description;
	    babelHelpers.classPrivateFieldLooseBase(this, _eventsCount)[_eventsCount] = fields.eventsCount;
	    babelHelpers.classPrivateFieldLooseBase(this, _permissions)[_permissions] = fields.permissions;
	    babelHelpers.classPrivateFieldLooseBase(this, _channelId)[_channelId] = fields.channelId;
	    babelHelpers.classPrivateFieldLooseBase(this, _isMuted)[_isMuted] = fields.isMuted;
	    babelHelpers.classPrivateFieldLooseBase(this, _isBanned)[_isBanned] = fields.isBanned;
	    babelHelpers.classPrivateFieldLooseBase(this, _newCount)[_newCount] = fields.newCount;
	    babelHelpers.classPrivateFieldLooseBase(this, _updatedAt)[_updatedAt] = fields.updatedAt || 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _channel)[_channel] = fields.channel;
	    babelHelpers.classPrivateFieldLooseBase(this, _isSelected)[_isSelected] = false;
	    this.fields = fields;
	  }
	  get id() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  get closed() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _closed)[_closed];
	  }
	  get name() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	  get description() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _description)[_description];
	  }
	  get eventsCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _eventsCount)[_eventsCount];
	  }
	  get permissions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _permissions)[_permissions];
	  }
	  get channelId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _channelId)[_channelId];
	  }
	  get isMuted() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isMuted)[_isMuted];
	  }
	  set isMuted(isMuted) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isMuted)[_isMuted] = isMuted;
	  }
	  get isBanned() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isBanned)[_isBanned];
	  }
	  set isBanned(isBanned) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isBanned)[_isBanned] = isBanned;
	  }
	  get newCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _newCount)[_newCount];
	  }
	  get isSelected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isSelected)[_isSelected];
	  }
	  set isSelected(isSelected) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isSelected)[_isSelected] = isSelected;
	  }
	  get updatedAt() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _updatedAt)[_updatedAt];
	  }
	  get channel() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _channel)[_channel];
	  }
	  set channel(channel) {
	    babelHelpers.classPrivateFieldLooseBase(this, _channel)[_channel] = channel;
	  }
	}

	var _update = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	var _create = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("create");
	var _delete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delete");
	var _eventScorerUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventScorerUpdated");
	class PullRequests extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _eventScorerUpdated, {
	      value: _eventScorerUpdated2
	    });
	    Object.defineProperty(this, _delete, {
	      value: _delete2
	    });
	    Object.defineProperty(this, _create, {
	      value: _create2
	    });
	    Object.defineProperty(this, _update, {
	      value: _update2
	    });
	    this.setEventNamespace('Calendar.OpenEvents.List.CategoryManager.PullRequests');
	  }
	  getModuleId() {
	    return 'calendar';
	  }
	  getMap() {
	    return {
	      EVENT_CATEGORY_CREATED: babelHelpers.classPrivateFieldLooseBase(this, _create)[_create].bind(this),
	      EVENT_CATEGORY_UPDATED: babelHelpers.classPrivateFieldLooseBase(this, _update)[_update].bind(this),
	      EVENT_CATEGORY_DELETED: babelHelpers.classPrivateFieldLooseBase(this, _delete)[_delete].bind(this),
	      OPEN_EVENT_SCORER_UPDATED: babelHelpers.classPrivateFieldLooseBase(this, _eventScorerUpdated)[_eventScorerUpdated].bind(this)
	    };
	  }
	}
	function _update2(event) {
	  this.emit('update', event);
	}
	function _create2(event) {
	  this.emit('create', event);
	}
	function _delete2(event) {
	  this.emit('delete', event);
	}
	function _eventScorerUpdated2(event) {
	  this.emit('eventScorerUpdated', event);
	}

	class CategoryApi {
	  static async list(params) {
	    const response = await BX.ajax.runAction('calendar.open-events.Category.list', {
	      data: params
	    });
	    return response.data;
	  }
	  static async add(fields) {
	    const response = await BX.ajax.runAction('calendar.open-events.Category.add', {
	      data: {
	        name: fields.name,
	        description: fields.description,
	        closed: fields.closed,
	        attendees: fields.attendees,
	        departmentIds: fields.departmentIds,
	        channelId: fields.channelId
	      }
	    });
	    return response.data;
	  }
	  static update(fields) {
	    return BX.ajax.runAction('calendar.open-events.Category.update', {
	      data: {
	        id: fields.id,
	        name: fields.name,
	        description: fields.description
	      }
	    });
	  }
	  static setMute(id, muteState) {
	    return BX.ajax.runAction('calendar.open-events.Category.setMute', {
	      data: {
	        id,
	        muteState
	      }
	    });
	  }
	  static setBan(id, banState) {
	    return BX.ajax.runAction('calendar.open-events.Category.setBan', {
	      data: {
	        id,
	        banState
	      }
	    });
	  }
	  static async getChannelInfo(id) {
	    const response = await BX.ajax.runAction('calendar.open-events.Category.getChannelInfo', {
	      data: {
	        id
	      }
	    });
	    return response.data;
	  }
	}

	const ListKeys = Object.freeze({
	  notBanned: 'notBanned',
	  banned: 'banned',
	  search: 'search'
	});
	var _categories = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categories");
	var _categoryIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryIds");
	var _categoryPromises = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryPromises");
	var _lastLoadedPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastLoadedPage");
	var _loadedLists = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadedLists");
	var _query = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("query");
	var _subscribeToPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPull");
	var _createCategoryPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createCategoryPull");
	var _updateCategoryPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCategoryPull");
	var _deleteCategoryPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteCategoryPull");
	var _onPullEventScorerUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPullEventScorerUpdated");
	var _addNewCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addNewCategory");
	var _prepareCategories = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareCategories");
	var _getListIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getListIds");
	var _loadCategories = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadCategories");
	var _getListKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getListKey");
	var _loadCategoryById = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadCategoryById");
	var _updateCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCounters");
	var _getCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCategory");
	var _updateCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCategory");
	var _buildCategoryModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildCategoryModel");
	class Manager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _buildCategoryModel, {
	      value: _buildCategoryModel2
	    });
	    Object.defineProperty(this, _updateCategory, {
	      value: _updateCategory2
	    });
	    Object.defineProperty(this, _getCategory, {
	      value: _getCategory2
	    });
	    Object.defineProperty(this, _updateCounters, {
	      value: _updateCounters2
	    });
	    Object.defineProperty(this, _loadCategoryById, {
	      value: _loadCategoryById2
	    });
	    Object.defineProperty(this, _getListKey, {
	      value: _getListKey2
	    });
	    Object.defineProperty(this, _loadCategories, {
	      value: _loadCategories2
	    });
	    Object.defineProperty(this, _getListIds, {
	      value: _getListIds2
	    });
	    Object.defineProperty(this, _prepareCategories, {
	      value: _prepareCategories2
	    });
	    Object.defineProperty(this, _addNewCategory, {
	      value: _addNewCategory2
	    });
	    Object.defineProperty(this, _onPullEventScorerUpdated, {
	      value: _onPullEventScorerUpdated2
	    });
	    Object.defineProperty(this, _deleteCategoryPull, {
	      value: _deleteCategoryPull2
	    });
	    Object.defineProperty(this, _updateCategoryPull, {
	      value: _updateCategoryPull2
	    });
	    Object.defineProperty(this, _createCategoryPull, {
	      value: _createCategoryPull2
	    });
	    Object.defineProperty(this, _subscribeToPull, {
	      value: _subscribeToPull2
	    });
	    Object.defineProperty(this, _categories, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _categoryIds, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _categoryPromises, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _lastLoadedPage, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _loadedLists, {
	      writable: true,
	      value: {
	        [ListKeys.notBanned]: false,
	        [ListKeys.banned]: false,
	        [ListKeys.search]: false
	      }
	    });
	    Object.defineProperty(this, _query, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Calendar.OpenEvents.List.CategoryManager');
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPull)[_subscribeToPull]();
	  }
	  async addCategory(fields) {
	    const categoryDto = await CategoryApi.add(fields);
	    babelHelpers.classPrivateFieldLooseBase(this, _addNewCategory)[_addNewCategory](categoryDto);
	  }
	  async updateCategory(fields) {
	    const category = babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](fields.id);
	    category.channel.title = fields.name;
	    return CategoryApi.update(fields);
	  }
	  async setMute(categoryId, isMuted) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](categoryId, {
	      isMuted
	    });
	    void CategoryApi.setMute(categoryId, isMuted);
	  }
	  async setBan(categoryId, isBanned) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](categoryId, {
	      isBanned
	    });
	    void CategoryApi.setBan(categoryId, isBanned);
	  }
	  async getChannelInfo(categoryId) {
	    var _category$channel;
	    const category = babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].find(category => category.id === categoryId);
	    (_category$channel = category.channel) != null ? _category$channel : category.channel = await CategoryApi.getChannelInfo(categoryId);
	    return category.channel;
	  }
	  async bubbleUp(categoryId) {
	    var _babelHelpers$classPr;
	    const category = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](categoryId)) != null ? _babelHelpers$classPr : await babelHelpers.classPrivateFieldLooseBase(this, _loadCategoryById)[_loadCategoryById](categoryId);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](category.id, {
	      updatedAt: Date.now()
	    });
	  }
	  async searchMore() {
	    var _babelHelpers$classPr2;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.search]) {
	      return [];
	    }
	    const query = babelHelpers.classPrivateFieldLooseBase(this, _query)[_query];
	    const listKey = babelHelpers.classPrivateFieldLooseBase(this, _getListKey)[_getListKey]({
	      query
	    });
	    const countBefore = babelHelpers.classPrivateFieldLooseBase(this, _getListIds)[_getListIds](listKey).length;
	    const lastPage = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _lastLoadedPage)[_lastLoadedPage][listKey]) != null ? _babelHelpers$classPr2 : -1;
	    const categories = await this.getCategories({
	      query,
	      page: lastPage + 1
	    });
	    if (categories.length === countBefore) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][listKey] = true;
	    }
	    return categories;
	  }
	  async loadMore() {
	    var _babelHelpers$classPr3;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.notBanned] && babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.banned]) {
	      return [];
	    }
	    const isBanned = babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.notBanned] && !babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.banned];
	    const listKey = babelHelpers.classPrivateFieldLooseBase(this, _getListKey)[_getListKey]({
	      isBanned
	    });
	    const countBefore = babelHelpers.classPrivateFieldLooseBase(this, _getListIds)[_getListIds](listKey).length;
	    const lastPage = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _lastLoadedPage)[_lastLoadedPage][listKey]) != null ? _babelHelpers$classPr3 : -1;
	    const categories = await this.getCategories({
	      isBanned,
	      page: lastPage + 1
	    });
	    if (categories.length === countBefore) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][listKey] = true;
	      return this.loadMore();
	    }
	    return categories;
	  }
	  async searchCategories(query) {
	    if (query !== babelHelpers.classPrivateFieldLooseBase(this, _query)[_query]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loadedLists)[_loadedLists][ListKeys.search] = false;
	      delete babelHelpers.classPrivateFieldLooseBase(this, _lastLoadedPage)[_lastLoadedPage][ListKeys.search];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises][ListKeys.search];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds][ListKeys.search];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _query)[_query] = query;
	    return this.getCategories({
	      query
	    });
	  }
	  async getCategories(params = {
	    isBanned: false
	  }) {
	    var _babelHelpers$classPr4, _babelHelpers$classPr5;
	    const listKey = babelHelpers.classPrivateFieldLooseBase(this, _getListKey)[_getListKey](params);
	    const categories = await babelHelpers.classPrivateFieldLooseBase(this, _loadCategories)[_loadCategories](params);
	    const alreadyLoadedIds = babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].map(it => it.id);
	    const newCategories = categories.filter(it => !alreadyLoadedIds.includes(it.id));
	    babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].push(...newCategories);
	    const alreadyLoadedListIds = babelHelpers.classPrivateFieldLooseBase(this, _getListIds)[_getListIds](listKey);
	    const newListCategories = categories.filter(it => !alreadyLoadedListIds.includes(it.id));
	    (_babelHelpers$classPr5 = (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds])[listKey]) != null ? _babelHelpers$classPr5 : _babelHelpers$classPr4[listKey] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds][listKey].push(...newListCategories.map(it => it.id));
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareCategories)[_prepareCategories](listKey);
	  }
	  incrementNewCounter(categoryId) {
	    const category = babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](categoryId);
	    if (category === null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](categoryId, {
	      newCount: category.newCount + 1
	    });
	  }
	  decrementNewCounter(categoryId) {
	    const category = babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](categoryId);
	    if (category === null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](categoryId, {
	      newCount: category.newCount - 1
	    });
	  }
	}
	function _subscribeToPull2() {
	  if (!BX.PULL) {
	    console.info('BX.PULL not initialized');
	    return;
	  }
	  const pullRequests = new PullRequests();
	  pullRequests.subscribe('create', babelHelpers.classPrivateFieldLooseBase(this, _createCategoryPull)[_createCategoryPull].bind(this));
	  pullRequests.subscribe('update', babelHelpers.classPrivateFieldLooseBase(this, _updateCategoryPull)[_updateCategoryPull].bind(this));
	  pullRequests.subscribe('delete', babelHelpers.classPrivateFieldLooseBase(this, _deleteCategoryPull)[_deleteCategoryPull].bind(this));
	  pullRequests.subscribe('eventScorerUpdated', babelHelpers.classPrivateFieldLooseBase(this, _onPullEventScorerUpdated)[_onPullEventScorerUpdated].bind(this));
	  BX.PULL.subscribe(pullRequests);
	}
	function _createCategoryPull2(event) {
	  const {
	    fields
	  } = event.getData();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](fields.id)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _addNewCategory)[_addNewCategory](fields);
	}
	function _updateCategoryPull2(event) {
	  const {
	    fields
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](fields.id, fields);
	}
	function _deleteCategoryPull2(event) {
	  const {
	    fields
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories] = babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].filter(category => category.id !== fields.id);
	  this.emit('update');
	}
	function _onPullEventScorerUpdated2(event) {
	  const {
	    fields: {
	      categoriesCounter
	    }
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters](categoriesCounter);
	}
	function _addNewCategory2(categoryDto) {
	  var _babelHelpers$classPr6;
	  categoryDto.updatedAt = Date.now();
	  const category = new CategoryModel(categoryDto);
	  babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].push(category);
	  (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds][ListKeys.notBanned]) == null ? void 0 : _babelHelpers$classPr6.push(category.id);
	  this.emit('update');
	}
	function _prepareCategories2(listKey) {
	  const listIds = babelHelpers.classPrivateFieldLooseBase(this, _getListIds)[_getListIds](listKey);
	  return babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].filter(category => listIds.includes(category.id)).map(category => new CategoryModel(category.fields));
	}
	function _getListIds2(listKey) {
	  const listKeys = listKey === ListKeys.search ? [ListKeys.search] : [ListKeys.notBanned, ListKeys.banned];
	  const listIds = Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds]).filter(([listKey]) => listKeys.includes(listKey)).flatMap(([, categoryIds]) => categoryIds);
	  return [...new Set(listIds)];
	}
	async function _loadCategories2(params) {
	  var _params$isBanned, _params$query, _params$page, _babelHelpers$classPr7, _babelHelpers$classPr8, _babelHelpers$classPr9, _babelHelpers$classPr10;
	  const isBanned = (_params$isBanned = params.isBanned) != null ? _params$isBanned : null;
	  const query = (_params$query = params.query) != null ? _params$query : '';
	  const page = (_params$page = params.page) != null ? _params$page : 0;
	  const listKey = babelHelpers.classPrivateFieldLooseBase(this, _getListKey)[_getListKey](params);
	  (_babelHelpers$classPr8 = (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises])[listKey]) != null ? _babelHelpers$classPr8 : _babelHelpers$classPr7[listKey] = {};
	  (_babelHelpers$classPr10 = (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises][listKey])[page]) != null ? _babelHelpers$classPr10 : _babelHelpers$classPr9[page] = CategoryApi.list({
	    isBanned,
	    query,
	    page
	  });
	  const categories = await babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises][listKey][page];
	  babelHelpers.classPrivateFieldLooseBase(this, _lastLoadedPage)[_lastLoadedPage][listKey] = page;
	  return categories.map(category => new CategoryModel(category));
	}
	function _getListKey2({
	  isBanned,
	  query
	}) {
	  if (main_core.Type.isStringFilled(query)) {
	    return ListKeys.search;
	  }
	  if (isBanned === true) {
	    return ListKeys.banned;
	  }
	  return ListKeys.notBanned;
	}
	async function _loadCategoryById2(categoryId) {
	  var _babelHelpers$classPr11, _babelHelpers$classPr12, _babelHelpers$classPr13, _babelHelpers$classPr14, _babelHelpers$classPr15;
	  const promiseByIdKey = 'byId';
	  (_babelHelpers$classPr12 = (_babelHelpers$classPr11 = babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises])[promiseByIdKey]) != null ? _babelHelpers$classPr12 : _babelHelpers$classPr11[promiseByIdKey] = {};
	  (_babelHelpers$classPr14 = (_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises][promiseByIdKey])[categoryId]) != null ? _babelHelpers$classPr14 : _babelHelpers$classPr13[categoryId] = CategoryApi.list({
	    categoryId
	  });
	  const categories = await babelHelpers.classPrivateFieldLooseBase(this, _categoryPromises)[_categoryPromises][promiseByIdKey][categoryId];
	  const categoryDto = categories.find(it => it.id === categoryId);
	  const category = new CategoryModel(categoryDto);
	  babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].push(category);
	  const listKey = category.isBanned ? ListKeys.banned : ListKeys.notBanned;
	  (_babelHelpers$classPr15 = babelHelpers.classPrivateFieldLooseBase(this, _categoryIds)[_categoryIds][listKey]) == null ? void 0 : _babelHelpers$classPr15.push(category.id);
	  return category;
	}
	function _updateCounters2(categoryCounters) {
	  for (const [id, newCount] of Object.entries(categoryCounters)) {
	    const categoryId = parseInt(id, 10);
	    const category = babelHelpers.classPrivateFieldLooseBase(this, _getCategory)[_getCategory](categoryId);
	    if (category === null) {
	      continue;
	    }
	    const eventsCreated = newCount > category.newCount;
	    const updatedAt = eventsCreated ? Date.now() : category.updatedAt;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCategory)[_updateCategory](categoryId, {
	      newCount,
	      updatedAt
	    });
	  }
	}
	function _getCategory2(categoryId) {
	  var _babelHelpers$classPr16;
	  return (_babelHelpers$classPr16 = babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].find(category => category.id === categoryId)) != null ? _babelHelpers$classPr16 : null;
	}
	function _updateCategory2(categoryId, fields) {
	  babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories] = babelHelpers.classPrivateFieldLooseBase(this, _categories)[_categories].map(category => {
	    if (category.id !== categoryId) {
	      return category;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _buildCategoryModel)[_buildCategoryModel](category, fields);
	  });
	  this.emit('update');
	}
	function _buildCategoryModel2(category, fields = {}) {
	  var _fields$closed, _fields$name, _fields$description, _fields$eventsCount, _fields$isMuted, _fields$isBanned, _fields$newCount, _fields$isSelected, _fields$updatedAt, _fields$channel;
	  return new CategoryModel({
	    id: category.id,
	    closed: (_fields$closed = fields.closed) != null ? _fields$closed : category.closed,
	    name: (_fields$name = fields.name) != null ? _fields$name : category.name,
	    description: (_fields$description = fields.description) != null ? _fields$description : category.description,
	    eventsCount: (_fields$eventsCount = fields.eventsCount) != null ? _fields$eventsCount : category.eventsCount,
	    permissions: category.permissions,
	    channelId: category.channelId,
	    isMuted: (_fields$isMuted = fields.isMuted) != null ? _fields$isMuted : category.isMuted,
	    isBanned: (_fields$isBanned = fields.isBanned) != null ? _fields$isBanned : category.isBanned,
	    newCount: (_fields$newCount = fields.newCount) != null ? _fields$newCount : category.newCount,
	    isSelected: (_fields$isSelected = fields.isSelected) != null ? _fields$isSelected : category.isSelected,
	    updatedAt: (_fields$updatedAt = fields.updatedAt) != null ? _fields$updatedAt : category.updatedAt,
	    channel: (_fields$channel = fields.channel) != null ? _fields$channel : category.channel
	  });
	}
	const CategoryManager = new Manager();

	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	class ExtensionSettings {
	  constructor() {
	    Object.defineProperty(this, _config, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config] = main_core.Extension.getSettings('calendar.open-events.list');
	  }
	  get currentUserId() {
	    return main_core.Text.toNumber(babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].currentUserId);
	  }
	  get openEventSection() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].openEventSection;
	  }
	  get currentUserTimeOffset() {
	    return main_core.Text.toNumber(babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].currentUserTimeOffset);
	  }
	  get pullEventUserFieldsKey() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].pullEventUserFieldsKey.toString();
	  }
	}
	const AppSettings = new ExtensionSettings();

	const CategoryEditForm = {
	  data() {
	    return {
	      id: 'calendar-open-events-category-edit-popup',
	      params: {},
	      category: null,
	      create: false,
	      popup: null,
	      name: '',
	      description: '',
	      closed: false,
	      selectedChannelId: null
	    };
	  },
	  computed: {
	    isEdit() {
	      return !this.create;
	    }
	  },
	  methods: {
	    show(params = {}) {
	      var _PopupManager$getPopu;
	      this.create = params.create;
	      this.category = params.category;
	      if (this.category) {
	        if (!this.category.channel) {
	          CategoryManager.getChannelInfo(this.category.id).then(channelInfo => {
	            this.category.channel = channelInfo;
	          });
	        }
	        this.name = this.category.name;
	        this.description = this.category.description;
	        this.closed = this.category.closed;
	      }
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(this.id)) == null ? void 0 : _PopupManager$getPopu.destroy();
	      this.popup = main_popup.PopupManager.create({
	        id: this.id,
	        autoHide: true,
	        autoHideHandler: event => {
	          const isClickInside = this.popup.getPopupContainer().contains(event.target);
	          let isClickUserSelector = false;
	          if (this.userSelector) {
	            const userSelectorPopup = this.userSelector.getDialog().getPopup();
	            isClickUserSelector = userSelectorPopup.getPopupContainer().contains(event.target);
	          }
	          let isClickChannelSelector = false;
	          if (this.channelSelector) {
	            const channelSelectorPopup = this.channelSelector.getDialog().getPopup();
	            isClickChannelSelector = channelSelectorPopup.getPopupContainer().contains(event.target);
	          }
	          return !isClickInside && !isClickUserSelector && !isClickChannelSelector;
	        },
	        width: 600,
	        content: this.$refs.popupContent,
	        className: 'calendar-open-events-category-edit-popup-container',
	        titleBar: true,
	        draggable: true
	      });
	      this.renderSwitcher();
	      if (this.create) {
	        this.renderChannelSelector();
	        this.renderUserSelector();
	      }
	      this.popup.show();
	      this.$refs.inputName.focus();
	    },
	    async onCreateButtonClick() {
	      var _this$userSelector, _this$userSelector2;
	      const attendees = (_this$userSelector = this.userSelector) == null ? void 0 : _this$userSelector.getTags().filter(tag => tag.entityId === 'user').map(tag => tag.id);
	      const departmentIds = (_this$userSelector2 = this.userSelector) == null ? void 0 : _this$userSelector2.getTags().filter(tag => tag.entityId === 'department').map(tag => tag.id);
	      await CategoryManager.addCategory({
	        name: this.name,
	        description: this.description,
	        closed: this.closed,
	        attendees: this.closed ? attendees : [],
	        departmentIds: this.closed ? departmentIds : [],
	        channelId: this.selectedChannelId
	      });
	      this.clearFields();
	      this.popup.close();
	    },
	    async onSaveButtonClick() {
	      await CategoryManager.updateCategory({
	        id: this.category.id,
	        name: this.name,
	        description: this.description
	      });
	      this.clearFields();
	      this.popup.close();
	    },
	    onCancelButtonClick() {
	      this.clearFields();
	      this.popup.close();
	    },
	    clearFields() {
	      var _this$userSelector3, _this$channelSelector;
	      this.name = '';
	      this.description = '';
	      this.closed = false;
	      (_this$userSelector3 = this.userSelector) == null ? void 0 : _this$userSelector3.getTags().forEach(tag => {
	        if (tag.getEntityId() === 'user' && tag.getId() === AppSettings.currentUserId) {
	          return;
	        }
	        this.userSelector.removeTag(tag, false);
	      });
	      (_this$channelSelector = this.channelSelector) == null ? void 0 : _this$channelSelector.getTags().forEach(tag => this.channelSelector.removeTag(tag, false));
	      this.selectedChannelId = null;
	    },
	    renderSwitcher() {
	      if (this.switcher) {
	        this.switcher.check(this.closed);
	        this.switcher.disable(Boolean(this.selectedChannelId));
	        return;
	      }
	      this.switcher = new ui_switcher.Switcher({
	        node: this.$refs.closedSwitcher,
	        checked: this.closed,
	        size: ui_switcher.SwitcherSize.extraSmall,
	        disabled: Boolean(this.selectedChannelId),
	        handlers: {
	          toggled: () => {
	            this.closed = this.switcher.isChecked();
	          }
	        }
	      });
	    },
	    renderUserSelector() {
	      if (this.userSelector) {
	        this.userSelector.renderTo(this.$refs.userSelector);
	        return;
	      }
	      const currentUserItem = ['user', AppSettings.currentUserId];
	      this.userSelector = new ui_entitySelector.TagSelector({
	        dialogOptions: {
	          context: 'CALENDAR_OPEN_EVENTS_CATEGORY_EDIT_FORM',
	          showAvatars: true,
	          dropdownMode: true,
	          preload: true,
	          entities: [{
	            id: 'user'
	          }, {
	            id: 'department',
	            options: {
	              selectMode: 'usersAndDepartments',
	              allowFlatDepartments: true,
	              allowSelectRootDepartment: true
	            }
	          }],
	          preselectedItems: [currentUserItem],
	          undeselectedItems: [currentUserItem]
	        }
	      });
	      this.userSelector.renderTo(this.$refs.userSelector);
	    },
	    renderChannelSelector() {
	      if (this.channelSelector) {
	        this.channelSelector.renderTo(this.$refs.channelSelector);
	        return;
	      }
	      this.channelSelector = new ui_entitySelector.TagSelector({
	        multiple: false,
	        dialogOptions: {
	          context: 'CALENDAR_OPEN_EVENTS_CATEGORY_EDIT_FORM',
	          dropdownMode: true,
	          preload: true,
	          entities: [{
	            id: 'im-channel',
	            dynamicLoad: true
	          }],
	          events: {
	            'Item:onSelect': this.onChannelSelected.bind(this),
	            'Item:onDeselect': this.onChannelDeselected.bind(this)
	          },
	          multiple: false
	        }
	      });
	      this.channelSelector.renderTo(this.$refs.channelSelector);
	    },
	    onChannelSelected(event) {
	      const {
	        item: tag
	      } = event.getData();
	      this.selectedChannelId = tag.id;
	      this.closed = tag.customData.get('closed');
	      if (!this.name || !this.userChangedName) {
	        this.name = tag.getTitle();
	      }
	      this.renderSwitcher();
	    },
	    onChannelDeselected(event) {
	      const {
	        item: tag
	      } = event.getData();
	      this.selectedChannelId = null;
	      this.closed = false;
	      if (this.name === tag.getTitle()) {
	        this.name = '';
	        this.userChangedName = false;
	      }
	      this.renderSwitcher();
	    },
	    getFirstLetters(text) {
	      var _words$0$, _words$, _words$1$, _words$2;
	      const words = text.split(/[\s,]/).filter(word => /[\p{L}\p{N} ]/u.test(word[0]));
	      return ((_words$0$ = (_words$ = words[0]) == null ? void 0 : _words$[0]) != null ? _words$0$ : '') + ((_words$1$ = (_words$2 = words[1]) == null ? void 0 : _words$2[0]) != null ? _words$1$ : '');
	    },
	    onNameInput() {
	      this.userChangedName = true;
	    }
	  },
	  template: `
		<div class="calendar-open-events-category-edit-popup" ref="popupContent">
			<input
				class="calendar-open-events-category-edit-name-input"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_NAME')"
				v-model="name"
				@input="onNameInput"
				ref="inputName"
			>
			<div class="calendar-open-events-category-edit-channel --edit" v-show="create">
				<div class="ui-icon-set --speaker-mouthpiece" v-if="create"></div>
				<div class="calendar-open-events-category-edit-channel-text" v-if="create">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CHOOSE_CHANNEL') }}
				</div>
				<div
					class="calendar-open-events-category-edit-channel-selector"
					ref="channelSelector"
					v-show="create"
				></div>
			</div>
			<div class="calendar-open-events-category-edit-channel --edit" v-if="!create && !category?.channel">
				<div class="ui-icon-set --speaker-mouthpiece"></div>
				<div class="calendar-open-events-category-edit-channel-loader"></div>
			</div>
			<div class="calendar-open-events-category-edit-channel" v-if="category?.channel">
				<div class="ui-icon-set --speaker-mouthpiece"></div>
				<img
					v-if="category.channel.avatar"
					class="calendar-open-events-category-edit-channel-avatar"
					:src="category.channel.avatar"
				>
				<div
					v-if="!category.channel.avatar && getFirstLetters(category.channel.title)"
					class="calendar-open-events-category-edit-channel-avatar"
					:style="'background-color: ' + category.channel.color"
				>
					{{ getFirstLetters(category.channel.title) }}
				</div>
				<div class="calendar-open-events-category-edit-channel-name">{{ category.channel.title }}</div>
			</div>
			<div
				class="calendar-open-events-category-edit-close"
				:class="{
					'--closed': closed,
					'--disabled': !create,
				}"
			>
				<div class="calendar-open-events-category-edit-close-switcher">
					<div ref="closedSwitcher"></div>
				</div>
				<div class="calendar-open-events-category-edit-close-body">
					<div class="calendar-open-events-category-edit-close-title">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CLOSE') }}
					</div>
					<div class="calendar-open-events-category-edit-close-hint">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CLOSE_HINT') }}
					</div>
					<div
						class="calendar-open-events-category-edit-close-users"
						ref="userSelector"
						v-show="create && closed && !selectedChannelId"
					></div>
				</div>
			</div>
			<textarea
				class="calendar-open-events-category-edit-description-textarea"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_DESCRIPTION')"
				v-model="description"
			></textarea>
			<div class="calendar-open-events-category-edit-buttons">
				<div
					v-if="create"
					class="calendar-open-events-category-edit-button-create"
					@click="onCreateButtonClick"
				>
					<div class="ui-icon-set --calendar-1"></div>
					<div class="calendar-open-events-category-edit-button-create-text">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CREATE') }}
					</div>
				</div>
				<div
					v-if="isEdit"
					class="calendar-open-events-category-edit-button-create"
					@click="onSaveButtonClick"
				>
					<div class="ui-icon-set --calendar-1"></div>
					<div class="calendar-open-events-category-edit-button-create-text">
						{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_SAVE') }}
					</div>
				</div>
				<div class="calendar-open-events-category-edit-button-cancel" @click="onCancelButtonClick">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_CANCEL') }}
				</div>
			</div>
		</div>
	`
	};

	const CategoriesTitle = {
	  methods: {
	    onSearchClick() {
	      this.$store.dispatch('setSearchMode', true);
	    },
	    onAddClick() {
	      this.$refs.editForm.show({
	        create: true
	      });
	    }
	  },
	  components: {
	    CategoryEditForm
	  },
	  template: `
		<div class="calendar-open-events-list-categories-title">
			<div class="calendar-open-events-list-categories-title-text">
				{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORIES') }}
			</div>
			<div class="calendar-open-events-list-categories-title-button" @click="onSearchClick()">
				<div class="ui-icon-set --search-2"></div>
			</div>
			<div class="calendar-open-events-list-categories-title-button" @click="onAddClick()">
				<div class="ui-icon-set --plus-30"></div>
			</div>
		</div>
		<CategoryEditForm ref="editForm"/>
	`
	};

	const CategoriesSearch = {
	  created() {
	    this.searchDebounced = main_core.Runtime.debounce(this.search, 500, this);
	  },
	  mounted() {
	    this.$refs.input.focus();
	    main_core.Event.bind(document, 'click', this.handleAutoHide, true);
	  },
	  unmounted() {
	    main_core.Event.unbind(document, 'click', this.handleAutoHide, true);
	  },
	  methods: {
	    handleAutoHide(event) {
	      if (this.shouldHideForm(event)) {
	        void this.closeSearch();
	      }
	    },
	    shouldHideForm(event) {
	      const queryIsEmpty = !main_core.Type.isStringFilled(this.getSearchQuery());
	      const clickOnSelf = this.$refs.search.contains(event.target);
	      return queryIsEmpty && !clickOnSelf;
	    },
	    onCloseSearchClick() {
	      void this.closeSearch();
	    },
	    async closeSearch() {
	      const categories = await CategoryManager.getCategories();
	      await this.$store.dispatch('setCategories', categories);
	      await this.$store.dispatch('setSearchMode', false);
	    },
	    async onSearchInput() {
	      const query = this.getSearchQuery();
	      if (main_core.Type.isStringFilled(query)) {
	        this.searchDebounced(query);
	      } else {
	        const categories = await CategoryManager.getCategories();
	        this.$store.dispatch('setCategories', categories);
	      }
	    },
	    async search(query) {
	      await this.$store.dispatch('setCategoriesQuery', query);
	      const categories = await CategoryManager.searchCategories(query);
	      if (query === this.getSearchQuery()) {
	        this.$store.dispatch('setCategories', categories);
	      }
	    },
	    getSearchQuery() {
	      return this.$refs.input.value.trim();
	    }
	  },
	  template: `
		<div class="calendar-open-events-list-categories-search" ref="search">
			<input
				ref="input"
				class="calendar-open-events-list-categories-search-input"
				type="text"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_SEARCH_CATEGORY')"
				@input="onSearchInput()"
			>
			<div class="calendar-open-events-list-categories-close-search-button" @click="onCloseSearchClick()">
				<div class="ui-icon-set --cross-circle-70"></div>
			</div>
		</div>
	`
	};

	const CategoriesHeader = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      isSearchMode: 'isSearchMode'
	    })
	  },
	  components: {
	    CategoriesTitle,
	    CategoriesSearch
	  },
	  template: `
		<div class="calendar-open-events-list-categories-title-container">
			<CategoriesSearch v-if="isSearchMode"/>
			<CategoriesTitle v-else/>
		</div>
	`
	};

	var _create$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("create");
	var _update$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	var _delete$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delete");
	class PullRequests$1 extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _delete$1, {
	      value: _delete2$1
	    });
	    Object.defineProperty(this, _update$1, {
	      value: _update2$1
	    });
	    Object.defineProperty(this, _create$1, {
	      value: _create2$1
	    });
	    this.setEventNamespace('Calendar.OpenEvents.List.EventManager.PullRequests');
	  }
	  getModuleId() {
	    return 'calendar';
	  }
	  getMap() {
	    return {
	      OPEN_EVENT_CREATED: babelHelpers.classPrivateFieldLooseBase(this, _create$1)[_create$1].bind(this),
	      OPEN_EVENT_UPDATED: babelHelpers.classPrivateFieldLooseBase(this, _update$1)[_update$1].bind(this),
	      OPEN_EVENT_DELETED: babelHelpers.classPrivateFieldLooseBase(this, _delete$1)[_delete$1].bind(this)
	    };
	  }
	}
	function _create2$1(event) {
	  this.emit('create', event);
	}
	function _update2$1(event) {
	  this.emit('update', event);
	}
	function _delete2$1(event) {
	  this.emit('delete', event);
	}

	var _id$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _name$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _isFullDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFullDay");
	var _dateFromTs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFromTs");
	var _dateToTs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateToTs");
	var _commentsCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("commentsCount");
	var _isAttendee = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAttendee");
	var _attendeesCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attendeesCount");
	var _creatorId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("creatorId");
	var _eventOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventOptions");
	var _categoryId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryId");
	var _categoryName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryName");
	var _color = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("color");
	var _categoryChannelId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryChannelId");
	var _threadId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("threadId");
	var _isNew = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNew");
	var _rrule = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rrule");
	var _rruleDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rruleDescription");
	var _exdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exdate");
	var _initFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initFields");
	var _getDateCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDateCode");
	class EventModel {
	  constructor(_fields = {}) {
	    Object.defineProperty(this, _getDateCode, {
	      value: _getDateCode2
	    });
	    Object.defineProperty(this, _initFields, {
	      value: _initFields2
	    });
	    Object.defineProperty(this, _id$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isFullDay, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateFromTs, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateToTs, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _commentsCount, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isAttendee, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _attendeesCount, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _creatorId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _eventOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _categoryId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _categoryName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _color, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _categoryChannelId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _threadId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isNew, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rrule, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rruleDescription, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _exdate, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initFields)[_initFields](_fields);
	  }
	  updateFields(fields) {
	    if ('name' in fields) {
	      babelHelpers.classPrivateFieldLooseBase(this, _name$1)[_name$1] = fields.name;
	    }
	    if (!main_core.Type.isBoolean(fields.isAttendee)) {
	      delete fields.isAttendee;
	    }
	    if (!main_core.Type.isNumber(fields.commentsCount)) {
	      delete fields.commentsCount;
	    }
	    if ('isAttendee' in fields) {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee] && fields.isAttendee) {
	        this.incrementAttendeesCount();
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee] && !fields.isAttendee) {
	        this.decrementAttendeesCount();
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee] = fields.isAttendee;
	    }
	    if ('attendeesCount' in fields) {
	      babelHelpers.classPrivateFieldLooseBase(this, _attendeesCount)[_attendeesCount] = fields.attendeesCount;
	    }
	    Object.assign(this.fields, fields);
	  }
	  get uniqueId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1].toString() + '|' + babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs].toString();
	  }
	  get id() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1];
	  }
	  get name() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name$1)[_name$1];
	  }
	  get commentsCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _commentsCount)[_commentsCount];
	  }
	  get isAttendee() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee];
	  }
	  set isAttendee(isAttendee) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee] = isAttendee;
	    this.updateFields({
	      isAttendee
	    });
	  }
	  get attendeesCount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _attendeesCount)[_attendeesCount];
	  }
	  set attendeesCount(attendeesCount) {
	    babelHelpers.classPrivateFieldLooseBase(this, _attendeesCount)[_attendeesCount] = attendeesCount;
	    this.updateFields({
	      attendeesCount
	    });
	  }
	  incrementAttendeesCount() {
	    this.attendeesCount = ++this.attendeesCount;
	  }
	  decrementAttendeesCount() {
	    this.attendeesCount = --this.attendeesCount;
	  }
	  get creatorId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _creatorId)[_creatorId];
	  }
	  get eventOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _eventOptions)[_eventOptions];
	  }
	  get categoryId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _categoryId)[_categoryId];
	  }
	  get categoryName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName];
	  }
	  get duration() {
	    return this.dateTo.getTime() - this.dateFrom.getTime();
	  }
	  get dateFrom() {
	    return new Date(babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs] * 1000);
	  }
	  get dateTo() {
	    return new Date(babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs] * 1000);
	  }
	  get formattedDateTime() {
	    const isSameDate = babelHelpers.classPrivateFieldLooseBase(this, _getDateCode)[_getDateCode](babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs]) === babelHelpers.classPrivateFieldLooseBase(this, _getDateCode)[_getDateCode](babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs]);
	    const startsInCurrentYear = this.dateFrom.getFullYear() === new Date().getFullYear();
	    const endsInCurrentYear = this.dateTo.getFullYear() === new Date().getFullYear();
	    if (isSameDate) {
	      const dateFormat = startsInCurrentYear ? 'DAY_OF_WEEK_MONTH_FORMAT' : 'FULL_DATE_FORMAT';
	      const date = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat(dateFormat), babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs]);
	      if (this.isFullDay) {
	        return date;
	      }
	      const from = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs]);
	      const to = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs]);
	      const time = main_core.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_TIME_RANGE', {
	        '#FROM#': from,
	        '#TO#': to
	      });
	      return main_core.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_DATE_TIME', {
	        '#DATE#': date,
	        '#TIME#': time
	      });
	    }
	    const dateFromFormat = startsInCurrentYear ? 'DAY_MONTH_FORMAT' : 'LONG_DATE_FORMAT';
	    const dateToFormat = endsInCurrentYear ? 'DAY_MONTH_FORMAT' : 'LONG_DATE_FORMAT';
	    const dateFrom = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat(dateFromFormat), babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs]);
	    const dateTo = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat(dateToFormat), babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs]);
	    if (this.isFullDay) {
	      return main_core.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_TIME_RANGE', {
	        '#FROM#': dateFrom,
	        '#TO#': dateTo
	      });
	    }
	    const timeFrom = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs]);
	    const timeTo = main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs]);
	    return main_core.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_DATE_TIME_RANGE', {
	      '#FROM_DATE#': dateFrom,
	      '#FROM_TIME#': timeFrom,
	      '#TO_DATE#': dateTo,
	      '#TO_TIME#': timeTo
	    });
	  }
	  get color() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _color)[_color];
	  }
	  get isFullDay() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isFullDay)[_isFullDay];
	  }
	  get threadId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _threadId)[_threadId];
	  }
	  get categoryChannelId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _categoryChannelId)[_categoryChannelId];
	  }
	  get isNew() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isNew)[_isNew];
	  }
	  set isNew(isNew) {
	    babelHelpers.classPrivateFieldLooseBase(this, _isNew)[_isNew] = isNew;
	    this.updateFields({
	      isNew
	    });
	  }
	  get exdate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _exdate)[_exdate];
	  }
	  get rrule() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rrule)[_rrule];
	  }
	  get rruleDescription() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rruleDescription)[_rruleDescription];
	  }
	}
	function _initFields2(fields) {
	  var _fields$eventOptions;
	  babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1] = parseInt(fields.id, 10);
	  babelHelpers.classPrivateFieldLooseBase(this, _name$1)[_name$1] = fields.name;
	  babelHelpers.classPrivateFieldLooseBase(this, _isFullDay)[_isFullDay] = fields.isFullDay;
	  const fullDayOffset = this.isFullDay ? new Date().getTimezoneOffset() * 60 : 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _dateFromTs)[_dateFromTs] = fields.dateFromTs + fullDayOffset;
	  babelHelpers.classPrivateFieldLooseBase(this, _dateToTs)[_dateToTs] = fields.dateToTs + fullDayOffset;
	  babelHelpers.classPrivateFieldLooseBase(this, _commentsCount)[_commentsCount] = fields.commentsCount;
	  babelHelpers.classPrivateFieldLooseBase(this, _isAttendee)[_isAttendee] = fields.isAttendee;
	  babelHelpers.classPrivateFieldLooseBase(this, _attendeesCount)[_attendeesCount] = fields.attendeesCount;
	  babelHelpers.classPrivateFieldLooseBase(this, _creatorId)[_creatorId] = parseInt(fields.creatorId, 10);
	  babelHelpers.classPrivateFieldLooseBase(this, _eventOptions)[_eventOptions] = {
	    maxAttendees: ((_fields$eventOptions = fields.eventOptions) == null ? void 0 : _fields$eventOptions.maxAttendees) || 0
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _categoryId)[_categoryId] = parseInt(fields.categoryId, 10);
	  babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName] = fields.categoryName;
	  babelHelpers.classPrivateFieldLooseBase(this, _color)[_color] = fields.color;
	  babelHelpers.classPrivateFieldLooseBase(this, _categoryChannelId)[_categoryChannelId] = fields.categoryChannelId;
	  babelHelpers.classPrivateFieldLooseBase(this, _threadId)[_threadId] = fields.threadId;
	  babelHelpers.classPrivateFieldLooseBase(this, _isNew)[_isNew] = fields.isNew;
	  babelHelpers.classPrivateFieldLooseBase(this, _rrule)[_rrule] = RecursionParser.parseRrule(fields.rrule);
	  babelHelpers.classPrivateFieldLooseBase(this, _rruleDescription)[_rruleDescription] = fields.rruleDescription;
	  if (main_core.Type.isNumber(fields.recursionAmount)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _rrule)[_rrule].amount = fields.recursionAmount;
	  }
	  if (main_core.Type.isNumber(fields.recursionNum)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _rrule)[_rrule].num = fields.recursionNum;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _exdate)[_exdate] = fields.exdate;
	  this.fields = fields;
	}
	function _getDateCode2(timestamp) {
	  return main_date.DateTimeFormat.format('d.m.Y', timestamp);
	}

	const END_OF_TIME = 2038;
	class RecursionParser {
	  static parseRecursion(event, {
	    fromLimit,
	    toLimit
	  }) {
	    if (event.rrule === null) {
	      return new EventModel(event.fields);
	    }
	    const {
	      timestamps
	    } = this.parseTimestamps(event, {
	      fromLimit,
	      toLimit
	    });
	    const recursionAmount = this.getAmount(event);
	    return timestamps.map(({
	      fromTs,
	      num
	    }) => new EventModel({
	      ...event.fields,
	      dateFromTs: fromTs / 1000,
	      dateToTs: fromTs / 1000 + event.duration / 1000,
	      recursionAmount,
	      recursionNum: num + 1
	    }));
	  }
	  static getAmount(event) {
	    const rruleCount = parseInt(event.rrule.COUNT, 10) || 0;
	    if (rruleCount > 0) {
	      return rruleCount;
	    }
	    const toLimit = main_date.DateTimeFormat.parse(event.rrule.UNTIL);
	    if (toLimit.getFullYear() === END_OF_TIME) {
	      return Infinity;
	    }
	    const {
	      count
	    } = this.parseTimestamps(event, {
	      fromLimit: null,
	      toLimit
	    });
	    return count;
	  }
	  static parseTimestamps(event, {
	    fromLimit,
	    toLimit
	  }) {
	    const timestamps = [];
	    const rrule = event.rrule;
	    const exDate = event.exdate.split(';');
	    const fullDayOffset = event.isFullDay ? new Date().getTimezoneOffset() * 60000 : 0;
	    let from = new Date(event.dateFrom.getTime() - fullDayOffset);
	    const to = new Date(Math.min(toLimit, main_date.DateTimeFormat.parse(rrule.UNTIL)));
	    to.setHours(from.getHours(), from.getMinutes());
	    const fromYear = from.getFullYear();
	    const fromMonth = from.getMonth();
	    const fromDate = from.getDate();
	    const fromHour = from.getHours();
	    const fromMinute = from.getMinutes();
	    let count = 0;
	    const FORMAT_DATE = main_date.DateTimeFormat.getFormat('FORMAT_DATE');
	    while (from <= to) {
	      if (rrule.COUNT > 0 && count >= rrule.COUNT) {
	        break;
	      }
	      const exclude = exDate.includes(main_date.DateTimeFormat.format(FORMAT_DATE, from.getTime() / 1000));
	      const include = !exclude && (!fromLimit || from.getTime() >= fromLimit.getTime()) && (!toLimit || from.getTime() + event.duration <= toLimit.getTime());
	      if (rrule.FREQ === 'WEEKLY') {
	        const weekDay = this.getWeekDayByInd(main_date.DateTimeFormat.format('w', from.getTime() / 1000));
	        if (main_core.Type.isStringFilled(rrule.BYDAY[weekDay])) {
	          if (include) {
	            timestamps.push({
	              fromTs: from.getTime(),
	              num: count
	            });
	          }
	          count++;
	        }
	        const skipWeek = (rrule.INTERVAL - 1) * 7 + 1;
	        const delta = weekDay === 'SU' ? skipWeek : 1;
	        from = new Date(from.getFullYear(), from.getMonth(), from.getDate() + delta, fromHour, fromMinute);
	      }
	      if (['DAILY', 'MONTHLY', 'YEARLY'].includes(rrule.FREQ)) {
	        if (include) {
	          timestamps.push({
	            fromTs: from.getTime(),
	            num: count
	          });
	        }
	        count++;
	        switch (rrule.FREQ) {
	          case 'DAILY':
	            from = new Date(fromYear, fromMonth, fromDate + count * rrule.INTERVAL, fromHour, fromMinute, 0, 0);
	            break;
	          case 'MONTHLY':
	            from = new Date(fromYear, fromMonth + count * rrule.INTERVAL, fromDate, fromHour, fromMinute, 0, 0);
	            break;
	          case 'YEARLY':
	            from = new Date(fromYear + count * rrule.INTERVAL, fromMonth, fromDate, fromHour, fromMinute, 0, 0);
	            break;
	        }
	      }
	    }
	    return {
	      timestamps,
	      count
	    };
	  }
	  static getWeekDayByInd(index) {
	    return ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][index];
	  }
	  static parseRrule(rule) {
	    if (!main_core.Type.isStringFilled(rule)) {
	      return null;
	    }
	    const res = {};
	    const pairs = rule.split(';').map(it => it.split('=')).filter(([field]) => main_core.Type.isStringFilled(field));
	    for (const [field, value] of pairs) {
	      if (field === 'FREQ' && ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'].includes(value)) {
	        res.FREQ = value;
	      }
	      if (['COUNT', 'INTERVAL'].includes(field)) {
	        var _parseInt;
	        res[field] = Math.max(1, (_parseInt = parseInt(value, 10)) != null ? _parseInt : 0);
	      }
	      if (field === 'UNTIL') {
	        res.UNTIL = value;
	      }
	      if (field === 'BYDAY') {
	        var _res$BYDAY2;
	        const regex = /(([-+])?\d+)?(MO|TU|WE|TH|FR|SA|SU)/;
	        for (const day of value.split(',').filter(d => regex.test(d))) {
	          var _res$BYDAY, _matches$;
	          const matches = [...day.match(regex)];
	          (_res$BYDAY = res.BYDAY) != null ? _res$BYDAY : res.BYDAY = {};
	          res.BYDAY[matches[3]] = (_matches$ = matches[1]) != null ? _matches$ : matches[3];
	        }
	        (_res$BYDAY2 = res.BYDAY) != null ? _res$BYDAY2 : res.BYDAY = {
	          MO: 'MO'
	        };
	      }
	    }
	    return res;
	  }
	}

	class EventApi {
	  static async list(params) {
	    const {
	      categoryId,
	      fromMonth,
	      fromYear,
	      toMonth,
	      toYear
	    } = params;
	    const response = await BX.ajax.runAction('calendar.open-events.Event.list', {
	      data: {
	        categoryId,
	        fromMonth,
	        fromYear,
	        toMonth,
	        toYear
	      }
	    });
	    return response.data;
	  }
	  static async getTsRange(categoryId) {
	    const response = await BX.ajax.runAction('calendar.open-events.Event.getTsRange', {
	      data: {
	        categoryId
	      }
	    });
	    return {
	      from: new Date(parseInt(response.data.from, 10) * 1000),
	      to: new Date(parseInt(response.data.to, 10) * 1000)
	    };
	  }
	  static async setAttendeeStatus(eventId, attendeeStatus) {
	    const response = await BX.ajax.runAction('calendar.open-events.Event.setAttendeeStatus', {
	      data: {
	        eventId,
	        attendeeStatus
	      }
	    });
	    return response.data;
	  }
	  static async setWatched(eventIds) {
	    const response = await BX.ajax.runAction('calendar.open-events.Event.setWatched', {
	      data: {
	        eventIds
	      }
	    });
	    return response.data;
	  }
	}

	class FilterApi {
	  static async query(params) {
	    const {
	      filterId,
	      fromDate,
	      fromMonth,
	      fromYear,
	      toDate,
	      toMonth,
	      toYear
	    } = params;
	    const response = await BX.ajax.runAction('calendar.open-events.Filter.query', {
	      data: {
	        filterId,
	        fromDate,
	        fromMonth,
	        fromYear,
	        toDate,
	        toMonth,
	        toYear
	      }
	    });
	    return response.data;
	  }
	  static async getTsRange(filterId) {
	    const response = await BX.ajax.runAction('calendar.open-events.Filter.getTsRange', {
	      data: {
	        filterId
	      }
	    });
	    return {
	      from: new Date(parseInt(response.data.from, 10) * 1000),
	      to: new Date(parseInt(response.data.to, 10) * 1000)
	    };
	  }
	}

	const FILTER_CATEGORY_ID = -1;
	var _filter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _events = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("events");
	var _eventIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventIds");
	var _shownRanges = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shownRanges");
	var _loadedRanges = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadedRanges");
	var _tsRanges = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tsRanges");
	var _eventPromises = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventPromises");
	var _tsRangePromises = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tsRangePromises");
	var _subscribeToPull$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPull");
	var _createEventPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createEventPull");
	var _updateEventPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateEventPull");
	var _deletePullEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deletePullEvent");
	var _updateEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateEvent");
	var _deleteEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteEvent");
	var _getEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEvent");
	var _getFirstDayOfPreviousMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFirstDayOfPreviousMonth");
	var _getLastDayOfNextMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLastDayOfNextMonth");
	var _prepareEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareEvents");
	var _loadEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadEvents");
	var _getDateKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDateKey");
	var _getDateCode$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDateCode");
	var _requestEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestEvents");
	var _loadTsRange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadTsRange");
	var _requestTsRange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestTsRange");
	class Manager$1 extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _requestTsRange, {
	      value: _requestTsRange2
	    });
	    Object.defineProperty(this, _loadTsRange, {
	      value: _loadTsRange2
	    });
	    Object.defineProperty(this, _requestEvents, {
	      value: _requestEvents2
	    });
	    Object.defineProperty(this, _getDateCode$1, {
	      value: _getDateCode2$1
	    });
	    Object.defineProperty(this, _getDateKey, {
	      value: _getDateKey2
	    });
	    Object.defineProperty(this, _loadEvents, {
	      value: _loadEvents2
	    });
	    Object.defineProperty(this, _prepareEvents, {
	      value: _prepareEvents2
	    });
	    Object.defineProperty(this, _getLastDayOfNextMonth, {
	      value: _getLastDayOfNextMonth2
	    });
	    Object.defineProperty(this, _getFirstDayOfPreviousMonth, {
	      value: _getFirstDayOfPreviousMonth2
	    });
	    Object.defineProperty(this, _getEvent, {
	      value: _getEvent2
	    });
	    Object.defineProperty(this, _deleteEvent, {
	      value: _deleteEvent2
	    });
	    Object.defineProperty(this, _updateEvent, {
	      value: _updateEvent2
	    });
	    Object.defineProperty(this, _deletePullEvent, {
	      value: _deletePullEvent2
	    });
	    Object.defineProperty(this, _updateEventPull, {
	      value: _updateEventPull2
	    });
	    Object.defineProperty(this, _createEventPull, {
	      value: _createEventPull2
	    });
	    Object.defineProperty(this, _subscribeToPull$1, {
	      value: _subscribeToPull2$1
	    });
	    Object.defineProperty(this, _filter, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _events, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _eventIds, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _shownRanges, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _loadedRanges, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _tsRanges, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _eventPromises, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _tsRangePromises, {
	      writable: true,
	      value: {}
	    });
	    this.setEventNamespace('Calendar.OpenEvents.List.EventManager');
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPull$1)[_subscribeToPull$1]();
	  }
	  setFilter(filter) {
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter] = filter;
	  }
	  async setEventAttendee(eventId, isAttendee) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateEvent)[_updateEvent](eventId, {
	      isAttendee
	    });
	    try {
	      await EventApi.setAttendeeStatus(eventId, isAttendee);
	    } catch (e) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateEvent)[_updateEvent](eventId, {
	        isAttendee: !isAttendee
	      });
	    }
	  }
	  async setEventWatched(eventId) {
	    const event = babelHelpers.classPrivateFieldLooseBase(this, _getEvent)[_getEvent](eventId);
	    if (!event.isNew) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _updateEvent)[_updateEvent](eventId, {
	      isNew: false
	    });
	    try {
	      await EventApi.setWatched([eventId]);
	      CategoryManager.decrementNewCounter(event.categoryId);
	    } catch {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateEvent)[_updateEvent](eventId, {
	        isNew: true
	      });
	    }
	  }
	  async filterEvents() {
	    const filterKey = babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFilterFieldsKey();
	    if (filterKey !== this.filterEvents.previousFilterKey) {
	      delete babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][FILTER_CATEGORY_ID];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][FILTER_CATEGORY_ID];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds][FILTER_CATEGORY_ID];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _eventPromises)[_eventPromises][FILTER_CATEGORY_ID];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][FILTER_CATEGORY_ID];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _tsRangePromises)[_tsRangePromises][FILTER_CATEGORY_ID];
	    }
	    this.filterEvents.previousFilterKey = filterKey;
	    return this.getEvents(FILTER_CATEGORY_ID);
	  }
	  filterNext() {
	    return this.getNext(FILTER_CATEGORY_ID);
	  }
	  filterPrevious() {
	    return this.getPrevious(FILTER_CATEGORY_ID);
	  }
	  async getNext(categoryId = 0) {
	    const everythingIsLoaded = babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].to >= babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].to;
	    const everythingIsShown = babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to >= babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].to;
	    const events = babelHelpers.classPrivateFieldLooseBase(this, _prepareEvents)[_prepareEvents](categoryId);
	    if (everythingIsShown) {
	      return events;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to = babelHelpers.classPrivateFieldLooseBase(this, _getLastDayOfNextMonth)[_getLastDayOfNextMonth](babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to);
	    const eventsBeforeLoad = babelHelpers.classPrivateFieldLooseBase(this, _prepareEvents)[_prepareEvents](categoryId);
	    if (everythingIsLoaded) {
	      if (eventsBeforeLoad.length === events.length) {
	        return this.getNext(categoryId);
	      }
	      return eventsBeforeLoad;
	    }
	    const loadedEvents = await this.getEvents(categoryId, {
	      from: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to,
	      to: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to
	    });
	    if (loadedEvents.length === eventsBeforeLoad.length) {
	      await this.getEvents(categoryId, {
	        from: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to,
	        to: babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].to
	      });
	      return this.getNext(categoryId);
	    }
	    return loadedEvents;
	  }
	  async getPrevious(categoryId = 0) {
	    const everythingIsLoaded = babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].from <= babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].from;
	    const everythingIsShown = babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from <= babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].from;
	    const events = babelHelpers.classPrivateFieldLooseBase(this, _prepareEvents)[_prepareEvents](categoryId);
	    if (everythingIsShown) {
	      return events;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from = babelHelpers.classPrivateFieldLooseBase(this, _getFirstDayOfPreviousMonth)[_getFirstDayOfPreviousMonth](babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from);
	    const eventsBeforeLoad = babelHelpers.classPrivateFieldLooseBase(this, _prepareEvents)[_prepareEvents](categoryId);
	    if (everythingIsLoaded) {
	      if (eventsBeforeLoad.length === events.length) {
	        return this.getPrevious(categoryId);
	      }
	      return eventsBeforeLoad;
	    }
	    const loadedEvents = await this.getEvents(categoryId, {
	      from: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from,
	      to: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from
	    });
	    if (loadedEvents.length === eventsBeforeLoad.length) {
	      await this.getEvents(categoryId, {
	        from: babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].from,
	        to: babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from
	      });
	      return this.getPrevious(categoryId);
	    }
	    return loadedEvents;
	  }
	  async getEvents(categoryId = 0, dateRange = {}) {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _dateRange$from, _dateRange$to, _babelHelpers$classPr3, _babelHelpers$classPr4, _babelHelpers$classPr5, _babelHelpers$classPr6, _babelHelpers$classPr7, _babelHelpers$classPr8, _babelHelpers$classPr9;
	    (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges])[categoryId]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[categoryId] = await babelHelpers.classPrivateFieldLooseBase(this, _loadTsRange)[_loadTsRange](categoryId);
	    const today = new Date();
	    let from = (_dateRange$from = dateRange.from) != null ? _dateRange$from : babelHelpers.classPrivateFieldLooseBase(this, _getFirstDayOfPreviousMonth)[_getFirstDayOfPreviousMonth](today);
	    let to = (_dateRange$to = dateRange.to) != null ? _dateRange$to : babelHelpers.classPrivateFieldLooseBase(this, _getLastDayOfNextMonth)[_getLastDayOfNextMonth](today);
	    if (categoryId === FILTER_CATEGORY_ID && babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].isDateFieldApplied()) {
	      from = babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].from;
	      to = babelHelpers.classPrivateFieldLooseBase(this, _tsRanges)[_tsRanges][categoryId].to;
	    }
	    (_babelHelpers$classPr4 = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges])[categoryId]) != null ? _babelHelpers$classPr4 : _babelHelpers$classPr3[categoryId] = {
	      from,
	      to
	    };
	    (_babelHelpers$classPr6 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges])[categoryId]) != null ? _babelHelpers$classPr6 : _babelHelpers$classPr5[categoryId] = {
	      from,
	      to
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].from = new Date(Math.min(from, babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].from));
	    babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].to = new Date(Math.max(to, babelHelpers.classPrivateFieldLooseBase(this, _loadedRanges)[_loadedRanges][categoryId].to));
	    const events = await babelHelpers.classPrivateFieldLooseBase(this, _loadEvents)[_loadEvents](categoryId, {
	      from,
	      to
	    });
	    const alreadyLoadedIds = Object.values(babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds]).flat();
	    const newEvents = events.filter(it => !alreadyLoadedIds.includes(it.id));
	    babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].push(...newEvents);
	    const alreadyLoadedCategoryIds = (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds][categoryId]) != null ? _babelHelpers$classPr7 : [];
	    const newCategoryEvents = events.filter(it => !alreadyLoadedCategoryIds.includes(it.id));
	    (_babelHelpers$classPr9 = (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds])[categoryId]) != null ? _babelHelpers$classPr9 : _babelHelpers$classPr8[categoryId] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds][categoryId].push(...newCategoryEvents.map(it => it.id));
	    return babelHelpers.classPrivateFieldLooseBase(this, _prepareEvents)[_prepareEvents](categoryId);
	  }
	}
	function _subscribeToPull2$1() {
	  if (!BX.PULL) {
	    console.info('BX.PULL not initialized');
	    return;
	  }
	  const pullRequests = new PullRequests$1();
	  pullRequests.subscribe('create', babelHelpers.classPrivateFieldLooseBase(this, _createEventPull)[_createEventPull].bind(this));
	  pullRequests.subscribe('update', babelHelpers.classPrivateFieldLooseBase(this, _updateEventPull)[_updateEventPull].bind(this));
	  pullRequests.subscribe('delete', babelHelpers.classPrivateFieldLooseBase(this, _deletePullEvent)[_deletePullEvent].bind(this));
	  BX.PULL.subscribe(pullRequests);
	}
	function _createEventPull2(event) {
	  const {
	    fields: eventDto
	  } = event.getData();
	  const newEvent = new EventModel(eventDto);
	  babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].push(newEvent);
	  [0, newEvent.categoryId].forEach(categoryId => {
	    var _babelHelpers$classPr10;
	    return (_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds][categoryId]) == null ? void 0 : _babelHelpers$classPr10.push(newEvent.id);
	  });
	  if (newEvent.creatorId !== AppSettings.currentUserId) {
	    newEvent.isNew = true;
	    [0, newEvent.categoryId].forEach(categoryId => CategoryManager.incrementNewCounter(categoryId));
	  }
	  CategoryManager.bubbleUp(newEvent.categoryId);
	  this.emit('update', {
	    eventId: newEvent.id
	  });
	}
	function _updateEventPull2(event) {
	  const {
	    fields: eventDto,
	    [AppSettings.pullEventUserFieldsKey]: userFields
	  } = event.getData();
	  Object.assign(eventDto, userFields || {});
	  babelHelpers.classPrivateFieldLooseBase(this, _updateEvent)[_updateEvent](eventDto.id, eventDto);
	}
	function _deletePullEvent2(event) {
	  const {
	    fields: {
	      eventId
	    }
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _deleteEvent)[_deleteEvent](eventId);
	}
	function _updateEvent2(eventId, fields) {
	  const event = babelHelpers.classPrivateFieldLooseBase(this, _getEvent)[_getEvent](eventId);
	  if (!event) {
	    return;
	  }
	  event.updateFields(fields);
	  this.emit('update', {
	    eventId
	  });
	}
	function _deleteEvent2(eventId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _events)[_events] = babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].filter(it => it.id !== eventId);
	  this.emit('delete', {
	    eventId
	  });
	}
	function _getEvent2(eventId) {
	  var _babelHelpers$classPr11;
	  return (_babelHelpers$classPr11 = babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].find(it => it.id === eventId)) != null ? _babelHelpers$classPr11 : null;
	}
	function _getFirstDayOfPreviousMonth2(date) {
	  return new Date(date.getFullYear(), date.getMonth() - 1, 1);
	}
	function _getLastDayOfNextMonth2(date) {
	  return new Date(date.getFullYear(), date.getMonth() + 2, 0, 23, 59, 59);
	}
	function _prepareEvents2(categoryId) {
	  const fromLimit = babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].from;
	  const toLimit = babelHelpers.classPrivateFieldLooseBase(this, _shownRanges)[_shownRanges][categoryId].to;
	  return babelHelpers.classPrivateFieldLooseBase(this, _events)[_events].filter(it => babelHelpers.classPrivateFieldLooseBase(this, _eventIds)[_eventIds][categoryId].includes(it.id)).flatMap(it => RecursionParser.parseRecursion(it, {
	    fromLimit,
	    toLimit
	  })).filter(it => it.dateFrom >= fromLimit && it.dateTo <= toLimit);
	}
	async function _loadEvents2(categoryId, dateRange) {
	  var _babelHelpers$classPr12, _babelHelpers$classPr13, _babelHelpers$classPr14, _babelHelpers$classPr15;
	  const datesKey = babelHelpers.classPrivateFieldLooseBase(this, _getDateKey)[_getDateKey](dateRange);
	  (_babelHelpers$classPr13 = (_babelHelpers$classPr12 = babelHelpers.classPrivateFieldLooseBase(this, _eventPromises)[_eventPromises])[categoryId]) != null ? _babelHelpers$classPr13 : _babelHelpers$classPr12[categoryId] = {};
	  (_babelHelpers$classPr15 = (_babelHelpers$classPr14 = babelHelpers.classPrivateFieldLooseBase(this, _eventPromises)[_eventPromises][categoryId])[datesKey]) != null ? _babelHelpers$classPr15 : _babelHelpers$classPr14[datesKey] = babelHelpers.classPrivateFieldLooseBase(this, _requestEvents)[_requestEvents](categoryId, dateRange);
	  const response = await babelHelpers.classPrivateFieldLooseBase(this, _eventPromises)[_eventPromises][categoryId][datesKey];
	  return response.map(eventDto => new EventModel(eventDto));
	}
	function _getDateKey2(dateRange) {
	  return `${babelHelpers.classPrivateFieldLooseBase(this, _getDateCode$1)[_getDateCode$1](dateRange.from)}-${babelHelpers.classPrivateFieldLooseBase(this, _getDateCode$1)[_getDateCode$1](dateRange.to)}`;
	}
	function _getDateCode2$1(date) {
	  return main_date.DateTimeFormat.format('d.m.Y', date);
	}
	function _requestEvents2(categoryId, dateRange) {
	  if (categoryId === FILTER_CATEGORY_ID) {
	    return FilterApi.query({
	      filterId: babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].id,
	      fromDate: dateRange.from.getDate(),
	      fromMonth: dateRange.from.getMonth() + 1,
	      fromYear: dateRange.from.getFullYear(),
	      toDate: dateRange.to.getDate(),
	      toMonth: dateRange.to.getMonth() + 1,
	      toYear: dateRange.to.getFullYear()
	    });
	  }
	  return EventApi.list({
	    categoryId,
	    fromMonth: dateRange.from.getMonth() + 1,
	    fromYear: dateRange.from.getFullYear(),
	    toMonth: dateRange.to.getMonth() + 1,
	    toYear: dateRange.to.getFullYear()
	  });
	}
	async function _loadTsRange2(categoryId) {
	  var _babelHelpers$classPr16, _babelHelpers$classPr17;
	  (_babelHelpers$classPr17 = (_babelHelpers$classPr16 = babelHelpers.classPrivateFieldLooseBase(this, _tsRangePromises)[_tsRangePromises])[categoryId]) != null ? _babelHelpers$classPr17 : _babelHelpers$classPr16[categoryId] = babelHelpers.classPrivateFieldLooseBase(this, _requestTsRange)[_requestTsRange](categoryId);
	  return babelHelpers.classPrivateFieldLooseBase(this, _tsRangePromises)[_tsRangePromises][categoryId];
	}
	function _requestTsRange2(categoryId) {
	  if (categoryId === FILTER_CATEGORY_ID) {
	    return FilterApi.getTsRange(babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].id);
	  }
	  return EventApi.getTsRange(categoryId);
	}
	const EventManager = new Manager$1();

	const Category = {
	  props: {
	    category: CategoryModel
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      selectedCategoryId: 'selectedCategoryId'
	    })
	  },
	  methods: {
	    async onClick() {
	      await this.$store.dispatch('selectCategory', this.category.id);
	      await this.$store.dispatch('setEventsLoading', true);
	      const events = await EventManager.getEvents(this.category.id);
	      if (this.selectedCategoryId !== this.category.id) {
	        return;
	      }
	      await this.$store.dispatch('setEvents', events);
	      await this.$store.dispatch('setEventsLoading', false);
	    },
	    getEventCountPhrase(eventsCount) {
	      return main_core.Loc.getMessagePlural('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_EVENTS_COUNT', eventsCount, {
	        '#COUNT#': eventsCount
	      });
	    },
	    renderCounter() {
	      this.$refs.counter.innerHTML = '';
	      if (this.category.newCount > 0) {
	        new ui_cnt.Counter({
	          value: this.category.newCount,
	          color: this.category.isMuted ? ui_cnt.Counter.Color.GRAY : ui_cnt.Counter.Color.DANGER
	        }).renderTo(this.$refs.counter);
	      }
	    }
	  },
	  mounted() {
	    this.renderCounter();
	  },
	  watch: {
	    category() {
	      this.renderCounter();
	    }
	  },
	  template: `
		<div
			class="calendar-open-events-list-category"
			:class="{
				'--banned': category.isBanned,
				'--selected': category.isSelected,
				'--all-category': category.id === 0,
			}"
			:data-category-id="category.id"
		>
			<div class="calendar-open-events-list-category-inner" @click="onClick">
				<div class="calendar-open-events-list-category-title">
					<div class="ui-icon-set --calendar-2" v-if="category.id === 0"></div>
					<div
						class="calendar-open-events-list-category-title-name"
						:title="category.name"
					>
						<span>{{ category.name }}</span>
						<div class="ui-icon-set --sound-off" v-if="category.isMuted && !category.isBanned"></div>
						<div class="ui-icon-set --lock" v-if="category.closed"></div>
					</div>
					<div ref="counter"></div>
				</div>
				<div
					class="calendar-open-events-list-category-info"
					v-html="getEventCountPhrase(category.eventsCount)"
					v-if="category.id !== 0"
				>
				</div>
			</div>
		</div>
	`
	};

	const CategoryList = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      categories: 'categories',
	      isSearchMode: 'isSearchMode',
	      categoriesQuery: 'categoriesQuery'
	    }),
	    allCategory() {
	      return this.categories.find(category => category.id === 0);
	    },
	    sortedCategories() {
	      return [...this.categories].filter(category => category.id > 0).sort((a, b) => {
	        if (a.isBanned !== b.isBanned) {
	          return a.isBanned - b.isBanned;
	        }
	        return b.updatedAt - a.updatedAt;
	      });
	    }
	  },
	  mounted() {
	    void this.loadOnScroll();
	    this.$refs.categoryList.addEventListener('scroll', this.loadOnScroll);
	    CategoryManager.subscribe('update', this.onCategoriesUpdatedHandler);
	  },
	  beforeUnmount() {
	    this.$refs.categoryList.removeEventListener('scroll', this.loadOnScroll);
	    CategoryManager.unsubscribe('update', this.onCategoriesUpdatedHandler);
	  },
	  watch: {
	    categories() {
	      void this.$nextTick(() => this.loadOnScroll());
	    }
	  },
	  methods: {
	    async onCategoriesUpdatedHandler() {
	      const categories = await this.getCategories();
	      this.$store.dispatch('setCategories', categories);
	    },
	    async loadOnScroll() {
	      const scrollTop = this.$refs.categoryList.scrollTop;
	      const scrollHeight = this.$refs.categoryList.scrollHeight;
	      const offsetHeight = this.$refs.categoryList.offsetHeight;
	      if (scrollTop + 1 >= scrollHeight - offsetHeight) {
	        const categories = await this.loadMore();
	        if (categories.length > 0) {
	          this.$store.dispatch('setCategories', categories);
	        }
	      }
	    },
	    getCategories() {
	      if (this.isSearchMode) {
	        return CategoryManager.searchCategories(this.categoriesQuery);
	      }
	      return CategoryManager.getCategories();
	    },
	    loadMore() {
	      if (this.isSearchMode) {
	        return CategoryManager.searchMore();
	      }
	      return CategoryManager.loadMore();
	    }
	  },
	  components: {
	    Category
	  },
	  template: `
		<div class="calendar-open-events-list-category-list --calendar-scroll-bar" ref="categoryList">
			<Category :category="allCategory" v-show="!isSearchMode"/>
			<Category v-for="category of sortedCategories" :category="category"/>
		</div>
	`
	};

	const Categories = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      isFilterMode: 'isFilterMode'
	    })
	  },
	  components: {
	    CategoriesHeader,
	    CategoryList
	  },
	  template: `
		<div class="calendar-open-events-list-categories" :class="{ '--filter': isFilterMode }" >
			<CategoriesHeader/>
			<CategoryList/>
		</div>
	`
	};

	const TitleMenu = {
	  props: {
	    category: CategoryModel
	  },
	  data() {
	    return {
	      menu: main_popup.Menu
	    };
	  },
	  methods: {
	    openMenu() {
	      this.menu = new main_popup.Menu({
	        bindElement: this.$refs.menuIcon,
	        closeByEsc: true,
	        items: this.getMenuItems()
	      });
	      this.menu.show();
	    },
	    redrawMenu() {
	      const itemIds = this.menu.getMenuItems().map(item => item.getId());
	      itemIds.forEach(id => this.menu.removeMenuItem(id, {
	        destroyEmptyPopup: false
	      }));
	      this.getMenuItems().forEach(item => this.menu.addMenuItem(item));
	    },
	    getMenuItems() {
	      const items = [this.getInfoItem(), this.getOpenChatItem()];
	      if (!this.category.isBanned) {
	        items.push(this.getMuteItem());
	      }
	      items.push(this.getBanItem());
	      if (this.category.permissions.edit === true) {
	        items.push(this.getEditItem());
	      }
	      if (this.category.permissions.delete === true) {
	        items.push(this.getDeleteItem());
	      }
	      return items;
	    },
	    getInfoItem() {
	      return {
	        html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --info-circle"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_ABOUT_CATEGORY')}</span>
					</div>
				`,
	        onclick: () => {
	          this.menu.close();
	          alert('info');
	        }
	      };
	    },
	    getOpenChatItem() {
	      return {
	        html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --chats-2"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_OPEN_CHANNEL')}</span>
					</div>
				`,
	        onclick: () => {
	          this.menu.close();
	          im_public_iframe.Messenger.openChat(`chat${this.category.channelId}`);
	        }
	      };
	    },
	    getMuteItem() {
	      return {
	        html: this.renderMuteItem(),
	        onclick: () => {
	          this.category.isMuted = !this.category.isMuted;
	          this.muteCategory(this.category.isMuted);
	          this.redrawMenu();
	        }
	      };
	    },
	    renderMuteItem() {
	      const icon = this.category.isMuted ? '--notifications-off' : '--bell-1';
	      const text = this.category.isMuted ? this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_ENABLE_NOTIFY') : this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_DISABLE_NOTIFY');
	      return `
				<div class="calendar-open-events-list-menu-item">
					<div class="ui-icon-set ${icon}"></div>
					<span>${text}</span>
				</div>
			`;
	    },
	    getBanItem() {
	      return {
	        html: this.renderBanItem(),
	        onclick: () => {
	          this.category.isBanned = !this.category.isBanned;
	          this.banCategory(this.category.isBanned);
	          this.redrawMenu();
	        }
	      };
	    },
	    renderBanItem() {
	      const icon = this.category.isBanned ? '--bell-1' : '--unavailable';
	      const text = this.category.isBanned ? this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_SUBSCRIBE') : this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_UNSUBSCRIBE');
	      return `
				<div class="calendar-open-events-list-menu-item">
					<div class="ui-icon-set ${icon}"></div>
					<span>${text}</span>
				</div>
			`;
	    },
	    getEditItem() {
	      return {
	        html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --pencil-40"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_EDIT')}</span>
					</div>
				`,
	        onclick: () => {
	          this.menu.close();
	          this.openEditCategoryForm();
	        }
	      };
	    },
	    getDeleteItem() {
	      return {
	        html: `
					<div class="calendar-open-events-list-menu-item">
						<div class="ui-icon-set --cross-40"></div>
						<span>${this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_MENU_DELETE')}</span>
					</div>
				`,
	        onclick: () => {
	          this.menu.close();
	          this.deleteCategory();
	        }
	      };
	    },
	    muteCategory(isMuted) {
	      void CategoryManager.setMute(this.category.id, isMuted);
	    },
	    banCategory(isBanned) {
	      void CategoryManager.setBan(this.category.id, isBanned);
	    },
	    openEditCategoryForm() {
	      this.$refs.editForm.show({
	        category: this.category
	      });
	    },
	    deleteCategory() {
	      alert('delete category ' + this.category.id);
	    }
	  },
	  components: {
	    CategoryEditForm
	  },
	  template: `
		<div
			class="calendar-open-events-list-item__list-header__menu ui-icon-set --more-information"
			@click="openMenu"
			ref="menuIcon"
		></div>
		<CategoryEditForm ref="editForm"/>
	`
	};

	const EventListTitle = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      isFilterMode: 'isFilterMode',
	      category: 'selectedCategory'
	    }),
	    title() {
	      var _this$category;
	      if (this.isFilterMode) {
	        return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_SEARCH_RESULT');
	      }
	      return (_this$category = this.category) == null ? void 0 : _this$category.name;
	    }
	  },
	  components: {
	    TitleMenu
	  },
	  template: `
		<div class="calendar__open-event__list-header">
			<div class="calendar__open-event__list-header__title" :title="title">
				{{ title }}
			</div>
			<div class="calendar__open-event__list-header__icon ui-icon-set --lock" v-if="category.closed"></div>
			<TitleMenu v-if="!isFilterMode && category.id" :category="category"/>
		</div>
	`
	};

	const CalendarSheet = {
	  props: {
	    event: EventModel
	  },
	  computed: {
	    calendarDate() {
	      return this.event.dateFrom.getDate();
	    },
	    calendarMonth() {
	      return main_date.DateTimeFormat.format('f', this.event.dateFrom);
	    },
	    calendarTime() {
	      if (this.event.isFullDay) {
	        return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_ALL_DAY');
	      }
	      const timeFormat = main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
	      const time = main_date.DateTimeFormat.format(timeFormat, this.event.dateFrom);
	      const dayOfWeek = main_date.DateTimeFormat.format('D', this.event.dateFrom);
	      return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_WEEKDAY_TIME', {
	        '#WEEKDAY#': dayOfWeek,
	        '#TIME#': time
	      });
	    },
	    isCreator() {
	      return this.event.creatorId === AppSettings.currentUserId;
	    }
	  },
	  template: `
		<div class="calendar-open-events-list-calendar-sheet" :style="{ borderColor: event.color }">
			<div class="calendar-open-events-list-calendar-sheet-header" :style="{ backgroundColor: event.color }">
				<div class="calendar-open-events-list-calendar-sheet-header-hole"></div>
				<div class="calendar-open-events-list-calendar-sheet-header-hole"></div>
			</div>
			<div class="calendar-open-events-list-calendar-sheet-content">
				<div class="calendar-open-events-list-calendar-sheet-date">
					{{ calendarDate }}
				</div>
				<div class="calendar-open-events-list-calendar-sheet-month">
					{{ calendarMonth }}
				</div>
				<div class="calendar-open-events-list-calendar-sheet-time" :style="{ color: event.color }">
					{{ calendarTime }}
				</div>
			</div>
			<div
				class="calendar-open-events-list-calendar-sheet-crown"
				v-if="isCreator"
				:title="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_EVENT_YOU_ARE_OWNER')"
			>
				<div class="ui-icon-set --crown-2"></div>
			</div>
		</div>
	`
	};

	const AttendButton = {
	  props: {
	    isAttendee: Boolean
	  },
	  methods: {
	    renderButton() {
	      const button = new ui_buttons.Button({
	        color: this.isAttendee ? ui_buttons.ButtonColor.LIGHT_BORDER : ui_buttons.ButtonColor.SUCCESS,
	        size: ui_buttons.ButtonSize.SMALL,
	        round: true,
	        //TODO: replace with icon property when icons ready
	        // icon: this.isAttendee ? ButtonIcon. : ButtonIcon.,
	        className: this.isAttendee ? 'calendar-open-events-list-item__attend-button --off' : 'calendar-open-events-list-item__attend-button --on'
	      });
	      this.$refs.bindBtn.innerHTML = '';
	      button.renderTo(this.$refs.bindBtn);
	    }
	  },
	  watch: {
	    isAttendee() {
	      this.renderButton();
	    }
	  },
	  mounted() {
	    this.renderButton();
	  },
	  template: `
		<div ref="bindBtn"></div>
	`
	};

	const CommentCounter = {
	  props: {
	    commentsCount: Number
	  },
	  methods: {
	    renderCounter() {
	      const value = this.commentsCount;
	      const color = value ? ui_cnt.Counter.Color.PRIMARY : ui_cnt.Counter.Color.GRAY;
	      this.$refs.counter.innerHTML = '';
	      new ui_cnt.Counter({
	        value,
	        color,
	        size: ui_cnt.Counter.Size.LARGE
	      }).renderTo(this.$refs.counter);
	    }
	  },
	  mounted() {
	    this.renderCounter();
	  },
	  watch: {
	    commentsCount() {
	      this.renderCounter();
	    }
	  },
	  template: `
		<div class="calendar-open-events-list-item-comment-counter">
			<div class="ui-icon-set --chats-1"></div>
			<div ref="counter"></div>
		</div>
	`
	};

	const AttendeeCounter = {
	  props: {
	    attendeesCount: Number,
	    maxAttendees: Number | null
	  },
	  computed: {
	    attendeesValue() {
	      if (this.maxAttendees) {
	        return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_EVENT_ATTENDEE_VALUE', {
	          '#COUNT#': this.attendeesCount,
	          '#COUNT_MAX#': this.maxAttendees
	        });
	      } else {
	        return this.attendeesCount;
	      }
	    }
	  },
	  template: `
		<div class="calendar-open-events-list-item-attendee-counter">
			<div class="ui-icon-set --persons-2"></div>
			<div v-html="attendeesValue"></div>
		</div>
	`
	};

	const NameWithCounter = {
	  emits: ['openEvent'],
	  props: {
	    event: EventModel
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      selectedCategoryId: 'selectedCategoryId'
	    }),
	    formattedRrule() {
	      if (this.event.rrule.amount === 0 || this.event.rrule.amount === Infinity) {
	        return '';
	      }
	      return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_NUM_MEETING_OF_AMOUNT', {
	        '#NUM#': this.event.rrule.num,
	        '#AMOUNT#': this.event.rrule.amount
	      });
	    }
	  },
	  methods: {
	    renderCounter() {
	      this.$refs.counter.innerHTML = '';
	      if (this.event.isNew) {
	        new ui_cnt.Counter({
	          value: 1,
	          color: ui_cnt.Counter.Color.DANGER
	        }).renderTo(this.$refs.counter);
	      }
	    },
	    bindRrulePopup() {
	      if (!this.$refs.rrule) {
	        return;
	      }
	      const popup = new main_popup.Popup({
	        bindElement: this.$refs.rrule,
	        content: this.event.rruleDescription,
	        darkMode: true,
	        bindOptions: {
	          position: 'top'
	        },
	        offsetTop: -10,
	        angle: true,
	        autoHide: true
	      });
	      this.bindShowOnHover(popup);
	    },
	    bindShowOnHover(popup) {
	      if (popup instanceof main_popup.Menu) {
	        popup = popup.getPopupWindow();
	      }
	      const bindElement = popup.bindElement;
	      const container = popup.getPopupContainer();
	      let hoverElement = null;
	      const closeMenuHandler = () => {
	        setTimeout(() => {
	          if (!container.contains(hoverElement) && !bindElement.contains(hoverElement)) {
	            popup.close();
	          }
	        }, 100);
	      };
	      const showMenuHandler = () => {
	        setTimeout(() => {
	          if (bindElement.contains(hoverElement)) {
	            popup.show();
	          }
	        }, 300);
	      };
	      const clickHandler = () => {
	        if (!popup.isShown()) {
	          popup.show();
	        }
	      };
	      main_core.Event.bind(document, 'mouseover', event => {
	        hoverElement = event.target;
	      });
	      main_core.Event.bind(bindElement, 'mouseenter', showMenuHandler);
	      main_core.Event.bind(bindElement, 'mouseleave', closeMenuHandler);
	      main_core.Event.bind(container, 'mouseleave', closeMenuHandler);
	      main_core.Event.bind(bindElement, 'click', clickHandler);
	      const adjustPosition = () => {
	        const angleLeft = main_popup.Popup.getOption('angleMinBottom');
	        const popupWidth = popup.getPopupContainer().offsetWidth;
	        const elementWidth = popup.bindElement.offsetWidth;
	        popup.setOffset({
	          offsetLeft: elementWidth / 2 - popupWidth / 2
	        });
	        popup.adjustPosition();
	        if (popup.angle) {
	          popup.setAngle({
	            offset: popupWidth / 2 + angleLeft
	          });
	        }
	      };
	      popup.subscribeFromOptions({
	        onShow: () => {
	          adjustPosition();
	          document.addEventListener('scroll', adjustPosition, true);
	        },
	        onClose: () => {
	          document.removeEventListener('scroll', adjustPosition, true);
	        }
	      });
	    }
	  },
	  mounted() {
	    this.renderCounter();
	    this.bindRrulePopup();
	  },
	  watch: {
	    event() {
	      this.renderCounter();
	    }
	  },
	  template: `
		<div class="calendar-open-events-list-item-name">
			<div
				class="calendar-open-events-list-item__event-name-with-counter"
				@click="$emit('openEvent')"
			>
				<div class="calendar-open-events-list-event-name-category" v-if="selectedCategoryId === 0">
					{{ event.categoryName }}
				</div>
				<div v-show="event.isNew" ref="counter"></div>
				<div class="calendar-open-events-list-item__event-name" :title="event.name">
					{{ event.name }}
				</div>
			</div>
			<div class="calendar-open-events-list-event-time">
				<div class="calendar-open-events-list-event-time-datetime">
					{{ event.formattedDateTime }}
				</div>
				<div class="calendar-open-events-list-event-time-full-day" v-if="event.isFullDay">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_ALL_DAY') }}
				</div>
				<div
					class="calendar-open-events-list-event-time-recursion"
					ref="rrule"
					v-if="event.rrule"
				>
					<div class="ui-icon-set --refresh-3"></div>
					<div class="calendar-open-events-list-event-time-rrule" v-if="formattedRrule">
						{{ formattedRrule }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	const Event = {
	  props: {
	    event: EventModel
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      selectedCategoryId: 'selectedCategoryId'
	    })
	  },
	  methods: {
	    async openComments() {
	      const categoryChannelId = this.event.categoryChannelId;
	      const messageId = this.event.threadId;
	      await im_public_iframe.Messenger.openChat(`chat${categoryChannelId}`, messageId);
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openComments, {
	        messageId
	      });
	    },
	    async openEvent() {
	      const {
	        EntryManager,
	        Entry,
	        CalendarSection
	      } = await main_core.Runtime.loadExtension('calendar.entry');
	      const section = new CalendarSection({
	        ...AppSettings.openEventSection,
	        PERM: {
	          'view_time': true,
	          'view_title': true,
	          'view_full': true,
	          'add': false,
	          'edit': false,
	          'edit_section': false,
	          'access': false
	        }
	      });
	      const entry = new Entry({
	        data: {
	          ID: this.event.id,
	          NAME: this.event.name,
	          SKIP_TIME: this.event.isFullDay,
	          dateFrom: this.event.dateFrom,
	          dateTo: this.event.dateTo,
	          SECT_ID: section.getId(),
	          RRULE: this.event.fields.rrule,
	          COLOR: this.event.color,
	          '~RRULE_DESCRIPTION': this.event.rruleDescription
	        }
	      });
	      EntryManager.openCompactViewForm({
	        entry,
	        sections: [section]
	      });
	      if (this.event.isNew) {
	        EventManager.setEventWatched(this.event.id);
	      }
	    },
	    async attendEvent(isAttendee) {
	      EventManager.setEventAttendee(this.event.id, isAttendee);
	    }
	  },
	  components: {
	    CalendarSheet,
	    AttendButton,
	    CommentCounter,
	    AttendeeCounter,
	    NameWithCounter
	  },
	  template: `
		<div class="calendar-open-events-list-item">
			<div class="calendar-open-events-list-item-info">
				<CalendarSheet :event="event"/>
				<NameWithCounter
					:event="event"
					@openEvent="openEvent()"
				/>
			</div>
			<div class="calendar-open-events-list-item-actions">
				<CommentCounter :commentsCount="event.commentsCount" @click="openComments()"/>
				<AttendeeCounter
					:attendeesCount="event.attendeesCount"
					:maxAttendees="event.eventOptions.maxAttendees"
				/>
				<AttendButton :isAttendee="event.isAttendee" @click="attendEvent(!event.isAttendee)"/>
			</div>
		</div>
	`
	};

	const EmptyState = {
	  template: `
		<div class="calendar-open-events-list-events-empty">
			<div class="calendar-open-events-list-events-empty-icon"></div>
			<div class="calendar-open-events-list-events-empty-title">
				{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_EMPTY_STATE') }}
			</div>
		</div>
	`
	};

	const WATCH_EVENT_MS = 2000;
	const EventList = {
	  data() {
	    return {
	      observedEvents: new Map(),
	      eventRefs: []
	    };
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      events: 'events',
	      selectedCategoryId: 'selectedCategoryId',
	      isFilterMode: 'isFilterMode'
	    }),
	    sortedEvents() {
	      return [...this.events].sort((a, b) => {
	        if (a.dateFrom.getTime() === b.dateFrom.getTime()) {
	          if (a.dateTo.getTime() === b.dateTo.getTime()) {
	            return parseInt(a.id) - parseInt(b.id);
	          }
	          return a.dateTo.getTime() - b.dateTo.getTime();
	        }
	        return a.dateFrom.getTime() - b.dateFrom.getTime();
	      });
	    }
	  },
	  methods: {
	    initObserver() {
	      this.observer = new IntersectionObserver(this.observerCallback, {
	        root: this.$refs.eventList,
	        threshold: 0.9
	      });
	    },
	    observerCallback(entries) {
	      entries.forEach(entry => {
	        if (entry.isIntersecting) {
	          this.processIntersectedElement(entry.target);
	        }
	      });
	    },
	    processIntersectedElement(element) {
	      const eventId = parseInt(element.dataset.eventId, 10);
	      if (this.observedEvents.has(eventId)) {
	        return;
	      }
	      this.observedEvents.set(eventId, eventId);
	      setTimeout(() => {
	        this.observer.unobserve(element);
	        EventManager.setEventWatched(eventId);
	      }, WATCH_EVENT_MS);
	    },
	    scrollToUpcomingEvent() {
	      const today = new Date();
	      today.setHours(0, 0, 0, 0);
	      const upcomingEvent = this.sortedEvents.find(event => event.dateFrom >= today);
	      if (!upcomingEvent) {
	        return;
	      }
	      this.$refs.eventList.scrollTop = this.eventRefs[upcomingEvent.uniqueId].offsetTop;
	    },
	    async loadOnScroll() {
	      const scrollTop = this.$refs.eventList.scrollTop;
	      const scrollHeight = this.$refs.eventList.scrollHeight;
	      const offsetHeight = this.$refs.eventList.offsetHeight;
	      if (scrollTop + 1 >= scrollHeight - offsetHeight) {
	        await this.$store.dispatch('setEventsUpdating', true);
	        const events = await this.getNext();
	        await this.$store.dispatch('setEvents', events);
	        await this.$store.dispatch('setEventsUpdating', false);
	      }
	      if (scrollTop <= 0) {
	        await this.$store.dispatch('setEventsUpdating', true);
	        const events = await this.getPrevious();
	        await this.$store.dispatch('setEvents', events);
	        await this.$store.dispatch('setEventsUpdating', false);
	        this.$refs.eventList.scrollTop += this.$refs.eventList.scrollHeight - scrollHeight;
	      }
	    },
	    getNext() {
	      if (this.isFilterMode) {
	        return EventManager.filterNext();
	      }
	      return EventManager.getNext(this.selectedCategoryId);
	    },
	    getPrevious() {
	      if (this.isFilterMode) {
	        return EventManager.filterPrevious();
	      }
	      return EventManager.getPrevious(this.selectedCategoryId);
	    },
	    setEventRef(ref) {
	      if (!ref) {
	        return;
	      }
	      const {
	        event,
	        $el
	      } = ref;
	      this.eventRefs[event.uniqueId] = $el;
	      if (event.isNew) {
	        this.observer.observe($el);
	      }
	    }
	  },
	  created() {
	    this.initObserver();
	  },
	  mounted() {
	    this.scrollToUpcomingEvent();
	    void this.loadOnScroll();
	    this.$refs.eventList.addEventListener('scroll', this.loadOnScroll);
	  },
	  beforeUnmount() {
	    this.observer.disconnect();
	    this.$refs.eventList.removeEventListener('scroll', this.loadOnScroll);
	  },
	  components: {
	    Event,
	    EmptyState
	  },
	  template: `
		<div class="calendar-open-events-list-events-list --calendar-scroll-bar" ref="eventList">
			<Event
				v-for="event of sortedEvents"
				:event="event"
				:data-event-id="event.id"
				:ref="setEventRef"
			/>
			<EmptyState v-if="events.length === 0"/>
		</div>
	`
	};

	const Events = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      selectedCategoryId: 'selectedCategoryId',
	      areEventsUpdating: 'areEventsUpdating',
	      isFilterMode: 'isFilterMode',
	      events: 'events'
	    })
	  },
	  mounted() {
	    EventManager.subscribe('update', this.eventManagerUpdateHandler);
	    EventManager.subscribe('delete', this.eventManagerDeleteHandler);
	    new main_loader.Loader().show(this.$refs.events);
	  },
	  beforeUnmount() {
	    EventManager.unsubscribe('update', this.eventManagerUpdateHandler);
	    EventManager.unsubscribe('delete', this.eventManagerDeleteHandler);
	  },
	  methods: {
	    async eventManagerUpdateHandler(event) {
	      const {
	        eventId
	      } = event.getData();
	      const events = await this.getEvents();
	      if (!events.find(it => it.id === eventId)) {
	        return;
	      }
	      this.$store.dispatch('setEvents', events);
	    },
	    async eventManagerDeleteHandler(event) {
	      const {
	        eventId
	      } = event.getData();
	      if (!this.events.find(it => it.id === eventId)) {
	        return;
	      }
	      const events = await this.getEvents();
	      this.$store.dispatch('setEvents', events);
	    },
	    async getEvents() {
	      if (this.isFilterMode) {
	        return EventManager.filterEvents();
	      }
	      return EventManager.getEvents(this.selectedCategoryId);
	    }
	  },
	  components: {
	    EventListTitle,
	    EventList
	  },
	  template: `
		<div
			class="calendar-open-events-list-events"
			:class="{ '--updating': areEventsUpdating }"
			ref="events"
		>
			<EventListTitle/>
			<EventList/>
		</div>
	`
	};

	const BaseComponent = {
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      areEventsLoading: 'areEventsLoading'
	    })
	  },
	  components: {
	    Categories,
	    Events
	  },
	  template: `
		<Categories/>
		<div class="calendar-open-events-list-events-loader" v-if="areEventsLoading"></div>
		<Events v-else/>
	`
	};

	const CategoriesSearchStore = {
	  state() {
	    return {
	      isSearchMode: false,
	      categoriesQuery: ''
	    };
	  },
	  actions: {
	    setSearchMode: (store, isSearchMode) => {
	      store.commit('setSearchMode', isSearchMode);
	    },
	    setCategoriesQuery: (store, categoriesQuery) => {
	      store.commit('setCategoriesQuery', categoriesQuery);
	    }
	  },
	  mutations: {
	    setSearchMode: (state, isSearchMode) => {
	      state.isSearchMode = isSearchMode;
	    },
	    setCategoriesQuery: (state, categoriesQuery) => {
	      state.categoriesQuery = categoriesQuery;
	    }
	  },
	  getters: {
	    isSearchMode: state => state.isSearchMode,
	    categoriesQuery: state => state.categoriesQuery
	  }
	};

	const CategoriesStore = {
	  state() {
	    return {
	      selectedCategoryId: 0,
	      categories: []
	    };
	  },
	  actions: {
	    setCategories: (store, categories) => {
	      store.commit('setCategories', categories);
	    },
	    selectCategory: (store, categoryId) => {
	      store.commit('selectCategory', categoryId);
	    }
	  },
	  mutations: {
	    setCategories: (state, categories) => {
	      state.categories = categories;
	    },
	    selectCategory: (state, categoryId) => {
	      state.selectedCategoryId = categoryId;
	    }
	  },
	  getters: {
	    categories: state => state.categories.map(category => {
	      category.isSelected = category.id === state.selectedCategoryId;
	      return category;
	    }),
	    selectedCategory: state => state.categories.find(it => it.id === state.selectedCategoryId),
	    selectedCategoryId: state => state.selectedCategoryId
	  }
	};

	const EventsStore = {
	  state() {
	    return {
	      events: [],
	      areEventsLoading: false,
	      areEventsUpdating: false,
	      isFilterMode: false
	    };
	  },
	  actions: {
	    setEventsLoading: (store, areEventsLoading) => {
	      store.commit('setEventsLoading', areEventsLoading);
	    },
	    setEventsUpdating: (store, areEventsUpdating) => {
	      store.commit('setEventsUpdating', areEventsUpdating);
	    },
	    setEvents: (store, events) => {
	      store.commit('setEvents', events);
	    },
	    setFilterMode: (store, isFilterMode) => {
	      store.commit('setFilterMode', isFilterMode);
	    }
	  },
	  mutations: {
	    setEventsLoading: (state, areEventsLoading) => {
	      state.areEventsLoading = areEventsLoading;
	    },
	    setEventsUpdating: (state, areEventsUpdating) => {
	      state.areEventsUpdating = areEventsUpdating;
	    },
	    setEvents: (state, events) => {
	      state.events = events;
	    },
	    setFilterMode: (state, isFilterMode) => {
	      state.isFilterMode = isFilterMode;
	    }
	  },
	  getters: {
	    areEventsLoading: state => state.areEventsLoading,
	    areEventsUpdating: state => state.areEventsUpdating,
	    events: state => state.events,
	    isFilterMode: state => state.isFilterMode
	  }
	};

	const Store = ui_vue3_vuex.createStore({
	  modules: {
	    categories: CategoriesStore,
	    categoriesSearch: CategoriesSearchStore,
	    events: EventsStore
	  }
	});

	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _mountApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mountApplication");
	class List {
	  constructor(params) {
	    Object.defineProperty(this, _mountApplication, {
	      value: _mountApplication2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _mountApplication)[_mountApplication]();
	  }
	}
	function _mountApplication2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = ui_vue3.BitrixVue.createApp({
	    name: 'List',
	    props: {
	      filterId: String
	    },
	    data() {
	      return {
	        isLoading: true
	      };
	    },
	    computed: {
	      ...ui_vue3_vuex.mapGetters({
	        selectedCategoryId: 'selectedCategoryId'
	      })
	    },
	    async mounted() {
	      this.bindFilter(this.filterId);
	      const categories = await CategoryManager.getCategories();
	      const events = await EventManager.getEvents(this.selectedCategoryId);
	      this.isLoading = false;
	      this.$store.dispatch('setCategories', categories);
	      this.$store.dispatch('setEvents', events);
	    },
	    methods: {
	      bindFilter(filterId) {
	        const filter = new calendar_openEvents_filter.Filter(filterId);
	        EventManager.setFilter(filter);
	        filter.subscribe('beforeApply', () => {
	          this.isLoading = true;
	        });
	        filter.subscribe('apply', async () => {
	          const events = await EventManager.filterEvents();
	          this.$store.dispatch('setEvents', events);
	          this.$store.dispatch('setFilterMode', true);
	          this.isLoading = false;
	        });
	        filter.subscribe('clear', async () => {
	          const events = await EventManager.getEvents(this.selectedCategoryId);
	          this.$store.dispatch('setEvents', events);
	          this.$store.dispatch('setFilterMode', false);
	          this.isLoading = false;
	        });
	      }
	    },
	    components: {
	      BaseComponent
	    },
	    template: `
					<div class="calendar-open-events-list-loader" v-if="isLoading"></div>
					<BaseComponent v-else/>
				`
	  }, {
	    filterId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].filterId
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].use(Store);
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].mount(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].container);
	}

	exports.List = List;

}((this.BX.Calendar.OpenEvents = this.BX.Calendar.OpenEvents || {}),BX.Vue3,BX.UI.EntitySelector,BX.UI,BX.Calendar.OpenEvents,BX,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Main,BX,BX.UI,BX,BX,BX.Main,BX.UI,BX.Vue3.Vuex));
//# sourceMappingURL=list.bundle.js.map
