import { BBCodeNode, nameSymbol, privateMap } from './node';
import { BBCodeElementNode } from './element-node';

export type FragmentNodeOptions = {
	children: Array<BBCodeNode>,
};

export class BBCodeFragmentNode extends BBCodeElementNode
{
	constructor(options: FragmentNodeOptions)
	{
		super({ ...options, name: '#fragment' });
		privateMap.get(this).type = BBCodeNode.FRAGMENT_NODE;
		BBCodeFragmentNode.makeNonEnumerableProperty(this, 'value');
		BBCodeFragmentNode.makeNonEnumerableProperty(this, 'attributes');
		BBCodeFragmentNode.freezeProperty(this, nameSymbol, '#fragment');
	}

	clone(options: { deep: boolean } = {}): BBCodeFragmentNode
	{
		const children = (() => {
			if (options.deep)
			{
				return this.getChildren().map((child) => {
					return child.clone(options);
				});
			}

			return [];
		})();

		return this.getScheme().createFragment({
			children,
		});
	}
}
