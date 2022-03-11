this.BX = this.BX || {};
(function (exports,main_loader,ui_dialogs_messagebox,main_popup,main_core_events,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10;
	/**
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */

	var Item = /*#__PURE__*/function () {
	  function Item(params) {
	    babelHelpers.classCallCheck(this, Item);
	    babelHelpers.defineProperty(this, "isProgress", false);
	    main_core_events.EventEmitter.makeObservable(this, 'UI.Timeline.Item');
	    this.id = params.id;
	    this.createdTimestamp = null;
	    this.action = '';
	    this.title = '';
	    this.description = '';
	    this.htmlDescription = '';
	    this.textDescription = '';
	    this.userId = params.userId;
	    this.isFixed = params.isFixed === true;
	    this.data = {};
	    this.eventIds = new Set();

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isSet(params.eventIds)) {
	        this.eventIds = params.eventIds;
	      }

	      if (main_core.Type.isString(params.action)) {
	        this.action = params.action;
	      }

	      if (main_core.Type.isString(params.title)) {
	        this.title = params.title;
	      }

	      if (main_core.Type.isString(params.description)) {
	        this.description = params.description;
	      }

	      if (main_core.Type.isString(params.htmlDescription)) {
	        this.htmlDescription = params.htmlDescription;
	      }

	      if (main_core.Type.isString(params.textDescription)) {
	        this.textDescription = params.textDescription;
	      }

	      if (main_core.Type.isNumber(params.createdTimestamp)) {
	        this.createdTimestamp = params.createdTimestamp;
	      }

	      if (main_core.Type.isPlainObject(params.data)) {
	        this.data = params.data;
	      }
	    }

	    this.layout = {};
	    this.timeFormat = 'H:M';
	    this.nameFormat = '';
	    this.users = new Map();
	    this.isLast = false;
	    this.events = params.events;
	    this.isPinned = false;
	  }

	  babelHelpers.createClass(Item, [{
	    key: "afterRender",
	    value: function afterRender() {
	      main_core.Event.bind(this.renderPin(), 'click', this.onPinClick.bind(this));
	      this.bindActionsButtonClick();
	    }
	  }, {
	    key: "bindActionsButtonClick",
	    value: function bindActionsButtonClick() {
	      var button = this.getActionsButton();

	      if (button) {
	        main_core.Event.bind(button, 'click', this.onActionsButtonClick.bind(this));
	      }
	    }
	  }, {
	    key: "setIsLast",
	    value: function setIsLast(isLast) {
	      this.isLast = isLast;

	      if (this.isRendered()) {
	        if (this.isLast) {
	          this.getContainer().classList.add('ui-item-detail-stream-section-last');
	        } else {
	          this.getContainer().classList.remove('ui-item-detail-stream-section-last');
	        }
	      }
	    }
	  }, {
	    key: "setUserData",
	    value: function setUserData(users) {
	      if (users) {
	        this.users = users;
	      }

	      return this;
	    }
	  }, {
	    key: "setTimeFormat",
	    value: function setTimeFormat(timeFormat) {
	      if (main_core.Type.isString(timeFormat)) {
	        this.timeFormat = timeFormat;
	      }

	      return this;
	    }
	  }, {
	    key: "setNameFormat",
	    value: function setNameFormat(nameFormat) {
	      if (main_core.Type.isString(nameFormat)) {
	        this.nameFormat = nameFormat;
	      }

	      return this;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.layout.container;
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return main_core.Type.isDomNode(this.getContainer());
	    }
	  }, {
	    key: "getCreatedTime",
	    value: function getCreatedTime() {
	      if (this.createdTimestamp > 0) {
	        this.createdTimestamp = main_core.Text.toInteger(this.createdTimestamp);
	        return new Date(this.createdTimestamp);
	      }

	      return null;
	    }
	  }, {
	    key: "formatTime",
	    value: function formatTime(time) {
	      return BX.date.format(this.timeFormat, time);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      return main_core.Text.toInteger(this.userId);
	    }
	  }, {
	    key: "getScope",
	    value: function getScope() {
	      if (main_core.Type.isString(this.data.scope)) {
	        return this.data.scope;
	      }

	      return null;
	    }
	  }, {
	    key: "isScopeManual",
	    value: function isScopeManual() {
	      var scope = this.getScope();
	      return !scope || scope === 'manual';
	    }
	  }, {
	    key: "isScopeAutomation",
	    value: function isScopeAutomation() {
	      return this.getScope() === 'automation';
	    }
	  }, {
	    key: "isScopeTask",
	    value: function isScopeTask() {
	      return this.getScope() === 'task';
	    }
	  }, {
	    key: "isScopeRest",
	    value: function isScopeRest() {
	      return this.getScope() === 'rest';
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.layout.container = this.renderContainer();
	      this.updateLayout();
	      return this.layout.container;
	    }
	  }, {
	    key: "updateLayout",
	    value: function updateLayout() {
	      this.clearLayout(true);
	      this.layout.container.appendChild(this.renderIcon());

	      if (this.hasMenu()) {
	        this.layout.container.appendChild(this.renderActionsButton());
	      }

	      this.layout.container.appendChild(this.renderPin());
	      var content = this.getContent();

	      if (!content) {
	        content = this.renderContent();
	      }

	      this.layout.container.appendChild(content);
	      this.afterRender();
	    }
	  }, {
	    key: "renderContainer",
	    value: function renderContainer() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ", "\"></div>"])), this.isLast ? 'ui-item-detail-stream-section-last' : '');
	    }
	  }, {
	    key: "renderPin",
	    value: function renderPin() {
	      if (!this.layout.pin) {
	        this.layout.pin = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-item-detail-stream-section-top-fixed-btn\"></span>"])));
	      }

	      if (this.isFixed) {
	        this.layout.pin.classList.add('ui-item-detail-stream-section-top-fixed-btn-active');
	      } else {
	        this.layout.pin.classList.remove('ui-item-detail-stream-section-top-fixed-btn-active');
	      }

	      return this.layout.pin;
	    }
	  }, {
	    key: "renderContent",
	    value: function renderContent() {
	      this.layout.content = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-content\">", "</div>"])), this.renderDescription());
	      return this.getContent();
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return this.layout.content;
	    }
	  }, {
	    key: "renderDescription",
	    value: function renderDescription() {
	      this.layout.description = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-event\"></div>"])));
	      var header = this.renderHeader();

	      if (header) {
	        this.layout.description.appendChild(header);
	      }

	      this.layout.description.appendChild(this.renderMain());
	      return this.layout.description;
	    }
	  }, {
	    key: "renderHeader",
	    value: function renderHeader() {
	      return null;
	    }
	  }, {
	    key: "renderHeaderUser",
	    value: function renderHeaderUser(userId) {
	      userId = main_core.Text.toInteger(userId);
	      var userData = {
	        link: 'javascript: void(0)',
	        fullName: '',
	        photo: null
	      };

	      if (userId > 0) {
	        userData = this.users.get(userId);
	      }

	      if (!userData) {
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<a></a>"])));
	      }

	      var safeFullName = main_core.Tag.safe(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["", ""])), userData.fullName);
	      return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<a class=\"ui-item-detail-stream-content-employee\" href=\"", "\" target=\"_blank\" title=\"", "\" ", "></a>"])), userData.link, safeFullName, userData.photo ? 'style="background-image: url(\'' + userData.photo + '\'); background-size: 100%;"' : '');
	    }
	  }, {
	    key: "renderMain",
	    value: function renderMain() {
	      this.layout.main = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail\">", "</div>"])), this.description);
	      return this.getMain();
	    }
	  }, {
	    key: "getMain",
	    value: function getMain() {
	      return this.layout.main;
	    }
	  }, {
	    key: "renderIcon",
	    value: function renderIcon() {
	      this.layout.icon = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-icon\"></div>"])));
	      return this.layout.icon;
	    }
	  }, {
	    key: "getItem",
	    value: function getItem() {
	      if (main_core.Type.isPlainObject(this.data.item)) {
	        return this.data.item;
	      }

	      return null;
	    }
	  }, {
	    key: "onPinClick",
	    value: function onPinClick() {
	      this.isFixed = !this.isFixed;
	      this.renderPin();

	      if (main_core.Type.isFunction(this.events.onPinClick)) {
	        this.events.onPinClick(this);
	      }

	      this.emit('onPinClick');
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      var _this = this;

	      var isSkipContainer = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var container = this.getContainer();
	      Object.keys(this.layout).forEach(function (name) {
	        var node = _this.layout[name];

	        if (!isSkipContainer || container !== node) {
	          main_core.Dom.remove(node);
	          delete _this.layout[name];
	        }
	      });
	      return this;
	    }
	  }, {
	    key: "getDataForUpdate",
	    value: function getDataForUpdate() {
	      return {
	        description: this.description,
	        htmlDescription: this.htmlDescription,
	        data: this.data,
	        userId: this.userId
	      };
	    }
	  }, {
	    key: "updateData",
	    value: function updateData(params) {
	      if (main_core.Type.isPlainObject(params)) {
	        if (main_core.Type.isString(params.description)) {
	          this.description = params.description;
	        }

	        if (main_core.Type.isString(params.htmlDescription)) {
	          this.htmlDescription = params.htmlDescription;
	        }

	        if (main_core.Type.isPlainObject(params.data)) {
	          this.data = params.data;
	        }

	        if (params.userId > 0) {
	          this.userId = params.userId;
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "update",
	    value: function update(params) {
	      this.updateData(params).updateLayout();
	      return this;
	    }
	  }, {
	    key: "onError",
	    value: function onError(params) {
	      if (main_core.Type.isFunction(this.events.onError)) {
	        this.events.onError(params);
	      }

	      this.emit('error', params);
	    }
	  }, {
	    key: "onDelete",
	    value: function onDelete() {
	      if (main_core.Type.isFunction(this.events.onDelete)) {
	        this.events.onDelete(this);
	      }

	      this.emit('onDeleteComplete');
	    }
	  }, {
	    key: "hasMenu",
	    value: function hasMenu() {
	      return this.hasActions();
	    }
	  }, {
	    key: "hasActions",
	    value: function hasActions() {
	      return this.getActions().length > 0;
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      return [];
	    }
	  }, {
	    key: "renderActionsButton",
	    value: function renderActionsButton() {
	      this.layout.contextMenuButton = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-item-context-menu\"></div>"])));
	      return this.getActionsButton();
	    }
	  }, {
	    key: "getActionsButton",
	    value: function getActionsButton() {
	      return this.layout.contextMenuButton;
	    }
	  }, {
	    key: "getActionsMenuId",
	    value: function getActionsMenuId() {
	      return 'ui-timeline-item-context-menu-' + this.getId();
	    }
	  }, {
	    key: "onActionsButtonClick",
	    value: function onActionsButtonClick() {
	      this.getActionsMenu().toggle();
	    }
	  }, {
	    key: "getActionsMenu",
	    value: function getActionsMenu() {
	      return main_popup.MenuManager.create({
	        id: this.getActionsMenuId(),
	        bindElement: this.getActionsButton(),
	        items: this.getActions(),
	        offsetTop: 0,
	        offsetLeft: 16,
	        angle: {
	          position: "top",
	          offset: 0
	        },
	        events: {
	          onPopupShow: this.onContextMenuShow.bind(this),
	          onPopupClose: this.onContextMenuClose.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "onContextMenuShow",
	    value: function onContextMenuShow() {
	      this.getActionsButton().classList.add('active');
	    }
	  }, {
	    key: "onContextMenuClose",
	    value: function onContextMenuClose() {
	      this.getActionsButton().classList.remove('active');
	      this.getActionsMenu().destroy();
	    }
	  }, {
	    key: "startProgress",
	    value: function startProgress() {
	      this.isProgress = true;
	      this.getLoader().show();
	    }
	  }, {
	    key: "stopProgress",
	    value: function stopProgress() {
	      this.isProgress = false;

	      if (this.getLoader().isShown()) {
	        this.getLoader().hide();
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.getContainer()
	        });
	      }

	      return this.loader;
	    }
	  }]);
	  return Item;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1;
	var History = /*#__PURE__*/function (_Item) {
	  babelHelpers.inherits(History, _Item);

	  function History() {
	    babelHelpers.classCallCheck(this, History);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(History).apply(this, arguments));
	  }

	  babelHelpers.createClass(History, [{
	    key: "renderContainer",
	    value: function renderContainer() {
	      var container = babelHelpers.get(babelHelpers.getPrototypeOf(History.prototype), "renderContainer", this).call(this);

	      if (this.isScopeAutomation()) {
	        container.classList.add('ui-item-detail-stream-section-icon-robot');
	      } else {
	        container.classList.add('ui-item-detail-stream-section-info');
	      }

	      return container;
	    }
	  }, {
	    key: "renderHeader",
	    value: function renderHeader() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-header\">\n\t\t\t<div class=\"ui-item-detail-stream-content-title\">\n\t\t\t\t<span class=\"ui-item-detail-stream-content-title-text\">", "</span>\n\t\t\t\t<span class=\"ui-item-detail-stream-content-title-time\">", "</span>\n\t\t\t</div>\n\t\t\t", "\n\t\t</div>"])), main_core.Text.encode(this.getTitle()), this.formatTime(this.getCreatedTime()), this.renderHeaderUser(this.getUserId()));
	    }
	  }, {
	    key: "renderStageChangeTitle",
	    value: function renderStageChangeTitle() {
	      return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-title\">\n\t\t\t<span class=\"ui-item-detail-stream-content-title-text\">", "</span>\n\t\t</div>"])), main_core.Loc.getMessage('UI_TIMELINE_STAGE_CHANGE_SUBTITLE'));
	    }
	  }, {
	    key: "renderStageChange",
	    value: function renderStageChange() {
	      var stageFrom = this.getStageFrom();
	      var stageTo = this.getStageTo();

	      if (stageFrom && stageTo && stageFrom.id !== stageTo.id) {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail-info\">\n\t\t\t\t<span class=\"ui-item-detail-stream-content-detail-info-status\">", "</span>\n\t\t\t\t<span class=\"ui-item-detail-stream-content-detail-info-separator\"></span>\n\t\t\t\t<span class=\"ui-item-detail-stream-content-detail-info-status\">", "</span>\n\t\t\t</div>"])), main_core.Text.encode(stageFrom.name), main_core.Text.encode(stageTo.name));
	      }

	      return null;
	    }
	  }, {
	    key: "getStageFrom",
	    value: function getStageFrom() {
	      if (main_core.Type.isPlainObject(this.data.stageFrom)) {
	        return this.data.stageFrom;
	      }

	      return null;
	    }
	  }, {
	    key: "getStageTo",
	    value: function getStageTo() {
	      if (main_core.Type.isPlainObject(this.data.stageTo)) {
	        return this.data.stageTo;
	      }

	      return null;
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      if (main_core.Type.isArray(this.data.fields)) {
	        return this.data.fields;
	      }

	      return null;
	    }
	  }, {
	    key: "renderFieldsChange",
	    value: function renderFieldsChange() {
	      var fields = this.getFields();

	      if (fields) {
	        var list = [];
	        fields.forEach(function (field) {
	          list.push(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail-field\">", "</div>"])), main_core.Text.encode(field.title)));
	        });
	        return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail-info ui-item-detail-stream-content-detail-info-break\">\n\t\t\t\t", "\n\t\t\t</div>"])), list);
	      }

	      return null;
	    }
	  }, {
	    key: "renderFieldsChangeTitle",
	    value: function renderFieldsChangeTitle() {
	      return main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-title\">\n\t\t\t<span class=\"ui-item-detail-stream-content-title-text\">", "</span>\n\t\t</div>"])), main_core.Loc.getMessage('UI_TIMELINE_FIELDS_CHANGE_SUBTITLE'));
	    }
	  }]);
	  return History;
	}(Item);

	var _templateObject$2;
	var StageChange = /*#__PURE__*/function (_History) {
	  babelHelpers.inherits(StageChange, _History);

	  function StageChange() {
	    babelHelpers.classCallCheck(this, StageChange);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StageChange).apply(this, arguments));
	  }

	  babelHelpers.createClass(StageChange, [{
	    key: "renderMain",
	    value: function renderMain() {
	      var stageChange = this.renderStageChange();

	      if (!stageChange) {
	        stageChange = '';
	      }

	      var fieldsChange = this.renderFieldsChange();

	      if (!fieldsChange) {
	        fieldsChange = '';
	      }

	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail\">\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), stageChange, fieldsChange);
	    }
	  }]);
	  return StageChange;
	}(History);

	var _templateObject$3;
	var FieldsChange = /*#__PURE__*/function (_History) {
	  babelHelpers.inherits(FieldsChange, _History);

	  function FieldsChange() {
	    babelHelpers.classCallCheck(this, FieldsChange);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsChange).apply(this, arguments));
	  }

	  babelHelpers.createClass(FieldsChange, [{
	    key: "renderMain",
	    value: function renderMain() {
	      var fieldsChange = this.renderFieldsChange();

	      if (!fieldsChange) {
	        fieldsChange = '';
	      }

	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail\">\n\t\t\t", "\n\t\t</div>"])), fieldsChange);
	    }
	  }]);
	  return FieldsChange;
	}(History);

	/**
	 * @abstract
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */

	var Editor = /*#__PURE__*/function () {
	  function Editor(params) {
	    babelHelpers.classCallCheck(this, Editor);
	    babelHelpers.defineProperty(this, "isProgress", false);

	    if (main_core.Type.isString(params.id) && params.id.length > 0) {
	      this.id = params.id;
	    } else {
	      this.id = main_core.Text.getRandom();
	    }

	    this.layout = {};
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.Timeline.Editor');
	  }

	  babelHelpers.createClass(Editor, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {}
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.layout.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('This method should be overridden');
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      var _this = this;

	      var isSkipContainer = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var container = this.getContainer();
	      Object.keys(this.layout).forEach(function (name) {
	        var node = _this.layout[name];

	        if (!isSkipContainer || container !== node) {
	          main_core.Dom.clean(node);
	          delete _this.layout[name];
	        }
	      });
	      return this;
	    }
	  }, {
	    key: "startProgress",
	    value: function startProgress() {
	      this.isProgress = true;
	      this.getLoader().show();
	    }
	  }, {
	    key: "stopProgress",
	    value: function stopProgress() {
	      this.isProgress = false;

	      if (this.getLoader().isShown()) {
	        this.getLoader().hide();
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.getContainer()
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return main_core.Type.isDomNode(this.getContainer());
	    }
	  }]);
	  return Editor;
	}();

	var _templateObject$4, _templateObject2$2, _templateObject3$2, _templateObject4$2, _templateObject5$2, _templateObject6$2, _templateObject7$1;
	/**
	 * @memberOf BX.UI.Timeline
	 * @mixes EventEmitter
	 */

	var CommentEditor = /*#__PURE__*/function (_Editor) {
	  babelHelpers.inherits(CommentEditor, _Editor);

	  function CommentEditor(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, CommentEditor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CommentEditor).call(this, params));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "commentId", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "editorContent", null);

	    if (main_core.Type.isNumber(params.commentId)) {
	      _this.commentId = params.commentId;
	    }

	    _this.setEventNamespace('BX.UI.Timeline.CommentEditor');

	    return _this;
	  }

	  babelHelpers.createClass(CommentEditor, [{
	    key: "getTitle",
	    value: function getTitle() {
	      return main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT');
	    }
	  }, {
	    key: "getVisualEditorName",
	    value: function getVisualEditorName() {
	      return 'UiTimelineCommentVisualEditor' + this.getId().replace('- ', '');
	    }
	  }, {
	    key: "getTextarea",
	    value: function getTextarea() {
	      return this.layout.textarea;
	    }
	  }, {
	    key: "renderTextarea",
	    value: function renderTextarea() {
	      this.layout.textarea = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<textarea onfocus=\"", "\" rows=\"1\" class=\"ui-item-detail-stream-section-new-comment-textarea\" placeholder=\"", "\"></textarea>"])), this.onFocus.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_TEXTAREA'));
	      return this.getTextarea();
	    }
	  }, {
	    key: "getVisualEditorContainer",
	    value: function getVisualEditorContainer() {
	      return this.layout.visualEditorContainer;
	    }
	  }, {
	    key: "renderVisualEditorContainer",
	    value: function renderVisualEditorContainer() {
	      this.layout.visualEditorContainer = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-comment-visual-editor\"></div>"])));
	      return this.getVisualEditorContainer();
	    }
	  }, {
	    key: "getButtonsContainer",
	    value: function getButtonsContainer() {
	      return this.layout.buttonsContainer;
	    }
	  }, {
	    key: "renderButtons",
	    value: function renderButtons() {
	      this.layout.buttonsContainer = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-new-comment-btn-container\">\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), this.renderSaveButton(), this.renderCancelButton());
	      return this.getButtonsContainer();
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      return this.layout.saveButton;
	    }
	  }, {
	    key: "renderSaveButton",
	    value: function renderSaveButton() {
	      this.layout.saveButton = main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["<button onclick=\"", "\" class=\"ui-btn ui-btn-xs ui-btn-primary\">", "</button>"])), this.save.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_SEND'));
	      return this.getSaveButton();
	    }
	  }, {
	    key: "getCancelButton",
	    value: function getCancelButton() {
	      return this.layout.cancelButton;
	    }
	  }, {
	    key: "renderCancelButton",
	    value: function renderCancelButton() {
	      this.layout.cancelButton = main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["<span onclick=\"", "\" class=\"ui-btn ui-btn-xs ui-btn-link\">", "</span>"])), this.cancel.bind(this), main_core.Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_CANCEL'));
	      return this.getCancelButton();
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.layout.container = main_core.Tag.render(_templateObject6$2 || (_templateObject6$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-comment-editor\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>"])), this.renderTextarea(), this.renderButtons(), this.renderVisualEditorContainer());
	      return this.getContainer();
	    }
	  }, {
	    key: "onFocus",
	    value: function onFocus() {
	      var container = this.getContainer();

	      if (container) {
	        container.classList.add('focus');
	      }

	      this.showVisualEditor();
	    }
	  }, {
	    key: "showVisualEditor",
	    value: function showVisualEditor() {
	      var _this2 = this;

	      if (!this.getVisualEditorContainer()) {
	        return;
	      }

	      if (this.postForm && this.visualEditor) {
	        this.postForm.eventNode.style.display = 'block';
	        this.visualEditor.Focus();
	      } else if (!this.isProgress) {
	        this.loadVisualEditor().then(function () {
	          main_core_events.EventEmitter.emit(_this2.postForm.eventNode, 'OnShowLHE', [true]); //todo there should be some other way

	          setTimeout(function () {
	            _this2.editorContent = _this2.postForm.oEditor.GetContent();
	          }, 300);
	        })["catch"](function () {
	          _this2.cancel();

	          _this2.emit('error', {
	            message: 'Could not load visual editor. Please try again later'
	          });
	        });
	      }
	    }
	  }, {
	    key: "loadVisualEditor",
	    value: function loadVisualEditor() {
	      var _this3 = this;

	      return new Promise(function (resolve, reject) {
	        if (_this3.isProgress) {
	          reject();
	        }

	        _this3.showEditorLoader();

	        var event = new main_core_events.BaseEvent({
	          data: {
	            name: _this3.getVisualEditorName(),
	            commentId: _this3.commentId
	          }
	        });

	        _this3.emitAsync('onLoadVisualEditor', event).then(function () {
	          var html = event.getData().html;

	          if (main_core.Type.isString(html)) {
	            main_core.Runtime.html(_this3.getVisualEditorContainer(), html).then(function () {
	              _this3.hideEditorLoader();

	              if (LHEPostForm && BXHtmlEditor) {
	                _this3.postForm = LHEPostForm.getHandler(_this3.getVisualEditorName());
	                _this3.visualEditor = BXHtmlEditor.Get(_this3.getVisualEditorName());
	                resolve();
	              } else {
	                reject();
	              }
	            });
	          } else {
	            reject();
	          }
	        })["catch"](function () {
	          reject();
	        });
	      });
	    }
	  }, {
	    key: "showEditorLoader",
	    value: function showEditorLoader() {
	      this.editorLoader = main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-wait\"></div>"])));
	      main_core.Dom.append(this.editorLoader, this.getContainer());
	    }
	  }, {
	    key: "hideEditorLoader",
	    value: function hideEditorLoader() {
	      main_core.Dom.remove(this.editorLoader);
	    }
	  }, {
	    key: "hideVisualEditor",
	    value: function hideVisualEditor() {
	      if (this.postForm) {
	        this.postForm.eventNode.style.display = 'none';
	      }
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this4 = this;

	      if (this.isProgress || !this.postForm) {
	        return;
	      }

	      var isCancel = false;
	      var description = this.postForm.oEditor.GetContent();
	      this.editorContent = description;
	      var files = this.getAttachments();
	      this.emit('beforeSave', {
	        description: description,
	        isCancel: isCancel,
	        files: files
	      });

	      if (description === '') {
	        this.getEmptyMessageNotification().show();
	        return;
	      }

	      this.startProgress();
	      var event = new main_core_events.BaseEvent({
	        data: {
	          description: description,
	          files: files,
	          commentId: this.commentId
	        }
	      });
	      this.emitAsync('onSave', event).then(function () {
	        _this4.postForm.reinit();

	        _this4.stopProgress();

	        _this4.emit('afterSave', {
	          data: event.getData()
	        });

	        _this4.cancel();
	      })["catch"](function () {
	        //todo why are we here?
	        _this4.stopProgress();

	        _this4.cancel();

	        var message = event.getData().message;

	        if (message) {
	          _this4.emit('error', {
	            message: message
	          });
	        }
	      });
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {
	      this.hideVisualEditor();
	      var container = this.getContainer();

	      if (container) {
	        container.classList.remove('focus');
	      }

	      this.stopProgress();
	      this.emit('cancel');
	    }
	  }, {
	    key: "getEmptyMessageNotification",
	    value: function getEmptyMessageNotification() {
	      if (!this.emptyMessagePopup) {
	        this.emptyMessagePopup = new main_popup.Popup({
	          id: this.getId() + '-empty-message-popup',
	          bindElement: this.getSaveButton(),
	          content: BX.message('UI_TIMELINE_EMPTY_COMMENT_NOTIFICATION'),
	          darkMode: true,
	          autoHide: true,
	          zIndex: 990,
	          angle: {
	            position: 'top',
	            offset: 77
	          },
	          closeByEsc: true,
	          bindOptions: {
	            forceBindPosition: true
	          }
	        });
	      }

	      return this.emptyMessagePopup;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh() {
	      if (this.postForm && this.postForm.oEditor) {
	        if (this.editorContent) {
	          this.postForm.oEditor.SetContent(this.editorContent);
	        }
	      }

	      if (this.visualEditor) {
	        this.visualEditor.ReInitIframe();
	      }
	    }
	  }, {
	    key: "getAttachments",
	    value: function getAttachments() {
	      var _this5 = this;

	      var attachments = [];

	      if (!this.postForm || !main_core.Type.isPlainObject(this.postForm.arFiles) || !main_core.Type.isPlainObject(this.postForm.controllers)) {
	        return attachments;
	      }

	      var fileControllers = [];
	      Object.values(this.postForm.arFiles).forEach(function (controller) {
	        if (!fileControllers.includes(controller)) {
	          fileControllers.push(controller);
	        }
	      });
	      fileControllers.forEach(function (fileController) {
	        if (_this5.postForm.controllers[fileController] && main_core.Type.isPlainObject(_this5.postForm.controllers[fileController].values)) {
	          Object.keys(_this5.postForm.controllers[fileController].values).forEach(function (fileId) {
	            if (!attachments.includes(fileId)) {
	              attachments.push(fileId);
	            }
	          });
	        }
	      });
	      return attachments;
	    }
	  }]);
	  return CommentEditor;
	}(Editor);

	var _templateObject$5, _templateObject2$3, _templateObject3$3, _templateObject4$3, _templateObject5$3, _templateObject6$3;
	var COLLAPSE_TEXT_MAX_LENGTH = 128;
	/**
	 * @memberOf BX.UI.Timeline
	 * @mixes EventEmitter
	 */

	var Comment = /*#__PURE__*/function (_History) {
	  babelHelpers.inherits(Comment, _History);

	  function Comment(props) {
	    var _this;

	    babelHelpers.classCallCheck(this, Comment);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Comment).call(this, props));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "isCollapsed", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "isContentLoaded", null);

	    _this.setEventNamespace('BX.UI.Timeline.Comment');

	    return _this;
	  }

	  babelHelpers.createClass(Comment, [{
	    key: "afterRender",
	    value: function afterRender() {
	      var _this2 = this;

	      babelHelpers.get(babelHelpers.getPrototypeOf(Comment.prototype), "afterRender", this).call(this);

	      if (this.isCollapsed === null) {
	        this.isCollapsed = this.isAddExpandBlock();
	      }

	      if (this.isContentLoaded === null) {
	        this.isContentLoaded = !this.hasFiles();
	      }

	      if (this.isCollapsed) {
	        this.getMain().classList.add('ui-timeline-content-description-collapsed');
	        this.getMain().classList.remove('ui-timeline-content-description-expand');
	      } else {
	        this.getMain().classList.remove('ui-timeline-content-description-collapsed');
	        this.getMain().classList.add('ui-timeline-content-description-expand');
	      }

	      if (this.isAddExpandBlock()) {
	        this.getMainDescription().appendChild(this.renderExpandBlock());
	      }

	      if (this.hasFiles()) {
	        this.getContent().appendChild(main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-section-files\">", "</div>"])), this.renderFilesContainer()));
	        main_core.Event.ready(function () {
	          setTimeout(function () {
	            _this2.loadFilesContent();
	          }, 100);
	        });
	      }
	    }
	  }, {
	    key: "getFiles",
	    value: function getFiles() {
	      if (main_core.Type.isArray(this.data.files)) {
	        return this.data.files;
	      }

	      return [];
	    }
	  }, {
	    key: "hasFiles",
	    value: function hasFiles() {
	      return this.getFiles().length > 0;
	    }
	  }, {
	    key: "isAddExpandBlock",
	    value: function isAddExpandBlock() {
	      return this.textDescription.length > COLLAPSE_TEXT_MAX_LENGTH || this.hasFiles();
	    }
	  }, {
	    key: "renderContainer",
	    value: function renderContainer() {
	      var container = babelHelpers.get(babelHelpers.getPrototypeOf(Comment.prototype), "renderContainer", this).call(this);
	      container.classList.add('ui-item-detail-stream-section-comment');
	      container.classList.remove('ui-item-detail-stream-section-info');
	      return container;
	    }
	  }, {
	    key: "renderMain",
	    value: function renderMain() {
	      this.layout.main = main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-detail\">\n\t\t\t", "\n\t\t</div>"])), this.renderMainDescription());
	      return this.getMain();
	    }
	  }, {
	    key: "getMain",
	    value: function getMain() {
	      return this.layout.main;
	    }
	  }, {
	    key: "renderMainDescription",
	    value: function renderMainDescription() {
	      this.layout.mainDescription = main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content-description\" onclick=\"", "\">", "</div>"])), this.onMainClick.bind(this), this.htmlDescription);
	      return this.getMainDescription();
	    }
	  }, {
	    key: "getMainDescription",
	    value: function getMainDescription() {
	      return this.layout.mainDescription;
	    }
	  }, {
	    key: "renderExpandBlock",
	    value: function renderExpandBlock() {
	      this.layout.expandBlock = main_core.Tag.render(_templateObject4$3 || (_templateObject4$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-content-description-expand-container\">", "</div>"])), this.renderExpandButton());
	      return this.getExpandBlock();
	    }
	  }, {
	    key: "getExpandBlock",
	    value: function getExpandBlock() {
	      return this.layout.expandBlock;
	    }
	  }, {
	    key: "renderExpandButton",
	    value: function renderExpandButton() {
	      this.layout.expandButton = main_core.Tag.render(_templateObject5$3 || (_templateObject5$3 = babelHelpers.taggedTemplateLiteral(["<a class=\"ui-timeline-content-description-expand-btn\" onclick=\"", "\">\n\t\t\t", "\n\t\t</a>"])), this.onExpandButtonClick.bind(this), main_core.Loc.getMessage(this.isCollapsed ? 'UI_TIMELINE_EXPAND_SM' : 'UI_TIMELINE_COLLAPSE_SM'));
	      return this.getExpandButton();
	    }
	  }, {
	    key: "getExpandButton",
	    value: function getExpandButton() {
	      return this.layout.expandButton;
	    }
	  }, {
	    key: "getCommendEditor",
	    value: function getCommendEditor() {
	      if (!this.commentEditor) {
	        this.commentEditor = new CommentEditor({
	          commentId: this.getId(),
	          id: 'UICommentEditor' + this.getId() + (this.isPinned ? 'pinned' : '') + main_core.Text.getRandom()
	        });
	        this.commentEditor.layout.container = this.getContainer();
	        this.commentEditor.subscribe('cancel', this.switchToViewMode.bind(this));
	        this.commentEditor.subscribe('afterSave', this.onSaveComment.bind(this));
	      }

	      return this.commentEditor;
	    }
	  }, {
	    key: "getEditorContainer",
	    value: function getEditorContainer() {
	      return this.layout.editorContainer;
	    }
	  }, {
	    key: "renderEditorContainer",
	    value: function renderEditorContainer() {
	      var editorContainer = this.getCommendEditor().getVisualEditorContainer();

	      if (editorContainer) {
	        this.layout.editorContainer = editorContainer;
	      } else {
	        this.layout.editorContainer = this.getCommendEditor().renderVisualEditorContainer();
	      }

	      return this.getEditorContainer();
	    }
	  }, {
	    key: "getEditorButtons",
	    value: function getEditorButtons() {
	      return this.layout.editorButtons;
	    }
	  }, {
	    key: "renderEditorButtons",
	    value: function renderEditorButtons() {
	      this.layout.editorButtons = this.getCommendEditor().renderButtons();
	      return this.getEditorButtons();
	    }
	  }, {
	    key: "renderFilesContainer",
	    value: function renderFilesContainer() {
	      this.layout.filesContainer = main_core.Tag.render(_templateObject6$3 || (_templateObject6$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-timeline-section-files-inner\"></div>"])));
	      return this.getFilesContainer();
	    }
	  }, {
	    key: "getFilesContainer",
	    value: function getFilesContainer() {
	      return this.layout.filesContainer;
	    }
	  }, {
	    key: "switchToEditMode",
	    value: function switchToEditMode() {
	      if (!this.isRendered()) {
	        return;
	      }

	      if (!this.getEditorContainer()) {
	        this.getMain().appendChild(this.renderEditorContainer());
	        this.getMain().appendChild(this.renderEditorButtons());
	      } else {
	        this.getCommendEditor().refresh();
	      }

	      this.getContent().classList.add('ui-item-detail-comment-edit');
	      this.getCommendEditor().showVisualEditor();
	    }
	  }, {
	    key: "switchToViewMode",
	    value: function switchToViewMode() {
	      this.getContent().classList.remove('ui-item-detail-comment-edit');
	      this.getCommendEditor().hideVisualEditor();
	    }
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      return [{
	        text: main_core.Loc.getMessage('UI_TIMELINE_ACTION_MODIFY'),
	        onclick: this.actionEdit.bind(this)
	      }, {
	        text: main_core.Loc.getMessage('UI_TIMELINE_ACTION_DELETE'),
	        onclick: this.actionDelete.bind(this)
	      }];
	    }
	  }, {
	    key: "actionEdit",
	    value: function actionEdit() {
	      this.getActionsMenu().close();
	      this.switchToEditMode();
	    }
	  }, {
	    key: "actionDelete",
	    value: function actionDelete() {
	      var _this3 = this;

	      this.getActionsMenu().close();
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('UI_TIMELINE_COMMENT_DELETE_CONFIRM'), function () {
	        return new Promise(function (resolve) {
	          if (_this3.isProgress) {
	            return;
	          }

	          _this3.startProgress();

	          var event = new main_core_events.BaseEvent({
	            data: {
	              commentId: _this3.getId()
	            }
	          });

	          _this3.emitAsync('onDelete', event).then(function () {
	            _this3.stopProgress();

	            _this3.onDelete();

	            resolve();
	          })["catch"](function () {
	            _this3.stopProgress();

	            var message = event.getData().message;

	            if (message) {
	              _this3.emit('error', {
	                message: message
	              });
	            }

	            resolve();
	          });
	        });
	      });
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      var isSkipContainer = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      this.commentEditor = null;
	      return babelHelpers.get(babelHelpers.getPrototypeOf(Comment.prototype), "clearLayout", this).call(this, isSkipContainer);
	    }
	  }, {
	    key: "onSaveComment",
	    value: function onSaveComment(event) {
	      var data = event.getData();

	      if (data.data && data.data.comment) {
	        this.update(data.data.comment);
	      }
	    }
	  }, {
	    key: "onMainClick",
	    value: function onMainClick(_ref) {
	      var target = _ref.target;

	      if (main_core.Type.isDomNode(target)) {
	        var tagName = target.tagName.toLowerCase();

	        if (tagName === 'a' || tagName === 'img' || main_core.Dom.hasClass(target, 'feed-con-file-changes-link-more') || main_core.Dom.hasClass(target, 'feed-com-file-inline') || document.getSelection().toString().length > 0) {
	          return;
	        }
	      }

	      this.switchToEditMode();
	    }
	  }, {
	    key: "onExpandButtonClick",
	    value: function onExpandButtonClick(event) {
	      var _this4 = this;

	      event.preventDefault();
	      event.stopPropagation();

	      if (!this.isRendered()) {
	        return;
	      }

	      if (this.isCollapsed === true) {
	        this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + 130 + "px";
	        this.getMain().classList.remove('ui-timeline-content-description-collapsed');
	        this.getMain().classList.add('ui-timeline-content-description-expand');
	        setTimeout(function () {
	          _this4.getExpandBlock().style.maxHeight = "";
	        }, 300);
	        this.getExpandButton().innerText = main_core.Loc.getMessage('UI_TIMELINE_COLLAPSE_SM');

	        if (!this.isContentLoaded) {
	          this.isContentLoaded = true;
	          this.loadContent();
	        }

	        this.isCollapsed = false;
	      } else if (this.isCollapsed === false) {
	        this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + "px";
	        this.getMain().classList.add('ui-timeline-content-description-collapsed');
	        this.getMain().classList.remove('ui-timeline-content-description-expand');
	        setTimeout(function () {
	          _this4.getExpandBlock().style.maxHeight = "";
	        }, 0);
	        this.getExpandButton().innerText = main_core.Loc.getMessage('UI_TIMELINE_EXPAND_SM');
	        this.isCollapsed = true;
	      }
	    }
	  }, {
	    key: "loadFilesContent",
	    value: function loadFilesContent() {
	      var _this5 = this;

	      if (this.isProgress) {
	        return;
	      }

	      this.startProgress();
	      var event = new main_core_events.BaseEvent({
	        data: {
	          commentId: this.getId()
	        }
	      });
	      this.emitAsync('onLoadFilesContent', event).then(function () {
	        _this5.stopProgress();

	        var html = event.getData().html;

	        if (main_core.Type.isString(html)) {
	          main_core.Runtime.html(_this5.getFilesContainer(), html);
	        }
	      })["catch"](function () {
	        _this5.stopProgress();

	        var message = event.getData().message;

	        if (message) {
	          _this5.emit('error', {
	            message: message
	          });
	        }
	      });
	    }
	  }, {
	    key: "loadContent",
	    value: function loadContent() {
	      var _this6 = this;

	      if (this.isProgress) {
	        return;
	      }

	      this.startProgress();
	      var event = new main_core_events.BaseEvent({
	        data: {
	          commentId: this.getId()
	        }
	      });
	      this.emitAsync('onLoadContent', event).then(function () {
	        _this6.stopProgress();

	        var comment = event.getData().comment;

	        if (comment && main_core.Type.isString(comment.htmlDescription)) {
	          main_core.Runtime.html(_this6.getMainDescription(), comment.htmlDescription);

	          if (_this6.isAddExpandBlock()) {
	            _this6.getMainDescription().appendChild(_this6.getExpandBlock());
	          }

	          _this6.updateData(comment);
	        }
	      })["catch"](function () {
	        _this6.stopProgress();

	        var message = event.getData().message;

	        if (message) {
	          _this6.emit('error', {
	            message: message
	          });
	        }
	      });
	    }
	  }]);
	  return Comment;
	}(History);

	/**
	 * @abstract
	 */
	var Animation = /*#__PURE__*/function () {
	  function Animation() {
	    babelHelpers.classCallCheck(this, Animation);
	  }

	  babelHelpers.createClass(Animation, [{
	    key: "start",
	    value: function start() {}
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {}
	  }]);
	  return Animation;
	}();

	var _templateObject$6;
	var Drop = /*#__PURE__*/function (_Animation) {
	  babelHelpers.inherits(Drop, _Animation);

	  function Drop(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, Drop);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Drop).call(this, params));

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.container)) {
	        _this.item = params.item;
	        _this.container = params.container;
	        _this.insertAfter = params.insertAfter;
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Drop, [{
	    key: "start",
	    value: function start() {
	      var _this2 = this;

	      var timeout = Drop.DEFAULT_TIMEOUT;
	      return new Promise(function (resolve) {
	        if (!_this2.item || !_this2.container) {
	          resolve();
	        }

	        setTimeout(function () {
	          _this2.createGhost(_this2.item.render(), resolve);
	        }, timeout);
	      });
	    }
	  }, {
	    key: "createGhost",
	    value: function createGhost(node, onFinish) {
	      node.style.position = "absolute";
	      node.style.width = this.container.offsetWidth + "px";
	      node.style.top = main_core.Dom.getPosition(this.container).top + "px";
	      node.style.left = main_core.Dom.getPosition(this.container).left + "px";
	      document.body.appendChild(node);
	      this.anchor = main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ui-item-detail-stream-section-shadow\"></div>"])));
	      main_core.Dom.prepend(this.anchor, this.container);

	      if (main_core.Type.isDomNode(this.insertAfter)) {
	        main_core.Dom.insertAfter(this.anchor, this.insertAfter);
	      }

	      this.moveGhost(node, onFinish);
	    }
	  }, {
	    key: "moveGhost",
	    value: function moveGhost(node, onFinish) {
	      var _this3 = this;

	      var anchorPosition = main_core.Dom.getPosition(this.anchor);
	      var startPosition = main_core.Dom.getPosition(this.container);
	      var movingEvent = new BX.easing({
	        duration: Drop.DURATION,
	        start: {
	          top: startPosition.top,
	          height: 0
	        },
	        finish: {
	          top: anchorPosition.top - 5,
	          height: main_core.Dom.getPosition(node).height
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	        step: function step(state) {
	          node.style.top = state.top + "px";
	          _this3.anchor.style.height = state.height + "px";
	        },
	        complete: function complete() {
	          _this3.finish(node, onFinish);
	        }
	      });
	      movingEvent.animate();
	    }
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {
	      node.style.position = "";
	      node.style.width = "";
	      node.style.height = "";
	      node.style.top = "";
	      node.style.left = "";
	      node.style.opacity = "";
	      main_core.Dom.insertAfter(node, this.anchor);
	      main_core.Dom.remove(this.anchor);
	      this.anchor = null;

	      if (main_core.Type.isFunction(onFinish)) {
	        onFinish();
	      }
	    }
	  }]);
	  return Drop;
	}(Animation);
	babelHelpers.defineProperty(Drop, "DEFAULT_TIMEOUT", 150);
	babelHelpers.defineProperty(Drop, "DURATION", 1200);

	var Pin = /*#__PURE__*/function (_Animation) {
	  babelHelpers.inherits(Pin, _Animation);

	  function Pin(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, Pin);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Pin).call(this, params));

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.anchor)) {
	        _this.item = params.item;
	        _this.anchor = params.anchor;
	        _this.startPosition = params.startPosition;
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Pin, [{
	    key: "start",
	    value: function start() {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        if (!_this2.item || !_this2.anchor) {
	          resolve();
	        }

	        _this2.node = _this2.item.render();
	        main_core.Dom.addClass(_this2.node, 'ui-item-detail-stream-section-top-fixed');
	        _this2.node.style.position = "absolute";
	        _this2.node.style.width = _this2.startPosition.width + "px";
	        var _cloneHeight = _this2.startPosition.height;
	        var _minHeight = 65;
	        var _sumPaddingContent = 18;
	        if (_cloneHeight < _sumPaddingContent + _minHeight) _cloneHeight = _sumPaddingContent + _minHeight;
	        _this2.node.style.height = _cloneHeight + "px";
	        _this2.node.style.top = _this2.startPosition.top + "px";
	        _this2.node.style.left = _this2.startPosition.left + "px";
	        _this2.node.style.zIndex = 960;
	        document.body.appendChild(_this2.node);
	        _this2._anchorPosition = main_core.Dom.getPosition(_this2.anchor);
	        var finish = {
	          top: _this2._anchorPosition.top,
	          height: _cloneHeight + 15,
	          opacity: 1
	        };

	        var _difference = _this2.startPosition.top - _this2._anchorPosition.bottom;

	        var _deepHistoryLimit = 2 * (document.body.clientHeight + _this2.startPosition.height);

	        if (_difference > _deepHistoryLimit) {
	          finish.top = _this2.startPosition.top - _deepHistoryLimit;
	          finish.opacity = 0;
	        }

	        var _duration = Math.abs(finish.top - _this2.startPosition.top) * 2;

	        _duration = _duration < Pin.DURATION ? Pin.DURATION : _duration;
	        var movingEvent = new BX.easing({
	          duration: _duration,
	          start: {
	            top: _this2.startPosition.top,
	            height: 0,
	            opacity: 1
	          },
	          finish: finish,
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            _this2.node.style.top = state.top + "px";
	            _this2.node.style.opacity = state.opacity;
	            _this2.anchor.style.height = state.height + "px";
	          },
	          complete: function complete() {
	            _this2.finish(_this2.node, resolve);
	          }
	        });
	        movingEvent.animate();
	      });
	    }
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {
	      node.style.position = "";
	      node.style.width = "";
	      node.style.height = "";
	      node.style.top = "";
	      node.style.left = "";
	      node.style.zIndex = "";
	      this.anchor.style.height = 0;
	      main_core.Dom.insertAfter(node, this.anchor);

	      if (main_core.Type.isFunction(onFinish)) {
	        onFinish();
	      }
	    }
	  }]);
	  return Pin;
	}(Animation);
	babelHelpers.defineProperty(Pin, "DURATION", 1500);

	var Show = /*#__PURE__*/function (_Animation) {
	  babelHelpers.inherits(Show, _Animation);

	  function Show(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, Show);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Show).call(this, params));

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && main_core.Type.isDomNode(params.container) && main_core.Type.isDomNode(params.insertAfter)) {
	        _this.item = params.item;
	        _this.container = params.container;
	        _this.insertAfter = params.insertAfter;
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Show, [{
	    key: "start",
	    value: function start() {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        if (!_this2.item || !_this2.container || !_this2.insertAfter) {
	          resolve();
	        }

	        main_core.Dom.insertAfter(_this2.item.render(), _this2.insertAfter);

	        _this2.expand().then(function () {
	          _this2.fadeIn().then(function () {
	            _this2.finish(_this2.item.getContainer(), resolve);
	          });
	        });
	      });
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      var _this3 = this;

	      return new Promise(function (resolve) {
	        var node = _this3.item.getContainer();

	        var position = main_core.Dom.getPosition(node);
	        node.style.height = 0;
	        node.style.opacity = 0;
	        node.style.overflow = 'hidden';
	        var show = new BX.easing({
	          duration: Show.EXPAND_DURATION,
	          start: {
	            height: 0
	          },
	          finish: {
	            height: position.height
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            node.style.height = state.height + 'px';
	          },
	          complete: resolve
	        });
	        show.animate();
	      });
	    }
	  }, {
	    key: "fadeIn",
	    value: function fadeIn() {
	      var _this4 = this;

	      return new Promise(function (resolve) {
	        _this4.item.getContainer().style.overflow = '';
	        var fadeIn = new BX.easing({
	          duration: Show.FADE_IN_DURATION,
	          start: {
	            opacity: 0
	          },
	          finish: {
	            opacity: 100
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            _this4.item.getContainer().style.opacity = state.opacity / 100;
	          },
	          complete: resolve
	        });
	        fadeIn.animate();
	      });
	    }
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {
	      this.item.getContainer().style.height = "";
	      this.item.getContainer().style.opacity = "";

	      if (main_core.Type.isFunction(onFinish)) {
	        onFinish();
	      }
	    }
	  }]);
	  return Show;
	}(Animation);
	babelHelpers.defineProperty(Show, "EXPAND_DURATION", 150);
	babelHelpers.defineProperty(Show, "FADE_IN_DURATION", 150);

	var _templateObject$7;
	var TaskComplete = /*#__PURE__*/function (_Animation) {
	  babelHelpers.inherits(TaskComplete, _Animation);

	  function TaskComplete(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, TaskComplete);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TaskComplete).call(this, params));

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.item instanceof Item && params.task instanceof Item && main_core.Type.isDomNode(params.insertAfter)) {
	        _this.item = params.item;
	        _this.task = params.task;
	        _this.insertAfter = params.insertAfter;
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(TaskComplete, [{
	    key: "start",
	    value: function start() {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        if (!_this2.item || !_this2.task || !_this2.container || !_this2.insertAfter) {
	          resolve();
	        }

	        var node = _this2.item.render();

	        var taskNode = _this2.task.getContainer();

	        var startPosition = main_core.Dom.getPosition(taskNode);
	        node.style.position = "absolute";
	        node.style.width = taskNode.offsetWidth + "px";
	        node.style.top = startPosition.top + "px";
	        node.style.left = startPosition.left + "px";
	        node.style.zIndex = "999";
	        main_core.Dom.addClass(node, 'ui-item-detail-stream-section-show');
	        document.body.appendChild(node);
	        _this2.anchor = main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ui-item-detail-stream-section-shadow\"></div>"])));
	        main_core.Dom.prepend(_this2.anchor, _this2.container);

	        if (main_core.Type.isDomNode(_this2.insertAfter)) {
	          main_core.Dom.insertAfter(_this2.anchor, _this2.insertAfter);
	        }

	        taskNode.style.height = taskNode.offsetHeight + 'px';
	        main_core.Dom.addClass(taskNode, 'ui-item-detail-stream-section-hide');
	        setTimeout(function () {
	          var _this3 = this;

	          var taskHeight = taskNode.offsetHeight;
	          this.anchor.style.height = taskHeight + "px";
	          main_core.Dom.remove(taskNode);
	          main_core.Dom.removeClass(node, 'ui-item-detail-stream-section-show');
	          var movingEvent = new BX.easing({
	            duration: 800,
	            start: {
	              top: main_core.Dom.getPosition(node).top,
	              height: taskHeight
	            },
	            finish: {
	              top: main_core.Dom.getPosition(this.anchor).top,
	              height: main_core.Dom.getPosition(node).height
	            },
	            transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	            step: function step(state) {
	              node.style.top = state.top + "px";
	              _this3.anchor.style.height = state.height + "px";
	            },
	            complete: function complete() {
	              _this3.finish(node, resolve);
	            }
	          });
	          movingEvent.animate();
	        }.bind(_this2), 200);
	      });
	    }
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {
	      node.style.position = "";
	      node.style.width = "";
	      node.style.top = "";
	      node.style.left = "";
	      node.style.zIndex = "";
	      main_core.Dom.insertAfter(node, this.anchor);
	      main_core.Dom.remove(this.anchor);
	      this.anchor = null;

	      if (main_core.Type.isFunction(onFinish)) {
	        onFinish();
	      }
	    }
	  }]);
	  return TaskComplete;
	}(Animation);
	babelHelpers.defineProperty(TaskComplete, "DURATION", 1200);

	var Hide = /*#__PURE__*/function (_Animation) {
	  babelHelpers.inherits(Hide, _Animation);

	  function Hide(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, Hide);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Hide).call(this, params));

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isDomNode(params.node)) {
	        _this.node = params.node;
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Hide, [{
	    key: "start",
	    value: function start() {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        if (!_this2.node) {
	          resolve();
	        }

	        var node = _this2.node;
	        var wrapperPosition = main_core.Dom.getPosition(node);
	        var hideEvent = new BX.easing({
	          duration: Hide.DURATION,
	          start: {
	            height: wrapperPosition.height,
	            opacity: 1,
	            marginBottom: 15
	          },
	          finish: {
	            height: 0,
	            opacity: 0,
	            marginBottom: 0
	          },
	          transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	          step: function step(state) {
	            if (node) {
	              node.style.height = state.height + "px";
	              node.style.opacity = state.opacity;
	              node.style.marginBottom = state.marginBottom;
	            }
	          },
	          complete: function complete() {
	            _this2.finish(node, resolve);
	          }
	        });
	        hideEvent.animate();
	      });
	    }
	  }, {
	    key: "finish",
	    value: function finish(node, onFinish) {
	      main_core.Dom.remove(node);

	      if (main_core.Type.isFunction(onFinish)) {
	        onFinish();
	      }
	    }
	  }]);
	  return Hide;
	}(Animation);
	babelHelpers.defineProperty(Hide, "DURATION", 1000);

	function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }

	function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }

	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }

	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var Queue = /*#__PURE__*/function () {
	  function Queue() {
	    babelHelpers.classCallCheck(this, Queue);
	  }

	  babelHelpers.createClass(Queue, null, [{
	    key: "add",
	    value: function add(animation) {
	      _classStaticPrivateFieldSpecGet(Queue, Queue, _items).push(animation);

	      return Queue;
	    }
	  }, {
	    key: "run",
	    value: function run() {
	      if (_classStaticPrivateFieldSpecGet(Queue, Queue, _isRunning)) {
	        return;
	      }
	      /** @var Animation animation */


	      var animations = _classStaticPrivateFieldSpecGet(Queue, Queue, _items).shift();

	      if (!animations) {
	        return;
	      }

	      if (!main_core.Type.isArray(animations)) {
	        animations = [animations];
	      }

	      _classStaticPrivateFieldSpecSet(Queue, Queue, _isRunning, true);

	      var promises = [];
	      animations.forEach(function (animation) {
	        if (animation instanceof Animation) {
	          promises.push(animation.start());
	        }
	      });
	      Promise.all(promises).then(function () {
	        _classStaticPrivateFieldSpecSet(Queue, Queue, _isRunning, false);

	        Queue.run();
	      });
	    }
	  }]);
	  return Queue;
	}();
	var _items = {
	  writable: true,
	  value: []
	};
	var _isRunning = {
	  writable: true,
	  value: false
	};

	var _templateObject$8, _templateObject2$4, _templateObject3$4, _templateObject4$4, _templateObject5$4, _templateObject6$4, _templateObject7$2, _templateObject8$1, _templateObject9$1, _templateObject10$1, _templateObject11, _templateObject12;
	/**
	 * @mixes EventEmitter
	 * @memberOf BX.UI.Timeline
	 */

	var Stream = /*#__PURE__*/function () {
	  function Stream(params) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, Stream);
	    this.users = new Map();
	    this.eventIds = new Set();
	    this.pinnedItems = [];
	    this.tasks = [];
	    this.items = [];
	    this.editors = new Map();
	    this.layout = {};
	    this.dateSeparators = new Map();
	    this.nameFormat = params.nameFormat;
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.Timeline.Stream');
	    this.initItemClasses(params.itemClasses);
	    this.currentPage = 1;

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isNumber(params.pageSize)) {
	        this.pageSize = params.pageSize;
	      }

	      if (!this.pageSize || this.pageSize <= 0) {
	        this.pageSize = 20;
	      }

	      this.addUsers(params.users);

	      if (main_core.Type.isArray(params.items)) {
	        params.items.forEach(function (data) {
	          var item = _this.createItem(data);

	          if (item) {
	            _this.addItem(item);
	          }
	        });
	      }

	      if (main_core.Type.isArray(params.tasks)) {
	        this.initTasks(params.tasks);
	      }

	      if (main_core.Type.isArray(params.editors)) {
	        params.editors.forEach(function (editor) {
	          if (editor instanceof Editor) {
	            _this.editors.set(editor.getId(), editor);
	          }
	        });
	      }
	    }

	    this.bindEvents();
	    this.progress = false;
	    this.emit('onAfterInit', {
	      stream: this
	    });
	  }

	  babelHelpers.createClass(Stream, [{
	    key: "initTasks",
	    value: function initTasks(tasks) {
	      var _this2 = this;

	      this.tasks = [];
	      tasks.forEach(function (data) {
	        var task = _this2.createItem(data);

	        if (task) {
	          _this2.tasks.push(task);
	        }
	      });
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this3 = this;

	      this.onScrollHandler = main_core.Runtime.throttle(this.onScroll.bind(this), 100).bind(this);
	      main_core.Event.ready(function () {
	        if (_this3.getItems().length >= _this3.pageSize) {
	          _this3.enableLoadOnScroll();
	        }
	      });
	      Array.from(this.editors.values()).forEach(function (editor) {
	        editor.subscribe('error', function (event) {
	          _this3.onError(event.getData());
	        });
	      });
	    }
	  }, {
	    key: "initItemClasses",
	    value: function initItemClasses(itemClasses) {
	      if (itemClasses) {
	        this.itemClasses = new Map(itemClasses);
	      } else {
	        this.itemClasses = new Map();
	      }

	      this.itemClasses.set('item_create', History);
	      this.itemClasses.set('stage_change', StageChange);
	      this.itemClasses.set('fields_change', FieldsChange);
	      this.itemClasses.set('comment', Comment);
	    }
	  }, {
	    key: "createItem",
	    value: function createItem(data, itemClassName) {
	      if (!main_core.Type.isPlainObject(data.events)) {
	        data.events = {};
	      }

	      data.eventIds = this.eventIds;
	      data.events.onPinClick = this.onItemPinClick.bind(this);
	      data.events.onDelete = this.onItemDelete.bind(this);
	      data.events.onError = this.onError.bind(this);

	      if (!main_core.Type.isFunction(itemClassName)) {
	        itemClassName = this.getItemClassName(data);
	      }

	      var item = new itemClassName(data);

	      if (item instanceof Item) {
	        return item.setUserData(this.users).setTimeFormat(this.getTimeFormat()).setNameFormat(this.nameFormat);
	      }

	      return null;
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(item) {
	      if (item instanceof Item) {
	        this.items.push(item);

	        if (item.isFixed) {
	          this.pinnedItems.push(this.getPinnedItemFromItem(item));
	        }
	      }

	      return this;
	    }
	    /**
	     * @protected
	     */

	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(id) {
	      return Stream.getItemFromArray(this.getItems(), id);
	    }
	  }, {
	    key: "getPinnedItems",
	    value: function getPinnedItems() {
	      return this.pinnedItems;
	    }
	  }, {
	    key: "getPinnedItem",
	    value: function getPinnedItem(id) {
	      return Stream.getItemFromArray(this.getPinnedItems(), id);
	    }
	  }, {
	    key: "getTasks",
	    value: function getTasks() {
	      return this.tasks;
	    }
	  }, {
	    key: "getTask",
	    value: function getTask(id) {
	      return Stream.getItemFromArray(this.getTasks(), id);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-container\"></div>"])));
	      }

	      if (this.editors.size > 0) {
	        this.renderEditors();
	      }

	      if (!this.layout.content) {
	        this.layout.content = main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-content\"></div>"])));
	        this.layout.container.appendChild(this.layout.content);
	      }

	      if (!this.layout.pinnedItemsContainer) {
	        this.layout.pinnedItemsContainer = main_core.Tag.render(_templateObject3$4 || (_templateObject3$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-container-list ui-item-detail-stream-container-list-fixed\"></div>"])));
	        this.layout.content.appendChild(this.layout.pinnedItemsContainer);
	      }

	      this.renderPinnedItems();

	      if (!this.layout.tasksContainer) {
	        this.layout.tasksContainer = main_core.Tag.render(_templateObject4$4 || (_templateObject4$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-container-list\"></div>"])));
	        this.layout.content.appendChild(this.layout.tasksContainer);
	      }

	      this.renderTasks();

	      if (!this.layout.itemsContainer) {
	        this.layout.itemsContainer = main_core.Tag.render(_templateObject5$4 || (_templateObject5$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-container-list\"></div>"])));
	        this.layout.content.appendChild(this.layout.itemsContainer);
	      }

	      this.renderItems();
	      this.emit('onAfterRender');
	      return this.layout.container;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.layout.container;
	    }
	  }, {
	    key: "renderEditors",
	    value: function renderEditors() {
	      var _this4 = this;

	      if (!this.layout.container) {
	        return;
	      }

	      if (!this.layout.editors) {
	        this.layout.editorsTitle = main_core.Tag.render(_templateObject6$4 || (_templateObject6$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-new-header\"></div>"])));
	        this.layout.editorsContent = main_core.Tag.render(_templateObject7$2 || (_templateObject7$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-new-detail\"></div>"])));
	        this.layout.editors = main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ui-item-detail-stream-section-new\">\n\t\t\t\t<div class=\"ui-item-detail-stream-section-icon\"></div>\n\t\t\t\t<div class=\"ui-item-detail-stream-section-content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>"])), this.layout.editorsTitle, this.layout.editorsContent);
	        var isTitleActive = true;
	        Array.from(this.editors.values()).forEach(function (editor) {
	          _this4.layout.editorsTitle.appendChild(main_core.Tag.render(_templateObject9$1 || (_templateObject9$1 = babelHelpers.taggedTemplateLiteral(["<a class=\"ui-item-detail-stream-section-new-action ", "\">", "</a>"])), isTitleActive ? 'ui-item-detail-stream-section-new-action-active' : '', editor.getTitle()));

	          _this4.layout.editorsContent.appendChild(editor.render());

	          isTitleActive = false;
	        });
	        this.layout.container.appendChild(this.layout.editors);
	      }
	    }
	  }, {
	    key: "renderPinnedItems",
	    value: function renderPinnedItems() {
	      var _this5 = this;

	      main_core.Dom.clean(this.layout.pinnedItemsContainer);
	      this.createFixedAnchor();
	      this.getPinnedItems().forEach(function (pinnedItem) {
	        if (!pinnedItem.isRendered()) {
	          pinnedItem.render();
	        }

	        main_core.Dom.append(pinnedItem.getContainer(), _this5.layout.pinnedItemsContainer);
	      });
	    }
	  }, {
	    key: "createFixedAnchor",
	    value: function createFixedAnchor() {
	      this.fixedAnchor = main_core.Tag.render(_templateObject10$1 || (_templateObject10$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section-fixed-anchor\"></div>"])));
	      main_core.Dom.prepend(this.fixedAnchor, this.layout.pinnedItemsContainer);
	    }
	  }, {
	    key: "updateTasks",
	    value: function updateTasks(tasks) {
	      var _this6 = this;

	      if (!this.tasks) {
	        this.tasks = [];
	      }

	      var newTasks = [];
	      tasks.forEach(function (data) {
	        var task = _this6.createItem(data);

	        if (task) {
	          newTasks.push(task);

	          _this6.addUsers(data.users);
	        }
	      });
	      var deleteTasks = [];
	      this.tasks.forEach(function (task) {
	        if (!Stream.getItemFromArray(newTasks, task.getId())) {
	          deleteTasks.push(task);
	        }
	      });
	      deleteTasks.forEach(function (task) {
	        _this6.deleteItem(task);
	      });
	      var tasksTitle = this.getTasksTitle();

	      if (newTasks.length > 0) {
	        if (!tasksTitle) {
	          tasksTitle = this.renderTasksTitle();
	          this.layout.tasksContainer.appendChild(tasksTitle);
	        }

	        newTasks.forEach(function (task) {
	          if (!_this6.getTask(task.getId())) {
	            _this6.tasks.push(task);

	            Queue.add(new Show({
	              item: task,
	              container: _this6.layout.tasksContainer,
	              insertAfter: tasksTitle
	            }));
	          } else {
	            var streamTask = _this6.getTask(task.getId());

	            streamTask.setUserData(_this6.users);
	            streamTask.update(task.getDataForUpdate());
	          }
	        });
	      } else {
	        var title = this.getTasksTitle();

	        if (title) {
	          main_core.Dom.remove(title);
	          this.layout.tasksTitle = null;
	        }
	      }

	      Queue.run();
	    }
	  }, {
	    key: "renderTasks",
	    value: function renderTasks() {
	      var _this7 = this;

	      if (this.getTasks().length > 0) {
	        this.layout.tasksContainer.appendChild(this.renderTasksTitle());
	        this.getTasks().forEach(function (task) {
	          if (!task.isRendered()) {
	            main_core.Dom.append(task.render(), _this7.layout.tasksContainer);
	          }
	        });
	      } else {
	        var title = this.getTasksTitle();

	        if (title) {
	          title.parentElement.removeChild(title);
	        }
	      }
	    }
	  }, {
	    key: "getTasksTitle",
	    value: function getTasksTitle() {
	      return this.layout.tasksTitle;
	    }
	  }, {
	    key: "renderTasksTitle",
	    value: function renderTasksTitle() {
	      if (!this.layout.tasksTitle) {
	        this.layout.tasksTitle = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ui-item-detail-stream-section-planned-label\">\n\t\t\t\t<div class=\"ui-item-detail-stream-section-content\">\n\t\t\t\t\t<div class=\"ui-item-detail-stream-planned-text\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>"])), main_core.Loc.getMessage('UI_TIMELINE_TASKS_TITLE'));
	      }

	      return this.layout.tasksTitle;
	    }
	  }, {
	    key: "renderItems",
	    value: function renderItems() {
	      var _this8 = this;

	      var lastItem = this.items[this.items.length - 1];
	      this.items.forEach(function (item) {
	        item.setIsLast(item === lastItem);

	        if (!item.isRendered()) {
	          var day = _this8.constructor.getDayFromDate(item.getCreatedTime());

	          if (!_this8.getDateSeparator(day)) {
	            var dateSeparator = _this8.createDateSeparator(day);

	            main_core.Dom.append(dateSeparator, _this8.layout.itemsContainer);
	          }

	          main_core.Dom.append(item.render(), _this8.layout.itemsContainer);
	        }
	      });
	    }
	  }, {
	    key: "getDateSeparator",
	    value: function getDateSeparator(day) {
	      return this.dateSeparators.get(day);
	    }
	  }, {
	    key: "createDateSeparator",
	    value: function createDateSeparator(day) {
	      var separator = this.renderDateSeparator(day);
	      this.dateSeparators.set(day, separator);
	      return separator;
	    }
	  }, {
	    key: "renderDateSeparator",
	    value: function renderDateSeparator(day) {
	      return main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-item-detail-stream-section ui-item-detail-stream-section-history-label\">\n\t\t\t<div class=\"ui-item-detail-stream-section-content\">\n\t\t\t\t<div class=\"ui-item-detail-stream-history-text\">", "</div>\n\t\t\t</div>\n\t\t</div>"])), day);
	    }
	  }, {
	    key: "getItemClassName",
	    value: function getItemClassName(data) {
	      var itemClassName = null;

	      if (main_core.Type.isPlainObject(data) && main_core.Type.isString(data.itemClassName)) {
	        itemClassName = data.itemClassName;
	      }

	      if (itemClassName) {
	        itemClassName = main_core.Reflection.getClass(itemClassName);
	      }

	      if (!main_core.Type.isFunction(itemClassName)) {
	        if (main_core.Type.isPlainObject(data) && main_core.Type.isString(data.action)) {
	          itemClassName = this.itemClasses.get(data.action);
	        }

	        if (!itemClassName) {
	          itemClassName = History;
	        }
	      }

	      return itemClassName;
	    }
	  }, {
	    key: "insertItem",
	    value: function insertItem(item) {
	      if (!(item instanceof Item)) {
	        return this;
	      }

	      if (this.getItem(item.getId())) {
	        return this;
	      }

	      this.items.unshift(item);
	      var day = this.constructor.getDayFromDate(item.getCreatedTime());

	      if (!day) {
	        return this;
	      }

	      if (!this.getDateSeparator(day)) {
	        var separator = this.createDateSeparator(day);
	        main_core.Dom.prepend(separator, this.layout.itemsContainer);
	      }

	      Queue.add(new Drop({
	        item: item,
	        insertAfter: this.getDateSeparator(day),
	        container: this.layout.editorsContent
	      })).run();
	      return this;
	    }
	  }, {
	    key: "getTimeFormat",
	    value: function getTimeFormat() {
	      if (!this.timeFormat) {
	        var datetimeFormat = main_core.Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
	        var dateFormat = main_core.Loc.getMessage("FORMAT_DATE");
	        this.timeFormat = BX.date.convertBitrixFormat(datetimeFormat.trim().replace(dateFormat, ""));
	      }

	      return this.timeFormat;
	    }
	  }, {
	    key: "getDateTimeFormat",
	    value: function getDateTimeFormat() {
	      if (!this.dateTimeFormat) {
	        var datetimeFormat = main_core.Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
	        this.dateTimeFormat = BX.date.convertBitrixFormat(datetimeFormat);
	      }

	      return this.dateTimeFormat;
	    }
	  }, {
	    key: "startProgress",
	    value: function startProgress() {
	      this.progress = true;

	      if (!this.getLoader().isShown()) {
	        var lastItem = this.items[this.items.length - 1];

	        if (lastItem && lastItem.isRendered()) {
	          this.getLoader().show(lastItem.getContainer());
	        } else {
	          this.getLoader().show(this.layout.container);
	        }
	      }
	    }
	  }, {
	    key: "stopProgress",
	    value: function stopProgress() {
	      this.progress = false;
	      this.getLoader().hide();
	    }
	  }, {
	    key: "isProgress",
	    value: function isProgress() {
	      return this.progress === true;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          size: 150
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "enableLoadOnScroll",
	    value: function enableLoadOnScroll() {
	      main_core.Event.bind(window, 'scroll', this.onScrollHandler);
	    }
	  }, {
	    key: "disableLoadOnScroll",
	    value: function disableLoadOnScroll() {
	      main_core.Event.unbind(window, 'scroll', this.onScrollHandler);
	    }
	  }, {
	    key: "onScroll",
	    value: function onScroll() {
	      if (this.isProgress()) {
	        return;
	      }

	      var lastItem = this.items[this.items.length - 1];

	      if (!lastItem) {
	        this.disableLoadOnScroll();
	        return;
	      }

	      if (!lastItem.isRendered()) {
	        return;
	      }

	      var pos = lastItem.getContainer().getBoundingClientRect();

	      if (pos.top <= document.documentElement.clientHeight) {
	        this.emit('onScrollToTheBottom');
	      }
	    }
	  }, {
	    key: "getPinnedItemFromItem",
	    value: function getPinnedItemFromItem(item) {
	      var pinnedItem = main_core.Runtime.clone(item);

	      if (item.isRendered()) {
	        pinnedItem.clearLayout();
	      }

	      pinnedItem.setTimeFormat(this.getDateTimeFormat());
	      pinnedItem.isPinned = true;
	      return pinnedItem;
	    }
	  }, {
	    key: "onItemPinClick",
	    value: function onItemPinClick(item) {
	      if (item.isFixed) {
	        this.pinItem(item);
	      } else {
	        this.unPinItem(item);
	      }

	      this.emit('onPinClick', {
	        item: item
	      });
	    }
	  }, {
	    key: "pinItem",
	    value: function pinItem(item) {
	      var pinnedItem = this.getPinnedItem(item.getId());

	      if (!pinnedItem) {
	        this.getPinnedItems().push(this.getPinnedItemFromItem(item));
	      }

	      Queue.add(new Pin({
	        item: this.getPinnedItem(item.getId()),
	        anchor: this.fixedAnchor,
	        startPosition: main_core.Dom.getPosition(item.getContainer())
	      })).run();
	      return this;
	    }
	  }, {
	    key: "unPinItem",
	    value: function unPinItem(item) {
	      var pinnedItem = this.getPinnedItem(item.getId());

	      if (pinnedItem === item) {
	        var commonItem = this.getItem(pinnedItem.getId());

	        if (commonItem) {
	          commonItem.isFixed = false;
	          commonItem.renderPin();
	        }
	      }

	      if (pinnedItem && pinnedItem.isRendered()) {
	        Queue.add(new Hide({
	          node: pinnedItem.getContainer()
	        })).run();
	      }

	      this.pinnedItems = this.pinnedItems.filter(function (filteredItem) {
	        return filteredItem.getId() !== item.getId();
	      });
	    }
	  }, {
	    key: "onItemDelete",
	    value: function onItemDelete(item) {
	      this.deleteItem(item);
	    }
	  }, {
	    key: "deleteItem",
	    value: function deleteItem(item) {
	      var itemIndex = Stream.getItemIndexFromArray(this.items, item.getId());
	      var animations = [];

	      if (itemIndex !== null) {
	        if (item.isRendered()) {
	          var animation = new Hide({
	            node: this.getItem(item.getId()).getContainer()
	          });
	          animations.push(animation);
	        }

	        this.items.splice(itemIndex, 1);
	      }

	      itemIndex = Stream.getItemIndexFromArray(this.pinnedItems, item.getId());

	      if (itemIndex !== null) {
	        if (item.isRendered()) {
	          var _animation = new Hide({
	            node: this.getPinnedItem(item.getId()).getContainer()
	          });

	          animations.push(_animation);
	        }

	        this.pinnedItems.splice(itemIndex, 1);
	      }

	      itemIndex = Stream.getItemIndexFromArray(this.tasks, item.getId());

	      if (itemIndex !== null) {
	        var isAddHideAnimation = true;

	        if (item.completedData) {
	          var newItem = this.createItem(item.completedData);

	          if (newItem) {
	            if (!this.getItem(newItem.getId())) {
	              this.items.unshift(newItem);
	              var day = this.constructor.getDayFromDate(newItem.getCreatedTime());

	              if (day) {
	                if (!this.getDateSeparator(day)) {
	                  var separator = this.createDateSeparator(day);
	                  main_core.Dom.prepend(separator, this.layout.itemsContainer);
	                }

	                Queue.add(new TaskComplete({
	                  item: newItem,
	                  task: item,
	                  insertAfter: this.getDateSeparator(day)
	                })).run();
	                isAddHideAnimation = false;
	              }
	            }
	          }
	        }

	        if (isAddHideAnimation) {
	          animations.push(new Hide({
	            node: this.getTask(item.getId()).getContainer()
	          }));
	        }

	        this.tasks.splice(itemIndex, 1);
	      }

	      Queue.add(animations).run();
	    }
	  }, {
	    key: "onError",
	    value: function onError(_ref) {
	      var message = _ref.message;
	      this.showError(message);
	    }
	  }, {
	    key: "showError",
	    value: function showError(message) {
	      console.error(message);
	    }
	  }, {
	    key: "addUsers",
	    value: function addUsers(users) {
	      var _this9 = this;

	      if (main_core.Type.isPlainObject(users)) {
	        if (!this.users) {
	          this.users = new Map();
	        }

	        Object.keys(users).forEach(function (userId) {
	          userId = main_core.Text.toInteger(userId);

	          if (userId > 0) {
	            _this9.users.set(userId, users[userId]);
	          }
	        });
	      }
	    }
	  }, {
	    key: "addAnimation",
	    value: function addAnimation(animation) {
	      Queue.add(animation).run();
	    }
	  }], [{
	    key: "getItemFromArray",
	    value: function getItemFromArray(items, id) {
	      var result = null;
	      var key = 0;

	      while (true) {
	        if (!items[key]) {
	          break;
	        }

	        var item = items[key];

	        if (item.getId() === id) {
	          result = item;
	          break;
	        }

	        key++;
	      }

	      return result;
	    }
	  }, {
	    key: "getItemIndexFromArray",
	    value: function getItemIndexFromArray(items, id) {
	      var result = null;
	      var key = 0;

	      while (true) {
	        if (!items[key]) {
	          break;
	        }

	        var item = items[key];

	        if (item.getId() === id) {
	          result = key;
	          break;
	        }

	        key++;
	      }

	      return result;
	    }
	  }, {
	    key: "getDayFromDate",
	    value: function getDayFromDate(date) {
	      if (date instanceof Date) {
	        if (Stream.isToday(date)) {
	          return BX.date.format('today');
	        }

	        return BX.date.format('d F Y', date);
	      }

	      return null;
	    }
	  }, {
	    key: "isToday",
	    value: function isToday(date) {
	      return BX.date.format('d F Y', date) === BX.date.format('d F Y');
	    }
	  }]);
	  return Stream;
	}();

	/**
	 * @memberOf BX.UI
	 */

	var Timeline = {
	  Stream: Stream,
	  Item: Item,
	  History: History,
	  StageChange: StageChange,
	  Editor: Editor,
	  CommentEditor: CommentEditor,
	  FieldsChange: FieldsChange
	};

	exports.Timeline = Timeline;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI.Dialogs,BX.Main,BX.Event,BX));
//# sourceMappingURL=timeline.bundle.js.map
