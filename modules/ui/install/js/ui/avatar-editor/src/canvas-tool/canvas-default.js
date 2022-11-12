import {Dom, Loc, Type, Cache, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Options} from '../options';
import CanvasLoader from "./canvas-loader";


export class CanvasDefault extends EventEmitter
{
	static imageSize = {
		width: 1024,
		height: 1024,
	};

	#isSet: Boolean = false;
	#canvas: HTMLCanvasElement;
	#ctx: CanvasRenderingContext2D;

	#table: Element;
	#tableCloth: Element;
	#tableFrame: Element;

	#stretchToCanvasSize = true;

	constructor(canvas: Element, options: ?Object) {
		super();
		this.setEventNamespace('Main.Avatar.Editor');

		this.#canvas = canvas;
		this.#ctx = this.#canvas.getContext("2d");

		this.#table = this.#canvas.parentNode;

		if (this.#canvas.clientWidth)
		{
			this.#table.width = this.#canvas.clientWidth;
			this.#table.height = this.#canvas.clientHeight;
		}
		else
		{
			this.#table.width = this.#canvas.width;
			this.#table.height = this.#canvas.height;
		}

		Dom.adjust(this.#canvas, {
			style: {
				// 'background' : '#fdbd00',
			}}
		);
		Dom.adjust(this.#table, {
			style: {
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
				border: 'none',
				position: 'relative',
				overflow: 'visible',
			},
			dataset: {
				role: 'table',
				width: this.#table.width,
				height: this.#table.height,
			}
		})

