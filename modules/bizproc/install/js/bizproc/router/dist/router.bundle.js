/* eslint-disable */
this.BX = this.BX || {};
(function (exports,sidepanel,main_core) {
	'use strict';

	var _startSliderWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startSliderWidth");
	var _bind = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bind");
	var _detectSliderWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("detectSliderWidth");
	var _openSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openSlider");
	var _createEditConstantSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createEditConstantSlider");
	class Router {
	  static init() {
	    if (top !== window) {
	      top.BX.Runtime.loadExtension('bizproc.router').then(({
	        Router
	      }) => {
	        Router.init();
	      }).catch(e => console.error(e));
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _bind)[_bind]();
	  }
	  static openWorkflowLog(workflowId) {
	    const url = `/bitrix/components/bitrix/bizproc.log/slider.php?WORKFLOW_ID=${workflowId}`;
	    const options = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _detectSliderWidth)[_detectSliderWidth](),
	      cacheable: false
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, options);
	  }
	  static openWorkflow(workflowId) {
	    const url = `/company/personal/bizproc/${workflowId}/`;
	    const options = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _detectSliderWidth)[_detectSliderWidth](),
	      cacheable: false,
	      loader: 'bizproc:workflow-info'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, options);
	  }
	  static openWorkflowTask(taskId, userId) {
	    let url = `/company/personal/bizproc/${taskId}/`;
	    if (userId > 0) {
	      url += `?USER_ID=${userId}`;
	    }
	    const options = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _detectSliderWidth)[_detectSliderWidth](),
	      cacheable: false,
	      loader: 'bizproc:workflow-info'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, options);
	  }
	  static openUserProcessesStart(options) {
	    const sliderOptions = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _startSliderWidth)[_startSliderWidth],
	      cacheable: false,
	      loader: 'bizproc:start-process-page',
	      ...options
	    };
	    let url = '/bizproc/start/';
	    if (options && options.requestMethod === 'get' && options.requestParams) {
	      url = BX.Uri.addParam(url, options.requestParams);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, sliderOptions);
	  }
	  static openWorkflowStartList(options) {
	    const sliderOptions = {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _startSliderWidth)[_startSliderWidth],
	      cacheable: false,
	      loader: 'bizproc:start-process-page',
	      ...options
	    };
	    let url = '/bitrix/components/bitrix/bizproc.workflow.start.list/';
	    if (options && options.requestMethod === 'get' && options.requestParams) {
	      url = BX.Uri.addParam(url, options.requestParams);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, sliderOptions);
	  }
	  static openWorkflowChangeConstants(params) {
	    const url = babelHelpers.classPrivateFieldLooseBase(Router, _createEditConstantSlider)[_createEditConstantSlider](params);
	    const sliderOptions = {
	      width: 900,
	      cacheable: false,
	      allowChangeHistory: false
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _openSlider)[_openSlider](url, sliderOptions);
	  }
	}
	function _bind2() {
	  top.BX.SidePanel.Instance.bindAnchors({
	    rules: [{
	      condition: ['/rpa/task/'],
	      options: {
	        width: 580,
	        cacheable: false,
	        allowChangeHistory: false
	      }
	    }, {
	      condition: ['/company/personal/bizproc/([a-zA-Z0-9\\.]+)/'],
	      options: {
	        cacheable: false,
	        loader: 'bizproc:workflow-info',
	        width: babelHelpers.classPrivateFieldLooseBase(this, _detectSliderWidth)[_detectSliderWidth]()
	      }
	    }]
	  });
	}
	function _detectSliderWidth2() {
	  if (window.innerWidth < 1500) {
	    return null; // default slider width
	  }

	  return 1500 + Math.floor((window.innerWidth - 1500) / 3);
	}
	function _openSlider2(url, options) {
	  top.BX.Runtime.loadExtension('sidepanel').then(() => {
	    BX.SidePanel.Instance.open(url, options);
	  }).catch(response => console.error(response.errors));
	}
	function _createEditConstantSlider2(params) {
	  let url = main_core.Uri.addParam('/bitrix/components/bitrix/bizproc.workflow.start/', {
	    sessid: main_core.Loc.getMessage('bitrix_sessid'),
	    action: 'CHANGE_CONSTANTS'
	  });
	  const templateId = main_core.Text.toInteger(params.templateId);
	  if (templateId > 0) {
	    url = main_core.Uri.addParam(url, {
	      templateId
	    });
	  }
	  if (params.signedDocumentType) {
	    url = main_core.Uri.addParam(url, {
	      signedDocumentType: params.signedDocumentType
	    });
	  }
	  return url;
	}
	Object.defineProperty(Router, _createEditConstantSlider, {
	  value: _createEditConstantSlider2
	});
	Object.defineProperty(Router, _openSlider, {
	  value: _openSlider2
	});
	Object.defineProperty(Router, _detectSliderWidth, {
	  value: _detectSliderWidth2
	});
	Object.defineProperty(Router, _bind, {
	  value: _bind2
	});
	Object.defineProperty(Router, _startSliderWidth, {
	  writable: true,
	  value: 970
	});

	exports.Router = Router;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX,BX));
//# sourceMappingURL=router.bundle.js.map
