import type { Point, DiagramPortPosition } from '../types';
import { PORT_POSITION } from '../constants';

const DIR_ACCESSOR_X = 'x';
const DIR_ACCESSOR_Y = 'x';

const DIRECTIONS_BY_POSITION: { [DiagramPortPosition]: Point } = {
	[PORT_POSITION.LEFT]: { x: -1, y: 0 },
	[PORT_POSITION.RIGHT]: { x: 1, y: 0 },
	[PORT_POSITION.TOP]: { x: 0, y: -1 },
	[PORT_POSITION.BOTTOM]: { x: 0, y: 1 },
};

export type PathInfo = {
	path: string;
	center: {
		x: number;
		y: number;
	}
};

export function getLinePath(start: Point, end: Point): PathInfo
{
	const [x, y] = getConnectionCenter({
		sourceX: start.x,
		sourceY: start.y,
		targetX: end.x,
		targetY: end.y,
	});

	return {
		path: `M ${start.x} ${start.y} L ${end.x} ${end.y}`,
		center: { x, y },
	};
}

export function getBeziePath(start: Point, end: Point): PathInfo
{
	const midX: number = (start.x + end.x) / 2;
	const [centerX, centerY] = getConnectionCenter({
		sourceX: start.x,
		sourceY: start.y,
		targetX: end.x,
		targetY: end.y,
	});

	return {
		path: `M ${start.x} ${start.y} C ${midX} ${start.y}, ${midX} ${end.y}, ${end.x} ${end.y}`,
		center: {
			x: centerX,
			y: centerY,
		},
	};
}

export function transformPoint(point: Point, transform, viewport): Point
{
	let transformedX: number = Math.round((point.x - transform.x) / transform.zoom);
	let transformedY: number = Math.round((point.y - transform.y) / transform.zoom);

	transformedX -= Math.round(viewport.left / transform.zoom);
	transformedY -= Math.round(viewport.top / transform.zoom);

	return {
		x: transformedX,
		y: transformedY,
	};
}

export function getConnectionCenter({
	sourceX,
	sourceY,
	targetX,
	targetY,
}: {
	sourceX: number,
	sourceY: number,
	targetX: number,
	targetY: number,
}): [number, number, number, number]
{
	const xOffset = Math.abs(targetX - sourceX) / 2;
	const centerX = targetX < sourceX
		? targetX + xOffset
		: targetX - xOffset;

	const yOffset = Math.abs(targetY - sourceY) / 2;
	const centerY = targetY < sourceY
		? targetY + yOffset
		: targetY - yOffset;

	return [centerX, centerY, xOffset, yOffset];
}

function getDirection({ source, sourcePosition = PORT_POSITION.BOTTOM, target }: {
	source: XYPosition,
	sourcePosition: Position,
	target: XYPosition,
}): XYPosition
{
	if (sourcePosition === PORT_POSITION.LEFT || sourcePosition === PORT_POSITION.RIGHT)
	{
		return source.x < target.x
			? { x: 1, y: 0 }
			: { x: -1, y: 0 };
	}

	return source.y < target.y
		? { x: 0, y: 1 }
		: { x: 0, y: -1 };
}

function distance(a: Point, b: Point): number
{
	return Math.sqrt((b.x - a.x) ** 2 + (b.y - a.y) ** 2);
}

export type GetPointsParams = {
	source: Point;
	sourcePosition: DiagramPortPosition;
	target: Point;
}

