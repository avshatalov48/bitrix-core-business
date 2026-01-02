import { BaseItem } from '../base-item';
import { Item } from '../list/item';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const AdaptiveItem: BitrixVueComponentProps = {
	name: 'AdaptiveItem',

	components: {
		Item,
	},

	extends: BaseItem,

	// language=Vue
	template: `
		<div class="main-file-field-selectable-view__adaptive-item">
			<div v-if="file.isImage" class="main-file-field-selectable-view__adaptive-item-image-container">
				<img class="main-file-field-selectable-view__image" :src="file.urlForViewer" :alt="file.name" :title="file.name" />
			</div>
			<Item v-else :file-item="file" />
		</div>
	`,
};
