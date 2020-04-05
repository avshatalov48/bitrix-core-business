;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements interface of landing preview card
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Card.LandingPreviewCard = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-landing-preview");
		this.onClickHandler = typeof data.onClick === "function" ? data.onClick : (function() {});

		if (!!data.preview && typeof data.preview === "string")
		{
			this.body.style.backgroundImage = "url(" + data.preview + ")";
		}
		else
		{
			this.body.hidden = true;
		}

		if (!!data.description && typeof data.description === "string")
		{
			this.footer = BX.create("div", {props: {className: "landing-ui-card-footer"}, text: data.description});
		}

		this.layout.addEventListener("click", this.onClick.bind(this));
	};


	BX.Landing.UI.Card.LandingPreviewCard.prototype = {
		constructor: BX.Landing.UI.Card.LandingPreviewCard,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		onClick: function()
		{
			this.onClickHandler();
		}
	};
})();