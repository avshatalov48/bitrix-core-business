import { Loc, Type, ajax, Dom, Runtime, Uri, Event, Tag } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import {FeedInstance, PinnedPanelInstance, InformerInstance} from './feed';
import {Loader} from './loader';
import {MoreButton} from './morebutton';
import {ContentView} from './contentview';

class Page
{
	constructor()
	{
		this.loadStarted = null;
		this.stopTrackNextPage = null;
		this.requestMode = null;
		this.nextPageFirst = null;
		this.nextPageUrl = null;
		this.scrollInitialized = null;
		this.firstPageLastTS = 0;
		this.firstPageLastId = 0;
		this.useBXMainFilter = 'N';
		this.commentFormUID = '';
		this.blogCommentFormUID = '';

		this.signedParameters = '';
		this.componentName = '';

		this.class = {
		};

		Event.ready(() => {
			this.init();
		});
	}

	init()
	{
		this.loadStarted = false;
		this.stopTrackNextPage = false;
		this.requestMode = false;
		this.nextPageFirst = true;
		this.nextPageUrl = false;
		this.scrollInitialized = false;
	}

	refresh(params, filterPromise)
	{
		if (this.loadStarted)
		{
			return;
		}

		this.setRequestModeNew();

		params = (
			Type.isPlainObject(params)
				? params
				: {}
		);

		params.siteTemplateId = Loc.getMessage('SONET_EXT_LIVEFEED_SITE_TEMPLATE_ID');
		params.assetsCheckSum = Loc.getMessage('sonetLAssetsCheckSum');

		this.loadStarted = true;

		Loader.showRefreshFade();

		MoreButton.clearCommentsList();
		FeedInstance.clearMoreButtons();

		if (Type.isStringFilled(this.commentFormUID))
		{
			params.commentFormUID = this.commentFormUID;
		}

		if (
			!Type.isStringFilled(params.useBXMainFilter)
			|| params.useBXMainFilter !== 'Y'
		)
		{
			EventEmitter.emit('BX.Livefeed:refresh', new BaseEvent({
				compatData: [],
			}));
		}

		InformerInstance.hideReloadNode();
		InformerInstance.lockCounterAnimation = true;

		this.loadStarted = false;

		ajax.runAction('socialnetwork.api.livefeed.refresh', {
			signedParameters: this.getSignedParameters(),
			data: {
				c: this.getComponentName(),
				logajax: 'Y', // compatibility
				RELOAD: 'Y', // compatibility
				params: params,
			}
		}).then((response) => {

			const responseData = (Type.isPlainObject(response.data) ? response.data : {});

			this.loadStarted = false;
			Loader.hideRefreshFade();

			if (filterPromise)
			{
				filterPromise.fulfill();
			}

			const emptyLivefeed = (
				Type.isPlainObject(responseData.componentResult)
				&& Type.isStringFilled(responseData.componentResult.EMPTY)
					? responseData.componentResult.EMPTY
					: 'N'
			);
			const forcePageRefresh = (
				Type.isPlainObject(responseData.componentResult)
				&& Type.isStringFilled(responseData.componentResult.FORCE_PAGE_REFRESH)
					? responseData.componentResult.FORCE_PAGE_REFRESH
					: 'N'
			);

			const isFilterUsed = (
				Type.isPlainObject(responseData.componentResult)
				&& Type.isStringFilled(responseData.componentResult.FILTER_USED)
				&& responseData.componentResult.FILTER_USED === 'Y'
			);

			if (forcePageRefresh === 'Y')
			{
				top.window.location.reload();
				return;
			}

			const loaderContainer = document.getElementById('feed-loader-container');

			InformerInstance.lockCounterAnimation = false;

			const feedContainer = document.getElementById('log_internal_container');
			if (!feedContainer)
			{
				return;
			}

			Dom.clean(feedContainer);

			const emptyBlock = document.getElementById('feed-empty-wrap');

			if (emptyBlock)
			{
				if (emptyLivefeed === 'Y')
				{
					emptyBlock.style.display = 'block';

					const emptyTextNode = emptyBlock.querySelector('.feed-wrap-empty');
					if (emptyTextNode)
					{
						emptyTextNode.innerHTML = (
							isFilterUsed
								? Loc.getMessage('SONET_C30_T_EMPTY_SEARCH')
								: Loc.getMessage('SONET_C30_T_EMPTY')
						);
					}
				}
				else
				{
					emptyBlock.style.display = 'none';
				}
			}

			if (loaderContainer)
			{
				feedContainer.appendChild(loaderContainer);
			}

			if (responseData.html.length > 0)
			{
				this.clearContainerExternal();
				BX.LazyLoad.clearImages();

				const pageNode = Tag.render`<div id="content_block_${(Math.floor(Math.random() * 1000))}" class="feed-wrap" style="display: block;"></div>`;

				feedContainer.appendChild(pageNode);

				Runtime.html(pageNode, responseData.html).then(() => {
					MoreButton.recalcPostsList()
					MoreButton.recalcCommentsList();

					ContentView.registerAreaList();

					PinnedPanelInstance.resetFlags();
					PinnedPanelInstance.initPanel();
					PinnedPanelInstance.initPosts();
				});

				this.stopTrackNextPage = false;

				MoreButton.clearCommentsList();

				const informerWrap = InformerInstance.getWrap();

				if (
					informerWrap
					&& informerWrap.classList.contains(InformerInstance.class.informerFixed)
				)
				{
					(new BX.easing({
						duration: 500,
						start: { scroll: window.pageYOffset },
						finish: { scroll: 0 },
						transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
						step: (state) => {
							window.scrollTo(0, state.scroll);
						},
						complete: () => {
							EventEmitter.emit('onGoUp', []);
						}
					})).animate();
				}
			}
		}, () => {

			this.loadStarted = false;
			if (filterPromise)
			{
				filterPromise.reject();
			}

			Loader.hideRefreshFade();
			this.showRefreshError();
		});

		return false;
	}

