import './search-input.css';
import { BIcon, Outline } from 'ui.icon-set.api.vue';
import { onMounted, useTemplateRef, toValue, computed } from 'ui.vue3';
import { useLoc } from '../../composables';

type SearchInputSetup = {
	iconSet: {...};
	placeholderOrDefaultValue: string;
	onInput: (event: InputEvent) => void;
	onClear: () => void;
};

// @vue/component
export const SearchInput = {
	name: 'search-input',
	components: {
		BIcon,
	},
	props: {
		value: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			default: '',
		},
		focusable: {
			type: Boolean,
			default: false,
		},
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	emit: ['update:value', 'clear'],
	setup(props, { emit }): SearchInputSetup
	{
		const loc = useLoc();
		const searchInput = useTemplateRef('searchInput');

		const placeholderOrDefaultValue = computed((): string => {
			if (props.placeholder)
			{
				return props.placeholder;
			}

			return loc.getMessage('UI_BLOCK_DIAGRAM_SEARCH_BAR_SEARCH_PLACEHOLDER');
		});

		onMounted(() => {
			if (toValue(props.focusable))
			{
				toValue(searchInput).focus();
			}
		});

		function onInput(event: InputEvent): void
		{
			if (props.disabled)
			{
				return;
			}

			emit('update:value', event.target.value);
		}

		function onClear(): void
		{
			if (props.disabled)
			{
				return;
			}

			emit('clear');
		}

		return {
			iconSet: Outline,
			placeholderOrDefaultValue,
			onInput,
			onClear,
		};
	},
	template: `
		<div class="ui-block-diagram-search-input">
			<BIcon
				:name="iconSet.SEARCH"
				:size="24"
				class="ui-block-diagram-search-input__icon"
			/>
			<input
				:value="value"
				:placeholder="placeholderOrDefaultValue"
				:data-test-id="$blockDiagramTestId('searchInput')"
				ref="searchInput"
				type="text"
				class="ui-block-diagram-search-input__input"
				@input="onInput"
			/>
			<button
				class="ui-block-diagram-search-input__clear-btn"
				:data-test-id="$blockDiagramTestId('searchClearInputBtn')"
				@click="onClear"
			>
				<BIcon
					:name="iconSet.CROSS_L"
					:size="24"
					class="ui-block-diagram-search-input__clear-btn-icon"
				/>
			</button>
		</div>
	`,
};
