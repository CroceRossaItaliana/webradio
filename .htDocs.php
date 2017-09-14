<?php
//20110206.012
$DOC_SECTIONS = array(
  "Radio" => array(
    "table" => "radio",
    "fieldName" => "modelloRadio",
    "evalFieldName" => "\$rcDocSource->fieldNameEvaluated = \$_SESSION[CACHE_MODELLI_RADIO][\$rcDocSource->fieldName];",
    "list" => "delle radio",
  ),
  "Ripetitori" => array(
    "table" => "ripetitori",
    "fieldName" => "localitaCollegata",
    "evalFieldName" => "\$rcDocSource->fieldNameEvaluated = str_replace(\"%%fn%%\", \"\$rcDocSource->fieldName\", \"Sito di %%fn%%\");",
    "list" => "dei ripetitori",
  ),
);

list($filtroUnitaCri, $unitaCriValues, $WHERE, $extraQuerySet) = setObjectFilter();
if($_REQUEST["id"] && is_numeric($_GET["id"]) && array_key_exists($_GET["type"], $DOC_SECTIONS)) {
  $rsDocSource = $DBL->query("SELECT id, " . $DOC_SECTIONS[$_GET["type"]]["fieldName"] . " AS fieldName FROM " . $DOC_SECTIONS[$_GET["type"]]["table"] . " WHERE id=" . $_GET["id"] . " AND " . $WHERE);
  if($rsDocSource->num_rows == 1) {
    $rcDocSource = $rsDocSource->fetch_object();
  }
}
if(!$rcDocSource) die(header("Location: " . $_SERVER["SCRIPT_NAME"]));

$PAGE_TITLE = "Documenti " . $_GET["type"];

eval($DOC_SECTIONS[$_GET["type"]]["evalFieldName"]);
$PAGE_CONTENT = "<h1>Documenti " . $_GET["type"] . " - " . $rcDocSource->fieldNameEvaluated . "</h1>
<h2>Documenti " . $_GET["type"] . "</h2>
<table border='0' cellspacing='2' cellpadding='2'>
  <tr>
    <th>Nome documento</th>
    <th>&nbsp;</th>
  </tr>\n";
  $docDir = $_SERVER["DOCUMENT_ROOT"] . $PATHS[$DOC_SECTIONS[$_GET["type"]]["table"] . "Docs"] . $_GET["id"] . "/";
  if(!is_dir($docDir)) {
    mkdir($docDir, 0755, true);
  }
  if(file_exists($_FILES["doc"]["tmp_name"]) && (!$LOGIN->isGuest() && !$LOGIN->getUserData("compilazioneCompletata"))) {
    copy($_FILES["doc"]["tmp_name"], $docDir . base64_encode($_FILES["doc"]["name"]) . ".bin");
    logMessage("Aggiunto file " . $PATHS[$DOC_SECTIONS[$_GET["type"]]["table"] . "Docs"] . $_GET["id"] . "/" . $_FILES["doc"]["name"]);
  }
  if($_GET["del"] && ($_GET["crc"] == sha1($_GET["del"] . ".bin" . $SECRET)) && file_exists($docDir . $_GET["del"] . ".bin") && !$LOGIN->isGuest() && !$LOGIN->getUserData("compilazioneCompletata")) {
    unlink($docDir . $_GET["del"] . ".bin");
    logMessage("Cancellato file " . $PATHS[$DOC_SECTIONS[$_GET["type"]]["table"] . "Docs"] . $_GET["id"] . "/" . base64_decode($_GET["del"]));
  }
  if($_GET["download"] && ($_GET["crc"] == sha1($_GET["download"] . ".bin" . $SECRET)) && file_exists($docDir . $_GET["download"] . ".bin")) {
    header("Pragma: ");
    header("Cache-control: ");
    header("Content-type: application/octet-stream");
    header("Content-Disposition: inline; filename=\"" . base64_decode($_GET["download"]) . "\"");
    readfile($docDir . $_GET["download"] . ".bin");
    die();
  }
  if($dh = opendir($docDir)) {
    while(($file = readdir($dh)) !== false) {
      if(is_file($docDir . $file) && strpos($file, ".bin")) {
        $realFilename = base64_decode(substr($file, 0, -4));
        $rowClass = ($rowClass == "B") ? "A" : "B";
        $PAGE_CONTENT .= "<tr class='row" . $rowClass . "'>
          <td>" . $realFilename . "</td>
          <td>
            <a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Docs&amp;type=" . $_GET["type"] . "&amp;id=" . $_GET["id"] . "&amp;download=" . substr($file, 0, -4) . "&amp;crc=" . sha1($file . $SECRET) . "' target='_blank' title='Scarica documento'><img src='img/icons/download.png' alt='Scarica documento' class='icon' /></a>
            " . ($LOGIN->isGuest() || ($LOGIN->getUserData("compilazioneCompletata")) ? "" : "<a href=\"javascript:if(confirm('Eliminare il documento?')) location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Docs&amp;type=" . $_GET["type"] . "&amp;id=" . $_GET["id"] . "&amp;del=" . substr($file, 0, -4) . "&amp;crc=" . sha1($file . $SECRET) . "';\" title='Elimina documento'><img src='img/icons/delete.png' alt='Elimina documento' class='icon' /></a>") . "
          </td>
        </tr>\n";
      }
    }
    closedir($dh);
  }

  if(!$LOGIN->isGuest() && !$LOGIN->getUserData("compilazioneCompletata")) {
    $PAGE_CONTENT .= "<tr>
      <td colspan='2'>
        <form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=Docs&amp;type=" . $_GET["type"] . "&amp;id=" . $_GET["id"] . "' enctype='multipart/form-data'>
          Nuovo documento: <input type='file' name='doc' />
          <input type='submit' value='Salva documento' />
        </form>
      </td>
    </tr>\n";
  }
$PAGE_CONTENT .= "</table>

<div><a href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_GET["type"] . "'>Torna all'elenco " . $DOC_SECTIONS[$_GET["type"]]["list"] . "</a></div>\n";
?>