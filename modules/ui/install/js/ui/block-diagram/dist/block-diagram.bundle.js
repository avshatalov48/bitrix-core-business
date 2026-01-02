/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,main_polyfill_intersectionobserver,ui_iconSet_api_vue,main_core,ui_vue3) {
	'use strict';

	function useState() {
	  return {
	    blockDiagramRef: null,
	    blockDiagramTop: 0,
	    blockDiagramLeft: 0,
	    cursorType: 'default',
	    isResizing: false,
	    isDisabled: false,
	    blocks: [],
	    connections: [],
	    portsElMap: ui_vue3.markRaw(new Map()),
	    portsRectMap: {},
	    newConnection: null,
	    movingBlock: null,
	    movingConnections: [],
	    resizingBlock: null,
	    canvasRef: null,
	    transformLayoutRef: null,
	    canvasInstance: null,
	    canvasWidth: 0,
	    canvasHeight: 0,
	    transformX: 0,
	    transformY: 0,
	    viewportX: 0,
	    viewportY: 0,
	    zoom: 1,
	    minZoom: 0.2,
	    maxZoom: 4,
	    contextMenuLayerRef: null,
	    targetContainerRef: null,
	    isOpenContextMenu: false,
	    contextMenuInstance: null,
	    positionContextMenu: {
	      top: 0,
	      left: 0
	    },
	    historyCurrentState: ui_vue3.markRaw({
	      blocks: [],
	      connections: []
	    }),
	    headSnapshot: null,
	    tailSnapshot: null,
	    currentSnapshot: null,
	    maxCountSnapshots: 20,
	    snapshotHandler: null,
	    revertHandler: null,
	    highlitedBlockIds: [],
	    animationQueue: null,
	    currentAnimationItem: null,
	    isPauseAnimation: false,
	    isStopAnimation: false
	  };
	}

	const NODE_HEADER_HEIGHT = 46;
	const NODE_CONTENT_HEADER_HEIGHT = 14;
	const STICKING_DISTANCE = 5;
	const HOOK_NAMES = {
	  CHANGED_BLOCKS: 'changedBlocks',
	  CHANGED_CONNECTIONS: 'changedConnections',
	  START_DRAG_BLOCK: 'startDragBlock',
	  MOVE_DRAG_BLOCK: 'moveDragBlock',
	  END_DRAG_BLOCK: 'endDragBlock',
	  ADD_BLOCK: 'addBlock',
	  UPDATE_BLOCK: 'updateBlock',
	  DELETE_BLOCK: 'deleteBlock',
	  CREATE_CONNECTION: 'createConnection',
	  DELETE_CONNECTION: 'deleteConnection',
	  BLOCK_TRANSITION_START: 'blockTransitionStart',
	  BLOCK_TRANSITION_END: 'blockTransitionEnd',
	  CONNECTION_TRANSITION_START: 'connectionTransitionStart',
	  CONNECTION_TRANSITION_END: 'connectionTransitionEnd',
	  DROP_NEW_BLOCK: 'dropNewBlock'
	};
	const NODE_TYPES = {
	  SIMPLE: 'simple',
	  TRIGGER: 'trigger',
	  COMPLEX: 'complex'
	};
	const BLOCK_GROUP_DEFAULT_NAME = 'default';
	const CONNECTION_GROUP_DEFAULT_NAME = 'default';
	const PORT_POSITION = {
	  TOP: 'top',
	  BOTTOM: 'bottom',
	  RIGHT: 'right',
	  LEFT: 'left'
	};
	const CONNECTION_VIEW_TYPE = {
	  BEZIER: 'bezier',
	  SMOOTHSTEP: 'smoothstep'
	};
	const NEW_CONNECTION_VIEW_TYPE = {
	  BEZIER: 'bezier',
	  LINE: 'line'
	};
	const ANIMATED_TYPES = {
	  BLOCK: 'block',
	  CONNECTION: 'connection',
	  REMOVE_BLOCK: 'remove_block',
	  REMOVE_CONNECTION: 'remove_connection'
	};
	const CURSOR_TYPES = {
	  EW_RESIZE: 'ew-resize',
	  NS_RESIZE: 'ns-resize',
	  NWSE_RESIZE: 'nwse-resize',
	  NESW_RESIZE: 'nesw-resize'
	};
	const BLOCK_INDEXES = {
	  HIGHLITED: 4,
	  MOVABLE: 3,
	  STANDING: 2,
	  RESIZABLE: 1
	};

	const DIR_ACCESSOR_X = 'x';
	const DIR_ACCESSOR_Y = 'x';
	const DIRECTIONS_BY_POSITION = {
	  [PORT_POSITION.LEFT]: {
	    x: -1,
	    y: 0
	  },
	  [PORT_POSITION.RIGHT]: {
	    x: 1,
	    y: 0
	  },
	  [PORT_POSITION.TOP]: {
	    x: 0,
	    y: -1
	  },
	  [PORT_POSITION.BOTTOM]: {
	    x: 0,
	    y: 1
	  }
	};
	function getLinePath(start, end) {
	  const [x, y] = getConnectionCenter({
	    sourceX: start.x,
	    sourceY: start.y,
	    targetX: end.x,
	    targetY: end.y
	  });
	  return {
	    path: `M ${start.x} ${start.y} L ${end.x} ${end.y}`,
	    center: {
	      x,
	      y
	    }
	  };
	}
	function getBeziePath(start, end) {
	  const midX = (start.x + end.x) / 2;
	  const [centerX, centerY] = getConnectionCenter({
	    sourceX: start.x,
	    sourceY: start.y,
	    targetX: end.x,
	    targetY: end.y
	  });
	  return {
	    path: `M ${start.x} ${start.y} C ${midX} ${start.y}, ${midX} ${end.y}, ${end.x} ${end.y}`,
	    center: {
	      x: centerX,
	      y: centerY
	    }
	  };
	}
	function transformPoint(point, transform, viewport) {
	  let transformedX = Math.round((point.x - transform.x) / transform.zoom);
	  let transformedY = Math.round((point.y - transform.y) / transform.zoom);
	  transformedX -= Math.round(viewport.left / transform.zoom);
	  transformedY -= Math.round(viewport.top / transform.zoom);
	  return {
	    x: transformedX,
	    y: transformedY
	  };
	}
	function getConnectionCenter({
	  sourceX,
	  sourceY,
	  targetX,
	  targetY
	}) {
	  const xOffset = Math.abs(targetX - sourceX) / 2;
	  const centerX = targetX < sourceX ? targetX + xOffset : targetX - xOffset;
	  const yOffset = Math.abs(targetY - sourceY) / 2;
	  const centerY = targetY < sourceY ? targetY + yOffset : targetY - yOffset;
	  return [centerX, centerY, xOffset, yOffset];
	}
	function getDirection({
	  source,
	  sourcePosition = PORT_POSITION.BOTTOM,
	  target
	}) {
	  if (sourcePosition === PORT_POSITION.LEFT || sourcePosition === PORT_POSITION.RIGHT) {
	    return source.x < target.x ? {
	      x: 1,
	      y: 0
	    } : {
	      x: -1,
	      y: 0
	    };
	  }
	  return source.y < target.y ? {
	    x: 0,
	    y: 1
	  } : {
	    x: 0,
	    y: -1
	  };
	}
	function distance(a, b) {
	  return Math.sqrt((b.x - a.x) ** 2 + (b.y - a.y) ** 2);
	}
	// eslint-disable-next-line max-lines-per-function
	function getPoints({
	  source,
	  sourcePosition = PORT_POSITION.BOTTOM,
	  target,
	  targetPosition = PORT_POSITION.TOP,
	  center,
	  offset
	}) {
	  const sourceDir = DIRECTIONS_BY_POSITION[sourcePosition];
	  const targetDir = DIRECTIONS_BY_POSITION[targetPosition];
	  const sourceGapped = {
	    x: source.x + sourceDir.x * offset,
	    y: source.y + sourceDir.y * offset
	  };
	  const targetGapped = {
	    x: target.x + targetDir.x * offset,
	    y: target.y + targetDir.y * offset
	  };
	  const dir = getDirection({
	    source: sourceGapped,
	    sourcePosition,
	    target: targetGapped
	  });
	  const dirAccessor = dir.x !== 0 ? DIR_ACCESSOR_X : DIR_ACCESSOR_Y;
	  const currDir = dir[dirAccessor];
	  let points = [];
	  let centerX = 0;
	  let centerY = 0;
	  const sourceGapOffset = {
	    x: 0,
	    y: 0
	  };
	  const targetGapOffset = {
	    x: 0,
	    y: 0
	  };
	  const [defaultCenterX, defaultCenterY, defaultOffsetX, defaultOffsetY] = getConnectionCenter({
	    sourceX: source.x,
	    sourceY: source.y,
	    targetX: target.x,
	    targetY: target.y
	  });
	  if (sourceDir[dirAccessor] * targetDir[dirAccessor] === -1) {
	    var _center$x, _center$y;
	    centerX = (_center$x = center.x) != null ? _center$x : defaultCenterX;
	    centerY = (_center$y = center.y) != null ? _center$y : defaultCenterY;
	    const verticalSplit = [{
	      x: centerX,
	      y: sourceGapped.y
	    }, {
	      x: centerX,
	      y: targetGapped.y
	    }];
	    const horizontalSplit = [{
	      x: sourceGapped.x,
	      y: centerY
	    }, {
	      x: targetGapped.x,
	      y: centerY
	    }];
	    if (sourceDir[dirAccessor] === currDir) {
	      points = dirAccessor === DIR_ACCESSOR_X ? verticalSplit : horizontalSplit;
	    } else {
	      points = dirAccessor === DIR_ACCESSOR_X ? horizontalSplit : verticalSplit;
	    }
	  } else {
	    const sourceTarget = [{
	      x: sourceGapped.x,
	      y: targetGapped.y
	    }];
	    const targetSource = [{
	      x: targetGapped.x,
	      y: sourceGapped.y
	    }];
	    if (dirAccessor === DIR_ACCESSOR_X) {
	      points = sourceDir.x === currDir ? targetSource : sourceTarget;
	    } else {
	      points = sourceDir.y === currDir ? sourceTarget : targetSource;
	    }
	    if (sourcePosition === targetPosition) {
	      const diff = Math.abs(source[dirAccessor] - target[dirAccessor]);
	      if (diff <= offset) {
	        const gapOffset = Math.min(offset - 1, offset - diff);
	        if (sourceDir[dirAccessor] === currDir) {
	          const dirSource = sourceGapped[dirAccessor] > source[dirAccessor] ? -1 : 1;
	          sourceGapOffset[dirAccessor] = dirSource * gapOffset;
	        } else {
	          const dirTarget = targetGapped[dirAccessor] > target[dirAccessor] ? -1 : 1;
	          targetGapOffset[dirAccessor] = dirTarget * gapOffset;
	        }
	      }
	    }
	    if (sourcePosition !== targetPosition) {
	      const dirAccessorOpposite = dirAccessor === DIR_ACCESSOR_X ? DIR_ACCESSOR_Y : DIR_ACCESSOR_X;
	      const isSameDir = sourceDir[dirAccessor] === targetDir[dirAccessorOpposite];
	      const sourceGtTargetOppo = sourceGapped[dirAccessorOpposite] > targetGapped[dirAccessorOpposite];
	      const sourceLtTargetOppo = sourceGapped[dirAccessorOpposite] < targetGapped[dirAccessorOpposite];
	      const isFlipSourceTarget = sourceDir[dirAccessor] === 1 && (!isSameDir && sourceGtTargetOppo || isSameDir && sourceLtTargetOppo) || sourceDir[dirAccessor] !== 1 && (!isSameDir && sourceLtTargetOppo || isSameDir && sourceGtTargetOppo);
	      if (isFlipSourceTarget) {
	        points = dirAccessor === DIR_ACCESSOR_X ? sourceTarget : targetSource;
	      }
	    }
	    const sourceGapPoint = {
	      x: sourceGapped.x + sourceGapOffset.x,
	      y: sourceGapped.y + sourceGapOffset.y
	    };
	    const targetGapPoint = {
	      x: targetGapped.x + targetGapOffset.x,
	      y: targetGapped.y + targetGapOffset.y
	    };
	    const maxXDistance = Math.max(Math.abs(sourceGapPoint.x - points[0].x), Math.abs(targetGapPoint.x - points[0].x));
	    const maxYDistance = Math.max(Math.abs(sourceGapPoint.y - points[0].y), Math.abs(targetGapPoint.y - points[0].y));
	    if (maxXDistance >= maxYDistance) {
	      centerX = (sourceGapPoint.x + targetGapPoint.x) / 2;
	      centerY = points[0].y;
	    } else {
	      centerX = points[0].x;
	      centerY = (sourceGapPoint.y + targetGapPoint.y) / 2;
	    }
	  }
	  const pathPoints = [source, {
	    x: sourceGapped.x + sourceGapOffset.x,
	    y: sourceGapped.y + sourceGapOffset.y
	  }, ...points, {
	    x: targetGapped.x + targetGapOffset.x,
	    y: targetGapped.y + targetGapOffset.y
	  }, target];
	  return {
	    points: pathPoints,
	    offsetX: defaultOffsetX,
	    offsetY: defaultOffsetY,
	    centerX,
	    centerY
	  };
	}
	function getBend(a, b, c, size) {
	  const bendSize = Math.min(distance(a, b) / 2, distance(b, c) / 2, size);
	  const {
	    x,
	    y
	  } = b;
	  if (a.x === x && x === c.x || a.y === y && y === c.y) {
	    return `L${x} ${y}`;
	  }
	  if (a.y === y) {
	    const xDir = a.x < c.x ? -1 : 1;
	    const yDir = a.y < c.y ? 1 : -1;
	    return `L ${x + bendSize * xDir},${y}Q ${x},${y} ${x},${y + bendSize * yDir}`;
	  }
	  const xDir = a.x < c.x ? 1 : -1;
	  const yDir = a.y < c.y ? -1 : 1;
	  return `L ${x},${y + bendSize * yDir}Q ${x},${y} ${x + bendSize * xDir},${y}`;
	}
	function getSmoothStepPath(params) {
	  const {
	    sourceX,
	    sourceY,
	    sourcePosition = PORT_POSITION.BOTTOM,
	    targetX,
	    targetY,
	    targetPosition = PORT_POSITION.TOP,
	    borderRadius = 5,
	    centerX,
	    centerY,
	    offset = 20
	  } = params;
	  const {
	    points,
	    centerX: pointsCenterX,
	    centerY: pointsCenterY
	  } = getPoints({
	    source: {
	      x: sourceX,
	      y: sourceY
	    },
	    sourcePosition,
	    target: {
	      x: targetX,
	      y: targetY
	    },
	    targetPosition,
	    center: {
	      x: centerX,
	      y: centerY
	    },
	    offset
	  });
	  const path = points.reduce((res, p, i) => {
	    let segment = '';
	    if (i > 0 && i < points.length - 1) {
	      segment = getBend(points[i - 1], p, points[i + 1], borderRadius);
	    } else {
	      segment = `${i === 0 ? 'M' : 'L'}${p.x} ${p.y}`;
	    }
	    res += segment;
	    return res;
	  }, '');
	  return {
	    path,
	    center: {
	      x: pointsCenterX,
	      y: pointsCenterY
	    }
	  };
	}

	const ARRAY_COMMANDS = Object.freeze({
	  REPLACE: 'replace',
	  PUSH: 'push',
	  UPDATE_BY_INDEX: 'updateByIndex',
	  DELETE_BY_INDEX: 'deleteByIndex'
	});
	const commandExecMap = {
	  [ARRAY_COMMANDS.REPLACE]: ({
	    payload
	  }) => {
	    return [...payload];
	  },
	  [ARRAY_COMMANDS.PUSH]: ({
	    source,
	    payload
	  }) => {
	    const result = [...source];
	    result.push(payload);
	    return result;
	  },
	  [ARRAY_COMMANDS.UPDATE_BY_INDEX]: ({
	    source,
	    payload,
	    index
	  }) => {
	    const result = [...source];
	    result[index] = {
	      ...result[index],
	      ...payload
	    };
	    return result;
	  },
	  [ARRAY_COMMANDS.DELETE_BY_INDEX]: ({
	    source,
	    index
	  }) => {
	    const result = [...source];
	    result.splice(index, 1);
	    return result;
	  }
	};
	function command(commandType, args) {
	  return {
	    commandType,
	    ...args
	  };
	}
	function commandReplace(payload) {
	  return command(ARRAY_COMMANDS.REPLACE, {
	    payload
	  });
	}
	function commandPush(payload) {
	  return command(ARRAY_COMMANDS.PUSH, {
	    payload
	  });
	}
	function commandUpdateByIndex(index, payload) {
	  return command(ARRAY_COMMANDS.UPDATE_BY_INDEX, {
	    index,
	    payload
	  });
	}
	function commandDeleteByIndex(index) {
	  return command(ARRAY_COMMANDS.DELETE_BY_INDEX, {
	    index
	  });
	}
	function runCommand(sourceArray, commandPayload, callback) {
	  const {
	    commandType,
	    payload,
	    index
	  } = commandPayload;
	  const result = commandExecMap[commandType]({
	    source: sourceArray,
	    payload,
	    index
	  });
	  callback(result);
	}

	function createHook() {
	  const handlers = new Set();
	  const on = handler => {
	    if (main_core.Type.isFunction(handler) && !handlers.has(handler)) {
	      handlers.add(handler);
	    }
	  };
	  const off = handler => {
	    handlers.delete(handler);
	  };
	  const trigger = (...args) => {
	    for (const handler of handlers) {
	      handler(...args);
	    }
	  };
	  return {
	    on,
	    off,
	    trigger
	  };
	}

	function getGroupBlockSlotName(group) {
	  return `block:${group}`;
	}
	function getGroupConnectionSlotName(group) {
	  return `conenction:${group}`;
	}

	/**
	 * Common utilities
	 * @module glMatrix
	 */
	// Configuration Constants
	var EPSILON = 0.000001;
	var ARRAY_TYPE = typeof Float32Array !== "undefined" ? Float32Array : Array;
	var degree = Math.PI / 180;
	if (!Math.hypot) Math.hypot = function () {
	  var y = 0,
	    i = arguments.length;
	  while (i--) {
	    y += arguments[i] * arguments[i];
	  }
	  return Math.sqrt(y);
	};

	/**
	 * 3x3 Matrix
	 * @module mat3
	 */

	/**
	 * Creates a new identity mat3
	 *
	 * @returns {mat3} a new 3x3 matrix
	 */

	function create$2() {
	  var out = new ARRAY_TYPE(9);
	  if (ARRAY_TYPE != Float32Array) {
	    out[1] = 0;
	    out[2] = 0;
	    out[3] = 0;
	    out[5] = 0;
	    out[6] = 0;
	    out[7] = 0;
	  }
	  out[0] = 1;
	  out[4] = 1;
	  out[8] = 1;
	  return out;
	}
	/**
	 * Copy the values from one mat3 to another
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the source matrix
	 * @returns {mat3} out
	 */

	function copy$2(out, a) {
	  out[0] = a[0];
	  out[1] = a[1];
	  out[2] = a[2];
	  out[3] = a[3];
	  out[4] = a[4];
	  out[5] = a[5];
	  out[6] = a[6];
	  out[7] = a[7];
	  out[8] = a[8];
	  return out;
	}
	/**
	 * Set a mat3 to the identity matrix
	 *
	 * @param {mat3} out the receiving matrix
	 * @returns {mat3} out
	 */

	function identity$2(out) {
	  out[0] = 1;
	  out[1] = 0;
	  out[2] = 0;
	  out[3] = 0;
	  out[4] = 1;
	  out[5] = 0;
	  out[6] = 0;
	  out[7] = 0;
	  out[8] = 1;
	  return out;
	}
	/**
	 * Inverts a mat3
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the source matrix
	 * @returns {mat3} out
	 */

	function invert$2(out, a) {
	  var a00 = a[0],
	    a01 = a[1],
	    a02 = a[2];
	  var a10 = a[3],
	    a11 = a[4],
	    a12 = a[5];
	  var a20 = a[6],
	    a21 = a[7],
	    a22 = a[8];
	  var b01 = a22 * a11 - a12 * a21;
	  var b11 = -a22 * a10 + a12 * a20;
	  var b21 = a21 * a10 - a11 * a20; // Calculate the determinant

	  var det = a00 * b01 + a01 * b11 + a02 * b21;
	  if (!det) {
	    return null;
	  }
	  det = 1.0 / det;
	  out[0] = b01 * det;
	  out[1] = (-a22 * a01 + a02 * a21) * det;
	  out[2] = (a12 * a01 - a02 * a11) * det;
	  out[3] = b11 * det;
	  out[4] = (a22 * a00 - a02 * a20) * det;
	  out[5] = (-a12 * a00 + a02 * a10) * det;
	  out[6] = b21 * det;
	  out[7] = (-a21 * a00 + a01 * a20) * det;
	  out[8] = (a11 * a00 - a01 * a10) * det;
	  return out;
	}
	/**
	 * Multiplies two mat3's
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the first operand
	 * @param {ReadonlyMat3} b the second operand
	 * @returns {mat3} out
	 */

	function multiply$2(out, a, b) {
	  var a00 = a[0],
	    a01 = a[1],
	    a02 = a[2];
	  var a10 = a[3],
	    a11 = a[4],
	    a12 = a[5];
	  var a20 = a[6],
	    a21 = a[7],
	    a22 = a[8];
	  var b00 = b[0],
	    b01 = b[1],
	    b02 = b[2];
	  var b10 = b[3],
	    b11 = b[4],
	    b12 = b[5];
	  var b20 = b[6],
	    b21 = b[7],
	    b22 = b[8];
	  out[0] = b00 * a00 + b01 * a10 + b02 * a20;
	  out[1] = b00 * a01 + b01 * a11 + b02 * a21;
	  out[2] = b00 * a02 + b01 * a12 + b02 * a22;
	  out[3] = b10 * a00 + b11 * a10 + b12 * a20;
	  out[4] = b10 * a01 + b11 * a11 + b12 * a21;
	  out[5] = b10 * a02 + b11 * a12 + b12 * a22;
	  out[6] = b20 * a00 + b21 * a10 + b22 * a20;
	  out[7] = b20 * a01 + b21 * a11 + b22 * a21;
	  out[8] = b20 * a02 + b21 * a12 + b22 * a22;
	  return out;
	}
	/**
	 * Translate a mat3 by the given vector
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the matrix to translate
	 * @param {ReadonlyVec2} v vector to translate by
	 * @returns {mat3} out
	 */

	function translate$1(out, a, v) {
	  var a00 = a[0],
	    a01 = a[1],
	    a02 = a[2],
	    a10 = a[3],
	    a11 = a[4],
	    a12 = a[5],
	    a20 = a[6],
	    a21 = a[7],
	    a22 = a[8],
	    x = v[0],
	    y = v[1];
	  out[0] = a00;
	  out[1] = a01;
	  out[2] = a02;
	  out[3] = a10;
	  out[4] = a11;
	  out[5] = a12;
	  out[6] = x * a00 + y * a10 + a20;
	  out[7] = x * a01 + y * a11 + a21;
	  out[8] = x * a02 + y * a12 + a22;
	  return out;
	}
	/**
	 * Rotates a mat3 by the given angle
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the matrix to rotate
	 * @param {Number} rad the angle to rotate the matrix by
	 * @returns {mat3} out
	 */

	function rotate$2(out, a, rad) {
	  var a00 = a[0],
	    a01 = a[1],
	    a02 = a[2],
	    a10 = a[3],
	    a11 = a[4],
	    a12 = a[5],
	    a20 = a[6],
	    a21 = a[7],
	    a22 = a[8],
	    s = Math.sin(rad),
	    c = Math.cos(rad);
	  out[0] = c * a00 + s * a10;
	  out[1] = c * a01 + s * a11;
	  out[2] = c * a02 + s * a12;
	  out[3] = c * a10 - s * a00;
	  out[4] = c * a11 - s * a01;
	  out[5] = c * a12 - s * a02;
	  out[6] = a20;
	  out[7] = a21;
	  out[8] = a22;
	  return out;
	}
	/**
	 * Scales the mat3 by the dimensions in the given vec2
	 *
	 * @param {mat3} out the receiving matrix
	 * @param {ReadonlyMat3} a the matrix to rotate
	 * @param {ReadonlyVec2} v the vec2 to scale the matrix by
	 * @returns {mat3} out
	 **/

	function scale$2(out, a, v) {
	  var x = v[0],
	    y = v[1];
	  out[0] = x * a[0];
	  out[1] = x * a[1];
	  out[2] = x * a[2];
	  out[3] = y * a[3];
	  out[4] = y * a[4];
	  out[5] = y * a[5];
	  out[6] = a[6];
	  out[7] = a[7];
	  out[8] = a[8];
	  return out;
	}
	/**
	 * Generates a 2D projection matrix with the given bounds
	 *
	 * @param {mat3} out mat3 frustum matrix will be written into
	 * @param {number} width Width of your gl context
	 * @param {number} height Height of gl context
	 * @returns {mat3} out
	 */

	function projection(out, width, height) {
	  out[0] = 2 / width;
	  out[1] = 0;
	  out[2] = 0;
	  out[3] = 0;
	  out[4] = -2 / height;
	  out[5] = 0;
	  out[6] = -1;
	  out[7] = 1;
	  out[8] = 1;
	  return out;
	}

	/**
	 * 3 Dimensional Vector
	 * @module vec3
	 */

	/**
	 * Creates a new, empty vec3
	 *
	 * @returns {vec3} a new 3D vector
	 */

	function create$4() {
	  var out = new ARRAY_TYPE(3);
	  if (ARRAY_TYPE != Float32Array) {
	    out[0] = 0;
	    out[1] = 0;
	    out[2] = 0;
	  }
	  return out;
	}
	/**
	 * Calculates the length of a vec3
	 *
	 * @param {ReadonlyVec3} a vector to calculate length of
	 * @returns {Number} length of a
	 */

	function length(a) {
	  var x = a[0];
	  var y = a[1];
	  var z = a[2];
	  return Math.hypot(x, y, z);
	}
	/**
	 * Creates a new vec3 initialized with the given values
	 *
	 * @param {Number} x X component
	 * @param {Number} y Y component
	 * @param {Number} z Z component
	 * @returns {vec3} a new 3D vector
	 */

	function fromValues$4(x, y, z) {
	  var out = new ARRAY_TYPE(3);
	  out[0] = x;
	  out[1] = y;
	  out[2] = z;
	  return out;
	}
	/**
	 * Normalize a vec3
	 *
	 * @param {vec3} out the receiving vector
	 * @param {ReadonlyVec3} a vector to normalize
	 * @returns {vec3} out
	 */

	function normalize(out, a) {
	  var x = a[0];
	  var y = a[1];
	  var z = a[2];
	  var len = x * x + y * y + z * z;
	  if (len > 0) {
	    //TODO: evaluate use of glm_invsqrt here?
	    len = 1 / Math.sqrt(len);
	  }
	  out[0] = a[0] * len;
	  out[1] = a[1] * len;
	  out[2] = a[2] * len;
	  return out;
	}
	/**
	 * Calculates the dot product of two vec3's
	 *
	 * @param {ReadonlyVec3} a the first operand
	 * @param {ReadonlyVec3} b the second operand
	 * @returns {Number} dot product of a and b
	 */

	function dot(a, b) {
	  return a[0] * b[0] + a[1] * b[1] + a[2] * b[2];
	}
	/**
	 * Computes the cross product of two vec3's
	 *
	 * @param {vec3} out the receiving vector
	 * @param {ReadonlyVec3} a the first operand
	 * @param {ReadonlyVec3} b the second operand
	 * @returns {vec3} out
	 */

	function cross(out, a, b) {
	  var ax = a[0],
	    ay = a[1],
	    az = a[2];
	  var bx = b[0],
	    by = b[1],
	    bz = b[2];
	  out[0] = ay * bz - az * by;
	  out[1] = az * bx - ax * bz;
	  out[2] = ax * by - ay * bx;
	  return out;
	}
	/**
	 * Alias for {@link vec3.length}
	 * @function
	 */

	var len = length;
	/**
	 * Perform some operation over an array of vec3s.
	 *
	 * @param {Array} a the array of vectors to iterate over
	 * @param {Number} stride Number of elements between the start of each vec3. If 0 assumes tightly packed
	 * @param {Number} offset Number of elements to skip at the beginning of the array
	 * @param {Number} count Number of vec3s to iterate over. If 0 iterates over entire array
	 * @param {Function} fn Function to call for each vector in the array
	 * @param {Object} [arg] additional argument to pass to fn
	 * @returns {Array} a
	 * @function
	 */

	var forEach = function () {
	  var vec = create$4();
	  return function (a, stride, offset, count, fn, arg) {
	    var i, l;
	    if (!stride) {
	      stride = 3;
	    }
	    if (!offset) {
	      offset = 0;
	    }
	    if (count) {
	      l = Math.min(count * stride + offset, a.length);
	    } else {
	      l = a.length;
	    }
	    for (i = offset; i < l; i += stride) {
	      vec[0] = a[i];
	      vec[1] = a[i + 1];
	      vec[2] = a[i + 2];
	      fn(vec, vec, arg);
	      a[i] = vec[0];
	      a[i + 1] = vec[1];
	      a[i + 2] = vec[2];
	    }
	    return a;
	  };
	}();

	/**
	 * 4 Dimensional Vector
	 * @module vec4
	 */

	/**
	 * Creates a new, empty vec4
	 *
	 * @returns {vec4} a new 4D vector
	 */

	function create$5() {
	  var out = new ARRAY_TYPE(4);
	  if (ARRAY_TYPE != Float32Array) {
	    out[0] = 0;
	    out[1] = 0;
	    out[2] = 0;
	    out[3] = 0;
	  }
	  return out;
	}
	/**
	 * Normalize a vec4
	 *
	 * @param {vec4} out the receiving vector
	 * @param {ReadonlyVec4} a vector to normalize
	 * @returns {vec4} out
	 */

	function normalize$1(out, a) {
	  var x = a[0];
	  var y = a[1];
	  var z = a[2];
	  var w = a[3];
	  var len = x * x + y * y + z * z + w * w;
	  if (len > 0) {
	    len = 1 / Math.sqrt(len);
	  }
	  out[0] = x * len;
	  out[1] = y * len;
	  out[2] = z * len;
	  out[3] = w * len;
	  return out;
	}
	/**
	 * Perform some operation over an array of vec4s.
	 *
	 * @param {Array} a the array of vectors to iterate over
	 * @param {Number} stride Number of elements between the start of each vec4. If 0 assumes tightly packed
	 * @param {Number} offset Number of elements to skip at the beginning of the array
	 * @param {Number} count Number of vec4s to iterate over. If 0 iterates over entire array
	 * @param {Function} fn Function to call for each vector in the array
	 * @param {Object} [arg] additional argument to pass to fn
	 * @returns {Array} a
	 * @function
	 */

	var forEach$1 = function () {
	  var vec = create$5();
	  return function (a, stride, offset, count, fn, arg) {
	    var i, l;
	    if (!stride) {
	      stride = 4;
	    }
	    if (!offset) {
	      offset = 0;
	    }
	    if (count) {
	      l = Math.min(count * stride + offset, a.length);
	    } else {
	      l = a.length;
	    }
	    for (i = offset; i < l; i += stride) {
	      vec[0] = a[i];
	      vec[1] = a[i + 1];
	      vec[2] = a[i + 2];
	      vec[3] = a[i + 3];
	      fn(vec, vec, arg);
	      a[i] = vec[0];
	      a[i + 1] = vec[1];
	      a[i + 2] = vec[2];
	      a[i + 3] = vec[3];
	    }
	    return a;
	  };
	}();

	/**
	 * Quaternion in the format XYZW
	 * @module quat
	 */

	/**
	 * Creates a new identity quat
	 *
	 * @returns {quat} a new quaternion
	 */

	function create$6() {
	  var out = new ARRAY_TYPE(4);
	  if (ARRAY_TYPE != Float32Array) {
	    out[0] = 0;
	    out[1] = 0;
	    out[2] = 0;
	  }
	  out[3] = 1;
	  return out;
	}
	/**
	 * Sets a quat from the given angle and rotation axis,
	 * then returns it.
	 *
	 * @param {quat} out the receiving quaternion
	 * @param {ReadonlyVec3} axis the axis around which to rotate
	 * @param {Number} rad the angle in radians
	 * @returns {quat} out
	 **/

	function setAxisAngle(out, axis, rad) {
	  rad = rad * 0.5;
	  var s = Math.sin(rad);
	  out[0] = s * axis[0];
	  out[1] = s * axis[1];
	  out[2] = s * axis[2];
	  out[3] = Math.cos(rad);
	  return out;
	}
	/**
	 * Performs a spherical linear interpolation between two quat
	 *
	 * @param {quat} out the receiving quaternion
	 * @param {ReadonlyQuat} a the first operand
	 * @param {ReadonlyQuat} b the second operand
	 * @param {Number} t interpolation amount, in the range [0-1], between the two inputs
	 * @returns {quat} out
	 */

	function slerp$1(out, a, b, t) {
	  // benchmarks:
	  //    http://jsperf.com/quaternion-slerp-implementations
	  var ax = a[0],
	    ay = a[1],
	    az = a[2],
	    aw = a[3];
	  var bx = b[0],
	    by = b[1],
	    bz = b[2],
	    bw = b[3];
	  var omega, cosom, sinom, scale0, scale1; // calc cosine

	  cosom = ax * bx + ay * by + az * bz + aw * bw; // adjust signs (if necessary)

	  if (cosom < 0.0) {
	    cosom = -cosom;
	    bx = -bx;
	    by = -by;
	    bz = -bz;
	    bw = -bw;
	  } // calculate coefficients

	  if (1.0 - cosom > EPSILON) {
	    // standard case (slerp)
	    omega = Math.acos(cosom);
	    sinom = Math.sin(omega);
	    scale0 = Math.sin((1.0 - t) * omega) / sinom;
	    scale1 = Math.sin(t * omega) / sinom;
	  } else {
	    // "from" and "to" quaternions are very close
	    //  ... so we can do a linear interpolation
	    scale0 = 1.0 - t;
	    scale1 = t;
	  } // calculate final values

	  out[0] = scale0 * ax + scale1 * bx;
	  out[1] = scale0 * ay + scale1 * by;
	  out[2] = scale0 * az + scale1 * bz;
	  out[3] = scale0 * aw + scale1 * bw;
	  return out;
	}
	/**
	 * Creates a quaternion from the given 3x3 rotation matrix.
	 *
	 * NOTE: The resultant quaternion is not normalized, so you should be sure
	 * to renormalize the quaternion yourself where necessary.
	 *
	 * @param {quat} out the receiving quaternion
	 * @param {ReadonlyMat3} m rotation matrix
	 * @returns {quat} out
	 * @function
	 */

	function fromMat3(out, m) {
	  // Algorithm in Ken Shoemake's article in 1987 SIGGRAPH course notes
	  // article "Quaternion Calculus and Fast Animation".
	  var fTrace = m[0] + m[4] + m[8];
	  var fRoot;
	  if (fTrace > 0.0) {
	    // |w| > 1/2, may as well choose w > 1/2
	    fRoot = Math.sqrt(fTrace + 1.0); // 2w

	    out[3] = 0.5 * fRoot;
	    fRoot = 0.5 / fRoot; // 1/(4w)

	    out[0] = (m[5] - m[7]) * fRoot;
	    out[1] = (m[6] - m[2]) * fRoot;
	    out[2] = (m[1] - m[3]) * fRoot;
	  } else {
	    // |w| <= 1/2
	    var i = 0;
	    if (m[4] > m[0]) i = 1;
	    if (m[8] > m[i * 3 + i]) i = 2;
	    var j = (i + 1) % 3;
	    var k = (i + 2) % 3;
	    fRoot = Math.sqrt(m[i * 3 + i] - m[j * 3 + j] - m[k * 3 + k] + 1.0);
	    out[i] = 0.5 * fRoot;
	    fRoot = 0.5 / fRoot;
	    out[3] = (m[j * 3 + k] - m[k * 3 + j]) * fRoot;
	    out[j] = (m[j * 3 + i] + m[i * 3 + j]) * fRoot;
	    out[k] = (m[k * 3 + i] + m[i * 3 + k]) * fRoot;
	  }
	  return out;
	}
	/**
	 * Normalize a quat
	 *
	 * @param {quat} out the receiving quaternion
	 * @param {ReadonlyQuat} a quaternion to normalize
	 * @returns {quat} out
	 * @function
	 */

	var normalize$2 = normalize$1;
	/**
	 * Sets a quaternion to represent the shortest rotation from one
	 * vector to another.
	 *
	 * Both vectors are assumed to be unit length.
	 *
	 * @param {quat} out the receiving quaternion.
	 * @param {ReadonlyVec3} a the initial vector
	 * @param {ReadonlyVec3} b the destination vector
	 * @returns {quat} out
	 */

	var rotationTo = function () {
	  var tmpvec3 = create$4();
	  var xUnitVec3 = fromValues$4(1, 0, 0);
	  var yUnitVec3 = fromValues$4(0, 1, 0);
	  return function (out, a, b) {
	    var dot$$1 = dot(a, b);
	    if (dot$$1 < -0.999999) {
	      cross(tmpvec3, xUnitVec3, a);
	      if (len(tmpvec3) < 0.000001) cross(tmpvec3, yUnitVec3, a);
	      normalize(tmpvec3, tmpvec3);
	      setAxisAngle(out, tmpvec3, Math.PI);
	      return out;
	    } else if (dot$$1 > 0.999999) {
	      out[0] = 0;
	      out[1] = 0;
	      out[2] = 0;
	      out[3] = 1;
	      return out;
	    } else {
	      cross(tmpvec3, a, b);
	      out[0] = tmpvec3[0];
	      out[1] = tmpvec3[1];
	      out[2] = tmpvec3[2];
	      out[3] = 1 + dot$$1;
	      return normalize$2(out, out);
	    }
	  };
	}();
	/**
	 * Performs a spherical linear interpolation with two control points
	 *
	 * @param {quat} out the receiving quaternion
	 * @param {ReadonlyQuat} a the first operand
	 * @param {ReadonlyQuat} b the second operand
	 * @param {ReadonlyQuat} c the third operand
	 * @param {ReadonlyQuat} d the fourth operand
	 * @param {Number} t interpolation amount, in the range [0-1], between the two inputs
	 * @returns {quat} out
	 */

	var sqlerp = function () {
	  var temp1 = create$6();
	  var temp2 = create$6();
	  return function (out, a, b, c, d, t) {
	    slerp$1(temp1, a, d, t);
	    slerp$1(temp2, b, c, t);
	    slerp$1(out, temp1, temp2, 2 * t * (1 - t));
	    return out;
	  };
	}();
	/**
	 * Sets the specified quaternion with values corresponding to the given
	 * axes. Each axis is a vec3 and is expected to be unit length and
	 * perpendicular to all other specified axes.
	 *
	 * @param {ReadonlyVec3} view  the vector representing the viewing direction
	 * @param {ReadonlyVec3} right the vector representing the local "right" direction
	 * @param {ReadonlyVec3} up    the vector representing the local "up" direction
	 * @returns {quat} out
	 */

	var setAxes = function () {
	  var matr = create$2();
	  return function (out, view, right, up) {
	    matr[0] = right[0];
	    matr[3] = right[1];
	    matr[6] = right[2];
	    matr[1] = up[0];
	    matr[4] = up[1];
	    matr[7] = up[2];
	    matr[2] = -view[0];
	    matr[5] = -view[1];
	    matr[8] = -view[2];
	    return normalize$2(out, fromMat3(out, matr));
	  };
	}();

	/**
	 * 2 Dimensional Vector
	 * @module vec2
	 */

	/**
	 * Creates a new, empty vec2
	 *
	 * @returns {vec2} a new 2D vector
	 */

	function create$8() {
	  var out = new ARRAY_TYPE(2);
	  if (ARRAY_TYPE != Float32Array) {
	    out[0] = 0;
	    out[1] = 0;
	  }
	  return out;
	}
	/**
	 * Transforms the vec2 with a mat3
	 * 3rd vector component is implicitly '1'
	 *
	 * @param {vec2} out the receiving vector
	 * @param {ReadonlyVec2} a the vector to transform
	 * @param {ReadonlyMat3} m matrix to transform with
	 * @returns {vec2} out
	 */

	function transformMat3$1(out, a, m) {
	  var x = a[0],
	    y = a[1];
	  out[0] = m[0] * x + m[3] * y + m[6];
	  out[1] = m[1] * x + m[4] * y + m[7];
	  return out;
	}
	/**
	 * Perform some operation over an array of vec2s.
	 *
	 * @param {Array} a the array of vectors to iterate over
	 * @param {Number} stride Number of elements between the start of each vec2. If 0 assumes tightly packed
	 * @param {Number} offset Number of elements to skip at the beginning of the array
	 * @param {Number} count Number of vec2s to iterate over. If 0 iterates over entire array
	 * @param {Function} fn Function to call for each vector in the array
	 * @param {Object} [arg] additional argument to pass to fn
	 * @returns {Array} a
	 * @function
	 */

	var forEach$2 = function () {
	  var vec = create$8();
	  return function (a, stride, offset, count, fn, arg) {
	    var i, l;
	    if (!stride) {
	      stride = 2;
	    }
	    if (!offset) {
	      offset = 0;
	    }
	    if (count) {
	      l = Math.min(count * stride + offset, a.length);
	    } else {
	      l = a.length;
	    }
	    for (i = offset; i < l; i += stride) {
	      vec[0] = a[i];
	      vec[1] = a[i + 1];
	      fn(vec, vec, arg);
	      a[i] = vec[0];
	      a[i + 1] = vec[1];
	    }
	    return a;
	  };
	}();

	var _x = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("x");
	var _y = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("y");
	var _rotation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rotation");
	var _zoom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("zoom");
	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _height = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("height");
	var _matrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("matrix");
	var _projectionMatrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("projectionMatrix");
	var _viewMatrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewMatrix");
	var _viewProjectionMatrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewProjectionMatrix");
	var _viewProjectionMatrixInv = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewProjectionMatrixInv");
	var _changeCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changeCallback");
	var _onChangedTransformParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangedTransformParams");
	class Camera {
	  constructor(options) {
	    Object.defineProperty(this, _onChangedTransformParams, {
	      value: _onChangedTransformParams2
	    });
	    Object.defineProperty(this, _x, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _y, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _rotation, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _zoom, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _height, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _matrix, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _projectionMatrix, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _viewMatrix, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _viewProjectionMatrix, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _viewProjectionMatrixInv, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _changeCallback, {
	      writable: true,
	      value: null
	    });
	    const {
	      width,
	      height,
	      onChangeCallback
	    } = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _changeCallback)[_changeCallback] = onChangeCallback;
	    this.projection(width, height);
	    this.updateMatrix();
	    babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	      width,
	      height
	    });
	  }
	  get projectionMatrix() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _projectionMatrix)[_projectionMatrix];
	  }
	  get viewMatrix() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _viewMatrix)[_viewMatrix];
	  }
	  get viewProjectionMatrix() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionMatrix)[_viewProjectionMatrix];
	  }
	  get viewProjectionMatrixInv() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionMatrixInv)[_viewProjectionMatrixInv];
	  }
	  get matrix() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix];
	  }
	  get zoom() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom];
	  }
	  set zoom(zoom) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom] !== zoom) {
	      babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom] = zoom;
	      babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	        zoom
	      });
	      this.updateMatrix();
	    }
	  }
	  get x() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _x)[_x];
	  }
	  set x(x) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _x)[_x] !== x) {
	      babelHelpers.classPrivateFieldLooseBase(this, _x)[_x] = x;
	      babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	        x
	      });
	      this.updateMatrix();
	    }
	  }
	  get y() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _y)[_y];
	  }
	  set y(y) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _y)[_y] !== y) {
	      babelHelpers.classPrivateFieldLooseBase(this, _y)[_y] = y;
	      babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	        y
	      });
	      this.updateMatrix();
	    }
	  }
	  get rotation() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation];
	  }
	  set rotation(rotation) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation] !== rotation) {
	      babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation] = rotation;
	      this.updateMatrix();
	    }
	  }
	  get width() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _width)[_width];
	  }
	  get height() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _height)[_height];
	  }
	  setChangeTransformCallback(callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _changeCallback)[_changeCallback] = callback;
	  }
	  clone(width = null, height = null) {
	    const camera = new Camera({
	      width: width || babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	      height: height || babelHelpers.classPrivateFieldLooseBase(this, _height)[_height],
	      onChangeCallback: babelHelpers.classPrivateFieldLooseBase(this, _changeCallback)[_changeCallback]
	    });
	    babelHelpers.classPrivateFieldLooseBase(camera, _x)[_x] = babelHelpers.classPrivateFieldLooseBase(this, _x)[_x];
	    babelHelpers.classPrivateFieldLooseBase(camera, _y)[_y] = babelHelpers.classPrivateFieldLooseBase(this, _y)[_y];
	    babelHelpers.classPrivateFieldLooseBase(camera, _zoom)[_zoom] = babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom];
	    babelHelpers.classPrivateFieldLooseBase(camera, _rotation)[_rotation] = babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation];
	    camera.updateMatrix();
	    return camera;
	  }
	  projection(width, height) {
	    babelHelpers.classPrivateFieldLooseBase(this, _width)[_width] = width;
	    babelHelpers.classPrivateFieldLooseBase(this, _height)[_height] = height;
	    projection(babelHelpers.classPrivateFieldLooseBase(this, _projectionMatrix)[_projectionMatrix], width, height);
	    this.updateViewProjectionMatrix();
	  }
	  updateMatrix() {
	    const zoomScale = 1 / babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom];
	    identity$2(babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix]);
	    translate$1(babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], [babelHelpers.classPrivateFieldLooseBase(this, _x)[_x], babelHelpers.classPrivateFieldLooseBase(this, _y)[_y]]);
	    rotate$2(babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation]);
	    scale$2(babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix], [zoomScale, zoomScale]);
	    invert$2(babelHelpers.classPrivateFieldLooseBase(this, _viewMatrix)[_viewMatrix], babelHelpers.classPrivateFieldLooseBase(this, _matrix)[_matrix]);
	    this.updateViewProjectionMatrix();
	  }
	  updateViewProjectionMatrix() {
	    multiply$2(babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionMatrix)[_viewProjectionMatrix], babelHelpers.classPrivateFieldLooseBase(this, _projectionMatrix)[_projectionMatrix], babelHelpers.classPrivateFieldLooseBase(this, _viewMatrix)[_viewMatrix]);
	    invert$2(babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionMatrixInv)[_viewProjectionMatrixInv], babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionMatrix)[_viewProjectionMatrix]);
	  }
	  createLandmark(params = {}) {
	    return {
	      zoom: babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom],
	      x: babelHelpers.classPrivateFieldLooseBase(this, _x)[_x],
	      y: babelHelpers.classPrivateFieldLooseBase(this, _y)[_y],
	      rotation: babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation],
	      ...params
	    };
	  }
	  viewportToCanvas({
	    x,
	    y
	  }, camera) {
	    const {
	      width,
	      height,
	      viewProjectionMatrixInv
	    } = camera || this;
	    const canvas = transformMat3$1(create$8(), [x / width * 2 - 1, (1 - y / height) * 2 - 1], viewProjectionMatrixInv);
	    return {
	      x: canvas[0],
	      y: canvas[1]
	    };
	  }
	  applyLandmark(landmark) {
	    const {
	      x,
	      y,
	      zoom,
	      rotation,
	      viewportX,
	      viewportY
	    } = landmark;
	    const useFixedViewport = viewportX || viewportY;
	    let preZoomX = 0;
	    let preZoomY = 0;
	    if (useFixedViewport) {
	      const canvas = this.viewportToCanvas({
	        x: viewportX,
	        y: viewportY
	      });
	      preZoomX = canvas.x;
	      preZoomY = canvas.y;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom] = zoom;
	    babelHelpers.classPrivateFieldLooseBase(this, _rotation)[_rotation] = rotation;
	    babelHelpers.classPrivateFieldLooseBase(this, _x)[_x] = x;
	    babelHelpers.classPrivateFieldLooseBase(this, _y)[_y] = y;
	    this.updateMatrix();
	    babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	      x: babelHelpers.classPrivateFieldLooseBase(this, _x)[_x],
	      y: babelHelpers.classPrivateFieldLooseBase(this, _y)[_y],
	      zoom: babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom]
	    });
	    if (useFixedViewport) {
	      const {
	        x: postZoomX,
	        y: postZoomY
	      } = this.viewportToCanvas({
	        x: viewportX,
	        y: viewportY
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _x)[_x] += preZoomX - postZoomX;
	      babelHelpers.classPrivateFieldLooseBase(this, _y)[_y] += preZoomY - postZoomY;
	      this.updateMatrix();
	      babelHelpers.classPrivateFieldLooseBase(this, _onChangedTransformParams)[_onChangedTransformParams]({
	        x: babelHelpers.classPrivateFieldLooseBase(this, _x)[_x],
	        y: babelHelpers.classPrivateFieldLooseBase(this, _y)[_y],
	        zoom: babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom]
	      });
	    }
	  }
	}
	function _onChangedTransformParams2(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _changeCallback)[_changeCallback]({
	    x: babelHelpers.classPrivateFieldLooseBase(this, _x)[_x],
	    y: babelHelpers.classPrivateFieldLooseBase(this, _y)[_y],
	    zoom: babelHelpers.classPrivateFieldLooseBase(this, _zoom)[_zoom],
	    width: babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	    height: babelHelpers.classPrivateFieldLooseBase(this, _height)[_height],
	    ...params
	  });
	}

	var _canvas = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvas");
	var _dpr = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dpr");
	var _width$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _height$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("height");
	var _minZoom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minZoom");
	var _maxZoom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxZoom");
	var _camera = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("camera");
	var _canvasStyleInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canvasStyleInstance");
	var _startInvertViewProjectionMatrix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startInvertViewProjectionMatrix");
	var _startCameraX = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startCameraX");
	var _startCameraY = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startCameraY");
	var _startPos = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startPos");
	var _resizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resizeObserver");
	var _initCanvas = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCanvas");
	var _initCamera = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCamera");
	var _initCanvasStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCanvasStyle");
	var _initResizeObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initResizeObserver");
	var _setCameraZoom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCameraZoom");
	var _getClipSpaceMousePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getClipSpaceMousePosition");
	class Canvas {
	  constructor(options) {
	    Object.defineProperty(this, _getClipSpaceMousePosition, {
	      value: _getClipSpaceMousePosition2
	    });
	    Object.defineProperty(this, _setCameraZoom, {
	      value: _setCameraZoom2
	    });
	    Object.defineProperty(this, _initResizeObserver, {
	      value: _initResizeObserver2
	    });
	    Object.defineProperty(this, _initCanvasStyle, {
	      value: _initCanvasStyle2
	    });
	    Object.defineProperty(this, _initCamera, {
	      value: _initCamera2
	    });
	    Object.defineProperty(this, _initCanvas, {
	      value: _initCanvas2
	    });
	    Object.defineProperty(this, _canvas, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _dpr, {
	      writable: true,
	      value: window.devicePixelRatio || 1
	    });
	    Object.defineProperty(this, _width$1, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _height$1, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _minZoom, {
	      writable: true,
	      value: 0.02
	    });
	    Object.defineProperty(this, _maxZoom, {
	      writable: true,
	      value: 4
	    });
	    Object.defineProperty(this, _camera, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _canvasStyleInstance, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _startInvertViewProjectionMatrix, {
	      writable: true,
	      value: create$2()
	    });
	    Object.defineProperty(this, _startCameraX, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _startCameraY, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _startPos, {
	      writable: true,
	      value: create$8()
	    });
	    Object.defineProperty(this, _resizeObserver, {
	      writable: true,
	      value: null
	    });
	    this.transform = ui_vue3.ref({
	      x: 0,
	      y: 0,
	      zoom: 0
	    });
	    const {
	      canvas: _canvas2,
	      canvasStyle: _canvasStyle,
	      minZoom,
	      maxZoom
	    } = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _minZoom)[_minZoom] = minZoom;
	    babelHelpers.classPrivateFieldLooseBase(this, _maxZoom)[_maxZoom] = maxZoom;
	    babelHelpers.classPrivateFieldLooseBase(this, _initCanvas)[_initCanvas](_canvas2);
	    babelHelpers.classPrivateFieldLooseBase(this, _initCamera)[_initCamera]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initCanvasStyle)[_initCanvasStyle](_canvasStyle);
	    babelHelpers.classPrivateFieldLooseBase(this, _initResizeObserver)[_initResizeObserver]();
	  }
	  get viewMatrix() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2;
	    return (_babelHelpers$classPr = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera]) == null ? void 0 : _babelHelpers$classPr2.viewMatrix) != null ? _babelHelpers$classPr : [];
	  }
	  get camera() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera];
	  }
	  clientToViewport({
	    x,
	    y
	  }) {
	    const {
	      left,
	      top
	    } = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].getBoundingClientRect();
	    return {
	      x: x - left,
	      y: y - top
	    };
	  }
	  viewportToClient({
	    x,
	    y
	  }) {
	    const {
	      left,
	      top
	    } = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].getBoundingClientRect();
	    return {
	      x: x + left,
	      y: y + top
	    };
	  }
	  render() {
	    var _babelHelpers$classPr3;
	    (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _canvasStyleInstance)[_canvasStyleInstance]) == null ? void 0 : _babelHelpers$classPr3.render({
	      projectionMatrix: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].projectionMatrix,
	      viewMatrix: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].viewMatrix,
	      viewProjectionMatrixInv: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].viewProjectionMatrixInv,
	      zoomScale: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].zoom
	    });
	  }
	  setCamera(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].applyLandmark(babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].createLandmark({
	      ...params
	    }));
	  }
	  zoomIn(zoomStep) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setCameraZoom)[_setCameraZoom](zoomStep);
	  }
	  zoomOut(zoomStep) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setCameraZoom)[_setCameraZoom](zoomStep * -1);
	  }
	  setCameraZoomByWheel(event, zoomChange = 0) {
	    const newZoom = Math.max(babelHelpers.classPrivateFieldLooseBase(this, _minZoom)[_minZoom], Math.min(babelHelpers.classPrivateFieldLooseBase(this, _maxZoom)[_maxZoom], (babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].zoom + zoomChange) * 2 ** (event.deltaY * -0.01)));
	    const viewport = this.clientToViewport({
	      x: event.clientX,
	      y: event.clientY
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].applyLandmark(babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].createLandmark({
	      viewportX: viewport.x,
	      viewportY: viewport.y,
	      zoom: newZoom
	    }));
	  }
	  setCameraPositionByWheel(event) {
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].x += event.deltaX / babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].zoom;
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].y += event.deltaY / babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].zoom;
	  }
	  setCameraPositionByMouseDown(event) {
	    copy$2(babelHelpers.classPrivateFieldLooseBase(this, _startInvertViewProjectionMatrix)[_startInvertViewProjectionMatrix], babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].viewProjectionMatrixInv);
	    babelHelpers.classPrivateFieldLooseBase(this, _startCameraX)[_startCameraX] = babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].x;
	    babelHelpers.classPrivateFieldLooseBase(this, _startCameraY)[_startCameraY] = babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].y;
	    transformMat3$1(babelHelpers.classPrivateFieldLooseBase(this, _startPos)[_startPos], babelHelpers.classPrivateFieldLooseBase(this, _getClipSpaceMousePosition)[_getClipSpaceMousePosition](event), babelHelpers.classPrivateFieldLooseBase(this, _startInvertViewProjectionMatrix)[_startInvertViewProjectionMatrix]);
	  }
	  setCameraPositionByMouseMove(event) {
	    const pos = transformMat3$1(create$8(), babelHelpers.classPrivateFieldLooseBase(this, _getClipSpaceMousePosition)[_getClipSpaceMousePosition](event), babelHelpers.classPrivateFieldLooseBase(this, _startInvertViewProjectionMatrix)[_startInvertViewProjectionMatrix]);
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].x = babelHelpers.classPrivateFieldLooseBase(this, _startCameraX)[_startCameraX] + babelHelpers.classPrivateFieldLooseBase(this, _startPos)[_startPos][0] - pos[0];
	    babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].y = babelHelpers.classPrivateFieldLooseBase(this, _startCameraY)[_startCameraY] + babelHelpers.classPrivateFieldLooseBase(this, _startPos)[_startPos][1] - pos[1];
	  }
	  destroy() {
	    var _babelHelpers$classPr4;
	    (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _canvasStyleInstance)[_canvasStyleInstance]) == null ? void 0 : _babelHelpers$classPr4.destroy();
	    babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver].unobserve(babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas]);
	  }
	}
	function _initCanvas2(canvas) {
	  const {
	    width,
	    height
	  } = canvas.getBoundingClientRect();
	  babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas] = canvas;
	  babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].width = width * babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr];
	  babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].height = height * babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr];
	  babelHelpers.classPrivateFieldLooseBase(this, _width$1)[_width$1] = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].width;
	  babelHelpers.classPrivateFieldLooseBase(this, _height$1)[_height$1] = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].height;
	}
	function _initCamera2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera] = new Camera({
	    width: babelHelpers.classPrivateFieldLooseBase(this, _width$1)[_width$1] / babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr],
	    height: babelHelpers.classPrivateFieldLooseBase(this, _height$1)[_height$1] / babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr],
	    onChangeCallback: ({
	      x,
	      y,
	      zoom
	    }) => {
	      this.transform.x = x;
	      this.transform.y = y;
	      this.transform.zoom = zoom;
	    }
	  });
	}
	function _initCanvasStyle2(canvasStyle) {
	  if (canvasStyle) {
	    const StyleInstance = canvasStyle.instance;
	    babelHelpers.classPrivateFieldLooseBase(this, _canvasStyleInstance)[_canvasStyleInstance] = new StyleInstance(babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas], canvasStyle.options);
	  }
	}
	function _initResizeObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver] = new ResizeObserver(entries => {
	    for (const entry of entries) {
	      babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera] = babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].clone(entry.contentRect.width, entry.contentRect.height);
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _resizeObserver)[_resizeObserver].observe(babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas]);
	}
	function _setCameraZoom2(zoomStep) {
	  const zoom = Number((babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].zoom + zoomStep).toFixed(1));
	  if (zoom < babelHelpers.classPrivateFieldLooseBase(this, _minZoom)[_minZoom] || zoom > babelHelpers.classPrivateFieldLooseBase(this, _maxZoom)[_maxZoom]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].applyLandmark(babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].createLandmark({
	    x: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].x,
	    y: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].y,
	    viewportX: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].width / 2,
	    viewportY: babelHelpers.classPrivateFieldLooseBase(this, _camera)[_camera].height / 2,
	    zoom
	  }));
	}
	function _getClipSpaceMousePosition2(event) {
	  const {
	    left,
	    top
	  } = babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].getBoundingClientRect();
	  const cssX = event.clientX - left;
	  const cssY = event.clientY - top;
	  const normalizedX = cssX / babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].clientWidth || babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].width / babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr];
	  const normalizedY = cssY / babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].clientHeight || babelHelpers.classPrivateFieldLooseBase(this, _canvas)[_canvas].height / babelHelpers.classPrivateFieldLooseBase(this, _dpr)[_dpr];
	  const clipX = normalizedX * 2 - 1;
	  const clipY = normalizedY * -2 + 1;
	  return [clipX, clipY];
	}

	const vertexShader = `
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
	const fragmentShader = `
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

	function compileShader(gl, shaderSource, shaderType) {
	  const shader = gl.createShader(shaderType);
	  gl.shaderSource(shader, shaderSource);
	  gl.compileShader(shader);
	  const success = gl.getShaderParameter(shader, gl.COMPILE_STATUS);
	  if (!success) {
	    throw new Error(`Error shader compilation: ${gl.getShaderInfoLog(shader)}`);
	  }
	  return shader;
	}
	function createProgram(gl, vertexShader, fragmentShader) {
	  const program = gl.createProgram();
	  gl.attachShader(program, vertexShader);
	  gl.attachShader(program, fragmentShader);
	  gl.linkProgram(program);
	  const success = gl.getProgramParameter(program, gl.LINK_STATUS);
	  if (!success) {
	    throw new Error(`Error initializing shader program: ${gl.getProgramInfoLog(program)}`);
	  }
	  return program;
	}
	function createBufferFromTypedArray(gl, array, type, drawType) {
	  const bufferType = type || gl.ARRAY_BUFFER;
	  const buffer = gl.createBuffer();
	  gl.bindBuffer(bufferType, buffer);
	  gl.bufferData(bufferType, array, drawType || gl.STATIC_DRAW);
	  return buffer;
	}
	function convHex(hex) {
	  const preHex = hex.replace(/^#/, '');
	  const r = parseInt(preHex.slice(0, 2), 16);
	  const g = parseInt(preHex.slice(2, 4), 16);
	  const b = parseInt(preHex.slice(4, 6), 16);
	  return [r / 255, g / 255, b / 255];
	}

	var _gl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gl");
	var _program = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("program");
	var _positionAttributeLocation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("positionAttributeLocation");
	var _vertexShader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vertexShader");
	var _fragmentShader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fragmentShader");
	var _projectionMatrixLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("projectionMatrixLink");
	var _viewMatrixLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewMatrixLink");
	var _viewProjectionInvMatrixLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewProjectionInvMatrixLink");
	var _backgroundColorLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("backgroundColorLink");
	var _backgroundColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("backgroundColor");
	var _gridColorLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridColorLink");
	var _gridColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridColor");
	var _gridColorAlpha = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridColorAlpha");
	var _gridSizeLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridSizeLink");
	var _gridSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridSize");
	var _zoomScaleLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("zoomScaleLink");
	var _gridPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridPosition");
	var _gridPositionBuffer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridPositionBuffer");
	var _zoomStepLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("zoomStepLink");
	var _zoomStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("zoomStep");
	var _zoomSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("zoomSteps");
	var _initGrid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initGrid");
	var _getPreparedZoomSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPreparedZoomSteps");
	var _initParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initParams");
	var _getParamsByZoom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParamsByZoom");
	class Grid {
	  constructor(_canvas, _options) {
	    Object.defineProperty(this, _getParamsByZoom, {
	      value: _getParamsByZoom2
	    });
	    Object.defineProperty(this, _initParams, {
	      value: _initParams2
	    });
	    Object.defineProperty(this, _getPreparedZoomSteps, {
	      value: _getPreparedZoomSteps2
	    });
	    Object.defineProperty(this, _initGrid, {
	      value: _initGrid2
	    });
	    Object.defineProperty(this, _gl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _program, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _positionAttributeLocation, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _vertexShader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _fragmentShader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _projectionMatrixLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _viewMatrixLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _viewProjectionInvMatrixLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _backgroundColorLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _backgroundColor, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridColorLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridColor, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridColorAlpha, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridSizeLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridSize, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _zoomScaleLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _gridPosition, {
	      writable: true,
	      value: [-1, -1, -1, 1, 1, -1, 1, 1]
	    });
	    Object.defineProperty(this, _gridPositionBuffer, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _zoomStepLink, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _zoomStep, {
	      writable: true,
	      value: 4
	    });
	    Object.defineProperty(this, _zoomSteps, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initParams)[_initParams](_options);
	    babelHelpers.classPrivateFieldLooseBase(this, _initGrid)[_initGrid](_canvas);
	  }
	  render({
	    projectionMatrix,
	    viewMatrix,
	    viewProjectionMatrixInv,
	    zoomScale
	  }) {
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].clearColor(1, 1, 1, 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].clear(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].COLOR_BUFFER_BIT);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].useProgram(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program]);
	    const {
	      gridColor,
	      zoomStep,
	      size
	    } = babelHelpers.classPrivateFieldLooseBase(this, _getParamsByZoom)[_getParamsByZoom](zoomScale);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniformMatrix3fv(babelHelpers.classPrivateFieldLooseBase(this, _projectionMatrixLink)[_projectionMatrixLink], false, projectionMatrix);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniformMatrix3fv(babelHelpers.classPrivateFieldLooseBase(this, _viewMatrixLink)[_viewMatrixLink], false, viewMatrix);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniformMatrix3fv(babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionInvMatrixLink)[_viewProjectionInvMatrixLink], false, viewProjectionMatrixInv);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniform4f(babelHelpers.classPrivateFieldLooseBase(this, _backgroundColorLink)[_backgroundColorLink], ...babelHelpers.classPrivateFieldLooseBase(this, _backgroundColor)[_backgroundColor], 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniform4f(babelHelpers.classPrivateFieldLooseBase(this, _gridColorLink)[_gridColorLink], ...gridColor, 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniform1f(babelHelpers.classPrivateFieldLooseBase(this, _gridSizeLink)[_gridSizeLink], size);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniform1f(babelHelpers.classPrivateFieldLooseBase(this, _zoomStepLink)[_zoomStepLink], zoomStep);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].uniform1f(babelHelpers.classPrivateFieldLooseBase(this, _zoomScaleLink)[_zoomScaleLink], zoomScale);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].enableVertexAttribArray(babelHelpers.classPrivateFieldLooseBase(this, _positionAttributeLocation)[_positionAttributeLocation]);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].bindBuffer(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].ARRAY_BUFFER, babelHelpers.classPrivateFieldLooseBase(this, _gridPositionBuffer)[_gridPositionBuffer]);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].vertexAttribPointer(babelHelpers.classPrivateFieldLooseBase(this, _positionAttributeLocation)[_positionAttributeLocation], 2, babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].FLOAT, false, 0, 0);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].drawArrays(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].TRIANGLE_STRIP, 0, 4);
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].deleteProgram(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program]);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].deleteShader(babelHelpers.classPrivateFieldLooseBase(this, _vertexShader)[_vertexShader]);
	    babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].deleteShader(babelHelpers.classPrivateFieldLooseBase(this, _fragmentShader)[_fragmentShader]);
	  }
	}
	function _initGrid2(canvas) {
	  babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl] = canvas.getContext('webgl');
	  babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getExtension('OES_standard_derivatives');
	  babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].viewport(0, 0, babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].canvas.width, babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].canvas.height);
	  babelHelpers.classPrivateFieldLooseBase(this, _vertexShader)[_vertexShader] = compileShader(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl], vertexShader, babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].VERTEX_SHADER);
	  babelHelpers.classPrivateFieldLooseBase(this, _fragmentShader)[_fragmentShader] = compileShader(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl], fragmentShader, babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].FRAGMENT_SHADER);
	  babelHelpers.classPrivateFieldLooseBase(this, _program)[_program] = createProgram(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl], babelHelpers.classPrivateFieldLooseBase(this, _vertexShader)[_vertexShader], babelHelpers.classPrivateFieldLooseBase(this, _fragmentShader)[_fragmentShader]);
	  babelHelpers.classPrivateFieldLooseBase(this, _positionAttributeLocation)[_positionAttributeLocation] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getAttribLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'a_Position');
	  babelHelpers.classPrivateFieldLooseBase(this, _projectionMatrixLink)[_projectionMatrixLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_ProjectionMatrix');
	  babelHelpers.classPrivateFieldLooseBase(this, _viewMatrixLink)[_viewMatrixLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_ViewMatrix');
	  babelHelpers.classPrivateFieldLooseBase(this, _viewProjectionInvMatrixLink)[_viewProjectionInvMatrixLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_ViewProjectionInvMatrix');
	  babelHelpers.classPrivateFieldLooseBase(this, _backgroundColorLink)[_backgroundColorLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_BackgroundColor');
	  babelHelpers.classPrivateFieldLooseBase(this, _gridColorLink)[_gridColorLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_GridColor');
	  babelHelpers.classPrivateFieldLooseBase(this, _gridSizeLink)[_gridSizeLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_GridSize');
	  babelHelpers.classPrivateFieldLooseBase(this, _zoomStepLink)[_zoomStepLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_ZoomStep');
	  babelHelpers.classPrivateFieldLooseBase(this, _zoomScaleLink)[_zoomScaleLink] = babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl].getUniformLocation(babelHelpers.classPrivateFieldLooseBase(this, _program)[_program], 'u_ZoomScale');
	  babelHelpers.classPrivateFieldLooseBase(this, _gridPositionBuffer)[_gridPositionBuffer] = createBufferFromTypedArray(babelHelpers.classPrivateFieldLooseBase(this, _gl)[_gl], new Float32Array(babelHelpers.classPrivateFieldLooseBase(this, _gridPosition)[_gridPosition]));
	}
	function _getPreparedZoomSteps2(zoomSteps) {
	  return zoomSteps.sort((stepA, stepB) => stepB.zoomStep - stepA.zoomStep).map(step => ({
	    zoomStep: babelHelpers.classPrivateFieldLooseBase(this, _zoomStep)[_zoomStep],
	    ...step,
	    size: 'size' in step ? step.size : babelHelpers.classPrivateFieldLooseBase(this, _gridSize)[_gridSize],
	    gridColor: 'gridColor' in step ? convHex(step.gridColor) : babelHelpers.classPrivateFieldLooseBase(this, _gridColor)[_gridColor]
	  }));
	}
	function _initParams2(options) {
	  const {
	    size,
	    gridColor,
	    gridColorAlpha,
	    backgroundColor,
	    zoomStep,
	    zoomSteps
	  } = options;
	  babelHelpers.classPrivateFieldLooseBase(this, _gridSize)[_gridSize] = size;
	  babelHelpers.classPrivateFieldLooseBase(this, _zoomStep)[_zoomStep] = zoomStep;
	  babelHelpers.classPrivateFieldLooseBase(this, _gridColor)[_gridColor] = new Float32Array(convHex(gridColor));
	  babelHelpers.classPrivateFieldLooseBase(this, _gridColorAlpha)[_gridColorAlpha] = gridColorAlpha;
	  babelHelpers.classPrivateFieldLooseBase(this, _backgroundColor)[_backgroundColor] = new Float32Array(convHex(backgroundColor));
	  babelHelpers.classPrivateFieldLooseBase(this, _zoomSteps)[_zoomSteps] = babelHelpers.classPrivateFieldLooseBase(this, _getPreparedZoomSteps)[_getPreparedZoomSteps](zoomSteps);
	}
	function _getParamsByZoom2(zoom) {
	  for (const step of babelHelpers.classPrivateFieldLooseBase(this, _zoomSteps)[_zoomSteps]) {
	    if (step.zoom <= zoom) {
	      return step;
	    }
	  }
	  return {
	    gridColor: babelHelpers.classPrivateFieldLooseBase(this, _gridColor)[_gridColor],
	    size: babelHelpers.classPrivateFieldLooseBase(this, _gridSize)[_gridSize],
	    zoomStep: babelHelpers.classPrivateFieldLooseBase(this, _zoomStep)[_zoomStep]
	  };
	}

	// eslint-disable-next-line max-lines-per-function
	function useHistory(options = {}) {
	  const commonSnapshotHandler = newState => {
	    return ui_vue3.markRaw({
	      blocks: ui_vue3.markRaw(JSON.parse(JSON.stringify(newState.blocks))),
	      connections: ui_vue3.markRaw(JSON.parse(JSON.stringify(newState.connections)))
	    });
	  };
	  const commonRevertHandler = snapshot => {
	    hooks.changedBlocks.trigger(commandReplace(snapshot.blocks));
	    hooks.changedConnections.trigger(commandReplace(snapshot.connections));
	  };
	  const commonEmptyHistorySnapshot = {
	    blocks: [],
	    connections: []
	  };
	  const instance = useBlockDiagram();
	  const {
	    headSnapshot,
	    tailSnapshot,
	    currentSnapshot,
	    maxCountSnapshots,
	    hooks,
	    snapshotHandler,
	    revertHandler,
	    setHistoryHandlers,
	    historyCurrentState
	  } = instance;
	  const {
	    snapshotHandler: newSnapshotHandler = null,
	    revertHandler: newRevertHandler = null,
	    emptyHistorySnapshot = commonEmptyHistorySnapshot,
	    maxCount
	  } = options;
	  setHandlers({
	    newSnapshotHandler,
	    newRevertHandler
	  });
	  maxCountSnapshots.value = maxCount || ui_vue3.toValue(maxCountSnapshots);
	  const hasNext = ui_vue3.computed(() => ui_vue3.toValue(currentSnapshot) && ui_vue3.toValue(currentSnapshot).next !== null);
	  const hasPrev = ui_vue3.computed(() => ui_vue3.toValue(currentSnapshot) && ui_vue3.toValue(currentSnapshot).prev !== null);
	  function setHandlers(newHandlerOptions) {
	    var _ref, _newHandlerOptions$sn, _ref2, _newHandlerOptions$re;
	    const handlerOptions = {
	      snapshotHandler: (_ref = (_newHandlerOptions$sn = newHandlerOptions.snapshotHandler) != null ? _newHandlerOptions$sn : ui_vue3.toValue(snapshotHandler)) != null ? _ref : commonSnapshotHandler,
	      revertHandler: (_ref2 = (_newHandlerOptions$re = newHandlerOptions.revertHandler) != null ? _newHandlerOptions$re : ui_vue3.toValue(revertHandler)) != null ? _ref2 : commonRevertHandler
	    };
	    setHistoryHandlers(handlerOptions);
	  }
	  function getCountSnapshots() {
	    let count = 0;
	    let current = ui_vue3.toValue(headSnapshot);
	    while (current) {
	      current = current.next;
	      count += 1;
	    }
	    return count;
	  }
	  function makeSnapshot(options = {}) {
	    var _toValue;
	    const {
	      snapshotHandler: newSnapshotHandler = null,
	      revertHandler: newRevertHandler = null,
	      emptySnapshot: newEmptySnapshot = null
	    } = options;
	    const snapshotHistoryHandler = newSnapshotHandler || ui_vue3.toValue(snapshotHandler);
	    const revertHistoryHandler = newRevertHandler || ui_vue3.toValue(revertHandler);
	    const emptySnapshot = newEmptySnapshot || emptyHistorySnapshot;
	    const newSnapshot = ui_vue3.markRaw({
	      snapshot: snapshotHistoryHandler(ui_vue3.toValue(historyCurrentState)),
	      revertHandler: revertHistoryHandler,
	      emptySnapshot,
	      next: null,
	      prev: tailSnapshot.value
	    });
	    if (ui_vue3.toValue(currentSnapshot) && ((_toValue = ui_vue3.toValue(currentSnapshot)) == null ? void 0 : _toValue.next) !== null) {
	      currentSnapshot.value.next = newSnapshot;
	      newSnapshot.prev = currentSnapshot.value;
	      tailSnapshot.value.prev = null;
	      tailSnapshot.value.next = null;
	      tailSnapshot.value = newSnapshot;
	    } else if (ui_vue3.toValue(headSnapshot) === null) {
	      headSnapshot.value = newSnapshot;
	      tailSnapshot.value = newSnapshot;
	    } else {
	      tailSnapshot.value.next = newSnapshot;
	      tailSnapshot.value = newSnapshot;
	    }
	    currentSnapshot.value = newSnapshot;
	    if (getCountSnapshots() <= ui_vue3.toValue(maxCountSnapshots) + 1) {
	      return;
	    }
	    const firstSnapshot = headSnapshot.value;
	    headSnapshot.value = firstSnapshot.next;
	    headSnapshot.value.prev = null;
	    firstSnapshot.next = null;
	  }
	  async function revertState({
	    revertHandler,
	    snapshot,
	    emptySnapshot
	  }) {
	    revertHandler(emptySnapshot);
	    await ui_vue3.nextTick();
	    revertHandler(snapshot);
	  }
	  async function next() {
	    if (ui_vue3.toValue(currentSnapshot) === null || ui_vue3.toValue(currentSnapshot).next === null) {
	      return;
	    }
	    await revertState(ui_vue3.toValue(currentSnapshot).next);
	    currentSnapshot.value = ui_vue3.toValue(currentSnapshot).next;
	  }
	  async function prev() {
	    if (ui_vue3.toValue(currentSnapshot) === null || ui_vue3.toValue(currentSnapshot).prev === null) {
	      return;
	    }
	    await revertState(ui_vue3.toValue(currentSnapshot).prev);
	    currentSnapshot.value = ui_vue3.toValue(currentSnapshot).prev;
	  }
	  function clear() {
	    headSnapshot.value = null;
	    tailSnapshot.value = null;
	    currentSnapshot.value = null;
	  }
	  return {
	    hasNext,
	    hasPrev,
	    setHandlers,
	    makeSnapshot: () => ui_vue3.nextTick(() => makeSnapshot()),
	    next,
	    prev,
	    clear,
	    commonSnapshotHandler,
	    commonRevertHandler
	  };
	}

	/* eslint-disable no-param-reassign */
	// eslint-disable-next-line max-lines-per-function
	function useActions({
	  state,
	  getters,
	  hooks
	}) {
	  function setState(options) {
	    state.blocks = ui_vue3.toValue(options.blocks);
	    state.connections = ui_vue3.toValue(options.connections);
	    state.transformX = options.transform.x;
	    state.transformY = options.transfrom.y;
	    state.zoom = ui_vue3.toValue(options.zoom);
	  }
	  function updateCanvasTransform(transform) {
	    const {
	      x = 0,
	      y = 0,
	      zoom = 1,
	      viewportX = 0,
	      viewportY = 0
	    } = transform;
	    state.transformX = x;
	    state.transformY = y;
	    state.viewportX = viewportX;
	    state.viewportY = viewportY;
	    state.zoom = zoom;
	  }
	  const isExistConnection = connection => {
	    const {
	      sourceBlockId,
	      sourcePortId,
	      targetBlockId,
	      targetPortId
	    } = connection;
	    return state.connections.some(({
	      sourceBlockId: exSourceBlockId,
	      sourcePortId: exSourcePortId,
	      targetBlockId: exTargetBlockId,
	      targetPortId: exTargetPortId
	    }) => {
	      const isSource = exSourceBlockId === sourceBlockId && exSourcePortId === sourcePortId && exTargetBlockId === targetBlockId && exTargetPortId === targetPortId;
	      const isTarget = exSourceBlockId === targetBlockId && exSourcePortId === targetPortId && exTargetBlockId === sourceBlockId && exTargetPortId === sourcePortId;
	      return isSource || isTarget;
	    });
	  };
	  const addConnection = newConnection => {
	    if (!isExistConnection(newConnection)) {
	      hooks.changedConnections.trigger(commandPush({
	        id: main_core.Text.getRandom(),
	        ...newConnection
	      }));
	      hooks.createConnection.trigger(newConnection);
	    }
	  };
	  const deleteConnectionById = connectionId => {
	    const connections = state.connections.filter(connection => connection.id !== connectionId);
	    hooks.changedConnections.trigger(commandReplace(connections));
	    hooks.deleteConnection.trigger(connectionId);
	  };
	  const deleteConnectionByBlockIdAndPortId = (blockId, portId) => {
	    var _block$ports$input, _block$ports, _block$ports$output, _block$ports2;
	    const block = state.blocks.find(stateBlock => stateBlock.id === blockId);
	    if (!block) {
	      return;
	    }
	    const portIdMap = new Set([...((_block$ports$input = (_block$ports = block.ports) == null ? void 0 : _block$ports.input) != null ? _block$ports$input : []), ...((_block$ports$output = (_block$ports2 = block.ports) == null ? void 0 : _block$ports2.output) != null ? _block$ports$output : [])].map(port => port.id));
	    const newConnections = state.connections.filter(connection => {
	      const {
	        sourceBlockId,
	        sourcePortId,
	        targetBlockId,
	        targetPortId
	      } = connection;
	      const isSource = sourceBlockId === blockId && portIdMap.has(sourcePortId);
	      const isTarget = targetBlockId === blockId && portIdMap.has(targetPortId);
	      return !isSource && !isTarget;
	    });
	    hooks.changedConnections.trigger(commandReplace(newConnections));
	  };
	  const deleteBlockById = blockId => {
	    const blockIndex = state.blocks.findIndex(block => block.id === blockId);
	    if (blockIndex === -1) {
	      return;
	    }
	    deleteConnectionByBlockIdAndPortId(blockId);
	    hooks.changedBlocks.trigger(commandDeleteByIndex(blockIndex));
	    hooks.deleteBlock.trigger(blockId);
	  };
	  const addBlock = block => {
	    hooks.changedBlocks.trigger(commandPush(block));
	    hooks.addBlock.trigger(block);
	  };
	  const updateBlockPositionByIndex = (index, x, y) => {
	    state.blocks[index].position.x = x;
	    state.blocks[index].position.y = y;
	  };
	  const updateBlock = newBlock => {
	    const blockIndex = state.blocks.findIndex(block => block.id === newBlock.id);
	    if (blockIndex === -1) {
	      return;
	    }
	    hooks.changedBlocks.trigger(commandUpdateByIndex(blockIndex, newBlock));
	    hooks.updateBlock.trigger(newBlock);
	  };
	  const getPortAbsolutePosition = (block, port) => {
	    const {
	      position: {
	        x: blockX,
	        y: blockY
	      },
	      dimensions: {
	        width
	      },
	      ports: {
	        output
	      },
	      node: {
	        type: nodeType
	      }
	    } = block;
	    const {
	      position,
	      id: portId
	    } = port;
	    let portOffsetY = position * NODE_HEADER_HEIGHT / 2;
	    if (nodeType === NODE_TYPES.COMPLEX) {
	      portOffsetY += NODE_CONTENT_HEADER_HEIGHT + NODE_HEADER_HEIGHT;
	    }
	    let portX = blockX;
	    const isOutputPort = output.some(outputPort => outputPort.id === portId);
	    if (isOutputPort) {
	      portX = Number(blockX) + Number(width);
	    }
	    return {
	      x: portX,
	      y: Number(blockY) + portOffsetY
	    };
	  };
	  const findNearestPort = (clientX, clientY) => {
	    let nearest = null;
	    let nearestDistance = Infinity;
	    state.blocks.forEach(block => {
	      const allPorts = [...block.ports.input, ...block.ports.output];
	      allPorts.forEach(port => {
	        const {
	          x,
	          y
	        } = getPortAbsolutePosition(block, port);
	        const dx = clientX - x;
	        const dy = clientY - y;
	        const distance = Math.hypot(dx, dy);
	        if (distance < STICKING_DISTANCE && distance < nearestDistance) {
	          nearest = {
	            block,
	            port
	          };
	          nearestDistance = distance;
	        }
	      });
	    });
	    return nearest;
	  };
	  const transformEventToPoint = point => {
	    var _toValue$getBoundingC, _toValue;
	    let transformedX = Math.round(point.clientX / ui_vue3.toValue(state.zoom));
	    let transformedY = Math.round(point.clientY / ui_vue3.toValue(state.zoom));
	    const {
	      top,
	      left
	    } = (_toValue$getBoundingC = (_toValue = ui_vue3.toValue(state.blockDiagramRef)) == null ? void 0 : _toValue.getBoundingClientRect()) != null ? _toValue$getBoundingC : {
	      top: 0,
	      left: 0
	    };
	    transformedX -= Math.round(left / ui_vue3.toValue(state.zoom));
	    transformedY -= Math.round(top / ui_vue3.toValue(state.zoom));
	    return {
	      x: transformedX,
	      y: transformedY
	    };
	  };
	  const setMovingBlock = block => {
	    state.movingBlock = ui_vue3.toRaw({
	      ...block
	    });
	    const movingConnections = [];
	    const allPorts = [...block.ports.input, ...block.ports.output];
	    allPorts.forEach(port => {
	      const connections = state.connections.filter(connection => {
	        const {
	          targetBlockId,
	          targetPortId,
	          sourceBlockId,
	          sourcePortId
	        } = connection;
	        const isTarget = targetBlockId === block.id && targetPortId === port.id;
	        const isSource = sourceBlockId === block.id && sourcePortId === port.id;
	        return isTarget || isSource;
	      });
	      movingConnections.push(...connections);
	    });
	    state.movingConnections = movingConnections;
	  };
	  const updateMovingBlockPosition = (x, y) => {
	    state.movingBlock.position.x = x;
	    state.movingBlock.position.y = y;
	  };
	  const resetMovingBlock = () => {
	    state.movingBlock = null;
	    state.movingConnections = [];
	  };
	  const setHistoryHandlers = ({
	    snapshotHandler: newSnapshotHandler = null,
	    revertHandler: newRevertHandler = null
	  }) => {
	    state.snapshotHandler = newSnapshotHandler || state.snapshotHandler;
	    state.revertHandler = newRevertHandler || state.revertHandler;
	  };
	  const setPortOffsetByBlockId = (blockId, offsets) => {
	    var _toValue$blockId, _toValue2;
	    const ports = (_toValue$blockId = (_toValue2 = ui_vue3.toValue(state.portsRectMap)) == null ? void 0 : _toValue2[blockId]) != null ? _toValue$blockId : {};
	    Object.entries(ports).forEach(([id, portRect]) => {
	      ports[id].x = portRect.x - offsets.x;
	      ports[id].y = portRect.y - offsets.y;
	    });
	  };
	  const updatePortPosition = (blockId, portId) => {
	    var _portsElMap$get$get$g, _portsElMap$get, _portsElMap$get$get;
	    const {
	      portsElMap,
	      blockDiagramLeft,
	      blockDiagramTop,
	      zoom,
	      transformX,
	      transformY
	    } = state;
	    const hasBlock = ui_vue3.toValue(portsElMap).has(blockId);
	    const hasPort = hasBlock && ui_vue3.toValue(portsElMap).get(blockId).has(portId);
	    if (!hasBlock || !hasPort) {
	      return;
	    }
	    const {
	      x = 0,
	      y = 0,
	      width = 0,
	      height = 0
	    } = (_portsElMap$get$get$g = (_portsElMap$get = portsElMap.get(blockId)) == null ? void 0 : (_portsElMap$get$get = _portsElMap$get.get(portId)) == null ? void 0 : _portsElMap$get$get.getBoundingClientRect()) != null ? _portsElMap$get$get$g : {};
	    state.portsRectMap[blockId][portId].x = x / zoom + ui_vue3.toValue(transformX) - ui_vue3.toValue(blockDiagramLeft) / zoom;
	    state.portsRectMap[blockId][portId].y = y / zoom + ui_vue3.toValue(transformY) - ui_vue3.toValue(blockDiagramTop) / zoom;
	    state.portsRectMap[blockId][portId].width = width;
	    state.portsRectMap[blockId][portId].height = height;
	  };
	  return {
	    setState,
	    updateCanvasTransform,
	    isExistConnection,
	    addConnection,
	    deleteConnectionById,
	    addBlock,
	    updateBlockPositionByIndex,
	    updateBlock,
	    deleteBlockById,
	    getPortAbsolutePosition,
	    findNearestPort,
	    transformEventToPoint,
	    setMovingBlock,
	    updateMovingBlockPosition,
	    resetMovingBlock,
	    setHistoryHandlers,
	    setPortOffsetByBlockId,
	    updatePortPosition
	  };
	}

	function useGetters(state) {
	  const transform = ui_vue3.computed(() => ({
	    x: state.transformX,
	    y: state.transformY,
	    zoom: state.zoom,
	    viewportX: state.viewportX,
	    viewportY: state.viewportY
	  }));
	  const canvasId = ui_vue3.computed(() => {
	    var _state$canvasRef$canv, _state$canvasRef;
	    return (_state$canvasRef$canv = (_state$canvasRef = state.canvasRef) == null ? void 0 : _state$canvasRef.canvasId) != null ? _state$canvasRef$canv : null;
	  });
	  const groupedBlocks = ui_vue3.computed(() => {
	    return state.blocks.reduce((acc, block) => {
	      var _block$type;
	      const type = (_block$type = block == null ? void 0 : block.type) != null ? _block$type : BLOCK_GROUP_DEFAULT_NAME;
	      if (type in acc) {
	        acc[type] = [...acc[type], block];
	      } else {
	        acc[type] = [block];
	      }
	      return acc;
	    }, {
	      [BLOCK_GROUP_DEFAULT_NAME]: []
	    });
	  });
	  const blockGroupNames = ui_vue3.computed(() => {
	    return Object.keys(ui_vue3.toValue(groupedBlocks));
	  });
	  const groupedConnections = ui_vue3.computed(() => {
	    return state.connections.reduce((acc, connection) => {
	      var _connection$type;
	      const type = (_connection$type = connection == null ? void 0 : connection.type) != null ? _connection$type : CONNECTION_GROUP_DEFAULT_NAME;
	      if (type in acc) {
	        acc[type] = [...acc[type], connection];
	      } else {
	        acc[type] = [connection];
	      }
	      return acc;
	    }, {
	      [CONNECTION_GROUP_DEFAULT_NAME]: []
	    });
	  });
	  const connectionGroupNames = ui_vue3.computed(() => {
	    return Object.keys(ui_vue3.toValue(groupedConnections));
	  });
	  const isAnimate = ui_vue3.computed(() => {
	    return state.animationQueue !== null;
	  });
	  const isDisabledBlockDiagram = ui_vue3.computed(() => {
	    return state.isDisabled || ui_vue3.toValue(isAnimate);
	  });
	  return {
	    transform,
	    canvasId,
	    groupedBlocks,
	    blockGroupNames,
	    groupedConnections,
	    connectionGroupNames,
	    isAnimate,
	    isDisabledBlockDiagram
	  };
	}

	function useHooks() {
	  return {
	    [HOOK_NAMES.START_DRAG_BLOCK]: createHook(),
	    [HOOK_NAMES.MOVE_DRAG_BLOCK]: createHook(),
	    [HOOK_NAMES.END_DRAG_BLOCK]: createHook(),
	    [HOOK_NAMES.ADD_BLOCK]: createHook(),
	    [HOOK_NAMES.UPDATE_BLOCK]: createHook(),
	    [HOOK_NAMES.DELETE_BLOCK]: createHook(),
	    [HOOK_NAMES.CREATE_CONNECTION]: createHook(),
	    [HOOK_NAMES.DELETE_CONNECTION]: createHook(),
	    [HOOK_NAMES.CHANGED_BLOCKS]: createHook(),
	    [HOOK_NAMES.CHANGED_CONNECTIONS]: createHook(),
	    [HOOK_NAMES.BLOCK_TRANSITION_START]: createHook(),
	    [HOOK_NAMES.BLOCK_TRANSITION_END]: createHook(),
	    [HOOK_NAMES.CONNECTION_TRANSITION_START]: createHook(),
	    [HOOK_NAMES.CONNECTION_TRANSITION_END]: createHook(),
	    [HOOK_NAMES.DROP_NEW_BLOCK]: createHook()
	  };
	}

	function useBlockDiagram(options) {
	  var _getCurrentInstance, _app$config$globalPro, _app$config, _app$config$globalPro2, _app$config$globalPro3;
	  const app = (_getCurrentInstance = ui_vue3.getCurrentInstance()) == null ? void 0 : _getCurrentInstance.appContext.app;
	  const blockDiagramState = (_app$config$globalPro = app == null ? void 0 : (_app$config = app.config) == null ? void 0 : (_app$config$globalPro2 = _app$config.globalProperties) == null ? void 0 : _app$config$globalPro2.$blockDiagram) != null ? _app$config$globalPro : null;
	  if (blockDiagramState !== null) {
	    return blockDiagramState;
	  }
	  const state = useState(options);
	  const reactiveState = ui_vue3.reactive(state);
	  const getters = useGetters(reactiveState);
	  const hooks = useHooks();
	  const actions = useActions({
	    state: reactiveState,
	    getters,
	    hooks
	  });
	  if (options) {
	    actions.setState(options);
	  }
	  app.config.globalProperties.$blockDiagram = {
	    ...ui_vue3.toRefs(reactiveState),
	    ...getters,
	    ...actions,
	    hooks
	  };
	  app.config.globalProperties.$blockDiagramTestId = (id, ...args) => {
	    if (!id) {
	      throw new Error('ui.block-diagram not found test id');
	    }
	    const preparedArgs = args.reduce((acc, arg) => {
	      return `${acc}-${arg}`;
	    }, '');
	    return `${id}${preparedArgs}`;
	  };
	  return (_app$config$globalPro3 = app.config.globalProperties) == null ? void 0 : _app$config$globalPro3.$blockDiagram;
	}

	function useContextMenu(menuItems = []) {
	  const {
	    contextMenuLayerRef,
	    targetContainerRef,
	    isOpenContextMenu,
	    positionContextMenu,
	    contextMenuInstance,
	    zoom
	  } = useBlockDiagram();
	  contextMenuInstance.value = ui_vue3.toValue(contextMenuInstance) || ui_vue3.shallowRef(main_popup.MenuManager.create(getMenuOptions()));
	  const isOpen = ui_vue3.ref(false);
	  let options = {
	    items: menuItems
	  };
	  function setOptions(newOptions) {
	    options = getMenuOptions(newOptions);
	  }
	  function getItems(items = []) {
	    return items.map(item => {
	      return {
	        ...item,
	        onclick: () => {
	          if (main_core.Type.isFunction(item.onclick)) {
	            item.onclick();
	          }
	          closeMenu();
	        }
	      };
	    });
	  }
	  function getMenuOptions(additionalOptions) {
	    var _additionalOptions$it;
	    return {
	      id: 'block-diagram-context-menu',
	      bindElement: {
	        left: 0,
	        top: 0
	      },
	      minWidth: 200,
	      autoHide: true,
	      draggable: false,
	      cacheable: false,
	      targetContainer: ui_vue3.toValue(targetContainerRef),
	      ...additionalOptions,
	      items: getItems((_additionalOptions$it = additionalOptions == null ? void 0 : additionalOptions.items) != null ? _additionalOptions$it : [])
	    };
	  }
	  function setDestroyHandler(handler) {
	    var _toValue, _toValue$popupWindow;
	    (_toValue = ui_vue3.toValue(contextMenuInstance)) == null ? void 0 : (_toValue$popupWindow = _toValue.popupWindow) == null ? void 0 : _toValue$popupWindow.subscribeOnce('onDestroy', handler);
	  }
	  function showMenu(menuOptions) {
	    var _toValue2;
	    (_toValue2 = ui_vue3.toValue(contextMenuInstance)) == null ? void 0 : _toValue2.destroy();
	    contextMenuInstance.value = ui_vue3.shallowRef(main_popup.MenuManager.create(getMenuOptions(menuOptions)));
	    ui_vue3.toValue(contextMenuInstance).show();
	  }
	  function closeMenu() {
	    var _toValue3;
	    (_toValue3 = ui_vue3.toValue(contextMenuInstance)) == null ? void 0 : _toValue3.close();
	  }
	  function showContextMenu(payload) {
	    var _toValue$getBoundingC, _toValue4;
	    const {
	      clientX = 0,
	      clientY = 0
	    } = payload;
	    const {
	      left,
	      top
	    } = (_toValue$getBoundingC = (_toValue4 = ui_vue3.toValue(contextMenuLayerRef)) == null ? void 0 : _toValue4.getBoundingClientRect()) != null ? _toValue$getBoundingC : {
	      top: 0,
	      left: 0
	    };
	    positionContextMenu.value.top = (clientY - top) / ui_vue3.toValue(zoom);
	    positionContextMenu.value.left = (clientX - left) / ui_vue3.toValue(zoom);
	    showMenu(options);
	    setDestroyHandler(() => {
	      isOpen.value = false;
	    });
	    isOpen.value = true;
	    isOpenContextMenu.value = true;
	  }
	  function closeContextMenu() {
	    isOpen.value = false;
	    isOpenContextMenu.value = false;
	    closeMenu();
	  }
	  return {
	    isOpen,
	    showContextMenu,
	    closeContextMenu,
	    setOptions
	  };
	}

	// eslint-disable-next-line max-lines-per-function
	function useNewConnection(block, port) {
	  const {
	    isDisabledBlockDiagram,
	    newConnection,
	    portsRectMap,
	    blockDiagramTop,
	    blockDiagramLeft,
	    zoom,
	    transformX,
	    transformY,
	    addConnection
	  } = useBlockDiagram();
	  const isSourcePort = ui_vue3.ref(false);
	  function onMouseDownPort(event) {
	    var _toValue, _toValue$toValue$id;
	    event.stopPropagation();
	    if (ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    isSourcePort.value = true;
	    const portRect = (_toValue = ui_vue3.toValue(portsRectMap)) == null ? void 0 : (_toValue$toValue$id = _toValue[ui_vue3.toValue(block).id]) == null ? void 0 : _toValue$toValue$id[ui_vue3.toValue(port).id];
	    const startPosition = {
	      x: portRect.x + portRect.width / 2,
	      y: portRect.y + portRect.height / 2
	    };
	    newConnection.value = {
	      sourceBlockId: ui_vue3.toValue(block).id,
	      sourcePortId: ui_vue3.toValue(port).id,
	      targetBlockId: null,
	      targetPortId: null,
	      start: startPosition,
	      end: startPosition
	    };
	    main_core.Event.bind(document, 'mousemove', onMouseMove);
	    main_core.Event.bind(document, 'mouseup', onMouseUp);
	  }
	  function onMouseMove(event) {
	    if (!ui_vue3.toValue(newConnection) || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    const x = event.clientX / ui_vue3.toValue(zoom);
	    const y = event.clientY / ui_vue3.toValue(zoom);
	    newConnection.value.end = {
	      x: x + ui_vue3.toValue(transformX) - ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom),
	      y: y + ui_vue3.toValue(transformY) - ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom)
	    };
	  }
	  function onMouseUp(event) {
	    if (ui_vue3.toValue(newConnection) === null || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    const {
	      sourceBlockId = null,
	      sourcePortId = null,
	      targetBlockId = null,
	      targetPortId = null
	    } = ui_vue3.toValue(newConnection);
	    const isSamePort = sourceBlockId === targetBlockId && sourcePortId === targetPortId;
	    const hasSourceIds = sourceBlockId !== null && sourcePortId !== null;
	    const hasTargetIds = targetBlockId !== null && targetPortId !== null;
	    if (!isSamePort && hasSourceIds && hasTargetIds) {
	      addConnection({
	        sourceBlockId,
	        sourcePortId,
	        targetBlockId,
	        targetPortId
	      });
	    }
	    newConnection.value = null;
	    isSourcePort.value = false;
	    main_core.Event.unbind(document, 'mousemove', onMouseMove);
	    main_core.Event.unbind(document, 'mouseup', onMouseUp);
	  }
	  function onMouseOverPort() {
	    if (ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    if (ui_vue3.toValue(newConnection) !== null) {
	      newConnection.value.targetBlockId = ui_vue3.toValue(block).id;
	      newConnection.value.targetPortId = ui_vue3.toValue(port).id;
	    }
	  }
	  function onMouseLeavePort() {
	    if (ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    if (ui_vue3.toValue(newConnection) !== null) {
	      newConnection.value.targetBlockId = null;
	      newConnection.value.targetPortId = null;
	    }
	  }
	  return {
	    isSourcePort,
	    onMouseDownPort,
	    onMouseOverPort,
	    onMouseLeavePort
	  };
	}

	function useBlockState(block) {
	  const {
	    highlitedBlockIds,
	    isDisabledBlockDiagram,
	    movingBlock,
	    updatePortPosition
	  } = useBlockDiagram();
	  const isHiglitedBlock = ui_vue3.computed(() => {
	    return highlitedBlockIds.value.includes(ui_vue3.toValue(block).id);
	  });
	  const isDisabled = ui_vue3.computed(() => {
	    return ui_vue3.toValue(isDisabledBlockDiagram);
	  });
	  const blockZindex = ui_vue3.computed(() => {
	    var _toValue;
	    if (((_toValue = ui_vue3.toValue(movingBlock)) == null ? void 0 : _toValue.id) === ui_vue3.toValue(block).id) {
	      return {
	        zIndex: BLOCK_INDEXES.MOVABLE
	      };
	    }
	    if (ui_vue3.toValue(isHiglitedBlock)) {
	      return {
	        zIndex: BLOCK_INDEXES.HIGHLITED
	      };
	    }
	    return {
	      zIndex: BLOCK_INDEXES.STANDING
	    };
	  });
	  function updatePortsPositions() {
	    [...ui_vue3.toValue(block).ports.input, ...ui_vue3.toValue(block).ports.output].forEach(port => {
	      updatePortPosition(ui_vue3.toValue(block).id, port.id);
	    });
	  }
	  return {
	    isHiglitedBlock,
	    isDisabled,
	    blockZindex,
	    updatePortsPositions
	  };
	}

	// eslint-disable-next-line max-lines-per-function
	function useMoveableBlock(blockRef, block) {
	  const isDragged = ui_vue3.ref(false);
	  const {
	    isDisabledBlockDiagram,
	    zoom,
	    updateBlock,
	    hooks,
	    setMovingBlock,
	    updateMovingBlockPosition,
	    resetMovingBlock,
	    setPortOffsetByBlockId
	  } = useBlockDiagram();
	  let prevValueBlockX = 0;
	  let prevValueBlockY = 0;
	  const offsetBlockX = ui_vue3.ref(0);
	  const offsetBlockY = ui_vue3.ref(0);
	  const x = ui_vue3.ref(ui_vue3.toValue(block).position.x);
	  const y = ui_vue3.ref(ui_vue3.toValue(block).position.y);
	  ui_vue3.watchEffect(() => {
	    x.value = ui_vue3.toValue(block).position.x;
	    y.value = ui_vue3.toValue(block).position.y;
	  });
	  const blockPositionStyle = ui_vue3.computed(() => {
	    return {
	      top: `${y.value}px`,
	      left: `${x.value}px`
	    };
	  });
	  ui_vue3.onMounted(() => {
	    main_core.Event.bind(ui_vue3.toValue(blockRef), 'mousedown', onMouseDown);
	  });
	  ui_vue3.onBeforeUnmount(() => {
	    main_core.Event.unbind(ui_vue3.toValue(blockRef), 'mousedown', onMouseDown);
	  });
	  const onMouseDown = event => {
	    if (event.button !== 0 || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    event.stopPropagation();
	    setMovingBlock(ui_vue3.toValue(block));
	    hooks.startDragBlock.trigger(block);
	    prevValueBlockX = ui_vue3.toValue(block).position.x;
	    prevValueBlockY = ui_vue3.toValue(block).position.y;
	    offsetBlockX.value = Math.round(event.clientX - ui_vue3.toValue(block).position.x * ui_vue3.toValue(zoom));
	    offsetBlockY.value = Math.round(event.clientY - ui_vue3.toValue(block).position.y * ui_vue3.toValue(zoom));
	    isDragged.value = true;
	    main_core.Event.bind(document, 'mousemove', onMouseMove);
	    main_core.Event.bind(document, 'mouseup', onMouseUp);
	  };
	  const onMouseMove = event => {
	    if (!ui_vue3.toValue(isDragged) || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    event.stopPropagation();
	    hooks.moveDragBlock.trigger(block);
	    x.value = Math.round((event.clientX - ui_vue3.toValue(offsetBlockX)) / ui_vue3.toValue(zoom));
	    y.value = Math.round((event.clientY - ui_vue3.toValue(offsetBlockY)) / ui_vue3.toValue(zoom));
	    updateMovingBlockPosition(x.value, y.value);
	    setPortOffsetByBlockId(ui_vue3.toValue(block).id, {
	      x: prevValueBlockX - x.value,
	      y: prevValueBlockY - y.value
	    });
	    prevValueBlockX = x.value;
	    prevValueBlockY = y.value;
	  };
	  const onMouseUp = event => {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isDragged) || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    const positionX = Math.round((event.clientX - ui_vue3.toValue(offsetBlockX)) / ui_vue3.toValue(zoom));
	    const positionY = Math.round((event.clientY - ui_vue3.toValue(offsetBlockY)) / ui_vue3.toValue(zoom));
	    const newBlock = {
	      ...ui_vue3.toValue(block),
	      position: {
	        ...ui_vue3.toValue(block).position,
	        x: positionX,
	        y: positionY
	      }
	    };
	    setPortOffsetByBlockId(ui_vue3.toValue(block).id, {
	      x: prevValueBlockX - positionX,
	      y: prevValueBlockY - positionY
	    });
	    updateBlock(newBlock);
	    if (ui_vue3.toValue(block).position.x !== newBlock.position.x || ui_vue3.toValue(block).position.y !== newBlock.position.y) {
	      hooks.endDragBlock.trigger(newBlock);
	    }
	    resetMovingBlock();
	    offsetBlockX.value = 0;
	    offsetBlockY.value = 0;
	    isDragged.value = false;
	    main_core.Event.unbind(document, 'mousemove', onMouseMove);
	    main_core.Event.unbind(document, 'mouseup', onMouseUp);
	  };
	  return {
	    isDragged,
	    blockPositionStyle
	  };
	}

	function useModelValue(emit) {
	  const {
	    blocks,
	    connections,
	    historyCurrentState,
	    hooks
	  } = useBlockDiagram();
	  const handlersMap = {
	    [HOOK_NAMES.CHANGED_BLOCKS]: handleChangeBlocks,
	    [HOOK_NAMES.CHANGED_CONNECTIONS]: handleChangeConnections
	  };
	  Object.entries(handlersMap).forEach(([hookName, handler]) => {
	    hooks[hookName].on(handler);
	  });
	  function handleChangeBlocks(command) {
	    runCommand(ui_vue3.toValue(historyCurrentState.value.blocks), command, value => {
	      historyCurrentState.value.blocks = value;
	      emit('update:blocks', value);
	    });
	  }
	  function handleChangeConnections(command) {
	    runCommand(ui_vue3.toValue(historyCurrentState.value.connections), command, value => {
	      historyCurrentState.value.connections = value;
	      emit('update:connections', value);
	    });
	  }
	  function dispose() {
	    Object.entries(handlersMap).forEach(([hookName, handler]) => {
	      hooks[hookName].off(handler);
	    });
	  }
	  return {
	    dispose
	  };
	}

	function useWatchProps(props) {
	  const {
	    blocks,
	    connections,
	    historyCurrentState,
	    zoom,
	    isDisabled
	  } = useBlockDiagram();
	  const scope = ui_vue3.effectScope(true);
	  scope.run(() => {
	    ui_vue3.watch([() => props.blocks, () => props.blocks.length], ([newBlocks]) => {
	      if (newBlocks && Array.isArray(newBlocks)) {
	        historyCurrentState.value.blocks = ui_vue3.markRaw(JSON.parse(JSON.stringify(newBlocks)));
	        blocks.value = newBlocks;
	      }
	    }, {
	      immediate: true,
	      deep: true
	    });
	    ui_vue3.watch([() => props.connections, () => props.connections.length], ([newConnections]) => {
	      historyCurrentState.value.connections = ui_vue3.markRaw(JSON.parse(JSON.stringify(newConnections)));
	      connections.value = [...newConnections];
	    }, {
	      immediate: true,
	      deep: true
	    });
	    ui_vue3.watch(() => props.zoom, newZoom => {
	      zoom.value = newZoom;
	    }, {
	      immediate: true
	    });
	    ui_vue3.watch(() => props.minZoom, newMinZoom => {
	      zoom.value = newMinZoom;
	    }, {
	      immediate: true
	    });
	    ui_vue3.watch(() => props.maxZoom, newMaxZoom => {
	      zoom.value = newMaxZoom;
	    }, {
	      immediate: true
	    });
	    ui_vue3.watch(() => props.disabled, disabled => {
	      isDisabled.value = disabled;
	    }, {
	      immediate: true
	    });
	  });
	  function dispose() {
	    scope.stop();
	  }
	  return {
	    dispose
	  };
	}

	function useRegisterHooks(...hooksMaps) {
	  const {
	    hooks
	  } = useBlockDiagram();
	  const mergedHookMaps = [...hooksMaps].reduce((accHookMaps, hookMap) => {
	    const result = {
	      ...accHookMaps
	    };
	    Object.entries(hookMap).forEach(([hookName, handler]) => {
	      if (hookName in accHookMaps) {
	        result[hookName].push(...(main_core.Type.isFunction(handler) ? [handler] : handler));
	      } else {
	        result[hookName] = [...(main_core.Type.isFunction(handler) ? [handler] : handler)];
	      }
	    });
	    return result;
	  }, {});
	  Object.entries(mergedHookMaps).forEach(([hookName, handlers]) => {
	    handlers.forEach(handler => {
	      var _hooks$hookName;
	      hooks == null ? void 0 : (_hooks$hookName = hooks[hookName]) == null ? void 0 : _hooks$hookName.on(handler);
	    });
	  });
	  function dispose() {
	    Object.entries(mergedHookMaps).forEach(([hookName, handlers]) => {
	      handlers.forEach(handler => {
	        var _hooks$hookName2;
	        hooks == null ? void 0 : (_hooks$hookName2 = hooks[hookName]) == null ? void 0 : _hooks$hookName2.off(handler);
	      });
	    });
	  }
	  return {
	    dispose
	  };
	}

	const DEFAULT_OPTIONS = {
	  searchCallback: () => false,
	  delay: 300
	};
	function useSearchBlocks(optionParams) {
	  const {
	    blocks
	  } = useBlockDiagram();
	  const options = {
	    ...DEFAULT_OPTIONS,
	    ...optionParams
	  };
	  const seachText = ui_vue3.ref('');
	  const foundBlocks = ui_vue3.ref([]);
	  function onSearchBlocks(searchText) {
	    seachText.value = searchText;
	    foundBlocks.value = [];
	    if (ui_vue3.toValue(seachText).trim() === '') {
	      return;
	    }
	    blocks.value.forEach(block => {
	      if (options.searchCallback(block, ui_vue3.toValue(seachText))) {
	        foundBlocks.value.push(block);
	      }
	    });
	  }
	  function onClearSearch() {
	    seachText.value = '';
	    foundBlocks.value = [];
	  }
	  return {
	    seachText,
	    foundBlocks,
	    onSearchBlocks: main_core.debounce(onSearchBlocks, options.delay),
	    onClearSearch
	  };
	}

	function useCanvas() {
	  const {
	    zoom,
	    blocks,
	    canvasWidth,
	    canvasHeight,
	    blockDiagramTop,
	    blockDiagramLeft,
	    canvasInstance
	  } = useBlockDiagram();
	  function zoomIn(zoomStep) {
	    var _toValue;
	    (_toValue = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue.zoomIn(zoomStep);
	  }
	  function zoomOut(zoomStep) {
	    var _toValue2;
	    (_toValue2 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue2.zoomOut(zoomStep);
	  }
	  function setCamera(params) {
	    var _toValue3;
	    (_toValue3 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue3.setCamera(params);
	  }
	  function goToBlockById(id) {
	    const block = ui_vue3.toValue(blocks).find(block => block.id === id);
	    if (!block) {
	      return;
	    }
	    const {
	      x,
	      y
	    } = block.position;
	    const {
	      width,
	      height
	    } = block.dimensions;
	    const centerX = x + width / 2;
	    const centerY = y + height / 2;
	    setCamera({
	      x: centerX - ui_vue3.toValue(canvasWidth) / 2 / ui_vue3.toValue(zoom) - ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom),
	      y: centerY - ui_vue3.toValue(canvasHeight) / 2 / ui_vue3.toValue(zoom) - ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom)
	    });
	  }
	  return {
	    zoomIn,
	    zoomOut,
	    setCamera,
	    goToBlockById
	  };
	}

	function useHighlightedBlocks() {
	  const {
	    highlitedBlockIds
	  } = useBlockDiagram();
	  function set(blockIds) {
	    ui_vue3.toValue(highlitedBlockIds).push(...blockIds);
	  }
	  function add(blockId) {
	    ui_vue3.toValue(highlitedBlockIds).push(blockId);
	  }
	  function remove(blockId) {
	    const highlitedIndx = highlitedBlockIds.value.indexOf(blockId);
	    if (highlitedIndx > -1) {
	      highlitedBlockIds.value.splice(highlitedIndx, 1);
	    }
	  }
	  function clear() {
	    highlitedBlockIds.value = [];
	  }
	  return {
	    highlitedBlockIds,
	    set,
	    add,
	    remove,
	    clear
	  };
	}

	function useLoc() {
	  var _getCurrentInstance, _app$config$globalPro, _app$config, _app$config$globalPro2;
	  const app = (_getCurrentInstance = ui_vue3.getCurrentInstance()) == null ? void 0 : _getCurrentInstance.appContext.app;
	  const bitrix = (_app$config$globalPro = app == null ? void 0 : (_app$config = app.config) == null ? void 0 : (_app$config$globalPro2 = _app$config.globalProperties) == null ? void 0 : _app$config$globalPro2.$bitrix) != null ? _app$config$globalPro : null;
	  return {
	    getMessage: (messageId, replacements) => {
	      var _bitrix$Loc;
	      return bitrix == null ? void 0 : (_bitrix$Loc = bitrix.Loc) == null ? void 0 : _bitrix$Loc.getMessage(messageId, replacements);
	    }
	  };
	}

	// eslint-disable-next-line max-lines-per-function
	function usePortState(options) {
	  const {
	    portRef,
	    block,
	    port,
	    position = PORT_POSITION.LEFT
	  } = options;
	  const {
	    portsElMap,
	    portsRectMap,
	    zoom,
	    transformX,
	    transformY,
	    blockDiagramTop,
	    blockDiagramLeft,
	    isDisabledBlockDiagram
	  } = useBlockDiagram();
	  const isDisabled = ui_vue3.computed(() => {
	    return ui_vue3.toValue(isDisabledBlockDiagram);
	  });
	  function addPortElement(blockId, portId, portEl) {
	    if (!ui_vue3.toValue(portsElMap).has(blockId)) {
	      ui_vue3.toValue(portsElMap).set(blockId, new Map());
	    }
	    ui_vue3.toValue(portsElMap).get(blockId).set(portId, ui_vue3.toValue(portEl));
	  }
	  function deletePortElement(blockId, portId) {
	    if (!ui_vue3.toValue(portsElMap).has(blockId)) {
	      return;
	    }
	    ui_vue3.toValue(portsElMap).get(blockId).delete(portId);
	  }
	  function addPortRect(blockId, portId, portEl) {
	    var _toValue$getBoundingC, _toValue;
	    if (!(blockId in ui_vue3.toValue(portsRectMap))) {
	      ui_vue3.toValue(portsRectMap)[blockId] = {};
	    }
	    const {
	      x = 0,
	      y = 0,
	      width = 0,
	      height = 0
	    } = (_toValue$getBoundingC = (_toValue = ui_vue3.toValue(portEl)) == null ? void 0 : _toValue.getBoundingClientRect()) != null ? _toValue$getBoundingC : {};
	    ui_vue3.toValue(portsRectMap)[blockId][portId] = {
	      x: x / ui_vue3.toValue(zoom) + ui_vue3.toValue(transformX) - ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom),
	      y: y / ui_vue3.toValue(zoom) + ui_vue3.toValue(transformY) - ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom),
	      width,
	      height,
	      position
	    };
	  }
	  function deletePortRect(blockId, portId) {
	    if (!(blockId in ui_vue3.toValue(portsRectMap))) {
	      return;
	    }
	    const portsMap = ui_vue3.toValue(portsRectMap)[blockId];
	    if (Object.keys(portsMap).length === 1) {
	      delete ui_vue3.toValue(portsRectMap)[blockId];
	    } else {
	      delete ui_vue3.toValue(portsMap)[portId];
	    }
	  }
	  function onMountedPort() {
	    addPortElement(ui_vue3.toValue(block).id, ui_vue3.toValue(port).id, portRef);
	    addPortRect(ui_vue3.toValue(block).id, ui_vue3.toValue(port).id, portRef);
	  }
	  function onUnmountedPort() {
	    deletePortElement(ui_vue3.toValue(block).id, ui_vue3.toValue(port).id);
	    deletePortRect(ui_vue3.toValue(block).id, ui_vue3.toValue(port).id);
	  }
	  return {
	    isDisabled,
	    onMountedPort,
	    onUnmountedPort
	  };
	}

	// eslint-disable-next-line no-unused-vars

	// eslint-disable-next-line max-lines-per-function
	function useConnectionState(options) {
	  const {
	    portsRectMap,
	    isDisabledBlockDiagram
	  } = useBlockDiagram();
	  const {
	    connection,
	    viewType
	  } = options;
	  const connectionPortsPosition = ui_vue3.computed(() => {
	    const {
	      sourceBlockId,
	      sourcePortId,
	      targetBlockId,
	      targetPortId
	    } = ui_vue3.toValue(connection);
	    const hasSourceBlockId = (sourceBlockId in ui_vue3.toValue(portsRectMap));
	    const hasSourcePortId = hasSourceBlockId && sourcePortId in ui_vue3.toValue(portsRectMap)[sourceBlockId];
	    const hasTargetBlockId = (targetBlockId in ui_vue3.toValue(portsRectMap));
	    const hasTargetPortId = hasTargetBlockId && targetPortId in ui_vue3.toValue(portsRectMap)[targetBlockId];
	    if (!hasSourceBlockId || !hasSourcePortId || !hasTargetBlockId || !hasTargetPortId) {
	      return null;
	    }
	    const {
	      x: sourceX,
	      y: sourceY,
	      width: sourceWidth,
	      height: sourceHeight,
	      position: sourcePosition
	    } = ui_vue3.toValue(portsRectMap)[sourceBlockId][sourcePortId];
	    const {
	      x: targetX,
	      y: targetY,
	      width: targetWidth,
	      height: targetHeight,
	      position: targetPosition
	    } = ui_vue3.toValue(portsRectMap)[targetBlockId][targetPortId];
	    return {
	      sourcePort: {
	        x: sourceX + sourceWidth / 2,
	        y: sourceY + sourceHeight / 2,
	        position: sourcePosition
	      },
	      targetPort: {
	        x: targetX + targetWidth / 2,
	        y: targetY + targetHeight / 2,
	        position: targetPosition
	      }
	    };
	  });
	  const connectionPathInfo = ui_vue3.computed(() => {
	    if (ui_vue3.toValue(connectionPortsPosition) === null) {
	      return {
	        path: '',
	        center: {
	          x: 0,
	          y: 0
	        }
	      };
	    }
	    if (ui_vue3.toValue(viewType) === CONNECTION_VIEW_TYPE.BEZIER) {
	      return getBeziePath(ui_vue3.toValue(connectionPortsPosition).sourcePort, ui_vue3.toValue(connectionPortsPosition).targetPort);
	    }
	    return getSmoothStepPath({
	      sourceX: ui_vue3.toValue(connectionPortsPosition).sourcePort.x,
	      sourceY: ui_vue3.toValue(connectionPortsPosition).sourcePort.y,
	      sourcePosition: ui_vue3.toValue(connectionPortsPosition).sourcePort.position,
	      targetX: ui_vue3.toValue(connectionPortsPosition).targetPort.x,
	      targetY: ui_vue3.toValue(connectionPortsPosition).targetPort.y,
	      targetPosition: ui_vue3.toValue(connectionPortsPosition).targetPort.position,
	      borderRadius: 10,
	      offset: 30
	    });
	  });
	  const isDisabled = ui_vue3.computed(() => {
	    return ui_vue3.toValue(isDisabledBlockDiagram);
	  });
	  return {
	    connectionPortsPosition,
	    connectionPathInfo,
	    isDisabled
	  };
	}

	function useInitAppElements(options) {
	  const {
	    blockDiagramRef: newBlockDiagramRef
	  } = options;
	  const {
	    blockDiagramRef,
	    blockDiagramTop,
	    blockDiagramLeft
	  } = useBlockDiagram();
	  let observer = null;
	  function handleInterObserver(entries) {
	    entries.forEach(entry => {
	      const {
	        top,
	        left
	      } = entry.boundingClientRect;
	      blockDiagramTop.value = top;
	      blockDiagramLeft.value = left;
	    });
	  }
	  function onMountedAppElements() {
	    blockDiagramRef.value = ui_vue3.toValue(newBlockDiagramRef);
	    const {
	      left,
	      top
	    } = ui_vue3.toValue(newBlockDiagramRef).getBoundingClientRect();
	    blockDiagramTop.value = top;
	    blockDiagramLeft.value = left;
	    observer = new IntersectionObserver(handleInterObserver);
	    observer.observe(ui_vue3.toValue(blockDiagramRef));
	  }
	  function onUnmountedAppElements() {
	    observer.unobserve(ui_vue3.toValue(blockDiagramRef));
	  }
	  return {
	    onMountedAppElements,
	    onUnmountedAppElements
	  };
	}

	function useNewConnectionState(options) {
	  const {
	    newConnection
	  } = useBlockDiagram();
	  const {
	    viewType
	  } = options;
	  const hasNewConnection = ui_vue3.computed(() => {
	    return ui_vue3.toValue(newConnection) !== null;
	  });
	  const newConnectionPathInfo = ui_vue3.computed(() => {
	    if (!ui_vue3.toValue(hasNewConnection)) {
	      return {
	        path: '',
	        center: {
	          x: 0,
	          y: 0
	        }
	      };
	    }
	    if (ui_vue3.toValue(viewType) === CONNECTION_VIEW_TYPE.BEZIER) {
	      return getBeziePath(ui_vue3.toValue(newConnection).start, ui_vue3.toValue(newConnection).end);
	    }
	    return getLinePath(ui_vue3.toValue(newConnection).start, ui_vue3.toValue(newConnection).end);
	  });
	  return {
	    hasNewConnection,
	    newConnectionPathInfo
	  };
	}

	function useAnimationQueue() {
	  const {
	    zoom,
	    isPauseAnimation,
	    isStopAnimation,
	    animationQueue,
	    currentAnimationItem,
	    addConnection,
	    deleteConnectionById,
	    addBlock,
	    deleteBlockById
	  } = useBlockDiagram();
	  function animateItem(animatedItem) {
	    switch (animatedItem.type) {
	      case ANIMATED_TYPES.BLOCK:
	        {
	          addBlock(animatedItem.item);
	          break;
	        }
	      case ANIMATED_TYPES.CONNECTION:
	        {
	          addConnection(animatedItem.item);
	          break;
	        }
	      case ANIMATED_TYPES.REMOVE_BLOCK:
	        {
	          deleteBlockById(animatedItem.item.id);
	          break;
	        }
	      case ANIMATED_TYPES.REMOVE_CONNECTION:
	        {
	          deleteConnectionById(animatedItem.item.id);
	          break;
	        }
	      default:
	        break;
	    }
	  }
	  function* animationQueueFn(animatedQueueItems) {
	    for (const animatedItem of animatedQueueItems) {
	      if (ui_vue3.toValue(isPauseAnimation)) {
	        yield;
	      }
	      if (ui_vue3.toValue(isStopAnimation)) {
	        break;
	      }
	      currentAnimationItem.value = animatedItem;
	      if (animatedItem.type && animatedItem.item) {
	        animateItem(animatedItem);
	        yield animatedItem;
	      }
	    }
	    stop();
	  }
	  function start(options) {
	    const {
	      items: shouldAnimatedItems = []
	    } = options != null ? options : {};
	    zoom.value = 1;
	    isStopAnimation.value = false;
	    animationQueue.value = animationQueueFn(shouldAnimatedItems);
	    if (shouldAnimatedItems.length > 0) {
	      setTimeout(() => play(), 100);
	    } else {
	      stop();
	    }
	  }
	  function pause() {
	    isPauseAnimation.value = true;
	  }
	  function play() {
	    var _animationQueue$value;
	    isPauseAnimation.value = false;
	    (_animationQueue$value = animationQueue.value) == null ? void 0 : _animationQueue$value.next();
	  }
	  function stop() {
	    isStopAnimation.value = true;
	    isPauseAnimation.value = false;
	    currentAnimationItem.value = null;
	    animationQueue.value = null;
	  }
	  return {
	    start,
	    pause,
	    play,
	    stop
	  };
	}

	const CANVAS_STYLE_DEFAULT_OPTIONS = {
	  grid: {
	    options: {
	      size: 64,
	      gridColor: '#A1B8D9',
	      backgroundColor: '#ECF0F2',
	      zoomStep: 4,
	      zoomSteps: [{
	        zoom: 1.1,
	        size: 64,
	        zoomStep: 4
	      }, {
	        zoom: 1,
	        size: 64,
	        zoomStep: 4
	      }, {
	        zoom: 0.99,
	        size: 64,
	        zoomStep: 4
	      }, {
	        zoom: 0.5,
	        size: 64 * 5,
	        zoomStep: 0.5
	      }, {
	        zoom: 0.25,
	        size: 64 * 25,
	        zoomStep: 0.25
	      }, {
	        zoom: 0.125,
	        size: 64 * 125,
	        zoomStep: 0.125
	      }]
	    },
	    instance: Grid
	  }
	};

	// eslint-disable-next-line max-lines-per-function
	function useCanvasTransfrom(options) {
	  const {
	    canvasRef: newCanvasRef,
	    transformLayoutRef: newTransformLayoutRef,
	    canvasStyle: newCanvasStyle,
	    zoomSensitivity,
	    zoomSensitivityMouse
	  } = options;
	  const {
	    isDisabledBlockDiagram,
	    transformX,
	    transformY,
	    zoom,
	    minZoom,
	    maxZoom,
	    canvasRef,
	    transformLayoutRef,
	    canvasWidth,
	    canvasHeight,
	    canvasInstance
	  } = useBlockDiagram();
	  const dragOn = ui_vue3.ref(false);
	  const isDragging = ui_vue3.ref(false);
	  const zooming = ui_vue3.ref(false);
	  let requestAnimationId = null;
	  function getCanvasStyleOptions(canvasStyle) {
	    if (canvasStyle && canvasStyle.style in CANVAS_STYLE_DEFAULT_OPTIONS) {
	      return {
	        instance: CANVAS_STYLE_DEFAULT_OPTIONS[canvasStyle.style].instance,
	        options: {
	          ...CANVAS_STYLE_DEFAULT_OPTIONS[canvasStyle.style].options,
	          ...canvasStyle
	        }
	      };
	    }
	    return null;
	  }
	  function onMounted() {
	    canvasRef.value = newCanvasRef;
	    transformLayoutRef.value = newTransformLayoutRef;
	    canvasInstance.value = ui_vue3.markRaw(new Canvas({
	      canvas: ui_vue3.toValue(newCanvasRef),
	      canvasStyle: getCanvasStyleOptions(newCanvasStyle),
	      minZoom: ui_vue3.toValue(minZoom),
	      maxZoom: ui_vue3.toValue(maxZoom)
	    }));
	    ui_vue3.toValue(canvasInstance).camera.setChangeTransformCallback(payload => {
	      transformX.value = payload.x;
	      transformY.value = payload.y;
	      zoom.value = payload.zoom;
	      canvasWidth.value = payload.width;
	      canvasHeight.value = payload.height;
	    });
	    render();
	  }
	  function onUnmounted() {
	    var _toValue;
	    (_toValue = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue.destroy();
	    cancelAnimationFrame(requestAnimationId);
	  }
	  function onMouseDown(event) {
	    var _toValue2;
	    if (ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    dragOn.value = true;
	    (_toValue2 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue2.setCameraPositionByMouseDown(event);
	  }
	  function onMouseMove(event) {
	    var _toValue3;
	    if (!ui_vue3.toValue(dragOn) || ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    if (event.buttons !== 1) {
	      dragOn.value = false;
	      isDragging.value = false;
	      return;
	    }
	    isDragging.value = true;
	    (_toValue3 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue3.setCameraPositionByMouseMove(event);
	  }
	  function onMouseUp() {
	    dragOn.value = false;
	    isDragging.value = false;
	  }
	  function onWheel(event) {
	    event.preventDefault();
	    if (ui_vue3.toValue(isDisabledBlockDiagram)) {
	      return;
	    }
	    const isTrackpad = event.wheelDeltaY ? event.wheelDeltaY === -3 * event.deltaY : event.deltaMode === 0;
	    if (event.ctrlKey) {
	      var _toValue4;
	      const zoomChange = isTrackpad ? -event.deltaY * ui_vue3.toValue(zoomSensitivity) : -Math.sign(event.deltaY) * ui_vue3.toValue(zoomSensitivityMouse);
	      zooming.value = true;
	      (_toValue4 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue4.setCameraZoomByWheel(event, zoomChange);
	      setTimeout(() => {
	        zooming.value = false;
	      }, 200);
	    } else {
	      var _toValue5;
	      (_toValue5 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue5.setCameraPositionByWheel(event);
	    }
	  }
	  function render() {
	    if (canvasInstance) {
	      var _toValue6;
	      (_toValue6 = ui_vue3.toValue(canvasInstance)) == null ? void 0 : _toValue6.render();
	      const viewMatrix = ui_vue3.toValue(canvasInstance).viewMatrix;
	      main_core.Dom.style(ui_vue3.toValue(transformLayoutRef), 'transform', `
					matrix(
						${viewMatrix[0]}, 0, 0, ${viewMatrix[4]}, ${viewMatrix[6]}, ${viewMatrix[7]}
					)
				`);
	    }
	    requestAnimationId = requestAnimationFrame(render);
	  }
	  return {
	    isDragging,
	    onMounted,
	    onUnmounted,
	    onMouseDown,
	    onMouseMove,
	    onMouseUp,
	    onWheel
	  };
	}

	function useDragAndDrop() {
	  const {
	    zoom,
	    blockDiagramTop,
	    blockDiagramLeft,
	    transformX,
	    transformY,
	    addBlock,
	    hooks
	  } = useBlockDiagram();
	  function onDrop(event) {
	    event.preventDefault();
	    const dataString = event.dataTransfer.getData('text/plain');
	    const receivedData = JSON.parse(dataString);
	    const {
	      width,
	      height
	    } = receivedData.dimensions;
	    receivedData.position.x = (event.clientX - width * ui_vue3.toValue(zoom) / 2) / ui_vue3.toValue(zoom);
	    receivedData.position.y = (event.clientY - height * ui_vue3.toValue(zoom) / 2) / ui_vue3.toValue(zoom);
	    receivedData.position.x += ui_vue3.toValue(transformX);
	    receivedData.position.y += ui_vue3.toValue(transformY);
	    receivedData.position.x -= ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom);
	    receivedData.position.y -= ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom);
	    addBlock(receivedData);
	    hooks.dropNewBlock.trigger(receivedData);
	  }
	  function setBlockData(event, addedBlock) {
	    event.dataTransfer.setData('text/plain', JSON.stringify(addedBlock));
	  }
	  return {
	    setBlockData,
	    onDrop
	  };
	}

	// eslint-disable-next-line max-lines-per-function
	function useResizableBlock(options) {
	  const {
	    cursorType,
	    resizingBlock,
	    blockDiagramTop,
	    blockDiagramLeft,
	    transformX,
	    transformY,
	    zoom,
	    updateBlock
	  } = useBlockDiagram();
	  const {
	    block,
	    minWidth,
	    minHeight,
	    leftSideRef,
	    topSideRef,
	    rightSideRef,
	    bottomSideRef,
	    leftTopCornerRef,
	    rightTopCornerRef,
	    rightBottomCornerRef,
	    leftBottomCornerRef
	  } = options;
	  const isResize = ui_vue3.ref(false);
	  let prevBlockX = 0;
	  let prevBlockY = 0;
	  let prevBlockWidth = 0;
	  let prevBlockHeight = 0;
	  const sizeBlockStyle = ui_vue3.computed(() => {
	    if (ui_vue3.toValue(isResize)) {
	      return {
	        width: `${ui_vue3.toValue(resizingBlock).dimensions.width}px`,
	        height: `${ui_vue3.toValue(resizingBlock).dimensions.height}px`,
	        cursor: ui_vue3.toValue(cursorType)
	      };
	    }
	    return {
	      width: `${ui_vue3.toValue(block).dimensions.width}px`,
	      height: `${ui_vue3.toValue(block).dimensions.height}px`,
	      cursor: ui_vue3.toValue(cursorType)
	    };
	  });
	  function updateResizableBlock() {
	    updateBlock({
	      ...ui_vue3.toValue(block),
	      position: {
	        x: ui_vue3.toValue(resizingBlock).position.x,
	        y: ui_vue3.toValue(resizingBlock).position.y
	      },
	      dimensions: {
	        width: ui_vue3.toValue(resizingBlock).dimensions.width,
	        height: ui_vue3.toValue(resizingBlock).dimensions.height
	      }
	    });
	  }
	  function onMounted() {
	    main_core.Event.bind(ui_vue3.toValue(rightSideRef), 'mousedown', onMouseDownRightSide);
	    main_core.Event.bind(ui_vue3.toValue(bottomSideRef), 'mousedown', onMouseDownBottomSide);
	    main_core.Event.bind(ui_vue3.toValue(leftSideRef), 'mousedown', onMouseDownLeftSide);
	    main_core.Event.bind(ui_vue3.toValue(topSideRef), 'mousedown', onMouseDownTopSide);
	    main_core.Event.bind(ui_vue3.toValue(rightTopCornerRef), 'mousedown', onMouseDownRightTopCorner);
	    main_core.Event.bind(ui_vue3.toValue(rightBottomCornerRef), 'mousedown', onMouseDownRightBottomCorner);
	    main_core.Event.bind(ui_vue3.toValue(leftTopCornerRef), 'mousedown', onMouseDownLeftTopCorner);
	    main_core.Event.bind(ui_vue3.toValue(leftBottomCornerRef), 'mousedown', onMouseDownLeftBottomCorner);
	  }
	  function onUnmounted() {
	    main_core.Event.unbind(ui_vue3.toValue(rightSideRef), 'mousedown', onMouseDownRightSide);
	    main_core.Event.unbind(ui_vue3.toValue(bottomSideRef), 'mousedown', onMouseDownBottomSide);
	    main_core.Event.unbind(ui_vue3.toValue(leftSideRef), 'mousedown', onMouseDownLeftSide);
	    main_core.Event.unbind(ui_vue3.toValue(topSideRef), 'mousedown', onMouseDownTopSide);
	    main_core.Event.unbind(ui_vue3.toValue(rightTopCornerRef), 'mousedown', onMouseDownRightTopCorner);
	    main_core.Event.unbind(ui_vue3.toValue(rightBottomCornerRef), 'mousedown', onMouseDownRightBottomCorner);
	    main_core.Event.unbind(ui_vue3.toValue(leftTopCornerRef), 'mousedown', onMouseDownLeftTopCorner);
	    main_core.Event.unbind(ui_vue3.toValue(leftBottomCornerRef), 'mousedown', onMouseDownLeftBottomCorner);
	  }
	  function startResize(event, curType) {
	    event.stopPropagation();
	    cursorType.value = curType;
	    resizingBlock.value = {
	      ...ui_vue3.toValue(block)
	    };
	    prevBlockX = ui_vue3.toValue(block).position.x;
	    prevBlockY = ui_vue3.toValue(block).position.y;
	    prevBlockWidth = ui_vue3.toValue(block).dimensions.width;
	    prevBlockHeight = ui_vue3.toValue(block).dimensions.height;
	    isResize.value = true;
	  }
	  function endResize(event) {
	    event.stopPropagation();
	    cursorType.value = 'default';
	    updateResizableBlock();
	    isResize.value = false;
	    resizingBlock.value = null;
	  }
	  function resizeTopSide(event) {
	    let newY = event.clientY / ui_vue3.toValue(zoom);
	    newY += ui_vue3.toValue(transformY);
	    newY -= ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom);
	    let newHeight = event.clientY / ui_vue3.toValue(zoom);
	    newHeight += ui_vue3.toValue(transformY);
	    newHeight -= ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom);
	    newHeight -= prevBlockY + prevBlockHeight;
	    newHeight = Math.abs(newHeight);
	    const fixedPositionY = prevBlockY + prevBlockHeight - ui_vue3.toValue(minHeight);
	    resizingBlock.value.position.y = newHeight < ui_vue3.toValue(minHeight) || newY >= fixedPositionY ? fixedPositionY : newY;
	    resizingBlock.value.dimensions.height = newHeight < ui_vue3.toValue(minHeight) || newY >= fixedPositionY ? ui_vue3.toValue(minHeight) : newHeight;
	  }
	  function resizeRightSide(event) {
	    let cursorX = event.clientX / ui_vue3.toValue(zoom);
	    cursorX += ui_vue3.toValue(transformX);
	    cursorX -= ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom);
	    let newWidth = prevBlockX;
	    newWidth -= event.clientX / ui_vue3.toValue(zoom);
	    newWidth -= ui_vue3.toValue(transformX);
	    newWidth -= ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom);
	    newWidth = Math.abs(newWidth);
	    resizingBlock.value.dimensions.width = newWidth < ui_vue3.toValue(minWidth) || cursorX <= prevBlockX ? ui_vue3.toValue(minWidth) : newWidth;
	  }
	  function resizeBottomSide(event) {
	    let cursorX = event.clientY / ui_vue3.toValue(zoom);
	    cursorX += ui_vue3.toValue(transformY);
	    cursorX -= ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom);
	    let newHeight = event.clientY / ui_vue3.toValue(zoom);
	    newHeight -= prevBlockY;
	    newHeight += ui_vue3.toValue(transformY);
	    newHeight -= ui_vue3.toValue(blockDiagramTop) / ui_vue3.toValue(zoom);
	    newHeight = Math.abs(newHeight);
	    resizingBlock.value.dimensions.height = newHeight < ui_vue3.toValue(minHeight) || cursorX <= prevBlockY ? ui_vue3.toValue(minHeight) : newHeight;
	  }
	  function resizeLeftSide(event) {
	    let newX = event.clientX / ui_vue3.toValue(zoom);
	    newX += ui_vue3.toValue(transformX);
	    newX -= ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom);
	    let newWidth = event.clientX / ui_vue3.toValue(zoom);
	    newWidth += ui_vue3.toValue(transformX);
	    newWidth -= ui_vue3.toValue(blockDiagramLeft) / ui_vue3.toValue(zoom);
	    newWidth -= prevBlockX + prevBlockWidth;
	    newWidth = Math.abs(newWidth);
	    const fixedPositionX = prevBlockX + prevBlockWidth - ui_vue3.toValue(minWidth);
	    resizingBlock.value.position.x = newWidth < ui_vue3.toValue(minWidth) || newX >= fixedPositionX ? fixedPositionX : newX;
	    resizingBlock.value.dimensions.width = newWidth < ui_vue3.toValue(minWidth) || newX >= fixedPositionX ? ui_vue3.toValue(minWidth) : newWidth;
	  }
	  function onMouseDownRightSide(event) {
	    startResize(event, CURSOR_TYPES.EW_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveRightSide);
	    main_core.Event.bind(document, 'mouseup', onMouseUpRightSide);
	  }
	  function onMouseMoveRightSide(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeRightSide(event);
	  }
	  function onMouseUpRightSide(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveRightSide);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpRightSide);
	  }
	  function onMouseDownBottomSide(event) {
	    startResize(event, CURSOR_TYPES.NS_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveBottomSide);
	    main_core.Event.bind(document, 'mouseup', onMouseUpBottomSide);
	  }
	  function onMouseMoveBottomSide(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeBottomSide(event);
	  }
	  function onMouseUpBottomSide(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveBottomSide);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpBottomSide);
	  }
	  function onMouseDownLeftSide(event) {
	    startResize(event, CURSOR_TYPES.EW_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveLeftSide);
	    main_core.Event.bind(document, 'mouseup', onMouseUpLeftSide);
	  }
	  function onMouseMoveLeftSide(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeLeftSide(event);
	  }
	  function onMouseUpLeftSide(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveLeftSide);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpLeftSide);
	  }
	  function onMouseDownTopSide(event) {
	    startResize(event, CURSOR_TYPES.NS_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveTopSide);
	    main_core.Event.bind(document, 'mouseup', onMouseUpTopSide);
	  }
	  function onMouseMoveTopSide(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeTopSide(event);
	  }
	  function onMouseUpTopSide(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveTopSide);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpTopSide);
	  }
	  function onMouseDownRightBottomCorner(event) {
	    startResize(event, CURSOR_TYPES.NWSE_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveRightBottomCorner);
	    main_core.Event.bind(document, 'mouseup', onMouseUpRightBottomCorner);
	  }
	  function onMouseMoveRightBottomCorner(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeRightSide(event);
	    resizeBottomSide(event);
	  }
	  function onMouseUpRightBottomCorner(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveRightBottomCorner);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpRightBottomCorner);
	  }
	  function onMouseDownRightTopCorner(event) {
	    startResize(event, CURSOR_TYPES.NESW_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveRightTopCorner);
	    main_core.Event.bind(document, 'mouseup', onMouseUpRightTopCorner);
	  }
	  function onMouseMoveRightTopCorner(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeTopSide(event);
	    resizeRightSide(event);
	  }
	  function onMouseUpRightTopCorner(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveRightTopCorner);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpRightTopCorner);
	  }
	  function onMouseDownLeftBottomCorner(event) {
	    startResize(event, CURSOR_TYPES.NESW_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveLeftBottomCorner);
	    main_core.Event.bind(document, 'mouseup', onMouseUpLeftBottomCorner);
	  }
	  function onMouseMoveLeftBottomCorner(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeLeftSide(event);
	    resizeBottomSide(event);
	  }
	  function onMouseUpLeftBottomCorner(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveLeftBottomCorner);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpLeftBottomCorner);
	  }
	  function onMouseDownLeftTopCorner(event) {
	    startResize(event, CURSOR_TYPES.NWSE_RESIZE);
	    main_core.Event.bind(document, 'mousemove', onMouseMoveLeftTopCorner);
	    main_core.Event.bind(document, 'mouseup', onMouseUpLeftTopCorner);
	  }
	  function onMouseMoveLeftTopCorner(event) {
	    event.stopPropagation();
	    if (!ui_vue3.toValue(isResize)) {
	      return;
	    }
	    resizeLeftSide(event);
	    resizeTopSide(event);
	  }
	  function onMouseUpLeftTopCorner(event) {
	    endResize(event);
	    main_core.Event.unbind(document, 'mousemove', onMouseMoveLeftTopCorner);
	    main_core.Event.unbind(document, 'mouseup', onMouseUpLeftTopCorner);
	  }
	  return {
	    isResize,
	    sizeBlockStyle,
	    onMounted,
	    onUnmounted
	  };
	}

	const CANVAS_TRANSFORM_CLASS_NAMES = {
	  base: 'ui-block-diagram-canvas-transform',
	  dragging: '--dragging'
	};

	// @vue/component
	const CanvasTransform = {
	  name: 'canvas-transform',
	  props: {
	    canvasStyle: {
	      type: Object,
	      required: true
	    },
	    zoomSensitivity: {
	      type: Number,
	      default: 0.01
	    },
	    zoomSensitivityMouse: {
	      type: Number,
	      default: 0.04
	    }
	  },
	  setup(props) {
	    const {
	      isDragging,
	      onMounted: onMountedCanvasTransform,
	      onUmounted: onUnmountedCanvasTransform,
	      onMouseDown,
	      onMouseMove,
	      onMouseUp,
	      onWheel
	    } = useCanvasTransfrom({
	      canvasRef: ui_vue3.useTemplateRef('canvasLayout'),
	      transformLayoutRef: ui_vue3.useTemplateRef('transformLayout'),
	      canvasStyle: props.canvasStyle,
	      zoomSensitivity: props.zoomSensitivity,
	      zoomSensitivityMouse: props.zoomSensitivityMouse
	    });
	    const canvasTransformClassNames = ui_vue3.computed(() => ({
	      [CANVAS_TRANSFORM_CLASS_NAMES.base]: true,
	      [CANVAS_TRANSFORM_CLASS_NAMES.dragging]: ui_vue3.toValue(isDragging)
	    }));
	    ui_vue3.onMounted(() => {
	      onMountedCanvasTransform();
	    });
	    ui_vue3.onUnmounted(() => {
	      onUnmountedCanvasTransform();
	    });
	    return {
	      canvasTransformClassNames,
	      onMouseDown,
	      onMouseMove,
	      onMouseUp,
	      onWheel
	    };
	  },
	  template: `
		<div
			:class="canvasTransformClassNames"
			@mousedown="onMouseDown"
			@mousemove="onMouseMove"
			@mouseup="onMouseUp"
			@wheel="onWheel"
			@contextmenu.prevent
		>
			<canvas
				ref="canvasLayout"
				class="ui-block-diagram-canvas-transform__canvas"
			/>
			<div
				ref="transformLayout"
				class="ui-block-diagram-canvas-transform__transform"
			>
				<slot/>
			</div>
		</div>
	`
	};

	const TARGET_CONNECTION_CLASSES = {
	  base: 'ui-block-diagram-connection__target',
	  active: '--active'
	};

	// @vue/component
	const Connection = {
	  name: 'diagram-connection',
	  props: {
	    /** @type DiagramConnection */
	    connection: {
	      type: Object,
	      required: true
	    },
	    /** @type DiagramConnectionViewType */
	    viewType: {
	      type: String,
	      default: CONNECTION_VIEW_TYPE.SMOOTHSTEP,
	      validator(viewType) {
	        return Object.values(CONNECTION_VIEW_TYPE).includes(viewType);
	      }
	    },
	    barWidth: {
	      type: Number,
	      default: 22
	    },
	    barHeight: {
	      type: Number,
	      default: 22
	    },
	    contextMenuItems: {
	      type: Array,
	      default: () => []
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      connectionPathInfo,
	      isDisabled
	    } = useConnectionState({
	      connection: props.connection,
	      viewType: props.viewType
	    });
	    const {
	      deleteConnectionById
	    } = useBlockDiagram();
	    const loc = useLoc();
	    const {
	      isOpen,
	      showContextMenu
	    } = useContextMenu([{
	      id: 'deleteConnection',
	      text: loc.getMessage('UI_BLOCK_DIAGRAM_DELETE_CONNECTION_CONTEXT_MENU_ITEM'),
	      onclick: () => {
	        deleteConnectionById(props.connection.id);
	      }
	    }]);
	    const targetConnectionClasses = ui_vue3.computed(() => ({
	      [TARGET_CONNECTION_CLASSES.base]: true,
	      [TARGET_CONNECTION_CLASSES.active]: ui_vue3.toValue(isOpen)
	    }));
	    const barPosition = ui_vue3.computed(() => {
	      var _toValue$center;
	      const {
	        x: centerX = 0,
	        y: centerY = 0
	      } = (_toValue$center = ui_vue3.toValue(connectionPathInfo).center) != null ? _toValue$center : {};
	      return {
	        x: centerX - props.barWidth / 2,
	        y: centerY - props.barHeight / 2
	      };
	    });
	    function onOpenContextMenu(event) {
	      if (ui_vue3.toValue(isDisabled) || props.disabled) {
	        return;
	      }
	      event.preventDefault();
	      showContextMenu(event);
	    }
	    return {
	      isDisabled,
	      connectionPathInfo,
	      targetConnectionClasses,
	      barPosition,
	      onOpenContextMenu
	    };
	  },
	  template: `
		<svg class="ui-block-diagram-connection">
			<g class="ui-block-diagram-connection__group">
				<path
					:d="connectionPathInfo.path"
					:class="targetConnectionClasses"
					:data-test-id="$blockDiagramTestId('connectionLine', connection.id)"
				/>
				<path
					:d="connectionPathInfo.path"
					:data-test-id="$blockDiagramTestId('connectionHoveredLine', connection.id)"
					class="ui-block-diagram-connection__hovered"
					stroke="transparent"
					fill="transparent"
					@contextmenu="onOpenContextMenu"
				/>
				<foreignObject
					:x="barPosition.x"
					:y="barPosition.y"
					:width="barWidth"
					:height="barHeight"
					class="ui-block-diagram-connection__bar"
				>
					<slot :isDisabled="isDisabled || disabled" />
				</foreignObject>
			</g>
		</svg>
	`
	};

	// @vue/component
	const DeleteConnectionBtn = {
	  name: 'delete-connection-btn',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    connectionId: {
	      type: String,
	      required: true
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      deleteConnectionById
	    } = useBlockDiagram();
	    function onDeleteConnection() {
	      if (props.disabled) {
	        return;
	      }
	      deleteConnectionById(props.connectionId);
	    }
	    return {
	      iconSet: ui_iconSet_api_vue.Outline,
	      onDeleteConnection
	    };
	  },
	  template: `
		<button
			class="ui-block-diagram-delete-connection-btn"
			:data-test-id="$blockDiagramTestId('connectionDeleteBtn', connectionId)"
			@click="onDeleteConnection"
		>
			<div class="ui-block-diagram-delete-connection-btn__icon-wrap">
				<BIcon
					:name="iconSet.TRASHCAN"
					:size="14"
					class="ui-block-diagram-delete-connection-btn__icon"
				/>
			</div>
		</button>
	`
	};

	// @vue/component
	const ContextMenuLayout = {
	  name: 'ContextMenuLayout',
	  setup() {
	    const instance = useBlockDiagram();
	    const targetContainerStyle = ui_vue3.computed(() => ({
	      top: `${ui_vue3.toValue(instance.positionContextMenu).top}px`,
	      left: `${ui_vue3.toValue(instance.positionContextMenu).left}px`
	    }));
	    return {
	      instance,
	      targetContainerStyle
	    };
	  },
	  template: `
		<div
			:ref="instance.contextMenuLayerRef"
			class="ui-block-diagram-context-menu__layout"
		>
			<slot/>
			<div
				:ref="instance.targetContainerRef"
				:style="targetContainerStyle"
				class="ui-block-diagram-context-menu__target-container"
			/>
		</div>
	`
	};

	// @vue/component
	const BlocksQueueTransition = {
	  name: 'blocks-queue-transition',
	  setup() {
	    const {
	      isAnimate,
	      currentAnimationItem,
	      animationQueue,
	      hooks
	    } = useBlockDiagram();
	    const canvas = useCanvas();
	    const history = useHistory();
	    const highlighted = useHighlightedBlocks();
	    function nextAnimationItem() {
	      var _toValue$next, _toValue;
	      const {
	        done = false
	      } = (_toValue$next = (_toValue = ui_vue3.toValue(animationQueue)) == null ? void 0 : _toValue.next()) != null ? _toValue$next : {};
	      if (done) {
	        animationQueue.value = null;
	      } else {
	        history.makeSnapshot();
	      }
	    }
	    function onBeforeEnter() {
	      var _toValue2;
	      hooks.blockTransitionStart.trigger((_toValue2 = ui_vue3.toValue(currentAnimationItem)) == null ? void 0 : _toValue2.item);
	    }
	    function onEnter() {
	      highlighted.clear();
	      highlighted.add(ui_vue3.toValue(currentAnimationItem).item.id);
	      canvas.goToBlockById(ui_vue3.toValue(currentAnimationItem).item.id);
	    }
	    function onAfterEnter() {
	      var _toValue3;
	      hooks.blockTransitionEnd.trigger((_toValue3 = ui_vue3.toValue(currentAnimationItem)) == null ? void 0 : _toValue3.item);
	      nextAnimationItem();
	    }
	    function onAfterLeave() {
	      nextAnimationItem();
	    }
	    return {
	      isAnimate,
	      onBeforeEnter,
	      onEnter,
	      onAfterEnter,
	      onAfterLeave
	    };
	  },
	  template: `
		<TransitionGroup
			v-if="isAnimate"
			name="ui-block-diagram-blocks-queue-transition"
			@before-enter="onBeforeEnter"
			@enter="onEnter"
			@after-enter="onAfterEnter"
			@after-leave="onAfterLeave"
		>
			<slot/>
		</TransitionGroup>
		<template v-else>
			<slot/>
		</template>
	`
	};

	// @vue/component
	const GroupedBlocks = {
	  name: 'grouped-blocks',
	  components: {
	    BlocksQueueTransition
	  },
	  setup() {
	    const {
	      groupedBlocks,
	      blockGroupNames
	    } = useBlockDiagram();
	    return {
	      blockGroupNames,
	      groupedBlocks,
	      getGroupBlockSlotName
	    };
	  },
	  template: `
		<BlocksQueueTransition>
			<slot
				v-for="group in blockGroupNames"
				:key="group"
				:name="getGroupBlockSlotName(group)"
				:blocks="groupedBlocks[group]"
			/>
		</BlocksQueueTransition>
	`
	};

	// @vue/component
	const NewConnection = {
	  name: 'new-connection',
	  props: {
	    /** @type DiagramNewConnectionViewType */
	    viewType: {
	      type: String,
	      default: NEW_CONNECTION_VIEW_TYPE.BEZIER,
	      validator(viewType) {
	        return Object.values(NEW_CONNECTION_VIEW_TYPE).includes(viewType);
	      }
	    }
	  },
	  setup(props) {
	    const {
	      hasNewConnection,
	      newConnectionPathInfo
	    } = useNewConnectionState({
	      viewType: props.viewType
	    });
	    return {
	      hasNewConnection,
	      newConnectionPathInfo
	    };
	  },
	  template: `
		<svg
			v-if="hasNewConnection"
			class="ui-block-diagram-new-connection"
		>
			<path
				:d="newConnectionPathInfo.path"
				class="ui-block-diagram-new-connection__path"
			/>
		</svg>
	`
	};

	// @vue/component
	const ConnectionsQueueTransition = {
	  name: 'connections-queue-transition',
	  setup() {
	    const {
	      isAnimate,
	      currentAnimationItem,
	      animationQueue,
	      updatePortPosition,
	      hooks
	    } = useBlockDiagram();
	    const history = useHistory();
	    function onBeforeEnter() {
	      var _toValue;
	      const {
	        item: connection
	      } = (_toValue = ui_vue3.toValue(currentAnimationItem)) != null ? _toValue : {};
	      hooks.connectionTransitionStart.trigger(connection);
	      const {
	        sourceBlockId,
	        sourcePortId,
	        targetBlockId,
	        targetPortId
	      } = connection;
	      updatePortPosition(sourceBlockId, sourcePortId);
	      updatePortPosition(targetBlockId, targetPortId);
	    }
	    function nextAnimatedItem() {
	      var _toValue$next, _toValue2;
	      const {
	        done = false
	      } = (_toValue$next = (_toValue2 = ui_vue3.toValue(animationQueue)) == null ? void 0 : _toValue2.next()) != null ? _toValue$next : {};
	      if (done) {
	        animationQueue.value = null;
	      } else {
	        history.makeSnapshot();
	      }
	    }
	    function onAfterEnter() {
	      var _toValue3;
	      hooks.connectionTransitionEnd.trigger((_toValue3 = ui_vue3.toValue(currentAnimationItem)) == null ? void 0 : _toValue3.item);
	      nextAnimatedItem();
	    }
	    function onAfterLeave() {
	      nextAnimatedItem();
	    }
	    return {
	      isAnimate,
	      onBeforeEnter,
	      onAfterEnter,
	      onAfterLeave
	    };
	  },
	  template: `
		<TransitionGroup
			v-if="isAnimate"
			name="ui-block-diagram-connections-queue-transition"
			@before-enter="onBeforeEnter"
			@after-enter="onAfterEnter"
			@after-leave="onAfterLeave"
		>
			<slot/>
		</TransitionGroup>
		<template v-else>
			<slot/>
		</template>
	`
	};

	// @vue/component
	const GroupedConnections = {
	  name: 'grouped-connections',
	  components: {
	    Connection,
	    NewConnection,
	    ConnectionsQueueTransition
	  },
	  setup() {
	    const {
	      groupedConnections,
	      connectionGroupNames
	    } = useBlockDiagram();
	    return {
	      groupedConnections,
	      connectionGroupNames,
	      getGroupConnectionSlotName
	    };
	  },
	  template: `
		<ConnectionsQueueTransition>
			<slot
				v-for="connection in connectionGroupNames"
				:key="connection"
				:name="getGroupConnectionSlotName(connection)"
				:connections="groupedConnections[connection]"
			/>
			<NewConnection/>
		</ConnectionsQueueTransition>
	`
	};

	// eslint-disable-next-line no-unused-vars

	// @vue/component
	const MoveableBlock = {
	  name: 'moveable-block',
	  props: {
	    /** @type DiagramBlock */
	    block: {
	      type: Object,
	      required: true
	    },
	    highlighted: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      block
	    } = ui_vue3.toRefs(props);
	    const {
	      blockZindex,
	      isHiglitedBlock,
	      isDisabled
	    } = useBlockState(block);
	    const highlightedBlocks = useHighlightedBlocks();
	    const {
	      isDragged,
	      blockPositionStyle
	    } = useMoveableBlock(ui_vue3.useTemplateRef('blockEl'), block);
	    ui_vue3.watch(() => props.highlighted, value => {
	      if (value) {
	        highlightedBlocks.add(props.block.id);
	      } else {
	        highlightedBlocks.remove(props.block.id);
	      }
	    });
	    const blockStyle = ui_vue3.computed(() => ({
	      ...ui_vue3.toValue(blockPositionStyle),
	      ...ui_vue3.toValue(blockZindex)
	    }));
	    ui_vue3.onUnmounted(() => {
	      highlightedBlocks.remove(props.block.id);
	    });
	    function onMouseDownSelectBlock() {
	      highlightedBlocks.clear();
	      highlightedBlocks.add(props.block.id);
	    }
	    return {
	      isHiglitedBlock,
	      isDisabled,
	      isDragged,
	      blockStyle,
	      blockZindex,
	      blockPositionStyle,
	      onMouseDownSelectBlock
	    };
	  },
	  template: `
		<div
			class="ui-block-diagram-moveable-block"
			:style="blockStyle"
			ref="blockEl"
			:data-test-id="$blockDiagramTestId('block', block.id)"
			@mousedown="onMouseDownSelectBlock"
		>
			<slot
				:block="block"
				:isHighlighted="isHiglitedBlock"
				:isDragged="isDragged"
				:isDisabled="isDisabled"
			/>
		</div>
	`
	};

	const PORT_CLASS_NAMES = {
	  base: 'ui-block-diagram-port',
	  disabled: '--disabled',
	  active: '--active'
	};

	// @vue/component
	const Port = {
	  name: 'diagram-port',
	  props: {
	    /** @type DiagramBlock */
	    block: {
	      type: Object,
	      required: true
	    },
	    /** @type DiagramPort */
	    port: {
	      type: Object,
	      required: true
	    },
	    /** @type DiagramPortPosition */
	    position: {
	      type: String,
	      required: true,
	      validator(position) {
	        return Object.values(PORT_POSITION).includes(position);
	      }
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      isDisabled,
	      onMountedPort,
	      onUnmountedPort
	    } = usePortState({
	      portRef: ui_vue3.useTemplateRef('port'),
	      block: props.block,
	      port: props.port,
	      position: props.position
	    });
	    const {
	      isSourcePort,
	      onMouseDownPort,
	      onMouseOverPort,
	      onMouseLeavePort
	    } = useNewConnection(props.block, props.port);
	    const portClassNames = ui_vue3.computed(() => ({
	      [PORT_CLASS_NAMES.base]: true,
	      [PORT_CLASS_NAMES.active]: ui_vue3.toValue(isSourcePort),
	      [PORT_CLASS_NAMES.disabled]: ui_vue3.toValue(isDisabled)
	    }));
	    ui_vue3.onMounted(() => {
	      onMountedPort();
	    });
	    ui_vue3.onUnmounted(() => {
	      onUnmountedPort();
	    });
	    return {
	      portClassNames,
	      onMouseDownPort,
	      onMouseOverPort,
	      onMouseLeavePort
	    };
	  },
	  template: `
		<div
			ref="port"
			:class="portClassNames"
			:data-test-id="$blockDiagramTestId('port', port.id)"
			@mousedown="onMouseDownPort"
			@mouseover="onMouseOverPort"
			@mouseleave="onMouseLeavePort"
		/>
	`
	};

	const BLOCK_CONTENT_STUB_CLASS_NAMES = {
	  base: 'ui-block-diagram-block-content-stub',
	  highlighted: '--highlighted'
	};

	// @vue/component
	const BlockContentStub = {
	  name: 'block-content-stub',
	  components: {
	    Port
	  },
	  props: {
	    /* @type DiagramBlock */
	    block: {
	      type: Object,
	      required: true
	    },
	    highlighted: {
	      type: Boolean,
	      default: false
	    },
	    dragged: {
	      type: Boolean,
	      default: false
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      deleteBlockById
	    } = useBlockDiagram();
	    const loc = useLoc();
	    const {
	      showContextMenu
	    } = useContextMenu([{
	      id: 'deleteConnection',
	      text: loc.getMessage('UI_BLOCK_DIAGRAM_DELETE_BLOCK_CONTEXT_MENU_ITEM'),
	      onclick: () => {
	        deleteBlockById(props.block.id);
	      }
	    }]);
	    const blockContentClassNames = ui_vue3.computed(() => ({
	      [BLOCK_CONTENT_STUB_CLASS_NAMES.base]: true,
	      [BLOCK_CONTENT_STUB_CLASS_NAMES.highlighted]: props.highlighted
	    }));
	    function onShowContextMenu(event) {
	      event.preventDefault();
	      if (props.disabled) {
	        return;
	      }
	      const {
	        clientX,
	        clientY
	      } = event;
	      showContextMenu({
	        clientX,
	        clientY
	      });
	    }
	    return {
	      blockContentClassNames,
	      onShowContextMenu
	    };
	  },
	  template: `
		<div
			:class="blockContentClassNames"
			@contextmenu="onShowContextMenu"
		>
			<div class="ui-block-diagram-block-content-stub__id">
				{{ block.id }}
			</div>

			<div class="ui-block-diagram-block-content-stub__left-column">
				<div
					v-for="port in block.ports.input"
					:key="port.id"
					class="ui-block-diagram-block-content-stub__port-line"
				>
					<div class="ui-block-diagram-block-content-stub__port --left">
						<Port
							:block="block"
							:port="port"
							:styled="false"
							:portsToShow="null"
							position="left"
						/>
					</div>
				</div>
			</div>

			<div class="ui-block-diagram-block-content-stub__right-column">
				<div
					v-for="port in block.ports.output"
					:key="port.id"
					class="ui-block-diagram-block-content-stub__port-line"
				>
					<div class="ui-block-diagram-block-content-stub__port --right">
						<Port
							:block="block"
							:port="port"
							:styled="false"
							:portsToShow="null"
							position="right"
						/>
					</div>
				</div>
			</div>
		</div>
	`
	};

	const UI_CANVAS_GRID_COLOR = '#A1B8D9';
	const UI_CANVAS_BACKGROUND_COLOR = '#ECF0F2';
	const BLOCK_DIAGRAM_CLASS_NAMES = {
	  base: 'ui-block-diagram',
	  ewResize: '--cursor-ew-resize',
	  nsResize: '--cursor-ns-resize',
	  nwSeResize: '--cursor-nwse-resize',
	  neSwResize: '--cursor-nesw-resize',
	  grabbing: '--grabbing'
	};

	// @vue/component
	const BlockDiagram = {
	  name: 'block-diagram',
	  components: {
	    CanvasTransform,
	    ContextMenuLayout,
	    GroupedBlocks,
	    GroupedConnections,
	    Connection,
	    DeleteConnectionBtn,
	    MoveableBlock,
	    BlockContentStub
	  },
	  props: {
	    /** @type Array<DiagramBlock> */
	    blocks: {
	      type: Array,
	      required: true
	    },
	    /** @type Array<DiagramConnection> */
	    connections: {
	      type: Array,
	      required: true
	    },
	    canvasStyle: {
	      type: Object,
	      default: () => ({
	        style: 'grid',
	        size: 64,
	        gridColor: UI_CANVAS_GRID_COLOR,
	        backgroundColor: UI_CANVAS_BACKGROUND_COLOR
	      })
	    },
	    zoomSensitivity: {
	      type: Number,
	      default: 0.01
	    },
	    zoomSensitivityMouse: {
	      type: Number,
	      default: 0.04
	    },
	    zoom: {
	      type: Number,
	      default: 1
	    },
	    minZoom: {
	      type: Number,
	      default: 0.2
	    },
	    maxZoom: {
	      type: Number,
	      default: 4
	    },
	    historyHooks: {
	      type: Array,
	      default: () => [HOOK_NAMES.END_DRAG_BLOCK, HOOK_NAMES.ADD_BLOCK, HOOK_NAMES.DELETE_BLOCK, HOOK_NAMES.CREATE_CONNECTION, HOOK_NAMES.DELETE_CONNECTION]
	    },
	    snapshotHandler: {
	      type: Function,
	      default: null
	    },
	    revertHandler: {
	      type: Function,
	      default: null
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['update:blocks', 'update:connections', HOOK_NAMES.CHANGED_BLOCKS, HOOK_NAMES.CHANGED_CONNECTIONS, HOOK_NAMES.START_DRAG_BLOCK, HOOK_NAMES.MOVE_DRAG_BLOCK, HOOK_NAMES.END_DRAG_BLOCK, HOOK_NAMES.ADD_BLOCK, HOOK_NAMES.UPDATE_BLOCK, HOOK_NAMES.DELETE_BLOCK, HOOK_NAMES.CREATE_CONNECTION, HOOK_NAMES.DELETE_CONNECTION, HOOK_NAMES.BLOCK_TRANSITION_START, HOOK_NAMES.BLOCK_TRANSITION_END, HOOK_NAMES.CONNECTION_TRANSITION_START, HOOK_NAMES.CONNECTION_TRANSITION_END, HOOK_NAMES.DROP_NEW_BLOCK],
	  setup(props, {
	    emit
	  }) {
	    const {
	      blockGroupNames,
	      connectionGroupNames,
	      cursorType
	    } = useBlockDiagram(props);
	    const initAppElements = useInitAppElements({
	      blockDiagramRef: ui_vue3.useTemplateRef('blockDiagram')
	    });
	    const {
	      makeSnapshot
	    } = useHistory();
	    const {
	      dispose: disposeModelValue
	    } = useModelValue(emit);
	    const {
	      dispose: disposeWatchProps
	    } = useWatchProps(props);
	    const {
	      dispose: disposeRegisterHooks
	    } = useRegisterHooks({
	      ...Object.entries(HOOK_NAMES).reduce((acc, [name, hookName]) => {
	        acc[hookName] = (...args) => {
	          emit(hookName, ...args);
	        };
	        return acc;
	      }, {})
	    }, {
	      ...props.historyHooks.reduce((acc, hookName) => {
	        acc[hookName] = () => makeSnapshot();
	        return acc;
	      }, {})
	    });
	    const {
	      onDrop
	    } = useDragAndDrop();
	    const isGrabbing = ui_vue3.ref(false);
	    const blockDiagramClassNames = ui_vue3.computed(() => ({
	      [BLOCK_DIAGRAM_CLASS_NAMES.base]: true,
	      [BLOCK_DIAGRAM_CLASS_NAMES.grabbing]: isGrabbing.value,
	      [BLOCK_DIAGRAM_CLASS_NAMES.ewResize]: ui_vue3.toValue(cursorType) === CURSOR_TYPES.EW_RESIZE,
	      [BLOCK_DIAGRAM_CLASS_NAMES.nsResize]: ui_vue3.toValue(cursorType) === CURSOR_TYPES.NS_RESIZE,
	      [BLOCK_DIAGRAM_CLASS_NAMES.nwSeResize]: ui_vue3.toValue(cursorType) === CURSOR_TYPES.NWSE_RESIZE,
	      [BLOCK_DIAGRAM_CLASS_NAMES.neSwResize]: ui_vue3.toValue(cursorType) === CURSOR_TYPES.NESW_RESIZE
	    }));
	    ui_vue3.onMounted(() => {
	      initAppElements.onMountedAppElements();
	    });
	    ui_vue3.onUnmounted(() => {
	      disposeModelValue();
	      disposeWatchProps();
	      disposeRegisterHooks();
	      initAppElements.onUnmountedAppElements();
	    });
	    function onDragEnter(event) {
	      isGrabbing.value = true;
	    }
	    function onDragLeave(event) {
	      isGrabbing.value = false;
	    }
	    function onDragDrop(event) {
	      isGrabbing.value = false;
	      onDrop(event);
	    }
	    return {
	      blockDiagramClassNames,
	      blockGroupNames,
	      connectionGroupNames,
	      getGroupBlockSlotName,
	      getGroupConnectionSlotName,
	      onDragDrop,
	      onDragEnter,
	      onDragLeave
	    };
	  },
	  template: `
		<div
			:class="blockDiagramClassNames"
			ref="blockDiagram"
			@dragover.prevent
			@dragenter="onDragEnter"
			@dragleave="onDragLeave"
			@drop="onDragDrop"
		>
			<CanvasTransform
				:canvasStyle="canvasStyle"
				:zoomSensitivity="zoomSensitivity"
				:zoomSensitivityMouse="zoomSensitivityMouse"
			>
				<ContextMenuLayout>
					<GroupedConnections>
						<template
							v-for="groupName in connectionGroupNames"
							#[getGroupConnectionSlotName(groupName)]="{ connections }"
							:key="groupName"
						>
							<slot
								v-for="connection in connections"
								:name="getGroupConnectionSlotName(groupName)"
								:key="connection.id"
								:connection="connection"
							>
								<Connection
									:connection="connection"
									:key="connection.id"
								>
									<template #default="{ isDisabled }">
										<DeleteConnectionBtn 
											:connectionId="connection.id"
											:disabled="isDisabled"
										/>
									</template>
								</Connection>
							</slot>
						</template>

						<template #new-connection>
							<slot name="new-connection"/>
						</template>
					</GroupedConnections>
					<GroupedBlocks>
						<template
							v-for="groupName in blockGroupNames"
							#[getGroupBlockSlotName(groupName)]="{ blocks }"
							:key="groupName"
						>
							<slot
								v-for="block in blocks"
								:name="getGroupBlockSlotName(groupName)"
								:key="block.id"
								:block="block"
							>
								<MoveableBlock
									:block="block"
									:key="block.id"
								>
									<template #default="{ isHighlighted, isDragged, isDisabled }">
										<BlockContentStub
											:block="block"
											:highlighted="isHighlighted"
											:dragged="isDragged"
											:disabled="isDisabled"
										/>
									</template>
								</MoveableBlock>
							</slot>
						</template>
					</GroupedBlocks>
				</ContextMenuLayout>
			</CanvasTransform>
		</div>
	`
	};

	// @vue/component
	const IconButton = {
	  name: 'icon-button',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    iconName: {
	      type: String,
	      default: ''
	    },
	    size: {
	      type: [Number, String],
	      default: 16
	    },
	    color: {
	      type: String,
	      default: '#959CA4'
	    },
	    active: {
	      type: Boolean,
	      default: false
	    },
	    activeColor: {
	      type: String,
	      default: '#4A9DFF'
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      size,
	      active,
	      disabled
	    } = ui_vue3.toRefs(props);
	    const buttonClassNames = ui_vue3.computed(() => ({
	      'ui-block-diagram-icon-button': true,
	      '--disabled': ui_vue3.toValue(disabled)
	    }));
	    const buttonStyle = ui_vue3.computed(() => ({
	      width: `${ui_vue3.toValue(size)}px`,
	      height: `${ui_vue3.toValue(size)}px`
	    }));
	    const iconClassNames = ui_vue3.computed(() => ({
	      'ui-block-diagram-icon-button__icon': true,
	      '--active': ui_vue3.toValue(active)
	    }));
	    return {
	      buttonClassNames,
	      buttonStyle,
	      iconClassNames
	    };
	  },
	  template: `
		<button
			:class="buttonClassNames"
			:style="buttonStyle"
		>
			<slot>
				<BIcon
					:class="iconClassNames"
					:name="iconName"
					:color="color"
					:size="size"
				/>
			</slot>
		</button>
	`
	};

	// @vue/component
	const HistoryBar = {
	  name: 'history-bar',
	  components: {
	    IconButton
	  },
	  props: {
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      isDisabledBlockDiagram
	    } = useBlockDiagram();
	    const {
	      hasNext,
	      hasPrev,
	      next,
	      prev
	    } = useHistory();
	    function onNext() {
	      if (props.disabled || ui_vue3.toValue(isDisabledBlockDiagram)) {
	        return;
	      }
	      next();
	    }
	    function onPrev() {
	      if (props.disabled || ui_vue3.toValue(isDisabledBlockDiagram)) {
	        return;
	      }
	      prev();
	    }
	    return {
	      hasNext,
	      hasPrev,
	      onNext,
	      onPrev,
	      Outline: ui_iconSet_api_vue.Outline
	    };
	  },
	  template: `
		<div class="ui-block-diagram-histoy-bar">
			<slot>
				<IconButton
					class="ui-block-diagram-histoy-bar__prev-button"
					:icon-name="Outline.FORWARD"
					:size="22"
					:disabled="!hasPrev"
					:data-test-id="$blockDiagramTestId('historyPrevBtn')"
					@click="onPrev"
				/>
				<IconButton
					:icon-name="Outline.FORWARD"
					:size="22"
					:disabled="!hasNext"
					:data-test-id="$blockDiagramTestId('historyNextBtn')"
					@click="onNext"
				/>
			</slot>
		</div>
	`
	};

	const ZOOM_TYPES = {
	  in: 'in',
	  out: 'out'
	};

	// @vue/component
	const ZoomBtn = {
	  name: 'zoom-btn',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    stepZoom: {
	      type: Number,
	      default: 0.2
	    },
	    /** @type ZoomType */
	    typeZoom: {
	      type: String,
	      default: ZOOM_TYPES.in,
	      validator(value) {
	        return value === ZOOM_TYPES.in || value === ZOOM_TYPES.out;
	      }
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['zoom-change'],
	  setup(props, {
	    emit
	  }) {
	    const {
	      isDisabledBlockDiagram
	    } = useBlockDiagram();
	    const {
	      zoomIn,
	      zoomOut
	    } = useCanvas();
	    const {
	      stepZoom,
	      typeZoom
	    } = ui_vue3.toRefs(props);
	    function onZoom() {
	      if (props.disabled || ui_vue3.toValue(isDisabledBlockDiagram)) {
	        return;
	      }
	      if (ui_vue3.toValue(typeZoom) === ZOOM_TYPES.in) {
	        zoomIn(ui_vue3.toValue(stepZoom));
	      } else if (ui_vue3.toValue(typeZoom) === ZOOM_TYPES.out) {
	        zoomOut(ui_vue3.toValue(stepZoom));
	      }
	    }
	    return {
	      iconSet: ui_iconSet_api_vue.Outline,
	      zoomTypes: ZOOM_TYPES,
	      onZoom
	    };
	  },
	  template: `
		<button
			class="ui-block-diagram-control-btn__btn"
			@click="onZoom"
		>
			<BIcon
				v-if="typeZoom === zoomTypes.in"
				:name="iconSet.PLUS_M"
				:size="22"
				class="ui-block-diagram-control-btn__icon"
			/>
			<BIcon
				v-else
				:name="iconSet.MINUS_M"
				:size="22"
				class="ui-block-diagram-control-btn__icon"
			/>
		</button>
	`
	};

	// @vue/component
	const ZoomPercent = {
	  name: 'zoom-percent',
	  setup(props) {
	    const {
	      zoom
	    } = useBlockDiagram();
	    const percent = ui_vue3.computed(() => {
	      var _toValue;
	      return (((_toValue = ui_vue3.toValue(zoom)) != null ? _toValue : 0) * 100).toFixed(0);
	    });
	    return {
	      percent
	    };
	  },
	  template: `
		<span class="ui-block-diagram-percent">{{ percent }}</span>
	`
	};

	const CURSOR_RECT_STROKE_WIDTH = 2;
	const FIRST_EMPTY_BLOCK_ID = 'firstCanvasBlock';
	const LAST_EMPTY_BLOCK_ID = 'lastCanvasBlock';

	// @vue/component
	const CanvasMap = {
	  name: 'canvas-map',
	  props: {
	    mapWidth: {
	      type: Number,
	      default: 310
	    },
	    mapHeight: {
	      type: Number,
	      default: 183
	    }
	  },
	  // eslint-disable-next-line max-lines-per-function
	  setup(props, {
	    emit
	  }) {
	    const {
	      blocks,
	      canvasWidth,
	      canvasHeight,
	      transformX,
	      transformY,
	      zoom
	    } = useBlockDiagram();
	    const {
	      setCamera
	    } = useCanvas();
	    const {
	      mapWidth,
	      mapHeight
	    } = ui_vue3.toRefs(props);
	    const mapEl = ui_vue3.useTemplateRef('map');
	    let mapElLeft = 0;
	    let mapElTop = 0;
	    const isDragged = ui_vue3.ref(false);
	    const canvasMapStyle = ui_vue3.computed(() => ({
	      width: `${ui_vue3.toValue(mapWidth)}px`,
	      height: `${ui_vue3.toValue(mapHeight)}px`
	    }));
	    const preparedBlock = ui_vue3.computed(() => {
	      if (ui_vue3.toValue(blocks).length === 1) {
	        return [{
	          id: FIRST_EMPTY_BLOCK_ID,
	          position: {
	            x: 0,
	            y: 0
	          },
	          dimensions: {
	            width: 0,
	            height: 0
	          }
	        }, {
	          ...ui_vue3.toValue(blocks)[0]
	        }, {
	          id: LAST_EMPTY_BLOCK_ID,
	          position: {
	            x: props.mapWidth,
	            y: props.mapHeight
	          },
	          dimensions: {
	            width: 0,
	            height: 0
	          }
	        }];
	      }
	      return ui_vue3.toValue(blocks);
	    });
	    const startDiagramX = ui_vue3.computed(() => {
	      return ui_vue3.toValue(preparedBlock).reduce((min, block) => Math.min(min, block.position.x), Infinity);
	    });
	    const startDiagramY = ui_vue3.computed(() => {
	      return ui_vue3.toValue(preparedBlock).reduce((min, block) => Math.min(min, block.position.y), Infinity);
	    });
	    const diagramWidth = ui_vue3.computed(() => {
	      if (!main_core.Type.isArrayFilled(ui_vue3.toValue(blocks))) {
	        return props.mapWidth;
	      }
	      const maxX = ui_vue3.toValue(preparedBlock).reduce((max, block) => Math.max(max, block.position.x + block.dimensions.width), -Infinity);
	      return maxX - ui_vue3.toValue(startDiagramX);
	    });
	    const diagramHeight = ui_vue3.computed(() => {
	      if (!main_core.Type.isArrayFilled(ui_vue3.toValue(blocks))) {
	        return props.mapHeight;
	      }
	      const maxY = ui_vue3.toValue(preparedBlock).reduce((max, block) => Math.max(max, block.position.y + block.dimensions.height), -Infinity);
	      return maxY - ui_vue3.toValue(startDiagramY);
	    });
	    const scaleMap = ui_vue3.computed(() => {
	      return Math.min(ui_vue3.toValue(mapWidth) / ui_vue3.toValue(diagramWidth), ui_vue3.toValue(mapHeight) / ui_vue3.toValue(diagramHeight));
	    });
	    const cursorRectWidth = ui_vue3.computed(() => {
	      return ui_vue3.toValue(canvasWidth) * ui_vue3.toValue(scaleMap) / ui_vue3.toValue(zoom);
	    });
	    const cursorRectHeight = ui_vue3.computed(() => {
	      return ui_vue3.toValue(canvasHeight) * ui_vue3.toValue(scaleMap) / ui_vue3.toValue(zoom);
	    });
	    const cursorRectPosition = ui_vue3.computed(() => {
	      let width = ui_vue3.toValue(cursorRectWidth);
	      let height = ui_vue3.toValue(cursorRectHeight);
	      let x = (ui_vue3.toValue(transformX) - ui_vue3.toValue(startDiagramX)) * ui_vue3.toValue(scaleMap);
	      let y = (ui_vue3.toValue(transformY) - ui_vue3.toValue(startDiagramY)) * ui_vue3.toValue(scaleMap);
	      [x, width] = width > ui_vue3.toValue(mapWidth) ? [1, ui_vue3.toValue(mapWidth) - CURSOR_RECT_STROKE_WIDTH] : [x, width];
	      [y, height] = height > ui_vue3.toValue(mapHeight) ? [1, ui_vue3.toValue(mapHeight) - CURSOR_RECT_STROKE_WIDTH] : [y, height];
	      x = x < 0 ? 1 : x;
	      y = y < 0 ? 1 : y;
	      x = x + width > ui_vue3.toValue(mapWidth) ? ui_vue3.toValue(mapWidth) - width - CURSOR_RECT_STROKE_WIDTH : x;
	      y = y + height > ui_vue3.toValue(mapHeight) ? ui_vue3.toValue(mapHeight) - height - CURSOR_RECT_STROKE_WIDTH : y;
	      return {
	        x,
	        y,
	        width,
	        height
	      };
	    });
	    function updateCameraPosition(event) {
	      const x = event.clientX - mapElLeft;
	      const y = event.clientY - mapElTop;
	      const canvasX = x / ui_vue3.toValue(scaleMap) + ui_vue3.toValue(startDiagramX);
	      const canvasY = y / ui_vue3.toValue(scaleMap) + ui_vue3.toValue(startDiagramY);
	      setCamera({
	        x: canvasX - ui_vue3.toValue(canvasWidth) / ui_vue3.toValue(zoom) / 2,
	        y: canvasY - ui_vue3.toValue(canvasHeight) / ui_vue3.toValue(zoom) / 2,
	        zoom: ui_vue3.toValue(zoom),
	        viewportX: 0,
	        viewportY: 0
	      });
	    }
	    function onMapMouseDown(event) {
	      isDragged.value = true;
	      if (ui_vue3.toValue(mapEl)) {
	        const {
	          left,
	          top
	        } = ui_vue3.toValue(mapEl).getBoundingClientRect();
	        [mapElLeft, mapElTop] = [left, top];
	      }
	      updateCameraPosition(event);
	    }
	    function onMapMouseMove(event) {
	      if (!ui_vue3.toValue(isDragged)) {
	        return;
	      }
	      updateCameraPosition(event);
	    }
	    function onMapMouseUp(event) {
	      isDragged.value = false;
	      updateCameraPosition(event);
	    }
	    return {
	      preparedBlock,
	      canvasMapStyle,
	      startDiagramX,
	      startDiagramY,
	      scaleMap,
	      cursorRectPosition,
	      onMapMouseDown,
	      onMapMouseMove,
	      onMapMouseUp
	    };
	  },
	  template: `
		<div :style="canvasMapStyle">
			<svg
				:width="mapWidth"
				:height="mapHeight"
				ref="map"
				class="ui-block-diagram-canvas-map"
				@mousedown="onMapMouseDown"
				@mousemove="onMapMouseMove"
				@mouseup="onMapMouseUp"
			>
				<rect
					v-for="block in preparedBlock"
					:key="block.id"
					:x="(block.position.x - startDiagramX) * scaleMap"
					:y="(block.position.y - startDiagramY) * scaleMap"
					:width="block.dimensions.width * scaleMap"
					:height="block.dimensions.height * scaleMap"
					:rx="2"
					class="ui-block-diagram-canvas-map__block"
				/>
				<rect
					:x="cursorRectPosition.x"
					:y="cursorRectPosition.y"
					:width="cursorRectPosition.width"
					:height="cursorRectPosition.height"
					:rx="3"
					class="ui-block-diagram-canvas-map__cursor"
				/>
			</svg>
		</div>
	`
	};

	// @vue/component
	const CanvasMapBtn = {
	  name: 'canvas-map-btn',
	  props: {
	    width: {
	      type: Number,
	      default: 75
	    },
	    height: {
	      type: Number,
	      default: 32
	    }
	  },
	  setup(props) {
	    const {
	      blocks
	    } = useBlockDiagram();
	    const btnStyle = ui_vue3.computed(() => ({
	      width: `${props.width}px`,
	      height: `${props.height}px`
	    }));
	    const startDiagramX = ui_vue3.computed(() => {
	      return ui_vue3.toValue(blocks).reduce((min, block) => Math.min(min, block.position.x), Infinity);
	    });
	    const startDiagramY = ui_vue3.computed(() => {
	      return ui_vue3.toValue(blocks).reduce((min, block) => Math.min(min, block.position.y), Infinity);
	    });
	    const diagramWidth = ui_vue3.computed(() => {
	      if (!main_core.Type.isArrayFilled(ui_vue3.toValue(blocks))) {
	        return props.width;
	      }
	      const maxX = ui_vue3.toValue(blocks).reduce((max, block) => Math.max(max, block.position.x + block.dimensions.width), -Infinity);
	      return maxX - ui_vue3.toValue(startDiagramX);
	    });
	    const diagramHeight = ui_vue3.computed(() => {
	      if (!main_core.Type.isArrayFilled(ui_vue3.toValue(blocks))) {
	        return props.height;
	      }
	      const maxY = ui_vue3.toValue(blocks).reduce((max, block) => Math.max(max, block.position.y + block.dimensions.height), -Infinity);
	      return maxY - ui_vue3.toValue(startDiagramY);
	    });
	    const scaleButton = ui_vue3.computed(() => {
	      return Math.min(props.width / ui_vue3.toValue(diagramWidth), props.height / ui_vue3.toValue(diagramHeight));
	    });
	    return {
	      blocks,
	      btnStyle,
	      startDiagramX,
	      startDiagramY,
	      scaleButton
	    };
	  },
	  template: `
		<button
			:style="btnStyle"
			class="ui-block-diagram-canvas-map-btn"
		>
			<svg
				:width="width"
				:height="height"
				class="ui-block-diagram-canvas-map-btn__icon"
			>
				<rect
					v-for="block in blocks"
					:key="block.id"
					:x="(block.position.x - startDiagramX) * scaleButton"
					:y="(block.position.y - startDiagramY) * scaleButton"
					:width="block.dimensions.width * scaleButton"
					:height="block.dimensions.height * scaleButton"
					:rx="1"
					class="ui-block-diagram-canvas-map-btn__rect"
				/>
			</svg>
		</button>
	`
	};

	const VERTICAL_MAP_POSITION = {
	  left: 'left',
	  right: 'right'
	};
	const GORIZONTAL_MAP_POSITION = {
	  top: 'top',
	  bottom: 'bottom'
	};
	const MAP_CLASSES = {
	  base: 'ui-block-diagram-canvas-zoom-bar__map',
	  top: '--top',
	  bottom: '--bottom',
	  left: '--left',
	  right: '--right'
	};
	const POSITION_MAP_DEFAULT_VALUES = 'top right';

	// @vue/component
	const ZoomBar = {
	  name: 'zoom-bar',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    ZoomBtn,
	    ZoomPercent,
	    CanvasMap,
	    CanvasMapBtn
	  },
	  props: {
	    stepZoom: {
	      type: Number,
	      default: 0.2
	    },
	    positionMap: {
	      type: String,
	      default: POSITION_MAP_DEFAULT_VALUES
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['update:modelValue'],
	  setup(props) {
	    const isShowMap = ui_vue3.ref(false);
	    const mapPositionClasses = ui_vue3.computed(() => {
	      const isTop = props.positionMap.toLowerCase().includes(GORIZONTAL_MAP_POSITION.top);
	      const isLeft = props.positionMap.toLowerCase().includes(VERTICAL_MAP_POSITION.left);
	      return {
	        [MAP_CLASSES.base]: true,
	        [MAP_CLASSES.top]: isTop,
	        [MAP_CLASSES.bottom]: !isTop,
	        [MAP_CLASSES.left]: isLeft,
	        [MAP_CLASSES.right]: !isLeft
	      };
	    });
	    function onToggleMap() {
	      if (props.disabled) {
	        isShowMap.value = false;
	        return;
	      }
	      isShowMap.value = !ui_vue3.toValue(isShowMap);
	    }
	    return {
	      iconSet: ui_iconSet_api_vue.Outline,
	      mapPositionClasses,
	      isShowMap,
	      onToggleMap
	    };
	  },
	  template: `
		<div class="ui-block-diagram-canvas-zoom-bar">
			<div class="ui-block-diagram-canvas-zoom-bar__locate">
				<CanvasMapBtn
					:data-test-id="$blockDiagramTestId('zoomOpenMapBtn')"
					@click="onToggleMap"
				/>
				<transition name="editor-large-map-fade" mode="in-out">
					<div
						v-if="isShowMap"
						class="ui-block-diagram-canvas-zoom-bar__map"
						:class="mapPositionClasses"
					>
						<div class="ui-block-diagram-canvas-zoom-bar__map-header">
							<BIcon
								:name="iconSet.CROSS_M"
								:size="24"
								:data-test-id="$blockDiagramTestId('zoomCloseMapBtn')"
								class="ui-block-diagram-canvas-zoom-bar__map-close-icon"
								color="#2FC6F6"
								@click="onToggleMap"
							/>
						</div>
						<CanvasMap
							:mapSize="310"
							:data-test-id="$blockDiagramTestId('zoomCanvasMap')"
						/>
					</div>
				</transition>
			</div>
			<div class="ui-block-diagram-canvas-zoom-bar__separator"/>
			<div class="ui-block-diagram-canvas-zoom-bar__zoom">
				<ZoomBtn
					:stepZoom="stepZoom"
					:disabled="disabled"
					:data-test-id="$blockDiagramTestId('zoomOutBtn')"
					typeZoom="out"
				/>
				<ZoomPercent/>
				<ZoomBtn
					:stepZoom="stepZoom"
					:disabled="disabled"
					:data-test-id="$blockDiagramTestId('zoomInBtn')"
					typeZoom="in"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const SearchResult = {
	  name: 'search-result',
	  props: {
	    title: {
	      type: String,
	      required: true
	    },
	    count: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="ui-block-diagram-search-result">
			<div class="ui-block-diagram-search-result__left-col">
				<p class="ui-block-diagram-search-result__title">{{ title }}</p>
			</div>
			<div class="ui-block-diagram-search-result__right-col">
				<span class="ui-block-diagram-search-result__count">{{ count }}</span>
				<div class="ui-block-diagram-search-result__nav">
					<slot/>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const SearchNavBtn = {
	  name: 'search-nav-btn',
	  template: `
		<button class="ui-block-diagram-search-nav-btn">
			<slot/>
		</button>
	`
	};

	// @vue/component
	const SearchInput = {
	  name: 'search-input',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    value: {
	      type: String,
	      default: ''
	    },
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    focusable: {
	      type: Boolean,
	      default: false
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emit: ['update:value', 'clear'],
	  setup(props, {
	    emit
	  }) {
	    const loc = useLoc();
	    const searchInput = ui_vue3.useTemplateRef('searchInput');
	    const placeholderOrDefaultValue = ui_vue3.computed(() => {
	      if (props.placeholder) {
	        return props.placeholder;
	      }
	      return loc.getMessage('UI_BLOCK_DIAGRAM_SEARCH_BAR_SEARCH_PLACEHOLDER');
	    });
	    ui_vue3.onMounted(() => {
	      if (ui_vue3.toValue(props.focusable)) {
	        ui_vue3.toValue(searchInput).focus();
	      }
	    });
	    function onInput(event) {
	      if (props.disabled) {
	        return;
	      }
	      emit('update:value', event.target.value);
	    }
	    function onClear() {
	      if (props.disabled) {
	        return;
	      }
	      emit('clear');
	    }
	    return {
	      iconSet: ui_iconSet_api_vue.Outline,
	      placeholderOrDefaultValue,
	      onInput,
	      onClear
	    };
	  },
	  template: `
		<div class="ui-block-diagram-search-input">
			<BIcon
				:name="iconSet.SEARCH"
				:size="24"
				class="ui-block-diagram-search-input__icon"
			/>
			<input
				:value="value"
				:placeholder="placeholderOrDefaultValue"
				:data-test-id="$blockDiagramTestId('searchInput')"
				ref="searchInput"
				type="text"
				class="ui-block-diagram-search-input__input"
				@input="onInput"
			/>
			<button
				class="ui-block-diagram-search-input__clear-btn"
				:data-test-id="$blockDiagramTestId('searchClearInputBtn')"
				@click="onClear"
			>
				<BIcon
					:name="iconSet.CROSS_L"
					:size="24"
					class="ui-block-diagram-search-input__clear-btn-icon"
				/>
			</button>
		</div>
	`
	};

	// @vue/component
	const OpenSearchBtn = {
	  name: 'open-search-btn',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  setup() {
	    return {
	      iconSet: ui_iconSet_api_vue.Outline
	    };
	  },
	  template: `
		<button class="ui-block-diagram-open-search-btn">
			<BIcon
				:name="iconSet.SEARCH"
				:size="24"
				class="ui-block-diagram-open-search-btn__icon"
			/>
		</button>
	`
	};

	// @vue/component
	const SearchBar = {
	  name: 'search-bar',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    SearchResult,
	    SearchNavBtn,
	    SearchInput,
	    OpenSearchBtn
	  },
	  props: {
	    searchResultTitle: {
	      type: String,
	      default: ''
	    },
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    searchCallback: {
	      type: Function,
	      required: true,
	      default: (block, text) => {
	        return block.node.title.toLowerCase().includes(text.toLowerCase());
	      }
	    },
	    searchDelay: {
	      type: Number,
	      default: 300
	    },
	    disabled: {
	      type: Boolean,
	      default: false
	    }
	  },
	  // eslint-disable-next-line max-lines-per-function
	  setup(props) {
	    const {
	      seachText,
	      foundBlocks,
	      onSearchBlocks,
	      onClearSearch
	    } = useSearchBlocks({
	      searchCallback: props.searchCallback,
	      delay: props.searchDelay
	    });
	    const {
	      isDisabledBlockDiagram
	    } = useBlockDiagram();
	    const highlitedBlocks = useHighlightedBlocks();
	    const loc = useLoc();
	    const {
	      goToBlockById
	    } = useCanvas();
	    const searchPanel = ui_vue3.useTemplateRef('searchPanel');
	    const isShowSearchBar = ui_vue3.ref(false);
	    const isShowOpenBtn = ui_vue3.ref(true);
	    const currentBlockIndex = ui_vue3.ref(0);
	    const isDisabled = ui_vue3.computed(() => {
	      return props.disabled || ui_vue3.toValue(isDisabledBlockDiagram);
	    });
	    const labelResult = ui_vue3.computed(() => {
	      return `${currentBlockIndex.value + 1} / ${ui_vue3.toValue(foundBlocks).length}`;
	    });
	    const placeholderOrDefaultValue = ui_vue3.computed(() => {
	      if (props.placeholder) {
	        return props.placeholder;
	      }
	      return loc.getMessage('UI_BLOCK_DIAGRAM_SEARCH_BAR_SEARCH_PLACEHOLDER');
	    });
	    const searchResultTitleOrDefaultValue = ui_vue3.computed(() => {
	      if (props.searchResultTitle) {
	        return props.searchResultTitle;
	      }
	      return loc.getMessage('UI_BLOCK_DIAGRAM_SEARCH_BAR_SEARCH_RESULT_TITLE');
	    });
	    ui_vue3.watch(foundBlocks, newBlocks => {
	      currentBlockIndex.value = 0;
	      if (ui_vue3.toValue(newBlocks).length > 0) {
	        const id = ui_vue3.toValue(newBlocks)[0].id;
	        highlitedBlocks.clear();
	        highlitedBlocks.add(id);
	        goToBlockById(id);
	      } else {
	        highlitedBlocks.clear();
	      }
	    });
	    ui_vue3.onMounted(() => {
	      main_core.Event.bind(document, 'mousedown', onClickOutside);
	    });
	    ui_vue3.onUnmounted(() => {
	      main_core.Event.unbind(document, 'mousedown', onClickOutside);
	    });
	    function onOpenSearchBar() {
	      if (ui_vue3.toValue(isDisabled)) {
	        return;
	      }
	      isShowOpenBtn.value = false;
	      isShowSearchBar.value = true;
	    }
	    function onLeaveTransition() {
	      isShowOpenBtn.value = true;
	    }
	    function onGoToNextBlock() {
	      if (ui_vue3.toValue(isDisabled)) {
	        return;
	      }
	      currentBlockIndex.value += 1;
	      if (ui_vue3.toValue(currentBlockIndex) > ui_vue3.toValue(foundBlocks).length - 1) {
	        currentBlockIndex.value = 0;
	      }
	      const id = ui_vue3.toValue(foundBlocks)[ui_vue3.toValue(currentBlockIndex)].id;
	      highlitedBlocks.clear();
	      highlitedBlocks.add(id);
	      goToBlockById(id);
	    }
	    function onGoToPrevBlock() {
	      if (ui_vue3.toValue(isDisabled)) {
	        return;
	      }
	      currentBlockIndex.value -= 1;
	      if (ui_vue3.toValue(currentBlockIndex) < 0) {
	        currentBlockIndex.value = ui_vue3.toValue(foundBlocks).length - 1;
	      }
	      const id = ui_vue3.toValue(foundBlocks)[ui_vue3.toValue(currentBlockIndex)].id;
	      highlitedBlocks.clear();
	      highlitedBlocks.add(id);
	      goToBlockById(ui_vue3.toValue(foundBlocks)[ui_vue3.toValue(currentBlockIndex)].id);
	    }
	    function closeAndResetSearch() {
	      highlitedBlocks.clear();
	      onClearSearch();
	      isShowSearchBar.value = false;
	      currentBlockIndex.value = 0;
	    }
	    function onClickOutside(event) {
	      if (ui_vue3.toValue(searchPanel) && !ui_vue3.toValue(searchPanel).contains(event.target)) {
	        closeAndResetSearch();
	      }
	    }
	    return {
	      iconSet: ui_iconSet_api_vue.Outline,
	      isDisabled,
	      placeholderOrDefaultValue,
	      searchResultTitleOrDefaultValue,
	      seachText,
	      labelResult,
	      isShowOpenBtn,
	      isShowSearchBar,
	      foundBlocks,
	      onOpenSearchBar,
	      onLeaveTransition,
	      onSearchBlocks,
	      onClearSearch,
	      closeAndResetSearch,
	      onGoToNextBlock,
	      onGoToPrevBlock
	    };
	  },
	  template: `
		<div
			:class="{ '--opened': isShowSearchBar }"
			class="ui-block-diagram-search-bar"
			ref="searchPanel"
		>
			<OpenSearchBtn
				v-if="isShowOpenBtn"
				:data-test-id="$blockDiagramTestId('searchOpenBtn')"
				@click="onOpenSearchBar"
			/>
			<transition
				name="ui-block-diagram-search-bar-fade"
				mode="in-out"
				@after-leave="onLeaveTransition"
			>
				<SearchInput
					v-if="isShowSearchBar"
					:value="seachText"
					:placeholder="placeholderOrDefaultValue"
					:disabled="isDisabled"
					focusable
					@update:value="onSearchBlocks"
					@clear="closeAndResetSearch"
				/>
			</transition>
			<div
				v-if="isShowSearchBar && foundBlocks.length > 0"
				class="ui-block-diagram-search-bar__search-result"
			>
				<SearchResult
					:title="searchResultTitleOrDefaultValue"
					:count="labelResult"
				>
					<SearchNavBtn
						:data-test-id="$blockDiagramTestId('searchResultPrevBtn')"
						@click="onGoToPrevBlock"
					>
						&lt;
					</SearchNavBtn>
					<SearchNavBtn
						:data-test-id="$blockDiagramTestId('searchResultNextBtn')"
						@click="onGoToNextBlock"
					>
						&gt;
					</SearchNavBtn>
				</SearchResult>
			</div>
		</div>
	`
	};

	// @vue/component
	const ResizableBlock = {
	  name: 'resizable-block',
	  props: {
	    /** @type DiagramBlock */
	    block: {
	      type: Object,
	      required: true
	    },
	    minWidth: {
	      type: Number,
	      default: 100
	    },
	    minHeight: {
	      type: Number,
	      default: 100
	    },
	    highlighted: {
	      type: Boolean,
	      default: false
	    }
	  },
	  setup(props) {
	    const {
	      block,
	      minWidth,
	      minHeight
	    } = ui_vue3.toRefs(props);
	    const {
	      isHiglitedBlock,
	      isDisabled
	    } = useBlockState(block);
	    const highlightedBlocks = useHighlightedBlocks();
	    const {
	      isDragged,
	      blockPositionStyle
	    } = useMoveableBlock(ui_vue3.useTemplateRef('blockEl'), block);
	    const {
	      isResize,
	      sizeBlockStyle,
	      onMounted: onMountedResizableBlock,
	      onUnmounted: onUnmountedResizableBlock
	    } = useResizableBlock({
	      block,
	      minWidth,
	      minHeight,
	      leftSideRef: ui_vue3.useTemplateRef('leftSide'),
	      topSideRef: ui_vue3.useTemplateRef('topSide'),
	      rightSideRef: ui_vue3.useTemplateRef('rightSide'),
	      bottomSideRef: ui_vue3.useTemplateRef('bottomSide'),
	      leftTopCornerRef: ui_vue3.useTemplateRef('leftTopCorner'),
	      rightTopCornerRef: ui_vue3.useTemplateRef('rightTopCorner'),
	      rightBottomCornerRef: ui_vue3.useTemplateRef('rightBottomCorner'),
	      leftBottomCornerRef: ui_vue3.useTemplateRef('leftBottomCorner')
	    });
	    const blockStyle = ui_vue3.computed(() => {
	      return {
	        ...ui_vue3.toValue(blockPositionStyle),
	        ...ui_vue3.toValue(sizeBlockStyle)
	      };
	    });
	    ui_vue3.watch(() => props.highlighted, value => {
	      if (value) {
	        highlightedBlocks.add(props.block.id);
	      } else {
	        highlightedBlocks.remove(props.block.id);
	      }
	    });
	    ui_vue3.onMounted(() => {
	      onMountedResizableBlock();
	    });
	    ui_vue3.onUnmounted(() => {
	      highlightedBlocks.remove(props.block.id);
	      onUnmountedResizableBlock();
	    });
	    function onMouseDownSelectBlock() {
	      highlightedBlocks.clear();
	      highlightedBlocks.add(props.block.id);
	    }
	    return {
	      isHiglitedBlock,
	      isDisabled,
	      isResize,
	      isDragged,
	      blockStyle,
	      onMouseDownSelectBlock
	    };
	  },
	  template: `
		<div
			:style="blockStyle"
			ref="blockEl"
			class="ui-block-diagram-resizable-block"
			@mousedown="onMouseDownSelectBlock"
		>
			<div class="ui-block-diagram-resizable-block__container">
				<div
					ref="leftSide"
					class="ui-block-diagram-resizable-block__left-side"
				/>
				<div
					ref="topSide"
					class="ui-block-diagram-resizable-block__top-side"
				/>
				<div
					ref="rightSide"
					class="ui-block-diagram-resizable-block__right-side"
				/>
				<div
					ref="bottomSide"
					class="ui-block-diagram-resizable-block__bottom-side"
				/>
				<div
					ref="leftTopCorner"
					class="ui-block-diagram-resizable-block__top-left-corner"
				/>
				<div
					ref="rightTopCorner"
					class="ui-block-diagram-resizable-block__top-right-corner"
				/>
				<div
					ref="rightBottomCorner"
					class="ui-block-diagram-resizable-block__bottom-right-corner"
				/>
				<div
					ref="leftBottomCorner"
					class="ui-block-diagram-resizable-block__bottom-left-corner"
				/>

				<slot
					:block="block"
					:isHighlighted="isHiglitedBlock"
					:isDragged="isDragged"
					:isResize="isResize"
					:isDisabled="isDisabled"
				/>
			</div>
		</div>
	`
	};

	let _ = t => t,
	  _t;
	let copiedDragItem = null;
	function onDragStart(event, value) {
	  const {
	    dragData,
	    dragImage
	  } = main_core.Type.isFunction(value) ? value() : value;
	  copiedDragItem = ui_vue3.toValue(dragImage).cloneNode(true).children[0];
	  const wrapper = document.getElementById('blockDiagramDragWrapper');
	  main_core.Dom.append(copiedDragItem, wrapper);
	  main_core.Dom.style(copiedDragItem, {
	    display: 'block',
	    position: 'absolute',
	    top: 0,
	    left: 0
	  });
	  const {
	    width,
	    height
	  } = copiedDragItem.getBoundingClientRect();
	  event.dataTransfer.setDragImage(copiedDragItem, width / 2, height / 2);
	  event.dataTransfer.setData('text/plain', JSON.stringify({
	    ...ui_vue3.toValue(dragData),
	    dimensions: {
	      width,
	      height
	    }
	  }));
	}
	function onDragEnd(event) {
	  main_core.Dom.remove(copiedDragItem);
	  copiedDragItem = null;
	}
	function initDragItemWrapper() {
	  const hasWrapper = document.getElementById('blockDiagramDragWrapper');
	  if (hasWrapper) {
	    return;
	  }
	  const wrapper = main_core.Tag.render(_t || (_t = _`
		<div>
			<div
				id="blockDiagramDragWrapper"
				style="position: relative; width: 100%; height: 100%;"
			>
			</div>
		</div>
	`));
	  main_core.Dom.append(wrapper, document.body);
	  main_core.Dom.style(wrapper, {
	    position: 'absolute',
	    transform: 'translate(-100%, -100%)',
	    top: 0,
	    right: 0
	  });
	}
	const DragBlock = {
	  mounted(el, {
	    arg,
	    value
	  }) {
	    initDragItemWrapper();
	    main_core.Dom.attr(el, 'draggable', 'true');
	    main_core.Event.bind(el, 'dragstart', event => onDragStart(event, value));
	    main_core.Event.bind(el, 'dragend', event => onDragEnd());
	  },
	  unmounted(el, {
	    arg,
	    value
	  }) {
	    main_core.Event.unbind(el, 'dragstart', event => onDragStart(event, value));
	    main_core.Event.unbind(el, 'dragend', event => onDragEnd(event));
	  }
	};

	exports.BlockDiagram = BlockDiagram;
	exports.HistoryBar = HistoryBar;
	exports.ZoomBar = ZoomBar;
	exports.SearchBar = SearchBar;
	exports.MoveableBlock = MoveableBlock;
	exports.ResizableBlock = ResizableBlock;
	exports.Port = Port;
	exports.transformPoint = transformPoint;
	exports.useBlockDiagram = useBlockDiagram;
	exports.useContextMenu = useContextMenu;
	exports.useHistory = useHistory;
	exports.useSearchBlocks = useSearchBlocks;
	exports.useCanvas = useCanvas;
	exports.useBlockState = useBlockState;
	exports.useMoveableBlock = useMoveableBlock;
	exports.useResizableBlock = useResizableBlock;
	exports.useHighlightedBlocks = useHighlightedBlocks;
	exports.useAnimationQueue = useAnimationQueue;
	exports.usePortState = usePortState;
	exports.useConnectionState = useConnectionState;
	exports.useNewConnectionState = useNewConnectionState;
	exports.useDragAndDrop = useDragAndDrop;
	exports.DragBlock = DragBlock;

}((this.BX.UI = this.BX.UI || {}),BX.Main,BX,BX.UI.IconSet,BX,BX.Vue3));
//# sourceMappingURL=block-diagram.bundle.js.map
