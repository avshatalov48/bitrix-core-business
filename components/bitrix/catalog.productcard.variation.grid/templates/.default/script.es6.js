import {Dom, Event, Loc, Reflection, Runtime, Tag, Text, Type, Uri} from 'main.core';
import {typeof BaseEvent, EventEmitter} from 'main.core.events';
import {MenuManager, Popup, PopupManager} from 'main.popup';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {TagSelector} from "ui.entity-selector";

const GRID_TEMPLATE_ROW = 'template_0';

class VariationGrid
{
	grid = null;
	isNew = false;
	propertiesWithMenu = []

	constructor(settings = {})
	{
		this.createPropertyId = settings.createPropertyId;
		this.createPropertyHintId = settings.createPropertyHintId;
		this.gridId = settings.gridId;
		this.isNew = settings.isNew;
		this.hiddenProperties = settings.hiddenProperties;
		this.modifyPropertyLink = settings.modifyPropertyLink;
		this.gridEditData = settings.gridEditData;
		this.canHaveSku = settings.canHaveSku || false;
		this.storeAmount = settings.storeAmount;
		this.isShowedStoreReserve = settings.isShowedStoreReserve;
		this.reservedDealsSliderLink = settings.reservedDealsSliderLink;
		if (settings.copyItemsMap)
		{
			this.getGrid().arParams.COPY_ITEMS_MAP = settings.copyItemsMap;
		}

		const isGridReload = settings.isGridReload || false;
		if (!isGridReload)
		{
			this.addCustomClassToGrid();
			this.bindCreateNewVariation();
			this.bindCreateSkuProperty();
			this.clearGridSettingsPopupStuff();
		}

		const gridEditData = settings.gridEditData || null;
		if (gridEditData)
		{
			this.setGridEditData(gridEditData);
		}

		if (this.isNew)
		{
			this.enableEdit();
			this.prepareNewNodes();
			this.getGrid().updateCounterSelected();
			this.getGrid().disableCheckAllCheckboxes();
		}
		else
		{
			this.bindInlineEdit();
			this.bindPopupInitToQuantityNodes();
			this.bindSliderToReservedQuantityNodes();
		}

		Event.bind(this.getGrid().getScrollContainer(), 'scroll', Runtime.throttle(this.onScrollHandler.bind(this), 50));
		Event.bind(this.getGridSettingsButton(), 'click', this.showGridSettingsWindowHandler.bind(this));

		this.subscribeCustomEvents();
	}

	subscribeCustomEvents()
	{
		this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);

