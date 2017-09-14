<?php
//20110206.014
$FIELDS = Objects::getUtentiFields();
$TABLE = "users";

$PAGE_CONTENT = "<h1>Gestione utenti</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "show":
  case "edit":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "idValue" => $_GET["id"],
      "table" => $TABLE,
    );
    $old = BasicTable::getOldData($config);
    if(!is_array($old)) $_REQUEST["cmd2"] = "add";
    //continue...

  case "add":
    $config = array(
      "jsCheckForm" => "(" . ((!is_array($old)) ? "!ADMIN.checkIfUsernameExists('username')" : "1") . " &amp;&amp; ADMIN.validateObject('Utenti', '" . $_REQUEST["cmd2"] . "'))",
      "action" => $_REQUEST["cmd2"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $old,
    );
    $PAGE_CONTENT .= BasicTable::getForm($config);
    break;

  case "save":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "data" => $_POST,
      "table" => $TABLE,
    );
    $saveRecord = BasicTable::saveRecord($config);
    $PAGE_CONTENT .= $saveRecord["htmlCode"];
    logMessage((($saveRecord["action"] == "add") ? "Inserito" : "Modificato") . " utente " . $saveRecord["recordId"]);
    break;

  case "del":
    $config = array(
      "idValue" => $_GET["id"],
      "idField" => BasicTable::getIdField($FIELDS),
      "fields" => $FIELDS,
      "table" => $TABLE,
    );
    $deleteRecord = BasicTable::deleteRecord($config);
    $PAGE_CONTENT .= $deleteRecord["htmlCode"];
    logMessage("Cancellato utente " . $_GET["id"]);
    break;

  default:
    $config = array(
      "title" => "Elenco utenti",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "username" => array("name"=>"Username", "type"=>"field"),
        "maglia" => array("name"=>"Maglia", "type"=>"arrayIndex", "values"=>getElencoMaglie()),
        "provincia" => array("name"=>"Provincia", "type"=>"arrayIndex", "values"=>getProvince()),
        "realName" => array("name"=>"Nome", "type"=>"field"),
        "type" => array("name"=>"Tipo", "type"=>"arrayIndex", "values"=>$LOGIN->getUserTypes()),
        "referenceUser" => array("name"=>"Comitato di riferimento", "type"=>"field"),
        "status" => array("name"=>"Status", "type"=>"arrayIndex", "values"=>BasicTable::$basicStatus),
      ),
      "table" => $TABLE,
      "filters" => array(
        "username" => array("name"=>"Username", "type"=>"keyword", "query"=>"username LIKE '%%%value%%%'"),
        "userLevel" => (($LOGIN->getUserData("type") == 1) ? array("name"=>"Type", "type"=>"select", "values"=>$LOGIN->getUserTypes(), "query"=>"type='%%value%%'", "width"=>150) : null),
      ),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Visualizza l'elenco degli utenti</a></div>\n";
?>