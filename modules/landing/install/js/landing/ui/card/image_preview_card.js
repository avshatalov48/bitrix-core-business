;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");


	/**
	 * Implements interface for works with image preview
	 *
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Card.ImagePreview = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.title = "title" in data ? data.title : "";
		this.imageSrc = ("image" in data ? data.image : "").replace("http:", "");
		this.code = "code" in data ? data.code : "";
		this.clickHandler = "onClick" in data ? data.onClick : "";
		this.credit = "credit" in data ? data.credit : null;
		this.layout.classList.add("landing-ui-card-image-preview");
		this.imageContainer = BX.Landing.UI.Card.ImagePreview.createImageContainer();
		this.header.innerText = this.title;
		this.layout.dataset.code = this.code;

		if (this.credit)
		{
			this.creditLayout = BX.create("div", {
				props: {className: "landing-ui-card-image-preview-credit"},
				children: [
					BX.create("a", {
						props: {className: "landing-ui-card-image-preview-credit-link"},
						attrs: {
							href: this.credit.link,
							target: "_blank",
							rel: "nofollow",
							title: BX.Landing.Loc.getMessage("LANDING_UNSPLASH_CREDIT_LABEL") + " " + this.credit.name
						},
						text: this.credit.name
					})
				]
			});

			this.layout.appendChild(this.creditLayout);
		}

		if (this.imageSrc)
		{
			this.imageContainer.style.backgroundImage = "url("+this.imageSrc+")";
			this.body.appendChild(this.imageContainer);
		}
	};


	BX.Landing.UI.Card.ImagePreview.createImageContainer = function()
	{
		return BX.create("div", {props: {className: "landing-ui-card-image-preview-container"}});
	};


	BX.Landing.UI.Card.ImagePreview.prototype = {
		constructor: BX.Landing.UI.Card.ImagePreview,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		onClick: function()
		{
			this.clickHandler(this);
		}
	};
})();