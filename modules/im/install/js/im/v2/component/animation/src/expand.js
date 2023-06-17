import {Dom} from 'main.core';

// @vue/component
export const ExpandAnimation = {
	props:
	{
		duration: {
			type: Number,
			default: 300
		}
	},
	methods:
	{
		onBeforeEnter(element)
		{
			Dom.style(element, 'overflow', 'hidden');
			Dom.style(element, 'transition', `height ${this.duration}ms, opacity ${this.duration}ms`);
		},
		onBeforeLeave(element)
		{
			this.onBeforeEnter(element);
		},
		onEnter(element)
		{
			Dom.style(element, 'height', 0);
			Dom.style(element, 'opacity', 0);

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					Dom.style(element, 'opacity', 1);
					Dom.style(element, 'height', `${element.scrollHeight}px`);
				});
			});
		},
		onAfterEnter(element)
		{
			Dom.style(element, 'height', 'auto');
		},
		onLeave(element)
		{
			Dom.style(element, 'height', `${element.scrollHeight}px`);

			requestAnimationFrame(() => {
				Dom.style(element, 'height', 0);
				Dom.style(element, 'opacity', 0);
			});
		}
	},
	template: `
		<Transition
			@before-enter="onBeforeEnter"
			@enter="onEnter"
			@after-enter="onAfterEnter"
			@before-leave="onBeforeLeave"
			@leave="onLeave"
		>
			<slot></slot>
		</Transition>
	`
};