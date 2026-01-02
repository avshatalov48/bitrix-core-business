/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_popup) {
	'use strict';

	class Tooltip {
	  constructor(options) {
	    this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	    this.bindElement = main_core.Type.isElementNode(options.bindElement) ? options.bindElement : null;
	    this.title = main_core.Type.isString(options.title) ? options.title : null;
	    this.minWidth = main_core.Type.isNumber(options.minWidth) ? options.minWidth : null;
	    this.minHeight = main_core.Type.isNumber(options.minHeight) ? options.minHeight : null;
	    this.maxWidth = main_core.Type.isNumber(options.maxWidth) ? options.maxWidth : 340;
	    this.setContent(options.content);
	  }
	  getPopupWindow() {
	    if (!this.popupWindow) {
	      var _this$title;
	      this.popupWindow = new main_popup.Popup({
	        bindElement: this.bindElement,
	        className: 'ui-dialog-tooltip --ui-context-content-light',
	        angle: true,
	        titleBar: (_this$title = this.title) != null ? _this$title : null,
	        minWidth: this.minWidth,
	        minHeight: this.minHeight,
	        maxWidth: this.maxWidth,
	        draggable: null,
	        animation: 'fading-slide',
	        closeByEsc: true,
	        ...this.popupOptions
	      });
	      main_core.Dom.style(this.popupWindow.getTitleContainer(), 'cursor', null);
	      main_core.Event.unbindAll(this.popupWindow.getTitleContainer());
	    }
	    return this.popupWindow;
	  }
	  setContent(content) {
	    if (main_core.Type.isString(content) || main_core.Type.isElementNode(content)) {
	      this.getPopupWindow().setContent(content);
	    }
	  }
	  show() {
	    this.getPopupWindow().show();
	  }
	  close() {
	    this.getPopupWindow().close();
	  }
	}

	exports.Tooltip = Tooltip;

}((this.BX.UI.Dialogs = this.BX.UI.Dialogs || {}),BX,BX.Main));
//# sourceMappingURL=tooltip.bundle.js.map
