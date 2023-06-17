import {Dom} from 'main.core';
import {lazyload} from 'ui.vue3.directives.lazyload';

import type {AttachImageItemConfig} from 'im.v2.const';

const MAX_IMAGE_SIZE = 250;

// @vue/component
export const AttachImageItem = {
	name: 'AttachImageItem',
	directives:
	{
		lazyload
	},
	props:
	{
		config: {
			type: Object,
			default: () => {}
		}
	},
	computed:
	{
		internalConfig(): AttachImageItemConfig
		{
			return this.config;
		},
		width(): number
		{
			return this.internalConfig.WIDTH;
		},
		height(): number
		{
			return this.internalConfig.HEIGHT;
		},
		link(): string
		{
			return this.internalConfig.LINK;
		},
		name(): string
		{
			return this.internalConfig.NAME;
		},
		preview(): string
		{
			return this.internalConfig.PREVIEW;
		},
		source()
		{
			return this.preview ?? this.link;
		},
		imageSize(): Object
		{
			if (!this.width && !this.height)
			{
				return {};
			}

			const aspectRatio = this.width > MAX_IMAGE_SIZE ? MAX_IMAGE_SIZE / this.width : 1;

			const sizes = {
				width: this.width * aspectRatio,
				height: this.height * aspectRatio
			};

			return {
				width: `${sizes.width}px`,
				height: `${sizes.height}px`,
				backgroundSize: (sizes.width < 100 || sizes.height < 100)? 'contain': 'initial'
			};
		}
	},
	methods:
	{
		open()
		{
			if (!this.link)
			{
				return false;
			}

			window.open(this.link, '_blank');
		},
		lazyLoadCallback(event: {element: HTMLElement, state: string})
		{
			const {element} = event;
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
		<div class="bx-im-attach-image__item" @click="open">
			<img
				v-lazyload="{callback: lazyLoadCallback}"
				:data-lazyload-src="source"
				:style="imageSize"
				:title="name"
				:alt="name"
				class="bx-im-attach-image__source"
			/>
		</div>
	`
};