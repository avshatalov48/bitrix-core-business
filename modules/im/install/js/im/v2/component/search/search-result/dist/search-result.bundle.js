this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_designTokens,ui_fonts_opensans,ui_vue3,im_public,im_v2_lib_utils,im_v2_lib_user,im_v2_application_core,ui_dexie,im_v2_lib_rest,im_v2_lib_logger,im_v2_lib_menu,main_core,im_v2_component_animation,main_core_events,im_v2_const,im_v2_component_elements) {
	'use strict';

	const SearchUtils = {
	  getWordsFromString(string) {
	    const clearedString = string.replaceAll('(', ' ').replaceAll(')', ' ').replaceAll('[', ' ').replaceAll(']', ' ').replaceAll('{', ' ').replaceAll('}', ' ').replaceAll('<', ' ').replaceAll('>', ' ').replaceAll('-', ' ').replaceAll('#', ' ').replaceAll('"', ' ').replaceAll('\'', ' ').replace(/\s\s+/g, ' ');
	    return clearedString.split(' ').filter(word => word !== '');
	  },
	  getTypeByEntityId(entityId) {
	    switch (entityId) {
	      case im_v2_const.SearchEntityIdTypes.user:
	      case im_v2_const.SearchEntityIdTypes.bot:
	        return 'user';
	      case im_v2_const.SearchEntityIdTypes.chat:
	      case im_v2_const.SearchEntityIdTypes.chatUser:
	        return 'chat';
	      case im_v2_const.SearchEntityIdTypes.department:
	        return 'department';
	      case im_v2_const.SearchEntityIdTypes.network:
	        return 'network';
	      default:
	        throw new Error(`Unknown entity id: ${entityId}`);
	    }
	  },
	  createItemMap(items) {
	    const map = new Map();
	    items.forEach(item => {
	      const mapItem = new SearchItem(item);
	      map.set(mapItem.getEntityFullId(), mapItem);
	    });
	    return map;
	  },
	  getFirstItemFromMap(map) {
	    const iterator = map.entries();
	    const firstIteration = iterator.next();
	    const firstItem = firstIteration.value;
	    const [, content] = firstItem;
	    return content;
	  },
	  convertKeysToLowerCase(object) {
	    const result = {};
	    Object.keys(object).forEach(key => {
	      if (main_core.Type.isObject(object[key]) && !main_core.Type.isArray(object[key])) {
	        result[key.toLowerCase()] = this.convertKeysToLowerCase(object[key]);
	      } else {
	        result[key.toLowerCase()] = object[key];
	      }
	    });
	    return result;
	  },
	  convertKeysToUpperCase(object) {
	    const result = {};
	    Object.keys(object).forEach(key => {
	      if (main_core.Type.isObject(object[key]) && !main_core.Type.isArray(object[key])) {
	        result[key.toUpperCase()] = this.convertKeysToUpperCase(object[key]);
	      } else {
	        result[key.toUpperCase()] = object[key];
	      }
	    });
	    return result;
	  },
	  prepareRecentItems(recentItems) {
	    if (!recentItems) {
	      return [];
	    }
	    return recentItems.map(item => {
	      const [entityId, id] = item;
	      const type = SearchUtils.getTypeByEntityId(entityId);
	      return {
	        cacheId: `${type}|${id}`,
	        date: new Date()
	      };
	    });
	  }
	};

	var _isFromProviderResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFromProviderResponse");
	var _setId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setId");
	var _setDialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialogId");
	var _setEntityId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setEntityId");
	var _setEntityType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setEntityType");
	var _setTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setTitle");
	var _setSubtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setSubtitle");
	var _setName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setName");
	var _setLastName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setLastName");
	var _setSecondName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setSecondName");
	var _setPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPosition");
	var _setAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAvatar");
	var _setAvatarOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAvatarOptions");
	var _setContextSort = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setContextSort");
	var _setRawData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setRawData");
	class SearchItem {
	  constructor(_itemOptions) {
	    Object.defineProperty(this, _setRawData, {
	      value: _setRawData2
	    });
	    Object.defineProperty(this, _setContextSort, {
	      value: _setContextSort2
	    });
	    Object.defineProperty(this, _setAvatarOptions, {
	      value: _setAvatarOptions2
	    });
	    Object.defineProperty(this, _setAvatar, {
	      value: _setAvatar2
	    });
	    Object.defineProperty(this, _setPosition, {
	      value: _setPosition2
	    });
	    Object.defineProperty(this, _setSecondName, {
	      value: _setSecondName2
	    });
	    Object.defineProperty(this, _setLastName, {
	      value: _setLastName2
	    });
	    Object.defineProperty(this, _setName, {
	      value: _setName2
	    });
	    Object.defineProperty(this, _setSubtitle, {
	      value: _setSubtitle2
	    });
	    Object.defineProperty(this, _setTitle, {
	      value: _setTitle2
	    });
	    Object.defineProperty(this, _setEntityType, {
	      value: _setEntityType2
	    });
	    Object.defineProperty(this, _setEntityId, {
	      value: _setEntityId2
	    });
	    Object.defineProperty(this, _setDialogId, {
	      value: _setDialogId2
	    });
	    Object.defineProperty(this, _setId, {
	      value: _setId2
	    });
	    Object.defineProperty(this, _isFromProviderResponse, {
	      value: _isFromProviderResponse2
	    });
	    this.entityType = null;
	    this.dialogId = null;
	    this.title = null;
	    this.subtitle = null;
	    this.name = null;
	    this.lastName = null;
	    this.secondName = null;
	    this.position = null;
	    this.avatar = null;
	    this.avatarOptions = null;
	    this.customSort = 0;
	    this.contextSort = 0;
	    this.fromStore = false;
	    this.rawData = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _setRawData)[_setRawData](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setId)[_setId](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setDialogId)[_setDialogId](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setEntityId)[_setEntityId](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setEntityType)[_setEntityType](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setTitle)[_setTitle](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setSubtitle)[_setSubtitle](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setName)[_setName](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setLastName)[_setLastName](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setSecondName)[_setSecondName](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setPosition)[_setPosition](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAvatar)[_setAvatar](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setAvatarOptions)[_setAvatarOptions](_itemOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _setContextSort)[_setContextSort](_itemOptions);
	  }
	  isFromModel(itemOptions) {
	    return main_core.Type.isString(itemOptions.dialogId) && main_core.Type.isObject(itemOptions.dialog);
	  }
	  getId() {
	    return this.id;
	  }
	  getEntityId() {
	    return this.entityId;
	  }
	  getEntityType() {
	    return this.entityType.toLowerCase();
	  }
	  getEntityFullId() {
	    const type = SearchUtils.getTypeByEntityId(this.entityId);
	    return `${type}|${this.id}`;
	  }
	  getTitle() {
	    return this.title;
	  }
	  getSubtitle() {
	    return this.subtitle;
	  }
	  getName() {
	    return this.name;
	  }
	  getLastName() {
	    return this.lastName;
	  }
	  getSecondName() {
	    return this.secondName;
	  }
	  getPosition() {
	    return this.position;
	  }
	  getCustomData() {
	    return this.rawData.customData;
	  }
	  getDialogId() {
	    return this.dialogId;
	  }
	  getAvatar() {
	    return this.avatar;
	  }
	  getAvatarOptions() {
	    return this.avatarOptions;
	  }
	  getContextSort() {
	    return this.contextSort ? this.contextSort : 0;
	  }
	  addCustomSort(value) {
	    this.customSort += value;
	  }
	  getCustomSort() {
	    return this.customSort;
	  }
	  isUser() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](this.rawData)) {
	      var _this$rawData$customD;
	      return !!((_this$rawData$customD = this.rawData.customData) != null && _this$rawData$customD.imUser) && this.rawData.customData.imUser.ID > 0;
	    }
	    return !!this.rawData.user;
	  }
	  isChat() {
	    return [im_v2_const.SearchEntityIdTypes.chat, im_v2_const.SearchEntityIdTypes.chatUser].includes(this.getEntityId());
	  }
	  isExtranet() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](this.rawData)) {
	      var _this$rawData$customD2, _this$rawData$customD3, _this$rawData$customD4, _this$rawData$customD5;
	      return !!((_this$rawData$customD2 = this.rawData.customData) != null && (_this$rawData$customD3 = _this$rawData$customD2.imUser) != null && _this$rawData$customD3.EXTRANET) || !!((_this$rawData$customD4 = this.rawData.customData) != null && (_this$rawData$customD5 = _this$rawData$customD4.imChat) != null && _this$rawData$customD5.EXTRANET);
	    } else if (this.isFromModel(this.rawData)) {
	      var _this$rawData$user;
	      return !!((_this$rawData$user = this.rawData.user) != null && _this$rawData$user.extranet) || !!this.rawData.dialog.extranet;
	    }
	  }
	  getUserCustomData() {
	    var _this$rawData$customD6;
	    return (_this$rawData$customD6 = this.rawData.customData) != null && _this$rawData$customD6.imUser ? this.rawData.customData.imUser : null;
	  }
	  getChatCustomData() {
	    var _this$rawData$customD7;
	    return (_this$rawData$customD7 = this.rawData.customData) != null && _this$rawData$customD7.imChat ? this.rawData.customData.imChat : null;
	  }
	  isOpeLinesType() {
	    return this.getEntityType() === 'lines';
	  }
	  isDepartmentType() {
	    return this.getEntityId() === im_v2_const.SearchEntityIdTypes.department;
	  }
	  isNetworkType() {
	    return this.getEntityId() === im_v2_const.SearchEntityIdTypes.network;
	  }
	  getOpenlineEntityId() {
	    var _this$rawData$customD8, _this$rawData$customD9;
	    if (!this.isOpeLinesType()) {
	      return '';
	    }
	    const entityId = (_this$rawData$customD8 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD9 = _this$rawData$customD8.imChat) == null ? void 0 : _this$rawData$customD9.ENTITY_ID;
	    return entityId.toString().split('|')[0];
	  }
	  getAvatarColor() {
	    let color = '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](this.rawData)) {
	      if (this.isUser()) {
	        var _this$rawData$customD10, _this$rawData$customD11, _this$rawData$customD12;
	        color = (_this$rawData$customD10 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD11 = _this$rawData$customD10.imUser) == null ? void 0 : (_this$rawData$customD12 = _this$rawData$customD11.COLOR) == null ? void 0 : _this$rawData$customD12.toString();
	      } else if (this.isChat()) {
	        var _this$rawData$customD13, _this$rawData$customD14, _this$rawData$customD15;
	        color = (_this$rawData$customD13 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD14 = _this$rawData$customD13.imChat) == null ? void 0 : (_this$rawData$customD15 = _this$rawData$customD14.COLOR) == null ? void 0 : _this$rawData$customD15.toString();
	      }
	    } else if (this.isFromModel(this.rawData)) {
	      color = this.rawData.dialog.color.toString();
	    }
	    return color;
	  }
	  isCrmSession() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](this.rawData) && this.isOpeLinesType()) {
	      var _this$rawData$customD16, _this$rawData$customD17;
	      const sessionData = (_this$rawData$customD16 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD17 = _this$rawData$customD16.imChat) == null ? void 0 : _this$rawData$customD17.ENTITY_DATA_1.toString().split('|');
	      return sessionData[0] === 'Y';
	    }
	    return false;
	  }
	}
	function _isFromProviderResponse2(itemOptions) {
	  return main_core.Type.isString(itemOptions.entityId) && !main_core.Type.isNil(itemOptions.id);
	}
	function _setId2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.id = itemOptions.id;
	  } else if (this.isFromModel(itemOptions)) {
	    const id = itemOptions.dialogId.startsWith('chat') ? itemOptions.dialogId.slice(4) : itemOptions.dialogId;
	    this.id = Number.parseInt(id, 10);
	    this.fromStore = true;
	  }
	}
	function _setDialogId2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    var _itemOptions$customDa, _itemOptions$customDa2, _itemOptions$customDa3, _itemOptions$customDa4;
	    if (((_itemOptions$customDa = itemOptions.customData) == null ? void 0 : (_itemOptions$customDa2 = _itemOptions$customDa.imChat) == null ? void 0 : _itemOptions$customDa2.ID) > 0) {
	      this.dialogId = `chat${itemOptions.customData.imChat.ID}`;
	    } else if (((_itemOptions$customDa3 = itemOptions.customData) == null ? void 0 : (_itemOptions$customDa4 = _itemOptions$customDa3.imUser) == null ? void 0 : _itemOptions$customDa4.ID) > 0) {
	      this.dialogId = itemOptions.customData.imUser.ID.toString();
	    }
	  } else if (this.isFromModel(itemOptions)) {
	    this.dialogId = itemOptions.dialogId;
	  }
	}
	function _setEntityId2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.entityId = itemOptions.entityId;
	  } else if (this.isFromModel(itemOptions)) {
	    if (!itemOptions.user) {
	      this.entityId = im_v2_const.SearchEntityIdTypes.chat;
	    } else if (itemOptions.user.bot) {
	      this.entityId = im_v2_const.SearchEntityIdTypes.bot;
	    } else {
	      this.entityId = im_v2_const.SearchEntityIdTypes.user;
	    }
	  }
	}
	function _setEntityType2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.entityType = itemOptions.entityType ? itemOptions.entityType.toLowerCase() : '';
	  } else if (this.isFromModel(itemOptions)) {
	    const {
	      type
	    } = itemOptions.dialog;
	    if (type === im_v2_const.DialogType.user) {
	      this.entityType = itemOptions.user.extranet ? 'extranet' : 'employee';
	    } else {
	      this.entityType = type;
	    }
	  }
	}
	function _setTitle2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.title = itemOptions.title;
	  } else if (this.isFromModel(itemOptions)) {
	    this.title = itemOptions.dialog.name;
	  }
	}
	function _setSubtitle2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.subtitle = itemOptions.subtitle;
	  }
	}
	function _setName2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    var _itemOptions$customDa5;
	    this.name = (_itemOptions$customDa5 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa5.name;
	  } else if (this.isFromModel(itemOptions)) {
	    var _itemOptions$user;
	    this.name = (_itemOptions$user = itemOptions.user) == null ? void 0 : _itemOptions$user.firstName;
	  }
	}
	function _setLastName2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    var _itemOptions$customDa6;
	    this.lastName = (_itemOptions$customDa6 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa6.lastName;
	  } else if (this.isFromModel(itemOptions)) {
	    var _itemOptions$user2;
	    this.lastName = (_itemOptions$user2 = itemOptions.user) == null ? void 0 : _itemOptions$user2.lastName;
	  }
	}
	function _setSecondName2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    var _itemOptions$customDa7;
	    this.secondName = (_itemOptions$customDa7 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa7.secondName;
	  }
	}
	function _setPosition2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    var _itemOptions$customDa8;
	    this.position = (_itemOptions$customDa8 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa8.position;
	  } else if (this.isFromModel(itemOptions)) {
	    var _itemOptions$user3;
	    this.position = (_itemOptions$user3 = itemOptions.user) == null ? void 0 : _itemOptions$user3.workPosition;
	  }
	}
	function _setAvatar2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.avatar = itemOptions.avatar !== '' ? itemOptions.avatar : null;
	  } else if (this.isFromModel(itemOptions)) {
	    const avatar = itemOptions.user ? itemOptions.user.avatar : itemOptions.dialog.avatar;
	    this.avatar = avatar !== '' ? decodeURI(avatar) : null;
	  }
	}
	function _setAvatarOptions2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.avatarOptions = itemOptions.avatarOptions;
	  }
	}
	function _setContextSort2(itemOptions) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFromProviderResponse)[_isFromProviderResponse](itemOptions)) {
	    this.contextSort = itemOptions.contextSort;
	  }
	}
	function _setRawData2(itemOptions) {
	  this.rawData = itemOptions;
	}

	const CHAT_TYPE_OPEN = 'O';
	const CHAT_TYPE_PRIVATE = 'C';
	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _currentUserId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentUserId");
	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _networkSearchButtonClicked = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("networkSearchButtonClicked");
	var _get = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("get");
	var _getBotsEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBotsEntity");
	var _getUserEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserEntity");
	var _getChatUserEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatUserEntity");
	var _getNetworkEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNetworkEntity");
	var _getDepartmentEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDepartmentEntity");
	var _getChatEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatEntity");
	var _needChats = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needChats");
	var _needBots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needBots");
	var _needNetwork = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needNetwork");
	var _needDepartments = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needDepartments");
	var _isDepartmentsAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDepartmentsAvailable");
	var _needExtranet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needExtranet");
	var _needCurrentUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needCurrentUser");
	var _isCurrentUserExtranet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCurrentUserExtranet");
	var _getDefaultConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultConfig");
	class Config {
	  constructor(config) {
	    Object.defineProperty(this, _getDefaultConfig, {
	      value: _getDefaultConfig2
	    });
	    Object.defineProperty(this, _isCurrentUserExtranet, {
	      value: _isCurrentUserExtranet2
	    });
	    Object.defineProperty(this, _needCurrentUser, {
	      value: _needCurrentUser2
	    });
	    Object.defineProperty(this, _needExtranet, {
	      value: _needExtranet2
	    });
	    Object.defineProperty(this, _isDepartmentsAvailable, {
	      value: _isDepartmentsAvailable2
	    });
	    Object.defineProperty(this, _needDepartments, {
	      value: _needDepartments2
	    });
	    Object.defineProperty(this, _needNetwork, {
	      value: _needNetwork2
	    });
	    Object.defineProperty(this, _needBots, {
	      value: _needBots2
	    });
	    Object.defineProperty(this, _needChats, {
	      value: _needChats2
	    });
	    Object.defineProperty(this, _getChatEntity, {
	      value: _getChatEntity2
	    });
	    Object.defineProperty(this, _getDepartmentEntity, {
	      value: _getDepartmentEntity2
	    });
	    Object.defineProperty(this, _getNetworkEntity, {
	      value: _getNetworkEntity2
	    });
	    Object.defineProperty(this, _getChatUserEntity, {
	      value: _getChatUserEntity2
	    });
	    Object.defineProperty(this, _getUserEntity, {
	      value: _getUserEntity2
	    });
	    Object.defineProperty(this, _getBotsEntity, {
	      value: _getBotsEntity2
	    });
	    Object.defineProperty(this, _get, {
	      value: _get2
	    });
	    Object.defineProperty(this, _config, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentUserId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _networkSearchButtonClicked, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config] = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getDefaultConfig)[_getDefaultConfig](),
	      ...config
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = main_core.Extension.getSettings('im.v2.component.search.search-result');
	    babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId] = im_v2_application_core.Core.getUserId();
	  }
	  getRecentRequestConfig() {
	    const entities = [babelHelpers.classPrivateFieldLooseBase(this, _getUserEntity)[_getUserEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getChatEntity)[_getChatEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getBotsEntity)[_getBotsEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getChatUserEntity)[_getChatUserEntity]()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](entities);
	  }
	  getSearch() {
	    const entities = [babelHelpers.classPrivateFieldLooseBase(this, _getUserEntity)[_getUserEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getChatEntity)[_getChatEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getBotsEntity)[_getBotsEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getChatUserEntity)[_getChatUserEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getDepartmentEntity)[_getDepartmentEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getNetworkEntity)[_getNetworkEntity]()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](entities);
	  }
	  getDepartmentUsers() {
	    const entities = [babelHelpers.classPrivateFieldLooseBase(this, _getUserEntity)[_getUserEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getBotsEntity)[_getBotsEntity](), babelHelpers.classPrivateFieldLooseBase(this, _getDepartmentEntity)[_getDepartmentEntity]()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](entities);
	  }
	  getNetwork() {
	    const entities = [babelHelpers.classPrivateFieldLooseBase(this, _getNetworkEntity)[_getNetworkEntity]()];
	    return babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](entities);
	  }
	  enableNetworkSearch() {
	    babelHelpers.classPrivateFieldLooseBase(this, _networkSearchButtonClicked)[_networkSearchButtonClicked] = true;
	  }
	  disableNetworkSearch() {
	    babelHelpers.classPrivateFieldLooseBase(this, _networkSearchButtonClicked)[_networkSearchButtonClicked] = false;
	  }
	  isItemAllowed(item) {
	    if (item.isUser() && item.isExtranet() && !babelHelpers.classPrivateFieldLooseBase(this, _needExtranet)[_needExtranet]()) {
	      return false;
	    }
	    if (item.isNetworkType() && !babelHelpers.classPrivateFieldLooseBase(this, _needNetwork)[_needNetwork]()) {
	      return false;
	    }
	    if (item.isChat() && !babelHelpers.classPrivateFieldLooseBase(this, _needChats)[_needChats]()) {
	      return false;
	    }
	    if (item.isUser() && !babelHelpers.classPrivateFieldLooseBase(this, _needCurrentUser)[_needCurrentUser]() && item.getId() === babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId]) {
	      return false;
	    }
	    return true;
	  }
	  isNetworkAvailable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].get('isNetworkAvailable', false) && babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].network;
	  }
	}
	function _get2(entities) {
	  entities = entities.filter(entity => !main_core.Type.isNil(entity));
	  return {
	    dialog: {
	      entities: entities,
	      preselectedItems: [],
	      clearUnavailableItems: false,
	      context: 'IM_CHAT_SEARCH',
	      id: 'im-search'
	    }
	  };
	}
	function _getBotsEntity2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _needBots)[_needBots]()) {
	    return null;
	  }
	  return {
	    id: 'im-bot',
	    options: {
	      searchableBotTypes: ['H', 'B', 'S', 'N'],
	      fillDialogWithDefaultValues: false
	    },
	    dynamicLoad: true,
	    dynamicSearch: true
	  };
	}
	function _getUserEntity2() {
	  return {
	    id: 'user',
	    dynamicLoad: true,
	    dynamicSearch: true,
	    filters: [{
	      id: 'im.userDataFilter'
	    }]
	  };
	}
	function _getChatUserEntity2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _needChats)[_needChats]()) {
	    return null;
	  }
	  return {
	    id: 'im-chat-user',
	    options: {
	      searchableChatTypes: [CHAT_TYPE_OPEN, CHAT_TYPE_PRIVATE],
	      fillDialogWithDefaultValues: false
	    },
	    dynamicLoad: true,
	    dynamicSearch: true
	  };
	}
	function _getNetworkEntity2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _needNetwork)[_needNetwork]()) {
	    return;
	  }
	  return {
	    id: 'imbot-network',
	    dynamicSearch: true,
	    options: {
	      'filterExistingLines': true
	    }
	  };
	}
	function _getDepartmentEntity2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _needDepartments)[_needDepartments]) {
	    return null;
	  }
	  return {
	    id: 'department',
	    dynamicLoad: true,
	    dynamicSearch: true,
	    options: {
	      selectMode: 'usersAndDepartments',
	      allowSelectRootDepartment: true
	    },
	    filters: [{
	      id: 'im.departmentDataFilter'
	    }]
	  };
	}
	function _getChatEntity2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _needChats)[_needChats]()) {
	    return null;
	  }
	  return {
	    id: 'im-chat',
	    options: {
	      searchableChatTypes: [CHAT_TYPE_PRIVATE, CHAT_TYPE_OPEN],
	      fillDialogWithDefaultValues: false
	    },
	    dynamicLoad: true,
	    dynamicSearch: true
	  };
	}
	function _needChats2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].chats;
	}
	function _needBots2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].bots;
	}
	function _needNetwork2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _networkSearchButtonClicked)[_networkSearchButtonClicked] && this.isNetworkAvailable();
	}
	function _needDepartments2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isCurrentUserExtranet)[_isCurrentUserExtranet]() || !babelHelpers.classPrivateFieldLooseBase(this, _isDepartmentsAvailable)[_isDepartmentsAvailable]()) {
	    return false;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].departments;
	}
	function _isDepartmentsAvailable2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].get('isDepartmentsAvailable', false);
	}
	function _needExtranet2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].extranet;
	}
	function _needCurrentUser2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].currentUser;
	}
	function _isCurrentUserExtranet2() {
	  const user = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['users/get'](babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId], true);
	  return user.extranet;
	}
	function _getDefaultConfig2() {
	  return {
	    currentUser: true,
	    excludeUsers: [],
	    extranet: true,
	    chats: true,
	    bots: true,
	    departments: true,
	    network: true
	  };
	}

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	var _addDialoguesToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addDialoguesToModel");
	var _setDialoguesToModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDialoguesToModel");
	var _removeActivityData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeActivityData");
	var _prepareDataForModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareDataForModels");
	class StoreUpdater {
	  constructor() {
	    Object.defineProperty(this, _prepareDataForModels, {
	      value: _prepareDataForModels2
	    });
	    Object.defineProperty(this, _removeActivityData, {
	      value: _removeActivityData2
	    });
	    Object.defineProperty(this, _setDialoguesToModel, {
	      value: _setDialoguesToModel2
	    });
	    Object.defineProperty(this, _addDialoguesToModel, {
	      value: _addDialoguesToModel2
	    });
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  update(updateConfig) {
	    const {
	      items,
	      onlyAdd
	    } = updateConfig;
	    const {
	      users,
	      dialogues
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareDataForModels)[_prepareDataForModels](items);
	    if (onlyAdd) {
	      const cleanedUsers = babelHelpers.classPrivateFieldLooseBase(this, _removeActivityData)[_removeActivityData](users);
	      return Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].addUsersToModel(cleanedUsers), babelHelpers.classPrivateFieldLooseBase(this, _addDialoguesToModel)[_addDialoguesToModel](dialogues)]);
	    }
	    return Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(users), babelHelpers.classPrivateFieldLooseBase(this, _setDialoguesToModel)[_setDialoguesToModel](dialogues)]);
	  }
	}
	function _addDialoguesToModel2(dialogues) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/add', dialogues);
	}
	function _setDialoguesToModel2(dialogues) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('dialogues/set', dialogues);
	}
	function _removeActivityData2(users) {
	  return users.map(user => {
	    return {
	      ...user,
	      last_activity_date: false,
	      mobile_last_date: false,
	      status: '',
	      idle: false,
	      absent: false,
	      birthday: ''
	    };
	  });
	}
	function _prepareDataForModels2(items) {
	  const result = {
	    users: [],
	    dialogues: []
	  };
	  items.forEach(item => {
	    if (!item.getCustomData() || item.fromStore) {
	      return;
	    }
	    if (item.isUser()) {
	      const preparedUser = SearchUtils.convertKeysToLowerCase(item.getUserCustomData());
	      result.users.push(preparedUser);
	    }
	    if (item.isChat() && !item.isOpeLinesType()) {
	      const chat = SearchUtils.convertKeysToLowerCase(item.getChatCustomData());
	      result.dialogues.push({
	        ...chat,
	        dialogId: `chat${chat.id}`
	      });
	    }
	  });
	  return result;
	}

	var _store$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _isRussianInterface = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRussianInterface");
	class LayoutManager {
	  constructor() {
	    Object.defineProperty(this, _isRussianInterface, {
	      value: _isRussianInterface2
	    });
	    Object.defineProperty(this, _store$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$2)[_store$2] = im_v2_application_core.Core.getStore();
	  }
	  changeLayout(query) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isRussianInterface)[_isRussianInterface]() && BX.correctText) {
	      // eslint-disable-next-line bitrix-rules/no-bx
	      return BX.correctText(query, {
	        replace_way: 'AUTO'
	      });
	    }
	    return query;
	  }
	  needLayoutChange(originalLayoutQuery) {
	    const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
	    const isIdenticalQuery = wrongLayoutQuery === originalLayoutQuery;
	    return babelHelpers.classPrivateFieldLooseBase(this, _isRussianInterface)[_isRussianInterface]() && !isIdenticalQuery;
	  }
	}
	function _isRussianInterface2() {
	  return im_v2_application_core.Core.getLanguageId() === 'ru';
	}

	class SortingResult {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	    this.layoutManager = new LayoutManager();
	  }
	  sortItemsByEntityIdAndContextSort(items) {
	    const entityWeight = {
	      'user': 100,
	      'im-chat': 80,
	      'im-chat-user': 80,
	      'im-bot': 70,
	      'department': 60,
	      'extranet': 10
	    };
	    return new Map([...items.entries()].sort((firstItem, secondItem) => {
	      const [, firstItemValue] = firstItem;
	      const [, secondItemValue] = secondItem;
	      const secondItemEntityId = secondItemValue.isExtranet() ? 'extranet' : secondItemValue.getEntityId();
	      const firstItemEntityId = firstItemValue.isExtranet() ? 'extranet' : firstItemValue.getEntityId();
	      if (entityWeight[secondItemEntityId] < entityWeight[firstItemEntityId]) {
	        return -1;
	      } else if (entityWeight[secondItemEntityId] > entityWeight[firstItemEntityId]) {
	        return 1;
	      } else {
	        return secondItemValue.getContextSort() - firstItemValue.getContextSort();
	      }
	    }));
	  }
	  allocateSearchResults(items, originalLayoutQuery) {
	    const usersAndChats = new Map();
	    const chatUsers = new Map();
	    const departments = new Map();
	    const openLines = new Map();
	    const network = new Map();
	    items.forEach(item => {
	      switch (item.getEntityId()) {
	        case im_v2_const.SearchEntityIdTypes.chatUser:
	          {
	            chatUsers.set(item.getEntityFullId(), item);
	            break;
	          }
	        case im_v2_const.SearchEntityIdTypes.department:
	          {
	            departments.set(item.getEntityFullId(), item);
	            break;
	          }
	        case im_v2_const.SearchEntityIdTypes.network:
	          {
	            network.set(item.getEntityFullId(), item);
	            break;
	          }
	        default:
	          {
	            if (item.isOpeLinesType()) {
	              openLines.set(item.getEntityFullId(), item);
	            } else {
	              usersAndChats.set(item.getEntityFullId(), item);
	            }
	          }
	      }
	    });
	    return {
	      usersAndChats: this.getSortedItems(usersAndChats, originalLayoutQuery),
	      chatUsers: chatUsers,
	      departments: departments,
	      openLines: openLines,
	      network: network
	    };
	  }
	  sortItemsBySearchField(items, originalLayoutQuery) {
	    let queryWords = SearchUtils.getWordsFromString(originalLayoutQuery);
	    if (this.layoutManager.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQueryWords = SearchUtils.getWordsFromString(this.layoutManager.changeLayout(originalLayoutQuery));
	      queryWords = [...queryWords, ...wrongLayoutQueryWords];
	    }
	    const uniqueWords = [...new Set(queryWords)];
	    const searchFieldsWeight = {
	      title: 10000,
	      name: 1000,
	      lastName: 100,
	      position: 1
	    };
	    items.forEach(item => {
	      uniqueWords.forEach(word => {
	        var _item$getName, _item$getLastName, _item$getPosition;
	        if (item.getTitle().toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.title);
	        } else if ((_item$getName = item.getName()) != null && _item$getName.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.name);
	        } else if ((_item$getLastName = item.getLastName()) != null && _item$getLastName.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.lastName);
	        } else if ((_item$getPosition = item.getPosition()) != null && _item$getPosition.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.position);
	        }
	      });
	    });
	    return new Map([...items.entries()].sort((firstItem, secondItem) => {
	      const [, firstItemValue] = firstItem;
	      const [, secondItemValue] = secondItem;
	      return secondItemValue.getCustomSort() - firstItemValue.getCustomSort();
	    }));
	  }
	  getSortedItems(items, originalLayoutQuery) {
	    let sortedItems = this.sortItemsBySearchField(items, originalLayoutQuery);
	    sortedItems = this.sortItemsByEntityIdAndContextSort(sortedItems);
	    return sortedItems;
	  }
	}

	const collator = new Intl.Collator(undefined, {
	  sensitivity: 'base'
	});
	var _store$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	class RecentStateSearchService {
	  constructor() {
	    Object.defineProperty(this, _store$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3] = im_v2_application_core.Core.getStore();
	    this.layoutManager = new LayoutManager();
	  }
	  load() {
	    const recentUsers = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['recent/getSortedCollection'].forEach(recentItem => {
	      const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['dialogues/get'](recentItem.dialogId, true);
	      const user = babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['users/get'](recentItem.dialogId, true);
	      recentUsers.push({
	        dialogId: recentItem.dialogId,
	        dialog,
	        user
	      });
	    });
	    return recentUsers.filter(item => {
	      return item.dialog.type === 'user' && !item.user.bot && item.user.id !== im_v2_application_core.Core.getUserId();
	    });
	  }
	  search(originalLayoutQuery) {
	    let wrongLayoutSearchPromise = Promise.resolve([]);
	    if (this.layoutManager.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQuery = this.layoutManager.changeLayout(originalLayoutQuery);
	      wrongLayoutSearchPromise = this.getItemsFromRecentListByQuery(wrongLayoutQuery);
	    }
	    const correctLayoutSearchPromise = this.getItemsFromRecentListByQuery(originalLayoutQuery);
	    return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
	      return new Map([...result[0], ...result[1]]);
	    });
	  }
	  getItemsFromRecentListByQuery(query) {
	    const queryWords = SearchUtils.getWordsFromString(query);
	    return SearchUtils.createItemMap(this.getFromStore(queryWords));
	  }
	  getFromStore(queryWords) {
	    const recentListItems = this.getRecentListItems();
	    const foundItems = [];
	    recentListItems.forEach(recentListItem => {
	      if (this.searchByQueryWords(recentListItem, queryWords)) {
	        foundItems.push(recentListItem);
	      }
	    });
	    return foundItems;
	  }
	  //endregion

	  getRecentListItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['recent/getSortedCollection'].map(item => {
	      const dialog = babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['dialogues/get'](item.dialogId, true);
	      const isUser = dialog.type === im_v2_const.DialogType.user;
	      const recentListItem = {
	        dialogId: item.dialogId,
	        dialog: dialog
	      };
	      if (isUser) {
	        recentListItem.user = babelHelpers.classPrivateFieldLooseBase(this, _store$3)[_store$3].getters['users/get'](item.dialogId, true);
	      }
	      return recentListItem;
	    });
	  }
	  searchByQueryWords(recentListItem, queryWords) {
	    if (recentListItem.user) {
	      return this.searchByUserFields(recentListItem, queryWords);
	    }
	    return this.searchByDialogFields(recentListItem, queryWords);
	  }
	  searchByDialogFields(recentListItem, queryWords) {
	    const searchField = [];
	    if (recentListItem.dialog.name) {
	      const dialogNameWords = SearchUtils.getWordsFromString(recentListItem.dialog.name.toLowerCase());
	      searchField.push(...dialogNameWords);
	    }
	    return this.doesItemMatchQuery(searchField, queryWords);
	  }
	  searchByUserFields(recentListItem, queryWords) {
	    const searchField = [];
	    if (recentListItem.user.firstName) {
	      const userFirstNameWords = SearchUtils.getWordsFromString(recentListItem.user.firstName.toLowerCase());
	      searchField.push(...userFirstNameWords);
	    }
	    if (recentListItem.user.lastName) {
	      const userLastNameWords = SearchUtils.getWordsFromString(recentListItem.user.lastName.toLowerCase());
	      searchField.push(...userLastNameWords);
	    }
	    if (recentListItem.user.workPosition) {
	      const userWorkPositionWords = SearchUtils.getWordsFromString(recentListItem.user.workPosition.toLowerCase());
	      searchField.push(...userWorkPositionWords);
	    }
	    return this.doesItemMatchQuery(searchField, queryWords);
	  }
	  doesItemMatchQuery(fieldsForSearch, queryWords) {
	    let found = 0;
	    queryWords.forEach(queryWord => {
	      let queryWordsMatchCount = 0;
	      fieldsForSearch.forEach(field => {
	        const word = field.slice(0, queryWord.length);
	        if (collator.compare(queryWord, word) === 0) {
	          queryWordsMatchCount++;
	        }
	      });
	      if (queryWordsMatchCount > 0) {
	        found++;
	      }
	    });
	    return found >= queryWords.length;
	  }
	}

	class IndexedDbConnection {
	  static getInstance(currentUserId) {
	    if (!this.instance) {
	      this.instance = new this(currentUserId);
	    }
	    return this.instance;
	  }
	  constructor(currentUserId) {
	    /** @type {Dexie} */
	    this.db = new ui_dexie.Dexie('bx-im-search-results');
	    this.db.version(2).stores({
	      items: 'id, *title, *name, *lastName, *secondName, *position, date',
	      recentItems: '++id, cacheId, date',
	      settings: '&name'
	    }).upgrade(transaction => {
	      const clearItemsPromise = transaction.table('items').clear();
	      const clearRecentItemsPromise = transaction.table('recentItems').clear();
	      return ui_dexie.Dexie.Promise.all([clearItemsPromise, clearRecentItemsPromise]);
	    });
	    this.db.version(3).stores({
	      items: 'id, *title, *name, *lastName, *position, date',
	      recentItems: '++id, cacheId, date',
	      settings: '&name'
	    });
	    this.checkTables(currentUserId);
	    this.onAccessDeniedHandler = this.onAccessDenied.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	    this.collator = new Intl.Collator(undefined, {
	      sensitivity: 'base'
	    });
	  }
	  checkTables(currentUserId) {
	    this.db.open();
	    this.db.on('ready', () => {
	      return this.db.transaction('rw', this.db.settings, this.db.items, this.db.recentItems, () => {
	        return this.db.settings.where('name').equals('userId').first();
	      }).then(settings => {
	        const promises = [];
	        if ((settings == null ? void 0 : settings.value) !== currentUserId) {
	          const clearItemsPromise = this.db.items.clear();
	          const clearRecentItemsPromise = this.db.recentItems.clear();
	          promises.push(clearItemsPromise, clearRecentItemsPromise);
	        }
	        return ui_dexie.Dexie.Promise.all(promises);
	      }).then(() => {
	        return this.db.settings.put({
	          name: 'userId',
	          value: currentUserId
	        });
	      });
	    });
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	  }
	  loadRecentFromCache() {
	    const searchResults = {};
	    return this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
	      return this.deleteExpiredItems().then(() => {
	        return this.db.recentItems.orderBy('id').toArray();
	      });
	    }).then(recentItemsFromCache => {
	      searchResults.recentItems = recentItemsFromCache;
	      const resultItemsPromises = [];
	      searchResults.recentItems.forEach(recentItem => {
	        resultItemsPromises.push(this.db.items.get({
	          id: recentItem.cacheId
	        }));
	      });
	      return ui_dexie.Dexie.Promise.all(resultItemsPromises);
	    }).then(result => {
	      searchResults.items = result.filter(item => !main_core.Type.isUndefined(item)).map(item => item.json);
	      return searchResults;
	    });
	  }
	  save(searchResults) {
	    const preparedItems = searchResults.items ? this.prepareItems(searchResults.items) : [];
	    const preparedRecentItems = searchResults.recentItems ? SearchUtils.prepareRecentItems(searchResults.recentItems) : [];
	    this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
	      if (preparedItems.length > 0) {
	        this.db.items.bulkPut(preparedItems);
	      }
	      if (preparedRecentItems.length > 0) {
	        this.db.recentItems.clear().then(() => {
	          this.db.recentItems.bulkPut(preparedRecentItems);
	        });
	      }
	    });
	  }
	  deleteExpiredItems() {
	    const oneMonthAgo = new Date(Date.now() - 60 * 60 * 1000 * 24 * 7 * 30);
	    return this.db.items.where('date').below(oneMonthAgo).delete().then(() => {
	      return this.db.recentItems.where('date').below(oneMonthAgo).delete();
	    });
	  }
	  onAccessDenied({
	    data: eventData
	  }) {
	    const cacheId = this.convertDialogIdToCacheItemId(eventData.dialogId);
	    return this.db.items.where('id').equals(cacheId).delete().then(() => {
	      return this.db.recentItems.where('cacheId').equals(cacheId).delete();
	    });
	  }
	  convertDialogIdToCacheItemId(dialogId) {
	    if (dialogId.startsWith('chat')) {
	      return `chat|${dialogId.slice(4)}`;
	    }
	    return `user|${dialogId}`;
	  }
	  prepareItems(items) {
	    return items.filter(item => {
	      return item.entityId !== im_v2_const.SearchEntityIdTypes.department && item.entityId !== im_v2_const.SearchEntityIdTypes.network && item.entityType !== 'LINES';
	    }).map(item => {
	      var _item$customData, _item$customData2, _item$customData$imUs, _item$customData$imUs2;
	      const type = SearchUtils.getTypeByEntityId(item.entityId);
	      return {
	        id: `${type}|${item.id}`,
	        name: (_item$customData = item.customData) != null && _item$customData.name ? SearchUtils.getWordsFromString(item.customData.name) : [],
	        lastName: (_item$customData2 = item.customData) != null && _item$customData2.lastName ? SearchUtils.getWordsFromString(item.customData.lastName) : [],
	        position: (_item$customData$imUs = item.customData.imUser) != null && _item$customData$imUs.WORK_POSITION ? SearchUtils.getWordsFromString((_item$customData$imUs2 = item.customData.imUser) == null ? void 0 : _item$customData$imUs2.WORK_POSITION) : [],
	        title: item.title ? SearchUtils.getWordsFromString(item.title) : [],
	        json: item,
	        date: new Date()
	      };
	    });
	  }

	  /**
	   * Moves item to the top of the recent search items list.
	   *
	   * @param itemToMove Array<string, number>
	   */
	  unshiftItem(itemToMove) {
	    const [itemToMoveEntityId, itemToMoveId] = itemToMove;
	    const type = SearchUtils.getTypeByEntityId(itemToMoveEntityId);
	    const itemToMoveCacheId = `${type}|${itemToMoveId}`;
	    this.db.transaction('rw', this.db.recentItems, () => {
	      return this.db.recentItems.toArray();
	    }).then(recentItems => {
	      const itemIndexToUpdate = recentItems.findIndex(recentItem => {
	        return recentItem.cacheId === itemToMoveCacheId;
	      });
	      if (itemIndexToUpdate === 0) {
	        return;
	      }
	      if (itemIndexToUpdate !== -1) {
	        const item = recentItems.splice(itemIndexToUpdate, 1);
	        item[0].date = new Date();
	        recentItems.unshift(item[0]);
	      } else {
	        const item = {
	          cacheId: `${itemToMoveCacheId}|${itemToMoveId}`,
	          date: new Date()
	        };
	        recentItems.unshift(item);
	      }
	      recentItems.forEach(item => delete item.id);
	      this.db.recentItems.clear().then(() => {
	        this.db.recentItems.bulkPut(recentItems);
	      });
	    });
	  }
	  search(words) {
	    return this.db.transaction('r', this.db.items, function* () {
	      // Parallel search for all words - just select resulting primary keys
	      const results = yield this.getQueryResultByWords(words);
	      if (!main_core.Type.isArrayFilled(results)) {
	        return [];
	      }
	      const intersectedResult = this.intersectArrays(...results);
	      const distinctIds = [...new Set(intersectedResult.flat())];

	      // Finally, select entire items from intersection
	      return yield this.db.items.where(':id').anyOf(distinctIds).toArray();
	    }.bind(this)).then(items => {
	      return items.map(item => item.json);
	    });
	  }
	  getQueryResultByWords(words) {
	    return ui_dexie.Dexie.Promise.all(words.map(word => {
	      return this.db.table('items').filter(record => {
	        const fieldsForSearch = [...record.name.flat(), ...record.lastName.flat(), ...record.position.flat(), ...record.title.flat()];
	        return fieldsForSearch.some(field => {
	          const fieldToCompare = field.slice(0, word.length).toLowerCase();
	          return this.collator.compare(fieldToCompare, word) === 0;
	        });
	      }).distinct().primaryKeys();
	    }));
	  }
	  intersectArrays(firstArray, secondArray, ...restArrays) {
	    if (main_core.Type.isUndefined(secondArray)) {
	      return firstArray;
	    }
	    const intersectedArray = firstArray.filter(value => secondArray.includes(value));
	    if (restArrays.length === 0) {
	      return intersectedArray;
	    }
	    return this.intersectArrays(intersectedArray, ...restArrays);
	  }
	}
	IndexedDbConnection.instance = null;

	class IndexedDbSearchService {
	  constructor(config) {
	    this.store = im_v2_application_core.Core.getStore();
	    this.db = IndexedDbConnection.getInstance(im_v2_application_core.Core.getUserId());
	    this.config = config;
	    this.layoutManager = new LayoutManager();
	  }
	  load() {
	    return this.db.loadRecentFromCache().then(responseFromCache => {
	      im_v2_lib_logger.Logger.warn('Im.Search: Recent search loaded from cache', responseFromCache);
	      return responseFromCache;
	    }).then(responseFromCache => {
	      const {
	        items,
	        recentItems
	      } = responseFromCache;
	      const itemMap = SearchUtils.createItemMap(items);
	      return {
	        recentItems,
	        itemMap
	      };
	    });
	  }
	  save(items) {
	    return this.db.save(items);
	  }
	  unshiftItem(item) {
	    return this.db.unshiftItem(item);
	  }
	  search(originalLayoutQuery) {
	    let wrongLayoutSearchPromise = Promise.resolve([]);
	    if (this.layoutManager.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQuery = this.layoutManager.changeLayout(originalLayoutQuery);
	      wrongLayoutSearchPromise = this.getItemsFromCacheByQuery(wrongLayoutQuery);
	    }
	    const correctLayoutSearchPromise = this.getItemsFromCacheByQuery(originalLayoutQuery);
	    return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
	      return new Map([...result[0], ...result[1]]);
	    }).catch(error => {
	      console.error('Unknown exception', error);
	      return new Map();
	    });
	  }
	  getItemsFromCacheByQuery(query) {
	    const queryWords = SearchUtils.getWordsFromString(query);
	    return this.db.search(queryWords).then(cacheItems => {
	      return SearchUtils.createItemMap(cacheItems);
	    });
	  }
	  destroy() {
	    this.db.destroy();
	  }
	}

	var _searchConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	class BaseServerSearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _searchConfig, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig] = searchConfig;
	  }
	  searchRequest(query) {
	    const config = {
	      json: babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig].getSearch()
	    };
	    config.json.searchQuery = {
	      'queryWords': SearchUtils.getWordsFromString(query),
	      'query': query
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.doSearch', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Search request result`, response);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  loadRecentFromServer() {
	    const config = {
	      json: babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig].getRecentRequestConfig()
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.load', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Recent search request result`, response);
	        resolve(response.data.dialog);
	      }).catch(error => reject(error));
	    });
	  }
	  addItemsToRecentSearchResults(recentItem) {
	    const [entityId, id] = recentItem;
	    const recentItems = [{
	      id,
	      entityId
	    }];
	    const config = {
	      json: {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _searchConfig)[_searchConfig].getRecentRequestConfig(),
	        recentItems
	      }
	    };
	    main_core.ajax.runAction('ui.entityselector.saveRecentItems', config);
	  }
	}

	const RestMethodImopenlinesNetworkJoin = 'imopenlines.network.join';
	var _searchConfig$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	class NetworkSearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _searchConfig$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig$1)[_searchConfig$1] = searchConfig;
	  }
	  search(query) {
	    const config = {
	      json: babelHelpers.classPrivateFieldLooseBase(this, _searchConfig$1)[_searchConfig$1].getNetwork()
	    };
	    config.json.searchQuery = {
	      'queryWords': SearchUtils.getWordsFromString(query.trim()),
	      'query': query.trim()
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.doSearch', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Network Search request result`, response);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  loadItem(networkCode) {
	    const query = {
	      [RestMethodImopenlinesNetworkJoin]: {
	        code: networkCode
	      },
	      [im_v2_const.RestMethod.imUserGet]: {
	        id: `$result[${RestMethodImopenlinesNetworkJoin}]`
	      }
	    };
	    return im_v2_lib_rest.callBatch(query).then(result => {
	      const user = result[im_v2_const.RestMethod.imUserGet];
	      return {
	        id: user.id,
	        entityId: im_v2_const.SearchEntityIdTypes.bot,
	        entityType: 'network',
	        title: user.name,
	        customData: {
	          imUser: SearchUtils.convertKeysToUpperCase(user)
	        },
	        avatar: user.avatar
	      };
	    });
	  }
	}

	var _searchConfig$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchConfig");
	class DepartmentSearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _searchConfig$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _searchConfig$2)[_searchConfig$2] = searchConfig;
	  }
	  loadUsers(department) {
	    const parentItem = {
	      id: department.getId(),
	      entityId: department.getEntityId()
	    };
	    const config = {
	      json: {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _searchConfig$2)[_searchConfig$2].getDepartmentUsers(),
	        parentItem
	      }
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.getChildren', config).then(response => {
	        im_v2_lib_logger.Logger.warn('Im.V2.Search: department users response', response);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	}

	var _processResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processResponse");
	var _filterByConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterByConfig");
	var _getItemsFromRecentItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsFromRecentItems");
	class SearchService {
	  constructor(searchConfig) {
	    Object.defineProperty(this, _getItemsFromRecentItems, {
	      value: _getItemsFromRecentItems2
	    });
	    Object.defineProperty(this, _filterByConfig, {
	      value: _filterByConfig2
	    });
	    Object.defineProperty(this, _processResponse, {
	      value: _processResponse2
	    });
	    this.searchConfig = new Config(searchConfig);
	    this.storeUpdater = new StoreUpdater();
	    this.sortingResult = new SortingResult();
	    this.recentStateSearchService = new RecentStateSearchService();
	    this.indexedDbSearchService = new IndexedDbSearchService();
	    this.baseServerSearchService = new BaseServerSearchService(this.searchConfig);
	    this.networkSearchService = new NetworkSearchService(this.searchConfig);
	    this.departmentSearchService = new DepartmentSearchService(this.searchConfig);
	  }
	  loadRecentSearchFromCache() {
	    return this.indexedDbSearchService.load().then(result => {
	      const {
	        recentItems,
	        itemMap
	      } = result;
	      return babelHelpers.classPrivateFieldLooseBase(this, _getItemsFromRecentItems)[_getItemsFromRecentItems](recentItems, itemMap);
	    }).then(items => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items,
	        onlyAdd: true
	      });
	    });
	  }
	  loadRecentUsers() {
	    const recentUsers = this.recentStateSearchService.load();
	    const items = SearchUtils.createItemMap(recentUsers);
	    return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	      items,
	      updateStore: false
	    });
	  }
	  loadRecentSearchFromServer() {
	    return this.baseServerSearchService.loadRecentFromServer().then(responseFromServer => {
	      this.indexedDbSearchService.save(responseFromServer);
	      im_v2_lib_logger.Logger.warn('Im.Search: Recent search loaded from server');
	      const {
	        items,
	        recentItems
	      } = responseFromServer;
	      const itemMap = SearchUtils.createItemMap(items);
	      const preparedRecentItems = SearchUtils.prepareRecentItems(recentItems);
	      return babelHelpers.classPrivateFieldLooseBase(this, _getItemsFromRecentItems)[_getItemsFromRecentItems](preparedRecentItems, itemMap);
	    }).then(items => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items
	      });
	    });
	  }
	  searchLocal(query) {
	    const searchInCachePromise = this.indexedDbSearchService.search(query);
	    const searchInRecentListPromise = this.recentStateSearchService.search(query);
	    return Promise.all([searchInCachePromise, searchInRecentListPromise]).then(result => {
	      const [itemsFromCache, itemsFromRecent] = result;
	      return Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items: itemsFromCache,
	        onlyAdd: false
	      }), babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items: itemsFromRecent,
	        updateStore: false
	      })]);
	    }).then(result => {
	      const [itemsFromCacheProcessed, itemsFromRecentProcessed] = result;
	      // Spread order is important, because we have more data in cache than in recent list
	      // (for example contextSort field)
	      const items = new Map([...itemsFromRecentProcessed, ...itemsFromCacheProcessed]);
	      return this.sortingResult.getSortedItems(items, query);
	    });
	  }
	  searchOnServer(query) {
	    return this.baseServerSearchService.searchRequest(query).then(itemsFromServer => {
	      this.indexedDbSearchService.save({
	        items: itemsFromServer
	      });
	      return SearchUtils.createItemMap(itemsFromServer);
	    }).then(items => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items
	      });
	    }).then(items => {
	      return this.sortingResult.allocateSearchResults(items, query);
	    });
	  }
	  searchOnNetwork(query) {
	    this.searchConfig.enableNetworkSearch();
	    return this.networkSearchService.search(query).then(items => {
	      return SearchUtils.createItemMap(items);
	    });
	  }
	  loadDepartmentUsers(parentItem) {
	    return this.departmentSearchService.loadUsers(parentItem).then(responseFromServer => {
	      this.indexedDbSearchService.save({
	        items: responseFromServer
	      });
	      const items = SearchUtils.createItemMap(responseFromServer);
	      return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items
	      });
	    });
	  }
	  loadNetworkItem(networkCode) {
	    return this.networkSearchService.loadItem(networkCode).then(responseFromServer => {
	      const items = SearchUtils.createItemMap([responseFromServer]);
	      return babelHelpers.classPrivateFieldLooseBase(this, _processResponse)[_processResponse]({
	        items
	      });
	    });
	  }
	  addItemToRecent(selectedItem) {
	    if (selectedItem.isDepartmentType() || selectedItem.isNetworkType()) {
	      return;
	    }
	    const item = [selectedItem.entityId, selectedItem.id];
	    this.indexedDbSearchService.unshiftItem(item);
	    this.baseServerSearchService.addItemsToRecentSearchResults(item);
	  }
	  isNetworkAvailable() {
	    return this.searchConfig.isNetworkAvailable();
	  }
	  disableNetworkSearch() {
	    this.searchConfig.disableNetworkSearch();
	  }
	  destroy() {
	    this.indexedDbSearchService.destroy();
	  }
	}
	function _processResponse2({
	  items,
	  updateStore = true,
	  onlyAdd = false
	}) {
	  const filteredItems = babelHelpers.classPrivateFieldLooseBase(this, _filterByConfig)[_filterByConfig](items);
	  if (!updateStore) {
	    return Promise.resolve(filteredItems);
	  }
	  return this.storeUpdater.update({
	    items: filteredItems,
	    onlyAdd: onlyAdd
	  }).then(() => {
	    return filteredItems;
	  });
	}
	function _filterByConfig2(items) {
	  const filteredItems = [...items].filter(item => {
	    const [, value] = item;
	    return this.searchConfig.isItemAllowed(value);
	  });
	  return new Map(filteredItems);
	}
	function _getItemsFromRecentItems2(recentItems, items) {
	  const filledRecentItems = new Map();
	  recentItems.forEach(recentItem => {
	    const itemFromMap = items.get(recentItem.cacheId);
	    if (itemFromMap && !itemFromMap.isOpeLinesType()) {
	      filledRecentItems.set(itemFromMap.getEntityFullId(), itemFromMap);
	    }
	  });
	  return filledRecentItems;
	}

	class SearchContextMenu extends im_v2_lib_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getSendMessageItem(), this.getCallItem(), this.getHistoryItem(), this.getOpenProfileItem()];
	  }
	}

	// @vue/component
	const CarouselUser = {
	  name: 'CarouselUser',
	  components: {
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    isSelected: {
	      type: Boolean,
	      required: false
	    }
	  },
	  emits: ['clickItem'],
	  data() {
	    return {
	      selected: this.isSelected
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    searchItem() {
	      return this.item;
	    },
	    userId() {
	      return this.searchItem.getId();
	    },
	    user() {
	      return this.$store.getters['users/get'](this.userId, true);
	    },
	    name() {
	      return this.user.firstName ? this.user.firstName : this.user.name;
	    },
	    isExtranet() {
	      return this.user.extranet;
	    },
	    userDialogId() {
	      return this.userId.toString();
	    }
	  },
	  watch: {
	    isSelected(newValue, oldValue) {
	      if (newValue === true && oldValue === false) {
	        this.selected = true;
	      } else if (newValue === false && oldValue === true) {
	        this.selected = false;
	      }
	    }
	  },
	  created() {
	    this.contextMenuManager = new SearchContextMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    onClick(event) {
	      if (this.selectMode) {
	        this.selected = !this.selected;
	      }
	      this.$emit('clickItem', {
	        selectedItem: this.searchItem,
	        selectedStatus: this.selected,
	        nativeEvent: event
	      });
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      const item = {
	        dialogId: this.userDialogId
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.openContextMenu, {
	        item,
	        nativeEvent: event
	      });
	    }
	  },
	  template: `
		<div 
			class="bx-im-carousel-user__container bx-im-carousel-user__scope"
			:class="{'--extranet': isExtranet, '--selected': selectMode && selected}"
			@click="onClick" 
			@click.right.prevent="onRightClick"
		>
			<div v-if="selectMode && selected" class="bx-im-carousel-user__selected-mark"></div>
			<Avatar :dialogId="userDialogId" :size="AvatarSize.XL" />
			<div class="bx-im-carousel-user__title" :title="user.name">
				{{ name }}
			</div>
		</div>
	`
	};

	const recentUsersLimit = 6;

	// @vue/component
	const RecentUsersCarousel = {
	  name: 'RecentUsersCarousel',
	  components: {
	    CarouselUser
	  },
	  props: {
	    items: {
	      type: Object,
	      // Map<string, SearchItem>
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    selectedItems: {
	      type: Array,
	      default: () => []
	    }
	  },
	  emits: ['clickItem'],
	  computed: {
	    users() {
	      const itemsFromMap = [...this.items.values()];
	      return itemsFromMap.slice(0, recentUsersLimit);
	    }
	  },
	  methods: {
	    isSelected(item) {
	      return this.selectedItems.includes(item.getEntityFullId());
	    }
	  },
	  template: `
		<div class="bx-im-recent-users-carousel__container bx-im-recent-users-carousel__scope">
			<div class="bx-im-recent-users-carousel__title-container">
				<span class="bx-im-recent-users-carousel__section-title">
					{{ $Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</span>
			</div>
			<div class="bx-im-recent-users-carousel__users-container">
				<CarouselUser 
					v-for="user in users"
					:key="user.getId()"
					:item="user"
					:selectMode="selectMode"
					:isSelected="isSelected(user)"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</div>
	`
	};

	const SearchResultSection = {
	  name: 'SearchResultSection',
	  components: {
	    ExpandAnimation: im_v2_component_animation.ExpandAnimation
	  },
	  props: {
	    component: {
	      type: Object,
	      required: true
	    },
	    items: {
	      type: Object,
	      // Map<string, SearchItem>
	      required: true
	    },
	    title: {
	      type: String,
	      default: ''
	    },
	    showMoreButton: {
	      type: Boolean,
	      default: true,
	      required: false
	    },
	    minItems: {
	      type: Number,
	      default: 10,
	      required: false
	    },
	    maxItems: {
	      type: Number,
	      default: 50,
	      required: false
	    },
	    canBeFolded: {
	      type: Boolean,
	      default: true,
	      required: false
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    selectedItems: {
	      type: Array,
	      default: () => []
	    }
	  },
	  emits: ['clickItem'],
	  data: function () {
	    return {
	      expanded: false,
	      folded: false
	    };
	  },
	  computed: {
	    showMore() {
	      if (!this.showMoreButton) {
	        return false;
	      }
	      return this.items.size > this.minItems;
	    },
	    showMoreButtonText() {
	      return this.expanded ? this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_LESS') : this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_MORE');
	    },
	    sectionItems() {
	      const itemsFromMap = [...this.items.values()];
	      if (!this.showMoreButton) {
	        return itemsFromMap;
	      }
	      return this.expanded ? itemsFromMap.slice(0, this.maxItems) : itemsFromMap.slice(0, this.minItems);
	    }
	  },
	  methods: {
	    onFoldSection() {
	      if (!this.canBeFolded) {
	        return;
	      }
	      this.folded = !this.folded;
	    },
	    onShowMore() {
	      this.expanded = !this.expanded;
	    },
	    isSelected(item) {
	      return this.selectedItems.includes(item.getEntityFullId());
	    }
	  },
	  template: `
		<div class="bx-im-search-result-section__container bx-im-search-result-section__scope">
			<div 
				v-if="title" 
				class="bx-im-search-result-section__title-container" 
				:class="{'--down': !folded, '--foldable': canBeFolded}"
				@click="onFoldSection"
			>
				<span 
					class="bx-im-search-result-section__title-text"
					:class="{'--icon': canBeFolded}"
				>
					{{title}}
				</span>
			</div>
			<ExpandAnimation>
				<div v-if="!folded" class="bx-im-search-result-section__items-container">
					<component 
						:is="component"
						v-for="item in sectionItems" 
						:key="item.getEntityFullId()" 
						:item="item" 
						:selectMode="selectMode"
						:isSelected="isSelected(item)"
						@clickItem="$emit('clickItem', $event)"
					/>
					<button 
						v-if="showMore" 
						class="bx-im-search-result-section__show-more" 
						@click.prevent="onShowMore"
					>
						{{ showMoreButtonText }}
					</button>
				</div>
			</ExpandAnimation>
		</div>
	`
	};

	// @vue/component
	const SearchResultNetworkItem = {
	  name: 'SearchResultNetworkItem',
	  components: {
	    Loader: im_v2_component_elements.Loader
	  },
	  inject: ['searchService'],
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['clickItem'],
	  data: function () {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    searchItem() {
	      return this.item;
	    },
	    hasAvatar() {
	      return main_core.Type.isStringFilled(this.searchItem.getAvatar());
	    },
	    avatarStyles() {
	      if (!this.hasAvatar) {
	        return {
	          backgroundSize: '37px',
	          backgroundPosition: 'center 8px',
	          backgroundColor: this.searchItem.getAvatarOptions().color
	        };
	      }
	      return {
	        backgroundImage: `url('${this.searchItem.getAvatar()}')`
	      };
	    },
	    title() {
	      return main_core.Text.decode(this.searchItem.getTitle());
	    }
	  },
	  methods: {
	    onClick(event) {
	      this.isLoading = true;
	      const networkCode = this.searchItem.getId();
	      this.searchService.loadNetworkItem(networkCode).then(response => {
	        const searchItem = SearchUtils.getFirstItemFromMap(response);
	        this.$emit('clickItem', {
	          selectedItem: searchItem,
	          nativeEvent: event
	        });
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.isLoading = false;
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-search-result-network-item__container bx-im-search-result-network-item__scope"
			@click="onClick"
		>
			<div class="bx-im-search-result-network-item__avatar-container">
				<div
					:title="searchItem.title"
					class="bx-im-search-result-network-item__avatar"
					:style="avatarStyles"
				></div>
			</div>
			<div class="bx-im-search-result-network-item__content-container">
				<div class="bx-im-search-result-network-item__title-text" :title="title">
					{{title}}
				</div>
				<div class="bx-im-search-result-network-item__item-text" :title="searchItem.getSubtitle()">
					{{ searchItem.getSubtitle() }}
				</div>
				<div v-if="isLoading" class="bx-im-search-result-network-item__loader">
					<Loader />
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const SearchResultItem = {
	  name: 'SearchResultItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  inject: ['searchService'],
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    isSelected: {
	      type: Boolean,
	      required: false
	    }
	  },
	  emits: ['clickItem'],
	  data() {
	    return {
	      selected: this.isSelected
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    searchItem() {
	      return this.item;
	    },
	    dialogId() {
	      return this.searchItem.getDialogId();
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    userItemText() {
	      if (!this.isUser) {
	        return '';
	      }
	      const status = this.$store.getters['users/getLastOnline'](this.dialogId);
	      if (status) {
	        return status;
	      }
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    chatItemText() {
	      if (this.isUser) {
	        return '';
	      }
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_GROUP_V2');
	    },
	    itemText() {
	      return this.isUser ? this.userItemText : this.chatItemText;
	    },
	    selectedStyles() {
	      return {
	        '--selected': this.selectMode && this.selected
	      };
	    }
	  },
	  watch: {
	    isSelected(newValue, oldValue) {
	      if (newValue === true && oldValue === false) {
	        this.selected = true;
	      } else if (newValue === false && oldValue === true) {
	        this.selected = false;
	      }
	    }
	  },
	  methods: {
	    onClick(event) {
	      if (this.selectMode) {
	        this.selected = !this.selected;
	      } else {
	        this.searchService.addItemToRecent(this.searchItem);
	      }
	      this.$emit('clickItem', {
	        selectedItem: this.searchItem,
	        selectedStatus: this.selected,
	        nativeEvent: event
	      });
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      const item = {
	        dialogId: this.dialogId
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.openContextMenu, {
	        item,
	        nativeEvent: event
	      });
	    }
	  },
	  template: `
		<div 
			@click="onClick" 
			@click.right.prevent="onRightClick" 
			class="bx-im-search-result-item__container bx-im-search-result-item__scope"
			:class="selectedStyles"
		>
			<div class="bx-im-search-result-item__avatar-container">
				<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
			</div>
			<div class="bx-im-search-result-item__content-container">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-result-item__item-text" :title="itemText">
					{{ itemText }}
				</div>
			</div>
			<div v-if="selectMode && selected" class="bx-im-search-result-item__selected"></div>
		</div>
	`
	};

	// @vue/component
	const SearchResultDepartmentItem = {
	  name: 'SearchResultDepartmentItem',
	  components: {
	    SearchResultItem,
	    ExpandAnimation: im_v2_component_animation.ExpandAnimation,
	    Loader: im_v2_component_elements.Loader
	  },
	  inject: ['searchService'],
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    isSelected: {
	      type: Boolean,
	      required: false
	    }
	  },
	  emits: ['clickItem'],
	  data: function () {
	    return {
	      selected: this.isSelected,
	      expanded: false,
	      isLoading: false,
	      usersInDepartment: []
	    };
	  },
	  computed: {
	    searchItem() {
	      return this.item;
	    },
	    departmentAvatarStyle() {
	      var _this$searchItem$avat;
	      if ((_this$searchItem$avat = this.searchItem.avatarOptions) != null && _this$searchItem$avat.color) {
	        return {
	          backgroundColor: this.searchItem.avatarOptions.color
	        };
	      }
	      return {};
	    },
	    title() {
	      return main_core.Text.decode(this.searchItem.title);
	    },
	    selectedStyles() {
	      return {
	        '--selected': this.selectMode && this.selected
	      };
	    }
	  },
	  watch: {
	    isSelected(newValue, oldValue) {
	      if (newValue === true && oldValue === false) {
	        this.selected = true;
	      } else if (newValue === false && oldValue === true) {
	        this.selected = false;
	      }
	    }
	  },
	  methods: {
	    onClick(event) {
	      if (!this.expanded) {
	        this.openDepartment(event);
	      } else {
	        this.expanded = false;
	      }
	    },
	    openDepartment(event) {
	      if (this.selectMode) {
	        this.selected = !this.selected;
	        this.$emit('clickItem', {
	          selectedItem: this.searchItem,
	          selectedStatus: this.selected,
	          nativeEvent: event
	        });
	      } else {
	        this.isLoading = true;
	        if (main_core.Type.isArrayFilled(this.usersInDepartment)) {
	          this.isLoading = false;
	          this.expanded = true;
	          return;
	        }
	        this.searchService.loadDepartmentUsers(this.searchItem).then(response => {
	          this.usersInDepartment = [...response.values()].filter(user => user.isUser());
	          this.isLoading = false;
	          this.expanded = true;
	        });
	      }
	    }
	  },
	  template: `
		<div 
			@click="onClick" 
			class="bx-im-search-result-department-item__container bx-im-search-result-department-item__scope"
			:class="selectedStyles"
		>
			<div class="bx-im-search-result-department-item__avatar_container">
				<div 
					:title="searchItem.title" 
					class="bx-im-search-result-department-item__avatar"
					:style="departmentAvatarStyle"
				></div>
			</div>
			<div class="bx-im-search-result-department-item__title_container">
				<div class="bx-im-search-result-department-item__title_text" :title="title">
					{{title}}
				</div>
				<div v-if="!selectMode" class="bx-im-search-result-department-item__expand-button">
					<div v-if="isLoading" class="bx-im-search-result-department-item__loader">
						<Loader />
					</div>
					<div v-else-if="expanded" class="bx-im-search-result-department-item__arrow --down"></div>
					<div v-else class="bx-im-search-result-department-item__arrow"></div>
				</div>
				<div v-if="selectMode && selected" class="bx-im-search-result-department-item__selected"></div>
			</div>
		</div>
		<ExpandAnimation>
			<div v-if="expanded" class="bx-im-search-result-department-item__users">
				<SearchResultItem 
					v-for="user in usersInDepartment" 
					:key="user.getEntityFullId()" 
					:item="user"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</ExpandAnimation>
	`
	};

	// @vue/component
	const SearchResult = {
	  name: 'SearchResult',
	  components: {
	    RecentUsersCarousel,
	    SearchResultSection,
	    SearchResultNetworkItem,
	    SearchResultDepartmentItem,
	    SearchResultItem,
	    Button: im_v2_component_elements.Button,
	    Loader: im_v2_component_elements.Loader
	  },
	  props: {
	    searchQuery: {
	      type: String,
	      default: ''
	    },
	    searchMode: {
	      type: Boolean,
	      required: true
	    },
	    searchConfig: {
	      type: Object,
	      required: true
	    },
	    selectMode: {
	      type: Boolean,
	      default: false
	    },
	    selectedItems: {
	      type: Array,
	      required: false,
	      default: () => []
	    }
	  },
	  data: function () {
	    return {
	      isRecentLoading: false,
	      isLocalLoading: false,
	      isServerLoading: false,
	      isNetworkLoading: false,
	      currentServerQueries: 0,
	      isNetworkButtonClicked: false,
	      result: {
	        recentUsers: new Map(),
	        recent: new Map(),
	        usersAndChats: new Map(),
	        chatUsers: new Map(),
	        departments: new Map(),
	        openLines: new Map(),
	        network: new Map()
	      }
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor,
	    itemComponent: () => SearchResultItem,
	    itemDepartmentComponent: () => SearchResultDepartmentItem,
	    itemNetworkComponent: () => SearchResultNetworkItem,
	    cleanQuery() {
	      return this.searchQuery.trim().toLowerCase();
	    },
	    isEmptyState() {
	      if (this.isServerLoading || this.isLocalLoading || this.isNetworkLoading) {
	        return false;
	      }
	      if (this.isNetworkSectionAvailable && !this.isNetworkButtonClicked && this.isServerSearch) {
	        return false;
	      }
	      return this.result.usersAndChats.size === 0 && this.result.departments.size === 0 && this.result.chatUsers.size === 0 && this.result.openLines.size === 0 && this.result.network.size === 0;
	    },
	    isLoadingState() {
	      return this.isServerLoading || this.isRecentLoading;
	    },
	    isServerSearch() {
	      return this.cleanQuery.length >= this.minTokenSize;
	    },
	    needToShowNetworkSection() {
	      return !this.isNetworkButtonClicked || this.result.network.size > 0;
	    },
	    showSearchResult() {
	      return this.cleanQuery.length > 0;
	    },
	    isNetworkSearchCode() {
	      return !!(this.cleanQuery.length === 32 && /[\da-f]{32}/.test(this.cleanQuery));
	    },
	    isNetworkSectionAvailable() {
	      if (!this.searchService.isNetworkAvailable()) {
	        return false;
	      }
	      return this.isNetworkSearchEnabled || this.isNetworkSearchCode;
	    }
	  },
	  watch: {
	    cleanQuery(newQuery, previousQuery) {
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.startSearch(newQuery);
	    },
	    searchMode(newValue, oldValue) {
	      // search switched on and we have recent items
	      if (newValue === true && oldValue === false && this.result.recent.size > 0) {
	        return;
	      }
	      if (newValue === false && oldValue === true)
	        // search switched off
	        {
	          this.isNetworkButtonClicked = false;
	          this.searchService.disableNetworkSearch();
	        }
	      this.loadRecentSearchFromServer();
	    }
	  },
	  created() {
	    this.initSettings();
	    this.contextMenuManager = new SearchContextMenu();
	    this.searchService = new SearchService(this.searchConfig);
	    ui_vue3.provide('searchService', this.searchService);
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 1500, this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.openContextMenu, this.onOpenContextMenu);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.keyPressed, this.onPressEnterKey);
	    this.loadInitialRecentResult();
	  },
	  beforeUnmount() {
	    this.searchService.destroy();
	    this.contextMenuManager.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.openContextMenu, this.onOpenContextMenu);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.keyPressed, this.onPressEnterKey);
	  },
	  methods: {
	    loadInitialRecentResult() {
	      this.searchService.loadRecentUsers().then(items => {
	        this.result.recentUsers = items;
	      });

	      // we don't need an extra request to get recent items while messenger initialization
	      this.searchService.loadRecentSearchFromCache().then(recentItems => {
	        if (recentItems.size > 0) {
	          this.result.recent = recentItems;
	          return;
	        }
	        this.loadRecentSearchFromServer();
	      });
	    },
	    loadRecentSearchFromServer() {
	      this.isRecentLoading = true;
	      this.searchService.loadRecentSearchFromServer().then(recentItemsFromServer => {
	        this.result.recent = recentItemsFromServer;
	        this.isRecentLoading = false;
	      });
	    },
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.search.search-result');
	      const defaultMinTokenSize = 3;
	      this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
	      this.isNetworkSearchEnabled = settings.get('isNetworkSearchEnabled', true);
	    },
	    startSearch(query) {
	      if (query.length > 0 && query.length < this.minTokenSize) {
	        this.isLocalLoading = true;
	        const queryBeforeRequest = query;
	        this.searchService.searchLocal(query).then(localSearchResult => {
	          if (queryBeforeRequest !== this.cleanQuery) {
	            return;
	          }
	          this.result.usersAndChats = localSearchResult;
	          this.isLocalLoading = false;
	        });
	      } else if (query.length >= this.minTokenSize) {
	        this.isServerLoading = true;
	        const queryBeforeRequest = query;
	        this.searchService.searchLocal(query).then(localSearchResult => {
	          if (queryBeforeRequest !== this.cleanQuery) {
	            this.isServerLoading = false;
	            return;
	          }
	          this.result.usersAndChats = localSearchResult;
	        }).then(() => this.searchOnServerDelayed(query));
	      } else {
	        this.cleanSearchResult();
	      }
	    },
	    cleanSearchResult() {
	      this.result.usersAndChats = new Map();
	      this.result.departments = new Map();
	      this.result.chatUsers = new Map();
	      this.result.network = new Map();
	      this.result.openLines = new Map();
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.isNetworkLoading = this.isNetworkButtonClicked;
	      const queryBeforeRequest = query;
	      this.searchService.searchOnServer(query).then(searchResultFromServer => {
	        if (queryBeforeRequest !== this.cleanQuery) {
	          this.stopLoader();
	          return;
	        }
	        this.result.usersAndChats = this.mergeResults(this.result.usersAndChats, searchResultFromServer.usersAndChats);
	        this.result.departments = searchResultFromServer.departments;
	        this.result.chatUsers = searchResultFromServer.chatUsers;
	        this.result.openLines = searchResultFromServer.openLines;
	        this.result.network = searchResultFromServer.network;
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isNetworkLoading = false;
	      this.isServerLoading = false;
	    },
	    mergeResults(originalItems, newItems) {
	      const mergedMap = new Map(originalItems.entries());
	      newItems.forEach((newItemValue, newItemKey) => {
	        if (!mergedMap.has(newItemKey)) {
	          mergedMap.set(newItemKey, newItemValue);
	        }
	      });
	      return mergedMap;
	    },
	    onOpenContextMenu(event) {
	      const {
	        item,
	        nativeEvent
	      } = event.getData();
	      if (im_v2_lib_utils.Utils.key.isAltOrOption(nativeEvent)) {
	        return;
	      }
	      this.contextMenuManager.openMenu(item, nativeEvent.currentTarget);
	    },
	    onDelete({
	      data: eventData
	    }) {
	      const {
	        dialogId
	      } = eventData;
	      this.result.recent.delete(dialogId);
	      this.result.usersAndChats.delete(dialogId);
	      this.result.chatUsers.delete(dialogId);
	    },
	    onScroll(event) {
	      this.$emit('scroll', event);
	      this.contextMenuManager.destroy();
	    },
	    onClickLoadNetworkResult() {
	      this.isNetworkLoading = true;
	      const originalQuery = this.cleanQuery;
	      this.searchService.searchOnNetwork(originalQuery).then(searchResultFromServer => {
	        this.isNetworkLoading = false;
	        if (originalQuery !== this.cleanQuery) {
	          return;
	        }
	        this.result.network = searchResultFromServer;
	        this.isNetworkButtonClicked = true;
	      });
	    },
	    onClickItem(event) {
	      if (!this.searchMode) {
	        return;
	      }
	      const {
	        selectedItem,
	        nativeEvent
	      } = event;
	      if (this.selectMode) {
	        this.$emit('selectItem', event);
	      } else {
	        im_public.Messenger.openChat(selectedItem.getDialogId());
	      }
	      if (!im_v2_lib_utils.Utils.key.isAltOrOption(nativeEvent)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
	    },
	    onPressEnterKey(event) {
	      if (this.selectMode) {
	        return;
	      }
	      const {
	        keyboardEvent
	      } = event.getData();
	      if (!im_v2_lib_utils.Utils.key.isCombination(keyboardEvent, 'Enter')) {
	        return;
	      }
	      const firstItem = this.getFirstItemFromSearchResults();
	      if (!firstItem) {
	        return;
	      }
	      this.onClickItem({
	        selectedItem: firstItem,
	        nativeEvent: keyboardEvent
	      });
	    },
	    getFirstItemFromSearchResults() {
	      if (!this.showSearchResult && this.result.recent.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.recent);
	      }
	      if (this.result.usersAndChats.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.usersAndChats);
	      }
	      if (this.result.chatUsers.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.chatUsers);
	      }
	      if (this.result.openLines.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.openLines);
	      }
	      return null;
	    }
	  },
	  template: `
		<div class="bx-im-search-result__container bx-im-search-result__scope" @scroll="onScroll">
			<template v-if="!showSearchResult">
				<RecentUsersCarousel 
					:items="result.recentUsers"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
				<SearchResultSection
					:component="itemComponent"
					:items="result.recent"
					:showMoreButton="false"
					:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT')"
					:canBeFolded="false"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
			</template>
			<template v-else>
				<SearchResultSection
					v-if="result.usersAndChats.size > 0"
					:component="itemComponent"
					:items="result.usersAndChats"
					:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_USERS_AND_CHATS')"
					:min-items:="20"
					:max-items="50"
					:selectMode="selectMode"
					:selectedItems="selectedItems"
					@clickItem="onClickItem"
				/>
				<template v-if="!isLoadingState && isServerSearch">
					<SearchResultSection
						v-if="result.chatUsers.size > 0"
						:component="itemComponent"
						:items="result.chatUsers"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_CHAT_USERS')"
						:min-items:="5"
						:max-items="20"
						:selectMode="selectMode"
						:selectedItems="selectedItems"
						@clickItem="onClickItem"
					/>
					<SearchResultSection
						v-if="result.departments.size > 0"
						:component="itemDepartmentComponent"
						:items="result.departments"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_DEPARTMENTS')"
						:min-items:="5"
						:max-items="20"
						:selectMode="selectMode"
						:selectedItems="selectedItems"
						@clickItem="onClickItem"
					/>
					<template v-if="isNetworkSectionAvailable">
						<SearchResultSection
							v-if="needToShowNetworkSection"
							:component="itemNetworkComponent"
							:items="result.network"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK')"
							:canBeFolded="isNetworkButtonClicked"
							:min-items:="5"
							:max-items="20"
							:selectMode="selectMode"
							:selectedItems="selectedItems"
							@clickItem="onClickItem"
						/>
						<div class="bx-im-search-result__network-button-container">
							<Button
								v-if="!isNetworkButtonClicked"
								:text="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK_BUTTON')"
								:color="ButtonColor.Primary"
								:size="ButtonSize.L"
								:isLoading="isNetworkLoading"
								:isRounded="true"
								@click="onClickLoadNetworkResult"
							/>
						</div>
					</template>
				</template>
				<div v-if="isEmptyState" class="bx-im-search-result__empty-state-container">
					<div class="bx-im-search-result__empty-state-icon"></div>
					<div class="bx-im-search-result__empty-state-title">
						{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND') }}
					</div>
					<div class="bx-im-search-result__empty-state-subtitle">
						{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND_DESCRIPTION') }}
					</div>
				</div>
			</template>
			<div v-if="isLoadingState" class="bx-im-search-result__loader-container">
				<Loader />
			</div>
		</div>
	`
	};

	exports.SearchResult = SearchResult;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX,BX,BX.Vue3,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Dexie3,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Animation,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=search-result.bundle.js.map
