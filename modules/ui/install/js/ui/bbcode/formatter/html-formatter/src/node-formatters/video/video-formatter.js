import { Dom, Type, Uri } from 'main.core';
import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';
import { VideoService } from 'ui.video-service';
import { createVideoNode } from '../../helpers/create-video-node';
import { sanitizeUrl } from '../../helpers/sanitize-url';
import { validateVideoUrl } from '../../helpers/validate-video-url';

export class VideoNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'video',
			convert({ node }: ConvertCallbackOptions): HTMLSpanElement {
				// [video type={type} width={width} height={height}]{url}[/video]
				const src = sanitizeUrl(node.getContent().trim());
				if (!validateVideoUrl(src))
				{
					return document.createTextNode(node.toString());
				}

				let width = Number(node.getAttribute('width'));
				let height = Number(node.getAttribute('height'));
				width = Type.isNumber(width) && width > 0 ? Math.round(width) : 560;
				height = Type.isNumber(height) && height > 0 ? Math.round(height) : 315;

				const url = /^https?:/.test(src) ? src : `https://${src.replace(/^\/\//, '')}`;
				const uri = new Uri(url);
				const trusted = VideoService.createByHost(uri.getHost()) !== null;
				if (trusted)
				{
					const video: HTMLIFrameElement = Dom.create({
						tag: 'iframe',
						attrs: {
							src,
							className: 'ui-typography-video-object',
							width,
							height: 'auto',
							frameborder: 0,
							allowfullscreen: true,
						},
						style: {
							aspectRatio: `${width} / ${height}`,
						},
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

				// const path = uri.getPath();
				// const position: number = path.lastIndexOf('.');
				// const extension = position >= 0 ? path.slice(Math.max(0, position + 1)).toLowerCase() : '';
				// const type = ['mp4', 'webm', 'mov'].includes(extension) ? `video/${extension}` : null;

				return createVideoNode({ url, width, height });
			},
			...options,
		});
	}
}
