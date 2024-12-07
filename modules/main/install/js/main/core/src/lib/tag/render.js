import Type from '../type';
import parse from './internal/parse';
import renderNode from './internal/render-node';

export default function render(
	sections: Array<string>,
	...substitutions: Array<any>
): HTMLElement | Array<HTMLElement> | { root: HTMLElement, [key: string]: HTMLElement }
{
	const html = sections
		.reduce((acc, item, index) => {
			if (index > 0)
			{
				const substitution = substitutions[index - 1];
				if (Type.isString(substitution) || Type.isNumber(substitution))
				{
					return `${acc}${substitution}${item}`;
				}

				return `${acc}{{uid${index}}}${item}`;
			}

			return acc;
		}, sections[0])
		.replace(/^[\r\n\t\s]+/gm, '')
		.replace(/>[\n]+/g, '>')
		.replace(/[}][\n]+/g, '}');

	const ast = parse(html);

	if (ast.length === 1)
	{
		const refs = [];
		const renderedNode = renderNode({
			node: ast[0],
			substitutions,
			refs,
		});

		if (Type.isArrayFilled(refs))
		{
			return Object.fromEntries([['root', renderedNode], ...refs]);
		}

		return renderedNode;
	}

	if (ast.length > 1)
	{
		const refs = [];
		const renderedNodes = ast.map((node) => {
			return renderNode({
				node,
				substitutions,
				refs,
			});
		});

		if (Type.isArrayFilled(refs))
		{
			return Object.fromEntries([['root', renderedNodes], ...refs]);
		}

		return renderedNodes;
	}

	return false;
}
