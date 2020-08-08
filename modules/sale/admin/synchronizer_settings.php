<?
use \Bitrix\Sale\Exchange\Integration\App;
use \Bitrix\Sale\Exchange\Integration\OAuth;
use \Bitrix\Sale\Exchange\Integration\Entity;
use \Bitrix\Sale\Exchange\Integration\Token;
use \Bitrix\Sale\Exchange\Integration\Connector;
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions <= "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(Loc::getMessage('SALE_SYNCHRONIZER_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$app = new App\IntegrationB24();
$manager = new Connector\Manager();
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$server = \Bitrix\Main\Application::getInstance()->getContext()->getServer();

$error = [];
$message = [];
$refreshToken = '';
$isNew = !Token::getExistsByGuid($app->getCode());
$isHttps = CMain::IsHTTPS();

//\Bitrix\Main\Config\Option::set("sale", "log_integration_b24_router_request", 'Y');
//\Bitrix\Main\Config\Option::set("sale", "log_integration_b24_rest_client", 'Y');

if($isHttps == false)
{
	$error[] = Loc::getMessage('SALE_SYNCHRONIZER_PORT');
}

if ($isHttps
	&& $request->isPost()
	&& $saleModulePermissions == "W"
	&& check_bitrix_sessid()
)
{
    if($request->get('DISABLE') == 'Y')
    {
		$manager->delete();
		$error[] = Loc::getMessage('SALE_SYNCHRONIZER_DISABLED');
    }
	elseif($request->get('CONFIG_REFRESH_TOKEN') <> '')
	{
	    $response = (new Entity\AccessCode(new OAuth\Bitrix24()))
            ->create(['refreshToken'=>$request->get('CONFIG_REFRESH_TOKEN')]);

		if (!isset($response["error"]) && is_array($response))
		{
			$token = Token::getToken($response, $app->getCode());

			if($token)
            {
				if($isNew)
                {
					$manager->add();
                }

				$message[] = Loc::getMessage('SALE_SYNCHRONIZER_CONNECTED');
            }
            else
            {
				$error[] = Loc::getMessage('SALE_SYNCHRONIZER_CREATE_TOKEN');
            }

		}
		else
        {
			$error[] = Loc::getMessage('SALE_SYNCHRONIZER_REFRESH_TOKEN');
        }
	}
	else
    {
		$error[] = Loc::getMessage('SALE_SYNCHRONIZER_TOKEN');
    }
}

if(Token::getExistsByGuid($app->getCode()))
{
	$token = Token::getToken([], $app->getCode());
	$refreshToken = is_null($token) ? '':$token->getRefreshToken();
}

if(count($message)>0)
{
	$adminMessage = new CAdminMessage(
		array("MESSAGE" => implode("<br>\n", $message), "TYPE" => "OK")
	);
	echo $adminMessage->Show();
}

if(count($error)>0)
{
	$adminMessage = new CAdminMessage(
		array("MESSAGE" => implode("<br>\n", $error), "TYPE" => "ERROR")
	);
	echo $adminMessage->Show();
}
	?>
    <div class="crm-admin-wrap">
        <?=Loc::getMessage('SALE_SYNCHRONIZER_DESCRIPTION')?>
        <div class="crm-admin-set" id="id_new_crm_reg_form">
            <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form_do_create_link">
                <div class="crm-admin-set-title"><?=Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_TITLE')?></div>
                <table class="crm-admin-set-content-table" cellspacing="0">
                    <tr>
                        <td class="crm-admin-set-left"><nobr><span class="required">*</span><?=Loc::getMessage('SALE_SYNCHRONIZER_SETTINGS_ACCESS_TOKEN')?></nobr></td>
                        <td class="crm-admin-set-right"><input class="crm-admin-set-input" type="text" name="CONFIG_REFRESH_TOKEN" value="<?=$refreshToken?>"/></td>
                    </tr>
                </table>
                <div class="crm-admin-set-button">
                    <a class="adm-btn adm-btn-green" href='javascript:document.forms["form_do_create_link"].submit();'><?=Loc::getMessage('SALE_SYNCHRONIZER_APPLY')?></a>&nbsp;&nbsp;
                    <a class="adm-btn" href="javascript:disable();"><?=Loc::getMessage('SALE_SYNCHRONIZER_DISABLE')?></a>
                </div>
				<?=bitrix_sessid_post();?>
                <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
            </form>
        </div>
    </div>

<script type="application/javascript">
    disable = function () {
        document.forms["form_do_create_link"].appendChild(
            BX.create('input', {
                props: {
                    name: 'DISABLE'
                },
                attrs: {
                    type: 'hidden',
                    value: 'Y'
                }
            })
        );
        document.forms["form_do_create_link"].submit();
    }
</script>
	<?
?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>