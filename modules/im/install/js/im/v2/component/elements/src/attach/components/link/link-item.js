import {Parser} from 'im.v2.lib.parser';
import {Color} from 'im.v2.const';

import {AttachImage} from '../image/image';

import type {AttachLinkItemConfig, AttachImageConfig} from 'im.v2.const';

// @vue/component
export const AttachLinkItem = {
	name: 'AttachLinkItem',
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
		internalConfig(): AttachLinkItemConfig
		{
			return this.config;
		},
		link(): string
		{
			return this.internalConfig.LINK;
		},
		name(): string
		{
			return this.internalConfig.NAME ?? this.link;
		},
		description(): string
		{
			return this.internalConfig.DESC;
		},
		html(): string
		{
			const content = this.internalConfig.HTML || this.description;

			return Parser.decodeText(content);
		},
		preview(): string
		{
			return this.internalConfig.PREVIEW;
		},
		imageConfig(): AttachImageConfig
		{
			return {
				IMAGE: [{
					NAME: this.internalConfig.NAME,
					PREVIEW: this.internalConfig.PREVIEW,
					WIDTH: this.internalConfig.WIDTH,
					HEIGHT: this.internalConfig.HEIGHT,
				}]
			};
		}
	},
	template: `
		<div class="bx-im-attach-link__item">
			<a v-if="link" :href="link" target="_blank" class="bx-im-attach-link__link">
				{{ name }}
			</a>
			<span v-else class="bx-im-attach-link__name">
				{{ name }}
			</span>
			<div v-if="internalConfig.HTML || description" class="bx-im-attach-link__desc" v-html="html"></div>
			<div v-if="preview" class="bx-im-attach-link__image">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
		</div>
	`
};