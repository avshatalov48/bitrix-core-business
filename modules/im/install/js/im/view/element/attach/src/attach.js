/**
 * Bitrix Messenger
 * Attach element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './attach.css';

import {AttachTypeDelimiter} from './types/delimiter';
import {AttachTypeFile} from './types/file';
import {AttachTypeGrid} from './types/grid';
import {AttachTypeHtml} from './types/html';
import {AttachTypeImage} from './types/image';
import {AttachTypeLink} from './types/link';
import {AttachTypeMessage} from './types/message';
import {AttachTypeRich} from './types/rich';
import {AttachTypeUser} from './types/user';

import {BitrixVue} from 'ui.vue';

const AttachTypes = [
	AttachTypeDelimiter,
	AttachTypeFile,
	AttachTypeGrid,
	AttachTypeHtml,
	AttachTypeImage,
	AttachTypeLink,
	AttachTypeMessage,
	AttachTypeRich,
	AttachTypeUser
];

const AttachComponents = {};
AttachTypes.forEach(attachType => {
	AttachComponents[attachType.name] = attachType.component;
});

BitrixVue.component('bx-im-view-element-attach',
{
	props:
	{
		config: {type: Object, default: {}},
		baseColor: {type: String, default: '#17a3ea'},
	},
	methods:
	{
		getComponentForBlock(block)
		{
			for (let attachType of AttachTypes)
			{
				if (typeof block[attachType.property] !== 'undefined')
				{
					return attachType.name;
				}
			}

			return '';
		}
	},
	computed:
	{
		color()
		{
			if (
				typeof(this.config.COLOR) === 'undefined'
				|| !this.config.COLOR
			)
			{
				return this.baseColor;
			}

			if (this.config.COLOR === 'transparent')
			{
				return '';
			}

			return this.config.COLOR;
		},
	},
	components: AttachComponents,
	template: `
		<div class="bx-im-element-attach">
			<div v-if="color" class="bx-im-element-attach-border" :style="{borderColor: color}"></div>
			<div class="bx-im-element-attach-content">
				<template v-for="(block, index) in config.BLOCKS">
					<component :is="getComponentForBlock(block)" :config="block" :color="color" :key="index" />
				</template>
			</div>
		</div>
	`
});