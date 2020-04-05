/**
 * Bitrix Messenger
 * File element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './file.css';
import 'ui.vue.directives.lazyload';
import 'ui.icons';

import {Vue} from 'ui.vue';
import {FilesModel} from 'im.model';

Vue.component('bx-messenger-element-file',
{
	props:
	{
		userId: { default: 0 },
		file: {
			type: Object,
			default: FilesModel.create().getElementStore
		},
	},
	methods:
	{
		download(file, event)
		{
			if (file.image && file.urlShow)
			{
				window.open(file.urlShow, '_blank');
			}
			else if (file.video && file.urlShow)
			{
				window.open(file.urlShow, '_blank');
			}
			else if (file.urlDownload)
			{
				window.open(file.urlDownload, '_self');
			}
			else
			{
				window.open(file.urlShow, '_blank');
			}
		},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('IM_MESSENGER_ELEMENT_FILE_', this.$root.$bitrixMessages);
		},
		fileNameLeft()
		{
			let end = this.file.name.length - this.fileNameRight.length;

			return this.file.name.substring(0, end);
		},
		fileNameRight()
		{
			let cutLength = this.file.extension.length+1;
			if (this.file.name.length > 30)
			{
				cutLength = cutLength+5;
			}

			let start = this.file.name.length-1 - cutLength;

			return this.file.name.substring(start);
		},
		fileSize()
		{
			let size = this.file.size;

			let sizes = ["BYTE", "KB", "MB", "GB", "TB"];
			let position = 0;

			while (size >= 1024 && position < 4)
			{
				size /= 1024;
				position++;
			}

			return Math.round(size) + " " + this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_'+sizes[position]];
		}
	},
	template: `
		<div class="bx-im-element-file" @click="download(file, $event)">
			<div class="bx-im-element-file-icon">
				<div :class="['ui-icon', 'ui-icon-file-'+file.icon]"><i></i></div>
			</div>
			<div class="bx-im-element-file-block">
				<div class="bx-im-element-file-name" :title="file.name">
					<span class="bx-im-element-file-name-left">{{fileNameLeft}}</span><span class="bx-im-element-file-name-right">{{fileNameRight}}</span>
				</div>
				<div class="bx-im-element-file-size">{{fileSize}}</div>
			</div>
		</div>
	`
});

Vue.cloneComponent('bx-messenger-element-file-image', 'bx-messenger-element-file',
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
		}
	},
	template: `
		<div class="bx-im-element-file-image" @click="download(file, $event)" :style="styleBoxSizes">
			<img v-bx-lazyload
				class="bx-im-element-file-image-source"
				:data-lazyload-src="fileSource"
				:title="localize.IM_MESSENGER_ELEMENT_FILE_SHOW_TITLE.replace('#NAME#', file.name).replace('#SIZE#', fileSize)"
				:style="styleFileSizes"
			/>
		</div>
	`
});