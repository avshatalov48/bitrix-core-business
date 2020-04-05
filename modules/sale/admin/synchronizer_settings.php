<?
/*
 * TODO: переделать на мастер.
 * Данная страница как врменное решение. Настройку должен выполнять мастер.
 * */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

$inst = \Bitrix\Main\Application::getInstance();
$context = $inst->getContext();
$request = $context->getRequest();
$server = $context->getServer();
$lang = $context->getLanguage();
$documentRoot = \Bitrix\Main\Application::getDocumentRoot();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$error = [];
$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();

$connectorStatus = false;

if ($request->isPost()
	//&& ($request->get('save') !== null || $request->get('apply') !== null)
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid()
)
{
	if(
		$request->get('CONFIG_EXTERNAL_SERVICE_SCHEME') !== null &&
		$request->get('CONFIG_EXTERNAL_SERVICE_URL') !== null &&
		$request->get('CONFIG_REFRESH_TOKEN') !== null
	)
	{
		$manager->setSchemeServiceUrl($request->get('CONFIG_EXTERNAL_SERVICE_SCHEME'));
		$manager->setServiceUrl($request->get('CONFIG_EXTERNAL_SERVICE_URL'));
		$manager->setRefreshToken($request->get('CONFIG_REFRESH_TOKEN'));
	}

	if($request->get('synchronizerIsAction') == 'Y')
	{
		// do nothing
	}
    elseif($request->get('synchronizerIsSettings') == 'Y')
	{
		if($request->get('DEFAULT_SITE_ID') !== null)
			$manager->setDefaultSiteId($request->get('DEFAULT_SITE_ID'));
		if($request->get('DEFAULT_PAY_SYSTEM_ID') !== null)
			$manager->setDefaultPaySystemId($request->get('DEFAULT_PAY_SYSTEM_ID'));
		if($request->get('DEFAULT_DELIVERY_SYSTEM_ID') !== null)
			$manager->setDefaultDeliverySystemId($request->get('DEFAULT_DELIVERY_SYSTEM_ID'));
		if($request->get('DEFAULT_PERSON_TYPE') !== null)
			$manager->setDefaultPersonTypeId($request->get('DEFAULT_PERSON_TYPE'));
		if($request->get('DEFAULT_ORDER_STATUS') !== null)
			$manager->setDefaultOrderStatusId($request->get('DEFAULT_ORDER_STATUS'));
		if($request->get('DEFAULT_DELIVERY_STATUS') !== null)
			$manager->setDefaultDeliveryStatusId($request->get('DEFAULT_DELIVERY_STATUS'));

		$manager->marked($request->get('IS_MARKED')=='Y'?'Y':'N');


		$internal = getInternalFields();
		if(count($internal)>0)
		{
			foreach ($internal as $name=>$list)
			{
				foreach ($list as $fields)
				{
					$value = '';
					if($request->get($name.'_'.$fields['ID']) !== null)
					{
						$value = $request->get($name.'_'.$fields['ID']);

						//TODO: переделать на вызовы не Internals
						if($name == 'PERSON_TYPE')
						{
							\Bitrix\Sale\Internals\PersonTypeTable::update($fields['ID'], ['XML_ID'=>$value]);
						}
                        elseif($name == 'PROPERTIES')
						{
							\Bitrix\Sale\Internals\OrderPropsTable::update($fields['ID'], ['XML_ID'=>$value]);
						}
                        elseif($name == 'PAY_SYSTEMS')
						{
							$r=\Bitrix\Sale\PaySystem\Manager::update($fields['ID'], ['XML_ID'=>$value]);
							$res[] = $value;
						}
                        elseif($name == 'DELIVERY_SYSTEMS')
						{
							\Bitrix\Sale\Delivery\Services\Manager::update($fields['ID'], ['XML_ID'=>$value]);
						}
                        elseif($name == 'ORDER_STATUSES')
						{
							\Bitrix\Sale\Internals\StatusTable::update($fields['ID'], ['XML_ID'=>$value]);
						}
                        elseif($name == 'DELIVERY_STATUSES')
						{
							\Bitrix\Sale\Internals\StatusTable::update($fields['ID'], ['XML_ID'=>$value]);
						}
                        elseif($name == 'SITES')
						{
							$manager->setTradePlatformsXmlId($fields['ID'], $value);
						}
					}
				}
			}
		}

		if($manager->isActive() !== ($request->get('IS_ACTIVE')=='Y'))
		    $connectorStatus = $request->get('IS_ACTIVE')=='Y' ? 'activate':'deactivate';
	}
}


