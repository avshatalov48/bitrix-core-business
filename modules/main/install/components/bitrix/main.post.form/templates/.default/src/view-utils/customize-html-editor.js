import {Editor} from '../editor';
import {Loc} from 'main.core';

export default function customizeHTMLEditor(editor: Editor, htmlEditor)
{
	editor.exec(() => {
		// Contextmenu changing for images/files
		htmlEditor.contextMenu.items['postimage'] =
			htmlEditor.contextMenu.items['postdocument'] =
				htmlEditor.contextMenu.items['postfile'] =
					[
						{
							TEXT: Loc.getMessage('BXEdDelFromText'),
							bbMode: true,
							ACTION: function()
							{
								var node = htmlEditor.contextMenu.GetTargetItem('postimage');
								if (!node)
									node = htmlEditor.contextMenu.GetTargetItem('postdocument');
								if (!node)
									node = htmlEditor.contextMenu.GetTargetItem('postfile');

								if (node && node.element)
								{
									htmlEditor.selection.RemoveNode(node.element);
								}
								htmlEditor.contextMenu.Hide();
							}
						}
					];
		if (htmlEditor.toolbar.controls && htmlEditor.toolbar.controls.FontSelector)
		{
			htmlEditor.toolbar.controls.FontSelector.SetWidth(45);
		}
	});
}