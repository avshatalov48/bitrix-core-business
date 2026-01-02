import { BaseList } from '../base-list';
import { Item } from './item';
import { ViewerDecorator } from '../../viewer-decorator';

import type { BitrixVueComponentProps } from 'ui.vue3';

import '../../../css/view/list.css';
import 'ui.fonts.inter';

// @vue/component
export const List: BitrixVueComponentProps = {
	// eslint-disable-next-line vue/multi-word-component-names
	name: 'List',

	components: {
		ViewerDecorator,
		Item,
	},

	extends: BaseList,

	// language=Vue
	template: `
		<div class='main-file-field-selectable-view__list'>
			<ViewerDecorator v-for="file in files" :file-item="file">
				<Item :file-item="file"/>
			</ViewerDecorator>
		</div>
	`,
};
