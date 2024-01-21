/* eslint-disable */
this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_vue3,ui_vue3_vuex,main_core_events,main_core,catalog_storeUse) {
	'use strict';

	var WarehouseSection = {
	  props: {
	    title: String,
	    description: String,
	    iconType: String
	  },
	  computed: {
	    getIconClass: function getIconClass() {
	      var _sectionIconClasses$t;
	      var sectionIconClasses = {
	        documents: '--docs',
	        crm: '--crm',
	        mobile: '--mobile'
	      };
	      return (_sectionIconClasses$t = sectionIconClasses[this.$props.iconType]) !== null && _sectionIconClasses$t !== void 0 ? _sectionIconClasses$t : '--docs';
	    }
	  },
	  template: "\n\t\t<div class=\"catalog-warehouse__master-clear__section\">\n\t\t\t<div \n\t\t\t\tclass=\"catalog-warehouse__master-clear_section_icon\"\n\t\t\t\t:class=\"getIconClass\"\n\t\t\t></div>\n\t\t\t<div class=\"catalog-warehouse__master-clear_section_inner\">\n\t\t\t\t<div class=\"catalog-warehouse__master-clear__title\">{{title}}</div>\n\t\t\t\t<div class=\"catalog-warehouse__master-clear__text\">{{description}}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Content = {
	  components: {
	    WarehouseSection: WarehouseSection
	  },
	  computed: _objectSpread({
	    sectionTitlePrefix: function sectionTitlePrefix() {
	      return 'CAT_WAREHOUSE_MASTER_NEW_SECTION_TITLE_';
	    },
	    sectionDescriptionPrefix: function sectionDescriptionPrefix() {
	      return 'CAT_WAREHOUSE_MASTER_NEW_SECTION_DESCRIPTION_';
	    },
	    getMobileBoxClass: function getMobileBoxClass() {
	      var result = {
	        'catalog-warehouse__master-clear__mobile-box': true
	      };
	      if (this.getPreviewLang !== 'ru') {
	        result['--eng'] = true;
	      }
	      return result;
	    }
	  }, ui_vue3_vuex.mapGetters(['getPreviewLang'])),
	  // language = Vue
	  template: "\n\t\t<div class=\"catalog-warehouse__master-clear--content\">\n\t\t\t<div class=\"catalog-warehouse__master-clear_inner\">\n\t\t\t\t<div class=\"catalog-warehouse-master-clear-title\">\n\t\t\t\t\t<div class=\"catalog-warehouse-master-clear-title-text--new\">\n\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_TITLE') }}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-warehouse__master-clear__box\">\n\t\t\t\t\t<div :class=\"getMobileBoxClass\"></div>\n\t\t\t\t\t<div class=\"catalog-warehouse__master-clear__section_box\">\n\t\t\t\t\t\t<WarehouseSection\n\t\t\t\t\t\t\t:title=\"$Bitrix.Loc.getMessage(sectionTitlePrefix + 'DOCUMENTS')\"\n\t\t\t\t\t\t\t:description=\"$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'DOCUMENTS')\"\n\t\t\t\t\t\t\t:iconType=\"'documents'\"\n\t\t\t\t\t\t/>\n\n\t\t\t\t\t\t<WarehouseSection\n\t\t\t\t\t\t\t:title=\"$Bitrix.Loc.getMessage(sectionTitlePrefix + 'CRM')\"\n\t\t\t\t\t\t\t:description=\"$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'CRM')\"\n\t\t\t\t\t\t\t:iconType=\"'crm'\"\n\t\t\t\t\t\t/>\n\n\t\t\t\t\t\t<WarehouseSection\n\t\t\t\t\t\t\t:title=\"$Bitrix.Loc.getMessage(sectionTitlePrefix + 'MOBILE')\"\n\t\t\t\t\t\t\t:description=\"$Bitrix.Loc.getMessage(sectionDescriptionPrefix + 'MOBILE')\"\n\t\t\t\t\t\t\t:iconType=\"'mobile'\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Footer = {
	  computed: _objectSpread$1({
	    getButtonClass: function getButtonClass() {
	      var classes = ['ui-btn', 'ui-btn-round', 'ui-btn-no-caps', 'ui-btn-lg', 'catalog-warehouse__master-clear--btn'];
	      if (this.isLoading === true) {
	        classes.push('ui-btn-wait');
	      }
	      if (this.isRestrictedAccess === true) {
	        classes.push('ui-btn-disabled');
	      }
	      if (this.isUsed === true) {
	        classes.push('ui-btn-default');
	      } else {
	        classes.push('ui-btn-success');
	      }
	      return classes;
	    },
	    getHintClass: function getHintClass() {
	      return ['ui-link-dashed', 'catalog-warehouse__master-clear--hint'];
	    },
	    getButtonText: function getButtonText() {
	      return this.isUsed ? this.$Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_DEACTIVATE_BUTTON') : this.$Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_ACTIVATE_BUTTON');
	    }
	  }, ui_vue3_vuex.mapGetters(['isLoading', 'isUsed', 'isRestrictedAccess'])),
	  methods: _objectSpread$1({
	    openHelpdesk: function openHelpdesk() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=14566618');
	      }
	    },
	    onButtonClick: function onButtonClick() {
	      this.$emit('onButtonClick');
	    }
	  }, ui_vue3_vuex.mapMutations(['setIsLoading'])),
	  // language = Vue
	  template: "\n\t<div class=\"catalog-warehouse__master-clear--footer\">\n\t\t<button \n\t\t\t:class=\"getButtonClass\"\n\t\t\tv-on:click=\"onButtonClick\"\n\t\t>{{ getButtonText }}</button>\n\t\t<span \n\t\t\t:class=\"getHintClass\"\n\t\t\tv-on:click=\"openHelpdesk\"\n\t\t>\n\t\t\t{{ $Bitrix.Loc.getMessage('CAT_WAREHOUSE_MASTER_NEW_HINT_MORE') }}\n\t\t</span>\n\t</div>\n\t"
	};

	var ButtonClickHandler = /*#__PURE__*/function () {
	  function ButtonClickHandler(props) {
	    babelHelpers.classCallCheck(this, ButtonClickHandler);
	    this.props = props;
	    this.isUsed = props.isUsed;
	    this.hasErrors = false;
	    this.isPlanRestricted = props.isPlanRestricted;
	    this.isUsed1C = props.isUsed1C;
	    this.isWithOrdersMode = props.isWithOrdersMode;
	    this.isRestrictedAccess = props.isRestrictedAccess;
	  }
	  babelHelpers.createClass(ButtonClickHandler, [{
	    key: "handle",
	    value: function handle() {
	      if (this.isUsed) {
	        this.handleDisableInventoryManagement();
	      } else {
	        this.handleEnableInventoryManagement();
	      }
	    }
	  }, {
	    key: "handleEnableInventoryManagement",
	    value: function handleEnableInventoryManagement() {
	      this.checkAccess();
	      this.checkPlanRestriction();
	      this.checkUsage1C();
	      this.checkWithOrdersMode();
	      if (!this.hasErrors) {
	        this.showEnablePopup();
	      }
	      this.hasErrors = false;
	    }
	  }, {
	    key: "handleDisableInventoryManagement",
	    value: function handleDisableInventoryManagement() {
	      this.checkAccess();
	      if (!this.hasErrors) {
	        this.showConfirmDisablePopup();
	      }
	      this.hasErrors = false;
	    }
	  }, {
	    key: "showEnablePopup",
	    value: function showEnablePopup() {
	      /**
	       * @see DialogEnable.popup()
	       */
	      new BX.Catalog.StoreUse.DialogEnable().popup();
	    }
	  }, {
	    key: "showErrorPopup",
	    value: function showErrorPopup(options) {
	      /**
	       * @see DialogError.popup()
	       */
	      new BX.Catalog.StoreUse.DialogError(options).popup();
	    }
	  }, {
	    key: "showPlanRestrictionSlider",
	    value: function showPlanRestrictionSlider() {
	      top.BX.UI.InfoHelper.show('limit_store_inventory_management');
	    }
	  }, {
	    key: "showConfirmDisablePopup",
	    value: function showConfirmDisablePopup() {
	      /**
	       * @see DialogDisable.disablePopup()
	       */
	      var dialogDisable = new BX.Catalog.StoreUse.DialogDisable();
	      dialogDisable.disablePopup();
	    }
	  }, {
	    key: "checkAccess",
	    value: function checkAccess() {
	      if (this.hasErrors || !this.isRestrictedAccess) {
	        return;
	      }
	      this.hasErrors = true;
	      var helpArticleId = '16556596';
	      this.showErrorPopup({
	        text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_RIGHTS_RESTRICTED_MSGVER_1', {
	          '#LINK_START#': '<a href="#" class="ui-link ui-link-dashed documents-grid-link">',
	          '#LINK_END#': '</a>'
	        }),
	        helpArticleId: helpArticleId
	      });
	    }
	  }, {
	    key: "checkPlanRestriction",
	    value: function checkPlanRestriction() {
	      if (this.hasErrors || !this.isPlanRestricted) {
	        return;
	      }
	      this.hasErrors = true;
	      this.showPlanRestrictionSlider();
	    }
	  }, {
	    key: "checkUsage1C",
	    value: function checkUsage1C() {
	      if (this.hasErrors || !this.isUsed1C) {
	        return;
	      }
	      this.hasErrors = true;
	      this.showErrorPopup({
	        text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ERROR_1C_USED_MSGVER_1')
	      });
	    }
	  }, {
	    key: "checkWithOrdersMode",
	    value: function checkWithOrdersMode() {
	      if (this.hasErrors || !this.isWithOrdersMode) {
	        return;
	      }
	      this.hasErrors = true;
	      var helpArticleId = '15718276';
	      this.showErrorPopup({
	        text: main_core.Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ERROR_ORDER_MODE_MSGVER_1', {
	          '#LINK_START#': '<a href="#" class="ui-link ui-link-dashed documents-grid-link">',
	          '#LINK_END#': '</a>'
	        }),
	        helpArticleId: helpArticleId
	      });
	    }
	  }]);
	  return ButtonClickHandler;
	}();

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _application = /*#__PURE__*/new WeakMap();
	var _initStore = /*#__PURE__*/new WeakSet();
	var App = /*#__PURE__*/function () {
	  function App(_props) {
	    babelHelpers.classCallCheck(this, App);
	    _classPrivateMethodInitSpec(this, _initStore);
	    _classPrivateFieldInitSpec(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    this.rootNode = document.getElementById(_props.rootNodeId);
	    this.store = _classPrivateMethodGet(this, _initStore, _initStore2).call(this, _props);
	  }
	  babelHelpers.createClass(App, [{
	    key: "attachTemplate",
	    value: function attachTemplate() {
	      babelHelpers.classPrivateFieldSet(this, _application, ui_vue3.BitrixVue.createApp({
	        components: {
	          Content: Content,
	          Footer: Footer
	        },
	        computed: _objectSpread$2({}, ui_vue3_vuex.mapGetters(['getSelectedCostPriceAccountingMethod', 'getButtonClickHandler', 'getInventoryManagementSource'])),
	        created: function created() {
	          this.controller = new catalog_storeUse.Controller();
	        },
	        mounted: function mounted() {
	          main_core_events.EventEmitter.subscribe(catalog_storeUse.EventType.popup.disable, this.disable);
	          main_core_events.EventEmitter.subscribe(catalog_storeUse.EventType.popup.enableWithResetDocuments, this.enableWithResetDocuments);
	          main_core_events.EventEmitter.subscribe(catalog_storeUse.EventType.popup.enableWithoutReset, this.enableWithoutReset);
	          main_core_events.EventEmitter.subscribe(catalog_storeUse.EventType.popup.selectCostPriceAccountingMethod, this.handleAccountingMethodSelected);
	        },
	        unmounted: function unmounted() {
	          main_core_events.EventEmitter.unsubscribe(catalog_storeUse.EventType.popup.disable, this.disable);
	          main_core_events.EventEmitter.unsubscribe(catalog_storeUse.EventType.popup.enableWithResetDocuments, this.enableWithResetDocuments);
	          main_core_events.EventEmitter.unsubscribe(catalog_storeUse.EventType.popup.enableWithoutReset, this.enableWithoutReset);
	          main_core_events.EventEmitter.unsubscribe(catalog_storeUse.EventType.popup.selectCostPriceAccountingMethod, this.handleAccountingMethodSelected);
	        },
	        methods: _objectSpread$2(_objectSpread$2({}, ui_vue3_vuex.mapMutations(['setIsLoading', 'setSelectedCostPriceAccountingMethod'])), {}, {
	          handleOnButtonClick: function handleOnButtonClick() {
	            /**
	             * @see ButtonClickHandler.handle()
	             */
	            this.getButtonClickHandler.handle();
	          },
	          handleAccountingMethodSelected: function handleAccountingMethodSelected(item) {
	            var value = item.data.method === catalog_storeUse.DialogCostPriceAccountingMethodSelection.METHOD_FIFO ? catalog_storeUse.DialogCostPriceAccountingMethodSelection.METHOD_FIFO : catalog_storeUse.DialogCostPriceAccountingMethodSelection.METHOD_AVERAGE;
	            this.setSelectedCostPriceAccountingMethod(value);
	          },
	          closeSlider: function closeSlider() {
	            var slider = BX.SidePanel.Instance.getTopSlider();
	            if (slider) {
	              slider.close();
	            }
	          },
	          disable: function disable() {
	            this.setIsLoading(true);
	            this.controller.inventoryManagementDisabled().then(this.handleSuccessfulChanging)["catch"](this.handleUnsuccessfulChanging);
	          },
	          enable: function enable() {
	            var _this = this;
	            this.enableBy(function () {
	              return _this.controller.inventoryManagementEnabled();
	            });
	          },
	          enableWithResetDocuments: function enableWithResetDocuments() {
	            var _this2 = this;
	            this.enableBy(function () {
	              return _this2.controller.inventoryManagementEnableWithResetDocuments({
	                costPriceAccountingMethod: _this2.getSelectedCostPriceAccountingMethod
	              });
	            });
	          },
	          enableWithoutReset: function enableWithoutReset() {
	            var _this3 = this;
	            this.enableBy(function () {
	              return _this3.controller.inventoryManagementEnableWithoutReset({
	                costPriceAccountingMethod: _this3.getSelectedCostPriceAccountingMethod
	              });
	            });
	          },
	          enableBy: function enableBy(method) {
	            this.setIsLoading(true);
	            method().then(this.handleSuccessfulChanging)["catch"](this.handleUnsuccessfulChanging);
	          },
	          handleSuccessfulChanging: function handleSuccessfulChanging() {
	            this.setIsLoading(false);
	            var slider = BX.SidePanel.Instance.getTopSlider();
	            if (slider) {
	              slider.getData().set('isInventoryManagementEnabled', true);
	            }
	            this.closeSlider();
	          },
	          handleUnsuccessfulChanging: function handleUnsuccessfulChanging(response) {
	            if (response.errors.length) {
	              top.BX.UI.Notification.Center.notify({
	                content: main_core.Text.encode(response.errors[0].message)
	              });
	            }
	            this.setIsLoading(false);
	          }
	        }),
	        // language = Vue
	        template: "\n\t\t\t\t<Content/>\n\t\t\t\t<Footer @onButtonClick=\"handleOnButtonClick\"/>\n\t\t\t"
	      }));
	      babelHelpers.classPrivateFieldGet(this, _application).use(this.store);
	      babelHelpers.classPrivateFieldGet(this, _application).mount(this.rootNode);
	    }
	  }]);
	  return App;
	}();
	function _initStore2(props) {
	  var settingsStore = {
	    state: function state() {
	      return _objectSpread$2({
	        isLoading: false
	      }, props);
	    },
	    getters: {
	      isUsed: function isUsed(state) {
	        return state.isUsed;
	      },
	      isLoading: function isLoading(state) {
	        return state.isLoading;
	      },
	      getSelectedCostPriceAccountingMethod: function getSelectedCostPriceAccountingMethod(state) {
	        return state.selectedCostPriceAccountingMethod;
	      },
	      isPlanRestricted: function isPlanRestricted(state) {
	        return state.isPlanRestricted;
	      },
	      isUsed1C: function isUsed1C(state) {
	        return state.isUsed1C;
	      },
	      isWithOrdersMode: function isWithOrdersMode(state) {
	        return state.isWithOrdersMode;
	      },
	      isRestrictedAccess: function isRestrictedAccess(state) {
	        return state.isRestrictedAccess;
	      },
	      getInventoryManagementSource: function getInventoryManagementSource(state) {
	        return state.inventoryManagementSource;
	      },
	      getPreviewLang: function getPreviewLang(state) {
	        return state.previewLang;
	      },
	      getButtonClickHandler: function getButtonClickHandler(state) {
	        return new ButtonClickHandler(state);
	      }
	    },
	    mutations: {
	      setIsLoading: function setIsLoading(state, value) {
	        state.isLoading = value;
	      },
	      setSelectedCostPriceAccountingMethod: function setSelectedCostPriceAccountingMethod(state, value) {
	        state.selectedCostPriceAccountingMethod = value;
	      }
	    }
	  };
	  return ui_vue3_vuex.createStore(settingsStore);
	}

	exports.App = App;

}((this.BX.Catalog.WarehouseMaster = this.BX.Catalog.WarehouseMaster || {}),BX.Vue3,BX.Vue3.Vuex,BX.Event,BX,BX.Catalog.StoreUse));
//# sourceMappingURL=warehouse-master.bundle.js.map