// eslint-disable-next-line max-lines-per-function
function getPoints({
	source,
	sourcePosition = PORT_POSITION.BOTTOM,
	target,
	targetPosition = PORT_POSITION.TOP,
	center,
	offset,
}: {
  source: Point,
  sourcePosition: DiagramPortPosition,
  target: Point,
  targetPosition: DiagramPortPosition,
  center: Partial<Point>,
  offset: number,
}): [Point[], number, number, number, number]
{
	const sourceDir = DIRECTIONS_BY_POSITION[sourcePosition];
	const targetDir = DIRECTIONS_BY_POSITION[targetPosition];
	const sourceGapped: Point = {
		x: source.x + sourceDir.x * offset,
		y: source.y + sourceDir.y * offset,
	};
	const targetGapped: Point = {
		x: target.x + targetDir.x * offset,
		y: target.y + targetDir.y * offset,
	};
	const dir = getDirection({
		source: sourceGapped,
		sourcePosition,
		target: targetGapped,
	});
	const dirAccessor = dir.x !== 0
		? DIR_ACCESSOR_X
		: DIR_ACCESSOR_Y;
	const currDir = dir[dirAccessor];

	let points: Point[] = [];
	let centerX = 0;
	let centerY = 0;

	const sourceGapOffset: Point = { x: 0, y: 0 };
	const targetGapOffset: Point = { x: 0, y: 0 };

	const [
		defaultCenterX,
		defaultCenterY,
		defaultOffsetX,
		defaultOffsetY,
	] = getConnectionCenter({
		sourceX: source.x,
		sourceY: source.y,
		targetX: target.x,
		targetY: target.y,
	});

	if (sourceDir[dirAccessor] * targetDir[dirAccessor] === -1)
	{
		centerX = center.x ?? defaultCenterX;
		centerY = center.y ?? defaultCenterY;

		const verticalSplit: Point[] = [
			{ x: centerX, y: sourceGapped.y },
			{ x: centerX, y: targetGapped.y },
		];

		const horizontalSplit: Point[] = [
			{ x: sourceGapped.x, y: centerY },
			{ x: targetGapped.x, y: centerY },
		];

		if (sourceDir[dirAccessor] === currDir)
		{
			points = dirAccessor === DIR_ACCESSOR_X
				? verticalSplit
				: horizontalSplit;
		}
		else
		{
			points = dirAccessor === DIR_ACCESSOR_X
				? horizontalSplit
				: verticalSplit;
		}
	}
	else
	{
		const sourceTarget: Point[] = [{
			x: sourceGapped.x,
			y: targetGapped.y,
		}];
		const targetSource: Point[] = [{
			x: targetGapped.x,
			y: sourceGapped.y,
		}];

		if (dirAccessor === DIR_ACCESSOR_X)
		{
			points = sourceDir.x === currDir
				? targetSource
				: sourceTarget;
		}
		else
		{
			points = sourceDir.y === currDir
				? sourceTarget
				: targetSource;
		}

		if (sourcePosition === targetPosition)
		{
			const diff = Math.abs(source[dirAccessor] - target[dirAccessor]);

			if (diff <= offset)
			{
				const gapOffset = Math.min(offset - 1, offset - diff);

				if (sourceDir[dirAccessor] === currDir)
				{
					const dirSource = sourceGapped[dirAccessor] > source[dirAccessor] ? -1 : 1;
					sourceGapOffset[dirAccessor] = dirSource * gapOffset;
				}
				else
				{
					const dirTarget = targetGapped[dirAccessor] > target[dirAccessor] ? -1 : 1;
					targetGapOffset[dirAccessor] = dirTarget * gapOffset;
				}
			}
		}

		if (sourcePosition !== targetPosition)
		{
			const dirAccessorOpposite = dirAccessor === DIR_ACCESSOR_X
				? DIR_ACCESSOR_Y
				: DIR_ACCESSOR_X;
			const isSameDir = sourceDir[dirAccessor] === targetDir[dirAccessorOpposite];
			const sourceGtTargetOppo = sourceGapped[dirAccessorOpposite] > targetGapped[dirAccessorOpposite];
			const sourceLtTargetOppo = sourceGapped[dirAccessorOpposite] < targetGapped[dirAccessorOpposite];
			const isFlipSourceTarget =
				(sourceDir[dirAccessor] === 1 && ((!isSameDir && sourceGtTargetOppo) || (isSameDir && sourceLtTargetOppo))) ||
				(sourceDir[dirAccessor] !== 1 && ((!isSameDir && sourceLtTargetOppo) || (isSameDir && sourceGtTargetOppo)));

			if (isFlipSourceTarget)
			{
				points = dirAccessor === DIR_ACCESSOR_X
					? sourceTarget
					: targetSource;
			}
		}

		const sourceGapPoint = {
			x: sourceGapped.x + sourceGapOffset.x,
			y: sourceGapped.y + sourceGapOffset.y,
		};
		const targetGapPoint = {
			x: targetGapped.x + targetGapOffset.x,
			y: targetGapped.y + targetGapOffset.y,
		};
		const maxXDistance = Math.max(
			Math.abs(sourceGapPoint.x - points[0].x),
			Math.abs(targetGapPoint.x - points[0].x),
		);
		const maxYDistance = Math.max(
			Math.abs(sourceGapPoint.y - points[0].y),
			Math.abs(targetGapPoint.y - points[0].y),
		);

		if (maxXDistance >= maxYDistance)
		{
			centerX = (sourceGapPoint.x + targetGapPoint.x) / 2;
			centerY = points[0].y;
		}
		else
		{
			centerX = points[0].x;
			centerY = (sourceGapPoint.y + targetGapPoint.y) / 2;
		}
	}

	const pathPoints: Point[] = [
		source,
		{
			x: sourceGapped.x + sourceGapOffset.x,
			y: sourceGapped.y + sourceGapOffset.y,
		},
		...points,
		{
			x: targetGapped.x + targetGapOffset.x,
			y: targetGapped.y + targetGapOffset.y,
		},
		target,
	];

	return {
		points: pathPoints,
		offsetX: defaultOffsetX,
		offsetY: defaultOffsetY,
		centerX,
		centerY,
	};
}

