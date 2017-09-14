<?php
//20110320.017
$PAGE_TITLE = "Stato compilazione";

$PAGE_CONTENT = "<h1>Stato compilazione</h1>\n";

if($LOGIN->getUserData("type") == 1) {
  if($_GET["chiudi"]) {
    $DBL->query("UPDATE users SET compilazioneCompletata=true, compilazioneCompletataData=NOW(), compilazioneCompletataIp='" . $_SERVER["REMOTE_ADDR"] . "' WHERE username='" . $DBL->real_escape_string(stripslashes(urldecode($_GET["chiudi"]))) . "'");
    logMessage("Forzata chiusura compilazione per utente " . $_GET["chiudi"]);
  }
  if($_GET["riapri"]) {
    $DBL->query("UPDATE users SET compilazioneCompletata=false, compilazioneCompletataData='', compilazioneCompletataIp='' WHERE username='" . $DBL->real_escape_string(stripslashes(urldecode($_GET["riapri"]))) . "'");
    logMessage("Forzata riapertura compilazione per utente " . $_GET["riapri"]);
  }
}

$rsUtentiCompilazioneInCorso = $DBL->query("SELECT username, realName FROM users WHERE type IN(2, 3, 4, 5) AND compilazioneCompletata=false ORDER BY realName");
$rsUtentiCompilazioneCompletata = $DBL->query("SELECT username, realName FROM users WHERE type IN(2, 3, 4, 5) AND compilazioneCompletata=true ORDER BY realName");
$percentualeCompletamento = (int)($rsUtentiCompilazioneCompletata->num_rows * 100 / ($rsUtentiCompilazioneInCorso->num_rows + $rsUtentiCompilazioneCompletata->num_rows));

$numeroRadio = $DBL->query("SELECT id FROM radio")->num_rows;
$numeroRipetitori = $DBL->query("SELECT id FROM ripetitori")->num_rows;
$ultimoRadio = $DBL->query("SELECT id, DATE_FORMAT(ultimaModificaData, '%d/%m/%Y') AS dataFormat FROM radio ORDER BY ultimaModificaData DESC LIMIT 1")->fetch_object();
$ultimoRipetitori = $DBL->query("SELECT id, DATE_FORMAT(ultimaModificaData, '%d/%m/%Y') AS dataFormat FROM ripetitori ORDER BY ultimaModificaData DESC LIMIT 1")->fetch_object();
$PAGE_CONTENT .= drawChart("Generale", $percentualeCompletamento, $numeroRadio, $numeroRipetitori, $ultimoRadio->dataFormat, $ultimoRipetitori->dataFormat);

foreach(getProvince() as $provId=>$provName) {
	$provCompilazioneInCorso = $DBL->query("SELECT username FROM users WHERE type IN(2, 3, 4, 5) AND compilazioneCompletata=false AND provincia='" . $provId . "'")->num_rows;
	$provCompilazioneCompletata = $DBL->query("SELECT username, realName FROM users WHERE type IN(2, 3, 4, 5) AND compilazioneCompletata=true AND provincia='" . $provId . "'")->num_rows;

	$numeroRadio = $numeroRipetitori = $ultimoRadio = $ultimoRipetitori = $provPercentualeCompletamento = 0;
	$utentiProvincia = array();
	$rsUtentiPerProvincia = $DBL->query("SELECT username FROM users WHERE provincia='" . $provId . "'");
	while($rcUtentiPerProvincia = $rsUtentiPerProvincia->fetch_object()) {
	  $numeroRadio += $DBL->query("SELECT id FROM radio WHERE unitaCri='" . $DBL->real_escape_string(stripslashes($rcUtentiPerProvincia->username)) . "'")->num_rows;
    $numeroRipetitori += $DBL->query("SELECT id FROM ripetitori WHERE unitaCri='" . $DBL->real_escape_string(stripslashes($rcUtentiPerProvincia->username)) . "'")->num_rows;
    $utentiProvincia[] = "'" . $DBL->real_escape_string(stripslashes($rcUtentiPerProvincia->username)) . "'";
	}
	if(count($utentiProvincia)) {
  	$ultimoRadio = $DBL->query("SELECT id, DATE_FORMAT(ultimaModificaData, '%d/%m/%Y') AS dataFormat FROM radio WHERE unitaCri IN(" . implode(",", $utentiProvincia) . ") ORDER BY ultimaModificaData DESC LIMIT 1")->fetch_object();
    $ultimoRipetitori = $DBL->query("SELECT id, DATE_FORMAT(ultimaModificaData, '%d/%m/%Y') AS dataFormat FROM ripetitori WHERE unitaCri IN(" . implode(",", $utentiProvincia) . ") ORDER BY ultimaModificaData DESC LIMIT 1")->fetch_object();
    $provPercentualeCompletamento = (int)($provCompilazioneCompletata * 100 / ($provCompilazioneInCorso + $provCompilazioneCompletata));
	}

	$PAGE_CONTENT .= drawChart($provName, $provPercentualeCompletamento, $numeroRadio, $numeroRipetitori, $ultimoRadio->dataFormat, $ultimoRipetitori->dataFormat);
}

