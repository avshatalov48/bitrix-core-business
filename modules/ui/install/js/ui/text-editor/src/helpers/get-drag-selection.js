interface DragEvent {
	rangeOffset?: number;
	rangeParent?: Node;
}

export function getDragSelection(event: DragEvent): Range | null
{
	const target: Element | Document | null = event.target;
	let targetWindow = null;
	if (target !== null)
	{
		targetWindow = target.nodeType === 9 ? target.defaultView : target.ownerDocument.defaultView;
	}

	let range = null;
	const domSelection = (targetWindow || window).getSelection();
	if (document.caretRangeFromPoint)
	{
		range = document.caretRangeFromPoint(event.clientX, event.clientY);
	}
	else if (event.rangeParent && domSelection !== null)
	{
		domSelection.collapse(event.rangeParent, event.rangeOffset || 0);
		range = domSelection.getRangeAt(0);
	}
	else
	{
		throw new Error('Cannot get the selection when dragging');
	}

	return range;
}
