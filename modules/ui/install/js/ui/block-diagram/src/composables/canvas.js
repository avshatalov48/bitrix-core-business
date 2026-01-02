import { toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import type { DiagramBlockId } from '../types';

export type UseCanvas = {
	zoomIn: (zoomStep: number) => void,
	zoomOut: (zoomStep: number) => void,
	setCamera: (params: { x: number, y: number, zoom: number}) => void,
	goToBlockById: (id: DiagramBlockId) => void,
};

export function useCanvas(): UseCanvas
{
	const {
		zoom,
		blocks,
		canvasWidth,
		canvasHeight,
		blockDiagramTop,
		blockDiagramLeft,
		canvasInstance,
	} = useBlockDiagram();

	function zoomIn(zoomStep: number): void
	{
		toValue(canvasInstance)?.zoomIn(zoomStep);
	}

	function zoomOut(zoomStep: number): void
	{
		toValue(canvasInstance)?.zoomOut(zoomStep);
	}

	function setCamera(params: { x: number, y: number, zoom: number}): void
	{
		toValue(canvasInstance)?.setCamera(params);
	}

	function goToBlockById(id: DiagramBlockId): void
	{
		const block = toValue(blocks).find((block) => block.id === id);

		if (!block)
		{
			return;
		}

		const { x, y } = block.position;
		const { width, height } = block.dimensions;
		const centerX = x + (width / 2);
		const centerY = y + (height / 2);

		setCamera({
			x: centerX - (toValue(canvasWidth) / 2 / toValue(zoom)) - toValue(blockDiagramLeft) / toValue(zoom),
			y: centerY - (toValue(canvasHeight) / 2 / toValue(zoom)) - toValue(blockDiagramTop) / toValue(zoom),
		});
	}

	return {
		zoomIn,
		zoomOut,
		setCamera,
		goToBlockById,
	};
}
