import {Type, Tag, Loc, Runtime, Dom} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Popup} from 'main.popup';

import {Editor} from "./editor";

/**
 * @memberOf BX.UI.Timeline
 * @mixes EventEmitter
 */
export class CommentEditor extends Editor
{
	postForm: ?LHEPostForm;
	visualEditor: ?BXHtmlEditor;
	commentId = 0;
	editorContent = null;

	constructor(params: {
		id: string,
		commentId: ?number,
	})
	{
		super(params);
		if(Type.isNumber(params.commentId))
		{
			this.commentId = params.commentId;
		}
		this.setEventNamespace('BX.UI.Timeline.CommentEditor');
	}

	getTitle(): string
	{
		return Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT');
	}

	getVisualEditorName(): string
	{
		return 'UiTimelineCommentVisualEditor' + this.getId().replace('- ', '');
	}

	getTextarea(): ?HTMLTextAreaElement
	{
		return this.layout.textarea;
	}

	renderTextarea(): HTMLTextAreaElement
	{
		this.layout.textarea = Tag.render`<textarea onfocus="${this.onFocus.bind(this)}" rows="1" class="ui-item-detail-stream-section-new-comment-textarea" placeholder="${Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_TEXTAREA')}"></textarea>`;

		return this.getTextarea();
	}

	getVisualEditorContainer(): ?Element
	{
		return this.layout.visualEditorContainer;
	}

	renderVisualEditorContainer(): Element
	{
		this.layout.visualEditorContainer = Tag.render`<div class="ui-timeline-comment-visual-editor"></div>`;

		return this.getVisualEditorContainer();
	}

	getButtonsContainer(): ?Element
	{
		return this.layout.buttonsContainer;
	}

	renderButtons(): Element
	{
		this.layout.buttonsContainer = Tag.render`<div class="ui-item-detail-stream-section-new-comment-btn-container">
			${this.renderSaveButton()}
			${this.renderCancelButton()}
		</div>`;

		return this.getButtonsContainer();
	}

	getSaveButton(): ?Element
	{
		return this.layout.saveButton;
	}

	renderSaveButton(): Element
	{
		this.layout.saveButton = Tag.render`<button onclick="${this.save.bind(this)}" class="ui-btn ui-btn-xs ui-btn-primary">${Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_SEND')}</button>`;

		return this.getSaveButton();
	}

	getCancelButton(): ?Element
	{
		return this.layout.cancelButton;
	}

	renderCancelButton(): Element
	{
		this.layout.cancelButton = Tag.render`<span onclick="${this.cancel.bind(this)}" class="ui-btn ui-btn-xs ui-btn-link">${Loc.getMessage('UI_TIMELINE_EDITOR_COMMENT_CANCEL')}</span>`;

		return this.getCancelButton();
	}

	render(): Element
	{
		this.layout.container = Tag.render`<div class="ui-timeline-comment-editor">
				${this.renderTextarea()}
				${this.renderButtons()}
				${this.renderVisualEditorContainer()}
			</div>`;

		return this.getContainer();
	}

	onFocus()
	{
		const container = this.getContainer();
		if(container)
		{
			container.classList.add('focus');
		}

		this.showVisualEditor();
	}

	showVisualEditor()
	{
		if(!this.getVisualEditorContainer())
		{
			return;
		}

		if(this.postForm && this.visualEditor)
		{
			this.postForm.eventNode.style.display = 'block';
			this.visualEditor.Focus();
		}
		else if(!this.isProgress)
		{
			this.loadVisualEditor().then(() =>
			{
				EventEmitter.emit(this.postForm.eventNode, 'OnShowLHE', [true]);
				//todo there should be some other way
				setTimeout(() =>
				{
					this.editorContent = this.postForm.oEditor.GetContent();
				}, 300);
			}).catch(() =>
			{
				this.cancel();
				this.emit('error', {message: 'Could not load visual editor. Please try again later'});
			});
		}
	}

