function initACFTelephoneInputMask(){if("undefined"!=typeof Inputmask)for(var e=document.querySelectorAll(".acf-input-mask"),t=0;t<e.length;t++){var n=e[t].getAttribute("data-imask");Inputmask(n,{jitMasking:!1,showMaskOnHover:!1}).mask(e[t]),Inputmask.setValue(e[t],e[t].defaultValue)}}document.addEventListener("DOMContentLoaded",function(e){initACFTelephoneInputMask()});

