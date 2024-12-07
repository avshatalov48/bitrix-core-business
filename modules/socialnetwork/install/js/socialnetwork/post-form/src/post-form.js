import {
	ajax,
	AjaxError,
	AjaxResponse,
	Dom,
	Loc,
	Reflection,
	Runtime,
	Tag,
	Text,
	Type,
} from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { TagSelector } from 'ui.entity-selector';
import { Button, ButtonColor } from 'ui.buttons';
import { UploaderFile } from 'ui.uploader.core';
import { PostData } from './post-data';
import { PostFormManager } from './post-form-manager';
import { PostFormRouter } from './post-form-router';
import { PostFormTags } from './post-form-tags';

const UserOptions = Reflection.namespace('BX.userOptions');
const NotificationCenter = Reflection.namespace('BX.UI.Notification.Center');

import 'ui.alerts';
import 'ui.notification';
import 'ui.icon-set.actions';

import './css/form.css';

type Params = {
	postId?: number,
	groupId?: number,
	pathToDefaultRedirect?: string,
	pathToGroupRedirect?: string,
}

export type PostFormData = {
	title: string,
	message: string,
	recipients: string,
	fileIds: Array,
	tags: string,
}

export type InitData = {
	isShownPostTitle: 'Y' | 'N',
	title?: string,
	message?: string,
	recipients?: string,
	fileIds?: Array<string | number>,
	allUsersTitle: string,
	allowEmailInvitation: boolean,
	allowToAll: boolean,
}

export class PostForm extends EventEmitter
{
	#postId: number;
	#groupId: number;

	#isShownPostTitle: boolean;

	#initData: InitData;

	#formId: string;
	#jsObjName: string;
	#LHEId: string;
	#sended: boolean;
	#editMode: boolean;

	#popup: Popup;
	#sendBtn: Button;
	#postData: PostData;
	#postFormManager: PostFormManager;
	#postFormRouter: PostFormRouter;
	#postFormTags: PostFormTags;
	#node: HTMLElement;
	#titleNode: HTMLElement;
	#recipientSelector: HTMLElement;