	getNextPage()
	{
		const stubContainer = document.getElementById('feed-new-message-inf-wrap');
		const stubFirstContainer = document.getElementById('feed-new-message-inf-wrap-first');

		if (this.loadStarted)
		{
			return false;
		}

		this.setRequestModeMore();

		this.loadStarted = true;

		InformerInstance.lockCounterAnimation = true;
		FeedInstance.clearMoreButtons();

		if (
			!this.nextPageFirst
			&& stubContainer
		)
		{
			stubContainer.style.display = 'block';
		}
		else if (
			this.nextPageFirst
			&& stubFirstContainer
		)
		{
			stubFirstContainer.classList.add('feed-new-message-inf-wrap-first-visible');
		}

		const nextUrlParamsList = (new Uri(this.getNextPageUrl())).getQueryParams();
		let pageNumber = 1;
		let prevPageLogId = '';
		let ts = 0;
		let noblog = 'N';

		Object.entries(nextUrlParamsList).forEach(([ key, value ]) => {
			if (key.match(/^PAGEN_(\d+)$/i))
			{
				pageNumber = parseInt(value);
			}
			else if (key === 'pplogid')
			{
				prevPageLogId = decodeURI(value);
			}
			else if (key === 'ts')
			{
				ts = value;
			}
			else if (key === 'noblog')
			{
				noblog = value;
			}
		});

		const queryParams = {
			PAGE_NUMBER: pageNumber,
			LAST_LOG_TIMESTAMP: ts,
			PREV_PAGE_LOG_ID: prevPageLogId,
			siteTemplateId: Loc.getMessage('SONET_EXT_LIVEFEED_SITE_TEMPLATE_ID'),
			useBXMainFilter: this.useBXMainFilter,
			preset_filter_top_id: (Type.isStringFilled(nextUrlParamsList.preset_filter_top_id) && nextUrlParamsList.preset_filter_top_id !== '0' ? nextUrlParamsList.preset_filter_top_id : ''),
			preset_filter_id: (Type.isStringFilled(nextUrlParamsList.preset_filter_id) && nextUrlParamsList.preset_filter_id !== '0' ? nextUrlParamsList.preset_filter_id : '')
		};

		if (Type.isStringFilled(this.commentFormUID))
		{
			queryParams.commentFormUID = this.commentFormUID;
		}

		if (Type.isStringFilled(this.blogCommentFormUID))
		{
			queryParams.blogCommentFormUID = this.blogCommentFormUID;
		}

		const queryData = {
			c: this.getComponentName(),
			logajax: 'Y', // compatibility with socialnetwork.blog.post.comment
			noblog: noblog, // compatibility with socialnetwork.blog.post.comment
			params: queryParams,
		};

		if (!Type.isUndefined(nextUrlParamsList.CREATED_BY_ID))
		{
			queryData.flt_created_by_id = parseInt(nextUrlParamsList.CREATED_BY_ID);
		}

		if (!Type.isUndefined(nextUrlParamsList.flt_date_datesel))
		{
			queryData.flt_date_datesel = nextUrlParamsList.flt_date_datesel;
		}

		if (!Type.isUndefined(nextUrlParamsList.flt_date_from))
		{
			queryData.flt_date_from = decodeURIComponent(nextUrlParamsList.flt_date_from);
		}

		if (!Type.isUndefined(nextUrlParamsList.flt_date_to))
		{
			queryData.flt_date_to = decodeURIComponent(nextUrlParamsList.flt_date_to);
		}

		ajax.runAction('socialnetwork.api.livefeed.getNextPage', {
			signedParameters: this.getSignedParameters(),
			data: queryData,
		}).then((response) => {
			const responseData = (Type.isPlainObject(response.data) ? response.data : {});

			this.loadStarted = false;

			const stubContainer = document.getElementById('feed-new-message-inf-wrap');
			if (stubContainer)
			{
				Dom.clean(stubContainer);
				Dom.remove(stubContainer)
			}

			InformerInstance.lockCounterAnimation = false;

			const lastEntryTimestamp = (
				Type.isPlainObject(responseData.componentResult)
				&& !Type.isUndefined(responseData.componentResult.LAST_TS)
					? parseInt(responseData.componentResult.LAST_TS)
					: 0
			);
			const lastEntryId = (
				Type.isPlainObject(responseData.componentResult)
				&& !Type.isUndefined(responseData.componentResult.LAST_ID)
					? parseInt(responseData.componentResult.LAST_ID)
					: null
			);

			if (
				responseData.html.length > 0
				&& lastEntryTimestamp > 0
				&& (
					parseInt(this.firstPageLastTS) <= 0
					|| lastEntryTimestamp < parseInt(this.firstPageLastTS)
					|| (
						lastEntryTimestamp == parseInt(this.firstPageLastTS)
						&& !Type.isNull(lastEntryId)
						&& lastEntryId < parseInt(this.firstPageLastId)
					)
				)
			)
			{
				MoreButton.clearCommentsList();

				const contentBlockId = `content_block_${(Math.floor(Math.random() * 1000))}`;

				const pageNode = Tag.render`<div id="${contentBlockId}" class="feed-wrap" style="display:${(this.nextPageFirst ? 'none' : 'block')};"></div>`;

				const feedContainer = document.getElementById('log_internal_container');
				if (!feedContainer)
				{
					return;
				}

				feedContainer.appendChild(pageNode);

				Runtime.html(pageNode, responseData.html).then(() => {
					if (pageNumber > 2)
					{
						this.stopTrackNextPage = false;
						MoreButton.recalcPostsList();
						ContentView.registerAreaList();
						MoreButton.recalcCommentsList();
						PinnedPanelInstance.resetFlags();
						PinnedPanelInstance.initPosts();
					}
				});

				this.clearContainerExternal();

				if (pageNumber === 2)
				{
					document.getElementById('feed-new-message-inf-text-first').style.display = 'block';
					document.getElementById('feed-new-message-inf-loader-first').style.display = 'none';
					stubFirstContainer.classList.add('feed-new-message-inf-wrap-first-active');

					const f = () => {

						this.stopTrackNextPage = false;
						if (pageNode)
						{
							pageNode.style.display = 'block';
						}

						Event.unbind(document.getElementById('sonet_log_more_container_first'), 'click', f);
						stubFirstContainer.style.display = 'none';
						MoreButton.recalcPostsList()
						ContentView.registerAreaList();
						MoreButton.recalcCommentsList();

						EventEmitter.emit('BX.Livefeed:recalculateComments', new BaseEvent({
							compatData: [{
								rootNode: pageNode,
							}],
						}));


						PinnedPanelInstance.resetFlags();
						PinnedPanelInstance.initPosts();
					};
					Event.bind(document.getElementById('sonet_log_more_container_first'), 'click', f);
				}
				else
				{
					if (pageNode)
					{
						pageNode.style.display = 'block';
					}
				}

				this.nextPageFirst = false;
			}

			else if (document.getElementById('feed-new-message-inf-wrap-first'))
			{
				document.getElementById('feed-new-message-inf-wrap-first').style.display = 'none';
			}
		}, () => {

			this.loadStarted = false;
			this.stopTrackNextPage = false;

			const stubContainer = document.getElementById('feed-new-message-inf-wrap');
			if (stubContainer)
			{
				stubContainer.style.display = 'none';
			}

			InformerInstance.lockCounterAnimation = false;
			this.clearContainerExternal();
		});

		return false;
	}

