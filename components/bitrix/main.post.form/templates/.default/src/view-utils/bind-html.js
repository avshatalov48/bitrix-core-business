import {EventEmitter} from 'main.core.events';
import Editor from "../editor";

export default function bindHTML(editor: Editor)
{
	const submitButton = document.querySelector('#lhe_button_submit_' + editor.getFormId());
	if (submitButton)
	{
		submitButton.addEventListener('click', function(event) {
			EventEmitter.emit(editor.getEventObject(), 'OnButtonClick', ['submit']);
			event.preventDefault();
			event.stopPropagation();
		});
	}
	const cancelButton = document.querySelector('#lhe_button_cancel_' + editor.getFormId());
	if (cancelButton)
	{
		cancelButton.addEventListener('click', function(event) {
			EventEmitter.emit(editor.getEventObject(), 'OnButtonClick', ['cancel']);
			event.preventDefault();
			event.stopPropagation();
		});
	}
}