import { ajax, Event, Type, Loc, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';

class PinnedPanel
{
	constructor()
	{
		this.panelInitialized = false;
		this.postsInitialized = false;
		this.handlePostClick = this.handlePostClick.bind(this);

		this.init();
	}

	resetFlags()
	{
		this.panelInitialized = false;
		this.postsInitialized = false;
	}

	init()
	{
		this.initPanel();
		this.initPosts();
		this.initEvents();
		EventEmitter.subscribe('onFrameDataProcessed', () => {
			this.initPanel();
			this.initPosts();
		});
	}

	initPanel()
	{
		if (this.panelInitialized)
		{
			return;
		}

		const pinnedPanelNode = this.getPanelNode();
		if (!pinnedPanelNode)
		{
			return;
		}

		this.panelInitialized = true;

		Event.bind(pinnedPanelNode, 'click', (event) => {
			const likeClicked = event.target.classList.contains('feed-inform-ilike') || event.target.closest('.feed-inform-ilike') !== null;
			const followClicked = event.target.classList.contains('feed-inform-follow') || event.target.closest('.feed-inform-follow') !== null;
			const menuClicked = (
				event.target.classList.contains('feed-post-more-link')
				|| event.target.closest('.feed-post-more-link') !== null
				|| event.target.classList.contains('feed-post-right-top-menu')
			);
			const contentViewClicked = event.target.classList.contains('feed-inform-contentview') || event.target.closest('.feed-inform-contentview') !== null;
			const pinClicked = event.target.classList.contains('feed-post-pin') || event.target.closest('.feed-post-pin') !== null;
			const collapseClicked = event.target.classList.contains('feed-post-pinned-link-collapse');
			const commentsClicked = event.target.classList.contains('feed-inform-comments-pinned') || event.target.closest('.feed-inform-comments-pinned') !== null;

			let postNode = null;

			if (event.target.classList.contains('feed-post-block'))
			{
				postNode = event.target;
			}
			else
			{
				postNode = event.target.closest('.feed-post-block');
			}

			if (!postNode)
			{
				return;
			}

			if (postNode.classList.contains('feed-post-block-pinned'))
			{
				if (
					!likeClicked
					&& !followClicked
					&& !menuClicked
					&& !contentViewClicked
					&& !pinClicked
				)
				{
					postNode.classList.remove('feed-post-block-pinned');

					const menuId = postNode.getAttribute('data-menu-id');

					if (menuId)
					{
						MenuManager.destroy(menuId);
					}

					const event = new BaseEvent({
						compatData: [{
							rootNode: postNode
						}],
						data: {
							rootNode: postNode
						},
					});
					EventEmitter.emit('BX.Livefeed:recalculateComments', event);
				}

				if (commentsClicked)
				{
					const anchorNode = postNode.querySelector('.feed-comments-block a[name=comments]');

					if (anchorNode)
					{
						const position = Dom.getPosition(anchorNode);
						window.scrollTo(0, position.top - 200);
					}
				}

				event.stopPropagation();
				event.preventDefault();
			}
			else if (collapseClicked)
			{
				postNode.classList.add('feed-post-block-pinned');

				event.stopPropagation();
				event.preventDefault();
			}
		});
	}

	initPosts()
	{
		if (this.postsInitialized)
		{
			return;
		}

		const postList = document.querySelectorAll('[data-livefeed-post-pinned]');
		if (postList.length > 0)
		{
			this.postsInitialized = true;
		}

		Array.from(postList).forEach((post) => {
			Event.unbind(post, 'click', this.handlePostClick);
			Event.bind(post, 'click', this.handlePostClick);
		});
	}

	handlePostClick(event)
	{
		if (!event.target.classList.contains('feed-post-pin'))
		{
			return
		}

		const post = event.target.closest('[data-livefeed-id]');

		if (!post)
		{
			return;
		}

		const newState = (post.getAttribute('data-livefeed-post-pinned') === 'Y' ? 'N' : 'Y');
		const logId = parseInt(post.getAttribute('data-livefeed-id'));

		if (logId <= 0)
		{
			return;
		}

		this.changePinned({
			logId: logId,
			newState: newState,
			event: event
		});
	}

	initEvents()
	{
		EventEmitter.subscribe('OnUCCommentWasRead', (event: BaseEvent) =>
		{
			const [ xmlId, id, options ] = event.getData();
			const { oldValue, newValue } = this.getCommentsData(xmlId);

			if (!!options.new)
			{
				this.setCommentsData(xmlId, {
					newValue: (Type.isInteger(newValue) ? (newValue - 1) : 0),
					oldValue: (Type.isInteger(oldValue) ? (oldValue + 1) : 1)
				});
			}
		});

		EventEmitter.subscribe('OnUCCommentWasPulled', (event: BaseEvent) =>
		{
			const [ id, data, params ] = event.getData();
			const [ xmlId, commentId ] = id;
			const { newValue, oldValue, follow } = this.getCommentsData(xmlId);

			if (
				parseInt(params.AUTHOR.ID) !== parseInt(BX.message('USER_ID'))
				&& follow
			)
			{
				this.setCommentsData(xmlId, {newValue: (Type.isInteger(newValue) ? (newValue + 1) : 1)});
			}
			else
			{
				this.setCommentsData(xmlId, {oldValue: (Type.isInteger(oldValue) ? (oldValue + 1) : 1)});
			}
		});

		EventEmitter.subscribe('OnUCommentWasDeleted', (event: BaseEvent) =>
		{
			const [ xmlId, id, data ] = event.getData();
			const { oldValue } = this.getCommentsData(xmlId);

			this.setCommentsData(xmlId, {oldValue: (Type.isInteger(oldValue) ? (oldValue - 1) : 0)});
		});
	}

	changePinned(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		const node = (params.node ? params.node : null);
		const event = (params.event ? params.event : null);

		let newState = (params.newState ? params.newState : null);

		if (
			!logId
			|| !newState
		)
		{
			return;
		}

		this.setPostState({
			node: (node ? node : event.target),
			state: newState
		});

		ajax.runAction('socialnetwork.api.livefeed.logentry.' + (newState === 'Y' ? 'pin' : 'unpin'), {
			data: {
				params: {
					logId: logId
				}
			},
			analyticsLabel: {
				b24statAction: (newState === 'Y' ? 'pinLivefeedEntry' : 'unpinLivefeedEntry')
			}
		}).then(response => {
			if (!response.data.success)
			{
				this.setPostState({
					node: (node ? node : event.target),
					state: (newState === 'Y' ? 'N' : 'Y')
				});
			}
			else
			{
				this.movePost({
					node: (node ? node : event.target),
					state: newState
				});
			}
		}, response => {
			this.setPostState({
				node: (node ? node : event.target),
				state: (newState === 'Y' ? 'N' : 'Y')
			});
		});
	}

	setPostState(params)
	{
		const state = (params.state ? params.state : null);
		const node = (params.node ? params.node : null);

		if (
			!node
			|| !['Y', 'N'].includes(state)
		)
		{
			return;
		}

		const post = node.closest('[data-livefeed-post-pinned]');
		if (!post)
		{
			return;
		}

		post.setAttribute('data-livefeed-post-pinned', state);

		post.classList.remove('feed-post-block-pin-active');
		post.classList.remove('feed-post-block-pin-inactive');

		if (state === 'Y')
		{
			post.classList.add('feed-post-block-pin-active', state);
		}
		else
		{
			post.classList.add('feed-post-block-pin-inactive', state);
		}
	}

	getPanelNode()
	{
		return document.querySelector('[data-livefeed-pinned-panel]');
	}

	getPinnedData(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);

		if (logId <= 0)
		{
			return Promise.reject();
		}

		return new Promise((resolve, reject) => {
			ajax.runAction('socialnetwork.api.livefeed.logentry.getPinData', {
				data: {
					params: {
						logId: logId
					}
				}
			}).then(response => {
				resolve(response.data);
			}, response => {
				reject();
			});
		})
	}

	movePost(params)
	{
		const state = (params.state ? params.state : null);
		const node = (params.node ? params.node : null);

		if (
			!node
			|| !['Y', 'N'].includes(state)
		)
		{
			return;
		}

		const post = node.closest('[data-livefeed-post-pinned]');
		if (!post)
		{
			return;
		}

		const logId = parseInt(post.getAttribute('data-livefeed-id'));
		if (!logId)
		{
			return;
		}

		const pinnedPanelNode = this.getPanelNode();
		if (!pinnedPanelNode)
		{
			return;
		}

		const postToMove = (post.parentNode.classList.contains('feed-item-wrap') ? post.parentNode : post);

		if (state === 'Y')
		{
			this.getPinnedData({
				logId: logId
			}).then(data => {

				const pinnedPanelTitleNode = post.querySelector('.feed-post-pinned-title');
				const pinnedPanelDescriptionNode = post.querySelector('.feed-post-pinned-desc');
				const pinnedPanelPinNode = post.querySelector('.feed-post-pin');

				if (pinnedPanelTitleNode)
				{
					pinnedPanelTitleNode.innerHTML = data.TITLE;
				}
				if (pinnedPanelDescriptionNode)
				{
					pinnedPanelDescriptionNode.innerHTML = data.DESCRIPTION;
				}
				if (pinnedPanelPinNode)
				{
					pinnedPanelPinNode.title = Loc.getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_Y');
				}

				post.classList.add('feed-post-block-pinned');
				pinnedPanelNode.insertBefore(postToMove, pinnedPanelNode.firstChild);
			});
		}
		else
		{
			post.classList.remove('feed-post-block-pinned');
			pinnedPanelNode.parentNode.insertBefore(postToMove, pinnedPanelNode.nextSibling);
		}
	}

	getCommentsNodes(xmlId)
	{
		const result = {
			follow: true,
			newNode: null,
			oldNode: null
		};

		if (!Type.isStringFilled(xmlId))
		{
			return result;
		}

		const commentsNode = document.querySelector(`.feed-comments-block[data-bx-comments-entity-xml-id="${xmlId}"]`);
		if (!commentsNode)
		{
			return result;
		}

		const postNode = commentsNode.closest('.feed-post-block-pin-active');
		if (!postNode)
		{
			return result;
		}

		const newPinnedCommentsNode = postNode.querySelector('.feed-inform-comments-pinned-new');
		const oldPinnedCommentsNode = postNode.querySelector('.feed-inform-comments-pinned-old');

		if (
			!newPinnedCommentsNode
			|| !oldPinnedCommentsNode
		)
		{
			return result;
		}

		result.newNode = newPinnedCommentsNode;
		result.oldNode = oldPinnedCommentsNode;
		result.follow = (commentsNode.getAttribute('data-bx-follow') !== 'N');

		return result;
	}

	getCommentsData(xmlId)
	{
		const result = {
			newValue: null,
			oldValue: null
		};

		if (!Type.isStringFilled(xmlId))
		{
			return result;
		}

		const { newNode, oldNode, follow } = this.getCommentsNodes(xmlId);

		result.follow = follow;

		if (
			!Type.isDomNode(newNode)
			|| !Type.isDomNode(oldNode)
		)
		{
			return result;
		}

		let newCommentsValue = 0;
		let oldCommentsValue = 0;
		let matches = newNode.innerHTML.match(/\+(\d+)/);

		if (matches)
		{
			newCommentsValue = parseInt(matches[1]);
		}

		matches = oldNode.innerHTML.match(/(\d+)/);
		if (matches)
		{
			oldCommentsValue = parseInt(matches[1]);
		}

		result.oldValue = oldCommentsValue;
		result.newValue = newCommentsValue;

		return result;
	}

	setCommentsData(xmlId, value)
	{
		if (!Type.isStringFilled(xmlId))
		{
			return;
		}

		const { newNode, oldNode } = this.getCommentsNodes(xmlId);
		if (
			!Type.isDomNode(newNode)
			|| !Type.isDomNode(oldNode)
		)
		{
			return;
		}

		if (Type.isInteger(value.newValue))
		{
			newNode.innerHTML = `+${value.newValue}`;
			if (
				value.newValue > 0
				&& !newNode.classList.contains('feed-inform-comments-pinned-new-active')
			)
			{
				newNode.classList.add('feed-inform-comments-pinned-new-active');
			}
			else if (
				value.newValue <= 0
				&& newNode.classList.contains('feed-inform-comments-pinned-new-active')
			)
			{
				newNode.classList.remove('feed-inform-comments-pinned-new-active');
			}
		}

		if (Type.isInteger(value.oldValue))
		{
			oldNode.innerHTML = value.oldValue;
		}
	}
}

export {
	PinnedPanel
};