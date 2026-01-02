/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_iconSet_api_core,main_core_events,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _adjustPopupPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustPopupPosition");
	var _handlePopupShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupShow");
	var _handlePopupClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupClose");
	class NotificationPanel extends main_core_events.EventEmitter {
	  constructor(options) {
	    var _this$options, _this$options$id;
	    super();
	    Object.defineProperty(this, _handlePopupClose, {
	      value: _handlePopupClose2
	    });
	    Object.defineProperty(this, _handlePopupShow, {
	      value: _handlePopupShow2
	    });
	    Object.defineProperty(this, _adjustPopupPosition, {
	      value: _adjustPopupPosition2
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('UI:NotificationPanel');
	    this.options = {
	      ...this.getDefaultOptions(),
	      ...(main_core.Type.isPlainObject(options) ? options : {})
	    };
	    (_this$options$id = (_this$options = this.options).id) != null ? _this$options$id : _this$options.id = main_core.Text.getRandom();
	  }
	  getDefaultOptions() {
	    return {
	      styleClass: '',
	      backgroundColor: '#F2FEE2',
	      textColor: null,
	      crossColor: null,
	      leftIcon: null,
	      rightButtons: [],
	      showCloseIcon: true,
	      zIndex: null,
	      events: {}
	    };
	  }
	  getContainer() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = main_core.Tag.render(_t || (_t = _`
			<div class="ui-notification-panel__container">
				${0}
				${0}
			</div>
		`), this.getContent(), this.getFooter());
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  }
	  getButtonsContainer(buttons) {
	    const buttonsContainer = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-notification-panel__buttons-container"></div>`));
	    buttons.forEach(button => {
	      button.renderTo(buttonsContainer);
	    });
	    return buttonsContainer;
	  }
	  getContent() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-notification-panel__content"></div>`));
	    if (this.options.leftIcon) {
	      this.options.leftIcon.size = 28;
	      this.options.leftIcon.renderTo(babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	      main_core.Dom.append(main_core.Tag.render(_t4 || (_t4 = _`<div class="ui-notification-panel__left-icon-divider"></div>`)), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	    }
	    if (main_core.Type.isElementNode(this.options.content)) {
	      main_core.Dom.append(this.options.content, babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	    } else if (main_core.Type.isString(this.options.content)) {
	      const textColor = this.options.textColor;
	      main_core.Dom.append(main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-notification-panel__text" ${0}>${0}</div>`), textColor ? `style="color: ${textColor}"` : '', this.options.content), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	    }
	    main_core.Dom.append(this.getFooter(), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	  }
	  getFooter() {
	    const footer = main_core.Tag.render(_t6 || (_t6 = _`<div class="ui-notification-panel__footer"></div>`));
	    if (this.options.rightButtons) {
	      main_core.Dom.append(this.getButtonsContainer(this.options.rightButtons), footer);
	    }
	    if (this.options.showCloseIcon) {
	      main_core.Dom.append(this.getCloseButton(), footer);
	    }
	    return footer;
	  }
	  getCloseButton() {
	    const crossColor = this.options.crossColor;
	    return main_core.Tag.render(_t7 || (_t7 = _`
			<div 
				class="ui-notification-panel__close-button ui-icon-set --cross-45"
				onclick="${0}"
				${0}
			>
			</div>
		`), this.hideByButton.bind(this), crossColor ? `style="--ui-icon-set__icon-color: ${crossColor}"` : '');
	  }
	  getPopup() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2;
	    (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup))[_popup]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_popup] = main_popup.PopupWindowManager.create({
	      id: this.options.id,
	      content: this.getContent(),
	      background: this.options.backgroundColor,
	      fixed: true,
	      bindElement: {
	        left: 0,
	        top: 0
	      },
	      className: `ui-notification-panel__container ${this.options.styleClass}`,
	      animation: {
	        showClassName: 'ui-notification-panel__show',
	        closeClassName: 'ui-notification-panel__hide',
	        closeAnimationType: 'transition'
	      },
	      events: {
	        onShow: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupShow)[_handlePopupShow].bind(this),
	        onClose: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupClose)[_handlePopupClose].bind(this)
	      }
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  show() {
	    const popup = this.getPopup();
	    popup.show();
	    if (this.options.zIndex) {
	      popup.getZIndexComponent().setZIndex(this.options.zIndex);
	    }
	  }
	  hide() {
	    this.getPopup().close();
	  }
	  hideByButton() {
	    var _this$options$events;
	    (_this$options$events = this.options.events) == null ? void 0 : _this$options$events.onHideByButton == null ? void 0 : _this$options$events.onHideByButton();
	    this.emit('onHideByButton');
	    this.hide();
	  }
	}
	function _adjustPopupPosition2() {
	  const containerWidth = this.getContent().offsetWidth;
	  const windowWidth = window.innerWidth;
	  const offsetLeft = (windowWidth - containerWidth) / 2;
	  this.getPopup().setOffset({
	    offsetLeft,
	    offsetTop: 0
	  });
	}
	function _handlePopupShow2() {
	  var _this$options$events2;
	  babelHelpers.classPrivateFieldLooseBase(this, _adjustPopupPosition)[_adjustPopupPosition]();
	  (_this$options$events2 = this.options.events) == null ? void 0 : _this$options$events2.onShow == null ? void 0 : _this$options$events2.onShow();
	  this.emit('onShow');
	}
	function _handlePopupClose2() {
	  var _this$options$events3;
	  (_this$options$events3 = this.options.events) == null ? void 0 : _this$options$events3.onHide == null ? void 0 : _this$options$events3.onHide();
	  this.emit('onHide');
	}

	exports.NotificationPanel = NotificationPanel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI.IconSet,BX.Event,BX.Main));
//# sourceMappingURL=notification-panel.bundle.js.map
