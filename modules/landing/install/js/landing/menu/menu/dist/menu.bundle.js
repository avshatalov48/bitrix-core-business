this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_loc,landing_env,landing_main,landing_backend,landing_ui_form_menuform,landing_ui_panel_stylepanel,landing_menu_menuitem) {
	'use strict';

	function buildTree(root, selector) {
	  var parent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	  var depth = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
	  return babelHelpers.toConsumableArray(root.querySelectorAll(selector)).filter(function (element) {
	    return element.parentElement.closest(selector) === parent;
	  }).map(function (element) {
	    var newDepth = depth + 1;
	    return {
	      layout: element,
	      children: buildTree(element, selector, element, newDepth),
	      depth: depth
	    };
	  });
	}

	function makeFlatTree(tree) {
	  var acc = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	  tree.forEach(function (item) {
	    acc.push(item);
	    makeFlatTree(item.children, acc);
	  });
	  return acc;
	}

	function getNodeClass(type) {
	  if (type === 'link') {
	    return BX.Landing.Block.Node.Link;
	  }

	  if (type === 'img') {
	    return BX.Landing.Block.Node.Img;
	  }

	  if (type === 'icon') {
	    return BX.Landing.Block.Node.Icon;
	  }

	  if (type === 'embed') {
	    return BX.Landing.Block.Node.Embed;
	  }

	  if (type === 'map') {
	    return BX.Landing.Block.Node.Map;
	  }

	  if (type === 'component') {
	    return BX.Landing.Block.Node.Component;
	  }

	  return BX.Landing.Block.Node.Text;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<li class=\"", "\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</li>\n\t\t"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a class=\"", "\" href=\"", "\" target=\"", "\">", "</a>\n\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<ul class=\"", "\">", "</ul>\n\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-menu-add\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-menu-add-field\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span \n\t\t\t\t\tclass=\"landing-menu-add-field-apply\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t</span>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span \n\t\t\t\t\tclass=\"landing-menu-add-field-close\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t</span>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\ttype=\"text\" \n\t\t\t\t\tclass=\"landing-menu-add-field-input\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\tonkeydown=\"", "\"\n\t\t\t\t\t>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button \n\t\t\t\t\tclass=\"ui-btn ui-btn-light-border ui-btn-icon-add ui-btn-round landing-ui-menu-add-button\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.Menu
	 */

	var Menu = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(Menu, _Event$EventEmitter);

	  function Menu() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Menu);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Menu).call(this, options));

	    _this.setEventNamespace('BX.Landing.Menu.Menu');

	    _this.code = options.code;
	    _this.root = options.root;
	    _this.block = options.block;
	    _this.manifest = Object.freeze(babelHelpers.objectSpread({}, options.manifest));
	    _this.cache = new main_core.Cache.MemoryCache();

	    if (landing_env.Env.getInstance().getType() === 'KNOWLEDGE' || landing_env.Env.getInstance().getType() === 'GROUP') {
	      if (main_core.Dom.hasClass(_this.root.nextElementSibling, 'landing-menu-add')) {
	        main_core.Dom.remove(_this.root.nextElementSibling);
	      }

	      main_core.Dom.addClass(_this.root, 'landing-menu-root-list');
	      main_core.Dom.insertAfter(_this.getAddPageLayout(), _this.root);
	    }

	    main_core.Event.bind(_this.root, 'click', function (event) {
	      if (!landing_ui_panel_stylepanel.StylePanel.getInstance().isShown() && event.target.nodeName === 'A') {
	        event.preventDefault();
	        var href = main_core.Dom.attr(event.target, 'href');

	        if (href.startsWith('#landing')) {
	          var pageId = main_core.Text.toNumber(href.replace('#landing', ''));

	          _this.reloadPage(pageId);
	        }
	      }
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Menu, [{
	    key: "createMenuItem",
	    value: function createMenuItem(options) {
	      var _this2 = this;

	      var nodes = new BX.Landing.Collection.NodeCollection();
	      Object.entries(this.manifest.nodes).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            code = _ref2[0],
	            nodeManifest = _ref2[1];

	        var nodeElements = babelHelpers.toConsumableArray(options.layout.querySelectorAll(code)).filter(function (nodeElement) {
	          var elementParent = nodeElement.closest(_this2.manifest.item);
	          return elementParent === options.layout;
	        });

	        if (nodeElements.length > 0) {
	          var NodeClass = getNodeClass(nodeManifest.type);
	          nodeElements.forEach(function (nodeElement) {
	            nodes.push(new NodeClass({
	              node: nodeElement,
	              manifest: babelHelpers.objectSpread({}, nodeManifest, {
	                allowInlineEdit: false,
	                menuMode: true
	              })
	            }));
	          });
	        }
	      });
	      return new landing_menu_menuitem.MenuItem({
	        layout: options.layout,
	        children: options.children.map(function (itemOptions, index) {
	          return _this2.createMenuItem(babelHelpers.objectSpread({}, itemOptions, {
	            index: index
	          }));
	        }),
	        selector: "".concat(this.manifest.item, "@").concat(options.index),
	        depth: options.depth,
	        nodes: nodes
	      });
	    }
	  }, {
	    key: "getTree",
	    value: function getTree() {
	      var _this3 = this;

	      var item = this.manifest.item;
	      return buildTree(this.root, item).map(function (options, index) {
	        return _this3.createMenuItem(babelHelpers.objectSpread({}, options, {
	          index: index
	        }));
	      });
	    }
	  }, {
	    key: "getFlatTree",
	    value: function getFlatTree() {
	      return makeFlatTree(this.getTree());
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      return new landing_ui_form_menuform.MenuForm({
	        title: 'Menu',
	        type: 'menu',
	        code: this.code,
	        forms: this.getFlatTree().map(function (item) {
	          return item.getForm();
	        })
	      });
	    }
	  }, {
	    key: "getAddPageButton",
	    value: function getAddPageButton() {
	      var _this4 = this;

	      return this.cache.remember('addPageButton', function () {
	        return main_core.Tag.render(_templateObject(), _this4.onAddPageButtonClick.bind(_this4), landing_loc.Loc.getMessage('LANDING_MENU_CREATE_NEW_PAGE'));
	      });
	    }
	  }, {
	    key: "onAddPageTextInputKeydown",
	    value: function onAddPageTextInputKeydown(event) {
	      if (event.keyCode === 13) {
	        this.addPage();
	      }
	    }
	  }, {
	    key: "addPage",
	    value: function addPage() {
	      var _this5 = this;

	      var input = this.getAddPageInput();
	      var value = input.value;
	      input.value = '';
	      input.focus();

	      if (main_core.Type.isStringFilled(value)) {
	        var code = BX.translit(value, {
	          change_case: 'L',
	          replace_space: '-',
	          replace_other: ''
	        });
	        var backend = landing_backend.Backend.getInstance();
	        backend.createPage({
	          title: value,
	          menuCode: this.code,
	          blockId: this.block,
	          code: code
	        }).then(function (id) {
	          var li = _this5.createLi({
	            text: value,
	            href: "#landing".concat(id),
	            target: '_self',
	            children: []
	          });

	          main_core.Dom.append(li, _this5.root);
	          main_core.Dom.remove(_this5.getAddPageField());
	          main_core.Dom.removeClass(_this5.root, 'landing-menu-root-list-with-field');
	          main_core.Dom.removeClass(_this5.getAddPageLayout(), 'landing-menu-add-with-background');

	          _this5.reloadPage(id);
	        });
	      }
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "reloadPage",
	    value: function reloadPage(id) {
	      var main = landing_main.Main.getInstance();
	      var url = landing_env.Env.getInstance().getLandingEditorUrl({
	        landing: id
	      });
	      void main.reloadSlider(url);
	    }
	  }, {
	    key: "getAddPageInput",
	    value: function getAddPageInput() {
	      var _this6 = this;

	      return this.cache.remember('addPageTextInput', function () {
	        return main_core.Tag.render(_templateObject2(), landing_loc.Loc.getMessage('LANDING_MENU_CREATE_NEW_PAGE'), _this6.onAddPageTextInputKeydown.bind(_this6));
	      });
	    }
	  }, {
	    key: "onAddPageInputCloseButtonClick",
	    value: function onAddPageInputCloseButtonClick(event) {
	      event.preventDefault();
	      var input = this.getAddPageInput();
	      input.value = '';
	      main_core.Dom.removeClass(this.root, 'landing-menu-root-list-with-field');
	      main_core.Dom.removeClass(this.getAddPageLayout(), 'landing-menu-add-with-background');
	      main_core.Dom.remove(this.getAddPageField());
	      main_core.Dom.append(this.getAddPageButton(), this.getAddPageLayout());
	    }
	  }, {
	    key: "getAddPageInputCloseButton",
	    value: function getAddPageInputCloseButton() {
	      var _this7 = this;

	      return this.cache.remember('addPageInputCloseButton', function () {
	        return main_core.Tag.render(_templateObject3(), _this7.onAddPageInputCloseButtonClick.bind(_this7), landing_loc.Loc.getMessage('LANDING_MENU_CLOSE_BUTTON_LABEL'));
	      });
	    }
	  }, {
	    key: "getAddPageInputApplyButton",
	    value: function getAddPageInputApplyButton() {
	      var _this8 = this;

	      return this.cache.remember('addPageInputApplyButton', function () {
	        return main_core.Tag.render(_templateObject4(), _this8.onAddPageInputApplyButtonClick.bind(_this8), landing_loc.Loc.getMessage('LANDING_MENU_APPLY_BUTTON_LABEL'));
	      });
	    }
	  }, {
	    key: "onAddPageInputApplyButtonClick",
	    value: function onAddPageInputApplyButtonClick(event) {
	      event.preventDefault();
	      this.addPage();
	    }
	  }, {
	    key: "getAddPageField",
	    value: function getAddPageField() {
	      var _this9 = this;

	      return this.cache.remember('addPageInput', function () {
	        return main_core.Tag.render(_templateObject5(), _this9.getAddPageInput(), _this9.getAddPageInputApplyButton(), _this9.getAddPageInputCloseButton());
	      });
	    }
	  }, {
	    key: "getAddPageLayout",
	    value: function getAddPageLayout() {
	      var _this10 = this;

	      return this.cache.remember('addPageLayout', function () {
	        return main_core.Tag.render(_templateObject6(), _this10.getAddPageButton());
	      });
	    }
	  }, {
	    key: "onAddPageButtonClick",
	    value: function onAddPageButtonClick(event) {
	      event.preventDefault();
	      main_core.Dom.addClass(this.root, 'landing-menu-root-list-with-field');
	      main_core.Dom.addClass(this.getAddPageLayout(), 'landing-menu-add-with-background');
	      main_core.Dom.prepend(this.getAddPageField(), this.getAddPageLayout());
	      main_core.Dom.remove(this.getAddPageButton());
	      this.getAddPageInput().focus();
	    }
	  }, {
	    key: "createList",
	    value: function createList(items) {
	      var _this11 = this;

	      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'root';
	      var ulClassName = this.manifest[type].ulClassName;
	      return main_core.Tag.render(_templateObject7(), ulClassName, items.map(function (item) {
	        return _this11.createLi(item, type);
	      }));
	    }
	  }, {
	    key: "createA",
	    value: function createA(item) {
	      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'root';
	      var aClassName = this.manifest[type].aClassName;
	      return main_core.Tag.render(_templateObject8(), aClassName, item.href, item.target, main_core.Text.encode(item.text));
	    }
	  }, {
	    key: "createLi",
	    value: function createLi(item) {
	      var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'root';
	      var liClassName = this.manifest[type].liClassName;
	      return main_core.Tag.render(_templateObject9(), liClassName, this.createA(item, type), item.children ? this.createList(item.children, 'children') : undefined);
	    }
	  }, {
	    key: "rebuild",
	    value: function rebuild(items) {
	      var newList = this.createList(items);
	      main_core.Dom.replace(this.root, newList);
	      this.root = newList;
	    }
	  }]);
	  return Menu;
	}(main_core.Event.EventEmitter);

	exports.Menu = Menu;

}((this.BX.Landing.Menu = this.BX.Landing.Menu || {}),BX,BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Panel,BX.Landing.Menu));
//# sourceMappingURL=menu.bundle.js.map