		this.#tableCloth = Dom.create('DIV');
		Dom.adjust(this.#tableCloth, {
			style: {
				'border': 'none',
				// 'background' : '#ffd7e1',
				'position' : 'absolute',
				'display': 'flex',
				'align-items': 'center',
				'justify-content': 'center',
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
				left: 0,
				top: 0,
			},
			dataset: {
				role: 'tableCloth'
			}
		});
		this.#tableCloth.appendChild(this.#canvas);
		this.#table.appendChild(this.#tableCloth);

		this.#tableFrame = Dom.create('DIV');
		Dom.adjust(this.#tableFrame, {
			style: {
				'box-sizing': 'border-box',
				// 'border' : '4px dotted grey',
				'position' : 'absolute',
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
			},
			dataset: {
				role: 'tableFrame'
			}
		});
		this.#tableCloth.appendChild(this.#tableFrame);

		this.mouseMove = this.mouseMove.bind(this);
		this.stopMoving = this.stopMoving.bind(this);

		this.#stretchToCanvasSize = !(options && options.stretchToCanvasSize === false);

		this.reset();
	}

	isEmpty(): boolean
	{
		return !this.#isSet;
	}

	reset()
	{
		this.#ctx.clearRect(0, 0, this.#canvas.width, this.#canvas.height);
		this.#isSet = false;

		Dom.adjust(this.#tableCloth, {
			style: {
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
				left: 0,
				top: 0,
			}
		});

		Dom.adjust(this.#canvas, {
			style: {
				transform: 'none',
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
			}
		});

		Dom.adjust(this.#tableFrame, {
			style: {
				width: `${this.#table.width}px`,
				height: `${this.#table.height}px`,
				left: 0,
				top: 0,
			}
		});

		this.disableToMove();

		this.emit('onReset', this.#canvas);
	}

	set(imageNode)
	{
		//region set image
		const image = {
			width: 0,
			height: 0,
			scale: 1,
			name: imageNode["name"]
		};

		if (imageNode.clientWidth)
		{
			image.width = imageNode.clientWidth;
			image.height = imageNode.clientHeight;
		}
		else
		{
			image.width = imageNode.width;
			image.height = imageNode.height;
		}
		if (image.width <= 0 || image.height <= 0)
		{
			return;
		}

		const scaleForImage = Math.ceil(Math.max(
			(image.width > Options.imageSize ? Options.imageSize / image.width : (
				this.#stretchToCanvasSize !== false && image.width < this.#table.width
					? this.#table.width / image.width : 1
			)),
			(image.height > Options.imageSize ? Options.imageSize / image.height : (
				this.#stretchToCanvasSize !== false && image.height < this.#table.height
					? this.#table.height / image.height : 1
			))
		) * 1000) / 1000;

		image.width = Math.ceil(image.width * scaleForImage);
		image.height = Math.ceil(image.height * scaleForImage);

		const k = Math.ceil(Math.max(
			(image.width > 0 ? this.#table.width / image.width : 1),
			(image.height > 0 ? this.#table.height / image.height : 1)
		) * 1000) / 1000;

		image.scale = (0 < k && k < 1 ? k : 1);


		Dom.adjust(
			this.#canvas,
			{
				props: {
					width: image.width,
					height: image.height
				},
				style: {
					width: image.width + 'px',
					height: image.height + 'px',
					transform:
						'scale(' + image.scale + ', ' + image.scale + ')'
				},
				dataset: {
					width: image.width,
					height: image.height,
					scale: image.scale,
					initialScale: image.scale,
					hasChanged: false
				}
			}
		);
		this.#isSet = true;
		this.#ctx.drawImage(imageNode, 0, 0, image.width, image.height);
		//endregion

		//region set TableCloth
		const tableFrame = {
			width: this.#table.width,
			height: this.#table.height,
		};

		const tableCloth = {
			width: 2 * tableFrame.width + Math.ceil(image.scale * image.width),
			height: 2 * tableFrame.height + Math.ceil(image.scale * image.height),
			left: (-1) * Math.ceil((tableFrame.width + image.scale * image.width) / 2),
			top: (-1) * Math.ceil((tableFrame.height + image.scale * image.height) / 2)
		};
		Dom.adjust(this.#tableCloth, {
			style: {
				width: `${tableCloth.width}px`,
				height: `${tableCloth.height}px`,
				top: `${tableCloth.top}px`,
				left: `${tableCloth.left}px`
			},
			dataset: {
				top: tableCloth.top,
				left: tableCloth.left,
				topToBeInTheCenter: tableCloth.top,
				leftToBeInTheCenter: tableCloth.left,
				height: tableCloth.height,
				width: tableCloth.width,
			}
		});

		Dom.adjust(this.#tableFrame, {
			style: {
				top: `${tableCloth.top * (-1)}px`,
				left: `${tableCloth.left * (-1)}px`
			}}
		);
		//endregion

		this.enableToMove();

		this.emit('onSetImage', {canvas: this.#canvas});
	}

	scale(zoomScale)
	{
		zoomScale = Math.max(0.01, (1 + zoomScale));
		const oldScale = Number(this.#canvas.dataset.scale);
		const newScale = zoomScale * Number(this.#canvas.dataset.initialScale);
		//region set image
		Dom.adjust(
			this.#canvas,
			{
				style: {
					transform: 'scale(' + newScale + ', ' + newScale + ')'
				},
				dataset: {
					scale: newScale,
					hasChanged: true
				}
			}
		);
		//endregion

		//region set TableCloth
		const tableFrame = {
			height: this.#table.height,
			width: this.#table.width,
		};

		const tableCloth = {
			height: 2 * tableFrame.height + Math.ceil(newScale * Number(this.#canvas.dataset.height)),
			width: 2 * tableFrame.width + Math.ceil(newScale * Number(this.#canvas.dataset.width)),
			topToBeInTheCenter: (-1) * Math.ceil((tableFrame.height + newScale * Number(this.#canvas.dataset.height)) / 2),
			leftToBeInTheCenter: (-1) * Math.ceil((tableFrame.width + newScale * Number(this.#canvas.dataset.width)) / 2),
			top: null,
			left: null,
		};
		const deltaTopOld = this.#tableCloth.dataset.top - this.#tableCloth.dataset.topToBeInTheCenter;
		const deltaTop = deltaTopOld / oldScale * newScale;
		tableCloth.top = Math.ceil(tableCloth.topToBeInTheCenter + deltaTop);
		tableCloth.top =  Math.max(
			Math.min(
				tableCloth.top,
				0
			),
			(-1) * (Number(tableCloth.height) - tableFrame.height)
		);

		const deltaLeftOld = (this.#tableCloth.dataset.left - this.#tableCloth.dataset.leftToBeInTheCenter);
		const deltaLeft = deltaLeftOld / oldScale * newScale;
		tableCloth.left = Math.ceil(deltaLeft + tableCloth.leftToBeInTheCenter);
		tableCloth.left =  Math.max(
			Math.min(
				tableCloth.left,
				0
			),
			(-1) * (Number(tableCloth.width) - tableFrame.width)
		);

		Dom.adjust(this.#tableCloth, {
			style: {
				width: `${tableCloth.width}px`,
				height: `${tableCloth.height}px`,
				top: `${tableCloth.top}px`,
				left: `${tableCloth.left}px`
			},
			dataset: {
				top: tableCloth.top,
				left: tableCloth.left,
				topToBeInTheCenter: tableCloth.topToBeInTheCenter,
				leftToBeInTheCenter: tableCloth.leftToBeInTheCenter,
				height: tableCloth.height,
				width: tableCloth.width,
			}
		});
		//endregion

		//region set Table sights
		Dom.adjust(this.#tableFrame, {
			style: {
				top: `${tableCloth.top * (-1)}px`,
				left: `${tableCloth.left * (-1)}px`
			}}
		);
		//endregion


		this.emit('onScale', {
			zoomScale,
			scale: newScale,
			topOffsetFromTheCenter: Number(this.#tableCloth.dataset.top) - Number(this.#tableCloth.dataset.topToBeInTheCenter),
			leftOffsetFromTheCenter: Number(this.#tableCloth.dataset.left) - Number(this.#tableCloth.dataset.leftToBeInTheCenter),
			topInPercent: Math.ceil(this.#tableCloth.dataset.top * 1000 / Number(this.#tableCloth.dataset.topToBeInTheCenter)) / 1000,
			leftInPercent: Math.ceil(this.#tableCloth.dataset.left * 1000 / Number(this.#tableCloth.dataset.leftToBeInTheCenter)) / 1000,
			oldScale,
		});
	}

	onScale({data: {oldScale, scale, topOffsetFromTheCenter, leftOffsetFromTheCenter, topInPercent, leftInPercent}})
	{
		const deltaScale = scale / oldScale;
		let top, left, newScale;
		if (Number(oldScale) === Number(this.#canvas.dataset.scale))
		{
			newScale = scale;
		}
		else
		{
			newScale = this.#canvas.dataset.scale * deltaScale;
		}

		//region set image
		Dom.adjust(
			this.#canvas,
			{
				style: {
					transform: 'scale(' + newScale + ', ' + newScale + ')'
				},
				dataset: {
					scale: newScale,
					hasChanged: true
				}
			}
		);
		//endregion

		//region set TableCloth
		const tableFrame = {
			height: this.#table.height,
			width: this.#table.width,
		};

		const tableCloth = {
			height: 2 * tableFrame.height + Math.ceil(newScale * Number(this.#canvas.dataset.height)),
			width: 2 * tableFrame.width + Math.ceil(newScale * Number(this.#canvas.dataset.width)),
			topToBeInTheCenter: (-1) * Math.ceil((tableFrame.height + newScale * Number(this.#canvas.dataset.height)) / 2),
			leftToBeInTheCenter: (-1) * Math.ceil((tableFrame.width + newScale * Number(this.#canvas.dataset.width)) / 2),
			top: null,
			left: null,
		};

		if (Number(scale) === Number(this.#canvas.dataset.scale))
		{
			top = Number(this.#tableCloth.dataset.topToBeInTheCenter) + topOffsetFromTheCenter;
			left = Number(this.#tableCloth.dataset.leftToBeInTheCenter) + leftOffsetFromTheCenter;
		}
		else
		{
			top =  Math.ceil(Number(this.#tableCloth.dataset.topToBeInTheCenter) * topInPercent);
			left = Math.ceil(Number(this.#tableCloth.dataset.leftToBeInTheCenter) * leftInPercent);
		}
		tableCloth.top =  Math.max(
			Math.min(
				top,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.height) - this.#table.height)
		);
		tableCloth.left = Math.max(
			Math.min(
				left,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.width) - this.#table.width)
		);

		Dom.adjust(this.#tableCloth, {
			style: {
				width: `${tableCloth.width}px`,
				height: `${tableCloth.height}px`,
				top: `${tableCloth.top}px`,
				left: `${tableCloth.left}px`
			},
			dataset: {
				top: tableCloth.top,
				left: tableCloth.left,
				topToBeInTheCenter: tableCloth.topToBeInTheCenter,
				leftToBeInTheCenter: tableCloth.leftToBeInTheCenter,
				height: tableCloth.height,
				width: tableCloth.width,
			}
		});
		//endregion

		//region set Table sights
		Dom.adjust(this.#tableFrame, {
			style: {
				top: `${tableCloth.top * (-1)}px`,
				left: `${tableCloth.left * (-1)}px`
			}}
		);
		//endregion
	}

	enableToMove()
	{
		if (this.#table.style.cursor === 'move')
		{
			return;
		}

		this.#table.style.cursor = 'move';
		Event.bind(this.#table, 'mousedown', (e) => {
			this.cursor = {
				startX : e.pageX,
				startY : e.pageY
			};
			Event.bind(document, 'mousemove', this.mouseMove);
			Event.bind(document, 'mouseup', this.stopMoving);
		});
	}

	disableToMove()
	{
		this.#table.style.cursor = 'default';
		Event.unbindAll(this.#table);
		Event.unbind(document, 'mousemove', this.mouseMove);
		Event.unbind(document, 'mouseup', this.stopMoving);
	}

	move(deltaX, deltaY)
	{
		//region set image
		Dom.adjust(
			this.#canvas,
			{
				dataset: {
					hasChanged: true
				}
			}
		);
		//endregion

		//region set TableCloth
		const tableFrame = {
			height: this.#table.height,
			width: this.#table.width,
		};

		const left = Math.max(
			Math.min(
				Number(this.#tableCloth.dataset.left) - deltaX,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.width) - tableFrame.width)
		);
		const top =  Math.max(
			Math.min(
				Number(this.#tableCloth.dataset.top) - deltaY,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.height) - tableFrame.height)
		);

		Dom.adjust(this.#tableCloth, {
			style: {
				top: `${top}px`,
				left: `${left}px`
			},
			dataset: {
				top: top,
				left: left
			}
		});

		this.emit('onMove',  {
			topOffsetFromTheCenter: top - Number(this.#tableCloth.dataset.topToBeInTheCenter),
			leftOffsetFromTheCenter: left - Number(this.#tableCloth.dataset.leftToBeInTheCenter),
			scale: this.#canvas.dataset.scale,
			topInPercent: Math.ceil(top * 1000 / Number(this.#tableCloth.dataset.topToBeInTheCenter)) / 1000,
			leftInPercent: Math.ceil(left * 1000 / Number(this.#tableCloth.dataset.leftToBeInTheCenter)) / 1000,
		});
		//region set Table sights
		Dom.adjust(this.#tableFrame, {
			style: {
				top: `${top * (-1)}px`,
				left: `${left * (-1)}px`
			}}
		);
		//endregion
	}

	onMove({data: {scale, topOffsetFromTheCenter, leftOffsetFromTheCenter, topInPercent, leftInPercent}})
	{
		let top, left;
		if (Number(scale) === Number(this.#canvas.dataset.scale))
		{
			top = Number(this.#tableCloth.dataset.topToBeInTheCenter) + topOffsetFromTheCenter;
			left = Number(this.#tableCloth.dataset.leftToBeInTheCenter) + leftOffsetFromTheCenter;
		}
		else
		{
			top =  Math.ceil(Number(this.#tableCloth.dataset.topToBeInTheCenter) * topInPercent);
			left = Math.ceil(Number(this.#tableCloth.dataset.leftToBeInTheCenter) * leftInPercent);
		}
		left = Math.max(
			Math.min(
				left,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.width) - this.#table.width)
		);
		top =  Math.max(
			Math.min(
				top,
				0
			),
			(-1) * (Number(this.#tableCloth.dataset.height) - this.#table.height)
		);

		Dom.adjust(this.#tableCloth, {
			style: {
				top: `${top}px`,
				left: `${left}px`
			},
			dataset: {
				top: top,
				left: left
			}
		});
	}

	mouseMove(e)
	{
		if (this.cursor === null)
		{
			return;
		}
		this.move(this.cursor.startX - e.pageX, this.cursor.startY - e.pageY);
		this.cursor.startX = e.pageX;
		this.cursor.startY = e.pageY;
	}

	stopMoving()
	{
		BX.unbind(document, "mousemove", this.mouseMove);
		BX.unbind(document, "mouseup", this.stopMoving);
	}

	getCanvas()
	{
		return this.#canvas;
	}

	getContext()
	{
		return this.#ctx;
	}

	getTable()
	{
		return this.#table;
	}

	getTableCloth()
	{
		return this.#tableCloth;
	}

	packBlob(): Promise
	{
		if (this.#isSet !== true)
		{
			return Promise.reject({message: 'Source canvas does not exist.', code: 'empty data'});
		}

		return new Promise((resolve, reject) => {
			//region set TableCloth
			const tableFrame = {
				height: this.#table.height,
				width: this.#table.width,
			};
			const scale = this.#canvas.dataset.scale;
			const size = Math.min(
				Options.imageSize,
				Math.max(
					this.#table.width / scale,
					this.#table.width
				)
			);
			const scaleToResult = size / this.#table.width;
			const loader = CanvasLoader.getInstance();

			loader.getCanvas().height = size;
			loader.getCanvas().width = size;
			loader.getContext().clearRect(0, 0, size, size);
			//region getY
			let imageY, imageY1, imageY2;
			let canvasY1, canvasY2;

			imageY = ((-1)  * (this.#tableCloth.dataset.topToBeInTheCenter
				 - this.#tableCloth.dataset.top)
				 + tableFrame.height / 2
				 - this.#canvas.height * scale / 2
			 );

			if (imageY > 0)
			{
				canvasY1 = imageY;
				imageY1 = 0;
			}
			else
			{
				canvasY1 = 0;
				imageY1 = (-1) * imageY;
			}

			imageY2 = imageY + this.#canvas.height * scale;
			if (imageY2 > tableFrame.height)
			{
				canvasY2 = tableFrame.height;
				imageY2 = tableFrame.height - imageY;
			}
			else
			{
				canvasY2 = imageY2;
				imageY2 = this.#canvas.height * scale;
			}
			//endregion
			//region getX
			let imageX, imageX1, imageX2;
			let canvasX1, canvasX2;

			imageX = ((-1) * (this.#tableCloth.dataset.leftToBeInTheCenter
					- this.#tableCloth.dataset.left)
				+ tableFrame.width / 2
				- this.#canvas.width * scale / 2
			);

			if (imageX > 0)
			{
				canvasX1 = imageX;
				imageX1 = 0;
			}
			else
			{
				canvasX1 = 0;
				imageX1 = (-1) * imageX;
			}

			imageX2 = imageX + this.#canvas.width * scale;
			if (imageX2 > tableFrame.width)
			{
				canvasX2 = tableFrame.width;
				imageX2 = tableFrame.width - imageX;
			}
			else
			{
				canvasX2 = imageX2;
				imageX2 = this.#canvas.width * scale;
			}
			//endregion

			imageX1 /= scale;
			imageX2 /= scale;
			imageY1 /= scale;
			imageY2 /= scale;
			canvasX1 *=  scaleToResult;
			canvasY1 *= scaleToResult;
			canvasX2 *= scaleToResult;
			canvasY2 *= scaleToResult;

			loader.getContext().drawImage(
				this.#canvas,
				imageX1, imageY1, imageX2 - imageX1, imageY2 - imageY1,
				canvasX1, canvasY1, canvasX2 - canvasX1, canvasY2 - canvasY1
			);

			const changed = this.#canvas.dataset.changed;
			loader.pack()
				.then((blob) => {
					blob.changed = changed;
					blob.width = size;
					blob.height = size;
					resolve(blob);
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}
}

