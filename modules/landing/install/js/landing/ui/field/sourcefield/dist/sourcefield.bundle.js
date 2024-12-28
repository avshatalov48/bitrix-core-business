/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_env,landing_ui_field_basefield,landing_loc,main_core) {
	'use strict';

	function getFilterStub() {
	  var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	  var name = text;
	  if (name === '') {
	    name = landing_loc.Loc.getMessage('LANDING_BLOCK__SOURCE_FILTER_STUB');
	  }
	  return {
	    key: 'filterStub',
	    name: name,
	    value: ''
	  };
	}

	function prepareSources(sources) {
	  var stubText = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	  if (main_core.Type.isArray(sources)) {
	    return sources.reduce(function (acc, item) {
	      if (main_core.Type.isPlainObject(item) && main_core.Type.isString(item.name) && main_core.Type.isString(item.value)) {
	        var source = main_core.Runtime.clone(item);
	        if (!main_core.Type.isArray(source.filter) || source.filter.length <= 0) {
	          source.filter = [main_core.Runtime.clone(getFilterStub(stubText))];
	        }
	        if (!main_core.Type.isPlainObject(source.sort) || !main_core.Type.isArray(source.sort.items)) {
	          source.sort = {
	            items: []
	          };
	        }
	        return [].concat(babelHelpers.toConsumableArray(acc), [source]);
	      }
	      return acc;
	    }, []);
	  }
	  return [];
	}

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function prepareFilter(filter, source) {
	  return filter.reduce(function (acc, field) {
	    if (main_core.Type.isPlainObject(field)) {
	      return [].concat(babelHelpers.toConsumableArray(acc), [_objectSpread(_objectSpread({}, field), {}, {
	        url: source.url
	      })]);
	    }
	    return acc;
	  }, []);
	}
	function prepareValue(value, sources) {
	  var _sources = babelHelpers.slicedToArray(sources, 1),
	    firstSource = _sources[0];
	  if (!main_core.Type.isPlainObject(value)) {
	    return {
	      source: firstSource.value,
	      filter: prepareFilter(babelHelpers.toConsumableArray(firstSource.filter), firstSource),
	      sort: {
	        by: firstSource.sort.items[0].key,
	        order: 'DESC'
	      }
	    };
	  }
	  var source = sources.find(function (item) {
	    return item.value === value.source;
	  });
	  if (!main_core.Type.isArray(value.filter) || value.filter.length <= 0) {
	    if (source) {
	      value.filter = babelHelpers.toConsumableArray(source.filter);
	    }
	  }
	  value.filter = prepareFilter(value.filter, source);
	  if (!main_core.Type.isPlainObject(value.sort)) {
	    value.sort = {};
	  }
	  if (!main_core.Type.isString(value.sort.by)) {
	    if (source) {
	      value.sort.by = source.sort.items[0].value;
	    }
	  }
	  if (!main_core.Type.isString(value.sort.order)) {
	    value.sort.order = 'DESC';
	  }
	  return value;
	}

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6;
	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @memberOf BX.Landing.UI.Field
	 */
	var SourceField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(SourceField, _BaseField);
	  function SourceField(options) {
	    var _options$linkType;
	    var _this;
	    babelHelpers.classCallCheck(this, SourceField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SourceField).call(this, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-source');
	    _this.items = prepareSources(options.items, options.stubText);
	    _this.value = prepareValue(options.value, _this.items);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.linkType = (_options$linkType = options.linkType) !== null && _options$linkType !== void 0 ? _options$linkType : '';
	    _this.onButtonClick = _this.onButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMenuItemClick = _this.onMenuItemClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onSliderMessage = _this.onSliderMessage.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPlaceholderRemoveClick = _this.onPlaceholderRemoveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPlaceholderClick = _this.onPlaceholderClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.append(_this.getGrid(), _this.layout);
	    if (!options.hideSort) {
	      main_core.Dom.append(_this.getSortByField().layout, _this.layout);
	      main_core.Dom.append(_this.getSortOrderField().layout, _this.layout);
	    }
	    if (options.useLink) {
	      main_core.Dom.append(_this.getLink(), _this.layout);
	    }
	    if (options.showValueInHeader && options.showValueInHeader !== false) {
	      main_core.Dom.append(_this.getValueLayoutWrapper(), _this.header);
	    }
	    _this.setValue(_this.value);
	    window.top.BX.addCustomEvent('SidePanel.Slider:onMessage', _this.onSliderMessage);
	    return _this;
	  }
	  babelHelpers.createClass(SourceField, [{
	    key: "getItem",
	    value: function getItem(value) {
	      return this.items.find(function (item) {
	        return item.value === value;
	      });
	    }
	  }, {
	    key: "getButtonField",
	    value: function getButtonField() {
	      var _this2 = this;
	      return this.getMenuItems().length > 1 ? this.cache.remember('buttonField', function () {
	        new BX.Landing.UI.Button.BaseButton('dropdown_button', {
	          text: landing_loc.Loc.getMessage('LINK_URL_SUGGESTS_SELECT'),
	          className: 'landing-ui-button-select-link',
	          onClick: _this2.onButtonClick
	        });
	      }) : null;
	    }
	  }, {
	    key: "getSortByField",
	    value: function getSortByField() {
	      var _this3 = this;
	      return this.cache.remember('sortByField', function () {
	        var item = _this3.getItem(_this3.value.source);
	        return new BX.Landing.UI.Field.DropdownInline({
	          title: landing_loc.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_TITLE').toLowerCase(),
	          items: item.sort.items,
	          content: _this3.value.sort.by
	        });
	      });
	    }
	  }, {
	    key: "getSortOrderField",
	    value: function getSortOrderField() {
	      var _this4 = this;
	      return this.cache.remember('sortOrderField', function () {
	        return new BX.Landing.UI.Field.DropdownInline({
	          title: ', ',
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_DESC'),
	            value: 'DESC'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_SORT_ASC'),
	            value: 'ASC'
	          }],
	          content: _this4.value.sort.order
	        });
	      });
	    }
	  }, {
	    key: "getValueLayout",
	    value: function getValueLayout() {
	      return this.cache.remember('valueLayout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span></span>"])));
	      });
	    }
	  }, {
	    key: "getValueLayoutWrapper",
	    value: function getValueLayoutWrapper() {
	      var _this5 = this;
	      return this.cache.remember('valueLayoutWrapper', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span>&nbsp;(", ")</span>"])), _this5.getValueLayout());
	      });
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      return this.input;
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      var _this6 = this;
	      return this.cache.remember('grid', function () {
	        var _this6$getButtonField;
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-source-grid\">\n\t\t\t\t\t<div class=\"landing-ui-field-source-grid-left\">", "</div>\n\t\t\t\t\t<div class=\"landing-ui-field-source-grid-right\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), _this6.getInput(), (_this6$getButtonField = _this6.getButtonField()) === null || _this6$getButtonField === void 0 ? void 0 : _this6$getButtonField.layout);
	      });
	    }
	  }, {
	    key: "onButtonClick",
	    value: function onButtonClick() {
	      this.getMenu().show();
	    }
	  }, {
	    key: "onMenuItemClick",
	    value: function onMenuItemClick(item) {
	      var value = prepareValue({
	        source: item.value
	      }, this.items);
	      this.setValue(value);
	      this.getMenu().close();
	      this.openSourceFilterSlider(item.url);
	    }
	  }, {
	    key: "onPlaceholderClick",
	    value: function onPlaceholderClick(item, event) {
	      event.preventDefault();
	      this.openSourceFilterSlider(item.url);
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "onPlaceholderRemoveClick",
	    value: function onPlaceholderRemoveClick(item, event) {
	      event.preventDefault();
	      event.stopPropagation();
	      var currentTarget = event.currentTarget;
	      if (main_core.Type.isDomNode(currentTarget)) {
	        var placeholder = currentTarget.closest('.landing-ui-field-source-placeholder');
	        if (placeholder) {
	          main_core.Dom.remove(placeholder);
	        }
	        if (this.getPlaceholders().length <= 0) {
	          var value = prepareValue({
	            source: this.getValue().source
	          }, this.items);
	          this.prepareLink(value);
	          this.value = value;
	          this.setFilter(value.filter);
	        }
	        this.value.filter = this.getPlaceholders().map(function (placeholderNode) {
	          return main_core.Dom.attr(placeholderNode, 'data-item');
	        });
	      }
	    }
	  }, {
	    key: "onSliderMessage",
	    value: function onSliderMessage(event) {
	      if (event.getEventId() === 'save') {
	        var sourceValue = _objectSpread$1(_objectSpread$1({}, this.getValue()), {}, {
	          filter: event.getData().filter
	        });
	        var value = prepareValue(sourceValue, this.items);
	        this.prepareLink(value);
	        this.value = value;
	        this.setFilter(value.filter);
	      }
	    }
	  }, {
	    key: "openSourceFilterSlider",
	    value: function openSourceFilterSlider(url) {
	      if (main_core.Type.isString(url)) {
	        var siteId = landing_env.Env.getInstance().getOptions().site_id;
	        BX.SidePanel.Instance.open(url, {
	          cacheable: false,
	          requestMethod: 'post',
	          requestParams: {
	            filter: this.getValue().filter,
	            landingParams: {
	              siteId: siteId
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      var _this7 = this;
	      return this.cache.remember('menuItems', function () {
	        return _this7.items.map(function (item) {
	          return {
	            id: item.value,
	            text: main_core.Text.encode(item.name),
	            onclick: function onclick() {
	              return _this7.onMenuItemClick(item);
	            }
	          };
	        });
	      });
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu() {
	      var _this8 = this;
	      return this.cache.remember('menu', function () {
	        var _this8$getButtonField;
	        var form = _this8.input.closest('.landing-ui-field-source');
	        var menu = new BX.PopupMenuWindow({
	          id: "".concat(_this8.selector, "_").concat(main_core.Text.getRandom()),
	          bindElement: (_this8$getButtonField = _this8.getButtonField()) === null || _this8$getButtonField === void 0 ? void 0 : _this8$getButtonField.layout,
	          autoHide: true,
	          items: _this8.getMenuItems(),
	          className: 'landing-ui-field-source-popup',
	          events: {
	            onPopupShow: function onPopupShow() {
	              var _this8$getButtonField2;
	              var buttonPosition = main_core.Dom.getRelativePosition((_this8$getButtonField2 = _this8.getButtonField()) === null || _this8$getButtonField2 === void 0 ? void 0 : _this8$getButtonField2.layout, form);
	              var offsetX = 0;
	              var popupWindowTop = buttonPosition.bottom;
	              requestAnimationFrame(function () {
	                main_core.Dom.style(menu.popupWindow.popupContainer, {
	                  top: "".concat(popupWindowTop, "px"),
	                  left: 'auto',
	                  right: "".concat(offsetX, "px")
	                });
	              });
	            }
	          }
	        });
	        main_core.Dom.append(menu.popupWindow.popupContainer, form);
	        return menu;
	      });
	    }
	  }, {
	    key: "addPlaceholder",
	    value: function addPlaceholder(options) {
	      var placeholder = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-source-placeholder\">\n\t\t\t\t<span class=\"landing-ui-field-source-placeholder-text\">", "</span>\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(options.name));
	      main_core.Dom.attr(placeholder, {
	        'data-item': options,
	        title: options.name
	      });
	      if (!options.url) {
	        main_core.Dom.addClass(placeholder.firstElementChild, 'landing-ui-field-source-placeholder-text-plain');
	      }
	      if (options.url) {
	        if (options.useLink) {
	          var removeButton = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-field-source-placeholder-remove\"></span>"])));
	          main_core.Dom.append(removeButton, placeholder);
	          main_core.Event.bind(removeButton, 'click', this.onPlaceholderRemoveClick.bind(this, options));
	        }
	        main_core.Event.bind(placeholder, 'click', this.onPlaceholderClick.bind(this, options));
	      }
	      main_core.Dom.append(placeholder, this.input);
	    }
	  }, {
	    key: "getPlaceholders",
	    value: function getPlaceholders() {
	      return babelHelpers.toConsumableArray(this.input.querySelectorAll('.landing-ui-field-source-placeholder'));
	    }
	  }, {
	    key: "setFilter",
	    value: function setFilter(filter) {
	      var _this9 = this;
	      main_core.Dom.clean(this.getInput());
	      filter.forEach(function (field) {
	        _this9.addPlaceholder(field);
	      });
	    }
	  }, {
	    key: "setSource",
	    value: function setSource(_ref) {
	      var value = _ref.value,
	        name = _ref.name;
	      var valueLayout = this.getValueLayout();
	      main_core.Dom.attr(valueLayout, 'data-value', value);
	      valueLayout.innerText = name;
	    }
	  }, {
	    key: "setSortByItems",
	    value: function setSortByItems(items) {
	      if (main_core.Type.isArray(items)) {
	        this.getSortByField().setItems(items);
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value, preventEvent) {
	      var preparedValue = prepareValue(value, this.items);
	      var sourceItem = this.getItem(value.source);
	      if (main_core.Type.isPlainObject(sourceItem)) {
	        if (preparedValue.source !== this.value.source || this.getPlaceholders().length <= 0) {
	          this.value = main_core.Runtime.clone(preparedValue);
	          this.setFilter(preparedValue.filter);
	          this.setSource(sourceItem);
	          var sortByField = this.getSortByField();
	          sortByField.setItems(sourceItem.sort.items);
	          sortByField.setValue(preparedValue.sort.by);
	          var orderByField = this.getSortOrderField();
	          orderByField.setValue(preparedValue.sort.order);
	          if (!preventEvent) {
	            this.onValueChangeHandler(this);
	          }
	        }
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = main_core.Runtime.clone(this.value);
	      value.filter = value.filter.filter(function (field) {
	        return field.key !== getFilterStub().key;
	      }).map(function (field) {
	        Reflect.deleteProperty(field, 'url');
	        return field;
	      });
	      value.sort.by = this.getSortByField().getValue();
	      value.sort.order = this.getSortOrderField().getValue();
	      return value;
	    }
	  }, {
	    key: "getCurrentSource",
	    value: function getCurrentSource() {
	      var value = this.getValue();
	      return this.getItem(value.source);
	    }
	  }, {
	    key: "isDetailPageAllowed",
	    value: function isDetailPageAllowed() {
	      var source = this.getCurrentSource();
	      return !main_core.Type.isPlainObject(source) || !main_core.Type.isPlainObject(source.settings) || source.settings.detailPage !== false;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var _this$value$filter$0$, _this$value, _this$value$filter, _this$value$filter$;
	      var href = '#';
	      var hrefAttr = '';
	      var value = (_this$value$filter$0$ = (_this$value = this.value) === null || _this$value === void 0 ? void 0 : (_this$value$filter = _this$value.filter) === null || _this$value$filter === void 0 ? void 0 : (_this$value$filter$ = _this$value$filter[0]) === null || _this$value$filter$ === void 0 ? void 0 : _this$value$filter$.value) !== null && _this$value$filter$0$ !== void 0 ? _this$value$filter$0$ : null;
	      if (this.linkType === 'group' && value && value.startsWith('SG')) {
	        href = "/workgroups/group/".concat(value.slice(2), "/");
	        hrefAttr = "href=\"".concat(href, "\"");
	      }
	      return this.cache.remember('linkLayout', function () {
	        var text = BX.Landing.Loc.getMessage('LANDING_SOURCEFIELD_LINK_TEXT');
	        return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-link-container\">\n\t\t\t\t\t<a ", " target=\"_blank\" class=\"landing-ui-field-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t</div>\n\t\t\t"])), hrefAttr, text);
	      });
	    }
	  }, {
	    key: "prepareLink",
	    value: function prepareLink(value) {
	      var linkElement = this.layout.querySelector('.landing-ui-field-link');
	      if (linkElement && this.linkType === 'group') {
	        var _value$filter$0$value, _value$filter, _value$filter$;
	        var newValue = (_value$filter$0$value = value === null || value === void 0 ? void 0 : (_value$filter = value.filter) === null || _value$filter === void 0 ? void 0 : (_value$filter$ = _value$filter[0]) === null || _value$filter$ === void 0 ? void 0 : _value$filter$.value) !== null && _value$filter$0$value !== void 0 ? _value$filter$0$value : null;
	        if (newValue !== null && newValue.startsWith('SG')) {
	          linkElement.href = "/workgroups/group/".concat(newValue.slice(2), "/");
	        } else {
	          linkElement.removeAttribute('href');
	        }
	      }
	    }
	  }]);
	  return SourceField;
	}(landing_ui_field_basefield.BaseField);

	exports.SourceField = SourceField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing,BX.Landing.UI.Field,BX.Landing,BX));
//# sourceMappingURL=sourcefield.bundle.js.map
