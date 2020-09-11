import {Uri, Cache, Loc, Reflection, Type, Http, ajax, Text} from 'main.core';
import {Env} from 'landing.env';
import type {Block, Landing, Site, Template, CreatePageOptions, SourceResponse, PreparedResponse} from './types';

let additionalRequestCompleted = true;

/**
 * @memberOf BX.Landing
 */
export class Backend
{
	static getInstance()
	{
		if (!Backend.instance)
		{
			Backend.instance = new Backend();
		}

		return Backend.instance;
	}

	static makeResponse(xhr: XMLHttpRequest, sourceResponse: SourceResponse = {}): PreparedResponse
	{
		const type = (() => {
			if (Type.isStringFilled(sourceResponse.type))
			{
				return sourceResponse.type;
			}

			if (Type.isPlainObject(sourceResponse) && Object.values(sourceResponse).length > 0)
			{
				const allSuccess = Object.values(sourceResponse).every((item) => {
					return item.type === 'success';
				});

				if (allSuccess)
				{
					return 'success';
				}
			}

			if (Type.isArray(sourceResponse))
			{
				return 'other';
			}

			return 'error';
		})();

		if (type === 'other')
		{
			return sourceResponse;
		}

		return {
			result: null,
			type,
			...sourceResponse,
			status: xhr.status,
			authorized: xhr.getResponseHeader('X-Bitrix-Ajax-Status') !== 'Authorize',
		};
	}

	static request({url, data}): Promise<any, any>
	{
		return new Promise((resolve, reject) => {
			const fd = data instanceof FormData ? data : Http.Data.convertObjectToFormData(data);
			const xhr = ajax({
				method: 'POST',
				dataType: 'json',
				url,
				data: fd,
				start: false,
				preparePost: false,
				onsuccess: (sourceResponse) => {
					const response = Backend.makeResponse(xhr, sourceResponse);

					if (Type.isStringFilled(response.sessid) && additionalRequestCompleted)
					{
						Loc.setMessage('bitrix_sessid', response.sessid);
						additionalRequestCompleted = false;

						const newData = {...data, sessid: Loc.getMessage('bitrix_sessid')};

						Backend
							.request({url, data: newData})
							.then((newResponse) => {
								additionalRequestCompleted = true;
								resolve(newResponse);
							})
							.catch((newResponse) => {
								additionalRequestCompleted = true;
								reject(newResponse);
							});

						return;
					}

					if (!Type.isPlainObject(response))
					{
						resolve(response);
						return;
					}

					if (
						response.type === 'error'
						|| response.authorized === false
					)
					{
						reject(response);
						return;
					}

					resolve(response);
				},
				onfailure: (sourceResponse) => {
					if (sourceResponse === 'auth')
					{
						reject(
							Backend.makeResponse(xhr),
						);
					}
					else
					{
						reject(
							Backend.makeResponse(xhr, sourceResponse),
						);
					}
				},
			});

			xhr.send(fd);
		});
	}

	cache = new Cache.MemoryCache();

	getControllerUrl(): string
	{
		return this.cache.remember('controllerUrl', () => {
			const uri = new Uri('/bitrix/tools/landing/ajax.php');
			uri.setQueryParams({
				site: Loc.getMessage('SITE_ID') || undefined,
				type: this.getSitesType(),
			});
			return uri.toString();
		});
	}

	getSiteId(): number
	{
		return this.cache.remember('siteId', () => {
			const landing = Reflection.getClass('BX.Landing.Main');

			if (landing)
			{
				const instance = landing.getInstance();

				if (
					'options' in instance
					&& 'site_id' in instance.options
					&& !Type.isUndefined(instance.options.site_id)
				)
				{
					return instance.options.site_id;
				}
			}

			return -1;
		});
	}

	getLandingId(): number
	{
		return this.cache.remember('landingId', () => {
			const landing = Reflection.getClass('BX.Landing.Main');

			if (landing)
			{
				return landing.getInstance().id;
			}

			return -1;
		});
	}

	getSitesType(): 'PAGE' | 'STORE'
	{
		return this.cache.remember('siteType', () => {
			return Env.getInstance().getType();
		});
	}

