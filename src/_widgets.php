<?php
//@@licence@@

if (!defined('DC_RC_PATH')) { return; }

// Enregistrement des behaviors pour l'initialisation du widget.
$core->addBehavior('initWidgets',array('CoolMiniOrNotWidgets','initWidgets'));

/**
 * Classe pour l'initialisation du widget.
 * 
 * @author akewea
 *
 */
class CoolMiniOrNotWidgets
{
	
	/**
	 * Initialisation du widget (paramètres à saisir et valeurs par défaut).
	 * 
	 * @param $w
	 */
	public static function initWidgets($w)
	{
		global $core;
		
		$w->create('coolminiornot',__('CoolMiniOrNot'),array('CoolMiniOrNotPublic','mainWidget'));
		$w->coolminiornot->setting('title', __('Title:'), __('CoolMiniOrNot'));
		$w->coolminiornot->setting('cmonId', __('Image ID (empty means current post Image ID):'), '');
		$w->coolminiornot->setting('limit', __('Limit (empty means no limit):'), '');
	}
}




?>