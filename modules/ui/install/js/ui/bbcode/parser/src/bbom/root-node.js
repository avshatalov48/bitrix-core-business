import { Node, privateMap, type ContentNode } from './node';
import { ElementNode } from './element-node';

export type RootNodeOptions = {
	children: Array<Node>,
};

export class RootNode extends ElementNode
{
	constructor(options: RootNodeOptions)
	{
		super(options);
		privateMap.get(this).type = Node.ROOT_NODE;
		RootNode.freezeProperty(this, 'name', '#root', false);
		RootNode.makeNonEnumerableProperty(this, 'value');
		RootNode.makeNonEnumerableProperty(this, 'void');
		RootNode.makeNonEnumerableProperty(this, 'inline');
		RootNode.makeNonEnumerableProperty(this, 'attributes');
	}

	getParent(): null
	{
		return null;
	}

	setName(name: string)
	{}

	toString(): string
	{
		return this.getChildren()
			.map((child: ContentNode) => {
				return child.toString();
			})
			.join('');
	}

	toJSON(): any
	{
		return this.getChildren().map((node: Node) => {
			return node.toJSON();
		});
	}
}
