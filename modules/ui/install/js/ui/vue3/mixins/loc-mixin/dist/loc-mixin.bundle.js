/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,main_core) {
	'use strict';

	const locMixin = {
	  methods: {
	    loc(name, replacements) {
	      return main_core.Loc.getMessage(name, replacements);
	    }
	  }
	};

	exports.locMixin = locMixin;

}((this.BX.Vue3.Mixins = this.BX.Vue3.Mixins || {}),BX));
//# sourceMappingURL=loc-mixin.bundle.js.map
