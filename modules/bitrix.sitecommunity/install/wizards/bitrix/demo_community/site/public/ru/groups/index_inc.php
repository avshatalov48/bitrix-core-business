<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CModule::IncludeModule("socialnetwork"))
{

$arSubjects = array();
$dbSubjects = CSocNetGroupSubject::GetList(
	array("SORT"=>"ASC", "NAME" => "ASC"),
	array("SITE_ID" => SITE_ID),
	false,
	false,
	array("ID", "NAME")
);
while ($arSubjectTmp = $dbSubjects->GetNext())
	$arSubjects[$arSubjectTmp["ID"]] = $arSubjectTmp["NAME"];

?>
<div class="rounded-block">
	<div class="corner left-top"></div><div class="corner right-top"></div>
	<div class="block-content">
		<h3>Быстрый поиск</h3>
<div class="filter-box filter-people">
	<form method="get" action="#SITE_DIR#groups/" class="bx-selector-form filter-form">
		<input type="hidden" name="page" value="group_search">
		<?if ($_REQUEST["how"] == "d"):?>
			<input type="hidden" name="how" value="d">
		<?endif;?>
		<div class="filter-item filter-name">
			<label for="filter-name"><span class="required-field">*</span>Искать:</label>
			<input type="text" id="filter-name" name="q" class="filter-textbox" value="<?= htmlspecialcharsbx(trim($_REQUEST["q"])) ?>"/>
		</div>
		<div class="filter-item filter-subject">
			<label for="filter-subject">Тематика:</label>
			<select name="subject" id="filter-subject" class="filter-select">
				<option value="">Любая</option>
				<?foreach ($arSubjects as $k => $v):?>
					<option value="<?= $k ?>"<?= ($k == $_REQUEST["subject"]) ? " selected" : "" ?>><?= $v ?></option>
				<?endforeach;?>
			</select>
		</div>
		<div class="filter-button">
			<input type="submit" name="set_filter" value="Искать" class="filter-submit" />
			<input type="reset" name="del_filter" value="Отмена" class="filter-submit filter-inline" onclick="window.location='#SITE_DIR#groups/'" />
		</div>
	</form>
</div>
	</div>
	<div class="corner left-bottom"></div><div class="corner right-bottom"></div>
</div>
<?
}
?>