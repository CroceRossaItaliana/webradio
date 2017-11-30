<?php
//20120506.078

/*===========================================================================
- Class: Authentication
- This class perform authentications. Must be extended.
===========================================================================*/
class Authentication {
  private $forceLogout = false;
  private $userTypes = array(
    "0"  => "Guest",
    "1"  => "Amministratore",
    "2"  => "Comitato centrale",
    "3"  => "Comitato regionale",
    "4"  => "Comitato provinciale",
    "5"  => "Unità CRI",
  	"6"  => "S.I.E.", //Permessi di Comitato regionale
  	"7"  => "C.I.E.", //Permessi di Comitato provinciale
  	"8"  => "Centro Mob.", //Permessi di Comitato provinciale
  	"9"  => "N.O.P.I.", //Permessi di Comitato provinciale
    "10" => "Centro di Mobilitazione", //Permessi di Comitato provinciale
  );
  private $sections = array(
    "Home" => array("title"=>"Home", "types"=>"all"),
    "AlboOperatoriTLC" => array("title"=>"Albo Operatori TLC", "types"=>"all"),
  	"Utenti" => array("title"=>"Utenti", "types"=>array(1)),
    "Radio" => array("title"=>"Radio", "types"=>array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
    "Ripetitori" => array("title"=>"Ripetitori", "types"=>array(0, 1, 2, 3, 4, 6, 7, 8, 9, 10)),
    "Mappa" => array("title"=>"Mappa", "types"=>array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
    "Docs" => array("title"=>"Documenti", "types"=>array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), "hideFromMenu"=>true),
    "RipetitoriSezioni" => array("title"=>"Ripetitori Sezioni", "types"=>array(0, 1, 2, 3, 4, 6, 7, 8, 9, 10), "hideFromMenu"=>true),
    "DocumentazioneMinistero" => array("title"=>"Documentazione Ministero", "types"=>array(1, 2)),
    "ModelliRadio" => array("title"=>"Modelli Radio", "types"=>array(1)),
    "ModelliRipetitori" => array("title"=>"Modelli Ripetitori", "types"=>array(1)),
    "ModelliAntenne" => array("title"=>"Modelli Antenne", "types"=>array(1)),
    "ModalitaSincronizzazione" => array("title"=>"Modalità Sincronizzazione", "types"=>array(1)),
    "Maglie" => array("title"=>"Maglie Radio", "types"=>array(1)),
    "Canali" => array("title"=>"Canali VHF", "types"=>array(1)),
    "TermineCompilazione" => array("title"=>"Termine Compilazione", "types"=>array()),
    "StatoCompilazione" => array("title"=>"Stato Compilazione", "types"=>array(1, 2)),
    "LogAccessi" => array("title"=>"Log Accessi", "types"=>array(1)),
    "ProfiloUtente" => array("title"=>"Profilo Utente", "types"=>"login"),
    "Messaggi" => array("title"=>"Messaggi", "types"=>array(1)),
    "Strumenti" => array("title"=>"Strumenti", "types"=>array(1)),
    "ElencoSedi" => array("title"=>"Elenco Sedi", "types"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
    "SegnalazioneInterferenze" => array("title"=>"Segnalazione interferenze", "types"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
    "CodiciCentralizzatiEmergenze" => array("title"=>"Codici centralizzati ed emergenze", "types"=>array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)),
    //"IscrizioneCorsoFormatoriTLC" => array("title"=>"Iscrizione Corso Formatori TLC", "types"=>array(1)),
  	"Cerca" => array("types"=>"login", "hideFromMenu"=>true),
    "CambiaPassword" => array("title"=>"Cambia password", "types"=>"login"),
  	"Info" => array("title"=>"Info", "types"=>"login"),
  	"Ajax" => array("types"=>"all", "hideFromMenu"=>true),
  );
  protected $userData = array();

  /*---------------------------------------------------------------------------
  - Constructor
  ---------------------------------------------------------------------------*/
  function __construct($forceLogout=false) {
    if($forceLogout == "logout") {
      $this->forceLogout = true;
      session_unset();
    }
    $this->login();
  }

  /*---------------------------------------------------------------------------
  - Method: login
  ---------------------------------------------------------------------------*/
  public function login() {
    if($_POST["loginUsername"] && $_POST["loginPassword"]) {
      $username = $_POST["loginUsername"];
      $password = $_POST["loginPassword"];
    } elseif($_SESSION["Authentication"]["username"] && $_SESSION["Authentication"]["token"]) {
      $username = $_SESSION["Authentication"]["username"];
      $password = $_SESSION["Authentication"]["token"];
    }

    if($username && $password && !$this->forceLogout) {
      $userData = $this->loadUserData($username, $password);
      if(is_array($userData)) {
        $this->userData = $userData;
        $this->userData["isLoggedIn"] = true;
        if(!is_array($_SESSION["Authentication"])) {
          logMessage("Login", $this->userData["username"]);
        }
        $_SESSION["Authentication"] = array(
          "username" => $this->userData["username"],
          "token" => sha1($this->userData["username"] . $_SERVER["REMOTE_ADDR"] . date("YmdHis")),
        );
        $GLOBALS["DBL"]->query("UPDATE users SET token='" . $_SESSION["Authentication"]["token"] . "' WHERE username='" . $GLOBALS["DBL"]->real_escape_string($_SESSION["Authentication"]["username"]) . "' LIMIT 1");
      } else {
        session_unset();
      }
    }
  }

  /*---------------------------------------------------------------------------
  - Method: loadUserData
  ---------------------------------------------------------------------------*/
  private function loadUserData($username, $password="") {
    $userData = false;
    $username = stripslashes($username);
    if($username) {
      $rcUser = $GLOBALS["DBL"]->query("SELECT * FROM users WHERE username='" . $GLOBALS["DBL"]->real_escape_string($username) . "' " . (($password) ? " AND (password='" . sha1(stripslashes($password)) . "' OR token='" . $GLOBALS["DBL"]->real_escape_string($password) . "') AND status=true" : "") . " LIMIT 1")->fetch_assoc();
      if($rcUser["username"] == $username) {
        $userData = $rcUser;
        $userData["type"] = (int)$rcUser["type"];
        $userData["globals"] = $rcUser;
      }
    }
    return $userData;
  }

  /*---------------------------------------------------------------------------
  - Method: isLoginValid
  ---------------------------------------------------------------------------*/
  public function isLoginValid($username, $password="") {
    $username = stripslashes($username);
    if($GLOBALS["DBL"]->query("SELECT * FROM users WHERE username='" . $GLOBALS["DBL"]->real_escape_string(stripslashes($username)) . "' " . (($password) ? " AND password='" . sha1(stripslashes($password)) . "'" : ""))->num_rows === 1) return true;
    return false;
  }

  /*---------------------------------------------------------------------------
  - Method: getUserData
  ---------------------------------------------------------------------------*/
  public function getUserData($param, $htmlentities=false) {
    if(!array_key_exists($param, $this->userData) || ($param == "globals")) return false;
    return ($htmlentities) ? htmlentities($this->userData[$param], ENT_QUOTES, "utf-8") : $this->userData[$param];
  }

  /*---------------------------------------------------------------------------
  - Method: getUserType
  ---------------------------------------------------------------------------*/
  public function getUserType() {
    return array("id"=>$this->userData["type"], "name"=>$this->userTypes[$this->userData["type"]]);
  }

  /*---------------------------------------------------------------------------
  - Method: getMenuSections
  ---------------------------------------------------------------------------*/
  public function getMenuSections($htmlentities=true) {
    $menuSections = array();
    foreach($this->sections as $sectionId=>$sectionData) {
      if($this->checkType($sectionId) && !$sectionData["hideFromMenu"]) {
        $menuSections[$sectionId] = ($htmlentities) ? htmlentities($sectionData["title"], ENT_QUOTES, "utf-8") : $sectionData["title"];
      }
    }
    return $menuSections;
  }

  /*---------------------------------------------------------------------------
  - Method: getSectionTitle
  ---------------------------------------------------------------------------*/
  public function getSectionTitle($id) {
    return $this->sections[$id]["title"];
  }

  /*---------------------------------------------------------------------------
  - Method: isLoggedIn
  ---------------------------------------------------------------------------*/
  public function isLoggedIn() {
    return $this->userData["isLoggedIn"];
  }

  /*---------------------------------------------------------------------------
  - Method: isGuest
  ---------------------------------------------------------------------------*/
  public function isGuest() {
    return ($this->userData["type"] === 0);
  }

  /*---------------------------------------------------------------------------
  - Method: checkType
  ---------------------------------------------------------------------------*/
  public function checkType($section) {
    if(
      array_key_exists($section, $this->sections) &&
      (
        ($this->sections[$section]["types"] == "all") ||
        (($this->sections[$section]["types"] == "login") && $this->userData["isLoggedIn"]) ||
        (($this->sections[$section]["types"] == "logout") && !$this->userData["isLoggedIn"]) ||
        ($this->userData["isLoggedIn"] && is_array($this->sections[$section]["types"]) && in_array($this->userData["type"], $this->sections[$section]["types"]))
      )
    ) return true;
    return false;
  }

  /*---------------------------------------------------------------------------
  - Method: changePassword
  ---------------------------------------------------------------------------*/
  public function changePassword($newPassword) {
    $GLOBALS["DBL"]->query("UPDATE users SET password='" . sha1(stripslashes($newPassword)) . "' WHERE username='" . $GLOBALS["DBL"]->real_escape_string($this->userData["username"]) . "' LIMIT 1");
    return $GLOBALS["DBL"]->errno;
  }

  /*---------------------------------------------------------------------------
  - Method: getUserTypes
  ---------------------------------------------------------------------------*/
  public function getUserTypes() {
    return $this->userTypes;
  }
}
?>
