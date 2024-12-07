import { BBCodeNode, privateMap, nameSymbol, type BBCodeContentNode } from './node';
import { BBCodeElementNode } from './element-node';
import { typeof BBCodeScheme } from '../scheme/bbcode-scheme';

export type RootNodeOptions = {
	children: Array<BBCodeNode>,
};

export type BBCodeToStringOptions = {
	encode?: boolean,
};

export class BBCodeRootNode extends BBCodeElementNode
{
	constructor(options: RootNodeOptions)
	{
		super({ ...options, name: '#root' });
		privateMap.get(this).type = BBCodeNode.ROOT_NODE;
		BBCodeRootNode.makeNonEnumerableProperty(this, 'value');
		BBCodeRootNode.makeNonEnumerableProperty(this, 'attributes');
		BBCodeRootNode.freezeProperty(this, nameSymbol, '#root');
	}

	setScheme(scheme: BBCodeScheme, onUnknown: (node: BBCodeContentNode) => any)
	{
		BBCodeNode.flattenAst(this).forEach((node: BBCodeContentNode) => {
			node.setScheme(scheme, onUnknown);
		});

		super.setScheme(scheme);

		BBCodeNode.flattenAst(this).forEach((node: BBCodeContentNode) => {
			node.adjustChildren();
		});
	}

	getParent(): null
	{
		return null;
	}

	clone(options: { deep: boolean } = {}): BBCodeRootNode
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

		return this.getScheme().createRoot({
			children,
		});
	}

	toString(options: BBCodeToStringOptions = {}): string
	{
		return this.getChildren()
			.map((child: BBCodeContentNode) => {
				return child.toString(options);
			})
			.join('');
	}

	toJSON(): any
	{
		return this.getChildren().map((node: BBCodeNode) => {
			return node.toJSON();
		});
	}
}
