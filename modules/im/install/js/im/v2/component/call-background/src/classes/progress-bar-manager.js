import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Uploader as ProgressBar} from 'ui.progressbarjs.uploader';

import {FileStatus} from 'im.v2.const';

import type {UploadState} from './items/background';

const EVENT_NAMESPACE = 'BX.Messenger.v2.CallBackground.ProgressBar';
const SIZE_LOWER_THRESHOLD = 1024 * 1024 * 2;
const STARTING_PROGRESS = 5;

export class ProgressBarManager extends EventEmitter
{
	static event = {
		cancel: 'cancel',
		destroy: 'destroy'
	};

	container: HTMLElement;
	uploadState: UploadState;

	constructor(params: {container: HTMLElement, uploadState: UploadState})
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		const {container, uploadState} = params;
		this.container = container;
		this.uploadState = uploadState;

		this.progressBar = new ProgressBar({
			...this.#getProgressBarParams(),
			container
		});

		this.#adjustProgressBarTitleVisibility();
	}

	start()
	{
		this.progressBar.start();
		this.update();
	}

	update()
	{
		if (this.uploadState.status === FileStatus.error)
		{
			this.progressBar.setProgress(0);
			this.progressBar.setCancelDisable(false);
			this.progressBar.setIcon(ProgressBar.icon.error);
			this.progressBar.setProgressTitle(Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_ERROR'));
		}
		else if (this.uploadState.status === FileStatus.wait)
		{
			this.progressBar.setProgress(this.item.state.progress > STARTING_PROGRESS? this.item.state.progress: STARTING_PROGRESS);
			this.progressBar.setCancelDisable(true);
			this.progressBar.setIcon(ProgressBar.icon.cloud);
			this.progressBar.setProgressTitle(Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_SAVING'));
		}
		else if (this.uploadState.progress === 100)
		{
			this.progressBar.setProgress(100);
		}
		else if (this.uploadState.progress === -1)
		{
			this.progressBar.setProgress(10);
			this.progressBar.setProgressTitle(Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_WAITING'));
		}
		else
		{
			if (this.uploadState.progress === 0)
			{
				this.progressBar.setIcon(ProgressBar.icon.cancel);
			}
			const progress = this.uploadState.progress > STARTING_PROGRESS ? this.uploadState.progress: STARTING_PROGRESS;
			this.progressBar.setProgress(progress);

			if (this.#isSmallSizeFile())
			{
				this.progressBar.setProgressTitle(Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_LOADING'));
			}
			else
			{
				const byteSent = (this.uploadState.size / 100) * this.uploadState.progress;
				this.progressBar.setByteSent(byteSent, this.uploadState.size);
			}
		}
	}

	destroy()
	{
		this.progressBar.destroy(false);
	}

	#getProgressBarParams()
	{
		return {
			labels: {
				loading: Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_LOADING'),
				completed: Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_COMPLETED'),
				canceled: Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_CANCELED'),
				cancelTitle: Loc.getMessage('BX_IM_CALL_BG_FILE_UPLOAD_CANCEL_TITLE'),
				megabyte: Loc.getMessage('BX_IM_CALL_BG_FILE_SIZE_MB'),
			},
			cancelCallback: () => {
				this.emit(ProgressBarManager.event.cancel);
			},
			destroyCallback: () => {
				this.emit(ProgressBarManager.event.destroy);
			}
		};
	}

	#adjustProgressBarTitleVisibility()
	{
		if (this.#isSmallSizeFile() || this.#isSmallContainer())
		{
			this.progressBar.setProgressTitleVisibility(false);
		}
	}

	#isSmallSizeFile(): boolean
	{
		return this.uploadState.size < SIZE_LOWER_THRESHOLD;
	}

	#isSmallContainer(): boolean
	{
		const WIDTH_LOWER_THRESHOLD = 240;
		const HEIGHT_LOWER_THRESHOLD = 54;

		return this.container.offsetHeight <= HEIGHT_LOWER_THRESHOLD && this.container.offsetWidth < WIDTH_LOWER_THRESHOLD;
	}
}