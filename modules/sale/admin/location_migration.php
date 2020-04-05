<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Migration;
use Bitrix\Sale\Location\Admin\Helper;
use Bitrix\Sale\Location\Admin\LocationHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Main\Loader::includeModule('sale');

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/location/migration/migration.php");

Loc::loadMessages(__FILE__);

$result = 	true;
$errors = 	array();

try
{
	$migration = new Migration\MigrationProcess();
	$migration->hideNotifier();

	// action: process ajax
	if(isset($_REQUEST['AJAX_MODE']))
	{
		$data = array();

		if($_REQUEST['step'] == 0)
			$migration->reset();

		try
		{
			$data['PERCENT'] = $migration->performStage();
			$data['NEXT_STAGE'] = $migration->getStageCode();
		}
		catch(Main\SystemException $e)
		{
			$result = false;
			$errors[] = $e->getMessage();
		}

		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		print(CUtil::PhpToJSObject(array(
			'result' => $result,
			'errors' => $errors,
			'data' => $data
		), false, false, true));

		die();
	}

	$migration->reset(); // reset cached data
}
catch(Main\SystemException $e)
{
	$result = false;
	$errors[] = $e->getMessage();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_MIGRATION_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?if(!$result):?>
	<?CAdminMessage::ShowMessage(array('MESSAGE' => htmlspecialcharsbx(implode(', ', $errors)), 'type' => 'ERROR'))?>
<?else:?>

	<?
	$aTabs = array(
		array(
			"DIV" => "migration",
			"TAB" => Loc::getMessage("SALE_LOCATION_MIGRATION_TAB_MIGRATION_TITLE"),
			"ICON" => "sale",
			"TITLE" => Loc::getMessage("SALE_LOCATION_MIGRATION_TAB_MIGRATION_TITLE")
		),
	);

	$tabControl = new CAdminTabControl("tabctrl_migration", $aTabs, true, true);

	CJSCore::Init();
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_iterator.js');
	?>

	<div id="location-migration">

		<div class="bx-ui-loc-m-progressbar">
			<?
			CAdminMessage::ShowMessage(array(
				"TYPE" => "PROGRESS",
				"DETAILS" => '#PROGRESS_BAR#'.
					'<div class="adm-loc-m-statusbar">'.Loc::getMessage('SALE_LOCATION_MIGRATION_STATUS').': <span class="bx-ui-loc-m-loader"></span>&nbsp;<span class="bx-ui-loc-m-status-text">'.Loc::getMessage('SALE_LOCATION_STAGE_INITIAL').'</span></div>',
				"HTML" => true,
				"PROGRESS_TOTAL" => 100,
				"PROGRESS_VALUE" => 0,
				"PROGRESS_TEMPLATE" => '<span class="bx-ui-loc-m-percents">#PROGRESS_VALUE#</span>%'
			));
			?>
		</div>

		<?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>

			<tr>
				<td colspan="2">
					<?if(CSaleLocation::isLocationProMigrated()):?>
						<?=Loc::getMessage('SALE_LOCATION_MIGRATION_ALREADY_DONE')?>
					<?else:?>
						<input type="submit" class="adm-btn-save bx-ui-loc-m-button-start" value="<?=Loc::getMessage('SALE_LOCATION_MIGRATION_START')?>">
					<?endif?>
				</td>
			</tr>

		<?
		$tabControl->EndTab();
		$tabControl->Buttons();
		?>

		<?
		$tabControl->End();
		?>

	</div>

	<script>

		BX.locationMigration = function(opts, nf){

			this.parentConstruct(BX.locationMigration, opts);

			BX.merge(this, {
				opts: { // default options
					url: 				'/somewhere.php',
					ajaxFlag: 			'AJAX_MODE',
					progressWidth : 	500
				},
				vars: { // significant variables
					stage: 			false,
					disableBtn: 	false
				},
				ctrls: { // links to controls
					buttons: {}
				},
				sys: {
					code: 'loc-m'
				}
			});

			this.handleInitStack(nf, BX.locationMigration, opts);
		};
		BX.extend(BX.locationMigration, BX.ui.widget);

		BX.merge(BX.locationMigration.prototype, {

			init: function(){

				var so = this.opts,
					sv = this.vars,
					sc = this.ctrls,
					ctx = this;

				// iterator

				sv.iterator = new BX.iterator({
					source: so.url,
					interval: 100,
					whenHit: function(result){
						ctx.setPercent(result.data.PERCENT);

						var next = result.data.NEXT_STAGE;

						// set message
						if(BX.type.isNotEmptyString(next) && sv.stage != result.data.NEXT_STAGE)
							ctx.setStage(result.data.NEXT_STAGE);

						var proceed = result.data.PERCENT < 100;

						if(!proceed)
						{
							ctx.setStage('COMPLETE');
							sv.disableBtn = true;

							setTimeout(function(){

								window.location = so.redirectTo;

							}, 1000);
						}

						return proceed;
					}
				});

				sv.iterator.bindEvent('set-status', function(stat){

					if(stat == 'R'){
						sc.buttons.startStop.disabled = true;
						ctx.setCSSState('running');
					}else{
						sc.buttons.startStop.disabled = sv.disableBtn;
						ctx.dropCSSState('running');
					}
				});

				sc.buttons.startStop = 	this.getControl('button-start');

				sc.percentIndicator = 	this.getControl('percents', false, false, true);
				sc.percentGrade = 		this.getControl('adm-progress-bar-inner');
				sc.statusText = 		this.getControl('status-text');

				this.pushFuncStack('bindEvents', BX.locationMigration);
			},

			/*buildUpDOM: function(){},*/

			bindEvents: function(){

				var sc = this.ctrls,
					sv = this.vars,
					so = this.opts,
					ctx = this;

				// iterator

				BX.bind(sc.buttons.startStop, 'click', function(){

					if(sv.iterator.getIsRunning()){

						sv.iterator.stop();
						ctx.setStage('INTERRUPTED');

					}else{

						ctx.setPercent(0);
						ctx.setStage('CREATE_TYPES');

						BX.show(ctx.getControl('progressbar'));
						var request = {};
						request[so.ajaxFlag] = 1;
						sv.iterator.start(request);
					}
				});

				var onError = function(errors){

					var errMsg = [];

					if(typeof errors != 'undefined'){
						for(var k in errors){
							if(errors[k].message)
								errMsg.push(errors[k].message);
						}
					}

					ctx.setStatusText(so.messages.error_occured+': '+errMsg.join(', '), true);
				}

				sv.iterator.bindEvent('server-error', onError);
				sv.iterator.bindEvent('ajax-error', onError);
			},

			setPercent: function(value){
				var sc = this.ctrls,
					so = this.opts;

				value = parseInt(value);
				if(value < 0)
					value = 0;
				if(value > 100)
					value = 100;

				if(sc.percentIndicator != null){
					for(var k in sc.percentIndicator){
						sc.percentIndicator[k].innerHTML = value;
					}
				}

				value = value * (so.progressWidth / 100) - 4;
				if(value < 0)
					value = 0;

				BX.style(sc.percentGrade, 'width', value+'px');
			},

			setStatusText: function(text, highlight){
				this.ctrls.statusText.innerHTML = BX.util.htmlspecialchars(text);
				BX.style(this.ctrls.statusText, 'color', highlight ? 'red' : 'inherit');
			},

			setStage: function(stageCode){

				var so = this.opts,
					sv = this.vars;

				if(typeof so.messages['stage_'+stageCode] == 'undefined'){
					this.setStatusText('Unknown status', true);
					sv.stage = false;
					return;
				}

				this.setStatusText(this.opts.messages['stage_'+stageCode], false);
				sv.stage = stageCode;
			}
		});

		<?if(!CSaleLocation::isLocationProMigrated()):?>

			new BX.locationMigration(<?=CUtil::PhpToJSObject(array(

					// common
					'url' => Helper::getMigrationUrl(),
					'scope' => 'location-migration',
					'ajaxFlag' => 'AJAX_MODE',
					'redirectTo' => LocationHelper::getListUrl(0),

					'messages' => array(
						'error_occured' => Loc::getMessage('SALE_LOCATION_MIGRATION_ERROR'),

						'stage_CREATE_TYPES' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_CREATE_TYPES'),
						'stage_CONVERT_TREE' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_CONVERT_TREE'),
						'stage_CONVERT_ZONES' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_CONVERT_ZONES'),
						'stage_CONVERT_LINKS' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_CONVERT_LINKS'),
						'stage_COPY_DEFAULT_LOCATIONS' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_COPY_DEFAULT_LOCATIONS'),
						'stage_COPY_ZIP_CODES' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_COPY_ZIP_CODES'),
						'stage_COMPLETE' => Loc::getMessage('SALE_LOCATION_MIGRATION_STAGE_COMPLETE'),
					)

			), false, false, true)?>);

		<?endif?>

	</script>

	<style>
		.adm-loc-m-statusbar {
			margin-top: 10px;
			margin-bottom: -15px;
		}
		.bx-ui-loc-m-progressbar{
			display: none;
		}
		.bx-ui-state-running .bx-ui-loc-m-loader{
			background: url(/bitrix/panel/main/images/filter-active-waiter.gif) 0px 0px no-repeat scroll;
			width: 20px;
			height: 20px;
			display: inline;
			padding: 2px 10px;
		}
	</style>

<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
