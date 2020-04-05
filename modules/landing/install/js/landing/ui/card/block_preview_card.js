;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	var addClass = BX.Landing.Utils.addClass;
	var append = BX.Landing.Utils.append;
	var create = BX.Landing.Utils.create;


	/**
	 * Implements interface for works with block preview card
	 *
	 * @extends {BX.Landing.UI.Card.BaseCard}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Card.BlockPreviewCard = function(data)
	{
		BX.Landing.UI.Card.BaseCard.apply(this, arguments);
		this.layout.classList.add("landing-ui-card-block-preview");

		this.mode = typeof data.mode === "string" ? data.mode : "img";
		this.title = typeof data.title === "string" ? data.title : "";
		this.imageSrc = typeof data.image === "string" ? data.image : "/bitrix/images/landing/empty-preview.png";
		this.code = typeof data.code === "string" ? data.code : "";
		this.isNew = typeof data.isNew === "boolean" ? data.isNew : false;
		this.imageContainer = BX.create("div", {props: {className: "landing-ui-card-block-preview-image-container"}});
		this.body.appendChild(this.imageContainer);
		this.header.innerText = this.title;
		this.layout.dataset.code = this.code;
		this.requiresUpdates = data.requires_updates;

		if (this.isNew)
		{
			this.title = BX.create("span", {
				props: {className: "landing-ui-new-inline"},
				text: BX.message("LANDING_BLOCKS_LIST_PREVIEW_NEW")
			}).outerHTML + "&nbsp;" + this.title;
			this.header.innerHTML = this.title;
		}

		if (this.mode === "background")
		{
			this.imageContainer.style.backgroundImage = "url(" + this.imageSrc + ")";
		}
		else
		{
			var src = this.imageSrc || "/bitrix/images/landing/empty-preview.png";
			this.image = BX.create("img", {
				props: {src: src},
				attrs: {
					style: "opacity: " + (this.imageSrc ? 1 : .6)
				}
			});
			this.imageContainer.appendChild(this.image);
		}

		if (this.requiresUpdates)
		{
			addClass(this.layout, "landing-ui-requires-update");

			var overlay = create("div", {
				props: {className: "landing-ui-requires-update-overlay"},
				children: [
					create("div", {
						props: {className: "landing-ui-requires-update-overlay-footer"},
						html: BX.message("LANDING_BLOCK_REQUIRES_UPDATE_MESSAGE")
					})
				]
			});

			append(overlay, this.imageContainer);

			this.onClickHandler = (function() {});
		}
	};


	BX.Landing.UI.Card.BlockPreviewCard.prototype = {
		constructor: BX.Landing.UI.Card.BlockPreviewCard,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype
	};
})();