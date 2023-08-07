import { BitrixVue } from 'ui.vue3';
import { lazyload } from 'ui.vue3.directives.lazyload';

import { File } from './file';

import './css/image.css';

const MAX_IMAGE_SIZE = 250;

// @vue/component
export const Image = BitrixVue.cloneComponent(File, {
	name: 'ImageComponent',
	directives: { lazyload },
	computed:
	{
		imageSize(): {width: string, height: string, backgroundSize: string}
		{
			const aspectRatio = this.file.width > MAX_IMAGE_SIZE ? MAX_IMAGE_SIZE / this.file.width : 1;

			const sizes = {
				width: this.file.width * aspectRatio,
				height: this.file.height * aspectRatio,
			};

			return {
				width: `${sizes.width}px`,
				height: `${sizes.height}px`,
				backgroundSize: (sizes.width < 100 || sizes.height < 100) ? 'contain' : 'initial',
			};
		},
	},
	template: `
		<div v-bind="viewerAttributes" class="bx-im-media-image__container" @click="download" ref="container">
			<img
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="file.urlPreview"
				:title="loc('IM_ELEMENTS_MEDIA_IMAGE_TITLE', {'#NAME#': file.name, '#SIZE#': file.size})"
				:style="imageSize"
				:alt="file.name"
				class="bx-im-media-image__source"
			/>
		</div>
	`,
});