$errorOAuth = [];

$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();
$instance = $manager::getInstance();
$internal = getInternalFields();

$accessToken = $manager->getAccessToken();

if($accessToken<>'')
{

	$r = $instance->getClient()->checkAccessToken($manager->getAccessToken());
	if($r->isSuccess())
	{
		// do nothing
	}
	else
	{
		if($r->getErrorMessages()[0]=='The access token provided has expired.')
		{
			$sync = new \Bitrix\Sale\Rest\Synchronization\Synchronizer();
			if($sync->refreshToken()->isSuccess())
				LocalRedirect($APPLICATION->GetCurPageParam());
			else
				$errorOAuth[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ACCESS_TOKEN_PROVIDED_HAS_EXPIRED');
		}
		else
		{
			$errorOAuth = $r->getErrorMessages();
			$errorOAuth[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ACCESS_TOKEN_PROVIDED_IS_INVALID');
		}
	}
}
else
{
	$sync = new \Bitrix\Sale\Rest\Synchronization\Synchronizer();
	if($sync->refreshToken()->isSuccess())
		LocalRedirect($APPLICATION->GetCurPageParam());
	else
		$errorOAuth[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ACCESS_TOKEN_PROVIDED_HAS_EXPIRED');
}

if(isConnected() === false)
{
	?>
    <div class="crm-admin-wrap">
        <?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_DESCRIPTION')?>
        <div class="crm-admin-set" id="id_new_crm_reg_form">
            <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1_do_create_link">
                <div class="crm-admin-set-title"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_TITLE')?></div>
                <table class="crm-admin-set-content-table" cellspacing="0">
                    <tr>
                        <td class="crm-admin-set-left"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_ADDRES')?></td>
                        <td class="crm-admin-set-right">
                            <select class="crm-admin-set-select" name="CONFIG_EXTERNAL_SERVICE_SCHEME" title="<?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_ADDRES')?>">
                                <option value="https"<?= (($instance->getSchemeServiceUrl()=="https") ? " selected" : "")?>>https</option>
                                <option value="http"<?= (($instance->getSchemeServiceUrl()=="http") ? " selected" : "")?>>http</option>
                            </select><span class="crm-admin-set-text">&nbsp;://&nbsp;</span><input type="text" class="crm-admin-set-input" name="CONFIG_EXTERNAL_SERVICE_URL" value="<?= htmlspecialcharsbx($instance->getServiceUrl()) ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="crm-admin-set-left"><nobr><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_ACCESS_TOKEN')?></nobr></td>
                        <td class="crm-admin-set-right"><input class="crm-admin-set-input" type="text" name="CONFIG_REFRESH_TOKEN" value="<?= htmlspecialcharsbx($instance->getRefreshToken()) ?>"/></td>
                    </tr>
                </table>
                <div class="crm-admin-set-button">
                    <a class="adm-btn adm-btn-green" href='javascript:document.forms["form1_do_create_link"].submit();'><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_APPLY')?></a>&nbsp;&nbsp;
                    <a class="adm-btn" href="javascript:location.href='<?=$APPLICATION->GetCurPage()?>'"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_CANCEL')?></a>
                </div>
				<?=bitrix_sessid_post();?>
                <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
                <input type="hidden" name="synchronizerIsAction" value="Y">
            </form>
        </div>
    </div>
	<?
}
else
{
	if(empty($errorOAuth))
	{
		//region Rest action
		$errorConnection = [];

		$cmd = [
			'getdefaultsettings'=>'sale.synchronizer.getdefaultsettings',
			'persontype'=>'sale.persontype.list',
			'property'=>'sale.property.list?'.http_build_query(['filter'=>['!type'=>['file','location', 'enum']], 'order'=>['personTypeId'=>'asc']]),
			'paysystem'=>'sale.paysystem.list?'.http_build_query(['filter'=>['!action_file'=>'inner', 'entity_registry_type'=>'order']]),
			'deliveryservices'=>'sale.deliveryservices.getactivelist',
			'statuslang'=>'sale.statuslang.list?'.http_build_query(['select'=>['statusId', 'name'], 'filter'=>['lid'=>LANG]]),
			'status'=>'sale.status.list?'.http_build_query(['select'=>['id', 'xmlId','type']]),
			'tradeplatform'=>'sale.tradeplatform.list?'.http_build_query(['select'=>['id', 'xmlId','name'], 'filter'=>['active'=>'y']])
		];

		$endPoint = ((CMain::IsHTTPS()) ? "https://" : "http://").$server->getHttpHost().$manager::END_POINT;

		if($connectorStatus)
		{
			$cmd['eventbindordersaved'] =  ($connectorStatus == 'activate'?'event.bind':'event.unbind').'?'.http_build_query(['auth_type'=>0,'event'=>'OnSaleOrderSaved', 'handler'=>$endPoint]);
			$cmd['eventbindorderdelete'] = ($connectorStatus == 'activate'?'event.bind':'event.unbind').'?'.http_build_query(['auth_type'=>0,'event'=>'OnSaleBeforeOrderDelete', 'handler'=>$endPoint]);
		}
		else
        {
			$cmd['event'] = 'event.get';
        }

		$r = $instance->getClient()->call('batch',
			[
				'auth'=>$accessToken,
				'cmd'=>$cmd
			]);

		if($r->isSuccess())
        {
			$batchResult = (isset($r->getData()['DATA']['result']['result'])) ? $r->getData()['DATA']['result']['result']:[];
			$batchResultError = (isset($r->getData()['DATA']['result']['result_error'])) ? $r->getData()['DATA']['result']['result_error']:[];

			if($connectorStatus)
			{
				if($connectorStatus == 'activate')
				{
					if(
						(
							(isset($batchResult['eventbindordersaved']) && $batchResult['eventbindordersaved'] == 1) || // если регистрация выполнена успешно
							(isset($batchResultError['eventbindordersaved']['error_description']) && $batchResultError['eventbindordersaved']['error_description'] == 'Unable to set event handler: Handler already binded') // если регистрация была выполнена успешно ранее
						) 
						&&
						(
							(isset($batchResult['eventbindorderdelete']) && $batchResult['eventbindorderdelete'] == 1) ||
							(isset($batchResultError['eventbindorderdelete']['error_description']) && $batchResultError['eventbindorderdelete']['error_description'] == 'Unable to set event handler: Handler already binded')
						)
					)
					{
						if($manager->checkDefaultSettings()->isSuccess())
                        {
							RegisterModuleDependences("sale", "OnSaleOrderSaved", 'sale', 'Bitrix\Sale\Rest\Synchronization\Synchronizer', "onSaleOrderSaved");
							RegisterModuleDependences("sale", "OnSaleBeforeOrderDelete", 'sale', 'Bitrix\Sale\Rest\Synchronization\Synchronizer', "onSaleBeforeOrderDelete");

							$manager->activate();
                        }
					}
					else
						$error[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_BINDING_EVENTS_ERROR');
				}
                elseif ($connectorStatus == 'deactivate')
				{
					UnRegisterModuleDependences("sale", "OnSaleOrderSaved", 'sale', 'Bitrix\Sale\Rest\Synchronization\Synchronizer', "onSaleOrderSaved");
					UnRegisterModuleDependences("sale", "OnSaleBeforeOrderDelete", 'sale', 'Bitrix\Sale\Rest\Synchronization\Synchronizer', "onSaleBeforeOrderDelete");

					$manager->deactivate();

				    if(
						(isset($batchResult['eventbindordersaved']) && $batchResult['eventbindordersaved']['count'] == 1) &&
						(isset($batchResult['eventbindorderdelete']) && $batchResult['eventbindorderdelete']['count'] == 1)
					)
					{

					}
					else
						$error[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_UNBINDING_EVENTS_ERROR');
				}
			}

			if(isset($batchResult['event']))
            {
				$checkEvents = [];
                foreach ($batchResult['event'] as $event)
                {
                    if(
                            $event['event'] == strtoupper('OnSaleOrderSaved') &&
							$event['handler'] == $endPoint
                    )
                    {
						$checkEvents[] = 'OnSaleOrderSaved';
                    }

					if(
						$event['event'] == strtoupper('OnSaleBeforeOrderDelete') &&
						$event['handler'] == $endPoint
					)
					{
						$checkEvents[] = 'OnSaleBeforeOrderDelete';
                    }
				}

				if($manager->isActive())
                {
					if(in_array('OnSaleOrderSaved', $checkEvents) == false ||
						in_array('OnSaleBeforeOrderDelete', $checkEvents) == false
					)
					{
						$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_EVENTS_NOT_FOUND_ERROR');
					}
                }
            }

			if(isset($batchResult['getdefaultsettings']['synchronizer']) && count($batchResult['getdefaultsettings']['synchronizer'])>0)
			{
				$row = $batchResult['getdefaultsettings']['synchronizer'];

				if($row['isActive']!='Y')
					$error[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_NOT_DONE');

				$catalogs = $row['catalogs'];
				if(count($catalogs)>0)
					foreach ($catalogs as $k=>$catalog)
						$catalogs[$k] = ['ID'=>$catalog['id'], 'NAME'=>$catalog['name']];

				$external['SYNCHRONIZER'] = [
					'IS_ACTIVE'=>$row['isActive'],
					'SITE'=>['ID'=>$row['site']['id'], 'NAME'=>$row['site']['name']],
					'PAY_SYSTEM'=>['ID'=>$row['paySystem']['id'], 'NAME'=>$row['paySystem']['name']],
					'DELIVERY_SYSTEM'=>['ID'=>$row['deliverySystem']['id'], 'NAME'=>$row['deliverySystem']['name']],
					'PERSON_TYPE'=>['ID'=>$row['personType']['id'], 'NAME'=>$row['personType']['name']],
					'ORDER_STATUS'=>['ID'=>$row['orderStatus']['id'], 'NAME'=>$row['orderStatus']['name']],
					'DELIVERY_STATUS'=>['ID'=>$row['deliveryStatus']['id'], 'NAME'=>$row['deliveryStatus']['name']],
					'CATALOGS'=>$catalogs,
				];
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_ERROR');

			if(isset($batchResult['persontype']['personTypes']) && count($batchResult['persontype']['personTypes'])>0)
            {
                foreach($batchResult['persontype']['personTypes'] as $row)
                    if($row['xmlId']<>'')
                        $external['PERSON_TYPE'][] = ['ID'=>$row['id'], 'NAME'=>$row['name'], 'XML_ID'=>$row['xmlId']];
            }
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_PERSON_TYPE_ERROR');

			if(isset($batchResult['property']['properties']) && count($batchResult['property']['properties'])>0)
			{
                foreach($batchResult['property']['properties'] as $row)
                {
					if($row['xmlId']<>'')
					{
						$propertyPersonName = '';
					    $propertyPersonTypes = isset($external['PERSON_TYPE']) ? $external['PERSON_TYPE']:null;
						if($propertyPersonTypes !== null)
						{
							foreach ($propertyPersonTypes as $propertyPersonType)
							{
								if($propertyPersonType['ID'] == $row['personTypeId'])
								{
									$propertyPersonName = $propertyPersonType['NAME'];
								}
                            }
						}

						$external['PROPERTIES'][] = ['ID'=>$row['id'], 'NAME'=>($propertyPersonName<>'')? $propertyPersonName.' '.$row['name']:$row['name'], 'XML_ID'=>$row['xmlId']];
					}
                }
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_PROPERTIES_ERROR');

			if(isset($batchResult['paysystem']) && count($batchResult['paysystem'])>0)
			{
				foreach($batchResult['paysystem'] as $row)
				{
					if($row['XML_ID']<>'')
					{
						$external['PAY_SYSTEMS'][] = ['ID'=>$row['ID'], 'NAME'=>$row['NAME'], 'XML_ID'=>$row['XML_ID']];
					}
				}
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_PAY_SYSTEMS_ERROR');

			if(isset($batchResult['deliveryservices']['deliveryServices']) && count($batchResult['deliveryservices']['deliveryServices'])>0)
			{
				foreach($batchResult['deliveryservices']['deliveryServices'] as $row)
					if($row['xmlId']<>'')
						$external['DELIVERY_SYSTEMS'][] = ['ID'=>$row['id'], 'NAME'=>$row['name'], 'XML_ID'=>$row['xmlId']];
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_DELIVERY_SERVICES_ERROR');

			if(isset($batchResult['statuslang']['statusLangs']) && count($batchResult['statuslang']['statusLangs'])>0)
			{
				foreach($batchResult['statuslang']['statusLangs'] as $row)
					$external['STATUS_LANGS'][$row['statusId']] = ['STATUS_ID'=>$row['statusId'], 'NAME'=>$row['name']];
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_STATUS_LANGS_ERROR');

			if(isset($batchResult['status']['statuses']) && count($batchResult['status']['statuses'])>0)
			{
				foreach($batchResult['status']['statuses'] as $row)
					if($row['xmlId']<>'')
						$external['STATUSES'][] = ['ID'=>$row['id'], 'NAME'=>$external['STATUS_LANGS'][$row['id']]['NAME'], 'XML_ID'=>$row['xmlId'], 'TYPE'=>$row['type']];
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_STATUS_ERROR');

			if(isset($batchResult['tradeplatform']['tradePlatforms']))
			{
				foreach($batchResult['tradeplatform']['tradePlatforms'] as $row)
					if($row['xmlId']<>'')
						$external['TRADE_PLATFORMS'][] = ['ID'=>$row['id'], 'NAME'=>$row['name'], 'XML_ID'=>$row['xmlId']];
			}
			else
				$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_SETTINGS_TRADE_PLATFORMS_ERROR');
        }
		else
			$errorConnection[] = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_REQUEST_EXTERNAL_ERROR');

		if(count($errorConnection)>0)
			$error = array_merge($error, $errorConnection);
		//endregion
	}
	else
	{
		$error = array_merge($error, $errorOAuth);
	}

	if(count($error)<=0)
	{
		if ($manager->checkDefaultSettings()->isSuccess())
		{
			if($manager->isActive())
			{
				$messageType = 'OK';
				$messageText = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ACTIVE');
				$messageDetails = '';
			}
			else
			{
				$messageType = "ERROR";
				$messageText = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_INACTIVE');
				$messageDetails = '';
			}
		}
		else
		{
			$messageType = "ERROR";
			$messageText = \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_INACTIVE');
			$messageDetails = "<span style=\"font-style: italic;\">".\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_NEED_SETTINGS_REQUIRED_PARAMS')."</span>";
		}


		CAdminMessage::ShowMessage(array(
			"MESSAGE" => $messageText,
			"TYPE" => $messageType,
			"DETAILS" => $messageDetails,
			"HTML" => true
		));
	}

	if(count($error)>0)
	{
		$adminMessage = new CAdminMessage(
			array("MESSAGE" => implode("<br>\n", $error), "TYPE" => "ERROR")
		);
		echo $adminMessage->Show();
	}

	?>
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="sale_synchronizer_settings">
		<?echo GetFilterHiddens("filter_");?>
        <input type="hidden" name="lang" value="<?echo LANG ?>">
        <input type="hidden" name="synchronizerIsSettings" value="Y">
		<?=bitrix_sessid_post();

		$aTabs = array(
			array("DIV" => "edit1", "TAB" => \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_TABS_TITLE'), "ICON" => "sale")
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		$tabControl->Begin();
		$tabControl->BeginNextTab();

		// region Local settings
		$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
		$arSites = array("" => GetMessage("SALE_1C_ALL_SITES"));
		while ($arSite = $rsSite->GetNext())
		{
			$arSites[$arSite["LID"]] = $arSite["NAME"];
		}

		$arStatuses = Array("" => GetMessage("SALE_1C_NO"));
		$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
		while ($arStatus = $dbStatus->Fetch())
		{
			$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
		}

		$arPaySystems = array("" => GetMessage("SALE_1C_NO"));
		$dbPaySystems = CSalePaySystem::GetList(array("SORT"=>"ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
		$arPaySystemsWithoutInner = Array("" => GetMessage("SALE_1C_NO"));
		while ($arPaySystem = $dbPaySystems->Fetch())
		{
			$arPaySystems[$arPaySystem["ID"]] = "[".$arPaySystem["ID"]."] ".$arPaySystem["NAME"];

			if($arPaySystem["ID"] != Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId())
			{
				$arPaySystemsWithoutInner[$arPaySystem["ID"]] = "[".$arPaySystem["ID"]."] ".$arPaySystem["NAME"];
			}
		}

		$shipmentServices = array("" => GetMessage("SALE_1C_NO"));
		$deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
		foreach($deliveryList as $shipmentService)
		{
			$shipmentServices[$shipmentService["ID"]] = "[".$shipmentService["ID"]."] ".$shipmentService["NAME"];
		}

		$catalogList = \Bitrix\Catalog\CatalogIblockTable::getList([
			'select' => ['IBLOCK_ID', 'IBLOCK.NAME'],
			'filter' => ['=IBLOCK.ACTIVE'=>'Y']
		])->fetchAll();
		// endregion
		?>

        <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_CONNECTED_SETTINGS')?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_CONNECTED_ACTIVE')?></td><td align="left"><input type="checkbox" <?=$instance->isActive()?'checked':''?> name="IS_ACTIVE" value="Y"></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_CONNECTED_ADDRES')?></td>
            <td align="left">
                <select name="CONFIG_EXTERNAL_SERVICE_SCHEME">
                    <option value="https"<?= (($instance->getSchemeServiceUrl()=="https") ? " selected" : "")?>>https</option>
                    <option value="http"<?= (($instance->getSchemeServiceUrl()=="http") ? " selected" : "")?>>http</option>
                </select><span >&nbsp;://&nbsp;</span><input type="text" name="CONFIG_EXTERNAL_SERVICE_URL" value="<?=$instance->getServiceUrl()?>"/>
            </td>
        </tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ACCESS_TOKEN')?></td><td align="left"><input name="CONFIG_REFRESH_TOKEN" type='text' size=80 value='<?=$instance->getRefreshToken()?>'/></td></tr>

        <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_GENERAL_SETTINGS')?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_SITE_DEFAULT')?></td><td align="left"><select name="DEFAULT_SITE_ID"><?foreach($arSites as $id=>$name):?><option <?=$instance->getDefaultSiteId()==$id?'selected':''?> value="<?=$id?>"><?=$name?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_PS_DEFAULT')?></td><td align="left"><select name="DEFAULT_PAY_SYSTEM_ID"><?foreach($arPaySystemsWithoutInner as $id=>$name):?><option <?=$instance->getDefaultPaySystemId()==$id?'selected':''?> value="<?=$id?>"><?=$name?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_DS_DEFAULT')?></td><td align="left"><select name="DEFAULT_DELIVERY_SYSTEM_ID"><?foreach($shipmentServices as $id=>$name):?><option <?=$instance->getDefaultDeliverySystemId()==$id?'selected':''?> value="<?=$id?>"><?=$name?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_PT_DEFAULT')?></td><td align="left"><select name="DEFAULT_PERSON_TYPE"><option></option><?foreach($internal['PERSON_TYPE'] as $row):?><option <?=$instance->getDefaultPersonTypeId()==$row['ID']?'selected':''?> value="<?=$row['ID']?>"><?='['.$row['ID'].'] '.$row['NAME']?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_ORDER_STATUS_DEFAULT')?></td><td align="left"><select name="DEFAULT_ORDER_STATUS"><option></option><?foreach($internal['ORDER_STATUSES'] as $row):?><option <?=$instance->getDefaultOrderStatusId()==$row['ID']?'selected':''?> value="<?=$row['ID']?>"><?='['.$row['ID'].'] '.$row['NAME']?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_DELIVERY_STATUS_DEFAULT')?></td><td align="left"><select name="DEFAULT_DELIVERY_STATUS"><option></option><?foreach($internal['DELIVERY_STATUSES'] as $row):?><option <?=$instance->getDefaultDeliveryStatusId()==$row['ID']?'selected':''?> value="<?=$row['ID']?>"><?='['.$row['ID'].'] '.$row['NAME']?></option><?endforeach;?></td></tr>
        <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_CATALOGS')?></td><td align="left"><select name="" multiple size="5" disabled><?foreach($catalogList as $catalog){?><option selected><?='['.$catalog['IBLOCK_ID'].'] '.$catalog['CATALOG_CATALOG_IBLOCK_IBLOCK_NAME']?></option><?}?></select></td></tr>

        <tr><td align="right" width="40%"><label for="use-"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MARKED')?></td><td align="left"><input type="checkbox" <?=$instance->isMarked()?'checked':''?> name="IS_MARKED" value="Y"></td></tr>

		<?if(empty($errorOAuth) && empty($errorConnection)):?>

            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_TITLE')?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_ACTIVE');?></label></td><td align="left"><?=$external['SYNCHRONIZER']['IS_ACTIVE']=='Y'?'<font color="green">'.\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_ACTIVE_Y').'</font>':'<font color="red">'.\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_ACTIVE_N').'</font>'?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_SITE_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['SITE']['ID'].'] '.$external['SYNCHRONIZER']['SITE']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_PS_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['PAY_SYSTEM']['ID'].'] '.$external['SYNCHRONIZER']['PAY_SYSTEM']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_DS_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['DELIVERY_SYSTEM']['ID'].'] '.$external['SYNCHRONIZER']['DELIVERY_SYSTEM']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_PT_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['PERSON_TYPE']['ID'].'] '.$external['SYNCHRONIZER']['PERSON_TYPE']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_ORDER_STATUS_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['ORDER_STATUS']['ID'].'] '.$external['SYNCHRONIZER']['ORDER_STATUS']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_DELIVERY_STATUS_DEFAULT');?></label></td><td align="left"><?='['.$external['SYNCHRONIZER']['DELIVERY_STATUS']['ID'].'] '.$external['SYNCHRONIZER']['DELIVERY_STATUS']['NAME'];?></td></tr>
            <tr><td align="right" width="40%"><label for="use-"><span class="required">*</span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_EXTERNAL_SETTINGS_CATALOGS');?></label></td><td align="left"><select name="" multiple size="5" disabled><?foreach($external['SYNCHRONIZER']['CATALOGS'] as $tradeCatalog):?><option selected><?='['.$tradeCatalog['ID'].'] '.$tradeCatalog['NAME']?></option><?endforeach;?></select></td></tr>

            <tr><td colspan="2" align="center"><?echo BeginNote('align="center"');?><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_NOTE')?><?echo EndNote();?></td></tr>

            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_PT')?></td></tr>
            <tr><?showSelect($internal['PERSON_TYPE'], $external['PERSON_TYPE'], 'PERSON_TYPE')?></tr>
            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_PS')?></td></tr>
            <tr><?showSelect($internal['PAY_SYSTEMS'], $external['PAY_SYSTEMS'], 'PAY_SYSTEMS')?></tr>
            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_SD')?></td></tr>
            <tr><?showSelect($internal['DELIVERY_SYSTEMS'], $external['DELIVERY_SYSTEMS'], 'DELIVERY_SYSTEMS')?></tr>
            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_P')?></td></tr>
            <tr><td colspan="2" align="center"><?echo BeginNote('align="center"');?><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_NOTE1')?><?echo EndNote();?></td></tr>

            <tr><td colspan="2">
					<?
					$aTabs2 = Array();

					foreach($internal['PERSON_TYPE'] as $type)
					{
						$aTabs2[] = Array("DIV"=>"reminder".$type["ID"], "TAB" => "[".$type["ID"]."] ".htmlspecialcharsbx($type["NAME"]), "TITLE" => "[".htmlspecialcharsbx($type["ID"])."] ".htmlspecialcharsbx($type["NAME"]));
					}
					$tabControl2 = new CAdminViewTabControl("tabControl2", $aTabs2);
					$tabControl2->Begin();


					$r = \CSaleOrderPropsGroup::GetList([], $filter);
					while ($l = $r->fetch())
						$groupsList[] = $l;

					foreach($internal['PERSON_TYPE'] as $type)
					{
						$tabControl2->BeginNextTab();

						?><table cellspacing="5" cellpadding="0" border="0" width="100%" align="center">
						<?
						foreach($groupsList as $group)
						{
							if($group['PERSON_TYPE_ID'] == $type['ID'])
							{
								?><tr class="heading">
                                <td colspan="2"><?=$group['NAME']?></td>
                                </tr><?
							}

							$propertiesPG = [];
							foreach($internal['PROPERTIES'] as $property)
							{
								if($property['PERSON_TYPE_ID'] == $type['ID'] &&
									$property['PROPS_GROUP_ID'] == $group['ID'])
								{
									$propertiesPG[] = $property;
								}
							}
							showSelect($propertiesPG, $external['PROPERTIES'], 'PROPERTIES');
						}
						?></table><?
					}
					$tabControl2->End();
					?>

                </td></tr>
            <tr class="heading"><td colspan="2" align="center"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING1')?></td></tr>
            <tr><td colspan="2">
					<?
					$aTabs1 = Array();

					if(!isset($internal['ORDER_STATUSES']))
						$internal['ORDER_STATUSES'] = [];

					if(!isset($internal['DELIVERY_STATUSES']))
						$internal['DELIVERY_STATUSES'] = [];

					$statuses = array_merge($internal['ORDER_STATUSES'], $internal['DELIVERY_STATUSES']);

					foreach($statuses as $status)
					{
						$statusTypes[$status['TYPE']] = ['ID'=>$status['TYPE'], 'NAME'=>$status['TYPE']=='O'? \Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_ORDER_STATUS'):\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING_DELIVERY_STATUS')];
					}

					foreach($statusTypes as $type)
					{
						$aTabs1[] = Array("DIV"=>"reminder".$type["ID"], "TAB" => "[".$type["ID"]."] ".htmlspecialcharsbx($type["NAME"]), "TITLE" => "[".htmlspecialcharsbx($type["ID"])."] ".htmlspecialcharsbx($type["NAME"]));
					}
					$tabControl1 = new CAdminViewTabControl("tabControl1", $aTabs1);
					$tabControl1->Begin();

					foreach($statusTypes as $type)
					{
						$tabControl1->BeginNextTab();
						?><table cellspacing="5" cellpadding="0" border="0" width="100%" align="center"><?

						showSelect(
                                array_filter($statuses, function($status) use ($type)
                                {
                                    return $status['TYPE'] == $type['ID'];
                                }),
							    array_filter($external['STATUSES'], function($status) use ($type)
                                {
                                    return $status['TYPE'] == $type['ID'];
                                }),
                                ($type['ID']=='O'? 'ORDER_STATUSES':'DELIVERY_STATUSES'));
						?></table><?
					}
					$tabControl1->End();
					?>
                </td></tr>
            <tr class="heading"><td colspan="2" align="center"><span class="required"></span><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_SYNCHRONIZER_MATCHING2')?></td></tr>
            <tr><?showSelect($internal['SITES'], $external['TRADE_PLATFORMS'], 'SITES')?></tr>
		<?endif;

		$tabControl->EndTab();
		$tabControl->End();

		$tabControl->Buttons(
			array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_synchronizer_settings.php?lang=".LANG.GetFilterParams("filter_")
			)
		);
		?>
    </form>
	<?
}

function showSelect($internal, $external, $name)
{
	foreach($internal as $internalFields):?>
        <tr>
            <td align="right" width="40%"><label for="use-<?=$internalFields["ID"]?>">[<?=$internalFields['ID']?>] <?=$internalFields['NAME']?>:</label></td>
            <!--<td align="left">[<?=$internalFields['ID']?>] <?=$internalFields['NAME']?></td>-->
            <td align="left"><select name="<?=$name.'_'.$internalFields['ID']?>"><option></option><?
					foreach($external as $externalFields)
					{
						?><option <?=$externalFields['XML_ID']<>''? 'value="'.$externalFields['XML_ID'].'"':''?> <?=$externalFields['XML_ID']<>'' && $externalFields['XML_ID']==$internalFields['XML_ID'] ? 'selected':''?> >[<?=$externalFields['ID']?>] <?=$externalFields['NAME']?></option><?
					}
					?></select></td>
        </tr>
	<?endforeach;
}

function getInternalFields()
{
	$internal = [];
	$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();

	foreach(\Bitrix\Sale\PersonType::getList(['select'=>['ID', 'NAME', 'XML_ID']]) as $row)
		$internal['PERSON_TYPE'][] = $row;

	foreach(\Bitrix\Sale\Property::getList(['select'=>['ID', 'NAME', 'XML_ID', 'PROPS_GROUP_ID', 'PERSON_TYPE_ID'], 'filter'=>['!CODE'=>['LOCATION', 'FILE', 'ENUM']]])->fetchAll() as $row)
		$internal['PROPERTIES'][] = $row;

	foreach(\Bitrix\Sale\PaySystem\Manager::getList(['select'=>['ID', 'NAME', 'XML_ID']])->fetchAll() as $row)
		$internal['PAY_SYSTEMS'][] = $row;

	foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $row)
		$internal['DELIVERY_SYSTEMS'][] = ['ID'=>$row['ID'], 'NAME'=>$row['NAME'], 'XML_ID'=>$row['XML_ID']];

	foreach(\Bitrix\Sale\TradingPlatformTable::getList(['select'=>['ID', 'NAME', 'XML_ID']])->fetchAll() as $row)
		$internal['TRADE_PLATFORMS'][] = $row;

	foreach(\Bitrix\Sale\OrderStatus::getList(['select' => ['*', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'],
		'filter' => [
			'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID
		]]) as $row)
		$internal['ORDER_STATUSES'][] = $row;

	foreach(\Bitrix\Sale\DeliveryStatus::getList(['select' => ['*', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'],
		'filter' => [
			'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID
		]]) as $row)
		$internal['DELIVERY_STATUSES'][] = $row;

	$r = \CSite::GetList($by,$order);
	while ($row = $r->fetch())
		$internal['SITES'][] = ['ID'=>$row['ID'], 'NAME'=>$row['NAME'], 'XML_ID'=>$manager->getTradePlatformsXmlId($row['LID'])];

	return $internal;
}

function isConnected()
{
	$manager = new \Bitrix\Sale\Rest\Synchronization\Manager();

	return ($manager->getServiceUrl()<>'' && $manager->getRefreshToken()<>'');
}

?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>