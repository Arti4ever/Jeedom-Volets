<?php
if (!isConnect('admin')) {
throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('Volets');
sendVarToJS('eqType', $plugin->getId());
sendVarToJS('GestionsVolets', Volets::$_Gestions);
$eqLogics = eqLogic::byType($plugin->getId());

?>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
   		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
  		<div class="eqLogicThumbnailContainer">
      		<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
    		</div>
      		<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      			<i class="fas fa-wrench"></i>
    			<br>
    		<span>{{Configuration}}</span>
  			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes Zones}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
				foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
					echo '<br>';
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			?>
		</div>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation" class="JourNuit"><a href="#journuitab" aria-controls="home" role="tab" data-toggle="tab"><i class="icon nature-weather1"></i> {{Gestion Jour / Nuit}}</a></li>
			<li role="presentation" class="Absent"><a href="#presentab" aria-controls="home" role="tab" data-toggle="tab"><i class="icon loisir-runner5"></i> {{Gestion de l'absent}}</a></li>
			<li role="presentation" class="Meteo"><a href="#meteotab" aria-controls="home" role="tab" data-toggle="tab"><i class="icon meteo-orage"></i> {{Gestion Météo}}</a></li>
			<li role="presentation" class="Azimut"><a href="#azimutab" aria-controls="home" role="tab" data-toggle="tab"><i class="icon nature-planet5"></i> {{Gestion Azimut}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
			<li role="presentation"><a href="#conditiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-cube"></i> {{Conditions d'exécution}}</a></li>
			<li role="presentation"><a href="#actiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="icon divers-viral"></i> {{Actions}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Nom de l'équipement template}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" >{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (jeeObject::all() as $object) {
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Catégorie}}</label>
							<div class="col-sm-9">
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
							<label class="col-sm-3 control-label" >{{Etat du widget}}</label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Héliotrope}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner l'équipement source du plugin Héliotrope}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
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
							<label class="col-sm-3 control-label" >
								{{Gestions}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Choisir les types de gestions souhaités pour cette zone}}"></i>
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
							<label class="col-sm-3 control-label">{{Objet état réel}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Cet objet, initialisera le plugin avec l'état réel du volet. Lors d'une action manuelle sur le volet, les gestions seront désactivées et il sera de votre action pour la réactiver.}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="RealState" placeholder="{{Commande déterminant l'état du volet}}"/>
									<span class="input-group-btn">
										<a class="btn btn-success btn-sm listCmdAction" data-type="info">
											<i class="fas fa-list-alt"></i>
										</a>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Hauteur de fermeture}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Ce paramètre permet de déterminer si le volet est considéré comme fermé (pour le retour d'état proportionnel).}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="SeuilRealState" placeholder="{{0 si binaire}}"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Hauteur calculée}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Ce paramètre permet d'inverser la hauteur calculée par le plugin).}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
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
							<label class="col-sm-3 control-label">{{Objet indiquant la présence}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner la commande déterminant la présence}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cmdPresent" placeholder="{{Commande déterminant la présence}}"/>
									<span class="input-group-btn">
										<!--a class="btn btn-success btn-sm listAction" title="Sélectionner un mot-clé">
											<i class="fas fa-tasks"></i>
										</a-->
										<a class="btn btn-success btn-sm listCmdAction" data-type="info">
											<i class="fas fa-list-alt"></i>
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
								<label class="col-sm-3 control-label">{{Ouverture et fermeture aléatoire}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Les volets d'une même zone s'ouvriront ou se fermeront de façon aléatoire avec un delai entre chaque exécution}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
									<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="RandExecution"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Délai maximal du mode aléatoire (s)}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Temps d'attente aléatoire entre deux commandes de volet (s)}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
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
								<label class="col-sm-3 control-label">{{Heure d'ouverture minimum (HHMM)}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Si le soleil se lève avant, l'heure d'ouverture sera ce paramètre}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DayMin" placeholder="{{Heure d'ouverture minimum (HHMM)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Type de lever du soleil}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Choisir le type de lever du jour}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="TypeDay">
										<option value="sunrise">Lever du Soleil</option>
										<option value="aubenau">Aube Nautique</option>
										<option value="aubeciv">Aube Civile</option>
										<option value="aubeast">Aube Astronomique</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Délai au lever du jour (min)}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="Saisir le délai avant (-) ou après (+)"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
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
								<label class="col-sm-3 control-label">{{Heure de fermeture maximum (HHMM)}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Si le soleil se couche après, l'heure de fermeture sera ce paramètre}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="NightMax" placeholder="{{Heure de fermeture maximum (HHMM)}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Type de coucher du soleil}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Choisir le type de coucher du soleil}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="TypeNight">
										<option value="sunset">Coucher du Soleil</option>
										<option value="crepnau">Crépuscule Nautique</option>
										<option value="crepciv">Crépuscule Civile</option>
										<option value="crepast">Crépuscule Astronomique</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Délai à la tombée de la nuit (min)}}
									<sup>
										<i class="fas fa-question-circle tooltips" title="{{Saisir le délai avant (-) ou après (+)}}"></i>
									</sup>
								</label>
								<div class="col-sm-3 ">
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
							<label class="col-sm-3 control-label">{{L'exposition au soleil est comprise entre}}</label>
							<a class="btn btn-info pull-right" id="bt_openMap" style="margin-top:5px;">
								<i class="icon nature-planet5"></i> Déterminer les angles
							</a>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AngleDroite" disabled />
							</div>
							<label class="col-sm-3 control-label">{{ Et }}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="AngleGauche" disabled />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Ratio d'ouverture}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Ce paramètre permet d'appliquer un ratio sur l'ouverture proportionnel.(uniquement l'été et quand le soleil est dans la fenêtre)}}"></i>
								</sup>
							</label>
							<div class="col-sm-3 ">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ratioOuverture" placeholder="{{1 par defaut}}"/>
								</div>
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
								<i class="fas fa-question-circle tooltips" title="{{Saisir toutes les conditions d'exécution de la gestion}}"></i>
							</sup>
							<a class="btn btn-success btn-xs conditionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fas fa-plus-circle"></i>
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
									<i class="fas fa-question-circle tooltips" title="{{Si cochée, alors la condition sera testée avant l'execution d'action}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Sur Réactivation (BETA)}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Si cochée, alors la condition sera testée pour un réarmement automatique}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Inverser l'action}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Si cochée, et si la condition est fausse alors le plugin testera l'action inverse}}"></i>
								</sup>
							</th>
							<th>{{Condition}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Saisir la condition à tester}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Type de gestion}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les gestions où la condition s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Saison}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les saisons où la condition s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Action}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les actions où la condition s'applique}}"></i>
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
								<i class="fas fa-question-circle tooltips" title="{{Saisir toutes les actions à mener à l'ouverture}}"></i>
							</sup>
							<a class="btn btn-success btn-xs ActionAttr" data-action="add" style="margin-left: 5px;">
								<i class="fas fa-plus-circle"></i>
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
									<i class="fas fa-question-circle tooltips" title="{{Cocher pour activer l'action}}"></i>
								</sup>
							</th>
							<th style="width: 100px;">{{Mouvement}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Cocher si l'action déclenche un mouvement}}"></i>
								</sup>
							</th>
							<th>{{Action}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Saisir l'action et ses paramètres}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Type de gestion}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les gestions où l'action s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Saison}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les saisons où l'action s'applique}}"></i>
								</sup>
							</th>
							<th style="width: 150px;">{{Action}}
								<sup>
									<i class="fas fa-question-circle tooltips" title="{{Sélectionner les actions où l'action s'applique}}"></i>
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
