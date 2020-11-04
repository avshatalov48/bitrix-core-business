import {Dom, Event, Loc, Reflection, Runtime, Tag, Text, Type} from 'main.core';
import {typeof BaseEvent, EventEmitter} from 'main.core.events';
import {MenuManager, PopupManager} from 'main.popup';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

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
		}
		else
		{
			this.bindInlineEdit();
		}

		Event.bind(this.getGrid().getScrollContainer(), 'scroll', Runtime.throttle(this.onScrollHandler.bind(this), 50));
		Event.bind(this.getGridSettingsButton(), 'click', this.showGridSettingsWindowHandler.bind(this));

		this.subscribeCustomEvents();
	}

	subscribeCustomEvents()
	{
		this.onPropertySaveHandler = this.onPropertySave.bind(this);
		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onPropertySaveHandler);

		this.onAllRowsSelectHandler = this.enableEdit.bind(this)
		EventEmitter.subscribe('Grid::allRowsSelected', this.onAllRowsSelectHandler);

		this.showPropertySettingsSliderHandler = this.showPropertySettingsSlider.bind(this);
		EventEmitter.subscribe('VariationGrid::propertyModify', this.showPropertySettingsSliderHandler);

		this.onPrepareDropDownItemsHandler = this.onPrepareDropDownItems.bind(this);
		EventEmitter.subscribe('Dropdown::onPrepareItems', this.onPrepareDropDownItemsHandler);
	}

	unsubscribeCustomEvents()
	{
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
			'text': `
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

	enableEdit()
	{
		this.getGrid().getRows().selectAll();
		this.getGrid().editSelected();
	}

	prepareNewNodes()
	{
		this.getGrid().getRows().getBodyChild().map(row => {
			const newNode = row.getNode();
			this.markNodeAsNew(newNode)
			this.addSkuListCreationItem(newNode);
			this.modifyCustomSkuProperties(newNode);
		});
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
			if (this.getGrid().getRows().isSelected())
			{
				EventEmitter.emit('Grid::thereEditedRows', []);
			}
			else
			{
				EventEmitter.emit('Grid::noEditedRows', []);
			}

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
						 onclick="BX.Catalog.VariationGrid.firePropertyModification(${propertyId})">
						<label class="catalog-productcard-popup-select-label">
							<span class="catalog-productcard-popup-select-add"></span>
							<span class="catalog-productcard-popup-select-text">
								${BX.message('C_PVG_ADD_NEW_PROPERTY_VALUE_BUTTON')}
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

		const checkbox = newRow.getCheckbox();
		if (Type.isDomNode(checkbox))
		{
			checkbox.setAttribute('disabled', 'disabled');
		}

		const newNode = newRow.getNode();
		grid.getRows().reset();

		if (Type.isDomNode(newNode))
		{
			newNode.setAttribute('data-id', Text.getRandom());
			this.markNodeAsNew(newNode);
			this.modifyCustomSkuProperties(newNode);
			this.addSkuListCreationItem(newNode);
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
	}

	removeRowFromGrid(skuId)
	{
		this.getGrid().removeRow(skuId);
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