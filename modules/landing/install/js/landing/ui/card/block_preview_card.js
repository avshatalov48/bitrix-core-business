;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Card");

	BX.Runtime.loadExtension('ui.dialogs.messagebox');
	BX.Runtime.loadExtension('ui.notification');

	const HEART_CLASS = '--heart';
	const O_HEART_CLASS = '--o-heart';
	const ACTION_REMOVE = 'remove';
	const ACTION_ADD = 'add';
	const FAVOURITE_BADGE_CLASS = 'landing-ui-card-favorite-badge';
	const FAVOURITE_BADGE_ON_CLASS = 'landing-ui-card-favorite-badge--on';

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

		if (getComputedStyle(this.layout).position === 'static')
		{
			BX.Dom.style(this.layout, 'position', 'relative');
		}

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
		this.like = data.like;
		this.currentCategory = data.currentCategory;

		if (data.useFavouriteBadge === true)
		{
			let favoriteBadgeClassList = FAVOURITE_BADGE_CLASS;
			let iconClassList = 'ui-icon-set';
			if (data.isFavorite)
			{
				favoriteBadgeClassList = `${FAVOURITE_BADGE_CLASS} ${FAVOURITE_BADGE_ON_CLASS}`;
				iconClassList = `${iconClassList} ${HEART_CLASS}`;
			}
			else
			{
				iconClassList = `${iconClassList} ${O_HEART_CLASS}`;
			}

			this.icon = BX.create('i', { props: { className: iconClassList } });

			this.favoriteBadge = BX.create('div', {
				props: {
					className: favoriteBadgeClassList,
				},
				children: [
					this.icon,
				],
			});

			BX.append(this.favoriteBadge, this.imageContainer);

			this.showFavoriteNotification = function(message) {
				top.BX.UI.Notification.Center.notify({
					content: message,
					autoHideDelay: 5000,
					closeButton: false,
					useAirDesign: true,
				});
			};

			BX.Event.bind(this.favoriteBadge, 'click', (event) => {
				event.stopPropagation();
				let action = '';
				if (BX.Dom.hasClass(this.favoriteBadge, FAVOURITE_BADGE_ON_CLASS))
				{
					action = ACTION_REMOVE;
					BX.Dom.removeClass(this.favoriteBadge, FAVOURITE_BADGE_ON_CLASS);
					BX.Dom.addClass(this.icon, O_HEART_CLASS);
					BX.Dom.removeClass(this.icon, HEART_CLASS);
					if (this.currentCategory === 'favourite')
					{
						const mainInstance = BX.Landing.Main.getInstance();
						const blocksPanelContent = mainInstance.getBlocksPanelContent();
						BX.Dom.remove(this.layout);
						const hasCards = blocksPanelContent.querySelector('.landing-ui-card-block-preview');
						if (!hasCards)
						{
							BX.Dom.append(
								mainInstance.createFavouriteCategoryEmptyState(),
								blocksPanelContent,
							);
						}
					}
				}
				else
				{
					action = ACTION_ADD;
					BX.Dom.addClass(this.favoriteBadge, FAVOURITE_BADGE_ON_CLASS);
					BX.Dom.removeClass(this.icon, O_HEART_CLASS);
					BX.Dom.addClass(this.icon, HEART_CLASS);
				}
				const type = BX.Landing.Env.getInstance().getType();

				BX.Landing.Backend.getInstance()
					.action('Landing::markFavouriteBlock', { codeBlock: this.code, action, type })
					.then((result) => {
						if (action === ACTION_REMOVE)
						{
							this.showFavoriteNotification(BX.Landing.Loc.getMessage('LANDING_SECTION_FAVOURITE_BALLOON_REMOVE'));
						}
						if (action === ACTION_ADD)
						{
							this.showFavoriteNotification(BX.Landing.Loc.getMessage('LANDING_SECTION_FAVOURITE_BALLOON_ADD'));
						}

						return result;
					});
			});
		}

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
			const marketLabel = BX.create('div', {
				props: {
					className: 'landing-ui-card-label landing-ui-card-label-repo'
				},
				text: BX.Landing.Loc.getMessage('LANDING_BLOCKS_LIST_PREVIEW_MARKET_MSGVER_1'),
				dataset: {
					hint: BX.Landing.Loc.getMessage('LANDING_BLOCKS_LIST_PREVIEW_MARKET_HINT_MSGVER_1'),
					hintNoIcon: 'Y',
				},
			});

			BX.append(marketLabel, this.labels);
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
				const deleteMyButton = this.getRemoveButton();
				deleteMyButton.onclick = event => {
					event.stopPropagation();
					BX.UI.Dialogs.MessageBox.show({
						message: BX.Landing.Loc.getMessage("LANDING_BLOCKS_LIST_PREVIEW_DELETE_MSG"),
						buttons: BX.UI.Dialogs.MessageBoxButtons.YES_CANCEL,
						popupOptions: {
							targetContainer: parent.document.body
						},
						onYes: () => {
							return BX.Landing.Backend.getInstance()
								.action(
									"Landing::unFavoriteBlock",
									{blockId: blockId}
								)
								.then(() => {
									const mainInstance = BX.Landing.Main.getInstance();
									mainInstance.removeBlockFromList(this.code);
									return true;
								})
								.catch(function(error) {
									console.log("error", error);
									return false;
								});
						},
					});
				};
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
			addClass(this.layout, 'landing-ui-requires-update');

			const overlay = create('div', {
				props: {
					className: 'landing-ui-requires-update-overlay',
				},
				children: [
					create('div', {
						props: {
							className: 'landing-ui-requires-update-overlay-footer',
						},
						html: BX.Landing.Loc.getMessage('LANDING_BLOCK_REQUIRES_UPDATE_MESSAGE'),
					}),
				],
			});

			append(overlay, this.imageContainer);

			this.onClickHandler = (function() {});
		}
	};

	BX.Landing.UI.Card.BlockPreviewCard.prototype = {
		constructor: BX.Landing.UI.Card.BlockPreviewCard,
		__proto__: BX.Landing.UI.Card.BaseCard.prototype,

		onAppend: function()
		{
			if (this.labels !== undefined)
			{
				let hint = BX.UI.Hint;
				if (
					this.labels.ownerDocument.defaultView.BX
					&& this.labels.ownerDocument.defaultView.BX.UI
					&& this.labels.ownerDocument.defaultView.BX.UI.Hint
				)
				{
					hint = this.labels.ownerDocument.defaultView.BX.UI.Hint;
				}
				hint.init(this.labels);
			}
		},
	};
})();