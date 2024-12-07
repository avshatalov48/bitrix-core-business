/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type, Uri } from 'main.core';
import VideoComponent from './video-component';

import {
	DecoratorNode,
	$applyNodeReplacement,
	type DOMConversionMap,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type LexicalEditor,
	type NodeKey,
	type SerializedLexicalNode,
} from 'ui.lexical.core';

import type { DecoratorOptions } from '../../types/decorator-options';
import { VideoService } from 'ui.video-service';

export interface VideoPayload {
	src: string;
	width?: number;
	height?: number;
	maxWidth?: number;
	key?: NodeKey;
}

export type SerializedVideoNode = SerializedLexicalNode &
{
	src: string;
	width?: number;
	height?: number;
};

export class VideoNode extends DecoratorNode
{
	__src: string;
	__width: number = 560;
	__height: number = 315;
	__provider: string = null;

	constructor(
		src: string,
		width?: number,
		height?: number,
		key?: NodeKey,
	)
	{
		super(key);
		this.__src = src;

		if (Type.isNumber(width))
		{
			this.__width = Math.round(width);
		}

		if (Type.isNumber(height))
		{
			this.__height = Math.round(height);
		}

		const url = /^https?:/.test(src) ? src : `https://${src.replace(/^\/\//, '')}`;
		const uri = new Uri(url);
		const videoService = VideoService.createByHost(uri.getHost());
		if (videoService)
		{
			this.__provider = videoService.getId();
		}
	}

	static useDecoratorComponent = true;

	static getType(): string
	{
		return 'video';
	}

	static clone(node: VideoNode): VideoNode
	{
		return new VideoNode(
			node.__src,
			node.__width,
			node.__height,
			node.__key,
		);
	}

	static importJSON(serializedNode: SerializedVideoNode): VideoNode
	{
		const { width, height, src } = serializedNode;

		return $createVideoNode({ src, width, height });
	}

	exportDOM(): DOMExportOutput
	{
		return { element: null };
	}

	static importDOM(): DOMConversionMap | null
	{
		return null;
	}

	exportJSON(): SerializedVideoNode
	{
		return {
			src: this.getSrc(),
			width: this.getWidth(),
			height: this.getHeight(),
			type: 'video',
			version: 1,
		};
	}

	setWidthAndHeight(width: number, height: number): void
	{
		const writable = this.getWritable();
		if (Type.isNumber(width))
		{
			writable.__width = Math.round(width);
		}

		if (Type.isNumber(height))
		{
			writable.__height = Math.round(height);
		}
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const span = document.createElement('span');
		const theme = config.theme;
		const className = theme?.video?.container;
		if (className !== undefined)
		{
			span.className = className;
		}

		return span;
	}

	updateDOM(): false
	{
		return false;
	}

	getSrc(): string
	{
		return this.__src;
	}

	getWidth(): number
	{
		const self = this.getLatest();

		return self.__width;
	}

	getHeight(): number
	{
		const self = this.getLatest();

		return self.__height;
	}

	getProvider(): string | null
	{
		const self = this.getLatest();

		return self.__provider;
	}

	decorate(editor: LexicalEditor, config: EditorConfig): DecoratorOptions
	{
		return {
			componentClass: VideoComponent,
			options: {
				src: this.getSrc(),
				width: this.getWidth(),
				height: this.getHeight(),
				provider: this.getProvider(),
				config,
			},
		};
	}

	isInline(): true
	{
		return true;
	}
}

export function $createVideoNode({ src, width, height, key }): VideoNode
{
	return $applyNodeReplacement(new VideoNode(src, width, height, key));
}

export function $isVideoNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof VideoNode;
}
