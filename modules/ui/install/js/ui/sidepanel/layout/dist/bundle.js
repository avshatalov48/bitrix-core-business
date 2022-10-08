this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_fonts_opensans,sidepanel,main_core,main_core_events,ui_buttons,ui_sidepanel_menu) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UI = BX.UI;
	var SidePanel = BX.SidePanel;

	function prepareOptions() {
	  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  options = Object.assign({}, options);
	  options.design = Object.assign({}, options.design || {});
	  options.design = _objectSpread({
	    margin: true,
	    section: true
	  }, options.design);
	  options.extensions = (options.extensions || []).concat(['ui.sidepanel.layout', 'ui.buttons']);

	  if (options.toolbar) {
	    options.extensions.push('ui.buttons.icons');
	  }

	  if (options.design.section) {
	    options.extensions.push('ui.sidepanel-content');
	  }

	  if (options.menu) {
	    options.extensions.push('ui.sidepanel.menu');
	  }

	  return options;
	}

	var _container = /*#__PURE__*/new WeakMap();

	var _containerFooter = /*#__PURE__*/new WeakMap();

	var _options = /*#__PURE__*/new WeakMap();

	var _menu = /*#__PURE__*/new WeakMap();

	var _getScrollWidth = /*#__PURE__*/new WeakSet();

	var _adjustFooter = /*#__PURE__*/new WeakSet();

	var _onMenuItemClick = /*#__PURE__*/new WeakSet();

	var Layout = /*#__PURE__*/function () {
	  babelHelpers.createClass(Layout, null, [{
	    key: "createContent",
	    value: function createContent() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      options = prepareOptions(options);
	      return top.BX.Runtime.loadExtension(options.extensions).then(function () {
	        return new Layout(options).render();
	      });
	    }
	  }, {
	    key: "createLayout",
	    value: function createLayout() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      options = prepareOptions(options);
	      return top.BX.Runtime.loadExtension(options.extensions).then(function () {
	        return new Layout(options);
	      });
	    }
	  }]);

	  function Layout() {
	    var _this = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Layout);

	    _classPrivateMethodInitSpec(this, _onMenuItemClick);

	    _classPrivateMethodInitSpec(this, _adjustFooter);

	    _classPrivateMethodInitSpec(this, _getScrollWidth);

	    _classPrivateFieldInitSpec(this, _container, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _containerFooter, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _options, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _menu, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _options, prepareOptions(options));
	    var menuOptions = babelHelpers.classPrivateFieldGet(this, _options).menu;

	    if (menuOptions) {
	      babelHelpers.classPrivateFieldSet(this, _menu, new ui_sidepanel_menu.Menu(Object.assign(menuOptions)));

	      if (main_core.Type.isUndefined(menuOptions.contentAttribute)) {
	        menuOptions.contentAttribute = 'data-menu-item-id';
	      }

	      if (menuOptions.contentAttribute) {
	        babelHelpers.classPrivateFieldGet(this, _menu).subscribe('click', function (event) {
	          _classPrivateMethodGet(_this, _onMenuItemClick, _onMenuItemClick2).call(_this, (event.getData() || {}).item);
	        });
	      }
	    }
	  }

	  babelHelpers.createClass(Layout, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!babelHelpers.classPrivateFieldGet(this, _container)) {
	        babelHelpers.classPrivateFieldSet(this, _container, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout\"></div>"]))));
	      }

	      return babelHelpers.classPrivateFieldGet(this, _container);
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu() {
	      return babelHelpers.classPrivateFieldGet(this, _menu);
	    }
	  }, {
	    key: "getFooterContainer",
	    value: function getFooterContainer() {
	      if (!babelHelpers.classPrivateFieldGet(this, _containerFooter)) {
	        babelHelpers.classPrivateFieldSet(this, _containerFooter, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-footer\"></div>"]))));
	      }

	      return babelHelpers.classPrivateFieldGet(this, _containerFooter);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      var content = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var promised = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	      if (babelHelpers.classPrivateFieldGet(this, _options).content && !promised) {
	        content = babelHelpers.classPrivateFieldGet(this, _options).content();

	        if (Object.prototype.toString.call(content) === "[object Promise]" || content.toString && content.toString() === "[object BX.Promise]") {
	          return content.then(function (content) {
	            return _this2.render(content, true);
	          });
	        }
	      }

	      var container = this.getContainer();
	      container.innerHTML = ''; // HEADER

	      if (babelHelpers.classPrivateFieldGet(this, _options).title) {
	        var title = main_core.Tag.safe(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["", ""])), babelHelpers.classPrivateFieldGet(this, _options).title);
	        var header = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-sidepanel-layout-header\">\n\t\t\t\t\t<div class=\"ui-sidepanel-layout-title\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), title);

	        if (main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _options).toolbar)) {
	          var toolbar = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-toolbar\"></div>"])));
	          babelHelpers.classPrivateFieldGet(this, _options).toolbar(_objectSpread({}, UI)).forEach(function (button) {
	            if (button instanceof ui_buttons.BaseButton) {
	              button.renderTo(toolbar);
	            } else if (main_core.Type.isDomNode(button)) {
	              toolbar.appendChild(button);
	            } else {
	              throw main_core.BaseError('Wrong button type ' + button);
	            }
	          });
	          header.appendChild(toolbar);
	        }

	        container.appendChild(header);
	      } // CONTENT


	      {
	        var design = babelHelpers.classPrivateFieldGet(this, _options).design;
	        var classes = ['ui-sidepanel-layout-content'];
	        var styles = [];

	        if (design.margin) {
	          if (design.margin === true) {
	            classes.push('ui-sidepanel-layout-content-margin');
	          } else {
	            styles.push('margin: ' + design.margin);
	          }
	        }

	        var contentElement = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\" style=\"", "\"></div>"])), classes.join(' '), styles.join('; '));
	        container.appendChild(contentElement);

	        if (babelHelpers.classPrivateFieldGet(this, _menu)) {
	          babelHelpers.classPrivateFieldGet(this, _menu).renderTo(contentElement);
	        }

	        contentElement.appendChild(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-content-inner\"></div>"]))));
	        contentElement = contentElement.lastElementChild;

	        if (design.section) {
	          contentElement.appendChild(main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-slider-section ui-sidepanel-layout-content-fill-height\"></div>"]))));
	          contentElement = contentElement.firstElementChild;
	        }

	        if (typeof content === 'string') {
	          contentElement.innerHTML = content;
	        } else if (content instanceof Element) {
	          contentElement.appendChild(content);
	        }

	        if (babelHelpers.classPrivateFieldGet(this, _menu)) {
	          _classPrivateMethodGet(this, _onMenuItemClick, _onMenuItemClick2).call(this, babelHelpers.classPrivateFieldGet(this, _menu).getActiveItem(), contentElement);
	        }
	      } // FOOTER

	      var isButtonsUndefined = typeof babelHelpers.classPrivateFieldGet(this, _options).buttons === 'undefined';

	      if (typeof babelHelpers.classPrivateFieldGet(this, _options).buttons === 'function' || isButtonsUndefined) {
	        var cancelButton = new ui_buttons.CancelButton({
	          onclick: function onclick() {
	            return SidePanel.Instance.close();
	          }
	        });
	        var closeButton = new ui_buttons.CloseButton({
	          onclick: function onclick() {
	            return SidePanel.Instance.close();
	          }
	        });

	        var defaults = _objectSpread(_objectSpread({}, UI), {}, {
	          cancelButton: cancelButton,
	          closeButton: closeButton
	        });

	        if (isButtonsUndefined) {
	          babelHelpers.classPrivateFieldGet(this, _options).buttons = function () {
	            return [closeButton];
	          };
	        }

	        var buttonList = babelHelpers.classPrivateFieldGet(this, _options).buttons(defaults);

	        if (buttonList && buttonList.length > 0) {
	          container.appendChild(main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-footer-anchor\"></div>"]))));
	          var _classes = ['ui-sidepanel-layout-buttons'];

	          if (babelHelpers.classPrivateFieldGet(this, _options).design.alignButtonsLeft) {
	            _classes.push('ui-sidepanel-layout-buttons-align-left');
	          }

	          var buttons = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"])), _classes.join(' '));
	          this.getFooterContainer().appendChild(buttons);
	          buttonList.forEach(function (button) {
	            if (button instanceof ui_buttons.BaseButton) {
	              button.renderTo(buttons);
	            } else if (main_core.Type.isDomNode(button)) {
	              buttons.appendChild(button);
	            } else {
	              throw main_core.BaseError('Wrong button type ' + button);
	            }
	          });
	          container.appendChild(this.getFooterContainer());
	        }
	      }

	      setTimeout(function () {
	        _this2.afterRender();
	      });
	      return container;
	    }
	  }, {
	    key: "afterRender",
	    value: function afterRender() {
	      _classPrivateMethodGet(this, _adjustFooter, _adjustFooter2).call(this);

	      var resizeHandler = main_core.Runtime.throttle(_classPrivateMethodGet(this, _adjustFooter, _adjustFooter2), 300, this);
	      main_core.Event.bind(window, "resize", resizeHandler);
	      var topSlider = SidePanel.Instance.getTopSlider();

	      if (topSlider) {
	        main_core_events.EventEmitter.subscribeOnce(topSlider, 'SidePanel.Slider:onDestroy', function () {
	          main_core.Event.unbind(window, "resize", resizeHandler);
	        });
	      }
	    }
	  }]);
	  return Layout;
	}();

	function _getScrollWidth2() {
	  var div = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<div style=\"overflow-y: scroll; width: 50px; height: 50px; opacity: 0; pointer-events: none; position: absolute;\"></div>"])));
	  document.body.appendChild(div);
	  var scrollWidth = div.offsetWidth - div.clientWidth;
	  main_core.Dom.remove(div);
	  return scrollWidth;
	}

	function _adjustFooter2() {
	  var parentSet = this.getContainer().parentNode;

	  if (parentSet.scrollWidth > parentSet.offsetWidth) {
	    main_core.Dom.style(this.getFooterContainer(), 'bottom', _classPrivateMethodGet(this, _getScrollWidth, _getScrollWidth2).call(this) + 'px');
	  } else {
	    main_core.Dom.style(this.getFooterContainer(), 'bottom', 0);
	  }
	}

	function _onMenuItemClick2(item) {
	  var container = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	  if (!item) {
	    return;
	  }

	  var id = item.getId();
	  var attr = babelHelpers.classPrivateFieldGet(this, _options).menu.contentAttribute;

	  if (!attr) {
	    return;
	  }

	  container = container || babelHelpers.classPrivateFieldGet(this, _container);
	  var nodes = container.querySelectorAll("[".concat(attr, "]"));
	  nodes = Array.prototype.slice.call(nodes);
	  nodes.forEach(function (node) {
	    node.hidden = node.getAttribute(attr) !== id;
	  });
	}

	exports.Layout = Layout;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX,BX,BX,BX.Event,BX.UI,BX.UI.SidePanel));
//# sourceMappingURL=bundle.js.map
