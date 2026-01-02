import { Dom, Event, Tag, Type, Loc } from 'main.core';
import { PULL, PullClient } from 'pull.client';
import { PageObject } from 'landing.pageobject';
import { Skeleton } from 'landing.ui.copilot.skeleton';
import { Lottie } from 'ui.lottie';
import { EventEmitter } from 'main.core.events';
import loaderAnimation
	from '../../../../../components/bitrix/landing.site_copilot/templates/.default/lottie/landing-site-loader.json';
import { AiLoader } from './ai-loader/ai-loader';
import { load } from './change-background-with-animation';
import { Confetti } from 'ui.confetti';

import './css/style.css';

const ANIMATION_DURATION_DELAY = 300;
const ANIMATION_DURATION = 500;

type BlockImage = {
	defaultSrc?: string,
	src?: string,
	src2x?: string,
	id?: number,
	id2x?: number,
	type?: string,
	isSwitchedToDefault?: boolean,
	isSwitchedBackToOriginal?: boolean,
	nodeCode?: string,
	position?: number,
	node?: any,
};

type BlockData = {
	id: number,
	anchor: string,
	images?: BlockImage[],
	element?: HTMLElement,
};

export class Copilot extends EventEmitter
{
	generationId: ?number = null;
	blocksData: BlockData[];
	aiLoader: AiLoader;
	mainDocumentBody: HTMLElement;
	editorWindow: ?Window;
	completedBlockIds: ?Set;
	scrollTimeout: ?number;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.Animation.Copilot');

