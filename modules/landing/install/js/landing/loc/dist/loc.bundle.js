this.BX = this.BX || {};
(function (exports, main_core, landing_env) {
	'use strict';

	var pageTypeAlias = {
	  GROUP: 'KNOWLEDGE'
	};
	/**
	 * @memberOf BX.Landing
	 */

	var Loc =
	/*#__PURE__*/
	function (_MainLoc) {
	  babelHelpers.inherits(Loc, _MainLoc);

	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Loc).apply(this, arguments));
	  }

	  babelHelpers.createClass(Loc, null, [{
	    key: "getMessage",
	    value: function getMessage(key) {
	      var pageType = function () {
	        var type = landing_env.Env.getInstance().getType();
	        return pageTypeAlias[type] || type;
	      }();

	      if (pageType) {
	        var typedMessageKey = "".concat(key, "__").concat(pageType);

	        if (main_core.Type.isString(BX.message[typedMessageKey])) {
	          return main_core.Loc.getMessage(typedMessageKey);
	        }
	      }

	      return main_core.Loc.getMessage(key);
	    }
	  }]);
	  return Loc;
	}(main_core.Loc);

	exports.Loc = Loc;

}(this.BX.Landing = this.BX.Landing || {}, BX, BX.Landing));
//# sourceMappingURL=loc.bundle.js.map
