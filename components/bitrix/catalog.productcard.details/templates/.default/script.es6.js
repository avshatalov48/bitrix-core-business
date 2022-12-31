import {Loc, Reflection} from 'main.core';
import {EntityCard} from 'catalog.entity-card';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {MenuManager, Popup} from "main.popup";
import {Button} from "ui.buttons";

class ProductCard extends EntityCard
{
	#isQuantityTraceNoticeShown = false;

	constructor(id, settings = {})
	{
		super(id, settings);

		this.initDocumentTypeSelector();
	}

	getEntityType()
	{
		return 'Product';
	}

	onSectionLayout(event: BaseEvent)
	{
		const [section, eventData] = event.getCompatData();

		if (eventData.id === 'catalog_parameters')
		{
			eventData.visible = this.isSimpleProduct && this.isCardSettingEnabled('CATALOG_PARAMETERS');
		}

		EventEmitter.subscribe('BX.UI.EntityEditorList:onItemSelect', (event) => {
			const isQuantityTraceRestricted = !(this.isWithOrdersMode && !this.isInventoryManagementUsed);
			if (this.#isQuantityTraceNoticeShown || !isQuantityTraceRestricted)
			{
				return;
			}

			const field = event.getData()[1]?.field;
			if (!field)
			{
				return;
			}

			if (field.getId() !== 'QUANTITY_TRACE' || field._selectedValue !== 'N')
			{
				return;
			}

			const popup = new Popup({
				content: Loc.getMessage('CPD_QUANTITY_TRACE_NOTICE'),
				overlay: true,
				titleBar: Loc.getMessage('CPD_QUANTITY_TRACE_NOTICE_TITLE'),
				closeByEsc: true,
				closeIcon: true,
				buttons: [
					new Button({
						text: Loc.getMessage('CPD_QUANTITY_TRACE_ACCEPT'),
						className: 'ui-btn ui-btn-md ui-btn-primary',
						events: {
							click: (function()
							{
								this.#isQuantityTraceNoticeShown = false;
								popup.destroy();
							}).bind(this),
						}
					}),
				],
				events: {
					onAfterClose: (function () {
						this.#isQuantityTraceNoticeShown = false;
					}).bind(this),
				}
			});
			popup.show();

			this.#isQuantityTraceNoticeShown = true;
		});

		section?.getChildren().forEach((field) => {
			if (this.hiddenFields.includes(field?.getId()))
			{
				field.setVisible(false);
			}
		});

		EventEmitter.subscribe('onEntityUpdate', (event) => {
			const editor = event.getData()[0]?.sender;
			if (!editor)
			{
				return;
			}

			const quantityTraceValue = editor._model.getField('QUANTITY_TRACE', 'D');
			const isQuantityTraceRestricted = !(this.isWithOrdersMode && !this.isInventoryManagementUsed);
			if (quantityTraceValue !== 'N' && isQuantityTraceRestricted)
			{
				editor.getControlById('QUANTITY_TRACE')?.setVisible(false);
			}
		});
	}

	onGridUpdatedHandler(event: BaseEvent)
	{
		super.onGridUpdatedHandler(event);

		const [grid] = event.getCompatData();
		if ((grid && grid.getId() === this.getVariationGridId()) && (grid.getRows().getCountDisplayed() <= 0))
		{
			document.location.reload();
		}
	}

	onEditorAjaxSubmit(event: BaseEvent)
	{
		super.onEditorAjaxSubmit(event);

		const [, response] = event.getCompatData();

		if (response.data)
		{
			if (response.data.NOTIFY_ABOUT_NEW_VARIATION)
			{
				this.showNotification(Loc.getMessage('CPD_NEW_VARIATION_ADDED'));
			}
		}
	}

	initDocumentTypeSelector()
	{
		let productTypeSelector = document.getElementById(this.settings.productTypeSelector);
		let productTypeSelectorTypes = this.settings.productTypeSelectorTypes;

		if (!productTypeSelector || !productTypeSelectorTypes)
		{
			return;
		}

		let menuItems = [];

		Object.keys(productTypeSelectorTypes).forEach((type) => {
			menuItems.push({
				text: productTypeSelectorTypes[type],
				onclick: (e) => {
					let slider = BX.SidePanel.Instance.getTopSlider();
					if (slider)
					{
						slider.url = BX.Uri.addParam(slider.getUrl(), {productTypeId: type});
						slider.requestMethod = 'post';

						slider.setFrameSrc();
					}
				},
			});
		});

		let popupMenu = MenuManager.create({
			id: 'productcard-product-type-selector',
			bindElement: productTypeSelector,
			items: menuItems,
			minWidth: productTypeSelector.offsetWidth,
		});

		productTypeSelector.addEventListener('click', e => {
			e.preventDefault();
			popupMenu.show();
		});
	}
}

Reflection.namespace('BX.Catalog').ProductCard = ProductCard;