/* eslint-disable */
this.BX = this.BX || {};
(function (exports,landing_backend,main_core) {
	'use strict';

	var _enable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enable");
	class Logger {
	  constructor(enable = false) {
	    Object.defineProperty(this, _enable, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _enable)[_enable] = enable;
	  }
	  log(...message) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enable)[_enable]) {
	      console.log(...message);
	    }
	  }
	}

	var _rootNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootNode");
	var _template = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	var _style = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("style");
	var _lang = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lang");
	var _appId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appId");
	var _appAllowedByTariff = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appAllowedByTariff");
	var _fetchable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchable");
	var _clickable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clickable");
	var _uniqueId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uniqueId");
	var _frame = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("frame");
	var _logger = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logger");
	var _demoData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("demoData");
	var _useDemoData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useDemoData");
	var _blockId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("blockId");
	var _getFrameContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFrameContent");
	var _getCoreConfigs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCoreConfigs");
	var _getAssetsConfigs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAssetsConfigs");
	var _parseExtensionConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseExtensionConfig");
	var _fetchData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchData");
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _onMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMessage");
	class WidgetVue {
	  /**
	   * Unique string for every widget
	   * @type {string}
	   */

	  constructor(options) {
	    Object.defineProperty(this, _onMessage, {
	      value: _onMessage2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _message, {
	      value: _message2
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
	    Object.defineProperty(this, _style, {
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
	    Object.defineProperty(this, _logger, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _demoData, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _useDemoData, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _blockId, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId] = `widget_${main_core.Text.getRandom(8)}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger] = new Logger(options.debug || false);
	    babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] = main_core.Type.isString(options.rootNode) ? document.querySelector(options.rootNode) : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _template)[_template] = main_core.Type.isString(options.template) ? options.template : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _style)[_style] = main_core.Type.isString(options.style) ? options.style : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _demoData)[_demoData] = main_core.Type.isObject(options.demoData) ? options.demoData : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _useDemoData)[_useDemoData] = main_core.Type.isBoolean(options.useDemoData) ? options.useDemoData : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _lang)[_lang] = options.lang || {};
	    babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] = options.blockId ? main_core.Text.toNumber(options.blockId) : 0;
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
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].onload = () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('getSize', {}, babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].contentWindow);
	      };
	      if (babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] > 0 && babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] && !WidgetVue.runningAppNodes.has(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode])) {
	        const blockWrapper = babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode].parentElement;
	        main_core.Dom.clean(blockWrapper);
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame], blockWrapper);
	        WidgetVue.runningAppNodes.add(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]);
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]) {
	      content += `<link rel="stylesheet" href="${babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]}">`;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _getAssetsConfigs)[_getAssetsConfigs]();
	  }).then(assets => {
	    content += babelHelpers.classPrivateFieldLooseBase(this, _parseExtensionConfig)[_parseExtensionConfig](assets);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _appAllowedByTariff)[_appAllowedByTariff]) {
	      throw new Error(main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_PAYMENT'));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _useDemoData)[_useDemoData]) {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _demoData)[_demoData]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger].log('Widget haven\'t demo data and can be render correctly');
	      }
	      return babelHelpers.classPrivateFieldLooseBase(this, _demoData)[_demoData] || {};
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
	    babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger].log('Fetch data is impossible now (haven`t handler)');
	    return Promise.resolve({});
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _useDemoData)[_useDemoData]) {
	    return Promise.resolve(babelHelpers.classPrivateFieldLooseBase(this, _demoData)[_demoData] || {});
	  }
	  return landing_backend.Backend.getInstance().action('RepoWidget::fetchData', {
	    blockId: babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId],
	    params
	  }).then(jsonData => {
	    let data = {};
	    data = JSON.parse(jsonData);
	    if (data.error) {
	      throw new Error(data.error);
	    }
	    return data;
	  }).catch(error => {
	    const logMessages = [`Fetch data error!\nWidget ID: ${babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId]}`];
	    if (Object.keys(params) > 0) {
	      logMessages.push('\nFetch request params:', params);
	    }
	    if (main_core.Type.isString(error)) {
	      logMessages.push(`\nError in JSON data: ${error}`);
	    } else if (main_core.Type.isObject(error)) {
	      if (error instanceof Error && error.message) {
	        logMessages.push(`\nJavaScript error: ${error.message}`);
	      } else if (error.result && main_core.Type.isArray(error.result) && error.result.length > 0) {
	        logMessages.push('\nError from backend:');
	        error.result.forEach(e => {
	          logMessages.push(e);
	        });
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _logger)[_logger].log(...logMessages);
	    throw new Error(main_core.Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'));
	  });
	}
	function _message2(name, params = {}, target = window) {
	  target.postMessage({
	    name,
	    params,
	    origin: babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId]
	  }, '*');
	}
	function _bindEvents2() {
	  main_core.Event.bind(window, 'message', babelHelpers.classPrivateFieldLooseBase(this, _onMessage)[_onMessage].bind(this));
	}
	function _onMessage2(event) {
	  // todo: need check origin manually?

	  if (event.data && event.data.origin && event.data.name && event.data.params && main_core.Type.isObject(event.data.params)) {
	    if (event.data.origin !== babelHelpers.classPrivateFieldLooseBase(this, _uniqueId)[_uniqueId]) {
	      return;
	    }
	    if (event.data.name === 'fetchData') {
	      babelHelpers.classPrivateFieldLooseBase(this, _fetchData)[_fetchData](event.data.params).then(data => {
	        babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('setData', {
	          data
	        }, event.source);
	      }).catch(error => {
	        babelHelpers.classPrivateFieldLooseBase(this, _message)[_message]('setError', {
	          error
	        }, event.source);
	      });
	    }
	    if (event.data.name === 'setSize' && event.data.params.size !== undefined) {
	      babelHelpers.classPrivateFieldLooseBase(this, _frame)[_frame].height = parseInt(event.data.params.size, 10);
	    }
	    if (event.data.name === 'openApplication' && babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] > 0) {
	      const params = main_core.Type.isObject(event.data.params) ? event.data.params : {};
	      BX.rest.AppLayout.openApplication(babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId], params);
	    }
	    if (event.data.name === 'openPath' && main_core.Type.isString(event.data.params.path)) {
	      BX.rest.AppLayout.openPath(babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId], {
	        path: event.data.params.path
	      });
	    }
	  }
	}
	WidgetVue.runningAppNodes = new Set();

	exports.WidgetVue = WidgetVue;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX));
//# sourceMappingURL=widgetvue.bundle.js.map
