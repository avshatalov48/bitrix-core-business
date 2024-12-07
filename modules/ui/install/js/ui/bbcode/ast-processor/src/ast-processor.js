import { Type } from 'main.core';
import { typeof BBCodeNode } from 'ui.bbcode.model';
import { getByIndex } from '../../shared';

type ParsedSelector = {
	nodeName: string,
	props: Array<{key: string, value: any}>,
};

export class AstProcessor
{
	/**
	 * Makes flat list from AST
	 */
	static flattenAst(ast): Array<any>
	{
		if (ast && ast.getChildren)
		{
			const children = ast.getChildren();

			return [
				...children,
				...children.flatMap((node) => {
					return AstProcessor.flattenAst(node);
				}),
			];
		}

		return [];
	}

	/**
	 * Parses selector
	 */
	static parseSelector(selector: string): Array<ParsedSelector | '>'>
	{
		const regex = /(\w+)\[(.*?)]|\s*(>)\s*|\w+/g;
		const matches = [...selector.matchAll(regex)];

		return matches.map(([fullMatch: string, nodeName: ?string, rawProps: ?string, arrow: ?string]) => {
			if (arrow)
			{
				return '>';
			}

			if (rawProps)
			{
				const propsRegexp = /(\w+)=["'](.*?)["']/g;
				const propsMatches = [...rawProps.matchAll(propsRegexp)];
				const props = propsMatches.map(([, key: string, value: string]) => {
					return [key, value];
				});

				return {
					nodeName,
					props,
				};
			}

			return {
				nodeName: fullMatch,
				props: [],
			};
		});
	}

	/**
	 * @private
	 */
	static matchesNodeWithSelector(node, selector: ParsedSelector): boolean
	{
		if (node && node.constructor.name === selector.nodeName)
		{
			if (selector.props.length > 0)
			{
				return selector.props.every(([key, value]) => {
					const propValue = (() => {
						const name = `${key.charAt(0).toUpperCase()}${key.slice(1)}`;
						if (Type.isFunction(node[`get${name}`]))
						{
							return node[`get${name}`]();
						}

						if (Type.isFunction(node[`is${name}`]))
						{
							return node[`is${name}`]();
						}

						return null;
					})();

					if (['true', 'false'].includes(value))
					{
						return propValue === (value === 'true');
					}

					return propValue === value;
				});
			}

			return true;
		}

		return false;
	}

	/**
	 * Finds parent node by parsed selector
	 */
	static findParentNode(node, selector: ParsedSelector | string): ?any
	{
		if (node)
		{
			const preparedSelector = (() => {
				if (Type.isStringFilled(selector))
				{
					return AstProcessor.parseSelector(selector)[0];
				}

				return selector;
			})();
			const parent = node.getParent();

			if (AstProcessor.matchesNodeWithSelector(parent, preparedSelector))
			{
				return parent;
			}

			return AstProcessor.findParentNode(parent, preparedSelector);
		}

		return null;
	}

	static findParentNodeByName(node: BBCodeNode, name: string): BBCodeNode | null
	{
		if (node)
		{
			const parent: ?BBCodeNode = node.getParent();
			if (parent && parent.getName() === name)
			{
				return parent;
			}

			return AstProcessor.findParentNodeByName(parent, name);
		}

		return null;
	}

	/**
	 * Find elements by selector
	 */
	static findElements(ast, selector: string): Array<any>
	{
		const flattenedAst = AstProcessor.flattenAst(ast);
		const parsedSelector = AstProcessor.parseSelector(selector);
		const lastSelector = getByIndex(parsedSelector, -1);

		let checkClosestParent = false;

		return parsedSelector.reduceRight((acc: Array<any>, currentSelector: ParsedSelector) => {
			if (Type.isPlainObject(currentSelector))
			{
				if (currentSelector === lastSelector)
				{
					return acc.filter((node) => {
						return AstProcessor.matchesNodeWithSelector(node, currentSelector);
					});
				}

				if (checkClosestParent)
				{
					checkClosestParent = false;

					return acc.filter((node) => {
						return AstProcessor.matchesNodeWithSelector(node.getParent(), currentSelector);
					});
				}

				return acc.filter((node) => {
					return AstProcessor.findParentNode(node, currentSelector) !== null;
				});
			}

			if (currentSelector === '>')
			{
				checkClosestParent = true;
			}

			return acc;
		}, flattenedAst);
	}

	/**
	 * Reduces AST
	 */
	static reduceAst(ast, reducer: (node: any, children?: Array<any>) => any | null): any
	{
		const children = ast.getChildren?.().reduce((acc, child) => {
			const preparedChild = [AstProcessor.reduceAst(child, reducer)].flat();
			if (!Type.isNil(preparedChild))
			{
				acc.replaceChild(child, ...preparedChild);
			}

			return acc;
		}, ast);

		return reducer(ast, children);
	}
}
