import {Text, Dom, Tag} from 'main.core';
import {EventEmitter} from "main.core.events";
import CanvasLoader from "./canvas-loader";
import {Options} from '../options';

type ActiveMaskType = {
	id: String,
	src: String
};

export default class CanvasMask extends EventEmitter
{
	#canvas: ?HTMLCanvasElement;
	#ctx: ?CanvasRenderingContext2D;
	#container: HTMLElement;
	#activeMask: ?ActiveMaskType = null;

	constructor(container)
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');
		this.#container = container;
		this.set = this.set.bind(this);
		this.mask = this.mask.bind(this);
		this.unmask = this.unmask.bind(this);
	}

	mask({id, src, thumb})
	{
		Dom.style(this.#container, 'background-image', 'url("' + Text.encode(thumb) + '")');
		this.#activeMask = {id, src};
		CanvasLoader.loadFile(
			src,
			this.set,
			this.unmask
		);
	}

	set(imageSource)
	{
		//region set image
		const trueK = Math.max(
			imageSource.width > 0 ? Options.maskSize / imageSource.width : 1,
			imageSource.height > 0 ? Options.maskSize / imageSource.height : 1
		);
		const sourceS = parseInt(Options.maskSize / trueK);
		const sourceX = parseInt((imageSource.width - sourceS) / 2);
		const sourceY = parseInt((imageSource.height - sourceS) / 2);
		this.getCanvas().width = Options.maskSize;
		this.getCanvas().height = Options.maskSize;
		this.getContext().clearRect(0, 0, Options.maskSize, Options.maskSize);
		this.getContext().drawImage(imageSource,
			sourceX, sourceY, sourceS, sourceS,
			0, 0, Options.maskSize, Options.maskSize
		);
		//endregion
	}

	applyAndPack(imageSource: HTMLCanvasElement): Promise
	{
		if (this.#activeMask === null
			|| imageSource.width < 100
			|| imageSource.height < 100
		)
		{
			return Promise.reject({message: 'Image for masking has not appropriate format', code: 'bad image'});
		}
		return new Promise((resolve) => {
			const trueK = Math.max(
				imageSource.width > 0 ? Options.maskSize / imageSource.width : 1,
				imageSource.height > 0 ? Options.maskSize / imageSource.height : 1
			);

			const sourceS = parseInt(Options.maskSize / trueK);
			const sourceX = parseInt((imageSource.width - sourceS) / 2);
			const sourceY = parseInt((imageSource.height - sourceS) / 2);

			const loader = CanvasLoader.getInstance();
			loader.getCanvas().width = Options.maskSize;
			loader.getCanvas().height = Options.maskSize;

			loader.getContext().clearRect(0, 0, Options.maskSize, Options.maskSize);
			loader.getContext().drawImage(imageSource,
				sourceX, sourceY, sourceS, sourceS,
				0, 0, Options.maskSize, Options.maskSize
			);
			loader.getContext().drawImage(
				this.getCanvas(),
				0, 0, Options.maskSize, Options.maskSize
			);
			loader
				.pack('image/png')
				.then(blob => {
					blob.name = 'mask.png';
					blob.maskId = this.#activeMask.id;
					resolve(blob, this.#activeMask.id);
				})
			;
		});
	}

	getCanvas(): HTMLCanvasElement
	{
		if (!this.#canvas)
		{
			this.#canvas = document.createElement('CANVAS');
		}
		return this.#canvas;
	}

	getContext(): CanvasRenderingContext2D
	{
		if (!this.#ctx)
		{
			this.#ctx = this.getCanvas().getContext("2d");
		}
		return this.#ctx;
	}

	unmask()
	{
		this.#container.style.backgroundImage = '';
		this.#activeMask = null;
	}
}