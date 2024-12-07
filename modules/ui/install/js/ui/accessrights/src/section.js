import {EventEmitter, BaseEvent} from "main.core.events";
import {PopupMenu} from 'main.popup';
import {Dom, Event, Reflection, Text, Tag, Loc} from 'main.core';
import Grid from "./grid";
import Column from "./column";
import Title from "./item/title";
import UserGroupTitle from "./item/usergrouptitle";
import VariableSelector from "./item/variableselector";
import MultiSelector from "./item/multivariable/multiselector";
import Hint from "./item/hint";

type SectionOptions = {
	id: string;
	headSection: Section;
	title: string;
	hint: string;
	rights: [];
	userGroups: [];
	grid: Grid;
};

export default class Section {
	constructor(options: SectionOptions)
	{
		this.id = options.id ?? null;
		this.headSection = options.headSection ? options.headSection : null;
		this.title = options.title;
		this.hint = options.hint;
		this.rights = options.rights ? options.rights : [];
		this.userGroups = options.userGroups ? options.userGroups : [];
		this.grid = options.grid ? options.grid : null;

		this.layout = {
			title: null,
			headColumn: null,
			columns: null,
			content: null,
			earLeft: null,
			earRight: null
		};

		this.scroll = 0;
		this.earTimer = null;
		this.earLeftTimer = null;
		this.earRightTimer = null;
		this.columns = [];

		this.bindEvents();
	}

	bindEvents(): void
	{
		EventEmitter.subscribe(this.grid, 'AccessRights.Section:scroll', (event: BaseEvent) => {
			const [object] = event.getData();
			if (this.title !== object.title)
			{
				this.getColumnsContainer().scrollLeft = object.getScroll()
			}
			object.adjustEars();
			PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
		});

		Event.bind(window, 'resize', this.adjustEars.bind(this));
	}

	getGrid(): Grid
	{
		return this.grid;
	}

	addColumn(param): void
	{
		if(!param)
		{
			return;
		}

		const options = Object.assign({}, param);
		options.userGroup = param;
		const column = this.getColumn(options);

		Dom.append(column.render(), this.layout.columns)
		this.columns.push(column);
	}

	getColumn(options): Column
	{
		const controls = [];

		this.rights.map(
			(data) => {
				const isVariable = (data.type === VariableSelector.TYPE || data.type === MultiSelector.TYPE);

				controls.push({
					type: data.type,
					title: isVariable ? data.title : null,
					hint: data.hint,
					group: data.group,
					variables: isVariable ? data.variables : [],
					enableSearch: isVariable ? data.enableSearch : null,
					showAvatars: isVariable ? data.showAvatars : false,
					compactView: isVariable ? data.compactView : false,
					hintTitle: isVariable ? data.hintTitle : null,
					allSelectedCode: isVariable ? data.allSelectedCode : null,
					changerOptions: data.changerOptions || {},
					access: data
				})
			}
		);

		return new Column({
			items: controls,
			userGroup: options.userGroup ? options.userGroup : null,
			section: this,
			headSection: options.headSection,
			grid: this.grid,
			newColumn: options.newColumn ? options.newColumn : null
		});
	}

	removeColumn(param): void
	{
		if (!param)
		{
			return;
		}

		for (let i = 0; i < this.columns.length; i++)
		{
			if (param.userGroup === this.columns[i].userGroup)
			{
				this.columns[i].remove();
				break;
			}
		}
	}

	addHeadColumn(): Column
	{
		let titles = [];

		if(!this.headSection)
		{
			this.rights.map((data) => {
				titles.push({
					id: data.id,
					type: Title.TYPE,
					title: data.title,
					hint: data.hint,
					group: data.group,
					groupHead: data.groupHead,
				})
			});
		}

		if (this.headSection)
		{
			titles = [
				{
					type: UserGroupTitle.TYPE,
					title: Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLES'),
					controller: true

				},
				{
					type: UserGroupTitle.TYPE,
					title: Loc.getMessage('JS_UI_ACCESSRIGHTS_EMPLOYEES_AND_DEPARTMENTS'),
					controller: false
				}
			]
		}

		const column = new Column({
			items: titles,
			section: this,
			grid: this.grid
		});

		Dom.append(column.render(), this.layout.headColumn);

		return column;
	}

	getColumnsContainer(): HTMLElement
	{
		if (!this.layout.columns)
		{
			const column = Tag.render`<div class='ui-access-rights-section-wrapper'></div>`;
			Event.bind(column, 'scroll', this.adjustScroll.bind(this));
			this.layout.columns = column;
		}

		return this.layout.columns;
	}

