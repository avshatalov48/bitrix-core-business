/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
(function (exports,ui_iconSet_api_vue,ui_iconSet_outline,main_core,ui_iconSet_api_core) {
	'use strict';

	const ChipDesign = Object.freeze({
	  Filled: 'filled',
	  FilledSuccess: 'filled-success',
	  FilledAlert: 'filled-alert',
	  FilledWarning: 'filled-warning',
	  FilledNoAccent: 'filled-warning',
	  FilledInverted: 'filled-inverted',
	  FilledSuccessInverted: 'filled-success-inverted',
	  FilledAlertInverted: 'filled-alert-inverted',
	  FilledWarningInverted: 'filled-warning-inverted',
	  FilledNoAccentInverted: 'filled-no-accent-inverted',
	  Tinted: 'tinted',
	  TintedSuccess: 'tinted-success',
	  TintedAlert: 'tinted-alert',
	  TintedWarning: 'tinted-warning',
	  TintedNoAccent: 'tinted-no-accent',
	  OutlineAccent: 'outline-accent',
	  OutlineAccent2: 'outline-accent-2',
	  OutlineSuccess: 'outline-success',
	  OutlineAlert: 'outline-alert',
	  OutlineWarning: 'outline-warning',
	  Outline: 'outline',
	  OutlineNoAccent: 'outline-no-accent',
	  OutlineCopilot: 'outline-copilot',
	  ShadowNoAccent: 'shadow-no-accent',
	  Shadow: 'shadow',
	  ShadowAccent: 'shadow-accent',
	  ShadowDisabled: 'shadow-disabled',
	  ShadowOutlineAccent2: 'shadow-outline-accent-2',
	  ShadowOutline: 'shadow-outline',
	  Disabled: 'disabled'
	});
	const ChipSize = Object.freeze({
	  Lg: 'l',
	  Md: 'm',
	  Sm: 's',
	  Xs: 'xs'
	});

	// @vue/component
	const Chip = {
	  name: 'UiChip',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    size: {
	      type: String,
	      default: ChipSize.Lg
	    },
	    design: {
	      type: String,
	      default: ChipDesign.Outline
	    },
	    icon: {
	      type: String,
	      default: null
	    },
	    iconColor: {
	      type: String,
	      default: null
	    },
	    iconBackground: {
	      type: String,
	      default: null
	    },
	    image: {
	      /** @type ChipImage */
	      type: Object,
	      default: null
	    },
	    text: {
	      type: String,
	      default: ''
	    },
	    rounded: {
	      type: Boolean,
	      default: false
	    },
	    withClear: {
	      type: Boolean,
	      default: false
	    },
	    dropdown: {
	      type: Boolean,
	      default: false
	    },
	    lock: {
	      type: Boolean,
	      default: false
	    },
	    compact: {
	      type: Boolean,
	      default: true
	    },
	    trimmable: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['click', 'clear'],
	  setup() {
	    return {
	      Outline: ui_iconSet_api_vue.Outline
	    };
	  },
	  methods: {
	    handleKeydown(event) {
	      if (event.key === 'Enter' && !(event.ctrlKey || event.metaKey)) {
	        this.$emit('click');
	      }
	    }
	  },
	  template: `
		<div
			class="ui-chip"
			:class="[
				'--' + design,
				'--' + size,
				{
					'--rounded': rounded,
					'--compact': compact,
					'--trimmable': trimmable,
					'--lock': lock,
					'--with-right-icon': withClear || dropdown,
					'--no-text': text.length === 0,
				},
			]"
			tabindex="0"
			@keydown="handleKeydown"
			@click="$emit('click')"
		>
			<img v-if="image" class="ui-chip-icon --image" :src="image.src" :alt="image.alt">
			<div
				v-if="icon"
				class="ui-chip-icon"
				:class="{ '--with-background': Boolean(iconBackground) }"
				:style="{ '--icon-background': iconBackground }"
			>
				<BIcon :name="icon" :color="iconColor"/>
			</div>
			<div class="ui-chip-text">{{ text }}</div>
			<BIcon v-if="dropdown" class="ui-chip-right-icon" :name="Outline.CHEVRON_DOWN_M"/>
			<BIcon v-if="withClear" class="ui-chip-right-icon" :name="Outline.CROSS_M" @click.stop="$emit('clear')"/>
			<BIcon v-if="lock" class="ui-chip-lock" :name="Outline.LOCK_M"/>
		</div>
	`
	};

	var vue = /*#__PURE__*/Object.freeze({
		Chip: Chip,
		ChipDesign: ChipDesign,
		ChipSize: ChipSize
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	var _size = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("size");
	var _design = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("design");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _iconColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconColor");
	var _iconBackground = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconBackground");
	var _image = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("image");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _rounded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rounded");
	var _withClear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("withClear");
	var _dropdown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropdown");
	var _dropdownActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropdownActive");
	var _lock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lock");
	var _compact = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("compact");
	var _trimmable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("trimmable");
	var _onClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClick");
	var _onClear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClear");
	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _iconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconElement");
	var _textElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textElement");
	var _dropdownIconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropdownIconElement");
	var _clearIconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearIconElement");
	var _lockIconElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lockIconElement");
	var _iconInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconInstance");
	var _dropdownIconInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dropdownIconInstance");
	var _clearIconInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearIconInstance");
	var _lockIconInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lockIconInstance");
	var _getModifierClasses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getModifierClasses");
	var _renderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderIcon");
	var _renderText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderText");
	var _renderDropdownIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDropdownIcon");
	var _renderClearIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderClearIcon");
	var _renderLock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLock");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _bindClearEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindClearEvent");
	var _unbindClearEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindClearEvent");
	var _handleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClick");
	var _handleKeydown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleKeydown");
	var _handleClear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClear");
	var _updateClasses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateClasses");
	var _updateIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateIcon");
	var _updateDropdownIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateDropdownIcon");
	var _updateClearIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateClearIcon");
	var _updateLock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateLock");
	var _updateDropdownStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateDropdownStyle");
	class Chip$1 {
	  constructor(options = {}) {
	    var _options$size, _options$design, _options$icon, _options$iconColor, _options$iconBackgrou, _options$image, _options$text, _options$compact, _options$onClick, _options$onClear;
	    Object.defineProperty(this, _updateDropdownStyle, {
	      value: _updateDropdownStyle2
	    });
	    Object.defineProperty(this, _updateLock, {
	      value: _updateLock2
	    });
	    Object.defineProperty(this, _updateClearIcon, {
	      value: _updateClearIcon2
	    });
	    Object.defineProperty(this, _updateDropdownIcon, {
	      value: _updateDropdownIcon2
	    });
	    Object.defineProperty(this, _updateIcon, {
	      value: _updateIcon2
	    });
	    Object.defineProperty(this, _updateClasses, {
	      value: _updateClasses2
	    });
	    Object.defineProperty(this, _handleClear, {
	      value: _handleClear2
	    });
	    Object.defineProperty(this, _handleKeydown, {
	      value: _handleKeydown2
	    });
	    Object.defineProperty(this, _handleClick, {
	      value: _handleClick2
	    });
	    Object.defineProperty(this, _unbindClearEvent, {
	      value: _unbindClearEvent2
	    });
	    Object.defineProperty(this, _bindClearEvent, {
	      value: _bindClearEvent2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _renderLock, {
	      value: _renderLock2
	    });
	    Object.defineProperty(this, _renderClearIcon, {
	      value: _renderClearIcon2
	    });
	    Object.defineProperty(this, _renderDropdownIcon, {
	      value: _renderDropdownIcon2
	    });
	    Object.defineProperty(this, _renderText, {
	      value: _renderText2
	    });
	    Object.defineProperty(this, _renderIcon, {
	      value: _renderIcon2
	    });
	    Object.defineProperty(this, _getModifierClasses, {
	      value: _getModifierClasses2
	    });
	    Object.defineProperty(this, _size, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _design, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _iconColor, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _iconBackground, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _image, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rounded, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _withClear, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dropdown, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dropdownActive, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lock, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _compact, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _trimmable, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onClick, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _onClear, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _iconElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _textElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _dropdownIconElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clearIconElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _lockIconElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _iconInstance, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _dropdownIconInstance, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _clearIconInstance, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _lockIconInstance, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = (_options$size = options.size) != null ? _options$size : ChipSize.Lg;
	    babelHelpers.classPrivateFieldLooseBase(this, _design)[_design] = (_options$design = options.design) != null ? _options$design : ChipDesign.Outline;
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = (_options$icon = options.icon) != null ? _options$icon : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _iconColor)[_iconColor] = (_options$iconColor = options.iconColor) != null ? _options$iconColor : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _iconBackground)[_iconBackground] = (_options$iconBackgrou = options.iconBackground) != null ? _options$iconBackgrou : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] = (_options$image = options.image) != null ? _options$image : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = (_options$text = options.text) != null ? _options$text : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _rounded)[_rounded] = options.rounded === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear] = options.withClear === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdown)[_dropdown] = options.dropdown === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownActive)[_dropdownActive] = options.dropdownActive === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _lock)[_lock] = options.lock === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _compact)[_compact] = (_options$compact = options.compact) != null ? _options$compact : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _trimmable)[_trimmable] = options.trimmable === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick] = (_options$onClick = options.onClick) != null ? _options$onClick : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _onClear)[_onClear] = (_options$onClear = options.onClear) != null ? _options$onClear : null;
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = main_core.Tag.render(_t || (_t = _`
			<div class="ui-chip ${0}">
				${0}
				${0}
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getModifierClasses)[_getModifierClasses](), babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](), babelHelpers.classPrivateFieldLooseBase(this, _renderText)[_renderText](), babelHelpers.classPrivateFieldLooseBase(this, _renderDropdownIcon)[_renderDropdownIcon](), babelHelpers.classPrivateFieldLooseBase(this, _renderClearIcon)[_renderClearIcon](), babelHelpers.classPrivateFieldLooseBase(this, _renderLock)[_renderLock]());
	    main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], 'tabindex', '0');
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	  }
	  getWrapper() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	  }
	  setSize(size) {
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = size;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  getSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _size)[_size];
	  }
	  setDesign(design) {
	    babelHelpers.classPrivateFieldLooseBase(this, _design)[_design] = design;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  getDesign() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _design)[_design];
	  }
	  setIcon(icon) {
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = icon;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	  }
	  getIcon() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon];
	  }
	  setIconColor(color) {
	    babelHelpers.classPrivateFieldLooseBase(this, _iconColor)[_iconColor] = color;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance].setColor(color);
	    }
	  }
	  getIconColor() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _iconColor)[_iconColor];
	  }
	  setIconBackground(background) {
	    babelHelpers.classPrivateFieldLooseBase(this, _iconBackground)[_iconBackground] = background;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	  }
	  getIconBackground() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _iconBackground)[_iconBackground];
	  }
	  setImage(image) {
	    babelHelpers.classPrivateFieldLooseBase(this, _image)[_image] = image;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateIcon)[_updateIcon]();
	  }
	  getImage() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _image)[_image];
	  }
	  setText(text) {
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = text;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      const textElement = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-text');
	      if (textElement) {
	        textElement.textContent = text;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	    }
	  }
	  getText() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _text)[_text];
	  }
	  setRounded(rounded) {
	    babelHelpers.classPrivateFieldLooseBase(this, _rounded)[_rounded] = rounded === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isRounded() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rounded)[_rounded];
	  }
	  setWithClear(withClear) {
	    babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear] = withClear === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClearIcon)[_updateClearIcon]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isWithClear() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear];
	  }
	  setDropdown(dropdown) {
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdown)[_dropdown] = dropdown === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateDropdownIcon)[_updateDropdownIcon]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isDropdown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dropdown)[_dropdown];
	  }
	  setDropdownActive(active) {
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownActive)[_dropdownActive] = active === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateDropdownStyle)[_updateDropdownStyle]();
	  }
	  isDropdownActive() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dropdownActive)[_dropdownActive];
	  }
	  setLock(lock) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lock)[_lock] = lock === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateLock)[_updateLock]();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isLock() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _lock)[_lock];
	  }
	  setCompact(compact = true) {
	    babelHelpers.classPrivateFieldLooseBase(this, _compact)[_compact] = compact;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isCompact() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _compact)[_compact];
	  }
	  setTrimmable(trimmable) {
	    babelHelpers.classPrivateFieldLooseBase(this, _trimmable)[_trimmable] = trimmable === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	  }
	  isTrimmable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _trimmable)[_trimmable];
	  }
	  setOnClick(callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick] = callback;
	  }
	  setOnClear(callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClear)[_onClear] = callback;
	  }
	  destroy() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      main_core.Event.unbindAll(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear]) {
	        const clearIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-clear-icon');
	        if (clearIcon) {
	          main_core.Event.unbindAll(clearIcon);
	        }
	      }
	      main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconInstance)[_dropdownIconInstance] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _clearIconInstance)[_clearIconInstance] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lockIconInstance)[_lockIconInstance] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _textElement)[_textElement] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _clearIconElement)[_clearIconElement] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lockIconElement)[_lockIconElement] = null;
	  }
	}
	function _getModifierClasses2() {
	  const classes = [`--${babelHelpers.classPrivateFieldLooseBase(this, _design)[_design]}`, `--${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}`];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _rounded)[_rounded]) {
	    classes.push('--rounded');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _compact)[_compact]) {
	    classes.push('--compact');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _trimmable)[_trimmable]) {
	    classes.push('--trimmable');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _lock)[_lock]) {
	    classes.push('--lock');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear] || babelHelpers.classPrivateFieldLooseBase(this, _dropdown)[_dropdown]) {
	    classes.push('--with-right-icon');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _text)[_text].length === 0) {
	    classes.push('--no-text');
	  }
	  return classes.join(' ');
	}
	function _renderIcon2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _image)[_image]) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<img class="ui-chip-icon --image" src="${0}" alt="${0}">
			`), babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].src, babelHelpers.classPrivateFieldLooseBase(this, _image)[_image].alt || '');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement] = main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-chip-icon"></div>`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _iconBackground)[_iconBackground]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], '--with-background');
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement], '--icon-background', babelHelpers.classPrivateFieldLooseBase(this, _iconBackground)[_iconBackground]);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance] = new ui_iconSet_api_core.Icon({
	      icon: babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon],
	      color: babelHelpers.classPrivateFieldLooseBase(this, _iconColor)[_iconColor] || null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement];
	  }
	  return null;
	}
	function _renderText2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _textElement)[_textElement] = main_core.Tag.render(_t4 || (_t4 = _`<div class="ui-chip-text">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _text)[_text]);
	  return babelHelpers.classPrivateFieldLooseBase(this, _textElement)[_textElement];
	}
	function _renderDropdownIcon2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _dropdown)[_dropdown]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement] = main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-chip-right-icon ui-chip-dropdown-icon"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconInstance)[_dropdownIconInstance] = new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Outline.CHEVRON_DOWN_M
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconInstance)[_dropdownIconInstance].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement]);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateDropdownStyle)[_updateDropdownStyle]();
	    return babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement];
	  }
	  return null;
	}
	function _renderClearIcon2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _clearIconElement)[_clearIconElement] = main_core.Tag.render(_t6 || (_t6 = _`<div class="ui-chip-right-icon ui-chip-clear-icon"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _clearIconInstance)[_clearIconInstance] = new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Outline.CROSS_M
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _clearIconInstance)[_clearIconInstance].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _clearIconElement)[_clearIconElement]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _clearIconElement)[_clearIconElement];
	  }
	  return null;
	}
	function _renderLock2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _lock)[_lock]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lockIconElement)[_lockIconElement] = main_core.Tag.render(_t7 || (_t7 = _`<div class="ui-chip-lock"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _lockIconInstance)[_lockIconInstance] = new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Outline.LOCK_M
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _lockIconInstance)[_lockIconInstance].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _lockIconElement)[_lockIconElement]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _lockIconElement)[_lockIconElement];
	  }
	  return null;
	}
	function _bindEvents2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick].bind(this));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _handleKeydown)[_handleKeydown].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _bindClearEvent)[_bindClearEvent]();
	  }
	}
	function _bindClearEvent2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _withClear)[_withClear] && babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    const clearIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-clear-icon');
	    if (clearIcon) {
	      main_core.Event.bind(clearIcon, 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleClear)[_handleClear].bind(this));
	    }
	  }
	}
	function _unbindClearEvent2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    const clearIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-clear-icon');
	    if (clearIcon) {
	      main_core.Event.unbindAll(clearIcon);
	    }
	  }
	}
	function _handleClick2(event) {
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick](event);
	  }
	}
	function _handleKeydown2(event) {
	  if (event.key === 'Enter' && !(event.ctrlKey || event.metaKey) && main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick](event);
	  }
	}
	function _handleClear2(event) {
	  event.stopPropagation();
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _onClear)[_onClear])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onClear)[_onClear](event);
	  }
	}
	function _updateClasses2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].className = `ui-chip ${babelHelpers.classPrivateFieldLooseBase(this, _getModifierClasses)[_getModifierClasses]()}`;
	}
	function _updateIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    return;
	  }
	  const oldIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-icon');
	  if (oldIcon) {
	    main_core.Dom.remove(oldIcon);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _iconInstance)[_iconInstance] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _iconElement)[_iconElement] = null;
	  const newIcon = babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon]();
	  if (newIcon) {
	    const textElement = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-text');
	    if (textElement) {
	      main_core.Dom.insertBefore(newIcon, textElement);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].prepend(newIcon);
	    }
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateClasses)[_updateClasses]();
	}
	function _updateDropdownIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    return;
	  }
	  const oldDropdownIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-dropdown-icon');
	  if (oldDropdownIcon) {
	    main_core.Dom.remove(oldDropdownIcon);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconInstance)[_dropdownIconInstance] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement] = null;
	  const newDropdownIcon = babelHelpers.classPrivateFieldLooseBase(this, _renderDropdownIcon)[_renderDropdownIcon]();
	  if (newDropdownIcon) {
	    // Вставляем перед clear иконкой, если она есть, или перед замком
	    const clearIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-clear-icon');
	    const lockElement = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-lock');
	    if (clearIcon) {
	      main_core.Dom.insertBefore(newDropdownIcon, clearIcon);
	    } else if (lockElement) {
	      main_core.Dom.insertBefore(newDropdownIcon, lockElement);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].append(newDropdownIcon);
	    }
	  }
	}
	function _updateClearIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    return;
	  }
	  const oldClearIcon = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-clear-icon');
	  if (oldClearIcon) {
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindClearEvent)[_unbindClearEvent]();
	    main_core.Dom.remove(oldClearIcon);
	  }

	  // Очищаем старый экземпляр
	  babelHelpers.classPrivateFieldLooseBase(this, _clearIconInstance)[_clearIconInstance] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _clearIconElement)[_clearIconElement] = null;
	  const newClearIcon = babelHelpers.classPrivateFieldLooseBase(this, _renderClearIcon)[_renderClearIcon]();
	  if (newClearIcon) {
	    const lockElement = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-lock');
	    if (lockElement) {
	      main_core.Dom.insertBefore(newClearIcon, lockElement);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].append(newClearIcon);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _bindClearEvent)[_bindClearEvent]();
	  }
	}
	function _updateLock2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	    return;
	  }
	  const oldLock = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-chip-lock');
	  if (oldLock) {
	    main_core.Dom.remove(oldLock);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _lockIconInstance)[_lockIconInstance] = null;
	  babelHelpers.classPrivateFieldLooseBase(this, _lockIconElement)[_lockIconElement] = null;
	  const newLock = babelHelpers.classPrivateFieldLooseBase(this, _renderLock)[_renderLock]();
	  if (newLock) {
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].append(newLock);
	  }
	}
	function _updateDropdownStyle2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement] && babelHelpers.classPrivateFieldLooseBase(this, _dropdownActive)[_dropdownActive]) {
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement], 'transform', 'rotate(180deg)');
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement]) {
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _dropdownIconElement)[_dropdownIconElement], 'transform', '');
	  }
	}

	exports.Vue = vue;
	exports.ChipDesign = ChipDesign;
	exports.ChipSize = ChipSize;
	exports.Chip = Chip$1;

}((this.BX.UI.System.Chip = this.BX.UI.System.Chip || {}),BX.UI.IconSet,BX,BX,BX.UI.IconSet));
//# sourceMappingURL=chip.bundle.js.map
