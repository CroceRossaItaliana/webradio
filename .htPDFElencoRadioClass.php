<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180508.004
 */

/*===========================================================================
- CLASS PDF
===========================================================================*/
require("fpdf16/fpdf.php");
class PDF extends FPDF {
  private $canali;

  /*---------------------------------------------------------------------------
  - Function: Header()
  ---------------------------------------------------------------------------*/
  function Header() {
    $this->Image("img/logo.jpg", 20, 15, 50);
    $this->SetXY(77, 25);
    $this->SetFont("Arial", "B", 28);
    $this->MultiCell(600, 32, utf8_decode("Elenco Radio"));
    $this->SetXY(15, 80);
  }

  /*---------------------------------------------------------------------------
  - Function: Footer()
  ---------------------------------------------------------------------------*/
  function Footer() {
    $this->SetXY(727, 15);
    $this->SetFont("Times", "I", 8);
    $this->Cell(100, 10, utf8_decode("Pag. " . $this->PageNo() . "/{nb}"), 0, 0, "R");
  }

  /*---------------------------------------------------------------------------
  - Function: drawVeicolari()
  ---------------------------------------------------------------------------*/
  function drawRadio($filters) {
    $columns = array(
        "1" => array("title"=>"Unità di appartenenza", "width"=>190),
        "2" => array("title"=>"Sigla Radio", "width"=>170),
        "3" => array("title"=>"Modello Radio", "width"=>220),
        "4" => array("title"=>"Note", "width"=>230),
    );

    $i = $row = 0;
    $rs = $GLOBALS["DBL"]->query("SELECT * FROM radio WHERE " . $filters);
    while($rc = $rs->fetch_object()) {
      $this->SetLeftMargin(15);
      if(($i == 0) || ($row > 9)) {
        $row = 0;
        $this->AddPage();
        $this->SetFont("Times", "BI", 12);
        foreach($columns as $col) {
          $this->Cell($col["width"], 15, utf8_decode($col["title"]), "BR");
        }
        $this->Ln();
      }
      $this->SetFont("Times", "", 10);
      $rowY = 83 + 15 + (11 * 4 * $row);

      //Get radio data
      $posizioneAltitudine = Methods::getPosizioneFormat($rc->posizione, $rc->altitudine);
      $ripetitore = $GLOBALS["DBL"]->query("SELECT id, numero, localitaCollegata FROM ripetitori WHERE id=" . $rc->ripetitoreId)->fetch_object();
      $ripetitore = ($ripetitore->numero) ? $ripetitore->numero . ": " . $ripetitore->localitaCollegata : "";
      $rc->modelloRadio = getRadioById($rc->modelloRadio);
      $rc->modelloRadio = $rc->modelloRadio["produttore"] . " - " . $rc->modelloRadio["modello"];
      $rc->modelloAntenna = getAntennaById($rc->modelloAntenna);
      $rc->modelloAntenna = $rc->modelloAntenna["produttore"] . " - " . $rc->modelloAntenna["modello"];

      //Colonna 1
      $this->SetFont("Times", "", 10);
      $xPos = 15;
      $this->SetXY($xPos, $rowY);
      $this->Write(11, utf8_decode($rc->unitaCri));
      $this->Ln();
      $this->SetFont("Times", "I", 8);
      $this->Write(11, utf8_decode(getMagliaById($rc->maglia)));
      $this->Ln();
      $this->SetFont("Times", "I", 7);
      $this->Write(11, utf8_decode($rc->localita));
      $this->Ln();
      if($posizioneAltitudine["posizione"]["lat"] != "0") $this->Write(11, utf8_decode(html_entity_decode($posizioneAltitudine["htmlCode"])));

      //Colonna 2
      $this->SetFont("Times", "", 10);
      $xPos += $columns["1"]["width"];
      $this->SetY($rowY);
      $this->SetLeftMargin($xPos);
      $this->Write(11, utf8_decode("Sigla: " . $rc->siglaRadio));
      $this->SetFont("Times", "I", 8);
      if($rc->targaAutomezzo) {
        $this->Ln();
        $this->Write(11, utf8_decode("Targa: " . $rc->targaAutomezzo));
      }
      $this->Ln();
      $this->Write(11, utf8_decode($GLOBALS["RADIO_TIPO"][$rc->tipo]));
      $this->Ln();
      $this->SetFont("Times", "I", 7);
      $this->Write(11, utf8_decode($ripetitore));

      //Colonna 3
      $this->SetFont("Times", "", 10);
      $xPos += $columns["2"]["width"];
      $this->SetY($rowY);
      $this->SetLeftMargin($xPos);
      $this->Write(11, utf8_decode($rc->modelloRadio));
      $this->Ln();
      $this->SetFont("Times", "I", 8);
      $this->Write(11, utf8_decode($rc->modelloAntenna));
      $this->Ln();
      $this->Write(11, utf8_decode("Mat. " . $rc->matricola));
      $this->Ln();
      if($rc->numeroInventario) $this->Write(11, utf8_decode("N. Inv. " . $rc->numeroInventario));

      //Colonna 4
      $this->SetFont("Times", "", 10);
      $xPos += $columns["3"]["width"];
      $this->SetY($rowY);
      $this->SetLeftMargin($xPos);
      if($rc->utilizzatore) {
        $this->Write(11, utf8_decode("Usato da " . $rc->utilizzatore));
        $this->Ln();
      }
      $this->Write(11, utf8_decode($rc->note));

      //Separator
      $xPos += $columns["4"]["width"];
      $lineY = $rowY + (11 * 4);
      $this->Line(15, $lineY, $xPos, $lineY);
      $i++;
      $row++;
    }
  }
}
?>