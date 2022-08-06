import { ajax, Event, Type, Loc, Dom, Tag } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager } from 'main.popup';
import { Utils } from "./utils";

class PinnedPanel
{
	constructor()
	{
		this.class = {
			pin: 'feed-post-pin',

			post: 'feed-item-wrap',
			postHide: 'feed-item-wrap-hide',
			postComments: 'feed-comments-block',

			postPinned: 'feed-post-block-pinned',
			postPinnedHide: 'feed-post-block-pinned-hide',
			postPinActive: 'feed-post-block-pin-active',
			postUnfollowed: 'feed-post-block-unfollowed',

			postExpanding: 'feed-post-block-expand',

			panelCollapsed: 'feed-pinned-panel-collapsed',
			panelNonEmpty: 'feed-pinned-panel-nonempty',
			panelPosts: 'feed-pinned-panel-posts',

			collapsedPanel: 'feed-post-collapsed-panel',
			collapsedPanelExpand: 'feed-post-collapsed-panel-right',
			collapsedPanelCounterPostsValue: 'feed-post-collapsed-panel-count-posts',
			collapsedPanelCounterComments: 'feed-post-collapsed-panel-box-comments',
			collapsedPanelCounterCommentsValue: 'feed-post-collapsed-panel-count-comments-value',
			collapsedPanelCounterCommentsShown: 'feed-post-collapsed-panel-box-shown',
			collapsedPanelCounterCommentsValueNew: 'feed-inform-comments-pinned-new',
			collapsedPanelCounterCommentsValueNewValue: 'feed-inform-comments-pinned-new-value',
			collapsedPanelCounterCommentsValueNewActive: 'feed-inform-comments-pinned-new-active',
			collapsedPanelCounterCommentsValueOld: 'feed-inform-comments-pinned-old',
			collapsedPanelCounterCommentsValueAll: 'feed-inform-comments-pinned-all',
			collapsedPanelShow: 'feed-post-collapsed-panel--show',
			collapsedPanelHide: 'feed-post-collapsed-panel--hide',

			cancelPanel: 'feed-post-cancel-pinned-panel',
			cancelPanelButton: 'feed-post-cancel-pinned-btn',
			cancelPanelLabel: 'feed-post-cancel-pinned-label'
		};

		this.panelInitialized = false;
		this.postsInitialized = false;
		this.handlePostClick = this.handlePostClick.bind(this);
		this.options = {};

		Event.ready(() => {
			/* for detail page without pinned panel */
			this.initPosts();
		});
	}

	resetFlags()
	{
		this.panelInitialized = false;
		this.postsInitialized = false;
	}

	init()
	{
		/* for list page in composite mode */
		this.initPanel();

		this.initPosts();
		this.initEvents();
	}

	setOptions(options)
	{
		this.options = { ...this.options, ...options };
	}

