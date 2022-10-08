/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './video.css';
import 'ui.vue.directives.lazyload';
import 'ui.icons';
import "ui.vue.components.socialvideo";
import {Utils} from "im.lib.utils";

import {Vue} from 'ui.vue';

Vue.cloneComponent('bx-im-view-element-file-video', 'bx-im-view-element-file',
{
	methods:
	{
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
		}
	},
	computed:
	{
		isSafari()
		{
			return Utils.browser.isSafari() || Utils.platform.isBitrixMobile();
		},

		styleBoxSizes()
		{
			if (parseInt(this.styleVideoSizes.height) <= 280)
			{
				return {};
			}

			return {
				height: '280px'
			}
		},
		styleVideoSizes()
		{
			if (!this.file.image)
			{
				return {};
			}

			let sizes = this.getImageSize(this.file.image.width, this.file.image.height, 280);

			return {
				width: sizes.width+'px',
				height: sizes.height+'px',
				backgroundSize: sizes.width < 100 || sizes.height < 100? 'contain': 'initial'
			}
		},
		autoplay()
		{
			return this.file.size < 5000000 && this.application.options.autoplayVideo;
		}
	},
	template: `
		<div :class="['bx-im-element-file-video', {'bx-im-element-file-video-safari': isSafari}]" :style="styleBoxSizes" ref="container">
			<bx-socialvideo 
				:id="file.id" 
				:src="file.urlShow" 
				:preview="file.urlPreview" 
				:containerStyle="styleBoxSizes"
				:elementStyle="styleVideoSizes"
				:autoplay="autoplay"
				:showControls="!file.viewerAttrs.viewerType"
				:data-viewer="file.viewerAttrs.viewer === null"
				:data-viewer-type="file.viewerAttrs.viewerType? file.viewerAttrs.viewerType: false"
				:data-src="file.viewerAttrs.src? file.viewerAttrs.src: false"
				:data-viewer-group-by="file.viewerAttrs.viewerGroupBy? file.viewerAttrs.viewerGroupBy: false"
				:data-title="file.viewerAttrs.title? file.viewerAttrs.title: false"
				:data-actions="file.viewerAttrs.action? file.viewerAttrs.actions: false"
				@click="download(file, $event)"
			/>
		</div>
	`
});