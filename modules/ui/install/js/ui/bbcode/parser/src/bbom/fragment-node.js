import { Node, nameSymbol, privateMap } from './node';
import { ElementNode } from './element-node';

export type FragmentNodeOptions = {
	children: Array<Node>,
};

export class FragmentNode extends ElementNode
{
	[nameSymbol]: string = '#fragment';

	constructor(options: FragmentNodeOptions)
	{
		super(options);
		privateMap.get(this).type = Node.FRAGMENT_NODE;
		FragmentNode.makeNonEnumerableProperty(this, 'value');
		FragmentNode.makeNonEnumerableProperty(this, 'void');
		FragmentNode.makeNonEnumerableProperty(this, 'inline');
		FragmentNode.makeNonEnumerableProperty(this, 'attributes');
	}

	setName()
	{}
}
