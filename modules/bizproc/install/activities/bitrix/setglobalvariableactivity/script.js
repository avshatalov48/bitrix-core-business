/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,bp_field_type,main_popup,main_core,main_core_events,ui_entitySelector,bizproc_globals) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popup = /*#__PURE__*/new WeakMap();
	var _popupOptions = /*#__PURE__*/new WeakMap();
	var _contentData = /*#__PURE__*/new WeakMap();
	var _createContent = /*#__PURE__*/new WeakSet();
	var _onRowClick = /*#__PURE__*/new WeakSet();
	var _createDefaultButtons = /*#__PURE__*/new WeakSet();
	var Menu = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Menu, _EventEmitter);
	  function Menu(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Menu);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Menu).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createDefaultButtons);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onRowClick);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createContent);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _popupOptions, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _contentData, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Activity.SetGlobalVariable.Menu');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _popupOptions, {});
	    if (main_core.Type.isPlainObject(options.popupOptions)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _popupOptions, main_core.Runtime.clone(options.popupOptions));
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).target = options.popupOptions.target;
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).autoHide)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).autoHide = true;
	      }
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).closeByEsc)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).closeByEsc = true;
	      }
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).cacheable)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).cacheable = true;
	      }
	      if (!main_core.Type.isArray(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).buttons)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _popupOptions).buttons = _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _createDefaultButtons, _createDefaultButtons2).call(babelHelpers.assertThisInitialized(_this));
	      }
	    }
	    if (main_core.Type.isPlainObject(options.contentData)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _contentData, main_core.Runtime.clone(options.contentData));
	      if (!main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _contentData).rows)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _contentData).rows = [];
	      }
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _contentData).values = {};
	    }
	    if (main_core.Type.isPlainObject(options.events)) {
	      _this.subscribeFromOptions(options.events);
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Menu, [{
	    key: "create",
	    value: function create() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _popup)) && Object.keys(babelHelpers.classPrivateFieldGet(this, _popupOptions)).length > 0) {
	        babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	          id: babelHelpers.classPrivateFieldGet(this, _popupOptions).id,
	          bindElement: babelHelpers.classPrivateFieldGet(this, _popupOptions).target,
	          className: 'bizproc-automation-popup-set',
	          autoHide: babelHelpers.classPrivateFieldGet(this, _popupOptions).autoHide,
	          closeByEsc: babelHelpers.classPrivateFieldGet(this, _popupOptions).closeByEsc,
	          offsetLeft: babelHelpers.classPrivateFieldGet(this, _popupOptions).offsetLeft,
	          offsetTop: babelHelpers.classPrivateFieldGet(this, _popupOptions).offsetTop,
	          overlay: babelHelpers.classPrivateFieldGet(this, _popupOptions).overlay,
	          content: _classPrivateMethodGet(this, _createContent, _createContent2).call(this),
	          buttons: babelHelpers.classPrivateFieldGet(this, _popupOptions).buttons,
	          events: babelHelpers.classPrivateFieldGet(this, _popupOptions).events
	        }));
	      }
	      return this;
	    }
	  }, {
	    key: "createEmptyRow",
	    value: function createEmptyRow(index) {
	      var node = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-dropdown\" readonly=\"readonly\"></div>"])));
	      main_core.Event.bind(node, 'click', _classPrivateMethodGet(this, _onRowClick, _onRowClick2).bind(this, main_core.Text.toInteger(index)));
	      return node;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _popup))) {
	        this.create();
	        if (!babelHelpers.classPrivateFieldGet(this, _popup)) {
	          return;
	        }
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _popup).isShown()) {
	        return;
	      }
	      babelHelpers.classPrivateFieldGet(this, _popup).show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _popup))) {
	        return;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _popup).isShown()) {
	        babelHelpers.classPrivateFieldGet(this, _popup).close();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      babelHelpers.classPrivateFieldGet(this, _contentData).values = {};
	      babelHelpers.classPrivateFieldGet(this, _contentData).rows.forEach(function (row) {
	        delete row.targetNode;
	        delete row.inputNode;
	        delete row.labelNode;
	      });
	      if (!babelHelpers.classPrivateFieldGet(this, _popup)) {
	        return;
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _popup).isDestroyed()) {
	        babelHelpers.classPrivateFieldGet(this, _popup).destroy();
	      }
	      babelHelpers.classPrivateFieldSet(this, _popup, null);
	    }
	  }, {
	    key: "getRowValue",
	    value: function getRowValue(rowIndex) {
	      var _babelHelpers$classPr;
	      return (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _contentData).values[rowIndex]) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : null;
	    }
	  }, {
	    key: "setRowValue",
	    value: function setRowValue(rowIndex, value, text) {
	      if (main_core.Type.isNumber(rowIndex) && rowIndex < babelHelpers.classPrivateFieldGet(this, _contentData).rows.length && main_core.Type.isString(value)) {
	        babelHelpers.classPrivateFieldGet(this, _contentData).values[rowIndex] = value;
	        if (babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].inputNode) {
	          babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].inputNode.value = value; // ?
	          if (main_core.Type.isStringFilled(text)) {
	            babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].inputNode.innerText = main_core.Text.encode(text);
	          }
	        }
	        this.emit('onSetRowValue', new main_core_events.BaseEvent({
	          data: {
	            value: value,
	            rowIndex: main_core.Text.toInteger(rowIndex),
	            menu: this
	          }
	        }));
	      }
	    }
	  }, {
	    key: "getRowTarget",
	    value: function getRowTarget(rowIndex) {
	      var _babelHelpers$classPr2, _babelHelpers$classPr3;
	      return (_babelHelpers$classPr2 = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex]) === null || _babelHelpers$classPr3 === void 0 ? void 0 : _babelHelpers$classPr3.targetNode) !== null && _babelHelpers$classPr2 !== void 0 ? _babelHelpers$classPr2 : null;
	    }
	  }, {
	    key: "getRowInput",
	    value: function getRowInput(rowIndex) {
	      var _babelHelpers$classPr4, _babelHelpers$classPr5;
	      return (_babelHelpers$classPr4 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex]) === null || _babelHelpers$classPr5 === void 0 ? void 0 : _babelHelpers$classPr5.inputNode) !== null && _babelHelpers$classPr4 !== void 0 ? _babelHelpers$classPr4 : null;
	    }
	  }, {
	    key: "replaceRowTarget",
	    value: function replaceRowTarget(rowIndex, target, input) {
	      if (main_core.Type.isNumber(rowIndex) && rowIndex < babelHelpers.classPrivateFieldGet(this, _contentData).rows.length) {
	        if (main_core.Type.isElementNode(babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].targetNode) && main_core.Type.isElementNode(target)) {
	          main_core.Dom.replace(babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].targetNode, target);
	          babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].targetNode = target;
	          babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].inputNode = input;
	        }
	      }
	    }
	  }, {
	    key: "setRowLabel",
	    value: function setRowLabel(rowIndex, label) {
	      var _babelHelpers$classPr6;
	      if (main_core.Type.isNumber(rowIndex) && rowIndex < babelHelpers.classPrivateFieldGet(this, _contentData).rows.length && main_core.Type.isStringFilled(label) && main_core.Type.isElementNode((_babelHelpers$classPr6 = babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex]) === null || _babelHelpers$classPr6 === void 0 ? void 0 : _babelHelpers$classPr6.labelNode)) {
	        babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].labelNode.innerText = main_core.Text.encode(label);
	      }
	    }
	  }, {
	    key: "target",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _popupOptions).target;
	    }
	  }]);
	  return Menu;
	}(main_core_events.EventEmitter);
	function _createContent2() {
	  var _this2 = this;
	  var content = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<form class=\"bizproc-automation-popup-select-block\"></form>"])));
	  var _loop = function _loop(index) {
	    var _row$label;
	    var row = babelHelpers.classPrivateFieldGet(_this2, _contentData).rows[index];
	    var valueNode = '';
	    if (row.onClick) {
	      var _row$values$;
	      valueNode = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"bizproc-automation-popup-settings-dropdown\" readonly=\"readonly\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Text.encode(((_row$values$ = row.values[0]) === null || _row$values$ === void 0 ? void 0 : _row$values$.text) || ''));
	    } else {
	      valueNode = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<select class=\"bizproc-automation-popup-settings-dropdown\"></select>"])));
	      if (main_core.Type.isArrayFilled(row.values)) {
	        row.values.forEach(function (_ref) {
	          var id = _ref.id,
	            text = _ref.text;
	          main_core.Dom.append(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<option value=\"", "\">", "</option>"])), main_core.Text.encode(id), main_core.Text.encode(text)), valueNode);
	        });
	        _this2.setRowValue(0, row.values[0].id);
	      }
	      main_core.Event.bind(valueNode, 'change', function (event) {
	        _this2.setRowValue(main_core.Text.toInteger(index), event.target.value);
	      });
	    }
	    main_core.Event.bind(valueNode, 'click', _classPrivateMethodGet(_this2, _onRowClick, _onRowClick2).bind(_this2, main_core.Text.toInteger(index)));
	    var labelNode = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bizproc-automation-robot-settings-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Text.encode((_row$label = row.label) !== null && _row$label !== void 0 ? _row$label : ''));
	    row.targetNode = valueNode;
	    row.inputNode = valueNode;
	    row.labelNode = labelNode;
	    main_core.Dom.append(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"bizproc-automation-popup-settings\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t"])), labelNode, valueNode), content);
	  };
	  for (var index in babelHelpers.classPrivateFieldGet(this, _contentData).rows) {
	    _loop(index);
	  }
	  return content;
	}
	function _onRowClick2(rowIndex) {
	  var _babelHelpers$classPr7;
	  if (main_core.Type.isFunction((_babelHelpers$classPr7 = babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex]) === null || _babelHelpers$classPr7 === void 0 ? void 0 : _babelHelpers$classPr7.onClick)) {
	    var event = new main_core_events.BaseEvent({
	      data: {
	        menu: this
	      }
	    });
	    event.setTarget(babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].targetNode);
	    babelHelpers.classPrivateFieldGet(this, _contentData).rows[rowIndex].onClick.call(this, event);
	  }
	}
	function _createDefaultButtons2() {
	  return [new main_popup.PopupWindowButton({
	    text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
	    className: 'webform-button webform-button-create',
	    events: {
	      click: function () {
	        var event = new main_core_events.BaseEvent({
	          data: {
	            menu: this,
	            values: babelHelpers.classPrivateFieldGet(this, _contentData).values,
	            target: babelHelpers.classPrivateFieldGet(this, _popupOptions).target
	          }
	        });
	        this.emit('onApplyChangesClick', event);
	        this.close();
	      }.bind(this)
	    }
	  }), new main_popup.PopupWindowButtonLink({
	    text: main_core.Loc.getMessage('BIZPROC_AUTOMATION_CMP_CANCEL'),
	    className: 'popup-window-button-link',
	    events: {
	      click: function () {
	        this.emit('onDiscardChangesClick', new main_core_events.BaseEvent({}));
	        this.close();
	      }.bind(this)
	    }
	  })];
	}

	var _templateObject$1;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _options = /*#__PURE__*/new WeakMap();
	var _extraOptions = /*#__PURE__*/new WeakMap();
	var _items = /*#__PURE__*/new WeakMap();
	var _itemCreateContext = /*#__PURE__*/new WeakMap();
	var _dialog = /*#__PURE__*/new WeakMap();
	var _getRecentTabStubOptions = /*#__PURE__*/new WeakSet();
	var _getSearchTabStubOptions = /*#__PURE__*/new WeakSet();
	var _getSearchOptions = /*#__PURE__*/new WeakSet();
	var _onCreateGlobalsClick = /*#__PURE__*/new WeakSet();
	var _onAfterCreateGlobals = /*#__PURE__*/new WeakSet();
	var _getAvailableTypes = /*#__PURE__*/new WeakSet();
	var Selector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Selector, _EventEmitter);
	  function Selector() {
	    var _this;
	    var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    var options = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, Selector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Selector).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getAvailableTypes);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onAfterCreateGlobals);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onCreateGlobalsClick);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getSearchOptions);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getSearchTabStubOptions);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getRecentTabStubOptions);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _options, {
	      writable: true,
	      value: {
	        width: 480,
	        height: 300,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        showAvatars: false,
	        compactView: true,
	        tagSelectorOptions: {
	          textBoxWidth: 400
	        },
	        targetNode: null,
	        events: {},
	        recentTabOptions: {},
	        searchTabOptions: {},
	        searchOptions: {}
	      }
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _extraOptions, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _items, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _itemCreateContext, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _dialog, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Bizproc.Activity.SetGlobalVariable.Selector');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _items, main_core.Type.isArrayFilled(items) ? items : []);
	    if (main_core.Type.isPlainObject(options)) {
	      if (main_core.Type.isElementNode(options.target)) {
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _options).targetNode = options.target;
	      }
	      if (options.showStubs === true) {
	        babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _extraOptions, {
	          recentTabOptions: {
	            stub: true,
	            icon: '',
	            stubOptions: _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getRecentTabStubOptions, _getRecentTabStubOptions2).call(babelHelpers.assertThisInitialized(_this), options.objectName)
	          },
	          searchTabOptions: {
	            stub: true,
	            stubOptions: _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getSearchTabStubOptions, _getSearchTabStubOptions2).call(babelHelpers.assertThisInitialized(_this), options.objectName)
	          },
	          searchOptions: {
	            allowCreateItem: true,
	            footerOptions: _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getSearchOptions, _getSearchOptions2).call(babelHelpers.assertThisInitialized(_this), options.objectName)
	          }
	        });
	      }
	      if (main_core.Type.isPlainObject(options.itemCreateContext)) {
	        babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _itemCreateContext, options.itemCreateContext);
	      }
	      if (main_core.Type.isPlainObject(options.events) && Object.keys(options.events).length > 0) {
	        _this.subscribeFromOptions(options.events);
	      }
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Selector, [{
	    key: "create",
	    value: function create() {
	      var _this2 = this;
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _dialog))) {
	        var options = babelHelpers.classPrivateFieldGet(this, _options);
	        if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _extraOptions))) {
	          options = Object.assign(options, babelHelpers.classPrivateFieldGet(this, _extraOptions));
	        }
	        options.items = babelHelpers.classPrivateFieldGet(this, _items);
	        options.events = {
	          'Item:onBeforeSelect': function (event) {
	            var dialogItem = event.data.item;
	            this.emit('onBeforeSelect', new main_core_events.BaseEvent({
	              data: {
	                item: dialogItem
	              }
	            }));
	          }.bind(this),
	          onHide: function onHide() {
	            return _this2.destroy();
	          },
	          'Search:onItemCreateAsync': function (event) {
	            var _this3 = this;
	            return new Promise(function (resolve) {
	              var query = event.getData().searchQuery.query;
	              _classPrivateMethodGet$1(_this3, _onCreateGlobalsClick, _onCreateGlobalsClick2).call(_this3, query, resolve);
	            });
	          }.bind(this)
	        };
	        babelHelpers.classPrivateFieldSet(this, _dialog, new ui_entitySelector.Dialog(options));
	        if (babelHelpers.classPrivateFieldGet(this, _items).length <= 0) {
	          var _options$searchOption, _options$searchOption2;
	          var footer = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-selector-footer-link ui-selector-footer-link-add\" style=\"border: none\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"])), main_core.Text.encode((_options$searchOption = (_options$searchOption2 = options.searchOptions.footerOptions) === null || _options$searchOption2 === void 0 ? void 0 : _options$searchOption2.label) !== null && _options$searchOption !== void 0 ? _options$searchOption : ''));
	          main_core.Event.bind(footer, 'click', _classPrivateMethodGet$1(this, _onCreateGlobalsClick, _onCreateGlobalsClick2).bind(this));
	          babelHelpers.classPrivateFieldGet(this, _dialog).setFooter(footer);
	        }
	      }
	      return this;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _dialog))) {
	        this.create();
	      }
	      babelHelpers.classPrivateFieldGet(this, _dialog).show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _dialog))) {
	        return;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _dialog).isOpen()) {
	        babelHelpers.classPrivateFieldGet(this, _dialog).hide();
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _dialog))) {
	        return;
	      }
	      babelHelpers.classPrivateFieldGet(this, _dialog).destroy();
	      babelHelpers.classPrivateFieldSet(this, _dialog, null);
	    }
	  }]);
	  return Selector;
	}(main_core_events.EventEmitter);
	function _getRecentTabStubOptions2(objectName) {
	  if (!main_core.Type.isStringFilled(objectName)) {
	    return {};
	  }
	  if (objectName === 'GlobalVar') {
	    return {
	      title: main_core.Loc.getMessage('BPSGVA_GVARIABLE_NO_EXIST'),
	      subtitle: main_core.Loc.getMessage('BPSGVA_CREATE_GVARIABLE_QUESTION'),
	      arrow: true
	    };
	  }
	  if (objectName === 'GlobalConst') {
	    return {
	      title: main_core.Loc.getMessage('BPSGVA_GCONSTANT_NO_EXIST'),
	      subtitle: main_core.Loc.getMessage('BPSGVA_CREATE_GCONSTANT_QUESTION'),
	      arrow: true
	    };
	  }
	  return {};
	}
	function _getSearchTabStubOptions2(objectName) {
	  if (!main_core.Type.isStringFilled(objectName)) {
	    return {};
	  }
	  if (objectName === 'GlobalVar') {
	    return {
	      title: main_core.Loc.getMessage('BPSGVA_GVARIABLE_NOT_FOUND'),
	      subtitle: main_core.Loc.getMessage('BPSGVA_CREATE_GVARIABLE_QUESTION'),
	      arrow: true
	    };
	  }
	  if (objectName === 'GlobalConst') {
	    return {
	      title: main_core.Loc.getMessage('BPSGVA_GCONSTANT_NOT_FOUND'),
	      subtitle: main_core.Loc.getMessage('BPSGVA_CREATE_GCONSTANT_QUESTION'),
	      arrow: true
	    };
	  }
	  return {};
	}
	function _getSearchOptions2(objectName) {
	  if (!main_core.Type.isStringFilled(objectName)) {
	    return {};
	  }
	  if (objectName === 'GlobalVar') {
	    return {
	      label: main_core.Loc.getMessage('BPSGVA_CREATE_GVARIABLE')
	    };
	  }
	  if (objectName === 'GlobalConst') {
	    return {
	      label: main_core.Loc.getMessage('BPSGVA_CREATE_GCONSTANT')
	    };
	  }
	  return {};
	}
	function _onCreateGlobalsClick2(query, resolve) {
	  var _this4 = this;
	  if (!main_core.Type.isStringFilled(query)) {
	    query = '';
	  }
	  var visibility = babelHelpers.classPrivateFieldGet(this, _itemCreateContext).visibility;
	  var context = {
	    visibility: visibility.slice(visibility.indexOf(':') + 1),
	    availableTypes: _classPrivateMethodGet$1(this, _getAvailableTypes, _getAvailableTypes2).call(this, babelHelpers.classPrivateFieldGet(this, _itemCreateContext).type)
	  };
	  bizproc_globals.Globals.Manager.Instance.createGlobals(babelHelpers.classPrivateFieldGet(this, _itemCreateContext).mode, babelHelpers.classPrivateFieldGet(this, _itemCreateContext).signedDocumentType, query, context).then(function (slider) {
	    var newContext = {
	      'objectName': babelHelpers.classPrivateFieldGet(_this4, _itemCreateContext).objectName,
	      'visibility': babelHelpers.classPrivateFieldGet(_this4, _itemCreateContext).visibility,
	      'index': babelHelpers.classPrivateFieldGet(_this4, _itemCreateContext).index
	    };
	    _classPrivateMethodGet$1(_this4, _onAfterCreateGlobals, _onAfterCreateGlobals2).call(_this4, slider, newContext);
	    if (main_core.Type.isFunction(resolve)) {
	      resolve();
	    }
	  });
	}
	function _onAfterCreateGlobals2(slider, context) {
	  var info = slider.getData().entries();
	  var keys = Object.keys(info);
	  if (keys.length <= 0) {
	    return;
	  }
	  var id = keys[0];
	  var property = main_core.Runtime.clone(info[keys[0]]);
	  property.Multiple = property.Multiple === 'Y';
	  var newDialogItem = {
	    entityId: 'bp',
	    tabs: 'recents',
	    title: property.Name,
	    id: '{=' + context.objectName + ':' + id + '}',
	    customData: {
	      groupId: context.objectName + ':' + property['Visibility'],
	      property: property,
	      title: property['Name']
	    }
	  };
	  var availableTypes = _classPrivateMethodGet$1(this, _getAvailableTypes, _getAvailableTypes2).call(this, babelHelpers.classPrivateFieldGet(this, _itemCreateContext).type);
	  if (newDialogItem.customData.groupId === context.visibility && availableTypes.includes(property.Type)) {
	    babelHelpers.classPrivateFieldGet(this, _dialog).setFooter(null);
	    babelHelpers.classPrivateFieldGet(this, _dialog).addItem(newDialogItem);
	  }
	  this.emit('onAfterCreate', new main_core_events.BaseEvent({
	    data: {
	      item: newDialogItem
	    }
	  }));
	}
	function _getAvailableTypes2(baseType) {
	  if (baseType === 'double') {
	    return ['int', 'double'];
	  }
	  if (baseType === 'datetime') {
	    return ['date', 'datetime'];
	  }
	  if (['date', 'int', 'user'].includes(baseType)) {
	    return baseType;
	  }
	  return ['string', 'text', 'select', 'bool', 'int', 'double', 'date', 'datetime', 'user'];
	}

	var _templateObject$2, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7$1, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18, _templateObject19;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _formatTitle = /*#__PURE__*/new WeakSet();
	var _parseSystemExpression = /*#__PURE__*/new WeakSet();
	var _onMenuRowVariableValuesClick = /*#__PURE__*/new WeakSet();
	var _getObjectName = /*#__PURE__*/new WeakSet();
	var _onMenuVariableSetRowValue = /*#__PURE__*/new WeakSet();
	var _onMenuVariableApplyChangesClick = /*#__PURE__*/new WeakSet();
	var _replaceTitle = /*#__PURE__*/new WeakSet();
	var _setHiddenValue = /*#__PURE__*/new WeakSet();
	var _clearRelatedParameter = /*#__PURE__*/new WeakSet();
	var _addEmptyRelatedParameter = /*#__PURE__*/new WeakSet();
	var _onMenuRowParameterValuesClick = /*#__PURE__*/new WeakSet();
	var _onMenuParameterSetRowValue = /*#__PURE__*/new WeakSet();
	var _onMenuParameterApplyChangesClick = /*#__PURE__*/new WeakSet();
	var _onBeforeSelectItemInSelector = /*#__PURE__*/new WeakSet();
	var _onAfterCreateItemInSelector = /*#__PURE__*/new WeakSet();
	var SetGlobalVariableActivity = /*#__PURE__*/function () {
	  function SetGlobalVariableActivity(_options) {
	    babelHelpers.classCallCheck(this, SetGlobalVariableActivity);
	    _classPrivateMethodInitSpec$2(this, _onAfterCreateItemInSelector);
	    _classPrivateMethodInitSpec$2(this, _onBeforeSelectItemInSelector);
	    _classPrivateMethodInitSpec$2(this, _onMenuParameterApplyChangesClick);
	    _classPrivateMethodInitSpec$2(this, _onMenuParameterSetRowValue);
	    _classPrivateMethodInitSpec$2(this, _onMenuRowParameterValuesClick);
	    _classPrivateMethodInitSpec$2(this, _addEmptyRelatedParameter);
	    _classPrivateMethodInitSpec$2(this, _clearRelatedParameter);
	    _classPrivateMethodInitSpec$2(this, _setHiddenValue);
	    _classPrivateMethodInitSpec$2(this, _replaceTitle);
	    _classPrivateMethodInitSpec$2(this, _onMenuVariableApplyChangesClick);
	    _classPrivateMethodInitSpec$2(this, _onMenuVariableSetRowValue);
	    _classPrivateMethodInitSpec$2(this, _getObjectName);
	    _classPrivateMethodInitSpec$2(this, _onMenuRowVariableValuesClick);
	    _classPrivateMethodInitSpec$2(this, _parseSystemExpression);
	    _classPrivateMethodInitSpec$2(this, _formatTitle);
	    babelHelpers.defineProperty(this, "rowIndex", -1);
	    babelHelpers.defineProperty(this, "numberOfTypes", 9);
	    if (main_core.Type.isPlainObject(_options)) {
	      var _options$constants, _options$documentFiel;
	      this.isRobot = _options.isRobot;
	      this.documentType = _options.documentType;
	      this.signedDocumentType = _options.signedDocumentType;
	      this.variables = _options.variables;
	      this.constants = (_options$constants = _options.constants) !== null && _options$constants !== void 0 ? _options$constants : {};
	      this.documentFields = (_options$documentFiel = _options.documentFields) !== null && _options$documentFiel !== void 0 ? _options$documentFiel : {};
	      this.currentValues = _options.currentValues;
	      this.visibilityMessages = _options.visibilityMessages;
	      this.formName = _options.formName;
	      this.addRowTable = _options.addRowTable;
	    }
	  }
	  babelHelpers.createClass(SetGlobalVariableActivity, [{
	    key: "init",
	    value: function init() {
	      this.initAvailableOptions();
	      var addAssignmentExpression = this.isRobot ? 'addAssignmentExpressionRobot' : 'addAssignmentExpressionDesigner';
	      if (Object.keys(this.currentValues).length <= 0) {
	        this[addAssignmentExpression]();
	      }
	      for (var variableExpression in this.currentValues) {
	        this[addAssignmentExpression](variableExpression, this.currentValues[variableExpression]);
	      }
	      if (this.isRobot) ; else {
	        this.addExpressionButtonDesigner();
	      }
	    } // region check visibility
	  }, {
	    key: "isGVariableVisibility",
	    value: function isGVariableVisibility(visibility) {
	      return visibility.startsWith(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME));
	    }
	  }, {
	    key: "isGConstantVisibility",
	    value: function isGConstantVisibility(visibility) {
	      return visibility.startsWith(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_CONST_OBJECT_NAME));
	    }
	  }, {
	    key: "isDocumentVisibility",
	    value: function isDocumentVisibility(visibility) {
	      return visibility.startsWith(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _DOCUMENT_OBJECT_NAME));
	    }
	  }, {
	    key: "isHelperVisibility",
	    value: function isHelperVisibility(visibility) {
	      return visibility.startsWith(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME));
	    } // endregion
	    // region options
	  }, {
	    key: "initAvailableOptions",
	    value: function initAvailableOptions() {
	      this.availableOptions = this.getAvailableOptions();
	      this.availableOptionsByGroupId = this.getAvailableOptionsByGroup();
	    }
	  }, {
	    key: "getAvailableOptions",
	    value: function getAvailableOptions() {
	      var options = new Map();
	      this.fillOptions(this.variables, options);
	      this.fillOptions(this.constants, options);
	      this.fillOptions(this.documentFields, options);
	      options.set('variable', {
	        id: '',
	        title: main_core.Loc.getMessage('BPSGVA_VARIABLE'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME),
	          title: main_core.Loc.getMessage('BPSGVA_VARIABLE')
	        }
	      });
	      options.set('parameter', {
	        id: '',
	        title: main_core.Loc.getMessage('BPSGVA_PARAMETER'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME),
	          title: main_core.Loc.getMessage('BPSGVA_PARAMETER')
	        }
	      });
	      options.set('clear', {
	        id: '',
	        title: main_core.Loc.getMessage('BPSGVA_CLEAR'),
	        customData: {
	          property: {
	            Type: 'string',
	            Multiple: false
	          },
	          groupId: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME),
	          title: main_core.Loc.getMessage('BPSGVA_CLEAR')
	        }
	      });
	      return options;
	    }
	  }, {
	    key: "fillOptions",
	    value: function fillOptions(source, options) {
	      var optionId, optionProperty, optionsSource;
	      for (var groupName in source) {
	        optionsSource = source[groupName];
	        if (optionsSource['children']) {
	          optionsSource = optionsSource['children'];
	        }
	        for (var i in optionsSource) {
	          optionId = optionsSource[i]['id'];
	          optionProperty = optionsSource[i];
	          options.set(optionId, optionProperty);
	        }
	      }
	    }
	  }, {
	    key: "getAvailableOptionsByGroup",
	    value: function getAvailableOptionsByGroup() {
	      var options = new Map();
	      this.fillOptionsByGroupWithGlobals(this.variables, options, _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME));
	      this.fillOptionsByGroupWithGlobals(this.constants, options, _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_CONST_OBJECT_NAME));
	      var items = [];
	      for (var i in this.documentFields) {
	        items.push(this.documentFields[i]);
	      }
	      options.set(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _DOCUMENT_OBJECT_NAME) + ':' + _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _DOCUMENT_OBJECT_NAME), items);
	      return options;
	    }
	  }, {
	    key: "fillOptionsByGroupWithGlobals",
	    value: function fillOptionsByGroupWithGlobals(source, options, topGroupName) {
	      for (var subGroupName in source) {
	        var key = topGroupName + ':' + subGroupName;
	        options.set(key, source[subGroupName]);
	      }
	    } // endregion
	  }, {
	    key: "addAssignmentExpressionRobot",
	    value: function addAssignmentExpressionRobot(variableId, values) {
	      if (main_core.Type.isString(values)) {
	        values = {
	          0: values
	        };
	      }
	      var incomingData = {
	        variable: variableId,
	        values: values
	      };
	      this.modifyIncomingDataRobot(incomingData);
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var rowInputs = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\"></div>"])), _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _RESULTS_ID) + this.rowIndex);
	      var parameterRowWrapper = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tclass=\"bizproc-automation-popup-settings-title\"\n\t\t\t\tdata-role=\"", "\"\n\t\t\t></div>\n\t\t"])), main_core.Text.encode(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _PARAMETER_ROLE) + this.rowIndex));
	      if (incomingData.values.length <= 0) {
	        var option = main_core.Runtime.clone(this.getOptionPropertiesRobot('clear'));
	        option['multiple'] = incomingData.variable.property.Multiple;
	        option['type'] = incomingData.variable.property.Type;
	        option['inputIndex'] = 0;
	        main_core.Dom.append(this.createParameterRowRobot(this.rowIndex, option, rowInputs), parameterRowWrapper);
	      }
	      for (var i in incomingData.values) {
	        var _option = main_core.Runtime.clone(incomingData.values[i]);
	        _option['multiple'] = incomingData.variable.property.Multiple;
	        _option['type'] = incomingData.variable.property.Type;
	        _option['inputIndex'] = i;
	        main_core.Dom.append(this.createParameterRowRobot(this.rowIndex, _option, rowInputs), parameterRowWrapper);
	      }
	      if (incomingData.variable.property.Multiple && incomingData.variable.property.Type !== 'user') {
	        var inputIndex = incomingData.values.length <= 0 ? 1 : incomingData.values.length;
	        main_core.Dom.append(this.createAddParameterRowRobot(this.rowIndex, inputIndex), parameterRowWrapper);
	      }
	      var newRow = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings\">\n\t\t\t\t<div\n\t\t\t\t\tclass=\"bizproc-automation-popup-settings bizproc-automation-popup-settings-text\"\n\t\t\t\t\tstyle=\"display: flex; align-items: flex-start\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.createVariableRowRobot(incomingData.variable, rowInputs), parameterRowWrapper, rowInputs);
	      main_core.Dom.append(newRow, addRowTable);
	    }
	  }, {
	    key: "modifyIncomingDataRobot",
	    value: function modifyIncomingDataRobot(incomingData) {
	      var option = this.getOptionPropertiesRobot(incomingData.variable);
	      if (incomingData.variable === undefined || option.groupId === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text') {
	        incomingData.variable = main_core.Runtime.clone(this.getOptionPropertiesRobot('variable'));
	        var valueOption = main_core.Runtime.clone(this.getOptionPropertiesRobot('parameter'));
	        incomingData.values = [{
	          id: valueOption.id,
	          title: valueOption.title
	        }];
	        return;
	      }
	      var valuesOptions = [];
	      switch (option.property.Type) {
	        case 'select':
	          valuesOptions = this.getIncomingValuesSelect(incomingData);
	          break;
	        case 'bool':
	          valuesOptions = this.getIncomingValuesBool(incomingData);
	          break;
	        default:
	          for (var i in incomingData.values) {
	            var _valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
	            if (incomingData.values[i] === '') {
	              _valueOption = this.getOptionPropertiesRobot('clear');
	            }
	            valuesOptions.push({
	              id: _valueOption.id,
	              title: _valueOption.title
	            });
	          }
	      }
	      incomingData.variable = main_core.Runtime.clone(option);
	      incomingData.values = valuesOptions;
	    }
	  }, {
	    key: "getOptionPropertiesRobot",
	    value: function getOptionPropertiesRobot(optionId) {
	      var option = this.availableOptions.get(optionId);
	      if (main_core.Type.isUndefined(option)) {
	        return this.getDefaultOptionProperties(optionId);
	      }
	      return this.getShortOptionProperties(option);
	    }
	  }, {
	    key: "getDefaultOptionProperties",
	    value: function getDefaultOptionProperties(optionId) {
	      return {
	        id: optionId,
	        property: {
	          Type: 'string',
	          Multiple: false
	        },
	        groupId: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text',
	        title: optionId
	      };
	    }
	  }, {
	    key: "getShortOptionProperties",
	    value: function getShortOptionProperties(option) {
	      return {
	        id: option.id,
	        property: option.customData.property,
	        groupId: option.customData.groupId,
	        title: option.customData.title
	      };
	    }
	  }, {
	    key: "getIncomingValuesSelect",
	    value: function getIncomingValuesSelect(incomingData) {
	      var option = this.getOptionPropertiesRobot(incomingData.variable);
	      var valuesOptions = [];
	      var title;
	      var valueOption;
	      var isExpressionOption;
	      for (var i in incomingData.values) {
	        title = main_core.Loc.getMessage('BPSGVA_CLEAR');
	        if (incomingData.values[i] !== '') {
	          valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
	          isExpressionOption = true;
	          title = valueOption.title;
	        }
	        if (option.property.Options[incomingData.values[i]] !== undefined) {
	          isExpressionOption = false;
	          title = option.property.Options[incomingData.values[i]];
	        }
	        valuesOptions.push({
	          id: incomingData.values[i],
	          title: title,
	          isExpressionOption: isExpressionOption
	        });
	      }
	      return valuesOptions;
	    }
	  }, {
	    key: "getIncomingValuesBool",
	    value: function getIncomingValuesBool(incomingData) {
	      var valuesOptions = [];
	      var title;
	      var valueOption;
	      for (var i in incomingData.values) {
	        var isExpressionOption = false;
	        switch (incomingData.values[i]) {
	          case 'Y':
	            title = main_core.Loc.getMessage('BPSGVA_BOOL_YES');
	            break;
	          case 'N':
	            title = main_core.Loc.getMessage('BPSGVA_BOOL_NO');
	            break;
	          case '':
	            title = main_core.Loc.getMessage('BPSGVA_CLEAR');
	            break;
	          default:
	            valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
	            title = valueOption.title;
	            isExpressionOption = true;
	        }
	        valuesOptions.push({
	          id: incomingData.values[i],
	          title: title,
	          isExpressionOption: isExpressionOption
	        });
	      }
	      return valuesOptions;
	    }
	  }, {
	    key: "createVariableRowRobot",
	    value: function createVariableRowRobot(variableData, rowInputs) {
	      var variableNode = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span \n\t\t\t\tclass=\"bizproc-automation-popup-settings-link setglobalvariableactivity-underline\"\n\t\t\t\tdata-role=\"", "\"\n\t\t\t\tbp_sgva_index=\"", "\"\n\t\t\t>\n\t\t\t\t", "\n\t\t\t</span>\n\t\t"])), main_core.Text.encode(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _VARIABLE_ROLE) + this.rowIndex), main_core.Text.encode(String(this.rowIndex)), main_core.Loc.getMessage('BPSGVA_VARIABLE'));
	      var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, variableData.id);
	      var isDeleted = systemExpression.groupId === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text';
	      if (isDeleted) {
	        systemExpression.title = main_core.Loc.getMessage('BPSGVA_VARIABLE');
	      }
	      _classPrivateMethodGet$2(this, _replaceTitle, _replaceTitle2).call(this, variableNode, systemExpression.title);
	      _classPrivateMethodGet$2(this, _setHiddenValue, _setHiddenValue2).call(this, variableNode, systemExpression.id, {
	        isMultiple: false,
	        inputIndex: 0,
	        isExpressionOption: false
	      }, rowInputs);
	      main_core.Event.bind(variableNode, 'click', this.onVariableSelectClickRobot.bind(this));
	      return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), variableNode);
	    }
	  }, {
	    key: "createParameterRowRobot",
	    value: function createParameterRowRobot(index, valueData, rowInputs) {
	      var _classPrivateMethodGe;
	      var parameterNode = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span \n\t\t\t\tclass=\"bizproc-automation-popup-settings-link setglobalvariableactivity-underline\"\n\t\t\t\tdata-role=\"", "\"\n\t\t\t\tbp_sgva_index=\"", "\"\n\t\t\t>\n\t\t\t</span>\n\t\t"])), main_core.Text.encode(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _PARAMETER_ROLE) + index), main_core.Text.encode(String(index)));
	      parameterNode.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME), main_core.Text.toInteger(String(valueData.inputIndex)));
	      var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, valueData.id);
	      systemExpression.title = (_classPrivateMethodGe = _classPrivateMethodGet$2(this, _formatTitle, _formatTitle2).call(this, valueData.type, valueData.title, valueData.id)) !== null && _classPrivateMethodGe !== void 0 ? _classPrivateMethodGe : valueData.title;
	      if (!main_core.Type.isStringFilled(systemExpression.title)) {
	        systemExpression.title = main_core.Loc.getMessage('BPSGVA_CLEAR');
	      }
	      _classPrivateMethodGet$2(this, _replaceTitle, _replaceTitle2).call(this, parameterNode, systemExpression.title);
	      _classPrivateMethodGet$2(this, _setHiddenValue, _setHiddenValue2).call(this, parameterNode, systemExpression.id, {
	        isMultiple: valueData.multiple,
	        inputIndex: main_core.Text.toInteger(String(valueData.inputIndex)),
	        isExpressionOption: valueData.isExpressionOption
	      }, rowInputs);
	      main_core.Event.bind(parameterNode, 'click', this.onParameterSelectClickRobot.bind(this, valueData.inputIndex));
	      return main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-automation-popup-settings-title setglobalvariableactivity-parameter-wrapper\">\n\t\t\t\t<div class=\"bizproc-automation-popup-settings-title setglobalvariableactivity-symbol-equal\"> = </div>\n\t\t\t\t<div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), parameterNode);
	    }
	  }, {
	    key: "replaceHiddenInputRobot",
	    value: function replaceHiddenInputRobot(data, rowInputs) {
	      var inputValue = data.inputValue;
	      var role = data.role + '_input';
	      var input = document.querySelectorAll('[data-role="' + role + '"]');

	      // single input
	      if (input.length >= 1 && !data.multiple) {
	        input[0].name = data.isExpressionOption ? data.role + '_text' : data.role;
	        input[0].value = data.inputValue;
	        return;
	      }

	      // multiple input
	      if (input.length >= 1 && data.multiple) {
	        var inputKeys = Object.keys(input);
	        for (var i in inputKeys) {
	          var inputIndex = input[inputKeys[i]].getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME));
	          if (data.inputIndex === inputIndex) {
	            input[i].name = data.isExpressionOption ? data.role + '_text' : data.role + '[]';
	            input[i].value = data.inputValue;
	            return;
	          }
	        }
	      }

	      // create input
	      input = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\">"])));
	      if (data.isExpressionOption) {
	        input.name = data.role + '_text';
	      } else {
	        input.name = data.multiple ? data.role + '[]' : data.role;
	      }
	      input.value = inputValue;
	      input.setAttribute('data-role', role);
	      input.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME), String(data.inputIndex));
	      main_core.Dom.append(input, rowInputs);
	    }
	  }, {
	    key: "onVariableSelectClickRobot",
	    value: function onVariableSelectClickRobot(event) {
	      var target = event.target;
	      var visibilityNames = this.getVisibilityNamesForSelect('variable');
	      var menu = new Menu({
	        popupOptions: {
	          id: target.dataset.role + '_popup',
	          target: target,
	          offsetTop: 5,
	          overlay: {
	            backgroundColor: 'transparent'
	          },
	          cacheable: false,
	          events: {
	            onClose: function onClose() {
	              return menu.destroy();
	            }
	          }
	        },
	        contentData: {
	          rows: [{
	            label: main_core.Loc.getMessage('BPSGVA_TYPE_OF_PARAMETER'),
	            values: visibilityNames
	          }, {
	            label: main_core.Loc.getMessage('BPSGVA_LIST_OF_VALUES'),
	            values: [{
	              id: 'empty',
	              text: main_core.Loc.getMessage('BPSGVA_EMPTY')
	            }],
	            onClick: _classPrivateMethodGet$2(this, _onMenuRowVariableValuesClick, _onMenuRowVariableValuesClick2).bind(this)
	          }]
	        },
	        events: {
	          'onSetRowValue': _classPrivateMethodGet$2(this, _onMenuVariableSetRowValue, _onMenuVariableSetRowValue2),
	          'onApplyChangesClick': _classPrivateMethodGet$2(this, _onMenuVariableApplyChangesClick, _onMenuVariableApplyChangesClick2).bind(this)
	        }
	      });
	      menu.create();
	      var selectedVariable = this.getVariableInputValue(target.getAttribute('data-role'));
	      var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, selectedVariable);
	      var isDeleted = systemExpression.groupId === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text';
	      if (isDeleted) {
	        systemExpression.groupId = visibilityNames[0].id;
	        systemExpression.title = main_core.Loc.getMessage('BPSGVA_EMPTY');
	      }
	      menu.setRowValue(0, systemExpression.groupId);
	      menu.setRowValue(1, selectedVariable, systemExpression.title);
	      menu.show();
	    }
	  }, {
	    key: "getVariableInputValue",
	    value: function getVariableInputValue(role) {
	      var inputRole = role + '_input';
	      var inputs = document.querySelectorAll('[data-role="' + inputRole + '"]');
	      return inputs.length >= 1 ? inputs['0'].value : '';
	    }
	  }, {
	    key: "createInputForMenuFormRobot",
	    value: function createInputForMenuFormRobot(type, index, inputValue) {
	      if (type === 'variable') {
	        var _wrapper = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-select\"></div>"])));
	        var _input = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<input class=\"bizproc-automation-popup-input\" type=\"hidden\" style=\"width: 280px\">"])));
	        main_core.Dom.append(_input, _wrapper);
	        return _wrapper;
	      }
	      var variableOption = this.getVariableOptionFromVariableInput(index);
	      var wrapper;
	      switch (variableOption.property.Type) {
	        case 'user':
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, variableOption.property, 'bp_sgva_field_input', inputValue);
	          break;
	        case 'select':
	        case 'bool':
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, {
	            Type: variableOption.property.Type,
	            Options: variableOption.property.Options
	          }, 'bp_sgva_field_input', inputValue);
	          break;
	        default:
	          wrapper = BX.Bizproc.FieldType.renderControl(this.documentType, {
	            Type: variableOption.property.Type
	          }, 'bp_sgva_field_input', variableOption.id);
	      }
	      main_core.Dom.style(wrapper, 'width', '280px');
	      var input = this.findInputInFormRobot(wrapper);
	      if (['bool', 'select'].includes(variableOption.property.Type)) {
	        if (input.value !== inputValue) {
	          var option = this.getOptionPropertiesRobot(inputValue);
	          this.resolveAdditionOptionInSelectRobot(input, option);
	        }
	      }
	      if (input) {
	        main_core.Dom.style(input, 'width', '100%');
	      }
	      return wrapper;
	    }
	  }, {
	    key: "getVariableOptionFromVariableInput",
	    value: function getVariableOptionFromVariableInput(index) {
	      var variableInput = document.querySelector('[data-role="' + _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _VARIABLE_ROLE) + index + '_input"]');
	      var variableId = variableInput ? variableInput.value : '';
	      return this.getOptionPropertiesRobot(variableId);
	    }
	  }, {
	    key: "findInputInFormRobot",
	    value: function findInputInFormRobot(form) {
	      var inputs = form.getElementsByTagName('input');
	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }
	      inputs = form.getElementsByTagName('textarea');
	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }
	      inputs = form.getElementsByTagName('select');
	      if (inputs.length >= 1) {
	        return inputs[inputs.length - 1];
	      }
	    }
	  }, {
	    key: "resolveAdditionOptionInSelectRobot",
	    value: function resolveAdditionOptionInSelectRobot(input, option) {
	      var selectOptions = input.options;
	      var opt = selectOptions[selectOptions.length - 1];
	      if (opt.getAttribute('data-role') !== 'expression') {
	        opt = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<option></option>"])));
	        opt.setAttribute('data-role', 'expression');
	        main_core.Dom.append(opt, input);
	      }
	      opt.value = option.id;
	      if (!option.customData) {
	        opt.text = option.title;
	      } else {
	        opt.text = option.customData.get('title');
	      }
	      opt.setAttribute('selected', 'selected');
	      if (!opt.selected) {
	        opt.selected = true;
	      }
	    }
	  }, {
	    key: "filterItemsInStandardMenuRobot",
	    value: function filterItemsInStandardMenuRobot(variableType, items) {
	      var filter = this.getFilterByVariableType(variableType);
	      if (filter.length === this.numberOfTypes) {
	        return items;
	      }
	      var filterItems = [];
	      for (var i in items) {
	        if (items[i].children) {
	          var filterChildrenItems = this.filterItemsInStandardMenuRobot(variableType, items[i].children);
	          if (filterChildrenItems.length >= 1) {
	            var menuItem = items[i];
	            menuItem.children = filterChildrenItems;
	            filterItems.push(menuItem);
	          }
	        } else {
	          if (filter.includes(items[i].customData.property.Type)) {
	            filterItems.push(items[i]);
	          }
	        }
	      }
	      return filterItems;
	    }
	  }, {
	    key: "getFilterByVariableType",
	    value: function getFilterByVariableType(type) {
	      switch (type) {
	        case 'double':
	          return ['int', 'double'];
	        case 'datetime':
	          return ['date', 'datetime'];
	        case 'date':
	        case 'int':
	        case 'user':
	          return [type];
	        default:
	          // this.numberOfTypes = 9
	          return ['string', 'text', 'select', 'bool', 'int', 'double', 'date', 'datetime', 'user'];
	      }
	    }
	  }, {
	    key: "getVisibilityNamesForSelect",
	    value: function getVisibilityNamesForSelect(type) {
	      var list = [];
	      var parameterTypes = this.visibilityMessages;
	      parameterTypes[_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME)] = {
	        'text': main_core.Loc.getMessage('BPSGVA_TEXT')
	      };
	      for (var topGroupName in parameterTypes) {
	        if (type === 'variable' && topGroupName !== _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME)) {
	          continue;
	        }
	        for (var subGroupName in parameterTypes[topGroupName]) {
	          list.push({
	            id: topGroupName + ':' + subGroupName,
	            text: parameterTypes[topGroupName][subGroupName]
	          });
	        }
	      }
	      return list;
	    }
	  }, {
	    key: "changeParameterExpressionRobot",
	    value: function changeParameterExpressionRobot(index, variable) {
	      var parameterNode = document.querySelector('[data-role="' + _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _PARAMETER_ROLE) + index + '"]');
	      this.deleteOldValueRowsRobot(parameterNode);
	      var rowInputs = document.getElementById(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _RESULTS_ID) + index);
	      var option = main_core.Runtime.clone(this.getOptionPropertiesRobot('parameter'));
	      option['multiple'] = variable.property.Multiple;
	      option['inputIndex'] = '0';
	      main_core.Dom.append(this.createParameterRowRobot(index, option, rowInputs), parameterNode);
	      if (variable.property.Multiple && variable.property.Type !== 'user') {
	        var inputIndex = variable.inputIndex !== '0' ? variable.inputIndex : '1';
	        main_core.Dom.append(this.createAddParameterRowRobot(index, inputIndex), parameterNode);
	      }
	    }
	  }, {
	    key: "deleteOldValueRowsRobot",
	    value: function deleteOldValueRowsRobot(node) {
	      var role = node.getAttribute('data-role');
	      node.innerHTML = '';
	      var oldInputs = document.querySelectorAll('[data-role="' + role + '_input"]');
	      for (var i in Object.keys(oldInputs)) {
	        oldInputs[i].remove();
	      }
	    }
	  }, {
	    key: "createAddParameterRowRobot",
	    value: function createAddParameterRowRobot(index, inputIndex) {
	      var addWrapper = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<div class=\"bizproc-automation-popup-settings-title\" style=\"display:flex;\"></div>"])));
	      var addExpression = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bizproc-type-control-clone-btn setglobalvariableactivity-dashed-grey setglobalvariableactivity-add-parameter\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(main_core.Loc.getMessage('BPSGVA_ADD_PARAMETER')));
	      addExpression.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME), String(index));
	      addExpression.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME), String(inputIndex));
	      main_core.Event.bind(addExpression, 'click', this.onAddParameterButtonClickRobot.bind(this));
	      main_core.Dom.append(addExpression, addWrapper);
	      return addWrapper;
	    }
	  }, {
	    key: "onAddParameterButtonClickRobot",
	    value: function onAddParameterButtonClickRobot(event) {
	      var index = event.target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	      var rowInputs = document.getElementById(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _RESULTS_ID) + index);
	      var inputIndex = event.target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME));
	      var option = main_core.Runtime.clone(this.getOptionPropertiesRobot('parameter'));
	      option['multiple'] = true;
	      option['inputIndex'] = inputIndex;
	      event.target.parentNode.before(this.createParameterRowRobot(index, option, rowInputs));
	      event.target.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME), Number(inputIndex) + 1);
	    }
	  }, {
	    key: "onParameterSelectClickRobot",
	    value: function onParameterSelectClickRobot(inputIndex, event) {
	      var target = event.target;
	      var visibilityNames = this.getVisibilityNamesForSelect('all');
	      var menu = new Menu({
	        popupOptions: {
	          id: target.dataset.role + '_popup',
	          target: target,
	          offsetTop: 5,
	          overlay: {
	            backgroundColor: 'transparent'
	          },
	          cacheable: false,
	          events: {
	            onClose: function onClose() {
	              return menu.destroy();
	            }
	          }
	        },
	        contentData: {
	          rows: [{
	            label: main_core.Loc.getMessage('BPSGVA_TYPE_OF_PARAMETER'),
	            values: visibilityNames
	          }, {
	            label: main_core.Loc.getMessage('BPSGVA_LIST_OF_VALUES'),
	            values: [{
	              id: 'empty',
	              text: main_core.Loc.getMessage('BPSGVA_EMPTY')
	            }],
	            onClick: _classPrivateMethodGet$2(this, _onMenuRowParameterValuesClick, _onMenuRowParameterValuesClick2).bind(this)
	          }]
	        },
	        events: {
	          'onSetRowValue': _classPrivateMethodGet$2(this, _onMenuParameterSetRowValue, _onMenuParameterSetRowValue2).bind(this),
	          'onApplyChangesClick': _classPrivateMethodGet$2(this, _onMenuParameterApplyChangesClick, _onMenuParameterApplyChangesClick2).bind(this)
	        }
	      });
	      menu.create();
	      var selectedParameter = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
	      var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, selectedParameter);
	      menu.setRowValue(0, systemExpression.groupId);
	      var isOwnValue = systemExpression.groupId === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text';
	      var inputValue = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
	      if (isOwnValue) {
	        var index = target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	        var secondRowTarget = this.createInputForMenuFormRobot('all', index, inputValue);
	        var input = this.findInputInFormRobot(secondRowTarget);
	        menu.replaceRowTarget(1, secondRowTarget, input);
	      }
	      menu.setRowValue(1, inputValue, isOwnValue ? '' : systemExpression.title);
	      menu.show();
	    }
	  }, {
	    key: "getParameterInputValue",
	    value: function getParameterInputValue(role, index) {
	      var inputs = document.querySelectorAll('[data-role="' + role + '"]', index);
	      var keys = Object.keys(inputs);
	      for (var i in keys) {
	        if (String(inputs[keys[i]].getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME))) === String(index)) {
	          return inputs[keys[i]].value;
	        }
	      }
	      return '';
	    }
	  }, {
	    key: "addExpressionButtonRobot",
	    value: function addExpressionButtonRobot() {
	      var buttonAdd = document.getElementById(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _ADD_BUTTON_ID));
	      buttonAdd.innerText = main_core.Loc.getMessage('BPSGVA_ADD_VARIABLE');
	      main_core.Event.bind(buttonAdd, 'click', this.addAssignmentExpressionRobot.bind(this));
	    }
	  }, {
	    key: "addExpressionButtonDesigner",
	    value: function addExpressionButtonDesigner() {
	      var _this = this;
	      var button = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<a href='#'>", "</a>"])), main_core.Loc.getMessage('BPSGVA_PD_ADD'));
	      main_core.Event.bind(button, 'click', function (event) {
	        _this.addAssignmentExpressionDesigner();
	        event.preventDefault();
	      });
	      main_core.Dom.insertAfter(button, this.addRowTable);
	    }
	  }, {
	    key: "convertFieldExpression",
	    value: function convertFieldExpression(option) {
	      if (this.isDocumentVisibility(option.groupId)) {
	        return '{{' + option.property.Name + '}}';
	      }
	      if (this.isGVariableVisibility(option.groupId)) {
	        var messages = this.visibilityMessages[_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME)];
	        var visibility = option.property.Visibility;
	        var name = option.property.Name;
	        return '{{' + messages[visibility] + ': ' + name + '}}';
	      }
	      if (this.isGConstantVisibility(option.groupId)) {
	        var _messages = this.visibilityMessages[_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_CONST_OBJECT_NAME)];
	        var _visibility = option.property.Visibility;
	        var _name = option.property.Name;
	        return '{{' + _messages[_visibility] + ': ' + _name + '}}';
	      }
	      return option.id;
	    }
	  }, {
	    key: "addAssignmentExpressionDesigner",
	    value: function addAssignmentExpressionDesigner(variable, value) {
	      var addRowTable = this.addRowTable;
	      this.rowIndex++;
	      var newRow = addRowTable.insertRow(-1);
	      newRow.id = 'delete_row_' + this.rowIndex;
	      var cellSelect = newRow.insertCell(-1);
	      var newSelect = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<select name=\"", "\"></select>"])), _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _VARIABLE_ROLE) + this.rowIndex);
	      newSelect.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME), this.rowIndex);
	      var me = this;
	      newSelect.onchange = function () {
	        me.changeFieldTypeDesigner(this.getAttribute(_classStaticPrivateFieldSpecGet(me.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME)), this.options[this.selectedIndex].value, null);
	      };
	      var objectVisibilityMessages = this.visibilityMessages[_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME)];
	      for (var visibility in objectVisibilityMessages) {
	        var optgroupLabel = objectVisibilityMessages[visibility];
	        var optgroup = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<optgroup label=\"", "\"></optgroup>"])), main_core.Text.encode(optgroupLabel));
	        var groupOptions = this.availableOptionsByGroupId.get(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME) + ':' + visibility);
	        if (!groupOptions) {
	          continue;
	        }
	        var optionNode = void 0;
	        for (var i in groupOptions) {
	          optionNode = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<option value=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</option>\n\t\t\t\t"])), main_core.Text.encode(groupOptions[i]['id']), main_core.Text.encode(groupOptions[i]['customData']['title']));
	          main_core.Dom.append(optionNode, optgroup);
	        }
	        main_core.Dom.append(optgroup, newSelect);
	      }
	      newSelect.value = variable;
	      if (newSelect.selectedIndex === -1) {
	        newSelect.selectedIndex = 0;
	      }
	      main_core.Dom.append(newSelect, cellSelect);
	      var cellSymbolEquals = newRow.insertCell(-1);
	      cellSymbolEquals.innerHTML = '=';
	      var cellValue = newRow.insertCell(-1);
	      cellValue.id = 'id_td_variable_value_' + this.rowIndex;
	      cellValue.innerHTML = '';
	      var cellDeleteRow = newRow.insertCell(-1);
	      cellDeleteRow.aligh = 'right';
	      var deleteLink = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Text.encode(main_core.Loc.getMessage('BPSGVA_PD_DELETE')));
	      var index = this.rowIndex;
	      main_core.Event.bind(deleteLink, 'click', function (event) {
	        me.deleteConditionDesigner(index);
	        event.preventDefault();
	      });
	      main_core.Dom.append(deleteLink, cellDeleteRow);
	      if (main_core.Type.isArray(value)) {
	        for (var _i in value) {
	          var item = this.getOptionPropertiesRobot(value[_i]);
	          if (item.groupId === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text') {
	            continue;
	          }
	          value[_i] = this.convertFieldExpression(item);
	        }
	      } else {
	        var _item = this.getOptionPropertiesRobot(value);
	        if (_item.groupId !== _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text') {
	          value = this.convertFieldExpression(_item);
	        }
	      }
	      if (value === undefined) {
	        value = null;
	      }
	      this.changeFieldTypeDesigner(this.rowIndex, newSelect.value, value);
	    }
	  }, {
	    key: "changeFieldTypeDesigner",
	    value: function changeFieldTypeDesigner(index, field, value) {
	      BX.showWait();
	      var valueTd = document.getElementById('id_td_variable_value_' + index);
	      var separatingSymbol = field.indexOf(':');
	      var fieldId = field;
	      if (separatingSymbol !== -1) {
	        fieldId = field.slice(separatingSymbol + 1, field.length - 1);
	      }
	      objFieldsGlobalVar.GetFieldInputControl(objFieldsGlobalVar.arDocumentFields[fieldId], value, {
	        'Field': fieldId,
	        'Form': this.formName
	      }, function (v) {
	        if (v === undefined) {
	          valueTd.innerHTML = '';
	        } else {
	          valueTd.innerHTML = v;
	          if (!main_core.Type.isUndefined(BX.Bizproc.Selector)) {
	            BX.Bizproc.Selector.initSelectors(valueTd);
	          }
	        }
	        BX.closeWait();
	      }, true);
	    }
	  }, {
	    key: "deleteConditionDesigner",
	    value: function deleteConditionDesigner(index) {
	      var addrowTable = document.getElementById(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _ROW_TABLE_ID));
	      var count = addrowTable.rows.length;
	      for (var i = 0; i < count; i++) {
	        if (addrowTable.rows[i].id !== 'delete_row_' + index) {
	          continue;
	        }
	        addrowTable.deleteRow(i);
	        break;
	      }
	    }
	  }]);
	  return SetGlobalVariableActivity;
	}();
	function _formatTitle2(type, title, inputValue) {
	  var _BX$Bizproc$FieldType;
	  var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	  var property = {
	    Type: type,
	    Options: main_core.Type.isPlainObject(options) ? options : null
	  };
	  var value = type === 'bool' ? inputValue : title;
	  if (type === 'bool' && !['Y', 'N'].includes(value)) {
	    return null;
	  }
	  return (_BX$Bizproc$FieldType = BX.Bizproc.FieldType.formatValuePrintable(property, value)) !== null && _BX$Bizproc$FieldType !== void 0 ? _BX$Bizproc$FieldType : null;
	}
	function _parseSystemExpression2(systemExpression) {
	  var option = this.availableOptions.get(systemExpression);
	  if (main_core.Type.isUndefined(option)) {
	    return {
	      id: systemExpression,
	      groupId: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text',
	      title: systemExpression
	    };
	  }
	  return {
	    id: option.id,
	    groupId: option.customData.groupId,
	    title: option.customData.title
	  };
	}
	function _onMenuRowVariableValuesClick2(event) {
	  var _this$availableOption;
	  var menu = event.getData().menu;
	  var selectedVariableType = menu.getRowValue(0);
	  var items = (_this$availableOption = this.availableOptionsByGroupId.get(selectedVariableType)) !== null && _this$availableOption !== void 0 ? _this$availableOption : [];
	  var filteredItems = this.filterItemsInStandardMenuRobot('string', items);
	  var selector = new Selector(filteredItems, {
	    target: event.getTarget(),
	    showStubs: true,
	    //this.isGVariableVisibility(selectedVariableType) || this.isGConstantVisibility(selectedVariableType),
	    objectName: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME),
	    //this.#getObjectName(selectedVariableType),
	    events: {
	      'onBeforeSelect': _classPrivateMethodGet$2(this, _onBeforeSelectItemInSelector, _onBeforeSelectItemInSelector2).bind(this, menu),
	      'onAfterCreate': _classPrivateMethodGet$2(this, _onAfterCreateItemInSelector, _onAfterCreateItemInSelector2).bind(this)
	    },
	    itemCreateContext: {
	      index: 0,
	      visibility: String(selectedVariableType),
	      type: 'string',
	      mode: bizproc_globals.Globals.Manager.Instance.mode.variable,
	      objectName: _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME),
	      signedDocumentType: this.signedDocumentType
	    }
	  });
	  selector.show();
	}
	function _getObjectName2(visibility) {
	  if (this.isGVariableVisibility(visibility)) {
	    return _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_VAR_OBJECT_NAME);
	  }
	  if (this.isGConstantVisibility(visibility)) {
	    return _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _G_CONST_OBJECT_NAME);
	  }
	  if (this.isDocumentVisibility(visibility)) {
	    return _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _DOCUMENT_OBJECT_NAME);
	  }
	  return '';
	}
	function _onMenuVariableSetRowValue2(event) {
	  var eventData = event.getData();
	  var rowIndex = eventData.rowIndex;
	  var menu = eventData.menu;
	  if (rowIndex === 0) {
	    menu.setRowValue(1, '', main_core.Loc.getMessage('BPSGVA_EMPTY'));
	  }
	}
	function _onMenuVariableApplyChangesClick2(event) {
	  var eventData = event.getData();
	  var values = eventData.values;
	  var target = eventData.target;
	  var newSelectedVariable = values[1];
	  var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, newSelectedVariable);
	  var isExist = systemExpression.groupId !== _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text';
	  if (!isExist) {
	    systemExpression.title = main_core.Loc.getMessage('BPSGVA_VARIABLE');
	  }
	  _classPrivateMethodGet$2(this, _replaceTitle, _replaceTitle2).call(this, target, systemExpression.title);
	  _classPrivateMethodGet$2(this, _setHiddenValue, _setHiddenValue2).call(this, target, systemExpression.id, {
	    isMultiple: false,
	    inputIndex: 0,
	    isExpressionOption: false
	  });
	  _classPrivateMethodGet$2(this, _clearRelatedParameter, _clearRelatedParameter2).call(this, target);
	  _classPrivateMethodGet$2(this, _addEmptyRelatedParameter, _addEmptyRelatedParameter2).call(this, target, newSelectedVariable);
	}
	function _replaceTitle2(target, title) {
	  target.innerText = title;
	}
	function _setHiddenValue2(target, value, context, rowInputs) {
	  var index = target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	  var targetRole = target.getAttribute('data-role');
	  var role = targetRole + '_input';
	  if (main_core.Type.isNil(rowInputs)) {
	    rowInputs = document.getElementById(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _RESULTS_ID) + index);
	  }
	  var inputs = document.querySelectorAll('[data-role="' + role + '"]');
	  // single input
	  if (inputs.length >= 1 && !context.isMultiple) {
	    inputs[0].name = main_core.Text.encode(targetRole + (context.isExpressionOption ? '_text' : ''));
	    inputs[0].value = value;
	    return;
	  }

	  // multiple input
	  if (inputs.length >= 1 && context.isMultiple) {
	    var _iterator = _createForOfIteratorHelper(inputs),
	      _step;
	    try {
	      for (_iterator.s(); !(_step = _iterator.n()).done;) {
	        var _input2 = _step.value;
	        if (context.inputIndex === _input2.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME))) {
	          _input2.name = context.isExpressionOption ? main_core.Text.encode(targetRole + '_text') : main_core.Text.encode(targetRole + '[]');
	          _input2.value = value;
	          return;
	        }
	      }
	    } catch (err) {
	      _iterator.e(err);
	    } finally {
	      _iterator.f();
	    }
	  }
	  var inputName;
	  if (context.isExpressionOption) {
	    inputName = targetRole + '_text';
	  } else {
	    inputName = targetRole + (context.isMultiple ? '[]' : '');
	  }
	  var input = main_core.Tag.render(_templateObject19 || (_templateObject19 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\ttype=\"hidden\"\n\t\t\t\tname=\"", "\" value=\"", "\"\n\t\t\t\tdata-role=\"", "\"\n\t\t\t>\n\t\t"])), main_core.Text.encode(inputName), main_core.Text.encode(value), main_core.Text.encode(role));
	  input.setAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME), main_core.Text.toInteger(context.inputIndex));
	  main_core.Dom.append(input, rowInputs);
	}
	function _clearRelatedParameter2(target) {
	  var index = target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	  var parameterNode = document.querySelector('[data-role="' + _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _PARAMETER_ROLE) + index + '"]');
	  this.deleteOldValueRowsRobot(parameterNode);
	}
	function _addEmptyRelatedParameter2(target, selectedVariable) {
	  var index = target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	  var variableOption = this.getOptionPropertiesRobot(selectedVariable);
	  variableOption.inputIndex = '0';
	  this.changeParameterExpressionRobot(index, variableOption);
	}
	function _onMenuRowParameterValuesClick2(event) {
	  var _this$availableOption2;
	  var menu = event.getData().menu;
	  var selectedParameterType = menu.getRowValue(0);
	  var selectedVariableIndex = menu.target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	  var selectedVariableOption = this.getVariableOptionFromVariableInput(selectedVariableIndex);
	  var selectedVariableType = selectedVariableOption.property.Type;
	  var items = (_this$availableOption2 = this.availableOptionsByGroupId.get(selectedParameterType)) !== null && _this$availableOption2 !== void 0 ? _this$availableOption2 : [];
	  var filteredItems = this.filterItemsInStandardMenuRobot(selectedVariableType, items);
	  var showStubs = this.isGVariableVisibility(selectedParameterType) || this.isGConstantVisibility(selectedParameterType);
	  var objectName = _classPrivateMethodGet$2(this, _getObjectName, _getObjectName2).call(this, selectedParameterType);
	  var mode = '';
	  if (showStubs) {
	    mode = this.isGVariableVisibility(selectedParameterType) ? bizproc_globals.Globals.Manager.Instance.mode.variable : bizproc_globals.Globals.Manager.Instance.mode.constant;
	  }
	  var selector = new Selector(filteredItems, {
	    showStubs: showStubs,
	    objectName: objectName,
	    target: event.getTarget(),
	    events: {
	      'onBeforeSelect': _classPrivateMethodGet$2(this, _onBeforeSelectItemInSelector, _onBeforeSelectItemInSelector2).bind(this, menu),
	      'onAfterCreate': _classPrivateMethodGet$2(this, _onAfterCreateItemInSelector, _onAfterCreateItemInSelector2).bind(this)
	    },
	    itemCreateContext: {
	      mode: mode,
	      objectName: objectName,
	      index: 0,
	      visibility: String(selectedParameterType),
	      type: String(selectedVariableType),
	      signedDocumentType: this.signedDocumentType
	    }
	  });
	  selector.show();
	}
	function _onMenuParameterSetRowValue2(event) {
	  var eventData = event.getData();
	  var rowIndex = eventData.rowIndex;
	  var menu = eventData.menu;
	  if (rowIndex === 0) {
	    if (eventData.value !== _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text') {
	      menu.setRowLabel(1, main_core.Loc.getMessage('BPSGVA_LIST_OF_VALUES'));
	      var row = menu.createEmptyRow(1);
	      menu.replaceRowTarget(1, row, row);
	      menu.setRowValue(1, '', main_core.Loc.getMessage('BPSGVA_EMPTY'));
	      return;
	    }
	    menu.setRowLabel(1, main_core.Loc.getMessage('BPSGVA_INPUT_TEXT'));
	    var index = menu.target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	    var secondRowTarget = this.createInputForMenuFormRobot('all', index, '');
	    var input = this.findInputInFormRobot(secondRowTarget);
	    menu.replaceRowTarget(1, secondRowTarget, input);
	    menu.setRowValue(1, '', '');
	  }
	}
	function _onMenuParameterApplyChangesClick2(event) {
	  var _classPrivateMethodGe2;
	  var eventData = event.getData();
	  var menu = eventData.menu;
	  var values = eventData.values;
	  var target = eventData.target;
	  var parameterType = values[0];
	  var newSelectedParameter = values[1];
	  if (parameterType === _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text') {
	    var _input$value, _input3;
	    var input = menu.getRowInput(1);
	    if (!input) {
	      input = this.findInputInFormRobot(menu.getRowTarget(1));
	    }
	    newSelectedParameter = (_input$value = (_input3 = input) === null || _input3 === void 0 ? void 0 : _input3.value) !== null && _input$value !== void 0 ? _input$value : '';
	  }
	  var systemExpression = _classPrivateMethodGet$2(this, _parseSystemExpression, _parseSystemExpression2).call(this, newSelectedParameter);
	  if (!main_core.Type.isStringFilled(systemExpression.title)) {
	    systemExpression.title = main_core.Loc.getMessage('BPSGVA_CLEAR');
	  }
	  var selectedVariableIndex = menu.target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INDEX_ATTRIBUTE_NAME));
	  var selectedVariableOption = this.getVariableOptionFromVariableInput(selectedVariableIndex);
	  systemExpression.title = (_classPrivateMethodGe2 = _classPrivateMethodGet$2(this, _formatTitle, _formatTitle2).call(this, selectedVariableOption.property.Type, systemExpression.title, newSelectedParameter, selectedVariableOption.property.Options)) !== null && _classPrivateMethodGe2 !== void 0 ? _classPrivateMethodGe2 : systemExpression.title;
	  _classPrivateMethodGet$2(this, _replaceTitle, _replaceTitle2).call(this, target, systemExpression.title);
	  var isExpressionOption = ['select', 'bool'].includes(selectedVariableOption.property.Type) && parameterType !== _classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _HELPER_OBJECT_NAME) + ':text';
	  _classPrivateMethodGet$2(this, _setHiddenValue, _setHiddenValue2).call(this, target, systemExpression.id, {
	    isMultiple: selectedVariableOption.property.Multiple,
	    inputIndex: target.getAttribute(_classStaticPrivateFieldSpecGet(this.constructor, SetGlobalVariableActivity, _INPUT_INDEX_ATTRIBUTE_NAME)),
	    isExpressionOption: isExpressionOption
	  });
	}
	function _onBeforeSelectItemInSelector2(menu, event) {
	  var dialogItem = event.getData().item;
	  menu.setRowValue(1, dialogItem.id, dialogItem.customData.get('title'));
	}
	function _onAfterCreateItemInSelector2(event) {
	  var _this$availableOption3;
	  var item = event.getData().item;
	  this.availableOptions.set(item.id, item);
	  var groupItems = (_this$availableOption3 = this.availableOptionsByGroupId.get(item.customData.groupId)) !== null && _this$availableOption3 !== void 0 ? _this$availableOption3 : [];
	  groupItems.push(item);
	  this.availableOptionsByGroupId.set(item.customData.groupId, groupItems);
	}
	var _INDEX_ATTRIBUTE_NAME = {
	  writable: true,
	  value: 'bp_sgva_index'
	};
	var _INPUT_INDEX_ATTRIBUTE_NAME = {
	  writable: true,
	  value: 'bp_sgva_input_index'
	};
	var _G_VAR_OBJECT_NAME = {
	  writable: true,
	  value: 'GlobalVar'
	};
	var _G_CONST_OBJECT_NAME = {
	  writable: true,
	  value: 'GlobalConst'
	};
	var _DOCUMENT_OBJECT_NAME = {
	  writable: true,
	  value: 'Document'
	};
	var _HELPER_OBJECT_NAME = {
	  writable: true,
	  value: 'Default'
	};
	var _ROW_TABLE_ID = {
	  writable: true,
	  value: 'bp_sgva_addrow_table'
	};
	var _ADD_BUTTON_ID = {
	  writable: true,
	  value: 'bp_sgva_add_button'
	};
	var _RESULTS_ID = {
	  writable: true,
	  value: 'bp_sgva_results_'
	};
	var _VARIABLE_ROLE = {
	  writable: true,
	  value: 'bp_sgva_variable_'
	};
	var _PARAMETER_ROLE = {
	  writable: true,
	  value: 'bp_sgva_value_'
	};
	namespace.SetGlobalVariableActivity = SetGlobalVariableActivity;

}((this.BX.Bizproc.Activity = this.BX.Bizproc.Activity || {}),BX,BX.Main,BX,BX.Event,BX.UI.EntitySelector,BX.Bizproc));
//# sourceMappingURL=script.js.map
