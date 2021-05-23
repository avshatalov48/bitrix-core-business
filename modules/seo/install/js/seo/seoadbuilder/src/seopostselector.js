import { Helper } from './helper';
import { Event, Tag } from "main.core";
import {type PostSelectorOptions} from './types/postselectoroptions'

export class SeoPostSelector
{
	_accountId: string;
	_clientId: string;
	_type: string;

	constructor(options: PostSelectorOptions)
	{
		this.helper = Helper.getCreated();
		this.last = null;
		this.stopLoading = false;
		this.loadInProgress = false;
		this._accountId = options.accountId;
		this._clientId = options.clientId;
		this._type = options.type;
		this.signedParameters = options.signedParameters;
		this.emptyBlock = document.querySelector('.seo-ads-empty-post-list-block');
		this.listContent = document.querySelector('.crm-order-instagram-view-list');
		this.dataContent = [];
		this.loader = new BX.Loader({
			target: document.querySelector(".crm-order-instagram-view")
		});
		this.init();
	}

	init()
	{
		this.hideListContentBlock();
		const topSlider = BX.SidePanel.Instance.getTopSlider().iframe.contentDocument;

		const observer = new IntersectionObserver(this.loadPostList.bind(this), {
			root: topSlider,
			rootMargin: '0px',
			threshold: 1.0
		});

		observer.observe(this.listContent)

		this.loadPostList();

	}

	loadPostList()
	{
		if(this.loadInProgress)
		{
			return;
		}

		if (this.stopLoading)
		{
			return;
		}
		this.loader.show();

		this.loadInProgress = true;
		const requestData = {
			'clientId': this._clientId || null,
			'type': this._type || null,
			'accountId': this._accountId || null,
			'last': this.last
		};

		BX.ajax.runComponentAction('bitrix:seo.ads.builder', 'getPostList', {
			'mode': 'class',
			'signedParameters': this.signedParameters,
			'data': requestData
		}).then(
			response => {
				const data = response.data || {};
				if (data.error)
				{
				}
				else
				{
					this.successFn.apply(this, [data]);
				}
				this.loadInProgress = false;
			},
			() => {
				const data = { 'error': true, 'text': '' };
				this.loadInProgress = false;
				this.loader.hide();
			}
		);

	}

	showEmptyListBlock()
	{
		this.emptyBlock.style.display = 'block';
	}

	hideEmptyListBlock()
	{
		this.emptyBlock.style.display = 'none';
	}

	showListContentBlock()
	{
		this.listContent.parentNode.style.display = 'block';
	}

	hideListContentBlock()
	{
		this.listContent.parentNode.style.display = 'none';
	}

	successFn(response)
	{
		const data = response.data;
		if (this.clientSelector)
		{
			this.clientSelector.enable();
		}

		data.postList.forEach(postListItem => {
				const postBlock = Tag.render`
					<div class="crm-order-instagram-view-item">
						<input class="crm-order-instagram-view-item-input" 
							type="checkbox" 
							id="${postListItem.id}" 
							data-id="${postListItem.id}"
							>
						<label class="crm-order-instagram-view-item-detail"
						 	for="${postListItem.id}"
						 	>
							 <span class="crm-order-instagram-view-item-img" 
							 style="background-image: url(${postListItem.media_url})"></span>
							 <div class="crm-order-instagram-view-item-decs-block">
								  <div class="crm-order-instagram-view-item-decs">
									   <span class="crm-order-instagram-view-item-name">
									   ${postListItem.caption||''}
									   </span>
									   <span class="crm-order-instagram-view-item-edit"></span>
								  </div>
							 </div>
						</label>
					</div>`;
				this.listContent.appendChild(postBlock);

				Event.bind(postBlock, 'click', this.selectPost.bind(this));
				this.dataContent[postListItem.id] = postListItem;
				this.showListContentBlock();
			}
		);

		this.loader.hide();

		if (data.last)
		{
			this.last = data.last;
			return;
		}

		if(Object.keys(this.dataContent).length === 0)
		{
			this.hideListContentBlock();
			this.showEmptyListBlock();
		}

		this.stopLoading = true;
	}

	selectPost(event)
	{
		const targetElement = event.target;
		const id = targetElement.dataset.id;

		document.querySelectorAll('.crm-order-instagram-view-item-input').forEach(
			element => {
				element.checked = id === element.dataset.id;
			}
		)
		BX.SidePanel.Instance.close();

		BX.SidePanel.Instance.postMessage(
			window,
			'seo-ads-post-selected',
			this.dataContent[id]
		);
	}
}