		this.editorWindow = PageObject.getEditorWindow();
		this.blocksData = [];
		this.aiLoader = new AiLoader({});
		this.mainDocumentBody = window.parent.document.body;
		this.subscribeToPullEvents();
	}

	setBlocksData(blocksData: BlockData[]): Copilot
	{
		this.blocksData = blocksData;

		return this;
	}

	setGenerationId(generationId: number): Copilot
	{
		this.generationId = generationId;

		return this;
	}

	getAiLoader(): AiLoader
	{
		return this.aiLoader;
	}

	init(): void
	{
		this.disableEditor();

		if (!this.editorWindow)
		{
			return;
		}

		this.completedBlockIds = new Set();

		const blocks = PageObject.getBlocks();
		this.blocksData = this.blocksData.map((blockData) => {
			const block = blocks.get(blockData.id);
			const blockDataNew = {
				...blockData,
				element: block.content,
				anchor: block.anchor,
			};

			Skeleton.initOnBlock(blockDataNew.anchor, blockDataNew.element);

			return blockDataNew;
		});

		this.replaceAllImagesToDefaultImage();

		this.emit('init', { blocksData: this.blocksData });

		this.getAiLoader().open();
	}

	subscribeToPullEvents()
	{
		PULL.subscribe({
			type: PullClient.SubscriptionType.Server,
			moduleId: 'landing',
			callback: (event) => {
				if (
					event.params.generationId !== undefined
					&& this.generationId !== null
					&& event.params.generationId !== this.generationId
				)
				{
					return;
				}

				const command = event.command;

				if (command === 'LandingCopilotGeneration:onCopilotImageCreate')
				{
					this.onImageCreate(event);
				}

				if (command === 'LandingCopilotGeneration:onChangeBlockFinish')
				{
					const blocksData = JSON.parse(event.params.params.blockData);
					this.animateBlock(blocksData);
				}
			},
		});
	}

	onImageCreate(event)
	{
		const blockId = parseInt(event.params.params.blockId, 10);
		const selector = event.params.params.selector;
		const position = event.params.params.position;
		const value = event.params.params.value;

		const neededBlockData = this.blocksData.find((blockData) => blockData.id === blockId);
		if (neededBlockData)
		{
			neededBlockData.images.forEach((image) => {
				if (
					image.nodeCode === selector
					&& image.position === position
				)
				{
					const { src = '', src2x = '', id = '', id2x = '' } = Type.isString(value)
						? { src: value, src2x: value, id: '', id2x: '' }
						: value;

					Object.assign(image, {
						src,
						src2x,
						id,
						id2x,
						isSwitchedToDefault: true,
					});
				}
			});
		}

		if (this.completedBlockIds.has(blockId))
		{
			const block = PageObject.getBlocks().get(blockId);
			if (block)
			{
				this.replaceAllDefaultImagesWithImageWithoutAnimation(block, neededBlockData);
			}
		}
	}

	async start(): Promise<void>
	{
		for (const blockData of this.blocksData)
		{
			// eslint-disable-next-line no-await-in-loop
			await this.scrollToBlock(blockData.element);

			// eslint-disable-next-line no-await-in-loop
			await this.replaceDefaultImagesOnce(blockData.id);

			// eslint-disable-next-line no-await-in-loop
			await Skeleton.removeSkeletonFromBlock(blockData.anchor);

			// eslint-disable-next-line no-await-in-loop
			await this.replaceDefaultImagesRecursive(blockData.id);
			this.completedBlockIds.add(blockData.id);

			// eslint-disable-next-line no-await-in-loop
			await this.delay(ANIMATION_DURATION_DELAY);
		}
	}

	async stop(): void
	{
		if (this.blocksData[0]?.element)
		{
			await this.scrollToBlock(this.blocksData[0].element);
		}
		this.getAiLoader().close();
	}

	scrollToBlock(block: HTMLElement): Promise<void>
	{
		return new Promise((resolve) => {
			const windowOfElement = block.ownerDocument.defaultView;
			if (!windowOfElement)
			{
				resolve();

				return;
			}

			let scrollTimeout = null;

			const handleScrollEnd = () => {
				if (scrollTimeout)
				{
					clearTimeout(scrollTimeout);
				}
				scrollTimeout = setTimeout(() => {
					Event.unbind(windowOfElement, 'scroll', handleScrollEnd);
					resolve();
				}, 300);
			};

			Event.bind(windowOfElement, 'scroll', handleScrollEnd);

			if (
				(block.getBoundingClientRect().top === 0 && windowOfElement.scrollY === 0)
				|| block.getBoundingClientRect().top < 5
			)
			{
				resolve();
			}

			windowOfElement.scroll({
				top: block.getBoundingClientRect().top + windowOfElement.scrollY,
				behavior: 'smooth',
			});
		});
	}

	replaceAllImagesToDefaultImage(): void
	{
		this.blocksData.forEach((blockData) => {
			blockData.images.forEach((image) => {
				if (image && image.src !== image.defaultSrc)
				{
					const block = PageObject.getBlocks().get(blockData.id);
					if (block)
					{
						this.setImageToNode(block, image, false);
					}

					Object.assign(image, {
						isSwitchedToDefault: true,
					});
				}
			});
		});
	}

	setImageToNode(block, image, useOriginalSrc = true)
	{
		const selector = `${image.nodeCode}@${image.position}`;
		block.forceInit();
		const node = block.nodes.getBySelector(selector);
		if (!node)
		{
			return;
		}

		if (!this.setImageToStyle(node, image, useOriginalSrc))
		{
			node.setValue(this.getImageValueObject(image, useOriginalSrc), true);
		}
	}

	setImageToStyle(node, image, useOriginalSrc = true): boolean
	{
		const styleObj = node.node.style;
		const src = useOriginalSrc ? image.src : image.defaultSrc;
		const src2x = useOriginalSrc ? image.src : image.defaultSrc;

		if (
			styleObj.getPropertyValue('--bg-url')
			&& src
		)
		{
			styleObj.setProperty('--bg-url', `url("${src}")`);
			if (
				styleObj.getPropertyValue('--bg-url-2x')
				&& src2x
			)
			{
				styleObj.setProperty('--bg-url-2x', `url("${src2x}")`);
			}

			return true;
		}

		return false;
	}

	getImageValueObject(imageNode, useOriginalSrc = true): {}
	{
		if (useOriginalSrc)
		{
			return {
				src: imageNode.src ?? '',
				src2x: imageNode.src2x ?? '',
			};
		}

		return {
			src: imageNode.defaultSrc ?? '',
			src2x: imageNode.defaultSrc ?? '',
		};
	}

	async processBlockImages(blockId: number): Promise<boolean>
	{
		if (this.completedBlockIds.has(blockId))
		{
			return true;
		}

		let allImagesReplaced = true;
		const neededBlockData = this.blocksData.find((blockData) => blockData.id === blockId);
		const block = PageObject.getBlocks().get(blockId);

		if (!block || !neededBlockData?.images)
		{
			return true;
		}

		block.forceInit();

		for (const image of neededBlockData.images)
		{
			if (!image || !image.src || !image.src2x)
			{
				continue;
			}

			const node = block.nodes.getBySelector(`${image.nodeCode}@${image.position}`);
			if (node && image.isSwitchedToDefault === true && !image.isSwitchedBackToOriginal)
			{
				// eslint-disable-next-line no-await-in-loop
				await this.replaceDefaultImageWithImage(image, node, block);
				image.isSwitchedBackToOriginal = true;
			}

			if (image.src === image.defaultSrc)
			{
				allImagesReplaced = false;
			}
		}

		return allImagesReplaced;
	}

	async replaceDefaultImagesOnce(blockId: number): Promise<void>
	{
		await this.processBlockImages(blockId);
	}

	// eslint-disable-next-line consistent-return
	async replaceDefaultImagesRecursive(
		blockId: number,
		currentAttempt = 0,
		maxAttempts = 5,
	): Promise<void>
	{
		const allReplaced = await this.processBlockImages(blockId);
		const canTryAgain = currentAttempt < maxAttempts;

		if (!allReplaced && canTryAgain)
		{
			await this.delay(ANIMATION_DURATION * 2);

			return this.replaceDefaultImagesRecursive(blockId, currentAttempt + 1, maxAttempts);
		}
	}

	replaceAllDefaultImagesWithImageWithoutAnimation(block, blockData: BlockData)
	{
		block.forceInit();
		if (blockData.images)
		{
			for (const image of blockData.images)
			{
				if (image)
				{
					const node = block.nodes.getBySelector(`${image.nodeCode}@${image.position}`);
					if (node && image.isSwitchedToDefault === true && !image.isSwitchedBackToOriginal)
					{
						this.replaceDefaultImageWithImageWithoutAnimation(image, node, block);
						image.isSwitchedBackToOriginal = true;
					}
				}
			}
		}
	}

	async replaceDefaultImageWithImage(image: BlockImage, node: Object, block): Promise<void>
	{
		await load(image.src);
		await load(image.src2x);

		Dom.addClass(node.node, 'landing-animation-copilot-default-image');
		Dom.addClass(node.node, 'fade-out');
		// eslint-disable-next-line no-await-in-loop
		await this.delay(ANIMATION_DURATION);

		this.setImageToNode(block, image);

		Dom.removeClass(node.node, 'fade-out');
		Dom.addClass(node.node, 'fade-in');
		// eslint-disable-next-line no-await-in-loop
		await this.delay(ANIMATION_DURATION);

		Dom.removeClass(node.node, 'fade-in');
		Dom.removeClass(node.node, 'landing-animation-copilot-default-image');
	}

	replaceDefaultImageWithImageWithoutAnimation(image: BlockImage, node: Object, block): void
	{
		this.setImageToNode(block, image);
	}

	delay(ms: number): Promise<void>
	{
		return new Promise((resolve) => {
			setTimeout(resolve, ms);
		});
	}

	async showConfetti(): Promise<void>
	{
		return new Promise((resolve) => {
			setTimeout(async () => {
				Confetti.fire({
					particleCount: 100,
					spread: 70,
					origin: {y: 0.7},
					zIndex: 99999,
				});
				await this.delay(ANIMATION_DURATION * 3);
				resolve();
			}, 1000);
		});
	}

	showLoader()
	{
		if (!this.editorWindow)
		{
			console.error('editorDocument is not defined');

			return;
		}

		const editorDocumentBody = this.editorWindow.document.querySelector('.landing-editor');
		if (!editorDocumentBody)
		{
			console.error('Element with class .landing-editor not found');

			return;
		}
		this.loader = this.getStatusCreate();
		Dom.append(this.loader, editorDocumentBody);
	}

	hideLoader()
	{
		if (this.loader)
		{
			this.loader.remove();
			this.loader = null;
		}
	}

	disableEditor(): Copilot
	{
		Dom.addClass(this.mainDocumentBody, 'landing-animating');
		const editorDocumentBody = this.editorWindow.document.querySelector('.landing-editor');
		if (editorDocumentBody)
		{
			Dom.addClass(editorDocumentBody, 'landing-editor-disable');
		}

		return this;
	}

	enableEditor(): Copilot
	{
		Dom.removeClass(this.mainDocumentBody, 'landing-animating');
		const editorDocumentBody = this.editorWindow.document.querySelector('.landing-editor');
		if (editorDocumentBody)
		{
			Dom.removeClass(editorDocumentBody, 'landing-editor-disable');
		}

		return this;
	}

	animateSite(): Promise<void>
	{
		this.start()
			.then(() => {
				this.stop();

				return this.showConfetti();
			})
			.then(() => {
				this.emit('onSiteFinish', { blocksData: this.blocksData });
				this.enableEditor();
			})
			.catch(() => {});
	}

	animateBlock(blocksData)
	{
		this.setBlocksData(blocksData);
		const reloadPromises = [];
		const pageBlocks = PageObject.getBlocks();
		for (const blockData of this.blocksData)
		{
			const block = pageBlocks.get(blockData.id);
			if (block)
			{
				reloadPromises.push(block.reload());
			}
		}

		Promise.all(reloadPromises)
			.then(() => {
				setTimeout(() => {
					this.init();
					this.hideLoader();
					this.start()
						.then(() => {
							this.stop();

							return this.showConfetti();
						})
						.then(() => {
							this.emit('onBlockFinish', { blocksData: this.blocksData });
							this.enableEditor();
						})
						.catch(() => {});
				}, ANIMATION_DURATION * 6);
			})
			.catch((error) => {
				console.error(error);
			});
	}

	getStatusCreate(): HTMLElement
	{
		const nodeAnimate = Tag.render`<div class="landing-site-copilot-status-image"></div>`;
		const nodeText = Tag.render`
			<div class="landing-site-copilot-content-text">
				${Loc.getMessage('LANDING_BLOCK_COPILOT_CREATED_1')}
			</div>
		`;

		Lottie.loadAnimation({
			container: nodeAnimate,
			renderer: 'svg',
			loop: true,
			autoplay: true,
			animationData: loaderAnimation,
		});

		let i = 2;
		const iMax = 6;
		this.interval = setInterval(() => {
			if (i > iMax)
			{
				i = 1;
			}

			let message = Loc.getMessage(`LANDING_BLOCK_COPILOT_CREATED_${i}`);

			if (!message)
			{
				i = 1;
				message = Loc.getMessage(`LANDING_BLOCK_COPILOT_CREATED_${i}`);
			}

			nodeText.innerText = message;
			i++;
		}, 3000);

		return Tag.render`
			<div class="landing-site-copilot-status-wrapper">
				${nodeAnimate}
				${nodeText}
			</div>
		`;
	}
}
