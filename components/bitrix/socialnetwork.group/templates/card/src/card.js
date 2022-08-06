import {Type, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Circle} from 'ui.graph.circle';

import {WorkgroupCardFavorites} from './favorites';
import {WorkgroupCardSubscription} from './subscription';
import {WorkgroupCardThemePicker} from './themepicker';
import {WorkgroupCardAvatar} from './avatar';

class WorkgroupCard
{
	constructor()
	{
		this.componentName = '';
		this.signedParameters = '';

		this.instance = null;
		this.currentUserId = null;

		this.userRole = null;
		this.initiatedByType = null;
		this.initiatedByUserId = null;
		this.userIsMember = null;
		this.userIsAutoMember = null;
		this.userIsScrumMaster = null;

		this.canInitiate = null;
		this.canModify = null;
		this.canLeave = null;

		this.groupId = null;
		this.isProject = null;
		this.isScrumProject = null;
		this.styles = null;
		this.urls = null;
		this.containerNode = null;
		this.menuButtonNode = null;
		this.editFeaturesAllowed = true;
		this.copyFeatureAllowed = true;

		this.favoritesInstance = null;
	}

	init(params)
	{
		if (
			Type.isUndefined(params)
			|| Type.isUndefined(params.groupId)
			|| parseInt(params.groupId) <= 0
		)
		{
			return;
		}

		this.componentName = (params.componentName ?? '');
		this.signedParameters = (params.signedParameters ?? '');
		this.currentUserId = parseInt(params.currentUserId);

		this.groupId = parseInt(params.groupId);
		this.groupType = params.groupType;
		this.isProject = !!params.isProject;
		this.isScrumProject = !!params.isScrumProject;
		this.isOpened = !!params.isOpened;

		this.userRole = params.userRole;
		this.initiatedByType = params.initiatedByType;
		this.initiatedByUserId = parseInt(params.initiatedByUserId);
		this.userIsMember = !!params.userIsMember;
		this.userIsAutoMember = !!params.userIsAutoMember;
		this.userIsScrumMaster = this.isScrumProject && (Type.isBoolean(params.userIsScrumMaster) ? params.userIsScrumMaster : false);

		this.canInitiate = !!params.canInitiate;
		this.canProcessRequestsIn = !!params.canProcessRequestsIn;
		this.canModify = !!params.canModify;
		this.canLeave = (
			Type.isBoolean(params.canLeave)
				? params.canLeave
				: (
					this.userIsMember
					&& this.userRole !== 'A'
					&& !this.userIsAutoMember
					&& !this.userIsScrumMaster
				)
		);

		this.containerNode = (Type.isStringFilled(params.containerNodeId) ? document.getElementById(params.containerNodeId) : null);
		this.menuButtonNode = (Type.isStringFilled(params.menuButtonNodeId) ? document.getElementById(params.menuButtonNodeId) : null);
		this.editFeaturesAllowed = (!Type.isUndefined(params.editFeaturesAllowed) ? !!params.editFeaturesAllowed : true);
		this.copyFeatureAllowed = (!Type.isUndefined(params.copyFeatureAllowed) ? !!params.copyFeatureAllowed : true);

		this.favoritesInstance = new WorkgroupCardFavorites({
			groupId: this.groupId,
			value: !!params.favoritesValue,
			containerNode: this.containerNode,
		});

		this.subscriptionInstance = new WorkgroupCardSubscription({
			groupId: this.groupId,
			buttonNode: (Type.isStringFilled(params.subscribeButtonNodeId) ? document.getElementById(params.subscribeButtonNodeId) : null),
		});

		new WorkgroupCardThemePicker({
			containerNode: this.containerNode,
		});

		new WorkgroupCardAvatar({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			containerNode: this.containerNode,
			groupId: this.groupId,
		});

		if (
			this.containerNode
			&& Type.isPlainObject(params.styles)
		)
		{
			this.styles = params.styles;

			if (
				Type.isPlainObject(params.styles.tags)
				&& Type.isStringFilled(params.styles.tags.box)
			)
			{
				this.containerNode.querySelectorAll('[bx-tag-value]').forEach((node) => {
					node.addEventListener('click', (e) => {
						const tagValue = e.target.getAttribute('bx-tag-value');
						if (Type.isStringFilled(tagValue))
						{
							this.clickTag(tagValue);
						}
						e.preventDefault();
					}, true);
				});
			}

			if (
				Type.isPlainObject(params.tasksEfficiency)
				&& params.tasksEfficiency.available === true
			)
			{
				const circleNode = this.containerNode.querySelector('.socialnetwork-group-slider-efficency');
				if (circleNode)
				{
					const circle = new Circle(circleNode, 131, Number(params.tasksEfficiency.value), null, null);
					circle.show();
				}
			}

			const efficiencyHelperNode = this.containerNode.querySelector('[data-role="efficiency-helper"]');
			if (efficiencyHelperNode)
			{
				efficiencyHelperNode.addEventListener('click', () => {
					top.BX.Helper.show('redirect=detail&code=6576263');
				});
			}
		}

		if (Type.isPlainObject(params.urls))
		{
			this.urls = params.urls;
		}

		if (Type.isDomNode(this.menuButtonNode))
		{
			const sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();
			sonetGroupMenu.favoritesValue = this.favoritesInstance.getValue();

			this.menuButtonNode.addEventListener('click', () => {

				BX.SocialnetworkUICommon.showGroupMenuPopup({
					bindElement: this.menuButtonNode,
					groupId: this.groupId,
					groupType: this.groupType,
					userIsMember: this.userIsMember,
					userIsAutoMember: this.userIsAutoMember,
					userIsScrumMaster: this.userIsScrumMaster,
					userRole: this.userRole,
					initiatedByType: this.initiatedByType,
					initiatedByUserId: this.initiatedByUserId,
					editFeaturesAllowed: this.editFeaturesAllowed,
					copyFeatureAllowed: this.copyFeatureAllowed,
					isProject: this.isProject,
					isScrumProject: this.isScrumProject,
					isOpened: this.isOpened,
					perms: {
						canInitiate: this.canInitiate,
						canProcessRequestsIn: this.canProcessRequestsIn,
						canModify: this.canModify,
						canLeave: this.canLeave,
					},
					urls: {
						requestUser: Loc.getMessage('SGCSPathToRequestUser'),
						edit: Loc.getMessage('SGCSPathToEdit'),
						delete: Loc.getMessage('SGCSPathToDelete'),
						features: Loc.getMessage('SGCSPathToFeatures'),
						members: Loc.getMessage('SGCSPathToMembers'),
						requests: Loc.getMessage('SGCSPathToRequests'),
						requestsOut: Loc.getMessage('SGCSPathToRequestsOut'),
						userRequestGroup: Loc.getMessage('SGCSPathToUserRequestGroup'),
						userLeaveGroup: Loc.getMessage('SGCSPathToUserLeaveGroup'),
						copy: Loc.getMessage('SGCSPathToCopy')
					},
				});
			}, true);
		}

		EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onSliderMessage.bind(this));
	}

	clickTag(tagValue)
	{
		if (!Type.isStringFilled(tagValue))
		{
			return;
		}

		top.location.href = Loc.getMessage('SGCSPathToGroupTag').replace('#tag#', tagValue);
	}

	onSliderMessage(event: BaseEvent)
	{
		const [ sliderEvent ] = event.getCompatData();

		if (sliderEvent.getEventId() !== 'sonetGroupEvent')
		{
			return;
		}
		const eventData = sliderEvent.getData();

		if (
			!Type.isStringFilled(eventData.code)
			|| !Type.isPlainObject(eventData.data)
		)
		{
			return;
		}

		if (
			eventData.code === 'afterJoinRequestSend'
			&& parseInt(eventData.data.groupId) === this.groupId
		)
		{
			BX.SocialnetworkUICommon.reload();
		}
		else if (
			eventData.code === 'afterEdit'
			&& Type.isPlainObject(eventData.data.group)
			&& parseInt(eventData.data.group.ID) === this.groupId
		)
		{
			BX.SocialnetworkUICommon.reload();
		}
		else if (
			[ 'afterDelete', 'afterLeave', 'afterIncomingRequestCancel',  ].includes(eventData.code)
			&& !Type.isUndefined(eventData.data.groupId)
			&& parseInt(eventData.data.groupId) === this.groupId
		)
		{
			if (window !== top.window) // frame
			{
				top.BX.SidePanel.Instance.getSliderByWindow(window).close();
			}

			top.location.href = this.urls.groupsList;
		}
	}
}

export {
	WorkgroupCard,
}