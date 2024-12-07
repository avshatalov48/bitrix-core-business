export function calcImageSize(previewWidth, previewHeight, renderWidth, renderHeight): [number, number]
{
	const ratioWidth: number = renderWidth / previewWidth;
	const ratioHeight: number = renderHeight / previewHeight;
	const ratio: number = Math.min(ratioWidth, ratioHeight);

	const useOriginalSize = ratio > 1; // image is too small
	const width = useOriginalSize ? previewWidth : previewWidth * ratio;
	const height = useOriginalSize ? previewHeight : previewHeight * ratio;

	return [width, height];
}
