import { EventEmitter } from 'main.core.events';
import { Tag, Loc } from 'main.core';

export class IblockProductListHints
{
	/**
	 * @type {?BX.Main.grid}
	 */
	grid;
	variations = new Map();
	variationsEditData = new Map();
	editedVariations = new Map();
	morePhotoChangedInputs = new Map();
	canEditPrice;

	constructor(options = {})
	{
		this.gridId = options.gridId;
		this.canEditPrice = options.canEditPrice === undefined ? true : options.canEditPrice === true;

		this.#addAccessDeniedHintForCreateButton();
		this.handleOnGridUpdatedHandler();

		EventEmitter.subscribe('Grid::updated', this.handleOnGridUpdatedHandler.bind(this));
	}

	getGrid(): BX.Main.grid
	{
		if (!this.grid)
		{
			this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		}

		return this.grid;
	}

	handleOnGridUpdatedHandler()
	{
		if (!this.canEditPrice)
		{
			this.#addAccessDeniedHintForPriceColumns();
		}
	}

	#addAccessDeniedHintForPriceColumns(): void
	{
		this.getGrid().getHeaders().forEach((header) => {
			const cellsTitles = header.querySelectorAll(
				'.main-grid-cell-head[data-name^="CATALOG_GROUP_"] .main-grid-head-title, .main-grid-cell-head[data-name^="PRICE_"] .main-grid-head-title',
			);
			cellsTitles.forEach((title) => {
				const lockIcon = Tag.render`
					<span class="ui-btn ui-btn-link ui-btn-icon-lock ui-btn-xs catalog-product-grid-lock-hint"></span>
				`;
				lockIcon.dataset.hint = Loc.getMessage('CATALOG_IBLOCK_PRODUCT_LIST_PRICE_ACCESS_DENIED_HINT');
				lockIcon.dataset.hintNoIcon = true;

				BX.UI.Hint.createInstance({}).initNode(lockIcon);

				title.prepend(lockIcon);
			});
		});
	}

	#addAccessDeniedHintForCreateButton(): void
	{
		const button = document.querySelector('#create_new_product_button_access_denied');
		if (button)
		{
			button.classList.add('ui-btn-icon-lock', 'ui-btn-disabled');
			button.dataset.hint = Loc.getMessage('CATALOG_IBLOCK_PRODUCT_LIST_CREATE_ACCESS_DENIED_HINT');
			button.dataset.hintNoIcon = true;

			BX.UI.Hint.createInstance({}).initNode(button);
		}
	}
}