	getOption(optionName)
	{
		return this.options[optionName];
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

		this.adjustCollapsedPostsPanel();

		Event.bind(this.getCollapsedPanelNode(), 'click', () => {
			const pinnedPanelNode = this.getPanelNode();
			if (!pinnedPanelNode)
			{
				return;
			}

			const collapsedHeight = pinnedPanelNode.offsetHeight;

			Utils.setStyle(pinnedPanelNode, {
				height: collapsedHeight + 'px',
				transition: 'height .5s'
			});
			setTimeout(() => {
				pinnedPanelNode.style = '';
			}, 550);
			this.hideCollapsedPanel();
		});

		Event.bind(pinnedPanelNode, 'click', (event) => {
			const likeClicked = event.target.classList.contains('feed-inform-ilike') || event.target.closest('.feed-inform-ilike') !== null;
			const followClicked = event.target.classList.contains('feed-inform-follow') || event.target.closest('.feed-inform-follow') !== null;
			const menuClicked = (
				event.target.classList.contains('feed-post-more-link')
				|| event.target.closest('.feed-post-more-link') !== null
				|| event.target.classList.contains('feed-post-right-top-menu')
			);
			const contentViewClicked = event.target.classList.contains('feed-inform-contentview') || event.target.closest('.feed-inform-contentview') !== null;
			const pinClicked = event.target.classList.contains(`${this.class.pin}`) || event.target.closest(`.${this.class.pin}`) !== null;
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

			if (postNode.classList.contains(`${this.class.postPinned}`))
			{
				if (
					!likeClicked
					&& !followClicked
					&& !menuClicked
					&& !contentViewClicked
					&& !pinClicked
				)
				{
					postNode.classList.remove(`${this.class.postPinned}`);

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
					const anchorNode = postNode.querySelector(`.${this.class.postComments} a[name=comments]`);

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
				postNode.classList.add(`${this.class.postPinned}`);

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
		if (!event.target.classList.contains(`${this.class.pin}`))
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
				}).then(() => {
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

		EventEmitter.incrementMaxListeners('OnUCCommentWasPulled');
		EventEmitter.subscribe('OnUCCommentWasPulled', (event: BaseEvent) =>
		{
			const [ id, data, params ] = event.getData();
			const [ xmlId, commentId ] = id;
			const { newValue, oldValue, allValue, follow } = this.getCommentsData(xmlId);

			const commentsData = {
				allValue: (Type.isInteger(allValue) ? (allValue + 1) : 1)
			};

			if (parseInt(params.AUTHOR.ID) !== parseInt(BX.message('USER_ID')))
			{
				commentsData.newValue = (Type.isInteger(newValue) ? (newValue + 1) : 1);
			}
			else
			{
				commentsData.oldValue = (Type.isInteger(oldValue) ? (oldValue + 1) : 1);
			}

			this.setCommentsData(xmlId, commentsData);
		});

		EventEmitter.subscribe('OnUCommentWasDeleted', (event: BaseEvent) =>
		{
			const [ xmlId, id, data ] = event.getData();
			const { oldValue, allValue } = this.getCommentsData(xmlId);

			this.setCommentsData(xmlId, {
				allValue: (Type.isInteger(allValue) ? (allValue - 1) : 0),
				oldValue: (Type.isInteger(oldValue) ? (oldValue - 1) : 0)
			});
		});
	}

	changePinned(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		const event = (params.event ? params.event : null);

		let node = (params.node ? params.node : null);
		let newState = (params.newState ? params.newState : null);

		const panelNode = this.getPanelNode();

		if (
			!node
			&& !event
			&& logId > 0
			&& panelNode
		)
		{
			node = panelNode.querySelector(`.${this.class.post} > [data-livefeed-id="${logId}"]`);
		}

		if (
			!node
			&& event
		)
		{
			node = event.target;
		}

		return new Promise((resolve, reject) => {

			if (
				!!this.getOption('pinBlocked') ||
				!node
				|| !newState
			)
			{
				return resolve();
			}

			this.setPostState({
				node: node,
				state: newState
			});

			const action = (
				newState === 'Y'
					? 'socialnetwork.api.livefeed.logentry.pin'
					: 'socialnetwork.api.livefeed.logentry.unpin'
			);

			ajax.runAction(action, {
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
						node: node,
						state: (newState === 'Y' ? 'N' : 'Y')
					});
					return resolve();
				}
				else
				{
					this.movePost({
						node: node,
						state: newState
					}).then(() => {
						return resolve();
					});
				}
			}, response => {
				this.setPostState({
					node: node,
					state: (newState === 'Y' ? 'N' : 'Y')
				});
				return resolve();
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

		if (state === 'Y')
		{
			post.classList.add(`${this.class.postPinActive}`);
		}
		else
		{
			post.classList.remove(`${this.class.postPinActive}`);
		}

		const pin = post.querySelector(`.${this.class.pin}`);
		if (pin)
		{
			pin.setAttribute('title', Loc.getMessage(`SONET_EXT_LIVEFEED_PIN_TITLE_${state}`));
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
						logId: logId,
					},
				},
				headers: [
					{
						name: Loc.getMessage('SONET_EXT_LIVEFEED_AJAX_ENTITY_HEADER_NAME'),
						value: params.entityValue || '',
					},
					{
						name: Loc.getMessage('SONET_EXT_LIVEFEED_AJAX_TOKEN_HEADER_NAME'),
						value: params.tokenValue || '',
					}
				],
			}).then(response => {
				return resolve(response.data);
			}, response => {
				return reject();
			});
		})
	}

