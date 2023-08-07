/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Clipboard manager
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */

	var Clipboard = /*#__PURE__*/function () {
	  function Clipboard() {
	    babelHelpers.classCallCheck(this, Clipboard);
	  }
	  babelHelpers.createClass(Clipboard, null, [{
	    key: "copy",
	    value: function copy() {
	      var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var store = Clipboard.getStore();
	      if (text) {
	        store.focus();
	        store.value = text;
	        store.selectionStart = 0;
	        document.execCommand("copy");
	      } else {
	        document.execCommand("copy");
	        store.focus();
	        document.execCommand("paste");
	        text = store.value;
	      }
	      Clipboard.removeStore();
	      return text;
	    }
	  }, {
	    key: "getStore",
	    value: function getStore() {
	      if (Clipboard.store) {
	        return Clipboard.store;
	      }
	      Clipboard.store = document.createElement('textarea');
	      Clipboard.store.style = "position: absolute; opacity: 0; top: -1000px; left: -1000px;";
	      document.body.insertBefore(Clipboard.store, document.body.firstChild);
	      return Clipboard.store;
	    }
	  }, {
	    key: "removeStore",
	    value: function removeStore() {
	      if (!Clipboard.store) {
	        return true;
	      }
	      document.body.removeChild(Clipboard.store);
	      Clipboard.store = null;
	      return true;
	    }
	  }]);
	  return Clipboard;
	}();
	Clipboard.store = null;

	exports.Clipboard = Clipboard;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=clipboard.bundle.js.map
