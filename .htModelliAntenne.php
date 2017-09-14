<?php
//20120212.011
$PAGE_TITLE = "Modelli Antenne";

$FIELDS = Objects::getModelliAntenneFields();
$TABLE = "modelliAntenne";

$PAGE_CONTENT = "<h1>Gestione modelli antenne</h1>\n";

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
      "jsCheckForm" => "ADMIN.validateObject('ModelliAntenne', '" . $_REQUEST["cmd2"] . "')",
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
    $REGISTRY->setValue("lastModelliAntenneChange", time());
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
    $REGISTRY->setValue("lastModelliAntenneChange", time());
    break;

  default:
    $config = array(
      "title" => "Modelli antenne",
      "idField" => BasicTable::getIdField($FIELDS),
      "columns" => array(
        "id" => array("name"=>"ID", "type"=>"field", "hideColumn"=>true),
        "produttore" => array("name"=>"Produttore", "type"=>"tagList", "values"=>$_SESSION["ANTENNE_PRODUTTORI"]),
        "modello" => array("name"=>"Modello", "type"=>"field"),
        "guadagno" => array("name"=>"Guadagno (dB)", "type"=>"field"),
        "status" => array("name"=>"Status", "type"=>"arrayIndex", "values"=>BasicTable::$basicStatus),
      ),
      "table" => $TABLE,
      "filters" => array(
        "key" => array("name"=>"Modello", "type"=>"keyword", "query"=>"modello LIKE '%%%value%%%'"),
        "produttore" => array("name"=>"Produttore", "type"=>"select", "values"=>$_SESSION["ANTENNE_PRODUTTORI"], "query"=>"produttore=';%%value%%;'", "width"=>150),
      ),
      "defaultOrder" => array("col"=>"produttore", "dir"=>"ASC"),
    );
    $PAGE_CONTENT .= BasicTable::getTable($config);
    break;
}
$PAGE_CONTENT .= "<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "'>Torna all'elenco dei modelli antenne</a></div>\n";
?>