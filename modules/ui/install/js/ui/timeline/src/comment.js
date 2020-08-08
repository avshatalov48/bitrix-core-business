import {Loc, Text, Tag, Type, Runtime, Event, Dom} from 'main.core';
import {History} from './history';
import {MessageBox} from 'ui.dialogs.messagebox';
import {CommentEditor} from "./commenteditor";
import {BaseEvent} from "main.core.events";

const COLLAPSE_TEXT_MAX_LENGTH = 128;

/**
 * @memberOf BX.UI.Timeline
 * @mixes EventEmitter
 */
export class Comment extends History
{
	commentEditor: ?CommentEditor;
	isCollapsed = null;
	isContentLoaded = null;

	constructor(props)
	{
		super(props);
		this.setEventNamespace('BX.UI.Timeline.Comment');
	}


	afterRender()
	{
		super.afterRender();

		if(this.isCollapsed === null)
		{
			this.isCollapsed = this.isAddExpandBlock();
		}
		if(this.isContentLoaded === null)
		{
			this.isContentLoaded = !this.hasFiles();
		}
		if(this.isCollapsed)
		{
			this.getMain().classList.add('ui-timeline-content-description-collapsed');
			this.getMain().classList.remove('ui-timeline-content-description-expand');
		}
		else
		{
			this.getMain().classList.remove('ui-timeline-content-description-collapsed');
			this.getMain().classList.add('ui-timeline-content-description-expand');
		}
		if(this.isAddExpandBlock())
		{
			this.getMainDescription().appendChild(this.renderExpandBlock());
		}
		if(this.hasFiles())
		{
			this.getContent().appendChild(Tag.render`<div class="ui-timeline-section-files">${this.renderFilesContainer()}</div>`);
			Event.ready(() => {
				setTimeout(() =>
				{
					this.loadFilesContent();
				}, 100);
			});
		}
	}

	getFiles(): Array
	{
		if(Type.isArray(this.data.files))
		{
			return this.data.files;
		}

		return [];
	}

	hasFiles(): boolean
	{
		return (this.getFiles().length > 0);
	}

	isAddExpandBlock(): boolean
	{
		return ((this.textDescription.length > COLLAPSE_TEXT_MAX_LENGTH) || this.hasFiles());
	}

	renderContainer(): Element
	{
		const container = super.renderContainer();
		container.classList.add('ui-item-detail-stream-section-comment');
		container.classList.remove('ui-item-detail-stream-section-info');

		return container;
	}

	renderMain(): Element
	{
		this.layout.main = Tag.render`<div class="ui-item-detail-stream-content-detail">
			${this.renderMainDescription()}
		</div>`;

		return this.getMain();
	}

	getMain(): ?Element
	{
		return this.layout.main;
	}

	renderMainDescription(): Element
	{
		this.layout.mainDescription = Tag.render`<div class="ui-item-detail-stream-content-description" onclick="${this.onMainClick.bind(this)}">${this.htmlDescription}</div>`;

		return this.getMainDescription();
	}

	getMainDescription(): ?Element
	{
		return this.layout.mainDescription;
	}

	renderExpandBlock(): Element
	{
		this.layout.expandBlock = Tag.render`<div class="ui-timeline-content-description-expand-container">${this.renderExpandButton()}</div>`;

		return this.getExpandBlock();
	}

	getExpandBlock(): ?Element
	{
		return this.layout.expandBlock;
	}

	renderExpandButton(): Element
	{
		this.layout.expandButton = Tag.render`<a class="ui-timeline-content-description-expand-btn" onclick="${this.onExpandButtonClick.bind(this)}">
			${Loc.getMessage((this.isCollapsed ? 'UI_TIMELINE_EXPAND_SM' : 'UI_TIMELINE_COLLAPSE_SM'))}
		</a>`;

		return this.getExpandButton();
	}

	getExpandButton(): ?Element
	{
		return this.layout.expandButton;
	}

	getCommendEditor(): CommentEditor
	{
		if(!this.commentEditor)
		{
			this.commentEditor = new CommentEditor({
				commentId: this.getId(),
				id: 'UICommentEditor' + this.getId() + (this.isPinned ? 'pinned' : '') + Text.getRandom(),
			});
			this.commentEditor.layout.container = this.getContainer();
			this.commentEditor.subscribe('cancel', this.switchToViewMode.bind(this));
			this.commentEditor.subscribe('afterSave', this.onSaveComment.bind(this));
		}

		return this.commentEditor;
	}

	getEditorContainer(): ?Element
	{
		return this.layout.editorContainer;
	}

	renderEditorContainer(): Element
	{
		const editorContainer = this.getCommendEditor().getVisualEditorContainer();
		if(editorContainer)
		{
			this.layout.editorContainer = editorContainer;
		}
		else
		{
			this.layout.editorContainer = this.getCommendEditor().renderVisualEditorContainer();
		}

		return this.getEditorContainer();
	}

	getEditorButtons(): ?Element
	{
		return this.layout.editorButtons;
	}

	renderEditorButtons(): Element
	{
		this.layout.editorButtons = this.getCommendEditor().renderButtons();

		return this.getEditorButtons();
	}

	renderFilesContainer(): Element
	{
		this.layout.filesContainer = Tag.render`<div class="ui-timeline-section-files-inner"></div>`;

		return this.getFilesContainer();
	}

