import { Core } from 'im.v2.application.core';
import { Color } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { AttachImage } from '../image/image';
import { RichService } from './rich-service';

import type { AttachRichItemConfig, AttachImageConfig } from 'im.v2.const';

// @vue/component
export const AttachRichItem = {
	name: 'AttachRichItem',
	components: { AttachImage },
	inject: ['message'],
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
		color: {
			type: String,
			default: Color.transparent,
		},
		attachId: {
			type: String,
			required: true,
		},
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
				}],
			};
		},
		canShowDeleteIcon(): boolean
		{
			if (!this.message)
			{
				return false;
			}

			return this.message.authorId === Core.getUserId();
		},
		deleteRichLinkTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_LINK_DELETE');
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
		deleteRichLink()
		{
			if (!this.message)
			{
				return;
			}

			(new RichService(this.message)).deleteRichLink(this.attachId);
		},
	},
	template: `
		<div class="bx-im-attach-rich__scope bx-im-attach-rich__container">
			<div class="bx-im-attach-rich__block">
				<div class="bx-im-attach-rich__name" @click="openLink">{{ name }}</div>
				<div v-if="html || description" class="bx-im-attach-rich__desc">{{ html || description }}</div>
				<button 
					v-if="canShowDeleteIcon" 
					class="bx-im-attach-rich__hide-icon"
					@click="deleteRichLink"
					:title="deleteRichLinkTitle"
				></button>
			</div>
			<div v-if="preview" class="bx-im-attach-rich__image" @click="openLink">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
		</div>
	`,
};
