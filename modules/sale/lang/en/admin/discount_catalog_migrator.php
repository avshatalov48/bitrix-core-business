<?
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_FAILED"] = "Error migrating discounts";
$MESS["DISCOUNT_CATALOG_MIGRATOR_PROCESSED_SUMMARY"] = "Discounts migrated (cumulative):";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_IN_PROGRESS"] = "Migration is still in progress";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_COMPLETE"] = "Migration has been completed";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TITLE"] = "Migrate Commercial Catalog Discounts";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB"] = "Data transfer";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_TAB_TITLE"] = "Data transfer";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_START_BUTTON"] = "Start Migration";
$MESS["DISCOUNT_CATALOG_MIGRATOR_CONVERT_STOP_BUTTON"] = "Abort";
$MESS["DISCOUNT_CATALOG_MIGRATOR_UNKNOWN_ERROR"] = "Unknown error migrating discounts";
$MESS["DISCOUNT_CATALOG_MIGRATOR_ERROR_REPORT"] = "Cannot process <a href\"#URL#\">#TITLE#</a>: #ERRORS#";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_SAVE"] = "Progressive discounts";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_TYPE_SALE"] = "Discount action 'Set item price'";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_DISC_CURRENCY_SALE_SITE"] = "Discount currency and e-store currency selected for this site are different.";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_TEXT"] = "Some discounts cannot be currently converted or merged. Please pay attention to this issue.<br>
The following unsupported discounts have been found:<br><br>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT"] = "The wizard will merge Commercial Catalog discounts with e-Store discounts so that they are applied sequentially.<br><br>
This will help manage discount dependencies, pause discounts when required or specify discount priority.<br><br>
The merging may take a while depending on how many discounts your project has. You are advised to proceed when server load and website traffic are low.<br>";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_FINAL"] = "Once the discounts have been merged, you have to check if the \"Don't apply further discounts\" discount option is selected correctly in each discount. The Commercial Catalog discounts and those of the e-Store module are now in the same queue and therefore may affect each other.<br><br>
It is recommended to backup your project and the database before continuing.<br><br>
Your site's public area will be disabled while merging.
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_PAGE_HELLO_TEXT"] = "<p>The discount merge wizard will help you migrate to a better, improved way to manage discounts.</p> 
<p>After the wizard completes, you will get a ready to use armory of marketing tools. You can start using discounts without having to learn all the sophisticated configuration options.</p> 
<p>Offer flexible discounts, market your products, increase sales! The system will do the chores for you.</p>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_NON_SUPPORTED_FEATURE_RELATIVE_ACTIVE_PERIOD"] = "Progressive discount lifetime is set to \"Time period from a discount start date\" ";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_NEW"] = "This update will merge the Commercial Catalog discounts and the e-Store module's discounts in a unified processing queue.<br><br>
When merged, the discounts will be easier to manage. This will also help you control discount dependencies, suspend discounts at any time and specify discount application priority.<br><br>
The merging may take a while if your project specifies a lot of discounts. It is advised to perform this task when your web store's website experiences the lowest load possible.<br><br>
#CUMULATIVE_PART#
<br>
";
$MESS["DISCOUNT_CATALOG_MIGRATOR_HELLO_TEXT_CUMULATIVE_PART"] = "You Are Using Progressive Discounts!<br><br>
<b>Important!</b><br><br>
The Discount Processing Logic Has Been Improved; Two More Options Can Now Be Applied: \"Don't Apply Further Rules\" And \"Set Discount Priority\".
The Progressive Discounts Will Be Migrated As Well. You Will Then Have to Check And Set Discount Priorities, And Configure Expiration If Required.<br>
By Default, Progressive Discount Priority Is Set to The Lowest Possible Value So They Will Apply Last. Note That If There Are Discounts With Higher Priority Whose Option \"Don't Apply Further Rules\" Is Enabled, No Progressive Discount Will Apply.<br><br><br>
";
?>