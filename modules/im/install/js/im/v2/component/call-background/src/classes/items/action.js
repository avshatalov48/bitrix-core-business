import {Loc} from 'main.core';

export class Action
{
	static type = {
		none: 'none',
		upload: 'upload',
		blur: 'blur',
		gaussianBlur: 'gaussianBlur'
	};

	id: string;
	title: string;
	background: string;

	constructor(type: String)
	{
		let id = Action.type.none;
		let background = Action.type.none;
		let title = Loc.getMessage('BX_IM_CALL_BG_ACTION_NONE');

		if (type === Action.type.upload)
		{
			id = type;
			background = type;
			title = Loc.getMessage('BX_IM_CALL_BG_ACTION_UPLOAD');
		}
		else if (type === Action.type.gaussianBlur)
		{
			id = type;
			background = type;
			title = Loc.getMessage('BX_IM_CALL_BG_ACTION_BLUR');
		}
		else if (type === Action.type.blur)
		{
			id = type;
			background = type;
			title = Loc.getMessage('BX_IM_CALL_BG_ACTION_BLUR_MAX');
		}

		this.id = id;
		this.background = background;
		this.title = title;
	}

	isEmpty(): boolean
	{
		return this.id === Action.type.none;
	}

	isBlur(): boolean
	{
		return this.id === Action.type.gaussianBlur || this.id === Action.type.blur;
	}

	isUpload(): boolean
	{
		return this.id === Action.type.upload;
	}
}