import { Dom, Loc, Text } from 'main.core';

import { getUtils, getBigSmileOption } from '../utils/core-proxy';
import { ParserIcon } from './icon';

export const ParserImage = {

	decodeLink(text): string
	{
		return text.replaceAll(/>((https|http):\/\/(\S+)\.(jpg|jpeg|png|gif|webp)(\?\S+[^<])?)<\/a>/gi, (whole, urlParsed): string => {
			const url = Text.decode(urlParsed);
			if (
				!/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i.test(url)
				|| url.toLowerCase().indexOf('/docs/pub/') > 0
				|| url.toLowerCase().indexOf('logout=yes') > 0
			)
			{
				return whole;
			}

			if (!getUtils().text.checkUrl(url))
			{
				return whole;
			}

			const result = Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-message-image',
				},
				children: [
					Dom.create({
						tag: 'img',
						attrs: {
							className: 'bx-im-message-image-source',
							src: url,
						},
						events: {
							error() {
								ParserImage.hideErrorImage(this);
							},
						},
					}),
				],
			}).outerHTML;

			return `>${result}</a>`;
		});
	},

	purifyLink(text): string
	{
		text = text.replace(/(.)?((https|http):\/\/(\S+)\.(jpg|jpeg|png|gif|webp)(\?\S+)?)/gi, function(whole, letter, url): string
		{
			if(
				letter && !(['>', ']', ' '].includes(letter))
				|| !url.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i)
				|| url.toLowerCase().indexOf("/docs/pub/") > 0
				|| url.toLowerCase().indexOf("logout=yes") > 0
			)
			{
				return whole;
			}
			else
			{
				return (letter? letter: '') + ParserIcon.getImageBlock();
			}
		});

		return text;
	},

	// eslint-disable-next-line max-lines-per-function,sonarjs/cognitive-complexity
	decodeIcon(text): string
	{
		let textElementSize = 0;

		const enableBigSmile = getBigSmileOption();
		if (enableBigSmile)
		{
			textElementSize = text.replaceAll(/\[icon=([^\]]*)]/gi, '').trim().length;
		}

		return text.replaceAll(/\[icon=([^\]]*)]/gi, (whole) => {
			let url = whole.match(/icon=(\S+[^\s!"'),.;>?\]])/i);
			if (url && url[1])
			{
				url = url[1];
			}
			else
			{
				return '';
			}

			if (!getUtils().text.checkUrl(url))
			{
				return whole;
			}

			const attrs = { src: url, border: 0 };

			const size = whole.match(/size=(\d+)/i);
			if (size && size[1])
			{
				attrs.width = size[1];
				attrs.height = size[1];
			}
			else
			{
				const width = whole.match(/width=(\d+)/i);
				if (width && width[1])
				{
					attrs.width = width[1];
				}

				const height = whole.match(/height=(\d+)/i);
				if (height && height[1])
				{
					attrs.height = height[1];
				}

				if (attrs.width && !attrs.height)
				{
					attrs.height = attrs.width;
				}
				else if (attrs.height && !attrs.width)
				{
					attrs.width = attrs.height;
				}
				else if (attrs.height && attrs.width)
				{
					/* empty */
				}
				else
				{
					attrs.width = 20;
					attrs.height = 20;
				}
			}

			attrs.width = attrs.width > 100 ? 100 : attrs.width;
			attrs.height = attrs.height > 100 ? 100 : attrs.height;

			if (
				enableBigSmile
				&& textElementSize === 0
				&& attrs.width === attrs.height
				&& attrs.width === 20
			)
			{
				attrs.width = 40;
				attrs.height = 40;
			}

			let title = whole.match(/title=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];
				if (title.includes('width='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('width=')));
				}

				if (title.includes('height='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('height=')));
				}

				if (title.includes('size='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('size=')));
				}

				if (title)
				{
					attrs.title = Text.decode(title).trim();
					attrs.alt = attrs.title;
				}
			}

			return Dom.create({
				tag: 'img',
				attrs: {
					className: 'bx-smile bx-icon',
					...attrs,
				},
			}).outerHTML;
		});
	},

	purifyIcon(text): string
	{
		return text.replaceAll(/\[icon=([^\]]*)]/gi, (whole) => {
			let title = whole.match(/title=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];

				if (title.includes('width='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('width=')));
				}

				if (title.includes('height='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('height=')));
				}

				if (title.includes('size='))
				{
					title = title.slice(0, Math.max(0, title.indexOf('size=')));
				}

				if (title)
				{
					title = `(${title.trim()})`;
				}
			}
			else
			{
				title = `(${Loc.getMessage('IM_PARSER_IMAGE_ICON')})`;
			}

			return title;
		});
	},

	hideErrorImage(element)
	{
		const result = element;

		if (result && result.parentNode)
		{
			result.parentNode.innerHTML = `<a href="${encodeURI(element.src)}" target="_blank">${element.src}</a>`;
		}
	},
};
