import { Button, ButtonSize, AirButtonStyle, ButtonTag } from 'ui.vue3.components.button';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ViewOptions } from '../types';

// @vue/component
export const ViewSelector: BitrixVueComponentProps = {
	name: 'ViewSelector',

	components: {
		UiButton: Button,
	},

	props: {
		views: {
			type: Array,
			required: true,
		},
		viewId: {
			type: String,
			required: true,
		},
		onChange: {
			type: Function,
			required: false,
			default: () => {},
		},
	},

	setup(): Object
	{
		return {
			ButtonSize,
			AirButtonStyle,
			ButtonTag,
		};
	},

	data(): Object
	{
		return {
			currentViewId: this.viewId,
		};
	},

	methods: {
		handleViewClick(view: ViewOptions): void
		{
			if (this.currentViewId === view.id)
			{
				return;
			}

			this.currentViewId = view.id;
			this.onChange(view);
		},
	},

	// language=Vue
	template: `
		<div class="main-file-field-selectable-view__view-selector">
			<div class="main-file-field-selectable-view__view-selector-items">
				<UiButton
					v-for="view in views"
					@click="handleViewClick(view)"
					:dataset="{ editorControlType: 'button' }"
					:text="view.title"
					:left-icon="view.icon"
					:size="ButtonSize.SMALL"
					:style="currentViewId === view.id ? AirButtonStyle.SELECTION : AirButtonStyle.PLAIN_NO_ACCENT"
				/>
			</div>
		</div>
	`,
};
