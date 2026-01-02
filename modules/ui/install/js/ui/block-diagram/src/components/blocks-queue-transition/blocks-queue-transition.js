import './blocks-queue-transition.css';
import { toValue } from 'ui.vue3';
import { useBlockDiagram, useCanvas, useHistory, useHighlightedBlocks } from '../../composables';

type BlocksQueueTransitionSetup = {
	isAnimate: boolean;
	onEnter: () => void;
	onAfterEnter: () => void;
	onBeforeEnter: () => void;
	onAfterLeave: () => void;
};

// @vue/component
export const BlocksQueueTransition = {
	name: 'blocks-queue-transition',
	setup(): BlocksQueueTransitionSetup
	{
		const {
			isAnimate,
			currentAnimationItem,
			animationQueue,
			hooks,
		} = useBlockDiagram();
		const canvas = useCanvas();
		const history = useHistory();
		const highlighted = useHighlightedBlocks();

		function nextAnimationItem()
		{
			const { done = false } = toValue(animationQueue)?.next() ?? {};

			if (done)
			{
				animationQueue.value = null;
			}
			else
			{
				history.makeSnapshot();
			}
		}

		function onBeforeEnter(): void
		{
			hooks.blockTransitionStart.trigger(toValue(currentAnimationItem)?.item);
		}

		function onEnter(): void
		{
			highlighted.clear();
			highlighted.add(toValue(currentAnimationItem).item.id);
			canvas.goToBlockById(toValue(currentAnimationItem).item.id);
		}

		function onAfterEnter(): void
		{
			hooks.blockTransitionEnd.trigger(toValue(currentAnimationItem)?.item);
			nextAnimationItem();
		}

		function onAfterLeave(): void
		{
			nextAnimationItem();
		}

		return {
			isAnimate,
			onBeforeEnter,
			onEnter,
			onAfterEnter,
			onAfterLeave,
		};
	},
	template: `
		<TransitionGroup
			v-if="isAnimate"
			name="ui-block-diagram-blocks-queue-transition"
			@before-enter="onBeforeEnter"
			@enter="onEnter"
			@after-enter="onAfterEnter"
			@after-leave="onAfterLeave"
		>
			<slot/>
		</TransitionGroup>
		<template v-else>
			<slot/>
		</template>
	`,
};
