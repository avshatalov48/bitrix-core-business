this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core) {
	'use strict';

	const Public = main_core.Reflection.getClass('top.BX.Messenger.Public');
	if (!Public) {
	  console.error('The BX.Messenger.Public class cannot be accessed from this location.');
	}

	// pretty export
	const namespace = main_core.Reflection.namespace('BX.Messenger.Public');
	if (namespace) {
	  namespace.Iframe = Public;
	}

	exports.Messenger = Public;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX));
//# sourceMappingURL=iframe.bundle.js.map
