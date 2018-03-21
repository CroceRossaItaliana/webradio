<?php
/**
 * @package CRI Web Radio
 * @author WizLab.it
 * @version 20180321.133
 */

/*===========================================================================
- Class: Objects
===========================================================================*/

class Objects {
  /*---------------------------------------------------------------------------
  - Method: getUtentiFields
  ---------------------------------------------------------------------------*/
  public static function getUtentiFields() {
    $fields = array(
      "username" => array("name"=>"Username", "tip"=>"Usare solo caratteri alfanumerici minuscoli, senza spazi", "size"=>15, "maxlen"=>30, "regexp"=>"/^[a-z0-9.]*$/", "mandatory"=>true, "readonlyOnEdit"=>true, "idField"=>true),
      "password" => array("name"=>"Password", "type"=>"password", "size"=>15, "maxlen"=>15, "mandatoryOnCreate"=>true),
      "type" => array("name"=>"Tipo", "type"=>"select", "mandatory"=>true, "values"=>$GLOBALS["LOGIN"]->getUserTypes(), "readonlyOnEdit"=>true),
      "referenceUser" => array("name"=>"Comitato di riferimento", "type"=>"select", "values"=>getUnitaCri("type IN(2, 3, 4, 6, 7, 8, 9, 10)")),
      "realName" => array("name"=>"Nome", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "maglia" => array("name"=>"Maglia", "type"=>"checkbox", "values"=>getElencoMaglie()),
      "provincia" => array("name"=>"Provincia", "type"=>"select", "values"=>getProvince()),
      "nomeResponsabile" => array("name"=>"Nome responsabile", "size"=>40, "maxlen"=>40, "mandatory"=>true),
      "telefonoResponsabile" => array("name"=>"Telefono responsabile", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "emailResponsabile" => array("name"=>"E-mail responsabile", "type"=>"email", "size"=>60, "maxlen"=>60, "mandatory"=>true),
      "status" => array("name"=>"Status", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
    );
    return $fields;
  }
  public static function getProfiloUtenteFields() {
    $fields = Objects::getUtentiFields();
    $availableFields = array("username", "nomeResponsabile", "telefonoResponsabile", "emailResponsabile");
    foreach($fields as $fieldId=>&$fieldParams) {
      if(!in_array($fieldId, $availableFields)) {
        $fieldParams = null;
      }
    }
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getRadioFields
  ---------------------------------------------------------------------------*/
  public static function getRadioFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "unitaCri" => array("name"=>"Unità CRI", "type"=>"select", "values"=>getUnitaCri(), "mandatory"=>true),
      "maglia" => array("name"=>"Maglia", "type"=>"select", "values"=>getElencoMaglie($GLOBALS["LOGIN"]->getUserData("maglia")), "mandatory"=>true),
      "tipo" => array("name"=>"Tipo radio", "type"=>"select", "mandatory"=>true, "values"=>$GLOBALS["RADIO_TIPO"]),
      "localita" => array("name"=>"Località", "size"=>50, "maxlen"=>120, "tip"=>"Solo per radio fisse"),
      "posizione" => array("name"=>"Posizione", "type"=>"point", "xLabel"=>"Longitudine", "yLabel"=>"Latitudine", "tip"=>"Solo per radio fisse", "useDMS"=>true),
      "altitudine" => array("name"=>"Altitudine", "type"=>"numeric", "size"=>4, "maxlen"=>4, "minvalue"=>0, "maxvalue"=>9999, "tip"=>"In metri, solo per radio fisse"),
      "ripetitoreId" => array("name"=>"Ripetitore", "tip"=>"Solo per radio fisse"),
      "matricola" => array("name"=>"Matricola", "size"=>20, "maxlen"=>20, "mandatory"=>true),
      "modelloRadio" => array("name"=>"Modello radio", "type"=>"select", "values"=>getModelliRadio(), "mandatory"=>true),
      "modelloAntenna" => array("name"=>"Modello antenna", "type"=>"select", "values"=>getModelliAntenne(), "mandatory"=>true),
      "siglaRadio" => array("name"=>"Sigla radio", "tip"=>"Usare caratteri numerici,<br />o il valore <b>FFFFFF</b> se senza selettiva.<br /><a href='javascript:ADMIN.radioCalcolaSelettiva();'>Calcolo automatico selettiva</a>", "size"=>6, "maxlen"=>6, "regexp"=>"/^([0-9]|FFFFFF)*$/", "mandatory"=>true),
      "numeroInventario" => array("name"=>"Numero di inventario", "size"=>30, "maxlen"=>30),
      "targaAutomezzo" => array("name"=>"Targa automezzo", "size"=>15, "maxlen"=>15, "tip"=>"Solo per apparati veicolari"),
      "acquistatoDaVolontario" => array("name"=>"Apparato acquistato da Volontario", "type"=>"checkbox", "values"=>array("1"=>"Apparato acquistato da Volontario")),
      "utilizzatore" => array("name"=>"Utilizzatore", "size"=>50, "maxlen"=>50),
      "contrattoAssistenza" => array("name"=>"Contratto assistenza", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "riferimentoAssistenza" => array("name"=>"Riferimento assistenza", "size"=>60, "maxlen"=>200),
      "note" => array("name"=>"Note", "type"=>"textarea", "cols"=>50, "rows"=>5),
      "escludiDaElencoSedi" => array("name"=>"Escludi da elenco sedi", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "escludiDaSchedaTecnica" => array("name"=>"Escludi da scheda tecnica", "type"=>"select", "values"=>BasicTable::$basicStatus),
    	"ultimaModificaData" => array("name"=>"Data ultima modifica", "manualQuery"=>"true"),
      "ultimaModificaUser" => array("name"=>"Utente ultima modifica", "manualQuery"=>"true"),
    );

    if($GLOBALS["LOGIN"]->getUserData("type") == "5") {
      $fields["unitaCri"] = array("type"=>"hidden", "manualQuery"=>"true");
    }
    if($GLOBALS["LOGIN"]->getUserData("type") != "1") {
      $fields["escludiDaElencoSedi"] = $fields["escludiDaSchedaTecnica"] = null;
    }
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getRipetitoriFields
  ---------------------------------------------------------------------------*/
  public static function getRipetitoriFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "unitaCri" => array("name"=>"Unità CRI", "type"=>"select", "values"=>getUnitaCri(), "mandatory"=>true),
      "maglia" => array("name"=>"Maglia", "type"=>"select", "values"=>getElencoMaglie($GLOBALS["LOGIN"]->getUserData("maglia")), "mandatory"=>true),
      "tipo" => array("name"=>"Tipo ripetitore", "type"=>"select", "mandatory"=>true, "values"=>$GLOBALS["RIPETITORE_TIPO"]),
      "numero" => array("name"=>"Numero", "type"=>"numeric", "size"=>2, "maxlen"=>2, "minvalue"=>1, "maxvalue"=>99, "mandatory"=>true),
      "localitaCollegata" => array("name"=>"Località", "size"=>50, "maxlen"=>120, "mandatory"=>true),
      "posizione" => array("name"=>"Posizione", "type"=>"point", "xLabel"=>"Longitudine", "yLabel"=>"Latitudine", "useDMS"=>true),
      "altitudine" => array("name"=>"Altitudine", "type"=>"numeric", "size"=>4, "maxlen"=>4, "minvalue"=>0, "maxvalue"=>9999, "tip"=>"In metri, solo per ponti radio"),
      "ripetitoreAttivo" => array("name"=>"Ripetitore attivo", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
      "ospitante" => array("name"=>"Ospitante", "size"=>50, "maxlen"=>120),
      "contrattoLocazione" => array("name"=>"Contratto locazione", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "canoneAffitto" => array("name"=>"Canone affitto", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "quotaCanone" => array("name"=>"Quota canone", "type"=>"numeric", "size"=>10, "maxlen"=>10, "minvalue"=>0, "maxvalue"=>999999, "tip"=>"Valore in euro. Es.: <b>350.00</b>"),
      "fonteAlimentazione1" => array("name"=>"Fonte alimentazione primaria", "type"=>"select", "values"=>$GLOBALS["RIPETITORE_ALIMENTAZIONE_1"]),
      "fonteAlimentazione2" => array("name"=>"Fonte alimentazione secondaria", "type"=>"select", "values"=>$GLOBALS["RIPETITORE_ALIMENTAZIONE_2"]),
      "contatoreEnelACaricoCri" => array("name"=>"Contatore Enel a carico CRI", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "contrattoAssistenza" => array("name"=>"Contratto assistenza", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "riferimentoAssistenza" => array("name"=>"Riferimento assistenza", "size"=>60, "maxlen"=>200),
      "note" => array("name"=>"Note", "type"=>"textarea", "cols"=>50, "rows"=>5),
      "ultimaModificaData" => array("name"=>"Data ultima modifica", "manualQuery"=>"true"),
      "ultimaModificaUser" => array("name"=>"Utente ultima modifica", "manualQuery"=>"true"),
    );

    if($GLOBALS["LOGIN"]->getUserData("type") == "5") {
      $fields["unitaCri"] = array("type"=>"hidden", "manualQuery"=>"true");
    }
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getRipetitoriSezioniFields
  ---------------------------------------------------------------------------*/
  public static function getRipetitoriSezioniFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "idRipetitore" => array("type"=>"hidden"),
      "tipo" => array("name"=>"Tipo sezione", "type"=>"select", "mandatory"=>true, "values"=>$GLOBALS["RIPETITORE_SEZIONE_TIPO"]),
      "matricola" => array("name"=>"Matricola", "size"=>20, "maxlen"=>20, "mandatory"=>true),
      "localitaCollegata" => array("name"=>"Località collegata", "size"=>50, "maxlen"=>120, "tip"=>"Solo per Sezione UHF"),
      "funzione" => array("name"=>"Funzione", "size"=>50, "maxlen"=>120),
      "modelloRipetitore" => array("name"=>"Modello ripetitore", "type"=>"select", "values"=>getModelliRipetitore(), "mandatory"=>true),
      "modelloAntenna1" => array("name"=>"Modello antenna 1", "type"=>"select", "values"=>getModelliAntenne(), "mandatory"=>true),
      "polarizzazioneAntenna1" => array("name"=>"Polarizzazione antenna 1", "type"=>"select", "values"=>$GLOBALS["ANTENNE_POLARIZZAZIONI"]),
      "modelloAntenna2" => array("name"=>"Modello antenna 2", "type"=>"select", "values"=>getModelliAntenne()),
      "polarizzazioneAntenna2" => array("name"=>"Polarizzazione antenna 2", "type"=>"select", "values"=>$GLOBALS["ANTENNE_POLARIZZAZIONI"]),
      "modelloAntenna3" => array("name"=>"Antenna diversity", "type"=>"select", "values"=>getModelliAntenne()),
      "polarizzazioneAntenna3" => array("name"=>"Polarizzazione antenna diversity", "type"=>"select", "values"=>$GLOBALS["ANTENNE_POLARIZZAZIONI"]),
      "canale" => array("name"=>"Canale", "type"=>"select", "values"=>getCanali("elencoCanali"), "tip"=>"Solo per Sezione VHF"),
      "identita" => array("name"=>"Identità", "tip"=>"Usare caratteri numerici e lettere da A a F, senza spazi", "size"=>6, "maxlen"=>6, "regexp"=>"/^[a-fA-F0-9]*$/"),
      "tonoSubAudio" => array("name"=>"Tono Sub Audio", "type"=>"select", "values"=>getToniSubAudio()),
      "frequenzaRicezione" => array("name"=>"Frequenza Ricezione", "type"=>"numeric", "size"=>11, "maxlen"=>11, "minvalue"=>0, "maxvalue"=>99999, "tip"=>"Valore in MHz. Es.: <b>436.9625</b><br />Solo per sezioni UHF/SHF"),
      "frequenzaTrasmissione" => array("name"=>"Frequenza Trasmissione", "type"=>"numeric", "size"=>11, "maxlen"=>11, "minvalue"=>0, "maxvalue"=>99999, "tip"=>"Valore in MHz. Es.: <b>436.9625</b><br />Solo per sezioni UHF/SHF"),
      "modalitaSincronizzazione" => array("name"=>"Modalità di Sincronizzazione", "type"=>"checkbox", "values"=>getModalitaSincronizzazione(), "tip"=>"Solo per Sezione VHF"),
      "note" => array("name"=>"Note", "type"=>"textarea", "cols"=>50, "rows"=>5),
      "ultimaModificaData" => array("name"=>"Data ultima modifica", "manualQuery"=>"true"),
      "ultimaModificaUser" => array("name"=>"Utente ultima modifica", "manualQuery"=>"true"),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getModelliRadioFields
  ---------------------------------------------------------------------------*/
  public static function getModelliRadioFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "produttore" => array("name"=>"Produttore", "type"=>"tagList", "valuesSessionVar"=>"RADIO_PRODUTTORI", "singleSelection"=>true, "mandatory"=>true),
      "modello" => array("name"=>"Modello", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "capitolato" => array("name"=>"A capitolato", "type"=>"select", "values"=>$GLOBALS["RADIO_CAPITOLATO"]),
      "omologazione" => array("name"=>"Omologazione", "type"=>"select", "values"=>BasicTable::$basicStatus),
    	"image" => array("name"=>"Immagine JPG", "type"=>"fileImage", "tip"=>"Immagine in formato <b>JPG</b>", "path"=>$GLOBALS["PATHS"]["modelliRadioJpg"], "widthBig"=>250, "widthSmall"=>90),
      "status" => array("name"=>"Status", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getModelliRipetitoreFields
  ---------------------------------------------------------------------------*/
  public static function getModelliRipetitoreFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "produttore" => array("name"=>"Produttore", "type"=>"tagList", "valuesSessionVar"=>"RIPETITORI_PRODUTTORI", "singleSelection"=>true, "mandatory"=>true),
      "modello" => array("name"=>"Modello", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "capitolato" => array("name"=>"A capitolato", "type"=>"select", "values"=>$GLOBALS["RADIO_CAPITOLATO"]),
      "omologazione" => array("name"=>"Omologazione", "type"=>"select", "values"=>BasicTable::$basicStatus),
      "image" => array("name"=>"Immagine JPG", "type"=>"fileImage", "tip"=>"Immagine in formato <b>JPG</b>", "path"=>$GLOBALS["PATHS"]["modelliRipetitoriJpg"], "widthBig"=>250, "widthSmall"=>90),
      "status" => array("name"=>"Status", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getModelliAntenneFields
  ---------------------------------------------------------------------------*/
  public static function getModelliAntenneFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "produttore" => array("name"=>"Produttore", "type"=>"tagList", "valuesSessionVar"=>"ANTENNE_PRODUTTORI", "singleSelection"=>true, "mandatory"=>true),
      "modello" => array("name"=>"Modello", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "guadagno" => array("name"=>"Guadagno", "type"=>"numeric", "size"=>5, "maxlen"=>5, "minvalue"=>0, "maxvalue"=>65000, "mandatory"=>true),
      "image" => array("name"=>"Immagine JPG", "type"=>"fileImage", "tip"=>"Immagine in formato <b>JPG</b>", "path"=>$GLOBALS["PATHS"]["modelliAntenneJpg"], "widthBig"=>250, "widthSmall"=>90),
      "status" => array("name"=>"Status", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getModalitaSincronizzazioneFields
  ---------------------------------------------------------------------------*/
  public static function getModalitaSincronizzazioneFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "modalitaSincronizzazione" => array("name"=>"Modalità di sincronizzazione", "size"=>50, "maxlen"=>50, "mandatory"=>true),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getMaglieFields
  ---------------------------------------------------------------------------*/
  public static function getMaglieFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "codice" => array("name"=>"Codice", "size"=>6, "maxlen"=>6, "mandatory"=>true),
      "provincia" => array("name"=>"Provincia", "size"=>50, "maxlen"=>120, "mandatory"=>true),
      "canale" => array("name"=>"Canale", "type"=>"select", "values"=>getCanali("elencoCanali"), "mandatory"=>true),
      "selettive" => array("name"=>"Selettive", "tip"=>"Elenco delle selettive separate<br />da virgola, nel formato <b>ZZXXXX</b> dove<br /><b>ZZ</b> corrisponde al codice provinciale", "size"=>60, "maxlen"=>100, "mandatory"=>true),
      "lunghezzaCollegamento" => array("name"=>"Lunghezza del collegamento", "type"=>"select", "values"=>$GLOBALS["MAGLIA_LUNGHEZZA_COLLEGAMENTO"]),
      "visualizzaScrittaComunicazioneNegataTraStazioniFisse" => array("name"=>"Visualizza scritta \"Le stazioni fisse non comunicano fra di loro...\"", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
      "numeroAutorizzazione" => array("name"=>"Numero autorizzazione", "size"=>20, "maxlen"=>20),
      "scadenzaAutorizzazione" => array("name"=>"Data scadenza autorizzazione", "type"=>"date"),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getCanaliFields
  ---------------------------------------------------------------------------*/
  public static function getCanaliFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "canale" => array("name"=>"Canale", "type"=>"numeric", "minvalue"=>1, "maxvalue"=>50, "mandatory"=>true),
      "frequenzaTx" => array("name"=>"Frequenza TX", "type"=>"numeric", "minvalue"=>100, "maxvalue"=>500, "mandatory"=>true),
      "frequenzaRx" => array("name"=>"Frequenza RX", "type"=>"numeric", "minvalue"=>100, "maxvalue"=>500, "mandatory"=>true),
    );
    return $fields;
  }

  /*---------------------------------------------------------------------------
  - Method: getMessaggiFields
  ---------------------------------------------------------------------------*/
  public static function getMessaggiFields() {
    $fields = array(
      "id" => array("type"=>"hidden", "idField"=>true),
      "dataMessaggio" => array("name"=>"Data", "type"=>"date", "mandatory"=>true),
      "destinatario" => array("name"=>"Destinatario", "tip"=>"Per inviare il messaggio a tutti gli utenti inserire il carattere <b>*</b>", "size"=>30, "maxlen"=>30, "mandatory"=>true),
      "requireLogin" => array("name"=>"Richiede login", "type"=>"select", "mandatory"=>true, "values"=>BasicTable::$basicStatus),
      "oggetto" => array("name"=>"Oggetto", "size"=>60, "maxlen"=>150, "mandatory"=>true),
      "testo" => array("name"=>"Testo", "type"=>"textarea", "cols"=>50, "rows"=>5),
    );
    return $fields;
  }
}
?>