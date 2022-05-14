import Editor from '../editor';

export default function showPanelEditor(editor: Editor, htmlEditor, editorParams)
{
	let save = false;
	if (
		editorParams.showPanelEditor !== true
		&& editorParams.showPanelEditor !== false
	)
	{
		editorParams.showPanelEditor = !htmlEditor.toolbar.IsShown();
		save = true;
	}

	editor.exec(() => {
		const buttonNode = editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]');

		if (editorParams.showPanelEditor)
		{
			htmlEditor.dom.toolbarCont.style.opacity = 'inherit';
			htmlEditor.toolbar.Show();

			if (buttonNode)
			{
				buttonNode.classList.add('feed-add-post-form-btn-active');
			}
		}
		else
		{
			htmlEditor.toolbar.Hide();

			if (buttonNode)
			{
				buttonNode.classList.remove('feed-add-post-form-btn-active');
			}
		}
	});

	if (save !== false)
	{
		BX.userOptions.save('main.post.form', 'postEdit', 'showBBCode', editorParams.showPanelEditor ? 'Y' : 'N');
	}
}