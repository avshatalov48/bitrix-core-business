/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var _bind = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bind");
	var _detectSliderWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("detectSliderWidth");
	var _openSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openSlider");
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
	Object.defineProperty(Router, _openSlider, {
	  value: _openSlider2
	});
	Object.defineProperty(Router, _detectSliderWidth, {
	  value: _detectSliderWidth2
	});
	Object.defineProperty(Router, _bind, {
	  value: _bind2
	});

	exports.Router = Router;

}((this.BX.Bizproc = this.BX.Bizproc || {})));
//# sourceMappingURL=router.bundle.js.map
