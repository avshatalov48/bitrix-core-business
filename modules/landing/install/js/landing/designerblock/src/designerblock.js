import {Dom, Event, Tag, Text} from 'main.core';
import {Backend} from 'landing.backend';
import {Env} from 'landing.env';
import {Metrika} from 'landing.metrika';
import {Highlight} from 'landing.ui.highlight';

import {Node, NodeType} from './node';
import {DesignerBlockUI} from './ui/designerblock';
import {RepoElementType} from './ui/panels/repo';
import {RepoManager} from './panels/repo';

import 'ui.fonts.opensans';
import './designerblock.css';

type ManifestNodesItem = {
	[selector: string]: {
		code: ?string,
		name: ?string,
		type: string,
		useInDesigner: ?boolean
	}
};

export type DesignerBlockOptions = {
	id: number,
	lid: number,
	code: string,
	designed: boolean,
	autoPublicationEnabled: boolean,
	access: string,
	active: boolean,
	anchor: string,
	manifest: {
		cards: {
			[selector: string]: {
				name: string
			}
		},
		nodes: {
			[selector: string]: {
				code: ?string,
				name: ?string,
				type: string
			}
		}
	},
	repository: Array<RepoElementType>
};

export class DesignerBlock
{
	blockNode: HTMLElement;
	originalNode: HTMLElement;
	hoverArea: HTMLElement = null;
	activeNode: Node = null;
	changed: boolean = false;
	saving: boolean = false;
	designed: boolean;
	autoPublicationEnabled: boolean;
	blockCode: string;
	blockId: number;
	landingId: number;
	nodes: ManifestNodesItem;
	highlight: Highlight;
	nodeMap: WeakMap;
	metrika: Metrika;
	repoManager: RepoManager;
	cardSelectors: Array<string>;

	constructor(blockNode: HTMLElement, options: DesignerBlockOptions)
	{
		if (!blockNode)
		{
			return;
		}

		this.originalNode = blockNode;
		this.blockNode = blockNode.children[0];
		this.blockCode = options.code;
		this.blockId = options.id;
		this.designed = options.designed;
		this.autoPublicationEnabled = options.autoPublicationEnabled;
		this.landingId = options.lid;
		this.nodes = options.manifest.nodes;
		this.highlight = new Highlight();
		this.cardSelectors = options.manifest.cards ? Object.keys(options.manifest.cards) : [];
		this.designAllowed = !!Env.getInstance().getOptions().design_block_allowed;
		this.cardSelectors.push('');// for without cards elements
		this.nodeMap = new WeakMap();
		this.metrika = new Metrika(true);
		this.repoManager = new RepoManager({
			repository: options.repository,
			onElementSelect: this.addElement.bind(this)
		});

		this.saveButton = parent.document.getElementById('landing-design-block-save')
			|| top.document.getElementById('landing-design-block-save')
			|| document.getElementById('landing-design-block-save');

		BX.addCustomEvent('Landing.Editor:load', () => {
			this.preventEvents();
			this.initHistoryEvents();
			this.initTopPanel();
			this.initNodes();
			this.initGrid();
			this.initSliders();
			this.initHoverArea();
		});
	}

	clearHtml(content: string): string
	{
		return content
			.replace(/<div class="[^"]*landing-designer-block-pseudo-last[^"]*"[^>]*>[\s]*<\/div>/g, '')
			.replace(/<div class="[^"]*landing-highlight-border[^"]*"[^>]*>[\s]*<\/div>/g, '')
			.replace(/url\(&quot;(.*?)&quot;\)/g, 'url($1)')
			.replace(/\s*data-(landingwrapper)="[^"]+"\s*/g, ' ')
			.replace(/\s*[\w-_]+--type-wrapper\s*/g, ' ')
			.replace(/<div[\s]*>[\s]*<\/div>/g, '')
			.replace(/\s*style=""/g, '')
			.replace(/cursor: pointer;/g, '')
			.replace(/user-select: none;/g, '');
	}

