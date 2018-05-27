<?php
if (!isConnect('admin')) {
throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'Volets');
sendVarToJS('GestionsVolets', Volets::$_Gestions);
$eqLogics = eqLogic::byType('Volets');
?>
<div class="row row-overflow">
	<link rel="stylesheet" href="https://openlayers.org/en/v4.1.1/css/ol.css" type="text/css">
	<script src="https://openlayers.org/en/v4.3.3/build/ol.js" type="text/javascript"></script>
	<div class="col-lg-2">
		<div class="bs-sidebar">
			<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
				<a class="btn btn-default eqLogicAction" style="width : 50%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
					foreach ($eqLogics as $eqLogic)
						echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
				?>
			</ul>
		</div>
	</div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend>{{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 130px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 170px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 5em;color:#406E88;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#406E88"><center>{{Ajouter}}</center></span>
			</div>
			<div class="cursor eqLogicAction" data-action="gotoPluginConf" style="height: 130px; margin-bottom: 10px; padding: 5px; border-radius: 2px; width: 170px; margin-left: 10px; position: absolute; left: 170px; top: 0px; background-color: rgb(255, 255, 255);">
				    <center>
			      		<i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
			    	</center>
			    	<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>Configuration</center></span>
			</div>
			<div class="cursor bt_showExpressionTest" style="height: 130px; margin-bottom: 10px; padding: 5px; border-radius: 2px; width: 170px; margin-left: 10px; position: absolute; left: 170px; top: 0px; background-color: rgb(255, 255, 255);">
				  <center>
			      		<i class="fa fa-check" style="font-size : 5em;color:#767676;"></i>
			    </center>
			    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>Testeur d'expression</center></span>
			</div>
		</div>
		<legend>{{Mes zones}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" style="margin-bottom:4px;" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			if (count($eqLogics) == 0) {
				echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore de zones, cliquez sur Ajouter pour commencer}}</span></center>";
			} else {
				foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/Volets/plugin_info/Volets_icon.png" height="105" width="95" />';
					echo "</center>";
					echo '<span class="name" style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
	</div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success btn-sm eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> Sauvegarder</a>
		<a class="btn btn-danger btn-sm eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> Supprimer</a>
		<a class="btn btn-default btn-sm eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i></a>
		<a class="btn btn-default btn-sm eqLogicAction pull-right" data-action="copy"><i class="fa fa-copy"></i></a>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="fa fa-tachometer"></i> Equipement</a>
			</li>
			<li role="presentation" class="JourNuit">
				<a href="#journuitab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="icon nature-weather1"></i> Gestion Jour / Nuit</a>
			</li>
			<li role="presentation" class="Absent">
				<a href="#presentab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="icon loisir-runner5"></i> Gestion de l'absent</a>
			</li>
			<li role="presentation" class="Meteo">
				<a href="#meteotab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="icon meteo-orage"></i> Gestion Météo</a>
			</li>
			<li role="presentation" class="Azimut">
				<a href="#azimutab" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true">
					<i class="icon nature-planet5"></i> Gestion Azimut</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-list-alt"></i> Commandes</a>
			</li>
			<li role="presentation">
				<a href="#conditiontab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="fa fa-cube"></i> {{Conditions d'exécution}}</a>
			</li>
			<li role="presentation">
				<a href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false">
					<i class="icon divers-viral"></i> {{Actions}}</a>
			</li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<legend>Général</legend>
					<fieldset>
						<div class="form-group ">
							<label class="col-sm-2 control-label">{{Nom de la Zone}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Indiquer le nom de votre zone}}" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du groupe de zone}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Objet parent}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Indiquer l'objet dans lequel le widget de cette zone apparaîtra sur le Dashboard}}" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
										foreach (object::all() as $object)
											echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-2 control-label">
								{{Catégorie}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Choisir une catégorie. Cette information n'est pas obligatoire mais peut être utile pour filtrer les widgets}}" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-md-8">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>

							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >
								{{Etat du widget}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Choisir les options de visibilité et d'activation. Si l'équipement n'est pas activé, il ne sera pas utilisable dans Jeedom ni visible sur le Dashboard. Si l'équipement n'est pas visible, il sera caché sur le Dashboard}}" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<label>{{Activer}}</label>
								<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
								<label>{{Visible}}</label>
								<input type="checkbox" class="eqLogicAttr" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Héliotrope}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner l'équipement source du plugin Héliotrope}}"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<select class="eqLogicAttr" data-l1key="configuration" data-l2key="heliotrope">
									<option>Aucun</option>
									<?php
										foreach(eqLogic::byType('heliotrope') as $heliotrope)
											echo '<option value="'.$heliotrope->getId().'">'.$heliotrope->getName().'</option>';
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label" >
								{{Gestions}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Choisir les types de gestions souhaités pour cette zone}}" style="font-size : 1em;color:grey;"></i>
								</sup>
							</label>
							<div class="col-sm-8 Gestions">
								<?php
									foreach (Volets::$_Gestions as $Gestion) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="' . $Gestion . '" />' . $Gestion;
										echo '</label>';
									}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet état réel}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Cet objet, initialisera le plugin avec l'état réel du volet. Lors d'une action manuelle sur le volet, les gestions seront désactivées et il sera de votre action pour la réactiver.}}"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="RealState" placeholder="{{Commande déterminant l'état du volet}}"/>
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm listCmdAction data-type="info">
											<i class="fa fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Hauteur de fermeture}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Ce paramètre permet de déterminer si le volet est considéré comme fermé (pour le retour d'état proportionnel).}}"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="SeuilRealState" placeholder="{{0 si binaire}}"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Hauteur calculée}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Ce paramètre permet d'inverser la hauteur calculée par le plugin).}}"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<label>{{Inverser}}</label>
								<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="InverseHauteur"/>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="presentab">
				<form class="form-horizontal">
					<fieldset>
						{{La gestion d'absence va fermer le volet lorsque l'objet de présence surveillé passe à False.}}
						{{Seule la gestion de Nuit est autorisée à s'exécuter}}
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet indiquant la présence}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner la commande déterminant la présence}}"></i>
								</sup>
							</label>
							<div class="col-sm-5">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cmdPresent" placeholder="{{Commande déterminant la présence}}"/>
									<span class="input-group-btn">
										<!--a class="btn btn-success btn-sm listAction" title="Sélectionner un mot-clé">
											<i class="fa fa-tasks"></i>
										</a-->
										<a class="btn btn-success btn-sm listCmdAction data-type="info">
											<i class="fa fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		<div role="tabpanel" class="tab-pane" id="meteotab">
				<form class="form-horizontal">
					<fieldset>
						{{La gestion par météo est une tâche executée toutes les minutes qui va verifier les conditions météorologique que vous avez spécifées dans l'onget Condition}}
						{{Lorsque toutes les conditions sont vérifiées le plugin passe en mode Météo, les volets se ferment}}
						{{Seule la gestion de Nuit est autorisée à s'exécuter}}
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="journuitab">
				<div>
					<form class="form-horizontal">
						<legend>Général</legend>
						<fieldset>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Ouverture et fermeture aléatoire}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Les volets d'une même zone s'ouvriront ou se fermeront de façon aléatoire avec un delai entre chaque exécution}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="RandExecution"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Délai maximal du mode aléatoire (s)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Temps d'attente aléatoire entre deux commandes de volet (s)}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="maxDelaiRand" placeholder="{{Temps d'attente aléatoire entre deux commandes de volet (s)}}"/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="col-sm-6 Jour">
					<form class="form-horizontal">
						<legend>Gestion Jour</legend>
						<fieldset>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Heure d'ouverture minimum (HHMM)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Si le soleil se lève avant, l'heure d'ouverture sera ce paramètre}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DayMin" placeholder="{{Heure d'ouverture minimum (HHMM)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Type de lever du soleil}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Choisir le type de lever du jour}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="TypeDay">
										<option value="sunrise">Lever du Soleil</option>
										<option value="aubenau">Aube Nautique</option>
										<option value="aubeciv">Aube Civile</option>
										<option value="aubeast">Aube Astronomique</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Délai au lever du jour (min)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="Saisir le délai avant (-) ou après (+)"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DelaisDay" placeholder="{{Délai au lever du jour (min)}}"/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="col-sm-6 Nuit">
					<form class="form-horizontal">
						<legend>Gestion Nuit</legend>
						<fieldset>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Heure de fermeture maximum (HHMM)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Si le soleil se couche après, l'heure de fermeture sera ce paramètre}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="NightMax" placeholder="{{Heure de fermeture maximum (HHMM)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Type de coucher du soleil}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Choisir le type de coucher du soleil}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="TypeNight">
										<option value="sunset">Coucher du Soleil</option>
										<option value="crepnau">Crépuscule Nautique</option>
										<option value="crepciv">Crépuscule Civile</option>
										<option value="crepast">Crépuscule Astronomique</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Délai à la tombée de la nuit (min)}}
									<sup>
										<i class="fa fa-question-circle tooltips" title="{{Saisir le délai avant (-) ou après (+)}}"></i>
									</sup>
								</label>
								<div class="col-sm-5">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DelaisNight" placeholder="{{Délai à la tombée de la nuit (min)}}"/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="azimutab">
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{L'exposition au soleil est comprise entre}}</label>
							<a class="btn btn-info pull-right" id="bt_openMap" style="margin-top:5px;">
								<i class="icon nature-planet5"></i> Déterminer les angles
							</a>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AngleDroite" disabled />
							</div>
							<label class="col-sm-2 control-label">{{ Et }}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AngleGauche" disabled />
							</div>
						</div>
						<input type="hidden" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Droite"/>
						<input type="hidden" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Centre"/>
						<input type="hidden" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Gauche"/>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="conditiontab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les conditions d'exécutions :}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="{{Saisir toutes les conditions d'exécution de la gestion}}"></i>
							</sup>
							<a class="btn btn-success btn-xs conditionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Condition}}
							</a>
						</legend>
					</fieldset>
				</form>
				<table id="table_condition" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 100px;">{{Sur Action}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Si cochée, alors la condition sera testée avant l'execution d'action}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Sur Réactivation (BETA)}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Si cochée, alors la condition sera testée pour un réarmement automatique}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Inverser l'action}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Si cochée, et si la condition est fausse alors le plugin testera l'action inverse}}"></i>
								</sup>
							</th>
							<th>{{Condition}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Saisir la condition à tester}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Type de gestion}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les gestions où la condition s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Saison}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les saisons où la condition s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Action}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les actions où la condition s'applique}}"></i>
								</sup>
							</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="actiontab">
				<form class="form-horizontal">
					<fieldset>
						<legend>{{Les actions:}}
							<sup>
								<i class="fa fa-question-circle tooltips" title="{{Saisir toutes les actions à mener à l'ouverture}}"></i>
							</sup>
							<a class="btn btn-success btn-xs ActionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fa fa-plus-circle"></i>
								{{Ajouter une Action}}
							</a>
						</legend>
					</fieldset>
				</form>
				<table id="table_action" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 100px;">{{Activation}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Cocher pour activer l'action}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Mouvement}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Cocher si l'action déclenche un mouvement}}"></i>
								</sup>
							</th>
							<th>{{Action}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Saisir l'action et ses paramètres}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Type de gestion}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les gestions où l'action s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Saison}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les saisons où l'action s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Action}}
								<sup>
									<i class="fa fa-question-circle tooltips" title="{{Sélectionner les actions où l'action s'applique}}"></i>
								</sup>
							</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
					<tr>
						<th>{{Nom}}</th>
						<th>{{Paramètre}}</th>
						<th></th>
					</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'Volets', 'js', 'Volets'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
