var ACF_Timepicker_Init=function(){function n(){this.init()}return n.prototype.init=function(){document.querySelectorAll("joomla-field-subform").forEach(function(n){n.addEventListener("subform-row-add",function(n){jQuery(".clockpicker").clockpicker()})})},n}();document.addEventListener("DOMContentLoaded",function(n){new ACF_Timepicker_Init});

