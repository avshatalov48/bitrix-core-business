import {EventEmitter, BaseEvent} from 'main.core.events';
import {Editor} from '../editor';

export default function bindToolbar(editor: Editor, htmlEditor)
{
	const toolbar = editor.getContainer().querySelector('[data-bx-role="toolbar"]')
	if (toolbar.querySelector('[data-id="file"]'))
	{
		const fileButton = toolbar.querySelector('[data-id="file"]');
		if (fileButton)
		{
			fileButton.addEventListener('click', () => {
				EventEmitter.emit(editor.getEventObject(), 'onShowControllers', fileButton.hasAttribute('data-bx-button-status')
					? 'hide' : 'show');
			});
			EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', ({data}) => {
				if (data.toString() === 'show')
				{
					fileButton.setAttribute('data-bx-button-status', 'active');
				}
				else
				{
					fileButton.removeAttribute('data-bx-button-status');
				}
			});
			fileButton.setAttribute('data-bx-files-count', 0);
			EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers:File:Increment', ({data}) => {
				const count = data > 0 ? data : 1;
				const filesCount = Math.max(parseInt(fileButton.getAttribute('data-bx-files-count') || 0) + count, 0);
				if (filesCount > 0)
				{
					if (!fileButton['counterObject'])
					{
						fileButton['counterObject'] = new BX.UI.Counter({
							value: filesCount,
							color: BX.UI.Counter.Color.GRAY,
							animate: true
						});
						const container = fileButton.querySelector('span');
						container.appendChild(fileButton['counterObject'].getContainer());
					}
					else
					{
						fileButton['counterObject'].update(filesCount);
					}
				}
				fileButton.setAttribute('data-bx-files-count', filesCount);
			});
			EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers:File:Decrement', ({data}) => {
				const count = data > 0 ? data : 1;
				const filesCount = Math.max(parseInt(fileButton.getAttribute('data-bx-files-count') || 0) - count, 0);
				fileButton.setAttribute('data-bx-files-count', filesCount);

				if (fileButton['counterObject'])
				{
					fileButton['counterObject'].update(filesCount);
				}
			});
		}
	}

	if (toolbar.querySelector('[data-id="search-tag"]'))
	{
		window['BXPostFormTags_' + editor.getFormId()] = new BXPostFormTags(
			editor.getFormId(),
			toolbar.querySelector('[data-id="search-tag"]')
		);
	}

	if (toolbar.querySelector('[data-id="create-link"]'))
	{
		toolbar.querySelector('[data-id="create-link"]').addEventListener('click', (event) => {
			htmlEditor.toolbar.controls.InsertLink.OnClick(event);
		});
	}

	if (toolbar.querySelector('[data-id="video"]'))
	{
		toolbar.querySelector('[data-id="video"]').addEventListener('click', (event) => {
			htmlEditor.toolbar.controls.InsertVideo.OnClick(event);
		});
	}

	if (toolbar.querySelector('[data-id="quote"]'))
	{
		const quoteNode = toolbar.querySelector('[data-id="quote"]');
		quoteNode.setAttribute('data-bx-type', 'action');
		quoteNode.setAttribute('data-bx-action', 'quote');


		quoteNode.addEventListener('mousedown', (event) => {
			htmlEditor.toolbar.controls.Quote.OnMouseDown.apply(htmlEditor.toolbar.controls.Quote, [event]);
			htmlEditor.CheckCommand(quoteNode);
		});
	}

	if (editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]'))
	{
		editor.getContainer().querySelector('[data-bx-role="button-show-panel-editor"]')
			.addEventListener('click', () => {
				editor.showPanelEditor();
			});
	}
}