	#errorLayout: {
		container: HTMLElement,
		message: HTMLElement,
	};

	#selector: TagSelector;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.PostForm');

		this.#postId = (
			Type.isInteger(parseInt(params.postId, 10))
				? parseInt(params.postId, 10)
				: 0
		);
		this.#groupId = (
			Type.isInteger(parseInt(params.groupId, 10))
				? parseInt(params.groupId, 10)
				: 0
		);

		this.#formId = `blogPostForm_${Text.getRandom().toLowerCase()}`;
		this.#jsObjName = `oPostFormLHE_blogPostForm${this.#formId}`;
		this.#LHEId = `idPostFormLHE_${this.#formId}`;
		this.#sended = false;
		this.#editMode = this.#postId > 0;

		this.#postFormRouter = new PostFormRouter({
			pathToDefaultRedirect: params.pathToDefaultRedirect,
			pathToGroupRedirect: params.pathToGroupRedirect,
		});

		this.#errorLayout = {};
	}

	show(): Promise
	{
		if (this.#popup)
		{
			return new Promise((resolve, reject) => {
				this.#popup.subscribeOnce('onShow', () => {
					resolve();
				});

				this.#popup.show();
			});
		}

		return new Promise((resolve, reject) => {
			this.#init()
				.then(() => {
					this.#createPopup();

					this.#popup.subscribeOnce('onShow', () => {
						resolve();
					});

					this.#popup.show();
				})
				.catch(() => reject())
			;
		});
	}

	#init(): Promise
	{
		return ajax.runAction('socialnetwork.api.livefeed.blogpost.getPostFormInitData', {
			data: {
				postId: this.#postId,
				groupId: this.#groupId,
			},
		})
			.then((response: AjaxResponse) => {
				this.#initData = response.data;

				this.#postData = new PostData(this.#initData);

				this.#isShownPostTitle = this.#initData.isShownPostTitle === 'Y';

				this.#postFormManager = new PostFormManager({
					formId: this.#formId,
					LHEId: this.#LHEId,
					isShownPostTitle: this.#isShownPostTitle,
				});
				this.#postFormManager.subscribe(
					'editorInited',
					this.#afterEditorInit.bind(this),
				);
				this.#postFormManager.subscribe(
					'toggleVisibilityPostTitle',
					this.#toggleVisibilityPostTitle.bind(this),
				);
				this.#postFormManager.subscribe(
					'fullscreenExpand',
					this.#changePostFormPosition.bind(this),
				);
				this.#postFormManager.subscribe(
					'addMention',
					this.#addMention.bind(this),
				);
				this.#postFormManager.subscribe(
					'showControllers',
					this.#showControllers.bind(this),
				);

				return this;
			})
			.catch((error: AjaxError) => {
				this.#consoleError('init', error);
			})
		;
	}

	#createPopup()
	{
		this.#popup = new Popup(
			{
				id: this.#formId,
				className: 'sn-post-form-popup --normal',
				content: this.#renderForm(),
				contentNoPaddings: true,
				minHeight: 370,
				width: 720,
				disableScroll: true,
				draggable: false,
				overlay: true,
				padding: 0,
				buttons: [
					this.#sendBtn = new Button({
						text: Loc.getMessage('SN_PF_SEND_BTN'),
						color: ButtonColor.PRIMARY,
						onclick: () => {
							this.#sendForm();
						},
					}),
					new Button({
						text: Loc.getMessage('SN_PF_CANCEL_BTN'),
						color: ButtonColor.LINK,
						onclick: () => {
							this.#popup.close();
						},
					}),
				],
				events: {
					onFirstShow: this.#firstShow.bind(this),
					onAfterShow: this.#onAfterShow.bind(this),
					onAfterClose: this.#afterClose.bind(this),
				},
			},
		);
	}

	#firstShow()
	{
		this.#sendBtn.setWaiting(true);

		this.#initRecipientSelector();

		// eslint-disable-next-line promise/catch-or-return
		this.#renderMainPostForm()
			.then((runtimePromise: Promise) => {
				// eslint-disable-next-line promise/catch-or-return,promise/no-nesting
				runtimePromise.then(() => {
					this.#postFormManager.initLHE();
				});
			})
		;
	}

	#onAfterShow(): void
	{
		this.#initTagsSelector();

		this.#postFormManager.focusToEditor();
	}

	#afterClose()
	{
		if (this.#sended)
		{
			this.#clearForm();

			if (BX.Livefeed && BX.Livefeed.PageInstance)
			{
				BX.Livefeed.PageInstance.refresh();
			}
			else
			{
				this.#postFormRouter.redirectTo(this.#groupId);
			}
		}
	}

	#sendForm()
	{
		if (this.#sendBtn.isWaiting())
		{
			return;
		}

		this.#hideError();

		this.#postData.setFormData(this.#collectFormData());
		const errorMessage = this.#postData.validateRequestData();
		if (errorMessage)
		{
			this.#showError(errorMessage);

			this.#postFormManager.focusToEditor();

			return;
		}

		this.#sendBtn.setWaiting(true);

		const action = `socialnetwork.api.livefeed.blogpost.${this.#postId ? 'update' : 'add'}`;

		const data = this.#postId
			? {
				id: this.#postId,
				params: this.#postData.prepareRequestData(),
			}
			: {
				params: this.#postData.prepareRequestData(),
			}
		;

		ajax.runAction(action, {
			data,
			analyticsLabel: {
				b24statAction: 'addLogEntry',
				b24statContext: 'spaces',
			},
		})
			.then((response: AjaxResponse) => {
				this.#sended = true;
				this.#popup.close();
			})
			.catch((error: AjaxError) => {
				this.#sendBtn.setWaiting(false);
				this.#consoleError('sendForm', error);
			})
		;
	}

	#clearForm(): void
	{
		this.#postData.setData(this.#initData);

		this.#clearSelector();
		this.#titleNode.querySelector('input').value = '';
		this.#postFormManager.clearEditorText();
		this.#clearFiles();
		this.#postFormTags.clear();

		this.#sended = false;

		this.#sendBtn.setWaiting(false);
	}

	#collectFormData(): PostFormData
	{
		const postFormData = {
			title: this.#titleNode.querySelector('input').value,
			message: this.#postFormManager.getEditorText(),
		};

		postFormData.recipients = this.#postData.getRecipients();

		const fileIds = [];
		const userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(this.#formId);
		userFieldControl.getFiles().forEach((file: UploaderFile) => {
			if (file.getServerFileId() !== null)
			{
				fileIds.push(file.getServerFileId());
			}
		});
		postFormData.fileIds = fileIds;

		if (this.#postFormTags.isFilled())
		{
			postFormData.tags = this.#postFormTags.getValue();
		}

		return postFormData;
	}

	#clearFiles()
	{
		const userFieldControl = BX.Disk.Uploader.UserFieldControl.getById(this.#formId);

		userFieldControl.clear();
		userFieldControl.hide();
	}

	#showError(message: string): void
	{
		Dom.removeClass(this.#errorLayout.container, '--hidden');

		this.#errorLayout.message.textContent = Text.encode(message);
	}

	#hideError(): void
	{
		Dom.addClass(this.#errorLayout.container, '--hidden');

		this.#errorLayout.message.textContent = '';
	}

	#renderMainPostForm(): Promise
	{
		return ajax.runAction('socialnetwork.api.livefeed.blogpost.getMainPostForm', {
			data: {
				params: {
					formId: this.#formId,
					jsObjName: this.#jsObjName,
					LHEId: this.#LHEId,
					postId: this.#postId,
					text: this.#postData.getMessage(),
				},
			},
		})
			.then((response: AjaxResponse) => {
				return Runtime.html(
					this.#node.querySelector('#sn-post-form'),
					response.data.html,
					{ htmlFirst: true },
				);
			})
			.catch((error: AjaxError) => {
				this.#consoleError('afterShow', error);
			})
		;
	}

	#renderForm(): HTMLElement
	{
		this.#node = Tag.render`
			<div class="sn-post-form__discussion">
				${this.#renderErrorAlert()}
				${this.#renderRecipientSelector()}
				${this.#renderTitle()}
				<div id="sn-post-form"></div>
			</div>
		`;

		return this.#node;
	}

	#renderErrorAlert(): HTMLElement
	{
		const { container, message } = Tag.render`
			<div
				class="sn-post-form__discussion-error-alert ui-alert ui-alert-danger --hidden"
				ref="container"
			>
				<span class="ui-alert-message" ref="message"></span>
			</div>
		`;

		this.#errorLayout.container = container;
		this.#errorLayout.message = message;

		return container;
	}

	#renderRecipientSelector(): HTMLElement
	{
		this.#recipientSelector = Tag.render`
			<div class="sn-post-form__discussion-row"></div>
		`;

		return this.#recipientSelector;
	}

	#initRecipientSelector(): TagSelector
	{
		const selectorId = 'sn-post-form-recipient-selector';

		this.#selector = new TagSelector({
			id: selectorId,
			dialogOptions: {
				id: selectorId,
				context: 'PostForm',
				preselectedItems: (
					Type.isStringFilled(this.#postData.getRecipients())
						? JSON.parse(this.#postData.getRecipients())
						: []
				),
				entities: [
					{
						id: 'meta-user',
						options: {
							'all-users': {
								title: this.#postData.getAllUsersTitle(),
								allowView: this.#postData.isAllowToAll(),
							},
						},
					},
					{
						id: 'user',
						options: {
							emailUsers: this.#postData.isAllowEmailInvitation(),
							inviteGuestLink: this.#postData.isAllowEmailInvitation(),
							myEmailUsers: true,
						},
					},
					{
						id: 'project',
						options: {
							features: {
								blog: [
									'premoderate_post',
									'moderate_post',
									'write_post',
									'full_post',
								],
							},
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersAndDepartments',
							allowFlatDepartments: false,
						},
					},
				],
				events: {
					'Item:onSelect': () => {
						this.#changeSelectedRecipients(this.#selector.getDialog().getSelectedItems());
					},
					'Item:onDeselect': () => {
						this.#changeSelectedRecipients(this.#selector.getDialog().getSelectedItems());
					},
				},
			},
		});

		this.#selector.renderTo(this.#recipientSelector);

		return this.#selector;
	}

	#clearSelector()
	{
		Dom.clean(this.#recipientSelector);

		this.#initRecipientSelector();
	}

	#initTagsSelector()
	{
		if (!this.#postFormTags)
		{
			this.#postFormTags = new PostFormTags(this.#formId, this.#node);
		}
	}

	#changeSelectedRecipients(selectedItems: Array): void
	{
		const recipients = [];

		selectedItems.forEach((item) => {
			recipients.push([item.entityId, item.id]);
		});

		this.#postData.setRecipients(recipients.length > 0 ? JSON.stringify(recipients) : '');
	}

	#renderTitle(): HTMLElement
	{
		const uiClasses = 'ui-ctl ui-ctl-textbox ui-ctl-no-border ui-ctl-w100 ui-ctl-no-padding ui-ctl-xs';
		const hiddenClass = this.#isShownPostTitle ? '' : '--hidden';

		this.#titleNode = Tag.render`
			<div class="sn-post-form__discussion-row ${hiddenClass}">
				<div class="${uiClasses}">
					<input
						type="text"
						class="ui-ctl-element sn-post-form__discussion_title"
						placeholder="${Loc.getMessage('SN_PF_TITLE_PLACEHOLDER')}"
						data-id="sn-post-form-title-input"
						value="${Text.encode(this.#postData.getTitle())}"
					>
				</div>
			</div>
		`;

		return this.#titleNode;
	}

	#afterEditorInit()
	{
		this.#sendBtn.setWaiting(false);
	}

	#toggleVisibilityPostTitle()
	{
		Dom.toggleClass(this.#titleNode, '--hidden');

		const isShown = !Dom.hasClass(this.#titleNode, '--hidden');
		if (isShown)
		{
			this.#titleNode.querySelector('input').focus();
		}

		UserOptions.save('socialnetwork', 'postEdit', 'showTitle', (isShown ? 'Y' : 'N'));
	}

	#changePostFormPosition()
	{
		Dom.toggleClass(this.#popup.getPopupContainer(), '--normal');
	}

	#addMention(baseEvent: BaseEvent)
	{
		const { type, entity, entityType } = baseEvent.getData();

		this.#selector
			.getDialog()
			.addItem({
				avatar: entity.avatar,
				customData: {
					email: Type.isStringFilled(entity.email) ? entity.email : '',
				},
				entityId: type,
				entityType: entityType,
				id: entity.entityId,
				title: entity.name,
			})
			.select()
		;
	}

	#showControllers(baseEvent: BaseEvent)
	{
		const contentContainer = this.#popup.getContentContainer();

		contentContainer.scrollTo({
			top: contentContainer.scrollHeight - contentContainer.clientHeight,
			behavior: 'smooth',
		});
	}

	#consoleError(action: string, error: AjaxError)
	{
		// todo
		NotificationCenter.notify({
			content: Loc.getMessage('SN_PF_REQUEST_ERROR'),
		});

		// eslint-disable-next-line no-console
		console.error(`PostForm: ${action} error`, error);
	}
}
