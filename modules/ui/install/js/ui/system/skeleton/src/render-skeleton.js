import { localStorage } from 'main.core';
import { Circle } from './circle';
import { Line } from './line';

const map: Map<string, string> = new Map();
let css = null;

export async function renderSkeleton(path: string, root: HTMLElement): Promise<HTMLElement>
{
	let html = map.get(path);
	if (!html)
	{
		html = (await fetch(path).then((r) => r.text()))
			.replaceAll(/(Circle|Line)\((.*)\)/g, (_, fn, args) => parse({ Line, Circle }[fn], args))
			.replaceAll(/{(.+)}/g, (_, key) => localStorage.get(key))
		;

		map.set(path, html);
	}

	const shadowRoot = root.attachShadow({ mode: 'open' });
	css ??= [...document.styleSheets].find(({ href }) => href?.includes('ui/system/skeleton'))?.href;
	shadowRoot.innerHTML = `${html}<link rel="stylesheet" href="${css}">`;

	return root;
}

const parse = (func, args) => func(...args.split(',')
	.filter((it) => it)
	.map((value) => (value.trim() === 'null' ? null : value)))
	.outerHTML
;
