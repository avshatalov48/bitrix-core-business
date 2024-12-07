/* eslint-disable */
this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_iconSet_api_vue,main_popup,ui_hint,ui_forms,ui_buttons,catalog_toolAvailabilityManager,ui_iconSet_main,ui_iconSet_crm,ui_iconSet_actions,ui_vue3,main_core) {
	'use strict';

	const ENABLED_SESSION_KEY = 'b24:catalog:inventory-management:enabled';
	class EnableWizardOpener {
	  open(url, params = {}) {
	    const sliderParams = main_core.Type.isPlainObject(params) ? params : {};
	    return new Promise(resolve => {
	      var _sliderParams$data, _sliderParams$events, _events$onClose;
	      const data = (_sliderParams$data = sliderParams.data) != null ? _sliderParams$data : {};
	      const events = (_sliderParams$events = sliderParams.events) != null ? _sliderParams$events : {};
	      events.onClose = (_events$onClose = events.onClose) != null ? _events$onClose : event => resolve(event.getSlider());
	      const urlParams = params.urlParams || {};
	      const sliderUrl = BX.util.add_url_param(url, {
	        analyticsLabel: 'inventoryManagementEnabled_openSlider',
	        ...urlParams
	      });
	      if (main_core.Type.isString(sliderUrl) && sliderUrl.length > 1) {
	        BX.SidePanel.Instance.open(sliderUrl, {
	          cacheable: false,
	          allowChangeHistory: false,
	          events,
	          data,
	          width: 930
	        });
	      } else {
	        resolve();
	      }
	    });
	  }
	  static saveEnabledFlag() {
	    if (!window.sessionStorage) {
	      return;
	    }
	    sessionStorage.setItem(ENABLED_SESSION_KEY, 'y');
	  }
	  static showEnabledNotificationIfNeeded() {
	    if (!window.sessionStorage) {
	      return;
	    }
	    if (sessionStorage.getItem(ENABLED_SESSION_KEY) === 'y') {
	      sessionStorage.removeItem(ENABLED_SESSION_KEY);
	      window.top.BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ENABLED'),
	        autoHide: true,
	        autoHideDelay: 4000,
	        width: 'auto'
	      });
	    }
	  }
	}

	class AnalyticsContextList {}
	AnalyticsContextList.SECTION = 'section';
	AnalyticsContextList.ANALYTICS_MENU_ITEM = 'analytics_menu_item';
	AnalyticsContextList.LANDING = 'landing';
	AnalyticsContextList.PRODUCT_CARD = 'product_card';
	AnalyticsContextList.DOCUMENT_LIST = 'document_list';
	AnalyticsContextList.DOCUMENT_CARD = 'document_card';
	AnalyticsContextList.SETTINGS = 'settings';
	AnalyticsContextList.OLD_SETTINGS = 'old_settings';

	const ActionHint = {
	  props: {
	    title: {
	      type: String
	    }
	  },
	  data() {
	    return {
	      timer: null
	    };
	  },
	  created() {
	    this.popup = new main_popup.Popup({
	      bindElement: null,
	      darkMode: true,
	      angle: {
	        offset: 82
	      },
	      content: this.title,
	      maxWidth: 220,
	      offsetLeft: 115 / 2 - 57.5,
	      animation: 'fading-slide'
	    });
	  },
	  beforeUnmount() {
	    this.closePopup();
	  },
	  methods: {
	    mouseenter(ev) {
	      this.timer = setTimeout(() => {
	        this.popup.setBindElement(ev.target);
	        this.popup.show();
	      }, 400);
	    },
	    mouseleave() {
	      this.closePopup();
	    },
	    closePopup() {
	      clearTimeout(this.timer);
	      this.popup.close();
	    }
	  },
	  template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			class="inventory-management__action-hint">
		</div>
	`
	};

	const CardBoxHelp = {
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    title: {
	      type: String
	    },
	    link: {
	      type: String,
	      required: false
	    }
	  },
	  computed: {
	    set() {
	      return ui_iconSet_api_vue.Set;
	    }
	  },
	  methods: {
	    onClick() {
	      if (top.BX && top.BX.Helper) {
	        top.BX.Helper.show(this.link || 'redirect=detail&code=20233688');
	      }
	    }
	  },
	  template: `
		<div
			@click.stop="onClick"
			class="inventory-management__card-help">
			<BIcon :name="set.HELP" :size="23" color="var(--ui-color-base-40)"></BIcon>
			<div class="inventory-management__card-help-text">
				{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
			</div>
		</div>
	`
	};

	const CardBoxTrait = {
	  props: {
	    isActive: {
	      type: Boolean,
	      required: true
	    },
	    isHovered: {
	      type: Boolean,
	      required: true,
	      default: false
	    }
	  },
	  components: {
	    CardBoxHelp,
	    ActionHint
	  },
	  computed: {
	    cardItemClass() {
	      return {
	        '--active': this.isHovered
	      };
	    },
	    cardItemStyle() {
	      return {
	        cursor: this.isActive ? 'default' : 'pointer'
	      };
	    },
	    langClass() {
	      return `--${this.$Bitrix.Loc.getMessage('LANGUAGE_ID') || 'en'}`;
	    }
	  },
	  methods: {
	    onClick() {
	      this.$emit('pick');
	    },
	    mouseenter() {
	      this.$emit('enter');
	    },
	    mouseleave() {
	      this.$emit('leave');
	    }
	  }
	};

	const ReplaceLangPhraseTrait = {
	  methods: {
	    replaceLangPhrase(phrase) {
	      return this.$Bitrix.Loc.getMessage(phrase).replaceAll('[break]', '<br>').replaceAll('[bold]', '<span>').replaceAll('[/bold]', '</span>');
	    }
	  }
	};

	const CardBoxB24 = {
	  mixins: [CardBoxTrait, ReplaceLangPhraseTrait],
	  template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			@click="onClick"
			class="inventory-management__card-item"
			:class="cardItemClass"
			:style="cardItemStyle"
		>
			<div class="inventory-management__card-logo" :class="langClass"></div>
			<div
				v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_TITLE_B24')"
				class="inventory-management__card-title"
			></div>
			<ul class="inventory-management__card-list">
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_1')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_2')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_3')}}
				</li>
			</ul>
			<div class="inventory-management__card-action-box">
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --play"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1')"
						class="inventory-management__card-action-text"
					>
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --refresh-6"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --mobile-2"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3_HINT')"
					></action-hint>
				</div>
			</div>
			<div class="inventory-management__card-select-box">
				<div
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_B24')"
					class="inventory-management__card-select-text"
				>
				</div>
				<div class="inventory-management__card-select-icon">
					<div class="ui-icon-set --check"></div>
				</div>
			</div>
			<div class="inventory-management__card-control-box">
				<button
					v-if="!isActive"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON')}}
				</button>
				<card-box-help></card-box-help>
			</div>
		</div>
	`
	};

	const CardBoxB24Solo = {
	  mixins: [CardBoxTrait, ReplaceLangPhraseTrait],
	  created() {
	    this.setBodyClass();
	  },
	  methods: {
	    getHelpLink() {
	      return 'redirect=detail&code=15992592';
	    },
	    setBodyClass() {
	      main_core.Dom.addClass(document.body, 'inventory-management__solo');
	    }
	  },
	  template: `
		<div class="inventory-management__card-box-solo">
			<div class="inventory-management__card-solo-icon" :class="langClass"></div>
			<div
				@click="onClick"
				@mouseenter="mouseenter"
				@mouseleave="mouseleave"
				class="inventory-management__card-item"
				:class="cardItemClass"
				:style="cardItemStyle"
			>
				<div class="inventory-management__card-logo" :class="langClass"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_TITLE_B24_SOLO')}}
				</div>
				<ul class="inventory-management__card-list">
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_1_SOLO')}}
					</li>
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_2_SOLO')}}
					</li>
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_3_SOLO')}}
					</li>
				</ul>
				<div class="inventory-management__card-action-box">
					<div class="inventory-management__card-action-item">
						<div class="ui-icon-set --play"></div>
						<div
							v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1')"
							class="inventory-management__card-action-text"
						>
						</div>
						<action-hint
							:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1_HINT')"
						></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --refresh-6"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --mobile-2"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3_HINT')"
					></action-hint>
				</div>
				</div>
				<div class="inventory-management__card-select-box">
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_B24_SOLO')"
						class="inventory-management__card-select-text"
					>
					</div>
					<div class="inventory-management__card-select-icon">
						<div class="ui-icon-set --check"></div>
					</div>
				</div>
				<div class="inventory-management__card-control-box">
					<button
						v-if="!isActive"
						class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
					>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_ENABLE_SOLO')}}
					</button>
					<card-box-help
						:link="getHelpLink()"
					>
					</card-box-help>
				</div>
			</div>
		</div>
	`
	};

	const CardBoxOnec = {
	  mixins: [CardBoxTrait, ReplaceLangPhraseTrait],
	  template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			@click="onClick"
			class="inventory-management__card-item --1c"
			:class="cardItemClass"
			:style="cardItemStyle"
		>
			<div class="inventory-management__card-logo"></div>
			<div
				v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_TITLE_1C')"
				class="inventory-management__card-title"
			>
			</div>
			<ul class="inventory-management__card-list">
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_1')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_2')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_1C_3')}}
				</li>
			</ul>
			<div class="inventory-management__card-action-box">
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --cubes-3"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_1')"
						class="inventory-management__card-action-text">
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_1_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --shop-list"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_2')"
						class="inventory-management__card-action-text"
					>
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_2_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --persons-3"></div>
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_3')"
						class="inventory-management__card-action-text"
					>
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_1C_3_HINT')"
					></action-hint>
				</div>
			</div>
			<div class="inventory-management__card-select-box">
				<div
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_1C')"
					class="inventory-management__card-select-text"
				>
				</div>
				<div class="inventory-management__card-select-icon">
					<div class="ui-icon-set --check"></div>
				</div>
			</div>
			<div class="inventory-management__card-control-box">
				<button
					v-if="!isActive"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON')}}
				</button>
				<card-box-help></card-box-help>
			</div>
		</div>
	`
	};

	let _ = t => t,
	  _t;
	const IconHint = {
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    title: {
	      type: String
	    },
	    helpLink: {
	      type: String
	    }
	  },
	  data() {
	    return {
	      timer: null
	    };
	  },
	  created() {
	    this.hint = BX.UI.Hint.createInstance({
	      popupParameters: {
	        maxWidth: 430,
	        className: 'inventory-management__popup-hint',
	        borderRadius: '10px',
	        autoHide: true
	      }
	    });
	  },
	  beforeUnmount() {
	    this.hint.hide();
	  },
	  computed: {
	    set() {
	      return ui_iconSet_api_vue.Set;
	    },
	    getContent() {
	      return main_core.Tag.render(_t || (_t = _`
				<div>
					${0}
				</div>		
			`), this.title.replace('[link]', `<a class="inventory-management__popup-link --hint-link" onclick="if(top.BX.Helper) { top.BX.Helper.show('${this.helpLink}'); event.preventDefault(); }" href="#">`).replace('[/link]', '</a>'));
	    }
	  },
	  methods: {
	    mouseenter(ev) {
	      this.hint.show(ev.target, this.getContent.outerHTML);
	    }
	  },
	  template: `
		<div
			@mouseenter="mouseenter"
			class="inventory-management__icon-hint"
			ref="hintNode"
			>
			<BIcon :name="set.HELP" :size="23" color="var(--ui-color-base-40)"></BIcon>
		</div>
	`
	};

	const EnableWarning = {
	  props: {
	    text: {
	      type: String,
	      required: true
	    },
	    hint: {
	      type: String,
	      required: true,
	      default: ''
	    },
	    helpLink: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  components: {
	    IconHint
	  },
	  template: `
		<div class="inventory-management__card-limit">
			<div v-html="text" class="inventory-management__card-limit-text"></div>
			<icon-hint
				v-if="hint"
				:title="hint"
				:helpLink="helpLink"
			/>
		</div>
	`
	};

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _iconNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconNode");
	var _titleNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("titleNode");
	var _primaryButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("primaryButton");
	var _secondaryButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("secondaryButton");
	var _helpLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("helpLink");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	class Popup {
	  constructor(options = {}) {
	    var _babelHelpers$classPr;
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _iconNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _titleNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _primaryButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _secondaryButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _helpLink, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    const contentNode = babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	      content: contentNode,
	      width: (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].width) != null ? _babelHelpers$classPr : 527,
	      overlay: true,
	      events: {
	        onClose: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].events.onClose.bind(this)
	      }
	    });
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], 'click', babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].events.onPrimaryClick.bind(this));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _secondaryButton)[_secondaryButton], 'click', babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].events.onSecondaryClick.bind(this));
	    BX.UI.Hint.init(contentNode);
	  }
	  show(value) {
	    if (value === true) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].isShown()) {
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].show();
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].resizeOverlay();
	    } else {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].isShown()) {
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	    }
	  }
	  load(value) {
	    const clockClass = 'ui-btn-clock';
	    if (value && !main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], clockClass)) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], clockClass);
	    } else if (value === false && main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], clockClass)) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], clockClass);
	    }
	  }
	}
	function _getContent2() {
	  var _babelHelpers$classPr2;
	  const primaryButtonClass = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].primaryButtonClass) != null ? _babelHelpers$classPr2 : 'ui-btn-primary';
	  babelHelpers.classPrivateFieldLooseBase(this, _iconNode)[_iconNode] = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].hideIcon ? '' : main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="inventory-management__popup-icon"></div>`));
	  babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton] = main_core.Tag.render(_t2 || (_t2 = _$1`
			<button class="ui-btn ${0}">
				${0}
			</button>
		`), primaryButtonClass, babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].primaryButtonText);
	  babelHelpers.classPrivateFieldLooseBase(this, _secondaryButton)[_secondaryButton] = main_core.Tag.render(_t3 || (_t3 = _$1`
			<button	class="ui-btn ui-btn-light-border inventory-management__popup-cancel">
				${0}
			</button>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].secondaryButtonText);
	  babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode] = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title ? main_core.Tag.render(_t4 || (_t4 = _$1`
				<div class="inventory-management__popup-title">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title) : null;
	  babelHelpers.classPrivateFieldLooseBase(this, _helpLink)[_helpLink] = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].hideHelp ? '' : main_core.Tag.render(_t5 || (_t5 = _$1`
				<a href="#" class="inventory-management__popup-link">
					${0}
				</a>
			`), main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS'));
	  if (babelHelpers.classPrivateFieldLooseBase(this, _helpLink)[_helpLink] && babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].helpCode) {
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _helpLink)[_helpLink], 'click', event => {
	      if (top.BX && top.BX.Helper) {
	        event.preventDefault();
	        top.BX.Helper.show(`redirect=detail&code=${babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].helpCode}`);
	      }
	    });
	  }
	  const isSimplePopup = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].texts.length === 1 && !babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title;
	  return main_core.Tag.render(_t6 || (_t6 = _$1`
			<div class="inventory-management__popup">
				${0}
				${0}
				${0}
				${0}
				<div class="ui-btn-container ui-btn-container-center">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _iconNode)[_iconNode], babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode], babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].texts.map(text => `
					<div class="inventory-management__popup-text${isSimplePopup ? ' --no-margin' : ''}">
						${text.text}
						${text.hint ? `<span data-hint="${text.hint}"></span>` : ''}
					</div>
				`).join(''), babelHelpers.classPrivateFieldLooseBase(this, _helpLink)[_helpLink], babelHelpers.classPrivateFieldLooseBase(this, _primaryButton)[_primaryButton], babelHelpers.classPrivateFieldLooseBase(this, _secondaryButton)[_secondaryButton]);
	}

	const PopupField = {
	  props: {
	    isLoading: {
	      type: Boolean
	    },
	    isShown: {
	      type: Boolean
	    },
	    primaryButtonText: {
	      type: String
	    },
	    title: {
	      type: String
	    },
	    texts: {
	      type: Array
	    }
	  },
	  created() {
	    this.popup = new Popup({
	      helpCode: this.getSetting('availableModes').length > 1 ? '20233748' : '15992592',
	      title: this.title,
	      texts: this.texts,
	      primaryButtonText: this.primaryButtonText,
	      secondaryButtonText: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_CANCEL'),
	      events: {
	        onPrimaryClick: () => this.$emit('enable'),
	        onSecondaryClick: () => this.popup.show(false),
	        onClose: () => this.$emit('cancel')
	      }
	    });
	  },
	  methods: {
	    getSetting(name) {
	      return main_core.Extension.getSettings('catalog.store-enable-wizard').get(name);
	    }
	  },
	  watch: {
	    isLoading(newValue) {
	      this.popup.load(newValue);
	    },
	    isShown(newValue) {
	      this.popup.show(newValue);
	    }
	  },
	  template: ''
	};

	class Service {
	  static enable(config) {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('catalog.config.inventoryManagementEnable', config).then(response => resolve(response)).catch(response => reject(response.errors[0]));
	    });
	  }
	  static disable() {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('catalog.config.inventoryManagementDisable').then(response => resolve()).catch(response => reject(response.errors[0]));
	    });
	  }
	  static isOnecAppInstalled() {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runComponentAction('bitrix:catalog.store.enablewizard', 'getOnecApp', {
	        mode: 'class'
	      }).then(response => {
	        var _response$data;
	        return resolve(Boolean(response == null ? void 0 : (_response$data = response.data) == null ? void 0 : _response$data.isInstalled));
	      }).catch(e => reject(e));
	    });
	  }
	}

	const EnableTrait = {
	  data() {
	    return {
	      isShownPopup: false,
	      isEnabling: false
	    };
	  },
	  props: {
	    options: {
	      type: Object,
	      required: true
	    }
	  },
	  components: {
	    IconHint,
	    EnableWarning,
	    PopupField
	  },
	  methods: {
	    onBack() {
	      this.$emit('back');
	    },
	    /**
	     * @abstract
	     */
	    getMode() {
	      throw new Error('Abstract method "getMode" must be implemented');
	    },
	    getEnableOptions() {
	      return {};
	    },
	    onEnableSuccess() {
	      this.$Bitrix.Application.instance.sendEnableDoneEvent(this.getMode(), 'success');
	      const slider = BX.SidePanel.Instance.getTopSlider();
	      if (slider) {
	        slider.getData().set('isInventoryManagementEnabled', true);
	        slider.getData().set('inventoryManagementMode', this.getMode());
	        slider.close();
	      }
	    },
	    onEnableError(error) {
	      var _error$customData$ana, _error$customData;
	      this.$Bitrix.Application.instance.sendEnableDoneEvent(this.getMode(), `error_${(_error$customData$ana = error == null ? void 0 : (_error$customData = error.customData) == null ? void 0 : _error$customData.analyticsCode) != null ? _error$customData$ana : 'unknown'}`);
	      top.BX.UI.Notification.Center.notify({
	        content: main_core.Text.encode(error.message)
	      });
	    },
	    enable() {
	      if (this.isEnabling) {
	        return;
	      }
	      this.isEnabling = true;
	      this.$Bitrix.Application.instance.sendEnableProceededEvent(this.getMode());
	      Service.enable({
	        analyticsLabel: this.makeAnalyticsData(),
	        data: {
	          mode: this.getMode(),
	          options: this.getEnableOptions()
	        }
	      }).then(() => this.onEnableSuccess()).catch(error => this.onEnableError(error)).finally(() => {
	        this.isEnabling = false;
	        this.isShownPopup = false;
	      });
	    },
	    makeAnalyticsData() {
	      const result = {
	        iME: 'inventoryManagementEnabled'
	      };
	      if (this.options.inventoryManagementSource) {
	        result.inventoryManagementSource = this.options.inventoryManagementSource;
	      }
	      return result;
	    },
	    openHelp() {
	      if (top.BX && top.BX.Helper) {
	        top.BX.Helper.show(this.getHelpLink());
	      }
	    },
	    /**
	     * @abstract
	     */
	    getHelpLink() {
	      throw new Error('Abstract method "getHelpLink" must be implemented');
	    }
	  }
	};

	class ModeList {}
	ModeList.MODE_B24 = 'B24';
	ModeList.MODE_1C = '1C';

	const EnableB24 = {
	  data() {
	    return {
	      costPriceMethod: ''
	    };
	  },
	  mixins: [EnableTrait, ReplaceLangPhraseTrait],
	  computed: {
	    popupTitle() {
	      if (this.options.hasConductedDocumentsOrQuantities) {
	        return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DATA_WILL_BE_DELETED_MSGVER_1');
	      }
	      return null;
	    },
	    popupTexts() {
	      const result = [];
	      if (this.options.hasConductedDocumentsOrQuantities) {
	        result.push({
	          text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_ENABLE_B24_MSGVER_1'),
	          hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2')
	        });
	      }
	      return result;
	    },
	    startEnablingButtonClass() {
	      return {
	        'ui-btn-clock': this.isEnabling && !this.isShownPopup,
	        'ui-btn-disabled': !this.isFormValid
	      };
	    },
	    startEnablingButtonTitle() {
	      if (this.isFormValid) {
	        return null;
	      }
	      return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_B24_ENABLE_COST_PRICE_METHOD_REQUIRED');
	    },
	    isFormValid() {
	      return main_core.Type.isStringFilled(this.costPriceMethod);
	    }
	  },
	  methods: {
	    startEnabling() {
	      if (!this.isFormValid) {
	        return;
	      }
	      if (this.options.isPlanRestricted) {
	        top.BX.UI.InfoHelper.show('limit_store_inventory_management');
	        return;
	      }

	      // enabling right away because we have nothing to warn about
	      if (this.popupTexts.length === 0) {
	        this.enable();
	        return;
	      }
	      this.isShownPopup = true;
	    },
	    getMode() {
	      return ModeList.MODE_B24;
	    },
	    getEnableOptions() {
	      return {
	        costPriceCalculationMethod: this.costPriceMethod
	      };
	    },
	    getHelpLink() {
	      return 'redirect=detail&code=17858278';
	    }
	  },
	  template: `
		<div class="inventory-management__card-item --active --inner-field">
			<div class="inventory-management__card-item-inner">
				<div class="inventory-management__card-logo"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_TITLE')}}
				</div>
				<div 
					class="inventory-management__card-desc" 
					v-html="this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_1')"
				>
				</div>
				<div
					class="inventory-management__card-desc"
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_2')"
				>
				</div>
				<a
					@click="openHelp"
					href="#" class="inventory-management__card-link"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
				</a>
				<div class="inventory-management__card-select-field">
					<div class="inventory-management__card-select-title">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_SELECT_TITLE')}}
					</div>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select
							v-model="costPriceMethod"
							class="ui-ctl-element"
						>
							<option value="">
								{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_COST_PRICE_METHOD_NOT_SELECTED')}}
							</option>
							<option
								v-for="(name, value) in options.costPriceMethodList"
								:value="value"
								:key="value"
							>
								{{name}}
							</option>
						</select>
					</div>
				</div>
			</div>
			<div class="ui-btn-container inventory-management__card-footer">
				<button
					v-if="!options.isBlocked"
					@click="onBack"
					class="ui-btn ui-btn-light-border ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_BACK')}}
				</button>
				<button
					@click="startEnabling"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
					:class="startEnablingButtonClass"
					:title="startEnablingButtonTitle"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_START')}}
				</button>
			</div>
		</div>
		<popup-field
			@enable="enable"
			@cancel="isShownPopup = false"
			:isShown="isShownPopup"
			:isLoading="isEnabling"
			:title="popupTitle"
			:texts="popupTexts"
			:primaryButtonText="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT')"
		/>
	`
	};

	const OTHER_VERSION = 'OTHER';
	const EnableOnec = {
	  mixins: [EnableTrait, ReplaceLangPhraseTrait],
	  data() {
	    return {
	      isAppInstalled: false,
	      isAppStatusChecking: false,
	      version: Object.keys(this.options.versionList)[0],
	      isDemoEnabledFromSlider: false
	    };
	  },
	  computed: {
	    isLoading() {
	      return this.isEnabling || this.isAppStatusChecking;
	    },
	    popupPrimaryButtonText() {
	      return this.$Bitrix.Loc.getMessage(this.popupTexts > 0 ? 'CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT' : 'CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT_2');
	    },
	    popupTitle() {
	      if (this.options.hasConductedDocumentsOrQuantities) {
	        if (this.options.areTherePublishedShops) {
	          return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_1');
	        }
	        return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_2');
	      }
	      if (this.options.areTherePublishedShops) {
	        return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_3');
	      }
	      if (this.options.areThereActiveProducts) {
	        return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_4');
	      }
	      return '';
	    },
	    popupTexts() {
	      const result = [];
	      if (this.options.hasConductedDocumentsOrQuantities) {
	        if (this.options.currentMode === ModeList.MODE_B24) {
	          if (this.options.areTherePublishedShops && this.options.areThereActiveProducts) {
	            result.push({
	              text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_1'),
	              hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2')
	            });
	          } else {
	            result.push({
	              text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_1')
	            }, {
	              text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_11'),
	              hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2')
	            });
	          }
	        } else {
	          result.push({
	            text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_2'),
	            hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2')
	          });
	        }
	      }
	      if (this.options.areTherePublishedShops) {
	        result.push({
	          text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_3')
	        });
	      }
	      if (this.options.areThereActiveProducts) {
	        result.push({
	          text: this.replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_4'),
	          hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_4_HINT')
	        });
	      }
	      return result;
	    },
	    startEnablingButtonClass() {
	      return {
	        'ui-btn-clock': this.isLoading && !this.isShownPopup,
	        'ui-btn-disabled': !this.isFormValid
	      };
	    },
	    isFormValid() {
	      return true;
	    }
	  },
	  methods: {
	    startEnabling() {
	      if (!this.isFormValid) {
	        return;
	      }
	      if (this.options.isPlanRestricted && !this.isDemoEnabledFromSlider) {
	        catalog_toolAvailabilityManager.OneCPlanRestrictionSlider.show({
	          onActivateSuccessHandler: () => {
	            this.isDemoEnabledFromSlider = true;
	            this.startEnabling();
	          }
	        });
	        return;
	      }

	      // enabling right away because we have nothing to warn about
	      if (this.popupTexts.length === 0) {
	        this.onecEnable();
	        return;
	      }
	      this.isShownPopup = true;
	    },
	    onecEnable() {
	      this.checkIfOnecAppInstalled().then(() => this.enableOrInstall()).catch(error => console.error(error));
	    },
	    checkIfOnecAppInstalled() {
	      this.isAppStatusChecking = true;
	      return new Promise(resolve => {
	        Service.isOnecAppInstalled().then(isInstalled => {
	          this.isAppInstalled = isInstalled;
	        }).catch(error => console.error(error)).finally(() => {
	          this.isAppStatusChecking = false;
	          resolve();
	        });
	      });
	    },
	    enableOrInstall() {
	      if (this.isAppInstalled) {
	        this.enable();
	      } else {
	        this.isShownPopup = false;
	        BX.SidePanel.Instance.open(this.options.installUrl, {
	          cacheable: false,
	          allowChangeHistory: false,
	          width: 1000,
	          events: {
	            onCloseComplete: () => {
	              if (!this.isAppInstalled) {
	                return;
	              }
	              this.enable();
	            }
	          }
	        });
	        top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', (installed, eventResult) => {
	          this.isAppInstalled = Boolean(installed);
	        });
	      }
	    },
	    getModeLimitationTexts() {
	      return [{
	        text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LANDING_SHOP_PUBLICATION_WARNING'),
	        hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LANDING_SHOP_PUBLICATION_WARNING_HINT')
	      }];
	    },
	    getMode() {
	      return ModeList.MODE_1C;
	    },
	    getEnableOptions() {
	      return {
	        version: this.version
	      };
	    },
	    getHelpLink() {
	      return 'redirect=detail&code=20233716';
	    },
	    openHelp() {
	      if (top.BX && top.BX.Helper) {
	        top.BX.Helper.show(this.getHelpLink());
	      }
	    }
	  },
	  watch: {
	    version(newValue) {
	      if (newValue === OTHER_VERSION) {
	        main_core.Runtime.loadExtension(['ui.feedback.form']).then(() => {
	          BX.UI.Feedback.Form.open({
	            id: 'catalog-enable-wizard-1c-other-version',
	            forms: [{
	              zones: ['ru', 'by', 'kz'],
	              title: '',
	              id: 704,
	              lang: 'ru',
	              sec: 'phfehj'
	            }],
	            presets: main_core.Extension.getSettings('catalog.store-enable-wizard').get('feedbackFormOtherVersion1CPresets')
	          });
	        }).catch(error => console.error(error));
	      }
	    }
	  },
	  template: `
		<div class="inventory-management__card-item --1c --active --inner-field">
			<div class="inventory-management__card-item-inner">
				<div class="inventory-management__card-logo"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_TITLE_1C')}}
				</div>
				<div class="inventory-management__card-desc">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_1C')}}
				</div>
				<a
					@click="openHelp"
					href="#" class="inventory-management__card-link"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
				</a>
				<div class="inventory-management__card-select-field">
					<div class="inventory-management__card-select-title-wo-star">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_SELECT_TITLE_1C')}}
					</div>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select
							v-model="version"
							class="ui-ctl-element"
						>
							<option
								v-for="(name, value) in options.versionList"
								:value="value"
								:key="value"
							>
								{{name}}
							</option>
						</select>
					</div>
				</div>
				<enable-warning
					v-for="warning in getModeLimitationTexts()"
					:text="warning.text"
					:hint="warning.hint"
					:help-link="getHelpLink()"
				>
				</enable-warning>
			</div>
			<div class="ui-btn-container inventory-management__card-footer">
				<button
					v-if="!options.isBlocked"
					@click="onBack"
					class="ui-btn ui-btn-light-border ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_BACK')}}
				</button>
				<button
					@click="startEnabling"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
					:class="startEnablingButtonClass"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT')}}
				</button>
			</div>
		</div>
		<popup-field
			@enable="onecEnable"
			@cancel="isShownPopup = false"
			:isShown="isShownPopup"
			:isLoading="isLoading"
			:title="popupTitle"
			:texts="popupTexts"
			:primaryButtonText="popupPrimaryButtonText"
		/>
	`
	};

	const TitleBox = {
	  props: {
	    options: {
	      type: Object,
	      required: true
	    }
	  },
	  template: `
		<div v-if="options !== null" class="inventory-management-card-title-box">
			<div v-html="options.title" class="inventory-management-card-title"></div>
			<div
				v-if="options.subTitle"
				v-html="options.subTitle" class="inventory-management-card-subtitle">
			</div>
		</div>
	`
	};

	const InventoryCardBox = {
	  created() {
	    if (this.initEnableMode) {
	      this.startEnable(this.initEnableMode);
	    }
	  },
	  mounted() {
	    if (this.enableMode === null) {
	      this.$Bitrix.Application.instance.sendOpenedEvent();
	    }
	  },
	  props: {
	    initEnableMode: {
	      type: String,
	      required: false,
	      default: null
	    },
	    availableModes: {
	      type: Object,
	      required: true
	    },
	    currentMode: {
	      type: String,
	      required: true
	    },
	    hasConductedDocumentsOrQuantities: {
	      type: Boolean,
	      required: true
	    },
	    areTherePublishedShops: {
	      type: Boolean,
	      required: true
	    },
	    areThereActiveProducts: {
	      type: Boolean,
	      required: true
	    },
	    inventoryManagementSource: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      enableMode: null,
	      hoveredMode: null
	    };
	  },
	  components: {
	    CardBoxB24,
	    CardBoxB24Solo,
	    CardBoxOnec,
	    EnableB24,
	    EnableOnec,
	    TitleBox
	  },
	  computed: {
	    b24Mode() {
	      return ModeList.MODE_B24;
	    },
	    onecMode() {
	      return ModeList.MODE_1C;
	    },
	    titleBoxOptions() {
	      if (this.enableMode) {
	        if (this.enableMode === ModeList.MODE_1C) {
	          return {
	            title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE_ENABLE_1C')
	          };
	        }
	        return {
	          title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE_ENABLE_B24'),
	          subTitle: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_SUBTITLE_ENABLE_B24')
	        };
	      }
	      if (!this.isAvailable(ModeList.MODE_1C)) {
	        return null;
	      }
	      return {
	        title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE'),
	        subTitle: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_SUBTITLE')
	      };
	    }
	  },
	  methods: {
	    isHovered(mode) {
	      if (mode === this.b24Mode) {
	        return this.hoveredMode === mode || !this.hoveredMode && (this.currentMode === mode || !this.currentMode);
	      }
	      return this.hoveredMode === mode || !this.hoveredMode && this.currentMode === mode;
	    },
	    isAvailable(mode) {
	      return Boolean(this.availableModes[mode]);
	    },
	    isActive(mode) {
	      return this.currentMode === mode;
	    },
	    isEnabling(mode) {
	      return this.enableMode === mode;
	    },
	    getEnableOptions(mode) {
	      var _this$availableModes$;
	      const result = (_this$availableModes$ = this.availableModes[mode]) != null ? _this$availableModes$ : {};
	      result.currentMode = this.currentMode;
	      result.isBlocked = main_core.Type.isStringFilled(this.initEnableMode);
	      result.inventoryManagementSource = this.inventoryManagementSource;
	      result.hasConductedDocumentsOrQuantities = this.hasConductedDocumentsOrQuantities;
	      result.areTherePublishedShops = this.areTherePublishedShops;
	      result.areThereActiveProducts = this.areThereActiveProducts;
	      return result;
	    },
	    discardEnable() {
	      if (this.initEnableMode) {
	        return;
	      }
	      this.enableMode = null;
	    },
	    enableB24() {
	      this.startEnable(ModeList.MODE_B24);
	    },
	    enableOnec() {
	      this.startEnable(ModeList.MODE_1C);
	    },
	    startEnable(mode) {
	      if (this.currentMode === mode) {
	        return;
	      }
	      this.$Bitrix.Application.instance.sendStep2ProceededEvent(mode);
	      this.enableMode = mode;
	    },
	    onCardBoxEnter(mode) {
	      this.hoveredMode = mode;
	    },
	    onCardBoxLeave() {
	      this.hoveredMode = null;
	    }
	  },
	  template: `
		<title-box :options="titleBoxOptions"></title-box>
		<div v-if="enableMode" class="inventory-management__inner">
			<enable-b24
				v-if="isEnabling(b24Mode)"
				:options="getEnableOptions(b24Mode)"
				@back="discardEnable"
			>
			</enable-b24>
			<enable-onec
				v-if="isEnabling(onecMode)"
				:options="getEnableOptions(onecMode)"
				@back="discardEnable"
			>
			</enable-onec>
		</div>
		<template v-else>
			<div
				v-if="isAvailable(onecMode)"
				class="inventory-management__card-box"
			>
				<card-box-b24
					:isActive="isActive(b24Mode)"
					:isHovered="isHovered(b24Mode)"
					@pick="enableB24"
					@enter="onCardBoxEnter(b24Mode)"
					@leave="onCardBoxLeave()"
				>
				</card-box-b24>
				<card-box-onec
					:isActive="isActive(onecMode)"
					:isHovered="isHovered(onecMode)"
					@pick="enableOnec"
					@enter="onCardBoxEnter(onecMode)"
					@leave="onCardBoxLeave()"
				>
				</card-box-onec>
			</div>
			<card-box-b24-solo
				v-else
				:isActive="isActive(b24Mode)"
				:isHovered="isHovered(b24Mode)"
				@pick="enableB24"
			>
			</card-box-b24-solo>
		</template>
	`
	};

	let _$2 = t => t,
	  _t$2;
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _analytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analytics");
	var _template = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	var _sendEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendEvent");
	class EnableWizard {
	  constructor(params, analytics) {
	    Object.defineProperty(this, _sendEvent, {
	      value: _sendEvent2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _analytics, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _template, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _analytics)[_analytics] = analytics;
	  }
	  getCardTemplate() {
	    const app = this;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _template)[_template]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _template)[_template] = main_core.Tag.render(_t$2 || (_t$2 = _$2`<div id="inventory-management-card-wrap"></div>`));
	      ui_vue3.BitrixVue.createApp({
	        ...InventoryCardBox,
	        beforeCreate() {
	          this.$bitrix.Application.set(app);
	        }
	      }, babelHelpers.classPrivateFieldLooseBase(this, _params)[_params]).mount(babelHelpers.classPrivateFieldLooseBase(this, _template)[_template]);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _template)[_template];
	  }
	  render(node) {
	    main_core.Dom.append(this.getCardTemplate(), node);
	  }
	  sendOpenedEvent() {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _analytics)[_analytics],
	      event: 'opened'
	    });
	  }
	  sendStep2ProceededEvent(mode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _analytics)[_analytics],
	      event: 'step2_proceeded',
	      p2: `choose_${mode}`
	    });
	  }
	  sendEnableProceededEvent(mode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _analytics)[_analytics],
	      event: 'enable_proceeded',
	      p2: `choose_${mode}`
	    });
	  }
	  sendEnableDoneEvent(mode, status) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _analytics)[_analytics],
	      event: 'enable_done',
	      status,
	      p2: `choose_${mode}`
	    });
	  }
	}
	function _sendEvent2(data) {
	  main_core.Runtime.loadExtension('ui.analytics').then(exports => {
	    const {
	      sendData
	    } = exports;
	    sendData(data);
	  });
	}

	var _popup$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _options$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _getPopupTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTitle");
	var _getPopupTexts = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTexts");
	var _disable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disable");
	var _getSetting = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSetting");
	var _sendEvent$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendEvent");
	var _hasConductedDocumentsOrQuantities = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasConductedDocumentsOrQuantities");
	class Disabler {
	  constructor(options = {}) {
	    Object.defineProperty(this, _hasConductedDocumentsOrQuantities, {
	      value: _hasConductedDocumentsOrQuantities2
	    });
	    Object.defineProperty(this, _sendEvent$1, {
	      value: _sendEvent2$1
	    });
	    Object.defineProperty(this, _getSetting, {
	      value: _getSetting2
	    });
	    Object.defineProperty(this, _disable, {
	      value: _disable2
	    });
	    Object.defineProperty(this, _getPopupTexts, {
	      value: _getPopupTexts2
	    });
	    Object.defineProperty(this, _getPopupTitle, {
	      value: _getPopupTitle2
	    });
	    Object.defineProperty(this, _popup$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = options;
	    const hasCriticalErrors = babelHelpers.classPrivateFieldLooseBase(this, _getPopupTexts)[_getPopupTexts]().some(text => text.critical === true);
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = new Popup({
	      helpCode: babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('availableModes').length > 1 ? '20233748' : '15992592',
	      width: hasCriticalErrors ? null : 'auto',
	      title: babelHelpers.classPrivateFieldLooseBase(this, _getPopupTitle)[_getPopupTitle](),
	      texts: babelHelpers.classPrivateFieldLooseBase(this, _getPopupTexts)[_getPopupTexts](),
	      hideHelp: !hasCriticalErrors,
	      hideIcon: !hasCriticalErrors,
	      primaryButtonText: hasCriticalErrors ? main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_TURN_OFF_ANYWAY') : main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_TURN_OFF'),
	      primaryButtonClass: hasCriticalErrors ? 'ui-btn-danger' : 'ui-btn-primary',
	      secondaryButtonText: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_CANCEL'),
	      events: {
	        onPrimaryClick: () => babelHelpers.classPrivateFieldLooseBase(this, _disable)[_disable](),
	        onSecondaryClick: () => babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].show(false),
	        onClose: () => {}
	      }
	    });
	  }
	  open() {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].show(true);
	  }
	  sendDisableDoneEvent(status) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendEvent$1)[_sendEvent$1]({
	      tool: 'inventory',
	      category: 'settings',
	      event: 'disable_done',
	      c_section: 'settings',
	      p1: `mode_${babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('currentMode')}`,
	      status
	    });
	  }
	}
	function _getPopupTitle2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('currentMode') === ModeList.MODE_B24) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('isWithOrdersMode') === true) {
	      return main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_NOT_ABLE_TO_TURN_BACK_TITLE').replace('[break]', '<br>');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasConductedDocumentsOrQuantities)[_hasConductedDocumentsOrQuantities]()) {
	      return main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DATA_WILL_BE_DELETED_2').replace('[break]', '<br>');
	    }
	  }
	  return '';
	}
	function _getPopupTexts2() {
	  const result = [];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('currentMode') === ModeList.MODE_B24) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasConductedDocumentsOrQuantities)[_hasConductedDocumentsOrQuantities]()) {
	      result.push({
	        critical: true,
	        text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_DISABLE_B24_TEXT_1')
	      }, {
	        critical: true,
	        text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_DISABLE_B24_TEXT_2')
	      });
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getSetting)[_getSetting]('isWithOrdersMode') === true) {
	      result.push({
	        critical: true,
	        text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_NOT_ABLE_TO_TURN_BACK_TEXT').replace('[break]', '<br>')
	      });
	    }
	  }
	  if (result.length === 0) {
	    result.push({
	      text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT')
	    });
	  }
	  return result;
	}
	function _disable2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].load(true);
	  Service.disable().then(() => {
	    var _babelHelpers$classPr;
	    this.sendDisableDoneEvent('success');
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].events) == null ? void 0 : _babelHelpers$classPr.onDisabled == null ? void 0 : _babelHelpers$classPr.onDisabled();
	  }).catch(error => {
	    var _error$customData$ana, _error$customData;
	    this.sendDisableDoneEvent(`error_${(_error$customData$ana = error == null ? void 0 : (_error$customData = error.customData) == null ? void 0 : _error$customData.analyticsCode) != null ? _error$customData$ana : 'unknown'}`);
	    top.BX.UI.Notification.Center.notify({
	      content: main_core.Text.encode(error.message)
	    });
	  }).finally(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].load(false);
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].show(false);
	  });
	}
	function _getSetting2(name) {
	  return main_core.Extension.getSettings('catalog.store-enable-wizard').get(name);
	}
	function _sendEvent2$1(data) {
	  main_core.Runtime.loadExtension('ui.analytics').then(exports => {
	    const {
	      sendData
	    } = exports;
	    sendData(data);
	  });
	}
	function _hasConductedDocumentsOrQuantities2() {
	  if (main_core.Type.isBoolean(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].hasConductedDocumentsOrQuantities)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].hasConductedDocumentsOrQuantities;
	  }
	  return true;
	}

	exports.EnableWizardOpener = EnableWizardOpener;
	exports.EnableWizard = EnableWizard;
	exports.ModeList = ModeList;
	exports.Disabler = Disabler;
	exports.AnalyticsContextList = AnalyticsContextList;

}((this.BX.Catalog.Store = this.BX.Catalog.Store || {}),BX.UI.IconSet,BX.Main,BX,BX,BX.UI,BX.Catalog,BX,BX,BX,BX.Vue3,BX));
//# sourceMappingURL=index.bundle.js.map
