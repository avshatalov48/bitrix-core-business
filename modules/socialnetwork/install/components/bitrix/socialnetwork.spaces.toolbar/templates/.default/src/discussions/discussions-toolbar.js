import { Dom, Event, Tag, Type, Loc, Cache, Reflection } from 'main.core';
import { Filter } from '../filter';
import { DiscussionsAddButtonMenu } from './discussions-add-button-menu';
import { DiscussionsSettings } from './discussions-settings';
import { Calendar } from '../calendar/calendar';
import { PostForm } from 'socialnetwork.post-form';
import { DiscussionsComposition } from './discussions-composition';
import { BaseEvent } from 'main.core.events';

const NotificationCenter = Reflection.namespace('BX.UI.Notification.Center');

import '../css/discussions.css';

type Params = {
	type: 'user' | 'group',
	locationAccess: ?boolean,
	userId: number,
	ownerId: number,
	spaceId: number,
	spaceName: string,
	compositionFilters: Array<string>,
	hrefChangeSmartTrackingMode: string,
	isSmartTrackingMode: string,
	mainFilterId: string,
	pathToUserPage: string,
	pathToGroupPage: string,
	pathToFilesPage: string,
	filterContainer: HTMLElement,
	isDiskStorageWasObtained: 'Y' | 'N',
}

export class DiscussionsToolbar
{
	#cache = new Cache.MemoryCache();

	#postForm: PostForm;
	#addButtonMenu: DiscussionsAddButtonMenu;
	#composition: DiscussionsComposition;
	#settings: DiscussionsSettings;
	#filter: Filter;

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#filter = new Filter({
			filterId: this.#getParam('filterId'),
			filterContainer: this.#getParam('filterContainer'),
		});
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	renderAddBtnTo(container: HTMLElement): void
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderAddBtn(), container);
	}

	renderCompositionBtnTo(container: HTMLElement): void
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for add composition btn not found');
		}

		Dom.append(this.#renderCompositionBtn(), container);
	}

	renderSettingsBtnTo(container: HTMLElement): void
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for settings btn not found');
		}

		Dom.append(this.#renderSettingsBtn(), container);
	}

	#renderAddBtn(): HTMLElement
	{
		const { node, mainBtn, menuBtn } = Tag.render`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps" ref="node">
				<button class="ui-btn-main" data-id="spaces-discussions-add-main-btn" ref="mainBtn">
					${Loc.getMessage('SN_SPACES_DISCUSSIONS_START_DISCUSSIONS')}
				</button>
				<button 
					class="ui-btn-menu"  
					id="spaces-discussions-toolbar-menu" 
					data-id="spaces-discussions-add-menu-btn"
					ref="menuBtn"
				>	
				</button>
			</div>
		`;

		Event.bind(mainBtn, 'click', this.#addMainClick.bind(this));
		Event.bind(menuBtn, 'click', this.#addMenuClick.bind(this));

		return node;
	}

	#renderCompositionBtn(): HTMLElement
	{
		const node = Tag.render`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes sn-spaces__toolbar-space_btn-options"
			>
				<div class="ui-icon-set --customer-cards" style="--ui-icon-set__icon-size: 25px;"></div>
				<div class="sn-spaces__toolbar-space_btn-text">
					${Loc.getMessage('SN_SPACES_DISCUSSIONS_COMPOSITION')}
				</div>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 19px;"></div>
			</button>
		`;

		this.#composition = new DiscussionsComposition({
			userId: this.#getParam('userId'),
			spaceId: this.#getParam('spaceId'),
			bindElement: node,
			spaceName: this.#getParam('spaceName'),
			compositionFilters: this.#getParam('compositionFilters'),
			mainFilterId: this.#getParam('mainFilterId'),
			appliedFields: this.#getParam('appliedFields'),
		});

		Event.bind(node, 'click', () => this.#composition.show());

		return node;
	}

	#renderSettingsBtn(): HTMLElement
	{
		const node = Tag.render`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes sn-spaces__toolbar-space_btn-more" 
				data-id="spaces-discussions-settings-btn"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`;

		Event.bind(node, 'click', this.#settingsClick.bind(this));

		return node;
	}

	#addMainClick(): void
	{
		if (!this.#postForm)
		{
			this.#postForm = new PostForm({
				groupId: this.#getParam('type') === 'user' ? 0 : this.#getParam('ownerId'),
				pathToDefaultRedirect: this.#getParam('pathToUserPage'),
				pathToGroupRedirect: this.#getParam('pathToGroupPage'),
			});
		}

		this.#postForm.show();
	}

	#createAddButtonMenu(): void
	{
		const calendar = new Calendar({
			type: this.#getParam('type'),
			// eslint-disable-next-line no-constant-binary-expression
			locationAccess: this.#getParam('locationAccess') === '1' ?? false,
			userId: this.#getParam('userId'),
			ownerId: this.#getParam('ownerId'),
		});

		this.#addButtonMenu = new DiscussionsAddButtonMenu({
			bindElement: document.getElementById('spaces-discussions-toolbar-menu'),
			calendar,
			isDiskStorageWasObtained: this.#getParam('isDiskStorageWasObtained') === 'Y',
		});

		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
		BX.addCustomEvent(window, 'onPopupFileUploadClose', () => {
			this.#showSuccessUploadNotify();
		});

		this.#addButtonMenu.subscribe('showMenu', (baseEvent: BaseEvent) => {
			const { fileUploadContainer } = baseEvent.getData();
			this.#appendUploadInput(fileUploadContainer);
		});
		this.#addButtonMenu.subscribe('closeMenu', (baseEvent: BaseEvent) => {
			const { fileUploadContainer } = baseEvent.getData();
			this.#hideUploadInput(fileUploadContainer);
		});
	}

	#appendUploadInput(container: HTMLElement)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
		BX.onCustomEvent(window, 'onDiskUploadPopupShow', [container]);
	}

	#hideUploadInput(container: HTMLElement)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
		BX.onCustomEvent(window, 'onDiskUploadPopupClose', [container]);
	}

	#showSuccessUploadNotify()
	{
		const content = Loc.getMessage('SN_SPACES_LINE_UPLOAD_FILE_NOTIFY_MESSAGE')
			.replace(
				'#handler#',
				`top.BX.Socialnetwork.Spaces.space.reloadPageContent('${this.#getParam('pathToFilesPage')}');`,
			)
		;

		NotificationCenter.notify({ content });
	}

	#addMenuClick(): void
	{
		if (!this.#addButtonMenu)
		{
			this.#createAddButtonMenu();
		}

		this.#addButtonMenu.show();
	}

	#settingsClick(event): void
	{
		if (!this.#settings)
		{
			this.#settings = new DiscussionsSettings({
				bindElement: event.target,
				isSmartTrackingMode: this.#getParam('isSmartTrackingMode'),
				mainFilterId: this.#getParam('mainFilterId'),
			});
		}

		this.#settings.show();
	}
}
