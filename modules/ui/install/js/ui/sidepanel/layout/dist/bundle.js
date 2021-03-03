this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,sidepanel,main_core,ui_buttons) {
	'use strict';

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-footer\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-slider-section ui-sidepanel-layout-content-fill-height\"></div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\" style=\"", "\"></div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout-toolbar\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-sidepanel-layout-header\">\n\t\t\t\t\t<div class=\"ui-sidepanel-layout-title\">", "</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-layout\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var UI = BX.UI;
	var SidePanel = BX.SidePanel;

	function prepareOptions() {
	  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  options = Object.assign({}, options);
	  options.design = Object.assign({}, options.design || {});
	  options.design = babelHelpers.objectSpread({
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

	  return options;
	}

	var _container = new WeakMap();

	var _options = new WeakMap();

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
	  }]);

	  function Layout() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Layout);

	    _container.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _options.set(this, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _options, prepareOptions(options));
	  }

	  babelHelpers.createClass(Layout, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!babelHelpers.classPrivateFieldGet(this, _container)) {
	        babelHelpers.classPrivateFieldSet(this, _container, main_core.Tag.render(_templateObject()));
	      }

	      return babelHelpers.classPrivateFieldGet(this, _container);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this = this;

	      var content = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var promised = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	      if (babelHelpers.classPrivateFieldGet(this, _options).content && !promised) {
	        content = babelHelpers.classPrivateFieldGet(this, _options).content();

	        if (Object.prototype.toString.call(content) === "[object Promise]" || content.toString && content.toString() === "[object BX.Promise]") {
	          return content.then(function (content) {
	            return _this.render(content, true);
	          });
	        }
	      }

	      var container = this.getContainer();
	      container.innerHTML = ''; // HEADER

	      if (babelHelpers.classPrivateFieldGet(this, _options).title) {
	        var title = main_core.Tag.safe(_templateObject2(), babelHelpers.classPrivateFieldGet(this, _options).title);
	        var header = main_core.Tag.render(_templateObject3(), title);

	        if (main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _options).toolbar)) {
	          var toolbar = main_core.Tag.render(_templateObject4());
	          babelHelpers.classPrivateFieldGet(this, _options).toolbar(babelHelpers.objectSpread({}, UI)).forEach(function (button) {
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

	        var contentElement = main_core.Tag.render(_templateObject5(), classes.join(' '), styles.join('; '));
	        container.appendChild(contentElement);

	        if (design.section) {
	          contentElement.appendChild(main_core.Tag.render(_templateObject6()));
	          contentElement = contentElement.firstElementChild;
	        }

	        if (typeof content === 'string') {
	          contentElement.innerHTML = content;
	        } else if (content instanceof Element) {
	          contentElement.appendChild(content);
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
	        var defaults = babelHelpers.objectSpread({}, UI, {
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
	          var footer = main_core.Tag.render(_templateObject7());
	          var _classes = ['ui-sidepanel-layout-buttons'];

	          if (babelHelpers.classPrivateFieldGet(this, _options).design.alignButtonsLeft) {
	            _classes.push('ui-sidepanel-layout-buttons-align-left');
	          }

	          var buttons = main_core.Tag.render(_templateObject8(), _classes.join(' '));
	          footer.appendChild(buttons);
	          buttonList.forEach(function (button) {
	            if (button instanceof ui_buttons.BaseButton) {
	              button.renderTo(buttons);
	            } else if (main_core.Type.isDomNode(button)) {
	              buttons.appendChild(button);
	            } else {
	              throw main_core.BaseError('Wrong button type ' + button);
	            }
	          });
	          container.appendChild(footer);
	        }
	      }

	      return container;
	    }
	  }]);
	  return Layout;
	}();

	exports.Layout = Layout;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX,BX,BX.UI));
//# sourceMappingURL=bundle.js.map
