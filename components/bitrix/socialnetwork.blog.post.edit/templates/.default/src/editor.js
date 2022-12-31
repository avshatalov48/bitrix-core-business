import { Dom, Type, Uri, ajax as Ajax, Event, Browser, Loc } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import PostForm from './form';
import PostFormTabs from './tabs';
import PostFormGratSelector from './grat';
import PostFormAutoSave from './autosave';

export default class PostFormEditor extends EventEmitter
{
	static instance: { [key: string]: any } = {};

	disabled: boolean = false;
	formParams: {};
	formId: string = '';

	static setInstance(id, instance)
	{
		PostFormEditor.instance[id] = instance;
	}

	static getInstance(id)
	{
		return PostFormEditor.instance[id];
	}

	constructor(formID, params)
	{
		super();
		this.init(formID, params);
		PostFormEditor.setInstance(formID, this);

		window['setBlogPostFormSubmitted'] = this.setBlogPostFormSubmitted.bind(this);
		window['submitBlogPostForm'] = this.submitBlogPostForm.bind(this);
	}

	init(formID, params)
	{
		this.disabled = false;
		this.formId = formID;

		this.formParams = {
			editorID: params.editorID,
			showTitle: !!params.showTitle,
			submitted: false,
			text: params.text,
			autoSave: params.autoSave,
			handler: (LHEPostForm && LHEPostForm.getHandler(params.editorID)),
			editor: (LHEPostForm && LHEPostForm.getEditor(params.editorID)),
			restoreAutosave: !!params.restoreAutosave,
			createdFromEmail: !!params.createdFromEmail,
		};

		EventEmitter.subscribe('onInitialized', (event: BaseEvent) => {
			const [ obj, form ] = event.getData();
			this.onHandlerInited(obj, form);
		});
		if (this.formParams.handler)
		{
			this.onHandlerInited(this.formParams.handler, formID);
		}

		EventEmitter.subscribe('OnEditorInitedAfter', (event: BaseEvent) => {
			const [ editor ] = event.getData();
			this.onEditorInited(editor);
		});

		if (this.formParams.editor)
		{
			this.onEditorInited(this.formParams.editor);
		}

		EventEmitter.subscribe('onSocNetLogMoveBody', (event: BaseEvent) => {
			const [ p ] = event.getData();
			if (p === 'sonet_log_microblog_container')
			{
				this.reinit();
			}
		});

		Event.ready(() => {
			if (
				Browser.isIE()
				&& document.getElementById('POST_TITLE')
			)
			{
				const showTitlePlaceholderBlur = () => {
					const node = document.getElementById('POST_TITLE');

					if (
						!node
						|| node.value === node.getAttribute('placeholder')
					)
					{
						node.value = node.getAttribute('placeholder');
						node.classList.remove('feed-add-post-inp-active');
					}
				};

				Event.bind(document.getElementById('POST_TITLE'), 'blur', showTitlePlaceholderBlur);
				showTitlePlaceholderBlur();

				document.getElementById('POST_TITLE').__onchange = (e) => {
					const node = document.getElementById('POST_TITLE');

					if (node.value === node.getAttribute('placeholder'))
					{
						node.value = '';
					}

					if (node.className.indexOf('feed-add-post-inp-active') < 0 )
					{
						node.classList.add('feed-add-post-inp-active');
					}
				};

				Event.bind(document.getElementById('POST_TITLE'), 'click', document.getElementById('POST_TITLE').__onchange);
				Event.bind(document.getElementById('POST_TITLE'), 'keydown', document.getElementById('POST_TITLE').__onchange);
				Event.bind(document.getElementById('POST_TITLE').form, 'submit', () => {
					const node = document.getElementById('POST_TITLE');

					if(node.value === node.getAttribute('placeholder'))
					{
						node.value = '';
					}
				});
			}

			if (params.activeTab !== '')
			{
				PostFormTabs.getInstance().changePostFormTab(params.activeTab);
			}

			PostFormTabs.getInstance().subscribe('changePostFormTab', this.checkHideAlert.bind(this));
			if (PostFormGratSelector.getInstance())
			{
				PostFormGratSelector.getInstance().subscribe('Selector::onContainerClick', this.hideAlert.bind(this));
			}
		});
	}

