export const vertexShader: string = `
  #extension GL_OES_standard_derivatives : enable
  precision mediump float;

  attribute vec2 a_Position;
  uniform mat3 u_ViewProjectionInvMatrix;
  varying vec2 v_Position;

  vec2 project_clipspace(vec2 p) {
  	return (u_ViewProjectionInvMatrix * vec3(p, 1)).xy;
  }

  void main() {
  	v_Position = project_clipspace(a_Position);
  	gl_Position = vec4(a_Position, 0, 1);
  }
`;

export const fragmentShader: string = `
  #extension GL_OES_standard_derivatives : enable
  precision mediump float;

  uniform vec4 u_BackgroundColor;
  uniform vec4 u_GridColor;
  uniform float u_GridSize;
  uniform float u_ZoomStep;
  uniform float u_ZoomScale;
  varying vec2 v_Position;

  vec4 render_grid(vec2 coord) {
  	float alpha = 0.0;
  	float gridSize1 = u_GridSize;
  	float gridSize2 = gridSize1 / 4.0;

  	vec2 grid1 = abs(fract(coord / gridSize1 - 0.5) - 0.5) / fwidth(coord) * gridSize1 / 0.95;
  	vec2 grid2 = abs(fract(coord / gridSize2 - 0.5) - 0.5) / fwidth(coord) * gridSize2 / 0.75;
  	float v1 = 1.0 - min(min(grid1.x, grid1.y), 1.0);
  	float v2 = 1.0 - min(min(grid2.x, grid2.y), 1.0);

  	if (v1 > 0.0) {
  		alpha = clamp(v1, 0.0, 0.222);
  	} else {
  		alpha = v2 * clamp(u_ZoomScale / u_ZoomStep, 0.0, 1.0);
  	}

  	return mix(u_BackgroundColor, u_GridColor, alpha);
  }

  void main() {
  	gl_FragColor = render_grid(v_Position);
  }
`;
