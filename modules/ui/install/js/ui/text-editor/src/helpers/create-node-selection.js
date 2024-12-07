import {
	$getSelection,
	$getNodeByKey,
	$isNodeSelection,
	$createNodeSelection,
	$setSelection,
	type NodeKey,
	type NodeSelection,
} from 'ui.lexical.core';

import { type TextEditor } from '../text-editor';

function isNodeSelected(editor: TextEditor, key: NodeKey): boolean
{
	return editor.getEditorState().read(() => {
		const node = $getNodeByKey(key);
		if (node === null)
		{
			return false;
		}

		return node.isSelected();
	});
}

export function createNodeSelection(editor: TextEditor, key: NodeKey)
{
	let isSelected = false;
	const subscribers = new Set();
	const onSelect = (fn: Function) => {
		subscribers.add(fn);
	};

	const unregisterListener = editor.registerUpdateListener(() => {
		isSelected = isNodeSelected(editor, key);
		for (const subscribeFunc of subscribers)
		{
			subscribeFunc(isSelected);
		}
	});

	const setSelected = (selected: boolean) => {
		editor.update(() => {
			let selection: NodeSelection = $getSelection();
			if (!$isNodeSelection(selection))
			{
				selection = $createNodeSelection();
				$setSelection(selection);
			}

			if (selected)
			{
				selection.add(key);
			}
			else
			{
				selection.delete(key);
			}
		});
	};

	const clearSelection = () => {
		editor.update(() => {
			const selection: NodeSelection = $getSelection();
			if ($isNodeSelection(selection))
			{
				selection.clear();
			}
		});
	};

	return {
		isSelected: () => {
			return isSelected;
		},
		dispose: () => {
			unregisterListener();
		},
		onSelect,
		setSelected,
		clearSelection,
	};
}