	preventEvents()
	{
		const preventMap = {
			a: 'click',
			form: 'submit',
			input: 'keydown'
		};
		Object.keys(preventMap).map(tag => {
			[...this.blockNode.querySelectorAll(tag)].map(node => {
				Event.bind(node, preventMap[tag], (e) => {
					e.preventDefault();
				});
			});
		});
	}

	initHistoryEvents()
	{
		BX.Landing.History.getInstance()
			.setTypeDesignerBlock(this.blockId)
			.then(() => {
				return Backend.getInstance()
					.action("History::clearDesignerBlock", {
						blockId: this.blockId,
					});
			});

		const body = this.getDocumentBody();

		top.BX.addCustomEvent('Landing:onHistoryAddNode',
			tags => {
				let elementAdded = false;
				tags.map(tag => {
					const insertAfterSelector = tag.insertAfterSelector || null;
					const parentNodeSelector = tag.parentNodeSelector || null;
					const element = Tag.render`${tag.elementHtml}`;

					if (insertAfterSelector)
					{
						elementAdded = true;
						Dom.insertAfter(element, body.querySelector(insertAfterSelector));
					}
					else if (parentNodeSelector)
					{
						elementAdded = true;
						Dom.prepend(element, body.querySelector(parentNodeSelector));
					}
				});
				if (elementAdded)
				{
					this.refreshManifest();
					setTimeout(() => {
						this.sendLabel(
							'designerBlock',
							'onHistoryAddNode'
						);
					}, 0);
				}
			}
		);

		top.BX.addCustomEvent('Landing:onHistoryRemoveNode',
			tags => {
				tags.map(tag => {
					this.removeNode(
						body.querySelector(tag.elementSelector)
					);
				});
				this.refreshManifest();
				setTimeout(() => {
					this.sendLabel(
						'designerBlock',
						'onHistoryRemoveNode'
					);
				}, 0);
			}
		);
	}

	initTopPanel()
	{
		Event.bind(this.saveButton, 'click', () => {
			this.highlight.hide(true);

			const finishCallback = () => {
				if (BX.SidePanel && BX.SidePanel.Instance)
				{
					BX.SidePanel.Instance.close();
				}
			};
			if (!this.changed)
			{
				finishCallback();
				return;
			}
			if (!this.designAllowed)
			{
				top.BX.UI.InfoHelper.show('limit_crm_superblock');
				return;
			}

			this.saving = true;

			const batch = {};
			batch['Block::updateContent'] = {
				action: 'Block::updateContent',
				data: {
					lid: this.landingId,
					block: this.blockId,
					content: this.clearHtml(this.originalNode.innerHTML).replaceAll(' style="', ' bxstyle="'),
					designed: 1
				}
			};
			if (this.autoPublicationEnabled)
			{
				batch['Landing::publication'] = {
					action: 'Landing::publication',
					data: {
						lid: this.landingId
					}
				};
			}
			batch['History::clearDesignerBlock'] = {
				action: 'History::clearDesignerBlock',
				data: {
					blockId: this.blockId,
				}
			};

			Backend.getInstance()
				.batch('Block::updateContent', batch)
				.then(() => {
					this.saving = false;
					finishCallback();
				});

			this.sendLabel(
				'designerBlock',
				'save' +
				'&designed=' + (this.designed ? 'Y' : 'N') +
				'&code=' + this.blockCode
			);
		});
	}

