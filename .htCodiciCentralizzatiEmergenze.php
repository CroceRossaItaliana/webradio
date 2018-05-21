<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180521.004
 */

$FILENAME = "/filez/docs/CodiciCentralizzatiEdEmergenze.pdf";

$PAGE_TITLE = "Codici Centralizzati ed Emergenze";

logMessage("Scaricato Elenco Codici Centralizzati ed Emergenze");

$PAGE_CONTENT = "<h1>Codici Centralizzati ed Emergenze</h1>
<h3>Codici Centralizzati ed Emergenze</h3>
<div>Codici Centralizzati ed Emergenze.</div>
<div><a href='" . $FILENAME . "' target='_blank'>Scarica Codici Centralizzati ed Emergenze</a></div>";

//Gestione file
$USER_TYPE = $LOGIN->getUserType();
$USER_TYPE = $USER_TYPE["id"];
if(in_array($USER_TYPE, array(1, 2))) {
  $PAGE_CONTENT .= "<h1 style='margin:50px 0 10px;'>Gestione file (only admin)</h1>";
  if($_FILES["doc"] && ($_FILES["doc"]["error"] == 0) && ($_FILES["doc"]["type"] == "application/pdf")) {
    logMessage("Modulo Codici Centralizzati ed Emergenze sostituito");
    move_uploaded_file($_FILES["doc"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"] . $FILENAME);
    $PAGE_CONTENT .= BasicTable::showMessageBox("Documento sostituito");
  }
  $PAGE_CONTENT .= "<form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "' enctype='multipart/form-data' style='margin-top:10px;'>
    Nuovo documento: <input type='file' name='doc' />
    <input type='submit' value='Salva documento' />
  </form>";
}
?>