	action(
		action: string,
		data: {[key: string]: any} = {},
		queryParams = {},
		uploadParams = {},
	): Promise<{[key: string]: any}, any>
	{
		queryParams.site_id = this.getSiteId();

		const requestBody = {
			sessid: Loc.getMessage('bitrix_sessid'),
			action: uploadParams.action || action.replace('Landing\\Block', 'Block'),
			data: {...data, uploadParams, lid: data.lid || this.getLandingId()},
		};

		const uri = new Uri(this.getControllerUrl());
		uri.setQueryParams({
			action: requestBody.action,
			...queryParams,
		});

		return Backend
			.request({
				url: uri.toString(),
				data: requestBody,
			})
			.then((response) => {
				if (
					requestBody.action === 'Block::updateNodes'
					|| requestBody.action === 'Block::removeCard'
					|| requestBody.action === 'Block::cloneCard'
					|| requestBody.action === 'Block::addCard'
					|| requestBody.action === 'Block::updateStyles'
				)
				{
					// eslint-disable-next-line
					BX.Landing.UI.Panel.StatusPanel.getInstance().update();
				}

				return response.result;
			})
			.catch((err) => {
				if (requestBody.action !== 'Block::getById')
				{
					const error = Type.isString(err) ? {type: 'error'} : err;
					err.action = requestBody.action;

					// eslint-disable-next-line
					BX.Landing.ErrorManager.getInstance().add(error);
				}

				return Promise.reject(err);
			});
	}

	batch(action, data = {}, queryParams = {}): Promise<{[key: string]: any}, any>
	{
		queryParams.site_id = this.getSiteId();

		const requestBody = {
			sessid: Loc.getMessage('bitrix_sessid'),
			action: action.replace('Landing\\Block', 'Block'),
			data: {lid: data.lid || this.getLandingId()},
			batch: data,
		};

		const uri = new Uri(this.getControllerUrl());
		uri.setQueryParams({
			action: requestBody.action,
			...queryParams,
		});

		return Backend
			.request({
				url: uri.toString(),
				data: requestBody,
			})
			.then((response) => {
				// eslint-disable-next-line
				BX.Landing.UI.Panel.StatusPanel.getInstance().update();
				return response;
			})
			.catch((err) => {
				if (requestBody.action !== 'Block::getById')
				{
					const error = Type.isString(err) ? {type: 'error'} : err;
					error.action = requestBody.action;
					// eslint-disable-next-line
					BX.Landing.ErrorManager.getInstance().add(error);
				}

				return Promise.reject(err);
			});
	}

	upload(file: File | Blob, uploadParams = {}): Promise<{[key: string]: any}, any>
	{
		const formData = new FormData();

		formData.append('sessid', Loc.getMessage('bitrix_sessid'));
		formData.append('picture', file, file.name);

		if ('block' in uploadParams)
		{
			formData.append('action', 'Block::uploadFile');
			formData.append('data[block]', uploadParams.block);
		}

		if ('lid' in uploadParams)
		{
			formData.set('action', 'Landing::uploadFile');
			formData.append('data[lid]', uploadParams.lid);
		}

		if ('id' in uploadParams)
		{
			formData.set('action', 'Site::uploadFile');
			formData.append('data[id]', uploadParams.id);
		}

		const uri = new Uri(this.getControllerUrl());
		uri.setQueryParams({
			action: formData.get('action'),
			site_id: this.getSiteId(),
		});

		if (uploadParams.context)
		{
			uri.setQueryParam('context', uploadParams.context);
		}

		return Backend
			.request({
				url: uri.toString(),
				data: formData,
			})
			.then((response) => response.result)
			.catch((err) => {
				const error = Type.isString(err) ? {type: 'error'} : err;
				error.action = 'Block::uploadFile';
				// eslint-disable-next-line
				BX.Landing.ErrorManager.getInstance().add(error);
				return Promise.reject(err);
			});
	}

	getSites({filter = {}} = {}): Promise<Array<Site>>
	{
		return this.cache.remember(`sites+${JSON.stringify(filter)}`, () => {
			return this
				.action('Site::getList', {
					params: {
						order: {ID: 'DESC'},
						filter: {TYPE: this.getSitesType(), ...filter},
					},
				})
				.then((response) => response);
		});
	}

