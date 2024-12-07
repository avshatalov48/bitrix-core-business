import { AttachType, ColorToken } from 'im.v2.const';

import { AttachDelimiter } from './components/delimiter/delimiter';
import { AttachFile } from './components/file/file';
import { AttachGrid } from './components/grid/grid';
import { AttachHtml } from './components/html/html';
import { AttachImage } from './components/image/image';
import { AttachLink } from './components/link/link';
import { AttachMessage } from './components/message/message';
import { AttachRich } from './components/rich/rich';
import { AttachUser } from './components/user/user';

import './attach.css';

import type { AttachConfig, AttachConfigBlock } from 'im.v2.const';

const PropertyToComponentMap = {
	[AttachType.Delimiter]: AttachDelimiter,
	[AttachType.File]: AttachFile,
	[AttachType.Grid]: AttachGrid,
	[AttachType.Html]: AttachHtml,
	[AttachType.Image]: AttachImage,
	[AttachType.Link]: AttachLink,
	[AttachType.Message]: AttachMessage,
	[AttachType.Rich]: AttachRich,
	[AttachType.User]: AttachUser,
};

// @vue/component
export const Attach = {
	name: 'MessengerAttach',
	components: {
		AttachDelimiter,
		AttachFile,
		AttachGrid,
		AttachHtml,
		AttachImage,
		AttachLink,
		AttachMessage,
		AttachRich,
		AttachUser,
	},
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachConfig
		{
			return this.config;
		},
		blocks(): AttachConfigBlock[]
		{
			return this.internalConfig.blocks;
		},
		colorToken(): string
		{
			const { colorToken = ColorToken.base } = this.internalConfig;

			return colorToken;
		},
	},
	methods:
	{
		getComponentForBlock(block: AttachConfigBlock)
		{
			const [blockType] = Object.keys(block);
			if (!PropertyToComponentMap[blockType])
			{
				return '';
			}

			return PropertyToComponentMap[blockType];
		},
	},
	template: `
		<div class="bx-im-attach__container bx-im-attach__scope">
			<div class="bx-im-attach__border" :class="colorToken"></div>
			<div class="bx-im-attach__content">
				<component
					v-for="(block, index) in blocks"
					:is="getComponentForBlock(block)"
					:config="block"
					:colorToken="colorToken"
					:key="index"
					:attachId="internalConfig.id.toString()"
				/>
			</div>
		</div>
	`,
};
