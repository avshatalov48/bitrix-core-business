import { gridShaders } from './shaders';
import {
	compileShader,
	createProgram,
	createBufferFromTypedArray,
	convHex,
} from './helpers';
import type { CanvasStyleZoomStep } from '../composables';

export type GridOptions = {
	size: number,
	borderColor: string,
	backgroundColor: string,
};

export class Grid
{
	#gl: WebGLRenderingContext;
	#program: WebGLProgram | null = null;
	#positionAttributeLocation: number | null = null;

	#vertexShader: WebGLShader | null = null;
	#fragmentShader: WebGLShader | null = null;

	#projectionMatrixLink: WebGLUniformLocation | null = null;
	#viewMatrixLink: WebGLUniformLocation | null = null;
	#viewProjectionInvMatrixLink: WebGLUniformLocation | null = null;

	#backgroundColorLink: WebGLUniformLocation | null = null;
	#backgroundColor = null;

	#gridColorLink: WebGLUniformLocation | null = null;
	#gridColor = null;
	#gridColorAlpha = null;

	#gridSizeLink: WebGLUniformLocation | null = null;
	#gridSize = null;

	#zoomScaleLink: WebGLUniformLocation | null = null;

	#gridPosition: Array<number> = [
		-1, -1,
		-1, 1,
		1, -1,
		1, 1,
	];

	#gridPositionBuffer: WebGLBuffer | null = null;

	#zoomStepLink: WebGLUniformLocation | null = null;
	#zoomStep: nubmer = 4;
	#zoomSteps = [];

	constructor(canvas: HTMLElementCanvas, options: GridOptions)
	{
		this.#initParams(options);
		this.#initGrid(canvas);
	}

	#initGrid(canvas): void
	{
		this.#gl = canvas.getContext('webgl');
		this.#gl.getExtension('OES_standard_derivatives');
		this.#gl.viewport(0, 0, this.#gl.canvas.width, this.#gl.canvas.height);

		this.#vertexShader = compileShader(
			this.#gl,
			gridShaders.vertexShader,
			this.#gl.VERTEX_SHADER,
		);
		this.#fragmentShader = compileShader(
			this.#gl,
			gridShaders.fragmentShader,
			this.#gl.FRAGMENT_SHADER,
		);
		this.#program = createProgram(
			this.#gl,
			this.#vertexShader,
			this.#fragmentShader,
		);
		this.#positionAttributeLocation = this.#gl.getAttribLocation(
			this.#program,
			'a_Position',
		);
		this.#projectionMatrixLink = this.#gl.getUniformLocation(
			this.#program,
			'u_ProjectionMatrix',
		);
		this.#viewMatrixLink = this.#gl.getUniformLocation(
			this.#program,
			'u_ViewMatrix',
		);
		this.#viewProjectionInvMatrixLink = this.#gl.getUniformLocation(
			this.#program,
			'u_ViewProjectionInvMatrix',
		);
		this.#backgroundColorLink = this.#gl.getUniformLocation(
			this.#program,
			'u_BackgroundColor',
		);
		this.#gridColorLink = this.#gl.getUniformLocation(
			this.#program,
			'u_GridColor',
		);
		this.#gridSizeLink = this.#gl.getUniformLocation(
			this.#program,
			'u_GridSize',
		);
		this.#zoomStepLink = this.#gl.getUniformLocation(
			this.#program,
			'u_ZoomStep',
		);
		this.#zoomScaleLink = this.#gl.getUniformLocation(
			this.#program,
			'u_ZoomScale',
		);
		this.#gridPositionBuffer = createBufferFromTypedArray(
			this.#gl,
			new Float32Array(this.#gridPosition),
		);
	}

	#getPreparedZoomSteps(zoomSteps: CanvasStyleZoomStep): CanvasStyleZoomStep
	{
		return zoomSteps
			.sort((stepA, stepB) => stepB.zoomStep - stepA.zoomStep)
			.map((step) => ({
				zoomStep: this.#zoomStep,
				...step,
				size: 'size' in step
					? step.size
					: this.#gridSize,
				gridColor: 'gridColor' in step
					? convHex(step.gridColor)
					: this.#gridColor,
			}));
	}

	#initParams(options)
	{
		const {
			size,
			gridColor,
			gridColorAlpha,
			backgroundColor,
			zoomStep,
			zoomSteps,
		} = options;

		this.#gridSize = size;
		this.#zoomStep = zoomStep;
		this.#gridColor = new Float32Array(convHex(gridColor));
		this.#gridColorAlpha = gridColorAlpha;
		this.#backgroundColor = new Float32Array(convHex(backgroundColor));
		this.#zoomSteps = this.#getPreparedZoomSteps(zoomSteps);
	}

	#getParamsByZoom(zoom: number): CanvasStyleZoomStep
	{
		for (const step of this.#zoomSteps)
		{
			if (step.zoom <= zoom)
			{
				return step;
			}
		}

		return {
			gridColor: this.#gridColor,
			size: this.#gridSize,
			zoomStep: this.#zoomStep,
		};
	}

	render({
		projectionMatrix,
		viewMatrix,
		viewProjectionMatrixInv,
		zoomScale,
	}): void
	{
		this.#gl.clearColor(1, 1, 1, 1);
		this.#gl.clear(this.#gl.COLOR_BUFFER_BIT);
		this.#gl.useProgram(this.#program);

		const {
			gridColor,
			zoomStep,
			size,
		} = this.#getParamsByZoom(zoomScale);

		this.#gl.uniformMatrix3fv(
			this.#projectionMatrixLink,
			false,
			projectionMatrix,
		);
		this.#gl.uniformMatrix3fv(
			this.#viewMatrixLink,
			false,
			viewMatrix,
		);
		this.#gl.uniformMatrix3fv(
			this.#viewProjectionInvMatrixLink,
			false,
			viewProjectionMatrixInv,
		);
		this.#gl.uniform4f(
			this.#backgroundColorLink,
			...this.#backgroundColor,
			1,
		);
		this.#gl.uniform4f(
			this.#gridColorLink,
			...gridColor,
			1,
		);
		this.#gl.uniform1f(
			this.#gridSizeLink,
			size,
		);
		this.#gl.uniform1f(
			this.#zoomStepLink,
			zoomStep,
		);
		this.#gl.uniform1f(
			this.#zoomScaleLink,
			zoomScale,
		);

		this.#gl.enableVertexAttribArray(this.#positionAttributeLocation);
		this.#gl.bindBuffer(this.#gl.ARRAY_BUFFER, this.#gridPositionBuffer);

		this.#gl.vertexAttribPointer(
			this.#positionAttributeLocation,
			2,
			this.#gl.FLOAT,
			false,
			0,
			0,
		);

		this.#gl.drawArrays(
			this.#gl.TRIANGLE_STRIP,
			0,
			4,
		);
	}

	destroy(): void
	{
		this.#gl.deleteProgram(this.#program);
		this.#gl.deleteShader(this.#vertexShader);
		this.#gl.deleteShader(this.#fragmentShader);
	}
}
