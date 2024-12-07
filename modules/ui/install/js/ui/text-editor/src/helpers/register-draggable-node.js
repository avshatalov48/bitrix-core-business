import { Tag, Type } from 'main.core';
import {
	$createRangeSelection, $setSelection,
	COMMAND_PRIORITY_HIGH,
	COMMAND_PRIORITY_LOW,
	DRAGOVER_COMMAND,
	DRAGSTART_COMMAND,
	DROP_COMMAND,
	type LexicalNode,
} from 'ui.lexical.core';

import { mergeRegister } from 'ui.lexical.utils';
import { DRAG_START_COMMAND, DRAG_END_COMMAND } from '../commands';

import { getDragSelection } from './get-drag-selection';
import { getNodeInSelection } from './get-node-in-selection';

import { type TextEditor } from '../text-editor';

const DRAG_DATA_FORMAT = 'application/x-lexical-drag-image';
const TRANSPARENT_IMAGE = Tag.render`<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">`;

export function registerDraggableNode(
	editor: TextEditor,
	targetNode: Class<LexicalNode>,
	onDrop: Function,
): () => void
{
	const isTargetNode = (node: LexicalNode | null | undefined): boolean => {
		return node instanceof targetNode;
	};

	const getDraggableNode = (): LexicalNode | null => {
		return getNodeInSelection((node: LexicalNode) => isTargetNode(node));
	};

	return mergeRegister(
		editor.registerCommand(
			DRAGSTART_COMMAND,
			(event: DragEvent): boolean => {
				const draggableNode: LexicalNode = getDraggableNode();
				if (!draggableNode)
				{
					return false;
				}

				const success = handleDragStart(event, draggableNode);
				if (success)
				{
					editor.dispatchCommand(DRAG_START_COMMAND);
				}

				return success;
			},
			COMMAND_PRIORITY_HIGH,
		),

		editor.registerCommand(
			DRAGOVER_COMMAND,
			(event: DragEvent): boolean => {
				const draggableNode: LexicalNode = getDraggableNode();
				if (!draggableNode)
				{
					return false;
				}

				return handleDragOver(event, editor);
			},
			COMMAND_PRIORITY_LOW,
		),

		editor.registerCommand(
			DROP_COMMAND,
			(event: DragEvent): boolean => {
				const draggableNode: LexicalNode = getDraggableNode();
				if (!draggableNode)
				{
					return false;
				}

				editor.dispatchCommand(DRAG_END_COMMAND);

				return handleDragDrop(event, editor, draggableNode, onDrop);
			},
			COMMAND_PRIORITY_HIGH,
		),
	);
}

function handleDragStart(event: DragEvent, draggableNode: LexicalNode): boolean
{
	const dataTransfer = event.dataTransfer;
	if (!dataTransfer)
	{
		return false;
	}

	dataTransfer.setData('text/plain', '_');
	dataTransfer.setDragImage(TRANSPARENT_IMAGE, 0, 0);
	dataTransfer.setData(
		DRAG_DATA_FORMAT,
		JSON.stringify({
			data: draggableNode.exportJSON(),
			type: draggableNode.getType(),
		}),
	);

	return true;
}

function handleDragOver(event: DragEvent, editor: TextEditor): boolean
{
	if (!canDrop(event, editor))
	{
		event.preventDefault();
	}

	return true;
}

function handleDragDrop(
	event: DragEvent,
	editor: TextEditor,
	draggableNode: LexicalNode,
	onDrop: Function,
): boolean
{
	const dragData = event.dataTransfer?.getData(DRAG_DATA_FORMAT);
	if (!dragData)
	{
		return false;
	}

	const { type, data } = JSON.parse(dragData);
	if (type !== draggableNode.getType() || !Type.isPlainObject(data))
	{
		return false;
	}

	event.preventDefault();
	if (canDrop(event, editor) && Type.isFunction(onDrop))
	{
		const range = getDragSelection(event);
		draggableNode.remove();
		const rangeSelection = $createRangeSelection();
		if (range !== null && range !== undefined)
		{
			rangeSelection.applyDOMRange(range);
		}

		$setSelection(rangeSelection);

		onDrop(data);
	}

	return true;
}

function canDrop(event: DragEvent, editor: TextEditor): boolean
{
	const target = event.target;
	const selectors = ['code', '.ui-text-editor__file-image'];
	const imageClassName = editor.getThemeClass('image');
	if (Type.isStringFilled(imageClassName))
	{
		selectors.push(`.${imageClassName}`);
	}

	// editor.getBBCodeScheme().isAllowedTag();

	return (
		target instanceof HTMLElement
		&& target.closest(selectors.join(',')) === null
		&& editor.getEditableContainer().contains(target.parentElement)
	);
}