function getBend(
	a: Point,
	b: Point,
	c: Point,
	size: number,
): string
{
	const bendSize = Math.min(
		distance(a, b) / 2,
		distance(b, c) / 2,
		size,
	);
	const { x, y } = b;

	if ((a.x === x && x === c.x) || (a.y === y && y === c.y))
	{
		return `L${x} ${y}`;
	}

	if (a.y === y)
	{
		const xDir = a.x < c.x ? -1 : 1;
		const yDir = a.y < c.y ? 1 : -1;

		return `L ${x + bendSize * xDir},${y}Q ${x},${y} ${x},${y + bendSize * yDir}`;
	}

	const xDir = a.x < c.x ? 1 : -1;
	const yDir = a.y < c.y ? -1 : 1;

	return `L ${x},${y + bendSize * yDir}Q ${x},${y} ${x + bendSize * xDir},${y}`;
}

export type GetSmoothStepPathParams = {
	sourceX: number;
	sourceY: number;
	sourcePosition?: DiagramPortPosition;
	targetX: number;
	targetY: number;
	targetPosition?: DiagramPortPosition;
	borderRadius?: number;
	centerX?: number;
	centerY?: number;
	offset?: number;
};

export function getSmoothStepPath(params: GetSmoothStepPathParams): PathInfo
{
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
		offset = 20,
	} = params;

	const {
		points,
		centerX: pointsCenterX,
		centerY: pointsCenterY,
	} = getPoints({
		source: { x: sourceX, y: sourceY },
		sourcePosition,
		target: { x: targetX, y: targetY },
		targetPosition,
		center: { x: centerX, y: centerY },
		offset,
	});

	const path = points.reduce((res, p, i) => {
		let segment = '';

		if (i > 0 && i < points.length - 1)
		{
			segment = getBend(points[i - 1], p, points[i + 1], borderRadius);
		}
		else
		{
			segment = `${i === 0 ? 'M' : 'L'}${p.x} ${p.y}`;
		}

		res += segment;

		return res;
	}, '');

	return {
		path,
		center: {
			x: pointsCenterX,
			y: pointsCenterY,
		},
	};
}
