import {SearchResultItem} from './search-result-item';
import {SearchResultDepartmentItem} from './search-result-department-item';
import {BitrixVue} from 'ui.vue3';
import '../css/search.css';

// @vue/component
export const SearchResultSection = {
	name: 'SearchResultSection',
	components: {SearchResultItem, SearchResultDepartmentItem},
	props: {
		items: {
			type: Array,
			required: true
		},
		hasChildren: {
			type: Boolean,
			default: false,
			required: false
		},
		title: {
			type: String,
			required: true
		},
		showMore: {
			type: Boolean,
			default: false,
			required: false
		},
	},
	computed:
	{
		phrases()
		{
			return BitrixVue.getFilteredPhrases(this, 'IM_SEARCH_');
		},
	},
	// language=Vue
	template: `
		<div class="bx-messenger-search-result-section-wrapper">
			<div class="bx-messenger-search-result-section-title">
				<div>{{title}}</div>
				<div v-if="showMore" class="bx-messenger-search-result-section-show-more">
					{{phrases['IM_SEARCH_SECTION_TITLE_SHOW_MORE']}}
				</div>
			</div>
			<template v-if="!hasChildren">
				<SearchResultItem v-for="item in items" :key="item" :dialogId="item" />
			</template>
			<template v-else>
				<SearchResultDepartmentItem v-for="item in items" :key="item" :item="item" />
			</template>
		</div>
	`
};