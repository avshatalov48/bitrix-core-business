/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var _bind = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bind");
	var _detectSliderWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("detectSliderWidth");
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
	Object.defineProperty(Router, _detectSliderWidth, {
	  value: _detectSliderWidth2
	});
	Object.defineProperty(Router, _bind, {
	  value: _bind2
	});

	exports.Router = Router;

}((this.BX.Bizproc = this.BX.Bizproc || {})));
//# sourceMappingURL=router.bundle.js.map
