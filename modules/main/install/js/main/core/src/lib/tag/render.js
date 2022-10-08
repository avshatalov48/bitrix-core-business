import Type from '../type';
import parse from './internal/parse';
import renderNode from './internal/render-node';

export default function render(sections: Array<string>, ...substitutions: Array<any>): HTMLElement | Array<HTMLElement>
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
		return renderNode({
			node: ast[0],
			substitutions,
		});
	}

	if (ast.length > 1)
	{
		return ast.map((node) => {
			return renderNode({
				node,
				substitutions,
			});
		});
	}

	return false;
}