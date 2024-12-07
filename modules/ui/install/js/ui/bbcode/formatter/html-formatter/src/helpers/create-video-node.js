import { Dom } from 'main.core';

export function createVideoNode({ url, width, height, type }): HTMLSpanElement
{
	const video: HTMLVideoElement = Dom.create({
		tag: 'video',
		attrs: {
			controls: true,
			className: 'ui-typography-video-object',
			preload: 'metadata',
			playsinline: true,
			src: url,
			width,
		},
		dataset: {
			decorator: true,
		},
		style: {
			aspectRatio: width > 0 && height > 0 ? `${width} / ${height}` : 'auto',
		},
		/* children: [
			Dom.create({
				tag: 'source',
				attrs: {
					type,
					src: url,
				},
			}),
		], */
	});

	return Dom.create({
		tag: 'span',
		attrs: {
			className: 'ui-typography-video-container',
		},
		dataset: {
			decorator: true,
		},
		children: [video],
	});
}
