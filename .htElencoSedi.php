<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180511.019
 */

require("fpdf16/fpdf.php");

class PDF extends FPDF {
  private $cols = array(
    array("title"=>"PR", "width"=>15),
    array("title"=>"Unità", "width"=>105),
    array("title"=>"Località", "width"=>185),
    array("title"=>"Selettiva", "width"=>30),
    array("title"=>"Ch.Rip", "width"=>25),
    array("title"=>"Ch.Dir", "width"=>25),
  );

  /*---------------------------------------------------------------------------
  - Function: Header()
  ---------------------------------------------------------------------------*/
  function Header() {
    $this->Image("img/logo.jpg", 20, 15, 50);
    $this->SetXY(77, 25);
    $this->SetFont("Arial", "B", 12);
    $this->MultiCell(320, 14, utf8_decode("ATTRIBUZIONE DELLE SELETTIVE E CANALI ALLE VARIE UNITA' OPERATIVE FISSE DELLA C.R.I."));
    $this->Ln(20);
    $this->SetFont("Arial", "", 6);
    $this->SetFillColor(200, 200, 200);
    foreach($this->cols as $col) {
      $this->Cell($col["width"], 7, utf8_decode($col["title"]), "RB", "", "", 1);
    }
    $this->Ln();
  }

  /*---------------------------------------------------------------------------
  - Function: Footer()
  ---------------------------------------------------------------------------*/
  function Footer() {
    $this->SetY(-60);
    $this->SetFont("Arial", "", 5);
    $this->MultiCell(380, 7, utf8_decode("I CODICI DA ITALIA 99-90 (999990) AD ITALIA 99-99 (999999) SONO ASSEGNATI ALLA COMMISSIONE NAZIONALE RADIO DEL COMITATO CENTRALE\nI CODICI DI IDENTIFICAZIONE ASSEGNATI AI REFERENTI REGIONALI RADIOCOMUNICAZIONI SONO COSTITUITI DAL NOME/CAP DEL CAPOLUOGO DI REGIONE SEGUITO DALLE CIFRE 7000 (ES. ROMA 70-00 - D.R.R. LAZIO)\nI CODICI DI IDENTIFICAZIONE ASSEGNATI AI REFERENTI PROVINCIALI RADIOCOMUNICAZIONI SONO COSTITUITI DAL NOME/CAP DEL CAPOLUOGO DI PROVINCIA SEGUITO DALLE CIFRE 8000 (ES. ANCONA 80-00 - R.P.R. ANCONA)"), 0, "C");
    $this->Ln(5);
    $this->SetFont("Arial", "I", 6);
    $this->Cell(0, 8, "Generato il " . date("d/m/Y") . ", alle ore " . date("H:i"), 0, 0, "L");
    $this->Cell(0, 8, "Pag. " . $this->PageNo() . "/{nb}", 0, 0, "R");
  }

  /*---------------------------------------------------------------------------
  - Function: drawElencoSedi()
  ---------------------------------------------------------------------------*/
  function drawElencoSedi() {
    $this->SetFont("Arial", "", 6);
    $provinciaCurrent = "";
    $lineHeight = 8;
    $showBorder = 0;
    $rs = $GLOBALS["DBL"]->query("SELECT users.realName, users.provincia, radio.maglia, radio.localita, radio.siglaRadio FROM radio INNER JOIN users ON radio.unitaCri=users.username AND radio.tipo=1 AND escludiDaElencoSedi=false AND users.type>2 ORDER BY users.provincia, radio.localita");
    $maglieCanali = array();
    $bgcolor = true;
    while($rc = $rs->fetch_object()) {
      if($provinciaCurrent != $rc->provincia) $this->Ln(4);
      if($rc->maglia && is_numeric($rc->maglia)) {
        if(!array_key_exists($rc->maglia, $maglieCanali)) {
          $rcMaglia = $GLOBALS["DBL"]->query("SELECT id, canale FROM maglie WHERE id=" . $rc->maglia)->fetch_object();
          $maglieCanali[$rcMaglia->id] = $rcMaglia->canale;
        }
        $rc->canale = $maglieCanali[$rc->maglia];
      }
      ($bgcolor) ? $this->SetFillColor(230, 230, 230) : $this->SetFillColor(255, 255, 255);
      $this->Cell($this->cols[0]["width"], $lineHeight, utf8_decode($rc->provincia), $showBorder, 0, "L", 1);
      $this->Cell($this->cols[1]["width"], $lineHeight, utf8_decode($rc->realName), $showBorder, 0, "L", 1);
      $this->Cell($this->cols[2]["width"], $lineHeight, utf8_decode($rc->localita), $showBorder, 0, "L", 1);
      $this->Cell($this->cols[3]["width"], $lineHeight, utf8_decode($rc->siglaRadio), $showBorder, 0, "R", 1);
      $this->Cell($this->cols[4]["width"], $lineHeight, utf8_decode($rc->canale), $showBorder, 0, "C", 1);
      $this->Cell($this->cols[5]["width"], $lineHeight, utf8_decode($rc->canale + 6), $showBorder, 1, "C", 1);
      $provinciaCurrent = $rc->provincia;
      $bgcolor = !$bgcolor;
    }
  }
}

logMessage("Scaricato elenco sedi");
$pdf = new PDF("P", "pt", "A5");
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 60);
$pdf->SetAuthor("Croce Rossa Italiana", true);
$pdf->SetCreator("Croce Rossa Italiana", true);
$pdf->SetSubject("Elenco Sedi", true);
$pdf->SetTitle("Elenco Sedi", true);
$pdf->SetMargins(20, 12, 20);
$pdf->AddPage();
$pdf->drawElencoSedi();
$pdf->Output();
?>