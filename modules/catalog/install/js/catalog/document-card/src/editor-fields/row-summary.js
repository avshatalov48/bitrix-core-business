import {Loc} from 'main.core';
import {EventEmitter} from "main.core.events";
import {CurrencyCore} from "currency.currency-core";

/**
 * @deprecated Use BX.UI.EntityEditorProductRowSummary instead
 */
export default class ProductRowSummary extends BX.UI.EntityEditorField
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);

		this._loader = null;
		this._productsContainer = null;
		this._previousData = [];

		this._itemCount = 0;
		this._totalCount = 0;

		this._moreButton = null;
		this._moreButtonRow = null;
		this._totalsRow = null;
		this._moreButtonClickHandler = BX.delegate(this._onMoreButtonClick, this);

		this._visibleItemsLimit = 5;
	}

	layout(options = {})
	{
		if(this._hasLayout)
		{
			return;
		}
		this.ensureWrapperCreated({});
		this.adjustWrapper();

		var data = this.getValue();

		if(!BX.type.isPlainObject(data))
		{
			return;
		}

		var title = this.getTitle();
		var items = BX.prop.getArray(data, 'items', []);

		this._totalCount = BX.prop.getInteger(data, 'count', 0);

		this._itemCount = items.length;
		var length = this._itemCount;
		var maxLength = this._visibleItemsLimit;
		var restLength = 0;
		if(
			(length > maxLength)
		)
		{
			restLength = (this._totalCount - maxLength);
			length = maxLength;
		}

		if (this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		this._wrapper.appendChild(this.createTitleNode(title));
		this._productsContainer = BX.create(
			'div',
			{
				props: {
					className: 'catalog-entity-widget-content-block-products-list'
				}
			}
		);

		for (var i = 0; i < length; i++)
		{
			this.addProductRow(items[i]);
		}

		this._moreButton = null;
		if (restLength > 0)
		{
			this.addMoreButton(restLength);
		}
		this.addTotalRow(data['total']);

		this._wrapper.appendChild(
			BX.create(
				'div',
				{
					props: {className: 'catalog-entity-widget-content-block-products'},
					children: [this._productsContainer]
				}
			)
		);

		if (this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if (this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	}

	addMoreButton(restLength)
	{
		var row = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-item'
			}
		});
		this._moreButtonRow = row;
		this._productsContainer.appendChild(row);

		var nameCell = BX.create("div", {
			props: {
				className: 'catalog-entity-widget-content-block-products-item-name'
			}
		});
		row.appendChild(nameCell);

		this._moreButton = BX.create(
			'span',
			{
				attrs: {
					className: 'catalog-entity-widget-content-block-products-show-more'
				},
				events: {
					click: this._moreButtonClickHandler
				},
				text: Loc.getMessage('DOCUMENT_PRODUCTS_NOT_SHOWN', {'#COUNT#': restLength.toString()}),
			}
		);
		nameCell.appendChild(this._moreButton);

		row.appendChild(
			BX.create('div', {
				props: {
					className: 'catalog-entity-widget-content-block-products-price'
				}
			})
		);
	}

	addTotalRow(total)
	{
		var row = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-item'
			}
		});
		this._totalsRow = row;
		this._productsContainer.appendChild(row);
		var nameCell = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-item-name'
			},
			html: Loc.getMessage('DOCUMENT_PRODUCTS_TOTAL'),
		});
		row.appendChild(nameCell);

		var valueCell = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-price'
			},
			html: CurrencyCore.currencyFormat(total.amount, total.currency, true),
		});
		row.appendChild(valueCell);
	}

	addAddProductButton()
	{
		let addProductsLink = BX.create(
			'a',
			{
				props: {href: '#'}
			}
		);
		addProductsLink.text = Loc.getMessage('DOCUMENT_PRODUCTS_ADD_PRODUCT');
		addProductsLink.onclick = () => {
			EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'tab_products'})
		};
		let row = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-add-products',
			},
			children: [addProductsLink],
		});
		this._productsContainer.appendChild(row);
	}

	_onMoreButtonClick(e)
	{
		EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'tab_products'});
	}

	doClearLayout()
	{
		this._productsContainer = null;
		this._moreButton = null;
		this._moreButtonRow = null;
		this._totalsRow = null;
	}

	addProductRow(data)
	{
		var row = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-item'
			}
		});

		this._productsContainer.appendChild(row);

		var nameCell = BX.create('div', {
			props: {
				className: 'catalog-entity-widget-content-block-products-item-name'
			}
		});
		nameCell.innerHTML = BX.util.htmlspecialchars(data['PRODUCT_NAME']);
		row.appendChild(nameCell);

		var valueCell = BX.create(
			'div',
			{
				props: {
					className: 'catalog-entity-widget-content-block-products-price'
				}
			}
		);
		row.appendChild(valueCell);

		valueCell.appendChild(
			BX.create(
				'div',
				{
					attrs: {
						className: 'catalog-entity-widget-content-block-products-price-value'
					},
					html: data['SUM']
				}
			)
		);
	}
}
