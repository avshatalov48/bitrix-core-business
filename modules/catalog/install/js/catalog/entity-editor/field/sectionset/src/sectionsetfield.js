import { TagSelector } from 'ui.entity-selector';
import { BaseEvent } from 'main.core.events';
import { Const } from './const';
import { ProductSetField, Footer } from 'catalog.entity-editor.field.productset';

export class SectionSetField extends ProductSetField
{
	constructor(id, settings)
	{
		super(id, settings);
		this.initialize(id, settings);

		this._input = null;
		this._inputWrapper = null;
		this.innerWrapper = null;
		this.entityList = null;
		this.tagSelector = null;
	}

	getProductSelector(value)
	{
		const iblockId = this.getIBlockIdFromModel();

		if (!this.tagSelector)
		{
			this.tagSelector = new TagSelector({
				// items: currentSelectedItems,
				textBoxWidth: '100%',
				multiple: true,
				dialogOptions: {
					context: 'catalog_document_sectionset',
					entities: [
						{
							id: Const.ENTITY_ID.SECTION,
							options: {
								iblockId: iblockId,
							},
						},
					],
					searchOptions: {
						allowCreateItem: false,
					},
					events: {
						'Item:onSelect': (event) => {
							this.handleUserSelectorChanges(event);
							this._changeHandler();
						},
						'Item:onDeselect': (event) => {
							this.handleUserSelectorChanges(event);
							this._changeHandler();
						},
					},
					footer: Footer,
				},
			});
		}

		if (this.tagSelector.getDialog() && value.length > 0)
		{
			const dialog = this.tagSelector.getDialog();

			value.forEach((item) => {
				dialog.addItem({
					id: item.PRODUCT_ID,
					title: item.PRODUCT_NAME,
					avatar: item.IMAGE,
					selected: true,
					entityId: Const.ENTITY_ID.SECTION,
				});
			});
		}

		return this.tagSelector;
	}

	handleUserSelectorChanges(event: BaseEvent)
	{
		this.entityList = [];
		const values = [];

		const selectedItems = event.getTarget().getSelectedItems();
		selectedItems.forEach((item) => {
			values.push({
				PRODUCT_ID: item.getId(),
				PRODUCT_TYPE: Const.TYPE.SECTION,
			});

			this.entityList.push({
				PRODUCT_ID: item.getId(),
				PRODUCT_TYPE: Const.TYPE.SECTION,
				PRODUCT_NAME: item.getTitle(),
			});
		});

		this._input.value = JSON.stringify(values);
	}

	static create(id, settings)
	{
		const self = new this(id, settings);
		self.initialize(id, settings);

		return self;
	}
}
