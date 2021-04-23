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

	$global_rep = 20;


	const LOGIN_URL = 'https://www.instagram.com';
	
	//const USERNAME = 'sat@tech-impulse.com';
	//const PASSWORD = 'Timpulse02';
	
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

	const POST_DIV = 'v1Nh3 kIKUG  _bz0w';
	const POST_DATETIME = '_1o9PC Nzb55';
	const POST_MSG = 'C4VMK';
	const COMMENTSYLIKES = '-V_eO'; //li
	const IS_VIDEO = 'HbPOm _9Ytll';
	const LIKESVIDEO = 'vJRqr';
	const CLOSE_LIKESVIDEO = 'QhbhU';
	const IMGPOST = 'eLAPa _23QFA';
	//const IMGPOST = 'eLAPa kPFhm';
	const IMGPOST_CAR = 'eLAPa RzuR0';
	const VIDEOPOST = 'tWeCl';

	const TAGGED_CLASS = '_9VEo1 ';

	const HASH_ALGORITHM = 'md5';

	const BLOQUE = '5';
	const TABLA_COLAS = 'scrapper_ig_mentions_cola';
	const TABLA_LOG = 'scrapper_ig_log_profiles';
	const BAN_CLASS = 'vqibd  wNNoj ';
	const SALIR_BAN = '_6vuJt';



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

		function __construct($url, $idExterno, $numLikes, $img, $msg, $numComments, $type, $date, $numViews=0) {
			$this->url = $url;
			$this->idExterno = $idExterno;
			$this->numLikes = $numLikes;
			$this->numViews = $numViews;
			$this->numComments = $numComments;
			$this->msg = $msg;
			$this->img = $img;
			$this->type = $type;
			$this->date = $date;
		}

		function toString() {
			return 'POST '.$this->url."\n".$this->date."\n";
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

			$var = true;
			$varBaneado = 1;

			$credenciales = $this->get_user();
			$user = $credenciales["user"];
			$passwd = $credenciales["password"];
			$id_user = $credenciales["id_user"];
			$this->firstlogin($user,$passwd);

			while ($varBaneado == 1) {
				$this->randomSleep();
				$varBaneado = $this->baneadito($id_user);
				echo "VARBANEADO = ".$varBaneado."\n";

				if($varBaneado == 1){
					/*$credenciales = $this->get_user();
					//$this->driver->findElement(WebDriverBy::cssSelector("a[class='".SALIR_BAN."']"))->click();
					$user = $credenciales["user"];
					$passwd = $credenciales["password"];
					$id_user = $credenciales["id_user"];
					$this->randomSleep();
					$this->login($user,$passwd);*/
					$this->driver->close();
					passthru("php C:\Users\Tech\Documents\Scraper\instagram-tagged.php");


				}
			}

			while($var){
				$contadorPerfilesMal = 0;
				$urls = $this->getCompaniesUrls($id_maquina);

				//SCRAP ALL THE PHOTOS IN THE FEEDS
				foreach ($urls as $id => $urlindiv) {	

					echo 'Scrapping ', $urlindiv ,"...\n"; 
					$this->getFechasCargas($id);

					$id_log = $this->insertLog($id,$id_maquina,0,0,$id_user);
					$urlprofile = "https://www.instagram.com/".$urlindiv;
					$posts = array();

					try{
						$this->randomSleep();
						$this->driver->get($urlprofile);

						$varBaneado = 1;

						while ($varBaneado == 1) {
							$this->randomSleep();
							$varBaneado = $this->baneadito($id_user);
							echo "VARBANEADO = ".$varBaneado."\n";
							if($varBaneado == 1){
								
								$this->driver->close();
								passthru("php C:\Users\Tech\Documents\Scraper\instagram-tagged.php");
							}
						}

						$this->driver->get($urlprofile);

						$this->randomSleep();
						$this->taggedPage();
						$this->randomSleep();
						$posts = $this->scrapPosts($id, $urlindiv,$id_user);
						//$this->storePosts($id, $posts, $urlindiv);
					} catch (Exception $e) {
						echo 'Fallo storeando info de ', $id, " \n";
						echo 'Mensaje de error: ', $e->getMessage(), "\n";
					}

					//$this->syncBrandByProfile($id);
					$this->insertLog($id,$id_maquina,1,$id_log,$id_user);
					$contadorPerfilesMal = $this->borrarCola($id, $id_maquina, $id_log, $contadorPerfilesMal, $id_user);
				}
				$this->Sleep_alograndre();		
			}
			//$this->syncBrandByContent();
			$this->desbloquearUsuario($id_user,$user,$passwd);
			$this->logout();
					
		}

		function insertLog($id,$id_maquina,$accion,$id_log,$id_user){
			/****
				$id --> id del perfil
				$id_maquina --> id de la maquina
				$accion --> si es un insert(0) o un update(1)
				$id_log --> en el caso que sea una actualizacion tendra el id del log que se ha insertado anteriormente, si es un insert siempre sera 0
			****/
			$local_ip = getHostByName(getHostName());

			if($accion == 0){
				echo "\nENTRAMOS PARA INSERTAR UN NUEVO LOG CON ESTE PERFIL\n";
				$fecha_ini = date("Y-m-d H:i:s");
				$sql = "INSERT INTO ".TABLA_LOG." VALUES(NULL,$id,$id_maquina,'$fecha_ini','','','INICIAMOS EL PERFIL $id EN LA MAQUINA $local_ip',3,$id_user)";

				if(!$this->db->query($sql)) {
					echo "Error updating en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					return 1;
				}else{
					echo "CONSULTA --> $sql\n";
				}
				$sql = "SELECT max(id) as 'max_id' FROM ".TABLA_LOG;
				$queryResult = $this->db->query($sql);
				foreach ($queryResult as $valor) {
					$id_log = $valor["max_id"];
				}
				echo "YA HEMOS INTRODUCIDO UN LOG PARA ESTE PERFIL\n";
				return $id_log;
			}
			if($accion == 1){
				$fecha_fin = date("Y-m-d H:i:s");
				if($id_log == 0){
					echo "\nHA HABIDO UN ERROR OBTENIENDO EL ID DEL LOG\n";
				}
				echo "\nENTRAMOS PARA ACTUALIZAR EL LOG CON ESTE PERFIL\n";
				$sql = "update ".TABLA_LOG." set fecha_fin='$fecha_fin',duracion=timediff(fecha_fin,fecha_ini),descripcion='FINALIZAMOS EL PERFIL $id EN LA MAQUINA $local_ip' where id=$id_log";
				if(!$this->db->query($sql)) {
					echo "Error updating en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					//return 1;
				}else{
					echo "CONSULTA --> $sql\n";
				}

				echo "YA HEMOS ACTUALIZADO EL LOG CON ESTE PERFIL\n";

			}

		}
		function borrarCola($id, $idmaq, $idlog, $contador, $idUser){

			echo "QUE TE VOY A BORRARRR\n";

			$querylog = "SELECT * FROM ".TABLA_LOG." WHERE id = '".$idlog."'";

			$queryResult = $this->db->query($querylog);
		
			foreach ($queryResult as $r) {
				$fecha = $r['fecha_ini'];
			}

			$dteStart = new DateTime($fecha);
   			$dteEnd   = new DateTime('now');

   			$dteDiff  = $dteStart->diff($dteEnd);
   			$dteFinal = $dteDiff->format("%H:%I:%S");

   			echo "\nEl perfil ha tardado: ".$dteFinal."\n";

   			if ($dteFinal < "00:00:45") {

   				echo "Menos del minimo\n";
   				$contador++;


   				$qUpdateCola = "UPDATE ".TABLA_COLAS." SET bloqueado = 0, orden = 99 WHERE id_profile = ".$id."";
   				if(!$this->db->query($qUpdateCola)) {
					echo "Error update en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
				}

				$qUpdateLog = "UPDATE ".TABLA_LOG." SET fecha_fin=now(), duracion=timediff(fecha_fin,fecha_ini), descripcion='(POSIBLE ERROR) FINALIZAMOS EL PERFIL ".$id." EN LA MAQUINA ".$idmaq."' where id=".$idlog."";
				if(!$this->db->query($qUpdateLog)) {
					echo "Error update en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
				}

				/*
				$selectMaquinas = "SELECT COUNT(*) as apagadas FROM scrapper_maquinas WHERE tipo_maquina = 1 AND apagada = 1";

				$queryResult2 = $this->db->query($selectMaquinas);
						
				foreach ($queryResult2 as $r2) {
					$numOff = $r2['apagadas'];
				}*/

				if($contador >= 5){

					echo "Uy Uy Uy la apago\n";
					$qApagar = "UPDATE scrapper_maquinas SET apagada = 1 WHERE id=".$idmaq."";
					if(!$this->db->query($qApagar)) {
						echo "Error update en la base de datos\n";
						echo "ERROR: ", $this->db->error, "\n";
					}

					$qUser = "UPDATE scrapper_users SET en_uso = 0 WHERE id = ".$idUser."";
					if(!$this->db->query($qUser)) {
						echo "Error update en la base de datos\n";
						echo "ERROR: ", $this->db->error, "\n";
					}

					$this->driver->close();
					exit;

				}

   			}else{

				$queryborro = "DELETE FROM ".TABLA_COLAS." WHERE id_profile = ".$id."";

				if(!$this->db->query($queryborro)) {
						echo "Error deleting en la base de datos\n";
						echo "ERROR: ", $this->db->error, "\n";
				}
			}

			return $contador;


		}


		function desbloquearUsuario($id_user,$user,$passwd){
			//Indicamos que el usuario ya ha finalizado por lo que ya no esta bloqueado

			$actualizar_estado_user = "UPDATE scrapper_users SET en_uso = 0 WHERE id=".$id_user;
			if(!$this->db->query($actualizar_estado_user)) {
				echo "Error updating en la base de datos\n";
				echo "ERROR: ", $this->db->error, "\n";
				return 1;
			}
			echo "\nYa hemos finalizado. Desbloqueamos el usuario ---> ".$user." - ".$passwd."\n";
		}

		function logout(){
			//Hacemos logout porque el usuario ha sido baneado y volvemos a usar otro usuario
			$this->driver->findElement(WebDriverBy::cssSelector("span[class='_2dbep qNELH']"))->click();
			$this->randomSleep();
			$this->driver->findElement(WebDriverBy::xpath("(//div[@class='-qQT3'])[2]"))->click();
		}

		function actualizar_actividad($id_user){
			//Asigna un valor datetime para saber la ultima vez que ha hecho alguna actividad este usuario
			$actualizar_ultima_gestion = "UPDATE scrapper_users SET fecha_ultima_actividad = '".date('Y-m-d H:i:s')."' WHERE id=".$id_user;
			if(!$this->db->query($actualizar_ultima_gestion)) {
				echo "Error updating en la base de datos\n";
				echo "ERROR: ", $this->db->error, "\n";
				return 1;
			}
			return 0;
		}
		function get_user(){
			//Obtiene el primer usuario que este libre y NO baneado y siempre sera el que tenga la fecha de ultima actividad mas antigua. En caso
			//de que todos los usuarios esten ocupados obtendremos el ultimo usuario utilizado y sin banear.
			$gotcha = true;

			while($gotcha){
				$sql = "select * from scrapper_users where en_uso = 0 and baneado = 0 and dormido = 0 order by fecha_ultima_actividad";
				$queryResult = $this->db->query($sql);
				if($queryResult->num_rows < 1){
					echo "TODOS LOS USUARIOS ESTAN EN USO\n";
					/*$sql = "select * from scrapper_users where baneado != 1 order by fecha_ultima_actividad";
					$queryResult = $this->db->query($sql);*/
					sleep(3600);


				}else{
					$gotcha = false;
				}
			}

			$data = array();
			foreach ($queryResult as $valor) {
				$data["user"] = $valor["usuario"];
				$data["password"] = $valor["password"];
				$data["id_user"] =$valor["id"];
				break;
			}

			$actualizar_estado_user = "UPDATE scrapper_users SET en_uso = 1, fecha_ultima_actividad = '".date('Y-m-d H:i:s')."' WHERE id=".$data["id_user"];
			if(!$this->db->query($actualizar_estado_user)) {
				echo "Error updating en la base de datos\n";
				echo "ERROR: ", $this->db->error, "\n";
				return 1;
			}
			echo "Usuario Bloqueado";
			return $data;
		}

		function baneadito($id){

			sleep(3);

			$currentURL = $this->driver->getCurrentURL();
			$esperar = 0;

			if(strpos($currentURL, 'challenge') !== false || strpos($currentURL, 'restriction') !== false){

				echo "BANEADO\n";
				echo "OPPSS! Tiene pinta de que han baneado al usuario\n";
				$actualizar_ultima_gestion = "UPDATE scrapper_users SET fecha_baneado = '".date('Y-m-d H:i:s')."', baneado = 1, contador_baneos = contador_baneos+1, en_uso = 0 WHERE id=".$id;
				if(!$this->db->query($actualizar_ultima_gestion)) {
					echo "Error updating en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
				}

				return 1;

			}else{
				while($esperar == 0){
					$errorwait = $this->driver->findElements(WebDriverBy::cssSelector("body[class='".BAN_WAIT_CLASS."']"));	
					if(count($errorwait) <= 0){
						echo "NO BANEADO\n";
						return 0;
					}else{
						else echo "BANEADO POR WAIT...\n";
						sleep(1800);
						$this->driver->navigate()->refresh();
						sleep(3);
						$urlnow = $this->driver->getCurrentURL();
						if($urlnow != $currentURL){
							$this->driver->get($currentURL);
						}
					}
				}
								
			}
		}

		function taggedPage(){

			$eti = $this->driver->findElements(WebDriverBy::cssSelector("a[class='".TAGGED_CLASS."']"));
			foreach ($eti as $isit) {
				$linky = $isit->getAttribute("href");
				if(strpos($linky, 'tagged') !== false){
					$isit->click();
					break;
				}
			}
			

		}

		function getFechasCargas($id){

			$queryfecha = "SELECT *
						FROM scrapper_ig_mentions_cola
						WHERE id_profile = ".$id."";

			$queryResult = $this->db->query($queryfecha);

			global $fecha_fi, $fecha_ini, $global_rep;

			foreach ($queryResult as $r) {
				$fecha_ini = $r['fecha_ini'];
				$fecha_fi = $r['fecha_final'];
			}

			$dteStart = new DateTime($fecha_ini);
   			$dteEnd   = new DateTime($fecha_fi);

   			$dteDiff  = $dteStart->diff($dteEnd);
   			$dteFinal = $dteDiff->format("%a");

   			if($dteFinal < 8){
   				$global_rep = 5;
   			}



		}


		function scrapPosts($id, $url,$id_user) {

			$postarray = array();

			$flagsortir = true;


			$urlpostarray = array();

			$primero = 0;
			$cuantos2 = 0;
			$cuantosAntiguo = 0;
			$cont = 0;
			$cuantosFechaFuera = 0;


			while ($flagsortir) {
				

			$posts2 = $this->driver->findElements(WebDriverBy::cssSelector("div[class='".POST_DIV."']"));

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

				$urldelpost = $posting->findElement(WebDriverBy::xpath('.//a'))->getAttribute("href");
				if (!in_array($urldelpost, $urlpostarray)){
					$urlpostarray[] = $urldelpost;
				}else{
					continue;
				}
				$this->randomSleep();

				$this->driver->getMouse()->mouseMove($posting->getCoordinates());
				$metricas = $posting->findElements(WebDriverBy::cssSelector("li[class='".COMMENTSYLIKES."']"));

				if(count($metricas) > 0){
					$likes = $metricas[0]->getText();
				}else{
					$likes = 0;
				}
				
				if(count($metricas) > 1){
					$comment = $metricas[1]->getText();
				}else{
					$comment = 0;
				}

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
				$date = new DateTime($dateres);
				$idExterno = $date->getTimestamp();
				global $fecha_fi, $fecha_ini, $global_rep;
				$datefinal = $fecha_fi." 23:59:59";
				$dateini = $fecha_ini." 00:00:00";
				echo "FECHA INICIO: ".$dateini." | FECHA DEL POST: ".$dateres."\n";

				//SI NO ESTA ENTRE LAS FECHAS SELECCIONADAS SE LO SALTA O ACABA SI SE PASA
				if($dateres < $dateini){
					if ($cuantosFechaFuera < $global_rep){ 
						$cuantosFechaFuera++;
						$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
						echo "Seguimos\n";
						continue;
					}
					echo "FUERA DE FECHAS\n";
					$flagsortir = false;
					break;
				}else if($dateres > $datefinal){
					//CERRAR POST
					$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
					echo "AUN NO HA LLEGADO A LA FECHA\n";
					$cuantosFechaFuera = 0;
					continue;
				}

				echo "LO COGEMOS\n";
				$cuantosFechaFuera = 0;

				try {
					$msg = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".POST_MSG."']"));
					$msgdeverda = $msg->findElement(WebDriverBy::xpath('./span'))->getAttribute("innerText");

					//echo $msgdeverda."\n";
				} catch (Exception $e) {
					$msgdeverda = "";
				}
				
				$img = "";
				$type = "photo";
				$repros = 0;

				if(strpos($comment, 'k') !== false){
					$comment = str_replace("k", "", $comment);
					$comment2 = explode(',', $comment);
					if(count($comment2) <= 1){
						$comment = $comment2[0]."000";
					}else{
						if($comment2[1] >= 10){
							$comment = $comment2[0]."".$comment2[1]."0";
						}else{
							$comment = $comment2[0]."".$comment2[1]."00";
						}
					}
				}

				try {
					if(strpos($likes, 'k') !== false || $likes == 0){
						$pasouno = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".LIKES_DIV_CLASSNAME."']"));
						$pasodos = $pasouno->findElement(WebDriverBy::xpath('.//a'));	
						$likes = $pasodos->findElement(WebDriverBy::xpath('.//span'))->getText();
					}
					$imgdiv = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".IMGPOST."']"));
					$imgdiv2 = $imgdiv->findElement(WebDriverBy::xpath('.//div'));
					$img = $imgdiv2->findElement(WebDriverBy::xpath('.//img'))->getAttribute("src");
					//echo "TENIM IMG:".$img."\n";					
				} catch (Exception $e) {
				}

				if($img == ""){
					try {
						$imgdiv = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".IMGPOST_CAR."']"));
						$imgdiv2 = $imgdiv->findElement(WebDriverBy::xpath('.//div'));
						$img = $imgdiv2->findElement(WebDriverBy::xpath('.//img'))->getAttribute("src");
						//echo $img."\n";
					}
					catch (Exception $e) {
						//echo 'Mensaje de error: ', $e->getMessage(), "\n";
					}
				}


				echo "LIKES:".$likes."\n"; echo "COMMENTS:".$comment."\n";
				
				try {
					$sera = $this->driver->findElement(WebDriverBy::cssSelector("div[class='".IS_VIDEO."']"));
					$seravideo = $sera->getText();

					if(strpos($seravideo, 'reproducciones') !== false){

						$img = $this->driver->findElement(WebDriverBy::cssSelector("video[class='".VIDEOPOST."']"))->getAttribute("poster");
						//echo "SOY UN VIDEO:".$img."\n";
						$type = "video-preview";

						$repro1 = $sera->findElement(WebDriverBy::xpath('.//span'));
						if(strpos($likes, 'k') !== false || strpos($likes, 'mm') !== false || $likes == 0){
							$likes = $repro1->findElement(WebDriverBy::xpath('.//span'))->getText();						
						}
						$repros = $likes;
						echo "REPRODUCCIONES:".$repros."\n";

						//$this->randomSleep();

						
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
				} catch (Exception $e) {
					
				}

				$this->randomSleep();
				
				//CERRAR POST
				try{
						$this->driver->findElement(WebDriverBy::cssSelector("svg[aria-label='Cerrar']"))->click();
					}
					catch (Exception $e) {
						echo 'Fallo cerrando el post \n';
						echo 'Mensaje de error: ', $e->getMessage(), "\n";
					}

				$likes = str_replace(".", "", $likes);
				$comment = str_replace(".", "", $comment);
				$repros = str_replace(".", "", $repros);

				echo "LO TENEMOS\n";

				$bonk = new Post($urldelpost, $idExterno, $likes, $img, $msgdeverda, $comment, $type, $dateres, $repros);
				array_push($postarray, $bonk);

				if($comment == 0){
						$this->storePostUnique($id, $bonk, $url, 0);
					}else{
						$this->storePostUnique($id, $bonk, $url, 1);
					}

			}
			}


			//exit;

			return $postarray;
		}

		function randomSleep(){
			$int = rand(2, 5);
			sleep($int);
		}

		function Sleep_alograndre(){
			//Random de 3,4,5,6 min
			$min = array(180,240,300,360);
			$min = array(5,10,15,20);
			$int = rand(0, 3);
			echo "VAMOS A DORMIR DURANTE ".$min[$int]." SEGUNDOS";
			sleep($min[$int]);
			echo "VOY A DESPERTARME";
		}


		function getCompaniesUrls($id_maquina) {
		//Obtenemos los perfiles que estan en cola y los bloqueamos

			if(!$this->db->ping()){
				echo "\n No tenemos conexion a la BD. Volveremos en 8 minutos";
				sleep(480);
				$this->driver->close();
				passthru("php C:\Users\Tech\Documents\Scraper\instagram-tagged.php");
			}
			
			$companiesWithPinterestQuery = "SELECT *
						FROM scrapper_ig_mentions_cola where bloqueado != 1
						ORDER BY orden LIMIT 0,".BLOQUE;

			$queryResult = $this->db->query($companiesWithPinterestQuery);

			$urls = array();

			foreach ($queryResult as $r) {
				$urls[$r['id_profile']] = $r['id_instagram'];
				$sql = "UPDATE scrapper_ig_mentions_cola SET bloqueado = 1, maquina = '".$id_maquina."', tiempo=NOW()  where id_profile = ".$r['id_profile'];
				if(!$this->db->query($sql)) {
					echo "Error updating en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
				}
			}

			if(count($urls) <= 0){
				echo "\nNo hay perfiles disponibles!!\n";
				return -1;
			}

			return $urls;

			/*$companiesWithPinterestQuery = "SELECT * 
						FROM icarus_profiles AS pro, icarus_brand_plantillas AS pla
						WHERE pro.id_plataforma =21 AND pro.extra!='' AND pla.id_profile=pro.id AND pla.id_departamento=1069 AND pla.id_profile = 11038
						GROUP BY pro.id ORDER BY pro.cuenta ASC";

			$queryResult = $this->db->query($companiesWithPinterestQuery);

			$urls = array();

			foreach ($queryResult as $r) {
				$urls[$r['id_profile']] = $r['extra'];
			}

			if(count($urls) <= 0){
				return -1;
			}

			return $urls;*/
		}

		/*
		Inicia sesion en Instagram.
		*/
		function firstlogin($user,$passwd) {
			echo "Voy a loggearme por primera vez \n";
			$this->driver->get(LOGIN_URL);

			$this->randomSleep();

			$this->driver->findElement(WebDriverBy::cssSelector("button[class='".COOKIES."']"))->click();

			$this->randomSleep();
			$login2 = $this->driver->findElements(WebDriverBy::cssSelector("input[class='".LOGIN_USERNAME_CLASSNAME."']"));
			$login2[0]->sendKeys($user);
			$login2[1]->sendKeys($passwd)->submit();

			//$login2[0]->sendKeys(USERNAME);
			//$login2[1]->sendKeys(PASSWORD)->submit();

			$this->randomSleep();
		}
		
		function login($user,$passwd) {
			echo "Voy a volver a loggearme \n";
			$this->driver->get(LOGIN_URL);

			$this->randomSleep();

			$login2 = $this->driver->findElements(WebDriverBy::cssSelector("input[class='".LOGIN_USERNAME_CLASSNAME."']"));
			$login2[0]->sendKeys($user);
			$login2[1]->sendKeys($passwd)->submit();

			//$login2[0]->sendKeys(USERNAME);
			//$login2[1]->sendKeys(PASSWORD)->submit();

			$this->randomSleep();
		}

		function syncBrandByContent(){

			global $fecha_fi, $fecha_ini;

			//$hoy = date('Y-m-d');
			//$ayer = date ( 'Y-m-d' , strtotime ( '-1 day' , strtotime ( $hoy ) ) ); 
			//$ayer = '2020-11-01';
			$ayer = date("Y-m-d", strtotime("-1 days"));
			$ayer = $fecha_ini;

			$profilesToUpdate = "SELECT DISTINCT c.id_profile FROM scrapper_ig_mentions_contents AS c, icarus_profiles AS pro, icarus_brand_plantillas AS pla, empresas AS e
						WHERE pro.id_plataforma=21 AND c.id_profile=pro.id AND pla.id_profile=pro.id AND pla.id_cliente=e.id AND (c.type LIKE 'video' OR c.type LIKE 'photo') AND c.createTime >='".$ayer." 00:00:00'";  //AND pro.mayor18=1
			echo "select profiles to update brand: ".$profilesToUpdate."<br>"; 
			$queryResult = $this->db->query($profilesToUpdate);

			foreach ($queryResult as $r) {
				$id_profile = $r['id_profile'];

				$sql_ig = "SELECT SUM(likes) as likes, SUM(comments) as com, SUM(shares) as shares, COUNT(*) as post, date(createTime) as fecha
					FROM scrapper_ig_mentions_contents WHERE id_profile='".$id_profile."' AND type NOT LIKE 'st - %' AND createTime>='".$ayer." 00:00:00' GROUP BY DATE(createTime)";
				echo "select sum interacciones: ".$sql_ig."<br>"; 
				$datos_ig = $this->db->query($sql_ig);
				
				foreach ($datos_ig as $s) {
					//print_r($s); exit; 
					$likes = $comments = $shares = $posts = 0;
					$fechaAct = $s['fecha'];

					if($s['likes']>0) $likes = $s['likes'];
					if($s['com']>0) $comments = $s['com'];
					if($s['shares']>0) $shares = $s['shares'];
					if($s['post']>0) $posts = $s['post'];
					
					$modif_brand = "UPDATE scrapper_ig_mentions_brand SET eficiencia='".$posts."', valor2='".$likes."', valor3='".$comments."', valor4='".$posts."', impacto=(valor6+valor2+valor3), actualizacion=NOW() WHERE id_profile= '".$id_profile."' AND  fecha LIKE '".$fechaAct."'";
					echo $modif_brand."\n"; //exit;
			
					if(!$this->db->query($modif_brand)) {
						echo "Error updating en la base de datos\n";
						echo "ERROR: ", $this->db->error, "\n";
					}
					
				}
			}

		}


		/*
		Almacena en la base de datos la informacion de los posts.
		@param mysqli db La base de datos en la que almacenar la informacion.
		@param int id El id de la compania a la que pertenecen esos posts.
		@param array(Post) posts Los posts a almacenar.
		*/

		function searchPost($linkPost, $idprof) {

	       	$q = "SELECT * FROM scrapper_ig_mentions_contents WHERE link='".$linkPost."' AND id_profile='".$idprof."'";
            $queryResult = $this->db->query($q);
            echo "Select Post: ".$q."\n";
            ///echo "Res Select: "; print_r($queryResult);

 			$post = array();

           	foreach ($queryResult as $r) {
           		$post[] = $r['createTime'];
            }

            if(!empty($post)){
                return true;
            } else {
            	return false;
            }           
       }

        function searchLikes($linkPost, $idprof) {

	
        	$q = "SELECT * FROM scrapper_ig_mentions_contents WHERE link='".$linkPost."' AND id_profile='".$idprof."'";
            $queryResult = $this->db->query($q);
            echo "Select Post: ".$q."\n";
            echo "Res Select: "; print_r($queryResult);

            $post = array();
 
            foreach ($queryResult as $r) {
           		$post[] = $r['likes'];
            }

            if(!empty($post)){
                return $post[0];
            } else {
            	return 0;
            }           
       }

		function storePosts($id, $posts, $url) {
			foreach ($posts as $p) {

				$linkecito = $p->getLink();
				$banderita = $this->searchPost($linkecito, $id);

				echo "EEEE - ".$p->getMsg()."\n";

				if($banderita == true){

					$insertQuery = "UPDATE `scrapper_ig_mentions_contents` SET `likes` = ".$p->getNumLikes().", `message` = '".$p->getMsg()."', `campo_8` = ".$p->getNumLikes().", `campo_7` = ".$p->getNumViews().", `comments` = ".$p->getNumComments().", `image` = '".$p->getImg()."', `actualizacion` = now() WHERE `link` = '".$linkecito."'";
					//$insertQuery = "UPDATE `instagram_parser_mentions` SET `likes` = ".$p->getNumLikes().", `campo_7` = ".$p->getNumViews().", `comments` = ".$p->getNumComments().", `actualizacion` = now() WHERE `link` = '".$linkecito."'";
				
				}else{

					$insertQuery = "REPLACE INTO `scrapper_ig_mentions_contents`(`id_profile`, `pageName`, `createTime`, `message`, `link`, `likes`, `campo_8`, `campo_7`, `comments`, `id_externo`, `image`, `type`, `actualizacion`)
															VALUES (".$id.", '".$url."', '".$p->getFecha()."', '".addslashes($p->getMsg())."', '".$p->getLink()."', ".$p->getNumLikes().", ".$p->getNumLikes().", ".$p->getNumViews().", ".$p->getNumComments().", '".$p->getIdExterno()."', '".$p->getImg()."', '".$p->getTipo()."', NOW())";

				}
				echo 'INSERTANDO ', $p->getIdExterno(), "\n";
				echo $insertQuery."\n";
				
				if(!$this->db->query($insertQuery)) {
					echo "Error insertando en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					echo $p->toString();
				}
			}
		}
				
		function storePostUnique($id, $p, $url, $num){

       		$linkecito = $p->getLink();
			$banderita = $this->searchPost($linkecito, $id);

			$likesAntiguos = $this->searchLikes($linkecito, $id);
			$likesScrap = $p->getNumLikes();

			echo "url: ".$linkecito."\n";
			echo "banderita: ".$banderita."\n";
			echo "Likes Anteriores: ".$likesAntiguos." | Likes Actuales: ".$likesScrap."\n";


			if(strpos($likesScrap, 'k') !== false){
				$likesScrap = str_replace("k", "", $likesScrap);
				$likesScrap2 = explode(',', $likesScrap);
				if(count($likesScrap2) <= 1){
					$likesScrap = $likesScrap2[0]."000";
				}else{
					if($likesScrap2[1] >= 10){
						$likesScrap = $likesScrap2[0]."".$likesScrap2[1]."0";
					}else{
						$likesScrap = $likesScrap2[0]."".$likesScrap2[1]."00";
					}
				}
			}


			if($likesAntiguos > $likesScrap){
				$likesFinales = $likesAntiguos; 
			}else{
				$likesFinales = $likesScrap;
			}


			if($banderita == true){

				if($num == 0){

					$insertQuery = "UPDATE `scrapper_ig_mentions_contents` SET `likes` = ".$likesFinales.", `campo_7` = ".$p->getNumViews().", `actualizacion` = now() WHERE `link` = '".$linkecito."'";

				}else{

				//$insertQuery = "UPDATE `instagram_icarus_contents` SET `likes` = ".$p->getNumLikes().", `campo_8` = ".$p->getNumLikes().", `campo_7` = ".$p->getNumViews().", `comments` = ".$p->getNumComments().", `actualizacion` = now() WHERE `link` = '".$linkecito."'";
					$insertQuery = "UPDATE `scrapper_ig_mentions_contents` SET `likes` = ".$likesFinales.", `campo_7` = ".$p->getNumViews().", `comments` = ".$p->getNumComments().", `actualizacion` = now() WHERE `link` = '".$linkecito."'";

				}
			
			}
			else{

				$insertQuery = "REPLACE INTO `scrapper_ig_mentions_contents` (`id_profile`, `pageName`, `createTime`, `message`, `link`, `likes`, `campo_8`, `campo_7`, `comments`, `id_externo`, `image`, `type`, `actualizacion`)
														VALUES (".$id.", '".$url."', '".$p->getFecha()."', '".addslashes($p->getMsg())."', '".$p->getLink()."', ".$likesFinales.", ".$likesFinales.", ".$p->getNumViews().", ".$p->getNumComments().", '".$p->getIdExterno()."', '".$p->getImg()."', '".$p->getTipo()."', NOW())"; //(addslashes($p->getMsg()))

			}

				echo 'INSERTANDO ', $p->getIdExterno(), "\n";
				echo $insertQuery."\n";
				
				if(!$this->db->query($insertQuery)) {
					echo "Error insertando en la base de datos\n";
					echo "ERROR: ", $this->db->error, "\n";
					echo $p->toString();
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
