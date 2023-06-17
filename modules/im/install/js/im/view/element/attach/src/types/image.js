/**
 * Bitrix Messenger
 * Vue component
 *
 * Image (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./image.css";
import {Utils} from "im.lib.utils";
import 'ui.vue.directives.lazyload';

export const AttachTypeImage =
{
	property: 'IMAGE',
	name: 'bx-im-view-element-attach-image',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		methods:
		{
			open(file)
			{
				if (!file)
				{
					return false;
				}

				if (Utils.platform.isBitrixMobile())
				{
					// TODO add multiply
					BXMobileApp.UI.Photo.show({photos: [{url: file}], default_photo: file})
				}
				else
				{
					window.open(file, '_blank');
				}
			},
			getImageSize(width, height, maxWidth)
			{
				let aspectRatio;

				if (width > maxWidth)
				{
					aspectRatio = maxWidth / width;
				}
				else
				{
					aspectRatio = 1;
				}

				return {
					width: width * aspectRatio,
					height: height * aspectRatio
				};
			},
			getElementSource(element)
			{
				return element.PREVIEW? element.PREVIEW: element.LINK;
			},
			lazyLoadCallback(event)
			{
				if (!event.element.style.width)
				{
					event.element.style.width = event.element.offsetWidth+'px';
				}
				if (!event.element.style.height)
				{
					event.element.style.height = event.element.offsetHeight+'px';
				}
			},
			styleFileSizes(image)
			{
				if (!(image.WIDTH && image.HEIGHT))
				{
					return {
						maxHeight: '100%',
						backgroundSize: 'contain'
					};
				}

				let sizes = this.getImageSize(image.WIDTH, image.HEIGHT, 250);

				return {
					width: sizes.width+'px',
					height: sizes.height+'px',
					backgroundSize: sizes.width < 100 || sizes.height < 100? 'contain': 'initial'
				}
			},
			styleBoxSizes(image)
			{
				if (!(image.WIDTH && image.HEIGHT))
				{
					return {
						height: '150px'
					};
				}

				if (parseInt(this.styleFileSizes(image).height) <= 250)
				{
					return {};
				}

				return {
					height: '280px'
				}
			},
		},
		template: `
			<div class="bx-im-element-attach-type-image">
				<template v-for="(image, index) in config.IMAGE">
					<div class="bx-im-element-attach-type-image-block" @click="open(image.LINK)" :style="styleBoxSizes(image)" :key="index">
						<img v-bx-lazyload="{callback: lazyLoadCallback}"
							class="bx-im-element-attach-type-image-source"
							:data-lazyload-src="getElementSource(image)"
							:style="styleFileSizes(image)"
							:title="image.NAME"
						/>
					</div>
				</template>
			</div>
		`
	},
};