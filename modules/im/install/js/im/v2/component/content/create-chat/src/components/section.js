import {ExpandAnimation} from 'im.v2.component.animation';

import '../css/section.css';

// @vue/component
export const Section = {
	components: {ExpandAnimation},
	props: {
		name: {
			type: String,
			required: true
		},
		title: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {
			isFolded: true
		};
	},
	computed:
	{
		containerClasses()
		{
			return [`--${this.name}`, {'--active': !this.isFolded}];
		}
	},
	methods:
	{
		onContainerClick()
		{
			if (this.isFolded)
			{
				this.isFolded = false;
			}
		},
		onHeaderClick()
		{
			if (!this.isFolded)
			{
				this.isFolded = true;
			}
		}
	},
	template: `
		<div :class="containerClasses" class="bx-im-content-create-chat__section bx-im-content-create-chat__section_scope">
			<div @click="isFolded = !isFolded" class="bx-im-content-create-chat__section_header">
				<div class="bx-im-content-create-chat__section_left">
					<div class="bx-im-content-create-chat__section_icon"></div>
					<div class="bx-im-content-create-chat__section_text">{{ title }}</div>
				</div>
				<div class="bx-im-content-create-chat__section_right"></div>	
			</div>
			<ExpandAnimation>
				<div v-if="!isFolded" class="bx-im-content-create-chat__section_content_container">
					<div class="bx-im-content-create-chat__section_content">
						<slot></slot>
					</div>
				</div>
			</ExpandAnimation>
		</div>
	`
};