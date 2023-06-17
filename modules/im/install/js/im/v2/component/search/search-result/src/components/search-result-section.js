import {ExpandAnimation} from 'im.v2.component.animation';
import '../css/search-result-section.css';
import type {SearchItem} from '../classes/search-item';

export const SearchResultSection = {
	name: 'SearchResultSection',
	components: {ExpandAnimation},
	props: {
		component: {
			type: Object,
			required: true,
		},
		items: {
			type: Object, // Map<string, SearchItem>
			required: true
		},
		title: {
			type: String,
			default: ''
		},
		showMoreButton: {
			type: Boolean,
			default: true,
			required: false
		},
		minItems: {
			type: Number,
			default: 10,
			required: false
		},
		maxItems: {
			type: Number,
			default: 50,
			required: false
		},
		canBeFolded: {
			type: Boolean,
			default: true,
			required: false
		},
		selectMode: {
			type: Boolean,
			default: false,
		},
		selectedItems: {
			type: Array,
			default: () => []
		}
	},
	emits: ['clickItem'],
	data: function ()
	{
		return {
			expanded: false,
			folded: false,
		};
	},
	computed:
	{
		showMore(): boolean
		{
			if (!this.showMoreButton)
			{
				return false;
			}

			return this.items.size > this.minItems;
		},
		showMoreButtonText(): string
		{
			return this.expanded
				? this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_LESS')
				: this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_MORE')
				;
		},
		sectionItems(): SearchItem[]
		{
			const itemsFromMap = [...this.items.values()];
			if (!this.showMoreButton)
			{
				return itemsFromMap;
			}

			return this.expanded ? itemsFromMap.slice(0, this.maxItems) : itemsFromMap.slice(0, this.minItems);
		},
	},
	methods:
	{
		onFoldSection()
		{
			if (!this.canBeFolded)
			{
				return;
			}

			this.folded = !this.folded;
		},
		onShowMore()
		{
			this.expanded = !this.expanded;
		},
		isSelected(item: SearchItem): boolean
		{
			return this.selectedItems.includes(item.getEntityFullId());
		}
	},
	template: `
		<div class="bx-im-search-result-section__container bx-im-search-result-section__scope">
			<div 
				v-if="title" 
				class="bx-im-search-result-section__title-container" 
				:class="{'--down': !folded, '--foldable': canBeFolded}"
				@click="onFoldSection"
			>
				<span 
					class="bx-im-search-result-section__title-text"
					:class="{'--icon': canBeFolded}"
				>
					{{title}}
				</span>
			</div>
			<ExpandAnimation>
				<div v-if="!folded" class="bx-im-search-result-section__items-container">
					<component 
						:is="component"
						v-for="item in sectionItems" 
						:key="item.getEntityFullId()" 
						:item="item" 
						:selectMode="selectMode"
						:isSelected="isSelected(item)"
						@clickItem="$emit('clickItem', $event)"
					/>
					<button 
						v-if="showMore" 
						class="bx-im-search-result-section__show-more" 
						@click.prevent="onShowMore"
					>
						{{ showMoreButtonText }}
					</button>
				</div>
			</ExpandAnimation>
		</div>
	`
};