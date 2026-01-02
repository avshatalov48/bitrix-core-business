import type { RelationData } from '../type/data';
import type { BarOptions, BarEntityLinkOptions } from '../type/constructor-options';
import { Tag, Dom, Loc, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Loader } from 'main.loader';
import { Messenger } from 'im.public';
import { Icon, Outline } from 'ui.icon-set.api.core';
import 'ui.icon-set.main';

export default class Bar
{
	constructor(options: BarOptions)
	{
		this.parentNode = options.parentNode;
		this.init();
	}

	init(): void
	{
		this.bar = Tag.render`
			<div class="calendar-relation-bar">
			</div>
		`;
		Event.bind(this.bar, 'mouseenter', () => {
			EventEmitter.emit('BX.Calendar.EntityRelation.onMouseEnter');
		});
	}

	renderLoader(): HTMLElement
	{
		Dom.clean(this.bar);
		if (!this.loaderWrap)
		{
			this.loaderWrap = Tag.render`<div class="calendar-relation-bar-loader"></div>`;
		}
		Dom.append(this.loaderWrap, this.bar);
		this.showLoader();

		return this.bar;
	}

	showLoader(): void
	{
		if (this.loader)
		{
			this.loader.destroy();
		}

		this.loader = new Loader({
			target: this.loaderWrap,
			size: 22,
			color: '#2066B0',
			offset: {
				left: '0px',
				top: '0px',
			},
			mode: 'inline',
		});
		this.loader.show();
	}

	render(relationData: RelationData, entityLink: ?HTMLElement): HTMLElement
	{
		Dom.clean(this.bar);
		Dom.append(entityLink, this.bar);
		Dom.append(this.getOwnerData(relationData), this.bar);

		return this.bar;
	}

	getEntityLink({ link = '#', text, title }: BarEntityLinkOptions): ?HTMLElement
	{
		const arrowIcon = new Icon({
			icon: Outline.CHEVRON_RIGHT_M,
			size: 20,
			color: 'rgb(130, 139, 149)',
		});

		return Tag.render`
			<a
				class="calendar-relation-entity-link"
				href="${link}"
				title="${title}"
			>
				<div class="calendar-relation-entity-link-text">
					${text}
				</div>
				<div class="calendar-relation-entity-link-arrow">
					${arrowIcon.render()}
				</div>
			</a>
		`;
	}

	getOwnerData(relationData: RelationData): HTMLElement
	{
		const chatIcon = new Icon({
			icon: Outline.MESSAGES,
			size: 22,
			color: 'rgb(0, 117, 255)',
		});

		const { root, chatButton } = Tag.render`
			<div class="calendar-relation-owner">
				<div class="calendar-relation-owner-role">${Loc.getMessage('CALENDAR_RELATION_OWNER_ROLE_DEAL')}</div>
				<div class="calendar-relation-owner-info">
					${this.getOwnerAvatarNode(relationData)}
					${this.getOwnerNameNode(relationData)}
					<div
						ref="chatButton"
						class="calendar-relation-owner-chat"
						title="${Loc.getMessage('CALENDAR_RELATION_CHAT_BUTTON_HINT')}"
					>
						${chatIcon.render()}
					</div>
				</div>
			</div>
		`;

		Event.bind(chatButton, 'click', () => this.openChat(relationData.owner.id));

		return root;
	}

	getOwnerAvatarNode(relationData: RelationData): HTMLElement
	{
		const avatarWrap = Tag.render`
			<a
				href="${relationData.owner.link}"
				class="calendar-relation-owner-avatar ui-icon ui-icon-common-user"
				title="${Loc.getMessage('CALENDAR_RELATION_OWNER_PROFILE_HINT')}"
			>
			</a>
		`;

		let avatar = null;

		if (relationData.owner.avatar)
		{
			avatar = Tag.render`
				<img
					src="${encodeURI(relationData.owner.avatar)}"
					alt=""
				/>
			`;
		}
		else
		{
			avatar = Tag.render`
				<i></i>
			`;
		}

		Dom.append(avatar, avatarWrap);

		return avatarWrap;
	}

	getOwnerNameNode(relationData: RelationData): HTMLElement
	{
		return Tag.render`
			<a
				class="calendar-relation-owner-name"
				href="${relationData.owner.link}"
				title="${Loc.getMessage('CALENDAR_RELATION_OWNER_PROFILE_HINT')}"
			>
				${relationData.owner.name}
			</a>
		`;
	}

	openChat(chatId: number)
	{
		Messenger.openChat(chatId);
	}
}