	showPanelTitle(show, saveChanges)
	{
		show = (
			show === true
			|| show === false
				? show
				: (
					document.getElementById('blog-title').style.display
					&& document.getElementById('blog-title').style.display === 'none'
				)
		);

		saveChanges = (saveChanges !== false);

		const showTitleValue = this.formParams.showTitle;
		const node = document.getElementById(`lhe_button_title_${this.formId}`);
		const nodeBlock = document.getElementById(`feed-add-post-block${this.formId}`);
		const stv = (document.getElementById('show_title') || {});

		if(show)
		{
			BX.show(document.getElementById('blog-title'));
			if (document.getElementById('POST_TITLE'))
			{
				document.getElementById('POST_TITLE').focus();
			}

			this.formParams.showTitle = true;
			stv.value = 'Y';

			if (node)
			{
				node.classList.add('feed-add-post-form-btn-active');
			}

			if (nodeBlock)
			{
				nodeBlock.classList.add('blog-post-edit-open');
			}
		}
		else
		{
			BX.hide(document.getElementById('blog-title'));
			this.formParams.showTitle = false;
			stv.value = "N";
			if (node)
			{
				node.classList.remove('feed-add-post-form-btn-active');
			}
		}

		if (saveChanges)
		{
			BX.userOptions.save('socialnetwork', 'postEdit', 'showTitle', (this.formParams.showTitle ? 'Y' : 'N'));
		}
		else
		{
			this.formParams.showTitle = showTitleValue;
		}
	}

	setBlogPostFormSubmitted(value)
	{
		if (document.getElementById('blog-submit-button-save'))
		{
			if (value)
			{
				document.getElementById('blog-submit-button-save').classList.add('ui-btn-clock');
			}
			else
			{
				document.getElementById('blog-submit-button-save').classList.remove('ui-btn-clock');
			}
		}

		this.formParams.submitted = value;
		this.disabled = value;
	};

	submitBlogPostForm(editor, value)
	{
		if (this.disabled)
		{
			return;
		}

		if (!Type.isObject(editor))
		{
			value = editor;
			editor = LHEPostForm.getEditor(this.formParams.editorID);
		}

		if (
			editor
			&& editor.id === this.formParams.editorID
		)
		{
			if(this.formParams.submitted)
			{
				return false;
			}

			editor.SaveContent();

			if(!value)
			{
				value = 'save';
			}

			if(document.getElementById('blog-title').style.display === 'none')
			{
				document.getElementById('POST_TITLE').value = '';
			}

			const submitButton = this.getSubmitButton({
				buttonType: value,
			});

			if (submitButton)
			{
				submitButton.classList.add('ui-btn-clock');
				this.disabled = true;

				window.addEventListener('beforeunload', (event) => { // is called on every sumbit, with or without dialog
					setTimeout(() => {
						BX.removeClass(submitButton, 'ui-btn-clock');
						this.disabled = false;
						this.formParams.submitted = false;
					}, 3000); // timeout needed to process a form on a back-end
				});
			}

			let actionUrl = '';
			const activeTab = PostFormTabs.getInstance().active;

			if (Type.isStringFilled(activeTab))
			{
				actionUrl = document.getElementById(this.formId).action;
				Uri.removeParam(actionUrl, [ 'b24statTab' ]);
				Uri.addParam(actionUrl, {
					b24statTab: activeTab
				});
				document.getElementById(this.formId).action = actionUrl;
			}

			if (
				[
					PostFormTabs.getInstance().config.id.message,
					PostFormTabs.getInstance().config.id.file,
					PostFormTabs.getInstance().config.id.gratitude,
					PostFormTabs.getInstance().config.id.important,
					PostFormTabs.getInstance().config.id.vote,
				].includes(activeTab)
			)
			{
				if (!this.checkDestinationValue({
					buttonType: value,
				}))
				{
					return;
				}
			}

			if (
				activeTab === PostFormTabs.getInstance().config.id.gratitude
				&& PostFormGratSelector.getInstance()
			)
			{
				if (!this.checkEmployeesValue({
					buttonType: value,
				}))
				{
					return;
				}
			}

			setTimeout(()=> {
				BX.submit(document.getElementById(this.formId), value);
				this.formParams.submitted = true;
			}, 100);

		}
	};

