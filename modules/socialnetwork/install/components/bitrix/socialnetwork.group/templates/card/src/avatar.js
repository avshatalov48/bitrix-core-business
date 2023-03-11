import {Type, Dom, ajax} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Loader} from 'main.loader';

class WorkgroupCardAvatar
{
	constructor(params)
	{
		this.componentName = (params.componentName ?? '');
		this.signedParameters = (params.signedParameters ?? '');

		this.containerNode = params.containerNode;
		this.groupId = parseInt(params.groupId);

		this.init();
	}

	init()
	{
		if (!this.containerNode)
		{
			return;
		}

		const avatarEditor = new BX.AvatarEditor({
			enableCamera: false,
		});

		const editButtonNode = this.containerNode.querySelector('.socialnetwork-group-slider-group-logo-btn');
		if (!editButtonNode)
		{
			return;
		}

		editButtonNode.addEventListener('click', () => {
			avatarEditor.show('file');
		});

		EventEmitter.subscribe(avatarEditor, 'onApply', (event: BaseEvent) => {
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

	changePhoto(formData)
	{
		if (this.componentName === '')
		{
			return;
		}

		let loader = null;
		const boxNode = this.containerNode.querySelector('.socialnetwork-group-slider-group-logo-box');

		if (boxNode)
		{
			loader = this.showLoader({
				node: boxNode,
				loader: null,
				size: 50,
			});
		}

		ajax.runComponentAction(this.componentName, 'loadPhoto', {
			signedParameters: this.signedParameters,
			mode: 'ajax',
			data: formData,
		}).then((response) => {

			if (Type.isPlainObject(response.data))
			{
				if (!boxNode)
				{
					return;
				}

				const avatarNode = boxNode.querySelector('i');
				if (!avatarNode)
				{
					return;
				}

				boxNode.className = 'sonet-common-workgroup-avatar socialnetwork-group-slider-group-logo-box';
				if (Type.isStringFilled(response.data.imageSrc))
				{
					boxNode.className += ' ui-icon ui-icon-common-user-group';
					avatarNode.style = `background: url('${encodeURI(response.data.imageSrc)}') no-repeat center center; background-size: cover;`;
				}
				else
				{
					avatarNode.style = 'background: none;';
				}
			}

			this.hideLoader({
				loader: loader,
			});
		}, (response) => {
			this.hideLoader({
				loader: loader,
			});
//			this.showErrorPopup(response["errors"][0].message);
		});
	}

	showLoader(params)
	{
		let loader = null;

		if (Type.isDomNode(params.node))
		{
			if (params.loader === null)
			{
				loader = new Loader({
					target: params.node,
					size: !Type.isUndefined(params.size) ? Number(params.size) : 40,
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
		if (params.loader !== null)
		{
			params.loader.hide();
		}

		if (params.loader !== null)
		{
			params.loader = null;
		}
	}
}

export {
	WorkgroupCardAvatar,
}