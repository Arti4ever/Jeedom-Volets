<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function Volets_install(){
}
function Volets_update(){
	log::add('Volets','debug','Lancement du script de mise a jours'); 
	foreach(eqLogic::byType('Volets') as $eqLogic){
		if($eqLogic->getConfiguration('Azimuth'))
			$eqLogic->setConfiguration('Azimut',true);
		if($eqLogic->getConfiguration('Present'))
			$eqLogic->setConfiguration('Absent',true);
		if($eqLogic->getConfiguration('DayNight')){
			$eqLogic->setConfiguration('Jours',true);
			$eqLogic->setConfiguration('Nuit',true);
		}
		$Conditions=$eqLogic->getConfiguration('condition');
		foreach($Conditions as $CondiKey => $Condition){	
			foreach($Condition["TypeGestion"] as $TypeGestionKey => $TypeGestion){	
				if($TypeGestion == "Day")
					$Conditions[$CondiKey]["TypeGestion"][]="Jour";
				if($TypeGestion == "Jours")
					$Conditions[$CondiKey]["TypeGestion"][]="Jour";
				if($TypeGestion == "Night")
					$Conditions[$CondiKey]["TypeGestion"][]="Nuit";
				if($TypeGestion == "Presence")
					$Conditions[$CondiKey]["TypeGestion"][]="Absent";
				if($TypeGestion == "Azimuth")
					$Conditions[$CondiKey]["TypeGestion"][]="Azimut";
			}
		}
		$eqLogic->setConfiguration('condition',$Conditions);	
		$Actions=$eqLogic->getConfiguration('action');
		foreach($Actions as $ActionKey => $Action){	
			foreach($Action["TypeGestion"] as $TypeGestionKey => $TypeGestion){	
				if($TypeGestion == "Jours")
					$Actions[$ActionKey]["TypeGestion"][]="Jour";
				if($TypeGestion == "Day")
					$Actions[$ActionKey]["TypeGestion"][]="Jour";
				if($TypeGestion == "Night")
					$Actions[$ActionKey]["TypeGestion"][]="Nuit";
				if($TypeGestion == "Presence")
					$Actions[$ActionKey]["TypeGestion"][]="Absent";
				if($TypeGestion == "Azimuth")
					$Conditions[$CondiKey]["TypeGestion"][]="Azimut";
			}
		}
		$eqLogic->setConfiguration('action',$Actions);	
		$eqLogic->save();
	}
	log::add('Volets','debug','Fin du script de mise a jours');
}
function Volets_remove(){
	foreach(eqLogic::byType('Volets') as $Volet){
		$listener = listener::byClassAndFunction('Volets', 'pull', array('Volets_id' => $Volet->getId()));
		if (is_object($listener))
			$listener->remove();
		$cron = cron::byClassAndFunction('Volets', 'ActionJour', array('Volets_id' => $Volet->getId()));
		if (is_object($cron)) 	
			$cron->remove();
		$cron = cron::byClassAndFunction('Volets', 'ActionNuit', array('Volets_id' => $Volet->getId()));
		if (is_object($cron)) 	
			$cron->remove();
		$cron = cron::byClassAndFunction('Volets', 'ActionMeteo', array('Volets_id' => $Volet->getId()));
		if (is_object($cron)) 	
			$cron->remove();
		
	}
}
?>
