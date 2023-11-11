import type { BaseEvent } from 'main.core.events';
import { EventEmitter } from 'main.core.events';
import { Dom, Text, Type, Uri } from 'main.core';
import { IblockProductListHints } from './iblock-product-list-hints';
import './style.css';

export class IblockProductList
{
	/**
	 * @type {?BX.Main.grid}
	 */
	grid;
	hints;
	variations = new Map();
	variationsEditData = new Map();
	editedVariations = new Map();
	morePhotoChangedInputs = new Map();

	onSettingsWindowSaveHandler = this.handleOnSettingsWindowSave.bind(this);
	onChangeVariationHandler = this.handleOnChangeVariation.bind(this);
	onBeforeGridRequestHandler = this.handleOnBeforeGridRequest.bind(this);
	onFilterApplyHandler = this.handleOnFilterApply.bind(this);
	onSaveImageHandler = this.handleOnSaveImage.bind(this);

	constructor(options = {})
	{
		this.gridId = options.gridId;
		this.rowIdMask = options.rowIdMask ?? '#ID#';
		this.variationFieldNames = options.variationFieldNames ?? [];
		this.productVariationMap = options.productVariationMap ?? {};
		this.createNewProductHref = options.createNewProductHref ?? '';
		this.showCatalogWithOffers = options.showCatalogWithOffers ?? false;

		this.addCustomClassToGrid();
		this.cacheSelectedVariation();

		EventEmitter.subscribe('BX.Grid.SettingsWindow:save', this.onSettingsWindowSaveHandler);
		EventEmitter.subscribe('SkuProperty::onChange', this.onChangeVariationHandler);
		EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);
		EventEmitter.subscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.handlerOnSliderMessage.bind(this));

		this.hints = new IblockProductListHints(options);
	}

	addCustomClassToGrid()
	{
		Dom.addClass(this.getGrid().getContainer(), 'catalog-product-grid');
	}

	cacheSelectedVariation()
	{
		this.getGrid().getRows().getBodyChild().forEach((row) => {
			const rowId = row.getId();
			const productId = this.getProductIdByRowId(rowId);
			const variationId = this.getCurrentVariationIdByProduct(productId);

			if (variationId)
			{
				this.variations.set(variationId, row.getNode().cloneNode(true));
				this.variationsEditData.set(variationId, row.getEditData());
			}
		});
	}

	clearAllVariationCache()
	{
		this.variations.clear();
		this.variationsEditData.clear();
		this.editedVariations.clear();
		this.morePhotoChangedInputs.clear();
	}

	clearVariationCache(variationId)
	{
		this.variations.delete(variationId);
		this.variationsEditData.delete(variationId);
		this.editedVariations.delete(variationId);
	}

	/**
	 * @returns {?BX.Main.grid}
	 */
	getGrid()
	{
		if (!this.grid)
		{
			this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		}

		return this.grid;
	}

	handleOnSettingsWindowSave(event: BaseEvent)
	{
		const [settingsWindow] = event.getCompatData();
		const selectedColumns = settingsWindow.getSelectedColumns();
		this.showCatalogWithOffers = selectedColumns.includes('CATALOG_PRODUCT');
	}

	handleOnChangeVariation(event: BaseEvent)
	{
		const [skuFields] = event.getData();
		const productId = Text.toNumber(skuFields.PARENT_PRODUCT_ID);
		const variationId = Text.toNumber(skuFields.ID);

		if (productId <= 0 || variationId <= 0)
		{
			return;
		}

		const productRow = this.getProductRow(productId);

		if (productRow.isEdit())
		{
			const values = this.getEditedVariationValues(productRow);
			const currentVariationId = this.getCurrentVariationIdByProduct(productId);
			this.editedVariations.set(currentVariationId, values);
		}

		if (productRow.isEdit() && this.editedVariations.has(variationId))
		{
			const editData = Object.assign(productRow.getEditData(), this.editedVariations.get(variationId));
			productRow.setEditData(editData);
			productRow.editCancel();
			productRow.edit();
			this.productVariationMap[productId] = variationId;

			return;
		}

		this.getVariation(productId, variationId)
			.then((variationNode) => {
				this.updateProductRow(productId, variationId, variationNode);
				this.productVariationMap[productId] = variationId;
			});
	}

	getEditedVariationValues(row: BX.Grid.Row)
	{
		const currentEditorValues = row.getEditorValue();
		const headRow = this.getHeadRow();
		const values = {};
		let morePhotoHtml = null;
		[...row.getCells()].forEach((cell, index) => {
			const cellName = headRow.getCellNameByCellIndex(index);
			if (cellName !== 'MORE_PHOTO')
			{
				return;
			}
			const editorContainer = row.getEditorContainer(cell);
			if (editorContainer)
			{
				const imageBlock = editorContainer.querySelector('.catalog-image-input-wrapper');
				const id = imageBlock.id;
				if (this.morePhotoChangedInputs.has(id))
				{
					morePhotoHtml = this.morePhotoChangedInputs.get(id);
				}
				else
				{
					morePhotoHtml = imageBlock.outerHTML;
				}
			}
		});

		for (const name in currentEditorValues)
		{
			if (!currentEditorValues.hasOwnProperty(name) || !this.variationFieldNames.includes(name))
			{
				continue;
			}

			if (name === 'MORE_PHOTO' && !Type.isNil(morePhotoHtml))
			{
				values[name] = morePhotoHtml;
			}
			else
			{
				values[name] = currentEditorValues[name];
			}
		}

		return values;
	}

	getVariation(productId, variationId)
	{
		if (this.getProductRow(productId).isEdit() && this.editedVariations.has(variationId))
		{
			return Promise.resolve(this.editedVariations.get(variationId));
		}

		if (this.variations.has(variationId))
		{
			return Promise.resolve(this.variations.get(variationId));
		}

		return new Promise((resolve) => {
			this.loadVariation(productId, variationId, resolve);
		})
			.then((variation) => {
				if (Type.isDomNode(variation))
				{
					this.variations.set(variationId, variation);

					return variation;
				}

				return null;
			});
	}

	loadVariation(productId, variationId, resolve)
	{
		const self = this;
		const url = '';
		const method = 'POST';
		const data = { productId, variationId };

		this.getProductRow(productId).stateLoad();
		this.getGrid().getData().request(url, method, data, 'changeVariation', function() {
			EventEmitter.emit('Grid::updated', [self.getGrid()]);
			const row = self.getProductRow(productId);
			if (row)
			{
				row.stateUnload();
				resolve(this.getRowById(row.getId()));
			}
		});
	}

	getProductIdByRowId(rowId)
	{
		const mask = new RegExp(this.rowIdMask.replace('#ID#', '([0-9]+)'));
		const matches = rowId.match(mask);

		return Type.isArray(matches) ? Text.toNumber(matches[1]) : 0;
	}

	getRowIdByProductId(id)
	{
		return this.rowIdMask.replace('#ID#', id);
	}

	/**
	 * @param id
	 * @returns {?BX.Grid.Row}
	 */
	getProductRow(id)
	{
		const rowId = this.getRowIdByProductId(id);

		return this.getGrid().getRows().getById(rowId);
	}

	/**
	 * @returns {?BX.Grid.Row}
	 */
	getHeadRow()
	{
		return this.getGrid().getRows().getHeadFirstChild();
	}

	updateProductRow(productId, variationId, variationNode)
	{
		if (!productId || !Type.isDomNode(variationNode))
		{
			return;
		}

		const headRow = this.getHeadRow();
		const productRow = this.getProductRow(productId);
		const fields = {};

		[...variationNode.cells].forEach((cell, index) => {
			const cellName = headRow.getCellNameByCellIndex(index);

			if (this.variationFieldNames.includes(cellName))
			{
				const columnCell = productRow.getCellByIndex(index);
				if (columnCell)
				{
					const cellHtml = productRow.getContentContainer(cell).innerHTML;
					productRow.getContentContainer(columnCell).innerHTML = cellHtml;
					fields[cellName] = cellHtml;
				}
			}
		});

		if (this.variationsEditData.has(variationId))
		{
			productRow.setEditData(this.variationsEditData.get(variationId));
		}
		else
		{
			productRow.resetEditData();
			this.variationsEditData.set(variationId, productRow.getEditData());
		}

		if (productRow.isEdit())
		{
			productRow.editCancel();
			productRow.edit();
		}
	}

	handleOnBeforeGridRequest(event: BaseEvent)
	{
		const [, gridData] = event.getData();
		const submitData = BX.prop.get(gridData, 'data', {});

		// reload settings, columns or something else
		if (!submitData.productId && !submitData.FIELDS)
		{
			this.clearAllVariationCache();
		}

		if (submitData.FIELDS)
		{
			this.editedVariations.forEach((editFields, variationId) => {
				const rowId = this.getRowIdByProductId(variationId);
				submitData.FIELDS[rowId] = submitData.FIELDS[rowId] || {};
				Object.keys(editFields).map((cellName) => {
					if (cellName.includes('CATALOG_GROUP_'))
					{
						const groupPriceId = cellName.replace('CATALOG_GROUP_', '');
						if (!Type.isNil(editFields[cellName].PRICE))
						{
							submitData.CATALOG_PRICE = submitData.CATALOG_PRICE || {};
							submitData.CATALOG_PRICE[variationId] = submitData.CATALOG_PRICE[variationId] || {};
							submitData.CATALOG_PRICE[variationId][groupPriceId] = editFields[cellName].PRICE.VALUE;
						}

						if (!Type.isNil(editFields[cellName].CURRENCY))
						{
							submitData.CATALOG_CURRENCY = submitData.CATALOG_CURRENCY || {};
							submitData.CATALOG_CURRENCY[variationId] = submitData.CATALOG_CURRENCY[variationId] || {};
							submitData.CATALOG_CURRENCY[variationId][groupPriceId] = editFields[cellName].CURRENCY.VALUE;
						}
					}
					else if (cellName !== 'MORE_PHOTO' && cellName !== 'MORE_PHOTO_custom')
					{
						submitData.FIELDS[rowId][cellName] = editFields[cellName];
					}
				});

				this.clearVariationCache(variationId);
			});

			for (const rowId in submitData.FIELDS)
			{
				if (!submitData.FIELDS.hasOwnProperty(rowId))
				{
					continue;
				}

				const productId = this.getProductIdByRowId(rowId);
				const variationId = this.getCurrentVariationIdByProduct(productId);
				const newFilesRegExp = new RegExp(/(\w+?(_n\d+)*)\[([A-Z_a-z]+)]/);
				const rowFields = submitData.FIELDS[rowId];
				const morePhotoValues = {};
				if (!Type.isNil(rowFields.MORE_PHOTO_custom))
				{
					for (const key in rowFields.MORE_PHOTO_custom)
					{
						if (!rowFields.MORE_PHOTO_custom.hasOwnProperty(key))
						{
							continue;
						}

						const inputValue = rowFields.MORE_PHOTO_custom[key];
						if (!Type.isNil(inputValue))
						{
							if (newFilesRegExp.test(inputValue.name))
							{
								let fileCounter; let
									fileSetting;
								[, fileCounter, , fileSetting] = inputValue.name.match(newFilesRegExp);
								if (fileCounter && fileSetting)
								{
									morePhotoValues[fileCounter] = morePhotoValues[fileCounter] || {};
									morePhotoValues[fileCounter][fileSetting] = inputValue.value;
								}
							}
							else
							{
								morePhotoValues[inputValue.name] = inputValue.value;
							}
						}
					}
				}
				rowFields.MORE_PHOTO = morePhotoValues;
				if (variationId && this.showCatalogWithOffers)
				{
					const variationRowId = this.getRowIdByProductId(variationId);
					// clear old cache
					this.clearVariationCache(variationId);

					submitData.FIELDS[variationRowId] = {};

					for (const fieldName of this.variationFieldNames)
					{
						if (!rowFields.hasOwnProperty(fieldName))
						{
							continue;
						}

						submitData.FIELDS[variationRowId][fieldName] = rowFields[fieldName];
						delete submitData.FIELDS[rowId][fieldName];
					}
				}
			}

			this.morePhotoChangedInputs.clear();
		}
	}

	getCurrentVariationIdByProduct(productId)
	{
		return productId in this.productVariationMap ? Text.toNumber(this.productVariationMap[productId]) : null;
	}

	handleOnFilterApply(event: BaseEvent)
	{
		const data = event.getData();
		const filterGridId = data[0];
		const filter = data[2] instanceof BX.Main.Filter ? event.getData()[2] : null;

		if (filter && (filterGridId === this.gridId))
		{
			const filterFields = this.getFilterFields(filter);

			let sectionId = '0';

			if (Type.isArray(filterFields))
			{
				const fieldSectionId = this.getFieldSectionId(filterFields);

				if (fieldSectionId)
				{
					const value = fieldSectionId.VALUE;

					if (Type.isObject(value))
					{
						sectionId = value.VALUE;
					}
				}
			}

			this.setNewProductButtonHrefSectionId(sectionId);
		}
	}

	handleOnSaveImage(event: BaseEvent)
	{
		const [id, inputId, response] = event.getData();
		this.morePhotoChangedInputs.set(id, response.data.input);
	}

	handlerOnSliderMessage(event: BaseEvent)
	{
		const [sliderEvent] = event.getCompatData();

		if (
			sliderEvent.getEventId() === 'Catalog.ProductCard::onCreate'
			|| sliderEvent.getEventId() === 'Catalog.ProductCard::onUpdate'
			// compatibility for admin forms
			|| sliderEvent.getEventId() === 'save'
			|| sliderEvent.getEventId() === 'apply'
		)
		{
			this.getGrid().reload();
		}
	}

	getFilterFields(filter: BX.Main.Filter)
	{
		const presets = filter.getParam('PRESETS');

		let tmpFilterPreset = null;

		if (Type.isArray(presets))
		{
			tmpFilterPreset = presets.find((preset) => {
				return preset.ID === 'tmp_filter';
			});
		}

		if (tmpFilterPreset)
		{
			return tmpFilterPreset.FIELDS || null;
		}

		return null;
	}

	getFieldSectionId(fields: Array)
	{
		return fields.find((field) => {
			return field.ID === 'field_SECTION_ID';
		});
	}

	setNewProductButtonHrefSectionId(sectionId: String)
	{
		const nodes = document.querySelectorAll('[data-grid-create-button]');
		if (nodes.length === 0)
		{
			return;
		}

		const buttonContainer = Array.prototype.find.call(nodes, (item) => item.dataset.gridCreateButton === this.gridId);
		if (Type.isDomNode(buttonContainer))
		{
			const buttonObject = BX.UI.ButtonManager.getByUniqid(buttonContainer.dataset.btnUniqid);
			if (!buttonObject)
			{
				return;
			}

			// main
			const mainButton = buttonObject.getMainButton();
			if (mainButton && mainButton.getLink())
			{
				const uri = new Uri(mainButton.getLink());
				uri.setQueryParams({
					IBLOCK_SECTION_ID: sectionId,
				});

				mainButton.setLink(uri.toString());
			}

			// menu
			buttonObject.getMenuWindow()?.getMenuItems()?.forEach((item) => {
				const link = item.getLayout().item;
				if (link.tagName === 'A')
				{
					const uri = new Uri(link.href);
					uri.setQueryParams({
						IBLOCK_SECTION_ID: sectionId,
					});

					link.href = uri.toString();
				}
			});
		}
	}
}
