/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_popup,ui_iconSet_api_core,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	const AnglePosition = Object.freeze({
	  TOP: 'top',
	  LEFT: 'left',
	  BOTTOM: 'bottom',
	  RIGHT: 'right'
	});
	const PromoVideoPopupEvents = Object.freeze({
	  ACCEPT: 'accept',
	  HIDE: 'hide'
	});
	const PromoVideoPopupButtonPosition = Object.freeze({
	  LEFT: 'left',
	  RIGHT: 'right',
	  CENTER: 'center'
	});
	var _videoSrc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoSrc");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _colors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("colors");
	var _targetOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetOptions");
	var _angleOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("angleOptions");
	var _offset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("offset");
	var _videoContainerMinHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("videoContainerMinHeight");
	var _buttonOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttonOptions");
	var _useOverlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useOverlay");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _iniPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iniPopup");
	var _getPopupClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupClassname");
	var _getAnimationCloseClassName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAnimationCloseClassName");
	var _renderPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPopupContent");
	var _renderVideo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderVideo");
	var _renderAcceptButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAcceptButton");
	var _renderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderIcon");
	var _getPopupContentVariablesStyles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupContentVariablesStyles");
	var _getOptionsTitleColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOptionsTitleColor");
	var _getOptionsIconColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOptionsIconColor");
	var _getOptionsButtonColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOptionsButtonColor");
	var _validateOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateOptions");
	var _isIconExist = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isIconExist");
	var _getButtonOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtonOptions");
	var _getDefaultButtonOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultButtonOptions");
	var _getPopupOverlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupOverlay");
	var _getPopupFooterElementClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupFooterElementClassname");
	class PromoVideoPopup extends main_core_events.EventEmitter {
	  constructor(_options) {
	    var _options$width, _options$targetOption, _options$button;
	    super(_options);
	    Object.defineProperty(this, _getPopupFooterElementClassname, {
	      value: _getPopupFooterElementClassname2
	    });
	    Object.defineProperty(this, _getPopupOverlay, {
	      value: _getPopupOverlay2
	    });
	    Object.defineProperty(this, _getDefaultButtonOptions, {
	      value: _getDefaultButtonOptions2
	    });
	    Object.defineProperty(this, _getButtonOptions, {
	      value: _getButtonOptions2
	    });
	    Object.defineProperty(this, _isIconExist, {
	      value: _isIconExist2
	    });
	    Object.defineProperty(this, _validateOptions, {
	      value: _validateOptions2
	    });
	    Object.defineProperty(this, _getOptionsButtonColor, {
	      value: _getOptionsButtonColor2
	    });
	    Object.defineProperty(this, _getOptionsIconColor, {
	      value: _getOptionsIconColor2
	    });
	    Object.defineProperty(this, _getOptionsTitleColor, {
	      value: _getOptionsTitleColor2
	    });
	    Object.defineProperty(this, _getPopupContentVariablesStyles, {
	      value: _getPopupContentVariablesStyles2
	    });
	    Object.defineProperty(this, _renderIcon, {
	      value: _renderIcon2
	    });
	    Object.defineProperty(this, _renderAcceptButton, {
	      value: _renderAcceptButton2
	    });
	    Object.defineProperty(this, _renderVideo, {
	      value: _renderVideo2
	    });
	    Object.defineProperty(this, _renderPopupContent, {
	      value: _renderPopupContent2
	    });
	    Object.defineProperty(this, _getAnimationCloseClassName, {
	      value: _getAnimationCloseClassName2
	    });
	    Object.defineProperty(this, _getPopupClassname, {
	      value: _getPopupClassname2
	    });
	    Object.defineProperty(this, _iniPopup, {
	      value: _iniPopup2
	    });
	    Object.defineProperty(this, _videoSrc, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _colors, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _targetOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _angleOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _offset, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _videoContainerMinHeight, {
	      writable: true,
	      value: 255
	    });
	    Object.defineProperty(this, _buttonOptions, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _useOverlay, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('UI.PromoVideoPopup');
	    babelHelpers.classPrivateFieldLooseBase(this, _validateOptions)[_validateOptions](_options);
	    babelHelpers.classPrivateFieldLooseBase(this, _videoSrc)[_videoSrc] = _options.videoSrc;
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = _options.title;
	    babelHelpers.classPrivateFieldLooseBase(this, _width)[_width] = (_options$width = _options.width) != null ? _options$width : PromoVideoPopup.getWidth();
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = _options.text;
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = babelHelpers.classPrivateFieldLooseBase(this, _isIconExist)[_isIconExist](_options.icon) ? _options.icon : ui_iconSet_api_core.Main.B_24;
	    babelHelpers.classPrivateFieldLooseBase(this, _colors)[_colors] = _options.colors;
	    babelHelpers.classPrivateFieldLooseBase(this, _targetOptions)[_targetOptions] = (_options$targetOption = _options.targetOptions) != null ? _options$targetOption : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _angleOptions)[_angleOptions] = _options.angleOptions || false;
	    babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset] = _options.offset;
	    babelHelpers.classPrivateFieldLooseBase(this, _videoContainerMinHeight)[_videoContainerMinHeight] = _options.videoContainerMinHeight;
	    babelHelpers.classPrivateFieldLooseBase(this, _buttonOptions)[_buttonOptions] = (_options$button = _options.button) != null ? _options$button : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _useOverlay)[_useOverlay] = _options.useOverlay === true;
	  }

	  /**
	   * @deprecated
	   */
	  static getWidth() {
	    return 498;
	  }
	  show() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _iniPopup)[_iniPopup]();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].isShown()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].show();
	  }
	  hide() {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr.close();
	  }
	  isShown() {
	    var _babelHelpers$classPr2;
	    return Boolean((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr2.isShown());
	  }
	  adjustPosition() {
	    var _babelHelpers$classPr3;
	    (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr3.adjustPosition({
	      forceBindPosition: true
	    });
	  }
	  getWidth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _width)[_width];
	  }
	  setTargetOptions(targetOptions) {
	    babelHelpers.classPrivateFieldLooseBase(this, _targetOptions)[_targetOptions] = targetOptions;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setBindElement(targetOptions);
	    }
	    return this;
	  }
	}
	function _iniPopup2() {
	  var _babelHelpers$classPr4, _babelHelpers$classPr5;
	  const styles = getComputedStyle(document.body);
	  const backgroundPrimary = styles.getPropertyValue('--ui-color-background-primary');
	  const backgroundPrimaryRgb = styles.getPropertyValue('--ui-color-background-primary-rgb');
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _targetOptions)[_targetOptions],
	    cacheable: false,
	    width: babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	    borderRadius: '16px',
	    angle: babelHelpers.classPrivateFieldLooseBase(this, _angleOptions)[_angleOptions],
	    content: babelHelpers.classPrivateFieldLooseBase(this, _renderPopupContent)[_renderPopupContent](),
	    closeByEsc: true,
	    autoHide: true,
	    closeIcon: true,
	    background: `rgba(${backgroundPrimaryRgb}, 0.5)`,
	    contentBackground: backgroundPrimary,
	    contentPadding: 12,
	    contentBorderRadius: '8px',
	    overlay: babelHelpers.classPrivateFieldLooseBase(this, _getPopupOverlay)[_getPopupOverlay](),
	    className: babelHelpers.classPrivateFieldLooseBase(this, _getPopupClassname)[_getPopupClassname](),
	    events: {
	      onPopupClose: () => {
	        setTimeout(() => {
	          this.emit(PromoVideoPopupEvents.HIDE);
	          babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].destroy();
	          babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = null;
	        }, 300);
	      }
	    },
	    animation: {
	      showClassName: '--show',
	      closeClassName: babelHelpers.classPrivateFieldLooseBase(this, _getAnimationCloseClassName)[_getAnimationCloseClassName](),
	      closeAnimationType: 'animation'
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setOffset({
	    offsetTop: (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset]) == null ? void 0 : _babelHelpers$classPr4.top,
	    offsetLeft: (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _offset)[_offset]) == null ? void 0 : _babelHelpers$classPr5.left
	  });
	}
	function _getPopupClassname2() {
	  var _babelHelpers$classPr6, _babelHelpers$classPr7;
	  let classNames = ['ui__promo-video-popup'];
	  if (((_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _angleOptions)[_angleOptions]) == null ? void 0 : _babelHelpers$classPr6.position) === AnglePosition.RIGHT) {
	    classNames = [...classNames, '--from-right'];
	  }
	  if (((_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _angleOptions)[_angleOptions]) == null ? void 0 : _babelHelpers$classPr7.position) === AnglePosition.TOP) {
	    classNames = [...classNames, '--from-top'];
	  }
	  return classNames.join(' ');
	}
	function _getAnimationCloseClassName2() {
	  var _babelHelpers$classPr8;
	  switch ((_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _angleOptions)[_angleOptions]) == null ? void 0 : _babelHelpers$classPr8.position) {
	    case AnglePosition.RIGHT:
	      {
	        return '--close-left';
	      }
	    case AnglePosition.TOP:
	      {
	        return '--close-bottom';
	      }
	    default:
	      {
	        return '--close';
	      }
	  }
	}
	function _renderPopupContent2() {
	  return main_core.Tag.render(_t || (_t = _`
			<div
				class="ui__promo-video-popup-content"
				style="${0}"
			>
				<div class="ui__promo-video-popup-content_header">
					<div class="ui__promo-video-popup-content_header-icon">
						${0}
					</div>
					<div class="ui__promo-video-popup-content_header-title">
						${0}
					</div>
				</div>
				<div class="ui__promo-video-popup-content_promo-text">
					${0}
				</div>
				<div
					class="ui__promo-video-popup-content_promo-video-wrapper"
					style="min-height: ${0}"
				>
					${0}
				</div>
				<div class="${0}">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getPopupContentVariablesStyles)[_getPopupContentVariablesStyles](), babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](), babelHelpers.classPrivateFieldLooseBase(this, _title)[_title], babelHelpers.classPrivateFieldLooseBase(this, _text)[_text], `${babelHelpers.classPrivateFieldLooseBase(this, _videoContainerMinHeight)[_videoContainerMinHeight]}px`, babelHelpers.classPrivateFieldLooseBase(this, _renderVideo)[_renderVideo](), babelHelpers.classPrivateFieldLooseBase(this, _getPopupFooterElementClassname)[_getPopupFooterElementClassname](), babelHelpers.classPrivateFieldLooseBase(this, _renderAcceptButton)[_renderAcceptButton]());
	}
	function _renderVideo2() {
	  const videoElement = main_core.Tag.render(_t2 || (_t2 = _`
			<video
				src="${0}"
				autoplay
				preload
				loop
				class="ui__promo-video-popup-content_promo-video"
			></video>
		`), babelHelpers.classPrivateFieldLooseBase(this, _videoSrc)[_videoSrc]);

	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-events-binding
	  videoElement.addEventListener('canplay', () => {
	    videoElement.muted = true;
	    videoElement.play();
	  });
	  return videoElement;
	}
	function _renderAcceptButton2() {
	  const buttonOptions = babelHelpers.classPrivateFieldLooseBase(this, _getButtonOptions)[_getButtonOptions]();
	  const btn = new ui_buttons.Button({
	    color: buttonOptions.color,
	    text: buttonOptions.text,
	    size: buttonOptions.size,
	    round: true,
	    onclick: () => {
	      this.emit(PromoVideoPopupEvents.ACCEPT);
	    }
	  });
	  return btn.render();
	}
	function _renderIcon2() {
	  const color = getComputedStyle(document.body).getPropertyValue('--ui-color-on-primary');
	  const icon = new ui_iconSet_api_core.Icon({
	    color,
	    size: 18,
	    icon: babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]
	  });
	  return icon.render();
	}
	function _getPopupContentVariablesStyles2() {
	  const cssVariables = {};
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getOptionsTitleColor)[_getOptionsTitleColor]()) {
	    cssVariables['--ui__promo-video-popup_title-color'] = babelHelpers.classPrivateFieldLooseBase(this, _getOptionsTitleColor)[_getOptionsTitleColor]();
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getOptionsIconColor)[_getOptionsIconColor]()) {
	    cssVariables['--ui__promo-video-popup_icon-color'] = babelHelpers.classPrivateFieldLooseBase(this, _getOptionsIconColor)[_getOptionsIconColor]();
	  }
	  return Object.entries(cssVariables).map(([variable, value]) => {
	    return `${variable}: ${value}`;
	  }).join(';');
	}
	function _getOptionsTitleColor2() {
	  var _babelHelpers$classPr9;
	  return (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _colors)[_colors]) == null ? void 0 : _babelHelpers$classPr9.title;
	}
	function _getOptionsIconColor2() {
	  var _babelHelpers$classPr10;
	  return (_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _colors)[_colors]) == null ? void 0 : _babelHelpers$classPr10.iconBackground;
	}
	function _getOptionsButtonColor2() {
	  var _babelHelpers$classPr11;
	  return (_babelHelpers$classPr11 = babelHelpers.classPrivateFieldLooseBase(this, _colors)[_colors]) == null ? void 0 : _babelHelpers$classPr11.button;
	}
	function _validateOptions2(options) {
	  var _options$colors, _options$colors2, _options$colors3;
	  const title = options == null ? void 0 : options.title;
	  const text = options == null ? void 0 : options.text;
	  const video = options == null ? void 0 : options.videoSrc;
	  const iconColor = options == null ? void 0 : (_options$colors = options.colors) == null ? void 0 : _options$colors.iconBackground;
	  const titleColor = options == null ? void 0 : (_options$colors2 = options.colors) == null ? void 0 : _options$colors2.title;
	  const buttonColor = options == null ? void 0 : (_options$colors3 = options.colors) == null ? void 0 : _options$colors3.button;
	  const targetOptions = options == null ? void 0 : options.targetOptions;
	  const offset = options == null ? void 0 : options.offset;
	  const videoContainerMinHeight = options == null ? void 0 : options.videoContainerMinHeight;
	  if (!options) {
	    throw new TypeError('UI.PromoVideoPopup: options are required for constructor');
	  }
	  if (!targetOptions) {
	    throw new Error('UI.PromoVideoPopup: targetOptions is required option');
	  }
	  if (title && main_core.Type.isString(title) === false) {
	    throw new TypeError('UI.PromoVideoPopup: title option must be string');
	  }
	  if (iconColor && main_core.Type.isStringFilled(iconColor) === false) {
	    throw new TypeError('UI.PromoVideoPopup: colors.icon option must be string');
	  }
	  if (titleColor && main_core.Type.isStringFilled(titleColor) === false) {
	    throw new TypeError('UI.PromoVideoPopup: colors.title option must be string');
	  }
	  if (buttonColor && Object.values(ui_buttons.ButtonColor).includes(buttonColor) === false) {
	    throw new TypeError('UI.PromoVideoPopup: colors.button option must be from ButtonColor from ui.buttons');
	  }
	  if (text && main_core.Type.isString(text) === false) {
	    throw new TypeError('UI.PromoVideoPopup: description option must be string');
	  }
	  if (!video) {
	    throw new Error('UI.PromoVideoPopup: videoSrc is required option');
	  }
	  if (video && main_core.Type.isStringFilled(video) === false) {
	    throw new Error('UI.PromoVideoPopup: videoSrc must be string');
	  }
	  if (offset && main_core.Type.isPlainObject(offset) === false) {
	    throw new Error('UI.PromoVideoPopup: offset options must be plain object with top and left properties');
	  }
	  if (offset != null && offset.top && main_core.Type.isNumber(offset == null ? void 0 : offset.top) === false) {
	    throw new TypeError('UI.PromoVideoPopup: offset.top option must be number');
	  }
	  if (offset != null && offset.left && main_core.Type.isNumber(offset == null ? void 0 : offset.left) === false) {
	    throw new TypeError('UI.PromoVideoPopup: offset.left option must be number');
	  }
	  if (videoContainerMinHeight && main_core.Type.isNumber(videoContainerMinHeight) === false) {
	    throw new TypeError('UI.PromoVideoPopup: videoContainerMinHeight option must be number');
	  }
	}
	function _isIconExist2(icon) {
	  return Object.values(ui_iconSet_api_core.Set).includes(icon);
	}
	function _getButtonOptions2() {
	  var _babelHelpers$classPr12, _babelHelpers$classPr13, _babelHelpers$classPr14, _babelHelpers$classPr15, _babelHelpers$classPr16, _babelHelpers$classPr17, _babelHelpers$classPr18, _babelHelpers$classPr19;
	  const defaultOptions = babelHelpers.classPrivateFieldLooseBase(this, _getDefaultButtonOptions)[_getDefaultButtonOptions]();
	  return {
	    text: (_babelHelpers$classPr12 = (_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _buttonOptions)[_buttonOptions]) == null ? void 0 : _babelHelpers$classPr13.text) != null ? _babelHelpers$classPr12 : defaultOptions.text,
	    color: (_babelHelpers$classPr14 = (_babelHelpers$classPr15 = babelHelpers.classPrivateFieldLooseBase(this, _buttonOptions)[_buttonOptions]) == null ? void 0 : _babelHelpers$classPr15.color) != null ? _babelHelpers$classPr14 : defaultOptions.color,
	    size: (_babelHelpers$classPr16 = (_babelHelpers$classPr17 = babelHelpers.classPrivateFieldLooseBase(this, _buttonOptions)[_buttonOptions]) == null ? void 0 : _babelHelpers$classPr17.size) != null ? _babelHelpers$classPr16 : defaultOptions.size,
	    position: (_babelHelpers$classPr18 = (_babelHelpers$classPr19 = babelHelpers.classPrivateFieldLooseBase(this, _buttonOptions)[_buttonOptions]) == null ? void 0 : _babelHelpers$classPr19.position) != null ? _babelHelpers$classPr18 : defaultOptions.position
	  };
	}
	function _getDefaultButtonOptions2() {
	  return {
	    text: main_core.Loc.getMessage('PROMO_VIDEO_POPUP_ACCEPT'),
	    size: ui_buttons.ButtonSize.SMALL,
	    color: babelHelpers.classPrivateFieldLooseBase(this, _getOptionsButtonColor)[_getOptionsButtonColor]() || ui_buttons.ButtonColor.PRIMARY,
	    position: PromoVideoPopupButtonPosition.LEFT
	  };
	}
	function _getPopupOverlay2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _useOverlay)[_useOverlay] ? {
	    backgroundColor: 'rgba(0, 0, 0, 0.4)'
	  } : false;
	}
	function _getPopupFooterElementClassname2() {
	  let buttonAlignModifier = '';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getButtonOptions)[_getButtonOptions]().position === PromoVideoPopupButtonPosition.CENTER) {
	    buttonAlignModifier = '--align-center';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getButtonOptions)[_getButtonOptions]().position === PromoVideoPopupButtonPosition.RIGHT) {
	    buttonAlignModifier = '--align-right';
	  }
	  return `ui__promo-video-popup-content_footer ${buttonAlignModifier}`;
	}

	exports.AnglePosition = AnglePosition;
	exports.PromoVideoPopupEvents = PromoVideoPopupEvents;
	exports.PromoVideoPopupButtonPosition = PromoVideoPopupButtonPosition;
	exports.PromoVideoPopup = PromoVideoPopup;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event,BX.Main,BX.UI.IconSet,BX.UI));
//# sourceMappingURL=promo-video-popup.bundle.js.map
