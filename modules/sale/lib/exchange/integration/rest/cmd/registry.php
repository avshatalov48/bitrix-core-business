<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd;


class Registry
{
	const CRM_ACTIVITY_ADD_NAME = 'CRM_ACTIVITY_ADD';
	const CRM_DEAL_ADD_NAME = 'CRM_DEAL_ADD';
	const CRM_DEAL_CONTACT_ITEMS_SET_NAME = 'CRM_DEAL_CONTACT_ITEMS_SET';
	const CRM_DEAL_CONTACT_ITEMS_GET_NAME = 'CRM_DEAL_CONTACT_ITEMS_GET';
	const CRM_COMPANY_LIST_NAME = 'CRM_COMPANY_LIST';
	const CRM_COMPANY_ADD_NAME = 'CRM_COMPANY_ADD';
	const CRM_CONTACT_LIST_NAME = 'CRM_CONTACT_LIST';
	const CRM_CONTACT_ADD_NAME = 'CRM_CONTACT_ADD';
	const CRM_TIMELINE_ONRECEIVE_NAME = 'CRM_TIMELINE_ONRECEIVE';
	const APP_OPTIONS_ADD_NAME = 'APP_OPTIONS_ADD';
	const APP_PLACEMENT_BIND_NAME = 'APP_PLACEMENT_BIND';
	const APP_PLACEMENT_UNBIND_NAME = 'APP_PLACEMENT_UNBIND';
	const SALE_INTEGRATION_STATISTIC_PROVIDER_LIST_NAME = 'SALE_INTEGRATION_STATISTIC_PROVIDER_LIST';
	const SALE_INTEGRATION_STATISTIC_PROVIDER_ADD_NAME = 'SALE_INTEGRATION_STATISTIC_PROVIDER_ADD';
	const SALE_INTEGRATION_STATISTIC_MODIFY_NAME = 'SALE_INTEGRATION_STATISTIC_MODIFY';

	public static function getRegistry()
	{
		return [
			Registry::CRM_ACTIVITY_ADD_NAME => 'crm.activity.add',
			Registry::CRM_DEAL_ADD_NAME => 'crm.deal.add',
			Registry::CRM_DEAL_CONTACT_ITEMS_SET_NAME => 'crm.deal.contact.items.set',
			Registry::CRM_DEAL_CONTACT_ITEMS_GET_NAME => 'crm.deal.contact.items.get',
			Registry::CRM_COMPANY_LIST_NAME => 'crm.company.list',
			Registry::CRM_COMPANY_ADD_NAME => 'crm.company.add',
			Registry::CRM_CONTACT_LIST_NAME => 'crm.contact.list',
			Registry::CRM_CONTACT_ADD_NAME => 'crm.contact.add',
			Registry::CRM_TIMELINE_ONRECEIVE_NAME => 'crm.api.timeline.onreceive',
			Registry::APP_OPTIONS_ADD_NAME => 'app.option.set',
			Registry::APP_PLACEMENT_BIND_NAME => 'placement.bind',
			Registry::APP_PLACEMENT_UNBIND_NAME => 'placement.unbind',
			Registry::SALE_INTEGRATION_STATISTIC_PROVIDER_LIST_NAME => 'sale.integration.statisticprovider.list',
			Registry::SALE_INTEGRATION_STATISTIC_PROVIDER_ADD_NAME => 'sale.integration.statisticprovider.add',
			Registry::SALE_INTEGRATION_STATISTIC_MODIFY_NAME => 'sale.integration.statistic.modify'
		];
	}
}