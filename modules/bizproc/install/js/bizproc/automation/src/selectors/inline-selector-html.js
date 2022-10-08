import { Tag } from 'main.core';
import { Field } from './types';
import { InlineSelector } from './inline-selector';

export class InlineSelectorHtml extends InlineSelector
{
	#editorNode: ?BXHtmlEditor;
	#eventHandlers = {
		'OnEditorInitedAfter': this.#bindEditorHooks.bind(this),
	}

	destroy()
	{
		this.#unbindEvents();
	}

	renderTo(targetInput: Element)
	{
		this.targetInput = targetInput;
		this.#editorNode = targetInput.querySelector('.bx-html-editor');
		this.menuButton = Tag.render`
			<span
				onclick="${this.openMenu.bind(this)}"
				class="bizproc-automation-popup-select-dotted"
			></span>
		`;

		this.parseTargetProperties();
		this.bindTargetEvents()

		targetInput.firstElementChild.appendChild(this.menuButton);

		this.#bindEvents();
	}

	#bindEvents(): void
	{
		for (const [name, handler] of Object.entries(this.#eventHandlers))
		{
			BX.addCustomEvent(name, handler);
		}
	}

	#unbindEvents(): void
	{
		for (const [name, handler] of Object.entries(this.#eventHandlers))
		{
			BX.removeCustomEvent(name, handler);
		}
	}

	#bindEditorHooks(editor): void
	{
		if (editor.dom.cont !== this.#editorNode)
		{
			return false;
		}

		let header = '';
		let footer = '';

		const cutHeader = (content: string, shouldSaveHeader: boolean = false) => {
			return content.replace(/(^[\s\S]*?)(<body.*?>)/i, (str) => {
				if (shouldSaveHeader)
				{
					header = str;
				}

				return '';
			});
		};

		const cutFooter = (content: string, shouldSaveFooter: boolean = false) => {
			return content.replace(/(<\/body>[\s\S]*?$)/i, (str) => {
				if (shouldSaveFooter)
				{
					footer = str;
				}

				return '';
			});
		}

		BX.addCustomEvent(editor, 'OnParse', function (mode)
		{
			if (!mode)
			{
				this.content = cutFooter(cutHeader(this.content, true), true);
			}
		});

		BX.addCustomEvent(editor, 'OnAfterParse', function (mode)
		{
			if (mode)
			{
				let content = cutFooter(cutHeader(this.content));

				if (header !== '' && footer !== '')
				{
					content = header + content + footer;
				}

				this.content = content;
			}
		});
	}

	onFieldSelect(field: ?Field): void
	{
		const insertText = field.Expression;
		const editor = this.#getEditor();

		if (editor && editor.InsertHtml)
		{
			if (editor.synchro.IsFocusedOnTextarea())
			{
				editor.textareaView.Focus();
				editor.textareaView.WrapWith('', '', insertText);
			}
			else
			{
				editor.InsertHtml(insertText);
			}
			editor.synchro.Sync();
		}
	}

	onBeforeSave(): void
	{
		const editor = this.#getEditor();
		if (editor && editor.SaveContent)
		{
			editor.SaveContent();
		}
	}

	onPopupResize()
	{
		const editor = this.#getEditor();
		if (editor && editor.ResizeSceleton)
		{
			editor.ResizeSceleton();
		}
	}

	#getEditor(): ?BXHtmlEditor
	{
		if (this.#editorNode)
		{
			const editorId = this.#editorNode.id.split('-');
			return BXHtmlEditor.Get(editorId[editorId.length - 1]);
		}

		return null;
	}
}