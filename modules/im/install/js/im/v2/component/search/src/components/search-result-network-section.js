import {SearchResultNetworkItem} from './search-result-network-item';
import '../css/search.css';

// @vue/component
export const SearchResultNetworkSection = {
	name: 'SearchResultNetworkSection',
	components: {SearchResultNetworkItem},
	props: {
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
			default: 5,
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
	// language=Vue
	template: `
		<div class="bx-messenger-search-result-section-wrapper">
			<div class="bx-messenger-search-result-section-title">
				{{title}}
			</div>
			<div>
				<SearchResultNetworkItem v-for="item in sectionItems" :key="item" :item="item" />
			</div>
			<div v-if="showMore" class="bx-messenger-search-result-section-show-more" @click.prevent="onShowMore">
				{{ showMoreButtonText }}
			</div>
		</div>
	`
};