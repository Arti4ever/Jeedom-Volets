$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_condition").sortable({axis: "y", cursor: "move", items: ".ConditionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_action").sortable({axis: "y", cursor: "move", items: ".ActionGroup", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=heliotrope]').on('change',function(){
	if($(this).val() != 'Aucun'){
		$.ajax({
			type: 'POST',
			async: false,
			url: 'plugins/Volets/core/ajax/Volets.ajax.php',
			data:{
				action: 'getInformation',
				heliotrope:$(this).val()
			},
			dataType: 'json',
			global: false,
			error: function(request, status, error) {},
			success: function(data) {
				if (!data.result)
					$('#div_alert').showAlert({message: 'Aucun message reçu', level: 'error'});
				if (typeof(data.result.geoloc) === 'undefined')
					return;
				var center=data.result.geoloc.split(",");
				var CentreLatLng=new Object();
				CentreLatLng.lat=parseFloat(center[0]);
				CentreLatLng.lng=parseFloat(center[1]);
				if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val() != ''){
				  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val()) !== 'object')
				      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val(JSON.stringify(CentreLatLng))
				}
				if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val() != ''){
				  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val()) !== 'object')
				      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val(JSON.stringify(CentreLatLng))
				}
				if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val() != ''){
				  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val()) !== 'object')
				      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val(JSON.stringify(CentreLatLng))
				}
			}
		});
	}else{
		var CentreLatLng=new Object();
		CentreLatLng.lat=0;
		CentreLatLng.lng=0;
		if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val() != ''){
		  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val()) !== 'object')
		      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val(JSON.stringify(CentreLatLng))
		}
		if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val() != ''){
		  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val()) !== 'object')
		      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val(JSON.stringify(CentreLatLng))
		}
		if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val() != ''){
		  if(typeof jQuery.parseJSON($('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val()) !== 'object')
		      $('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val(JSON.stringify(CentreLatLng))
		}
	}
});
$('.bt_showExpressionTest').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Testeur d'expression}}"});
  $("#md_modal").load('index.php?v=d&modal=expression.test').dialog('open');
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=Jour]').on('change',function(){
	if($(this).is(':checked'))
		$('.Jour').show();
	else
		$('.Jour').hide();
	if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Nuit]').is(':checked') || $(this).is(':checked'))
		$('.JourNuit').show();
	else
		$('.JourNuit').hide();
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=Nuit]').on('change',function(){
	if($(this).is(':checked'))
		$('.Nuit').show();
	else
		$('.Nuit').hide();
	if($('.eqLogicAttr[data-l1key=configuration][data-l2key=Jour]').is(':checked') || $(this).is(':checked'))
		$('.JourNuit').show();
	else
		$('.JourNuit').hide();
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=Absent]').on('change',function(){
	if($(this).is(':checked'))
		$('.Absent').show();
	else
		$('.Absent').hide();
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=Meteo]').on('change',function(){
	if($(this).is(':checked'))
		$('.Meteo').show();
	else
		$('.Meteo').hide();
});
$('.eqLogicAttr[data-l1key=configuration][data-l2key=Azimut]').on('change',function(){
	if($(this).is(':checked')){
		$('.Azimut').show();
	}else
		$('.Azimut').hide();
});
$('#bt_openMap').on('click',function(){
	$('#md_modal').dialog({
		title: "{{Séléctionner vos angles}}",
		resizable: true
	});
	$('#md_modal').load('index.php?v=d&modal=Volets.MapsAngles&plugin=Volets&type=Volets').dialog('open');
});
function saveEqLogic(_eqLogic) {
	_eqLogic.configuration.condition=new Object();
	_eqLogic.configuration.action=new Object();
	var ConditionArray= new Array();
	var ActionArray= new Array();
	$('#conditiontab .ConditionGroup').each(function( index ) {
		ConditionArray.push($(this).getValues('.expressionAttr')[0])
	});
	$('#actiontab .ActionGroup').each(function( index ) {
		ActionArray.push($(this).getValues('.expressionAttr')[0])
	});
	_eqLogic.configuration.condition=ConditionArray;
	_eqLogic.configuration.action=ActionArray;
   	return _eqLogic;
}
function printEqLogic(_eqLogic) {
	$('.ConditionGroup').remove();
	$('.ActionGroup').remove();
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=Droite]').val(JSON.stringify(_eqLogic.configuration.Droite));
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=Centre]').val(JSON.stringify(_eqLogic.configuration.Centre));
	$('.eqLogicAttr[data-l1key=configuration][data-l2key=Gauche]').val(JSON.stringify(_eqLogic.configuration.Gauche));
	if (typeof(_eqLogic.configuration.condition) !== 'undefined') {
		for(var index in _eqLogic.configuration.condition) {
			if( (typeof _eqLogic.configuration.condition[index] === "object") && (_eqLogic.configuration.condition[index] !== null) )
				addCondition(_eqLogic.configuration.condition[index],$('#conditiontab').find('table tbody'));
		}
	}
	if (typeof(_eqLogic.configuration.action) !== 'undefined') {
			for(var index in _eqLogic.configuration.action) {
				if( (typeof _eqLogic.configuration.action[index] === "object") && (_eqLogic.configuration.action[index] !== null) )
					addAction(_eqLogic.configuration.action[index],$('#actiontab').find('table tbody'));
			}
	}
}

