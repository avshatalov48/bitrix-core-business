import {Cache, Tag, Text, Dom, Loc} from 'main.core';
import {Loader} from 'main.loader';

import './css/style.css';

export default class Status
{
	cache = new Cache.MemoryCache();

	static formatSize(bytes: number): {number: number, text: string}
	{
		if (bytes === 0)
		{
			return `0 ${Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_B')}`;
		}

		const sizes = [
			Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_B'),
			Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_KB'),
			Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE_MB'),
		];

		const textIndex = Math.floor(Math.log(bytes) / Math.log(1024));

		return {
			number: parseFloat((bytes / Math.pow(1024, textIndex)).toFixed(2)),
			text: sizes[textIndex],
		};
	}

	getUploadStatusLayout(): HTMLDivElement
	{
		return this.cache.remember('statusLayout', () => {
			const loaderLayout = Tag.render`
				<div class="ui-stamp-uploader-upload-status-loader"></div>
			`;
			const loader = new Loader({target: loaderLayout, mode: 'inline', size: 45});
			void loader.show();

			return Tag.render`
				<div class="ui-stamp-uploader-upload-status">
					${loaderLayout}
					<div class="ui-stamp-uploader-upload-status-text">
						${Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_TEXT')}
					</div>
					<div class="ui-stamp-uploader-upload-status-percent">
						${Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_PERCENT')}
					</div>
					<div class="ui-stamp-uploader-upload-status-size">
						${Loc.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE')}
					</div>
				</div>
			`;
		});
	}

	updateUploadStatus(options: {percent: number, size: number} = {percent: 0, size: 0})
	{
		const percentNode = this.cache.remember('percentNode', () => {
			return this.getUploadStatusLayout().querySelector('.ui-stamp-uploader-upload-status-percent');
		});

		const sizeNode = this.cache.remember('sizeNode', () => {
			return this.getUploadStatusLayout().querySelector('.ui-stamp-uploader-upload-status-size');
		});

		percentNode.innerHTML = (
			Loc
				.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_PERCENT')
				.replace('{{number}}', `<strong>${Text.encode(options.percent)}</strong>`)
		);

		const formatted = Status.formatSize(options.size);
		sizeNode.textContent = (
			Loc
				.getMessage('UI_STAMP_UPLOADER_UPLOAD_STATUS_SIZE')
				.replace('{{number}}', formatted.number)
				.replace('{{text}}', formatted.text)
		);
	}

	getPreparingStatusLayout(): HTMLDivElement
	{
		return this.cache.remember('preparingStatusLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-preparing-status">
					<div class="ui-stamp-uploader-preparing-status-icon"></div>
					<div class="ui-stamp-uploader-preparing-status-text">
						${Loc.getMessage('UI_STAMP_UPLOADER_PREPARING_STATUS')}		
					</div>
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-status"></div>
			`;
		});
	}

	showUploadStatus(options: {reset: boolean} = {reset: false})
	{
		const layout = this.getLayout();
		const uploadStatusLayout = this.getUploadStatusLayout();
		const preparingStatusLayout = this.getPreparingStatusLayout();

		Dom.remove(preparingStatusLayout);
		Dom.append(uploadStatusLayout, layout);

		if (options.reset === true)
		{
			this.updateUploadStatus({percent: 0, size: 0});
		}

		this.setOpacity(1);

		this.show();
	}

	showPreparingStatus()
	{
		const layout = this.getLayout();
		const uploadStatusLayout = this.getUploadStatusLayout();
		const preparingStatusLayout = this.getPreparingStatusLayout();

		Dom.remove(uploadStatusLayout);
		Dom.append(preparingStatusLayout, layout);

		this.setOpacity(.45);

		this.show();
	}

	setOpacity(value: number)
	{
		Dom.style(this.getLayout(), 'background-color', `rgba(255, 255, 255, ${value})`);
	}

	hide()
	{
		Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-status-show');
	}

	show()
	{
		Dom.addClass(this.getLayout(), 'ui-stamp-uploader-status-show');
	}
}