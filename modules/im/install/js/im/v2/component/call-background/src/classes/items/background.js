import {Loc} from 'main.core';

import {FileStatus} from 'im.v2.const';

import type {BackgroundRestResult} from '../../types/rest';

export type UploadState = {
	progress: number,
	status: string,
	size: number
};

export class Background
{
	id: string = '';
	title: string = '';
	background: string = '';
	preview: string = '';
	isVideo: boolean = false;
	isSupported: boolean = true;
	isCustom: boolean = false;
	canRemove: boolean = false;

	isLoading: boolean = false;
	uploadState: UploadState = null;

	constructor(params)
	{
		Object.assign(this, params);
	}

	static createDefaultFromRest(restItem: BackgroundRestResult): Background
	{
		return new Background({
			...restItem,
			isVideo: restItem.id.includes(':video'),
			isCustom: false,
			canRemove: false,
			isSupported: true
		});
	}

	static createCustomFromRest(restItem: BackgroundRestResult): Background
	{
		let title = Loc.getMessage('BX_IM_CALL_BG_CUSTOM');
		if (!restItem.isSupported)
		{
			title = Loc.getMessage('BX_IM_CALL_BG_UNSUPPORTED');
		}

		return new Background({
			...restItem,
			title,
			isCustom: true,
			canRemove: true
		});
	}

	static createCustomFromUploaderEvent(uploaderData: {id: string, filePreview: string, file: File}): Background
	{
		const {id, filePreview, file} = uploaderData;

		return new Background({
			id: id,
			background: filePreview,
			preview: filePreview,
			title: Loc.getMessage('BX_IM_CALL_BG_CUSTOM'),
			isVideo: file.type.startsWith('video'),
			isCustom: true,
			canRemove: false,
			isSupported: true,
			isLoading: true,
			uploadState: {
				progress: 0,
				status: FileStatus.upload,
				size: file.size,
			}
		});
	}

	setUploadProgress(progress: number)
	{
		this.uploadState.progress = progress;
	}

	setUploadError()
	{
		this.uploadState.status = FileStatus.error;
		this.uploadState.progress = 0;
	}

	onUploadComplete(fileResult)
	{
		this.id = fileResult.id;

		if (this.isVideo)
		{
			this.background = fileResult.links.download;
		}

		this.isLoading = false;
		this.canRemove = true;
	}
}