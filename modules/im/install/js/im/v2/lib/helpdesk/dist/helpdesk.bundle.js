/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports) {
	'use strict';

	const openHelpdeskArticle = articleCode => {
	  var _BX$Helper;
	  (_BX$Helper = BX.Helper) == null ? void 0 : _BX$Helper.show(`redirect=detail&code=${articleCode}`);
	};

	exports.openHelpdeskArticle = openHelpdeskArticle;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {})));
//# sourceMappingURL=helpdesk.bundle.js.map
