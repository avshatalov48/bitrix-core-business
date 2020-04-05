;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * @deprecated since - use BX.Loader instead of this
	 *
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @constructor
	 */
	BX.Landing.UI.Card.Loader = function()
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-loader");
		this.inner = BX.create("div", {
			props: {className: "landing-ui-loader-inner"},
			html: "<svg class=\"main-grid-loader-circular\" viewBox=\"25 25 50 50\">\n" +
			"    <circle class=\"main-grid-loader-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n" +
			"</svg>"
		});
		this.body.appendChild(this.inner);
	};

	BX.Landing.UI.Card.Loader.prototype = {
		constructor: BX.Landing.UI.Card.Loader,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype
	};
})();