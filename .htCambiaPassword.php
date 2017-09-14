<?php
//20100911.002
$PAGE_TITLE = "Cambia password";

$PAGE_CONTENT = "<h1>Cambia password</h1>
<h2>Imposta una nuova password</h2>\n";

if($_GET["save"] == "1") {
  if($_POST["password"] && $_POST["newPassword"] && $_POST["newPassword2"]) {
    if(!$LOGIN->isLoginValid($LOGIN->getUserData("username"), $_POST["password"])) {
      $PAGE_CONTENT .= BasicTable::showMessageBox("La password attuale è sbagliata", true);
    } elseif($_POST["newPassword"] != $_POST["newPassword2"]) {
      $PAGE_CONTENT .= BasicTable::showMessageBox("La ripetizione della nuova password non corrisponde", true);
    } else {
      if($LOGIN->changePassword($_POST["newPassword"]) === 0) {
        $PAGE_CONTENT .= BasicTable::showMessageBox("Password cambiata\La nuova password sarà attiva dal prossimo login");
      } else {
        $PAGE_CONTENT .= BasicTable::showMessageBox("Errore durante la definizione della nuova password", true);
      }
    }
  } else {
    $PAGE_CONTENT .= BasicTable::showMessageBox("Tutti i campi del modulo devono essere compilati", true);
  }
}

$PAGE_CONTENT .= "<form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&amp;save=1' onsubmit=\"return ADMIN.checkFormOnSubmit('password', 'newPassword', 'newPassword2');\">
  <table border='0' cellspacing='5'>
    <tr>
      <th>Password attuale</th>
      <td><input type='password' name='password' id='password' value='' size='20' maxlength='20' /> (*)</td>
    </tr>
    <tr>
      <th>Nuova password</th>
      <td><input type='password' name='newPassword' id='newPassword' value='' size='20' maxlength='20' /> (*)</td>
    </tr>
    <tr>
      <th>Ripeti nuova password</th>
      <td><input type='password' name='newPassword2' id='newPassword2' value='' size='20' maxlength='20' /> (*)</td>
    </tr>
    <tr>
      <td align='center' colspan='2'><input type='submit' value='Salva' /> &nbsp; <input type='reset' value='Reset' /></td>
    </tr>
  </table>
</form>\n";
?>