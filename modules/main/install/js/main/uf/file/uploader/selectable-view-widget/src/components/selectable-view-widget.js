import 'ui.entity-editor';

import { ViewSelector } from './view-selector';
import { TileList } from './view/tile/tile-list';
import { List } from './view/list/list';
import { AdaptiveList } from './view/adaptive/adaptive-list';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ViewOptions } from '../types';

import '../css/selectable-view-widget.css';
import 'ui.design-tokens';

const View = BX.UI.EntityEditorUserFieldFileView;

// @vue/component
export const SelectableViewWidget: BitrixVueComponentProps = {
	name: 'SelectableViewWidget',
	components: {
		ViewSelector,
		TileList,
		List,
		AdaptiveList,
	},

	props: {
		fileItems: {
			type: Array,
			required: true,
		},
		viewId: {
			type: [String, null],
			required: false,
			default: null,
		},
	},

	data(): Object
	{
		return {
			currentViewId: this.viewId ?? View.default().id,
			views: View.getAll(),
		};
	},

	computed: {
		component(): BitrixVueComponentProps
		{
			return this.getViewComponentMap().get(this.currentViewId);
		},
	},

	methods: {
		getViewComponentMap(): Map<string, BitrixVueComponentProps>
		{
			return new Map([
				[View.TILE.id, TileList],
				[View.LIST.id, List],
				[View.ADAPTIVE.id, AdaptiveList],
			]);
		},

		handleViewChange(view: ViewOptions): void
		{
			this.currentViewId = view.id;
		},
	},

	// language=Vue
	template: `
		<div class="main-file-field-selectable-view">
			<div class="main-file-field-selectable-view__view-selector-container">
				<ViewSelector :views="views" :view-id="currentViewId" :on-change="handleViewChange" />
			</div>
			<div class="main-file-field-selectable-view__file-list-container">
				<component :is="component" :file-items="fileItems" />
			</div>
		</div>
	`,
};
