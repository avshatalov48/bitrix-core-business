import './search-result.css';

// @vue/component
export const SearchResult = {
	name: 'search-result',
	props: {
		title: {
			type: String,
			required: true,
		},
		count: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="ui-block-diagram-search-result">
			<div class="ui-block-diagram-search-result__left-col">
				<p class="ui-block-diagram-search-result__title">{{ title }}</p>
			</div>
			<div class="ui-block-diagram-search-result__right-col">
				<span class="ui-block-diagram-search-result__count">{{ count }}</span>
				<div class="ui-block-diagram-search-result__nav">
					<slot/>
				</div>
			</div>
		</div>
	`,
};
