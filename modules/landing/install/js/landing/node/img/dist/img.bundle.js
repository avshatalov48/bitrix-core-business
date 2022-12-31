this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	class Img {
	  constructor(options = {
	    name: 'Img'
	  }) {
	    this.name = options.name;
	  }

	  setName(name) {
	    if (main_core.Type.isString(name)) {
	      this.name = name;
	    }
	  }

	  getName() {
	    return this.name;
	  }

	}

	exports.Img = Img;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX));
//# sourceMappingURL=img.bundle.js.map
