import 'ui.vue3';
import 'ui.buttons';
import 'ui.fonts.opensans';

import {BaseEvent} from 'main.core.events';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import {Utils} from 'im.v2.lib.utils';
import {Logger} from 'im.v2.lib.logger';

import {BackgroundComponent} from './components/background';
import {ActionComponent} from './components/action';
import {MaskComponent} from './components/mask';
import {Loader} from './components/loader';
import {TabPanel} from './components/tab-panel';
import {VideoPreview} from './components/video-preview';

import {Action} from './classes/items/action';
import {Background} from './classes/items/background';
import {Mask} from './classes/items/mask';
import {BackgroundService} from './classes/background-service';
import {UploadManager} from './classes/upload-manager';
import {LimitManager} from './classes/limit-manager';
import {TabId, MASK_HELP_ARTICLE_CODE} from './const';

import './css/call-background.css';
import './css/call-background-dark.css';

import type {ElementListRestResult, BackgroundListRestResult, BackgroundRestResult, MaskListRestResult, MaskRestResult} from './types/rest';

// @vue/component
export const CallBackground = {
	name: 'CallBackground',
	components: {BackgroundComponent, ActionComponent, MaskComponent, Loader, TabPanel, VideoPreview},
	props: {
		tab: {
			type: String,
			default: TabId.background,
		}
	},
	data()
	{
		return {
			selectedTab: '',
			selectedBackgroundId: '',
			selectedMaskId: '',
			loadingItems: true,
			actions: [],
			defaultBackgrounds: [],
			customBackgrounds: [],
			masks: [],
			listIsScrolled: false
		};
	},
	computed:
	{
		TabId: () => TabId,
		backgrounds(): Background[]
		{
			return [...this.customBackgrounds, ...this.defaultBackgrounds];
		},
		containerClasses(): string[]
		{
			const classes = [];

			if (this.isDesktop)
			{
				classes.push('--desktop');
			}

			return classes;
		},
		uploadTypes(): string
		{
			return UploadManager.allowedFileTypes.join(', ');
		},
		descriptionText(): string
		{
			const replaces = {
				'#HIGHLIGHT_START#': '<span class="bx-im-call-background__description_highlight">',
				'#HIGHLIGHT_END#': '</span>',
				'#BR#': '</br></br>'
			};
			if (this.selectedTab === TabId.mask)
			{
				return this.loc('BX_IM_CALL_BG_DESCRIPTION_MASK_2', replaces);
			}

			return this.loc('BX_IM_CALL_BG_DESCRIPTION_BG', replaces);
		},
		isDesktop()
		{
			return Utils.platform.isBitrixDesktop();
		}
	},
	created()
	{
		this.initSelectedTab();

		this.getBackgroundService().getElementsList().then((result: ElementListRestResult) => {
			const {backgroundResult, maskResult} = result;

			this.initLimitManager(backgroundResult);
			this.initBackgroundList(backgroundResult);

			this.uploadManager.setDiskFolderId(backgroundResult.upload.folderId);
			const uploadActionIsAvailable = !!backgroundResult.upload.folderId;
			this.initActions(uploadActionIsAvailable);

			this.initMasks(maskResult);
			this.initMaskLoadEventHandler();

			this.initPreviouslySelectedItem();
			this.loadingItems = false;

			this.hideLoader();
		}).catch(() => {
			this.loadingItems = false;
		});
	},
	mounted()
	{
		this.initUploader();
	},
	methods:
	{
		// region init
		initSelectedTab()
		{
			if (this.tab === TabId.mask && !LimitManager.isMaskFeatureAvailable())
			{
				this.selectedTab = TabId.background;
				return;
			}

			if (this.tab === TabId.mask && !LimitManager.isMaskFeatureSupportedByDesktopVersion())
			{
				this.selectedTab = TabId.background;
				LimitManager.showHelpArticle(MASK_HELP_ARTICLE_CODE);
				return;
			}

			this.selectedTab = this.tab;
		},
		initPreviouslySelectedItem()
		{
			this.initPreviouslySelectedMask();
			this.initPreviouslySelectedBackground();
		},
		initPreviouslySelectedMask()
		{
			if (this.isDesktop)
			{
				const {id: maskId} = DesktopApi.getCallMask();
				let foundMask = this.masks.find(mask => mask.id === maskId);
				if (!foundMask)
				{
					foundMask = Mask.createEmpty();
				}
				this.previouslySelectedMask = foundMask;
				Logger.warn('CallBackground: previously selected mask', this.previouslySelectedMask);
			}
			else
			{
				this.previouslySelectedMask = Mask.createEmpty();
			}

			this.selectedMaskId = this.previouslySelectedMask.id;
		},
		initPreviouslySelectedBackground()
		{
			if (this.isDesktop)
			{
				const {id: backgroundId} = DesktopApi.getBackgroundImage();
				const itemsToSearch = [...this.actions, ...this.backgrounds];
				let foundBackground = itemsToSearch.find(item => item.id === backgroundId);
				if (!foundBackground)
				{
					foundBackground = new Action(Action.type.none);
				}
				this.previouslySelectedBackground = foundBackground;
				Logger.warn('CallBackground: previously selected background', this.previouslySelectedBackground);
			}
			else
			{
				this.previouslySelectedBackground = new Action(Action.type.none);
			}

			this.selectedBackgroundId = this.previouslySelectedBackground.id;
		},
		initActions(uploadActionIsAvailable: boolean)
		{
			this.actions = [
				new Action(Action.type.none),
				...uploadActionIsAvailable ? [new Action(Action.type.upload)]: [],
				new Action(Action.type.gaussianBlur),
				new Action(Action.type.blur),
			];
		},
		initBackgroundList(restResult: BackgroundListRestResult)
		{
			this.defaultBackgrounds = [];
			restResult.backgrounds.default.forEach((background: BackgroundRestResult) => {
				this.defaultBackgrounds.push(Background.createDefaultFromRest(background));
			});

			this.customBackgrounds = [];
			restResult.backgrounds.custom.forEach((background: BackgroundRestResult) => {
				this.customBackgrounds.push(Background.createCustomFromRest(background));
			});
		},
		initLimitManager(result: BackgroundListRestResult)
		{
			const {limits, infoHelperParams} = result;
			this.limitManager = new LimitManager({
				limits,
				infoHelperUrlTemplate: infoHelperParams.frameUrlTemplate
			});
		},
		initUploader()
		{
			this.uploadManager = new UploadManager({
				inputNode: this.$refs['uploadInput']
			});

			this.uploadManager.subscribe(UploadManager.event.uploadStart, (event: BaseEvent) => {
				const backgroundsInstance = Background.createCustomFromUploaderEvent(event.getData());
				this.customBackgrounds.unshift(backgroundsInstance);
			});
			this.uploadManager.subscribe(UploadManager.event.uploadProgress, (event: BaseEvent) => {
				const {id, progress} = event.getData();
				const background = this.findCustomBackgroundById(id);
				if (!background)
				{
					return;
				}
				background.setUploadProgress(progress);
			});
			this.uploadManager.subscribe(UploadManager.event.uploadComplete, (event: BaseEvent) => {
				const {id, fileResult} = event.getData();
				const background = this.findCustomBackgroundById(id);
				if (!background)
				{
					return;
				}
				background.onUploadComplete(fileResult);

				this.onBackgroundClick(background);

				this.getBackgroundService().commitBackground(background.id);
			});
			this.uploadManager.subscribe(UploadManager.event.uploadError, (event: BaseEvent) => {
				const {id} = event.getData();
				const background = this.findCustomBackgroundById(id);
				if (!background)
				{
					return;
				}
				background.setUploadError();
			});
		},
		initMasks(result: MaskListRestResult)
		{
			const {masks} = result;
			this.masks.push(Mask.createEmpty());
			masks.forEach((mask: MaskRestResult) => {
				this.masks.push(Mask.createFromRest(mask));
			});
		},
		initMaskLoadEventHandler()
		{
			if (!this.isDesktop)
			{
				return;
			}
			this.maskLoadTimeouts = {};
			DesktopApi.setCallMaskLoadHandlers(this.onMaskLoad.bind(this));
		},
		// endregion init
		// region component events
		onActionClick(action: Action)
		{
			if (this.getLimitManager().isLimitedAction(action))
			{
				this.getLimitManager().showLimitSlider(LimitManager.limitCode.blur);
				return;
			}

			if (action.isUpload())
			{
				this.$refs['uploadInput'].click();
				return;
			}

			this.selectedBackgroundId = action.id;

			if (action.isEmpty())
			{
				this.removeCallBackground();
				return;
			}

			this.selectedMaskId = '';
			this.setCallBlur(action);
		},
		onBackgroundClick(background: Background)
		{
			if (this.getLimitManager().isLimitedBackground())
			{
				this.getLimitManager().showLimitSlider(LimitManager.limitCode.image);
				return;
			}

			if (!background.isSupported || background.isLoading)
			{
				return;
			}

			this.selectedBackgroundId = background.id;
			this.selectedMaskId = '';
			this.setCallBackground(background);
		},
		onBackgroundRemove(background: Background)
		{
			if (background.id === this.selectedBackgroundId)
			{
				this.selectedBackgroundId = Action.type.none;
				this.removeCallBackground();
			}

			if (background.isLoading)
			{
				this.uploadManager.cancelUpload(background.id);
			}
			else
			{
				this.getBackgroundService().deleteFile(background.id);
			}

			this.customBackgrounds = this.customBackgrounds.filter(element => element.id !== background.id);
		},
		onMaskClick(mask: Mask)
		{
			if (!mask.active)
			{
				return;
			}

			if (mask.isEmpty())
			{
				this.selectedMaskId = mask.id;
				this.removeCallMask();
			}

			this.setCallMask(mask);
		},
		onSaveButtonClick()
		{
			window.close();
		},
		onCancelButtonClick()
		{
			const backgroundWasChanged = this.previouslySelectedBackground.id !== this.selectedBackgroundId;
			const maskWasChanged = this.previouslySelectedMask.id !== this.selectedMaskId;
			if (!backgroundWasChanged && !maskWasChanged)
			{
				window.close();
				return;
			}

			let backgroundPromise = Promise.resolve();
			if (backgroundWasChanged)
			{
				backgroundPromise = this.setCallBackground(this.previouslySelectedBackground);
			}

			backgroundPromise.then(() => {
				if (maskWasChanged && !this.previouslySelectedMask.isEmpty())
				{
					this.setCallMask(this.previouslySelectedMask);
					this.isWaitingForMaskToCancel = true;
				}
				else if (this.previouslySelectedMask.isEmpty())
				{
					this.removeCallMask();
					window.close();
				}
				else
				{
					window.close();
				}
			});
		},
		onListScroll(event: Event)
		{
			if (event.target.scrollTop === 0)
			{
				this.listIsScrolled = false;
				return;
			}

			this.listIsScrolled = true;
		},
		onTabChange(newTabId: string)
		{
			if (newTabId === TabId.mask && !LimitManager.isMaskFeatureSupportedByDesktopVersion())
			{
				LimitManager.showHelpArticle(MASK_HELP_ARTICLE_CODE);
				return;
			}

			this.selectedTab = newTabId;
		},
		onMaskLoad(url: string)
		{
			Logger.warn('CallBackground: onMaskLoad', url);
			if (this.isWaitingForMaskToCancel)
			{
				window.close();
				return;
			}

			const masksWithoutEmpty = this.masks.filter((mask: Mask) => !mask.isEmpty());
			const loadedMask = masksWithoutEmpty.find((mask: Mask) => url.includes(mask.mask));
			Logger.warn('CallBackground: loaded mask', loadedMask);
			if (!loadedMask)
			{
				return;
			}

			clearTimeout(this.maskLoadTimeouts[loadedMask.id]);
			loadedMask.isLoading = false;
			if (this.lastRequestedMaskId === loadedMask.id)
			{
				this.selectedMaskId = loadedMask.id;
			}
		},
		// endregion component events
		// region desktop interactions
		setCallBackground(backgroundInstance: Background): Promise
		{
			Logger.warn('CallBackground: set background', backgroundInstance);
			if (!this.isDesktop)
			{
				return;
			}

			return DesktopApi.setCallBackground(backgroundInstance.id, backgroundInstance.background);
		},
		setCallBlur(action: Action): Promise
		{
			Logger.warn('CallBackground: set blur', action);
			if (!this.isDesktop)
			{
				return;
			}

			return DesktopApi.setCallBackground(action.id, action.background);
		},
		removeCallBackground(): Promise
		{
			if (!this.isDesktop)
			{
				return;
			}

			return DesktopApi.setCallBackground(Action.type.none, Action.type.none);
		},
		setCallMask(mask: Mask)
		{
			Logger.warn('CallBackground: set mask', mask);
			if (!this.isDesktop)
			{
				return;
			}

			if (mask.isEmpty())
			{
				Logger.warn('CallBackground: empty mask - removing it');
				DesktopApi.setCallMask();
				return;
			}

			this.lastRequestedMaskId = mask.id;

			const MASK_LOAD_STATUS_DELAY = 500;
			this.maskLoadTimeouts[mask.id] = setTimeout(() => {
				mask.isLoading = true;
			}, MASK_LOAD_STATUS_DELAY);
			DesktopApi.setCallMask(mask.id, mask.mask, mask.background);
		},
		removeCallMask()
		{
			if (!this.isDesktop)
			{
				return;
			}

			DesktopApi.setCallMask();
		},
		hideLoader()
		{
			if (!this.isDesktop)
			{
				return;
			}

			DesktopApi.hideLoader();
		},
		// endregion desktop interactions
		findCustomBackgroundById(id: string): ?Background
		{
			return this.customBackgrounds.find(element => element.id === id);
		},
		getBackgroundService(): BackgroundService
		{
			if (!this.backgroundService)
			{
				this.backgroundService = new BackgroundService();
			}

			return this.backgroundService;
		},
		getLimitManager(): LimitManager
		{
			return this.limitManager;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template:
	`
		<div :class="{'--desktop': isDesktop}" class="bx-im-call-background__scope bx-im-call-background__container">
			<div v-if="loadingItems" class="bx-im-call-background__loader_container">
				<Loader />
			</div>
			<div v-else class="bx-im-call-background__content">
				<div class="bx-im-call-background__left">
					<VideoPreview />
					<div v-html="descriptionText" class="bx-im-call-background__description"></div>
				</div>
				<div :class="{'--scrolled': listIsScrolled}" class="bx-im-call-background__right">
					<TabPanel :selectedTab="selectedTab" @tabChange="onTabChange" />
					<div v-if="selectedTab === TabId.background" @scroll="onListScroll" class="bx-im-call-background__list">
						<ActionComponent
							v-for="action in actions"
							:element="action"
							:key="action.id"
							:isSelected="selectedBackgroundId === action.id"
							@click="onActionClick(action)"
						/>
						<BackgroundComponent
							v-for="background in backgrounds"
							:element="background"
							:key="background.id"
							:isSelected="selectedBackgroundId === background.id"
							@click="onBackgroundClick(background)"
							@cancel="onBackgroundRemove(background)"
							@remove="onBackgroundRemove(background)"
						/>
					</div>
					<div v-else-if="selectedTab === TabId.mask" @scroll="onListScroll" class="bx-im-call-background__list">
						<MaskComponent
							v-for="mask in masks"
							:element="mask"
							:key="mask.id"
							:isSelected="selectedMaskId === mask.id"
							@click="onMaskClick(mask)"
						/>
					</div>
				</div>	
			</div>
			<div class="bx-im-call-background__button-panel">
				<button @click="onSaveButtonClick" :class="{'ui-btn-wait ui-btn-disabled': loadingItems}" class="ui-btn ui-btn-success">
					{{ loc('BX_IM_CALL_BG_SAVE') }}
				</button>
				<button @click="onCancelButtonClick" class="ui-btn ui-btn-link">
					{{ loc('BX_IM_CALL_BG_CANCEL') }}
				</button>
			</div>
		</div>
		<div class="bx-im-call-background__upload-input">
			<input type="file" :accept="uploadTypes" ref="uploadInput"/>
		</div>
	`
};