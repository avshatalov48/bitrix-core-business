import {Dom, Event, Loc, Reflection, Runtime, Tag, Text, Type, Uri} from 'main.core';
import {typeof BaseEvent, EventEmitter} from 'main.core.events';
import {MenuManager, Popup, PopupManager} from 'main.popup';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {TagSelector} from "ui.entity-selector";

const GRID_TEMPLATE_ROW = 'template_0';

class ServiceGrid
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
		this.isSimple = settings.isSimple;
		this.isReadOnly = settings.isReadOnly;
		this.hiddenProperties = settings.hiddenProperties;
		this.modifyPropertyLink = settings.modifyPropertyLink;
		this.productCopyLink = settings.productCopyLink;
		this.gridEditData = settings.gridEditData;
		this.canHaveSku = false; // deprecated
		this.storeAmount =[]; // deprecated
		this.isShowedStoreReserve = false; // deprecated
		this.reservedDealsSliderLink = settings.reservedDealsSliderLink;
		if (settings.copyItemsMap)
		{
			this.getGrid().arParams.COPY_ITEMS_MAP = settings.copyItemsMap;
		}

		if (settings.supportedAjaxFields)
		{
			this.getGrid().arParams.SUPPORTED_AJAX_FIELDS = settings.supportedAjaxFields;
		}

		if (this.isReadOnly)
		{
			return;
		}

		const isGridReload = settings.isGridReload || false;
		if (!isGridReload)
		{
			this.addCustomClassToGrid();
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
		EventEmitter.subscribe('ServiceGrid::propertyModify', this.showPropertySettingsSliderHandler);

		this.onPrepareDropDownItemsHandler = this.onPrepareDropDownItems.bind(this);
		EventEmitter.subscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);

		this.onCreatePopupHandler = this.onCreatePopup.bind(this);
		EventEmitter.subscribe('UiSelect::onCreatePopup', this.onCreatePopupHandler);
	}

	destroy()
	{
		this.unsubscribeCustomEvents();
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
			EventEmitter.unsubscribe('ServiceGrid::propertyModify', this.showPropertySettingsSliderHandler);
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

		this.getGrid().getSettingsWindow()._onSettingsButtonClick();
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
			'onclick': () => BX.Catalog.ServiceGrid.firePropertyModification(propertyId, menuId)
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
		Event.bind(addButton, 'mousedown', BX.Catalog.ServiceGrid.firePropertyModification.bind(this, propertyId));

		popup.contentContainer.appendChild(addButton);
	}

	clearGridSettingsPopupStuff()
	{
		Dom.remove(document.getElementById(this.gridId + '-grid-settings-window'));
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

			if (this.isSimple)
			{
				item.getNode()
					?.querySelectorAll('.main-grid-editor.main-dropdown.main-grid-editor-dropdown')
					.forEach((item) => {
						const id = item.id;
						if (Type.isNil(id) || id.indexOf('SKU_GRID_PROPERTY_') === -1)
						{
							return;
						}

						Event.unbindAll(item);
						Event.bind(item, 'click', this.openSimpleProductRestrictionPopup.bind(this));
					})
				;

				item.getNode()
					?.querySelectorAll('.catalog-productcard-select-container .catalog-productcard-select-block')
					.forEach((item) => {
						item.onclick = null;
						Event.unbindAll(item);
						Event.bind(item, 'click', this.openSimpleProductRestrictionPopup.bind(this));
					})
				;
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
						 onclick="BX.Catalog.ServiceGrid.firePropertyModification(${propertyId})">
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
			'bitrix:catalog.productcard.service.grid',
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

		BX.SidePanel.Instance.open(link, {
			width: 550,
			allowChangeHistory: false,
			cacheable: false
		});
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

		EventEmitter.emit('ServiceGrid::propertyModify', [propertyId]);
	}
}

Reflection.namespace('BX.Catalog').ProductServiceGrid = ServiceGrid;
