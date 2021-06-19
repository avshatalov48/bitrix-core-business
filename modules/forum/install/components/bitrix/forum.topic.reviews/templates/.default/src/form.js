import Entity from './entity';
import {Loc, Text, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';

export default class Form
{
	static maxMessageLength = 64000;
	static instances = {};

	static makeQuote(formId, {entity, messageId, text})
	{
		if (Form.instances[formId])
		{
			Form.instances[formId].quote({entity, messageId, text});
		}
	}

	static makeReply(formId, {entity, messageId, text})
	{
		if (Form.instances[formId])
		{
			Form.instances[formId].reply({entity, messageId, text});
		}
	}

	static create({formId, editorId, formNode, useAjax})
	{
		if (!Form.instances[formId])
		{
			Form.instances[formId] = new Form({formId, editorId, formNode, useAjax});
		}
		return Form.instances[formId];
	}

	constructor({formId, editorId, formNode, useAjax})
	{
		this.formId = formId;
		this.editorId = editorId;

		this.currentEntity = {
			entity: null,
			messageId: null,
		};

		this.init = this.init.bind(this);
		EventEmitter.subscribe('OnEditorInitedAfter', this.init);

		this.useAjax = (useAjax === true);

		this.formNode = formNode;
		this.formNode.addEventListener('submit', this.submit.bind(this));
		this.container = this.formNode.parentNode;

		this.onSuccess = this.onSuccess.bind(this);
		this.onFailure = this.onFailure.bind(this);
	}

	init({target})
	{
		if (target.id !== this.editorId)
		{
			return;
		}

		this.editor = target;
		target.insertImageAfterUpload = true;
		EventEmitter.unsubscribe('OnEditorInitedAfter', this.init);
		BX.bind(BX('post_message_hidden'), "focus", function(){ target.Focus();} );
	}

	submit(event)
	{
		let text = '';
		if (this.getLHE().editorIsLoaded)
		{
			this.getLHE().oEditor.SaveContent();
			text = this.getLHE().oEditor.GetContent();
		}
		const error = [];
		if (text.length <= 0)
		{
			error.push(Loc.getMessage('JERROR_NO_MESSAGE'))
		}
		else if (text.length > Form.maxMessageLength)
		{
			error.push(Loc.getMessage('JERROR_MAX_LEN')
				.replace(/#MAX_LENGTH#/gi, Form.maxMessageLength)
				.replace(/#LENGTH#/gi, text.length))
		}
		else if (this.isOccupied())
		{
			error.push('Occupied');
		}

		if (error.length <= 0)
		{
			this.occupy();
			if (!this.useAjax)
			{
				return true;
			}
			this.send();
		}
		else
		{
			alert(error.join(''));
		}
		event.stopPropagation();
		event.preventDefault();
		return false;
	}

	isOccupied()
	{
		return this.busy === true;
	}

	occupy()
	{
		this.busy = true;
		this.formNode
			.querySelectorAll("input[type=submit]")
			.forEach((input) => {
				input.disabled = true;
			});
	}

	release()
	{
		this.busy = false;
		this.formNode
			.querySelectorAll("input[type=submit]")
			.forEach((input) => {
				input.disabled = false;
			});
	}

	send()
	{
		const secretNode = document.createElement('input');
		secretNode.type = 'hidden';
		secretNode.name = 'dataType';
		secretNode.value = 'json';

		this.formNode.appendChild(secretNode);
		BX.ajax.submitAjax(this.formNode, {
			method: 'POST',
			url: this.formNode.action,
			dataType: 'json',
			onsuccess: this.onSuccess,
			onfailure: this.onFailure
		});
		this.formNode.removeChild(secretNode);
	}

	onSuccess({status, action, data, errors})
	{
		this.release();
		if (status !== 'success')
		{
			return this.showError(data.errorHtml, errors);
		}
		else if (action === 'preview')
		{
			return this.showPreview(data.previewHtml);
		}
		else if (action === 'add')
		{
			// Legacy sake for
			EventEmitter.emit(
				'onForumCommentAJAXPost',
				[data, this.formNode]
			);
			EventEmitter.emit(
				this.currentEntity.entity,
				'onForumCommentAdded',
				data
			);
			return this.clear();
		}

		this.showError('There is nothing')
	}

	onFailure()
	{
		this.release();
		this.showError('<b class="error">Some error with response</b>');
	}

	showError(errorHTML)
	{
		const errorNode = this.container.querySelector('div[data-bx-role=error]');
		errorNode.innerHTML = errorHTML;
		this.container.setAttribute('data-bx-status', 'errored');
		errorNode.style.display = 'block';
	}

	hideError()
	{
		const errorNode = this.container.querySelector('div[data-bx-role=error]');
		errorNode.innerHTML = '';
		this.container.removeAttribute('data-bx-status', 'errored');
		errorNode.style.display = 'none';
	}

	showPreview(previewHTML)
	{
		const previewNode = this.container.querySelector('div[data-bx-role=preview]');
		previewNode.innerHTML = previewHTML;
		this.container.setAttribute('data-bx-status', 'preview');
		previewNode.style.display = 'block';
	}

	hidePreview()
	{
		const previewNode = this.container.querySelector('div[data-bx-role=preview]');
		previewNode.innerHTML = '';
		this.container.setAttribute('data-bx-status', 'preview');
		previewNode.style.display = 'none';
	}

	isFormReady({entity, messageId}) {
		if (
			this.currentEntity.entity === null ||
			this.currentEntity.entity === entity
		)
		{
			return true;
		}
		return window.confirm("Do you want to miss all changes?");
	}

	parseText(text)
	{
		const editor = this.getLHE().oEditor;
		let tmpTxt = text;
		if (tmpTxt.length > 0 && editor.GetViewMode() === "wysiwyg")
		{
			const reg = /^\[USER\=(\d+)\](.+?)\[\/USER\]/i;
			if (reg.test(tmpTxt))
			{
				tmpTxt = tmpTxt.replace(reg, function() {
					const userId = parseInt(arguments[1]);
					const userName = Text.encode(arguments[2]);
					let result = `<span>${userName}</span>`;
					if (userId > 0)
					{
						const tagId = editor.SetBxTag(false, {tag: "postuser", params: {value : userId}});
						result = `<span id="${tagId}" class="bxhtmled-metion">${userName}</span>`;
					}
					return result;
				}.bind(this));
			}
		}
		return tmpTxt;
	}

	reply({entity, messageId, text})
	{
		this.show({entity, messageId})
			.then(() => {
				if (text !== '')
				{
					const editor = this.getLHE().oEditor;
					const tmpText = this.parseText(text);
					editor.action.Exec("insertHTML", tmpText);
				}
			});
	}

	quote({entity, messageId, text})
	{
		this.show({entity, messageId})
			.then(() => {
				const editor = this.getLHE().oEditor;
				if (!editor.toolbar.controls.Quote)
				{
					return;
				}
				const tmpText = this.parseText(text);

				if (editor.action.actions.quote.setExternalSelectionFromRange)
				{
					editor.action.actions.quote.setExternalSelection(tmpText);
				}
				editor.action.Exec("quote");
			});
	}

	clear()
	{
		this.hideError();
		this.hidePreview();
		this.editor.CheckAndReInit('');
		if (this.editor.fAutosave && this.editor.pEditorDocument)
		{
			this.editor.pEditorDocument.addEventListener(
				'keydown',
				this.editor.fAutosave.Init.bind(this.editor.fAutosave)
			);
		}

		this.formNode.querySelectorAll('.reviews-preview').forEach((node) => {
			node.parentNode.removeChild(node);
		});

		this.formNode.querySelectorAll('input[type="file"]').forEach((node) => {
			const newNode = node.cloneNode();
			newNode.value = '';
			node.parentNode.replaceChild(newNode, node);
		});
		const visibilityCheckbox = this.formNode.querySelector('[data-bx-role="attach-visibility"]');
		if (visibilityCheckbox)
		{
			visibilityCheckbox.checked = false;
		}

		const captchaWord = this.formNode.querySelector('input[name="captcha_word"]');
		if (captchaWord)
		{
			captchaWord.value = '';
			const captchaCode = this.formNode.querySelector('input[name="captcha_code"]');
			const captchaImage = this.formNode.querySelector('img[name="captcha_image"]');
			BX.ajax.getCaptcha(function(result) {
				captchaCode.value = result['captcha_sid'];
				captchaImage.src = '/bitrix/tools/captcha.php?captcha_code=' + result['captcha_sid'];
			});
		}
		const subscribeCheckbox = this.formNode.querySelector('input[name="TOPIC_SUBSCRIBE"]');
		if (subscribeCheckbox && subscribeCheckbox.checked)
		{
			subscribeCheckbox.disabled = true;
		}
	}

	show({entity, messageId})
	{
		return new Promise((resolve, reject) => {
			if (!this.isFormReady({entity, messageId}))
			{
				return reject();
			}

			const loaded = (!!this.getLHE() && !!this.getLHE().editorIsLoaded);
			if (loaded
				&& this.currentEntity.entity === entity
				&& this.currentEntity.messageId === messageId)
			{
				this.getLHE().oEditor.Focus();
				return resolve();
			}

			this.currentEntity.entity = entity;
			this.currentEntity.messageId = messageId;
			this.container.style.display = 'block';

			EventEmitter.emit(this.currentEntity.entity,'onForumCommentFormShow', []);
			EventEmitter.emit(this.getLHEEventNode(), 'OnShowLHE', ['show']);

			if (loaded !== true)
			{
				this.getLHE().exec(() => {
					this.show({entity, messageId}).then(resolve, reject);
				});
			}
			else
			{
				resolve();
			}
		});

	}

	getLHE()
	{
		return LHEPostForm.getHandlerByFormId(this.formId);
	}

	getLHEEventNode()
	{
		if (!this.handlerEventNode && this.getLHE())
		{
			this.handlerEventNode = this.getLHE().eventNode;
		}
		return this.handlerEventNode
	}
}