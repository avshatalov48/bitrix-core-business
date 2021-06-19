import {Dom, Reflection, Type} from "main.core";
import type {BaseEvent} from "main.core.events";
import {EventEmitter} from "main.core.events";
import {ProductSelector} from 'catalog.product-selector';

const instances = new Map();

class ProductField
{
	static EDIT_CLASS = 'catalog-grid-product-field-edit';
	static PRODUCT_MODE = 'product';
	static SKU_MODE = 'sku';

	onSelectEditHandler = this.onSelectEdit.bind(this);
	onCancelEditHandler = this.onCancelEdit.bind(this);
	onBeforeGridRequestHandler = this.onBeforeGridRequest.bind(this);
	onUnsubscribeEventsHandler = this.unsubscribeEvents.bind(this);

	static getById(id: string): ?ImageInput
	{
		return instances.get(id) || null;
	}

	constructor(id, settings = {})
	{
		this.selector = new ProductSelector(id, settings);
		this.componentName = settings.componentName || '';
		this.signedParameters = settings.signedParameters || '';
		this.rowIdMask = settings.rowIdMask || '#ID#';

		this.subscribeEvents();

		instances.set(id, this);
	}

	subscribeEvents()
	{
		EventEmitter.subscribe('Grid::thereEditedRows', this.onSelectEditHandler);
		EventEmitter.subscribe('Grid::noEditedRows', this.onCancelEditHandler);
		EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
		EventEmitter.subscribe('Grid::updated', this.onUnsubscribeEventsHandler);
	}

	unsubscribeEvents()
	{
		EventEmitter.unsubscribe('Grid::thereEditedRows', this.onSelectEditHandler);
		EventEmitter.unsubscribe('Grid::noEditedRows', this.onCancelEditHandler);
		EventEmitter.unsubscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
		EventEmitter.unsubscribe('Grid::updated', this.onUnsubscribeEventsHandler);
		this.selector.unsubscribeEvents();
	}

	getSelector(): ProductSelector
	{
		return this.selector;
	}

	onBeforeGridRequest(event: BaseEvent)
	{
		const wrapper = this.getSelector().getWrapper();
		if (!wrapper)
		{
			return;
		}

		const [, gridData] = event.getData();
		const submitData = BX.prop.get(gridData, 'data', {});
		if (!submitData.FIELDS)
		{
			return;
		}

		let productId = this.getSelector().getModel().getProductId();
		productId = this.rowIdMask.replace('#ID#', productId);

		submitData.FIELDS[productId] = submitData.FIELDS[productId] || {};

		const imageInputContainer = wrapper.querySelector('.ui-image-input-container');
		if (imageInputContainer)
		{
			const inputs = imageInputContainer.querySelectorAll('input');
			const values = {};
			const newFilesRegExp = new RegExp(/([0-9A-Za-z_]+?(_n\d+)*)\[([A-Za-z_]+)\]/);
			for (let inputItem of inputs)
			{
				if (newFilesRegExp.test(inputItem.name))
				{
					let [, fileCounter, code, fileSetting] = inputItem.name.match(newFilesRegExp);
					if (fileCounter && fileSetting)
					{
						values[fileCounter] = values[fileCounter] || {};
						values[fileCounter][fileSetting] = inputItem.value;
					}
				}
				else
				{
					values[inputItem.name] = inputItem.value;
				}
			}
			submitData.FIELDS[productId] = submitData.FIELDS[productId] || {};
			if (Object.keys(values).length > 0)
			{
				submitData.FIELDS[productId]['MORE_PHOTO'] = values;
			}
		}

		const productNameInput = wrapper.querySelector('input[name="NAME"]');
		if (productNameInput)
		{
			submitData.FIELDS[productId]['NAME'] = productNameInput.value;
		}
	}

	onCancelEdit()
	{
		this.getSelector().setMode(BX.Catalog.ProductSelector.MODE_VIEW);
		this.getSelector().clearLayout();
		this.getSelector().layout();

		const grid = BX.Main.gridManager.getInstanceById(this.getSelector().getConfig('GRID_ID'));
		if (!grid)
		{
			return;
		}

		const row = grid.getRows().getById(this.selector.getConfig('ROW_ID'));
		if (!row)
		{
			return;
		}

		const cell = row.getCellById('CATALOG_PRODUCT');
		if (cell)
		{
			Dom.removeClass(row.getContentContainer(cell), ProductField.EDIT_CLASS);
		}
	}

	onSelectEdit()
	{
		if (!this.getSelector().getConfig('GRID_ID', null))
		{
			return;
		}

		const grid = BX.Main.gridManager.getInstanceById(this.getSelector().getConfig('GRID_ID'));
		if (!grid)
		{
			return;
		}

		const row = grid.getRows().getById(this.selector.getConfig('ROW_ID'));
		if (row && row.isEdit())
		{
			this.getSelector().setMode(BX.Catalog.ProductSelector.MODE_EDIT);
			this.getSelector().clearLayout();
			this.getSelector().layout();

			const cell = row.getCellById('CATALOG_PRODUCT');
			if (cell)
			{
				Dom.addClass(row.getContentContainer(cell), ProductField.EDIT_CLASS);
			}
		}
	}
}

Reflection.namespace('BX.Catalog.Grid').ProductField = ProductField;