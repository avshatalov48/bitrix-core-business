import {
	$getRoot,
	$isDecoratorNode,
	$isElementNode,
	$isParagraphNode,
	$isTextNode,
	type DecoratorNode,
	type ElementNode,
} from 'ui.lexical.core';

export function $isRootEmpty(trim: boolean = true): boolean
{
	const root = $getRoot();
	let text = root.getTextContent();
	if (trim)
	{
		text = text.trim();
	}

	if (text !== '')
	{
		return false;
	}

	const children = root.getChildren();
	const childrenLength = children.length;
	if (childrenLength > 1)
	{
		return false;
	}

	for (let i = 0; i < childrenLength; i++)
	{
		const topBlock: ElementNode | DecoratorNode = children[i];
		if ($isDecoratorNode(topBlock))
		{
			return false;
		}

		if ($isElementNode(topBlock))
		{
			if (!$isParagraphNode(topBlock))
			{
				return false;
			}

			if (topBlock.__indent !== 0)
			{
				return false;
			}

			const topBlockChildren = topBlock.getChildren();
			const topBlockChildrenLength = topBlockChildren.length;

			for (let s = 0; s < topBlockChildrenLength; s++)
			{
				const child = topBlockChildren[i];
				if (!$isTextNode(child))
				{
					return false;
				}
			}
		}
	}

	return true;
}
