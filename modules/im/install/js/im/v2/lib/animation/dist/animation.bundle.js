/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	const DEFAULT_ANIMATION_PARAMS = {
	  increment: 20,
	  callback: () => {},
	  duration: 500
	};
	const Animation = {
	  start(params) {
	    if (main_core.Type.isUndefined(params.start) || main_core.Type.isUndefined(params.end) || !params.element || !params.elementProperty) {
	      return 0;
	    }
	    params = {
	      ...DEFAULT_ANIMATION_PARAMS,
	      ...params
	    };
	    const diff = params.end - params.start;
	    let currentValue = 0;
	    let frameId;
	    const animate = () => {
	      currentValue += params.increment;
	      params.element[params.elementProperty] = easeFunction(currentValue, params.start, diff, params.duration);
	      if (currentValue < params.duration) {
	        frameId = requestAnimationFrame(animate);
	      } else {
	        params.callback();
	      }
	      return frameId;
	    };
	    return animate();
	  },
	  cancel() {
	    cancelAnimationFrame();
	  }
	};
	const easeFunction = function (currentValue, start, diff, duration) {
	  currentValue /= duration / 2;
	  if (currentValue < 1) {
	    return diff / 2 * (currentValue * currentValue) + start;
	  }
	  currentValue--;
	  return -diff / 2 * (currentValue * (currentValue - 2) - 1) + start;
	};

	exports.Animation = Animation;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX));
//# sourceMappingURL=animation.bundle.js.map
