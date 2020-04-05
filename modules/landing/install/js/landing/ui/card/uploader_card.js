;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements interface for works with uploader
	 *
	 * @extends {BX.Landing.UI.Card.Library}
	 *
	 * @param {object} data
	 *
	 * @constructor
	 */
	BX.Landing.UI.Card.Uploader = function(data)
	{
		BX.Landing.UI.Card.Library.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-library-uploader");
		this.iframe = BX.create("iframe", {props: {className: "landing-ui-card-library-uploader-iframe"}});
		this.iframe.src = "/bitrix/tools/landing/uploader.php";
		this.imageList.appendChild(this.iframe);
		BX.remove(this.search);
		BX.remove(this.loadMore);
	};

	BX.Landing.UI.Card.Uploader.prototype = {
		constructor: BX.Landing.UI.Card.Uploader,
		__proto__: BX.Landing.UI.Card.Library.prototype
	};
})();