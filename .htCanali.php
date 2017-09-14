<?php
//20101016.003
$PAGE_TITLE = "Canali";

$FIELDS = Objects::getCanaliFields();
$TABLE = "canali";

$PAGE_CONTENT = "<h1>Gestione canali</h1>\n";

switch($_REQUEST["cmd2"]) {
  case "show":
  case "edit":
    $config = array(
      "idField" => BasicTable::getIdField($FIELDS),
      "idValue" => $_GET["id"],
      "table" => $TABLE,
    );
    $old = BasicTable::getOldData($config);
    if(!is_array($old)) {
      $_REQUEST["cmd2"] = "add";
    }
    //continue...

  case "add":
    $config = array(
      "jsCheckForm" => "ADMIN.validateObject('Canali', '" . $_REQUEST["cmd2"] . "')",
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
    $REGISTRY->setValue("lastCanaliChange", time());
    break;

  case "del":
    if(!Methods::disbaleDeleteCanale($_GET["id"])) {
      $config = array(
        "idValue" => $_GET["id"],
        "idField" => BasicTable::getIdField($FIELDS),
        "fields" => $FIELDS,
        "table" => $TABLE,
      );
      $deleteRecord = BasicTable::deleteRecord($config);
      $PAGE_CONTENT .= $deleteRecord["htmlCode"];
      $REGISTRY->setValue("lastCanaliChange", time());
    }
    break;

  default:
    $config = array(
      "title" => "Canali",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "canale" => array("name"=>"Canale", "type"=>"field"),
        "frequenzaTx" => array("name"=>"Frequenza TX", "type"=>"field"),
        "frequenzaRx" => array("name"=>"Frequenza RX", "type"=>"field"),
      ),
      "table" => $TABLE,
      "disableDelete" => "Methods::disbaleDeleteCanale(%object%->id)",
      "defaultOrder" => array("col"=>"canale", "dir"=>"ASC"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco dei canali</a></div>\n";
?>