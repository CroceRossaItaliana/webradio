<?php
//20110206.008
$TABLE = "users";

$PAGE_CONTENT = "<h1>Profilo utente</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "save":
  	$FIELDS = Objects::getProfiloUtenteFields();
  	$config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $_POST,
      "table" => $TABLE,
  		"where" => "username='" . $DBL->real_escape_string($LOGIN->getUserData("username")) . "'",
    );
    $saveRecord = BasicTable::saveRecord($config);
    $PAGE_CONTENT .= $saveRecord["htmlCode"];
    logMessage("Aggiornato profilo utente");
    break;

  case "edit":
  case "show":
  default:
  	$FIELDS = ($_REQUEST["cmd2"] == "edit") ? Objects::getProfiloUtenteFields() : Objects::getUtentiFields();
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "idValue" => $LOGIN->getUserData("username"),
      "table" => $TABLE,
    );
    $old = BasicTable::getOldData($config);
    if(!is_array($old)) die(".");
    $config = array(
      "jsCheckForm" => "ADMIN.validateObject('ProfiloUtente', '" . $_REQUEST["cmd2"] . "')",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    break;
}
$PAGE_CONTENT .= "<div>
	<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Visualizza profilo</a><br />
	" . ((!$LOGIN->isGuest()) ? "<a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;cmd2=edit'>Modifica profilo</a><br />" : "") . "
</div>\n";
?>