/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,main_core) {
	'use strict';

	var _templateObject;
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _id = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _content = /*#__PURE__*/new WeakMap();
	var _separatorClass = /*#__PURE__*/new WeakMap();
	var _className = /*#__PURE__*/new WeakMap();
	var _isHidden = /*#__PURE__*/new WeakMap();
	var Row = /*#__PURE__*/function () {
	  function Row(params) {
	    babelHelpers.classCallCheck(this, Row);
	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _separatorClass, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec(this, _className, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isHidden, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id, main_core.Type.isNil(params.id) ? 'row_' + main_core.Text.getRandom(8) : params.id);
	    babelHelpers.classPrivateFieldSet(this, _content, params.content);
	    babelHelpers.classPrivateFieldSet(this, _separatorClass, params.separator === 'top' ? '--top-separator' : params.separator === 'bottom' ? '--bottom-separator' : '');
	    babelHelpers.classPrivateFieldSet(this, _className, main_core.Type.isStringFilled(params.className) ? params.className : '');
	    babelHelpers.classPrivateFieldSet(this, _isHidden, params.isHidden === true);
	  }
	  babelHelpers.createClass(Row, [{
	    key: "render",
	    value: function render() {
	      if (babelHelpers.classPrivateFieldGet(this, _node)) {
	        return babelHelpers.classPrivateFieldGet(this, _node);
	      }
	      babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"ui-section__row ", " ", "\" ", ">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _separatorClass), babelHelpers.classPrivateFieldGet(this, _className), babelHelpers.classPrivateFieldGet(this, _isHidden) ? 'hidden' : '', babelHelpers.classPrivateFieldGet(this, _content)));
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }, {
	    key: "append",
	    value: function append(content) {
	      main_core.Dom.append(content, this.render());
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      if (!main_core.Type.isDomNode(targetNode)) {
	        throw new Error('Target node must be HTMLElement');
	      }
	      return main_core.Dom.append(this.render(), targetNode);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.hide(this.render());
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.show(this.render());
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return babelHelpers.classPrivateFieldGet(this, _isHidden);
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      main_core.Dom.toggle(this.render());
	    }
	  }]);
	  return Row;
	}();

	var _templateObject$1, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _linkIconRender = /*#__PURE__*/new WeakSet();
	var _collapseIconRender = /*#__PURE__*/new WeakSet();
	var _getRowsWrapper = /*#__PURE__*/new WeakSet();
	var Section = /*#__PURE__*/function () {
	  function Section(params) {
	    babelHelpers.classCallCheck(this, Section);
	    _classPrivateMethodInitSpec(this, _getRowsWrapper);
	    _classPrivateMethodInitSpec(this, _collapseIconRender);
	    _classPrivateMethodInitSpec(this, _linkIconRender);
	    babelHelpers.defineProperty(this, "className", {
	      titleIcon: '',
	      arrowTop: '--chevron-up',
	      arrowDown: '--chevron-down',
	      arrowRight: '--chevron-right',
	      bodyActive: '--body-active '
	    });
	    babelHelpers.defineProperty(this, "singleLink", {
	      href: '',
	      isSidePanel: false
	    });
	    this.title = main_core.Type.isString(params.title) ? params.title : '';
	    main_core.Type.isStringFilled(params.titleIconClasses) ? this.className.titleIcon = params.titleIconClasses : '';
	    main_core.Type.isStringFilled(params.iconArrowDown) ? this.className.arrowDown = params.iconArrowDown : '';
	    main_core.Type.isStringFilled(params.iconArrowTop) ? this.className.arrowTop = params.iconArrowTop : '';
	    main_core.Type.isStringFilled(params.iconArrowRight) ? this.className.arrowRight = params.iconArrowRight : '';
	    if (main_core.Type.isStringFilled(params.bodyActive)) {
	      this.className.bodyActive += params.bodyActive;
	    }
	    this.isOpen = main_core.Type.isBoolean(params.isOpen) ? params.isOpen : true;
	    this.isEnable = main_core.Type.isBoolean(params.isEnable) ? params.isEnable : true;
	    this.canCollapse = params.canCollapse !== false;
	    this.id = main_core.Type.isNil(params.id) ? 'section_' + main_core.Text.getRandom(8) : params.id;
	    this.bannerCode = main_core.Type.isStringFilled(params.bannerCode) ? params.bannerCode : null;
	    if (params.singleLink) {
	      main_core.Type.isStringFilled(params.singleLink.href) ? this.singleLink.href = params.singleLink.href : '';
	      main_core.Type.isBoolean(params.singleLink.isSidePanel) ? this.singleLink.isSidePanel = params.singleLink.isSidePanel : '';
	    }
	  }
	  babelHelpers.createClass(Section, [{
	    key: "render",
	    value: function render() {
	      var _this = this;
	      if (this.content) {
	        return this.content;
	      }
	      this.content = this.getContent();
	      var triggerElements = this.content.querySelectorAll('.ui-section__header');
	      var elementList = babelHelpers.toConsumableArray(triggerElements);
	      if (this.canCollapse && !this.singleLink.href) {
	        var _iterator = _createForOfIteratorHelper(elementList),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var element = _step.value;
	            if (main_core.Type.isElementNode(element)) {
	              element.addEventListener('click', this.toggle.bind(this));
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      } else if (this.singleLink.href) {
	        var _iterator2 = _createForOfIteratorHelper(elementList),
	          _step2;
	        try {
	          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	            var _element = _step2.value;
	            if (main_core.Type.isElementNode(_element)) {
	              main_core.Event.bind(_element, 'click', function () {
	                if (_this.singleLink.isSidePanel) {
	                  BX.SidePanel.Instance.open(_this.singleLink.href);
	                } else {
	                  window.open(_this.singleLink.href, '_blank');
	                }
	              });
	            }
	          }
	        } catch (err) {
	          _iterator2.e(err);
	        } finally {
	          _iterator2.f();
	        }
	      }
	      return this.content;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "toggle",
	    value: function toggle(open) {
	      var withAnimation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var container = this.content;
	      var iconNode = this.render().querySelector('.ui-section__collapse-icon');
	      this.isOpen = open === true || open === false ? open : !this.isOpen;
	      var innerContainer = this.content.querySelector('.ui-section__section-body_inner');
	      main_core.Dom.removeClass(innerContainer, 'ui-section__section-toggle-animation');
	      if (withAnimation !== false) {
	        main_core.Dom.addClass(innerContainer, 'ui-section__section-toggle-animation');
	      }
	      if (this.isOpen) {
	        main_core.Dom.removeClass(iconNode, this.className.arrowDown);
	        main_core.Dom.addClass(iconNode, this.className.arrowTop);
	        main_core.Dom.addClass(container, this.className.bodyActive);
	      } else {
	        main_core.Dom.addClass(iconNode, this.className.arrowDown);
	        main_core.Dom.removeClass(iconNode, this.className.arrowTop);
	        main_core.Dom.removeClass(container, this.className.bodyActive);
	      }
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      if (this.sectionWrapper) {
	        return this.sectionWrapper;
	      }
	      this.sectionWrapper = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__wrapper ", " ", "\">\n\t\t\t\t<div class=\"ui-section__header ", "\">\n\t\t\t\t\t<span class=\"ui-section__title-icon ", "\"></span>\n\t\t\t\t\t<span class=\"ui-section__title\">", "</span>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-section__separator ", "\"></div>\n\t\t\t\t<div class=\"ui-section__content ui-section__section-body_inner\">\n\t\t\t\t\t<div class=\"ui-section__section-section-body_container\">\n\t\t\t\t\t\t<div class=\"ui-section__row_box\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.id, this.isOpen ? this.className.bodyActive : '', this.canCollapse || this.singleLink.href ? 'clickable' : '', !this.title ? '--hidden' : '', this.className.titleIcon, this.title, this.isEnable ? '' : this.renderLockElement(), this.singleLink.href ? _classPrivateMethodGet(this, _linkIconRender, _linkIconRender2).call(this) : _classPrivateMethodGet(this, _collapseIconRender, _collapseIconRender2).call(this), !this.title ? '--hidden' : '');
	      return this.sectionWrapper;
	    }
	  }, {
	    key: "addRows",
	    value: function addRows(rows) {
	      var _this2 = this;
	      rows.forEach(function (item) {
	        _this2.append(item.render());
	      });
	    }
	  }, {
	    key: "addRow",
	    value: function addRow(row) {
	      this.append(row.render());
	    }
	  }, {
	    key: "append",
	    value: function append(content) {
	      main_core.Dom.append(content, _classPrivateMethodGet(this, _getRowsWrapper, _getRowsWrapper2).call(this));
	    }
	  }, {
	    key: "prepend",
	    value: function prepend(content) {
	      main_core.Dom.prepend(content, _classPrivateMethodGet(this, _getRowsWrapper, _getRowsWrapper2).call(this));
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      if (!main_core.Type.isDomNode(targetNode)) {
	        throw new Error('Target node must be HTMLElement');
	      }
	      return main_core.Dom.append(this.render(), targetNode);
	    }
	  }, {
	    key: "getBannerCode",
	    value: function getBannerCode() {
	      return this.bannerCode;
	    }
	  }, {
	    key: "showBanner",
	    value: function showBanner() {
	      if (this.getBannerCode()) {
	        BX.UI.InfoHelper.show(this.getBannerCode());
	      }
	    }
	  }, {
	    key: "renderLockElement",
	    value: function renderLockElement() {
	      var _this3 = this;
	      var lockElement = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-section__title-icon ui-icon-set --lock field-has-lock\" onclick=\"event.stopPropagation()\"></span>"])));
	      main_core.Event.bind(lockElement, 'click', function () {
	        _this3.showBanner();
	      });
	      return lockElement;
	    }
	  }]);
	  return Section;
	}();
	function _linkIconRender2() {
	  return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-section__collapse-icon clickable ui-icon-set ", "\"></span>"])), this.className.arrowRight);
	}
	function _collapseIconRender2() {
	  if (this.canCollapse) {
	    return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-section__collapse-icon clickable ui-icon-set ", "\"></span>"])), this.isOpen ? this.className.arrowTop : this.className.arrowDown);
	  } else {
	    return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-section__collapse-icon\"></span>"])));
	  }
	}
	function _getRowsWrapper2() {
	  if (this.rowsWrapper) {
	    return this.rowsWrapper;
	  }
	  this.rowsWrapper = this.render().querySelector('.ui-section__row_box');
	  return this.rowsWrapper;
	}

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _popup = /*#__PURE__*/new WeakMap();
	var HelpMessage = /*#__PURE__*/function () {
	  function HelpMessage(id, node, message) {
	    babelHelpers.classCallCheck(this, HelpMessage);
	    _classPrivateFieldInitSpec$1(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup(id, node, {
	      content: message,
	      darkMode: true,
	      autoHide: true,
	      angle: true,
	      offsetLeft: 20,
	      bindOptions: {
	        position: 'bottom'
	      },
	      closeByEsc: true
	    }));
	  }
	  babelHelpers.createClass(HelpMessage, [{
	    key: "getPopup",
	    value: function getPopup() {
	      return babelHelpers.classPrivateFieldGet(this, _popup);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      babelHelpers.classPrivateFieldGet(this, _popup).show();
	    }
	  }]);
	  return HelpMessage;
	}();

	var _templateObject$2;
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _id$1 = /*#__PURE__*/new WeakMap();
	var _isHidden$1 = /*#__PURE__*/new WeakMap();
	var _node$1 = /*#__PURE__*/new WeakMap();
	var SeparatorRow = /*#__PURE__*/function () {
	  function SeparatorRow(params) {
	    babelHelpers.classCallCheck(this, SeparatorRow);
	    _classPrivateFieldInitSpec$2(this, _id$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _isHidden$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(this, _node$1, {
	      writable: true,
	      value: void 0
	    });
	    params = main_core.Type.isNil(params) ? {} : params;
	    babelHelpers.classPrivateFieldSet(this, _isHidden$1, params.isHidden === true);
	    babelHelpers.classPrivateFieldSet(this, _id$1, main_core.Type.isNil(params.id) ? 'row_' + main_core.Text.getRandom(8) : params.id);
	  }
	  babelHelpers.createClass(SeparatorRow, [{
	    key: "render",
	    value: function render() {
	      if (babelHelpers.classPrivateFieldGet(this, _node$1)) {
	        return babelHelpers.classPrivateFieldGet(this, _node$1);
	      }
	      babelHelpers.classPrivateFieldSet(this, _node$1, main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__separator-row\" ", "></div>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _isHidden$1) ? 'hidden' : ''));
	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }, {
	    key: "append",
	    value: function append(content) {
	      main_core.Dom.append(content, this.render());
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(targetNode) {
	      if (!main_core.Type.isDomNode(targetNode)) {
	        throw new Error('Target node must be HTMLElement');
	      }
	      return main_core.Dom.append(this.render(), targetNode);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.hide(this.render());
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.show(this.render());
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      main_core.Dom.toggle(this.render());
	    }
	  }]);
	  return SeparatorRow;
	}();

	exports.Row = Row;
	exports.Section = Section;
	exports.HelpMessage = HelpMessage;
	exports.SeparatorRow = SeparatorRow;

}((this.BX.UI = this.BX.UI || {}),BX.Main,BX));
//# sourceMappingURL=section.bundle.js.map
