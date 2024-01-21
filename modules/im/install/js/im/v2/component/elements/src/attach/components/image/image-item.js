import { Dom } from 'main.core';
import { lazyload } from 'ui.vue3.directives.lazyload';

import { Utils } from 'im.v2.lib.utils';

import type { JsonObject } from 'main.core';
import type { AttachImageItemConfig } from 'im.v2.const';

const MAX_IMAGE_SIZE = 272;

// @vue/component
export const AttachImageItem = {
	name: 'AttachImageItem',
	directives: { lazyload },
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachImageItemConfig
		{
			return this.config;
		},
		width(): number
		{
			return this.internalConfig.width || 0;
		},
		height(): number
		{
			return this.internalConfig.height || 0;
		},
		link(): string
		{
			return this.internalConfig.link;
		},
		name(): string
		{
			return this.internalConfig.name;
		},
		preview(): string
		{
			return this.internalConfig.preview;
		},
		source(): string
		{
			return this.preview ?? this.link;
		},
		imageSize(): JsonObject
		{
			if (this.width === 0 || this.height === 0)
			{
				return {};
			}

			const sizes = Utils.file.resizeToFitMaxSize(this.width, this.height, MAX_IMAGE_SIZE);

			return {
				width: `${sizes.width}px`,
				height: `${sizes.height}px`,
				'object-fit': (sizes.width < 100 || sizes.height < 100) ? 'cover' : 'contain',
			};
		},
		hasWidth(): boolean
		{
			return Boolean(this.imageSize.width);
		},
	},
	methods:
	{
		open()
		{
			if (!this.link)
			{
				return;
			}

			window.open(this.link, '_blank');
		},
		lazyLoadCallback(event: {element: HTMLElement, state: string})
		{
			const { element } = event;
			if (!Dom.style(element, 'width'))
			{
				Dom.style(element, 'width', `${element.offsetWidth}px`);
			}

			if (!Dom.style(element, 'height'))
			{
				Dom.style(element, 'height', `${element.offsetHeight}px`);
			}
		},
	},
	template: `
		<div class="bx-im-attach-image__item" :class="{'--with-width': hasWidth }" @click="open">
			<img
				v-lazyload="{callback: lazyLoadCallback}"
				:data-lazyload-src="source"
				:style="imageSize"
				:title="name"
				:alt="name"
				class="bx-im-attach-image__source"
			/>
		</div>
	`,
};
