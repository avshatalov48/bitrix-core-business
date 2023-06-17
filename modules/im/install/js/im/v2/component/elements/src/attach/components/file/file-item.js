import {Utils} from 'im.v2.lib.utils';

import 'ui.icons.disk';
import './file.css';

import type {AttachFileItemConfig} from 'im.v2.const';

export const AttachFileItem = {
	name: 'AttachFileItem',
	props:
	{
		config: {
			type: Object,
			default: () => {}
		}
	},
	computed:
	{
		internalConfig(): AttachFileItemConfig
		{
			return this.config;
		},
		fileName(): string
		{
			return this.internalConfig.NAME;
		},
		fileSize(): number
		{
			return this.internalConfig.SIZE;
		},
		link()
		{
			return this.internalConfig.LINK;
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 70;

			return Utils.file.getShortFileName(this.fileName, NAME_MAX_LENGTH);
		},
		formattedFileSize(): string
		{
			return Utils.file.formatFileSize(this.fileSize);
		},
		iconClasses()
		{
			return ['ui-icon', `ui-icon-file-${this.fileIcon}`];
		},
		fileIcon(): string
		{
			return Utils.file.getIconTypeByFilename(this.fileName);
		}
	},
	methods:
	{
		openLink()
		{
			if (!this.link)
			{
				return false;
			}

			window.open(this.link, '_blank');
		}
	},
	template: `
		<div @click="openLink" class="bx-im-attach-file__container">
			<div class="bx-im-attach-file__item">
				<div class="bx-im-attach-file__icon">
					<div :class="iconClasses"><i></i></div>
				</div>
				<div class="bx-im-attach-file__block">
					<div class="bx-im-attach-file__name" :title="fileName">
						{{ fileShortName }}
					</div>
					<div class="bx-im-attach-file__size">
						{{ formattedFileSize }}
					</div>
				</div>
			</div>
		</div>
	`
};