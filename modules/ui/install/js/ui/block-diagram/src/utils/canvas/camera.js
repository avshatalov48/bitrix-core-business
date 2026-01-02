import { mat3, vec2 } from '../../lib/gl-matrix';

declare type Landmark = {
	zoom: number,
	x: number,
	y: number,
	rotation: number,
	viewportX: number,
	viewportY: number,
};

export class Camera
{
	#x: number = 0;
	#y: number = 0;
	#rotation: number = 0;
	#zoom: number = 1;
	#width: number = 0;
	#height: number = 0;
	#matrix: Float32Array = mat3.create();
	#projectionMatrix: Float32Array = mat3.create();
	#viewMatrix: Float32Array = mat3.create();
	#viewProjectionMatrix: Float32Array = mat3.create();
	#viewProjectionMatrixInv: Float32Array = mat3.create();

	#changeCallback: (...args: any) => void | null = null;

	constructor(options)
	{
		const {
			width,
			height,
			onChangeCallback,
		} = options;

		this.#changeCallback = onChangeCallback;
		this.projection(width, height);
		this.updateMatrix();
		this.#onChangedTransformParams({ width, height });
	}

	get projectionMatrix(): Float32Array
	{
		return this.#projectionMatrix;
	}

	get viewMatrix(): Float32Array
	{
		return this.#viewMatrix;
	}

	get viewProjectionMatrix(): Float32Array
	{
		return this.#viewProjectionMatrix;
	}

	get viewProjectionMatrixInv(): Float32Array
	{
		return this.#viewProjectionMatrixInv;
	}

	get matrix(): Float32Array
	{
		return this.#matrix;
	}

	get zoom(): number
	{
		return this.#zoom;
	}

	set zoom(zoom: number): void
	{
		if (this.#zoom !== zoom)
		{
			this.#zoom = zoom;
			this.#onChangedTransformParams({ zoom });
			this.updateMatrix();
		}
	}

	get x(): number
	{
		return this.#x;
	}

	set x(x: number): void
	{
		if (this.#x !== x)
		{
			this.#x = x;
			this.#onChangedTransformParams({ x });
			this.updateMatrix();
		}
	}

	get y(): number
	{
		return this.#y;
	}

	set y(y: number): void
	{
		if (this.#y !== y)
		{
			this.#y = y;
			this.#onChangedTransformParams({ y });
			this.updateMatrix();
		}
	}

	get rotation(): number
	{
		return this.#rotation;
	}

	set rotation(rotation: number): void
	{
		if (this.#rotation !== rotation)
		{
			this.#rotation = rotation;
			this.updateMatrix();
		}
	}

	get width(): number
	{
		return this.#width;
	}

	get height(): number
	{
		return this.#height;
	}

	setChangeTransformCallback(callback: ({ x: number, y: number, zoom: number }) => any): void
	{
		this.#changeCallback = callback;
	}

	#onChangedTransformParams(params: {...}): void
	{
		this.#changeCallback({
			x: this.#x,
			y: this.#y,
			zoom: this.#zoom,
			width: this.#width,
			height: this.#height,
			...params,
		});
	}

	clone(width = null, height = null): Camera
	{
		const camera = new Camera({
			width: width || this.#width,
			height: height || this.#height,
			onChangeCallback: this.#changeCallback,
		});
		camera.#x = this.#x;
		camera.#y = this.#y;
		camera.#zoom = this.#zoom;
		camera.#rotation = this.#rotation;
		camera.updateMatrix();

		return camera;
	}

	projection(width, height): void
	{
		this.#width = width;
		this.#height = height;
		mat3.projection(this.#projectionMatrix, width, height);
		this.updateViewProjectionMatrix();
	}

	updateMatrix(): void
	{
		const zoomScale = 1 / this.#zoom;
		mat3.identity(this.#matrix);
		mat3.translate(this.#matrix, this.#matrix, [this.#x, this.#y]);
		mat3.rotate(this.#matrix, this.#matrix, this.#rotation);
		mat3.scale(this.#matrix, this.#matrix, [zoomScale, zoomScale]);
		mat3.invert(this.#viewMatrix, this.#matrix);
		this.updateViewProjectionMatrix();
	}

	updateViewProjectionMatrix(): void
	{
		mat3.multiply(
			this.#viewProjectionMatrix,
			this.#projectionMatrix,
			this.#viewMatrix,
		);
		mat3.invert(this.#viewProjectionMatrixInv, this.#viewProjectionMatrix);
	}

	createLandmark(params: Partial<Landmark> = {}): Landmark
	{
		return {
			zoom: this.#zoom,
			x: this.#x,
			y: this.#y,
			rotation: this.#rotation,
			...params,
		};
	}

	viewportToCanvas(
		{ x, y }: { x: number, y: number },
		camera: Camera,
	): { x: number, y: number }
	{
		const { width, height, viewProjectionMatrixInv } = camera || this;
		const canvas = vec2.transformMat3(
			vec2.create(),
			[(x / width) * 2 - 1, (1 - y / height) * 2 - 1],
			viewProjectionMatrixInv,
		);

		return { x: canvas[0], y: canvas[1] };
	}

	applyLandmark(landmark: Landmark): void
	{
		const { x, y, zoom, rotation, viewportX, viewportY } = landmark;
		const useFixedViewport = viewportX || viewportY;
		let preZoomX = 0;
		let preZoomY = 0;

		if (useFixedViewport)
		{
			const canvas = this.viewportToCanvas({
				x: viewportX,
				y: viewportY,
			});
			preZoomX = canvas.x;
			preZoomY = canvas.y;
		}

		this.#zoom = zoom;
		this.#rotation = rotation;
		this.#x = x;
		this.#y = y;
		this.updateMatrix();
		this.#onChangedTransformParams({
			x: this.#x,
			y: this.#y,
			zoom: this.#zoom,
		});

		if (useFixedViewport)
		{
			const { x: postZoomX, y: postZoomY } = this.viewportToCanvas({
				x: viewportX,
				y: viewportY,
			});
			this.#x += preZoomX - postZoomX;
			this.#y += preZoomY - postZoomY;
			this.updateMatrix();
			this.#onChangedTransformParams({
				x: this.#x,
				y: this.#y,
				zoom: this.#zoom,
			});
		}
	}
}
