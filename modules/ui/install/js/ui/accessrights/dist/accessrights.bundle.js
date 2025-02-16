/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_loader,ui_notification,ui_switcher,main_popup,main_core_events,ui_entitySelector,main_core) {
	'use strict';

	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _onItemSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onItemSelect");
	var _onDeselect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDeselect");
	var _normalizeType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("normalizeType");
	var _decodeId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("decodeId");
	var _encoderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("encoderId");
	class EntitySelectorAdapter {
	  constructor(options) {
	    Object.defineProperty(this, _encoderId, {
	      value: _encoderId2
	    });
	    Object.defineProperty(this, _decodeId, {
	      value: _decodeId2
	    });
	    Object.defineProperty(this, _normalizeType, {
	      value: _normalizeType2
	    });
	    Object.defineProperty(this, _onDeselect, {
	      value: _onDeselect2
	    });
	    Object.defineProperty(this, _onItemSelect, {
	      value: _onItemSelect2
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	  }
	  show(columnId, accessCodes, targetNode) {
	    const preselectedItems = [];
	    for (const code in accessCodes) {
	      if (!Object.hasOwn(accessCodes, code)) {
	        continue;
	      }
	      const data = babelHelpers.classPrivateFieldLooseBase(this, _encoderId)[_encoderId](code);
	      preselectedItems.push([data.entityName, data.id]);
	    }
	    const options = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].options,
	      targetNode,
	      preselectedItems,
	      events: {
	        'Item:onSelect': event => {
	          const item = event.data.item;
	          babelHelpers.classPrivateFieldLooseBase(this, _onItemSelect)[_onItemSelect](item, columnId);
	        },
	        'Item:onDeselect': event => {
	          const item = event.data.item;
	          babelHelpers.classPrivateFieldLooseBase(this, _onDeselect)[_onDeselect](item, columnId);
	        }
	      }
	    };
	    const dialog = new BX.UI.EntitySelector.Dialog(options);
	    dialog.show();
	  }
	}
	function _onItemSelect2(item, columnId) {
	  let id = item.id;
	  const decoder = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].entitiesIdsDecoder;
	  if (main_core.Type.isFunction(decoder)) {
	    id = decoder(item);
	  }
	  let type = item.entityId;
	  const normalizeType = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].normalizeType;
	  if (main_core.Type.isFunction(normalizeType)) {
	    type = normalizeType(item.entityId);
	  }
	  const option = {
	    accessCodes: {
	      [id]: type
	    },
	    columnId,
	    item: {
	      id,
	      entityId: item.id,
	      name: item.title.text,
	      avatar: item.avatar
	    }
	  };
	  main_core_events.EventEmitter.emit('BX.UI.AccessRights:addToAccessCodes', option);
	}
	function _onDeselect2(item, columnId) {
	  const id = babelHelpers.classPrivateFieldLooseBase(this, _decodeId)[_decodeId](item);
	  const type = babelHelpers.classPrivateFieldLooseBase(this, _normalizeType)[_normalizeType](item.entityId);
	  const option = {
	    accessCodes: {
	      [id]: type
	    },
	    columnId
	  };
	  main_core_events.EventEmitter.emit('BX.UI.AccessRights:removeFromAccessCodes', option);
	}
	function _normalizeType2(type) {
	  const normalizeType = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].normalizeType;
	  if (main_core.Type.isFunction(normalizeType)) {
	    return normalizeType(type);
	  }
	  return type;
	}
	function _decodeId2(item) {
	  const decoder = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].entitiesIdsDecoder;
	  if (main_core.Type.isFunction(decoder)) {
	    return decoder(item);
	  }
	  return item.id;
	}
	function _encoderId2(code) {
	  const encoder = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].entitiesIdsEncoder;
	  if (main_core.Type.isFunction(encoder)) {
	    return encoder(code);
	  }
	  return code;
	}

	let _ = t => t,
	  _t,
	  _t2;
	const BX$1 = main_core.Reflection.namespace('BX');
	var _makeChangedHash = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("makeChangedHash");
	var _storeChangedAccessId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("storeChangedAccessId");
	var _filterOnlyChangedAccessRight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterOnlyChangedAccessRight");
	class Grid {
	  constructor(options) {
	    var _options$needToLoadUs;
	    Object.defineProperty(this, _filterOnlyChangedAccessRight, {
	      value: _filterOnlyChangedAccessRight2
	    });
	    Object.defineProperty(this, _storeChangedAccessId, {
	      value: _storeChangedAccessId2
	    });
	    Object.defineProperty(this, _makeChangedHash, {
	      value: _makeChangedHash2
	    });
	    options = options || {};
	    this.options = options;
	    this.renderTo = options.renderTo;
	    this.buttonPanel = BX$1.UI.ButtonPanel || null;
	    this.layout = {
	      container: null
	    };
	    this.component = options.component ? options.component : null;
	    this.actionSave = options.actionSave || Grid.ACTION_SAVE;
	    this.actionDelete = options.actionDelete || Grid.ACTION_DELETE;
	    this.actionLoad = options.actionLoad || Grid.ACTION_LOAD;
	    this.mode = options.mode || Grid.MODE;
	    this.openPopupEvent = options.openPopupEvent ? options.openPopupEvent : null;
	    this.popupContainer = options.popupContainer ? options.popupContainer : null;
	    this.additionalSaveParams = options.additionalSaveParams ? options.additionalSaveParams : null;
	    this.loadParams = options.loadParams ? options.loadParams : null;
	    this.loader = null;
	    this.timer = null;
	    this.needToLoadUserGroups = (_options$needToLoadUs = options.needToLoadUserGroups) != null ? _options$needToLoadUs : true;
	    this.isSaveOnlyChangedRights = options.isSaveOnlyChangedRights || false;
	    this.useEntitySelectorDialogAsPopup = options.useEntitySelectorDialogAsPopup || false;
	    this.entitySelectorDialogOptions = options.entitySelectorDialogOptions || null;
	    this.expandedGroups = [];
	    this.groupElements = [];
	    this.changedAccessIds = new Map();
	    this.initData();
	    if (options.userGroups) {
	      this.userGroups = options.userGroups;
	    }
	    if (options.accessRights) {
	      this.accessRights = options.accessRights;
	    }
	    this.isRequested = false;
	    this.loadData();
	    this.bindEvents();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:updateRole', this.updateRole.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:selectAccessItems', this.updateAccessVariationRight.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOn', this.updateAccessRight.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOff', this.updateAccessRight.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:update', this.adjustButtonPanel.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addUserGroup.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addRoleColumn.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addRoleColumn.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addUserGroup.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.removeRoleColumn.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.adjustButtonPanel.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:toggleGroup', this.toggleGroup.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.SelectorV2:onGetEntityTypes', this.onGetEntityTypes.bind(this));
	  }
	  initData() {
	    this.accessRights = [];
	    this.userGroups = [];
	    this.accessRightsSections = [];
	    this.headSection = null;
	    this.members = [];
	    this.columns = [];
	    this.changedAccessIds = new Map();
	  }
	  fireEventReset() {
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights:reset', this);
	  }
	  fireEventRefresh() {
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights:refresh', this);
	  }
	  getButtonPanel() {
	    return this.buttonPanel;
	  }
	  showNotification(title) {
	    BX$1.UI.Notification.Center.notify({
	      content: title,
	      position: 'top-right',
	      autoHideDelay: 3000
	    });
	  }
	  sendActionRequest() {
	    if (this.isRequested) {
	      return;
	    }
	    this.isRequested = true;
	    main_core_events.EventEmitter.emit(this, 'onBeforeSave', this);
	    this.timer = setTimeout(() => {
	      this.blockGrid();
	    }, 1000);
	    let needReload = false;
	    const dataToSave = [];
	    for (let i = 0; i < this.userGroups.length; i++) {
	      const userGroup = this.userGroups[i];
	      if (main_core.Text.toNumber(userGroup.id) === 0) {
	        needReload = true;
	      }
	      let accessRights = userGroup.accessRights;
	      if (this.isSaveOnlyChangedRights === true) {
	        accessRights = babelHelpers.classPrivateFieldLooseBase(this, _filterOnlyChangedAccessRight)[_filterOnlyChangedAccessRight](accessRights, userGroup);
	      }
	      dataToSave.push({
	        accessCodes: userGroup.accessCodes,
	        id: userGroup.id,
	        title: userGroup.title,
	        type: userGroup.type,
	        accessRights
	      });
	    }
	    BX$1.ajax.runComponentAction(this.component, this.actionSave, {
	      mode: this.mode,
	      data: {
	        userGroups: dataToSave,
	        parameters: this.additionalSaveParams
	      }
	      // analyticsLabel: {
	      // 	viewMode: 'grid',
	      // 	filterState: 'closed'
	      // }
	    }).then(() => {
	      if (needReload) {
	        this.reloadGrid();
	      }
	      this.isRequested = false;
	      this.showNotification(main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_STTINGS_HAVE_BEEN_SAVED'));
	      this.unBlockGrid();
	      this.fireEventRefresh();
	      setTimeout(() => {
	        this.adjustButtonPanel();
	      });
	      clearTimeout(this.timer);
	      const waitContainer = this.buttonPanel.getContainer().querySelector('.ui-btn-wait');
	      main_core.Dom.removeClass(waitContainer, 'ui-btn-wait');
	      this.changedAccessIds = new Map();
	    }, response => {
	      let errorMessage = 'Error message';
	      if (response.errors) {
	        errorMessage = response.errors[0].message;
	      }
	      this.isRequested = false;
	      this.showNotification(errorMessage);
	      this.unBlockGrid();
	      clearTimeout(this.timer);
	      const waitContainer = this.buttonPanel.getContainer().querySelector('.ui-btn-wait');
	      main_core.Dom.removeClass(waitContainer, 'ui-btn-wait');
	    });
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights:preservation', this);
	  }
	  lock() {
	    main_core.Dom.addClass(this.getMainContainer(), '--lock');
	  }
	  unlock() {
	    main_core.Dom.removeClass(this.getMainContainer(), '--lock');
	  }
	  deleteActionRequest(roleId) {
	    if (this.isRequested) {
	      return;
	    }
	    this.isRequested = true;
	    this.timer = setTimeout(() => {
	      this.blockGrid();
	    }, 1000);
	    BX$1.ajax.runComponentAction(this.component, this.actionDelete, {
	      mode: this.mode,
	      data: {
	        roleId: roleId
	      }
	      // analyticsLabel: {
	      // 	viewMode: 'grid',
	      // 	filterState: 'closed'
	      // }
	    }).then(() => {
	      this.isRequested = false;
	      this.showNotification(main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_REMOVE'));
	      this.unBlockGrid();
	      clearTimeout(this.timer);
	    }, response => {
	      let errorMessage = 'Error message';
	      if (response.errors) {
	        errorMessage = response.errors[0].message;
	      }
	      this.isRequested = false;
	      this.showNotification(errorMessage);
	      this.unBlockGrid();
	      clearTimeout(this.timer);
	    });
	  }
	  reloadGrid() {
	    this.initData();
	    BX$1.ajax.runComponentAction(this.component, this.actionLoad, {
	      mode: this.mode,
	      data: {
	        parameters: this.loadParams
	      }
	    }).then(response => {
	      if (response.data['ACCESS_RIGHTS'] && response.data['USER_GROUPS']) {
	        this.accessRights = response.data.ACCESS_RIGHTS;
	        this.userGroups = response.data.USER_GROUPS;
	        this.loadData();
	        this.draw();
	      }
	      this.unBlockGrid();
	    }, err => {
	      console.error(err);
	      this.unBlockGrid;
	    });
	  }
	  blockGrid() {
	    const offsetTop = this.layout.container.getBoundingClientRect().top < 0 ? '0' : this.layout.container.getBoundingClientRect().top;
	    main_core.Dom.addClass(this.layout.container, 'ui-access-rights-block');
	    main_core.Dom.style(this.layout.container, 'height', 'calc(100vh - ' + offsetTop + 'px)');
	    setTimeout(() => {
	      main_core.Dom.style(this.layout.container, 'height', 'calc(100vh - ' + offsetTop + 'px)');
	    });
	    this.getLoader().show();
	  }
	  unBlockGrid() {
	    main_core.Dom.removeClass(this.layout.container, 'ui-access-rights-block');
	    main_core.Dom.style(this.layout.container, 'height', null);
	    this.getLoader().hide();
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        target: this.layout.container
	      });
	    }
	    return this.loader;
	  }
	  removeRoleColumn(param) {
	    this.headSection.removeColumn(param.data);
	    this.accessRightsSections.map(data => {
	      data.removeColumn(param.data);
	    });
	    const targetIndex = this.userGroups.indexOf(param.data.userGroup);
	    this.userGroups.splice(targetIndex, 1);
	    const roleId = param.data.userGroup.id;
	    if (roleId > 0) {
	      this.deleteActionRequest(roleId);
	    }
	  }
	  addRoleColumn(event) {
	    const [param] = event.getData();
	    if (!param) {
	      return;
	    }
	    const sections = this.accessRightsSections;
	    for (let i = 0; i < sections.length; i++) {
	      param.headSection = false;
	      param.newColumn = true;
	      sections[i].addColumn(param);
	      sections[i].scrollToRight(sections[i].getColumnsContainer().scrollWidth - sections[i].getColumnsContainer().offsetWidth, 'stop');
	    }
	    param.headSection = true;
	    param.newColumn = true;
	    this.headSection.addColumn(param);
	    this.actualizeExpandedGroups();
	  }
	  addUserGroup(event) {
	    let [options] = event.getData();
	    options = options || {};
	    this.userGroups.push(options);
	  }
	  updateRole(event) {
	    const item = event.getData();
	    const index = this.userGroups.indexOf(item.userGroup);
	    if (index >= 0) {
	      this.userGroups[index].title = item.text;
	    }
	  }
	  adjustButtonPanel() {
	    const modifiedItems = this.getMainContainer().querySelectorAll('.ui-access-rights-column-item-changer-on');
	    const modifiedRoles = this.getMainContainer().querySelectorAll('.ui-access-rights-column-new');
	    const modifiedUsers = this.getMainContainer().querySelectorAll('.ui-access-rights-members-item-new');
	    const modifiedVariables = this.getMainContainer().querySelectorAll('.ui-tag-selector-container');
	    if (modifiedItems.length > 0 || modifiedRoles.length > 0 || modifiedUsers.length > 0 || modifiedVariables.length > 0) {
	      this.buttonPanel.show();
	    } else {
	      this.buttonPanel.hide();
	    }
	  }
	  updateAccessRight(event) {
	    const data = event.getData();
	    const userGroup = this.userGroups[this.userGroups.indexOf(data.userGroup)];
	    const accessId = data.access.id;
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _storeChangedAccessId)[_storeChangedAccessId](data);
	    }, 0);
	    for (let i = 0; i < userGroup.accessRights.length; i++) {
	      const item = userGroup.accessRights[i];
	      if (item && String(item.id) === String(accessId)) {
	        item.value = String(item.value) === '0' ? '1' : '0';
	        return;
	      }
	    }
	    userGroup.accessRights.push({
	      id: accessId,
	      value: data.switcher.isChecked() ? '1' : '0'
	    });
	  }
	  updateAccessVariationRight(event) {
	    const item = event.getData();
	    const userGroup = this.userGroups[this.userGroups.indexOf(item.userGroup)];
	    const accessId = item.access.id;
	    babelHelpers.classPrivateFieldLooseBase(this, _storeChangedAccessId)[_storeChangedAccessId](item);
	    const deleteIds = [];
	    for (let i = 0; i < userGroup.accessRights.length; i++) {
	      const item = userGroup.accessRights[i];
	      if (item && String(item.id) === String(accessId)) {
	        deleteIds.push(i);
	      }
	    }
	    deleteIds.forEach(i => {
	      delete userGroup.accessRights[i];
	    });
	    const values = item.selectedValues || [];
	    values.forEach(value => {
	      userGroup.accessRights.push({
	        id: accessId,
	        value: value
	      });
	    });
	  }
	  loadData() {
	    this.accessRights.map((data, index) => {
	      data.id = index;
	      this.accessRightsSections.push(this.addSection(data));
	    });
	  }
	  getColumns() {
	    return this.columns;
	  }
	  getSections() {
	    return this.accessRightsSections;
	  }
	  getUserGroups() {
	    this.userGroups.forEach(item => {
	      if (item.accessCodes) {
	        for (const user in item.members) {
	          item.accessCodes[user] = item.members[user].type;
	        }
	      }
	    });
	    return this.userGroups;
	  }
	  getHeadSection() {
	    if (!this.headSection) {
	      this.headSection = new Section({
	        headSection: true,
	        userGroups: this.userGroups,
	        grid: this
	      });
	    }
	    return this.headSection;
	  }
	  addSection(options) {
	    options = options || {};
	    return new Section({
	      id: options.id,
	      hint: options.sectionHint,
	      title: options.sectionTitle,
	      rights: options.rights ? options.rights : [],
	      grid: this
	    });
	  }
	  getSectionNode() {
	    return main_core.Tag.render(_t || (_t = _`<div class='ui-access-rights-section'></div>`));
	  }
	  getMainContainer() {
	    if (!this.layout.container) {
	      this.layout.container = main_core.Tag.render(_t2 || (_t2 = _`<div class='ui-access-rights'></div>`));
	    }
	    return this.layout.container;
	  }
	  draw() {
	    const docFragmentSections = document.createDocumentFragment();
	    main_core.Dom.append(this.getHeadSection().render(), docFragmentSections);
	    this.getSections().map(data => {
	      main_core.Dom.append(data.render(), docFragmentSections);
	    });
	    this.layout.container = null;
	    main_core.Dom.append(docFragmentSections, this.getMainContainer());
	    this.renderTo.innerHTML = '';
	    main_core.Dom.append(this.getMainContainer(), this.renderTo);
	    this.afterRender();
	  }
	  afterRender() {
	    this.getHeadSection().adjustEars();
	    this.getSections().map(data => {
	      data.adjustEars();
	    });
	  }
	  onMemberSelect(params) {
	    const option = Grid.buildOption(params);
	    if (!option) {
	      return;
	    }
	    if (params.state === 'select') {
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights:addToAccessCodes', option);
	    }
	  }
	  onMemberUnselect(params) {
	    const option = Grid.buildOption(params);
	    if (!option) {
	      return;
	    }
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights:removeFromAccessCodes', option);
	  }
	  onGetEntityTypes() {
	    if (!this.needToLoadUserGroups) {
	      return;
	    }
	    const controls = BX$1.Main.selectorManagerV2.controls;
	    const selectorInstance = controls[Object.keys(controls)[0]];
	    selectorInstance.entityTypes.USERGROUPS = {
	      options: {
	        enableSearch: 'Y',
	        searchById: 'Y',
	        addTab: 'Y',
	        returnItemUrl: selectorInstance.getOption('returnItemUrl') === 'N' ? 'N' : 'Y'
	      }
	    };
	  }
	  toggleGroup(event) {
	    const groupId = event.getData().id;
	    var idx = this.expandedGroups.indexOf(groupId);
	    if (idx > -1) {
	      this.expandedGroups.splice(idx, 1);
	    } else {
	      this.expandedGroups.push(groupId);
	    }
	    this.actualizeExpandedGroups();
	  }
	  actualizeExpandedGroups() {
	    for (const groupItem of this.groupElements) {
	      if (this.igGroupsExpanded(groupItem.group)) {
	        groupItem.container.classList.add('--expanded');
	      } else {
	        groupItem.container.classList.remove('--expanded');
	      }
	    }
	  }
	  igGroupsExpanded(group) {
	    return this.expandedGroups.includes(group);
	  }
	  static buildOption(params) {
	    const controls = BX$1.Main.selectorManagerV2.controls;
	    const selectorInstance = controls[Object.keys(controls)[0]].selectorInstance;
	    const dataColumnAttribute = 'bx-data-column-id';
	    const node = selectorInstance.bindOptions.node;
	    if (!node.hasAttribute(dataColumnAttribute) || main_core.Type.isUndefined(params.item)) {
	      return false;
	    }
	    const columnId = node.getAttribute(dataColumnAttribute);
	    const accessItem = params.item.id;
	    const entityType = params.entityType;
	    const accessCodesResult = {};
	    accessCodesResult[accessItem] = entityType;
	    return {
	      accessCodes: accessCodesResult,
	      columnId,
	      item: params.item
	    };
	  }
	}
	function _makeChangedHash2(roleId, accessId) {
	  return `r${roleId}_a${accessId}`;
	}
	function _storeChangedAccessId2(item) {
	  const accessId = item.access.id;
	  const isAccessChanged = item.isModify;
	  const userGroup = this.userGroups[this.userGroups.indexOf(item.userGroup)];
	  const changedCode = babelHelpers.classPrivateFieldLooseBase(this, _makeChangedHash)[_makeChangedHash](userGroup.id, accessId);
	  if (isAccessChanged && !this.changedAccessIds.has(changedCode)) {
	    this.changedAccessIds.set(changedCode, {
	      accessId,
	      roleId: userGroup.id
	    });
	  } else if (!isAccessChanged && this.changedAccessIds.has(changedCode)) {
	    this.changedAccessIds.delete(changedCode);
	  }
	}
	function _filterOnlyChangedAccessRight2(accessRights, userGroup) {
	  const processedChanged = new Map(this.changedAccessIds);
	  const filteredAccessRights = accessRights.filter(access => {
	    if (Number(userGroup.id) === 0) {
	      return true;
	    }
	    const changedCode = babelHelpers.classPrivateFieldLooseBase(this, _makeChangedHash)[_makeChangedHash](userGroup.id, access.id);
	    const found = this.changedAccessIds.has(changedCode);
	    if (found) {
	      processedChanged.delete(changedCode);
	    }
	    return found;
	  });

	  // some rights may be changed but not present in the accessRights array because they values were deleted.
	  // Than have to will add them with null value.
	  for (const [key, data] of processedChanged) {
	    if (data.roleId != userGroup.id) {
	      continue;
	    }
	    filteredAccessRights.push({
	      id: data.accessId,
	      value: null
	    });
	  }
	  return filteredAccessRights;
	}
	Grid.ACTION_SAVE = 'save';
	Grid.ACTION_DELETE = 'delete';
	Grid.ACTION_LOAD = 'load';
	Grid.MODE = 'ajax';
	const namespace = main_core.Reflection.namespace('BX.UI');
	namespace.AccessRights = Grid;

	let _$1 = t => t,
	  _t$1;
	class Base {
	  constructor(options) {
	    this.changerOptions = options.changerOptions || {};
	    const defaultValue = this.changerOptions.replaceNullValueTo || null;
	    this.currentValue = options.currentValue || defaultValue;
	    this.identificator = `col-${Math.random()}`;
	    this.parentContainer = options.container;
	    this.grid = options.grid;
	    this.text = options.text;
	    this.userGroup = options.userGroup;
	    this.access = options.access;
	    this.bindEvents();
	  }
	  bindEvents() {}
	  render() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`<div></div>`));
	  }
	  getId() {
	    return this.identificator;
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$1;
	class Title extends Base {
	  constructor(options) {
	    super(options);
	    this.rightId = options.id;
	    this.group = options.group;
	    this.groupHead = options.groupHead;
	    this.isExpanded = false;
	    this.node = null;
	    this.toggleIndicator = null;
	  }
	  render() {
	    const node = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div 
				class='ui-access-rights-column-item-text ui-access-rights-column-item-title'
				data-id='${0}'
			>
				 ${0}
			</div>
		`), this.getId(), main_core.Text.encode(this.text));
	    if (this.groupHead) {
	      this.toggleIndicator = main_core.Tag.render(_t2$1 || (_t2$1 = _$2`
				<span class="ui-access-rights-column-item-text-toggle-indicator ui-icon-set --chevron-down"></span>
			`));
	      main_core.Dom.prepend(this.toggleIndicator, node);
	    }
	    if (this.group) {
	      main_core.Dom.addClass(node, '--group-children');
	    }
	    main_core.Event.bind(node, 'mouseenter', this.adjustPopupHelper.bind(this));
	    main_core.Event.bind(node, 'mouseleave', () => {
	      if (this.popupHelper) {
	        this.popupHelper.close();
	      }
	    });
	    main_core.Event.bind(node, 'click', this.onGroupToggle.bind(this));
	    this.node = node;
	    return node;
	  }
	  onGroupToggle() {
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:toggleGroup', {
	      id: this.rightId
	    });
	    if (!this.node || !this.groupHead) {
	      return;
	    }
	    if (this.grid.igGroupsExpanded(this.rightId)) {
	      main_core.Dom.removeClass(this.toggleIndicator, '--chevron-down');
	      main_core.Dom.addClass(this.toggleIndicator, '--chevron-up');
	    } else {
	      main_core.Dom.addClass(this.toggleIndicator, '--chevron-down');
	      main_core.Dom.removeClass(this.toggleIndicator, '--chevron-up');
	    }
	  }
	  adjustPopupHelper() {
	    const set = this.parentContainer.cloneNode(true);
	    main_core.Dom.style(set, 'position', 'absolute');
	    main_core.Dom.style(set, 'display', 'inline');
	    main_core.Dom.style(set, 'visibility', 'hidden');
	    main_core.Dom.style(set, 'height', '0');
	    main_core.Dom.append(set, document.body);
	    setTimeout(() => {
	      main_core.Dom.remove(set);
	    });
	    if (set.offsetWidth > this.parentContainer.offsetWidth) {
	      main_core.Dom.style(set, 'visibility', 'visible');
	      this.getPopupHelper().show();
	    }
	  }
	  getPopupHelper() {
	    if (!this.popupHelper) {
	      this.popupHelper = main_popup.PopupWindowManager.create(null, this.parentContainer, {
	        autoHide: true,
	        darkMode: true,
	        content: this.text,
	        maxWidth: this.parentContainer.offsetWidth,
	        offsetTop: -9,
	        offsetLeft: 5,
	        animation: 'fading-slide'
	      });
	    }
	    return this.popupHelper;
	  }
	}
	Title.TYPE = 'title';

	let _$3 = t => t,
	  _t$3;
	class Hint extends Base {
	  constructor(options) {
	    super(options);
	    this.hint = options.hint;
	    this.className = options.className;
	    this.hintNode = null;
	  }
	  render() {
	    if (!this.hintNode && this.hint) {
	      const hintManager = BX.UI.Hint.createInstance({
	        id: 'access-rights-ui-hint-' + this.getId(),
	        popupParameters: {
	          className: 'ui-access-rights-popup-pointer-events ui-hint-popup',
	          autoHide: true,
	          darkMode: true,
	          maxWidth: 280,
	          offsetTop: 0,
	          offsetLeft: 8,
	          angle: true,
	          animation: 'fading-slide'
	        }
	      });
	      this.hintNode = main_core.Tag.render(_t$3 || (_t$3 = _$3`<span class='${0}'></span>`), this.className);
	      this.hintNode.setAttribute(hintManager.attributeName, this.hint);
	      this.hintNode.setAttribute(hintManager.attributeHtmlName, true);
	      this.hintNode.setAttribute(hintManager.attributeInteractivityName, true);
	      hintManager.initNode(this.hintNode);
	    }
	    return this.hintNode;
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20;
	const BX$2 = main_core.Reflection.namespace('BX');
	var _showSelectorV = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSelectorV2");
	var _showEntitySelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showEntitySelector");
	class Member extends Base {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _showEntitySelector, {
	      value: _showEntitySelector2
	    });
	    Object.defineProperty(this, _showSelectorV, {
	      value: _showSelectorV2
	    });
	    this.openPopupEvent = options.openPopupEvent;
	    this.popupContainer = options.popupContainer;
	    this.accessCodes = options.accessCodes || [];
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:addToAccessCodes', this.addToAccessCodes.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:removeFromAccessCodes', this.removeFromAccessCodes.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.resetNewMembers.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetNewMembers.bind(this));
	  }
	  getMember() {
	    if (!this.member) {
	      const members = this.userGroup.members || {};
	      const membersFragment = document.createDocumentFragment();
	      let counter = 0;
	      this.validateVariables();
	      Object.keys(members).reverse().forEach(item => {
	        counter++;
	        if (counter < 7) {
	          const user = members[item];
	          const userNode = main_core.Tag.render(_t$4 || (_t$4 = _$4`
							<div class='ui-access-rights-members-item'></div>
						`));
	          if (user.new) {
	            main_core.Dom.addClass(userNode, 'ui-access-rights-members-item-new');
	          }
	          if (user.avatar) {
	            const userAvatar = main_core.Tag.render(_t2$2 || (_t2$2 = _$4`<a class='ui-access-rights-members-item-avatar' title="${0}"></a>`), main_core.Text.encode(user.name));
	            main_core.Dom.style(userAvatar, 'backgroundImage', 'url(\'' + encodeURI(user.avatar) + '\')');
	            main_core.Dom.style(userAvatar, 'backgroundSize', 'cover');
	            main_core.Dom.append(userAvatar, userNode);
	          } else {
	            let avatarClass = 'ui-icon-common-user';
	            if (user.type === 'groups') {
	              avatarClass = 'ui-icon-common-user-group';
	            } else if (user.type === 'sonetgroups') {
	              avatarClass = 'ui-icon-common-company';
	            } else if (user.type === 'usergroups') {
	              avatarClass = 'ui-icon-common-user-group';
	            }
	            const emptyAvatar = main_core.Tag.render(_t3 || (_t3 = _$4`<a class='ui-icon ui-icon-xs' title="${0}"><i></i></a>`), main_core.Text.encode(user.name));
	            main_core.Dom.addClass(emptyAvatar, avatarClass);
	            main_core.Dom.append(emptyAvatar, userNode);
	          }
	          main_core.Dom.append(userNode, membersFragment);
	        }
	      });
	      main_core.Dom.append(this.getAddUserToRole(), membersFragment);
	      this.member = main_core.Tag.render(_t4 || (_t4 = _$4`<div class='ui-access-rights-members'>${0}</div>`), membersFragment);
	      main_core.Event.bind(this.member, 'click', this.adjustPopupUserControl.bind(this));
	    }
	    return this.member;
	  }
	  render() {
	    return this.getMember();
	  }
	  resetNewMembers() {
	    const newMembers = this.getMember().querySelectorAll('.ui-access-rights-members-item-new');
	    newMembers.forEach(item => {
	      main_core.Dom.removeClass(item, 'ui-access-rights-members-item-new');
	    });
	  }
	  validateVariables() {
	    if (main_core.Type.isUndefined(this.userGroup.accessCodes)) {
	      this.userGroup.accessCodes = [];
	    }
	  }
	  updateMembers() {
	    main_core.Dom.remove(this.member);
	    this.member = null;
	    main_core.Dom.append(this.getMember(), this.parentContainer);
	    this.grid.getButtonPanel().show();
	  }
	  addToAccessCodes(event) {
	    const params = event.getData();
	    if (params.columnId !== this.getId()) {
	      return;
	    }
	    const firstKey = Object.keys(params.accessCodes)[0];
	    const type = params.accessCodes[firstKey].toUpperCase();
	    this.userGroup.accessCodes = Object.keys(this.accessCodes);
	    const item = params.item;
	    if (!main_core.Type.isUndefined(item) && Object.keys(item).length) {
	      this.userGroup.members[firstKey] = {
	        id: item.entityId,
	        name: item.name,
	        avatar: item.avatar,
	        url: '',
	        new: true,
	        type: type.toLowerCase()
	      };
	      this.updateMembers();
	    }
	    this.userGroup.accessCodes = [];
	    for (const key in this.userGroup.members) {
	      this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
	    }
	  }
	  removeFromAccessCodes(event) {
	    const params = event.data;
	    if (params.columnId !== this.identificator) {
	      return;
	    }
	    const firstKey = Object.keys(params.accessCodes)[0];
	    delete this.userGroup.members[firstKey];
	    this.updateMembers();
	    this.userGroup.accessCodes = [];
	    for (const key in this.userGroup.members) {
	      this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
	    }
	  }
	  adjustPopupUserControl() {
	    const users = [];
	    const groups = [];
	    const departments = [];
	    const sonetgroups = [];
	    for (const item in this.userGroup.members) {
	      this.userGroup.members[item].key = item;
	      if (this.userGroup.members[item].type === 'users') {
	        users.push(this.userGroup.members[item]);
	      } else if (this.userGroup.members[item].type === 'groups') {
	        groups.push(this.userGroup.members[item]);
	      } else if (this.userGroup.members[item].type === 'usergroups') {
	        groups.push(this.userGroup.members[item]);
	      } else if (this.userGroup.members[item].type === 'departments') {
	        departments.push(this.userGroup.members[item]);
	      } else if (this.userGroup.members[item].type === 'sonetgroups') {
	        sonetgroups.push(this.userGroup.members[item]);
	      }
	    }
	    const counterUsers = [];
	    for (const key in this.userGroup.members) {
	      counterUsers.push(this.userGroup.members[key]);
	    }
	    if (counterUsers.length === 0) {
	      this.showUserSelectorPopup();
	      return;
	    }
	    this.getUserPopup(users, groups, departments, sonetgroups).show();
	  }
	  getAddUserToRole() {
	    if (!this.addUserToRole) {
	      this.addUserToRole = main_core.Tag.render(_t5 || (_t5 = _$4`
				<span 
					class='ui-access-rights-members-item ui-access-rights-members-item-add'
					bx-data-column-id='${0}'
				>
				</span>
			`), this.getId());
	    }
	    return this.addUserToRole;
	  }
	  getUserPopup(users, groups, departments, sonetgroups) {
	    if (!this.popupUsers) {
	      users = users || [];
	      groups = groups || [];
	      departments = departments || [];
	      sonetgroups = sonetgroups || [];
	      const content = main_core.Tag.render(_t6 || (_t6 = _$4`<div class='ui-access-rights-popup-toggler'></div>`));
	      const contentTitle = main_core.Tag.render(_t7 || (_t7 = _$4`<div class='ui-access-rights-popup-toggler-title'></div>`));
	      const onTitleClick = event => {
	        const node = event.target;
	        activate(node);
	        adjustSlicker(node);
	      };
	      if (groups.length > 0) {
	        const groupTitleItem = main_core.Tag.render(_t8 || (_t8 = _$4`
					<div 
						class='ui-access-rights-popup-toggler-title-item ui-access-rights-popup-toggler-title-item-active'
						data-role='ui-access-rights-popup-toggler-content-groups'
					>
						${0}
					</div>
				`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_USER_GROUPS'));
	        main_core.Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));
	        main_core.Dom.append(groupTitleItem, contentTitle);
	      }
	      if (departments.length > 0) {
	        const groupTitleItem = main_core.Tag.render(_t9 || (_t9 = _$4`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-departments'
					>
						${0}
					</div>
				`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_DEPARTMENTS'));
	        main_core.Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));
	        main_core.Dom.append(groupTitleItem, contentTitle);
	      }
	      if (users.length > 0) {
	        const groupTitleItem = main_core.Tag.render(_t10 || (_t10 = _$4`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-users'
					>
						${0}
					</div>
				`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_STAFF'));
	        main_core.Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));
	        main_core.Dom.append(groupTitleItem, contentTitle);
	      }
	      if (sonetgroups.length > 0) {
	        const groupTitleItem = main_core.Tag.render(_t11 || (_t11 = _$4`
					<div 
						class='ui-access-rights-popup-toggler-title-item'
						data-role='ui-access-rights-popup-toggler-content-sonetgroups'
					>
						${0}
					</div>
				`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_SOCNETGROUP'));
	        main_core.Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));
	        main_core.Dom.append(groupTitleItem, contentTitle);
	      }
	      main_core.Dom.append(main_core.Tag.render(_t12 || (_t12 = _$4`<div class='ui-access-rights-popup-toggler-title-slicker'></div>`)), contentTitle);
	      main_core.Dom.append(contentTitle, content);
	      if (groups.length > 0) {
	        main_core.Dom.append(this.getUserPopupTogglerGroup(groups, 'groups'), content);
	      }
	      if (departments.length > 0) {
	        main_core.Dom.append(this.getUserPopupTogglerGroup(departments, 'departments'), content);
	      }
	      if (users.length > 0) {
	        main_core.Dom.append(this.getUserPopupTogglerGroup(users, 'users'), content);
	      }
	      if (sonetgroups.length > 0) {
	        main_core.Dom.append(this.getUserPopupTogglerGroup(sonetgroups, 'sonetgroups'), content);
	      }
	      const footer = main_core.Tag.render(_t13 || (_t13 = _$4`<div class='ui-access-rights-popup-toggler-footer'></div>`));
	      const footerLink = main_core.Tag.render(_t14 || (_t14 = _$4`
				<div class='ui-access-rights-popup-toggler-footer-link'>
					${0}
				</div>
			`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD'));
	      main_core.Event.bind(footerLink, 'click', event => {
	        this.popupUsers.close();
	        this.showUserSelectorPopup();
	        event.preventDefault();
	      });
	      main_core.Dom.append(footerLink, footer);
	      main_core.Dom.append(footer, content);
	      const adjustSlicker = node => {
	        if (!main_core.Type.isDomNode(node)) {
	          node = content.querySelector('.ui-access-rights-popup-toggler-title-item-active');
	        }
	        const slicker = content.querySelector('.ui-access-rights-popup-toggler-title-slicker');
	        main_core.Dom.style(slicker, 'left', node.offsetLeft + 'px');
	        main_core.Dom.style(slicker, 'width', node.offsetWidth + 'px');
	      };
	      const activate = node => {
	        const titles = content.querySelectorAll('.ui-access-rights-popup-toggler-title-item');
	        const contents = content.querySelectorAll('.ui-access-rights-popup-toggler-content');
	        const target = content.querySelector('.' + node.getAttribute('data-role'));
	        titles.forEach(item => {
	          main_core.Dom.removeClass(item, 'ui-access-rights-popup-toggler-title-item-active');
	        });
	        contents.forEach(item => {
	          main_core.Dom.style(item, 'display', 'none');
	        });
	        main_core.Dom.style(target, 'display', 'block');
	        main_core.Dom.addClass(node, 'ui-access-rights-popup-toggler-title-item-active');
	      };
	      this.popupUsers = main_popup.PopupWindowManager.create(null, this.getAddUserToRole(), {
	        contentPadding: 10,
	        animation: 'fading-slide',
	        content,
	        padding: 0,
	        offsetTop: 5,
	        angle: {
	          position: 'top',
	          offset: 35
	        },
	        autoHide: true,
	        closeEsc: true,
	        events: {
	          onPopupShow: () => {
	            setTimeout(() => {
	              const firstActiveNode = content.querySelector('.ui-access-rights-popup-toggler-title-item');
	              if (!firstActiveNode) {
	                return;
	              }
	              main_core.Dom.addClass(firstActiveNode, 'ui-access-rights-popup-toggler-title-item-active');
	              adjustSlicker(firstActiveNode);
	            });
	          },
	          onPopupClose: () => {
	            this.popupUsers.destroy();
	            this.popupUsers = null;
	          }
	        }
	      });
	    }
	    return this.popupUsers;
	  }
	  getUserPopupTogglerGroup(array, type) {
	    const node = main_core.Tag.render(_t15 || (_t15 = _$4`<div class='ui-access-rights-popup-toggler-content'></div>`));
	    main_core.Dom.addClass(node, 'ui-access-rights-popup-toggler-content-' + type);
	    array.forEach(item => {
	      const toggler = main_core.Tag.render(_t16 || (_t16 = _$4`<div class='ui-access-rights-popup-toggler-content-item'></div>`));
	      if (item.avatar) {
	        const avatar = main_core.Tag.render(_t17 || (_t17 = _$4`
					<a 
						class='ui-access-rights-popup-toggler-content-item-userpic'
						title="${0}"
					></a>
				`), main_core.Text.encode(item.name));
	        main_core.Dom.style(avatar, 'backgroundImage', 'url(\'' + encodeURI(item.avatar) + '\')');
	        main_core.Dom.style(avatar, 'backgroundSize', 'cover');
	        main_core.Dom.append(avatar, toggler);
	      } else {
	        let iconClass = '';
	        if (type === 'users') {
	          iconClass = 'ui-icon-common-user';
	        } else if (type === 'groups') {
	          iconClass = 'ui-icon-common-user-group';
	        } else if (type === 'sonetgroups' || type === 'departments') {
	          iconClass = 'ui-icon-common-company';
	        }
	        const emptyAvatar = main_core.Tag.render(_t18 || (_t18 = _$4`<a class='ui-icon ui-icon-sm' title="${0}"><i></i></a>`), main_core.Text.encode(item.name));
	        main_core.Dom.addClass(emptyAvatar, iconClass);
	        main_core.Dom.style(emptyAvatar, 'margin', '5px 10px');
	        main_core.Dom.append(emptyAvatar, toggler);
	      }
	      main_core.Dom.append(main_core.Tag.render(_t19 || (_t19 = _$4`<div class='ui-access-rights-popup-toggler-content-item-name'>${0}</div>`), main_core.Text.encode(item.name)), toggler);
	      const removeButton = main_core.Tag.render(_t20 || (_t20 = _$4`
				<div class='ui-access-rights-popup-toggler-content-item-remove'>${0}</div>
			`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_REMOVE'));
	      main_core.Event.bind(removeButton, 'click', () => {
	        this.userGroup.accessCodes.splice(this.userGroup.accessCodes.indexOf(item.key), 1);
	        delete this.userGroup.accessCodes[item.key];
	        delete this.userGroup.members[item.key];
	        main_core.Dom.remove(toggler);
	        this.updateMembers();
	        this.adjustPopupUserControl();
	        this.grid.getButtonPanel().show();
	      });
	      main_core.Dom.append(removeButton, toggler);
	      main_core.Dom.append(toggler, node);
	    });
	    return node;
	  }
	  showUserSelectorPopup() {
	    if (this.grid.useEntitySelectorDialogAsPopup) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showEntitySelector)[_showEntitySelector]();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _showSelectorV)[_showSelectorV]();
	    }
	  }
	}
	function _showSelectorV2() {
	  var _BX$Main$selectorMana;
	  const selectorInstance = (_BX$Main$selectorMana = BX$2.Main.selectorManagerV2.controls[this.popupContainer]) == null ? void 0 : _BX$Main$selectorMana.selectorInstance;
	  if (selectorInstance) {
	    selectorInstance.itemsSelected = {};
	  }
	  BX$2.onCustomEvent(this.openPopupEvent, [{
	    id: this.popupContainer,
	    bindNode: this.getAddUserToRole()
	  }]);
	  BX$2.onCustomEvent('BX.Main.SelectorV2:reInitDialog', [{
	    selectorId: this.popupContainer,
	    selectedItems: this.userGroup.accessCodes
	  }]);
	}
	function _showEntitySelector2() {
	  if (!this.entitySelectorAdapter) {
	    this.entitySelectorAdapter = new EntitySelectorAdapter(this.grid.entitySelectorDialogOptions);
	  }
	  this.entitySelectorAdapter.show(this.getId(), this.userGroup.accessCodes, this.addUserToRole);
	}
	Member.TYPE = 'members';

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1;
	class Role extends Base {
	  constructor(options) {
	    super(options);
	    this.column = options.column;
	  }
	  bindEvents() {
	    main_core.Event.bind(window, 'click', event => {
	      if (event.target === this.getRole() || event.target.closest('.ui-access-rights-role')) {
	        return;
	      }
	      this.updateRole();
	      this.offRoleEditMode();
	    });
	    main_core_events.EventEmitter.subscribe(this.grid, 'onBeforeSave', () => {
	      this.updateRole();
	      this.offRoleEditMode();
	    });
	  }
	  getRole() {
	    if (this.role) {
	      return this.role;
	    }
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.updateRole.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.offRoleEditMode.bind(this));
	    this.roleInput = main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<input
					type='text'
					class='ui-access-rights-role-input'
					value='${0}'
					placeholder='${0}'
				/>
			`), main_core.Text.encode(this.text), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'));
	    main_core.Event.bind(this.roleInput, 'keydown', event => {
	      if (event.keyCode === 13) {
	        this.updateRole();
	        this.offRoleEditMode();
	      }
	    });
	    main_core.Event.bind(this.roleInput, 'input', () => {
	      this.grid.getButtonPanel().show();
	    });
	    this.roleValue = main_core.Tag.render(_t2$3 || (_t2$3 = _$5`<div class='ui-access-rights-role-value'>${0}</div>`), main_core.Text.encode(this.text));
	    const editControl = main_core.Tag.render(_t3$1 || (_t3$1 = _$5`<div class='ui-access-rights-role-edit'></div>`));
	    main_core.Event.bind(editControl, 'click', this.onRoleEditMode.bind(this));
	    const removeControl = main_core.Tag.render(_t4$1 || (_t4$1 = _$5`<div class='ui-access-rights-role-remove'></div>`));
	    main_core.Event.bind(removeControl, 'click', this.showPopupConfirm.bind(this));
	    const roleControlWrapper = main_core.Tag.render(_t5$1 || (_t5$1 = _$5`
				<div class='ui-access-rights-role-controls'>
					${0}
					${0}
				</div>
			`), editControl, removeControl);
	    this.role = main_core.Tag.render(_t6$1 || (_t6$1 = _$5`
				<div class='ui-access-rights-role'>
					${0}
					${0}
					${0}
				</div>
			`), this.roleInput, this.roleValue, roleControlWrapper);
	    return this.role;
	  }
	  render() {
	    return this.getRole();
	  }
	  onRoleEditMode() {
	    main_core.Dom.addClass(this.getRole(), 'ui-access-rights-role-edit-mode');
	    this.roleInput.focus();
	  }
	  showPopupConfirm() {
	    if (!this.popupConfirm) {
	      /**@ToDO check role*/
	      this.popupConfirm = main_popup.PopupWindowManager.create(null, this.getRole(), {
	        width: 250,
	        overlay: true,
	        contentPadding: 10,
	        content: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE_THIS_ROLE'),
	        animation: 'fading-slide'
	      });
	      this.popupConfirm.setButtons([new BX.UI.Button({
	        text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE'),
	        className: 'ui-btn ui-btn-sm ui-btn-primary',
	        events: {
	          click: () => {
	            this.popupConfirm.close();
	            main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:removeRole', this);
	          }
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_CANCEL'),
	        className: 'ui-btn ui-btn-sm ui-btn-link',
	        events: {
	          click: () => {
	            this.popupConfirm.close();
	          }
	        }
	      })]);
	    }
	    this.popupConfirm.show();
	  }
	  updateRole() {
	    if (this.roleValue.innerHTML === this.roleInput.value || this.roleInput.value === '') {
	      return;
	    }
	    this.text = this.roleInput.value;
	    this.userGroup = this.column.getUserGroup();
	    this.roleValue.innerText = this.roleInput.value;
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:updateRole', this);
	  }
	  offRoleEditMode() {
	    main_core.Dom.removeClass(this.getRole(), 'ui-access-rights-role-edit-mode');
	  }
	}
	Role.TYPE = 'role';

	let _$6 = t => t,
	  _t$6;
	class Changer extends Base {
	  constructor(options) {
	    super(options);
	    this.isModify = false;
	  }
	  getChanger() {
	    if (!this.changer) {
	      this.changer = main_core.Tag.render(_t$6 || (_t$6 = _$6`<a class='ui-access-rights-column-item-changer'></a>`));
	    }
	    return this.changer;
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.offChanger.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refreshStatus.bind(this));
	  }
	  refreshStatus() {
	    this.isModify = false;
	    main_core.Dom.removeClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	  }
	  offChanger() {
	    if (this.isModify) {
	      setTimeout(() => {
	        this.refreshStatus();
	      });
	    }
	  }
	  adjustChanger() {
	    this.isModify = !this.isModify;
	    this.toggleChangerHtmlClass();
	  }
	  toggleChangerHtmlClass() {
	    main_core.Dom.toggleClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	  }
	  addChangerHtmlClass() {
	    main_core.Dom.addClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	  }
	  removeChangerHtmlClass() {
	    main_core.Dom.removeClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	  }
	}

	class Toggler extends Changer {
	  constructor(options) {
	    super(options);
	    this.switcher = new BX.UI.Switcher({
	      size: 'small',
	      checked: this.currentValue === '1',
	      handlers: {
	        checked: () => {
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOn', this);
	        },
	        unchecked: () => {
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOff', this);
	        },
	        toggled: () => {
	          this.adjustChanger();
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	        }
	      }
	    });
	  }
	  offChanger() {
	    if (this.isModify) {
	      this.switcher.check(!this.switcher.isChecked());
	    }
	    super.offChanger();
	  }
	  render() {
	    main_core.Dom.append(this.switcher.getNode(), this.getChanger());
	    return this.getChanger();
	  }
	}
	Toggler.TYPE = 'toggler';

	let _$7 = t => t,
	  _t$7,
	  _t2$4,
	  _t3$2;
	class Controller extends Base {
	  render() {
	    if (!this.controller) {
	      this.controllerLink = main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class='ui-access-rights-column-item-controller-link'>
					${0}
				</div>
			`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_CREATE_ROLE'));
	      this.controllerMenu = main_core.Tag.render(_t2$4 || (_t2$4 = _$7`
				<div class='ui-access-rights-column-item-controller-link'>
					${0}
				</div>
			`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_COPY_ROLE'));
	      main_core.Event.bind(this.controllerMenu, 'click', () => {
	        if (this.popupMenu) {
	          this.popupMenu.close();
	        } else if (this.grid.getUserGroups().length > 0) {
	          this.getPopupMenu(this.grid.getUserGroups()).show();
	        }
	      });
	      this.toggleControllerMenu();
	      this.controller = main_core.Tag.render(_t3$2 || (_t3$2 = _$7`
				<div class='ui-access-rights-column-item-controller'>
					${0}
					${0}
				</div>
			`), this.controllerLink, this.controllerMenu);
	      main_core.Event.bind(this.controllerLink, 'click', () => {
	        main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:addRole', [{
	          id: '0',
	          title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
	          accessRights: [],
	          members: [],
	          accessCodes: [],
	          type: Role.TYPE
	        }]);
	        main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	        this.toggleControllerMenu();
	        this.grid.lock();
	      });
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.toggleControllerMenu.bind(this));
	    }
	    return this.controller;
	  }
	  getPopupMenu(options) {
	    if (!options) {
	      return;
	    }
	    const menuItems = [];
	    options.map(data => {
	      menuItems.push({
	        text: main_core.Text.encode(data.title),
	        onclick: () => {
	          const accessRightsCopy = Object.assign([], data.accessRights);
	          const accessCodesCopy = Object.assign([], data.accessCodes);
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:copyRole', [{
	            id: '0',
	            title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
	            accessRights: accessRightsCopy,
	            accessCodes: accessCodesCopy,
	            type: Role.TYPE,
	            members: data.members
	          }]);
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	          this.popupMenu.destroy();
	        }
	      });
	    });
	    return this.popupMenu = new main_popup.Menu('ui_accessrights_copy_role_list', this.controllerMenu, menuItems, {
	      events: {
	        onPopupClose: () => {
	          this.popupMenu.destroy();
	          this.popupMenu = null;
	        }
	      }
	    });
	  }
	  toggleControllerMenu() {
	    if (this.grid.getUserGroups().length === 0) {
	      main_core.Dom.addClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
	    } else {
	      main_core.Dom.removeClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
	    }
	  }
	}

	let _$8 = t => t,
	  _t$8;
	class VariableSelector extends Changer {
	  constructor(options) {
	    var _this$currentValue;
	    super(options);
	    this.variables = options.variables || [];
	    this.selectedValues = [(_this$currentValue = this.currentValue) != null ? _this$currentValue : '0'];
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	  }
	  render() {
	    var _this$getSelected$tit, _this$getSelected;
	    const title = (_this$getSelected$tit = (_this$getSelected = this.getSelected()) == null ? void 0 : _this$getSelected.title) != null ? _this$getSelected$tit : main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
	    const variablesValue = main_core.Tag.render(_t$8 || (_t$8 = _$8`
				<div class='ui-access-rights-column-item-text-link'>
					${0}
				</div>
			`), main_core.Text.encode(title));
	    main_core.Event.bind(variablesValue, 'click', this.showVariablesPopup.bind(this));
	    main_core.Dom.append(variablesValue, this.getChanger());
	    return this.getChanger();
	  }
	  refresh() {
	    if (this.isModify) {
	      this.currentValue = this.selectedValues[0];
	      this.reset();
	    }
	  }
	  reset() {
	    if (this.isModify) {
	      this.selectedValues = [this.currentValue];
	      this.getChanger().innerHTML = '';
	      this.adjustChanger();
	      this.render();
	    }
	  }
	  getSelected() {
	    const selected = this.variables.filter(variable => this.selectedValues.map(String).includes(String(variable.id)));
	    return selected[0];
	  }
	  showVariablesPopup(event) {
	    const menuItems = [];
	    this.variables.map(data => {
	      menuItems.push({
	        id: data.id,
	        text: data.title,
	        onclick: this.select.bind(this)
	      });
	    });
	    main_popup.PopupMenu.show('ui-access-rights-column-item-popup-variables', event.target, menuItems, {
	      autoHide: true,
	      events: {
	        onPopupClose: () => {
	          main_popup.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
	        }
	      }
	    });
	  }
	  select(event, item) {
	    var _item$getMenuWindow;
	    this.selectedValues = [item.options.id];
	    (_item$getMenuWindow = item.getMenuWindow()) == null ? void 0 : _item$getMenuWindow.close();
	    this.getChanger().innerHTML = '';
	    this.render();
	    this.adjustChanger();
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	    main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	  }
	  adjustChanger() {
	    const defaultValue = this.changerOptions.replaceNullValueTo || null;
	    const selectedValue = this.selectedValues[0] || defaultValue;
	    if (selectedValue === this.currentValue) {
	      this.isModify = false;
	      this.removeChangerHtmlClass();
	    } else {
	      this.isModify = true;
	      this.addChangerHtmlClass();
	    }
	  }
	}
	VariableSelector.TYPE = 'variables';

	let _$9 = t => t,
	  _t$9;
	class UserGroupTitle extends Title {
	  render() {
	    return main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div 
				class='ui-access-rights-column-item-text'
				data-id='${0}'
			>
				${0}
			</div>
		`), this.getId(), main_core.Text.encode(this.text));
	  }
	}
	UserGroupTitle.TYPE = 'userGroupTitle';

	let _$a = t => t,
	  _t$a,
	  _t2$5,
	  _t3$3;
	class Footer extends ui_entitySelector.DefaultFooter {
	  constructor(dialog, options) {
	    super(dialog, options);
	    this.selectAllButton = main_core.Tag.render(_t$a || (_t$a = _$a`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${0}</div>`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_SELECT_LABEL'));
	    main_core.Event.bind(this.selectAllButton, 'click', this.selectAll.bind(this));
	    this.deselectAllButton = main_core.Tag.render(_t2$5 || (_t2$5 = _$a`<div class="ui-selector-footer-link ui-selector-search-footer-label--hide">${0}</div>`), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_DESELECT_LABEL'));
	    main_core.Event.bind(this.deselectAllButton, 'click', this.deselectAll.bind(this));
	    this.getDialog().subscribe('Item:onSelect', this.onItemStatusChange.bind(this));
	    this.getDialog().subscribe('Item:onDeselect', this.onItemStatusChange.bind(this));
	  }
	  getContent() {
	    this.toggleSelectButtons();
	    return main_core.Tag.render(_t3$3 || (_t3$3 = _$a`
			<div class="ui-selector-search-footer-box">
				${0}
				${0}
			</div>
		`), this.selectAllButton, this.deselectAllButton);
	  }
	  toggleSelectButtons() {
	    if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length) {
	      if (main_core.Dom.hasClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide')) {
	        main_core.Dom.addClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	        main_core.Dom.removeClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	      }
	    } else if (main_core.Dom.hasClass(this.selectAllButton, 'ui-selector-search-footer-label--hide')) {
	      main_core.Dom.addClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	      main_core.Dom.removeClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	    }
	  }
	  selectAll() {
	    if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length) {
	      return;
	    }
	    this.getDialog().getItems().forEach(item => {
	      item.select();
	    });
	  }
	  deselectAll() {
	    this.getDialog().getSelectedItems().forEach(item => {
	      item.deselect();
	    });
	  }
	  onItemStatusChange() {
	    this.toggleSelectButtons();
	  }
	}

	let _$b = t => t,
	  _t$b;
	var _obSelectItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("obSelectItem");
	var _onDeselectItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDeselectItem");
	var _afterSetupItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("afterSetupItems");
	var _getDialogFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialogFooter");
	var _useSelectedActionLogic = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useSelectedActionLogic");
	var _isArraysEqual = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isArraysEqual");
	class MultiSelector extends Changer {
	  constructor(options) {
	    var _options$enableSearch, _options$showAvatars, _options$compactView;
	    super(options);
	    Object.defineProperty(this, _isArraysEqual, {
	      value: _isArraysEqual2
	    });
	    Object.defineProperty(this, _useSelectedActionLogic, {
	      value: _useSelectedActionLogic2
	    });
	    Object.defineProperty(this, _getDialogFooter, {
	      value: _getDialogFooter2
	    });
	    Object.defineProperty(this, _afterSetupItems, {
	      value: _afterSetupItems2
	    });
	    Object.defineProperty(this, _onDeselectItem, {
	      value: _onDeselectItem2
	    });
	    Object.defineProperty(this, _obSelectItem, {
	      value: _obSelectItem2
	    });
	    this.variables = options.variables || [];
	    this.enableSearch = (_options$enableSearch = options.enableSearch) != null ? _options$enableSearch : false;
	    this.placeholder = options.placeholder || '';
	    this.hintTitle = options.hintTitle || '';
	    this.allSelectedCode = String(options.allSelectedCode || -1);
	    this.showAvatars = (_options$showAvatars = options.showAvatars) != null ? _options$showAvatars : true;
	    this.compactView = (_options$compactView = options.compactView) != null ? _options$compactView : false;
	    this.currentValue = main_core.Type.isArray(options.currentValue) ? options.currentValue.map(item => String(item)) : [];
	    this.selectedValues = this.currentValue.filter(val => Boolean(val));
	    this.variables = this.variables.map(item => {
	      item.entityId = item.entityId || 'editor-right-item';
	      item.tabs = 'recents';
	      if (item.selectedAction) {
	        item.customData = {
	          ...item.customData,
	          selectedAction: item.selectedAction
	        };
	      }
	      return item;
	    });
	    this.selector = this.createSelector();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	  }
	  createSelector() {
	    return new ui_entitySelector.Dialog({
	      height: 300,
	      id: this.getId(),
	      context: 'editor-right-items',
	      enableSearch: this.enableSearch,
	      multiple: true,
	      dropdownMode: true,
	      compactView: this.compactView,
	      showAvatars: this.showAvatars,
	      selectedItems: this.getSelected(),
	      searchOptions: {
	        allowCreateItem: false
	      },
	      events: {
	        'Item:onSelect': babelHelpers.classPrivateFieldLooseBase(this, _obSelectItem)[_obSelectItem].bind(this),
	        'Item:onDeselect': babelHelpers.classPrivateFieldLooseBase(this, _onDeselectItem)[_onDeselectItem].bind(this)
	      },
	      entities: [{
	        id: 'editor-right-item'
	      }],
	      items: this.variables,
	      footer: babelHelpers.classPrivateFieldLooseBase(this, _getDialogFooter)[_getDialogFooter]()
	    });
	  }
	  render() {
	    let title = '';
	    if (this.includesSelected(this.allSelectedCode)) {
	      title = main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_ACCEPTED');
	    } else {
	      var _this$getSelected;
	      const titles = [];
	      (_this$getSelected = this.getSelected()) == null ? void 0 : _this$getSelected.forEach(item => {
	        titles.push(item.title);
	      });
	      if (titles.length > 0) {
	        const firstItem = titles[0];
	        title = titles.length - 1 > 0 ? main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_HAS_SELECTED_ITEMS', {
	          '#FIRST_ITEM_NAME#': firstItem.length > 10 ? firstItem.slice(0, 10) + '...' : firstItem,
	          '#COUNT_REST_ITEMS#': titles.length - 1
	        }) : firstItem;
	      } else {
	        title = main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
	      }
	    }
	    let hint = '';
	    if (this.selector.getSelectedItems().length > 0) {
	      const hintTitle = main_core.Type.isStringFilled(this.hintTitle) ? this.hintTitle : main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_SELECTED_ITEMS_TITLE_MSGVER_1');
	      hint += '<p>' + hintTitle + '</p>';
	      hint += '<ul>';
	      this.selector.getSelectedItems().forEach(item => hint += '<li>' + main_core.Text.encode(item.getTitle()));
	      hint += '</ul>';
	    }
	    const variablesValue = main_core.Tag.render(_t$b || (_t$b = _$b`
				<div class='ui-access-rights-column-item-text-link' data-hint-html data-hint-no-icon data-hint="${0}">
					${0}
				</div>
			`), main_core.Text.encode(hint), main_core.Text.encode(title));
	    main_core.Event.bind(variablesValue, 'click', this.showSelector.bind(this));
	    main_core.Dom.append(variablesValue, this.getChanger());
	    BX.UI.Hint.init(this.getChanger());
	    this.selector.setTargetNode(this.getChanger());
	    return this.getChanger();
	  }
	  refresh() {
	    if (this.isModify) {
	      this.currentValue = [...this.selectedValues];
	      this.reset();
	    }
	  }
	  reset() {
	    if (this.isModify) {
	      this.selectedValues = [...this.currentValue];
	      this.selector = this.createSelector();
	      this.getChanger().innerHTML = '';
	      this.adjustChanger();
	      this.render();
	    }
	  }
	  getSelected() {
	    if (this.includesSelected(this.allSelectedCode)) {
	      return this.variables;
	    }
	    return this.variables.filter(variable => this.includesSelected(variable.id));
	  }
	  includesSelected(itemId) {
	    return this.selectedValues.some(id => String(id) === String(itemId));
	  }
	  showSelector(event) {
	    this.selector.show();
	  }
	}
	function _obSelectItem2(event) {
	  const addedItem = event.getData().item;
	  const addedId = String(addedItem.id);
	  if (this.changerOptions.useSelectedActions) {
	    babelHelpers.classPrivateFieldLooseBase(this, _useSelectedActionLogic)[_useSelectedActionLogic](addedItem);
	  }
	  if (!this.selectedValues.includes(addedId)) {
	    this.selectedValues.push(addedId);
	  }
	  if (this.selectedValues.length === this.variables.length) {
	    this.selectedValues = [this.allSelectedCode];
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _afterSetupItems)[_afterSetupItems]();
	}
	function _onDeselectItem2(event) {
	  const removedItem = event.getData().item;
	  const removedId = String(removedItem.id);
	  if (this.selectedValues.includes(this.allSelectedCode)) {
	    const allWithoutRemoved = this.variables.map(variable => String(variable.id)).filter(id => id !== removedId);
	    this.selectedValues = allWithoutRemoved;
	  } else {
	    this.selectedValues = this.selectedValues.filter(id => id !== removedId);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _afterSetupItems)[_afterSetupItems]();
	}
	function _afterSetupItems2() {
	  this.isModify = !babelHelpers.classPrivateFieldLooseBase(this, _isArraysEqual)[_isArraysEqual](this.selectedValues, this.currentValue);
	  this.getChanger().innerHTML = '';
	  if (this.isModify) {
	    this.addChangerHtmlClass();
	  } else {
	    this.removeChangerHtmlClass();
	  }
	  this.render();
	  main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	  main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	}
	function _getDialogFooter2() {
	  if (this.changerOptions.disableSelectAll) {
	    return null;
	  }
	  return Footer;
	}
	function _useSelectedActionLogic2(addedItem) {
	  const selectedAction = addedItem.customData.get('selectedAction', null);
	  if (selectedAction === 'clear-other') {
	    const selected = this.selector.getSelectedItems();
	    for (const item of selected) {
	      if (addedItem.id === item.id) {
	        continue;
	      }
	      item.deselect();
	    }
	  } else {
	    const selected = this.selector.getSelectedItems();
	    for (const item of selected) {
	      if (addedItem.id === item.id) {
	        continue;
	      }
	      const currSelectedAction = item.customData.get('selectedAction', null);
	      if (currSelectedAction) {
	        item.deselect();
	      }
	    }
	  }
	}
	function _isArraysEqual2(a, b) {
	  if (a === b) {
	    return true;
	  }
	  if (a === null || b === null) {
	    return false;
	  }
	  if (a.length !== b.length) {
	    return false;
	  }
	  const aClone = [...a];
	  const bClone = [...b];
	  aClone.sort();
	  bClone.sort();
	  for (let i = 0; i < a.length; i++) {
	    if (aClone[i] !== bClone[i]) {
	      return false;
	    }
	  }
	  return true;
	}
	MultiSelector.TYPE = 'multivariables';

	let _$c = t => t,
	  _t$c;
	class ColumnItem {
	  constructor(options) {
	    this.options = options;
	    this.type = options.type ? options.type : null;
	    this.hint = options.hint ? options.hint : null;
	    this.controller = options.controller ? options.controller : null;
	    this.column = options.column;
	  }
	  render() {
	    let item = null;
	    const container = main_core.Tag.render(_t$c || (_t$c = _$c`<div class='ui-access-rights-column-item'></div>`));
	    if (this.options.group) {
	      main_core.Dom.addClass(container, 'ui-access-rights-group-children');
	      container.dataset.group = this.options.group;
	      this.options.grid.groupElements.push({
	        container,
	        group: this.options.group,
	        isHidden: true
	      });
	    }
	    this.options.container = container;
	    if (this.type === Role.TYPE) {
	      item = new Role(this.options);
	      if (this.column.newColumn) {
	        setTimeout(() => {
	          item.onRoleEditMode();
	          item.roleInput.value = '';
	        });
	      }
	    } else if (this.type === Member.TYPE) {
	      item = new Member(this.options);
	    } else if (this.type === Title.TYPE) {
	      item = new Title(this.options);
	    } else if (this.type === VariableSelector.TYPE) {
	      item = new VariableSelector(this.options);
	    } else if (this.type === MultiSelector.TYPE) {
	      item = new MultiSelector(this.options);
	    } else if (this.type === Toggler.TYPE) {
	      item = new Toggler(this.options);
	    }
	    if (item) {
	      main_core.Dom.append(item.render(), container);
	    }
	    if (this.hint) {
	      const hintOptions = {
	        className: 'ui-access-rights-column-item-notify',
	        ...this.options
	      };
	      main_core.Dom.append(new Hint(hintOptions).render(), container);
	    }
	    if (this.type === UserGroupTitle.TYPE) {
	      main_core.Dom.append(new UserGroupTitle(this.options).render(), container);
	    }
	    if (this.controller) {
	      main_core.Dom.append(new Controller(this.options).render(), container);
	    }
	    return container;
	  }
	}
	const namespace$1 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$1.ColumnItem = ColumnItem;

	let _$d = t => t,
	  _t$d;
	class Column {
	  constructor(options) {
	    this.layout = {
	      container: null
	    };
	    this.grid = options.grid ? options.grid : null;
	    this.items = options.items ? options.items : [];
	    this.userGroup = options.userGroup ? options.userGroup : null;
	    this.accessCodes = options.accessCodes ? options.accessCodes : null;
	    this.section = options.section ? options.section : null;
	    this.headSection = options.headSection;
	    this.newColumn = options.newColumn ? options.newColumn : null;
	    this.openPopupEvent = options.grid.openPopupEvent ? options.grid.openPopupEvent : null;
	    this.popupContainer = options.grid.popupContainer ? options.grid.popupContainer : null;
	  }
	  getItem(options) {
	    options = options || {};
	    const defaultParam = {
	      group: options.group,
	      changerOptions: options.changerOptions
	    };
	    let param = {
	      ...defaultParam
	    };
	    if (options.type === UserGroupTitle.TYPE) {
	      param = {
	        type: options.type,
	        text: options.title,
	        controller: options.controller,
	        ...defaultParam
	      };
	    }
	    if (options.type === Title.TYPE) {
	      param = {
	        ...defaultParam,
	        id: options.id,
	        groupHead: options.groupHead,
	        type: options.type,
	        hint: options.hint,
	        text: options.title,
	        controller: options.controller
	      };
	    }
	    if (options.type === Toggler.TYPE) {
	      param = {
	        ...defaultParam,
	        type: options.type,
	        access: options.access
	      };
	    }
	    if (options.type === VariableSelector.TYPE || options.type === MultiSelector.TYPE) {
	      param = {
	        ...defaultParam,
	        type: options.type,
	        text: options.title,
	        variables: options.variables,
	        access: options.access
	      };
	    }
	    if (options.type === MultiSelector.TYPE) {
	      param.allSelectedCode = options.allSelectedCode;
	      param.enableSearch = options.enableSearch;
	      param.showAvatars = options.showAvatars;
	      param.compactView = options.compactView;
	      param.hintTitle = options.hintTitle;
	      param.disableSelectAll = options.disableSelectAll || false;
	    }
	    if (options.type === Role.TYPE) {
	      param = {
	        ...defaultParam,
	        type: options.type,
	        text: options.title
	      };
	    }
	    if (options.type === Member.TYPE) {
	      const accessCodes = [];
	      for (const item in options.members) {
	        accessCodes[item] = options.members[item].type;
	      }
	      param = {
	        type: options.type,
	        accessCodes: accessCodes
	      };
	    }
	    param.column = this;
	    param.userGroup = this.userGroup;
	    param.openPopupEvent = this.openPopupEvent;
	    param.popupContainer = this.popupContainer;
	    param.currentValue = null;
	    param.grid = this.grid;
	    if (options.type === VariableSelector.TYPE || options.type === MultiSelector.TYPE || options.type === Toggler.TYPE) {
	      var _param$userGroup$acce, _param$userGroup;
	      const accessId = param.access.id.toString();
	      const accessRights = (_param$userGroup$acce = (_param$userGroup = param.userGroup) == null ? void 0 : _param$userGroup.accessRights) != null ? _param$userGroup$acce : [];
	      for (let i = 0; i < accessRights.length; i++) {
	        if (accessId !== accessRights[i].id.toString()) {
	          continue;
	        }
	        if (options.type === MultiSelector.TYPE) {
	          var _param$currentValue;
	          param.currentValue = (_param$currentValue = param.currentValue) != null ? _param$currentValue : [];
	          if (main_core.Type.isArray(accessRights[i].value)) {
	            param.currentValue = [...param.currentValue, ...accessRights[i].value];
	          } else {
	            param.currentValue.push(accessRights[i].value);
	          }
	        } else {
	          param.currentValue = accessRights[i].value;
	        }
	      }
	    }
	    return new ColumnItem(param);
	  }
	  getUserGroup() {
	    return this.userGroup;
	  }
	  remove() {
	    if (main_core.Dom.hasClass(this.layout.container, 'ui-access-rights-column-new')) {
	      this.resetClassNew();
	    }
	    main_core.Dom.addClass(this.layout.container, 'ui-access-rights-column-remove');
	    main_core.Dom.style(this.layout.container, 'width', this.layout.container.offsetWidth + 'px');
	    main_core.Event.bind(this.layout.container, 'animationend', () => {
	      main_core.Dom.style(this.layout.container, 'minWidth', '0px');
	      main_core.Dom.style(this.layout.container, 'maxWidth', '0px');
	    });
	    setTimeout(() => {
	      main_core.Dom.remove(this.layout.container);
	    }, 500);
	  }
	  resetClassNew() {
	    main_core.Dom.removeClass(this.layout.container, 'ui-access-rights-column-new');
	  }
	  render() {
	    if (!this.layout.container) {
	      const itemsFragment = document.createDocumentFragment();
	      if (this.headSection) {
	        this.userGroup.type = Role.TYPE;
	        main_core.Dom.append(this.getItem(this.userGroup).render(), itemsFragment);
	        this.userGroup.type = Member.TYPE;
	        main_core.Dom.append(this.getItem(this.userGroup).render(), itemsFragment);
	      }
	      for (const data of this.items) {
	        const item = this.getItem(data);
	        main_core.Dom.append(item.render(), itemsFragment);
	      }
	      this.layout.container = main_core.Tag.render(_t$d || (_t$d = _$d`<div class='ui-access-rights-column'></div>`));
	      if (this.newColumn) {
	        main_core.Dom.addClass('ui-access-rights-column-new', this.layout.container);
	      }
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetClassNew.bind(this));
	      main_core.Dom.append(itemsFragment, this.layout.container);
	      return this.layout.container;
	    }
	  }
	}
	const namespace$2 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$2.Column = Column;

	let _$e = t => t,
	  _t$e,
	  _t2$6,
	  _t3$4,
	  _t4$2,
	  _t5$2,
	  _t6$2,
	  _t7$1,
	  _t8$1;
	class Section {
	  constructor(options) {
	    var _options$id;
	    this.id = (_options$id = options.id) != null ? _options$id : null;
	    this.headSection = options.headSection ? options.headSection : null;
	    this.title = options.title;
	    this.hint = options.hint;
	    this.rights = options.rights ? options.rights : [];
	    this.userGroups = options.userGroups ? options.userGroups : [];
	    this.grid = options.grid ? options.grid : null;
	    this.layout = {
	      title: null,
	      headColumn: null,
	      columns: null,
	      content: null,
	      earLeft: null,
	      earRight: null
	    };
	    this.scroll = 0;
	    this.earTimer = null;
	    this.earLeftTimer = null;
	    this.earRightTimer = null;
	    this.columns = [];
	    this.bindEvents();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe(this.grid, 'AccessRights.Section:scroll', event => {
	      const [object] = event.getData();
	      if (this.title !== object.title) {
	        this.getColumnsContainer().scrollLeft = object.getScroll();
	      }
	      object.adjustEars();
	      main_popup.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
	    });
	    main_core.Event.bind(window, 'resize', this.adjustEars.bind(this));
	  }
	  getGrid() {
	    return this.grid;
	  }
	  addColumn(param) {
	    if (!param) {
	      return;
	    }
	    const options = Object.assign({}, param);
	    options.userGroup = param;
	    const column = this.getColumn(options);
	    main_core.Dom.append(column.render(), this.layout.columns);
	    this.columns.push(column);
	  }
	  getColumn(options) {
	    const controls = [];
	    this.rights.map(data => {
	      const isVariable = data.type === VariableSelector.TYPE || data.type === MultiSelector.TYPE;
	      controls.push({
	        type: data.type,
	        title: isVariable ? data.title : null,
	        hint: data.hint,
	        group: data.group,
	        variables: isVariable ? data.variables : [],
	        enableSearch: isVariable ? data.enableSearch : null,
	        showAvatars: isVariable ? data.showAvatars : false,
	        compactView: isVariable ? data.compactView : false,
	        hintTitle: isVariable ? data.hintTitle : null,
	        allSelectedCode: isVariable ? data.allSelectedCode : null,
	        changerOptions: data.changerOptions || {},
	        access: data
	      });
	    });
	    return new Column({
	      items: controls,
	      userGroup: options.userGroup ? options.userGroup : null,
	      section: this,
	      headSection: options.headSection,
	      grid: this.grid,
	      newColumn: options.newColumn ? options.newColumn : null
	    });
	  }
	  removeColumn(param) {
	    if (!param) {
	      return;
	    }
	    for (let i = 0; i < this.columns.length; i++) {
	      if (param.userGroup === this.columns[i].userGroup) {
	        this.columns[i].remove();
	        break;
	      }
	    }
	  }
	  addHeadColumn() {
	    let titles = [];
	    if (!this.headSection) {
	      this.rights.map(data => {
	        titles.push({
	          id: data.id,
	          type: Title.TYPE,
	          title: data.title,
	          hint: data.hint,
	          group: data.group,
	          groupHead: data.groupHead
	        });
	      });
	    }
	    if (this.headSection) {
	      titles = [{
	        type: UserGroupTitle.TYPE,
	        title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLES'),
	        controller: true
	      }, {
	        type: UserGroupTitle.TYPE,
	        title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_EMPLOYEES_AND_DEPARTMENTS'),
	        controller: false
	      }];
	    }
	    const column = new Column({
	      items: titles,
	      section: this,
	      grid: this.grid
	    });
	    main_core.Dom.append(column.render(), this.layout.headColumn);
	    return column;
	  }
	  getColumnsContainer() {
	    if (!this.layout.columns) {
	      const column = main_core.Tag.render(_t$e || (_t$e = _$e`<div class='ui-access-rights-section-wrapper'></div>`));
	      main_core.Event.bind(column, 'scroll', this.adjustScroll.bind(this));
	      this.layout.columns = column;
	    }
	    return this.layout.columns;
	  }
	  getTitleNode() {
	    const node = main_core.Tag.render(_t2$6 || (_t2$6 = _$e`<div class='ui-access-rights-section-title'>${0}</div>`), main_core.Text.encode(this.title));
	    if (this.hint) {
	      const hintNode = new Hint({
	        hint: this.hint,
	        className: 'ui-access-rights-section-title-hint'
	      });
	      node.appendChild(hintNode.render());
	    }
	    return node;
	  }
	  adjustScroll() {
	    if (main_core.Text.toNumber(this.scroll) !== main_core.Text.toNumber(this.getColumnsContainer().scrollLeft)) {
	      this.scroll = this.getColumnsContainer().scrollLeft;
	      main_core_events.EventEmitter.emit(this.grid, "AccessRights.Section:scroll", [this]);
	    }
	  }
	  adjustEars() {
	    const container = this.getColumnsContainer();
	    const scroll = container.scrollLeft;
	    const isLeftVisible = scroll > 0;
	    const isRightVisible = container.scrollWidth > Math.round(scroll + container.offsetWidth);
	    this.getContentContainer().classList[isLeftVisible ? 'add' : 'remove']('ui-access-rights-section-ear-left-shown');
	    this.getContentContainer().classList[isRightVisible ? 'add' : 'remove']('ui-access-rights-section-ear-right-shown');
	  }
	  getContentContainer() {
	    if (!this.layout.content) {
	      this.layout.content = main_core.Tag.render(_t3$4 || (_t3$4 = _$e`
				<div class='ui-access-rights-section-content'>
					${0}
					${0}
					${0}
				</div>
			`), this.getColumnsContainer(), this.getEarLeft(), this.getEarRight());
	    }
	    return this.layout.content;
	  }
	  getEarLeft() {
	    if (!this.layout.earLeft) {
	      this.layout.earLeft = main_core.Tag.render(_t4$2 || (_t4$2 = _$e`<div class='ui-access-rights-section-ear-left'></div>`));
	      main_core.Event.bind(this.layout.earLeft, 'mouseenter', () => {
	        this.stopAutoScroll();
	        this.earLeftTimer = setTimeout(() => {
	          this.scrollToLeft();
	        }, 110);
	      });
	      main_core.Event.bind(this.layout.earLeft, 'mouseleave', () => {
	        clearTimeout(this.earLeftTimer);
	        this.stopAutoScroll();
	      });
	    }
	    return this.layout.earLeft;
	  }
	  getEarRight() {
	    if (!this.layout.earRight) {
	      this.layout.earRight = main_core.Tag.render(_t5$2 || (_t5$2 = _$e`<div class='ui-access-rights-section-ear-right'></div>`));
	      main_core.Event.bind(this.layout.earRight, 'mouseenter', () => {
	        this.stopAutoScroll();
	        this.earRightTimer = setTimeout(() => {
	          this.scrollToRight();
	        }, 110);
	      });
	      main_core.Event.bind(this.layout.earRight, 'mouseleave', () => {
	        clearTimeout(this.earRightTimer);
	        this.stopAutoScroll();
	      });
	    }
	    return this.layout.earRight;
	  }
	  scrollToRight(param, stop) {
	    const interval = param ? 2 : 20;
	    this.earTimer = setInterval(() => {
	      this.getColumnsContainer().scrollLeft += 10;
	      if (param && param <= this.getColumnsContainer().scrollLeft) {
	        this.stopAutoScroll();
	      }
	    }, interval);
	    if (stop === 'stop') {
	      setTimeout(() => {
	        this.stopAutoScroll();
	        this.getGrid().unlock();
	      }, param * 2);
	    }
	  }
	  scrollToLeft() {
	    this.earTimer = setInterval(() => {
	      this.getColumnsContainer().scrollLeft -= 10;
	    }, 20);
	  }
	  stopAutoScroll() {
	    clearInterval(this.earTimer);
	  }
	  getScroll() {
	    return this.scroll;
	  }
	  render() {
	    var _this$grid$getUserGro;
	    const title = this.title ? this.getTitleNode() : null;
	    const sectionContainer = main_core.Tag.render(_t6$2 || (_t6$2 = _$e`
			<div class='ui-access-rights-section'>
				${0}
				${0}
			</div>
		`), title, this.getMainContainer());
	    if (this.headSection) {
	      main_core.Dom.addClass(sectionContainer, 'ui-access-rights--head-section');
	    }
	    this.addHeadColumn();
	    const columnsFragment = document.createDocumentFragment();
	    const userGroups = (_this$grid$getUserGro = this.grid.getUserGroups()) != null ? _this$grid$getUserGro : [];
	    for (let i = 0; i < userGroups.length; i++) {
	      const column = this.getColumn({
	        headSection: this.headSection ? this.headSection : null,
	        userGroup: userGroups[i]
	      });
	      this.columns.push(column);
	      main_core.Dom.append(column.render(), columnsFragment);
	    }
	    main_core.Dom.append(columnsFragment, this.getColumnsContainer());
	    return sectionContainer;
	  }
	  getMainContainer() {
	    this.layout.headColumn = main_core.Tag.render(_t7$1 || (_t7$1 = _$e`<div class='ui-access-rights-section-head'></div>`));
	    return main_core.Tag.render(_t8$1 || (_t8$1 = _$e`
			<div class='ui-access-rights-section-container'>
				${0}
				${0}
			</div>
		`), this.layout.headColumn, this.getContentContainer());
	  }
	}
	const namespace$3 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$3.Section = Section;

	exports.Grid = Grid;
	exports.Section = Section;
	exports.Column = Column;
	exports.ColumnItem = ColumnItem;

}((this.BX.UI = this.BX.UI || {}),BX,BX,BX.UI,BX.Main,BX.Event,BX.UI.EntitySelector,BX));
//# sourceMappingURL=accessrights.bundle.js.map
