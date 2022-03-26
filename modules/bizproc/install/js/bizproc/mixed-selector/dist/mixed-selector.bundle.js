this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_popup) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _fillMenuItems = /*#__PURE__*/new WeakSet();

	var _getTabsMap = /*#__PURE__*/new WeakSet();

	var _extractMenuItem = /*#__PURE__*/new WeakSet();

	var _getTemplateActivitiesItems = /*#__PURE__*/new WeakSet();

	var _onChooseFieldClick = /*#__PURE__*/new WeakSet();

	var _onChooseTargetClick = /*#__PURE__*/new WeakSet();

	var BpMixedSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BpMixedSelector, _EventEmitter);

	  function BpMixedSelector(selectorOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, BpMixedSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BpMixedSelector).call(this));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onChooseTargetClick);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onChooseFieldClick);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTemplateActivitiesItems);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _extractMenuItem);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTabsMap);

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _fillMenuItems);

	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "targetNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tabs", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "template", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "activityName", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxWidth", 300);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxHeight", 500);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minWidth", 100);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minHeight", 60);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "objectName", 'mixed_selector[object]');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fieldName", 'mixed_selector[field]');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "checkActivityChildren", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "map", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menuItems", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menuTargetNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "menuId", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "objectInputNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "fieldInputNode", null);

	    _this.setEventNamespace('BX.Bizproc.MixedSelector.BpMixedSelector');

	    var options = main_core.Type.isPlainObject(selectorOptions) ? selectorOptions : {};

	    _this.setTargetNode(options.targetNode);

	    _this.setObjectTabs(options.objectTabs);

	    _this.setTemplate(options.template);

	    _this.setActivityName(options.activityName);

	    _this.setSize(options.size);

	    _this.setInputNames(options.inputNames);

	    _this.setTargetTitle(options.targetTitle);

	    _this.setCheckActivityChildren(options.checkActivityChildren);

	    return _this;
	  }

	  babelHelpers.createClass(BpMixedSelector, [{
	    key: "setTargetNode",

	    /* region basic SET/GET */
	    value: function setTargetNode(node) {
	      if (main_core.Type.isDomNode(node)) {
	        this.targetNode = node;
	      }
	    }
	  }, {
	    key: "getTargetNode",
	    value: function getTargetNode() {
	      return this.targetNode;
	    }
	  }, {
	    key: "setObjectTabs",
	    value: function setObjectTabs(tabs) {
	      if (main_core.Type.isPlainObject(tabs)) {
	        this.tabs = tabs;
	      }
	    }
	  }, {
	    key: "getObjectTabs",
	    value: function getObjectTabs() {
	      return this.tabs;
	    }
	  }, {
	    key: "setTemplate",
	    value: function setTemplate(template) {
	      if (main_core.Type.isArrayFilled(template)) {
	        this.template = template;
	      }
	    }
	  }, {
	    key: "getTemplate",
	    value: function getTemplate() {
	      return this.template;
	    }
	  }, {
	    key: "setActivityName",
	    value: function setActivityName(name) {
	      if (main_core.Type.isStringFilled(name)) {
	        this.activityName = name;
	      }
	    }
	  }, {
	    key: "getActivityName",
	    value: function getActivityName() {
	      return this.activityName;
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (!main_core.Type.isPlainObject(size)) {
	        return;
	      }

	      if (main_core.Type.isNumber(size.maxWidth)) {
	        this.maxWidth = size.maxWidth;
	      }

	      if (main_core.Type.isNumber(size.minWidth)) {
	        this.minWidth = size.minWidth;
	      }

	      if (main_core.Type.isNumber(size.maxHeight)) {
	        this.maxHeight = size.maxHeight;
	      }

	      if (main_core.Type.isNumber(size.minHeight)) {
	        this.minHeight = size.minHeight;
	      }
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return {
	        maxWidth: this.maxWidth,
	        minWidth: this.minWidth,
	        maxHeight: this.maxHeight,
	        minHeight: this.minHeight
	      };
	    }
	  }, {
	    key: "setInputNames",
	    value: function setInputNames(names) {
	      if (!main_core.Type.isPlainObject(names)) {
	        return;
	      }

	      if (main_core.Type.isStringFilled(names.object)) {
	        this.objectName = names.object;
	      }

	      if (main_core.Type.isStringFilled(names.field)) {
	        this.fieldName = names.field;
	      }
	    }
	  }, {
	    key: "getInputNames",
	    value: function getInputNames() {
	      return {
	        object: this.objectName,
	        field: this.fieldName
	      };
	    }
	  }, {
	    key: "setTargetTitle",
	    value: function setTargetTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.targetTitle = title;
	        return;
	      }

	      this.targetTitle = main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_CHOOSE_TARGET');
	    }
	  }, {
	    key: "getTargetTitle",
	    value: function getTargetTitle() {
	      return this.targetTitle;
	    }
	  }, {
	    key: "setCheckActivityChildren",
	    value: function setCheckActivityChildren(check) {
	      if (main_core.Type.isBoolean(check)) {
	        this.checkActivityChildren = check;
	      }
	    }
	  }, {
	    key: "getCheckActivityChildren",
	    value: function getCheckActivityChildren() {
	      return this.checkActivityChildren;
	    }
	    /* endregion */

	  }, {
	    key: "getMenu",
	    value: function getMenu() {
	      var me = this;

	      if (this.menuId) {
	        //todo: modify popup position.
	        return main_popup.MenuManager.getMenuById(this.menuId);
	      }

	      this.menuId = BX.util.getRandomString();
	      var size = this.getSize();
	      return main_popup.MenuManager.create(me.menuId, me.getMenuTargetNode(), me.getMenuItems(), {
	        zIndex: 200,
	        autoHide: true,
	        offsetLeft: main_core.Dom.getPosition(me.getMenuTargetNode())['width'] / 2,
	        angle: {
	          position: 'top',
	          offset: 0
	        },
	        maxWidth: size.maxWidth,
	        maxHeight: size.maxHeight,
	        minWidth: size.minWidth,
	        minHeight: size.minHeight
	      });
	    }
	  }, {
	    key: "getMenuTargetNode",
	    value: function getMenuTargetNode() {
	      return this.menuTargetNode;
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      if (this.menuItems) {
	        return this.menuItems;
	      }

	      this.menuItems = [];

	      _classPrivateMethodGet(this, _fillMenuItems, _fillMenuItems2).call(this);

	      return this.menuItems;
	    }
	  }, {
	    key: "getMenuItemsByTabName",
	    value: function getMenuItemsByTabName(tabName) {
	      var tabsItems = this.getMenuItems();

	      for (var i in tabsItems) {
	        if (tabsItems[i].tabName === tabName) {
	          return tabsItems[i].items;
	        }
	      }

	      return [];
	    }
	  }, {
	    key: "renderMixedSelector",
	    value: function renderMixedSelector() {
	      var link = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), BX.util.htmlspecialchars(this.getTargetTitle()));
	      this.menuTargetNode = link;
	      main_core.Event.bind(link, 'click', _classPrivateMethodGet(this, _onChooseTargetClick, _onChooseTargetClick2).bind(this));
	      var objectInput = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\ttype=\"hidden\" \n\t\t\t\tname=\"", "\" \n\t\t\t\tdata-role=\"mixed-selector-object\"\n\t\t\t\tvalue=\"\"\n\t\t\t>\n\t\t"])), this.objectName);
	      this.objectInputNode = objectInput;
	      var fieldInput = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\ttype=\"hidden\" \n\t\t\t\tname=\"", "\" \n\t\t\t\tdata-role=\"mixed-selector-field\"\n\t\t\t\tvalue=\"\"\n\t\t\t>\n\t\t"])), this.fieldName);
	      this.fieldInputNode = fieldInput;
	      main_core.Dom.append(link, this.targetNode);
	      main_core.Dom.append(objectInput, this.targetNode);
	      main_core.Dom.append(fieldInput, this.targetNode);
	    }
	  }, {
	    key: "getSelectedObjectValue",
	    value: function getSelectedObjectValue() {
	      if (this.objectInputNode) {
	        return this.objectInputNode.value;
	      }

	      return null;
	    }
	  }, {
	    key: "getSelectedFieldValue",
	    value: function getSelectedFieldValue() {
	      if (this.fieldInputNode) {
	        return this.fieldInputNode.value;
	      }

	      return null;
	    }
	  }, {
	    key: "setSelectedObjectAndField",
	    value: function setSelectedObjectAndField(object, field, fieldTitle) {
	      var target = this.getMenuTargetNode();
	      var tabsLocMessage = BpMixedSelector.getAvailableTabsLocMessages();

	      if (BpMixedSelector.getAvailableTabsName().includes(object)) {
	        target.innerText = tabsLocMessage[object] + ': ' + fieldTitle;
	      } else {
	        target.innerText = tabsLocMessage['Activity'] + ': ' + fieldTitle;
	      }

	      if (main_core.Type.isStringFilled(object) && main_core.Type.isStringFilled(field)) {
	        this.objectInputNode.value = object;
	        this.fieldInputNode.value = field;
	      }
	    }
	  }], [{
	    key: "getAvailableTabsName",
	    value: function getAvailableTabsName() {
	      return ['Parameter', 'Variable', 'Constant', 'GlobalConst', 'GlobalVar', 'Document', 'Activity'];
	    }
	  }, {
	    key: "getAvailableTabsLocMessages",
	    value: function getAvailableTabsLocMessages() {
	      return {
	        Parameter: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_PARAMETER'),
	        Variable: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_VARIABLE'),
	        Constant: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_CONSTANT'),
	        GlobalConst: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_GLOBAL_CONSTANT'),
	        GlobalVar: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_GLOBAL_VARIABLE'),
	        Document: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_DOCUMENT_FIELDS'),
	        Activity: main_core.Loc.getMessage('BIZPROC_MIXED_SELECTOR_EXT_ADDITIONAL_RESULT')
	      };
	    }
	  }]);
	  return BpMixedSelector;
	}(main_core_events.EventEmitter);

	function _fillMenuItems2() {
	  var _this2 = this;

	  var me = this;

	  var map = _classPrivateMethodGet(this, _getTabsMap, _getTabsMap2).call(this);

	  var locMapNames = BpMixedSelector.getAvailableTabsLocMessages();
	  var mapKeys = BX.util.object_keys(map);

	  for (var i in mapKeys) {
	    if (mapKeys[i] !== 'Activity') {
	      this.menuItems.push({
	        text: locMapNames[mapKeys[i]],
	        items: _classPrivateMethodGet(this, _extractMenuItem, _extractMenuItem2).call(this, map[mapKeys[i]], mapKeys[i]),
	        tabName: mapKeys[i]
	      });
	    } else {
	      (function () {
	        var activitiesItems = _classPrivateMethodGet(_this2, _getTemplateActivitiesItems, _getTemplateActivitiesItems2).call(_this2, _this2.template, map[mapKeys[i]]);

	        var groupByItemActivitiesItems = [];
	        activitiesItems.forEach(function (activityItem) {
	          if (!main_core.Type.isArrayFilled(activityItem)) {
	            return;
	          }

	          var items = [];
	          activityItem.forEach(function (item) {
	            if (!main_core.Type.isStringFilled(item.description)) {
	              return;
	            }

	            items.push({
	              text: main_core.Text.encode(item.text + ' (' + item.description + ')'),
	              object: item.object,
	              field: item.field,
	              property: item,
	              onclick: _classPrivateMethodGet(me, _onChooseFieldClick, _onChooseFieldClick2).bind(me)
	            });
	          });

	          if (main_core.Type.isArrayFilled(items)) {
	            groupByItemActivitiesItems.push({
	              text: activityItem[0].description,
	              object: activityItem[0].object,
	              items: items
	            });
	          }
	        });

	        if (main_core.Type.isArrayFilled(groupByItemActivitiesItems)) {
	          _this2.menuItems.push({
	            text: locMapNames[mapKeys[i]],
	            items: groupByItemActivitiesItems,
	            tabName: 'Activity'
	          });
	        }
	      })();
	    }
	  }
	}

	function _getTabsMap2() {
	  if (this.map) {
	    return this.map;
	  }

	  this.map = {};
	  var availableTabs = BpMixedSelector.getAvailableTabsName();
	  var keys = Object.keys(this.tabs);

	  for (var i in keys) {
	    if (availableTabs.includes(keys[i]) && Object.keys(this.tabs[keys[i]]).length > 0) {
	      this.map[keys[i]] = this.tabs[keys[i]];
	    }
	  }

	  if (this.template.length < 0) {
	    if (this.map['Activity']) {
	      delete this.map['Activity'];
	    }
	  }

	  return this.map;
	}

	function _extractMenuItem2(items, object) {
	  var result = [];
	  var itemsKeys = Object.keys(items);

	  for (var i in itemsKeys) {
	    result.push({
	      text: BX.util.htmlspecialchars(items[itemsKeys[i]].Name),
	      object: object,
	      field: itemsKeys[i],
	      property: items[itemsKeys[i]],
	      onclick: _classPrivateMethodGet(this, _onChooseFieldClick, _onChooseFieldClick2).bind(this)
	    });
	  }

	  return result;
	}

	function _getTemplateActivitiesItems2(template, activities) {
	  var _this3 = this;

	  var result = [];

	  var _loop = function _loop(i, s) {
	    var _activities$activityT;

	    if (template[i].Name === _this3.activityName && !_this3.checkActivityChildren) {
	      return "continue";
	    }

	    var activityType = template[i].Type.toLowerCase();
	    var activityData = (_activities$activityT = activities[activityType]) !== null && _activities$activityT !== void 0 ? _activities$activityT : {};
	    var returnActivityData = activityData['RETURN'];
	    var additionalResult = activityData['ADDITIONAL_RESULT'];

	    if (returnActivityData) {
	      var keys = Object.keys(returnActivityData);
	      var activityResult = [];

	      for (var j in keys) {
	        activityResult.push({
	          text: returnActivityData[keys[j]].NAME,
	          description: template[i].Properties.Title || activityData.NAME,
	          value: '{=' + template[i].Name + ':' + keys[j] + '}',
	          object: template[i].Name,
	          field: keys[j],
	          property: {
	            Name: returnActivityData[keys[j]].NAME,
	            Type: returnActivityData[keys[j]].TYPE
	          }
	        });
	      }

	      if (activityResult.length > 0) {
	        result.push(activityResult);
	      }
	    } else if (main_core.Type.isArray(additionalResult)) {
	      var properties = template[i]['Properties'];
	      additionalResult.forEach(function (addProp) {
	        if (properties[addProp]) {
	          var _keys = Object.keys(properties[addProp]);

	          var _activityResult = [];

	          for (var _j in _keys) {
	            var field = properties[addProp][_keys[_j]];

	            _activityResult.push({
	              text: field.Name,
	              description: properties['Title'] || activityData['NAME'],
	              value: '{=' + template[i]['Name'] + ':' + _keys[_j] + '}',
	              object: template[i]['Name'],
	              field: _keys[_j],
	              property: field
	            });
	          }

	          if (_activityResult.length > 0) {
	            result.push(_activityResult);
	          }
	        }
	      }, _this3);
	    }

	    if (template[i]['Children'] && template[i]['Children'].length > 0) {
	      var subResult = _classPrivateMethodGet(_this3, _getTemplateActivitiesItems, _getTemplateActivitiesItems2).call(_this3, template[i]['Children'], activities);

	      result = result.concat(subResult);
	    }
	  };

	  for (var i = 0, s = template.length; i < s; ++i) {
	    var _ret = _loop(i, s);

	    if (_ret === "continue") continue;
	  }

	  return result;
	}

	function _onChooseFieldClick2(event, item) {
	  var menu = this.getMenu();
	  menu.close(); // todo: item.text htmlspecialchars applied twice

	  this.setSelectedObjectAndField(item.object, item.field, item.text);
	  main_core_events.EventEmitter.emit(this, 'onSelect', {
	    item: item
	  });
	}

	function _onChooseTargetClick2(event) {
	  var menu = this.getMenu();
	  menu.show();
	  event.preventDefault();
	}

	var MixedSelector = {
	  BpMixedSelector: BpMixedSelector
	};

	exports.MixedSelector = MixedSelector;
	exports.BpMixedSelector = BpMixedSelector;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX,BX.Event,BX.Main));
//# sourceMappingURL=mixed-selector.bundle.js.map