function addCondition(_condition,_el) {
	var tr = $('<tr class="ConditionGroup">')
		.append($('<td>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable">'))
				.append('{{Activer}}')))
		.append($('<td>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="autoArm">'))
				.append('{{Activer}}')))
		.append($('<td>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="expressionAttr" data-l1key="Inverse">'))
				.append('{{Tester}}')))
		.append($('<td>')
			.append($('<div class="input-group">')
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-default conditionAttr btn-sm" data-action="remove">')
						.append($('<i class="fa fa-minus-circle">'))))
				.append($('<input class="expressionAttr form-control input-sm cmdCondition" data-l1key="expression"/>'))
				.append($('<span class="input-group-btn">')
					.append($('<a class="btn btn-warning btn-sm listCmdCondition">')
						.append($('<i class="fa fa-list-alt">'))))))
		.append(addParameters());

        _el.append(tr);
        _el.find('tr:last').setValues(_condition, '.expressionAttr');
	$('.conditionAttr[data-action=remove]').off().on('click',function(){
		$(this).closest('tr').remove();
	});
	$('.expressionAttr[data-l1key=autoArm]').on('change',function(){
		if($(this).is(':checked')){
			$(this).closest('tr').find('select[data-l1key=evaluation]').prop("disabled", true);
			$(this).closest('tr').find('input[data-l1key=enable]').prop("checked", false);
			$(this).closest('tr').find('input[data-l1key=enable]').prop("disabled", true);
		}else{
			$(this).closest('tr').find('select[data-l1key=evaluation]').prop("disabled", false);
			$(this).closest('tr').find('input[data-l1key=enable]').prop("disabled", false);
		}
	});
}
function addAction(_action,  _el) {
	var tr = $('<tr class="ActionGroup">');
	tr.append($('<td>')
		.append($('<label class="checkbox-inline">')
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="enable">'))
			.append('{{Activer}}')));
	tr.append($('<td>')
		.append($('<label class="checkbox-inline">')
			.append($('<input type="checkbox" class="expressionAttr" data-l1key="isVoletMove">'))
			.append('{{Activer}}')));
	tr.append($('<td>')
		.append($('<div class="input-group">')
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-default ActionAttr btn-sm" data-action="remove">')
					.append($('<i class="fa fa-minus-circle">'))))
			.append($('<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd"/>'))
			.append($('<span class="input-group-btn">')
				.append($('<a class="btn btn-success btn-sm listAction" title="Sélectionner un mot-clé">')
					.append($('<i class="fa fa-tasks">')))
				.append($('<a class="btn btn-success btn-sm listCmdAction data-type="action"">')
					.append($('<i class="fa fa-list-alt">')))))
		.append($('<div class="actionOptions">')
	       		.append($(jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options)))));
	tr.append(addParameters());
        _el.append(tr);
        _el.find('tr:last').setValues(_action, '.expressionAttr');
	$('.ActionAttr[data-action=remove]').off().on('click',function () {
		$(this).closest('.ActionGroup').remove();
	});
	$('.expressionAttr[data-l1key=cmd]').off().on('focusout', function (event) {
	    var expression = $(this).closest('.ActionGroup').getValues('.expressionAttr');
	    var el = $(this);
	    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
		el.closest('.ActionGroup').find('.actionOptions').html(html);
	    })
	});
}
$('#tab_zones a').click(function(e) {
    e.preventDefault();
    $(this).tab('show');
});
$('.conditionAttr[data-action=add]').off().on('click',function(){
	addCondition({},$(this).closest('.tab-pane').find('table'));
});
$('body').on('click','.listCmdCondition',function(){
	var el = $(this).closest('tr').find('.expressionAttr[data-l1key=expression]');
	jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
		var message = 'Aucun choix possible';
		if(result.cmd.subType == 'numeric'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égal}}</option>' +
			'                  <option value=">">{{supérieur}}</option>' +
			'                  <option value="<">{{inférieur}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input type="number" class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">rien</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'string'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="operator">' +
			'                    <option value="==">{{égale}}</option>' +
			'                  <option value="matches">{{contient}}</option>' +
			'                 <option value="!=">{{différent}}</option>' +
			'            </select>' +
			'       </div>' +
			'      <div class="col-xs-4">' +
			'         <input class="conditionAttr form-control" data-l1key="operande" />' +
			'    </div>' +
			'</div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                    <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}
		if(result.cmd.subType == 'binary'){
			message = '<div class="row">  ' +
			'<div class="col-md-12"> ' +
			'<form class="form-horizontal" onsubmit="return false;"> ' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >'+result.human+' {{est}}</label>' +
			'            <div class="col-xs-7">' +
			'                 <input class="conditionAttr" data-l1key="operator" value="==" style="display : none;" />' +
			'                  <select class="conditionAttr form-control" data-l1key="operande">' +
			'                       <option value="1">{{Ouvert}}</option>' +
			'                       <option value="0">{{Fermé}}</option>' +
			'                       <option value="1">{{Allumé}}</option>' +
			'                       <option value="0">{{Éteint}}</option>' +
			'                       <option value="1">{{Déclenché}}</option>' +
			'                       <option value="0">{{Au repos}}</option>' +
			'                       </select>' +
			'                    </div>' +
			'                 </div>' +
			'<div class="form-group"> ' +
			'<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
			'             <div class="col-xs-3">' +
			'                <select class="conditionAttr form-control" data-l1key="next">' +
			'                  <option value="">{{rien}}</option>' +
			'                  <option value="OU">{{ou}}</option>' +
			'            </select>' +
			'       </div>' +
			'</div>' +
			'</div> </div>' +
			'</form> </div>  </div>';
		}

		bootbox.dialog({
			title: "{{Ajout d'une nouvelle condition}}",
			message: message,
			buttons: {
				"Ne rien mettre": {
					className: "btn-default",
					callback: function () {
						el.atCaret('insert', result.human);
					}
				},
				success: {
					label: "Valider",
					className: "btn-primary",
					callback: function () {
    						var condition = result.human;
						condition += ' ' + $('.conditionAttr[data-l1key=operator]').value();
						if(result.cmd.subType == 'string'){
							if($('.conditionAttr[data-l1key=operator]').value() == 'matches'){
								condition += ' "/' + $('.conditionAttr[data-l1key=operande]').value()+'/"';
							}else{
								condition += ' "' + $('.conditionAttr[data-l1key=operande]').value()+'"';
							}
						}else{
							condition += ' ' + $('.conditionAttr[data-l1key=operande]').value();
						}
						condition += ' ' + $('.conditionAttr[data-l1key=next]').value()+' ';
						el.atCaret('insert', condition);
						if($('.conditionAttr[data-l1key=next]').value() != ''){
							el.click();
						}
					}
				},
			}
		});
	});
});
$('.ActionAttr[data-action=add]').off().on('click',function(){
	addAction({},$(this).closest('.tab-pane').find('table'));
});
$("body").on('click', ".listAction", function() {
	var el = $(this).closest('.input-group').find('input');
	jeedom.getSelectActionModal({}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(el.value(), '', function (html) {
			el.closest('.form-group').find('.actionOptions').html(html);
		});
	});
});
$("body").on('click', ".listCmdAction", function() {
	var el = $(this).closest('.input-group').find('input');
	var type=$(this).attr('data-type');
	jeedom.cmd.getSelectModal({cmd: {type: type}}, function (result) {
		el.value(result.human);
		jeedom.cmd.displayActionOption(result.human, '', function (html) {
			el.closest('td').find('.actionOptions').html(html);
		});
	});
});
function addCmdToTable(_cmd) {
	var tr =$('<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">');
	tr.append($('<td>')
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="id">'))
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="type">'))
		.append($('<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="subType">'))
		.append($('<input class="tooltips cmdAttr form-control input-sm" data-l1key="name" value="' + init(_cmd.name) + '" placeholder="{{Name}}" title="Name">')));
	tr.append($('<td>')
		.append($('<span>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="cmdAttr checkbox-inline" data-size="mini" data-label-text="{{Historiser}}" data-l1key="isHistorized"/>'))
				.append('{{Historiser}}')
				.append($('<sup>')
					.append($('<i class="fa fa-question-circle tooltips" style="font-size : 1em;color:grey;">')
					.attr('title','Souhaitez-vous historiser les changements de valeurs ?')))))
		.append($('<span>')
			.append($('<label class="checkbox-inline">')
				.append($('<input type="checkbox" class="cmdAttr checkbox-inline" data-size="mini" data-label-text="{{Afficher}}" data-l1key="isVisible" checked/>'))
				.append('{{Afficher}}')
				.append($('<sup>')
					.append($('<i class="fa fa-question-circle tooltips" style="font-size : 1em;color:grey;">')
					.attr('title','Souhaitez-vous afficher cette commande sur le dashboard ?')))))
		.append($('<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" >'))
		.append($('<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" >')));
	var parmetre=$('<td>');
	if (is_numeric(_cmd.id)) {
		parmetre.append($('<a class="btn btn-default btn-xs cmdAction" data-action="test">')
			.append($('<i class="fa fa-rss">')
				.text('{{Tester}}')));
	}
	parmetre.append($('<a class="btn btn-default btn-xs cmdAction tooltips" data-action="configure">')
		.append($('<i class="fa fa-cogs">')));
	tr.append(parmetre);
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
function addParameters() {
	var gestions=$('<select class="expressionAttr form-control input-sm cmdAction" data-l1key="TypeGestion" multiple>');
	$.each(GestionsVolets,function( index, value ) {
		gestions.append($('<option value="'+value+'">').text(value));
	});
	var Parameter=$('<div>');
	Parameter.append($('<td>')
		 .append(gestions));
	Parameter.append($('<td>')
		 .append($('<select class="expressionAttr form-control input-sm cmdAction" data-l1key="saison" multiple>')
			.append($('<option value="été">')
				.text('{{Eté}}'))
			.append($('<option value="hiver">')
				.text('{{Hiver}}'))));
	Parameter.append($('<td>')
		 .append($('<select class="expressionAttr form-control input-sm cmdAction" data-l1key="evaluation" multiple>')
			.append($('<option value="close">')
				.text('{{Fermeture}}'))
			.append($('<option value="open">')
				.text('{{Ouverture}}'))));
	return Parameter.children();
}
