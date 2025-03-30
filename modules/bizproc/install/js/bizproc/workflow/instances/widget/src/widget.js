import { ajax, Dom, Tag, Text, Type, Loc, Event, Runtime } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';
import { footerTypeEnum, ImageStackSteps, imageTypeEnum } from 'ui.image-stack-steps';
import { Label, LabelColor, LabelSize } from 'ui.label';
import { DateTimeFormat } from 'main.date';

import 'main.polyfill.intersectionobserver';

import 'ui.design-tokens';
import 'ui.icons';
import 'ui.icon-set.main';
import './css/style.css';

type WidgetParams = {
	tplId: number,
	allCount: number,
	users: Array<WidgetUsers>,
}

type WidgetUsers = {
	id: number,
	avatarUrl?: string,
}

const defaulFormatDuration = [
	['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff'],
];

const autoRunIconType = {
	type: imageTypeEnum.ICON,
	data: { icon: 'business-process-1', color: 'var(--ui-color-base-10)' },
};

export class Widget
{
	#params: WidgetParams;
	#stack: ImageStackSteps;
	#popupInstance: Popup;
	#popupListNode: Element;

	#listSkeleton: Element;
	#offset: number = 0;

	constructor(params: WidgetParams)
	{
		this.#params = params;
		this.#initStack();
	}

	static renderTo(node: HTMLElement): Widget
	{
		const instance = new Widget(JSON.parse(node.dataset.widget));

		Dom.replace(node, instance.render());
	}

