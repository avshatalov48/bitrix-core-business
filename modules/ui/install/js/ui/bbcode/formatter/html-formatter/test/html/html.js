import { defaultTheme } from '../../../../../text-editor/src/themes/default-theme';

export function paragraph(content = '<br>')
{
	return `<p class="ui-typography-paragraph">${content}</p>`;
}

export function bold(content = '')
{
	return `<b class="ui-typography-text-bold">${content}</b>`;
}

export function italic(content = '')
{
	return `<i class="ui-typography-text-italic">${content}</i>`;
}

export function underline(content = '')
{
	return `<u class="ui-typography-text-underline">${content}</u>`;
}

export function strike(content = '')
{
	return `<s class="ui-typography-text-strikethrough">${content}</s>`;
}

export function text(content = '')
{
	return `<span data-lexical-text="true">${content}</span>`;
}

export function link(content = '', url = 'https://example.com')
{
	return `<a href="${url}" target="_blank" class="ui-typography-link">${content}</a>`;
}

export function autolink(url)
{
	return link(url, url);
}

export function quote(content = '')
{
	return `<blockquote class="ui-typography-quote">${content}</blockquote>`;
}

export function mention(url, id, entityId, content = '')
{
	return `<a href="${url}" class="ui-typography-mention" data-mention-entity-id="${entityId}" data-mention-id="${id}">${content}</a>`;
}

export function user(url, id, content = '')
{
	return mention(url, id, 'user', content);
}

export function department(url, id, content = '')
{
	return mention(url, id, 'department', content);
}

export function project(url, id, content = '')
{
	return mention(url, id, 'project', content);
}

export function br(count = 1)
{
	return '<br>'.repeat(count);
}

export function spoiler(content = '', open = true, title = 'Spoiler')
{
	const openAttr = open ? ' open=""' : '';
	const contentHtml = content === '' ? paragraph() : content;

	return `
		<details class="ui-typography-spoiler"${openAttr}>
			<summary class="ui-typography-spoiler-title ui-icon-set__scope">${title}</summary>
			<div class="ui-typography-spoiler-content">${contentHtml}</div>
		</details>
	`;
}

export function table(content = '')
{
	return `<table class="ui-typography-table">${content}</table>`;
}

export function tr(content = '')
{
	return `<tr class="ui-typography-table-row">${content}</tr>`;
}

export function td(content = paragraph())
{
	return `<td class="ui-typography-table-cell">${content}</td>`;
}

export function th(content = paragraph())
{
	return `<th class="ui-typography-table-cell ui-typography-table-cell-header">${content}</th>`;
}

export function ul(content)
{
	return `<ul class="ui-typography-ul">${content}</ul>`;
}

export function ol(content)
{
	return `<ol class="ui-typography-ol">${content}</ol>`;
}

export function li(content)
{
	return `<li class="ui-typography-li">${content}</li>`;
}

export function img(src)
{
	return `<span class="ui-typography-image-container" data-decorator="true"><img src="${src}" class="ui-typography-image" width="null" loading="lazy"></span>`;
}
