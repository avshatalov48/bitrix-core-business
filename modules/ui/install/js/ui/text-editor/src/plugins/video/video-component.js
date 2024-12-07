import { Dom, Tag, Type } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import type { BaseEvent } from 'main.core.events';
import type { EditorConfig } from 'ui.lexical.core';

import { $getNodeByKey } from 'ui.lexical.core';

import DecoratorComponent from '../../decorator-component';

import type { JsonObject } from 'main.core';
import { calcImageSize } from '../../helpers/calc-image-size';
import type { DecoratorComponentOptions } from '../../types/decorator-component-options';
import { $isVideoNode, VideoNode } from './video-node';
import FigureResizer from '../../helpers/figure-resizer';

export default class VideoComponent extends DecoratorComponent
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#figureResizer: FigureResizer = null;
	#trusted: boolean = false;

	constructor(options: DecoratorComponentOptions)
	{
		super(options);

		this.#trusted = Type.isStringFilled(this.getOption('provider'));

		this.#figureResizer = new FigureResizer({
			target: this.#getVideo(),
			editor: this.getEditor(),
			minWidth: 120,
			minHeight: 120,
			freeTransform: true,
			events: {
				onResize: this.#handleResize.bind(this),
				onResizeEnd: this.#handleResizeEnd.bind(this),
			},
		});

		this.getNodeSelection().onSelect((selected: boolean) => {
			if (selected || this.#figureResizer.isResizing())
			{
				Dom.addClass(this.#getContainer(), '--selected');
				this.#figureResizer.show();
			}
			else
			{
				Dom.removeClass(this.#getContainer(), '--selected');
				this.#figureResizer.hide();
			}
		});

		this.update(this.getOptions());
		this.#render();
	}

	#render()
	{
		Dom.append(this.#getContainer(), this.getTarget());
	}

	#getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-text-editor-video-component">
					<div class="ui-text-editor-video-object-container">${this.#getVideo()}</div>
					${this.#figureResizer.getContainer()}
				</div>
			`;
		});
	}

	#getVideo(): HTMLIFrameElement | HTMLVideoElement
	{
		return this.#refs.remember('video', () => {
			let video: HTMLIFrameElement | HTMLVideoElement = null;
			const src = this.getOption('src');
			if (this.#trusted)
			{
				video = Tag.render`<iframe frameborder="0" src="about:blank" draggable="false"></iframe>`;
				video.src = src;
			}
			else
			{
				video = Dom.create({
					tag: 'video',
					attrs: {
						controls: true,
						preload: 'metadata',
						playsinline: true,
						src,
					},
					events: {
						loadedmetadata: (event: Event) => {
							this.getEditor().update(() => {
								const node: VideoNode = $getNodeByKey(this.getNodeKey());
								if ($isVideoNode(node) && node.getWidth() === 0)
								{
									const [width, height] = calcImageSize(
										event.target.videoWidth,
										event.target.videoHeight,
										600,
										600,
									);

									node.setWidthAndHeight(width, height);
								}
							});
						},
					},
				});
			}

			const config: EditorConfig = this.getOption('config', {});
			if (config?.theme?.video?.object)
			{
				video.className = config.theme.video.object;
			}

			return video;
		});
	}

	#handleResize(event: BaseEvent): void
	{
		this.update(event.getData());
	}

	#handleResizeEnd(event: BaseEvent): void
	{
		this.setSelected(true);

		this.getEditor().update(() => {
			const node: VideoNode = $getNodeByKey(this.getNodeKey());
			if ($isVideoNode(node))
			{
				const { width, height } = event.getData();
				node.setWidthAndHeight(width, height);
			}
		});
	}

	update(options: JsonObject): void
	{
		const width = Type.isNumber(options.width) && options.width > 0 ? options.width : null;
		const height = Type.isNumber(options.height) && options.height > 0 ? options.height : null;
		const aspectRatio = width > 0 && height > 0 ? `${width} / ${height}` : 'auto';

		Dom.adjust(this.#getVideo(), {
			attrs: {
				width,
			},
			style: {
				width,
				height: 'auto',
				aspectRatio,
			},
		});
	}
}
