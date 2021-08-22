import {ajax, Loc, Reflection} from 'main.core';
import {EventEmitter} from 'main.core.events'

class SectionSelector
{
	constructor(settings = {})
	{
		this.selectorId = settings.selectorId;
		this.selectorHiddenId = settings.selectorHiddenId;
		this.selectedItems = settings.selectedItems;
		this.iblockId = settings.iblockId;

		this.initSelector();
	}

	initSelector()
	{
		if (!this.selector)
		{
			this.selector = new BX.UI.EntitySelector.TagSelector({
				id: this.selectorId,
				multiple: true,
				placeholder: Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_PLACEHOLDER'),
				textBoxWidth: '100%',
				dialogOptions: {
					height: 300,
					id: this.selectorId,
					context: 'catalog-sections',
					enableSearch: false,
					multiple: false,
					dropdownMode: true,
					selectedItems: this.selectedItems,
					searchTabOptions: {
						stub: true,
						stubOptions: {
							title: Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_IS_EMPTY_TITLE'),
							subtitle: Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_IS_EMPTY_SUBTITLE'),
							arrow: true
						}
					},
					searchOptions: {
						allowCreateItem: true
					},
					events: {
						'Item:onSelect': this.setSelectedInputs.bind(this, 'Item:onSelect'),
						'Item:onDeselect': this.setSelectedInputs.bind(this, 'Item:onDeselect'),
						'Search:onItemCreateAsync': this.addNewSection.bind(this)
					},
					entities: [
						{
							id: 'section',
							options: {
								'iblockId': this.iblockId
							},
							dynamicSearch: true,
							dynamicLoad: true
						}
					]
				}
			});
		}

		this.selector.renderTo(document.getElementById(this.selectorId));
	}

	setSelectedInputs(eventName, event)
	{
		event.target.hide();

		const selectedSections = event.getData().item.getDialog().getSelectedItems();
		if (Array.isArray(selectedSections))
		{
			let htmlInputs = '';
			const selectedItemsId = [];

			selectedSections.forEach((section) => {
				htmlInputs += '<input type="hidden" name="IBLOCK_SECTION[]" value="' + section['id'] + '" />';
				selectedItemsId.push(section['id']);
			});

			document.getElementById(this.selectorHiddenId).innerHTML = htmlInputs;
			EventEmitter.emit(eventName, selectedItemsId);
		}
	}

	addNewSection(event)
	{
		return new Promise(function(resolve, reject) {

			/** @type  {BX.UI.EntitySelector.Item} */
			const {searchQuery} = event.getData();

			/** @type  {BX.UI.EntitySelector.Dialog} */
			const dialog = event.getTarget();

			ajax.runComponentAction(
				'bitrix:catalog.productcard.iblocksectionfield',
				'addSection',
				{
					mode: 'ajax',
					data: {
						iblockId: this.iblockId,
						name: searchQuery.getQuery()
					}
				}
			).then(response => {
				const item = dialog.addItem({
					id: response.data.id,
					entityId: 'tag',
					title: searchQuery.getQuery(),
					tabs: dialog.getRecentTab().getId()
				});

				if (item)
				{
					item.select();
				}
			}).bind(this);

			resolve();
		}.bind(this));
	}
}

Reflection.namespace('BX.Catalog').SectionSelector = SectionSelector;