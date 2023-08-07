/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Animation manager
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var Animation = /*#__PURE__*/function () {
	  function Animation() {
	    babelHelpers.classCallCheck(this, Animation);
	  }
	  babelHelpers.createClass(Animation, null, [{
	    key: "start",
	    value: function start(params) {
	      var _params$start = params.start,
	        start = _params$start === void 0 ? 0 : _params$start,
	        _params$end = params.end,
	        end = _params$end === void 0 ? 0 : _params$end,
	        _params$increment = params.increment,
	        increment = _params$increment === void 0 ? 20 : _params$increment,
	        _params$callback = params.callback,
	        callback = _params$callback === void 0 ? function () {} : _params$callback,
	        _params$duration = params.duration,
	        duration = _params$duration === void 0 ? 500 : _params$duration,
	        element = params.element,
	        elementProperty = params.elementProperty;
	      var diff = end - start;
	      var currentPosition = 0;
	      var easeInOutQuad = function easeInOutQuad(current, start, diff, duration) {
	        current /= duration / 2;
	        if (current < 1) {
	          return diff / 2 * current * current + start;
	        }
	        current--;
	        return -diff / 2 * (current * (current - 2) - 1) + start;
	      };
	      var requestFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
	        return window.setTimeout(callback, 1000 / 60);
	      };
	      var frameId = null;
	      var animateScroll = function animateScroll() {
	        currentPosition += increment;
	        element[elementProperty] = easeInOutQuad(currentPosition, start, diff, duration);
	        if (currentPosition < duration) {
	          frameId = requestFrame(animateScroll);
	        } else {
	          if (callback && typeof callback === 'function') {
	            callback();
	          }
	        }
	        return frameId;
	      };
	      return animateScroll();
	    }
	  }, {
	    key: "cancel",
	    value: function cancel(id) {
	      var cancelFrame = window.cancelAnimationFrame || window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame || function (id) {
	        clearTimeout(id);
	      };
	      cancelFrame(id);
	    }
	  }]);
	  return Animation;
	}();
	Animation.frameIds = {};

	exports.Animation = Animation;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=animation.bundle.js.map
