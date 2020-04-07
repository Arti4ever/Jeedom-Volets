<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
class Volets extends eqLogic {
	public static $_Gestions=array('Manuel','Jour','Nuit','Meteo','Absent','Azimut');
	public $_inverseCondition;
	public $_RatioHorizontal;
	public static function cron()
	{
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok')
			return;
		if ($deamon_info['state'] != 'ok')
			return;
		foreach(eqLogic::byType('Volets') as $Volet)
		{
			$Volet->execGestionVolet();
		}
	}
	public static function deamon_info()
	{
		$return = array();
		$return['log'] = 'Volets';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('Volets') as $Volet)
		{
			if($Volet->getIsEnable())
			{
				$listener = listener::byClassAndFunction('Volets', 'pull', array('Volets_id' => $Volet->getId()));
				if (!is_object($listener))
					return $return;
			}
		}
		$return['state'] = 'ok';
		return $return;
	}
	public static function deamon_start($_debug = false)
	{
		log::remove('Volets');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok')
			return;
		if ($deamon_info['state'] == 'ok')
			return;
		foreach(eqLogic::byType('Volets') as $Volet)
			$Volet->StartDemon();
	}
	public static function deamon_stop()
	{
		foreach(eqLogic::byType('Volets') as $Volet)
			$Volet->StopDemon();
	}
	public static function pull($_option)
	{
		$Volet = Volets::byId($_option['Volets_id']);
		if (is_object($Volet) && $Volet->getIsEnable())
		{
			$Event = cmd::byId($_option['event_id']);
			if(is_object($Event))
			{
				switch($Event->getlogicalId())
				{
					case 'azimuth360':
						log::add('Volets','info',$Volet->getHumanName().' : Mise à jour de la position du soleil, Azimut = '.$_option['value']);
						cache::set('Volets::Azimut::'.$Volet->getId(),$_option['value'], 0);
					break;
					case $Volet->getConfiguration('TypeDay'):
						log::add('Volets','info',$Volet->getHumanName().' : Actualisation de l\'heure du lever de soleil : '.$_option['value']);
						if($Volet->getConfiguration('DayMin') != '' && $_option['value'] < $Volet->getConfiguration('DayMin'))
							$timstamp=$Volet->CalculHeureEvent(jeedom::evaluateExpression($Volet->getConfiguration('DayMin')),false);
						else
							$timstamp=$Volet->CalculHeureEvent($_option['value'],'DelaisDay');
						cache::set('Volets::Jour::'.$Volet->getId(),$timstamp, 0);
						break;
					case $Volet->getConfiguration('TypeNight'):
						log::add('Volets','info',$Volet->getHumanName().' : Actualisation de l\'heure du coucher de soleil : '.$_option['value']);
						if($Volet->getConfiguration('NightMax') != '' && $_option['value'] > $Volet->getConfiguration('NightMax'))
							$timstamp=$Volet->CalculHeureEvent(jeedom::evaluateExpression($Volet->getConfiguration('NightMax')),false);
						else
							$timstamp=$Volet->CalculHeureEvent($_option['value'],'DelaisNight');
						cache::set('Volets::Nuit::'.$Volet->getId(),$timstamp, 0);
					break;
					default:
						if ($Event->getId() == str_replace('#','',$Volet->getConfiguration('RealState')))
						{
							log::add('Volets','info',$Volet->getHumanName().' : Changement de l\'état réel du volet');
							$Volet->CheckRealState($_option['value']);
						}
						if ($Event->getId() == str_replace('#','',$Volet->getConfiguration('cmdPresent')))
						{
							log::add('Volets','info',$Volet->getHumanName().' : Mise à jour de la présence : '.$_option['value']);
							$Volet->execGestionVolet(); //si changement d'absence, on relance pour rechecker toutes les conditions
						}
					break;
				}
			}
		}
	}
	public function RearmementAutomatique()
	{
		$Saison=$this->getSaison();
		if($this->checkCondition($Evenement,$Saison,$this->getCmd(null,'gestion')->execCmd(),true))
		{
		 	log::add('Volets','info',$this->getHumanName().' : Réarmement automatique');
			$this->Rearmement();
			return true;
		}
		return false;
	}

	public function Rearmement()
	{
			//on remet les valeur par defaut et on relance
			$this->checkAndUpdateCmd('isArmed',true);
      $this->relaunch();
	}

	public function relaunch()
	{
		if($this->getCmd(null,'isArmed')->execCmd())
		{
			log::add('Volets','info',$this->getHumanName().' : relaunch');
			$this->checkAndUpdateCmd('gestion','Jour'); //Jour par defaut, le vrai mode sera mis a jour automatiquement
			$this->checkAndUpdateCmd('RatioVertical','0');
			$this->checkAndUpdateCmd('RatioHorizontal','0');
			$this->execGestionVolet();
		}
	}

	public function CheckRealState($Value)
	{
		$SeuilRealState=$this->getConfiguration("SeuilRealState");
		if($SeuilRealState == '')
			$SeuilRealState=0;
		if($this->getConfiguration('InverseHauteur'))
		{
			if($Value < $SeuilRealState)
				$State='open';
			else
				$State='close';
		}
		else
		{
			if($Value > $SeuilRealState)
				$State='open';
			else
				$State='close';
		}
		log::add('Volets','debug',$this->getHumanName().' : '.$Value.' >= '.$SeuilRealState.' => '.$State);
		if(cache::byKey('Volets::ChangeState::'.$this->getId())->getValue(false))
		{
			log::add('Volets','info',$this->getHumanName().' : Le changement d\'état est autorisé');
			cache::set('Volets::ChangeState::'.$this->getId(),false, 0);
		}
		else
		{
			$this->GestionManuel($State);
		}
		$this->setPosition($State);
		$this->checkAndUpdateCmd('RatioVertical',$Value);
	}

	public function execGestionVolet()
	{
		if($this->getIsEnable())
		{
			if($this->getCmd(null,'isArmed')->execCmd() && $this->getCmd(null,'Gestion')->execCmd() != 'Manuel')
			{ //si on est armé et pas en manuel
				$Jour = cache::byKey('Volets::Jour::'.$this->getId())->getValue(0);
				$Nuit = cache::byKey('Volets::Nuit::'.$this->getId())->getValue(0);
				if((mktime() > $Jour) && (mktime() < $Nuit)) //on regarde si on est en journée ou la nuit
				{ // en journée
					$isPresent = true;
					if($this->getConfiguration('Absent'))
					{ //check pour savoir si on est absent
						$cmd=cmd::byString($this->getConfiguration('cmdPresent'));
						if(is_object($cmd))
							$isPresent = $cmd->execCmd();
						log::add('Volets','debug',$this->getHumanName().' : test absent :'.$isPresent);
					}
					if($isPresent)
						$this->GestionJour();
					else
						$this->GestionAbsent();
				}
				else
				{ // la nuit
					$this->GestionNuit();
				}
			}
			else
			{ //si on pas armé, on test pour le réarmement auto.
				$this->RearmementAutomatique();
			}
		}
	}

	public function GestionManuel($State)
	{
		if($this->getConfiguration('Manuel'))
		{
			if(!$this->RearmementAutomatique())
			{
				$Saison=$this->getSaison();
				log::add('Volets','info','Un evenement manuel a été détécté sur le volet '.$this->getHumanName().' La gestion a été désactivé');
				if($this->checkCondition($State,$Saison,'Manuel'))
				{
					$this->CheckRepetitive('Manuel',$Evenement,$Saison);
					$this->checkAndUpdateCmd('isArmed',false);
				}
			}
			else
			{
        log::add('Volets','debug',$this->getHumanName().' Le réarmement a eu lieu on ignore l\'action manuel');
      }
		}
	}

	public function GestionJour()
	{
		$Saison=$this->getSaison();
		if($this->getConfiguration('Meteo') && $this->checkCondition('close',$Saison,'Meteo')) //gestion météo
		{
			$this->CheckRepetitive('Meteo','close',$Saison);
		}
		else if($this->getConfiguration('Azimut')) //gestion Azimut
		{
			$Azimut = cache::byKey('Volets::Azimut::'.$this->getId())->getValue(0);
			$Evenement=$this->SelectAction($Azimut,$Saison);
			if($this->checkCondition($Evenement,$Saison,'Azimut'))
				$this->CheckRepetitive('Azimut',$Evenement,$Saison);
		}
		else if($this->getConfiguration('Jour')) //gestion jour
		{ //si la gestion de la jour est activée
			if($this->checkCondition('open',$Saison,'Jour')) //check des conditions
			{
				$this->CheckRepetitive('Jour','open',$Saison);
			}
		}
	}

	public function GestionNuit()
	{
		if($this->getConfiguration('Nuit'))
		{ //si la gestion de la nuit est activée
			$Saison=$this->getSaison();
			if($this->checkCondition('close',$Saison,'Nuit')) //check des conditions
			{
				$this->CheckRepetitive('Nuit','close',$Saison);
			}
		}
	}

	public function GestionAbsent()
	{
		if($this->getConfiguration('Absent')) //gestion Absent
		{
			$Saison=$this->getSaison();
			if($this->checkCondition('close',$Saison,'Absent'))
			{
				$this->CheckRepetitive('Absent','close',$Saison);
			}
		}
	}

	public function CheckAngle($Azimut)
	{
		$Droite=$this->getConfiguration('Droite');
		$Gauche=$this->getConfiguration('Gauche');
		$Centre=$this->getConfiguration('Centre');
		$AngleCntDrt=$this->getConfiguration('AngleDroite');
		$AngleCntGau=$this->getConfiguration('AngleGauche');
		if(!is_numeric($AngleCntDrt)&&!is_numeric($AngleCntGau))
		{
			if(is_array($Droite)&&is_array($Centre)&&is_array($Gauche))
			{
				$AngleCntDrt=$this->getAngle(
					$Centre['lat'],
					$Centre['lng'],
					$Droite['lat'],
					$Droite['lng']);
				$AngleCntGau=$this->getAngle(
					$Centre['lat'],
					$Centre['lng'],
					$Gauche['lat'],
					$Gauche['lng']);
				$this->setConfiguration('AngleDroite',$AngleCntDrt);
				$this->setConfiguration('AngleGauche',$AngleCntGau);
				$this->save();
			}
			else
			{
				log::add('Volets','debug',$this->getHumanName().'[Gestion Azimut] : Les coordonnées GPS de l\'angle d\'exposition au soleil de votre fenêtre sont mal configurées');
				return false;
			}
		}
		$result=false;
		$Ratio=0;
		if ($AngleCntDrt < $AngleCntGau)
		{
			if($AngleCntDrt <= $Azimut && $Azimut <= $AngleCntGau)
				$result= true;
			$Ratio=($Azimut-$AngleCntDrt)*(100/($AngleCntGau-$AngleCntDrt));
		}
		else
		{
			if($AngleCntDrt <= $Azimut && $Azimut <= 360)
			{
				$result= true;
				$Ratio=($Azimut-$AngleCntDrt+360)*(100/($AngleCntGau-$AngleCntDrt+360));
			}
			if(0 <= $Azimut && $Azimut <= $AngleCntGau)
			{
				$result= true;
				$Ratio=($Azimut-($AngleCntDrt-360)+360)*(100/($AngleCntGau-($AngleCntDrt-360)+360));
			}
		}
		if(!$result)
			$Ratio=100;
		$this->_RatioHorizontal=round($Ratio);
		log::add('Volets','info',$this->getHumanName().'[Gestion Azimut] : L\'azimut ' . $Azimut . '° est compris entre : '.$AngleCntDrt.'°  et '.$AngleCntGau.'° => '.$this->boolToText($result));
		return $result;
	}
	public function getSaison()
	{
		$isInWindows=$this->getCmd(null,'isInWindows');
		if(!is_object($isInWindows))
			return false;
		if($isInWindows->execCmd())
		{
			//log::add('Volets','debug',$this->getHumanName().' : Le plugin est configuré en mode hiver');
			return 'hiver';
		}
		else
		{
			//log::add('Volets','debug',$this->getHumanName().' : Le plugin est configuré en mode été');
			return 'été';
		}
		return false;
	}
	public function SelectAction($Azimut,$saison)
	{
		$Action = 'close';
		if($this->CheckAngle($Azimut))
		{
			$this->checkAndUpdateCmd('state',true);
			log::add('Volets','info',$this->getHumanName().'[Gestion Azimut] : Le soleil est dans la fenêtre');
			if($saison =='hiver')
				$Action ='open';
		}
		else
		{
			$this->checkAndUpdateCmd('state',false);
			log::add('Volets','info',$this->getHumanName().'[Gestion Azimut] : Le soleil n\'est pas dans la fenêtre');
			if($saison == 'été')
				$Action ='open';
		}
		return $Action;
	}
	public function getHauteur($Gestion,$Evenement,$Saison)
	{
		if($Evenement == 'open')
			$Hauteur=100;
		else if($Evenement == 'close')
			$Hauteur=0;
		if($Gestion == 'Azimut' && $Saison != 'hiver' && $this->getCmd(null,'state')->execCmd())
			$Hauteur=$this->checkAltitude();
		if($this->getConfiguration('InverseHauteur'))
			$Hauteur=100-$Hauteur;
		return $Hauteur;
	}
	public function RatioEchelle($Ratio,$Value)
	{
		$cmd=$this->getCmd(null, $Ratio);
		if(!is_object($cmd))
			return $Value;
		$min=$cmd->getConfiguration('minValue');
		$max=$cmd->getConfiguration('maxValue');
		if($min == '' && $max == '')
			return $Value;
		if($min == '')
			$min=0;
		if($max == '')
			$max=100;
		return round(($Value/100)*($max-$min)+$min);
	}
	public function AleatoireActions($Gestion,$ActionMove)
	{
		log::add('Volets','info',$this->getHumanName().'[Gestion '.$Gestion.'] : Lancement aléatoire de volet');
		shuffle($ActionMove);
		for($loop=0;$loop<count($ActionMove);$loop++)
		{
			$this->ExecuteAction($ActionMove[$loop],$Gestion);
			sleep(rand(0,$this->getConfiguration('maxDelaiRand')));
		}
	}
	public function CheckRepetitive($Gestion,$Evenement,$Saison)
	{
		$this->checkAndUpdateCmd('gestion',$Gestion); //mise a jour de la gestion
		$RatioVertical=$this->getHauteur($Gestion,$Evenement,$Saison);
		$incrementMvt = 2;
		if($RatioVertical < ($this->getCmd(null,'RatioVertical')->execCmd() + $incrementMvt) && $RatioVertical > ($this->getCmd(null,'RatioVertical')->execCmd() - $incrementMvt))
		{
			log::add('Volets', 'info',$this->getHumanName().'[Gestion '.$Gestion.'] : Exécution annulée, le volet est déja à la bonne position ');
			return; //si deja à la position on fait rien
		}
		log::add('Volets', 'info',$this->getHumanName().'[Gestion '.$Gestion.'] : Exécution de la gestion '.$Gestion);

		$this->checkAndUpdateCmd('RatioVertical',$this->RatioEchelle('RatioVertical',$RatioVertical));
		$this->checkAndUpdateCmd('RatioHorizontal',$this->RatioEchelle('RatioHorizontal',$this->_RatioHorizontal));

		if ($this->getConfiguration('RealState') == '')
			$this->setPosition($Evenement);

		$this->CheckActions($Gestion,$Evenement,$Saison,$Change);
	}

	public function CheckActions($Gestion,$Evenement,$Saison,$Change)
	{
		$ActionMove=null;
		foreach($this->getConfiguration('action') as $Cmd)
		{
			if($this->CheckValid($Cmd,$Evenement,$Saison,$Gestion))
			{
				if($this->getConfiguration('RandExecution'))
					$ActionMove[]=$Cmd;
				else
					$this->ExecuteAction($Cmd,$Gestion);
			}
		}
		if($this->getConfiguration('RandExecution') && $ActionMove != null)
			$this->AleatoireActions($Gestion,$ActionMove);
	}
	public function ExecuteAction($Cmd,$Gestion)
	{
		try
		{
			$options = array();
			if(isset($Cmd['options']))
			{
				foreach($Cmd['options'] as $key => $option)
					$options[$key]=jeedom::evaluateExpression($option);
			}
			if($Cmd['isVoletMove'])
			{
				cache::set('Volets::ChangeState::'.$this->getId(),true, 0);
			}
			log::add('Volets','debug',$this->getHumanName().'[Gestion '.$Gestion.'] : Exécution de '.jeedom::toHumanReadable($Cmd['cmd']).' ('.json_encode($options).')');
			scenarioExpression::createAndExec('action', $Cmd['cmd'], $options);
		}
		catch (Exception $e)
		{
			log::add('Volets', 'error',$this->getHumanName().'[Gestion '.$Gestion.'] : '. __('Erreur lors de l\'exécution de ', __FILE__) . jeedom::toHumanReadable($Cmd['cmd']) . __('. Détails : ', __FILE__) . $e->getMessage());
		}
	}
	public function CalculHeureEvent($HeureStart, $delais)
	{
		if(strlen($HeureStart)==3)
			$Heure=substr($HeureStart,0,1);
		else
			$Heure=substr($HeureStart,0,2);
		$Minute=floatval(substr($HeureStart,-2));
		if($delais != false)
		{
			if($this->getConfiguration($delais)!='')
				$Minute+=floatval($this->getConfiguration($delais));
			while($Minute>=60)
			{
				$Minute-=60;
				$Heure+=1;
			}
		}
		return mktime($Heure,$Minute);
	}
	public function CreateCron($Schedule, $logicalId)
	{
		$cron =cron::byClassAndFunction('Volets', $logicalId, array('Volets_id' => $this->getId()));
		if (!is_object($cron))
		{
			$cron = new cron();
			$cron->setClass('Volets');
			$cron->setFunction($logicalId);
			$cron->setOption(array('Volets_id' => $this->getId()));
			$cron->setEnable(1);
			$cron->setDeamon(0);
			$cron->setSchedule($Schedule);
			$cron->save();
		}
		else
		{
			$cron->setSchedule($Schedule);
			$cron->save();
		}
		return $cron;
	}
	public function CheckValid($Element,$Evenement,$Saison,$Gestion,$isCondition=false,$autoArm=false)
	{
		if($autoArm)
		{ //condition de Rearmement
			if(isset($Element['autoArm']) && $Element['autoArm'] == 0)
				return false;
			if(array_search($Saison, $Element['saison']) === false)
				return false;
			if(array_search($Gestion, $Element['TypeGestion']) === false)
				return false;
		}
		else
		{ //autre condition ou actions
			if(array_search($Evenement, $Element['evaluation']) === false)
				return false;
			if(array_search($Saison, $Element['saison']) === false)
				return false;
			if(array_search($Gestion, $Element['TypeGestion']) === false)
				return false;
			if(isset($Element['enable']) && $Element['enable'] == 0 && !$isCondition)
				return false;
		}
		return true;
	}
	public function checkCondition($Evenement,$Saison,$Gestion,$autoArm=false)
	{
		$conditionExist = false;
		foreach($this->getConfiguration('condition') as $Condition)
		{ //parcours des conditions
			if($this->CheckValid($Condition,$Evenement,$Saison,$Gestion,true,$autoArm))
			{ //si elle correspond a l'evennement (saison, etc...)
				$conditionExist = true;
				if (!$this->EvaluateCondition($Condition,$Gestion))
				{
					if($Condition['Inverse'])
					{
						log::add('Volets','info',$this->getHumanName().'[Gestion '.$Gestion.'] : La condition inverse l\'état du volet');
						if($Evenement == 'close')
							$Evenement ='open';
						else
							$Evenement ='close';
						if($this->_inverseCondition)
						{
							$this->_inverseCondition=false;
							return false;
						}
						$this->_inverseCondition=true;
						return $this->checkCondition($Evenement,$Saison,$Gestion,$autoArm);
					}
					log::add('Volets','info',$this->getHumanName().'[Gestion '.$Gestion.'] : Les conditions ne sont pas remplies');
					return false;
				}
			}
		}
		if(!$conditionExist && $autoArm)
			return false; //si on a pas de condition de Rearmement, on return false
		else
			return true;	//toutes les conditions sont bonnes
	}
	public function boolToText($value)
	{
		if (is_bool($value))
		{
			if ($value)
				return __('Vrai', __FILE__);
			else
				return __('Faux', __FILE__);
		}
		else
		{
			return $value;
		}
	}
	public function EvaluateCondition($Condition,$Gestion)
	{
		$_scenario = null;
		$expression = scenarioExpression::setTags($Condition['expression'], $_scenario, true);
		$message = __('Evaluation de la condition : [', __FILE__) . trim($expression) . '] = ';
		$result = evaluate($expression);
		$message .=$this->boolToText($result);
		log::add('Volets','info',$this->getHumanName().'[Gestion '.$Gestion.'] : '.$message);
		if(!$result)
			return false;
		else
			return true;
	}
	public function getAngle($latitudeOrigine,$longitudeOrigne, $latitudeDest,$longitudeDest)
	{
		$rlongitudeOrigne = deg2rad($longitudeOrigne);
		$rlatitudeOrigine = deg2rad($latitudeOrigine);
		$rlongitudeDest = deg2rad($longitudeDest);
		$rlatitudeDest = deg2rad($latitudeDest);
		$longDelta = $rlongitudeDest - $rlongitudeOrigne;
		$y = sin($longDelta) * cos($rlatitudeDest);
		$x = (cos($rlatitudeOrigine)*sin($rlatitudeDest)) - (sin($rlatitudeOrigine)*cos($rlatitudeDest)*cos($longDelta));
		$angle = rad2deg(atan2($y, $x));
		if ($angle < 0)
			$angle += 360;
		return floatval($angle % 360);
	}
	public function checkAltitude()
	{
		$heliotrope=eqlogic::byId($this->getConfiguration('heliotrope'));
		if(is_object($heliotrope))
		{
			$cmdAltitude =$heliotrope->getCmd(null,'altitude');
			if(!is_object($cmdAltitude))
				return false;
			$AltitudeSoleil = $cmdAltitude->execCmd();
			if (!$heliotrope->getConfiguration('zenith', ''))
		    $zenith = '90.58';
			else
		    $zenith = $heliotrope->getConfiguration('zenith', '');
			$Hauteur=round($AltitudeSoleil*100/$zenith);
			$Hauteur = round($Hauteur * $this->getConfiguration('ratioOuverture'));
			log::add('Volets','info',$this->getHumanName().'[Gestion Altitude] : L\'altitude actuel est a '.$Hauteur.'% par rapport au zenith');
			return $Hauteur;
		}
		return false;
	}
	public function StopDemon()
	{
		$listener = listener::byClassAndFunction('Volets', 'pull', array('Volets_id' => $this->getId()));
		if (is_object($listener))
			$listener->remove();
		$cron = cron::byClassAndFunction('Volets', 'GestionMeteo', array('Volets_id' => $this->getId()));
		if (is_object($cron))
			$cron->remove();
		$cache = cache::byKey('Volets::Jour::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::Nuit::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::RearmementAutomatique::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::ChangeState::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::Azimut::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
	}
	public function StartDemon()
	{
		if($this->getIsEnable())
		{
			$heliotrope=eqlogic::byId($this->getConfiguration('heliotrope'));
			if(is_object($heliotrope))
			{
				$listener = listener::byClassAndFunction('Volets', 'pull', array('Volets_id' => $this->getId()));
				if (!is_object($listener))
				    $listener = new listener();
				$listener->setClass('Volets');
				$listener->setFunction('pull');
				$listener->setOption(array('Volets_id' => $this->getId()));
				$listener->emptyEvent();
				if ($this->getConfiguration('RealState') != '')
				{
					$listener->addEvent($this->getConfiguration('RealState'));
					$RealState=cmd::byString($this->getConfiguration('RealState'));
					if(is_object($RealState))
					{
						$Value=$RealState->execCmd();
						$this->checkAndUpdateCmd('RatioVertical',$Value);
						$SeuilRealState=$this->getConfiguration("SeuilRealState");
						if($SeuilRealState == '')
							$SeuilRealState=0;
						if($this->getConfiguration('InverseHauteur'))
						{
							if($Value < $SeuilRealState)
								$State='open';
							else
								$State='close';
						}
						else
						{
							if($Value > $SeuilRealState)
								$State='open';
							else
								$State='close';
						}
						$this->setPosition($State);
					}
				}
				if ($this->getConfiguration('Azimut'))
				{
					$listener->addEvent($heliotrope->getCmd(null,'azimuth360')->getId());
					cache::set('Volets::Azimut::'.$this->getId(),$heliotrope->getCmd(null,'azimuth360')->execCmd(), 0);
				}
				if ($this->getConfiguration('Absent'))
				{
					$listener->addEvent($this->getConfiguration('cmdPresent'));
				}
				if ($this->getConfiguration('Jour'))
				{
					$sunrise=$heliotrope->getCmd(null,$this->getConfiguration('TypeDay'));
					if(!is_object($sunrise))
						return false;
					$listener->addEvent($sunrise->getId());
					if($this->getConfiguration('DayMin') != '' && $sunrise->execCmd() < $this->getConfiguration('DayMin'))
						$Jour=$this->CalculHeureEvent(jeedom::evaluateExpression($this->getConfiguration('DayMin')),false);
					else
						$Jour=$this->CalculHeureEvent($sunrise->execCmd(),'DelaisDay');
				}
				else
				{
					$sunrise=$heliotrope->getCmd(null,'sunrise');
					$Jour=$this->CalculHeureEvent($sunrise->execCmd(),false);
				}
				cache::set('Volets::Jour::'.$this->getId(),$Jour, 0);
				if ($this->getConfiguration('Nuit'))
				{
					$sunset=$heliotrope->getCmd(null,$this->getConfiguration('TypeNight'));
					if(!is_object($sunset))
						return false;
					$listener->addEvent($sunset->getId());
					if($this->getConfiguration('NightMax') != '' && $sunset->execCmd() > $this->getConfiguration('NightMax'))
						$Nuit=$this->CalculHeureEvent(jeedom::evaluateExpression($this->getConfiguration('NightMax')),false);
					else
						$Nuit=$this->CalculHeureEvent($sunset->execCmd(),'DelaisNight');
				}
				else
				{
					$sunset=$heliotrope->getCmd(null,'sunset');
					$Nuit=$this->CalculHeureEvent($sunset->execCmd(),false);
				}
				cache::set('Volets::Nuit::'.$this->getId(),$Nuit, 0);
				$listener->save();
				$this->Rearmement();
			}
		}
	}
	public function AddCommande($Name,$_logicalId,$Type="info", $SubType='binary',$visible,$Template='')
	{
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new VoletsCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setIsVisible($visible);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
		}
		$Commande->setType($Type);
		$Commande->setSubType($SubType);
   	$Commande->setTemplate('dashboard',$Template );
		$Commande->setTemplate('mobile', $Template);
		$Commande->save();
		return $Commande;
	}
	public function setPosition($Evenement)
	{
		$this->checkAndUpdateCmd('position',$Evenement);
	}
	public function getPosition()
	{
		return $this->getCmd(null,'position')->execCmd();
	}
	public function preSave()
	{
		if($this->getConfiguration('heliotrope') == "Aucun")
			throw new Exception(__('Impossible d\'enregister, la configuration de l\'equipement heliotrope n\'existe pas', __FILE__));
		else
		{
			$heliotrope=eqlogic::byId($this->getConfiguration('heliotrope'));
			if(is_object($heliotrope))
			{
				//on cherche si la les donnees de geoloc sont renseigner dans jeedom
				$GeoLoc['lat']=config::byKey('info::latitude');
				$GeoLoc['lng']=config::byKey('info::longitude');
				if($GeoLoc['lat'] == '' || $GeoLoc['lng'] == '')	
				{ // la config dans jeedom n'est pas bonne, on cherche dans geotrav (pour la compatibilité)
					$geotrav=eqlogic::byId($this->getConfiguration('geotrav'));
					if(is_object($geotrav))
					{
						if($heliotrope->getConfiguration('geoloc') == "")
							throw new Exception(__('Impossible d\'enregister, la configuration  heliotrope n\'est pas correcte', __FILE__));
						$geoloc = geotravCmd::byEqLogicIdAndLogicalId($heliotrope->getConfiguration('geoloc'),'location:coordinate');
						if(is_object($geoloc) && $geoloc->execCmd() == '')	
							throw new Exception(__('Impossible d\'enregistrer, la configuration de  "Localisation et trajet" (geotrav) n\'est pas correcte', __FILE__));
						$center=explode(",",$geoloc->execCmd());
						$GeoLoc['lat']=$center[0];
						$GeoLoc['lng']=$center[1];
					}
					else
						throw new Exception(__('Impossible d\'enregistrer, la configuration de  "longitude" ou "longitude" n\'est pas correcte', __FILE__));			
				}

				if($this->getConfiguration('Droite') != '')
				{
					if(!is_array($this->getConfiguration('Droite')))
						$this->setConfiguration('Droite',$GeoLoc);
				}
				if($this->getConfiguration('Gauche') != '')
				{
					if(!is_array($this->getConfiguration('Gauche')))
						$this->setConfiguration('Gauche',$GeoLoc);
				}
				if($this->getConfiguration('Centre') != '')
				{
					if(!is_array($this->getConfiguration('Centre')))
						$this->setConfiguration('Centre',$GeoLoc);
				}
				if($this->getConfiguration('ratioOuverture') == '')
				{
						$this->setConfiguration('ratioOuverture','1');
				}
			}
		}
	}
	public function postSave()
	{
		$this->AddCommande("Ratio Vertical","RatioVertical","info", 'numeric',1);
		$this->AddCommande("Ratio Horizontal","RatioHorizontal","info", 'numeric',1);
		$this->AddCommande("Gestion Active","gestion","info", 'string',1);
		$this->checkAndUpdateCmd('gestion','Jour');
		$state=$this->AddCommande("Position du soleil","state","info", 'binary',1,'sunInWindows');
		//$this->checkAndUpdateCmd('state',false);
		$isInWindows=$this->AddCommande("Etat mode","isInWindows","info","binary",0,'isInWindows');
		$inWindows=$this->AddCommande("Mode","inWindows","action","select",1,'inWindows');
		$inWindows->setConfiguration('listValue','1|Hivers;0|Eté');
		$inWindows->setValue($isInWindows->getId());
		$inWindows->save();
		$isArmed=$this->AddCommande("Etat activation","isArmed","info","binary",0,'lock');
		//$this->checkAndUpdateCmd('isArmed',true);
		$Armed=$this->AddCommande("Activer","armed","action","other",1,'lock');
		$Armed->setValue($isArmed->getId());
		$Armed->setConfiguration('state', '1');
		$Armed->setConfiguration('armed', '1');
		$Armed->save();
		$Released=$this->AddCommande("Désactiver","released","action","other",1,'lock');
		$Released->setValue($isArmed->getId());
		$Released->save();
		$Released->setConfiguration('state', '0');
		$Released->setConfiguration('armed', '1');
		$Position=$this->AddCommande("Etat du volet","position","info","string",0);
		$VoletState=$this->AddCommande("Position du volet","VoletState","action","select",1,'volet');
		$VoletState->setConfiguration('listValue','open|Ouvert;close|Fermé');
		$VoletState->setDisplay('title_disable', 1);
		$VoletState->setValue($Position->getId());
		$VoletState->save();
		$this->StopDemon();
		$this->StartDemon();
	}
	public function preRemove()
	{
		$listener = listener::byClassAndFunction('Volets', 'pull', array('Volets_id' => $this->getId()));
		if (is_object($listener))
			$listener->remove();
		$cron = cron::byClassAndFunction('Volets', 'GestionMeteo', array('Volets_id' => $this->getId()));
		if (is_object($cron))
			$cron->remove();
		$cache = cache::byKey('Volets::Jour::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::Nuit::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::RearmementAutomatique::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::ChangeState::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
		$cache = cache::byKey('Volets::Azimut::'.$this->getId());
		if (is_object($cache))
			$cache->remove();
	}
}
class VoletsCmd extends cmd
{
	public function execute($_options = null)
	{
		$Listener=cmd::byId(str_replace('#','',$this->getValue()));
		if (is_object($Listener))
		{
			switch($this->getLogicalId())
			{
				case 'armed':
					$Listener->event(true);
					$this->getEqLogic()->Rearmement();
					break;
				case 'released':
					$Listener->event(false);
					//si on desarme et que l'on a la gestion manuelle, alors on pass en manuel
					if($this->getEqLogic()->getConfiguration('Manuel'))
						$this->getEqLogic()->checkAndUpdateCmd('gestion','Manuel');
					break;
				case 'VoletState':
				case 'inWindows':
					$Listener->event($_options['select']);
					break;
			}
			$Listener->setCollectDate(date('Y-m-d H:i:s'));
			$Listener->save();
		}
	}
}
?>
