<?php
//@@licence@@

class CoolMiniOrNot {

	const COOLMINIORNOT_ID_META = 'coolminiornot_id';
	
	/**
	 * Retourne les tweets du compte donné.
	 * 
	 * @param $twitterId le compte twitter
	 * @param $count le nombre de tweets à retourner
	 * @param $offset le décalage du premier tweet à retourner
	 * @param $cache_timeout la durée de mise en cache des tweets obtenus depuis le site twitter
	 * @return staticRecord la liste des tweets
	 */
	public static function getComments($cmonId='', $count=0, $offset=0){
				
		global $core;
		
		$xml = self::loadComments($cmonId);
		
		// On renseigne le contexte
		if( $xml == null || count($xml) < 1 ) {
			return self::commentRsFromArray(array());
		}
		
		$comments = array();
		$i = -1;
		foreach($xml as $comment) {
			
			$i++;
			
			if($i < $offset){
				continue;
			}
			
			if($count > 0 && $i >= ($offset + $count)){
				break;
			}
			
			$comments[] = array(	'user' 		=> $comment->attributes()->user,
									'date' 		=> (string) $comment->attributes()->date,
									'url' 		=> $comment->attributes()->url,
									'rating' 	=> $comment->attributes()->rating,
									'comment' 	=> $comment
									);					
		}
		
		return self::commentRsFromArray($comments);
	}	
	
	protected static function commentRsFromArray($comments){
		global $core;
		$rs = staticRecord::newFromArray($comments);
		$rs->core = $core;
		$rs->extend('CoolMiniOrNotExtComment');
		return $rs;
	}

	protected static function loadComments($cmonId){

		global $core;
		global $_ctx;

		$meta = new dcMeta($GLOBALS['core']);
		$cmon_id = !empty($cmonId) ? $cmonId : $meta->getMetaStr($_ctx->posts->post_meta,self::COOLMINIORNOT_ID_META);

		if(empty($cmon_id)){
			return;
		}
		
		$cache_timeout = $core->blog->settings->coolminiornot->cache_timeout;

		$cachedir = DC_TPL_CACHE."/coolminiornot";
		$cachefile = $cachedir."/".$cmon_id.".xml";

		// Création du répertoire de cache si besoin.
		if(!is_dir($cachedir)){
			try {
				mkdir($cachedir);
			} catch(Exception $e) {
				throw "Unable to create CoolMiniOrNot cache directory";
			}
		}

		$xml = null;

		//echo sprintf('%s | %s', @filemtime($cachefile), time());
		if(!file_exists($cachefile) || @filemtime($cachefile) < (time() - $cache_timeout)){
			// On récupère la dernière versions et on la met en cache.
			$url = $core->blog->settings->coolminiornot->root_url.'/comments.php?id='.$cmon_id;

			try{
				$content = self::getFromWeb($url);
				
				
				$xml = self::commentsToXml($content);
				if ($fp = @fopen($cachefile, 'wb'))	{
					fwrite($fp, $xml->asXML());
					fclose($fp);
				}
			}catch(Exception $e){
				// NOP.
			}

		}

		return self::getFromCache($cachefile);
	}

	protected static function commentsToXml($comments){
		
		global $core;
		
		$tmp = $comments;
		$tmp = preg_replace('/^\s*document.write\(\'/im', '', $tmp);
		$tmp = preg_replace('/\'\);\s*$/im', '', $tmp);
		$tmp = preg_replace('/^\s*|\s*$/im', '', $tmp);
		$tmp = preg_replace('/<[\/]*font\s*[^<]*[>]/im', '', $tmp);
		$tmp = preg_replace('/<[\/]*tr\s*[^<]*[>]/im', '', $tmp);
		$tmp = preg_replace('/<[\/]*td\s*[^<]*[>]/im', '', $tmp);
		$tmp = preg_replace('/<[\/]*table\s*[^<]*[>]/im', '', $tmp);
		//$xml = preg_replace('/^<t[^r].*$/im', '', $xml);

		$xml_out = '';
		foreach (explode("\n", $tmp) as $item) {
			if(preg_match('/^(.*?)<br>(.*?)<img[^>]*?src="([^"]*?)">RATING: ([0-9]+)<br>(.*)$/im', $item, $matches)){
				$name = $matches[1];
				$date = strtotime($matches[2]);
				$url = $matches[3];
				if(strtolower(substr($url, 0, 7)) != 'http://'){
					$url = $core->blog->settings->coolminiornot->root_url . $url;
				}
				$rating = (integer) $matches[4];
				$comment = $matches[5];
				$xml_out .= sprintf('<comment user="%s" date="%s" url="%s" rating="%s"><![CDATA[%s]]></comment>', $name, $date, $url, $rating,$comment);
			}
		}

		$xml_out = sprintf('<comments>%s</comments>', $xml_out);

		return simplexml_load_string($xml_out);
	}

	/**
	 * Retourne le flux XML des tweets depuis la version dans le cache.
	 *
	 * @param $filepath string le chemin du fichier mis en cache
	 * @return object Représentation objet du flux XML
	 */
	private static function getFromCache($filepath){
		if(file_exists($filepath)){
			return @simplexml_load_string(file_get_contents($filepath));
			//return file_get_contents($filepath);
		}
	}

	/**
	 * Retourne le flux XML des tweets depuis le site twitter.
	 *
	 * @param $url string L'URL du flux XML à télécharger
	 * @return object Représentation objet du flux XML
	 */
	private static function getFromWeb($url){

		$http = new netHttp('');
		//$http->setDebug(true);
		$http->readURL($url,$ssl,$host,$port,$path,$user,$pass);
		$http->setHost($host,$port);
		$http->useSSL($ssl);
		$http->setAuthorization($user,$pass);
		$http->setUserAgent("CoolMiniOrNot plugin for Dotclear");

		$http->get($path);

		//print_r($http);
		if($http->getStatus() != 200){
			throw new Exception();
		}

		return $http->getContent();
	}

}

?>