/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	class Manual {
	  constructor(params) {
	    this.manualCode = main_core.Type.isString(params.manualCode) ? params.manualCode : '';
	    this.width = main_core.Type.isNumber(params.width) ? params.width : 1000;
	    this.urlParams = main_core.Type.isPlainObject(params.urlParams) ? params.urlParams : {};
	    this.analyticsLabel = main_core.Type.isPlainObject(params.analyticsLabel) ? params.analyticsLabel : {};
	    this.sidePanelId = 'manual-side-panel-' + this.manualCode;
	  }
	  static show(manualCode, urlParams = {}, analyticsLabel = {}) {
	    const manual = new Manual({
	      manualCode,
	      urlParams,
	      analyticsLabel
	    });
	    manual.open();
	  }
	  open() {
	    if (this.isOpen()) {
	      return;
	    }
	    BX.SidePanel.Instance.open(this.sidePanelId, {
	      contentCallback: () => this.createFrame(),
	      width: this.width
	    });
	  }
	  createFrame() {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.manual.getInitParams', {
	        data: {
	          manualCode: this.manualCode,
	          urlParams: this.urlParams
	        },
	        analyticsLabel: this.analyticsLabel
	      }).then(response => {
	        resolve(this.renderFrame(response.data.url));
	      });
	    });
	  }
	  renderFrame(url) {
	    const frameStyles = 'position: absolute; left: 0; top: 0; padding: 0;' + ' border: none; margin: 0; width: 100%; height: 100%;';
	    return main_core.Tag.render(_t || (_t = _`<iframe style="${0}" src="${0}"></iframe>`), frameStyles, url);
	  }
	  getSidePanel() {
	    return BX.SidePanel.Instance.getSlider(this.sidePanelId);
	  }
	  isOpen() {
	    return this.getSidePanel() && this.getSidePanel().isOpen();
	  }
	}

	exports.Manual = Manual;

}((this.BX.UI.Manual = this.BX.UI.Manual || {}),BX));
//# sourceMappingURL=manual.bundle.js.map
