import './scroll-with-gradient.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ScrollWithGradient = {
	name: 'ScrollWithGradient',
	props:
	{
		containerMaxHeight: {
			type: Number,
			default: 0,
			required: false,
		},
		gradientHeight: {
			type: Number,
			default: 0,
		},
		withShadow: {
			type: Boolean,
			default: true,
		},
	},
	data(): JsonObject
	{
		return {
			showTopGradient: false,
			showBottomGradient: false,
		};
	},
	computed:
	{
		contentHeightStyle(): JsonObject
		{
			if (!this.containerMaxHeight)
			{
				return { height: '100%' };
			}

			return { maxHeight: `${this.containerMaxHeight}px` };
		},
		gradientHeightStyle(): JsonObject
		{
			return {
				maxHeight: `${this.gradientHeightStyle}px`,
			};
		},
	},
	mounted()
	{
		// const container = this.$refs['scroll-container'];
		// this.showBottomGradient = container.scrollHeight > container.clientHeight;
	},
	methods:
	{
		onScroll(event: Event)
		{
			this.$emit('scroll', event);
			const scrollPosition = Math.ceil(event.target.scrollTop + event.target.clientHeight);
			this.showBottomGradient = scrollPosition !== event.target.scrollHeight;

			if (event.target.scrollTop === 0)
			{
				this.showTopGradient = false;

				return;
			}

			this.showTopGradient = true;
		},
	},
	template: `
		<div class="bx-im-scroll-with-gradient__container">
			<Transition name="gradient-fade">
				<div v-if="showTopGradient" class="bx-im-scroll-with-gradient__gradient --top" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
			<div 
				class="bx-im-scroll-with-gradient__content" 
				:style="contentHeightStyle" 
				@scroll="onScroll"
				ref="scroll-container"
			>
				<slot></slot>
			</div>
			<Transition name="gradient-fade">
				<div v-if="showBottomGradient" class="bx-im-scroll-with-gradient__gradient --bottom" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
		</div>
	`,
};