		this.onPropertySaveHandler = this.onPropertySave.bind(this);
		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onPropertySaveHandler);

		this.onAllRowsSelectHandler = this.enableEdit.bind(this)
		EventEmitter.subscribe('Grid::allRowsSelected', this.onAllRowsSelectHandler);

		this.onAllRowsUnselectHandler = this.disableEdit.bind(this);
		EventEmitter.subscribe('Grid::allRowsUnselected', this.onAllRowsUnselectHandler);

		this.showPropertySettingsSliderHandler = this.showPropertySettingsSlider.bind(this);
		EventEmitter.subscribe('VariationGrid::propertyModify', this.showPropertySettingsSliderHandler);

		this.onPrepareDropDownItemsHandler = this.onPrepareDropDownItems.bind(this);
		EventEmitter.subscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);

		this.onCreatePopupHandler = this.onCreatePopup.bind(this);
		EventEmitter.subscribe('UiSelect::onCreatePopup', this.onCreatePopupHandler);
	}

	destroy()
	{
		this.unsubscribeCustomEvents();
		this.destroyStoreAmountPopups();
	}

	unsubscribeCustomEvents()
	{
		if (this.onGridUpdatedHandler)
		{
			EventEmitter.unsubscribe('Grid::updated', this.onGridUpdatedHandler);
			this.onGridUpdatedHandler = null;
		}

		if (this.onPropertySaveHandler)
		{
			EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.onPropertySaveHandler);
			this.onPropertySaveHandler = null;
		}

		if (this.showPropertySettingsSliderHandler)
		{
			EventEmitter.unsubscribe('VariationGrid::propertyModify', this.showPropertySettingsSliderHandler);
			this.showPropertySettingsSliderHandler = null;
		}

		if (this.onPrepareDropDownItemsHandler)
		{
			EventEmitter.unsubscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);
			this.onPrepareDropDownItemsHandler = null;
		}

		if (this.onAllRowsSelectHandler)
		{
			EventEmitter.unsubscribe('Grid::allRowsSelected', this.onAllRowsSelectHandler);
			this.onAllRowsSelectHandler = null;
		}

		if (this.onAllRowsUnselectHandler)
		{
			EventEmitter.unsubscribe('Grid::allRowsUnselected', this.onAllRowsUnselectHandler);
			this.onAllRowsUnselectHandler = null;
		}

		if (this.onCreatePopupHandler)
		{
			EventEmitter.unsubscribe('UiSelect::onCreatePopup', this.onCreatePopupHandler);
			this.onCreatePopupHandler = null;
		}
	}

	getGridSettingsButton()
	{
		return this.getGrid().getContainer().querySelector('.' + this.getGrid().settings.get('classSettingsButton'))
	}

	showGridSettingsWindowHandler(event)
	{
		event.preventDefault();
		event.stopPropagation();

		this.askToLossGridData(() => {
			this.getGrid().getSettingsWindow()._onSettingsButtonClick();
		});
	}

	onScrollHandler(event)
	{
		const popup = PopupManager.getCurrentPopup();
		if (popup)
		{
			popup.close();
		}

		this.propertiesWithMenu.forEach(propertyId => {
			let menu = MenuManager.getMenuById(propertyId + '_menu');
			if (menu)
			{
				menu.close();
			}
		})
	}

	onPrepareDropDownItems(event)
	{
		const [controlId, menuId, items] = event.getData();
		if (!Type.isStringFilled(controlId))
		{
			return;
		}

		this.propertiesWithMenu.push(controlId);

		if (controlId.indexOf('SKU_GRID_PROPERTY_') === -1)
		{
			return;
		}

		if (!Type.isArray(items))
		{
			return;
		}

		const actionList = items.filter((item) => {
			return (item.action === 'create-new');
		});

		if (actionList.length > 0)
		{
			return;
		}

		const propertyId = controlId.replace('SKU_GRID_PROPERTY_', '').replace('_control', '');

		items.push({
			'action': 'create-new',
			'html': `
				<li data-role="createItem" class="catalog-productcard-popup-select-item catalog-productcard-popup-select-item-new">
					<label class="catalog-productcard-popup-select-label main-dropdown-item" data-pseudo="true">
						<span class="catalog-productcard-popup-select-add"></span>
						<span class="catalog-productcard-popup-select-text">
							${Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON')}
						</span>
					</label>
				</li>`,
			'onclick': () => BX.Catalog.VariationGrid.firePropertyModification(propertyId, menuId)
		});

		requestAnimationFrame(function() {
			const popup = document.getElementById('menu-popup-' + menuId);
			Dom.addClass(popup, 'catalog-productcard-popup-list');
		});
	}

	onCreatePopup(event)
	{
		const [popup] = event.getData();
		const bindElementId = popup?.bindElement?.id;

		if (!Type.isStringFilled(bindElementId))
		{
			return;
		}

		if (bindElementId.indexOf('SKU_GRID_PROPERTY_') === -1)
		{
			return;
		}

		const propertyId = bindElementId.replace('SKU_GRID_PROPERTY_', '').replace('_control', '');

		const addButton = Tag.render`
			<div class="catalog-productcard-popup-select-item catalog-productcard-popup-multi-select-item-new">
				<label 
					class="catalog-productcard-popup-select-label main-dropdown-item">
					<span class="catalog-productcard-popup-select-add"></span>
					<span class="catalog-productcard-popup-select-text">
						${Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON')}
					</span>
				</label>
			</div>
		`;
		Event.bind(addButton, 'mousedown', BX.Catalog.VariationGrid.firePropertyModification.bind(this, propertyId));

		popup.contentContainer.appendChild(addButton);
	}

	clearGridSettingsPopupStuff()
	{
		Dom.remove(document.getElementById(this.gridId + '-grid-settings-window'));
	}

	bindCreateNewVariation()
	{
		if (!this.canHaveSku)
		{
			return;
		}

		const addRowButton = document.querySelector('[data-role="catalog-productcard-variation-add-row"]');

		if (Type.isDomNode(addRowButton))
		{
			Event.bind(addRowButton, 'click', this.addRowToGrid.bind(this));
		}
	}

	addCustomClassToGrid()
	{
		Dom.addClass(this.getGrid().getContainer(), 'catalog-product-variation-grid');
	}

	/**
	 * @returns {BX.Main.grid|null}
	 */
	getGrid()
	{
		if (this.grid === null)
		{
			if (!Reflection.getClass('BX.Main.gridManager.getInstanceById'))
			{
				throw Error(`Cannot find grid with '${this.gridId}' id.`)
			}

			this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		}

		return this.grid;
	}

	bindPopupInitToQuantityNodes()
	{
		const rows = this.getGrid().getRows().getRows();
		rows.forEach((row) => {
			if (row.isBodyChild() && !row.isTemplate())
			{
				const quantityNode = row.getNode().querySelector(
					'.main-grid-cell-content-catalog-quantity-inventory-management'
				);
				if (Type.isDomNode(quantityNode))
				{
					Event.bind(
						quantityNode,
						'click',
						this.openStoreAmountPopup.bind(this, row.getId(), quantityNode)
					);
				}
			}
		});
	}

	bindSliderToReservedQuantityNodes()
	{
		const rows = this.getGrid().getRows().getRows();
		rows.forEach((row) => {
			if (row.isBodyChild() && !row.isTemplate())
			{
				const reservedQuantityNode = row.getNode().querySelector(
					'.main-grid-cell-content-catalog-reserved-quantity'
				);
				if (Type.isDomNode(reservedQuantityNode))
				{
					Event.bind(
						reservedQuantityNode,
						'click',
						this.openDealsWithReservedProductSlider.bind(this, row.getId())
					);
				}
			}
		});
	}

	openStoreAmountPopup(rowId, quantityNode)
	{
		const popupId = rowId + '-store-amount';
		let popup = PopupManager.getPopupById(popupId);

		if (!popup)
		{
			popup = new Popup(
				popupId,
				quantityNode,
				{
					autoHide: true,
					draggable: false,
					offsetLeft: -218,
					offsetTop: 0,
					angle: {position: 'top', offset: 250},
					noAllPaddings: true,
					bindOptions: {forceBindPosition: true},
					closeByEsc: true,
					content: this.getStoreAmountPopupContent(rowId)
				}
			);
		}

		popup.show();
	}

	openDealsWithReservedProductSlider(rowId, storeId = 0)
	{
		if (!this.reservedDealsSliderLink)
		{
			return;
		}

		const sliderLink = new Uri(this.reservedDealsSliderLink);
		sliderLink.setQueryParam('productId', rowId);
		if (storeId > 0)
		{
			sliderLink.setQueryParam('storeId', storeId);
		}
		BX.SidePanel.Instance.open(sliderLink.toString(), {
			allowChangeHistory: false,
			cacheable: false
		});
	}

	getStoreAmountPopupContent(rowId)
	{
		const skuStoreAmountData = this.storeAmount[rowId];
		const currentSkusCount = skuStoreAmountData.storesCount;
		if (!Type.isObject(skuStoreAmountData) || currentSkusCount <= 0)
		{
			return Tag.render`
				<div class="store-amount-popup-container">
					<p class="store-amount-popup-not-found-message">${Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_EMPTY')}</p>
				</div>
			`;
		}

		const stores = skuStoreAmountData.stores;
		const linkToDetails = skuStoreAmountData.linkToDetails;

		return Tag.render`
			<div class="store-amount-popup-container">
				${this.getStoreAmountTable(stores, rowId)}
				${linkToDetails ? this.getOpenStoreAmountDetailsSliderLabel(linkToDetails, currentSkusCount) : ''}
			</div>
		`;
	}

	getStoreAmountTable(stores, rowId)
	{
		const table = Tag.render`<table class="main-grid-table"></table>`;
		const tableHead = table.createTHead();
		tableHead.className = 'main-grid-header';
		const tableHeadRow = tableHead.insertRow();
		tableHeadRow.className = 'main-grid-row-head';

		this.addCellToTable(tableHeadRow,
			Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_STORE'),
			true,
			'left',
		);
		this.addCellToTable(tableHeadRow, Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_COMMON1'), true);
		if (this.isShowedStoreReserve)
		{
			this.addCellToTable(tableHeadRow, Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_RESERVED'), true);
			this.addCellToTable(tableHeadRow, Loc.getMessage('C_PVG_STORE_AMOUNT_POPUP_QUANTITY_AVAILABLE'), true);
		}

		const tableBody = table.createTBody();
		stores.forEach((store) => {
			const tableRow = tableBody.insertRow();
			tableRow.className = 'main-grid-row main-grid-row-body';
			this.addCellToTable(tableRow, store.title, false, 'left');
			this.addCellToTable(tableRow, store.quantityCommon, false);
			if (this.isShowedStoreReserve)
			{
				const quantityReservedNode = Tag.render`<a class="main-grid-cell-content-catalog-reserved-quantity">${store.quantityReserved}</a>`;
				Event.bind(
					quantityReservedNode,
					'click',
					this.openDealsWithReservedProductSlider.bind(this, rowId, store.storeId)
				);
				this.addCellToTable(tableRow, quantityReservedNode, false);
				this.addCellToTable(tableRow, store.quantityAvailable, false);
			}
		});

		return table;
	}

	addCellToTable(row, textContent, isHead, horizontalPosition = 'right')
	{
		const cellClassName =
			isHead
				? 'main-grid-cell-head main-grid-col-no-sortable main-grid-cell-'
				: 'main-grid-cell main-grid-cell-'
		;
		const innerClassName =
			isHead
				? 'main-grid-cell-head-container'
				: 'main-grid-cell-content'
		;
		const cell = row.insertCell();
		cell.className = cellClassName + horizontalPosition;
		cell.appendChild(Tag.render`
			<div class="main-grid-cell-inner">
				<span class="${innerClassName}">
					${textContent}
				</span>
			</div>
		`);
	}

	getOpenStoreAmountDetailsSliderLabel(linkToDetails, currentSkusCount)
	{
		const openSliderLabel = Tag.render`
			<span class="ui-link ui-link-secondary ui-link-dashed ui-link-open-store-amount-slider">
				${Loc.getMessage(
			'C_PVG_STORE_AMOUNT_POPUP_OPEN_SLIDER_BUTTON',
			{'#STORE_COUNT#': currentSkusCount}
		)}
			</span>
		`;
		Event.bind(openSliderLabel, 'click', this.openStoreAmountSlider.bind(this, linkToDetails));

		return openSliderLabel;
	}

	openStoreAmountSlider(linkToDetails)
	{
		BX.SidePanel.Instance.open(linkToDetails, {
			width: 700,
			allowChangeHistory: false,
			cacheable: false
		});
	}

	destroyStoreAmountPopups()
	{
		const rows = this.getGrid().getRows().getRows();
		rows.forEach((row) => {
			if (row.isBodyChild() && !row.isTemplate())
			{
				const popupId = row.getId() + '-store-amount';
				const popup = PopupManager.getPopupById(popupId);
				popup?.destroy?.();
			}
		});
	}

	emitEditedRowsEvent()
	{
		if (this.getGrid().getRows().isSelected())
		{
			EventEmitter.emit('Grid::thereEditedRows', []);
		}
		else
		{
			EventEmitter.emit('Grid::noEditedRows', []);
		}
	}

	disableEdit()
	{
		if (this.isNew)
		{
			return;
		}

		this.getGrid().getRows().getRows().forEach((current) => {
			if (!Dom.hasClass(current.getNode(), 'main-grid-row-new'))
			{
				current.editCancel();
				current.unselect();
			}
		});

		this.emitEditedRowsEvent();
	}

	enableEdit()
	{
		this.getGrid().getRows().selectAll();
		this.getGrid().getRows().editSelected();
		this.getGrid()
			.getRows()
			.getRows()
			.forEach(item => this.enableBarcodeEditor(item))
		;
	}

	prepareNewNodes()
	{
		this.getGrid().getRows().getBodyChild().map(row => {
			const newNode = row.getNode();
			this.markNodeAsNew(newNode)
			this.addSkuListCreationItem(newNode);
			this.modifyCustomSkuProperties(newNode);
			this.disableCheckbox(row);
		});
	}

	disableCheckbox(row)
	{
		const checkbox = row.getCheckbox();
		if (Type.isDomNode(checkbox))
		{
			checkbox.setAttribute('disabled', 'disabled');
		}
	}

	markNodeAsNew(node)
	{
		Dom.addClass(node, 'main-grid-row-new');
	}

	bindInlineEdit()
	{
		this.getGrid().getRows().getBodyChild().forEach(
			item => Event.bind(item.node, 'click', event => this.toggleInlineEdit(item, event))
		);
	}

	/**
	 * @returns {BX.UI.EntityEditor|null}
	 */
	getEditorInstance()
	{
		if (Reflection.getClass('BX.UI.EntityEditor'))
		{
			return BX.UI.EntityEditor.getDefault();
		}

		return null;
	}

	bindCreateSkuProperty()
	{
		if (!this.canHaveSku)
		{
			return;
		}

		const createPropertyNode = document.getElementById(this.createPropertyId);
		const control = this.getEditorInstance().getControlByIdRecursive('variation_grid');

		if (Type.isDomNode(createPropertyNode) && control)
		{
			control._createChildButton = createPropertyNode;
			Event.bind(createPropertyNode, 'click', control.onCreateFieldBtnClick.bind(control));
		}

		const createPropertyHintNode = document.getElementById(this.createPropertyHintId);
		Event.bind(createPropertyHintNode, 'click', this.showHelp.bind(this));
	}

	showHelp(event)
	{
		if (Reflection.getClass('top.BX.Helper'))
		{
			top.BX.Helper.show('redirect=detail&code=11657102');
			event.preventDefault();
		}
	}

	// ToDo auto focus on input under event.point?
	toggleInlineEdit(item: BX.Grid.Row, event)
	{
		let changed = false;

		if (item.isEdit())
		{
			if (this.hasClickedOnCheckboxArea(item, event.target))
			{
				changed = true;
				this.deactivateInlineEdit(item);
			}
		}
		else
		{
			if (event.target.nodeName !== 'A')
			{
				changed = true;
				this.activateInlineEdit(item);
			}
		}

		if (changed)
		{
			this.emitEditedRowsEvent();

			this.getGrid().adjustRows();
			this.getGrid().updateCounterSelected();
			this.getGrid().updateCounterDisplayed();
			this.getGrid().adjustCheckAllCheckboxes();
		}
	}

	hasClickedOnCheckboxArea(item: BX.Grid.Row, target)
	{
		if (!Type.isDomNode(target))
		{
			return;
		}

		const cells = item.getCells();

		for (let i in cells)
		{
			if (
				cells.hasOwnProperty(i)
				&& cells[i].contains(item.getCheckbox())
				&& (cells[i] === target || cells[i].contains(target))
			)
			{
				return true;
			}
		}

		return false;
	}

	activateInlineEdit(item: BX.Grid.Row)
	{
		item.select();
		item.edit();
		this.enableBarcodeEditor(item);

		this.addSkuListCreationItem(item.getNode());
	}

	deactivateInlineEdit(item: BX.Grid.Row)
	{
		item.editCancel();
		item.unselect();

		// disable multi-selection(and self re-selection) while disabling editing
		this.getGrid().clickPrevent = true;
		setTimeout(() => {
			this.getGrid().clickPrevent = false;
		}, 100);
	}

	enableBarcodeEditor(item: BX.Grid.Row)
	{
		const barcodeNode =
			item.getCellById('SKU_GRID_BARCODE')
				?.querySelector('[data-role="barcode-selector"]')
		;

		if (barcodeNode)
		{
			barcodeNode.innerHTML = '';
			const inputWrapper = Tag.render`<div style="display: none"></div>`;
			Dom.append(inputWrapper, barcodeNode);
			const barcodes = item.editData?.SKU_GRID_BARCODE_VALUES;

			const items = [];
			if (Type.isArray(barcodes))
			{
				barcodes.forEach((barcode) => {
					const id = Text.toNumber(barcode.ID);
					const title = barcode.BARCODE;

					items.push({
						entityId: 'productBarcode',
						id,
						title,
					});

					const input = Tag.render`<input type="hidden">`;
					input.name = id;
					input.value = title;
					inputWrapper.appendChild(input);
				});
			}

			const createBarcode = (event: BaseEvent) => {
				if (blurThrottle)
				{
					clearTimeout(blurThrottle);
				}

				const selector = event.getTarget();
				const value = selector.getTextBoxValue();

				value.split(' ').forEach((title) => {
					if (!Type.isStringFilled(title))
					{
						return;
					}

					const id = Text.getRandom();
					selector.addTag({
						id,
						title,
						entityId: 'productBarcode',
					});

					const input = Tag.render`<input type="hidden">`;
					input.name = id;
					input.value = title;
					inputWrapper.appendChild(input);
				});

				hideBarcodeInput();
			};

			let blurThrottle = null;
			const hideBarcodeInput = () => {
				tagSelector.hideCreateButton();
				tagSelector.clearTextBox();
				tagSelector.showAddButton();
				tagSelector.hideTextBox();
			};

			const tagSelector = new TagSelector({
				placeholder: Loc.getMessage('C_PVG_STORE_CREATE_BARCODE_PLACEHOLDER'),
				addButtonCaption: Loc.getMessage('C_PVG_STORE_ADD_NEW_BARCODE'),
				addButtonCaptionMore: Loc.getMessage('C_PVG_STORE_ADD_ONE_MORE_BARCODE'),
				items,
				events: {
					onAddButtonClick: (event: BaseEvent) => {
						const selector = event.getTarget();
						selector.showCreateButton();
					},
					onBeforeTagRemove: (event: BaseEvent) => {
						const data = event.getData();
						const barcodeId = data.tag?.id;
						if (!Type.isNil(barcodeId))
						{
							const name = 'input[name="' + barcodeId + '"]';
							const input = inputWrapper.querySelector(name);
							if (input)
							{
								input.parentNode.removeChild(input);
							}
						}
					},
					onCreateButtonClick: createBarcode,
					onEnter: createBarcode,
					onMetaEnter: createBarcode,
					onBlur: () => {
						blurThrottle = setTimeout(hideBarcodeInput, 300);
					},
				},
			});

			tagSelector.renderTo(barcodeNode);
		}
	}

	modifyCustomSkuProperties(node)
	{
		const postfix = '_' + node.getAttribute('data-id');

		node.querySelectorAll('input[type="radio"]').forEach(input => {
			input.id += postfix;
			input.setAttribute('name', input.getAttribute('name') + postfix);
		});

		node.querySelectorAll('label[data-role]').forEach(label => {
			label.setAttribute('for', label.getAttribute('for') + postfix);
		});
	}

	addSkuListCreationItem(node)
	{
		node.querySelectorAll('[data-role="dropdownContent"] ul').forEach((listNode) => {
			if (!listNode.querySelector('[data-role="createItem"]'))
			{
				const propertyId = listNode.getAttribute('data-propertyId');
				const createItem = Tag.render`
					<li data-role="createItem"
						 class="catalog-productcard-popup-select-item catalog-productcard-popup-select-item-new"
						 onclick="BX.Catalog.VariationGrid.firePropertyModification(${propertyId})">
						<label class="catalog-productcard-popup-select-label">
							<span class="catalog-productcard-popup-select-add"></span>
							<span class="catalog-productcard-popup-select-text">
								${Loc.getMessage('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON')}
							</span>
						</label>
					</li>`;
				listNode.appendChild(createItem);
			}
		});
	}

	addRowToGrid()
	{
		const originalTemplate = this.redefineTemplateEditData();

		const grid = this.getGrid();
		const newRow = grid.prependRowEditor();

		this.disableCheckbox(newRow);

		const newNode = newRow.getNode();
		grid.getRows().reset();

		if (Type.isDomNode(newNode))
		{
			const newRowDataId = Text.getRandom();
			this.gridEditData[newRowDataId] = {...this.gridEditData['template_0']};
			newNode.setAttribute('data-id', newRowDataId);
			this.markNodeAsNew(newNode);
			this.modifyCustomSkuProperties(newNode);
			this.addSkuListCreationItem(newNode);
			this.setDeleteButton(newNode);
			this.enableBarcodeEditor(newRow);
			newRow.makeCountable();
		}

		if (originalTemplate)
		{
			this.setOriginalTemplateEditData(originalTemplate);
		}

		EventEmitter.emit('Grid::thereEditedRows', []);

		grid.adjustRows();
		grid.updateCounterDisplayed();
		grid.updateCounterSelected();
		this.updateCounterTotal();
	}

	updateCounterTotal()
	{
		const grid = this.getGrid();
		const counterTotalTextContainer = grid.getCounterTotal().querySelector('.main-grid-panel-content-text');
		counterTotalTextContainer.textContent = grid.getRows().getCountDisplayed();
	}

	setDeleteButton(row)
	{
		const actionCellContentContainer = row.querySelector('.main-grid-cell-action .main-grid-cell-content');
		const rowId = row?.dataset?.id;

		if (rowId)
		{
			const deleteButton = Tag.render`
				<span 
					class="main-grid-delete-button" 
					onclick="${this.removeNewRowFromGrid.bind(this, rowId)}"
				></span>
			`;

			Dom.append(deleteButton, actionCellContentContainer);
		}
	}

	removeNewRowFromGrid(rowId)
	{
		if (!Type.isStringFilled(rowId))
		{
			return;
		}

		const gridRow = this.getGrid().getRows().getById(rowId);
		if (gridRow)
		{
			Dom.remove(gridRow.getNode());
			this.getGrid().getRows().reset();
			this.getGrid().updateCounterDisplayed();
			this.getGrid().updateCounterSelected();
			this.updateCounterTotal();

			this.emitEditedRowsEvent();
		}
	}

	removeRowFromGrid(skuId)
	{
		const data = {
			'id': skuId,
			'action': 'deleteRow'
		}
		this.getGrid().reloadTable('POST', data);
	}

	getGridEditData()
	{
		return this.getGrid().arParams.EDITABLE_DATA;
	}

	// rewrite edit data because of grid component cuts necessary fields (VIEW_HTML/EDIT_HTML)
	setGridEditData(data)
	{
		this.getGrid().arParams.EDITABLE_DATA = data;
	}

	setOriginalTemplateEditData(data)
	{
		this.getGrid().arParams.EDITABLE_DATA[GRID_TEMPLATE_ROW] = data;
	}

	redefineTemplateEditData()
	{
		let newRowData = this.getEditDataFromSelectedValues();

		if (!newRowData)
		{
			newRowData = this.getEditDataFromNotSelectedValues();
		}

		if (newRowData)
		{
			newRowData = {...newRowData};
			this.prepareNewRowData(newRowData);

			const data = this.getGridEditData();
			const originalTemplateData = data[GRID_TEMPLATE_ROW];
			const customEditData = this.prepareCustomEditData(originalTemplateData);

			this.setOriginalTemplateEditData({...originalTemplateData, ...newRowData, ...customEditData})

			return originalTemplateData;
		}

		return null;
	}

	getEditDataFromSelectedValues()
	{
		const rowNodes = this.getGrid().getRows().getSelected();

		return rowNodes.length ? rowNodes[0].editGetValues() : null;
	}

	getEditDataFromNotSelectedValues()
	{
		const values = this.getGrid().arParams.EDITABLE_DATA;
		const id = Object.keys(values)
			.reverse()
			.find(index => index !== GRID_TEMPLATE_ROW && Type.isPlainObject(values[index]))
		;

		return id ? values[id] : null;
	}

	prepareNewRowData(newRowData)
	{
		for (let i in newRowData)
		{
			if (newRowData.hasOwnProperty(i) && (i.includes('[VIEW_HTML]') || i.includes('[EDIT_HTML]')))
			{
				delete newRowData[i]
			}
		}
		newRowData['SKU_GRID_BARCODE'] = '<div data-role="barcode-selector"></div>';
	}

	prepareCustomEditData(originalEditData)
	{
		const customEditData = {};

		for (let i in originalEditData)
		{
			if (originalEditData.hasOwnProperty(i) && i.includes('[EDIT_HTML]'))
			{
				// modify file input instance ids (due to collisions with default id)
				if (originalEditData[i].indexOf('new BX.UI.ImageInput') >= 0)
				{
					const filePrefix = 'bx_file_' + i.replace('[EDIT_HTML]', '').toLowerCase() + '_';
					const matches = originalEditData[i].match(new RegExp('\'(' + filePrefix + '[A-Za-z0-9_]+)\''));

					if (matches[1])
					{
						customEditData[i] = originalEditData[i].replace(
							new RegExp(matches[1], 'g'),
							filePrefix + this.getRandomInt()
						);
					}
				}
			}
		}

		return customEditData;
	}

	getRandomInt(max = 100000)
	{
		return Math.floor(Math.random() * Math.floor(max));
	}

	getHeaderNames()
	{
		const headers = [];
		const cells = this.getGrid().getRows().getHeadFirstChild().getCells();

		Array.from(cells).forEach((header) => {
			if ('name' in header.dataset)
			{
				headers.push(header.dataset.name);
			}
		});

		return headers;
	}

	addPropertyToGridHeader(item)
	{
		BX.ajax.runComponentAction(
			'bitrix:catalog.productcard.variation.grid',
			'addPropertyHeader',
			{
				mode: 'ajax',
				data: {
					gridId: this.getGrid().getId(),
					propertyCode: item.id,
					anchor: item.anchor || null,
					currentHeaders: this.getHeaderNames()
				}
			}
		).then((response) => {
			this.reloadGrid();
		});
	}

	reloadGrid()
	{
		this.getGrid().reload();
	}

	onGridUpdated(event: BaseEvent)
	{
		this.getGrid().getSettingsWindow().getItems().forEach((column) => {
			if(this.getHeaderNames().indexOf(column.node.dataset.name) !== -1)
			{
				column.state.selected = true;
				column.checkbox.checked = true;
			}
			else{
				column.state.selected = false;
				column.checkbox.checked = false;
			}
		});
	}

	onPropertySave(event: BaseEvent)
	{
		const [sliderEvent] = event.getCompatData();

		if (sliderEvent.getEventId() === 'PropertyCreationForm:onAdd')
		{
			const eventArgs = sliderEvent.getData();

			this.addPropertyToGridHeader({
				id: eventArgs.fields.CODE
			});
		}

		if (sliderEvent.getEventId() === 'PropertyCreationForm:onModify')
		{
			this.reloadGrid();
		}
	}

	showPropertySettingsSlider(event: BaseEvent)
	{
		const [propertyId] = event.getData();
		const link = this.modifyPropertyLink.replace('#PROPERTY_ID#', propertyId);

		this.askToLossGridData(() => {
			BX.SidePanel.Instance.open(link, {
				width: 550,
				allowChangeHistory: false,
				cacheable: false
			});
		});
	}

	askToLossGridData(okCallback?, cancelCallback?, options?: {})
	{
		if (this.isGridInEditMode())
		{
			const defaultOptions = {
				title: Loc.getMessage('C_PVG_UNSAVED_DATA_TITLE'),
				message: Loc.getMessage('C_PVG_UNSAVED_DATA_MESSAGE'),
				modal: true,
				buttons: MessageBoxButtons.OK_CANCEL,
				okCaption: Loc.getMessage('C_PVG_UNSAVED_DATA_CONTINUE'),
				onOk: messageBox => {
					okCallback && okCallback();
					messageBox.close();
				},
				onCancel: messageBox => {
					cancelCallback && cancelCallback();
					messageBox.close();
				}
			};

			MessageBox.show({...defaultOptions, ...options});
		}
		else
		{
			okCallback && okCallback();
		}
	}

	isGridInEditMode()
	{
		return this.getGrid()
			.getRows()
			.getBodyChild()
			.filter(row => row.isShown() && row.isEdit())
			.length > 0;
	}

	static firePropertyModification(propertyId, menuId)
	{
		if (menuId)
		{
			const menu = MenuManager.getMenuById(menuId);
			if (menu)
			{
				menu.close();
				menu.destroy();
			}
		}
		else
		{
			const popup = PopupManager.getCurrentPopup();
			if (popup)
			{
				popup.close();
			}
		}

		EventEmitter.emit('VariationGrid::propertyModify', [propertyId]);
	}
}

Reflection.namespace('BX.Catalog').VariationGrid = VariationGrid;