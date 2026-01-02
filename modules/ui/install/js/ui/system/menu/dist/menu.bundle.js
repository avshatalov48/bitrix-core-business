/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,ui_iconSet_main,ui_buttons,ui_cnt,ui_iconSet_api_core,ui_iconSet_outline,main_core) {
	'use strict';

	var _delta = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delta");
	var _position = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("position");
	var _update = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	class Mouse {
	  constructor() {
	    Object.defineProperty(this, _delta, {
	      writable: true,
	      value: {
	        top: 0,
	        left: 0
	      }
	    });
	    Object.defineProperty(this, _position, {
	      writable: true,
	      value: {
	        top: 0,
	        left: 0
	      }
	    });
	    Object.defineProperty(this, _update, {
	      writable: true,
	      value: event => {
	        const position = {
	          top: event.clientY + window.scrollY,
	          left: event.clientX + window.scrollX
	        };
	        babelHelpers.classPrivateFieldLooseBase(this, _delta)[_delta] = {
	          top: position.top - babelHelpers.classPrivateFieldLooseBase(this, _position)[_position].top,
	          left: position.left - babelHelpers.classPrivateFieldLooseBase(this, _position)[_position].left
	        };
	        babelHelpers.classPrivateFieldLooseBase(this, _position)[_position] = position;
	      }
	    });
	    main_core.Event.bind(window, 'mousemove', babelHelpers.classPrivateFieldLooseBase(this, _update)[_update]);
	  }
	  getPosition() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _position)[_position];
	  }
	  getDelta() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _delta)[_delta];
	  }
	}
	const mouse = new Mouse();

	const MenuItemDesign = Object.freeze({
	  Default: 'default',
	  Accent1: 'accent-1',
	  Accent2: 'accent-2',
	  Alert: 'alert',
	  Copilot: 'copilot',
	  Disabled: 'disabled'
	});
	const MenuSectionDesign = Object.freeze({
	  Default: 'default',
	  Accent: 'accent'
	});
	const MenuRichHeaderDesign = Object.freeze({
	  Default: 'default',
	  Copilot: 'copilot'
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16;
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _callbacks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callbacks");
	var _subMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subMenu");
	var _element = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("element");
	var _showTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showTimeout");
	var _closeTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeTimeout");
	var _subMenuHovered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subMenuHovered");
	var _onMouseEnter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMouseEnter");
	var _onMouseLeave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMouseLeave");
	var _onSubMenuItemClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onSubMenuItemClick");
	var _onFirstShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onFirstShow");
	var _onShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onShow");
	var _onClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClose");
	var _renderHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHeader");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	var _renderLock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLock");
	var _renderBadgeText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderBadgeText");
	var _renderSubtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSubtitle");
	var _renderButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtons");
	var _renderCheck = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCheck");
	var _renderExtra = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExtra");
	var _renderCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCounter");
	var _renderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderIcon");
	var _renderArrow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderArrow");
	class MenuItem {
	  constructor(options, callbacks) {
	    Object.defineProperty(this, _renderArrow, {
	      value: _renderArrow2
	    });
	    Object.defineProperty(this, _renderIcon, {
	      value: _renderIcon2
	    });
	    Object.defineProperty(this, _renderCounter, {
	      value: _renderCounter2
	    });
	    Object.defineProperty(this, _renderExtra, {
	      value: _renderExtra2
	    });
	    Object.defineProperty(this, _renderCheck, {
	      value: _renderCheck2
	    });
	    Object.defineProperty(this, _renderButtons, {
	      value: _renderButtons2
	    });
	    Object.defineProperty(this, _renderSubtitle, {
	      value: _renderSubtitle2
	    });
	    Object.defineProperty(this, _renderBadgeText, {
	      value: _renderBadgeText2
	    });
	    Object.defineProperty(this, _renderLock, {
	      value: _renderLock2
	    });
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _renderHeader, {
	      value: _renderHeader2
	    });
	    Object.defineProperty(this, _onSubMenuItemClick, {
	      value: _onSubMenuItemClick2
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _callbacks, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _subMenu, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _element, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showTimeout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _closeTimeout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _subMenuHovered, {
	      writable: true,
	      value: void 0
	    });
	    this.showSubMenu = () => {
	      var _babelHelpers$classPr, _babelHelpers$classPr2;
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenuHovered)[_subMenuHovered] = false;
	      (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _subMenu))[_subMenu]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_subMenu] = new Menu({
	        ...babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subMenu,
	        targetContainer: babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks].getTargetContainer(),
	        autoHide: false,
	        items: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subMenu.items.map(itemOptions => {
	          if (!itemOptions) {
	            return null;
	          }
	          return {
	            ...itemOptions,
	            onClick: () => babelHelpers.classPrivateFieldLooseBase(this, _onSubMenuItemClick)[_onSubMenuItemClick](itemOptions)
	          };
	        }),
	        offsetLeft: babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetWidth,
	        offsetTop: -babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetHeight,
	        bindOptions: {
	          forceBindPosition: true,
	          forceTop: true,
	          forceLeft: true
	        },
	        events: {
	          onFirstShow: babelHelpers.classPrivateFieldLooseBase(this, _onFirstShow)[_onFirstShow],
	          onShow: babelHelpers.classPrivateFieldLooseBase(this, _onShow)[_onShow],
	          onClose: babelHelpers.classPrivateFieldLooseBase(this, _onClose)[_onClose],
	          onDestroy: babelHelpers.classPrivateFieldLooseBase(this, _onClose)[_onClose]
	        }
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].show(babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]);
	    };
	    this.adjustSubMenu = () => {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu]) {
	        return;
	      }
	      let offsetLeft = babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetWidth;
	      let offsetTop = -babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetHeight;
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().setOffset({
	        offsetLeft,
	        offsetTop
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().adjustPosition();
	      const targetContainer = babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks].getTargetContainer();
	      const targetIsBody = targetContainer === document.body;
	      const targetRect = {
	        ...targetContainer.getBoundingClientRect().toJSON(),
	        ...(targetIsBody ? {
	          top: 0
	        } : null),
	        ...(targetIsBody ? {
	          right: window.innerWidth
	        } : null),
	        ...(targetIsBody ? {
	          bottom: window.innerHeight
	        } : null),
	        ...(targetIsBody ? {
	          left: 0
	        } : null)
	      };
	      let popupRect = babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopupContainer().getBoundingClientRect();
	      if (popupRect.right >= targetRect.right) {
	        offsetLeft = -popupRect.width;
	      }
	      if (popupRect.bottom >= targetRect.bottom) {
	        offsetTop = -popupRect.height;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().setOffset({
	        offsetLeft,
	        offsetTop
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().adjustPosition();
	      popupRect = babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopupContainer().getBoundingClientRect();
	      if (popupRect.left <= targetRect.left) {
	        offsetLeft = babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetWidth;
	      }
	      if (popupRect.top <= targetRect.top) {
	        offsetTop = -babelHelpers.classPrivateFieldLooseBase(this, _element)[_element].offsetHeight;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().setOffset({
	        offsetLeft,
	        offsetTop
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopup().adjustPosition();
	    };
	    this.closeSubMenu = () => {
	      var _babelHelpers$classPr3;
	      clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _showTimeout)[_showTimeout]);
	      (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu]) == null ? void 0 : _babelHelpers$classPr3.close();
	    };
	    Object.defineProperty(this, _onMouseEnter, {
	      writable: true,
	      value: () => {
	        var _babelHelpers$classPr4, _babelHelpers$classPr5;
	        if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].design === MenuItemDesign.Disabled) {
	          return;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _subMenuHovered)[_subMenuHovered] = false;
	        (_babelHelpers$classPr4 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks]).onMouseEnter) == null ? void 0 : _babelHelpers$classPr4.call(_babelHelpers$classPr5);
	        if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subMenu) {
	          clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _closeTimeout)[_closeTimeout]);
	          babelHelpers.classPrivateFieldLooseBase(this, _showTimeout)[_showTimeout] = setTimeout(this.showSubMenu, 200);
	        }
	      }
	    });
	    Object.defineProperty(this, _onMouseLeave, {
	      writable: true,
	      value: event => {
	        var _babelHelpers$classPr6;
	        clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _showTimeout)[_showTimeout]);
	        const subMenuContainer = (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu]) == null ? void 0 : _babelHelpers$classPr6.getPopupContainer();
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _subMenuHovered)[_subMenuHovered] && subMenuContainer && !subMenuContainer.contains(event.relatedTarget)) {
	          const distance = mouse.getPosition().left - subMenuContainer.getBoundingClientRect().left;
	          const distanceDelta = Math.abs(distance) - Math.abs(distance + mouse.getDelta().left);
	          if (distanceDelta <= 1) {
	            this.closeSubMenu();
	          } else {
	            this.closeSubMenuWithTimeout();
	          }
	        }
	      }
	    });
	    Object.defineProperty(this, _onFirstShow, {
	      writable: true,
	      value: () => {
	        main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu].getPopupContainer(), 'mouseenter', () => {
	          clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _closeTimeout)[_closeTimeout]);
	          babelHelpers.classPrivateFieldLooseBase(this, _subMenuHovered)[_subMenuHovered] = true;
	        });
	      }
	    });
	    Object.defineProperty(this, _onShow, {
	      writable: true,
	      value: () => {
	        this.adjustSubMenu();
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _element)[_element], '--hovered');
	      }
	    });
	    Object.defineProperty(this, _onClose, {
	      writable: true,
	      value: () => {
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _element)[_element], '--hovered');
	      }
	    });
	    const defaultItemOptions = {
	      closeOnSubItemClick: true
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = {
	      ...defaultItemOptions,
	      ...options
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks] = callbacks;
	  }
	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options];
	  }
	  getSubMenu() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu];
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _element)[_element];
	    }
	    const uiButtonOptions = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].uiButtonOptions;
	    const isUiButton = Boolean(uiButtonOptions);
	    const classNameIsUiButton = isUiButton ? ' --is-ui-button' : '';
	    const classNameDesign = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].design ? ` --${babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].design}` : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _element)[_element] = main_core.Tag.render(_t || (_t = _`
			<div class="ui-popup-menu-item${0}${0}">
				${0}
			</div>
		`), classNameIsUiButton, classNameDesign, isUiButton ? new ui_buttons.Button(uiButtonOptions).render() : main_core.Tag.render(_t2 || (_t2 = _`
						<button
							class="ui-popup-menu-item-action"
							title="${0}"
							onclick="${0}"
							onmouseenter="${0}"
							onmouseleave="${0}"
						>${0}${0}</button>
					`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].onClick, babelHelpers.classPrivateFieldLooseBase(this, _onMouseEnter)[_onMouseEnter], babelHelpers.classPrivateFieldLooseBase(this, _onMouseLeave)[_onMouseLeave], babelHelpers.classPrivateFieldLooseBase(this, _renderHeader)[_renderHeader](), babelHelpers.classPrivateFieldLooseBase(this, _renderButtons)[_renderButtons]()));
	    return babelHelpers.classPrivateFieldLooseBase(this, _element)[_element];
	  }
	  closeSubMenuWithTimeout() {
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _closeTimeout)[_closeTimeout]);
	    babelHelpers.classPrivateFieldLooseBase(this, _closeTimeout)[_closeTimeout] = setTimeout(this.closeSubMenu, 200);
	  }
	  destroy() {
	    var _babelHelpers$classPr7;
	    (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _subMenu)[_subMenu]) == null ? void 0 : _babelHelpers$classPr7.destroy();
	  }
	}
	function _onSubMenuItemClick2(item) {
	  item.onClick == null ? void 0 : item.onClick();
	  if (!item.subMenu && babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].closeOnSubItemClick) {
	    var _babelHelpers$classPr8, _babelHelpers$classPr9;
	    (_babelHelpers$classPr8 = (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _callbacks)[_callbacks]).onSubMenuItemClick) == null ? void 0 : _babelHelpers$classPr8.call(_babelHelpers$classPr9);
	  }
	}
	function _renderHeader2() {
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-popup-menu-item-header">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle](), babelHelpers.classPrivateFieldLooseBase(this, _renderSubtitle)[_renderSubtitle]());
	}
	function _renderTitle2() {
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-popup-menu-item-title">
				${0}
				<div class="ui-popup-menu-item-title-text">${0}</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderLock)[_renderLock](), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title), babelHelpers.classPrivateFieldLooseBase(this, _renderBadgeText)[_renderBadgeText]());
	}
	function _renderLock2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].isLocked) {
	    return '';
	  }
	  return main_core.Tag.render(_t5 || (_t5 = _`
			<div class="ui-popup-menu-item-lock">
				<div class="ui-icon-set --${0}"></div>
			</div>
		`), ui_iconSet_api_core.Outline.LOCK_L);
	}
	function _renderBadgeText2() {
	  var _babelHelpers$classPr10;
	  if (!main_core.Type.isStringFilled((_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].badgeText) == null ? void 0 : _babelHelpers$classPr10.title)) {
	    return '';
	  }
	  const color = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].badgeText.color;
	  const style = color ? `--badge-color: ${color};` : '';
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="ui-popup-menu-item-badge-text" style="${0}">
				${0}
			</div>
		`), style, main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].badgeText.title));
	}
	function _renderSubtitle2() {
	  if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subtitle)) {
	    return '';
	  }
	  return main_core.Tag.render(_t7 || (_t7 = _`
			<div class="ui-popup-menu-item-subtitle">${0}</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subtitle));
	}
	function _renderButtons2() {
	  return main_core.Tag.render(_t8 || (_t8 = _`
			<div class="ui-popup-menu-item-buttons">
				${0}
				${0}
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderCheck)[_renderCheck](), babelHelpers.classPrivateFieldLooseBase(this, _renderExtra)[_renderExtra](), babelHelpers.classPrivateFieldLooseBase(this, _renderCounter)[_renderCounter](), babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](), babelHelpers.classPrivateFieldLooseBase(this, _renderArrow)[_renderArrow]());
	}
	function _renderCheck2() {
	  if (!main_core.Type.isBoolean(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].isSelected)) {
	    return '';
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].isSelected) {
	    return main_core.Tag.render(_t9 || (_t9 = _`
				<div class="ui-popup-menu-item-check"></div>
			`));
	  }
	  return main_core.Tag.render(_t10 || (_t10 = _`
			<div class="ui-popup-menu-item-check">
				<div class="ui-icon-set --${0}"></div>
			</div>
		`), ui_iconSet_api_core.Outline.CHECK_L);
	}
	function _renderExtra2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].extraIcon) {
	    return '';
	  }
	  const extra = main_core.Tag.render(_t11 || (_t11 = _`
			<div class="ui-popup-menu-item-extra ${0}">
				<div class="ui-icon-set --${0}"></div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].extraIcon.isSelected ? '--selected' : '', babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].extraIcon.icon);
	  main_core.Event.bind(extra, 'click', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].extraIcon.onClick();
	    event.stopPropagation();
	  }, true);
	  return extra;
	}
	function _renderCounter2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].counter) {
	    return '';
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].counter.value) {
	    return main_core.Tag.render(_t12 || (_t12 = _`
				<div class="ui-popup-menu-item-counter"></div>
			`));
	  }
	  return main_core.Tag.render(_t13 || (_t13 = _`
			<div class="ui-popup-menu-item-counter">
				${0}
			</div>
		`), new ui_cnt.Counter({
	    color: ui_cnt.CounterColor.DANGER,
	    ...babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].counter
	  }).render());
	}
	function _renderIcon2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].icon) {
	    return main_core.Tag.render(_t14 || (_t14 = _`
				<div class="ui-popup-menu-item-icon">
					<div class="ui-icon-set --${0}"></div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].icon);
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].svg) {
	    return main_core.Tag.render(_t15 || (_t15 = _`
				<div class="ui-popup-menu-item-svg">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].svg);
	  }
	  return '';
	}
	function _renderArrow2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].subMenu) {
	    return '';
	  }
	  return main_core.Tag.render(_t16 || (_t16 = _`
			<div class="ui-popup-menu-item-arrow">
				<div class="ui-icon-set --${0}"></div>
			</div>
		`), ui_iconSet_api_core.Outline.CHEVRON_RIGHT_L);
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1;
	var _options$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _shouldHide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldHide");
	var _onPopupClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPopupClose");
	var _onPopupDestroy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPopupDestroy");
	var _onBeforeAdjustPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onBeforeAdjustPosition");
	var _prepareItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareItems");
	var _onItemClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onItemClick");
	var _onSubMenuItemClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onSubMenuItemClick");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _renderRichHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRichHeader");
	var _getRichHeaderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRichHeaderIcon");
	var _renderRichHeaderSubtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRichHeaderSubtitle");
	var _renderRichHeaderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRichHeaderIcon");
	var _renderItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderItems");
	var _renderSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSection");
	var _renderSectionTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSectionTitle");
	class Menu {
	  constructor(_options2) {
	    Object.defineProperty(this, _renderSectionTitle, {
	      value: _renderSectionTitle2
	    });
	    Object.defineProperty(this, _renderSection, {
	      value: _renderSection2
	    });
	    Object.defineProperty(this, _renderItems, {
	      value: _renderItems2
	    });
	    Object.defineProperty(this, _renderRichHeaderIcon, {
	      value: _renderRichHeaderIcon2
	    });
	    Object.defineProperty(this, _renderRichHeaderSubtitle, {
	      value: _renderRichHeaderSubtitle2
	    });
	    Object.defineProperty(this, _getRichHeaderIcon, {
	      value: _getRichHeaderIcon2
	    });
	    Object.defineProperty(this, _renderRichHeader, {
	      value: _renderRichHeader2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _prepareItems, {
	      value: _prepareItems2
	    });
	    Object.defineProperty(this, _options$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _shouldHide, {
	      writable: true,
	      value: event => {
	        const notSelfClick = !this.getPopupContainer().contains(event.target);
	        const notSubMenuClick = !babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].some(item => {
	          var _item$getSubMenu, _item$getSubMenu$getP;
	          return (_item$getSubMenu = item.getSubMenu()) == null ? void 0 : (_item$getSubMenu$getP = _item$getSubMenu.getPopupContainer()) == null ? void 0 : _item$getSubMenu$getP.contains(event.target);
	        });
	        return notSelfClick && notSubMenuClick;
	      }
	    });
	    Object.defineProperty(this, _onPopupClose, {
	      writable: true,
	      value: () => {
	        var _babelHelpers$classPr;
	        babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].forEach(item => item.closeSubMenu());
	        (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].events) == null ? void 0 : _babelHelpers$classPr.onClose == null ? void 0 : _babelHelpers$classPr.onClose();
	      }
	    });
	    Object.defineProperty(this, _onPopupDestroy, {
	      writable: true,
	      value: () => {
	        var _babelHelpers$classPr2;
	        (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].events) == null ? void 0 : _babelHelpers$classPr2.onDestroy == null ? void 0 : _babelHelpers$classPr2.onDestroy();
	        this.destroy();
	      }
	    });
	    Object.defineProperty(this, _onBeforeAdjustPosition, {
	      writable: true,
	      value: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].forEach(item => item.adjustSubMenu());
	      }
	    });
	    Object.defineProperty(this, _onItemClick, {
	      writable: true,
	      value: itemOptions => {
	        itemOptions.onClick == null ? void 0 : itemOptions.onClick();
	        if (!itemOptions.subMenu && babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].closeOnItemClick) {
	          this.close();
	        }
	      }
	    });
	    Object.defineProperty(this, _onSubMenuItemClick$1, {
	      writable: true,
	      value: () => {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].closeOnItemClick) {
	          this.close();
	        }
	      }
	    });
	    const defaultOptions = {
	      noAllPaddings: true,
	      autoHide: true,
	      closeByEsc: true,
	      autoHideHandler: babelHelpers.classPrivateFieldLooseBase(this, _shouldHide)[_shouldHide],
	      closeOnItemClick: true
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = {
	      ...defaultOptions,
	      ..._options2
	    };
	  }
	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1];
	  }
	  getPopup() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  getPopupContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getPopupContainer();
	  }
	  show(bindElement) {
	    var _babelHelpers$classPr3, _babelHelpers$classPr4, _babelHelpers$classPr5, _babelHelpers$classPr6;
	    (_babelHelpers$classPr4 = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _items))[_items]) != null ? _babelHelpers$classPr4 : _babelHelpers$classPr3[_items] = babelHelpers.classPrivateFieldLooseBase(this, _prepareItems)[_prepareItems](babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].items);
	    (_babelHelpers$classPr6 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _popup))[_popup]) != null ? _babelHelpers$classPr6 : _babelHelpers$classPr5[_popup] = new main_popup.Popup({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1],
	      content: babelHelpers.classPrivateFieldLooseBase(this, _render)[_render](),
	      events: {
	        ...babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].events,
	        onClose: babelHelpers.classPrivateFieldLooseBase(this, _onPopupClose)[_onPopupClose],
	        onDestroy: babelHelpers.classPrivateFieldLooseBase(this, _onPopupDestroy)[_onPopupDestroy],
	        onBeforeAdjustPosition: babelHelpers.classPrivateFieldLooseBase(this, _onBeforeAdjustPosition)[_onBeforeAdjustPosition]
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setBindElement(bindElement != null ? bindElement : babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].bindElement);
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].show();
	  }
	  updateItems(itemsOptions) {
	    var _babelHelpers$classPr7, _babelHelpers$classPr8;
	    const openedItem = (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items]) == null ? void 0 : _babelHelpers$classPr7.find(item => {
	      var _item$getSubMenu2, _item$getSubMenu2$get;
	      return (_item$getSubMenu2 = item.getSubMenu()) == null ? void 0 : (_item$getSubMenu2$get = _item$getSubMenu2.getPopup()) == null ? void 0 : _item$getSubMenu2$get.isShown();
	    });
	    (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items]) == null ? void 0 : _babelHelpers$classPr8.forEach(item => item.destroy());
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = babelHelpers.classPrivateFieldLooseBase(this, _prepareItems)[_prepareItems](itemsOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	    if (openedItem && !(openedItem != null && openedItem.getSubMenu().getOptions().closeOnItemClick)) {
	      var _babelHelpers$classPr9;
	      (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].find(item => item.getOptions().id === openedItem.getOptions().id)) == null ? void 0 : _babelHelpers$classPr9.showSubMenu();
	    }
	  }
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	  }
	  destroy() {
	    var _babelHelpers$classPr10;
	    (_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items]) == null ? void 0 : _babelHelpers$classPr10.forEach(item => item.destroy());
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].destroy();
	  }
	}
	function _prepareItems2(itemsOptions) {
	  const items = itemsOptions.map(itemOptions => {
	    if (!itemOptions) {
	      return null;
	    }
	    const item = new MenuItem({
	      ...itemOptions,
	      onClick: () => babelHelpers.classPrivateFieldLooseBase(this, _onItemClick)[_onItemClick](itemOptions)
	    }, {
	      getTargetContainer: () => this.getPopup().getTargetContainer(),
	      onMouseEnter: () => items.filter(it => it !== item).forEach(it => it.closeSubMenuWithTimeout()),
	      onSubMenuItemClick: babelHelpers.classPrivateFieldLooseBase(this, _onSubMenuItemClick$1)[_onSubMenuItemClick$1]
	    });
	    return item;
	  }).filter(it => it);
	  return items;
	}
	function _render2() {
	  var _babelHelpers$classPr11, _babelHelpers$classPr12;
	  (_babelHelpers$classPr12 = (_babelHelpers$classPr11 = babelHelpers.classPrivateFieldLooseBase(this, _container))[_container]) != null ? _babelHelpers$classPr12 : _babelHelpers$classPr11[_container] = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="ui-popup-menu-container"></div>
		`));
	  const itemsContainer = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="ui-popup-menu-items">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderRichHeader)[_renderRichHeader](), babelHelpers.classPrivateFieldLooseBase(this, _renderItems)[_renderItems]());
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	  main_core.Dom.append(itemsContainer, babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	  return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	}
	function _renderRichHeader2() {
	  var _babelHelpers$classPr13;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader) {
	    return '';
	  }
	  const design = (_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.design) != null ? _babelHelpers$classPr13 : MenuRichHeaderDesign.Default;
	  const richHeader = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<div class="ui-popup-menu-rich-header --${0}">
				<div class="ui-popup-menu-rich-header-image">
					<div class="ui-icon-set --${0}"></div>
				</div>
				<div class="ui-popup-menu-rich-header-header">
					${0}
					<div class="ui-popup-menu-rich-header-title">
						${0}
					</div>
				</div>
				<div class="ui-popup-menu-rich-header-buttons">
					${0}
				</div>
			</div>
		`), design, babelHelpers.classPrivateFieldLooseBase(this, _getRichHeaderIcon)[_getRichHeaderIcon](design), babelHelpers.classPrivateFieldLooseBase(this, _renderRichHeaderSubtitle)[_renderRichHeaderSubtitle](), babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.title, babelHelpers.classPrivateFieldLooseBase(this, _renderRichHeaderIcon)[_renderRichHeaderIcon]());
	  if (babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.onClick) {
	    main_core.Event.bind(richHeader, 'click', babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.onClick);
	  }
	  return richHeader;
	}
	function _getRichHeaderIcon2(design) {
	  var _MenuRichHeaderDesign;
	  return (_MenuRichHeaderDesign = {
	    [MenuRichHeaderDesign.Default]: ui_iconSet_api_core.Main.DIAMOND,
	    [MenuRichHeaderDesign.Copilot]: ui_iconSet_api_core.Main.COPILOT_AI
	  }[design]) != null ? _MenuRichHeaderDesign : ui_iconSet_api_core.Main.DIAMOND;
	}
	function _renderRichHeaderSubtitle2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.subtitle) {
	    return '';
	  }
	  return main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
			<div class="ui-popup-menu-rich-header-subtitle">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.subtitle);
	}
	function _renderRichHeaderIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.icon) {
	    return '';
	  }
	  return main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
			<div class="ui-popup-menu-rich-header-icon">
				<div class="ui-icon-set --${0}"></div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].richHeader.icon);
	}
	function _renderItems2() {
	  var _itemsBySection$baseS, _itemsBySection$baseS2, _babelHelpers$classPr14, _babelHelpers$classPr15;
	  const baseSection = 'base';
	  const itemsBySection = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].reduce((result, item) => {
	    var _item$getOptions$sect, _result$sectionCode;
	    const sectionCode = (_item$getOptions$sect = item.getOptions().sectionCode) != null ? _item$getOptions$sect : baseSection;
	    const sectionItems = (_result$sectionCode = result[sectionCode]) != null ? _result$sectionCode : [];
	    return {
	      ...result,
	      [sectionCode]: [...sectionItems, item]
	    };
	  }, {});
	  return [...((_itemsBySection$baseS = (_itemsBySection$baseS2 = itemsBySection[baseSection]) == null ? void 0 : _itemsBySection$baseS2.map(item => item.render())) != null ? _itemsBySection$baseS : []), ...((_babelHelpers$classPr14 = (_babelHelpers$classPr15 = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].sections) == null ? void 0 : _babelHelpers$classPr15.flatMap((options, index) => {
	    var _items$map;
	    const items = itemsBySection[options.code];
	    if (!items) {
	      return null;
	    }
	    const isFirstSection = !itemsBySection[baseSection] && index === 0;
	    const withoutTitle = !options.title;
	    return [!(isFirstSection && withoutTitle) && babelHelpers.classPrivateFieldLooseBase(this, _renderSection)[_renderSection](options), ...((_items$map = items.map(item => item.render())) != null ? _items$map : [])];
	  })) != null ? _babelHelpers$classPr14 : []).filter(it => it)];
	}
	function _renderSection2(options) {
	  var _options$design;
	  return main_core.Tag.render(_t6$1 || (_t6$1 = _$1`
			<div class="ui-popup-menu-section --${0}">
				${0}
				<div class="ui-popup-menu-section-divider"></div>
			</div>
		`), (_options$design = options.design) != null ? _options$design : MenuSectionDesign.Default, babelHelpers.classPrivateFieldLooseBase(this, _renderSectionTitle)[_renderSectionTitle](options.title));
	}
	function _renderSectionTitle2(title) {
	  if (!title) {
	    return '';
	  }
	  return main_core.Tag.render(_t7$1 || (_t7$1 = _$1`
			<div class="ui-popup-menu-section-title">${0}</div>
		`), title);
	}

	exports.Menu = Menu;
	exports.MenuItemDesign = MenuItemDesign;
	exports.MenuSectionDesign = MenuSectionDesign;
	exports.MenuRichHeaderDesign = MenuRichHeaderDesign;

}((this.BX.UI.System = this.BX.UI.System || {}),BX.Main,BX,BX.UI,BX.UI,BX.UI.IconSet,BX,BX));
//# sourceMappingURL=menu.bundle.js.map
