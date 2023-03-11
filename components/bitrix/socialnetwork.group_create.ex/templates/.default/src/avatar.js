import { Type, Loc, Dom, ajax } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button } from 'ui.buttons';

export class Avatar
{
	static classList = {
		hidden: '--hidden',
		selected: '--selected',
	};

	constructor(
		params: {
			componentName: string,
			signedParameters: string,
			groupId: number,
		}
	)
	{
		this.confirmPopup = null;

		if (
			!Type.isStringFilled(params.componentName)
			|| Type.isUndefined(params.signedParameters)
		)
		{
			return;
		}

		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.groupId = (!Type.isUndefined(params.groupId) ? parseInt(params.groupId) : 0);

		const container = document.querySelector('[data-role="group-avatar-cont"]');
		if (!container)
		{
			return;
		}

		this.selectorNode = container.querySelector('[data-role="group-avatar-selector"]');
		this.imageNode = container.querySelector('[data-role="group-avatar-image"]');
		this.inputNode = container.querySelector('[data-role="group-avatar-input"]');
		this.typeInputNode = container.querySelector('[data-role="group-avatar-type-input"]');
		this.removeNode = container.querySelector('[data-role="group-avatar-remove"]');

		if (
			!Type.isDomNode(this.imageNode)
			|| !Type.isDomNode(this.inputNode)
			|| !Type.isDomNode(this.typeInputNode)
			|| !Type.isDomNode(this.removeNode)
		)
		{
			return;
		}

		this.recalc();

		const avatarEditor = new BX.AvatarEditor({
			enableCamera: false,
		});

		this.selectorNode.addEventListener('click', (e) => {

			if (
				e.target.getAttribute('data-role') === 'group-avatar-remove'
				&& this.imageNode.style.backgroundImage !== ''
			)
			{
				this.showConfirmPopup(Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM'), this.deletePhoto.bind(this));
			}
			else if (e.target.getAttribute('data-role') === 'group-avatar-type')
			{
				this.clearType();
				this.setType(e.target.getAttribute('data-avatar-type'));
			}
			else if (e.target.getAttribute('data-role') === 'group-avatar-image')
			{
				avatarEditor.show('file');
			}
		});

		EventEmitter.subscribe('onApply', (event: BaseEvent) => {
			const [ file ] = event.getCompatData();

			const formData = new FormData();
			if (!file.name)
			{
				file.name = 'tmp.png';
			}
			formData.append('newPhoto', file, file.name);

			this.changePhoto(formData);
		});
	}

	recalc()
	{
		if (this.getFileId() <= 0)
		{
			this.removeNode.classList.add(Avatar.classList.hidden);
			this.imageNode.classList.remove(Avatar.classList.selected);
		}
		else
		{
			this.removeNode.classList.remove(Avatar.classList.hidden);
			this.imageNode.classList.add(Avatar.classList.selected);
		}
	}

	changePhoto(formData)
	{
		const loader = this.showLoader({
			node: this.imageNode,
			loader: null,
			size: 78,
		});

		ajax.runComponentAction(this.componentName, 'loadPhoto', {
			signedParameters: this.signedParameters,
			mode: 'ajax',
			data: formData,
		}).then((response) => {

			if (
				Type.isPlainObject(response.data)
				&& parseInt(response.data.fileId) > 0
				&& Type.isStringFilled(response.data.fileUri)
			)
			{
				this.clearType();
				this.inputNode.value = parseInt(response.data.fileId);
				this.typeInputNode.value = '';
				this.imageNode.style = `background-image: url('${encodeURI(response.data.fileUri)}'); background-size: cover;`;
				this.recalc();
			}

			this.hideLoader({
				loader: loader,
			});
		}, (response) => {
			this.hideLoader({loader: loader});
			this.showErrorPopup(response["errors"][0].message);
		});
	}

	deletePhoto()
	{
		const fileId = this.getFileId();
		if (fileId < 0)
		{
			return;
		}

		const loader = this.showLoader({
			node: this.imageNode,
			loader: null,
			size: 78,
		});

		ajax.runComponentAction(this.componentName, 'deletePhoto', {
			signedParameters: this.signedParameters,
			mode: 'ajax',
			data: {
				fileId: fileId,
				groupId: this.groupId,
			}
		}).then((response) => {

			this.imageNode.style = '';
			this.inputNode.value = '';
			this.recalc();

			this.hideLoader({
				loader: loader,
			});
		}, (response) => {
			this.hideLoader({
				loader: loader,
			});
			this.showErrorPopup(response.errors[0].message);
		});
	}

	clearType()
	{
		this.selectorNode.querySelectorAll('[data-role="group-avatar-type"]').forEach((typeItemNode) => {
			typeItemNode.classList.remove(Avatar.classList.selected);
		});
	}

	setType(avatarType)
	{
		this.inputNode.value = '';
		this.imageNode.style = '';
		this.typeInputNode.value = avatarType;

		this.imageNode.classList.remove(Avatar.classList.selected);

		this.selectorNode.querySelectorAll('[data-role="group-avatar-type"]').forEach((typeItemNode) => {
			if (typeItemNode.getAttribute('data-avatar-type') !== avatarType)
			{
				return;
			}
			typeItemNode.classList.add(Avatar.classList.selected);
		});


		this.recalc();
	}

	getFileId()
	{
		return (Type.isStringFilled(this.inputNode.value) ? parseInt(this.inputNode.value) : 0);
	}

	showLoader(params)
	{
		let loader = null;

		if (Type.isDomNode(params.node))
		{
			if (Type.isNull(params.loader))
			{
				loader = new BX.Loader({
					target: params.node,
					size: params.hasOwnProperty('size') ? params.size : 40,
				});
			}
			else
			{
				loader = params.loader;
			}

			loader.show();
		}

		return loader;
	}

	hideLoader(params)
	{
		if (!Type.isNull(params.loader))
		{
			params.loader.hide();
			params.loader = null;
		}

		if (Type.isDomNode(params.node))
		{
			Dom.clean(params.node)
		}
	}

	showErrorPopup(error)
	{
		if (!error)
		{
			return;
		}

		new Popup('gce-image-upload-error', null, {
			autoHide: true,
			closeByEsc: true,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			closeIcon: true,
			content: error,
			events: {},
			cacheable: false,
		}).show();
	}

	showConfirmPopup(text, confirmCallback)
	{
		this.confirmPopup = new Popup('gce-image-delete-confirm', null, {
			autoHide: true,
			closeByEsc: true,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			closeIcon: true,
			content: text,
			events: {
				onPopupClose: () => {
					this.confirmPopup.destroy();
				},
			},
			cacheable: false,
			buttons: [
				new Button({
					text: Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_YES'),
					events: {
						click: (button) => {
							button.setWaiting(true);
							this.confirmPopup.close();
							confirmCallback();
						}
					}
				}),
				new Button({
					text: Loc.getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_NO'),
					events : {
						click: () => {
							this.confirmPopup.close();
						}
					}
				}),
			],
		});
		this.confirmPopup.show();
	}
}
