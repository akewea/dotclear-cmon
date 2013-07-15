<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

if($core->blog->settings->coolminiornot->enabled) {
	require dirname(__FILE__).'/_widgets.php';
}

$core->tpl->addBlock('CoolMiniOrNotIf',array('CoolMiniOrNotTemplates','CoolMiniOrNotIf'));
$core->tpl->addValue('CoolMiniOrNotId',array('CoolMiniOrNotTemplates','CoolMiniOrNotId'));
$core->tpl->addValue('CoolMiniOrNotUrl',array('CoolMiniOrNotTemplates','CoolMiniOrNotUrl'));
$core->tpl->addValue('CoolMiniOrNotWidgetTitle',array('CoolMiniOrNotTemplates','CoolMiniOrNotWidgetTitle'));
$core->tpl->addValue('CoolMiniOrNotIfFirst',array('CoolMiniOrNotTemplates','CoolMiniOrNotIfFirst'));
$core->tpl->addValue('CoolMiniOrNotIfOdd',array('CoolMiniOrNotTemplates','CoolMiniOrNotIfOdd'));

$core->tpl->addBlock('CoolMiniOrNotComments',array('CoolMiniOrNotTemplates','CoolMiniOrNotComments'));
$core->tpl->addBlock('CoolMiniOrNotCommentsHeader',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentsHeader'));
$core->tpl->addBlock('CoolMiniOrNotCommentsFooter',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentsFooter'));
$core->tpl->addValue('CoolMiniOrNotComment',array('CoolMiniOrNotTemplates','CoolMiniOrNotComment'));
$core->tpl->addValue('CoolMiniOrNotCommentUser',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentUser'));
$core->tpl->addValue('CoolMiniOrNotCommentDate',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentDate'));
$core->tpl->addValue('CoolMiniOrNotCommentImage',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentImage'));
$core->tpl->addValue('CoolMiniOrNotCommentRating',array('CoolMiniOrNotTemplates','CoolMiniOrNotCommentRating'));

class CoolMiniOrNotTemplates {

	const COOLMINIORNOT_ID_META = 'coolminiornot_id';

	public static function CoolMiniOrNotIf($attr,$content)
	{
		$sortby = 'meta_id_lower';
		$order = 'asc';
		$res =
	          "<?php\n".
	          '$objMeta = new dcMeta($core); '.
	          "\$_ctx->meta = \$objMeta->getMetaRecordset(\$_ctx->posts->post_meta,'".self::COOLMINIORNOT_ID_META."'); ".
	          "\$_ctx->meta->sort('".$sortby."','".$order."'); ".
	          '?>';

		$res .=
	          '<?php while ($_ctx->meta->fetch()) : ?>'.$content.'<?php endwhile; '.
	          '$_ctx->meta = null; unset($objMeta); ?>';

		return $res;
	}

	public static function CoolMiniOrNotUrl($attr)
	{
		global $core;

		$root = !empty($attr['root']) ? true : false;
		$artist = !empty($attr['artist']) ? true : false;
		$cmon_id = !empty($attr['id']) ? "'".$attr['id']."'" : "((\$_ctx->posts) ? \$objMeta->getMetaStr(\$_ctx->posts->post_meta,'".self::COOLMINIORNOT_ID_META."') : '')";
		$score = !empty($attr['score']) ? true : false;
		$score_fg = !empty($attr['score_fg']) ? $attr['score_fg'] : $core->blog->settings->coolminiornot->score_fg;
		$score_bg = !empty($attr['score_bg']) ? $attr['score_bg'] : $core->blog->settings->coolminiornot->score_bg;

		$p_url = $core->blog->settings->coolminiornot->root_url;
		if(substr($p_url, -1, 1) != '/'){
			$p_url .= '/';
		}

		if($root){
			return '<?php echo "'.$p_url.'"; ?>';
		}		
		if($artist && !empty($core->blog->settings->coolminiornot->account)){
			return '<?php echo "'.$p_url.'artist/'.$core->blog->settings->coolminiornot->account.'"; ?>';
		}

		if($score){
			$p_url .= 'score.php?';
			if(!empty($score_fg)){
				$p_url .= 'fg='.$score_fg.'&amp;';
			}
			if(!empty($score_bg)){
				$p_url .= 'bg='.$score_bg.'&amp;';
			}
			$p_url .= 'id=';
		}

		$p_url = "'".$p_url."'.".$cmon_id;

		return
			"<?php \$objMeta = isset(\$objMeta) ? \$objMeta : new dcMeta(\$core);\n".
			" echo ".$p_url."; ?>";
	}

	public static function CoolMiniOrNotId($attr)
	{
		return "<?php echo (\$_ctx->posts) ? \$objMeta->getMetaStr(\$_ctx->posts->post_meta,'".self::COOLMINIORNOT_ID_META."') : ''; ?>";
	}

	public static function CoolMiniOrNotComments($attr,$content){

		global $core;
		global $_ctx;

		$args = (!empty($attr['id']) ? $attr['id'] : "''").',';
		$args .= (!empty($attr['count']) ? $attr['count'] : "0").',';
		$args .= (!empty($attr['offset']) ? $attr['offset'] : "0");

		return
	          	"<?php\n \$cmonErase = false;".
				'if(!$_ctx->coolminiornot) {'.
	          	"\$_ctx->coolminiornot = CoolMiniOrNot::getComments($args);\n".
				"\$cmonErase = true; } \n".
				'while ($_ctx->coolminiornot->fetch()) : ?>'.$content.'<?php endwhile; '.
				'if($cmonErase) $_ctx->coolminiornot = null; ?>';
	}
	
	public static function CoolMiniOrNotCommentsHeader($attr,$content)
	{
		return '<?php if ($_ctx->coolminiornot->isStart()) : ?>'.$content.'<?php endif; ?>';
	}
	
	public static function CoolMiniOrNotCommentsFooter($attr,$content)
	{
		return '<?php if ($_ctx->coolminiornot->isEnd()) : ?>'.$content.'<?php endif; ?>';
	}

	public static function CoolMiniOrNotComment($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->coolminiornot->comment').'; ?>';
	}

	public static function CoolMiniOrNotCommentUser($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->coolminiornot->user').'; ?>';
	}

	public static function CoolMiniOrNotCommentDate($attr) {
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->coolminiornot->date)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->coolminiornot->date)").'; ?>';
		} else if($format) {
			return '<?php echo '.sprintf($f,"dt::str('".$format."', \$_ctx->coolminiornot->date)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str(\$core->blog->settings->system->date_format, \$_ctx->coolminiornot->date)").'; ?>';
		}
	}

	public static function CoolMiniOrNotCommentImage($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->coolminiornot->url').'; ?>';
	}

	public static function CoolMiniOrNotCommentRating($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->coolminiornot->rating').'; ?>';
	}

	public static function CoolMiniOrNotWidgetTitle($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->coolminiornotWidgetTitle').'; ?>';
	}

	public static function CoolMiniOrNotIfFirst($attr) {
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->coolminiornot->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function CoolMiniOrNotIfOdd($attr) {
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->coolminiornot->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

}

class CoolMiniOrNotPublic extends dcUrlHandlers {

	public static function mainWidget($w){
		global $core;
		global $_ctx;

		$cmonId = $w->id;

		if (empty($cmonId) && $_ctx->posts) {
			$meta = new dcMeta($GLOBALS['core']);
			$cmonId = $meta->getMetaStr($_ctx->posts->post_meta,CoolMiniOrNot::COOLMINIORNOT_ID_META);
		}

		//Si pas d'id, on sort.
		if (empty($cmonId)) {
			return;
		}

		$_ctx->coolminiornot = CoolMiniOrNot::getComments($cmonId, ($w->limit > 0) ? $w->limit : 0);

		$_ctx->coolminiornotWidgetTitle = $w->title;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('coolminiornot-widget.html');
	}
}
?>