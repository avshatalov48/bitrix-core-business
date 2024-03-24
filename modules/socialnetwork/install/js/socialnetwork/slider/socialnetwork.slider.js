(function() {

if (window.top !== window)
{
	return;
}

if (
	!BX.type.isNotEmptyString(BX.message('SONET_SLIDER_USER_SEF'))
	|| BX.message('SONET_SLIDER_INTRANET_INSTALLED') !== 'Y'
)
{
	return;
}

const siteDir = ('/' + (BX.message.SITE_DIR || '/')
	.replace(/[\\*+?.()|[\]{}]/g, '\\$&') + '/')
	.replace(/\/+/g, '/')
;

const isSpacesAvailable = BX.Extension.getSettings('socialnetwork.slider').get('isSpacesAvailable') === 'Y';

const rules = [
	{
		condition: [
			BX.message('SONET_SLIDER_USER_SEF') + 'user/(\\d+)/groups/create/'
		],
		loader: 'group-loader',
		options: {
			width: 1200
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/edit/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 1200,
				loader: '/bitrix/js/socialnetwork/slider/images/group.svg',
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/invite/',
			BX.message('SONET_SLIDER_SPACES_SEF') + 'group/(\\d+)/invite/',
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 950,
				loader: 'group-invite-loader',
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/features/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 800,
				loader: 'group-features-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/card/'
		],
		loader: 'socialnetwork:group-card',
		options: {
			width: 900
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/users/',
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/moderators/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 1200,
				loader: 'group-users-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/user_request/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 800,
				loader: 'group-user-request-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/user_leave/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 800,
				loader: 'group-user-leave-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/requests/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 1200,
				loader: 'group-requests-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/requests_out/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 1200,
				loader: 'group-requests-out-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/delete/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 800,
				loader: 'group-delete-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
	{
		condition: [
			BX.message('SONET_SLIDER_GROUP_SEF') + 'group/(\\d+)/copy/'
		],
		handler: function(event, link)
		{
			BX.SidePanel.Instance.open(link.url, {
				width: 1000,
				loader: 'group-copy-loader'
			});
			BX.SocialnetworkUICommon.closeGroupCardMenu(link.anchor);
			event.preventDefault();
		}
	},
];

rules.push(
	{
		condition: [
			new RegExp(`${siteDir}workgroups/group/[0-9]+/$`, 'i'),
		],
		options: {
			contentClassName: 'bitrix24-group-slider-content',
			loader: 'intranet:slider-livefeed',
			cacheable: false,
			customLeftBoundary: 0,
			newWindowLabel: true,
			copyLinkLabel: true,
		},
	},
	{
		condition: [
			new RegExp(`${siteDir}workgroups/group/[0-9]+/tasks/$`, 'i'),
		],
		options: {
			contentClassName: 'bitrix24-group-slider-content',
			loader: 'intranet:slider-projects-tasklist',
			cacheable: false,
			customLeftBoundary: 0,
			newWindowLabel: true,
			copyLinkLabel: true,
		},
	},
	{
		condition: [
			new RegExp(`${siteDir}workgroups/group/[0-9]+/\\?scrum=Y$`, 'i'),
			new RegExp(`${siteDir}workgroups/group/[0-9]+/tasks/\\?scrum=Y$`, 'i'),
		],
		options: {
			contentClassName: 'bitrix24-group-slider-content',
			loader: 'intranet:slider-scrum',
			cacheable: false,
			customLeftBoundary: 0,
			newWindowLabel: true,
			copyLinkLabel: true,
		},
	},
);

rules.push(
	{
		condition: [
			'(?<url>/spaces/group/(\\d+)/tasks/task/view/(?<taskId>\\d+)/)',
			'(?<url>/spaces/group/(\\d+)/tasks/task/edit/(?<taskId>\\d+)/)',
		],
		minimizeOptions: (link) => {
			return {
				entityType: 'tasks:task',
				entityId: link.matches.groups.taskId,
				entityName: BX.message('INTRANET_BINDINGS_TASK'),
				url: link.matches.groups.url,
			};
		},
		loader: 'intranet:task-detail',
		options: {
			label: {
				text: BX.message('INTRANET_BINDINGS_TASK'),
				bgColor: '#2FC6F6',
			},
		},
	},
);

BX.SidePanel.Instance.bindAnchors({
	rules: rules,
});
})();