	loadVisualEditor(): Promise
	{
		return new Promise((resolve, reject) =>
		{
			if(this.isProgress)
			{
				reject();
			}
			this.showEditorLoader();

			const event = new BaseEvent({
				data: {
					name: this.getVisualEditorName(),
					commentId: this.commentId,
				},
			});
			this.emitAsync('onLoadVisualEditor', event).then(() => {
				const html = event.getData().html;
				if(Type.isString(html))
				{
					Runtime.html(this.getVisualEditorContainer(), html).then(() => {
						this.hideEditorLoader();
						if(LHEPostForm && BXHtmlEditor)
						{
							this.postForm = LHEPostForm.getHandler(this.getVisualEditorName());
							this.visualEditor = BXHtmlEditor.Get(this.getVisualEditorName());
							resolve();
						}
						else
						{
							reject();
						}
					});
				}
				else
				{
					reject();
				}
			}).catch(() =>
			{
				reject();
			});
		});
	}

	showEditorLoader()
	{
		this.editorLoader = Tag.render`<div class="ui-timeline-wait"></div>`;
		Dom.append(this.editorLoader, this.getContainer());
	}

	hideEditorLoader()
	{
		Dom.remove(this.editorLoader);
	}

	hideVisualEditor()
	{
		if(this.postForm)
		{
			this.postForm.eventNode.style.display = 'none';
		}
	}

	save()
	{
		if(this.isProgress || !this.postForm)
		{
			return;
		}

		let isCancel = false;
		const description = this.postForm.oEditor.GetContent();
		this.editorContent = description;
		const files = this.getAttachments();
		this.emit('beforeSave', {description, isCancel, files});
		if(description === '')
		{
			this.getEmptyMessageNotification().show();
			return;
		}
		if(isCancel === true)
		{
			this.cancel();
			return;
		}

		this.startProgress();
		const event = new BaseEvent({
			data: {
				description,
				files,
				commentId: this.commentId,
			},
		});
		this.emitAsync('onSave', event).then(() => {
			this.postForm.reinit();
			this.stopProgress();
			this.emit('afterSave', {
				data: event.getData(),
			});
			this.cancel();
		}).catch(() =>
		{
			//todo why are we here?
			this.stopProgress();
			this.cancel();
			const message = event.getData().message;
			if(message)
			{
				this.emit('error', {
					message
				});
			}
		});
	}

	cancel()
	{
		this.hideVisualEditor();
		const container = this.getContainer();
		if(container)
		{
			container.classList.remove('focus');
		}
		this.stopProgress();
		this.emit('cancel');
	}

	getEmptyMessageNotification(): Popup
	{
		if(!this.emptyMessagePopup)
		{
			this.emptyMessagePopup = new Popup({
				id: this.getId() + '-empty-message-popup',
				bindElement: this.getSaveButton(),
				content: BX.message('UI_TIMELINE_EMPTY_COMMENT_NOTIFICATION'),
				darkMode: true,
				autoHide: true,
				zIndex: 990,
				angle: {position: 'top', offset: 77},
				closeByEsc: true,
				bindOptions: { forceBindPosition: true}
			});
		}

		return this.emptyMessagePopup;
	}

	refresh()
	{
		if(this.postForm && this.postForm.oEditor)
		{
			if(this.editorContent)
			{
				this.postForm.oEditor.SetContent(this.editorContent);
			}
		}
		if(this.visualEditor)
		{
			this.visualEditor.ReInitIframe();
		}
	}

	getAttachments(): Array
	{
		const attachments = [];
		if(!this.postForm || !Type.isPlainObject(this.postForm.arFiles) || !Type.isPlainObject(this.postForm.controllers))
		{
			return attachments;
		}

		const fileControllers = [];
		Object.values(this.postForm.arFiles).forEach((controller) =>
		{
			if(!fileControllers.includes(controller))
			{
				fileControllers.push(controller);
			}
		});
		fileControllers.forEach((fileController) =>
		{
			if(this.postForm.controllers[fileController] && Type.isPlainObject(this.postForm.controllers[fileController].values))
			{
				Object.keys(this.postForm.controllers[fileController].values).forEach((fileId) =>
				{
					if(!attachments.includes(fileId))
					{
						attachments.push(fileId);
					}
				})
			}
		});

		return attachments;
	}
}