	clearContainerExternal()
	{
		if (this.requestMode === 'new')
		{
			InformerInstance.hideWrapAnimation();
			InformerInstance.recover();
		}

		InformerInstance.hideReloadAnimation();

		const counterPreset = document.getElementById('sonet_log_counter_preset');
		if (
			counterPreset
			&& this.requestMode === 'new'
		)
		{
			counterPreset.style.display = 'none';
		}
	}

	setRequestModeNew()
	{
		this.requestMode = 'new';
	}

	setRequestModeMore()
	{
		this.requestMode = 'more';
	}

	showRefreshError()
	{
		InformerInstance.lockCounterAnimation = false;
		this.clearContainerExternal();
	}
	setSignedParameters(value)
	{
		this.signedParameters = value;
	}
	getSignedParameters()
	{
		return this.signedParameters;
	}

	setComponentName(value)
	{
		this.componentName = value;
	}
	getComponentName()
	{
		return this.componentName;
	}

	setNextPageUrl(value)
	{
		this.nextPageUrl = value;
	}
	getNextPageUrl()
	{
		return this.nextPageUrl;
	}

	initScroll()
	{
		if (this.scrollInitialized)
		{
			return;
		}

		this.scrollInitialized = true;
		document.addEventListener('scroll', this.onFeedScroll.bind(this));
	}

	onFeedScroll()
	{
		if (!this.stopTrackNextPage)
		{
			const maxScroll = (document.documentElement.scrollHeight - window.innerHeight) - 500;
			if (
				window.pageYOffset >= maxScroll
				&& this.getNextPageUrl()
			)
			{
				this.stopTrackNextPage = true;
				this.getNextPage();
			}
		}

		InformerInstance.onFeedScroll();
	}
}

export {
	Page
};
