import { AttachFileItem } from './file-item';

import 'ui.icons.disk';
import './file.css';

import type { AttachFileConfig } from 'im.v2.const';

// @vue/component
export const AttachFile = {
	name: 'AttachFile',
	components: { AttachFileItem },
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachFileConfig
		{
			return this.config;
		},
	},
	template: `
		<div class="bx-im-attach-file__container">
			<AttachFileItem
				v-for="(fileItem, index) in internalConfig.file"
				:config="fileItem"
				:key="index"
			/>
		</div>
	`,
};
