import { ref } from 'ui.vue3';
import { mat3, vec2 } from '../../lib/gl-matrix';
import { Camera } from './camera';

export class Canvas
{
	#canvas: HTMLCanvasElement | null = null;
	#dpr: number = window.devicePixelRatio || 1;

	#width: number = 0;
	#height: number = 0;

	#minZoom: number = 0.02;
	#maxZoom: number = 4;

	#camera: Camera | null = null;
	#canvasStyleInstance = null;

	#startInvertViewProjectionMatrix: Float32Array = mat3.create();
	#startCameraX: number = 0;
	#startCameraY: number = 0;

	#startPos: Float32Array = vec2.create();

	#resizeObserver: ResizeObserver | null = null;

	transform = ref({
		x: 0,
		y: 0,
		zoom: 0,
	});

	constructor(options)
	{
		const {
			canvas,
			canvasStyle,
			minZoom,
			maxZoom,
		} = options;

		this.#minZoom = minZoom;
		this.#maxZoom = maxZoom;

		this.#initCanvas(canvas);
		this.#initCamera();
		this.#initCanvasStyle(canvasStyle);
		this.#initResizeObserver();
	}

	get viewMatrix(): Float32Array
	{
		return this.#camera?.viewMatrix ?? [];
	}

	get camera(): typeof Camera
	{
		return this.#camera;
	}

	#initCanvas(canvas: HTMLCanvasElement): void
	{
		const { width, height } = canvas.getBoundingClientRect();

		this.#canvas = canvas;
		this.#canvas.width = width * this.#dpr;
		this.#canvas.height = height * this.#dpr;
		this.#width = this.#canvas.width;
		this.#height = this.#canvas.height;
	}

	#initCamera(): void
	{
		this.#camera = new Camera({
			width: this.#width / this.#dpr,
			height: this.#height / this.#dpr,
			onChangeCallback: ({ x, y, zoom }) => {
				this.transform.x = x;
				this.transform.y = y;
				this.transform.zoom = zoom;
			},
		});
	}

	#initCanvasStyle(canvasStyle): void
	{
		if (canvasStyle)
		{
			const StyleInstance = canvasStyle.instance;
			this.#canvasStyleInstance = new StyleInstance(this.#canvas, canvasStyle.options);
		}
	}

	#initResizeObserver(): void
	{
		this.#resizeObserver = new ResizeObserver((entries) => {
			for (const entry of entries)
			{
				this.#camera = this.#camera.clone(
					entry.contentRect.width,
					entry.contentRect.height,
				);
			}
		});

		this.#resizeObserver.observe(this.#canvas);
	}

	clientToViewport({ x, y }: { x: number, y: number }): { x: number, y: number }
	{
		const { left, top } = this.#canvas.getBoundingClientRect();

		return { x: x - left, y: y - top };
	}

	viewportToClient({ x, y }: { x: number, y: number }): { x: number, y: number }
	{
		const { left, top } = this.#canvas.getBoundingClientRect();

		return { x: x + left, y: y + top };
	}

	render(): void
	{
		this.#canvasStyleInstance?.render({
			projectionMatrix: this.#camera.projectionMatrix,
			viewMatrix: this.#camera.viewMatrix,
			viewProjectionMatrixInv: this.#camera.viewProjectionMatrixInv,
			zoomScale: this.#camera.zoom,
		});
	}

	setCamera(params): void
	{
		this.#camera.applyLandmark(
			this.#camera.createLandmark({
				...params,
			}),
		);
	}

	#setCameraZoom(zoomStep: number): void
	{
		const zoom: number = Number((this.#camera.zoom + zoomStep).toFixed(1));

		if (zoom < this.#minZoom || zoom > this.#maxZoom)
		{
			return;
		}

		this.#camera.applyLandmark(
			this.#camera.createLandmark({
				x: this.#camera.x,
				y: this.#camera.y,
				viewportX: this.#camera.width / 2,
				viewportY: this.#camera.height / 2,
				zoom,
			}),
		);
	}

	zoomIn(zoomStep: number): void
	{
		this.#setCameraZoom(zoomStep);
	}

	zoomOut(zoomStep: number): void
	{
		this.#setCameraZoom(zoomStep * -1);
	}

	setCameraZoomByWheel(event: MouseEvent, zoomChange: number = 0): void
	{
		const newZoom = Math.max(
			this.#minZoom,
			Math.min(this.#maxZoom, (this.#camera.zoom + zoomChange) * 2 ** (event.deltaY * -0.01)),
		);

		const viewport = this.clientToViewport({
			x: event.clientX,
			y: event.clientY,
		});

		this.#camera.applyLandmark(
			this.#camera.createLandmark({
				viewportX: viewport.x,
				viewportY: viewport.y,
				zoom: newZoom,
			}),
		);
	}

	setCameraPositionByWheel(event: MouseEvent): void
	{
		this.#camera.x += event.deltaX / this.#camera.zoom;
		this.#camera.y += event.deltaY / this.#camera.zoom;
	}

	#getClipSpaceMousePosition(event: MouseEvent): Array<number, number>
	{
		const { left, top } = this.#canvas.getBoundingClientRect();
		const cssX: number = event.clientX - left;
		const cssY: number = event.clientY - top;

		const normalizedX: number = cssX / this.#canvas.clientWidth || this.#canvas.width / this.#dpr;
		const normalizedY: number = cssY / this.#canvas.clientHeight || this.#canvas.height / this.#dpr;

		const clipX: number = normalizedX * 2 - 1;
		const clipY: number = normalizedY * -2 + 1;

		return [clipX, clipY];
	}

	setCameraPositionByMouseDown(event: MouseEvent): void
	{
		mat3.copy(
			this.#startInvertViewProjectionMatrix,
			this.#camera.viewProjectionMatrixInv,
		);
		this.#startCameraX = this.#camera.x;
		this.#startCameraY = this.#camera.y;
		vec2.transformMat3(
			this.#startPos,
			this.#getClipSpaceMousePosition(event),
			this.#startInvertViewProjectionMatrix,
		);
	}

	setCameraPositionByMouseMove(event: MouseEvent): void
	{
		const pos = vec2.transformMat3(
			vec2.create(),
			this.#getClipSpaceMousePosition(event),
			this.#startInvertViewProjectionMatrix,
		);

		this.#camera.x = this.#startCameraX + this.#startPos[0] - pos[0];
		this.#camera.y = this.#startCameraY + this.#startPos[1] - pos[1];
	}

	destroy(): void
	{
		this.#canvasStyleInstance?.destroy();
		this.#resizeObserver.unobserve(this.#canvas);
	}
}
