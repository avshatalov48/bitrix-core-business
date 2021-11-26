import {Type, Loc, Dom} from 'main.core';
import {CommentRenderer} from 'tasks.comment-renderer';

export class RenderParts
{
	static currentUserSonetGroupIdList = [];
	static mobile: false;
	static publicSection: false;
	static currentExtranetUser: false;
	static availableUsersList: [];

	static init(params)
	{
		if (!Type.isUndefined(params.currentUserSonetGroupIdList))
		{
			this.currentUserSonetGroupIdList = params.currentUserSonetGroupIdList;
		}
		if (!Type.isUndefined(params.publicSection))
		{
			this.publicSection = !!params.publicSection;
		}
		this.mobile = !!params.mobile;

		if (!Type.isUndefined(params.currentExtranetUser))
		{
			this.currentExtranetUser = !!params.currentExtranetUser;
		}

		if (this.currentExtranetUser)
		{
			if (Type.isPlainObject(params.availableUsersList))
			{
				params.availableUsersList = Object.entries(params.availableUsersList).map(([key, value]) => value);
			}

			if (Type.isArray(params.availableUsersList))
			{
				this.availableUsersList = params.availableUsersList.map(value => parseInt(value)).filter(value => !Type.isNil(value));
			}
		}
	}

	static getNodeSG(entity)
	{
		const hidden = (
			Type.isStringFilled(entity.VISIBILITY)
			&& entity.VISIBILITY === 'group_members'
			&& !this.currentUserSonetGroupIdList.includes(entity.ENTITY_ID)
		);

		if (hidden)
		{
			return this.getNodeHiddenDestination();
		}
		else
		{
			return (
				!this.mobile
					? Dom.create('a', {
						attrs: {
							href: entity.LINK,
							target: '_blank',
						},
						text: entity.NAME,
					})
					: Dom.create('span', {
						text: entity.NAME,
					})
			);
		}
	}

	static getNodeU(entity)
	{
		const hidden = (
			this.currentExtranetUser
			&& !this.availableUsersList.includes(entity.ENTITY_ID)
		);

		if (hidden)
		{
			return this.getNodeHiddenDestination();
		}
		else
		{
			const classesList = [
				'blog-p-user-name',
			];

			if (entity.VISIBILITY === 'extranet')
			{
				classesList.push('blog-p-user-name-extranet');
			}

			return (
				!this.mobile
					? Dom.create('a', {
						attrs: {
							href: entity.LINK,
						},
						props: {
							className: classesList.join(' '),
						},
						text: entity.NAME
					})
					: Dom.create('a', {
						attrs: {
							href: entity.LINK,
						},
						text: entity.NAME,
					})
			);
		}
	}

	static getNodeDR(entity)
	{
		return (
			!this.mobile
				? Dom.create('a', {
					attrs: {
						href: entity.LINK,
						target: '_blank',
					},
					text: entity.NAME,
				})
				: Dom.create('span', {
					text: entity.NAME,
				})
		);
	}

	static getNodeTask(entity)
	{
		return (
			!this.mobile
			&& !this.publicSection
			&& entity.LINK.length > 0
			&& typeof entity.VISIBILITY != 'undefined'
			&& typeof entity.VISIBILITY.userId != 'undefined'
			&& parseInt(entity.VISIBILITY.userId) == parseInt(Loc.getMessage('USER_ID'))
				? Dom.create('a', {
					attrs: {
						href: entity.LINK,
						target: '_blank',
					},
					text: entity.NAME,
				})
				: Dom.create('span', {
					text: entity.NAME,
				})
		);
	}

	static getNodePost(entity)
	{
		return (
			!this.mobile
			&& !this.publicSection
			&& entity.LINK.length > 0
			&& Type.isPlainObject(entity.VISIBILITY)
			&& entity.VISIBILITY.available === true

				? Dom.create('a', {
					attrs: {
						href: entity.LINK,
						target: '_blank'
					},
					text: entity.NAME,
				})
				: Dom.create('span', {
					text: entity.NAME,
				})
		);
	}

	static getNodeCalendarEvent(entity)
	{
		return (
			!this.mobile
			&& !this.publicSection
			&& entity.LINK.length > 0
			&& Type.isPlainObject(entity.VISIBILITY)
			&& entity.VISIBILITY.available === true
				? Dom.create('a', {
					attrs: {
						href: entity.LINK,
						target: '_blank',
					},
					text: entity.NAME,
				})
				: Dom.create('span', {
					text: entity.NAME,
				})
		);
	}

	static getNodeUA()
	{
		return Dom.create('span', {
			text: Loc.getMessage('SONET_RENDERPARTS_JS_DESTINATION_ALL'),
		});
	}

	static getNodeHiddenDestination()
	{
		return Dom.create('span', {
			text: Loc.getMessage('SONET_RENDERPARTS_JS_HIDDEN'),
		});
	}

	static getTaskCommentPart(entity): string
	{
		return CommentRenderer.getCommentPart(entity);
	}
}
