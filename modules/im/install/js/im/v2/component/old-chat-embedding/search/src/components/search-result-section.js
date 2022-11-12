import '../css/search.css';

export const SearchResultSection = {
	name: 'SearchResultSection',
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
			required: true
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
		}
	},
	data: function ()
	{
		return {
			expanded: false,
		};
	},
	computed:
	{
		showMore()
		{
			if (!this.showMoreButton)
			{
				return false;
			}

			return this.items.size > this.minItems;
		},
		showMoreButtonText()
		{
			return this.expanded
				? this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_LESS')
				: this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_MORE')
			;
		},
		sectionItems()
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
		onShowMore()
		{
			this.expanded = !this.expanded;
		}
	},
	template: `
		<div class="bx-messenger-search-result-section-wrapper">
			<div class="bx-messenger-search-result-section-title">{{title}}</div>
			<div>
				<component :is="component" v-for="item in sectionItems" :key="item.getEntityFullId()" :item="item" />
			</div>
			<div v-if="showMore" class="bx-messenger-search-result-section-show-more" @click.prevent="onShowMore">
				{{ showMoreButtonText }}
			</div>
		</div>
	`
};