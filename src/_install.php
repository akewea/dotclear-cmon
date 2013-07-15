<?php
//@@licence@@

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('coolminiornot','version');
 
$i_version = $core->getVersion('coolminiornot');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# Création du setting (s'il existe, il ne sera pas écrasé)
$settings = new dcSettings($core,null);
$settings->addNameSpace('coolminiornot');
$settings->coolminiornot->put('account','','string','CoolMiniOrNot account (login)',false,true);
$settings->coolminiornot->put('enabled',false,'boolean','Enable CoolMiniOrNot',false,true);
$settings->coolminiornot->put('root_url','http://coolminiornot.com','string','CoolMiniOrNot root URL',false,true);
$settings->coolminiornot->put('cache_timeout',3600,'integer','CoolMiniOrNot cache timeout (in seconds)',false,true);

$core->setVersion('coolminiornot',$m_version);
?>