if($LOGIN->getUserData("type") == 1) {
  $PAGE_CONTENT .= "<table>
    <tr>
      <th>Compilazione in corso</th>
      <th>Compilazione completata</th>
    </tr>
    <tr>
      <td style='vertical-align:top;'>\n";
        while($rcUtentiCompilazioneInCorso = $rsUtentiCompilazioneInCorso->fetch_object()) {
          $PAGE_CONTENT .= $rcUtentiCompilazioneInCorso->realName . " [<a href='javascript:if(confirm(\"Chiudere la compilazione per " . htmlentities($rcUtentiCompilazioneInCorso->realName, ENT_QUOTES, "utf-8") . "?\")) location.href=\"" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;chiudi=" . urlencode($rcUtentiCompilazioneInCorso->username) . "\";'>Chiudi</a>]<br />\n";
        }
      $PAGE_CONTENT .= "</td>
      <td style='vertical-align:top;'>\n";
        while($rcUtentiCompilazioneCompletata = $rsUtentiCompilazioneCompletata->fetch_object()) {
          $PAGE_CONTENT .= $rcUtentiCompilazioneCompletata->realName . " [<a href='javascript:if(confirm(\"Riaprire la compilazione per " . htmlentities($rcUtentiCompilazioneCompletata->realName, ENT_QUOTES, "utf-8") . "?\")) location.href=\"" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;riapri=" . urlencode($rcUtentiCompilazioneCompletata->username) . "\";'>Riapri</a>]<br />\n";
        }
      $PAGE_CONTENT .= "</td>
    </tr>
  </table>\n";
}

logMessage("Visualizzato stato compilazione");

//==================================================================================================

function drawChart($label, $percentage, $radio, $ripetitori, $dataUltimaRadio, $dataUltimoRipetitore) {
  return "<table>
    <tr>
      <td style='font-weight:bold; width:130px; text-align:right;'>" . htmlentities($label, ENT_QUOTES, "utf-8") . ":</td>
      <td style='border:1px solid " . (($radio + $ripetitori) ? "#C00" : "#CC0") . "; width:500px; padding:0;'><img src='/img/redPixel.png' style='height:25px; width:" . ($percentage * 5) . "px;' alt='" . $percentage . "%' title='" . $percentage . "%'  /></td>
      <td style='width:300px; font-size:smaller;'>Radio: " . (int)$radio . " " . (($radio) ? " (" . $dataUltimaRadio . ")" : "") . " - Ripetitori: " . (int)$ripetitori . (($ripetitori) ? " (" . $dataUltimoRipetitore . ")" : "") . "</td>
    </tr>
  </table>\n";
}
?>