	checkDestinationValue({ buttonType }): boolean
	{
		if (Type.isUndefined(MPFEntitySelector))
		{
			return true;
		}

		const tagSelector = new MPFEntitySelector({
			id: `oPostFormLHE_${this.formId}`,
		});
		if (
			!tagSelector
			|| !Type.isArray(tagSelector.tags)
			|| tagSelector.tags.length > 0
		)
		{
			return true;
		}

		this.enableSubmitButton({
			buttonType,
		});

		this.showBottomAlert({
			text: Loc.getMessage('BLOG_POST_EDIT_T_GRAT_ERROR_NO_DESTINATION'),
		});

		tagSelector.subscribeOnce('onContainerClick', this.hideAlert);

		return false;
	}

	checkEmployeesValue({ buttonType }): boolean
	{
		const employeesValueNode = document.getElementById(this.formId).elements[PostFormGratSelector.getInstance().config.fields.employeesValue.name];
		if (
			employeesValueNode
			&& Type.isStringFilled(employeesValueNode.value)
			&& employeesValueNode.value !== '[]'
		)
		{
			return true;
		}

		this.enableSubmitButton({
			buttonType,
		});

		this.showBottomAlert({
			text: Loc.getMessage('BLOG_POST_EDIT_T_GRAT_ERROR_NO_EMPLOYEES'),
		});

		return false;
	}

	checkHideAlert(event) {
		const { type } = event.getData();
		if (type === PostFormTabs.getInstance().config.id.gratitude)
		{
			return;
		}

		this.hideAlert();
	}

	hideAlert()
	{
		const alertNode = document.getElementById('feed-add-post-bottom-alertblogPostForm');
		if (!alertNode)
		{
			return;
		}

		Dom.clean(alertNode);
	}

	enableSubmitButton({ buttonType })
	{
		const submitButton = this.getSubmitButton({
			buttonType,
		});

		if (submitButton)
		{
			submitButton.classList.remove('ui-btn-clock');
			this.disabled = false;
		}
	}

	getSubmitButton({ buttonType })
	{
		let result = null;

		if (
			buttonType === 'save'
			&& document.getElementById('blog-submit-button-save')
		)
		{
			result = document.getElementById('blog-submit-button-save');
		}
		else if (
			buttonType === 'draft'
			&& document.getElementById('blog-submit-button-draft')
		)
		{
			result = document.getElementById('blog-submit-button-draft');
		}

		return result;
	}

	showBottomAlert(params)
	{
		if (
			!Type.isPlainObject(params)
			|| !Type.isStringFilled(params.text)
		)
		{
			return;
		}

		const alertNode = document.getElementById('feed-add-post-bottom-alertblogPostForm');
		if (alertNode)
		{
			Dom.clean(alertNode);
			alertNode.appendChild(Dom.create('div', {
				props: {
					className: 'ui-alert ui-alert-danger',
				},
				children: [
					Dom.create('span', {
						props: {
							className: 'ui-alert-message',
						},
						text: params.text,
					}),
				],
			}));
		}
	}

	onHandlerInited(obj, form)
	{
		if (form !== this.formId)
		{
			return;
		}

		this.formParams.handler = obj;

		EventEmitter.subscribe(obj.eventNode, 'OnControlClick', () => {
			PostFormTabs.getInstance().changePostFormTab(PostFormTabs.getInstance().config.id.message);
		});

		EventEmitter.subscribe(obj.eventNode, 'OnAfterShowLHE', this.OnAfterShowLHE.bind(this));
		EventEmitter.subscribe(obj.eventNode, 'OnAfterHideLHE', this.OnAfterHideLHE.bind(this));

		if (obj.eventNode.style.display == 'none')
		{
			this.OnAfterHideLHE();
		}
		else
		{
			this.OnAfterShowLHE();
		}
	}

	OnAfterShowLHE()
	{
		const div = [
			document.getElementById('feed-add-post-form-notice-blockblogPostForm'),
			document.getElementById('feed-add-buttons-blockblogPostForm'),
			document.getElementById('feed-add-post-bottom-alertblogPostForm'),
			document.getElementById('feed-add-post-content-message-add-ins')
		];

		for (let ii = 0; ii < div.length; ii++)
		{
			if (!div[ii])
			{
				continue;
			}

			div[ii].classList.remove('feed-post-form-block-hidden');
		}

		if(this.formParams.showTitle)
		{
			this.showPanelTitle(true, false);
		}

	};

