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
		this.appExpired = typeof data.app_expired === "boolean" ? data.app_expired : false;
		this.repoId = data.repo_id || null;
		this.imageSrc = typeof data.image === "string" ? data.image : "/bitrix/images/landing/empty-preview.png";
		this.code = typeof data.code === "string" ? data.code : "";
		this.favorite = data.favorite;
		this.favoriteMy = data.favoriteMy;
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
				text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_NEW")
			}).outerHTML + "&nbsp;" + this.title;
			this.header.innerHTML = this.title;
		}

		if (this.repoId || this.favorite || this.appExpired)
		{
			this.labels = BX.create("div", {
				props: {className: "landing-ui-card-labels"},
			});
			BX.insertAfter(this.labels, this.getHeader());
		}

		// market label
		if (this.repoId || this.appExpired)
		{
			var marketLabel = BX.create("div", {
				props: {className: "landing-ui-card-label landing-ui-card-label-repo"},
				text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_MARKET"),
				dataset: {
					hint: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_MARKET_HINT"),
					hintNoIcon: 'Y'
				}
			});
			BX.append(marketLabel, this.labels);
			BX.UI.Hint.init(this.labels);
		}

		// my labels
		if (this.favorite)
		{
			if (this.favoriteMy)
			{
				BX.append(
					BX.create("div", {
						props: {className: "landing-ui-card-label landing-ui-card-label-my"},
						text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_MY_NEW")
					}),
					this.labels
				);
			}

			BX.append(
				BX.create("div", {
					props: {className: "landing-ui-card-label landing-ui-card-label-favorite"},
					text: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_FAVORITE")
				}),
				this.labels
			);

			var blockId = (this.code.split('@').length === 2)
				? this.code.split('@')[1]
				: false;
			if (blockId && this.favoriteMy)
			{
				BX.Runtime.loadExtension('ui.dialogs.messagebox');
				var deleteMyButton = this.getRemoveButton();
				deleteMyButton.onclick = function (event)
				{
					event.stopPropagation();
					BX.UI.Dialogs.MessageBox.show({
						message: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_DELETE_MSG"),
						buttons: BX.UI.Dialogs.MessageBoxButtons.YES_CANCEL,
						onYes: function ()
						{
							return BX.Landing.Backend.getInstance().action(
								"Landing::unFavoriteBlock",
								{blockId: blockId}
							).then(function() {
								var mainInstance = BX.Landing.Main.getInstance();
								mainInstance.removeBlockFromList(this.code);
								return true;
							}.bind(this))
								.catch(function(error) {
									console.log("error", error);
									return false;
								});
						}.bind(this),
					});
				}.bind(this);
				BX.append(deleteMyButton, this.getBody());
			}
		}

		if (this.appExpired)
		{
			this.addWarning(BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_EXPIRED"));
			this.onClickHandler = (function() {});
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
						html: BX.Landing.Loc.getMessage("LANDING_BLOCK_REQUIRES_UPDATE_MESSAGE")
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