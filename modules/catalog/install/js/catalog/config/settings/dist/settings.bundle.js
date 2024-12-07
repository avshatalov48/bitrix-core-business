/* eslint-disable */
this.BX = this.BX || {};
this.BX.Catalog = this.BX.Catalog || {};
(function (exports,ui_progressbar,ui_notification,catalog_externalCatalogPlacement,catalog_toolAvailabilityManager,ui_label,main_popup,ui_buttons,ui_alerts,ui_formElements_field,ui_formElements_view,ui_section,ui_iconSet_crm,ui_iconSet_editor,catalog_storeEnableWizard,main_core,main_core_events) {
	'use strict';

	class ProductSettingsUpdater {
	  constructor(params) {
	    this.url = '/bitrix/tools/catalog/product_settings.php';
	    this.stepOptions = {
	      ajaxSessionID: '',
	      maxExecutionTime: 30,
	      maxOperationCounter: 10
	    };
	    this.finish = false;
	    this.currentState = {
	      counter: 0,
	      operationCounter: 0,
	      errorCounter: 0,
	      lastID: 0
	    };
	    this.ajaxParams = {
	      operation: 'Y'
	    };
	    this.iblocks = [];
	    this.iblockIndex = -1;
	    this.stepOptions.ajaxSessionID = 'productSettings';
	    this.currentState.counter = 0;
	    this.events = params.events;
	    this.settings = params.settings;
	  }
	  nextStep() {
	    for (let key in this.stepOptions) {
	      if (this.stepOptions.hasOwnProperty(key)) {
	        this.ajaxParams[key] = this.stepOptions[key];
	      }
	    }
	    for (let key in this.currentState) {
	      if (this.currentState.hasOwnProperty(key)) {
	        this.ajaxParams[key] = this.currentState[key];
	      }
	    }
	    this.ajaxParams.sessid = BX.bitrix_sessid();
	    this.ajaxParams.lang = BX.message('LANGUAGE_ID');
	    BX.ajax.loadJSON(this.url, this.ajaxParams, BX.proxy(this.nextStepResult, this));
	  }
	  nextStepResult(result) {
	    if (BX.type.isPlainObject(result)) {
	      this.currentState.lastID = result.lastID;
	      this.stepOptions.maxOperationCounter = result.maxOperationCounter;
	      this.currentState.operationCounter = parseInt(result.operationCounter, 10);
	      if (isNaN(this.currentState.operationCounter)) {
	        this.currentState.operationCounter = 0;
	      }
	      this.currentState.errorCounter = parseInt(result.errorCounter, 10);
	      if (isNaN(this.currentState.errorCounter)) {
	        this.currentState.errorCounter = 0;
	      }
	      if (this.events.onProgress) {
	        this.events.onProgress({
	          allCnt: result.allCounter,
	          doneCnt: result.allOperationCounter,
	          currentIblockName: this.iblocks[this.iblockIndex].NAME
	        });
	      }
	      if (this.finish) {
	        this.finishOperation();
	      } else {
	        this.checkOperation(result.finishOperation);
	      }
	    }
	  }
	  finishOperation() {
	    this.currentState.operationCounter = 0;
	    this.currentState.errorCounter = 0;
	    this.currentState.lastID = 0;
	    this.finish = false;
	    if (this.events.onComplete) {
	      this.events.onComplete();
	    }
	  }
	  startOperation() {
	    BX.ajax.loadJSON(this.url, {
	      sessid: BX.bitrix_sessid(),
	      changeSettings: 'Y',
	      ...this.settings
	    }, BX.proxy(this.changeSettingsResult, this));
	  }
	  changeSettingsResult(result) {
	    if (!BX.type.isPlainObject(result)) {
	      return;
	    }
	    if (result.success === 'Y') {
	      this.loadIblockList();
	    } else {
	      this.stopOperation();
	    }
	  }
	  stopOperation() {
	    this.finish = true;
	  }
	  checkIblockIndex() {
	    return !(this.iblocks.length === 0 || this.iblockIndex < 0 || this.iblockIndex >= this.iblocks.length);
	  }
	  loadIblockList() {
	    BX.ajax.loadJSON(this.url, {
	      sessid: BX.bitrix_sessid(),
	      getIblock: 'Y'
	    }, result => {
	      if (BX.type.isArray(result)) {
	        this.iblocks = result;
	        if (this.iblocks.length > 0) {
	          this.iblockIndex = 0;
	          this.iblockReindex();
	        } else {
	          this.stopOperation();
	        }
	      }
	    });
	  }
	  iblockReindex() {
	    if (this.finish || !this.checkIblockIndex()) {
	      return;
	    }
	    this.initStep();
	    this.nextStep();
	  }
	  initStep() {
	    this.currentState.iblockId = this.iblocks[this.iblockIndex].ID;
	    this.currentState.counter = this.iblocks[this.iblockIndex].COUNT;
	    this.currentState.operationCounter = 0;
	    this.currentState.errorCounter = 0;
	    this.currentState.lastID = 0;
	  }
	  checkOperation(result) {
	    if (!!result) {
	      this.iblockIndex++;
	      if (this.iblockIndex >= this.iblocks.length || this.currentState.errorCounter > 0) {
	        this.finishOperation();
	        if (this.currentState.errorCounter == 0) {
	          this.finalRequest();
	        }
	      } else {
	        this.initStep();
	        this.nextStep();
	      }
	    } else {
	      this.nextStep();
	    }
	  }
	  finalRequest() {
	    let iblockList = [];
	    if (this.iblocks.length > 0) {
	      for (let i = 0; i < this.iblocks.length; i++) {
	        iblockList[iblockList.length] = this.iblocks[i].ID;
	      }
	      BX.ajax.get(this.url, {
	        sessid: BX.bitrix_sessid(),
	        finalRequest: 'Y',
	        iblockList
	      });
	    }
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _currentIblockName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentIblockName");
	var _allCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allCount");
	var _doneCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("doneCount");
	var _onComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onComplete");
	var _elements = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("elements");
	var _getProgressWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProgressWidth");
	var _redraw = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("redraw");
	class ProductUpdater {
	  constructor(props) {
	    Object.defineProperty(this, _redraw, {
	      value: _redraw2
	    });
	    Object.defineProperty(this, _getProgressWidth, {
	      value: _getProgressWidth2
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentIblockName, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _allCount, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _doneCount, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _onComplete, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _elements, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = props.settings;
	    babelHelpers.classPrivateFieldLooseBase(this, _onComplete)[_onComplete] = props.onComplete;
	    new ProductSettingsUpdater({
	      settings: babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings],
	      events: {
	        onProgress: data => {
	          babelHelpers.classPrivateFieldLooseBase(this, _currentIblockName)[_currentIblockName] = data.currentIblockName;
	          babelHelpers.classPrivateFieldLooseBase(this, _allCount)[_allCount] = data.allCnt;
	          babelHelpers.classPrivateFieldLooseBase(this, _doneCount)[_doneCount] = data.doneCnt;
	          babelHelpers.classPrivateFieldLooseBase(this, _redraw)[_redraw]();
	        },
	        onComplete: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _onComplete)[_onComplete]();
	        }
	      }
	    }).startOperation();
	  }
	  render() {
	    const processedText = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_OUT_OF').replace('#PROCESSED#', babelHelpers.classPrivateFieldLooseBase(this, _doneCount)[_doneCount]).replace('#TOTAL#', babelHelpers.classPrivateFieldLooseBase(this, _allCount)[_allCount]);
	    babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].text = main_core.Tag.render(_t || (_t = _`
			<div class="ui-progressbar-text-after">
				${0}
			</div>
		`), processedText);
	    babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].currentIblock = main_core.Tag.render(_t2 || (_t2 = _`
			<div style="padding-top: 10px;">
			</div>
		`));
	    babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].progressBar = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-progressbar-bar"></div>
		`));
	    main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].progressBar, 'width', babelHelpers.classPrivateFieldLooseBase(this, _getProgressWidth)[_getProgressWidth]());
	    return main_core.Tag.render(_t4 || (_t4 = _`
			<div>
				<div class="ui-progressbar ui-progressbar-column">
					<div style="font-weight: bold;" class="ui-progressbar-text-before">
						${0}
					</div>
					<div class="ui-progressbar-track">
						${0}
					</div>
					${0}
				</div>
				<div style="color: rgb(83, 92, 105); font-size: 12px;">
					${0}
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_UPDATE_TITLE'), babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].progressBar, babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].text, main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_UPDATE_WAIT'), babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].currentIblock);
	  }
	}
	function _getProgressWidth2() {
	  let width = 0;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _allCount)[_allCount] > 0) {
	    width = Math.round(babelHelpers.classPrivateFieldLooseBase(this, _doneCount)[_doneCount] / babelHelpers.classPrivateFieldLooseBase(this, _allCount)[_allCount] * 100);
	  }
	  return `${width}%`;
	}
	function _redraw2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].text.innerHTML = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_OUT_OF').replace('#PROCESSED#', babelHelpers.classPrivateFieldLooseBase(this, _doneCount)[_doneCount]).replace('#TOTAL#', babelHelpers.classPrivateFieldLooseBase(this, _allCount)[_allCount]);
	  babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].currentIblock.innerHTML = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_SETTINGS_CURRENT_CATALOG').replace('#CATALOG_NAME#', babelHelpers.classPrivateFieldLooseBase(this, _currentIblockName)[_currentIblockName]);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _elements)[_elements].progressBar, 'width', babelHelpers.classPrivateFieldLooseBase(this, _getProgressWidth)[_getProgressWidth]());
	}

	var _parentPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parentPage");
	var _costPriceCalculationParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("costPriceCalculationParams");
	class CostPriceCalculation {
	  constructor(params) {
	    Object.defineProperty(this, _parentPage, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _costPriceCalculationParams, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _costPriceCalculationParams)[_costPriceCalculationParams] = params.costPriceCalculationParams;
	    babelHelpers.classPrivateFieldLooseBase(this, _parentPage)[_parentPage] = params.parentPage;
	  }
	  buildSection() {
	    const section = new ui_section.Section({
	      title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_TITLE'),
	      titleIconClasses: 'ui-icon-set --numbered-list',
	      isOpen: true
	    });
	    const costPriceCalculationSection = new ui_formElements_field.SettingsSection({
	      parent: babelHelpers.classPrivateFieldLooseBase(this, _parentPage)[_parentPage],
	      section
	    });
	    section.append(new ui_section.Row({
	      content: new ui_alerts.Alert({
	        text: `
							${main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_HINT')}
							<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=17858278')">
								${main_core.Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
							</a>
						`,
	        inline: true,
	        size: ui_alerts.AlertSize.SMALL,
	        color: ui_alerts.AlertColor.PRIMARY
	      }).getContainer()
	    }).render());
	    const selector = new ui_formElements_view.Selector({
	      label: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_METHOD'),
	      name: 'costPriceCalculationMethod',
	      items: babelHelpers.classPrivateFieldLooseBase(this, _costPriceCalculationParams)[_costPriceCalculationParams].items,
	      hints: babelHelpers.classPrivateFieldLooseBase(this, _costPriceCalculationParams)[_costPriceCalculationParams].hints,
	      isFieldDisabled: true
	    });
	    selector.getInputNode().setAttribute('required', 'required');
	    main_core.Event.bind(selector.getInputNode(), 'change', () => {
	      const alert = new ui_alerts.Alert({
	        text: `
					${main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_WARNING')}
					<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=17858278')">
						${main_core.Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
					</a>
				`,
	        inline: true,
	        size: ui_alerts.AlertSize.SMALL,
	        color: ui_alerts.AlertColor.WARNING
	      }).getContainer();
	      const row = new ui_section.Row({
	        content: alert
	      }).render();
	      section.prepend(row);
	    });
	    new ui_formElements_field.SettingsRow({
	      parent: costPriceCalculationSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: selector
	      })
	    });
	    return costPriceCalculationSection;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	var _isInventoryManagementEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInventoryManagementEnabled");
	var _is1cRestricted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("is1cRestricted");
	var _currentMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentMode");
	var _onecStatusUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onecStatusUrl");
	var _rootElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootElement");
	var _refreshAppLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refreshAppLink");
	var _refreshStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refreshStatus");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _getSettingsLinkElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSettingsLinkElement");
	class ModeStatus {
	  constructor(params) {
	    Object.defineProperty(this, _getSettingsLinkElement, {
	      value: _getSettingsLinkElement2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _refreshStatus, {
	      value: _refreshStatus2
	    });
	    Object.defineProperty(this, _refreshAppLink, {
	      value: _refreshAppLink2
	    });
	    Object.defineProperty(this, _isInventoryManagementEnabled, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _is1cRestricted, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentMode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onecStatusUrl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rootElement, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isInventoryManagementEnabled)[_isInventoryManagementEnabled] = params.isInventoryManagementEnabled;
	    babelHelpers.classPrivateFieldLooseBase(this, _is1cRestricted)[_is1cRestricted] = params.is1cRestricted;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentMode)[_currentMode] = params.currentMode;
	    babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl] = params.onecStatusUrl;
	    babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement] = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div id="inventoryManagementStatus">
			</div>
		`));
	  }
	  initialize() {
	    let statusText = '';
	    let statusColor = '';
	    let labelStatus = '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentMode)[_currentMode] === catalog_storeEnableWizard.ModeList.MODE_1C) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isInventoryManagementEnabled)[_isInventoryManagementEnabled]) {
	        statusText = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_CHECKING');
	        statusColor = ui_label.LabelColor.LIGHT;
	        labelStatus = 'loading';
	        catalog_externalCatalogPlacement.ExternalCatalogPlacement.create().initialize().then(() => {
	          this.update({
	            text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_CONNECTED'),
	            color: ui_label.LabelColor.LIGHT_GREEN
	          });
	        }).catch(() => {
	          this.update({
	            text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_1C_NOT_CONNECTED'),
	            color: ui_label.LabelColor.LIGHT_RED
	          });
	        });
	      } else {
	        statusText = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_1C_NOT_CONNECTED');
	        statusColor = ui_label.LabelColor.LIGHT;
	      }
	    }
	    const label = new ui_label.Label({
	      text: statusText,
	      color: statusColor,
	      size: ui_label.LabelSize.LG,
	      fill: true,
	      status: labelStatus
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _render)[_render](label);
	    return babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement];
	  }
	  update({
	    text,
	    color
	  }) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement]) {
	      return;
	    }
	    const label = new ui_label.Label({
	      text,
	      color,
	      size: ui_label.LabelSize.LG,
	      fill: true
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _render)[_render](label);
	  }
	}
	function _refreshAppLink2() {
	  main_core.ajax.runComponentAction('bitrix:catalog.config.settings', 'refreshAppLink', {
	    mode: 'class'
	  }).then(response => {
	    if (!response.data) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl] = response.data;
	  });
	}
	function _refreshStatus2() {
	  catalog_externalCatalogPlacement.ExternalCatalogPlacement.create().reset();
	}
	function _render2(label) {
	  const settingsLinkElement = babelHelpers.classPrivateFieldLooseBase(this, _getSettingsLinkElement)[_getSettingsLinkElement]();
	  const labelElement = label.render();
	  let clickHandler = () => {};
	  if (babelHelpers.classPrivateFieldLooseBase(this, _is1cRestricted)[_is1cRestricted]) {
	    clickHandler = event => {
	      event.preventDefault();
	      catalog_toolAvailabilityManager.OneCPlanRestrictionSlider.show();
	    };
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl].type === 'app') {
	    clickHandler = event => {
	      event.preventDefault();
	      top.BX.rest.AppLayout.openApplication(babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl].value, {
	        source: 'inventory-management'
	      }, false, () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _refreshStatus)[_refreshStatus]();
	        this.initialize();
	      });
	    };
	  } else {
	    clickHandler = event => {
	      event.preventDefault();
	      BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl].value, {
	        customLeftBoundary: 0,
	        cacheable: false,
	        loader: 'market:detail',
	        width: 1162,
	        events: {
	          onClose: () => {
	            babelHelpers.classPrivateFieldLooseBase(this, _refreshAppLink)[_refreshAppLink]();
	            babelHelpers.classPrivateFieldLooseBase(this, _refreshStatus)[_refreshStatus]();
	            this.initialize();
	          }
	        }
	      });
	    };
	  }
	  main_core.Event.bind(settingsLinkElement, 'click', clickHandler);
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement]);
	  main_core.Dom.append(labelElement, babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement]);
	  main_core.Dom.append(settingsLinkElement, babelHelpers.classPrivateFieldLooseBase(this, _rootElement)[_rootElement]);
	}
	function _getSettingsLinkElement2() {
	  const before = babelHelpers.classPrivateFieldLooseBase(this, _is1cRestricted)[_is1cRestricted] ? '<span class="tariff-lock"></span>' : '';
	  return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<span class="catalog-settings-inventory-management-mode-settings-container">
				${0}
				<a href="${0}" class="catalog-settings-inventory-management-mode-settings" data-slider-ignore-autobinding="true">
					${0}
				</a>
			</span>
		`), before, babelHelpers.classPrivateFieldLooseBase(this, _onecStatusUrl)[_onecStatusUrl].value, main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_SETTINGS'));
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1,
	  _t4$1;
	var _parentPage$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parentPage");
	var _inventoryManagementParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inventoryManagementParams");
	var _configCatalogSource = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("configCatalogSource");
	var _inventoryManagementDisabler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inventoryManagementDisabler");
	var _getCurrentModeBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentModeBlock");
	var _sendEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendEvent");
	var _getHelpLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHelpLink");
	class Mode {
	  constructor(params) {
	    Object.defineProperty(this, _getHelpLink, {
	      value: _getHelpLink2
	    });
	    Object.defineProperty(this, _sendEvent, {
	      value: _sendEvent2
	    });
	    Object.defineProperty(this, _getCurrentModeBlock, {
	      value: _getCurrentModeBlock2
	    });
	    Object.defineProperty(this, _parentPage$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inventoryManagementParams, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _configCatalogSource, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _inventoryManagementDisabler, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _parentPage$1)[_parentPage$1] = params.parentPage;
	    babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams] = params.inventoryManagementParams;
	    babelHelpers.classPrivateFieldLooseBase(this, _configCatalogSource)[_configCatalogSource] = params.configCatalogSource;
	    babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementDisabler)[_inventoryManagementDisabler] = new catalog_storeEnableWizard.Disabler({
	      hasConductedDocumentsOrQuantities: babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].hasConductedDocumentsOrQuantities,
	      events: {
	        onDisabled: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _parentPage$1)[_parentPage$1].onInventoryManagementModeChanged({
	            isEnabled: false,
	            mode: catalog_storeEnableWizard.ModeList.MODE_B24
	          });
	        }
	      }
	    });
	  }
	  buildSection() {
	    const section = new ui_section.Section({
	      title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_SECTION_TITLE'),
	      titleIconClasses: 'ui-icon-set --settings',
	      isOpen: true
	    });
	    const modeSection = new ui_formElements_field.SettingsSection({
	      parent: babelHelpers.classPrivateFieldLooseBase(this, _parentPage$1)[_parentPage$1],
	      section
	    });
	    section.append(new ui_section.Row({
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getCurrentModeBlock)[_getCurrentModeBlock]()
	    }).render());
	    return modeSection;
	  }
	  openInventoryManagementSlider() {
	    let sliderUrl = '/bitrix/components/bitrix/catalog.store.enablewizard/slider.php';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _configCatalogSource)[_configCatalogSource]) {
	      sliderUrl += `?inventoryManagementSource=${babelHelpers.classPrivateFieldLooseBase(this, _configCatalogSource)[_configCatalogSource]}`;
	    }
	    new catalog_storeEnableWizard.EnableWizardOpener().open(sliderUrl, {
	      urlParams: {
	        analyticsContextSection: catalog_storeEnableWizard.AnalyticsContextList.SETTINGS
	      }
	    }).then(slider => {
	      if (!slider) {
	        return;
	      }
	      const isEnabled = slider.getData().get('isInventoryManagementEnabled');
	      const mode = slider.getData().get('inventoryManagementMode');
	      if (isEnabled !== undefined && isEnabled !== babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].isEnabled || mode !== undefined && mode !== babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].currentMode) {
	        var _document$querySelect;
	        babelHelpers.classPrivateFieldLooseBase(this, _parentPage$1)[_parentPage$1].onInventoryManagementModeChanged({
	          isEnabled,
	          mode
	        });
	        (_document$querySelect = document.querySelector('.catalog-settings-inventory-management-mode-wrapper')) == null ? void 0 : _document$querySelect.scrollIntoView();
	        if (babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].isEnabled && isEnabled && mode !== babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].currentMode) {
	          ui_notification.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGED')
	          });
	          babelHelpers.classPrivateFieldLooseBase(this, _parentPage$1)[_parentPage$1].updateDataAfterSave();
	        }
	      }
	    });
	  }
	}
	function _getCurrentModeBlock2() {
	  const isInventoryManagementEnabled = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].isEnabled;
	  const currentMode = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].currentMode;
	  const is1cRestricted = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].is1cRestricted;
	  let modeLogo = '';
	  if (currentMode === catalog_storeEnableWizard.ModeList.MODE_1C) {
	    modeLogo = main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="catalog-settings-inventory-management-mode-external-logo"></div>
			`));
	  } else {
	    modeLogo = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_B24_LOGO').replace('[color]', '<span class="catalog-settings-inventory-management-mode-b24-numbers">').replace('[/color]', '</span>');
	    modeLogo = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
				<span class="catalog-settings-inventory-management-mode-b24-name">${0}</span>
			`), modeLogo);
	  }
	  const changeModeButton = new ui_buttons.Button({
	    text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGE'),
	    color: ui_buttons.ButtonColor.LIGHT,
	    onclick: (button, event) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]('disable_clicked');
	      this.openInventoryManagementSlider();
	    }
	  });
	  const toggleButton = new ui_buttons.Button({
	    text: isInventoryManagementEnabled ? main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_DISABLE') : main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_ENABLE'),
	    color: isInventoryManagementEnabled ? ui_buttons.ButtonColor.LIGHT : ui_buttons.ButtonColor.PRIMARY,
	    onclick: (button, event) => {
	      if (isInventoryManagementEnabled) {
	        babelHelpers.classPrivateFieldLooseBase(this, _sendEvent)[_sendEvent]('change_mode_clicked');
	        babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementDisabler)[_inventoryManagementDisabler].open();
	      } else {
	        this.openInventoryManagementSlider();
	      }
	    },
	    round: !isInventoryManagementEnabled
	  });
	  const showChangeModeButton = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].availableModes.includes(catalog_storeEnableWizard.ModeList.MODE_1C) && isInventoryManagementEnabled;
	  let descriptionContent = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_B24_MODE_DESCRIPTION');
	  let descriptionClass = 'catalog-settings-inventory-management-mode-description';
	  if (currentMode === catalog_storeEnableWizard.ModeList.MODE_1C) {
	    const onecStatusUrl = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].onecStatusUrl;
	    descriptionContent = new ModeStatus({
	      currentMode,
	      isInventoryManagementEnabled,
	      onecStatusUrl,
	      is1cRestricted
	    }).initialize();
	    descriptionClass = 'catalog-settings-inventory-management-mode-status';
	  }
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div>
				<div class="catalog-settings-inventory-management-mode-wrapper">
					<div class="catalog-settings-inventory-management-mode-inner">
						<div class="catalog-settings-inventory-management-mode-selected ${0}">
							<div class="catalog-settings-inventory-management-mode-name">${0}</div>
							<div class="${0}">
								${0}
							</div>
						</div>
						<div class="catalog-settings-inventory-management-mode-buttons">
							${0}
							${0}
						</div>
					</div>
				</div>
				<div>
					<p class="catalog-settings-inventory-management-mode-warning">
						${0}
					</p>
					${0}
				</div>
			</div>
		`), isInventoryManagementEnabled ? '' : '--disabled', modeLogo, descriptionClass, descriptionContent, showChangeModeButton ? changeModeButton.render() : '', toggleButton.render(), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGE_WARNING').replace('[break]', '<br/>'), babelHelpers.classPrivateFieldLooseBase(this, _getHelpLink)[_getHelpLink]());
	}
	function _sendEvent2(event) {
	  main_core.Runtime.loadExtension('ui.analytics').then(exports => {
	    const {
	      sendData
	    } = exports;
	    sendData({
	      tool: 'inventory',
	      category: 'settings',
	      c_section: 'settings',
	      p1: `mode_${babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].currentMode}`,
	      event
	    });
	  });
	}
	function _getHelpLink2() {
	  const result = main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
			<a class="catalog-settings-inventory-management-mode-help ui-section__link">
				${0}
			</a>
		`), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_HELP'));
	  main_core.Event.bind(result, 'click', () => {
	    if (top.BX && top.BX.Helper) {
	      const helpCode = babelHelpers.classPrivateFieldLooseBase(this, _inventoryManagementParams)[_inventoryManagementParams].availableModes.length > 1 ? '20233748' : '15992592';
	      top.BX.Helper.show(`redirect=detail&code=${helpCode}`);
	    }
	  });
	  return result;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3;
	var _parentPage$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parentPage");
	var _values = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("values");
	var _showQuantityTracePopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showQuantityTracePopup");
	var _showNewCardPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNewCardPopup");
	var _createWarningProductCardPopupForBitrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWarningProductCardPopupForBitrix24");
	var _createWarningProductCardPopupForBUS = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWarningProductCardPopupForBUS");
	var _createWarningProductCardPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWarningProductCardPopup");
	class Products {
	  constructor(params) {
	    Object.defineProperty(this, _createWarningProductCardPopup, {
	      value: _createWarningProductCardPopup2
	    });
	    Object.defineProperty(this, _createWarningProductCardPopupForBUS, {
	      value: _createWarningProductCardPopupForBUS2
	    });
	    Object.defineProperty(this, _createWarningProductCardPopupForBitrix, {
	      value: _createWarningProductCardPopupForBitrix2
	    });
	    Object.defineProperty(this, _showNewCardPopup, {
	      value: _showNewCardPopup2
	    });
	    Object.defineProperty(this, _showQuantityTracePopup, {
	      value: _showQuantityTracePopup2
	    });
	    Object.defineProperty(this, _parentPage$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _values, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _parentPage$2)[_parentPage$2] = params.parentPage;
	    babelHelpers.classPrivateFieldLooseBase(this, _values)[_values] = params.values;
	  }
	  buildSection() {
	    const productsSection = new ui_formElements_field.SettingsSection({
	      parent: babelHelpers.classPrivateFieldLooseBase(this, _parentPage$2)[_parentPage$2],
	      section: {
	        title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCTS_SECTION_TITLE'),
	        titleIconClasses: 'ui-icon-set --cubes-3',
	        isOpen: true
	      }
	    });
	    new ui_formElements_field.SettingsRow({
	      parent: productsSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: new ui_formElements_view.Checker({
	          inputName: 'defaultSubscribe',
	          title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_SUBSCRIBE'),
	          checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].defaultSubscribe === 'Y'
	        })
	      })
	    });
	    const isInventoryManagementEnabled = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].isEnabledInventoryManagement;
	    const isEmptyCostPriceCalculationMethod = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].costPriceCalculationMethod.current === '';
	    const isCanBuyZeroInDocsVisible = isInventoryManagementEnabled && isEmptyCostPriceCalculationMethod;
	    if (isCanBuyZeroInDocsVisible) {
	      new ui_formElements_field.SettingsRow({
	        parent: productsSection,
	        child: new ui_formElements_field.SettingsField({
	          fieldView: new ui_formElements_view.Checker({
	            inputName: 'checkRightsOnDecreaseStoreAmount',
	            title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_IN_DOCS'),
	            checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].checkRightsOnDecreaseStoreAmount === 'Y',
	            hintOn: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_IN_DOCS_HINT').replace('[link]', '<a class="ui-section__link" onclick="top.BX.Helper.show(\'redirect=detail&code=15706692&anchor=products\')">').replace('[/link]', '</a>')
	          })
	        })
	      });
	    }
	    new ui_formElements_field.SettingsRow({
	      parent: productsSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: new ui_formElements_view.Checker({
	          inputName: 'defaultProductVatIncluded',
	          title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_SET_VAT_IN_PRICE_FOR_NEW_PRODUCTS'),
	          checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].defaultProductVatIncluded === 'Y'
	        })
	      })
	    });
	    const isDefaultCanBuyZeroVisible = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].isReservationUsed && babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].hasAccessToChangeCanBuyZero;
	    if (isDefaultCanBuyZeroVisible) {
	      new ui_formElements_field.SettingsRow({
	        parent: productsSection,
	        child: new ui_formElements_field.SettingsField({
	          fieldView: new ui_formElements_view.Checker({
	            inputName: 'defaultCanBuyZero',
	            title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO'),
	            checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].defaultCanBuyZero === 'Y',
	            hintOn: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_HINT')
	          })
	        })
	      });
	    }
	    const initDefaultQuantityTrace = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].defaultQuantityTrace;
	    const isDefaultQuantityTraceVisible = initDefaultQuantityTrace === 'Y' && !isInventoryManagementEnabled;
	    if (isDefaultQuantityTraceVisible) {
	      const defaultQuantityTraceChecker = new ui_formElements_view.Checker({
	        inputName: 'defaultQuantityTrace',
	        title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_QUANTITY_TRACE'),
	        checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].defaultQuantityTrace === 'Y'
	      });
	      new ui_formElements_field.SettingsRow({
	        parent: productsSection,
	        child: new ui_formElements_field.SettingsField({
	          fieldView: defaultQuantityTraceChecker
	        })
	      });
	      main_core_events.EventEmitter.subscribe(defaultQuantityTraceChecker.switcher, 'toggled', () => {
	        if (defaultQuantityTraceChecker.isChecked()) {
	          return;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _showQuantityTracePopup)[_showQuantityTracePopup]();
	      });
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].canEnableProductCardSlider) {
	      const canEnableProductCardSliderChecker = new ui_formElements_view.Checker({
	        inputName: 'productCardSliderEnabled',
	        title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD'),
	        checked: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].productCardSliderEnabled === 'Y'
	      });
	      new ui_formElements_field.SettingsRow({
	        parent: productsSection,
	        child: new ui_formElements_field.SettingsField({
	          fieldView: canEnableProductCardSliderChecker
	        })
	      });
	      main_core_events.EventEmitter.subscribe(canEnableProductCardSliderChecker.switcher, 'toggled', () => {
	        if (!canEnableProductCardSliderChecker.isChecked()) {
	          return;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _showNewCardPopup)[_showNewCardPopup](canEnableProductCardSliderChecker);
	      });
	    }
	    Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].vats.hints).forEach(hint => {
	      babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].vats.hints[hint] = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].vats.hints[hint].replace('#MORE_DETAILS#', `
				<a class="ui-section__link"
					onclick="top.BX.Helper.show('redirect=detail&code=15706692&anchor=products')">${main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_HELP')}</a>
			`);
	    });
	    const vatSelector = new ui_formElements_view.Selector({
	      label: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_VAT'),
	      name: 'defaultProductVatId',
	      items: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].vats.items,
	      hints: babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].vats.hints
	    });
	    new ui_formElements_field.SettingsRow({
	      parent: productsSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: vatSelector
	      })
	    });
	    return productsSection;
	  }
	  updateValues(newValues) {
	    Object.assign(babelHelpers.classPrivateFieldLooseBase(this, _values)[_values], newValues);
	  }
	}
	function _showQuantityTracePopup2() {
	  const warnPopup = new main_popup.Popup(null, null, {
	    events: {
	      onPopupClose: () => warnPopup.destroy()
	    },
	    content: main_core.Tag.render(_t$3 || (_t$3 = _$3`
				<div class="catalog-settings-popup-content">
					<h3>
						${0}
					</h3>
					<div class="catalog-settings-popup-text">
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_TURN_OFF_QUANTITY_TRACE_TITLE'), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_TURN_OFF_QUANTITY_TRACE_TEXT')),
	    maxWidth: 500,
	    overlay: true,
	    buttons: [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_CLOSE'),
	      color: ui_buttons.Button.Color.PRIMARY,
	      onclick: () => warnPopup.close()
	    })]
	  });
	  warnPopup.show();
	}
	function _showNewCardPopup2(checker) {
	  const askPopup = babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].isBitrix24 === 'Y' ? babelHelpers.classPrivateFieldLooseBase(this, _createWarningProductCardPopupForBitrix)[_createWarningProductCardPopupForBitrix](checker) : babelHelpers.classPrivateFieldLooseBase(this, _createWarningProductCardPopupForBUS)[_createWarningProductCardPopupForBUS](checker);
	  askPopup.show();
	}
	function _createWarningProductCardPopupForBitrix2(checker) {
	  const askPopup = babelHelpers.classPrivateFieldLooseBase(this, _createWarningProductCardPopup)[_createWarningProductCardPopup](main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_TEXT'), [new ui_buttons.Button({
	    text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_DISAGREE'),
	    color: ui_buttons.Button.Color.PRIMARY,
	    onclick: () => {
	      checker.switcher.toggle();
	      askPopup.close();
	    }
	  }), new ui_buttons.Button({
	    text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_AGREE'),
	    onclick: () => askPopup.close()
	  })], {
	    onPopupShow: () => {
	      const helpdeskLink = document.getElementById('catalog-settings-new-productcard-popup-helpdesk');
	      if (helpdeskLink) {
	        main_core.Event.bind(helpdeskLink, 'click', () => top.BX.Helper.show('redirect=detail&code=11657084'));
	      }
	    }
	  });
	  return askPopup;
	}
	function _createWarningProductCardPopupForBUS2(checker) {
	  const askPopup = babelHelpers.classPrivateFieldLooseBase(this, _createWarningProductCardPopup)[_createWarningProductCardPopup](main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_BUS_TEXT').replace('#HELP_LINK#', babelHelpers.classPrivateFieldLooseBase(this, _values)[_values].busProductCardHelpLink), [new ui_buttons.Button({
	    text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_AGREE'),
	    color: ui_buttons.Button.Color.SUCCESS,
	    onclick: () => askPopup.close()
	  }), new ui_buttons.Button({
	    text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_BUS_DISAGREE'),
	    color: ui_buttons.Button.Color.LINK,
	    onclick: () => {
	      checker.switcher.toggle();
	      askPopup.close();
	    }
	  })]);
	  return askPopup;
	}
	function _createWarningProductCardPopup2(contentText, buttons, events = {}) {
	  const popupParams = {
	    events: {
	      onPopupClose: () => askPopup.destroy(),
	      ...events
	    },
	    content: main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
				<div class="catalog-settings-new-productcard-popup-content">
					${0}
				</div>
			`), contentText),
	    className: 'catalog-settings-new-productcard-popup',
	    titleBar: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_TITLE'),
	    maxWidth: 800,
	    overlay: true,
	    buttons
	  };
	  const askPopup = new main_popup.Popup(null, null, popupParams);
	  return askPopup;
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$2;
	var _mode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mode");
	var _period = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("period");
	var _getModeSelectorClasses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getModeSelectorClasses");
	var _buildModeSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildModeSelector");
	var _getPeriodClasses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPeriodClasses");
	var _buildPeriodInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildPeriodInput");
	class ReservationMode extends ui_formElements_view.BaseField {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _buildPeriodInput, {
	      value: _buildPeriodInput2
	    });
	    Object.defineProperty(this, _getPeriodClasses, {
	      value: _getPeriodClasses2
	    });
	    Object.defineProperty(this, _buildModeSelector, {
	      value: _buildModeSelector2
	    });
	    Object.defineProperty(this, _getModeSelectorClasses, {
	      value: _getModeSelectorClasses2
	    });
	    Object.defineProperty(this, _mode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _period, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] = params.mode;
	    babelHelpers.classPrivateFieldLooseBase(this, _period)[_period] = params.period;
	  }
	  prefixId() {
	    return 'reservation_';
	  }
	  renderContentField() {
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div id="${0}" class="ui-section__field-selector --field-separator">
				<div class="ui-section__field-container">
					<div class="ui-section__field-inline-box">
						<label class="ui-section__field-label" for="${0}">${0}</label> 
						<div class="ui-section__field-inline-label-separator"></div>
						<label class="ui-section__field-label" for="${0}">${0}</label>
					</div>
					<div class="ui-section__field-inline-box">
						<div class="ui-section__field">
							<div class="${0}">
								<div class="ui-ctl-after ui-ctl-icon-angle"></div>
								${0}
							</div>
						</div>
						<div class="ui-section__field-inline-separator"></div>
						<div class="${0}">
							${0}
						</div>
					</div>
				</div>
			</div>
		`), this.getId(), babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].fieldName, babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].setting.name, babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].fieldName, babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].setting.name, babelHelpers.classPrivateFieldLooseBase(this, _getModeSelectorClasses)[_getModeSelectorClasses](), babelHelpers.classPrivateFieldLooseBase(this, _buildModeSelector)[_buildModeSelector](), babelHelpers.classPrivateFieldLooseBase(this, _getPeriodClasses)[_getPeriodClasses](), babelHelpers.classPrivateFieldLooseBase(this, _buildPeriodInput)[_buildPeriodInput]());
	  }
	}
	function _getModeSelectorClasses2() {
	  let result = 'ui-ctl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].setting.disabled) {
	    result += ' ui-ctl-disabled';
	  }
	  return result;
	}
	function _buildModeSelector2() {
	  const options = [];
	  for (const {
	    code,
	    name
	  } of babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].setting.values) {
	    let selectedAttr = '';
	    if (code === babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].value) {
	      selectedAttr = 'selected';
	    }
	    options.push(main_core.Tag.render(_t2$4 || (_t2$4 = _$4`<option ${0} value="${0}">${0}</option>`), selectedAttr, code, name));
	  }
	  const selector = main_core.Dom.create('select', {
	    attrs: {
	      class: 'ui-ctl-element',
	      disabled: babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].setting.disabled
	    },
	    children: options
	  });
	  selector.name = babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].fieldName;
	  return selector;
	}
	function _getPeriodClasses2() {
	  let result = 'ui-section__hint';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].setting.disabled) {
	    result += ' ui-ctl-disabled';
	  }
	  return result;
	}
	function _buildPeriodInput2() {
	  const periodInput = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
			<input
				value="${0}"
				name="${0}"
				type="text"
				class="ui-ctl-element"
			>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].value), babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].fieldName);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _period)[_period].setting.disabled) {
	    periodInput.disabled = true;
	  }
	  return periodInput;
	}

	var _reservationEntities = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reservationEntities");
	var _parentPage$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parentPage");
	class ReservationSection {
	  constructor(params) {
	    Object.defineProperty(this, _reservationEntities, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _parentPage$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _reservationEntities)[_reservationEntities] = params.reservationEntities;
	    babelHelpers.classPrivateFieldLooseBase(this, _parentPage$3)[_parentPage$3] = params.parentPage;
	  }

	  // todo: implement actual dynamic settings from the scheme parameter when reservation in other entities is implemented
	  buildSection() {
	    var _babelHelpers$classPr;
	    const section = new ui_section.Section({
	      title: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_RESERVATION_SECTION_TITLE'),
	      titleIconClasses: 'ui-icon-set --proposal-settings',
	      isOpen: true
	    });
	    const settingsSection = new ui_formElements_field.SettingsSection({
	      parent: babelHelpers.classPrivateFieldLooseBase(this, _parentPage$3)[_parentPage$3],
	      section
	    });
	    const dealSettings = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _reservationEntities)[_reservationEntities][0]) == null ? void 0 : _babelHelpers$classPr.settings;
	    if (!dealSettings) {
	      return settingsSection;
	    }
	    section.append(new ui_section.Row({
	      content: new ui_alerts.Alert({
	        text: `
						${main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_RESERVATION_SECTION_DESCRIPTION')}
						<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=15706692&anchor=reservation')">
							${main_core.Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
						</a>
					`,
	        inline: true,
	        size: ui_alerts.AlertSize.SMALL,
	        color: ui_alerts.AlertColor.PRIMARY
	      }).getContainer()
	    }).render());
	    const modeFieldName = ReservationSection.MODE_FIELD_NAME;
	    const modeSetting = dealSettings.scheme.find(schemeElement => {
	      return schemeElement.code === 'mode';
	    });
	    const modeValue = dealSettings.values.mode;
	    const periodFieldName = ReservationSection.PERIOD_FIELD_NAME;
	    const periodSetting = dealSettings.scheme.find(schemeElement => {
	      return schemeElement.code === 'period';
	    });
	    const periodValue = dealSettings.values.period;
	    new ui_formElements_field.SettingsRow({
	      row: {
	        separator: 'bottom',
	        className: '--block'
	      },
	      parent: settingsSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: new ReservationMode({
	          mode: {
	            fieldName: modeFieldName,
	            setting: modeSetting,
	            value: modeValue
	          },
	          period: {
	            fieldName: periodFieldName,
	            setting: periodSetting,
	            value: periodValue
	          }
	        })
	      })
	    });
	    const autoWriteOffSetting = dealSettings.scheme.find(schemeElement => {
	      return schemeElement.code === 'autoWriteOffOnFinalize';
	    });
	    const autoWriteOffValue = dealSettings.values.autoWriteOffOnFinalize;
	    const checker = new ui_formElements_view.Checker({
	      inputName: ReservationSection.AUTO_WRITE_OFF_FIELD_NAME,
	      title: autoWriteOffSetting.name,
	      checked: autoWriteOffValue,
	      hintOn: autoWriteOffSetting.description,
	      isFieldDisabled: autoWriteOffSetting.disabled,
	      hideSeparator: true
	    });
	    new ui_formElements_field.SettingsRow({
	      parent: settingsSection,
	      child: new ui_formElements_field.SettingsField({
	        fieldView: checker
	      })
	    });
	    return settingsSection;
	  }
	}
	ReservationSection.MODE_FIELD_NAME = 'reservationSettings[deal][mode]';
	ReservationSection.PERIOD_FIELD_NAME = 'reservationSettings[deal][period]';
	ReservationSection.AUTO_WRITE_OFF_FIELD_NAME = 'reservationSettings[deal][autoWriteOffOnFinalize]';

	let _$5 = t => t,
	  _t$5,
	  _t2$5;
	var _productUpdaterPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("productUpdaterPopup");
	var _initialData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialData");
	var _slider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slider");
	var _getDataForSaving = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDataForSaving");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _resetSaveButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resetSaveButton");
	var _onSaveSuccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onSaveSuccess");
	var _saveProductSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveProductSettings");
	var _didProductSettingsChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("didProductSettingsChange");
	var _needProgressBarOnProductsUpdating = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needProgressBarOnProductsUpdating");
	var _buildReservationSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildReservationSection");
	var _buildCostPriceCalculationSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildCostPriceCalculationSection");
	var _buildProductsSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildProductsSection");
	var _buildModeSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildModeSection");
	var _showNegativeBalancePopupIfNeeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNegativeBalancePopupIfNeeded");
	var _isReservationUsed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isReservationUsed");
	var _isStoreBatchUsed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isStoreBatchUsed");
	var _convertFormDataToObjectData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("convertFormDataToObjectData");
	class CatalogPage extends ui_formElements_field.BaseSettingsPage {
	  constructor() {
	    super();
	    Object.defineProperty(this, _convertFormDataToObjectData, {
	      value: _convertFormDataToObjectData2
	    });
	    Object.defineProperty(this, _isStoreBatchUsed, {
	      value: _isStoreBatchUsed2
	    });
	    Object.defineProperty(this, _isReservationUsed, {
	      value: _isReservationUsed2
	    });
	    Object.defineProperty(this, _showNegativeBalancePopupIfNeeded, {
	      value: _showNegativeBalancePopupIfNeeded2
	    });
	    Object.defineProperty(this, _buildModeSection, {
	      value: _buildModeSection2
	    });
	    Object.defineProperty(this, _buildProductsSection, {
	      value: _buildProductsSection2
	    });
	    Object.defineProperty(this, _buildCostPriceCalculationSection, {
	      value: _buildCostPriceCalculationSection2
	    });
	    Object.defineProperty(this, _buildReservationSection, {
	      value: _buildReservationSection2
	    });
	    Object.defineProperty(this, _needProgressBarOnProductsUpdating, {
	      value: _needProgressBarOnProductsUpdating2
	    });
	    Object.defineProperty(this, _didProductSettingsChange, {
	      value: _didProductSettingsChange2
	    });
	    Object.defineProperty(this, _saveProductSettings, {
	      value: _saveProductSettings2
	    });
	    Object.defineProperty(this, _onSaveSuccess, {
	      value: _onSaveSuccess2
	    });
	    Object.defineProperty(this, _resetSaveButton, {
	      value: _resetSaveButton2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _getDataForSaving, {
	      value: _getDataForSaving2
	    });
	    Object.defineProperty(this, _productUpdaterPopup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _initialData, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _slider, {
	      writable: true,
	      value: null
	    });
	    this.titlePage = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_TITLE');
	    this.descriptionPage = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_DESCRIPTION');
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'button-click', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _save)[_save]();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider] = BX.SidePanel.Instance.getTopSlider();
	  }

	  // temporary methods, to be removed after the page is integrated with the intranet settings
	  static init(settings) {
	    const page = new CatalogPage();
	    page.setData(settings);
	    babelHelpers.classPrivateFieldLooseBase(page, _initialData)[_initialData] = settings;
	    const permission = Boolean(settings.hasAccessToCatalogSettings) || Boolean(settings.hasAccessToReservationSettings);
	    page.setPermission({
	      canRead: () => permission,
	      canEdit: () => permission
	    });
	    return page;
	  }
	  onChange() {
	    BX.UI.ButtonPanel.show();
	  }
	  getType() {
	    return 'catalog';
	  }
	  appendSections(contentNode) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isReservationUsed)[_isReservationUsed]() && this.getValue('hasAccessToReservationSettings')) {
	      const reservationSection = babelHelpers.classPrivateFieldLooseBase(this, _buildReservationSection)[_buildReservationSection]();
	      reservationSection.renderTo(contentNode);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isStoreBatchUsed)[_isStoreBatchUsed]() && this.getValue('hasAccessToCatalogSettings')) {
	      const costPriceCalculationSection = babelHelpers.classPrivateFieldLooseBase(this, _buildCostPriceCalculationSection)[_buildCostPriceCalculationSection]();
	      costPriceCalculationSection.renderTo(contentNode);
	    }
	    if (this.getValue('hasAccessToCatalogSettings')) {
	      const productsSection = babelHelpers.classPrivateFieldLooseBase(this, _buildProductsSection)[_buildProductsSection]();
	      productsSection.renderTo(contentNode);
	      const modeSection = babelHelpers.classPrivateFieldLooseBase(this, _buildModeSection)[_buildModeSection]();
	      modeSection.renderTo(contentNode);
	    }
	  }
	  onInventoryManagementModeChanged({
	    isEnabled,
	    mode
	  }) {
	    var _this$getValue;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider].getData().set('isInventoryManagementChanged', true);
	      if (mode) {
	        babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider].getData().set('inventoryManagementMode', mode);
	        if (mode === catalog_storeEnableWizard.ModeList.MODE_1C) {
	          babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData].is1cRestricted = false;
	        }
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData].isEnabledInventoryManagement = isEnabled;
	    if (mode && (_this$getValue = this.getValue('storeControlAvailableModes')) != null && _this$getValue.includes(mode)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData].storeControlMode = mode;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData].defaultQuantityTrace = isEnabled ? 'Y' : 'N';
	    this.setData(babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData]);
	  }
	  // reads the data from the form element and updates the page object's #data
	  updateDataAfterSave() {
	    this.setData(babelHelpers.classPrivateFieldLooseBase(this, _convertFormDataToObjectData)[_convertFormDataToObjectData]());
	  }
	}
	function _getDataForSaving2() {
	  return BX.ajax.prepareForm(this.getFormNode()).data;
	}
	function _save2() {
	  const isNegativeBalancePopupShown = babelHelpers.classPrivateFieldLooseBase(this, _showNegativeBalancePopupIfNeeded)[_showNegativeBalancePopupIfNeeded]();
	  if (isNegativeBalancePopupShown) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _saveProductSettings)[_saveProductSettings]().then(() => {
	    const data = babelHelpers.classPrivateFieldLooseBase(this, _getDataForSaving)[_getDataForSaving]();
	    return main_core.ajax.runComponentAction('bitrix:catalog.config.settings', 'save', {
	      mode: 'class',
	      data: {
	        data
	      }
	    });
	  }).then(babelHelpers.classPrivateFieldLooseBase(this, _onSaveSuccess)[_onSaveSuccess].bind(this));
	}
	function _resetSaveButton2() {
	  const saveButton = document.getElementById('ui-button-panel-save');
	  main_core.Dom.removeClass(saveButton, 'ui-btn-wait');
	}
	function _onSaveSuccess2() {
	  BX.UI.ButtonPanel.hide();
	  babelHelpers.classPrivateFieldLooseBase(this, _resetSaveButton)[_resetSaveButton]();
	  this.updateDataAfterSave();
	  BX.SidePanel.Instance.postMessage(window, 'BX.Crm.Config.Catalog:onAfterSaveSettings');
	}
	function _saveProductSettings2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _didProductSettingsChange)[_didProductSettingsChange]()) {
	    return Promise.resolve();
	  }
	  const newData = babelHelpers.classPrivateFieldLooseBase(this, _getDataForSaving)[_getDataForSaving]();
	  const productUpdaterOptions = {
	    settings: {
	      default_quantity_trace: newData.defaultQuantityTrace,
	      default_can_buy_zero: newData.defaultCanBuyZero,
	      default_subscribe: newData.defaultSubscribe
	    }
	  };
	  return new Promise(resolve => {
	    productUpdaterOptions.onComplete = () => {
	      resolve();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _needProgressBarOnProductsUpdating)[_needProgressBarOnProductsUpdating]()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _productUpdaterPopup)[_productUpdaterPopup].destroy();
	      }
	    };
	    const productUpdater = new ProductUpdater(productUpdaterOptions).render();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _needProgressBarOnProductsUpdating)[_needProgressBarOnProductsUpdating]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _productUpdaterPopup)[_productUpdaterPopup] = new main_popup.Popup({
	        content: productUpdater,
	        width: 310,
	        overlay: true,
	        padding: 17,
	        animation: 'fading-slide',
	        angle: false
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _productUpdaterPopup)[_productUpdaterPopup].show();
	    }
	  });
	}
	function _didProductSettingsChange2() {
	  const newData = babelHelpers.classPrivateFieldLooseBase(this, _getDataForSaving)[_getDataForSaving]();
	  const affectedSettings = ['defaultQuantityTrace', 'defaultCanBuyZero', 'defaultSubscribe', 'checkRightsOnDecreaseStoreAmount'];
	  const productSettingsResult = affectedSettings.find(code => {
	    return newData[code] !== undefined && newData[code] !== this.getValue(code);
	  });
	  const costPriceCalculationMethodResult = newData.costPriceCalculationMethod !== undefined && newData.costPriceCalculationMethod !== this.getValue('costPriceCalculationMethod').current;
	  return Boolean(productSettingsResult) || costPriceCalculationMethodResult;
	}
	function _needProgressBarOnProductsUpdating2() {
	  return this.getValue('productsCount') > 500;
	}
	function _buildReservationSection2() {
	  const storeControlMode = this.getValue('storeControlMode');
	  const reservationEntities = this.getValue('reservationEntities');
	  for (const reservationEntity of reservationEntities) {
	    for (const schemeItem of reservationEntity.settings.scheme) {
	      if (['mode', 'period'].includes(schemeItem.code)) {
	        schemeItem.disabled = storeControlMode === catalog_storeEnableWizard.ModeList.MODE_1C;
	      }
	    }
	    if (storeControlMode === catalog_storeEnableWizard.ModeList.MODE_1C) {
	      reservationEntity.settings.values.mode = 'onAddToDocument';
	    }
	  }
	  const reservationSection = new ReservationSection({
	    parentPage: this,
	    reservationEntities
	  });
	  return reservationSection.buildSection();
	}
	function _buildCostPriceCalculationSection2() {
	  const costPriceCalculationSection = new CostPriceCalculation({
	    parentPage: this,
	    costPriceCalculationParams: this.getValue('costPriceCalculationMethod')
	  });
	  return costPriceCalculationSection.buildSection();
	}
	function _buildProductsSection2() {
	  const values = {};
	  ['defaultSubscribe', 'isEnabledInventoryManagement', 'costPriceCalculationMethod', 'checkRightsOnDecreaseStoreAmount', 'defaultProductVatIncluded', 'defaultCanBuyZero', 'defaultQuantityTrace', 'canEnableProductCardSlider', 'isBitrix24', 'productCardSliderEnabled', 'showNegativeStoreAmountPopup', 'storeBalancePopupLink', 'hasAccessToChangeCanBuyZero', 'busProductCardHelpLink', 'vats'].forEach(code => {
	    values[code] = this.getValue(code);
	  });
	  values.isReservationUsed = babelHelpers.classPrivateFieldLooseBase(this, _isReservationUsed)[_isReservationUsed]();
	  const productsSection = new Products({
	    parentPage: this,
	    values
	  });
	  return productsSection.buildSection();
	}
	function _buildModeSection2() {
	  const modeSection = new Mode({
	    parentPage: this,
	    inventoryManagementParams: {
	      isEnabled: this.getValue('isEnabledInventoryManagement'),
	      currentMode: this.getValue('storeControlMode'),
	      availableModes: this.getValue('storeControlAvailableModes'),
	      onecStatusUrl: this.getValue('onecStatusUrl'),
	      is1cRestricted: this.getValue('is1cRestricted'),
	      hasConductedDocumentsOrQuantities: this.getValue('hasConductedDocumentsOrQuantities')
	    },
	    configCatalogSource: this.getValue('configCatalogSource')
	  });
	  return modeSection.buildSection();
	}
	function _showNegativeBalancePopupIfNeeded2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getDataForSaving)[_getDataForSaving]().costPriceCalculationMethod || !this.getValue('showNegativeStoreAmountPopup')) {
	    return false;
	  }
	  const text = main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_NEGATIVE_STORE_BALANCE_POPUP_TEXT', {
	    '#STORE_BALANCE_LIST_LINK#': '<help-link></help-link>'
	  });
	  const content = main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="catalog-settings-popup-content">
				<div class="catalog-settings-popup-text">
					${0}
				</div>
			</div>
		`), text);
	  if (!main_core.Type.isUndefined(top.BX.SidePanel.Instance) && main_core.Type.isStringFilled(this.getValue('storeBalancePopupLink'))) {
	    const balanceInfoLink = main_core.Tag.render(_t2$5 || (_t2$5 = _$5`
				<a href="#" class="ui-form-link">
					${0}
				</a>
			`), main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_NEGATIVE_STORE_BALANCE_POPUP_LINK'));
	    main_core.Event.bind(balanceInfoLink, 'click', () => {
	      top.BX.SidePanel.Instance.open(String(this.getValue('storeBalancePopupLink')), {
	        requestMethod: 'post',
	        cacheable: false
	      });
	    });
	    main_core.Dom.replace(content.querySelector('help-link'), balanceInfoLink);
	  }
	  const popup = new main_popup.Popup({
	    id: 'catalog_settings_document_negative_balance_popup',
	    content,
	    overlay: true,
	    buttons: [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('CAT_CONFIG_SETTINGS_RETURN'),
	      color: ui_buttons.ButtonColor.DANGER,
	      onclick: (button, event) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _resetSaveButton)[_resetSaveButton]();
	        popup.destroy();
	      }
	    })]
	  });
	  popup.show();
	  return true;
	}
	function _isReservationUsed2() {
	  return this.getValue('isEnabledInventoryManagement') || this.getValue('defaultQuantityTrace') === 'Y';
	}
	function _isStoreBatchUsed2() {
	  return this.getValue('isStoreBatchUsed') || this.getValue('hasAccessToCatalogSettings');
	}
	function _convertFormDataToObjectData2() {
	  const formData = babelHelpers.classPrivateFieldLooseBase(this, _getDataForSaving)[_getDataForSaving]();
	  const objectData = babelHelpers.classPrivateFieldLooseBase(this, _initialData)[_initialData];

	  // reservation
	  if (formData.reservationSettings) {
	    formData.reservationSettings.deal.autoWriteOffOnFinalize = formData.reservationSettings.deal.autoWriteOffOnFinalize === 'Y';
	    Object.assign(objectData.reservationEntities[0].settings.values, formData.reservationSettings.deal);
	  }

	  // cost price calculation
	  if (formData.costPriceCalculationMethod) {
	    objectData.costPriceCalculationMethod.items.forEach(item => {
	      item.selected = item.value === formData.costPriceCalculationMethod;
	    });
	  }

	  // product settings
	  if (formData.defaultProductVatId) {
	    objectData.vats.items.forEach(item => {
	      item.selected = Number(item.value) === Number(formData.defaultProductVatId);
	    });
	  }
	  const options = ['defaultSubscribe', 'checkRightsOnDecreaseStoreAmount', 'defaultProductVatIncluded', 'defaultCanBuyZero', 'defaultQuantityTrace', 'productCardSliderEnabled'];
	  options.forEach(option => {
	    if (formData[option]) {
	      objectData[option] = formData[option];
	    }
	  });
	  return objectData;
	}

	class Slider {
	  static open(source = null, options = {}) {
	    Slider.closePopup();
	    let url = Slider.URL;
	    if (main_core.Type.isStringFilled(source)) {
	      url += `?configCatalogSource=${source}`;
	    }
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', event => {
	      const [data] = event.getData();
	      if (data.eventId === 'BX.Crm.Config.Catalog:onAfterSaveSettings') {
	        main_core_events.EventEmitter.emit(window, 'onCatalogSettingsSave');
	      }
	    });
	    if (!options.events) {
	      options.events = {};
	    }
	    if (!options.events.onClose) {
	      options.events.onClose = event => {
	        var _event$getSlider;
	        if ((_event$getSlider = event.getSlider()) != null && _event$getSlider.getData().get('isInventoryManagementChanged')) {
	          if (event.getSlider().getData().get('inventoryManagementMode') === catalog_storeEnableWizard.ModeList.MODE_1C) {
	            top.document.location = '/crm/';
	          } else {
	            document.location.reload();
	          }
	        }
	      };
	    }
	    return new Promise(resolve => {
	      BX.SidePanel.Instance.open(url, {
	        width: 1000,
	        allowChangeHistory: false,
	        cacheable: false,
	        ...options
	      });
	    });
	  }
	  static openRigthsSlider() {
	    Slider.closePopup();
	    return new Promise(resolve => {
	      BX.SidePanel.Instance.open(Slider.URL_RIGHTS, {});
	    });
	  }
	  static openSeoSlider(url) {
	    Slider.closePopup();
	    return new Promise(resolve => {
	      BX.SidePanel.Instance.open(url, {
	        width: 1000,
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    });
	  }
	  static closePopup() {
	    var _BX$PopupWindowManage;
	    (_BX$PopupWindowManage = BX.PopupWindowManager) == null ? void 0 : _BX$PopupWindowManage.getPopups().forEach(popup => {
	      popup.close();
	    });
	  }
	}
	Slider.URL = '/crm/configs/catalog/';
	Slider.URL_RIGHTS = '/shop/settings/permissions/';

	var _page = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("page");
	var _onEventChangeData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onEventChangeData");
	class CatalogSettings {
	  constructor(settings) {
	    Object.defineProperty(this, _onEventChangeData, {
	      value: _onEventChangeData2
	    });
	    Object.defineProperty(this, _page, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _page)[_page] = CatalogPage.init(settings);
	    babelHelpers.classPrivateFieldLooseBase(this, _page)[_page].subscribe('change', babelHelpers.classPrivateFieldLooseBase(this, _onEventChangeData)[_onEventChangeData].bind(this));
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _page)[_page].getPage();
	  }
	}
	function _onEventChangeData2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _page)[_page].onChange();
	}

	exports.CatalogSettings = CatalogSettings;
	exports.Slider = Slider;

}((this.BX.Catalog.Config = this.BX.Catalog.Config || {}),BX.UI,BX,BX.Catalog,BX.Catalog,BX.UI,BX.Main,BX.UI,BX.UI,BX.UI.FormElements,BX.UI.FormElements,BX.UI,BX,BX,BX.Catalog.Store,BX,BX.Event));
//# sourceMappingURL=settings.bundle.js.map
