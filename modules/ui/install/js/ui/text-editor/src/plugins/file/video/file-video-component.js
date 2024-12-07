import type { JsonObject } from 'main.core';
import { Dom, Tag, Type } from 'main.core';
import { type BaseCache, MemoryCache } from 'main.core.cache';
import type { BaseEvent } from 'main.core.events';
import { $getNodeByKey } from 'ui.lexical.core';
import type { EditorConfig } from 'ui.lexical.core';

import DecoratorComponent from '../../../decorator-component';
import { calcImageSize } from '../../../helpers/calc-image-size';
import FigureResizer from '../../../helpers/figure-resizer';
import type { DecoratorComponentOptions } from '../../../types/decorator-component-options';
import { $isFileVideoNode, FileVideoNode } from './file-video-node';

export class FileVideoComponent extends DecoratorComponent
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#figureResizer: FigureResizer = null;

	constructor(options: DecoratorComponentOptions)
	{
		super(options);

		this.#figureResizer = new FigureResizer({
			target: this.#getVideo(),
			editor: this.getEditor(),
			minWidth: 120,
			minHeight: 120,
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

			this.#setDraggable(selected);
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
			const video: HTMLVideoElement = Dom.create({
				tag: 'video',
				attrs: {
					controls: true,
					preload: 'metadata',
					playsinline: true,
					src: this.getOption('src'),
				},
				events: {
					loadedmetadata: (event: Event) => {
						this.getEditor().update(() => {
							const node: FileVideoNode = $getNodeByKey(this.getNodeKey());
							if ($isFileVideoNode(node) && node.getWidth() === 0)
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
			const node: FileVideoNode = $getNodeByKey(this.getNodeKey());
			if ($isFileVideoNode(node))
			{
				const { width, height } = event.getData();
				node.setWidthAndHeight(width, height);
			}
		});
	}

	#setDraggable(draggable: boolean): void
	{
		Dom.attr(this.#getContainer(), { draggable });
		if (draggable)
		{
			Dom.addClass(this.#getContainer(), '--draggable');
		}
		else
		{
			Dom.removeClass(this.#getContainer(), '--draggable');
		}
	}

	update(options: JsonObject): void
	{
		const width = Type.isNumber(options.width) && options.width > 0 ? options.width : null;
		const height = Type.isNumber(options.height) && options.height > 0 ? options.height : null;
		const aspectRatio = width > 0 && height > 0 ? `${width} / ${height}` : 'auto';

		Dom.adjust(this.#getVideo(), {
			attrs: {
				width,
				height: null,
			},
			style: {
				width,
				height: 'auto',
				aspectRatio,
			},
		});
	}
}
