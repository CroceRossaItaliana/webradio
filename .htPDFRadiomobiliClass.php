<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180329.010
 */

/*===========================================================================
- CLASS PDF
===========================================================================*/
require("fpdf16/fpdf.php");
class PDF extends FPDF {
  private $canali;

  /*---------------------------------------------------------------------------
  - Constructor
  ---------------------------------------------------------------------------*/
  function __construct($orientation, $unit, $format) {
    $rsCanali = $GLOBALS["DBL"]->query("SELECT frequenzaTx, frequenzaRx FROM canali ORDER BY canale");
    while($rcCanali = $rsCanali->fetch_object()) {
      $this->canali["tx"][] = $rcCanali->frequenzaTx;
      $this->canali["rx"][] = $rcCanali->frequenzaRx;
    }
    $this->canali["tx"] = implode(", ", $this->canali["tx"]);
    $this->canali["rx"] = implode(", ", $this->canali["rx"]);

    parent::__construct($orientation, $unit, $format);
  }

  /*---------------------------------------------------------------------------
  - Function: Header()
  ---------------------------------------------------------------------------*/
  function Header() {
    $this->Image("img/logo.jpg", 20, 15, 50);
    $this->SetXY(77, 25);
    $this->SetFont("Arial", "B", 28);
    $this->MultiCell(600, 32, utf8_decode("Scheda riepilogativa Radiomobile"));
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
  - Function: drawCover()
  ---------------------------------------------------------------------------*/
  function drawCover() {
    $this->SetY(90);
    $this->SetFont("Times", "B", 20);
    $this->Cell(0, 20, utf8_decode("CARATTERISTICHE GENERALI"), 0, 0, "C");
    $this->Rect(80, 140, 682, 400);
    $this->SetFont("Times", "", 16);
    $this->SetY(180);
    $this->SetLeftMargin(100);
    $col1 = 310;
    $col2 = 330;

    $this->Cell($col1, 24, utf8_decode("Banda di frequenza"));
    $this->Cell($col2, 24, utf8_decode("VHF-UHF"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tipo collegamento"));
    $this->Cell($col2, 24, utf8_decode("TF"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tipo comunicazione"));
    $this->Cell($col2, 24, utf8_decode("S"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° Canali"));
    $this->Cell($col2, 24, utf8_decode(sprintf("%02d", $GLOBALS["DBL"]->query("SELECT id FROM canali")->num_rows)), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Tono subaudio"));
    $this->Cell($col2, 24, utf8_decode("156.7 Hz"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Chiamata selettiva"));
    $this->Cell($col2, 24, utf8_decode("XXXXXX"), 0, 1);

    $totaleVeicolari = $GLOBALS["DBL"]->query("SELECT id FROM radio WHERE tipo=2 AND escludiDaSchedaTecnica=0")->num_rows;
    $totalePortatili = $GLOBALS["DBL"]->query("SELECT id FROM radio WHERE tipo=3 AND escludiDaSchedaTecnica=0")->num_rows;
    $this->Cell($col1, 24, utf8_decode("N° totale stazioni terminali"));
    $this->Cell($col2, 24, utf8_decode($totaleVeicolari + $totalePortatili), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni terminali multiple destinazioni"));
    $this->Cell($col2, 24, utf8_decode("0"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni fisse"));
    $this->Cell($col2, 24, utf8_decode("0"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni mobili"));
    $this->Cell($col2, 24, utf8_decode($totaleVeicolari), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni portatili"));
    $this->Cell($col2, 24, utf8_decode($totalePortatili), 0, 1);

    $this->Cell($col1, 24, utf8_decode("N° stazioni ripetitrici"));
    $this->Cell($col2, 24, utf8_decode("0"), 0, 1);

    $this->Cell($col1, 24, utf8_decode("Lunghezza massima del collegamento"));
    $this->SetFont("", "B");
    $this->Cell($col2, 24, utf8_decode("Tutto il territorio nazionale"), 0, 1);
    $this->SetFont("", "");
  }

  /*---------------------------------------------------------------------------
  - Function: drawVeicolari()
  ---------------------------------------------------------------------------*/
  function drawVeicolari() {
    $columns = array(
      "1" => array("title"=>"N.Veicolari", "width"=>64),
      "2" => array("title"=>"Apparati", "width"=>165),
      "3" => array("title"=>"Antenne", "width"=>75),
      "4" => array("title"=>"Emissione (MHz)", "width"=>515),
    );

    $i = $row = 0;
    $rs = $GLOBALS["DBL"]->query("SELECT COUNT(id) AS numeroVeicolari, modelloRadio FROM radio WHERE tipo=2 AND escludiDaSchedaTecnica=0 GROUP BY modelloRadio ORDER BY numeroVeicolari DESC");
    while($rc = $rs->fetch_object()) {
      $this->SetLeftMargin(15);
      if(($i == 0) || ($row > 20)) {
        $row = 0;
        $this->AddPage();
        $this->SetFont("Times", "BI", 12);
        foreach($columns as $col) {
          $this->Cell($col["width"], 15, utf8_decode($col["title"]), "BR");
        }
        $this->Ln();
      }
      $this->SetFont("Times", "", 10);
      $rowY = 83 + 15 + (11 * 2 * $row);

      //Colonna 1
      $this->SetXY(15, $rowY);
      $this->Write(11 * 2, utf8_decode($rc->numeroVeicolari));

      //Colonna 2
      $radio = getRadioById($rc->modelloRadio);
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"]);
      $this->Write(11, utf8_decode($radio["produttore"] . " " . $radio["modello"]));

      //Colonna 3
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"]);
      $this->Write(11, utf8_decode("Stilo 5/8\nGuad. 4db"));

      //Colonna 4
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"]);
      $this->SetFont("Times", "", 8);
      $this->Write(11, utf8_decode("TX " . $this->canali["tx"] . "\nRX " . $this->canali["rx"]));
      $this->SetFont("Times", "", 10);

      $lineY = $rowY + (11 * 2);
      $this->Line(15, $lineY, (15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + $columns["4"]["width"]), $lineY);

      $i++;
      $row++;
    }
  }

  /*---------------------------------------------------------------------------
  - Function: drawPortatili()
  ---------------------------------------------------------------------------*/
  function drawPortatili() {
    $columns = array(
      "1" => array("title"=>"N.Portatili", "width"=>58),
      "2" => array("title"=>"Apparati", "width"=>140),
      "3" => array("title"=>"Antenne", "width"=>65),
      "4" => array("title"=>"Emissione (MHz)", "width"=>550),
    );

    $i = $row = 0;
    $rs = $GLOBALS["DBL"]->query("SELECT COUNT(id) AS numeroPortatili, modelloRadio FROM radio WHERE tipo=3 AND escludiDaSchedaTecnica=0 GROUP BY modelloRadio ORDER BY numeroPortatili DESC");
    while($rc = $rs->fetch_object()) {
      $this->SetLeftMargin(15);
      if(($i == 0) || ($row > 20)) {
        $row = 0;
        $this->AddPage();
        $this->SetFont("Times", "BI", 12);
        foreach($columns as $col) {
          $this->Cell($col["width"], 15, utf8_decode($col["title"]), "BR");
        }
        $this->Ln();
      }
      $this->SetFont("Times", "", 10);
      $rowY = 83 + 15 + (11 * 2 * $row);

      //Colonna 1
      $this->SetXY(15, $rowY);
      $this->Write(11 * 2, utf8_decode($rc->numeroPortatili));

      //Colonna 2
      $radio = getRadioById($rc->modelloRadio);
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"]);
      $this->Write(11, utf8_decode($radio["produttore"] . "\n" . $radio["modello"]));

      //Colonna 3
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"]);
      $this->Write(11, utf8_decode("Gomma\nGuad. 0db"));

      //Colonna 4
      $this->SetY($rowY);
      $this->SetLeftMargin(15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"]);
      $this->SetFont("Times", "", 8);
      $this->Write(11, utf8_decode("TX " . $this->canali["tx"] . "\nRX " . $this->canali["rx"]));
      $this->SetFont("Times", "", 10);

      $lineY = $rowY + (11 * 2);
      $this->Line(15, $lineY, (15 + $columns["1"]["width"] + $columns["2"]["width"] + $columns["3"]["width"] + $columns["4"]["width"]), $lineY);

      $i++;
      $row++;
    }
  }
}
?>