	getTitleNode(): HTMLElement
	{
		const node = Tag.render`<div class='ui-access-rights-section-title'>${Text.encode(this.title)}</div>`;

		if (this.hint)
		{
			const hintNode = new Hint({
				hint: this.hint,
				className: 'ui-access-rights-section-title-hint'
			});
			node.appendChild(hintNode.render());
		}

		return node;
	}

	adjustScroll(): void
	{
		if (Text.toNumber(this.scroll) !== Text.toNumber(this.getColumnsContainer().scrollLeft))
		{
			this.scroll = this.getColumnsContainer().scrollLeft;
			EventEmitter.emit(this.grid, "AccessRights.Section:scroll", [this]);
		}
	}

	adjustEars(): void
	{
		const container = this.getColumnsContainer();
		const scroll = container.scrollLeft;

		const isLeftVisible = scroll > 0;
		const isRightVisible = container.scrollWidth > (Math.round(scroll + container.offsetWidth));

		this.getContentContainer().classList[isLeftVisible ? 'add' : 'remove']('ui-access-rights-section-ear-left-shown');
		this.getContentContainer().classList[isRightVisible ? 'add' : 'remove']('ui-access-rights-section-ear-right-shown');
	}

	getContentContainer(): HTMLElement
	{
		if (!this.layout.content)
		{
			this.layout.content = Tag.render`
				<div class='ui-access-rights-section-content'>
					${this.getColumnsContainer()}
					${this.getEarLeft()}
					${this.getEarRight()}
				</div>
			`;
		}

		return this.layout.content;
	}

	getEarLeft(): HTMLElement
	{
		if (!this.layout.earLeft)
		{
			this.layout.earLeft = Tag.render`<div class='ui-access-rights-section-ear-left'></div>`;
			Event.bind(this.layout.earLeft, 'mouseenter', () => {
				this.stopAutoScroll();
				this.earLeftTimer =	setTimeout(
					() => {
						this.scrollToLeft()
					},
					110
				);
			});

			Event.bind(this.layout.earLeft, 'mouseleave', () => {
				clearTimeout(this.earLeftTimer);
				this.stopAutoScroll()
			});
		}

		return this.layout.earLeft;
	}

	getEarRight(): HTMLElement
	{
		if (!this.layout.earRight)
		{
			this.layout.earRight = Tag.render`<div class='ui-access-rights-section-ear-right'></div>`;
			Event.bind(this.layout.earRight, 'mouseenter', () => {
				this.stopAutoScroll();
				this.earRightTimer = setTimeout(
					() => {
						this.scrollToRight()
					},
					110
				);
			});

			Event.bind(this.layout.earRight, 'mouseleave', () => {
				clearTimeout(this.earRightTimer);
				this.stopAutoScroll()
			});
		}

		return this.layout.earRight;
	}

	scrollToRight(param: number, stop): void
	{
		const interval = param ? 2 : 20;

		this.earTimer = setInterval(
			() => {
				this.getColumnsContainer().scrollLeft += 10;
				if(param && param <= this.getColumnsContainer().scrollLeft)
				{
					 this.stopAutoScroll();
				}
			},
			interval
		);

		if(stop === 'stop')
		{
			setTimeout(
				() => {
					this.stopAutoScroll();
					this.getGrid().unlock();
				},
				param * 2
			)
		}
	}

	scrollToLeft(): void
	{
		this.earTimer = setInterval(
			() => {
				this.getColumnsContainer().scrollLeft -= 10;
			},
			20
		)
	}

	stopAutoScroll(): void
	{
		clearInterval(this.earTimer);
	}

	getScroll(): number
	{
		return this.scroll;
	}

	render(): HTMLElement
	{
		const title = this.title ? this.getTitleNode() : null;

		const sectionContainer = Tag.render`
			<div class='ui-access-rights-section'>
				${title}
				${this.getMainContainer()}
			</div>
		`;

		if (this.headSection)
		{
			Dom.addClass(sectionContainer, 'ui-access-rights--head-section')
		}

		this.addHeadColumn();

		const columnsFragment = document.createDocumentFragment();

		const userGroups = this.grid.getUserGroups() ?? [];
		for (let i = 0; i < userGroups.length; i++)
		{
			const column = this.getColumn(
				{
					headSection: this.headSection ? this.headSection : null,
					userGroup: userGroups[i]
				}
			);

			this.columns.push(column);
			Dom.append(column.render(), columnsFragment);
		}

		Dom.append(columnsFragment, this.getColumnsContainer());

		return sectionContainer;
	}

	getMainContainer(): HTMLElement
	{
		this.layout.headColumn = Tag.render`<div class='ui-access-rights-section-head'></div>`;
		return Tag.render`
			<div class='ui-access-rights-section-container'>
				${this.layout.headColumn}
				${this.getContentContainer()}
			</div>
		`;
	}
}

const namespace = Reflection.namespace('BX.UI.AccessRights');
namespace.Section = Section;
