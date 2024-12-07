export function getDOMRangeRect(nativeSelection: Selection, rootElement: HTMLElement): DOMRect
{
	const domRange = nativeSelection.getRangeAt(0);

	let rect = null;
	if (nativeSelection.anchorNode === rootElement)
	{
		let inner = rootElement;
		while (inner.firstElementChild !== null)
		{
			inner = inner.firstElementChild;
		}

		rect = inner.getBoundingClientRect();
	}
	else
	{
		rect = domRange.getBoundingClientRect();
	}

	return rect;
}
