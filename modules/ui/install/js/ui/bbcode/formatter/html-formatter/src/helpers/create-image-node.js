import { Dom } from 'main.core';

export function createImageNode({ src, width, height }): HTMLSpanElement
{
	return Dom.create({
		tag: 'span',
		attrs: {
			className: 'ui-typography-image-container',
		},
		dataset: {
			decorator: true,
		},
		children: [
			Dom.create({
				tag: 'img',
				attrs: {
					src,
					className: 'ui-typography-image',
					width,
					loading: 'lazy',
				},
				style: {
					aspectRatio: width > 0 && height > 0 ? `${width} / ${height}` : 'auto',
				},
				events: {
					error: (event) => {
						const img: HTMLImageElement = event.target;
						img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
						Dom.addClass(img.parentNode, '--error ui-icon-set__scope');
					},
				},
			}),
		],
	});
}
