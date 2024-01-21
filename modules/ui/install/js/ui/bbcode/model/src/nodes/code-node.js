import { ElementNode } from './element-node';
import { TextNode } from './text-node';
import { Node } from './node';

export class CodeNode extends ElementNode
{
	getType(): number
	{
		return Node.CODE_NODE;
	}

	appendChild(...children)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
		super.appendChild(
			...children.map((node: Node): Node => {
				if (!['#linebreak', '#tab'].includes(node.getName()))
				{
					return new TextNode({
						content: node.toString(),
						parent: this,
						encode: false,
					});
				}

				return node;
			}),
		);
	}
}