import { Type } from 'main.core';
import 'ui.icons.disk';

import { Utils } from 'im.v2.lib.utils';

import './file.css';

import type { AttachFileItemConfig } from 'im.v2.const';

export const AttachFileItem = {
	name: 'AttachFileItem',
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachFileItemConfig
		{
			return this.config;
		},
		fileName(): ?string
		{
			return this.internalConfig.name;
		},
		fileSize(): ?number
		{
			return this.internalConfig.size;
		},
		link(): string
		{
			return this.internalConfig.link;
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 70;

			const fileName: string = Type.isStringFilled(this.fileName)
				? this.fileName
				: this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_FILE_NO_NAME')
			;

			return Utils.file.getShortFileName(fileName, NAME_MAX_LENGTH);
		},
		formattedFileSize(): string
		{
			if (!this.fileSize)
			{
				return '';
			}

			return Utils.file.formatFileSize(this.fileSize);
		},
		iconClasses()
		{
			return ['ui-icon', `ui-icon-file-${this.fileIcon}`];
		},
		fileIcon(): string
		{
			return Utils.file.getIconTypeByFilename(this.fileName);
		},
	},
	methods:
	{
		openLink()
		{
			if (!this.link)
			{
				return;
			}

			window.open(this.link, '_blank');
		},
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
	`,
};
