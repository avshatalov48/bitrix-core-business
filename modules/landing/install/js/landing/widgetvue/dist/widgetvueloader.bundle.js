/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_vue3,main_core,landing_backend,main_loader) {
	'use strict';

	var _rootNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootNode");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _lang = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lang");
	var _blockId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("blockId");
	var _appId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appId");
	var _fetchable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchable");
	var _clickable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clickable");
	var _allowedByTariff = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowedByTariff");
	var _getFrameContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFrameContent");
	var _getFrameHead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFrameHead");
	var _getAssetsConfigs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAssetsConfigs");
	var _getFrameBody = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFrameBody");
	class WidgetVueLoader {
	  // #application: VueCreateAppResult;
	  // #contentComponent: Object;

	  // #widgetOptions: {};

	  constructor(options) {
	    Object.defineProperty(this, _getFrameBody, {
	      value: _getFrameBody2
	    });
	    Object.defineProperty(this, _getAssetsConfigs, {
	      value: _getAssetsConfigs2
	    });
	    Object.defineProperty(this, _getFrameHead, {
	      value: _getFrameHead2
	    });
	    Object.defineProperty(this, _getFrameContent, {
	      value: _getFrameContent2
	    });
	    Object.defineProperty(this, _rootNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _lang, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _blockId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _appId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _fetchable, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _clickable, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _allowedByTariff, {
	      writable: true,
	      value: void 0
	    });
	    console.log("options", options);
	    babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] = main_core.Type.isString(options.rootNode) ? document.querySelector(options.rootNode) : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = main_core.Type.isObject(options.data) ? options.data : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lang)[_lang] = options.lang || {};
	    babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] = options.blockId ? main_core.Text.toNumber(options.blockId) : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] = options.appId ? main_core.Text.toNumber(options.appId) : 0;

	    // todo: need?
	    // this.#widgetOptions = options;
	    //
	    // delete this.#widgetOptions.rootNode;
	    //
	    // const isEditMode = Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
	    // this.#widgetOptions.clickable = !isEditMode;

	    babelHelpers.classPrivateFieldLooseBase(this, _fetchable)[_fetchable] = options.fetchable || false;
	    const isEditMode = main_core.Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
	    babelHelpers.classPrivateFieldLooseBase(this, _clickable)[_clickable] = !isEditMode;
	    babelHelpers.classPrivateFieldLooseBase(this, _allowedByTariff)[_allowedByTariff] = babelHelpers.classPrivateFieldLooseBase(this, _appId)[_appId] && main_core.Type.isBoolean(options.allowedByTariff) ? options.allowedByTariff : true;
	  }

	  /**
	   * Create frame with widget content
	   * @returns {Promise|*}
	   */
	  mount() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getFrameContent)[_getFrameContent]().then(srcDoc => {
	      const frame = document.createElement('iframe');
	      frame.sandbox = 'allow-scripts';
	      frame.srcdoc = srcDoc;
	      if (babelHelpers.classPrivateFieldLooseBase(this, _blockId)[_blockId] > 0 && babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode] && !WidgetVueLoader.runningAppNodes.has(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode])) {
	        main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]);
	        main_core.Dom.append(frame, babelHelpers.classPrivateFieldLooseBase(this, _rootNode)[_rootNode]);
	        window.addEventListener("message", event => {
	          if (event.origin === 'null') {
	            console.log("event", event);
	            // this.#getAssetsConfigs();
	          }

	          // can message back using event.source.postMessage(...)
	        });
	      }
	    });
	  }
	}
	function _getFrameContent2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getFrameHead)[_getFrameHead]().then(frameHead => {
	    return frameHead + babelHelpers.classPrivateFieldLooseBase(this, _getFrameBody)[_getFrameBody]();
	  });
	}
	function _getFrameHead2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getAssetsConfigs)[_getAssetsConfigs]().then(assets => {
	    const domain = `${document.location.protocol}//${document.location.host}`;
	    let head = '';
	    (assets.js || []).forEach(js => {
	      head += `<script src="${domain}${js}"></script>`;
	    });
	    (assets.css || []).forEach(css => {
	      head += `<link href="${domain}${css}" type="text/css" rel="stylesheet" />`;
	    });
	    const lang = JSON.stringify(assets.lang_additional || {});
	    head += `<script>BX.message(${lang})</script>`;
	    head += `<script>BX.message(${babelHelpers.classPrivateFieldLooseBase(this, _lang)[_lang]})</script>`;
	    return head;
	  });
	}
	function _getAssetsConfigs2() {
	  const extCodes = ['main.core', 'landing.widgetvue'];
	  return landing_backend.Backend.getInstance().action('Block::getAssetsConfig', {
	    extCodes
	  });
	}
	function _getFrameBody2() {
	  let frameContent = `
			<script>
				console.log('test message', BX.message('LANDING_WIDGETVUE_ERROR_PAYMENT'));	
			</script>
		`;

	  // window.parent.postMessage("halou", "*");

	  return frameContent;
	}
	WidgetVueLoader.runningAppNodes = new Set();

	exports.WidgetVueLoader = WidgetVueLoader;

}((this.BX.Landing = this.BX.Landing || {}),BX.Vue3,BX,BX.Landing,BX));
//# sourceMappingURL=widgetvueloader.bundle.js.map
