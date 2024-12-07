/* eslint-disable */
this.BX = this.BX || {};
(function (exports,landing_backend,main_core) {
	'use strict';

	var _rootNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootNode");
	var _template = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	var _lang = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lang");
	var _appId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appId");
	var _appAllowedByTariff = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appAllowedByTariff");
	var _fetchable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchable");
	var _clickable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clickable");
	var _uniqueId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uniqueId");
	var _frame = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("frame");
	var _defaultData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultData");
	var _blockId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("blockId");
	var _getFrameContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFrameContent");
	var _getCoreConfigs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCoreConfigs");
	var _getAssetsConfigs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAssetsConfigs");
	var _parseExtensionConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseExtensionConfig");
	var _fetchData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchData");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _onMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMessage");
	class WidgetVue {
	  /**
	   * Unique string for every widget
	   * @type {string}
	   */

	  // #rootContent: ?string = null;

	  // #application: VueCreateAppResult;
	  // #contentComponent: Object;

	  // #widgetOptions: {};

	  constructor(options) {
	    Object.defineProperty(this, _onMessage, {
	      value: _onMessage2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _fetchData, {
	      value: _fetchData2
	    });
	    Object.defineProperty(this, _parseExtensionConfig, {
	      value: _parseExtensionConfig2
	    });
	    Object.defineProperty(this, _getAssetsConfigs, {
	      value: _getAssetsConfigs2
	    });
	    Object.defineProperty(this, _getCoreConfigs, {
	      value: _getCoreConfigs2
	    });
	    Object.defineProperty(this, _getFrameContent, {
	      value: _getFrameContent2
	    });
	    Object.defineProperty(this, _rootNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _template, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lang, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _appId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _appAllowedByTariff, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _fetchable, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _clickable, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _uniqueId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _frame, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _defaultData, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _blockId, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId] = 'widget' + main_core.Text.getRandom(8);
	    babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] = main_core.Type.isString(options.rootNode) ? document.querySelector(options.rootNode) : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _template)[_template] = main_core.Type.isString(options.template) ? options.template : '';

	    // this.#rootContent = this.#rootNode ? this.#rootNode.innerHTML : null;

	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData] = main_core.Type.isObject(options.data) ? options.data : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lang)[_lang] = options.lang || {};
	    babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] = options.blockId ? main_core.Text.toNumber(options.blockId) : 0;

	    // const isEditMode = Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
	    // this.#widgetOptions.clickable = !isEditMode;

	    babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] = options.appId ? main_core.Text.toNumber(options.appId) : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _appAllowedByTariff)[_appAllowedByTariff] = babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] && main_core.Type.isBoolean(options.appAllowedByTariff) ? options.appAllowedByTariff : true;
	    babelHelpers.classPrivateFieldLooseBase(this, _fetchable)[_fetchable] = main_core.Type.isBoolean(options.fetchable) ? options.fetchable : false;
	    const isEditMode = main_core.Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
	    babelHelpers.classPrivateFieldLooseBase(this, _clickable)[_clickable] = !isEditMode;
	  }

	  /**
	   * Create frame with widget content
	   * @returns {Promise|*}
	   */
	  mount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getFrameContent)[_getFrameContent]().then(srcDoc => {
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame] = document.createElement('iframe');
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].className = 'landing-widgetvue-iframe';
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].sandbox = 'allow-scripts';
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].srcdoc = srcDoc;
	      if (babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] > 0 && babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] && !WidgetVue.runningAppNodes.has(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode])) {
	        const blockWrapper = babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode].parentElement;
	        main_core.Dom.clean(blockWrapper);
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame], blockWrapper);
	        babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	      }
	    });
	  }
	}
	function _getFrameContent2() {
	  let content = '';
	  const engineParams = {
	    id: babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId],
	    origin: window.location.origin,
	    fetchable: babelHelpers.classPrivateFieldLooseBase(this, _fetchable)[_fetchable],
	    clickable: babelHelpers.classPrivateFieldLooseBase(this, _clickable)[_clickable]
	  };
	  return babelHelpers.classPrivateFieldLooseBase(this, _getCoreConfigs)[_getCoreConfigs]().then(core => {
	    content += babelHelpers.classPrivateFieldLooseBase(this, _parseExtensionConfig)[_parseExtensionConfig](core);
	    content += babelHelpers.classPrivateFieldLooseBase(this, _parseExtensionConfig)[_parseExtensionConfig]({
	      lang_additional: babelHelpers.classPrivateFieldLooseBase(this, _lang)[_lang]
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _getAssetsConfigs)[_getAssetsConfigs]();
	  }).then(assets => {
	    content += babelHelpers.classPrivateFieldLooseBase(this, _parseExtensionConfig)[_parseExtensionConfig](assets);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _appAllowedByTariff)[_appAllowedByTariff]) {
	      throw new Error(main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_PAYMENT'));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData];
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _fetchData)[_fetchData]();
	  }).then(data => {
	    engineParams.data = data;
	  }).catch(error => {
	    engineParams.error = error.message || 'error';
	  }).then(() => {
	    const appInit = `
					<script>
						BX.ready(function() {
							(new BX.Landing.WidgetVue.Engine(
								${JSON.stringify(engineParams)}
							)).render();
						});
					</script>
					
					<div id="${babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId]}">${babelHelpers.classPrivateFieldLooseBase(this, _template)[_template]}</div>
				`;
	    content += appInit;
	    return content;
	  });
	}
	function _getCoreConfigs2() {
	  const extCodes = ['main.core', 'ui.design-tokens'];
	  const tplCodes = ['bitrix24'];
	  return landing_backend.Backend.getInstance().action('Block::getAssetsConfig', {
	    extCodes,
	    tplCodes
	  });
	}
	function _getAssetsConfigs2() {
	  const extCodes = ['landing.widgetvue.engine'];
	  return landing_backend.Backend.getInstance().action('Block::getAssetsConfig', {
	    extCodes
	  });
	}
	function _parseExtensionConfig2(ext) {
	  const domain = `${document.location.protocol}//${document.location.host}`;
	  let html = '';
	  if (ext.lang_additional !== undefined) {
	    html += `<script>BX.message(${JSON.stringify(ext.lang_additional)})</script>`;
	  }
	  (ext.js || []).forEach(js => {
	    html += `<script src="${domain}${js}"></script>`;
	  });
	  (ext.css || []).forEach(css => {
	    html += `<link href="${domain}${css}" type="text/css" rel="stylesheet" />`;
	  });
	  return html;
	}
	function _fetchData2(params = {}) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _fetchable)[_fetchable]) {
	    console.info('Fetch data is impossible now (haven`t handler)');
	    return Promise.resolve({});
	  }
	  return landing_backend.Backend.getInstance().action('RepoWidget::fetchData', {
	    blockId: babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId],
	    params
	  }).then(jsonData => {
	    let data = {};
	    try {
	      data = JSON.parse(jsonData);
	      if (data.error) {
	        throw new Error(main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'), data.error);
	      }
	    } catch (error) {
	      throw new Error(main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'), error);
	    }
	    return data;
	  });
	}
	function _bindEvents2() {
	  main_core.Event.bind(window, 'message', babelHelpers.classPrivateFieldLooseBase(this, _onMessage)[_onMessage].bind(this));
	}
	function _onMessage2(event) {
	  if (event.data && event.data.name && event.data.params) {
	    if (event.data.name === 'fetchData') {
	      babelHelpers.classPrivateFieldLooseBase(this, _fetchData)[_fetchData](event.data.params).then(data => {
	        event.source.postMessage({
	          name: 'setData',
	          params: {
	            data
	          }
	        }, '*');
	      }).catch(error => {
	        event.source.postMessage({
	          name: 'setError',
	          params: {
	            error
	          }
	        }, '*');
	      });
	    }
	    if (event.data.name === 'setSize' && event.data.params.size !== undefined) {
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].height = parseInt(event.data.params.size);
	    }
	    if (event.data.name === 'openApplication' && babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] > 0) {
	      const params = main_core.Type.isObject(event.data.params) ? event.data.params : {};
	      BX.rest.AppLayout.openApplication(babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId], params);
	    }
	    if (event.data.name === 'openPath' && main_core.Type.isString(event.data.path)) {
	      // todo: change open function
	      const url = new URL(event.data.path, window.location.origin);
	      if (url.origin === window.location.origin) {
	        window.open(url.href, '_blank');
	      }
	    }
	  }
	}
	WidgetVue.runningAppNodes = new Set();

	exports.WidgetVue = WidgetVue;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX));
//# sourceMappingURL=widgetvue.bundle.js.map