	#initStack()
	{
		this.#stack = new ImageStackSteps({
			steps: [
				{
					id: 'basis',
					stack: {
						images: Type.isArrayFilled(this.#params.users)
							? this.#getStackUserImages(this.#params.users, this.#params.allCount)
							: this.#getEmptyStackImages()
						,
					},
					footer: {
						type: footerTypeEnum.TEXT,
						data: {
							text: this.#getStackText(this.#params.allCount),
						},
						styles: { maxWidth: 90 },
					},
					styles: { minWidth: 90 },
				},
			],
		});
	}

	#getStackText(counter: number): string
	{
		if (counter < 1)
		{
			return Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_LIST_EMPTY');
		}

		return Loc.getMessagePlural(
			'BIZPROC_JS_WORKFLOW_INST_WIDGET_LIST',
			counter,
			{
				'#COUNT#': counter < 100 ? counter : '99+',
			},
		);
	}

	#getStackUserImages(avatars, allCount: number): []
	{
		const images = [];
		avatars.forEach((avatar) => {
			const userId = Text.toInteger(avatar.id);
			if (userId > 0)
			{
				images.push({
					type: imageTypeEnum.USER,
					data: { userId, src: String(avatar.avatarUrl || '') },
				});
			}
			else
			{
				images.push(autoRunIconType);
			}
		});

		if (allCount > 3)
		{
			const mixed = images.slice(0, 2);
			mixed.push({
				type: imageTypeEnum.COUNTER,
				data: { text: `+${allCount - 2}` },
			});

			return mixed;
		}

		return images;
	}

	#getEmptyStackImages(fill: {}, length: number = 3): []
	{
		return Array.from({ length }).fill(fill ?? { type: imageTypeEnum.USER_STUB });
	}

	render(): HTMLElement
	{
		const isEmpty = this.#params.allCount < 1;
		const node = Tag.render`<div class="bp-workflow-instances-widget ${isEmpty ? '--empty' : ''}"></div>`;
		this.#stack.renderTo(node);

		if (!isEmpty)
		{
			Event.bind(node, 'click', this.#handleClick.bind(this));
		}

		return node;
	}

	#handleClick(event: BaseEvent)
	{
		if (!this.#popupInstance)
		{
			this.#popupInstance = new Popup({
				autoHide: true,
				width: 305,
				minHeight: 342,
				animation: 'fading-slide',
				content: this.#getPopupContent(),
				bindElement: event.target,
				padding: 0,
				borderRadius: '12px',
			});
		}

		this.#popupInstance.toggle();
	}

	#getPopupContent(): Element
	{
		this.#listSkeleton = this.#renderListSkeleton();
		this.#popupListNode = Tag.render`<div class="bizproc-workflow-instances-popup-list">${this.#listSkeleton}</div>`;
		this.#loadList();

		return Tag.render`
			<div class="bizproc-workflow-instances-popup-content">
				<div class="bizproc-workflow-instances-popup-title">${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TITLE')}</div>
				<div class="bizproc-workflow-instances-popup-text">
					${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TEXT_P1')}
					<br>
					${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TEXT_P2')}
				</div>
				<div class="bizproc-workflow-instances-popup-heads">
					<div class="bizproc-workflow-instances-popup-heads-item">
						${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_AUTHOR')}
					</div>
					<div class="bizproc-workflow-instances-popup-heads-item">
						${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_IN_PROGRESS')}
					</div>
					<div class="bizproc-workflow-instances-popup-heads-item">
						${Loc.getMessage('BIZPROC_JS_WORKFLOW_INST_WIDGET_POPUP_TIME')}
					</div>
				</div>
				${this.#popupListNode}
			</div>
		`;
	}

	#renderListSkeleton(): Element
	{
		let i = 0;
		let opacity = 1.15;
		const target = Tag.render`<div class="bizproc-workflow-instances-popup-list-page"></div>`;
		while (i < 5)
		{
			++i;
			opacity -= 0.15;
			const facesNode = this.#renderListItemFaces();
			const label = new Label({
				color: LabelColor.DEFAULT,
				size: LabelSize.SM,
				fill: true,
				customClass: 'bizproc-workflow-instances-popup-list-item-time-skeleton',
			});
			const node = Tag.render`
				<div class="bizproc-workflow-instances-popup-list-item" style="opacity: ${opacity}">
					${facesNode}
					<div class="bizproc-workflow-instances-popup-list-item-time">${label.render()}</div>
				</div>
			`;
			Dom.append(node, target);
		}

		return target;
	}

	#renderListPage(list: []): Element
	{
		const pageNode = Tag.render`<div class="bizproc-workflow-instances-popup-list-page"></div>`;

		list.forEach((item) => {
			const facesNode = this.#renderListItemFaces(item.avatars.author, item.avatars.running);
			const label = new Label({
				text: this.#formatDuration(item.time.current),
				color: LabelColor.LIGHT_BLUE,
				size: LabelSize.SM,
				fill: true,
			});
			const itemNode = Tag.render`
				<div class="bizproc-workflow-instances-popup-list-item">
					${facesNode}
					<div class="bizproc-workflow-instances-popup-list-item-time">${label.render()}</div>
				</div>
			`;

			Dom.append(itemNode, pageNode);
		});

		return pageNode;
	}

	#loadList(): void
	{
		ajax.runAction('bizproc.workflow.getTemplateInstances', {
			data: { templateId: this.#params.tplId, offset: this.#offset },
		}).then((response) => {
			this.#offset += response.data.list.length;

			Dom.append(this.#renderListPage(response.data.list), this.#popupListNode);
			Dom.append(this.#listSkeleton, this.#popupListNode); // move skeleton to the end

			this.#handleNextPage(response.data.hasNextPage);
		}).catch((response) => {
			if (response.errors?.length > 0)
			{
				Runtime.loadExtension('ui.dialogs.messagebox')
					.then(({ MessageBox }) => {
						MessageBox.alert(Text.encode(response.errors[0].message));
					})
					.catch(() => {})
				;
			}
			else
			{
				console?.error(response);
			}
		});
	}

	#handleNextPage(hasNextPage: boolean): void
	{
		if (hasNextPage && this.#listSkeleton)
		{
			new IntersectionObserver((entries, observer) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting)
					{
						observer.disconnect();
						this.#loadList();
					}
				});
			}).observe(this.#listSkeleton);

			return;
		}

		Dom.remove(this.#listSkeleton);
		this.#listSkeleton = null;
	}

	#renderListItemFaces(author: [], running: []): Element
	{
		const facesNode = Tag.render`
			<div class="bizproc-workflow-instances-popup-list-item-faces"></div>
		`;

		const stack = new ImageStackSteps(
			{
				steps: [
					{
						id: 'col-1',
						stack: {
							images: Type.isArrayFilled(author)
								? this.#getStackUserImages(author)
								: this.#getEmptyStackImages(autoRunIconType, 1)
							,
						},
						styles: {
							minWidth: 36,
						},
					},
					{
						id: 'col-2',
						stack: {
							images: Type.isArrayFilled(running)
								? this.#getStackUserImages(running)
								: this.#getEmptyStackImages(autoRunIconType, 1)
							,
						},
					},
				],
			},
		);
		stack.renderTo(facesNode);

		return facesNode;
	}

	#formatDuration(duration?: number): string
	{
		if (!duration)
		{
			return '?';
		}

		return DateTimeFormat.format(defaulFormatDuration, 0, duration);
	}
}
