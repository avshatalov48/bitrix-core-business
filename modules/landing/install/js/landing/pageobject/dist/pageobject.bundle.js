this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @memberOf BX.Landing
	 */

	var PageObject = /*#__PURE__*/function () {
	  function PageObject() {
	    babelHelpers.classCallCheck(this, PageObject);
	    babelHelpers.defineProperty(this, "store", {});
	  }

	  babelHelpers.createClass(PageObject, [{
	    key: "top",

	    /**
	     * @deprecated
	     * @see PageObject.getTopPanel()
	     * @return {Promise}
	     */
	    value: function top() {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        if (!_this.store.topPanel) {
	          _this.store.topPanel = PageObject.getTopPanel();
	        }

	        if (_this.store.topPanel) {
	          resolve(_this.store.topPanel);
	          return;
	        }

	        reject(new Error('Top panel unavailable'));
	        console.warn('Top panel unavailable');
	      });
	    }
	    /**
	     * @deprecated
	     * @see BX.Landing.UI.Panel.StylePanel.getInstance()
	     * @return {Promise}
	     */

	  }, {
	    key: "design",
	    value: function design() {
	      return Promise.resolve(BX.Landing.UI.Panel.StylePanel.getInstance());
	    }
	    /**
	     * @deprecated
	     * @see BX.Landing.UI.Panel.ContentEdit.getInstance()
	     * @return {Promise}
	     */

	  }, {
	    key: "content",
	    value: function content() {
	      return Promise.resolve(BX.Landing.UI.Panel.ContentEdit.getInstance());
	    }
	    /**
	     * @deprecated
	     * @see BX.Landing.UI.Panel.EditorPanel.getInstance()
	     * @return {Promise}
	     */

	  }, {
	    key: "inlineEditor",
	    value: function inlineEditor() {
	      return Promise.resolve(BX.Landing.UI.Panel.EditorPanel.getInstance());
	    }
	    /**
	     * @deprecated
	     * @see PageObject.getEditorWindow()
	     * @return {Promise}
	     */

	  }, {
	    key: "view",
	    value: function view() {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (!_this2.store.view) {
	          var rootWindow = PageObject.getRootWindow();
	          _this2.store.view = rootWindow.document.querySelector('.landing-ui-view');
	        }

	        if (_this2.store.view) {
	          resolve(_this2.store.view);
	          return;
	        }

	        reject(new Error('View iframe unavailable'));
	        console.warn('View iframe unavailable');
	      });
	    }
	    /**
	     * @deprecated
	     * @see BX.Landing.Block.storage
	     * @return {Promise}
	     */

	  }, {
	    key: "blocks",
	    value: function blocks() {
	      return Promise.resolve(PageObject.getRootWindow().BX.Landing.Block.storage);
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (main_core.Type.isNil(PageObject.instance)) {
	        PageObject.instance = new PageObject();
	      }

	      return PageObject.instance;
	    }
	  }, {
	    key: "getRootWindow",
	    value: function getRootWindow() {
	      return this.cache.remember('rootWindow', function () {
	        if (document.body.querySelector('.landing-ui-view')) {
	          return window;
	        }

	        if (window.parent.document.body.querySelector('.landing-ui-view')) {
	          return window.parent;
	        }

	        return window.top;
	      });
	    }
	  }, {
	    key: "getEditorWindow",
	    value: function getEditorWindow() {
	      var _this3 = this;

	      return this.cache.remember('editorWindow', function () {
	        var rootWindow = _this3.getRootWindow();

	        var rootDocument = rootWindow.document;
	        var editorFrame = rootDocument.querySelector('.landing-ui-view');

	        if (editorFrame && editorFrame.contentWindow) {
	          return editorFrame.contentWindow;
	        }

	        return null;
	      });
	    }
	  }, {
	    key: "getTopPanel",
	    value: function getTopPanel() {
	      var _this4 = this;

	      return this.cache.remember('topPanel', function () {
	        return _this4.getRootWindow().document.querySelector('.landing-ui-panel-top');
	      });
	    }
	  }, {
	    key: "getEditPanelContent",
	    value: function getEditPanelContent() {
	      var _this5 = this;

	      return this.cache.remember('editPanel', function () {
	        return _this5.getRootWindow().document.querySelector('.landing-ui-panel-content.landing-ui-panel-content-edit .landing-ui-panel-content-body-content');
	      });
	    }
	  }, {
	    key: "getStylePanelContent",
	    value: function getStylePanelContent() {
	      var _this6 = this;

	      return this.cache.remember('stylePanel', function () {
	        return _this6.getRootWindow().document.querySelector('.landing-ui-panel-content.landing-ui-panel-style .landing-ui-panel-content-body-content');
	      });
	    }
	  }, {
	    key: "getBlocks",
	    value: function getBlocks() {
	      return this.getRootWindow().BX.Landing.Block.storage;
	    }
	  }]);
	  return PageObject;
	}();
	babelHelpers.defineProperty(PageObject, "cache", new main_core.Cache.MemoryCache());
	babelHelpers.defineProperty(PageObject, "instance", null);

	exports.PageObject = PageObject;

}((this.BX.Landing = this.BX.Landing || {}),BX));
//# sourceMappingURL=pageobject.bundle.js.map
