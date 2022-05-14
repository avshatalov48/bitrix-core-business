import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Editor from "./editor";
import TasksLimit from "./taskslimit";

	window['LHEPostForm'] = {

		//region compatibility
		getEditor: function(editor)
		{
			return (window["BXHtmlEditor"] ? window["BXHtmlEditor"].Get((typeof editor == "object" ? editor.id : editor)) : null);
		},

		getHandler: function(editor)
		{
			const id = Type.isStringFilled(editor) ? editor : editor.id;
			return Editor.repo.get(id);
		},

		getHandlerByFormId: function(formId)
		{
			let result = null;
			Editor.repo.forEach((editor) => {
				if (editor.getFormId() === formId)
				{
					result = editor;
				}
			});
			return result;
		},

		reinitData: function(editorID, text, data)
		{
			const files = {};
			if (!Type.isPlainObject(data))
			{
				data = {};
			}

			Object.entries(data).forEach(([userFieldName, userField]) => {
				if (Type.isPlainObject(userField)
					&& userField['USER_TYPE_ID']
					&& userField['VALUE']
					&& Object.values(userField['VALUE']).length > 0
				)
				{
					files[userFieldName] = userField;
				}
			});

			const handler = this.getHandler(editorID);
			if (handler && (handler.isReady || Type.isStringFilled(text) || Object.values(files).length > 0))
			{
				handler.exec(handler.reinit, [text, files]);
			}
			return false;
		},

		reinitDataBefore: function(editorID)
		{
			const handler = Editor.repo.get(editorID);
			if (handler && handler.getEventObject())
			{
				EventEmitter.emit(handler.getEventObject(), 'onReinitializeBefore', [handler]);
			}
		}
		//endregion
	}

export {
	Editor as PostForm,
	TasksLimit as PostFormTasksLimit,
}