	initNodes()
	{
		Object.keys(this.nodes).map(selector => {
			this.cardSelectors.map(cardSelector => {
				[...this.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)].map(element => {
					if (this.nodes[selector]['useInDesigner'] === false)
					{
						return;
					}
					this.addNode({
						element,
						selector,
						cardSelector,
						type: this.nodes[selector]['type']
					});
				});
			});
		});
	}

	initGrid()
	{
		// collect node's parent and add pseudo last elements into the wrappers
		Object.keys(this.nodes).map(selector => {
			this.cardSelectors.map(cardSelector => {
				[...this.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)].map(element => {
					if (this.nodes[selector]['useInDesigner'] === false)
					{
						return;
					}
					const wrapper = (this.nodes[selector]['type'] === 'icon')
						? element.parentNode.parentNode
						: element.parentNode;
					if (Dom.attr(wrapper, 'data-landingWrapper'))
					{
						return;
					}
					const pseudoElement = DesignerBlockUI.getPseudoLast();
					Dom.attr(wrapper, 'data-landingWrapper', true);
					Dom.append(pseudoElement, wrapper);
					this.addNode({
						cardSelector,
						element: pseudoElement,
						className: selector.substr(1) + '-last',
						selector: selector + '-last'
					});
				});
			});
		});
	}

	initSliders()
	{
		const sliderSelector = '.js-carousel';
		[...this.blockNode.querySelectorAll(sliderSelector)].map(slider => {
			const count =
				(Text.toNumber(slider.dataset.slidesShow) || 1)
				* (Text.toNumber(slider.dataset.rows) || 1)
			;
			const selector = `.${[...slider.classList].join('.')} .js-slide:not(:nth-child(-n+${count}))`;
			document.head.appendChild(
				Tag.render`<style>${selector}{display: none !important;}</style>`
			);
		});
	}

	initHoverArea()
	{
		if (this.hoverArea)
		{
			return;
		}

		this.hoverArea = DesignerBlockUI.getHoverDiv();

		const addNodeElement = DesignerBlockUI.getAddNodeButton();
		const CardAction = BX.Landing.UI.Button.CardAction;
		const BaseButtonPanel = BX.Landing.UI.Panel.BaseButtonPanel;
		const cardAction = new BaseButtonPanel(
			'nodeAction',
			'landing-ui-panel-block-card-action'
		);

		Event.bind(addNodeElement, 'click', () => {
			this.repoManager.showPanel();
			this.hideHoverArea();
		});

		cardAction.addButton(new CardAction('remove', {
			html: '&nbsp;',
			onClick: this.removeElement.bind(this)
		}));

		void cardAction.show();

		Dom.append(addNodeElement, this.hoverArea);
		Dom.append(cardAction.layout, this.hoverArea);
		Dom.append(this.hoverArea, this.getDocumentBody());

		Event.bind(this.blockNode, 'mouseover', () => {
			this.hideHoverArea();
		});
	}

	adjustHoverArea()
	{
		if (!this.hoverArea)
		{
			return;
		}

		this.showHoverArea();

		const clientRect = this.activeNode.getElement().getBoundingClientRect();
		const hoverElementAdd = this.hoverArea.querySelector('.landing-designer-block-node-hover-add');
		const hoverElementActions = this.hoverArea.querySelector('div[data-id="nodeAction"]');
		const editorWindow = BX.Landing.PageObject.getEditorWindow();

		if (hoverElementActions)
		{
			if (this.activeNode.isPseudoElement())
			{
				Dom.hide(hoverElementActions);
			}
			else
			{
				Dom.show(hoverElementActions);
			}
		}

		if (hoverElementAdd)
		{
			Dom.style(
				hoverElementAdd,
				{ top: (clientRect.height - 5) + 'px' }
			);
		}

		Dom.style(
			this.hoverArea,
			{
				top: clientRect.top + editorWindow.scrollY + 'px',
				left: clientRect.left + (clientRect.width < 30 ? 30 : 0) + 'px',
				width: clientRect.width + 'px',
				height: '35px'
			}
		);
	}

	showHoverArea()
	{
		if (this.hoverArea)
		{
			Dom.show(this.hoverArea);
		}
	}

	hideHoverArea()
	{
		if (this.hoverArea)
		{
			setTimeout(() => {
				Dom.hide(this.hoverArea);
			}, 0);
		}
	}

	refreshManifest(manifest: ?ManifestNodesItem)
	{
		if (manifest)
		{
			Object.keys(manifest).map(selector => {
				this.nodes[selector] = manifest[selector];
			});
		}
		this.initNodes();
		this.initGrid();
	}

	getDocumentBody(): HTMLElement
	{
		return document.body;
	}

	isInsideElement(element: HTMLElement): boolean
	{
		return element.parentElement.tagName === 'A';
	}

	sendLabel(key: string, value: string)
	{
		this.metrika.clearSendedLabel();
		this.metrika.sendLabel(null, key, value);
	}

	addElement(repoElement: RepoElementType)
	{
		const activeNode = this.activeNode;
		const tags = [];

		[...document.body.querySelectorAll(activeNode.getSelector())].map(node => {
			const elementHtml = repoElement.html;
			const element = Tag.render`${elementHtml}`;
			const insertAfter = this.isInsideElement(node) ? node.parentNode : node;
			Dom.insertAfter(element, insertAfter);
			tags.push({
				elementHtml,
				elementSelector: BX.Landing.Utils.getCSSSelector(element),
				insertAfterSelector: BX.Landing.Utils.getCSSSelector(insertAfter)
			});
		});

		this.sendLabel(
			'designerBlock',
			'addElement' +
			'&code=' + this.blockCode +
			'&name=' + repoElement.code +
			'&preset=' + (Object.keys(repoElement.manifest.nodes).length === 1 ? 'N' : 'Y')
		);

		this.changed = true;
		this.refreshManifest(repoElement.manifest.nodes);
		this.highlight.show(null);

		Backend.getInstance()
			.action("History::pushDesignerBlock", {
				blockId: this.blockId,
				action: 'ADD_NODE',
				data: {
					tags: tags,
				},
			})
			.then(result => {
				BX.Landing.History.getInstance().push();
			});
	}

	removeElement()
	{
		const tags = [];
		this.hideHoverArea();

		this.highlight.hide();

		setTimeout(() => {

			this.sendLabel(
				'designerBlock',
				'removeElement' +
				'&tagName=' + this.activeNode.getElement().tagName +
				'&code=' + this.blockCode
			);

			[...document.body.querySelectorAll(this.activeNode.getSelector())].map(node => {
				tags.push({
					elementHtml: this.clearHtml(node.outerHTML),
					elementSelector: BX.Landing.Utils.getCSSSelector(node),
					insertAfterSelector: node.previousElementSibling ? BX.Landing.Utils.getCSSSelector(node.previousElementSibling) : null,
					parentNodeSelector: BX.Landing.Utils.getCSSSelector(node.parentNode)
				});
				this.removeNode(node);
			});

			this.changed = true;
			this.refreshManifest();

			Backend.getInstance()
				.action("History::pushDesignerBlock", {
					blockId: this.blockId,
					action: 'REMOVE_NODE',
					data: {
						selector: this.activeNode.getOriginalSelector(),
						tags: tags,
					},
				})
				.then(result => {
					BX.Landing.History.getInstance().push();
				});

		}, 0);
	}

	typeWithWrapper(type: string)
	{
		return type === 'icon' || type === 'embed';
	}

	addNode(nodeOptions: NodeType): boolean
	{
		if (!this.nodeMap.get(nodeOptions.element))
		{
			if (nodeOptions.selector.match(/^\.[\w-_]+$/i) === null)
			{
				return false;
			}

			// for some type we get parent node
			const withWrapper = this.typeWithWrapper(nodeOptions.type);
			nodeOptions.element = withWrapper
				? nodeOptions.element.parentNode
				: nodeOptions.element;
			if (withWrapper)
			{
				nodeOptions.selector = nodeOptions.selector + '--type-wrapper';
				Dom.addClass(nodeOptions.element, nodeOptions.selector.substr(1));
			}
			// mouse over callback
			nodeOptions.onHover = this.onMouseOver.bind(this);

			this.nodeMap.set(
				nodeOptions.element,
				new Node(nodeOptions)
			);
			return true;
		}
		return false;
	}

	removeNode(node: HTMLElement)
	{
		if (node)
		{
			Dom.remove(node);
			this.nodeMap.delete(node);
		}
	}

	onMouseOver(node: Node)
	{
		if (this.saving)
		{
			return;
		}
		this.activeNode = node;
		this.adjustHoverArea();
		if (!node.isPseudoElement())
		{
			this.highlight.show(node.getElement());
		}
	}
}
