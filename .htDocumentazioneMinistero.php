<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180521.059
 */

$PAGE_TITLE = "Documentazione Ministero";

$PAGE_CONTENT = "<h1>Documentazione Ministero</h1>\n";

$USER_TYPE = $LOGIN->getUserType();
$USER_TYPE = $USER_TYPE["id"];
$USER_MAGLIE = arraizeCsv($LOGIN->getUserData("maglia"));

switch($_REQUEST["cmd2"]) {
  case "maglie":
    if(is_numeric($_GET["maglia"])) {
      if(($USER_TYPE == 3) && !in_array($_GET["maglia"], $USER_MAGLIE)) die("...");
      $maglia = $DBL->query("SELECT * FROM maglie WHERE id=" . $_GET["maglia"])->fetch_object();
      if(!$maglia->id || ($maglia->id != $_GET["maglia"])) die(".");

      require(".htPDFMaglieClass.php");
      $pdf = new PDF("L", "pt", "A4", $maglia->id, $maglia);
      $pdf->AliasNbPages();
      $pdf->SetAutoPageBreak(false);
      $pdf->SetAuthor("Croce Rossa Italiana", true);
      $pdf->SetCreator("Croce Rossa Italiana", true);
      $pdf->SetSubject("Maglia di " . $maglia->provincia, true);
      $pdf->SetTitle("Maglia di " . $maglia->provincia, true);

      //Copertina caratteristiche generali
      $pdf->AddPage();
      $pdf->drawCover();

      //Disegna mappa
      $pdf->AddPage();
      $pdf->drawMap();

      //Disegna elenco stazioni fisse
      $pdf->drawStazioniFisse();

      //Disegna elenco ripetitori
      $pdf->drawRipetitori();

      //Butta fuori PDF
      $pdf->Output();

      logMessage("Generata documentazione per maglia " . $maglia->provincia);
      die();
    }
    die("Err. 98349862");
    break;

  case "radiomobili":
    require(".htPDFRadiomobiliClass.php");
    $pdf = new PDF("L", "pt", "A4");
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(false);
    $pdf->SetAuthor("Croce Rossa Italiana", true);
    $pdf->SetCreator("Croce Rossa Italiana", true);
    $pdf->SetSubject("Scheda riepilogativa Radiomobile", true);
    $pdf->SetTitle("Scheda riepilogativa Radiomobile", true);

    //Applica filtro maglie
    $FILTRO_MAGLIE = false;
    if($USER_TYPE == 3) $FILTRO_MAGLIE = $USER_MAGLIE;

    //Copertina caratteristiche generali
    $pdf->AddPage();
    $pdf->drawCover($FILTRO_MAGLIE);

    //Disegna elenco veicolari
    $pdf->drawVeicolari($FILTRO_MAGLIE);

    //Disegna elenco portatili
    $pdf->drawPortatili($FILTRO_MAGLIE);

    //Butta fuori PDF
    $pdf->Output();

    logMessage("Generata Scheda riepilogativa Radiomobile");
    die();
    break;

  default:

    $PAGE_CONTENT .= "<h2>Generazione documenti per il Ministero</h2>
    <form method='get' action='" . $_SERVER["SCRIPT_NAME"] . "'>
      <input type='hidden' name='cmd' value='" . $_REQUEST["cmd"] . "' />
      <input type='hidden' name='cmd2' value='maglie' />
      <div style='margin-top:40px; text-align:center;'>
        Selezione maglia: <select name='maglia'>\n";
          foreach(getElencoMaglie() as $magliaId=>$magliaNome) {
            if(($USER_TYPE == 3) && !in_array($magliaId, $USER_MAGLIE)) continue;
            $PAGE_CONTENT .= "<option value='" . $magliaId . "'>" . htmlentities($magliaNome, ENT_QUOTES, "utf-8") . "</option>\n";
          }
        $PAGE_CONTENT .= "</select>
        <input type='submit' value='Genera PDF' />
      </div>
      <div style='margin-top:40px; text-align:center;'>
        <input type='button' value='Genera Scheda riepilogativa Radiomobile' onclick=\"location.href='" . $_SERVER["SCRIPT_NAME"] . "?cmd=" . $_REQUEST["cmd"] . "&cmd2=radiomobili'\" />
      </div>
    </form>\n";
    break;
}
?>