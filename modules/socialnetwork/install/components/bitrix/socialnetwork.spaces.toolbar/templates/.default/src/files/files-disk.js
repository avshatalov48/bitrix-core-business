import { Cache } from 'main.core';

type Params = {
	diskComponentId: string,
	networkDriveLink: string,
	pathToFilesBizprocWorkflowAdmin?: string,
	listAvailableFeatures: { [key: string]: boolean },
	featureRestrictionMap: { [key: string]: string },
}

export class FilesDisk
{
	#cache = new Cache.MemoryCache();

	constructor(params: Params)
	{
		this.#setParams(params);
	}

	createFolder()
	{
		this.#getDiskFolderList().createFolder();
	}

	appendUploadInput(container: HTMLElement)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
		BX.onCustomEvent(window, 'onDiskUploadPopupShow', [container]);
	}

	hideUploadInput(container: HTMLElement)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
		BX.onCustomEvent(window, 'onDiskUploadPopupClose', [container]);
	}

	runCreatingDocFile(code: string)
	{
		this.#getDiskFolderList().runCreatingFile('docx', code);
	}

	runCreatingTableFile(code: string)
	{
		this.#getDiskFolderList().runCreatingFile('xlsx', code);
	}

	runCreatingPresentationFile(code: string)
	{
		this.#getDiskFolderList().runCreatingFile('pptx', code);
	}

	showRights()
	{
		const listAvailableFeatures: { [key: string]: boolean } = this.#getParam('listAvailableFeatures');
		const featureRestrictionMap: { [key: string]: string } = this.#getParam('featureRestrictionMap');

		if (listAvailableFeatures.disk_folder_sharing === true)
		{
			this.#getDiskFolderList().showRightsOnStorage();
		}
		else
		{
			BX.UI.InfoHelper.show(featureRestrictionMap.disk_folder_sharing);
		}
	}

	showBizproc()
	{
		this.#getDiskFolderList().openSlider(this.#getParam('pathToFilesBizprocWorkflowAdmin'));
	}

	showBizprocSettings()
	{
		this.#getDiskFolderList().showSettingsOnBizproc();
	}

	showNetworkDriveConnect()
	{
		this.#getDiskFolderList().showNetworkDriveConnect({
			link: this.#getParam('networkDriveLink'),
		});
	}

	openWindowForSelectDocumentService()
	{
		this.#getDiskFolderList().openWindowForSelectDocumentService({});
	}

	cleanTrash()
	{
		this.#getDiskFolderList().openConfirmEmptyTrash();
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#getDiskFolderList(): BX.Disk.FolderListClass
	{
		return BX.Disk[`FolderListClass_${this.#getParam('diskComponentId')}`];
	}
}