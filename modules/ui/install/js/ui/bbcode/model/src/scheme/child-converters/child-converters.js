import { type ContentNode } from '../../nodes/node';
import { TextNode } from '../../nodes/text-node';
import { Tag } from '../../reference/tag';

export const childConverters = {
	[Tag.CODE]: (node: ContentNode): ContentNode => {
		if (node.getName() === '#text')
		{
			return node;
		}

		return new TextNode({ content: node.toString() });
	},
};
