<?php
	namespace Facebook\WebDriver;

	use Facebook\WebDriver\Remote\DesiredCapabilities;
	use Facebook\WebDriver\Remote\RemoteWebDriver;
	use Facebook\WebDriver\Interactions\WebDriverActions;
	use Exception;
	use Facebook\WebDriver\Chrome\ChromeOptions;
	use Facebook\WebDriver\WebDriverBy;
	use mysqli;
	use DateTime;
	use DateTimeZone;

	require_once('vendor/autoload.php');

	const EXTRA = "AND pro.id = 32922";
	$fecha_ini = "2020-12-08";
	$fecha_fi = "2020-12-20";


	const LOGIN_URL = 'https://www.instagram.com/accounts/login/';
	
	//const USERNAME = 'sat@tech-impulse.com';
	//const PASSWORD = 'Timpulse03';
	
	const USERNAME = 'epsilon_technologies';
	const PASSWORD = 'Epsilon2021';

	const LOGIN_USERNAME_NAME = 'username';
	const LOGIN_PASSWORD_NAME = 'password';

	const DATE_TIMEZONE = 'Europe/Madrid';
	const DATE_FORMAT = 'Y-m-d';
	
	const COOKIES = 'aOOlW  bIiDR  ';
	const LOGIN_USERNAME_CLASSNAME = '_2hvTZ pexuQ zyHYP';
	const LOGIN_PASSWORD_CLASSNAME = '_2hvTZ pexuQ zyHYP';
	const CUENTA_PENDIENTE_SMS = '_7UhW9     LjQVu     qyrsm KV-D4          uL8Hv     l4b0S    ';
	const LIKES_CLASSNAME = 'sqdOP yWX7d     _8A5w5    ';	
	const VIDEO_CLASSNAME = 'vcOH2';
	const LIKES_DIV_CLASSNAME = 'Nm9Fw';
	const LIKES_DIV2_CLASSNAME = 'sqdOP yWX7d     _8A5w5    ';
	const VIEWS_DIV_CLASSNAME = 'vcOH2';
	const MAX_WAITING = 10;

	const POST_DIV = 'KC1QD';
	const POST_DATETIME = '_1o9PC Nzb55';
	const POST_MSG = 'C4VMK';
	const COMMENTSYLIKES = '-V_eO'; //li
	const IS_VIDEO = 'HbPOm _9Ytll';
	const LIKESVIDEO = 'vJRqr';
	const CLOSE_LIKESVIDEO = 'QhbhU';
	const IMGPOST = 'eLAPa _23QFA';
	const VIDEOPOSTOLD = 'tWeCl';
	const VIDEOPOST = 'Q9bIO'; 

	const TAGGED_CLASS = '_9VEo1 ';

	const HASH_ALGORITHM = 'md5';

	const BLOQUE = '1';


	/*
	Clase para almacenar la informacion relativa a un post.
	*/
	class Post {
		
		private $url;		
		private $idExterno;
		private $img;
		private $msg;
		private $numLikes;
		private $numComments;
		private $numViews;
		private $type;
		private $date;
		private $user;

		function __construct($url, $idExterno, $numLikes, $img, $msg, $numComments, $type, $date, $username, $numViews=0) {
			$this->url = $url;
			$this->idExterno = $idExterno;
			$this->numLikes = $numLikes;
			$this->numViews = $numViews;
			$this->numComments = $numComments;
			$this->msg = $msg;
			$this->img = $img;
			$this->type = $type;
			$this->date = $date;
			$this->user = $username;
		}

		function toString() {
			return 'POST '.$this->url."\n".$this->content."\n".$this->numInteractions."\n".$this->date."\n";
		}

		function getLink() {
			return $this->url;
		}

		function getNumLikes() {
			return $this->numLikes;
		}
		
		function getNumViews() {
			return $this->numViews;
		}

		function getIdExterno() {
			return $this->idExterno;
		}

		function getNumComments() {
			return $this->numComments;
		}

		function getImg() {
			return $this->img;
		}

		function getUser() {
			return $this->user;
		}


		function getMsg() {
			return $this->msg;
		}

		function getTipo() {
			return $this->type;
		}

		function getFecha() {
			return $this->date;
		}

		function setLikes($num){
			$this->numLikes=$num;
		}

		function setViews($num){
			$this->numViews=$num;
		}

	}

	class InstagramScraper {

		private $db;
		private $driver;

		function __construct($db, $driver) {
			$this->db = $db;
			$this->driver = $driver;
		}

		/*
		Funcion lanzadora de todo el proceso.
		*/
		function run($id_maquina) { 

			$urls = $this->getCompaniesUrls($id_maquina);

			if($urls == -1){

			}else{

			$this->firstlogin();

			//SCRAP ALL THE PHOTOS IN THE FEEDS
			foreach ($urls as $id => $urlindiv) {	

				echo 'Scrapping ', $urlindiv ,"...\n"; 
				$this->getFechasCargas($id);

				$urlprofile = "https://www.instagram.com/explore/tags/".$urlindiv;

				try{
					$this->randomSleep();
					$this->driver->get($urlprofile);
					$this->randomSleep();
					//$this->taggedPage();
					//$this->randomSleep();
					$posts = $this->scrapPosts($id, $urlindiv);
					//$this->storePosts($id, $posts, $urlindiv);
					$this->deleteProfile($id);
					
				} catch (Exception $e) {
					echo 'Fallo storeando info de ', $id, " \n";
					echo 'Mensaje de error: ', $e->getMessage(), "\n";
				}

			}
		}

			//$this->syncBrandByContent();
				
		}

		function getFechasCargas($id){

			$queryfecha = "SELECT *
						FROM scrapper_ig_tags_cola
						WHERE id_profile_kmt = ".$id."";

			$queryResult = $this->db->query($queryfecha);

			global $fecha_fi, $fecha_ini;
			

			foreach ($queryResult as $r) {
				$fecha_ini = $r['fecha_ini'];
				$fecha_fi = $r['fecha_final'];
			}
		}

		function deleteProfile($idprof){

			$profilesQuery = "DELETE FROM scrapper_ig_tags_cola WHERE id_profile_kmt='".$idprof."'";
			echo "delete profile from queue: ".$profilesQuery."<br>"; 
			$queryResult = $this->db->query($profilesQuery);

		}


		function scrapPosts($id, $url) {

			$postarray = array();

			$flagsortir = true;

			$urlpostarray = array();

			$cuantos = 0;
			$cuantos2 = 0;
			$cuantosAntiguo = 0;

			while ($flagsortir) {
				$cuantos2++;
				$cuantos = 0;	
			
				$posts2 = $this->driver->findElements(WebDriverBy::cssSelector("div[class='v1Nh3 kIKUG  _bz0w']"));

				echo "\nHE ENCONTRADO: ".count($posts2)." POSTS Y ES LA VEZ NUMERO ".$cuantos2." QUE PASO POR AQUI\n\n";

				if(count($posts2) <= 0){
					echo "Arrivederci\n";
					$flagsortir = false;
					continue;
				}else {

					$flagrepetits = 0;

					foreach ($posts2 as $posting) {
						try {
							$urldelpost = $posting->findElement(WebDriverBy::xpath('.//a'))->getAttribute("href");
						} catch (Exception $e) {
							echo "Aqui estamos\n";
							continue;
						}
				
						if (!in_array($urldelpost, $urlpostarray)){
							$flagrepetits++;
						}

					}


					if($cuantosAntiguo == count($posts2) && $flagrepetits == 0){
						echo "Arrivederci2\n";
						$flagsortir = false;
						continue;
					}else{
						$cuantosAntiguo = count($posts2);
					}		
				}

				foreach ($posts2 as $posting) {
					$cuantos++;

					//if($cuantos == 13) break;
					try {
						$urldelpost = $posting->findElement(WebDriverBy::xpath('.//a'))->getAttribute("href");
					} catch (Exception $e) {
						echo "Aqui estamos\n";
						continue;
					}
				
					if (!in_array($urldelpost, $urlpostarray)){
						$urlpostarray[] = $urldelpost;
					}
					else{
						/*if($cuantos == count($posts2)-1) 
						{
							echo "ADIOS\n";
							$flagsortir = false;
							break;
						}*/
						continue;
					}
					$this->randomSleep();

					//echo "No entiendo nada\n";

					$this->driver->getMouse()->mouseMove($posting->getCoordinates());
					$metricas = $posting->findElements(WebDriverBy::cssSelector("li[class='".COMMENTSYLIKES."']"));

					if(count($metricas) > 0) $likes = $metricas[0]->getText();
					else $likes = 0;
										
					if(count($metricas) > 1) $comment = $metricas[1]->getText();
					else $comment = 0;
							

					echo "LIKES:".$likes."\n";
					echo "COMMENTS:".$comment."\n";

					$this->randomSleep();
					//ABRIR POST
					$posting->click();
					
					$this->driver->wait()->until(
						WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector("time[class='".POST_DATETIME."']"))
					);

					$divtime = $this->driver->findElement(WebDriverBy::cssSelector("time[class='".POST_DATETIME."']"));
					
					$datetime = $divtime->getAttribute("datetime");
					date_default_timezone_set('Europe/Madrid');
					$dateres = date('Y-m-d H:i:s', strtotime($datetime));
					$date = new DateTime($datetime);
					//$dateres = $date->format('Y-m-d');
					$idExterno = $date->getTimestamp();

					global $fecha_fi, $fecha_ini;

					$datefinal = $fecha_fi." 23:59:59";
					$dateini = $fecha_ini." 00:00:00";
					//echo "FECHA CARGAS: ".$dateini."\n";
					echo "fecha post actual ".$dateres." < fecha ini ".$dateini."? \n";

					if($dateres <= "2019-01-01 00:00:00"){
						$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
						continue;
					}

					//SI NO ESTA ENTRE LAS FECHAS SELECCIONADAS SE LO SALTA O ACABA SI SE PASA
					if($dateres < $dateini){
						if ($cuantos < 10) {
							$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
							echo "Seguimos\n";
							continue;
						}
						echo "Me voy\n";
						$flagsortir = false;
						break;
					}else if($dateres > $datefinal){
						//CERRAR POST
						$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
						echo "Seguimos\n";
						continue;
					}

					try {
						$msg = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".POST_MSG."']"));
						$msgdeverda = $msg->findElement(WebDriverBy::xpath('./span'))->getText();
						$usrmsg = $msg->findElement(WebDriverBy::xpath('./h2'))->getText();
						echo "USR: ".$usrmsg."\n";
					} catch (Exception $e) {
						$msgdeverda = "";
					}
					


					$img = "";
					$type = "photo";
					$repros = 0;

					try {
						if(strpos($likes, 'k') !== false){
							$pasouno = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".LIKES_DIV_CLASSNAME."']"));
							$pasodos = $pasouno->findElement(WebDriverBy::xpath('.//button'));	
							$likes = $pasodos->findElement(WebDriverBy::xpath('.//span'))->getText();
						}
						$imgdiv = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".IMGPOST."']"));
						$imgdiv2 = $imgdiv->findElement(WebDriverBy::xpath('.//div'));
						$img = $imgdiv2->findElement(WebDriverBy::xpath('.//img'))->getAttribute("src");
						//echo "TENIM IMG:".$img."\n";					
					} catch (Exception $e) {}
					
					try {
						$sera = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".IS_VIDEO."']"));
						$seravideo = $sera->getText();

						if(strpos($seravideo, 'reproducciones') !== false){

							$img = $this->driver->findElement(WebDriverBy::cssSelector("video[class='".VIDEOPOST."']"))->getAttribute("poster");
							//echo "SOY UN VIDEO:".$img."\n";
							$type = "video-preview";

							$repro1 = $sera->findElement(WebDriverBy::xpath('.//span'));
							if(strpos($likes, 'k') !== false){
								$likes = $repro1->findElement(WebDriverBy::xpath('.//span'))->getText();						
							}
							$repros = $likes;
							echo "REPRODUCCIONES:".$repros."\n";

							$this->randomSleep();

							$repro1->click();
							$this->randomSleep();
							try {
								$likeVideoDiv = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".LIKESVIDEO."']"));
								$likes = $likeVideoDiv->findElement(WebDriverBy::xpath('.//span'))->getText();
							
							} catch (Exception $e) {
								$likeVideoDiv = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".LIKESVIDEO."']"))->getText();
								$likesu = explode(" ", $likeVideoDiv);
								$likes = $likesu[0];
							}
							echo "LIKES DE VIDEO:".$likes."\n";

							$this->driver->findElement(WebDriverBy::cssSelector("div[class='".CLOSE_LIKESVIDEO."']"))->click();
						}	
					} catch (Exception $e) {}

					$this->randomSleep();
					
					//CERRAR POST
					$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();

					$likes = str_replace(".", "", $likes);
					$comment = str_replace(".", "", $comment);
					$repros = str_replace(".", "", $repros);

					echo "LO HEMOS COGIDO\n";

					$bonk = new Post($urldelpost, $idExterno, $likes, $img, $msgdeverda, $comment, $type, $dateres, $usrmsg, $repros);
					array_push($postarray, $bonk);

					
					$this->storePostUnique($id, $bonk, $url);
			
				}
			}
			//exit;
			return $postarray;
		}

		function randomSleep(){
			$int = rand(2, 6);
			sleep($int);
		}


		function getCompaniesUrls($id_maquina) {

			$companiesWithPinterestQuery = "SELECT * FROM `scrapper_ig_tags_cola` where bloqueado <> 1 ORDER BY orden ASC limit 0,".BLOQUE;
			$queryResult = $this->db->query($companiesWithPinterestQuery);

			$urls = array();

			foreach ($queryResult as $r) {
				$urls[$r['id_profile_kmt']] = $r['extra'];
				$sql = "UPDATE scrapper_ig_tags_cola SET bloqueado = 1, tiempo=NOW(),maquina='".$id_maquina."'  where id_profile_kmt = ".$r['id_profile_kmt'];
				if(!$this->db->query($sql)) {
					echo "Error updating en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
				}
			}
			if(count($urls) <= 0){
				return -1;
			}

			return $urls;
		}

		/*
		Inicia sesion en Instagram.
		*/
		function firstlogin() {
			echo "Voy a loggearme por primera vez \n";
			$this->driver->get(LOGIN_URL);

			$this->randomSleep();

			$this->driver->findElement(WebDriverBy::cssSelector("button[class='".COOKIES."']"))->click();

			$this->randomSleep();
			$login2 = $this->driver->findElements(WebDriverBy::cssSelector("input[class='".LOGIN_USERNAME_CLASSNAME."']"));
			$login2[0]->sendKeys(USERNAME);
			$login2[1]->sendKeys(PASSWORD)->submit();

			$this->randomSleep();
		}
		
		/*
		Almacena en la base de datos la informacion de los posts.
		@param mysqli db La base de datos en la que almacenar la informacion.
		@param int id El id de la compania a la que pertenecen esos posts.
		@param array(Post) posts Los posts a almacenar.
		*/

		function searchPost($linkPost) {
	
        	$q = "SELECT * FROM instagram_tags_profiles WHERE Link='".$linkPost."'";
	        $queryResult = $this->db->query($q);
    		$post = array();

           	foreach ($queryResult as $r) {
           		$post[] = $r['createTime'];
           	}

           	if(!empty($post)) return true;
          	else return false;
       	}

       	function storePostUnique($id, $p, $url) {
		
			$linkecito = $p->getLink();
			$banderita = $this->searchPost($linkecito);

			if($banderita == true){

				$insertQuery = "UPDATE `scrapper_ig_tags_content` SET `likes` = ".$p->getNumLikes().",  `comments` = ".$p->getNumComments().", `userId` = '".$p->getUser()."', `actualizacion` = now() WHERE `Link` = '".$linkecito."'";				
			}
			else{
				$insertQuery = "REPLACE INTO `scrapper_ig_tags_content`(`id_profile_kmt`, `tagName`, `createTime`, `text`, `Link`, `likes`, `comments`, `thumb`, `userId`, `actualizacion`)
				VALUES (".$id.", '".$url."', '".$p->getFecha()."', '".addslashes($p->getMsg())."', '".$p->getLink()."', ".$p->getNumLikes().", ".$p->getNumComments().", '".$p->getImg()."', '".$p->getUser()."', NOW())";

			}
			echo 'INSERTANDO ', $id, "\n";
			echo $insertQuery."\n"; 
			
			
			if(!$this->db->query($insertQuery)) {
				echo "Error insertando en la base de datos\n";
				echo "ERROR: ", $this->db->error, "\n";
				echo $p->toString();
			}
	
		}

		function storePosts($id, $posts, $url) {
			foreach ($posts as $p) {

				$linkecito = $p->getLink();
				$banderita = $this->searchPost($linkecito);

				if($banderita == true){

					$insertQuery = "UPDATE `scrapper_ig_tags_content` SET `likes` = ".$p->getNumLikes().",  `comments` = ".$p->getNumComments().", `userId` = '".$p->getUser()."', `actualizacion` = now() WHERE `Link` = '".$linkecito."'";				
				}
				else{
					$insertQuery = "REPLACE INTO `scrapper_ig_tags_content`(`id_profile_kmt`, `tagName`, `createTime`, `text`, `Link`, `likes`, `comments`, `thumb`, `userId`, `actualizacion`)
					VALUES (".$id.", '".$url."', '".$p->getFecha()."', '".addslashes($p->getMsg())."', '".$p->getLink()."', ".$p->getNumLikes().", ".$p->getNumComments().", '".$p->getImg()."', '".$p->getUser()."', NOW())";

				}
				echo 'INSERTANDO ', $id, "\n";
				echo $insertQuery."\n"; 
				
				
				if(!$this->db->query($insertQuery)) {
					echo "Error insertando en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					echo $p->toString();
				}
				
			}
		}
				
		function scrapLikes($id, $post){

			//Entra en la URL del post
			$this->driver->get($post);

			sleep(3);
			
			$likesDiv = $this->driver->findElement(WebDriverBy::cssSelector("button[class='sqdOP yWX7d     _8A5w5    ']"));
			$postLikes = $likesDiv->findElement(WebDriverBy::xpath('.//span'))->getText();
			$likesExplode=explode(" ", $postLikes);
			$likesFinal = $likesExplode[0];
			return $likesFinal;						
		}

		function scrapViews($id, $post){

			//Entra en la URL del post
			$this->driver->get($post);

			$viewsDiv = $this->driver->findElement(WebDriverBy::cssSelector("span[class='".VIEWS_DIV_CLASSNAME."']"));
			$postViews = $viewsDiv->findElement(WebDriverBy::xpath('.//span'))->getText();
			$viewsExplode=explode(" ", $postViews);

			$viewsFinal = $viewsExplode[0];			
			return $viewsFinal;
		}
	}
	
	/*
	Crea una nueva sesion de navegador y devuelve el driver de esta.
	@return WebDriver El driver del navegador.
	*/
	function createDriver() {
		$host = 'http://localhost:4444/wd/hub'; // this is the default
		$capabilities = DesiredCapabilities::chrome();
		return RemoteWebDriver::create($host, $capabilities);
	}

	/*
	Crea una nueva conexion con la base de datos de SAIO.
	@return mysqli La instancia de la conexion creada.
	*/
	function createDatabase() {
		mb_internal_encoding('UTF-8');
		$db = new mysqli('192.168.8.131', 'saio', 'eEp13Sa12cr', 'saio');
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			throw new Exception("Connection with database failed");
		}

		if (!$db->set_charset("utf8mb4")) {
			printf("Error cargando el conjunto de caracteres utf8: %s\n", $db->error);
			throw new Exception("Cannot load UTF8 charset");
		} else {
			printf("Conjunto de caracteres actual: %s\n", $db->character_set_name());
		}
		return $db;
	}

	function getId_by_Ip($db){
		$local_ip = getHostByName(getHostName());
		$sql = "SELECT * from scrapper_maquinas where ip_maquina = '".$local_ip."'";
		$queryResult = $db->query($sql);

		foreach ($queryResult as $r) {
			$ip = $r["id"];
		}
		return $ip;
	}

	try {

		$db = createDatabase();
		$id_maquina = getId_by_Ip($db);
		$driver = createDriver();
		$driver->manage()->window()->maximize();
		$scraper = new InstagramScraper($db, $driver);
		$scraper->run($id_maquina);
		//$scraper->syncBrandByContent();
		$driver->close();

	} catch (Exception $e) {
		echo "No se ha podido cargar la sesion\n";
		echo $e->getMessage(), "\n";
	}
?>
