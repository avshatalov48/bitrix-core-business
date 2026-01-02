export function compileShader(
	gl: WebGLRenderingContext,
	shaderSource: string,
	shaderType: number,
): WebGLShader
{
	const shader = gl.createShader(shaderType);
	gl.shaderSource(shader, shaderSource);
	gl.compileShader(shader);

	const success = gl.getShaderParameter(shader, gl.COMPILE_STATUS);

	if (!success)
	{
		throw new Error(`Error shader compilation: ${gl.getShaderInfoLog(shader)}`);
	}

	return shader;
}

export function createProgram(
	gl: WebGLRenderingContext,
	vertexShader: WebGLShader,
	fragmentShader: WebGLShader,
): WebGLProgram
{
	const program = gl.createProgram();

	gl.attachShader(program, vertexShader);
	gl.attachShader(program, fragmentShader);
	gl.linkProgram(program);

	const success = gl.getProgramParameter(program, gl.LINK_STATUS);

	if (!success)
	{
		throw new Error(`Error initializing shader program: ${gl.getProgramInfoLog(program)}`);
	}

	return program;
}

export function createBufferFromTypedArray(
	gl: WebGLRenderingContext,
	array: ArrayBuffer,
	type: number,
	drawType: number,
): WebGLBuffer
{
	const bufferType = type || gl.ARRAY_BUFFER;
	const buffer = gl.createBuffer();
	gl.bindBuffer(bufferType, buffer);
	gl.bufferData(bufferType, array, drawType || gl.STATIC_DRAW);

	return buffer;
}

export function convHex(hex: string): Array<number>
{
	const preHex = hex.replace(/^#/, '');
	const r = parseInt(preHex.slice(0, 2), 16);
	const g = parseInt(preHex.slice(2, 4), 16);
	const b = parseInt(preHex.slice(4, 6), 16);

	return [r / 255, g / 255, b / 255];
}
