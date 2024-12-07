import { Dom, type JsonObject } from 'main.core';

import './css/fade.css';

// @vue/component
export const FadeAnimation = {
	name: 'FadeAnimation',
	props:
	{
		duration: {
			type: Number,
			default: 100,
		},
	},
	emits: ['afterEnter'],
	computed:
	{
		formattedDuration(): string
		{
			return `${this.duration}ms`;
		},
	},
	methods:
	{
		setDuration(element: HTMLElement)
		{
			Dom.style(element, 'transition-duration', this.formattedDuration);
		},
		clearDuration(element: HTMLElement)
		{
			Dom.style(element, 'transition-duration', '');
		},
		onAfterEnter(element: HTMLElement)
		{
			this.$emit('afterEnter');
			this.clearDuration(element);
		},
	},
	template: `
		<Transition
			name="im-animation-fade"
			@beforeEnter="setDuration"
			@afterEnter="onAfterEnter"
			@beforeLeave="setDuration"
			@afterLeave="clearDuration"
		>
			<slot></slot>
		</Transition>
	`,
};
