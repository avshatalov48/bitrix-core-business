<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$readOnly = $APPLICATION->GetGroupRight('sale') < 'W';

if ($readOnly)
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/include.php');

use	Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;
use	Bitrix\Sale\Internals\Input;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$domainErrors = array();

$allPersonTypes = BusinessValue::getPersonTypes(true);

$personDomainInput = array('TYPE' => 'ENUM', 'OPTIONS' => array(
	''                               => Loc::getMessage('BIZVAL_DOMAIN_NONE'      ),
	BusinessValue::INDIVIDUAL_DOMAIN => Loc::getMessage('BIZVAL_DOMAIN_INDIVIDUAL'),
	BusinessValue::ENTITY_DOMAIN     => Loc::getMessage('BIZVAL_DOMAIN_ENTITY'    ),
));

// 1. post persons domains
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['PERSONS_DOMAINS']) && is_array($_POST['PERSONS_DOMAINS']))
{
	// sanitize & validate
	call_user_func(
		function () use (&$domainErrors, $allPersonTypes, $personDomainInput)
		{
			foreach ($_POST['PERSONS_DOMAINS'] as $personTypeId => $personTypeDomain)
			{
				if ($allPersonTypes[$personTypeId])
				{
					if ($error = Input\Manager::getError($personDomainInput, $personTypeDomain))
						$domainErrors[$personTypeId]['DOMAIN'] = $error;
				}
				else
				{
					unset ($_POST['PERSONS_DOMAINS'][$personTypeId]);
				}
			}
		}
	);

	if (! $domainErrors && ! $readOnly && check_bitrix_sessid() && ($_POST['save'] || $_POST['apply']))
	{
		// save
		call_user_func(
			function () use (&$domainErrors, &$allPersonTypes)
			{
				foreach ($_POST['PERSONS_DOMAINS'] as $personTypeId => $postedPersonDomain)
				{
					$savedPersonDomain = $allPersonTypes[$personTypeId]['DOMAIN'];

					if ($postedPersonDomain != $savedPersonDomain)
					{
						if ($savedPersonDomain)
						{
							$deletePersonDomainResult = BusinessValuePersonDomainTable::delete(array(
								'PERSON_TYPE_ID' => $personTypeId,
								'DOMAIN'         => $savedPersonDomain,
							));

							if ($deletePersonDomainResult->isSuccess())
							{
								$result = BusinessValueTable::getList(array(
									'select' => array('CODE_KEY', 'CONSUMER_KEY', 'PERSON_TYPE_ID'),
									'filter' => array('=PERSON_TYPE_ID' => $personTypeId),
								));

								while ($row = $result->fetch())
								{
									// TODO remove save_data_modification hack
									if (! $row['CONSUMER_KEY'])
										$row['CONSUMER_KEY'] = BusinessValueTable::COMMON_CONSUMER_KEY;

									BusinessValueTable::delete($row); // TODO errors
								}

								$allPersonTypes[$personTypeId]['DOMAIN'] = null;
							}
							else
							{
								$domainErrors[$personTypeId]['DELETE'] = $deletePersonDomainResult->getErrorMessages();
							}
						}

						if ($postedPersonDomain)
						{
							$addPersonDomainResult = BusinessValuePersonDomainTable::add(array(
								'PERSON_TYPE_ID' => $personTypeId,
								'DOMAIN'         => $postedPersonDomain,
							));

							if ($addPersonDomainResult->isSuccess())
							{
								$allPersonTypes[$personTypeId]['DOMAIN'] = $postedPersonDomain;
							}
							else
							{
								$domainErrors[$personTypeId]['ADD'] = $addPersonDomainResult->getErrorMessages();
							}
						}
					}
				}
			}
		);
	}
}

// VIEW ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(Loc::getMessage('BIZVAL_PAGE_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if ($domainErrors)
{
	call_user_func(function () {
		$m = new CAdminMessage(Loc::getMessage('BIZVAL_PAGE_ERRORS'));
		echo $m->Show();
	});
}

?>
	<form method="POST" action="<?=$APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.GetFilterParams('filter_', false)?>" name="bizvalTabs_form" id="bizvalTabs_form">

		<?=bitrix_sessid_post()?>

		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content">
				<div class="adm-detail-title"><?=Loc::getMessage('BIZVAL_PAGE_PTYPES')?></div>
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
					<tbody>
					<?

					foreach ($allPersonTypes as $personTypeId => $personType)
					{
						$error = isset($domainErrors[$personTypeId])
							? $domainErrors[$personTypeId]
							: array();

						?>
						<tr>
							<td class="adm-detail-content-cell-l" width="40%">
								<?

								echo htmlspecialcharsbx($personType['TITLE']);

								if ($error['ADD'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['ADD'])).'</div>';

								if ($error['DELETE'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['DELETE'])).'</div>';

								?>
							</td>
							<td class="adm-detail-content-cell-r">
								<?

								echo Input\Manager::getEditHtml("PERSONS_DOMAINS[$personTypeId]", $personDomainInput, $allPersonTypes[$personTypeId]['DOMAIN']);

								if ($error['DOMAIN'])
									echo '<div style="color:#ff5454">'.htmlspecialcharsbx(implode('<br>', $error['DOMAIN'])).'</div>';

								?>
							</td>
						</tr>
						<?
					}

					?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="adm-detail-content-btns-wrap">
			<div class="adm-detail-content-btns">
				<?

				$hkInst = CHotKeys::getInstance();
				echo '<input'.($aParams["disabled"] === true? " disabled":"")
					.' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'
					.GetMessage("admin_lib_edit_apply_title").$hkInst->GetTitle("Edit_Apply_Button").'" class="adm-btn-save" />';
				echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Apply_Button"));

				?>
			</div>
		</div>

	</form>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
