<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180321.005
 */

$PAGE_TITLE = "Welcome";

$PAGE_CONTENT = "<h1>Messaggi dall'amministratore</h1>\n";

if($LOGIN->isLoggedIn()) {
  $WHERE = "destinatario='*' OR destinatario='" . dbEsc($LOGIN->getUserData("username")) . "'";
} else {
  $WHERE = "destinatario='*' AND requireLogin=0";
}
$rsMessaggi = $DBL->query("SELECT *, DATE_FORMAT(dataMessaggio, '%d/%m/%Y') AS dataMessaggioFormat FROM messaggi WHERE " . $WHERE . " ORDER BY dataMessaggio DESC");
if($rsMessaggi->num_rows > 0) {
  while($rcMessaggi = $rsMessaggi->fetch_object()) {
    $PAGE_CONTENT .= "<div class='message'>
      <div class='oggetto'>" . htmlentities($rcMessaggi->oggetto, ENT_QUOTES, "utf-8") . "</div>
      <div class='data'>Data: " . htmlentities($rcMessaggi->dataMessaggioFormat, ENT_QUOTES, "utf-8") . "</div>
      <div class='testo'>" . nl2br(htmlentities($rcMessaggi->testo, ENT_QUOTES, "utf-8")) . "</div>
    </div>\n";
  }
} else {
  $PAGE_CONTENT .= "<p><i>Nessun messaggio</i></p>";
}
?>