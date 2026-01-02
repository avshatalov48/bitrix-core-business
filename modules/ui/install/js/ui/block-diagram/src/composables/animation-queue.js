import { toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import type {
	AnimationItem,
} from '../types';

import { ANIMATED_TYPES } from '../constants';

type Options = {
	items: Array<AnimationItem>;
	zoom: number;
};

type UseAnimatedQueue = {
	start: (options: Options) => void;
	pause: () => void;
	play: () => void;
	stop: () => void;
};

export function useAnimationQueue(): UseAnimatedQueue
{
	const {
		zoom,
		isPauseAnimation,
		isStopAnimation,
		animationQueue,
		currentAnimationItem,
		addConnection,
		deleteConnectionById,
		addBlock,
		deleteBlockById,
	} = useBlockDiagram();

	function animateItem(animatedItem: AnimationItem): void
	{
		switch (animatedItem.type)
		{
			case ANIMATED_TYPES.BLOCK: {
				addBlock(animatedItem.item);
				break;
			}

			case ANIMATED_TYPES.CONNECTION: {
				addConnection(animatedItem.item);
				break;
			}

			case ANIMATED_TYPES.REMOVE_BLOCK: {
				deleteBlockById(animatedItem.item.id);
				break;
			}

			case ANIMATED_TYPES.REMOVE_CONNECTION: {
				deleteConnectionById(animatedItem.item.id);
				break;
			}

			default:
				break;
		}
	}

	function* animationQueueFn(animatedQueueItems: Array<AnimationItem>): Generator<AnimationItem | undefined>
	{
		for (const animatedItem: AnimationItem of animatedQueueItems)
		{
			if (toValue(isPauseAnimation))
			{
				yield;
			}

			if (toValue(isStopAnimation))
			{
				break;
			}

			currentAnimationItem.value = animatedItem;

			if (animatedItem.type && animatedItem.item)
			{
				animateItem(animatedItem);

				yield animatedItem;
			}
		}

		stop();
	}

	function start(options: Options): void
	{
		const {
			items: shouldAnimatedItems = [],
		} = options ?? {};

		zoom.value = 1;
		isStopAnimation.value = false;

		animationQueue.value = animationQueueFn(shouldAnimatedItems);
		if (shouldAnimatedItems.length > 0)
		{
			setTimeout((): void => play(), 100);
		}
		else
		{
			stop();
		}
	}

	function pause(): void
	{
		isPauseAnimation.value = true;
	}

	function play(): void
	{
		isPauseAnimation.value = false;
		animationQueue.value?.next();
	}

	function stop(): void
	{
		isStopAnimation.value = true;
		isPauseAnimation.value = false;
		currentAnimationItem.value = null;
		animationQueue.value = null;
	}

	return {
		start,
		pause,
		play,
		stop,
	};
}
