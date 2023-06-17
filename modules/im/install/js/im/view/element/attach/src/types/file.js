/**
 * Bitrix Messenger
 * Vue component
 *
 * File (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./file.css";
import {Vue} from "ui.vue";
import {FilesModel} from "im.model";
import {Utils} from "im.lib.utils";
import "ui.icons.disk";

export const AttachTypeFile =
{
	property: 'FILE',
	name: 'bx-im-element-attach-file',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		methods:
		{
			openLink(element)
			{
				Utils.platform.openNewPage(element.LINK);
			},
			file()
			{
				return {
					name: this.config.FILE.NAME,
					extension: this.config.FILE.NAME.split('.').splice(-1)[0],
					size: this.config.FILE.SIZE,
				};
			},
			fileName(element)
			{
				let maxLength = 70;

				if (!element.NAME || element.NAME.length < maxLength)
				{
					return element.NAME;
				}

				let endWordLength = 10;

				let extension = element.NAME.split('.').splice(-1)[0];
				let secondPart = element.NAME.substring(element.NAME.length-1 - (extension.length+1+endWordLength));
				let firstPart = element.NAME.substring(0, maxLength-secondPart.length-3);

				return firstPart.trim()+'...'+secondPart.trim();
			},
			fileNameFull(element)
			{
				return element.NAME;
			},
			fileSize(element)
			{
				let size = element.SIZE;
				if (!size || size <= 0)
				{
					size = 0;
				}

				let sizes = ["BYTE", "KB", "MB", "GB", "TB"];
				let position = 0;

				while (size >= 1024 && position < 4)
				{
					size /= 1024;
					position++;
				}

				return Math.round(size) + " " + this.$Bitrix.Loc.getMessage('IM_MESSENGER_ATTACH_FILE_SIZE_'+sizes[position]);
			},
			fileIcon(element)
			{
				return FilesModel.getIconType(element.NAME.split('.').splice(-1)[0]);
			}
		},
		template: `
			<div class="bx-im-element-attach-type-file-element">
				<template v-for="(element, index) in config.FILE">
					<div class="bx-im-element-attach-type-file" @click="openLink(element)">
						<div class="bx-im-element-attach-type-file-icon">
							<div :class="['ui-icon', 'ui-icon-file-'+fileIcon(element)]"><i></i></div>
						</div>
						<div class="bx-im-element-attach-type-file-block">
							<div class="bx-im-element-attach-type-file-name" :title="fileNameFull(element)">
								{{fileName(element)}}
							</div>
							<div class="bx-im-element-attach-type-file-size">{{fileSize(element)}}</div>
						</div>
					</div>
				</template>
			</div>
		`
	},
};