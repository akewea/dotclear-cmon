<?php
//@@licence@@

if($core->blog->settings->coolminiornot->enabled) {
	require dirname(__FILE__).'/_widgets.php';
}
$core->addBehavior('adminBlogPreferencesForm',array('CoolMiniOrNotBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('CoolMiniOrNotBehaviors','adminBeforeBlogSettingsUpdate'));
if($core->blog->settings->coolminiornot->enabled) {
	$core->addBehavior('adminPostFormSidebar',array('CoolMiniOrNotBehaviors','adminPostFormSidebar'));
	$core->addBehavior('adminAfterPostUpdate',array('CoolMiniOrNotBehaviors','setCoolMiniOrNotId'));
	$core->addBehavior('adminAfterPostCreate',array('CoolMiniOrNotBehaviors','setCoolMiniOrNotId'));
}

class CoolMiniOrNotBehaviors
{
	
	const COOLMINIORNOT_ID_META = 'coolminiornot_id';
	const COOLMINIORNOT_ID_FIELD = 'coolminiornot_id';
	
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>CoolMiniOrNot</legend>'.
		'<p><label class="classic">'.
		form::checkbox('coolminiornot_enabled','1',$settings->coolminiornot->enabled).
		__('Enable CoolMiniOrNot').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('coolminiornot');
		$settings->coolminiornot->put('enabled',!empty($_POST['coolminiornot_enabled']),'boolean');
		//$settings->coolminiornot->put('account',!empty($_POST['coolminiornot_account']),'string');
	}

	public static function adminPostFormSidebar($post){

		$meta = new dcMeta($GLOBALS['core']);
		
		if (!empty($_POST[self::COOLMINIORNOT_ID_FIELD])) {
			$value = $_POST[self::COOLMINIORNOT_ID_FIELD];
		} else {
			$value = ($post) ? $meta->getMetaStr($post->post_meta,self::COOLMINIORNOT_ID_META) : '';
		}

		echo
		'<div>'.'<h3>'.__('CoolMiniOrNot').'</h3>'.
		'<p><label class="classic">'.__('Image ID').'</label>'.
		form::field(self::COOLMINIORNOT_ID_FIELD,20,255,$value,'maximal', 2).
		'</p>'.
		'</div>';
	}

	public static function setCoolMiniOrNotId($cur, $post_id){
		$post_id = (integer) $post_id;
		 
		if (isset($_POST[self::COOLMINIORNOT_ID_FIELD])) {			
			$coolminiornot_id = $_POST[self::COOLMINIORNOT_ID_FIELD];

			$meta = new dcMeta($GLOBALS['core']);				
			$meta->delPostMeta($post_id,self::COOLMINIORNOT_ID_META);				
			$meta->setPostMeta($post_id,self::COOLMINIORNOT_ID_META,$coolminiornot_id);
		}
	}

}
?>