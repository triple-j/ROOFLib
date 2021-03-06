var _ToggleControllers = Array();

function ToggleController(id, options, start) {
	this.id 		= id;
	this.options 	= options;

	this.switch = function(name) {
		for (var i in this.options) {
			$("#"+this.options[i]).find(":input").attr("disabled", "disabled");
			$("#"+this.options[i]).find("{cfg prefix_class}_matrix").attr("disabled", "disabled");
		}
		$("#"+name).find(":input").removeAttr("disabled");
		$("#"+name).find("{cfg prefix_class}_matrix").removeAttr("disabled");
	}
	_ToggleControllers[this.id] = this;
	var value = $('[name='+id+']:checked').val();
	_start = value+'_tci';
	this.switch(_start);
}
