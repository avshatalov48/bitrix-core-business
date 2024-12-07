this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core_events) {
	'use strict';

	var Public = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Public, _EventEmitter);
	  function Public() {
	    var _this;
	    babelHelpers.classCallCheck(this, Public);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Public).call(this));
	    _this.setEventNamespace('BX.Landing.Mainpage.Public');
	    _this.initializeBlocks();
	    return _this;
	  }
	  babelHelpers.createClass(Public, [{
	    key: "initializeBlocks",
	    value: function initializeBlocks() {
	      var blocks = Array.from(document.getElementsByClassName("block-wrapper"));
	      if (blocks.length > 0) {
	        blocks.forEach(function (block) {
	          var eventData = [];
	          eventData['block'] = block;
	          BX.onCustomEvent("BX.Landing.Block:init", [eventData]);
	        });
	      }
	    }
	  }]);
	  return Public;
	}(main_core_events.EventEmitter);

	exports.Public = Public;

}((this.BX.Landing.Mainpage = this.BX.Landing.Mainpage || {}),BX.Event));
//# sourceMappingURL=public.bundle.js.map
