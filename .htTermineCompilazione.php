<?php
//20110111.006
$PAGE_TITLE = "Termine compilazione";

if($_GET["termina"] == "1") {
  $DBL->query("UPDATE users SET compilazioneCompletata=true, compilazioneCompletataData=NOW(), compilazioneCompletataIp='" . $_SERVER["REMOTE_ADDR"] . "' WHERE username='" . $DBL->real_escape_string(stripslashes($LOGIN->getUserData("username"))) . "'");
  logMessage("Registrato termine compilazione");
  header("Location: " . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"]);
  die();
}

$PAGE_CONTENT = "<h1>Termine compilazione</h1>
<div style='margin-top:10px;'>\n";
  if($LOGIN->getUserData("compilazioneCompletata") == 1) {
    $PAGE_CONTENT .= "La compilazione del censimento è stata chiusa il <b>" . $LOGIN->getUserData("compilazioneCompletataData") . "</b> da <b>" . $LOGIN->getUserData("compilazioneCompletataIp") . "</b>.";
  } else {
    $PAGE_CONTENT .= "Quando la compilazione del censimento è terminata, clicca qua: <input type='button' value='Termina compilazione' onclick=\"if(confirm('ATTENZIONE, una ulteriore conferma chiuderà il censimento e non potranno essere più fatte modifiche o aggiunte al database senza intervento di un amministratore') && confirm('Terminare la compilazione del censimento?')) location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;termina=1';\" />";
  }
$PAGE_CONTENT .= "</div>";
?>