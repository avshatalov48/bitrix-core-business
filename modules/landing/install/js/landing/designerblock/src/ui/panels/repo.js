import {Loc} from 'landing.loc';
import {Content} from 'landing.ui.panel.content';
import {Dom, Cache, Tag} from 'main.core';

export type RepoElementType = {
	name: string,
	code: string,
	html: string,
	manifest: {
		nodes: {
			[selector: string]: {
				type: string
			}
		},
		style: {
			[selector: string]: {
				type: string
			}
		}
	}
};

export type RepoPanelOptions = {
	onElementSelect: (RepoElementType) => {}
};


export class RepoPanel extends Content
{
	constructor(options: RepoPanelOptions)
	{
		super('design_repo', {
			title: Loc.getMessage('LANDING_DESIGN_BLOCK_REPO_TITLE'),
			scrollAnimation: true
		});

		this.currentCategory = null;
		this.cache = new Cache.MemoryCache();
		this.onElementSelect = options.onElementSelect;

		this.renderTo(
			parent.document.body
			? parent.document.body
			: document.body
		);

		Dom.addClass(this.layout, 'landing-ui-panel-repo');
	}

	addRepository(repository: Array<RepoElementType>)
	{
		repository.map(item => {
			this.addElement(item);
		});
	}

	makeElementUnique(element: RepoElementType): RepoElementType
	{
		const newManifest = {};
		const newStyleManifest = {};
		const origNodes = element.manifest.nodes;
		Object.keys(element.manifest.nodes).map(selector => {
			const randPostfix = '-' + this.randomNum(1000, 9999);
			const className = selector.substring(1);
			element.html = element.html.replaceAll(new RegExp(className + '([\\s"]{1})', 'g'), className + randPostfix + '$1');
			newManifest[selector + randPostfix] = element.manifest.nodes[selector];
			if (element.manifest.style && selector in element.manifest.style)
			{
				newStyleManifest[selector + randPostfix] = element.manifest.style[selector];
			}
		});
		element.manifest.nodes = newManifest;
		if (element.manifest.style)
		{
			Object.keys(element.manifest.style).map(selector => {
				if (selector in origNodes)
				{
					return;
				}
				const randPostfix = '-' + this.randomNum(1000, 9999);
				const className = selector.substring(1);
				element.html = element.html.replaceAll(new RegExp(className + '([\\s"]{1})', 'g'), className + randPostfix + '$1');
				newStyleManifest[selector + randPostfix] = element.manifest.style[selector];
			});
			element.manifest.style = newStyleManifest;
		}
		return element;
	}

	addElement(element: RepoElementType)
	{
		const nodeCard = new BX.Landing.UI.Card.BlockPreviewCard({
			title: element.name,
			image: '/bitrix/images/landing/designerblock/presets/' + element .code + '.jpg',
			onClick: () => {
				this.onElementSelect(this.makeElementUnique(element));
				void this.hide();
			}
		});
		this.appendCard(nodeCard);
	}

	randomNum(min: number, max: number): number
	{
		return parseInt(Math.random() * (max - min) + min);
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`<div class="landing-ui-field-layer-list-container"></div>`;
		});
	}
}
