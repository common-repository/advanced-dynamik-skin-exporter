    window.onload = function() {
        var cgParentDiv = document.getElementById("dynamik-design-options-nav-import-export-box");
		var inputs = document.getElementsByTagName('input');
		var values;

			for (i=0; i<inputs.length; i++){
				if (inputs[i].value == 'dynamik_design_export'){
				
					inputs[i].value='cg_dynamik_design_export';
				}
			}
		
		}
