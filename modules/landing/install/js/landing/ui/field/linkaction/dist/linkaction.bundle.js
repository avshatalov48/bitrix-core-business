this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core) {
	'use strict';

	class Linkaction {
	  constructor() {
	    console.log('BX.Landing.UI.Field.LinkActions');
	    this.createEl = BX.Landing.UI.Field.Link.createElement();
	  }

	  static createElement() {
	    return BX.create("div", {
	      props: {
	        className: "landing-ui-field-link-actions"
	      }
	    });
	  }

	}

	exports.Linkaction = Linkaction;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX));
//# sourceMappingURL=linkaction.bundle.js.map
