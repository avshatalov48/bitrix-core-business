import {Color} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import {AttachImage} from '../image/image';

import type {AttachRichItemConfig, AttachImageConfig} from 'im.v2.const';

// @vue/component
export const AttachRichItem = {
	name: 'AttachRichItem',
	components: {AttachImage},
	props:
	{
		config: {
			type: Object,
			default: () => {}
		},
		color: {
			type: String,
			default: Color.transparent
		}
	},
	computed:
	{
		internalConfig(): AttachRichItemConfig
		{
			return this.config;
		},
		link(): string
		{
			return this.internalConfig.LINK;
		},
		name(): string
		{
			return Utils.text.convertHtmlEntities(this.internalConfig.NAME);
		},
		description(): string
		{
			return Utils.text.convertHtmlEntities(this.internalConfig.DESC);
		},
		html(): string
		{
			return this.internalConfig.HTML;
		},
		preview(): string
		{
			return this.internalConfig.PREVIEW;
		},
		imageConfig(): AttachImageConfig
		{
			return {
				IMAGE: [{
					NAME: this.name,
					PREVIEW: this.preview,
				}]
			};
		},
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
		<div class="bx-im-attach-rich__item">
			<div v-if="preview" class="bx-im-attach-rich__image">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
			<div class="bx-im-attach-rich__block">
				<div class="bx-im-attach-rich__name" @click="openLink">{{ name }}</div>
				<div v-if="html || description" class="bx-im-attach-rich__desc">{{ html || description }}</div>
			</div>
		</div>
	`
};