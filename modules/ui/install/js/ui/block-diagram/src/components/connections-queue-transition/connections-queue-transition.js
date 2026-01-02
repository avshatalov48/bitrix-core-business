import './connections-queue-transition.css';
import { toValue } from 'ui.vue3';
import { useBlockDiagram, useHistory } from '../../composables';

type ConnectionsQueueTransitionSetup = {
	isAnimate: boolean;
	onBeforeEnter: () => void;
	onAfterEnter: () => void;
	onAfterLeave: () => void;
};

// @vue/component
export const ConnectionsQueueTransition = {
	name: 'connections-queue-transition',
	setup(): ConnectionsQueueTransitionSetup
	{
		const {
			isAnimate,
			currentAnimationItem,
			animationQueue,
			updatePortPosition,
			hooks,
		} = useBlockDiagram();
		const history = useHistory();

		function onBeforeEnter(): void
		{
			const { item: connection } = toValue(currentAnimationItem) ?? {};
			hooks.connectionTransitionStart.trigger(connection);
			const {
				sourceBlockId,
				sourcePortId,
				targetBlockId,
				targetPortId,
			} = connection;

			updatePortPosition(sourceBlockId, sourcePortId);
			updatePortPosition(targetBlockId, targetPortId);
		}

		function nextAnimatedItem(): void
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

		function onAfterEnter(): void
		{
			hooks.connectionTransitionEnd.trigger(toValue(currentAnimationItem)?.item);
			nextAnimatedItem();
		}

		function onAfterLeave(): void
		{
			nextAnimatedItem();
		}

		return {
			isAnimate,
			onBeforeEnter,
			onAfterEnter,
			onAfterLeave,
		};
	},
	template: `
		<TransitionGroup
			v-if="isAnimate"
			name="ui-block-diagram-connections-queue-transition"
			@before-enter="onBeforeEnter"
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
