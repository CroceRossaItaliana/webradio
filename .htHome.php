<?php
//20110131.002
$PAGE_TITLE = "Welcome";

$rsMessaggi = $DBL->query("SELECT *, DATE_FORMAT(dataMessaggio, '%d/%m/%Y') AS dataMessaggioFormat FROM messaggi WHERE destinatario='*' OR destinatario='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($GLOBALS["LOGIN"]->getUserData("username"))) . "' ORDER BY dataMessaggio DESC");
if($LOGIN->isLoggedIn() && $rsMessaggi->num_rows) {
  $PAGE_CONTENT = "<h1>Messaggio dall'amministratore</h1>\n";
  while($rcMessaggi = $rsMessaggi->fetch_object()) {
    $PAGE_CONTENT .= "<div class='message'>
      <div class='oggetto'>" . htmlentities($rcMessaggi->oggetto, ENT_QUOTES, "utf-8") . "</div>
      <div class='data'>Data: " . htmlentities($rcMessaggi->dataMessaggioFormat, ENT_QUOTES, "utf-8") . "</div>
      <div class='testo'>" . nl2br(htmlentities($rcMessaggi->testo, ENT_QUOTES, "utf-8")) . "</div>
    </div>\n";
  }
} else {
  $PAGE_CONTENT = "<script type='text/javascript'>document.getElementById('main').style.display='none';</script>";
}
?>