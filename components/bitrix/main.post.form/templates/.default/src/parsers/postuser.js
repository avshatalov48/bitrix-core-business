import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Default from './default';
import Editor from "../editor";

export default class PostUser extends Default
{
	id: string = 'postuser';
	buttonParams = null;

	constructor(editor: Editor, htmlEditor)
	{
		super(editor, htmlEditor);

		EventEmitter.subscribe(
			htmlEditor,
			'OnIframeKeydown',
			function({compatData: [event]})
			{
				if (window.onKeyDownHandler)
				{
					window.onKeyDownHandler(event, htmlEditor, htmlEditor.formID);
				}
			}
		);

		EventEmitter.subscribe(
			htmlEditor,
			'OnIframeKeyup',
			function({compatData: [event]})
			{
				if (window.onKeyUpHandler)
				{
					window.onKeyUpHandler(event, htmlEditor, htmlEditor.formID);
				}
			}
		);

		EventEmitter.subscribe(
			htmlEditor,
			'OnIframeClick',
			function()
			{
				if (window['BXfpdStopMent' + htmlEditor.formID])
				{
					window['BXfpdStopMent' + htmlEditor.formID]();
				}
			}
		);

		EventEmitter.subscribe(
			htmlEditor,
			'OnTextareaKeyup',
			function({compatData: [event]})
			{
				if (htmlEditor.textareaView
					&& htmlEditor.textareaView.GetCursorPosition
					&& window.onTextareaKeyUpHandler
				)
				{
					window.onTextareaKeyUpHandler(event, htmlEditor, htmlEditor.formID);
				}
			}
		);
		EventEmitter.subscribe(
			htmlEditor,
			'OnTextareaKeydown',
			function({compatData: [event]})
			{
				if (htmlEditor.textareaView
					&& htmlEditor.textareaView.GetCursorPosition
					&& window.onTextareaKeyDownHandler)
				{
					window.onTextareaKeyDownHandler(event, htmlEditor, htmlEditor.formID);
				}
			}
		);
	}

	parse(content, pLEditor)
	{
		content = content.replace(
			/\[USER\s*=\s*(\d+)\](.*?)\[\/USER\]/ig,
			(str, id, name) => {
				name = name.trim();
				if (name === '')
				{
					return '';
				}
				const tagId = this.htmlEditor.SetBxTag(false, {tag: this.id, userId: id, userName: name});
				return `<span id="${tagId}" class="bxhtmled-metion">${name}</span>`;
			})
			.replace(
				/\[PROJECT\s*=\s*(\d+)\](.*?)\[\/PROJECT\]/ig,
				(str, id, name) => {
					name = name.trim();
					if (name === '')
					{
						return '';
					}
					const tagId = this.htmlEditor.SetBxTag(false, {tag: this.id, projectId: id, projectName: name});
					return `<span id="${tagId}" class="bxhtmled-metion">${name}</span>`;
				})
			.replace(
				/\[DEPARTMENT\s*=\s*(\d+)\](.*?)\[\/DEPARTMENT\]/ig,
				(str, id, name) => {
					name = name.trim();
					if (name === '')
					{
						return '';
					}
					const tagId = this.htmlEditor.SetBxTag(false, {tag: this.id, departmentId: id, departmentName: name});
					return `<span id="${tagId}" class="bxhtmled-metion">${name}</span>`;
				});
		return content;
	}

	unparse(bxTag, oNode)
	{
		let text = '';
		oNode.node.childNodes.forEach((node) => {
			text += this.htmlEditor.bbParser.GetNodeHtml(node);
		});
		text = String(text).trim();

		let result = '';
		if (Type.isStringFilled(text))
		{
			if (!Type.isUndefined(bxTag.userId))
			{
				result = `[USER=${bxTag.userId}]${text}[/USER]`;
			}
			else if (!Type.isUndefined(bxTag.projectId))
			{
				result = `[PROJECT=${bxTag.projectId}]${text}[/PROJECT]`;
			}
			else if (!Type.isUndefined(bxTag.departmentId))
			{
				result = `[DEPARTMENT=${bxTag.departmentId}]${text}[/DEPARTMENT]`;
			}
		}

		return result;
	}
}
