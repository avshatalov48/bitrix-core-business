import { ajax, Loc, Tag, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { Switcher } from 'ui.switcher';

type Params = {
	userId: number,
	spaceId: number,
	bindElement: HTMLElement,
	spaceName: string,
	compositionFilters: any,
	mainFilterId: string,
	appliedFields: string[],
}

export class DiscussionsComposition extends EventEmitter
{
	#params: Params;
	#menu: Menu;
	#fields: string[];
	#appliedFields: string[];
	#switchers: Object;
	#spaceName: string;
	#spaceId: number;
	#userId: number;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsComposition');

		this.#params = params;
		this.#init();
	}

	#init()
	{
		this.#spaceId = this.#params.spaceId;
		this.#userId = this.#params.userId;
		this.#spaceName = this.#params.spaceName;
		this.#fields = this.#params.compositionFilters;
		this.#appliedFields = JSON.parse(this.#params.appliedFields);
		this.#switchers = {};
		this.#menu = this.#createMenu(this.#params.bindElement);
	}

	show()
	{
		this.#menu.show();
	}

	#createMenu(bindElement: HTMLElement): PopupComponentsMaker
	{
		return new PopupComponentsMaker({
			id: 'spaces-discussions-composition',
			target: bindElement,
			padding: 0,
			contentPadding: 0,
			offsetTop: 5,
			width: 300,
			useAngle: false,
			content: [
				{
					html: [
						{
							html: this.#renderMenuContent(),
						},
					],
				},
			],
		});
	}

	#renderMenuContent()
	{
		return Tag.render`
			<div>
				${this.#renderHeader()}
				${this.#renderFilters()}
			</div>
		`;
	}

	#renderHeader(): HTMLElement
	{
		return Tag.render`
			<div class="sn-spaces-discussions-composition-header" data-id="spaces-discussions-composition-header">
				<div class="sn-spaces-discussions-composition-header-title">
					${
						Loc
							.getMessage('SN_SPACES_DISCUSSION_COMPOSITION_TITLE')
							.replace('%SPACE_NAME%', `<span>${Text.encode(this.#spaceName)}</span>`)
					}
				</div>
				<div class="sn-spaces-discussions-composition-header-icon"></div>
			</div>
		`;
	}

	#renderFilters(): HTMLElement
	{
		const filtersContainer = Tag.render`
			<div data-id="spaces-discussions-composition-filters" class="sn-spaces-discussions-composition-filters"></div>
		`;

		this.#fields.forEach((field: string) => {
			const messageId = `SN_SPACES_DISCUSSIONS_COMPOSITION_FILTER_${field.toUpperCase()}`;

			const { container, switchButton } = Tag.render`
				<div
					id="spaces-discussions-composition-filter-${field}" 
					ref="container"
					class="sn-spaces-discussions-composition-filters-item"
				>
					<div ref="switchButton" class="sn-spaces-discussions-composition-item-switcher"></div>
					<div class="sn-spaces__popup-settings_name">
						${Loc.getMessage(messageId)}
					</div>
				</div>
			`;

			this.#switchers[field] = new Switcher({
				node: switchButton,
				checked: this.#appliedFields.includes(field),
				color: 'green',
				size: 'small',
				handlers: {
					toggled: () => {
						if (this.#switchers[field].isChecked())
						{
							this.#appliedFields.push(field);
						}
						else
						{
							this.#appliedFields.splice(this.#appliedFields.indexOf(field), 1);
						}

						this.#applyComposition();
					},
				},
			});

			filtersContainer.append(container);
		});

		return filtersContainer;
	}

	#applyComposition()
	{
		this.#setCompositionSettings().then(() => {
			this.#refresh();
		});
	}

	#setCompositionSettings(): Promise
	{
		return ajax.runAction('socialnetwork.api.livefeed.spaces.composition.setSettings', {
			data: {
				composition: this.#spaceId,
				settings: this.#appliedFields,
			},
		});
	}

	#refresh(): void
	{
		const params = {
			composition: this.#appliedFields,
			context: 'spaces',
			useBXMainFilter: 'Y',
			spaceId: this.#spaceId,
			spaceUserId: this.#userId,
		};
		BX.Livefeed.PageInstance.refresh(params, null);
	}
}