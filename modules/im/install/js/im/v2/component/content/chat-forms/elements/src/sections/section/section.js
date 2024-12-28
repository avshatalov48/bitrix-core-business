import { ExpandAnimation } from 'im.v2.component.animation';

import './css/section.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const CreateChatSection = {
	components: { ExpandAnimation },
	props: {
		name: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		alwaysOpened: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isOpened: false,
		};
	},
	computed:
	{
		containerClasses(): string[]
		{
			return [`--${this.name}`, { '--active': this.isOpened }];
		},
	},
	created()
	{
		if (this.alwaysOpened)
		{
			this.isOpened = true;
		}
	},
	methods:
	{
		onHeaderClick()
		{
			if (this.alwaysOpened)
			{
				return;
			}
			this.isOpened = !this.isOpened;
		},
	},
	template: `
		<div :class="containerClasses" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope">
			<div @click="onHeaderClick" class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div v-if="!alwaysOpened" class="bx-im-content-create-chat__section_right"></div>	
			</div>
			<ExpandAnimation>
				<div v-if="isOpened" class="bx-im-content-create-chat__section_content_container">
					<div class="bx-im-content-create-chat__section_content">
						<slot></slot>
					</div>
				</div>
			</ExpandAnimation>
		</div>
	`,
};
