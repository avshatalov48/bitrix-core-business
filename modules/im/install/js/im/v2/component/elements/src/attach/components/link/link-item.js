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
			return this.internalConfig.link;
		},
		name(): string
		{
			return this.internalConfig.name ?? this.link;
		},
		description(): string
		{
			return this.internalConfig.desc;
		},
		html(): string
		{
			const content = this.internalConfig.html || this.description;

			return Parser.decodeText(content);
		},
		preview(): string
		{
			return this.internalConfig.preview;
		},
		imageConfig(): AttachImageConfig
		{
			return {
				image: [{
					name: this.internalConfig.name,
					preview: this.internalConfig.preview,
					width: this.internalConfig.width,
					height: this.internalConfig.height,
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
			<div v-if="internalConfig.html || description" class="bx-im-attach-link__desc" v-html="html"></div>
			<div v-if="preview" class="bx-im-attach-link__image">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
		</div>
	`
};