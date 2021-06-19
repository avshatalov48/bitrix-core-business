/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './image.css';
import 'ui.vue.directives.lazyload';
import 'ui.icons';
import 'ui.vue.components.socialvideo';

import "ui.vue.components.audioplayer";

import {Vue} from 'ui.vue';

Vue.cloneComponent('bx-im-view-element-file-image', 'bx-im-view-element-file',
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
		styleFileSizes()
		{
			let sizes = this.getImageSize(this.file.image.width, this.file.image.height, 280);

			return {
				width: sizes.width+'px',
				height: sizes.height+'px',
				backgroundSize: sizes.width < 100 || sizes.height < 100? 'contain': 'initial'
			}
		},
		styleBoxSizes()
		{
			if (parseInt(this.styleFileSizes.height) <= 280)
			{
				return {};
			}

			return {
				height: '280px'
			}
		},
		fileSource()
		{
			return this.file.urlPreview;
		},
	},
	template: `
		<div class="bx-im-element-file-image" @click="download(file, $event)" :style="styleBoxSizes" ref="container">
			<img v-bx-lazyload
				class="bx-im-element-file-image-source"
				:data-lazyload-src="fileSource"
				:title="$Bitrix.Loc.getMessage('IM_MESSENGER_ELEMENT_FILE_SHOW_TITLE').replace('#NAME#', file.name).replace('#SIZE#', fileSize)"
				:style="styleFileSizes"
				:data-viewer="file.viewerAttrs.viewer === null"
				:data-viewer-type="file.viewerAttrs.viewerType? file.viewerAttrs.viewerType: false"
				:data-src="file.viewerAttrs.src? file.viewerAttrs.src: false"
				:data-viewer-group-by="file.viewerAttrs.viewerGroupBy? file.viewerAttrs.viewerGroupBy: false"
				:data-title="file.viewerAttrs.title? file.viewerAttrs.title: false"
				:data-actions="file.viewerAttrs.actions? file.viewerAttrs.actions: false"
			/>
		</div>
	`
});