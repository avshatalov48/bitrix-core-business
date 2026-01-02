import { Checkbox } from './checkbox';
import { Item } from './item';
import { Draggable } from 'ui.draganddrop.draggable';
import 'ui.icon-set.actions';
import { BIcon, Outline } from 'ui.icon-set.api.vue';
import 'ui.icon-set.outline';
import 'ui.forms';
import { Type, Text } from 'main.core';
import { Chip, ChipDesign } from 'ui.system.chip.vue';

export const Selector = {
	name: 'Selector',
	components: {
		Item,
		BIcon,
		Checkbox,
		Chip,
	},
	props: {
		items: {
			type: Array,
			required: true,
		},
		maxSelected: {
			type: Number,
			default: null,
		},
	},
	data(): Object {
		return {
			localItems: this.items.map(item => ({ ...item })),
			searchQuery: '',
			sortDirection: null,
		};
	},
	computed: {
		selectedCount(): number {
			return this.localItems.filter((item) => item.selected).length;
		},
		filteredItems(): Array {
			let items = [...this.itemsBySearchQuery];

			if (this.sortDirection)
			{
				items.sort((item1, item2) => {
					if (item1.selected !== item2.selected)
					{
						return item1.selected ? -1 : 1;
					}
					const compare = item1.title.localeCompare(item2.title);

					return this.sortDirection === 'asc' ? compare : -compare;
				});
			}
			else
			{
				items.sort((item1, item2) => {
					if (item1.selected !== item2.selected)
					{
						return item1.selected ? -1 : 1;
					}

					if (!Type.isUndefined(item1.sort) && !Type.isUndefined(item2.sort))
					{
						const sortCompare = Text.toInteger(item1.sort) - Text.toInteger(item2.sort);
						if (sortCompare !== 0)
						{
							return sortCompare;
						}
					}

					if (this.sortDirection)
					{
						const titleCompare = item1.title.localeCompare(item2.title);

						return this.sortDirection === 'asc' ? titleCompare : -titleCompare;
					}

					return 0;
				});
			}

			return items;
		},
		itemsBySearchQuery(): Array {
			let items = [...this.localItems];

			if (this.searchQuery)
			{
				const query = this.searchQuery.toLowerCase();
				items = items.filter((item) => item.title.toLowerCase().includes(query));
			}

			return items;
		},
		hasActiveItems(): boolean {
			return this.itemsBySearchQuery.some((item) => item.selected);
		},
		allItemsActive(): boolean {
			const items = this.itemsBySearchQuery;

			return items.length > 0 && items.every((item) => item.selected);
		},
		toggleAllStatus(): string {
			return this.allItemsActive ? 'checked'
				: (this.hasActiveItems ? 'indeterminate' : 'unchecked');
		},
		selectionLimitReached(): boolean {
			return this.selectedCount >= this.maxSelected;
		},
		Outline() {
			return Outline;
		},
		ChipDesign() {
			return ChipDesign;
		},
	},
	mounted() {
		this.initDragAndDrop();
	},
	methods: {
		handleItemSelect(itemId) {
			const currentItem = this.localItems.find((item) => item.id === itemId);

			if (currentItem.selected)
			{
				currentItem.selected = false;

				return;
			}

			if (!currentItem.selected && this.maxSelected !== null && this.selectionLimitReached)
			{
				return;
			}

			currentItem.selected = true;
		},
		initDragAndDrop() {
			(new Draggable({
				container: this.$refs['items-container'].$el,
				draggable: '.ui-item-selector-item',
				dragElement: '.ui-item-selector-item__drag_element',
			})).subscribe('end', this.onDragEnd.bind(this));
		},
		onDragEnd() {
			const itemsMap = this.getCurrentOrder();

			this.localItems = this.localItems.map((localItem) => {
				const draggedItem = itemsMap.get(localItem.id);

				return draggedItem
					? { ...localItem, sort: draggedItem.sort }
					: localItem;
			});
		},
		toggleAllItems() {
			const shouldActivate = !this.hasActiveItems;

			this.itemsBySearchQuery.forEach((item) => {
				item.selected = shouldActivate;
			});
		},
		toggleSort() {
			if (this.sortDirection === 'asc')
			{
				this.sortDirection = 'desc';
			}
			else
			{
				this.sortDirection = 'asc';
			}
		},
		getCurrentOrder() {
			return new Map(
				[...this.$el.querySelectorAll('.ui-item-selector-item')]
					.map((itemElement, index) => [
						itemElement.dataset.id,
						{
							id: itemElement.dataset.id,
							sort: index + 1,
							selected: itemElement.dataset.selected === 'true',
						},
					]),
			);
		},
		getOrder() {
			this.searchQuery = '';
			this.filteredItems.forEach((item, index) => {
				item.sort = index;
			});

			return this.filteredItems;
		},
	},
	template: `
		<div class="ui-item-selector">
			<div class="ui-item-selector-controls">
				<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100">
					<input
						type="text"
						class="ui-ctl-element ui-ctl-textbox ui-item-selector-search__input"
						:placeholder="$Bitrix.Loc.getMessage('JS_UI_ITEM_SELECTOR_SEARCH_PLACEHOLDER')"
						v-model="searchQuery"
					>
					<BIcon
						class="ui-item-selector-search-icon"
						:name="Outline.SEARCH"
						:size="20"
						color="var(--ui-color-base-4)"
					></BIcon>
				</div>

				<Chip
					:icon="sortDirection === 'asc' ? Outline.LETTER_SORT_DOWN : Outline.LETTER_SORT_UP"
					:design="sortDirection ? ChipDesign.OutlineAccent : ChipDesign.Outline"
					@click="toggleSort"
				/>

				<Checkbox
					:status="toggleAllStatus"
					@change="toggleAllItems"
				/>
			</div>

			<TransitionGroup tag="div" name="ui-item-selector-items" class="ui-item-selector-items" ref="items-container">
				<Item
					v-for="(item) in filteredItems"
					:key="item.id"
					:id="item.id"
					:title="item.title"
					:isSelected="item.selected"
					:isDraggable="!this.searchQuery"
					@update:selected="handleItemSelect(item.id)"
				/>
			</TransitionGroup>
		</div>
	`,
};
