import { Dom, Type } from 'main.core';
import { $getSelection, $isRangeSelection } from 'ui.lexical.core';
import { $getSelectionPosition } from './get-selection-position';

import { type Popup } from 'main.popup';
import { type TextEditor } from '../text-editor';

const lastPositionMap: WeakMap<Popup, 'top' | 'bottom'> = new WeakMap();
const editorPadding = 16;

export function $adjustDialogPosition(
	popup: Popup,
	editor: TextEditor,
	initPosition?: (selectionPosition: Object) => {},
): boolean
{
	const selection = $getSelection();
	if (!$isRangeSelection(selection))
	{
		return false;
	}

	// for an embedded popup: document.body -> editor.getScrollerContainer()
	const selectionPosition = $getSelectionPosition(editor, selection, document.body);
	if (selectionPosition === null)
	{
		return false;
	}

	const { top, left, bottom, isBackward } = selectionPosition;
	const scrollerRect: DOMRect = Dom.getPosition(editor.getScrollerContainer());
	const popupRect: DOMRect = Dom.getPosition(popup.getPopupContainer());

	const popupWidth: number = popupRect.width;
	let offsetLeft: number = popupWidth / 2;

	// Try to fit a popup within a scroll area
	if (left - offsetLeft < scrollerRect.left)
	{
		// Left boundary
		const overflow = scrollerRect.left - (left - offsetLeft);
		offsetLeft -= overflow + editorPadding;
	}
	else if (scrollerRect.right < (left + popupWidth - offsetLeft))
	{
		// Right boundary
		offsetLeft += (left + popupWidth - offsetLeft) - scrollerRect.right + editorPadding;
	}

	popup.setOffset({ offsetLeft: -offsetLeft });

	if (bottom < scrollerRect.top || top > scrollerRect.bottom)
	{
		// hide our popup
		Dom.style(popup.getPopupContainer(), { left: '-9999px', top: '-9999px' });
	}
	else
	{
		const initialPosition = Type.isFunction(initPosition) ? initPosition(selectionPosition) : (isBackward ? 'top' : 'bottom');
		const lastPosition = lastPositionMap.get(popup) || null;
		let position = lastPosition === null ? initialPosition : lastPosition;
		if (top + popupRect.height > scrollerRect.bottom && (scrollerRect.top < top - popupRect.height))
		{
			position = 'top';
		}
		else if (top - popupRect.height < scrollerRect.top)
		{
			position = 'bottom';
		}

		lastPositionMap.set(popup, position);

		popup.setBindElement({ left, top, bottom });
		popup.adjustPosition({ position, forceBindPosition: true });
	}

	return true;
}

export function clearDialogPosition(popup: Popup)
{
	lastPositionMap.delete(popup);
}
