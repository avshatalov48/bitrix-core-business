import {Type} from 'main.core';
import {Vue} from 'ui.vue';
import 'ui.forms';
import '../style.css';
import 'ui.info-helper'
import 'ui.sidepanel-content';
import 'ui.layout-form';

export default Vue.extend({
	props: {
		defaultSetup:{
			type: Object,
			required: true
		},
		defaultConfig:{
			type: Object,
			required: true
		},
	},
	data()
	{
		return {
			config:{
				business:{
					name: null
				},
				ig_cta:{
					cta_button_text: "",
					cta_button_url: null,
				},
				messenger_chat:{
					domains: [window.location.protocol + '//' + (window.location.host || window.location.hostname)]
				},
				messenger_menu:{
					cta_button_text: "",
					cta_button_url: null,
				},
				page_card:{
					see_all_url: null
				},
				page_cta:{
					cta_button_text: "",
					cta_button_url: null,
				},
				page_post:{
					cta_button_text: "",
					cta_button_url: null,
					title: null
				},
				thread_intent:{
					cta_button_url: null
				},

			},
			setup: {
				timezone: null,
				currency: null,
				business_vertical: null,
			},
			values : {
				timezone:[],
				currency:[],
			},
			available : {
				business: true,
				messenger_chat:true,
				ig_cta : false,
				messenger_menu:false,
				page_cta: false,
				page_post: false,
				page_card: false,
				thread_intent: false
			},
			checked : {
				business: true,
				messenger_chat: true,
				ig_cta: false,
				page_cta: false,
				messenger_menu:false,
				page_post: false,
				page_card: false,
				thread_intent: false
			}
		};
	},
	created()
	{
		for (let [field, value] of Object.entries(this.setup))
		{
			if(this.defaultSetup[field] && this.defaultSetup[field].value)
			{
				this.setup[field] = this.defaultSetup[field].value;
			}
			if(this.defaultSetup[field] && this.defaultSetup[field].set)
			{
				this.values[field] = this.defaultSetup[field].set;
			}
		}

		for (let [field, value] of Object.entries(this.config))
		{
			if(this.defaultConfig[field] && this.defaultConfig[field].value)
			{
				this.checked[field] = !!this.defaultConfig[field].value;
				this.config[field] = this.defaultConfig[field].value;
			}
			this.available[field] = !!this.defaultConfig[field];
		}
	},
	methods:
	{
		getSetup()
		{
			return this.setup;
		},
		getConfig()
		{
			return Object.entries(this.checked).reduce((result,[field,value])=>{
				if (value && this.availableProps[field])
				{
					result[field] = this.config[field];
				}
				return result;
			},{});
		},
		addDomain()
		{
			this.config.messenger_chat.domains.push(null);
		},
		removeDomain(index)
		{
			this.config.messenger_chat.domains.splice(index, 1);
		},
		openInfoHelp()
		{
			top.BX.Helper.show('redirect=detail&code=13097346');
		},
		checkUrl(url)
		{
			if(Type.isString(url))
			{
				return url.search(/^((https:\/\/)|(www\.)|(http:\/\/))([a-z0-9-].?)+(:[0-9]+)?(\/.*)?$/i) === 0
			}
			return false;
		},
		checkDomain(domain)
		{
			if(Type.isString(domain))
			{
				return domain.search(/^((https:\/\/)|(http:\/\/)){1}[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/i) === 0
			}
			return false;
		},
		getSetupPropertiesStatus()
		{
			return Object.entries(this.getSetup() ?? {}).reduce((result,[key,value])=> {
				if(Type.isString(value) && value.length > 0)
				{
					switch (key)
					{
						case 'timezone':
							result[key] = this.values.timezone.includes(value);
							break;
						case 'currency':
							result[key] =  this.values.currency.includes(value);
							break;
						case 'business_vertical':
							result[key] = ['ECOMMERCE','SERVICES'].includes(value);
							break
					}
				}
				return result;
			},{});
		},
		getConfigPropertiesStatus()
		{
			return Object.entries(this.getConfig() ?? {}).reduce((result,[key,value]) => {
				result[key] = Object.entries(value).reduce((propertyResult,[propertyKey,propertyValue]) => {
					if (!['cta_button_text','see_all_url','cta_button_url','title','name','domains'].includes(propertyKey))
					{
						return propertyResult;
					}
					switch (propertyKey)
					{
						case 'cta_button_text':
							return propertyResult = propertyResult
								&& Type.isString(propertyValue)
								&& propertyValue.length > 0
								&& ['Reserve','Book Now','Buy Now','Book'].includes(propertyValue);
						case 'see_all_url':
						case 'cta_button_url':
							return propertyResult = propertyResult
								&& Type.isString(propertyValue)
								&& propertyValue.length > 0
								&& this.checkUrl(propertyValue) ;
						case 'title':
						case 'name':
							return propertyResult = propertyResult
								&& Type.isString(propertyValue)
								&& propertyValue.length > 0;
						case 'domains':
							return propertyResult = propertyResult
								&& Type.isArray(propertyValue) &&  propertyValue.length > 0
								&& propertyValue.reduce((value,domain) => value && this.checkDomain(domain), true);
					}
					return propertyResult;
				},true);
				return result;
			},{});
		},
		getPropertiesStatus()
		{
			return Object.assign({},this.getSetupPropertiesStatus(),this.getConfigPropertiesStatus());
		},
		alert(title,content,callback)
		{
			BX.UI.Dialogs.MessageBox.alert(content, title,callback);
			return this;
		},
		focusOnWrongProperty()
		{
			for (let [key,value] of Object.entries(this.getPropertiesStatus()))
			{
				if (!value && this.$refs[key])
				{
					this.$refs[key].scrollIntoView();
				}
			}
			return this;
		},
		validate()
		{
			return Object.entries(this.getPropertiesStatus()).reduce((result,[key,value]) => result && value,true);
		}
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('SEO_ADS_FACEBOOK_BUSINESS_');
		},
		availableProps()
		{
			return {
				business: this.available.business,
				messenger_chat: this.available.messenger_chat,
				ig_cta : this.available.ig_cta,
				page_cta: this.available.page_cta,
				page_post: this.available.page_post,
				messenger_menu: this.available.messenger_menu && this.checked.messenger_chat,
				page_card: this.available.page_card && this.setup.business_vertical === 'SERVICES',
				thread_intent: this.available.thread_intent && this.checked.messenger_chat
			};
		}
	},
	template:`
		<div class="seo-ads-login">
				<div class="ui-slider-section">
					<div class="ui-slider-content-box">
						<div class="ui-slider-heading-4">
							{{localize.SEO_ADS_FACEBOOK_BUSINESS_SETUP_FIELDS_TITLE}}
							<span class="seo-ads-login-hint"
								@click="openInfoHelp()"
							><span class="seo-ads-login-hint-icon"></span></span>
						</div>
						<div class="ui-form">
							<div ref="business" class="ui-form-row">
								<div class="ui-form-label">
									<div class="ui-ctl-label-text">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_NAME}}</div>
								</div>
								<div class="ui-form-content">
									<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" :class="{'ui-ctl-danger': !config.business.name}">
										<input 
										type ="text" 
										class="ui-ctl-element" 
										v-model="config.business.name">
									</div>
								</div>
							</div>
							<div ref="business_vertical" class="ui-form-row">
								<div class="ui-form-label">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TYPE}}
									</div>
								</div>
								<div class="ui-form-content">
									<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100" :class="{'ui-ctl-danger': !setup.business_vertical}">
										<div class="ui-ctl-after ui-ctl-icon-angle"></div>
											<select class="ui-ctl-element" v-model="setup.business_vertical">
												<option value="ECOMMERCE">
													{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_ECOMMERCE}}
												</option>
												<option value="SERVICES">
													{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_SERVICES}}
												</option>
											</select>
									</div>
								</div>
							</div>
							<div ref="timezone" class="ui-form-row">
								<div class="ui-form-label">
									<div class="ui-ctl-label-text">
										{{ localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TIMEZONE }}
									</div>
								</div>
								<div class="ui-form-content">
									<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100" :class="{'ui-ctl-danger': !setup.timezone}">
										<div class="ui-ctl-after ui-ctl-icon-angle"></div>
										<select class="ui-ctl-element" v-model="setup.timezone">
											<option v-for="timezone in values.timezone" :value="timezone">{{ timezone }}</option>
										</select>
									</div>
								</div>
							</div>
							<div ref="currency" class="ui-form-row">
								<div class="ui-form-label">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_CURRENCY}}
									</div>
								</div>
								<div class="ui-form-content">
									<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100" :class="{'ui-ctl-danger': !setup.currency}">
										<div class="ui-ctl-after ui-ctl-icon-angle"></div>
										<select class="ui-ctl-element" v-model="setup.currency">
											<option v-for="currency in values.currency" :value="currency">{{ currency }}</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="ui-slider-section">
					<div class="ui-slider-content-box">
						<div class="ui-slider-heading-4">
							{{localize.SEO_ADS_FACEBOOK_BUSINESS_FEATURE_TITLE}}
							<span class="seo-ads-login-hint"
								@click="openInfoHelp()"
							><span class="seo-ads-login-hint-icon"></span></span>
						</div>
						<div class="ui-form">
						<div ref="ig_cta" v-if="availableProps.ig_cta" class="ui-form-row">
							<div class="ui-form-label">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" v-model="checked.ig_cta">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_IG_CTA}}
									</div>
								</label>
							</div>
							<transition v-if="checked.ig_cta">
								<div class="ui-form-content">
									<div class="ui-form-row-group ui-form-row-inline">
										<div class="ui-form-row">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.ig_cta && !checkUrl(config.ig_cta.cta_button_url)}">
												<input 
													type="text"
													class="ui-ctl-element"
													:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
													v-model="config.ig_cta.cta_button_url"
													>
											</div>
										</div>
										<div class="ui-form-row">
											<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.ig_cta && !config.ig_cta.cta_button_text}">
												<div class="ui-ctl-after ui-ctl-icon-angle"></div>
												<select class="ui-ctl-element" v-model="config.ig_cta.cta_button_text">
													<option value="" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>
													<option value="Reserve">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>
													<option value="Book Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>
													<option value="Buy Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</transition>
						</div>
						<div ref="page_cta" v-if="availableProps.page_cta" class="ui-form-row" >
							<div class="ui-form-label">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" v-model="checked.page_cta">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_CTA}}
									</div>
								</label>
							</div>
							<transition v-if="checked.page_cta">
								<div class="ui-form-content">
									<div 
										class="ui-form-row-group ui-form-row-inline"  
										:class="{'ui-ctl-danger': checked.page_cta && !checkUrl(config.page_cta.cta_button_url)}"
									>
										<div class="ui-form-row">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm">
												<input 
												type="text" 
												class="ui-ctl-element"
												:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
												v-model="config.page_cta.cta_button_url"
												>
											</div>
										</div>
										<div class="ui-form-row">
											<div 
												class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm" 
												:class="{'ui-ctl-danger': checked.page_cta && !config.page_cta.cta_button_text}"
											>
												<div class="ui-ctl-after ui-ctl-icon-angle"></div>
												<select class="ui-ctl-element" v-model="config.page_cta.cta_button_text">
													<option value="" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>
													<option value="Reserve">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>
													<option value="Book Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>
													<option value="Buy Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</transition>
						</div>
						<div ref="page_post" v-if="availableProps.page_post" class="ui-form-row">
							<div class="ui-form-label">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" v-model="checked.page_post">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_POST}}
									</div>
								</label>
							</div>
							<transition v-if="checked.page_post">
								<div class="ui-form-content">
									<div class="ui-form-row-group">
										<div class="ui-form-row-inline">
											<div class="ui-form-row">
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm"  :class="{'ui-ctl-danger': checked.page_post && !checkUrl(config.page_post.cta_button_url) }">
													<input 
														type="text" 
														class="ui-ctl-element"
														:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
														v-model="config.page_post.cta_button_url" 
													>
												</div>
											</div>
											<div class="ui-form-row">
												<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.page_post && !config.page_post.cta_button_text}">
													<div class="ui-ctl-after ui-ctl-icon-angle"></div>
													<select class="ui-ctl-element" v-model="config.page_post.cta_button_text">
														<option value="" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>
														<option value="Reserve">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>
														<option value="Book Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>
														<option value="Buy Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>
													</select>
												</div>
											</div>
										</div>
										<div class="ui-form-row">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.page_post && !config.page_post.title}">
												<input
													type="text" 
													class="ui-ctl-element"
													:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_TITLE_PLACEHOLDER"
													v-model="config.page_post.title"
												>
											</div>
										</div>
									</div>
								</div>
							</transition>
						</div>
						<transition v-if="availableProps.messenger_menu">
							<div ref="messenger_menu" class="ui-form-row">
								<div class="ui-form-label">
									<label class="ui-ctl ui-ctl-checkbox">
										<input type="checkbox" class="ui-ctl-element" v-model="checked.messenger_menu">
										<div class="ui-ctl-label-text">
											{{ localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_MENU }}
										</div>
									</label>
								</div>
								<transition v-if="checked.messenger_menu">
									<div class="ui-form-content">
										<div 
											class="ui-form-row-group ui-form-row-inline "
											:class="{'ui-ctl-danger': checked.messenger_menu && !checkUrl(config.messenger_menu.cta_button_url)}"
										>
											<div class="ui-form-row">
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm">
													<input 
													type="text" 
													class="ui-ctl-element"
													:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
													v-model="config.messenger_menu.cta_button_url"  
													>
												</div>
											</div>
											<div class="ui-form-row">
												<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.messenger_menu && !config.messenger_menu.cta_button_text}">
													<div class="ui-ctl-after ui-ctl-icon-angle"></div>
													<select class="ui-ctl-element" v-model="config.messenger_menu.cta_button_text" >
														<option value="" disabled selected>{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUTTON_TEXT}}</option>
														<option value="Reserve">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_RESERVE}}</option>
														<option value="Book Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BOOK}}</option>
														<option value="Buy Now">{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_BUY}}</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</transition>
							</div>
						</transition>
						<transition v-if="availableProps.thread_intent">
							<div ref="thread_intent" class="ui-form-row">
							<div class="ui-form-label">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" v-model="checked.thread_intent">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_THREAD_INTENT}}
									</div>
								</label>
							</div>
							<transition v-if="checked.thread_intent">
								<div class="ui-form-content">
									<div class="ui-form-row-group">
										<div class="ui-form-row">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.thread_intent && !checkUrl(config.thread_intent.cta_button_url)}">
												<input 
												type="text" 
												class="ui-ctl-element"
												:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
												v-model="config.thread_intent.cta_button_url" 
												>
											</div>
										</div>
									</div>
								</div>
							</transition>
						</div>
						</transition>
						<div ref="messenger_chat" class="ui-form-row">
							<div class="ui-form-label">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" v-model="checked.messenger_chat">
									<div class="ui-ctl-label-text">
										{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_CHAT}}
									</div>
								</label>
							</div>
							<transition v-if="checked.messenger_chat" name="hidden-row">
								<div class="ui-form-content">
									<div class="ui-form-row-group">
										<div 
											v-for="(domain,index) in config.messenger_chat.domains" 
											class="ui-form-row"
											>
											<div class="ui-ctl ui-ctl-after-icon ui-ctl-textbox ui-ctl-w100" :class="{'ui-ctl-danger': !checkDomain(config.messenger_chat.domains[index])}">
												<button class="ui-ctl-after ui-ctl-icon-clear" @click="removeDomain(index)">
												</button>
												<input type="text" class="ui-ctl-element"
												:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_DOMAIN_PLACEHOLDER"
												v-model="config.messenger_chat.domains[index]"
												 >
											</div>
										</div>
										<div class="ui-form-row">
											<button class="ui-btn ui-btn-light-border ui-btn-xs" @click="addDomain">
												{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_MESSENGER_CHAT_ADD}}
											</button>
										</div>
									</div>
								</div>
							</transition>
						</div>
						<transition v-if="availableProps.page_card">
							<div id="page_card" class="ui-form-row">
								<div class="ui-form-label">
									<label class="ui-ctl ui-ctl-checkbox">
										<input type="checkbox" class="ui-ctl-element" v-model="checked.page_card">
										<div class="ui-ctl-label-text">
											{{localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_PAGE_CARD}}
										</div>
									</label>
								</div>
								<transition v-if="checked.page_card">
									<div class="ui-form-content">
										<div class="ui-form-row-group ui-form-row-inline">
											<div class="ui-form-row">
												<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-sm" :class="{'ui-ctl-danger': checked.page_card && !checkUrl(config.page_card.see_all_url)}">
													<input 
													type="text" 
													class="ui-ctl-element"
													:placeholder="localize.SEO_ADS_FACEBOOK_BUSINESS_LOGIN_URL_PLACEHOLDER"
													v-model="config.page_card.see_all_url"
													 >
												</div>
											</div>
										</div>
									</div>
								</transition>
							</div>
						</transition>
					</div>
				</div>
			</div>
		</div>
`
});
