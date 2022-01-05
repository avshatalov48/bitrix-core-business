import { Helper } from './helper';
import { Event, Loc, Tag } from "main.core";
import {type PostSelectorOptions} from './types/postselectoroptions'
import {TextCrop} from 'ui.textcrop';

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
					<label class="crm-order-instagram-view-item" id="instItemID_${postListItem.id}">
						<div class="crm-order-instagram-view-item-detail">
							<div>
								<span class="crm-order-instagram-view-item-img" style="background-image: url(${postListItem.media_url})"></span>
								<span class="crm-order-instagram-decal-container">
									<span>
										<svg width="67" height="16" viewBox="0 0 67 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M48.6026 0.306715C54.4273 0.290801 60.2679 0.306716 66.1244 0.290802C63.0688 5.35159 60.0292 10.3805 56.9736 15.4413C56.9577 15.3777 56.9418 15.3299 56.9418 15.2981C56.7508 13.9931 56.5439 12.6722 56.353 11.3672C56.162 10.0782 55.971 8.78911 55.7641 7.50004C55.6845 6.95895 55.605 6.41785 55.5254 5.86085C55.5095 5.74945 55.4458 5.66988 55.3503 5.60622C54.8252 5.19245 54.3 4.77867 53.7589 4.36489C52.0879 3.05991 50.401 1.75493 48.7299 0.449946C48.6822 0.402203 48.6345 0.370374 48.5708 0.32263C48.5867 0.338545 48.5867 0.322629 48.6026 0.306715ZM56.4962 5.66988C56.8463 7.96156 57.1805 10.2532 57.5306 12.5449C57.642 12.4335 63.976 1.96182 64.0078 1.81859C61.4933 3.10765 58.9947 4.38081 56.4962 5.66988ZM62.7824 1.34115C62.7824 1.32524 62.7665 1.32524 62.7665 1.30932C62.3845 1.2775 51.6105 1.2775 51.4672 1.32524C52.9791 2.50291 54.4751 3.66466 55.971 4.82641C58.2468 3.66466 60.5066 2.50291 62.7824 1.34115Z" fill="#999"/>
											<path d="M9.46094 15.2344C8.60547 14.3984 4.79192 10.6375 2.65772 8.51853C2.49858 8.3753 2.35535 8.21616 2.21212 8.05702C1.44823 7.26129 1.03445 6.32234 0.954879 5.24016C0.859393 4.01475 1.1936 2.91666 1.95749 1.96179C2.67364 1.07058 3.59667 0.481747 4.74251 0.306688C6.66816 0.00431397 8.19594 0.672722 9.34178 2.24825C9.37361 2.28008 9.38952 2.31191 9.40544 2.34374C9.40544 2.34374 9.42135 2.35965 9.45318 2.37556C9.48501 2.32782 9.53275 2.26416 9.5805 2.21642C10.2012 1.34113 11.0128 0.752293 12.0313 0.434004C12.4769 0.290774 12.9384 0.258947 13.4 0.258947C14.0206 0.258947 14.6095 0.370347 15.1665 0.640892C16.5828 1.29338 17.49 2.37556 17.8242 3.88743C18.1425 5.31973 17.8242 6.64063 16.933 7.80238C16.7738 8.00927 16.5828 8.18433 16.3919 8.37531C14.9277 9.82352 13.4795 11.2717 12.0154 12.7199C11.1923 13.543 10.2422 14.4922 9.46094 15.2344ZM9.42135 13.818C9.51684 13.7226 9.61233 13.643 9.6919 13.5634C10.233 13.0223 10.79 12.4812 11.3311 11.9401C12.827 10.4601 14.3389 8.98005 15.8349 7.4841C15.9622 7.3727 16.0736 7.24538 16.1691 7.10215C17.0125 5.95631 17.1717 4.71498 16.6306 3.39409C15.9622 1.77081 14.1479 0.879609 12.4292 1.32521C11.5539 1.54801 10.8537 2.04136 10.3444 2.78934C10.0579 3.21903 9.8033 3.64872 9.51684 4.07841C9.48501 4.12615 9.45318 4.1739 9.42135 4.23755C9.3577 4.14207 9.30995 4.07841 9.26221 3.99884C8.99166 3.58506 8.73703 3.15537 8.46649 2.7416C7.65485 1.56393 6.19073 0.975095 4.80617 1.27747C2.60998 1.7549 1.40048 3.99883 2.13255 6.09954C2.32352 6.64063 2.64181 7.10215 3.05558 7.51593C3.81948 8.2639 4.58337 9.01188 5.33135 9.75986C6.58859 11.0012 7.84583 12.2584 9.11898 13.4997C9.21447 13.6111 9.30995 13.7066 9.42135 13.818Z" fill="#999"/>
											<path d="M41.4411 14.9002C41.282 14.8843 41.1387 14.8684 40.9796 14.8525C40.343 14.7888 39.7223 14.7093 39.0858 14.6456C38.5447 14.5819 38.0195 14.5501 37.4784 14.4705C37.2874 14.4546 37.1442 14.4865 36.9851 14.566C36.2371 14.9002 35.4414 15.1071 34.6297 15.1708C32.9269 15.2981 31.3354 14.948 29.8872 14.009C28.5504 13.1337 27.5796 11.9561 26.9749 10.476C26.7043 9.82352 26.5452 9.13921 26.4815 8.43897C26.3383 6.60881 26.7203 4.92188 27.7865 3.42593C28.9642 1.77082 30.5397 0.736386 32.529 0.35444C32.9269 0.274868 33.3407 0.227125 33.7385 0.243039C34.0727 0.258954 34.4069 0.243039 34.7411 0.274868C35.7278 0.370354 36.6668 0.656814 37.5262 1.15016C39.2927 2.13686 40.4862 3.60098 41.1069 5.54254C41.3138 6.19503 41.4093 6.86344 41.4252 7.54776C41.457 8.50263 41.2979 9.40975 40.9796 10.301C40.9478 10.3964 40.9478 10.4919 40.9478 10.5874C41.0432 11.4627 41.1387 12.338 41.2342 13.1974C41.2979 13.7066 41.3456 14.2159 41.4093 14.7411C41.4411 14.7888 41.4411 14.8366 41.4411 14.9002ZM40.3271 13.8021C40.3271 13.7544 40.3271 13.7385 40.3271 13.7226C40.2793 13.2292 40.2157 12.7518 40.1679 12.2584C40.1043 11.6537 40.0406 11.0649 39.977 10.4601C39.9611 10.3487 39.977 10.2691 40.0088 10.1577C40.4385 9.09146 40.5499 7.97745 40.4226 6.84753C40.3271 6.03589 40.0725 5.272 39.6587 4.57177C38.3219 2.1846 35.7119 0.895532 33.0065 1.29339C31.7333 1.48437 30.6193 2.00954 29.6644 2.853C27.8502 4.46036 27.0863 6.83161 27.6274 9.18695C28.0889 11.1444 29.2347 12.5926 31.0012 13.5316C31.7492 13.9294 32.5449 14.1522 33.3884 14.2159C33.8499 14.2477 34.2955 14.2477 34.757 14.2C35.505 14.1045 36.2212 13.8817 36.9055 13.5475C37.0487 13.4838 37.176 13.4679 37.3352 13.4838C37.6694 13.5316 37.9877 13.5634 38.3219 13.5952C38.7675 13.643 39.2131 13.6907 39.6587 13.7385C39.8656 13.7544 40.0884 13.7703 40.3271 13.8021Z" fill="#999"/>
										</svg>
									</span>
									<span>
										<svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M6.145 8.0856L0.5 13.7847V0.5H12.5V13.7847L6.855 8.0856L6.5 7.727L6.145 8.0856Z" stroke="#999"/>
										</svg>
									</span>
								</span>
								<div class="crm-order-instagram-view-item-decs-block">
									<div class="crm-order-instagram-view-item-decs">
										<span class="crm-order-instagram-view-item-name">${BX.util.htmlspecialchars(postListItem.caption||'')}</span>
										<span class="crm-order-instagram-view-item-edit"></span>
									</div>
								</div>
							</div>
							<div class="crm-order-instagram-view-item-checkbox-container">
								<input class="crm-order-instagram-view-item-input" 
										type="checkbox" 
										id="${postListItem.id}" 
										data-id="${postListItem.id}"
								>
								<div class="crm-order-instagram-view-item-input-title">${Loc.getMessage('SEO_AD_BUILDER_POST_SELECTOR_SELECT')}</div>
							</div>
						</div>
						<div class="crm-order-instagram-view-item-selected-icon">
							<svg width="13" height="10" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M4.87744 6.02868L10.9842 0L12.8322 1.85828L4.90632 9.7162L4.87744 9.68717L4.84857 9.7162L0 5.02339L1.84802 3.16511L4.87744 6.02868Z" fill="white"/>
							</svg>
						</div>
					</label>`;
				this.listContent.appendChild(postBlock);

				Event.bind(postBlock, 'click', this.selectPost.bind(this));

				this.dataContent[postListItem.id] = postListItem;
				this.showListContentBlock();

				const text = new BX.UI.TextCrop({
					rows: 3,
					target: postBlock.querySelector('.crm-order-instagram-view-item-name'),
				});
				text.init();
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

		document.querySelectorAll('.crm-order-instagram-view-item').forEach(
			element => {
				BX.removeClass(element, "crm-order-instagram-view-item-selected")
			}
		)

		BX.addClass(document.querySelector('#instItemID_' + id), "crm-order-instagram-view-item-selected");

		BX.SidePanel.Instance.close();

		BX.SidePanel.Instance.postMessage(
			window,
			'seo-ads-post-selected',
			this.dataContent[id]
		);
	}
}