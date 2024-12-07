/* eslint-disable */
this.BX = this.BX || {};
this.BX.Lists = this.BX.Lists || {};
(function (exports,main_core) {
	'use strict';

	var _getFillConstantsUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFillConstantsUrl");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class CreationGuide {
	  static open(params) {
	    if (!main_core.Type.isPlainObject(params) || !main_core.Type.isStringFilled(params.iBlockTypeId) || !main_core.Type.isInteger(params.iBlockId)) {
	      throw new TypeError('invalid params');
	    }
	    const url = main_core.Uri.addParam('/bitrix/components/bitrix/lists.element.creation_guide/', {
	      iBlockTypeId: encodeURIComponent(params.iBlockTypeId),
	      iBlockId: encodeURIComponent(params.iBlockId),
	      fillConstantsUrl: encodeURIComponent(babelHelpers.classPrivateFieldLooseBase(this, _getFillConstantsUrl)[_getFillConstantsUrl](params)),
	      analyticsSection: params.analyticsSection || ''
	    });
	    BX.SidePanel.Instance.open(url, {
	      width: 900,
	      cacheable: false,
	      allowChangeHistory: false,
	      loader: '/bitrix/js/lists/element/creation-guide/images/skeleton.svg',
	      events: {
	        onCloseComplete: () => {
	          if (main_core.Type.isFunction(params.onClose)) {
	            params.onClose();
	          }
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics](params);
	  }
	}
	function _getFillConstantsUrl2(params) {
	  if (main_core.Type.isStringFilled(params.fillConstantsUrl)) {
	    return params.fillConstantsUrl;
	  }
	  return main_core.Uri.addParam('/bizproc/userprocesses/', {
	    iBlockId: params.iBlockId
	  });
	}
	function _sendAnalytics2(params) {
	  main_core.Runtime.loadExtension('ui.analytics').then(({
	    sendData
	  }) => {
	    sendData({
	      tool: 'automation',
	      category: 'bizproc_operations',
	      event: 'process_start_attempt',
	      c_section: params.analyticsSection || 'bizproc',
	      p1: params.analyticsP1
	    });
	  }).catch(() => {});
	}
	Object.defineProperty(CreationGuide, _sendAnalytics, {
	  value: _sendAnalytics2
	});
	Object.defineProperty(CreationGuide, _getFillConstantsUrl, {
	  value: _getFillConstantsUrl2
	});

	exports.CreationGuide = CreationGuide;

}((this.BX.Lists.Element = this.BX.Lists.Element || {}),BX));
//# sourceMappingURL=creation-guide.bundle.js.map
