import { Dom, Type } from 'main.core';
import { isElementVisible } from './helpers/is-element-visible';
import type { SkeletonBlock } from './skeleton-block';
import { SkeletonIconBlock } from './skeleton-icon-block';
import { SkeletonTextBlock } from './skeleton-text-block';

export class Skeleton
{
	static #skeletons = new Map();
	static #skeletonBlocks: Map<string, SkeletonBlock[]> = new Map();

	static initOnBlock(blockId: string, target: HTMLElement): void
	{
		this.#skeletons.set(blockId, target);
		this.#skeletonBlocks.set(blockId, this.#getSkeletonBlocks(blockId));
		this.#hideSkeletonBlocks(blockId);
	}

	static async removeSkeletonFromBlock(blockId: string): void
	{
		const skeletonBlocks = this.#skeletonBlocks.get(blockId);
		for (const block: SkeletonBlock of skeletonBlocks)
		{
			// eslint-disable-next-line no-await-in-loop
			await block.show();
		}
	}

	static #hideSkeletonBlocks(blockId: string)
	{
		const skeletonBlocks = this.#skeletonBlocks.get(blockId);

		skeletonBlocks.forEach((skeletonBlock) => {
			skeletonBlock.hide();
		});
	}

	static #getSkeletonBlocks(blockId: string): SkeletonBlock[]
	{
		const target = this.#skeletons.get(blockId);
		const skeletonBlocks: SkeletonBlock[] = [];

		const treeWalker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT, null, false);

		while (treeWalker.nextNode())
		{
			const currentNode: Node = treeWalker.currentNode;

			if (currentNode.nodeType === Node.TEXT_NODE && currentNode.data.trim() !== '' && isElementVisible(currentNode.parentElement))
			{
				const parentElement = currentNode.parentElement;

				if (Dom.getPosition(parentElement).left <= window.innerWidth)
				{
					currentNode.data = currentNode.data.trim();
					skeletonBlocks.push(new SkeletonTextBlock({ textNode: currentNode }));
				}
			}
			else if (
				currentNode.nodeType === Node.ELEMENT_NODE
				&& Type.isStringFilled(currentNode.className)
				&& currentNode.className?.split(' ').some((className) => className.startsWith('fa-'))
			)
			{
				skeletonBlocks.push(new SkeletonIconBlock({ iconElement: currentNode }));
			}
		}

		return skeletonBlocks;
	}
}
