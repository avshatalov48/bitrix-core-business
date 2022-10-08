import parseTag from './parse-tag';
import matchers from './matchers';
import parseText from './parse-text';

export default function parse(html: string, substitutions): Array<any>
{
	const result = [];

	if (html.indexOf('<') !== 0 && !html.startsWith('{{'))
	{
		const end = html.indexOf('<');
		result.push(...parseText(end === -1 ? html : html.slice(0, end)));
	}

	const commentsContent = [];
	let commentIndex = -1;
	html = html.replace(matchers.comment, (tag) => {
		commentIndex += 1;
		commentsContent.push(tag.replace(/^<!--|-->$/g, ''));
		return `<!--{{cUid${commentIndex}}}-->`;
	});

	const arr = [];
	let level = -1;
	let current;
	html.replace(matchers.tag, (tag, index) => {
		const start = index + tag.length;
		const nextChar = html.charAt(start);
		let parent;

		if (tag.startsWith('<!--'))
		{
			const comment = parseTag(tag, substitutions);
			comment.content = commentsContent[tag.replace(/<!--{{cUid|}}-->/g, '')];

			if (level < 0)
			{
				result.push(comment);
				return result;
			}

			parent = arr[level];
			parent.children.push(comment);

			return result;
		}

		if (tag.startsWith('{{'))
		{
			const [placeholder] = parseText(tag);

			if (level < 0)
			{
				result.push(placeholder);
				return result;
			}

			parent = arr[level];
			parent.children.push(placeholder);

			return result;
		}

		if (!tag.startsWith('</'))
		{
			level++;

			current = parseTag(tag, substitutions);

			if (
				!current.voidElement
				&& nextChar
				&& nextChar !== '<'
			)
			{
				current.children.push(
					...parseText(html.slice(start, html.indexOf('<', start))),
				);
			}

			if (level === 0)
			{
				result.push(current);
			}

			parent = arr[level - 1];

			if (parent)
			{
				if (!current.svg)
				{
					current.svg = parent.svg;
				}

				parent.children.push(current);
			}

			arr[level] = current;
		}

		if (tag.startsWith('</') || current.voidElement)
		{
			if (
				level > -1
				&& (current.voidElement || current.name === tag.slice(2, -1))
			)
			{
				level--;
				current = level === -1 ? result : arr[level];
			}

			if (nextChar && nextChar !== '<')
			{
				parent = level === -1 ? result : arr[level].children;

				const end = html.indexOf('<', start);
				const content = html.slice(start, end === -1 ? undefined : end);

				if ((end > -1 && level + parent.length >= 0) || content !== ' ')
				{
					parent.push(...parseText(content));
				}
			}
		}
	});

	return result;
}