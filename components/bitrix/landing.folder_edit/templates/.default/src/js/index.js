import { Event, Dom } from 'main.core';

type FolderEditOptions = {
	siteId: number,
	siteType: string,
	folderId: number,
	selectorCreateIndex: ?HTMLElement,
	selectorIndexMetaBlock: HTMLElement,
	selectorSelect: HTMLElement,
	selectorPageLink: HTMLElement,
	selectorFieldId: HTMLElement,
	selectorPreviewBlock: HTMLElement,
	selectorPreviewTitle: HTMLElement,
	selectorPreviewDescription: HTMLElement,
	selectorPreviewPicture: HTMLElement,
	selectorPreviewSrcPicture: HTMLElement,
	selectorPreviewPictureWrapper: HTMLElement,
	pathToLandingEdit: string,
	pathToLandingCreate: string
};

export class FolderEdit
{
	#siteId: number;
	#siteType: string;
	#folderId: number;
	#selectorCreateIndex: ?HTMLElement;
	#selectorIndexMetaBlock: HTMLElement;
	#selectorSelect: HTMLElement;
	#selectorPageLink: HTMLElement;
	#selectorFieldId: HTMLElement;
	#selectorPreviewBlock: HTMLElement;
	#selectorPreviewTitle: HTMLElement;
	#selectorPreviewDescription: HTMLElement;
	#selectorPreviewPicture: HTMLElement;
	#selectorPreviewSrcPicture: HTMLElement;
	#selectorPreviewPictureWrapper: HTMLElement;
	#pathToLandingEdit: string;
	#pathToLandingCreate: string;
	#linkUrlSelector: BX.Landing.UI.Field.LinkURL;
	#linkPictureSelector: BX.Landing.UI.Field.Image;
	#ajaxPathLoadPreview: string = '/bitrix/services/main/ajax.php?action=landing.api.landing.getById&landingId=#id#';

	constructor(options: FolderEditOptions)
	{
		this.#siteId = options.siteId;
		this.#siteType = options.siteType;
		this.#folderId = options.folderId;
		this.#selectorCreateIndex = options.selectorCreateIndex;
		this.#selectorIndexMetaBlock = options.selectorIndexMetaBlock;
		this.#selectorSelect = options.selectorSelect;
		this.#selectorPageLink = options.selectorPageLink;
		this.#selectorFieldId = options.selectorFieldId;
		this.#selectorPreviewBlock = options.selectorPreviewBlock;
		this.#selectorPreviewTitle = options.selectorPreviewTitle;
		this.#selectorPreviewDescription = options.selectorPreviewDescription;
		this.#selectorPreviewPicture = options.selectorPreviewPicture;
		this.#selectorPreviewSrcPicture = options.selectorPreviewSrcPicture;
		this.#selectorPreviewPictureWrapper = options.selectorPreviewPictureWrapper;
		this.#pathToLandingEdit = options.pathToLandingEdit;
		this.#pathToLandingCreate = options.pathToLandingCreate;

		this.#initSelector();
		this.#initPicture();

		Event.bind(this.#selectorSelect, 'click', this.#onClickSelect.bind(this));

		if (this.#selectorCreateIndex)
		{
			Event.bind(this.#selectorCreateIndex, 'click', this.#onClickIndexCreate.bind(this));
		}
	}

	#initSelector()
	{
		this.#linkUrlSelector = new BX.Landing.UI.Field.LinkURL({
			title: null,
			content: null,
			allowedTypes: [
				BX.Landing.UI.Field.LinkURL.TYPE_PAGE
			],
			options: {
				siteId: this.#siteId,
				currentSiteOnly: true,
				disableAddPage: true,
				landingId: -1,
				filter: {
					'ID': this.#siteId,
					'=TYPE': this.#siteType
				},
				filterLanding: {
					'FOLDER_ID': this.#folderId
				}
			},
			onInput: this.#onSelect.bind(this)
		});
	}

	#initPicture()
	{
		if (!this.#selectorPreviewSrcPicture)
		{
			return;
		}

		this.#linkPictureSelector = new BX.Landing.UI.Field.Image({
			id: 'folderPicture',
			disableLink: true,
			disableAltField: true,
			allowClear: true,
			content: {
				src: this.#selectorPreviewSrcPicture.getAttribute('value'),
				id: this.#selectorPreviewPicture.getAttribute('value')
			},
			uploadParams: {
				action: 'Site::uploadFile',
				id: this.#siteId
			},
			dimensions: {
				width: 1200,
				height: 1200
			}
		});

		Dom.clean(this.#selectorPreviewPictureWrapper);
		Dom.append(this.#linkPictureSelector['layout'], this.#selectorPreviewPictureWrapper);

		this.#linkPictureSelector['layout'].addEventListener('input', () => {
			const file = this.#linkPictureSelector.getValue();
			this.#selectorPreviewPicture.setAttribute(
				'value',
				file['id2x']
			);
		});
	}

	#onSelect(title)
	{
		const id = this.#linkUrlSelector.getValue().substr(8);
		const path = this.#pathToLandingEdit.replace('#landing_edit#', id);

		this.#selectorPageLink.text = title;
		this.#selectorPageLink.setAttribute('href', path);
		this.#selectorFieldId.setAttribute('value', id);

		this.#loadPreview(id);
	}

	#onClickSelect()
	{
		this.#linkUrlSelector.onSelectButtonClick();
	}

	#onClickIndexCreate(e)
	{
		BX.SidePanel.Instance.open(this.#pathToLandingCreate, {
			allowChangeHistory: false,
			events: {
				onClose: function()
				{
					window.location.reload();
				}
			}
		});
		BX.PreventDefault(e);
	}

	#loadPreview(landingId)
	{
		this.#selectorPreviewBlock.style.display = 'block';
		this.#selectorIndexMetaBlock.style.display = 'flex';

		BX.ajax({
			url: this.#ajaxPathLoadPreview.replace('#id#', landingId),
			method: 'GET',
			dataType: 'json',
			onsuccess: result => {

				const data = result.data;

				if (!data['ADDITIONAL_FIELDS'])
				{
					return;
				}

				const title = data['ADDITIONAL_FIELDS']['METAOG_TITLE'] || data['TITLE'];
				const description = data['ADDITIONAL_FIELDS']['METAOG_DESCRIPTION'] || data['DESCRIPTION'] || '';

				this.#selectorPreviewTitle.setAttribute('value', title);
				this.#selectorPreviewDescription.setAttribute('value', description);
				this.#selectorPreviewPicture.setAttribute('value', '');

				this.#selectorPreviewPicture.setAttribute(
					'value',
					data['ADDITIONAL_FIELDS']['~METAOG_IMAGE'] || ''
				);
				this.#selectorPreviewSrcPicture.setAttribute(
					'value',
					data['ADDITIONAL_FIELDS']['METAOG_IMAGE'] || ''
				);

				this.#linkPictureSelector.setValue({
					src: data['ADDITIONAL_FIELDS']['METAOG_IMAGE'] || '',
					id: data['ADDITIONAL_FIELDS']['~METAOG_IMAGE'] || -1
				});
			}
		});
	}
}
