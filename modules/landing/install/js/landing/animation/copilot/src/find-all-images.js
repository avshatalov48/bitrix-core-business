export const findAllImagesWithBackground = (target: HTMLElement) => {
	const result = [];
	const treeWalker = document.createTreeWalker(target, NodeFilter.SHOW_ELEMENT, null);

	do
	{
		const currentNode = treeWalker.currentNode;

		if (currentNode.tagName === 'INPUT' || currentNode.tagName === 'TEXTAREA')
		{
			continue;
		}

		if (currentNode.tagName === 'IMG')
		{
			result.push({
				type: 'img',
				node: currentNode,
				value: currentNode.getAttribute('src'),
			});
		}
		else if (getComputedStyle(currentNode).getPropertyValue('--bg-url') && !getComputedStyle(currentNode.parentElement).getPropertyValue('--bg-url'))
		{
			result.push({
				type: 'variable',
				node: currentNode,
				value: getComputedStyle(currentNode).getPropertyValue('--bg-url'),
			});
		}
		else if (getComputedStyle(currentNode).backgroundImage !== 'none')
		{
			result.push({
				type: 'background',
				node: currentNode,
				value: getComputedStyle(currentNode).backgroundImage,
			});
		}
	} while (treeWalker.nextNode());

	return result;
};

export type BlockWithImage = {
	type: 'background' | 'img',
	node: HTMLElement,
	value: string,
}
