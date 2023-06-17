import {Dom} from 'main.core';

export const DomUtil = {
	recursiveBackwardNodeSearch(node, className, maxNodeLevel = 10)
	{
		while (maxNodeLevel > 0)
		{
			if (Dom.hasClass(node, className))
			{
				return node;
			}

			if (!node || !node.parentNode)
			{
				return null;
			}

			node = node.parentNode;

			maxNodeLevel--;
		}

		return null;
	}
};

