export function getSelectionRect(nativeSelection: Selection): DOMRect | null
{
	let rect = nativeSelection.getRangeAt(0).getBoundingClientRect();
	rect = rect && rect.top ? rect : nativeSelection.getRangeAt(0).getClientRects()[0];
	if (!rect)
	{
		if (nativeSelection.anchorNode && nativeSelection.anchorNode.getBoundingClientRect)
		{
			rect = nativeSelection.anchorNode.getBoundingClientRect();
		}
		else
		{
			return null;
		}
	}

	return rect;
}