	OnAfterHideLHE()
	{
		const div = [
			document.getElementById('feed-add-post-form-notice-blockblogPostForm'),
			document.getElementById('feed-add-buttons-blockblogPostForm'),
			document.getElementById('feed-add-post-bottom-alertblogPostForm'),
			document.getElementById('feed-add-post-content-message-add-ins')
		];

		for (let ii = 0; ii < div.length; ii++)
		{
			if (!div[ii])
			{
				continue;
			}

			div[ii].classList.add('feed-post-form-block-hidden');
		}

		if(this.formParams.showTitle)
		{
			this.showPanelTitle(false, false);
		}
	};

	onEditorInited(editor)
	{
		if (PostForm.getInstance().initedEditorsList.includes(editor.id))
		{
			return;
		}

		if (editor.id !== this.formParams.editorID)
		{
			return;
		}

		this.formParams.editor = editor;
		if (this.formParams.autoSave !== 'N')
		{
			new PostFormAutoSave(this.formParams.autoSave, this.formParams.restoreAutosave);
		}

		const f = window[editor.id + 'Files'];
		const handler = LHEPostForm.getHandler(editor.id);
		const needToReparse = [];

		let node = null;
		let controller = null;

		for (let id in handler.controllers)
		{
			if (!handler.controllers.hasOwnProperty(id))
			{
				continue
			}

			if (
				handler.controllers[id].parser
				&& handler.controllers[id].parser.bxTag === 'postimage'
			)
			{
				controller = handler.controllers[id];
				break;
			}
		}

		const closure = (a, b) => { return () => { a.insertFile(b); } };
		const closure2 = (a, b, c) => { return () => {
			if (controller)
			{
				controller.deleteFile(b, {});
				Dom.remove(document.getElementById(`wd-doc'${b}`));
				Ajax({ method: 'GET', url: c});
			}
			else
			{
				a.deleteFile(b, c, a, { controlID : 'common' });
			}
		} };

		for (let intId in f)
		{
			if (!f.hasOwnProperty(intId))
			{
				continue;
			}

			if (controller)
			{
				controller.addFile(f[intId]);
			}
			else
			{
				let id = handler.checkFile(intId, "common", f[intId]);
				needToReparse.push(intId);

				if (
					!!id
					&& document.getElementById(`wd-doc${intId}`)
					&& !document.getElementById(`wd-doc${intId}`).hasOwnProperty('bx-bound'))
				{
					BX(`wd-doc${intId}`).setAttribute('bx-bound', 'Y');

					if (
						(node = document.getElementById(`wd-doc${intId}`).querySelector('.feed-add-img-wrap'))
						&& node
					)
					{
						Event.bind(node, 'click', closure(handler, id));
						node.style.cursor = 'pointer';
					}

					if (
						(node = document.getElementById(`wd-doc${intId}`).querySelector('.feed-add-img-title'))
						&& node
					)
					{
						Event.bind(node, 'click', closure(handler, id));
						node.style.cursor = 'pointer';
					}
				}
			}

			if (
				(node = document.getElementById(`wd-doc${intId}`).querySelector('.feed-add-post-del-but'))
				&& node
			)
			{
				Event.bind(node, 'click', closure2(handler, intId, f[intId].del_url));
				node.style.cursor = "pointer";
			}
		}

		if (needToReparse.length > 0)
		{
			editor.SaveContent();
			let content = editor.GetContent();
			content = content.replace(new RegExp('\\&\\#91\\;IMG ID=(' + needToReparse.join('|') + ')([WIDTHHEIGHT=0-9 ]*)\\&\\#93\\;','gim'), '[IMG ID=$1$2]');
			editor.SetContent(content);
			editor.Focus();
		}
		PostForm.getInstance().initedEditorsList.push(editor.id);

		EventEmitter.subscribe(editor, 'OnSetViewAfter', () => {
			if (this.formParams.createdFromEmail)
			{
				if (editor.GetContent() === '')
				{
					editor.SetContent(`${Loc.getMessage('CREATED_ON_THE_BASIC_OF_THE_MESSAGE')}`);
				}
				editor.Focus(true);
			}
		});
	}

	reinit()
	{
		if (!this.formParams.editorID)
		{
			return;
		}

		if (Type.isFunction(this.formParams.editor))
		{
			this.formParams.editor(this.formParams.text);
		}
		else
		{
			setTimeout(this.reinit, 50);
		}
	};
}
