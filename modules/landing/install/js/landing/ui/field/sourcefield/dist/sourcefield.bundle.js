this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_env,landing_ui_field_basefield,landing_loc,main_core) {
	'use strict';

	function getFilterStub() {
	  return {
	    key: 'filterStub',
	    name: landing_loc.Loc.getMessage('LANDING_BLOCK__SOURCE_FILTER_STUB'),
	    value: ''
	  };
	}

	function prepareSources(sources) {
	  if (main_core.Type.isArray(sources)) {
	    return sources.reduce(function (acc, item) {
	      if (main_core.Type.isPlainObject(item) && main_core.Type.isString(item.name) && main_core.Type.isString(item.value)) {
	        var source = main_core.Runtime.clone(item);

	        if (!main_core.Type.isArray(source.filter) || source.filter.length <= 0) {
	          source.filter = [main_core.Runtime.clone(getFilterStub())];
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

	function prepareFilter(filter, source) {
	  return filter.reduce(function (acc, field) {
	    if (main_core.Type.isPlainObject(field)) {
	      return [].concat(babelHelpers.toConsumableArray(acc), [babelHelpers.objectSpread({}, field, {
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

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-field-source-placeholder-remove\"></span>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-source-placeholder\">\n\t\t\t\t<span class=\"landing-ui-field-source-placeholder-text\">", "</span>\n\t\t\t</div>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-source-grid\">\n\t\t\t\t\t<div class=\"landing-ui-field-source-grid-left\">", "</div>\n\t\t\t\t\t<div class=\"landing-ui-field-source-grid-right\">", "</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span>&nbsp;(", ")</span>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span></span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var SourceField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(SourceField, _BaseField);

	  function SourceField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SourceField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SourceField).call(this, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-source');
	    _this.items = prepareSources(options.items);
	    _this.value = prepareValue(options.value, _this.items);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onButtonClick = _this.onButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMenuItemClick = _this.onMenuItemClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onSliderMessage = _this.onSliderMessage.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPlaceholderRemoveClick = _this.onPlaceholderRemoveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPlaceholderClick = _this.onPlaceholderClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.append(_this.getGrid(), _this.layout);
	    main_core.Dom.append(_this.getSortByField().layout, _this.layout);
	    main_core.Dom.append(_this.getSortOrderField().layout, _this.layout);
	    main_core.Dom.append(_this.getValueLayoutWrapper(), _this.header);

	    _this.setValue(_this.value); // const rootWindow = BX.Landing.PageObject.getRootWindow();


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

	      return this.cache.remember('buttonField', function () {
	        return new BX.Landing.UI.Button.BaseButton('dropdown_button', {
	          text: landing_loc.Loc.getMessage('LINK_URL_SUGGESTS_SELECT'),
	          className: 'landing-ui-button-select-link',
	          onClick: _this2.onButtonClick
	        });
	      });
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
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "getValueLayoutWrapper",
	    value: function getValueLayoutWrapper() {
	      var _this5 = this;

	      return this.cache.remember('valueLayoutWrapper', function () {
	        return main_core.Tag.render(_templateObject2(), _this5.getValueLayout());
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
	        return main_core.Tag.render(_templateObject3(), _this6.getInput(), _this6.getButtonField().layout);
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
	        var sourceValue = babelHelpers.objectSpread({}, this.getValue(), {
	          filter: event.getData().filter
	        });
	        var value = prepareValue(sourceValue, this.items);
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
	        var form = _this8.input.closest('.landing-ui-field-source');

	        var menu = new BX.PopupMenuWindow({
	          id: "".concat(_this8.selector, "_").concat(main_core.Text.getRandom()),
	          bindElement: _this8.getButtonField().layout,
	          autoHide: true,
	          items: _this8.getMenuItems(),
	          className: 'landing-ui-field-source-popup',
	          events: {
	            onPopupShow: function onPopupShow() {
	              var buttonPosition = main_core.Dom.getRelativePosition(_this8.getButtonField().layout, form);
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
	      var placeholder = main_core.Tag.render(_templateObject4(), main_core.Text.encode(options.name));
	      main_core.Dom.attr(placeholder, {
	        'data-item': options,
	        title: options.name
	      });

	      if (!options.url) {
	        main_core.Dom.addClass(placeholder.firstElementChild, 'landing-ui-field-source-placeholder-text-plain');
	      }

	      if (options.url) {
	        var removeButton = main_core.Tag.render(_templateObject5());
	        main_core.Dom.append(removeButton, placeholder);
	        main_core.Event.bind(placeholder, 'click', this.onPlaceholderClick.bind(this, options));
	        main_core.Event.bind(removeButton, 'click', this.onPlaceholderRemoveClick.bind(this, options));
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
	  }]);
	  return SourceField;
	}(landing_ui_field_basefield.BaseField);

	exports.SourceField = SourceField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing,BX.Landing.UI.Field,BX.Landing,BX));
//# sourceMappingURL=sourcefield.bundle.js.map
