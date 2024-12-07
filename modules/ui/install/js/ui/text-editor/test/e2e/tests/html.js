import { defaultTheme } from '../../../src/themes/default-theme';

function isHtml(content)
{
	return content.match(/^[\t\n]*<[a-z]/);
}

export function paragraph(content = '<br>')
{
	return `<p class="${defaultTheme.paragraph}">${isHtml(content) ? content : text(content)}</p>`;
}

export function bold(content = '')
{
	return `<strong class="${defaultTheme.text.bold}" data-lexical-text="true">${content}</strong>`;
}

export function italic(content = '')
{
	return `<em class="${defaultTheme.text.italic}" data-lexical-text="true">${content}</em>`;
}

export function underline(content = '')
{
	return `<span class="${defaultTheme.text.underline}" data-lexical-text="true">${content}</span>`;
}

export function strike(content = '')
{
	return `<span class="${defaultTheme.text.strikethrough}" data-lexical-text="true">${content}</span>`;
}

export function underlineStrike(content = '')
{
	return `<span class="${defaultTheme.text.underlineStrikethrough}" data-lexical-text="true">${content}</span>`;
}

export function boldItalic(content = '')
{
	return `<strong class="${defaultTheme.text.bold} ${defaultTheme.text.italic}" data-lexical-text="true">${content}</strong>`;
}

export function text(content = '')
{
	return `<span data-lexical-text="true">${content}</span>`;
}

export function link(content = '', url = 'https://example.com')
{
	return `<a href="${url}" target="_blank" class="${defaultTheme.link}">${
		isHtml(content) ? content : text(content)}</a>`
	;
}

export function autolink(url)
{
	return link(url, url);
}

export function code(content = '')
{
	return `<code spellcheck="false" class="${defaultTheme.code}">${content}</code>`;
}

export function codeToken(content = '', keyword = 'keyword')
{
	return `<span class="ui-typography-token-${keyword}" data-lexical-text="true">${content}</span>`;
}

export function parentheses(content = '')
{
	return codeToken(content, 'parentheses');
}

export function keyword(content = '')
{
	return codeToken(content, 'keyword');
}

export function brace(content = '')
{
	return codeToken(content, 'brace');
}

export function comment(content = '')
{
	return codeToken(content, 'comment');
}

export function semicolon(content = '')
{
	return codeToken(';', 'semicolon');
}

export function word(content = '')
{
	return codeToken(content, 'word');
}

export function string(content = '')
{
	return codeToken(content, 'string');
}

export function quote(content = '')
{
	return `<blockquote spellcheck="false" class="${defaultTheme.quote}">${
		isHtml(content) ? content : text(content)
	}</blockquote>`;
}

export function mention(content = '')
{
	return `<span class="${defaultTheme.mention}">${isHtml(content) ? content : text(content)}</span>`;
}

export function hashtag(content = '')
{
	return `<span class="${defaultTheme.hashtag}" data-lexical-text="true">${content}</span>`;
}

const smileys = {
	':)': 'bx_smile_smile.png',
	':-)': 'bx_smile_smile.png',
	';)': 'bx_smile_wink.png',
	';-)': 'bx_smile_wink.png',
	':D': 'bx_smile_biggrin.png',
	':-D': 'bx_smile_biggrin.png',
	'8-)': 'bx_smile_cool.png',
	':{}': 'bx_smile_kiss.png',
};

export function emoji(emojiCode = ':)')
{
	return `<img src="/upload/main/smiles/2/${smileys[emojiCode]}" class="${defaultTheme.smiley}" draggable="false" data-lexical-decorator="true" contenteditable="true">`;
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
		<details class="${defaultTheme.spoiler.container}"${openAttr}>
			<summary class="${defaultTheme.spoiler.title}">
				<span data-lexical-text="true">${title}</span>
			</summary>
			<div class="${defaultTheme.spoiler.content}">${contentHtml}</div>
		</details>
	`;
}

export function table(content = '')
{
	return `<table class="${defaultTheme.table}">${content}</table>`;
}

export function tr(content = '')
{
	return `<tr class="${defaultTheme.tableRow}">${content}</tr>`;
}

export function td(content = paragraph())
{
	return `<td class="${defaultTheme.tableCell}">${content}</td>`;
}

export function th(content = paragraph())
{
	return `<th class="${defaultTheme.tableCell} ${defaultTheme.tableCellHeader}">${content}</th>`;
}

export function image(
	selected = false,
	url = 'https://i.pinimg.com/564x/3d/d8/3f/3dd83fc6cfce54d3ad2bcc992cd5ed18.jpg',
)
{
	return `
		<span class="${defaultTheme.image.container}${selected ? ' --selected' : ''}" data-lexical-decorator="true" contenteditable="false">
			<div class="ui-text-editor-image-component${selected ? ' --selected --draggable' : ''}">
			<div class="ui-text-editor-image-container" draggable="${selected ? 'true' : 'false'}">
				<img draggable="false" src="https://i.pinimg.com/564x/3d/d8/3f/3dd83fc6cfce54d3ad2bcc992cd5ed18.jpg" class="${defaultTheme.image.img}"></div>
				<div class="ui-text-editor-figure-resizer${selected ? ' --shown' : ''}">
					<div class="ui-text-editor-figure-resizer-handle --north-east" data-direction="9"></div>
					<div class="ui-text-editor-figure-resizer-handle --south-east" data-direction="3"></div>
					<div class="ui-text-editor-figure-resizer-handle --south-west" data-direction="6"></div>
					<div class="ui-text-editor-figure-resizer-handle --north-west" data-direction="12"></div>
				</div>
			</div>
		</span>
	`;
}

export function video(
	url = 'https://video.1c-bitrix.ru/bitrix24/themes/video-rain/rain3.mp4',
	selected = false,
)
{
	return `
		<span class="${defaultTheme.video.container}${selected ? ' --selected' : ''}" data-lexical-decorator="true" contenteditable="false">
			<div class="ui-text-editor-video-component${selected ? ' --selected' : ''}">
			<div class="ui-text-editor-video-object-container">
				<video controls="true" preload="metadata" playsinline="true" src="${url}" class="${defaultTheme.video.object}" width="560"></video>
			</div>
			<div class="ui-text-editor-figure-resizer${selected ? ' --shown' : ''}">
				<div class="ui-text-editor-figure-resizer-handle --north-east" data-direction="9"></div>
				<div class="ui-text-editor-figure-resizer-handle --south-east" data-direction="3"></div>
				<div class="ui-text-editor-figure-resizer-handle --south-west" data-direction="6"></div>
				<div class="ui-text-editor-figure-resizer-handle --north-west" data-direction="12"></div>
				<div class="ui-text-editor-figure-resizer-handle --north" data-direction="8"></div>
				<div class="ui-text-editor-figure-resizer-handle --east" data-direction="1"></div>
				<div class="ui-text-editor-figure-resizer-handle --south" data-direction="2"></div>
				<div class="ui-text-editor-figure-resizer-handle --west" data-direction="4"></div><
				/div>
			</div>
		</span>
	`;
}

export function ul(content)
{
	return `<ul class="${defaultTheme.list.ul}">${content}</ul>`;
}

export function ol(content)
{
	return `<ol class="${defaultTheme.list.olDepth[0]}">${content}</ol>`;
}

export function li(content, value)
{
	return `<li value="${value}" class="${defaultTheme.list.listitem}">${
		isHtml(content) ? content : text(content)
	}</li>`;
}
