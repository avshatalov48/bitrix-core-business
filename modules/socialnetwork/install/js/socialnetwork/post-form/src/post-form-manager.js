import { Dom, Event, Loc, Tag } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

type Params = {
	formId: string,
	LHEId: string,
	isShownPostTitle: boolean,
}

export type MentionEntity = {
	name: string,
	entityId: number,
	avatar?: string,
	email?: string,
	isExtranet?: 'Y' | 'N',
	isEmail?: 'Y' | 'N',
}

export class PostFormManager extends EventEmitter
{
	#formId: string;
	#LHEId: string;
	#isShownPostTitle: boolean;

	#LHEPostForm: Object;
	#eventNode: HTMLElement;
	#showPostTitleBtn: HTMLElement;
	#editor: BXEditor;
	#userFieldControl: BX.Disk.Uploader.UserFieldControl;
	#blockFileShowEvent: boolean = false;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.PostFormManager');

		this.#formId = params.formId;
		this.#LHEId = params.LHEId;
		this.#isShownPostTitle = params.isShownPostTitle === true;

		EventEmitter.subscribe('OnEditorInitedAfter', (event: BaseEvent) => {
			const [editor] = event.getData();
			this.#editorInited(editor);
		});

		EventEmitter.subscribe('onMentionAdd', this.#addMention.bind(this));
	}

	initLHE()
	{
		if (!window.LHEPostForm)
		{
			throw new Error('BX.Socialnetwork.PostFormManager: LHEPostForm not found');
		}

		this.#LHEPostForm = window.LHEPostForm;

		const handler = this.#LHEPostForm.getHandler(this.#LHEId);

		this.#eventNode = handler.eventNode;

		EventEmitter.emit(this.#eventNode, 'OnShowLHE', ['show']);

		this.#appendButtonShowingPostTitle();

		this.#userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(this.#formId);

		EventEmitter.subscribe(this.#eventNode, 'onShowControllers', ({ data }) => {
			if (this.#blockFileShowEvent === false && data.toString() === 'show')
			{
				setTimeout(() => {
					this.emit('showControllers');
				}, 100);
			}
			this.#blockFileShowEvent = false;
		});

		EventEmitter.subscribe(this.#eventNode, 'onShowControllers:File:Increment', ({ data }) => {
			this.#blockFileShowEvent = true;
		});
	}

	getEditorText(): string
	{
		return this.#editor.GetContent();
	}

	clearEditorText(): void
	{
		EventEmitter.subscribeOnce(this.#editor, 'OnSetContentAfter', () => {
			this.#editor.ResizeSceleton();
		});

		this.#editor.SetContent('');
	}

	focusToEditor()
	{
		if (this.#editor)
		{
			this.#editor.Focus();
		}
	}

	#editorInited(editor)
	{
		if (editor.id === this.#LHEId)
		{
			this.#editor = editor;

			this.emit('editorInited');

			EventEmitter.subscribe(editor, 'OnFullscreenExpand', () => {
				this.emit('fullscreenExpand');
			});
		}
	}

	#addMention(baseEvent: BaseEvent)
	{
		const [
			entity: MentionEntity,
			type: 'user' | 'project' | 'department',
		] = baseEvent.getCompatData();

		const entityType = this.#getEntityType(type, entity);

		this.emit('addMention', { type, entity, entityType });
	}

	#getEntityType(type: 'user' | 'project' | 'department', entity: MentionEntity): string
	{
		let entityType = '';
		if (type === 'user')
		{
			if (entity.isExtranet === 'Y')
			{
				entityType = 'extranet';
			}
			else if (entity.isEmail === 'Y')
			{
				entityType = 'email';
			}
			else
			{
				entityType = 'employee';
			}
		}
		else if (type === 'project')
		{
			if (entity.isExtranet === 'Y')
			{
				entityType = 'extranet';
			}
		}

		return entityType;
	}

	#appendButtonShowingPostTitle()
	{
		const activeClass = this.#isShownPostTitle ? 'feed-add-post-form-btn-active' : '';

		this.#showPostTitleBtn = Tag.render`
			<div
				data-id="sn-post-form-manager-show-title-btn"
				class="feed-add-post-form-title-btn ${activeClass}"
				title="${Loc.getMessage('SN_PF_TITLE_PLACEHOLDER')}"
			>
			</div>
		`;

		Event.bind(this.#showPostTitleBtn, 'click', this.#toggleVisibilityPostTitle.bind(this));

		const containerWithAdditionalButtons = this.#eventNode
			.querySelector('.feed-add-post-form-but-more-open')
		;

		Dom.append(this.#showPostTitleBtn, containerWithAdditionalButtons);
	}

	#toggleVisibilityPostTitle()
	{
		this.emit('toggleVisibilityPostTitle');

		this.#isShownPostTitle = !this.#isShownPostTitle;

		Dom.toggleClass(this.#showPostTitleBtn, 'feed-add-post-form-btn-active');
	}
}
