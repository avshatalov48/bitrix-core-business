/* eslint-disable */
(function (exports) {
	'use strict';

	const PageObject = {
	  getRootWindow() {
	    return PageObject.getTopWindowOfCurrentHost(window);
	  },
	  isCrossOriginObject(currentWindow) {
	    try {
	      void currentWindow.location.host;
	    } catch {
	      // cross-origin object
	      return true;
	    }
	    return false;
	  },
	  getTopWindowOfCurrentHost(currentWindow) {
	    if (!PageObject.isCrossOriginObject(currentWindow.parent) && currentWindow.parent !== currentWindow && currentWindow.parent.location.host === currentWindow.location.host) {
	      return PageObject.getTopWindowOfCurrentHost(currentWindow.parent);
	    }
	    return currentWindow;
	  },
	  getParentWindowOfCurrentHost(currentWindow) {
	    if (PageObject.isCrossOriginObject(currentWindow.parent)) {
	      return currentWindow;
	    }
	    return currentWindow.parent;
	  }
	};

	exports.PageObject = PageObject;

}((this.BX = this.BX || {})));
//# sourceMappingURL=pageobject.bundle.js.map
