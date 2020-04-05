;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements interface of block html preview
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.BlockHTMLPreview = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-block-html-preview");
		this.onClickHandler = typeof data.onClick === "function" ? data.onClick : (function() {});
		this.block = BX.clone(document.querySelector("#block"+data.content+".block-wrapper"));

		[].slice.call(this.block.querySelectorAll(".landing-ui-panel")).forEach(BX.remove);

		this.body.appendChild(this.block);
		this.layout.addEventListener("click", this.onClick.bind(this));
	};


	BX.Landing.UI.Card.BlockHTMLPreview.prototype = {
		constructor: BX.Landing.UI.Card.BlockHTMLPreview,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		onClick: function()
		{
			this.onClickHandler();
		}
	};
})();