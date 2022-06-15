this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_base,main_core) {
	'use strict';

	function getDeltaFromEvent(event) {
	  var deltaX = event.deltaX;
	  var deltaY = -1 * event.deltaY;

	  if (main_core.Type.isUndefined(deltaX) || main_core.Type.isUndefined(deltaY)) {
	    deltaX = -1 * event.wheelDeltaX / 6;
	    deltaY = event.wheelDeltaY / 6;
	  }

	  if (event.deltaMode === 1) {
	    deltaX *= 10;
	    deltaY *= 10;
	  }
	  /** NaN checks */


	  if (Number.isNaN(deltaX) && Number.isNaN(deltaY)) {
	    deltaX = 0;
	    deltaY = event.wheelDelta;
	  }

	  return {
	    x: deltaX,
	    y: deltaY
	  };
	}

	function calculateDurationTransition(diff) {
	  var defaultDuration = 300;
	  return Math.min(400 / 500 * diff, defaultDuration);
	}

	function scrollTo(container, element) {
	  return new Promise(function (resolve) {
	    var elementTop = 0;
	    var duration = 0;

	    if (element) {
	      var defaultMargin = 20;
	      var elementMarginTop = Math.max(parseInt(main_core.Dom.style(element, 'margin-top')), defaultMargin);
	      var containerScrollTop = container.scrollTop;

	      if (!(container instanceof HTMLIFrameElement)) {
	        elementTop = element.offsetTop - (container.offsetTop || 0) - elementMarginTop;
	      } else {
	        containerScrollTop = container.contentWindow.scrollY;
	        elementTop = BX.pos(element).top - elementMarginTop - 100;
	      }

	      duration = calculateDurationTransition(Math.abs(elementTop - containerScrollTop));
	      var start = Math.max(containerScrollTop, 0);
	      var finish = Math.max(elementTop, 0);

	      if (start !== finish) {
	        new BX.easing({
	          duration: duration,
	          start: {
	            scrollTop: start
	          },
	          finish: {
	            scrollTop: finish
	          },
	          step: function step(state) {
	            if (!(container instanceof HTMLIFrameElement)) {
	              container.scrollTop = state.scrollTop;
	            } else {
	              container.contentWindow.scrollTo(0, Math.max(state.scrollTop, 0));
	            }
	          }
	        }).animate();
	        setTimeout(resolve, duration);
	      } else {
	        resolve();
	      }
	    } else {
	      resolve();
	    }
	  });
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-content-subtitle\">", "</div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-element landing-ui-panel-content-footer\"></div>\n\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-body-content\"></div>\n\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-body-sidebar\"></div>\n\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-element landing-ui-panel-content-body\"></div>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-title\"></div>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-element landing-ui-panel-content-header\"></div>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-content-overlay landing-ui-hide\" data-is-shown=\"false\" hidden></div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var Content = /*#__PURE__*/function (_BasePanel) {
	  babelHelpers.inherits(Content, _BasePanel);
	  babelHelpers.createClass(Content, null, [{
	    key: "createOverlay",
	    value: function createOverlay() {
	      return main_core.Tag.render(_templateObject());
	    }
	  }, {
	    key: "createHeader",
	    value: function createHeader() {
	      return main_core.Tag.render(_templateObject2());
	    }
	  }, {
	    key: "createTitle",
	    value: function createTitle() {
	      return main_core.Tag.render(_templateObject3());
	    }
	  }, {
	    key: "createBody",
	    value: function createBody() {
	      return main_core.Tag.render(_templateObject4());
	    }
	  }, {
	    key: "createSidebar",
	    value: function createSidebar() {
	      return main_core.Tag.render(_templateObject5());
	    }
	  }, {
	    key: "createContent",
	    value: function createContent() {
	      return main_core.Tag.render(_templateObject6());
	    }
	  }, {
	    key: "createFooter",
	    value: function createFooter() {
	      return main_core.Tag.render(_templateObject7());
	    }
	  }, {
	    key: "calculateTransitionDuration",
	    value: function calculateTransitionDuration() {
	      var diff = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      return calculateDurationTransition(diff);
	    }
	  }, {
	    key: "scrollTo",
	    value: function scrollTo$$1(container, element) {
	      return scrollTo(container, element);
	    }
	  }, {
	    key: "getDeltaFromEvent",
	    value: function getDeltaFromEvent$$1(event) {
	      return getDeltaFromEvent(event);
	    }
	  }]);

	  function Content(id) {
	    var _this;

	    var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Content);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Content).call(this, id, data));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "adjustActionsPanels", true);
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-content');
	    _this.data = Object.freeze(data);
	    _this.overlay = Content.createOverlay();
	    _this.header = Content.createHeader();
	    _this.title = Content.createTitle();
	    _this.body = Content.createBody();
	    _this.footer = Content.createFooter();
	    _this.sidebar = Content.createSidebar();
	    _this.content = Content.createContent();
	    _this.closeButton = new BX.Landing.UI.Button.BaseButton('close', {
	      className: 'landing-ui-panel-content-close',
	      onClick: function onClick() {
	        void _this.hide();

	        _this.emit('onCancel');
	      },
	      attrs: {
	        title: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_SLIDER_CLOSE')
	      }
	    });
	    _this.forms = new BX.Landing.UI.Collection.FormCollection();
	    _this.buttons = new BX.Landing.UI.Collection.ButtonCollection();
	    _this.sidebarButtons = new BX.Landing.UI.Collection.ButtonCollection();
	    _this.wheelEventName = main_core.Type.isNil(window.onwheel) ? window.onwheel : window.onmousewheel;
	    _this.onMouseWheel = _this.onMouseWheel.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMouseEnter = _this.onMouseEnter.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMouseLeave = _this.onMouseLeave.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.removeClass(_this.layout, 'landing-ui-hide');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-hide');
	    main_core.Dom.append(_this.sidebar, _this.body);
	    main_core.Dom.append(_this.content, _this.body);
	    main_core.Dom.append(_this.header, _this.layout);
	    main_core.Dom.append(_this.title, _this.header);
	    main_core.Dom.append(_this.body, _this.layout);
	    main_core.Dom.append(_this.footer, _this.layout);
	    main_core.Dom.append(_this.closeButton.layout, _this.layout);

	    if (main_core.Type.isString(data.className)) {
	      main_core.Dom.addClass(_this.layout, [data.className, "".concat(data.className, "-overlay")]);
	    }

	    if (main_core.Type.isString(data.subTitle) && data.subTitle !== '') {
	      _this.subTitle = main_core.Tag.render(_templateObject8(), data.subTitle);
	      main_core.Dom.append(_this.subTitle, _this.header);
	      main_core.Dom.addClass(_this.layout, 'landing-ui-panel-content-with-subtitle');
	    }

	    if (_this.data.showFromRight === true) {
	      _this.setLayoutClass('landing-ui-panel-show-from-right');
	    }

	    _this.init();

	    main_core.Event.bind(window.top, 'keydown', _this.onKeyDown.bind(babelHelpers.assertThisInitialized(_this)));
	    BX.Landing.PageObject.getEditorWindow();

	    if (_this.data.scrollAnimation) {
	      _this.scrollObserver = new IntersectionObserver(_this.onIntersecting.bind(babelHelpers.assertThisInitialized(_this)));
	    }

	    _this.checkReadyToSave = _this.checkReadyToSave.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Content, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;

	      main_core.Dom.append(this.overlay, document.body);
	      main_core.Event.bind(this.overlay, 'click', function () {
	        _this2.emit('onCancel');

	        void _this2.hide();
	      });
	      main_core.Event.bind(this.layout, 'mouseenter', this.onMouseEnter);
	      main_core.Event.bind(this.layout, 'mouseleave', this.onMouseLeave);
	      main_core.Event.bind(this.content, 'mouseenter', this.onMouseEnter);
	      main_core.Event.bind(this.content, 'mouseleave', this.onMouseLeave);
	      main_core.Event.bind(this.sidebar, 'mouseenter', this.onMouseEnter);
	      main_core.Event.bind(this.sidebar, 'mouseleave', this.onMouseLeave);
	      main_core.Event.bind(this.header, 'mouseenter', this.onMouseEnter);
	      main_core.Event.bind(this.header, 'mouseleave', this.onMouseLeave);
	      main_core.Event.bind(this.footer, 'mouseenter', this.onMouseEnter);
	      main_core.Event.bind(this.footer, 'mouseleave', this.onMouseLeave);

	      if ('title' in this.data) {
	        this.setTitle(this.data.title);
	      }

	      if ('footer' in this.data) {
	        if (main_core.Type.isArray(this.data.footer)) {
	          this.data.footer.forEach(function (item) {
	            if (item instanceof BX.Landing.UI.Button.BaseButton) {
	              _this2.appendFooterButton(item);
	            }

	            if (main_core.Type.isDomNode(item)) {
	              main_core.Dom.append(item, _this2.footer);
	            }
	          });
	        }
	      }
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onIntersecting",
	    value: function onIntersecting(items) {
	      items.forEach(function (item) {
	        if (item.isIntersecting) {
	          main_core.Dom.removeClass(item.target, 'landing-ui-is-not-visible');
	          main_core.Dom.addClass(item.target, 'landing-ui-is-visible');
	        } else {
	          main_core.Dom.addClass(item.target, 'landing-ui-is-not-visible');
	          main_core.Dom.removeClass(item.target, 'landing-ui-is-visible');
	        }
	      });
	    }
	  }, {
	    key: "onKeyDown",
	    value: function onKeyDown(event) {
	      if (event.keyCode === 27) {
	        this.emit('onCancel');
	        void this.hide();
	      }
	    }
	  }, {
	    key: "onMouseEnter",
	    value: function onMouseEnter(event) {
	      event.stopPropagation();
	      main_core.Event.bind(this.layout, this.wheelEventName, this.onMouseWheel);
	      main_core.Event.bind(this.layout, 'touchmove', this.onMouseWheel);

	      if (this.sidebar.contains(event.target) || this.content.contains(event.target) || this.header.contains(event.target) || this.footer.contains(event.target) || this.right && this.right.contains(event.target)) {
	        this.scrollTarget = event.currentTarget;
	      }
	    }
	  }, {
	    key: "onMouseLeave",
	    value: function onMouseLeave(event) {
	      event.stopPropagation();
	      BX.unbind(this.layout, this.wheelEventName, this.onMouseWheel);
	      BX.unbind(this.layout, 'touchmove', this.onMouseWheel);
	    }
	  }, {
	    key: "onMouseWheel",
	    value: function onMouseWheel(event) {
	      var _this3 = this;

	      event.preventDefault();
	      event.stopPropagation();
	      var delta = Content.getDeltaFromEvent(event);
	      var scrollTop = this.scrollTarget.scrollTop;
	      requestAnimationFrame(function () {
	        _this3.scrollTarget.scrollTop = scrollTop - delta.y;
	      });
	    }
	  }, {
	    key: "scrollTo",
	    value: function scrollTo$$1(element) {
	      void Content.scrollTo(this.content, element);
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.state === 'shown';
	    }
	  }, {
	    key: "shouldAdjustActionsPanels",
	    value: function shouldAdjustActionsPanels() {
	      return this.adjustActionsPanels;
	    } // eslint-disable-next-line no-unused-vars

	  }, {
	    key: "show",
	    value: function show(options) {
	      var _this4 = this;

	      if (!this.isShown()) {
	        if (this.shouldAdjustActionsPanels()) {
	          main_core.Dom.addClass(document.body, 'landing-ui-hide-action-panels');
	        }

	        void BX.Landing.Utils.Show(this.overlay);
	        return BX.Landing.Utils.Show(this.layout).then(function () {
	          _this4.state = 'shown';
	        });
	      }

	      return Promise.resolve(true);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      var _this5 = this;

	      if (this.isShown()) {
	        if (this.shouldAdjustActionsPanels()) {
	          main_core.Dom.removeClass(document.body, 'landing-ui-hide-action-panels');
	        }

	        void BX.Landing.Utils.Hide(this.overlay);
	        return BX.Landing.Utils.Hide(this.layout).then(function () {
	          _this5.state = 'hidden';
	        });
	      }

	      return Promise.resolve(true);
	    }
	  }, {
	    key: "appendForm",
	    value: function appendForm(form) {
	      this.forms.add(form);
	      main_core.Dom.append(form.getNode(), this.content);
	    }
	  }, {
	    key: "appendCard",
	    value: function appendCard(card) {
	      if (this.data.scrollAnimation) {
	        main_core.Dom.addClass(card.layout, 'landing-ui-is-not-visible');
	        this.scrollObserver.observe(card.layout);
	      }

	      main_core.Dom.append(card.layout, this.content);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.clearContent();
	      this.clearSidebar();
	      this.forms.clear();
	    }
	  }, {
	    key: "clearContent",
	    value: function clearContent() {
	      main_core.Dom.clean(this.content);
	    }
	  }, {
	    key: "clearSidebar",
	    value: function clearSidebar() {
	      main_core.Dom.clean(this.sidebar);
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      this.title.innerHTML = title;
	    }
	  }, {
	    key: "appendFooterButton",
	    value: function appendFooterButton(button) {
	      this.buttons.add(button);
	      main_core.Dom.append(button.layout, this.footer);
	    }
	  }, {
	    key: "appendSidebarButton",
	    value: function appendSidebarButton(button) {
	      this.sidebarButtons.add(button);
	      main_core.Dom.append(button.layout, this.sidebar);
	    }
	  }, {
	    key: "setOverlayClass",
	    value: function setOverlayClass(className) {
	      main_core.Dom.addClass(this.overlay, className);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(target) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Content.prototype), "renderTo", this).call(this, target);
	      main_core.Dom.append(this.overlay, target);
	    }
	  }, {
	    key: "checkReadyToSave",
	    value: function checkReadyToSave() {
	      var _this6 = this;

	      var canSave = true;
	      this.forms.forEach(function (form) {
	        form.fields.forEach(function (field) {
	          if (field.readyToSave === false) {
	            canSave = false;
	          }

	          if (!field.getListeners('onChangeReadyToSave').has(_this6.checkReadyToSave)) {
	            field.subscribe('onChangeReadyToSave', _this6.checkReadyToSave);
	          }
	        });
	      });
	      canSave ? this.enableSave() : this.disableSave();
	    }
	  }, {
	    key: "disableSave",
	    value: function disableSave() {
	      var saveButton = this.buttons.get('save_block_content');

	      if (saveButton) {
	        saveButton.disable();
	      }
	    }
	  }, {
	    key: "enableSave",
	    value: function enableSave() {
	      var saveButton = this.buttons.get('save_block_content');

	      if (saveButton) {
	        saveButton.enable();
	      }
	    }
	  }]);
	  return Content;
	}(landing_ui_panel_base.BasePanel);

	exports.Content = Content;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX));
//# sourceMappingURL=content.bundle.js.map
