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

	require_once('../Scraper_old/vendor/autoload.php');

	const LOGIN_URL = 'https://www.pinterest.es/login/';
	const LOGOUT_URL = 'https://www.pinterest.es/logout/';
	const USERNAME = 'ruben.hernandez@tech-impulse.com';
	const PASSWORD = 'Epsilon2019!';
	const LOGIN_USERNAME_ID = 'email';
	const LOGIN_PASSWORD_ID = 'password';

	const DATE_TIMEZONE = 'Europe/Madrid';
	const DATE_FORMAT = 'Y-m-d';
	/*
	const FEED_POSTS_CLASSNAME = 'feed-shared-update-v2';
	const FEED_SORT_BUTTON_CLASSNAME = 'sort-dropdown__icon';
	const FEED_RECENT_BUTTON_CLASSNAME = 'sort-dropdown__list-item';
	const FEED_POST_MORE_INFO_BUTTON_CLASSNAME = 'feed-shared-control-menu__trigger';
	const FEED_COPY_POST_LINK_CLASSNAME = 'option-share-via';
	const POST_TEXT_CLASSNAME = 'feed-shared-text__text-view';
	const POST_DATE_CLASSNAME = 'feed-shared-actor__sub-description';
	const POST_INTERACTIONS_CLASSNAME = 'social-details-social-counts__reactions-count';
	const POST_VIDEOVIEWS_CLASSNAME = 'social-details-social-counts__count-value';
	const POST_COMMENTS_CLASSNAME = 'social-details-social-counts__comments';
	*/

	const ALBUM_CLASSNAME = 'hA- sLG wYR zI7 iyn Hsu ';
	//const ALBUM_CLASSNAME = 'Mhr Zr3 sLG zI7 iyn Hsu ';
	//const SUBALBUM_CLASSNAME2 = 'Jea XiG gjz hs0 mix qJc zI7 iyn Hsu';
	const SUBALBUM_CLASSNAME = 'DUt Jea XiG gjz hs0 qJc zI7 iyn Hsu';
	const POST_CLASSNAME = 'hCL kVc L4E MIw';
	const POST_CLASSNAME2 = 'Pj7 sLG XiG eEj m1e';
	const POST_CLASSNAME3 = 'XiG sLG zI7 iyn Hsu';

	const POST_DATA_ID = 'initial-state';
	const REPINNED_CLASSNAME = 'tBJ dyH iFc _yT pBj DrD IZT swG';
	const REPINNED_CLASSNAME2 = 'tBJ dyH iFc SMy _yT pBj DrD IZT swG';
	const POST_TEXT_CLASSNAME_OLD = 'lH1 dyH iFc SMy kON pBj IZT';
	const POST_TEXT_CLASSNAME2 = 'Hvp Jea MtH sLG zI7 iyn Hsu';
	const POST_TEXT_CLASSNAME = 'Unauth_Heading Unauth_Gestalt fontSize1 darkGray breakWord';
	const POST_MSG_CLASSNAME = 'tBJ dyH iFc SMy MF7 pBj DrD swG';
	const ALBUM_STATS = 'tBJ dyH iFc yTZ pBj tg7 IZT mWe';
	const ALBUM_STATS_OLD = 'tBJ dyH iFc MF7 pBj DrD IZT swG';
	const FOLLOWERS_CLASSNAME = 'tBJ dyH iFc yTZ pBj DrD mWe';
	const FOLLOWERS_CLASSNAME_OLD = 'tBJ dyH iFc SMy yTZ pBj DrD mWe';
	const FOLLOWERS_CLASSNAME_OLD2 = 'tBJ dyH iFc SMy _S5 pBj DrD mWe';
	const COMMENTS_CLASSNAME = 'tBJ dyH iFc SMy _S5 B9u DrD IZT mWe';
	const VIDEO_CLASSNAME = 'iCM XiG L4E';
	const SUBALBUM_STATS_OLD = 'tBJ dyH iFc SMy MF7 B9u DrD IZT swG';
	const SUBALBUM_STATS = 'tBJ dyH iFc MF7 B9u DrD IZT swG';

	const MAX_WAITING = 10;

	const HASH_ALGORITHM = 'md5';



	const FOLLOWERS_PROFILE = 'tBJ dyH iFc yTZ pBj DrD IZT mWe';



	/*
	Clase para almacenar la informacion relativa a un post.
	*/
	class Post {
		/*
		@var string Enlace unico al post.
		*/
		private $url;
		/*
		@var string Texto del post.
		*/
		private $content;
		/*
		@var int Numero de repines del post.
		*/
		private $numPinned;
		/*
		@var DateTime Fecha de creacion del post.
		*/
		private $date;
		/*
		@var string Url de la imagen.
		*/
		private $imgUrl;
		/*
		@var int Id externo del pin.
		*/
		private $idExterno;

		/*
		@var int comentarios del post.
		*/
		private $numComments;

		function __construct($url, $content, $date, $imgUrl, $idExterno, $numPinned, $commNum, $typw) {
			$this->url = $url;
			$this->content = $content;
			$this->numPinned = $numPinned;
			$this->date = $date;
			$this->imgUrl = $imgUrl;
			$this->idExterno = $idExterno;
			$this->numComments= $commNum;
			$this->typePost = $typw;
		}

		/*
		Obtiene una cadena de texto con informacion sobre el post.
		@return string La cadena con la informacion.
		*/
		function toString() {
			return 'POST '.$this->url."\n".$this->content."\n".$this->numInteractions."\n".$this->date."\n";
		}

		function getDate() {
			return $this->date;
		}

		function getLink() {
			return $this->url;
		}

		function getContent() {
			return $this->content;
		}

		function getNumPinned() {
			return $this->numPinned;
		}
		function getNumComments() {
			return $this->numComments;
		}

		function getImgUrl() {
			return $this->imgUrl;
		}

		function getIdExterno() {
			return $this->idExterno;
		}

		function getType() {
			return $this->typePost;
		}

		function setComments($num){
			$this->numComments=$num;
		}

	}

	class PinterestScraper {

		private $db;
		private $driver;

		function __construct($db, $driver) {
			$this->db = $db;
			$this->driver = $driver;
		}

		/*
		Funcion lanzadora de todo el proceso.
		*/
		function run() {

			date_default_timezone_set('Europe/Madrid');
			$this->firstlogin($this->driver);
			//GET THE COMPANIES FROM THE DATABASE
			$urls = $this->getCompaniesUrls();

			//SCRAP ALL THE POSTS IN THE FEEDS
			foreach ($urls as $id => $url) {

				$urlprofile = 'https://www.pinterest.es/'.$url.'/_created/';
				echo 'Scrapping ', $urlprofile ,"...\n";

				try{
					$this->driver->get($urlprofile);
					$this->scrapCreated($id, $url);
					$this->storeData($id);
					
				} catch (Exception $e) {
					echo 'Fallo recopilando info de ', $id, " \n";
					echo 'Mensaje de error: ', $e->getMessage(), "\n";
				}

			}

		}

		/*
		Inicia sesion en Pinterest.
		*/
		function firstlogin() {
			echo "Voy a loggearme por primera vez \n";
			$this->driver->get(LOGIN_URL);
			$this->driver->findElement(WebDriverBy::id(LOGIN_USERNAME_ID))->sendKeys(USERNAME);
			$this->driver->findElement(WebDriverBy::id(LOGIN_PASSWORD_ID))->sendKeys(PASSWORD);

			$iniSes = $this->driver->findElement(WebDriverBy::cssSelector("button[type='submit']"));
			sleep(5);
			$iniSes->click();
			sleep(5);//Necesario para que Pinterest no sospeche que esta tratando con un bot.
		}

		/*
		Inicia sesion en Pinterest.
		*/
		function login() {
			echo "Voy a loggearme tal cual \n";
			$this->driver->get(LOGIN_URL);
			//sleep(130);
			sleep(30);
			$this->driver->findElement(WebDriverBy::id(LOGIN_PASSWORD_ID))->sendKeys(PASSWORD)->submit();
			sleep(5);//Necesario para que Pinterest no sospeche que esta tratando con un bot.
		}


		/*
		Cierra sesion en Pinterest.
		*/
		function logout() {
			$this->driver->get(LOGOUT_URL);
			sleep(5);//Necesario para que Pinterest no sospeche que esta tratando con un bot.
		}


		/*
		Extrae de la base de datos los links de Pinterest de todas las companias.
		@param mysqli db La base de datos de la que extraer la informacion.
		@return (int => string) Una lista de id's de las companias junto con el enlace de Pinterest de cada una de ellas.
		*/
		function getCompaniesUrls() {
			$companiesWithPinterestQuery = "SELECT *
						FROM icarus_profiles AS pro, icarus_brand_plantillas AS pla
						WHERE pro.id_plataforma =33 AND pla.id_profile=pro.id 
						GROUP BY pro.extra 
						ORDER BY pro.cuenta DESC";

			$queryResult = $this->db->query($companiesWithPinterestQuery);

			$urls = array();

			foreach ($queryResult as $r) {
				$urls[$r['id_profile']] = $r['extra'];
			}

			return $urls;
		}

		function scrapCreated($id, $url){

			$urlprofile = "https://www.pinterest.es/".$url;

			$urlArray = array();
			$cuantosAntiguo = 0;

			$flagOut = true;
			/*$seguidores = $this->getProfileFollowers();
			echo "SEGUIDORES: ".$seguidores."\n";*/

			sleep(5);

			while($flagOut){

				$posts2 = $this->driver->findElements(WebDriverBy::cssSelector("div[data-test-id='pinWrapper']"));

				echo "Hemos encontrado: ".count($posts2)." posts\n";

				if(count($posts2) <= 0){
					echo "Arrivederci\n";
					$flagOut = false;
					continue;
				}else {

					$flagRepeated = 0;

					foreach ($posts2 as $posting) {
						try {
							$urldelpost = $posting->findElement(WebDriverBy::xpath('.//a'))->getAttribute("href");
						} catch (Exception $e) {
							echo "Aqui estamos\n";
							continue;
						}
				
						if (!in_array($urldelpost, $urlArray)){
							$flagRepeated++;
						}

					}

					if($cuantosAntiguo == count($posts2) && $flagRepeated == 0){
						echo "Arrivederci2\n";
						$flagOut = false;
						continue;
					}else{
						$cuantosAntiguo = count($posts2);
					}		
				}


				for($i=0;$i<count($posts2);$i++)
				{

					sleep(2);
					/*
					try {
						$this->driver->getMouse()->mouseMove($posts2[$i]->getCoordinates());
					} catch (Exception $e) {
						echo "NO COORDENADAS DEL POST NUMERO ".$i."\n";
						echo $e."\n";
						break;

					}

						
					sleep(2);*/
					try {
						$urlPin = $posts2[$i]->findElement(WebDriverBy::xpath('.//a'))->getAttribute("href");
					} catch (Exception $e) {
						echo "NO HE PODIDO COGER EL POST NUMERO ".$i."\n";
						echo $e."\n";
						break;

					}
					

					if (!in_array($urlPin, $urlArray)) $urlArray[] = $urlPin;
					else continue;

					sleep(2);

					try {
		            	$this->driver->newWindow();
		           	}catch (Exception $e){

		            	echo $e;

		           	}
		           	$this->driver->switchTo()->window($this->driver->getWindowHandles()[1]);
		           	//echo $urlPin;
		           	sleep(2);
		       
		           	$newPinUrl = "https://www.pinterest.es".$urlPin;
		       
		           	$this->driver->get($newPinUrl);
		           	$pinid =  explode('pin/', $urlPin);
		           	$pinid2 = str_replace("/", "", $pinid[1]);

		           	$gotcha = $this->getPostInfo($pinid2, $i+1, $id, $url);

		           	sleep(3);
		           	$this->driver->close();
		           	sleep(1);

		           	$this->driver->switchTo()->window($this->driver->getWindowHandles()[0]);

		           	if($gotcha == -1){
		           		echo "FUERA DE FECHAS\n";
		           		break 2;
		           	}else if($gotcha == -2){
		           		echo "AUN NO HE LLEGADO A LA FECHA\n";
		           		continue;
		           	}else{
		           		echo "POST INSERTADO\n";
		           	}

		        }

		        $this->driver->executeScript('window.scrollTo(0, document.body.scrollHeight);');
		        sleep(5);
			}

		}


		function getPostInfo($pinid, $num, $profid, $page){

			sleep(3);

			//Iniciacion de variables
			$repins = 0;
			$comments = 0;
			$type = "photo";
			$descrip = "";
			$dateCarga = date("Y-m-d", strtotime( '-10 days' ))." 00:00:00";
			$img = "";
			$url = "https://www.pinterest.es/pin/".$pinid."/";


			/*Cogemos el page source para poder coger la informacion de un json de los scripts de la pagina*/
			$htmlCompleto = $this->driver->getPageSource();
			$pageExplode1 = explode("id=\"initial-state\" type=\"application/json\">", $htmlCompleto);
			$pageExplode2 = explode("</script>", $pageExplode1[1]);
			$pageExplode3 = $pageExplode2[0];
			$pageInfo = json_decode($pageExplode3, true);


			/*Si es el primer posts de la marca coge los followers y los guarda en la BBDD*/
			if($num == 1){
				$followers = 0;
				if(array_key_exists('pinner', $pageInfo['pins'][$pinid])){
					if(array_key_exists('follower_count', $pageInfo['pins'][$pinid]['pinner'])){
						$followers = $pageInfo['pins'][$pinid]['pinner']['follower_count'];
					}
				}
				echo "FOLLOWERS: ".$followers."\n";
				if($followers != 0){
					$this->saveFollowers($profid, $followers);
				}
			}

			/*Buscamos la fecha del post y la comparamos con la fecha de las cargas, si es anterior nos salimos*/
			if(array_key_exists('created_at', $pageInfo['pins'][$pinid])){
				$date1 = $pageInfo['pins'][$pinid]['created_at'];
				$dateres = date('Y-m-d H:i:s', strtotime($date1));

				echo "FECHA DEL POST: ".$dateres." vs FECHA CARGAS: ".$dateCarga."\n";

				if($dateres < $dateCarga){
					return -1;
				}
			}

			/*Cogemos el texto del Pin*/
			if(array_key_exists('description', $pageInfo['pins'][$pinid])){
				$descrip = $pageInfo['pins'][$pinid]['grid_title'];
			}
			echo "TEXT: ".$descrip."\n";

			/*Cogemos los repines*/
			if(array_key_exists('repin_count', $pageInfo['pins'][$pinid])){
				$repins = $pageInfo['pins'][$pinid]['repin_count'];
			}
			echo "REPINES: ".$repins."\n";

			/*Cogemos los comentarios*/
			if(array_key_exists('comment_count', $pageInfo['pins'][$pinid])){
				$comments = $pageInfo['pins'][$pinid]['comment_count'];
			}
			echo "COMMENTS: ".$comments."\n";

			/*Miramos si es un video, si no lo es tiene que ser una imagen*/
			if(array_key_exists('videos', $pageInfo['pins'][$pinid]) && !(is_null($pageInfo['pins'][$pinid]['videos'])))
			{

				$img = $pageInfo['pins'][$pinid]['videos']['video_list']['V_720P']['url'];
	
				$type = "video";

				echo "VIDEO: ".$img."\n";

			}else{

				$type = "photo";

				/*Cogemos la imagen*/
				if(array_key_exists('images', $pageInfo['pins'][$pinid])){
					if(array_key_exists('236x', $pageInfo['pins'][$pinid]['images'])){

						$img = $pageInfo['pins'][$pinid]['images']['236x']['url'];
					}else{
						if(array_key_exists('orig', $pageInfo['pins'][$pinid]['images'])){
							$img = $pageInfo['pins'][$pinid]['images']['orig']['url'];
						}

					}
				}
				echo "IMG: ".$img."\n";
			}

			sleep(3);

			$post = new Post($url, $descrip, $dateres, $img, $pinid, $repins, $comments, $type);

			$this->savePost($profid, $page, $post);

			return 1;

		}


		function saveFollowers($profid, $followers){

			$ayer = date("Y-m-d", strtotime("-1 days"));

			$insertQueryFans = "REPLACE INTO `pinterest_api_fans` (`idPerfil`, `fecha`, `valor`)
														VALUES (".$profid.", '".$ayer."', ".$followers.")";

			echo 'INSERTANDO DATOS PROFILE ', $profid, "\n";


			if(!$this->db->query($insertQueryFans)) {
				echo "Error insertando en la base de datos\n";
				echo "ERROR: ", $this->db->error, "\n";
			}else{
				echo "FANS INSERTADOS PARA ESTE PERFIL";
			}

		}


		function savePost($id, $url, $p){

				$insertQuery = "REPLACE INTO `pinterest_icarus_contents`(`id_profile`, `pageName`, `createTime`, `type`, `message`, `link`, `shares`, `comments`, `id_externo`, `image`, `actualizacion`)
															VALUES (".$id.", '".$url."', '".$p->getDate()."', '".$p->getType()."', '".addslashes($p->getContent())."', '".$p->getLink()."', ".$p->getNumPinned().", ".$p->getNumComments().",'".$p->getIdExterno()."', '".$p->getImgUrl()."', NOW())";
				echo 'INSERTANDO ', $p->getIdExterno(), "\n";
				echo $insertQuery."\n";

				if(!$this->db->query($insertQuery)) {
					echo "Error insertando en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					echo $p->toString();
				}

		}

	
	
		function storeData($id){
			//echo "HE ENTRADO";
			$ayer = date("Y-m-d", strtotime("-1 days"));
			$cargasDias = date("Y-m-d", strtotime("-10 days"));



			$qsd = "SELECT SUM(shares) as shares, SUM(comments) as comments, COUNT(*) as post, createTime FROM `pinterest_icarus_contents` WHERE id_profile=".$id." AND createTime <= '".$ayer." 23:59:59' AND createTime >= '".$cargasDias." 00:00:00' GROUP BY DATE(createTime)";
			//echo $qsd."\n";

			$queryResult = $this->db->query($qsd);

			foreach ($queryResult as $r) {

				$eng = 0;
				$eng = $r['comments'] + $r['shares'];
				$fecha = explode(" ", $r['createTime']);

				$sqlFans = "SELECT valor as fans 
		  FROM pinterest_api_fans 
		  WHERE idPerfil='" .$id. "' AND fecha <='" . $fecha[0] . "' AND valor > 0 ORDER BY fecha DESC LIMIT 0,1";
		  		$queryFans = $this->db->query($sqlFans);

		  		foreach ($queryFans as $fanesitos) {
		  			
		  			$insertQueryD = "UPDATE `pinterest_icarus_brand_datos` SET `followers` = ".$fanesitos['fans'].", `valor1` = ".$fanesitos['fans'].", `advocacy` = ".$r['shares'].", `valor3` = ".$r['shares'].", `impacto` = ".$eng.", `valor2` = ".$r['comments'].", `actualizacion` = NOW() WHERE `id_profile` = ".$id." AND `fecha` = '".$fecha[0]."'";
					echo "UPDATED BRAND FECHA: ".$fecha[0]."\n";

					if(!$this->db->query($insertQueryD)) {
						echo "Error insertando en la base de datos\n";
						echo "ERROR: ", $this->db->error, "\n";
					}

					break;

		  		}


				

			}

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
		$db = new mysqli('192.168.7.111', 'saio', 'eEp13Sa12cr', 'saio');
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			throw new Exception("Connection with database failed");
		}

		if (!$db->set_charset("utf8")) {
			printf("Error cargando el conjunto de caracteres utf8: %s\n", $db->error);
			throw new Exception("Cannot load UTF8 charset");
		} else {
			printf("Conjunto de caracteres actual: %s\n", $db->character_set_name());
		}
		return $db;
	}


	try {

		$db = createDatabase();
		$driver = createDriver();
		$driver->manage()->window()->maximize();
		$scraper = new PinterestScraper($db, $driver);
		$scraper->run();
		$driver->close();

	} catch (Exception $e) {
		echo "No se ha podido cargar la sesion\n";
		echo $e->getMessage(), "\n";
	}


?>