	movePost(params)
	{
		const state = (params.state ? params.state : null);
		const node = (params.node ? params.node : null);

		return new Promise((resolve, reject) => {

			if (
				!node
				|| !['Y', 'N'].includes(state)
			)
			{
				return resolve();
			}

			const post = node.closest('[data-livefeed-post-pinned]');
			if (!post)
			{
				return resolve();
			}

			const logId = parseInt(post.getAttribute('data-livefeed-id'));
			if (!logId)
			{
				return resolve();
			}

			const pinnedPanelNode = this.getPanelNode();
			if (!pinnedPanelNode)
			{
				return resolve();
			}

			const postToMove = (post.parentNode.classList.contains(`${this.class.post}`) ? post.parentNode : post);

			const entityValue = post.getAttribute('data-security-entity-pin');
			const tokenValue = post.getAttribute('data-security-token-pin');

			if (state === 'Y')
			{
				const originalPostHeight = postToMove.offsetHeight;
				postToMove.setAttribute('bx-data-height', originalPostHeight);

				this.getPinnedData({
					logId: logId,
					entityValue: entityValue,
					tokenValue: tokenValue,
				}).then(data => {
					const pinnedPanelTitleNode = post.querySelector('.feed-post-pinned-title');
					const pinnedPanelDescriptionNode = post.querySelector('.feed-post-pinned-desc');
					const pinnedPanelPinNode = post.querySelector(`.${this.class.pin}`);

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

					post.classList.add(`${this.class.postPinnedHide}`);

					const cancelPinnedPanel = this.getCancelPinnedPanel({ logId });
					const anchor = postToMove.nextSibling;

					anchor.parentNode.insertBefore(cancelPinnedPanel, anchor);
					this.centerCancelPinnedPanelElements({ cancelPinnedPanel });

					cancelPinnedPanel.setAttribute('bx-data-height', originalPostHeight);
					const cancelPanelHeight = cancelPinnedPanel.getAttribute('bx-data-height');

					Utils.setStyle(cancelPinnedPanel, {
						height: cancelPanelHeight + 'px'
					});

					Utils.setStyle(postToMove, {
						position: 'absolute',
						width: '100%',
						height: originalPostHeight + 'px',
						backgroundColor: 'transparent',
						opacity: 0
					});

					const panelNode = this.getPanelNode();
					if (panelNode)
					{
						this.setOptions({
							panelHeight: panelNode.offsetHeight
						});
					}

					// list.post::hide.start, cancelPanel::show.start
					setTimeout(() => {
						postToMove.classList.add(`${this.class.postHide}`);
						Utils.setStyle(cancelPinnedPanel, {
							height: '53px'
						});
						Utils.setStyle(postToMove, {
							height: 0,
							opacity: 0
						});
						this.setOptions({
							pinBlocked: true
						});
					}, 100);

					// list.post::hide.end
					Event.unbindAll(postToMove, 'transitionend');
					Event.bind(postToMove, 'transitionend', (event) => {
						if (!this.checkTransitionProperty(event, 'height'))
						{
							return;
						}

						Event.unbindAll(postToMove, 'transitionend');

						const panelPostsNode = pinnedPanelNode.querySelector(`.${this.class.panelPosts}`);

						panelPostsNode.insertBefore(postToMove, panelPostsNode.firstChild);
						this.adjustCollapsedPostsPanel();

						postToMove.classList.remove(`${this.class.postHide}`);
						post.classList.remove(`${this.class.postPinnedHide}`);

						this.adjustPanel();
						this.showCollapsedPostsPanel();

						// pinnedPanel.post::show.start
						setTimeout(() => {
							post.classList.add(`${this.class.postPinned}`);
							Utils.setStyle(postToMove, {
								position: '',
								width: '',
								height: '80px',
								backgroundColor: '',
								opacity: 1
							});

							this.setOptions({
								pinBlocked: false
							});

							setTimeout(() => {
								postToMove.classList.remove(`${this.class.postHide}`);
								Utils.setStyle(postToMove, {
									position: '',
									width: '',
									height: '',
									backgroundColor: '',
									opacity: ''
								});
							}, 600); // 600 > transition 0.5

						}, 300);
					});

					return resolve();
				});
			}
			else
			{
				const height = postToMove.getAttribute('bx-data-height');
				const pinnedHeight = postToMove.scrollHeight;

				Utils.setStyle(postToMove, {
					transition: '',
				});

				const cancelPinnedPanel = document.querySelector(`.${this.class.cancelPanel}[bx-data-log-id="${logId}"]`);
				if (Type.isDomNode(cancelPinnedPanel))
				{
					Utils.setStyle(postToMove, {
						height: pinnedHeight + 'px'
					});

					// pinnedPanel.post::hide.start, cancelPanel::show.start
					requestAnimationFrame(() => {

						postToMove.classList.add(`${this.class.postExpanding}`);
						cancelPinnedPanel.classList.add(`${this.class.postExpanding}`);

						Utils.setStyle(postToMove, {
							opacity: 0,
							height: 0
						});
						Utils.setStyle(cancelPinnedPanel, {
							opacity: 0,
							height: 0
						});
					});

					const collapsed = pinnedPanelNode.classList.contains(`${this.class.panelCollapsed}`);

					if (collapsed)
					{
						cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);
						this.adjustCollapsedPostsPanel();
						this.adjustPanel();
					}

					const showCollapsed = this.getCollapsedPanelNode().classList.contains(`${this.class.collapsedPanelShow}`);
					if (showCollapsed)
					{
						this.hideCollapsedPostsPanel();

						// cancelPanel::show.end
						Event.unbindAll(cancelPinnedPanel, 'transitionend');
						Event.bind(cancelPinnedPanel, 'transitionend', (event) => {
							if (!this.checkTransitionProperty(event, 'height'))
							{
								return;
							}

							Utils.setStyle(postToMove, {
								transform: '',
								display: 'block'
							});

							this.animateCancel({
								post,
								postToMove,
								cancelPinnedPanel,
								height
							});
						});
					}

					// pinnedPanel.post::hide.end
					Event.unbindAll(postToMove, 'transitionend');
					Event.bind(postToMove, 'transitionend', (event) => {
						if (!this.checkTransitionProperty(event, 'opacity'))
						{
							return;
						}

						if (!collapsed)
						{
							cancelPinnedPanel.parentNode.insertBefore(postToMove, cancelPinnedPanel.nextSibling);
							this.adjustCollapsedPostsPanel();
							this.adjustPanel();
						}

						this.animateCancel({
							post,
							postToMove,
							cancelPinnedPanel,
							height
						});
					});
				}
				else
				{
					post.classList.remove(`${this.class.postPinned}`);
					pinnedPanelNode.parentNode.insertBefore(postToMove, pinnedPanelNode.nextSibling);
					this.adjustPanel();

					const originalPostHeight = postToMove.scrollHeight;
					postToMove.setAttribute('bx-data-height', originalPostHeight);

					Utils.setStyle(postToMove, {
						opacity: 0,
						height: '80px'
					});

					// list.post::show.start
					setTimeout(() => {
						Utils.setStyle(postToMove, {
							opacity: 1,
							height: originalPostHeight + 'px'
						});
					}, 100);

					// list.post::show.end
					Event.unbindAll(postToMove, 'transitionend');
					Event.bind(postToMove, 'transitionend', (event) => {
						if (!this.checkTransitionProperty(event, 'height'))
						{
							return;
						}

						Utils.setStyle(postToMove, {
							height:''
						});
					})
				}

				return resolve();
			}
		});
	}

	animateCancel({
		post,
	 	postToMove,
		cancelPinnedPanel,
		height
	})
	{
		post.classList.remove(`${this.class.postPinned}`);

		// post.list:show.start, cancelPanel::hide.start
		setTimeout(() => {

			Utils.setStyle(postToMove, {
				opacity: 1,
				height: height + 'px'
			});
			Utils.setStyle(cancelPinnedPanel, {
				height: 0
			});

			setTimeout(() => {
				cancelPinnedPanel.remove();
			}, 100)
		}, 100);

		// post.list:show.end
		Event.unbindAll(postToMove, 'transitionend');
		Event.bind(postToMove, 'transitionend', (event) => {
			if (!this.checkTransitionProperty(event, 'height'))
			{
				return;
			}

			post.classList.remove(`${this.class.postPinnedHide}`);

			Utils.setStyle(postToMove, {
				marginBottom: '',
				height: ''
			});
			Utils.setStyle(cancelPinnedPanel, {
				marginBottom: '',
				height: ''
			});

			postToMove.classList.remove(`${this.class.postExpanding}`);
			cancelPinnedPanel.classList.remove(`${this.class.postExpanding}`);
		});
	}

	getCancelPinnedPanel(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		if (logId <= 0)
		{
			return null;
		}

		let cancelPinnedPanel = document.querySelector(`.${this.class.cancelPanel}[bx-data-log-id="${logId}"]`);

		if (!Type.isDomNode(cancelPinnedPanel))
		{
			cancelPinnedPanel = Tag.render`
				<div class="${this.class.cancelPanel}" bx-data-log-id="${logId}">
					<div class="feed-post-cancel-pinned-panel-inner">
						<div class="feed-post-cancel-pinned-content">
							<span class="${this.class.cancelPanelLabel}">${Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_TITLE')}</span>
							<span class="feed-post-cancel-pinned-text">${Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_DESCRIPTION')}</span>
						</div>
						<button class="ui-btn ui-btn-light-border ui-btn-round ui-btn-sm ${this.class.cancelPanelButton}">${Loc.getMessage('SONET_EXT_LIVEFEED_PINNED_CANCEL_BUTTON')}</button>
					</div>
				</div>	
				`;

			Event.bind(cancelPinnedPanel.querySelector(`.${this.class.cancelPanelButton}`), 'click', () => {
				this.changePinned({
					logId: logId,
					newState: 'N'
				}).then(() => {
					Utils.setStyle(cancelPinnedPanel, {
						opacity: 0,
						height: 0
					});
				});
			});
		}

		return cancelPinnedPanel;
	}

	centerCancelPinnedPanelElements({ cancelPinnedPanel })
	{
		if (!Type.isDomNode(cancelPinnedPanel))
		{
			return;
		}

		// cancelPanel::show.start
		setTimeout(() => {
			Utils.setStyle(cancelPinnedPanel, {
				opacity: 1
			});
		}, 100);

		Utils.setStyle(cancelPinnedPanel.querySelector(`.${this.class.cancelPanelLabel}`), {
			marginLeft: cancelPinnedPanel.querySelector(`.${this.class.cancelPanelButton}`).getBoundingClientRect().width + 'px'
		});
	}

	getPostsCount()
	{
		const panelNode = this.getPanelNode();
		return (panelNode ? Array.from(panelNode.getElementsByClassName(`${this.class.post}`)).length : 0);
	}

	hidePinnedItems()
	{
		const pinnedPanelNode = this.getPanelNode();
		if (!pinnedPanelNode)
		{
			return;
		}

		Utils.setStyle(pinnedPanelNode, {
			height: parseInt(this.getOption('panelHeight')) + 'px'
		});

		Array.from(pinnedPanelNode.getElementsByClassName(`${this.class.post}`)).reduce((count, item) => {

			count += item.offsetHeight;

			Utils.setStyle(item, {
				transition: 'opacity .1s linear, transform .2s .1s linear, height .5s linear'
			});
			Utils.setStyle(pinnedPanelNode, {
				transition: 'height .5s .1s linear'
			});

			// pinnedPanel.post::hide.start
			requestAnimationFrame(() => {
				Utils.setStyle(item, {
					opacity: '0!important',
					transform: `translateY(-${count}px)`
				});
				Utils.setStyle(pinnedPanelNode, {
					height: '58px'
				});
			});

			// pinnedPanel.post::hide.end
			Event.unbindAll(item, 'transitionend');
			Event.bind(item, 'transitionend', (event) => {
				if (!this.checkTransitionProperty(event, 'transform'))
				{
					return;
				}

				Utils.setStyle(item, {
					display: 'none',
					opacity: '',
					transform: '',
					transition: ''
				});
				Utils.setStyle(pinnedPanelNode, {
					transition: ''
				});
			});

			return count;
		}, 0);
	}

	showPinnedItems()
	{
		const pinnedPanelNode = this.getPanelNode();
		if (!pinnedPanelNode)
		{
			return;
		}

		Array.from(pinnedPanelNode.getElementsByClassName(`${this.class.post}`)).map((item, currentIndex, originalItemsList) => {

			Utils.setStyle(item, {
				display: 'block',
				opacity: 0
			});

			// pinnedPanel.post::show.start
			requestAnimationFrame(() => {
				Utils.setStyle(pinnedPanelNode, {
					height: ((84 * (currentIndex + 1)) - 4) + 'px'
				});
				Utils.setStyle(item, {
					transform: `translateY(${0}px)`,
					opacity: 1
				});
			});

			// pinnedPanel.post::show.end
			Event.unbindAll(item, 'transitionend');
			Event.bind(item, 'transitionend', (event) => {
				if (!this.checkTransitionProperty(event, 'transform'))
				{
					return;
				}

				Utils.setStyle(item, {
					display: 'block',
					height: '',
					transform: ''
				});
				Utils.setStyle(pinnedPanelNode, {
					height: ''
				});

				if ((currentIndex + 1) === originalItemsList.length)
				{
					Utils.setStyle(pinnedPanelNode, {
						transition: '',
						height: ''
					});
				}
			});
		});
	}

	animateCollapsedPanel()
	{
		// collapsedPanel::hide.start
		requestAnimationFrame(() => {
			const collapsedPanel = this.getCollapsedPanelNode();

			Utils.setStyle(collapsedPanel, {
				position: 'absolute',
				top: 0,
				width: '100%',
				opacity: 0
			});

			collapsedPanel.classList.remove(`${this.class.collapsedPanelHide}`);
			collapsedPanel.classList.add(`${this.class.collapsedPanelShow}`);

			// collapsedPanel::show.start
			requestAnimationFrame(() => {
				Utils.setStyle(collapsedPanel, {
					position: 'relative',
					opacity: 1
				});
			});
		})
	}

	adjustCollapsedPostsPanel()
	{
		const postsCounter = this.getPostsCount();
		const postsCounterNode = this.getCollapsedPanelNode().querySelector(`.${this.class.collapsedPanelCounterPostsValue}`);
		if (postsCounterNode)
		{
			postsCounterNode.innerHTML = parseInt(postsCounter);
		}

		const commentsCounterNode = this.getCollapsedPanelNode().querySelector(`.${this.class.collapsedPanelCounterComments}`);
		const commentsCounterValueNode = this.getCollapsedPanelNode().querySelector(`.${this.class.collapsedPanelCounterCommentsValue}`);
		const panelNode = this.getPanelNode();

		if (
			commentsCounterNode
			&& commentsCounterValueNode
			&& panelNode
		)
		{
			const newCommentCounter = Array.from(panelNode.querySelectorAll(`.${this.class.collapsedPanelCounterCommentsValueNewValue}`)).reduce((acc, node) => {
				return acc + (node.closest(`.${this.class.postUnfollowed}`) ? 0 : parseInt(node.innerHTML));
			}, 0);

			commentsCounterValueNode.innerHTML = newCommentCounter;
			if (newCommentCounter > 0)
			{
				commentsCounterNode.classList.add(`${this.class.collapsedPanelCounterCommentsShown}`);
			}
			else
			{
				commentsCounterNode.classList.remove(`${this.class.collapsedPanelCounterCommentsShown}`);
			}
		}
	}

	adjustPanel()
	{
		const panelNode = this.getPanelNode();
		if (!panelNode)
		{
			return;
		}

		setTimeout(() => {
			if (this.getPostsCount() > 0)
			{
				panelNode.classList.add(`${this.class.panelNonEmpty}`);
			}
			else
			{
				panelNode.classList.remove(`${this.class.panelNonEmpty}`);
			}
		}, 0);
	}

	showCollapsedPostsPanel()
	{
		if (this.getPostsCount() >= Loc.getMessage('SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT'))
		{
			this.showCollapsedPanel();
			this.hidePinnedItems();
		}
	}

	hideCollapsedPostsPanel()
	{
		if (this.getPostsCount() < Loc.getMessage('SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT'))
		{
			this.getPanelNode().classList.remove(`${this.class.panelCollapsed}`);
			this.removeCollapsedPanel();
			this.showPinnedItems();
		}
	}

	showCollapsedPanel()
	{
		this.getPanelNode().classList.add(`${this.class.panelCollapsed}`);
		this.animateCollapsedPanel();
	}

	hideCollapsedPanel()
	{
		this.getPanelNode().classList.remove(`${this.class.panelCollapsed}`);
		this.showPinnedItems();
		this.removeCollapsedPanel();
	}

	removeCollapsedPanel()
	{
		const collapsedPanel = this.getCollapsedPanelNode();

		Utils.setStyle(collapsedPanel, {
			position: 'absolute',
			top: 0,
			width: '100%'
		});

		collapsedPanel.classList.remove(`${this.class.collapsedPanelShow}`);
		collapsedPanel.classList.add(`${this.class.collapsedPanelHide}`);
	}

	getCommentsNodes(xmlId)
	{
		const result = {
			follow: true,
			newNode: null,
			newValueNode: null,
			oldNode: null,
			allNode: null
		};

		if (!Type.isStringFilled(xmlId))
		{
			return result;
		}

		const commentsNode = document.querySelector(`.${this.class.postComments}[data-bx-comments-entity-xml-id="${xmlId}"]`);
		if (!commentsNode)
		{
			return result;
		}

		const postNode = commentsNode.closest(`.${this.class.postPinActive}`);
		if (!postNode)
		{
			return result;
		}

		const newPinnedCommentsNode = postNode.querySelector(`.${this.class.collapsedPanelCounterCommentsValueNew}`);
		const newValuePinnedCommentsNode = postNode.querySelector(`.${this.class.collapsedPanelCounterCommentsValueNewValue}`);
		const oldPinnedCommentsNode = postNode.querySelector(`.${this.class.collapsedPanelCounterCommentsValueOld}`);
		const allPinnedCommentsNode = postNode.querySelector(`.${this.class.collapsedPanelCounterCommentsValueAll}`);

		if (
			!newPinnedCommentsNode
			|| !newValuePinnedCommentsNode
			|| !oldPinnedCommentsNode
			|| !allPinnedCommentsNode

		)
		{
			return result;
		}

		result.newNode = newPinnedCommentsNode;
		result.newValueNode = newValuePinnedCommentsNode;
		result.oldNode = oldPinnedCommentsNode;
		result.allNode = allPinnedCommentsNode;
		result.follow = (commentsNode.getAttribute('data-bx-follow') !== 'N');

		return result;
	}

	getCommentsData(xmlId)
	{
		const result = {
			newValue: null,
			oldValue: null,
			allValue: null
		};

		if (!Type.isStringFilled(xmlId))
		{
			return result;
		}

		const { newValueNode, oldNode, allNode, follow } = this.getCommentsNodes(xmlId);

		result.follow = follow;

		if (
			!Type.isDomNode(newValueNode)
			|| !Type.isDomNode(oldNode)
		)
		{
			return result;
		}

		let newCommentsValue = 0;
		let oldCommentsValue = 0;
		let allCommentsValue = 0;

		let matches = newValueNode.innerHTML.match(/(\d+)/);

		if (matches)
		{
			newCommentsValue = parseInt(matches[1]);
		}

		matches = oldNode.innerHTML.match(/(\d+)/);
		if (matches)
		{
			oldCommentsValue = parseInt(matches[1]);
		}

		matches = allNode.innerHTML.match(/(\d+)/);
		if (matches)
		{
			allCommentsValue = parseInt(matches[1]);
		}

		result.oldValue = oldCommentsValue;
		result.newValue = newCommentsValue;
		result.allValue = allCommentsValue;

		return result;
	}

	setCommentsData(xmlId, value)
	{
		if (!Type.isStringFilled(xmlId))
		{
			return;
		}

		const { newNode, newValueNode, oldNode, allNode } = this.getCommentsNodes(xmlId);
		if (
			!Type.isDomNode(newNode)
			|| !Type.isDomNode(newValueNode)
			|| !Type.isDomNode(oldNode)
			|| !Type.isDomNode(allNode)
		)
		{
			return;
		}

		if (Type.isInteger(value.newValue))
		{
			newValueNode.innerHTML = `${value.newValue}`;
			if (
				value.newValue > 0
				&& !newNode.classList.contains(`${this.class.collapsedPanelCounterCommentsValueNewActive}`)
			)
			{
				newNode.classList.add(`${this.class.collapsedPanelCounterCommentsValueNewActive}`);
			}
			else if (
				value.newValue <= 0
				&& newNode.classList.contains(`${this.class.collapsedPanelCounterCommentsValueNewActive}`)
			)
			{
				newNode.classList.remove(`${this.class.collapsedPanelCounterCommentsValueNewActive}`);
			}
		}

		if (Type.isInteger(value.oldValue))
		{
			oldNode.innerHTML = value.oldValue;
		}

		if (Type.isInteger(value.allValue))
		{
			allNode.innerHTML = value.allValue;
		}

		this.adjustCollapsedPostsPanel();
	}

	getCollapsedPanelNode()
	{
		return this.getPanelNode().querySelector(`.${this.class.collapsedPanel}`);
	}

	checkTransitionProperty(event, propertyName)
	{
		return (event.propertyName === propertyName);
	}
}

export {
	PinnedPanel
};