	getFilesContainer(): ?Element
	{
		return this.layout.filesContainer;
	}

	switchToEditMode()
	{
		if(!this.isRendered())
		{
			return;
		}

		if(!this.getEditorContainer())
		{
			this.getMain().appendChild(this.renderEditorContainer());
			this.getMain().appendChild(this.renderEditorButtons());
		}
		else
		{
			this.getCommendEditor().refresh();
		}

		this.getContent().classList.add('ui-item-detail-comment-edit');
		this.getCommendEditor().showVisualEditor();
	}

	switchToViewMode()
	{
		this.getContent().classList.remove('ui-item-detail-comment-edit');
		this.getCommendEditor().hideVisualEditor();
	}

	getActions(): Array
	{
		return [
			{
				text: Loc.getMessage('UI_TIMELINE_ACTION_MODIFY'),
				onclick: this.actionEdit.bind(this),
			},
			{
				text: Loc.getMessage('UI_TIMELINE_ACTION_DELETE'),
				onclick: this.actionDelete.bind(this),
			}
		];
	}

	actionEdit()
	{
		this.getActionsMenu().close();
		this.switchToEditMode();
	}

	actionDelete()
	{
		this.getActionsMenu().close();
		MessageBox.confirm(Loc.getMessage('UI_TIMELINE_COMMENT_DELETE_CONFIRM'), () => {
			return new Promise((resolve) => {
				if(this.isProgress)
				{
					return;
				}
				this.startProgress();
				const event = new BaseEvent({
					data: {
						commentId: this.getId(),
					},
				});
				this.emitAsync('onDelete', event).then(() => {
					this.stopProgress();
					this.onDelete();
					resolve();
				}).catch(() => {
					this.stopProgress();
					const message = event.getData().message;
					if(message)
					{
						this.emit('error', {
							message
						});
					}
					resolve();
				});
			});
		});
	}

	clearLayout(isSkipContainer = false): Item
	{
		this.commentEditor = null;

		return super.clearLayout(isSkipContainer);
	}

	onSaveComment(event: BaseEvent)
	{
		const data = event.getData();
		if(data.data && data.data.comment)
		{
			this.update(data.data.comment);
		}
	}

	onMainClick({target})
	{
		if(Type.isDomNode(target))
		{
			const tagName = target.tagName.toLowerCase();
			if(
				tagName === 'a'
				|| tagName === 'img'
				|| Dom.hasClass(target, 'feed-con-file-changes-link-more')
				|| Dom.hasClass(target, 'feed-com-file-inline')
				|| (document.getSelection().toString().length > 0)
			)
			{
				return;
			}
		}

		this.switchToEditMode();
	}

	onExpandButtonClick(event)
	{
		event.preventDefault();
		event.stopPropagation();

		if(!this.isRendered())
		{
			return;
		}

		if(this.isCollapsed === true)
		{
			this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + 130 + "px";
			this.getMain().classList.remove('ui-timeline-content-description-collapsed');
			this.getMain().classList.add('ui-timeline-content-description-expand');
			setTimeout(() =>
			{
				this.getExpandBlock().style.maxHeight = "";
			}, 300);
			this.getExpandButton().innerText = Loc.getMessage('UI_TIMELINE_COLLAPSE_SM');
			if(!this.isContentLoaded)
			{
				this.isContentLoaded = true;
				this.loadContent();
			}
			this.isCollapsed = false;
		}
		else if(this.isCollapsed === false)
		{
			this.getExpandBlock().style.maxHeight = this.getExpandBlock().scrollHeight + "px";
			this.getMain().classList.add('ui-timeline-content-description-collapsed');
			this.getMain().classList.remove('ui-timeline-content-description-expand');
			setTimeout(() =>
			{
				this.getExpandBlock().style.maxHeight = "";
			}, 0);
			this.getExpandButton().innerText = Loc.getMessage('UI_TIMELINE_EXPAND_SM');
			this.isCollapsed = true;
		}
	}

	loadFilesContent()
	{
		if(this.isProgress)
		{
			return;
		}

		this.startProgress();
		const event = new BaseEvent({
			data: {
				commentId: this.getId(),
			},
		});
		this.emitAsync('onLoadFilesContent', event).then(() => {
			this.stopProgress();
			const html = event.getData().html;
			if(Type.isString(html))
			{
				Runtime.html(this.getFilesContainer(), html);
			}
		}).catch(() => {
			this.stopProgress();
			const message = event.getData().message;
			if(message)
			{
				this.emit('error', {
					message
				});
			}
		});
	}

	loadContent()
	{
		if(this.isProgress)
		{
			return;
		}

		this.startProgress();
		const event = new BaseEvent({
			data: {
				commentId: this.getId(),
			},
		});
		this.emitAsync('onLoadContent', event).then(() => {
			this.stopProgress();
			const comment = event.getData().comment;
			if(comment && Type.isString(comment.htmlDescription))
			{
				Runtime.html(this.getMainDescription(), comment.htmlDescription);
				if(this.isAddExpandBlock())
				{
					this.getMainDescription().appendChild(this.getExpandBlock());
				}
				this.updateData(comment);
			}
		}).catch(() => {
			this.stopProgress();
			const message = event.getData().message;
			if(message)
			{
				this.emit('error', {
					message
				});
			}
		});
	}
}