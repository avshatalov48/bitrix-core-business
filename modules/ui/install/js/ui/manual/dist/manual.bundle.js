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
	    this.analyticsLabel = main_core.Type.isPlainObject(params.analyticsLabel) ? params.analyticsLabel : null;
	    this.analytics = main_core.Type.isPlainObject(params.analytics) ? params.analytics : null;
	    this.sidePanelId = `manual-side-panel-${this.manualCode}`;
	  }
	  static show(...args) {
	    let manualCode;
	    let urlParams;
	    let analyticsLabel;
	    let analytics;
	    if (main_core.Type.isPlainObject(args[0]) && args.length === 1) {
	      ({
	        manualCode,
	        urlParams = {},
	        analyticsLabel = null,
	        analytics = null
	      } = args[0]);
	    } else {
	      [manualCode, urlParams, analyticsLabel, analytics] = args;
	    }
	    const manual = new Manual({
	      manualCode,
	      urlParams,
	      analyticsLabel,
	      analytics
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
	    const config = {
	      data: {
	        manualCode: this.manualCode,
	        urlParams: this.urlParams
	      }
	    };
	    if (this.analyticsLabel) {
	      config.analyticsLabel = this.analyticsLabel;
	    } else if (this.analytics) {
	      config.analytics = this.analytics;
	    }
	    return new Promise((resolve, reject) => {
	      // eslint-disable-next-line promise/catch-or-return
	      main_core.ajax.runAction('ui.manual.getInitParams', config).then(response => {
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