	getLandings({siteId = []}: {siteId?: number | Array<number>} = {}): Promise<Array<Landing>>
	{
		const ids = Type.isArray(siteId) ? siteId : [siteId];
		const getBathItem = (id) => ({
			action: 'Landing::getList',
			data: {
				params: {
					filter: {SITE_ID: id},
					order: {ID: 'DESC'},
					get_preview: true,
					check_area: 1,
				},
			},
		});
		const prepareResponse = (response) => {
			return response.reduce((acc, item) => {
				return [...acc, ...item.result];
			}, []);
		};

		return this.cache.remember(`landings+${JSON.stringify(ids)}`, () => {
			if (ids.filter((id) => !Type.isNil(id)).length === 0)
			{
				return this.getSites()
					.then((sites) => {
						const data = sites.map((site) => getBathItem(site.ID));
						return this.batch('Landing::getList', data);
					})
					.then((response) => prepareResponse(response))
					.then((response) => {
						response.forEach((landing) => {
							this.cache.set(`landing+${landing.ID}`, Promise.resolve(landing));
						});
					});
			}

			const data = ids.map((id) => getBathItem(id));
			return this.batch('Landing::getList', data)
				.then((response) => prepareResponse(response))
				.then((response) => {
					response.forEach((landing) => {
						this.cache.set(`landing+${landing.ID}`, Promise.resolve(landing));
					});
					return response;
				});
		});
	}

	getLanding({landingId}: {landingId: string}): Promise<Landing>
	{
		return this.cache.remember(`landing+${landingId}`, () => {
			return this
				.action('Landing::getList', {
					params: {
						filter: {ID: landingId},
						get_preview: true,
					},
				})
				.then((response) => {
					if (Type.isArray(response) && response.length > 0)
					{
						return response[0];
					}

					return null;
				});
		});
	}

	getBlocks({landingId}: {landingId: string}): Promise<Array<Block>>
	{
		return this.cache.remember(`blocks+${landingId}`, () => {
			return this
				.action('Block::getList', {
					lid: landingId,
					params: {
						get_content: true,
						edit_mode: true,
					},
				})
				.then((blocks) => {
					blocks.forEach((block) => {
						this.cache.set(`block+${block.id}`, Promise.resolve(block));
					});

					return blocks;
				});
		});
	}

	getBlock({blockId}: {blockId: string}): Promise<Block>
	{
		return this.cache.remember(`blockId+${blockId}`, () => {
			return this.action('Block::getById', {
				block: blockId,
				params: {
					edit_mode: true,
				},
			});
		});
	}

	getTemplates({type = 'page', filter = {}} = {}): Promise<Array<Template>>
	{
		return this.cache.remember(`templates+${JSON.stringify(filter)}`, () => {
			return this
				.action('Demos::getPageList', {type, filter})
				.then((response) => Object.values(response));
		});
	}

	getDynamicTemplates(sourceId: string = ''): Promise<Array<Template>>
	{
		return this.cache.remember(`dynamicTemplates:${sourceId}`, () => {
			return this.getTemplates({filter: {section: `dynamic${sourceId ? `:${sourceId}` : ''}`}});
		});
	}

	createPage(options: CreatePageOptions = {})
	{
		const envOptions = Env.getInstance().getOptions();
		const {
			title,
			siteId = envOptions.site_id,
			siteType = envOptions.params.type,
			code = Text.getRandom(16),
			blockId,
			menuCode,
			folderId,
		} = options;

		const templateCode = (() => {
			const {theme} = envOptions;
			if (
				Type.isPlainObject(theme)
				&& Type.isArray(theme.newPageTemplate)
				&& Type.isStringFilled(theme.newPageTemplate[0])
			)
			{
				return theme.newPageTemplate[0];
			}

			return 'empty';
		})();

		const requestBody = {
			siteId,
			code: templateCode,
			fields: {
				TITLE: title,
				CODE: code,
				//@todo: refactor
				ADD_IN_MENU: (siteType === 'KNOWLEDGE' || siteType === 'GROUP') ? 'Y' : 'N'
			},
		};

		if (Type.isNumber(blockId) && Type.isString(menuCode))
		{
			requestBody.fields.BLOCK_ID = blockId;
			requestBody.fields.MENU_CODE = menuCode;
		}

		if (Type.isNumber(folderId))
		{
			requestBody.fields.FOLDER_ID = folderId;
		}

		return this.action('Landing::addByTemplate', requestBody);
	}
}