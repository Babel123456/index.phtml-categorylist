(function(a){function p(a){a=parseInt(a).toString(16);return 2>a.length?"0"+a:a}function y(f){for(;f;){var b=a.css(f,"backgroundColor");if(b&&"transparent"!=b&&"rgba(0, 0, 0, 0)"!=b)return 0<=b.indexOf("rgb")?(f=b.match(/\d+/g),"#"+p(f[0])+p(f[1])+p(f[2])):b;if("html"==f.nodeName.toLowerCase())break;f=f.parentNode}return"#ffffff"}function z(a,b,c){switch(a){case "round":return Math.round(c*(1-Math.cos(Math.asin(b/c))));case "cool":return Math.round(c*(1+Math.cos(Math.asin(b/c))));case "sharp":return c-
b;case "bite":return Math.round(c*Math.cos(Math.asin((c-b-1)/c)));case "slide":return Math.round(c*Math.atan2(b,c/b));case "jut":return Math.round(c*Math.atan2(c,c-b-1));case "curl":return Math.round(c*Math.atan(b));case "tear":return Math.round(c*Math.cos(b));case "wicked":return Math.round(c*Math.tan(b));case "long":return Math.round(c*Math.sqrt(b));case "sculpt":return Math.round(c*Math.log(c-b-1,c));case "dogfold":case "dog":return b&1?b+1:c;case "dog2":return b&2?b+1:c;case "dog3":return b&3?
b+1:c;case "fray":return b%2*c;case "notch":return c;case "bevelfold":case "bevel":return b+1;case "steep":return b/2+1;case "invsteep":return(c-b)/2+1}}var f=document.createElement("div").style,l=void 0!==f.MozBorderRadius,s=void 0!==f.WebkitBorderRadius,m=void 0!==f.borderRadius||void 0!==f.BorderRadius,f=document.documentMode||0,A=a.browser.msie&&(8>a.browser.version&&!f||8>f);if(f=a.browser.msie)a:{f=document.createElement("div");try{f.style.setExpression("width","0+0"),f.style.removeExpression("width")}catch(C){f=
!1;break a}f=!0}var v=f;a.support=a.support||{};a.support.borderRadius=l||s||m;a.fn.corner=function(f){if(0==this.length){if(!a.isReady&&this.selector){var b=this.selector,c=this.context;a(function(){a(b,c).corner(f)})}return this}return this.each(function(){var b,c,p,t,i=a(this),e=[i.attr(a.fn.corner.defaults.metaAttr)||"",f||""].join(" ").toLowerCase(),u=/keep/.test(e),h=(e.match(/cc:(#[0-9a-f]+)/)||[])[1];b=(e.match(/sc:(#[0-9a-f]+)/)||[])[1];var g=parseInt((e.match(/(\d+)px/)||[])[1])||10,w=(e.match(/round|bevelfold|bevel|notch|bite|cool|sharp|slide|jut|curl|tear|fray|wicked|sculpt|long|dog3|dog2|dogfold|dog|invsteep|steep/)||
["round"])[0],B=/dogfold|bevelfold/.test(e),x={T:0,B:1},e={TL:/top|tl|left/.test(e),TR:/top|tr|right/.test(e),BL:/bottom|bl|left/.test(e),BR:/bottom|br|right/.test(e)},q,k,d,j,r,n;!e.TL&&(!e.TR&&!e.BL&&!e.BR)&&(e={TL:1,TR:1,BL:1,BR:1});if(a.fn.corner.defaults.useNative&&"round"==w&&(m||l||s)&&!h&&!b)e.TL&&i.css(m?"border-top-left-radius":l?"-moz-border-radius-topleft":"-webkit-border-top-left-radius",g+"px"),e.TR&&i.css(m?"border-top-right-radius":l?"-moz-border-radius-topright":"-webkit-border-top-right-radius",
g+"px"),e.BL&&i.css(m?"border-bottom-left-radius":l?"-moz-border-radius-bottomleft":"-webkit-border-bottom-left-radius",g+"px"),e.BR&&i.css(m?"border-bottom-right-radius":l?"-moz-border-radius-bottomright":"-webkit-border-bottom-right-radius",g+"px");else for(q in i=document.createElement("div"),a(i).css({overflow:"hidden",height:"1px",minHeight:"1px",fontSize:"1px",backgroundColor:b||"transparent",borderStyle:"solid"}),b=parseInt(a.css(this,"paddingTop"))||0,c=parseInt(a.css(this,"paddingRight"))||
0,p=parseInt(a.css(this,"paddingBottom"))||0,t=parseInt(a.css(this,"paddingLeft"))||0,void 0!=typeof this.style.zoom&&(this.style.zoom=1),u||(this.style.border="none"),i.style.borderColor=h||y(this.parentNode),u=a(this).outerHeight(),x)if((h=x[q])&&(e.BL||e.BR)||!h&&(e.TL||e.TR)){i.style.borderStyle="none "+(e[q+"R"]?"solid":"none")+" none "+(e[q+"L"]?"solid":"none");k=document.createElement("div");a(k).addClass("jquery-corner");d=k.style;h?this.appendChild(k):this.insertBefore(k,this.firstChild);
h&&"auto"!=u?("static"==a.css(this,"position")&&(this.style.position="relative"),d.position="absolute",d.bottom=d.left=d.padding=d.margin="0",v?d.setExpression("width","this.parentNode.offsetWidth"):d.width="100%"):!h&&a.browser.msie?("static"==a.css(this,"position")&&(this.style.position="relative"),d.position="absolute",d.top=d.left=d.right=d.padding=d.margin="0",v?(j=(parseInt(a.css(this,"borderLeftWidth"))||0)+(parseInt(a.css(this,"borderRightWidth"))||0),d.setExpression("width","this.parentNode.offsetWidth - "+
j+'+ "px"')):d.width="100%"):(d.position="relative",d.margin=!h?"-"+b+"px -"+c+"px "+(b-g)+"px -"+t+"px":p-g+"px -"+c+"px -"+p+"px -"+t+"px");for(d=0;d<g;d++)j=Math.max(0,z(w,d,g)),r=i.cloneNode(!1),r.style.borderWidth="0 "+(e[q+"R"]?j:0)+"px 0 "+(e[q+"L"]?j:0)+"px",h?k.appendChild(r):k.insertBefore(r,k.firstChild);if(B&&a.support.boxModel&&(!h||!A))for(n in e)if(e[n]&&(!h||!("TL"==n||"TR"==n)))if(h||!("BL"==n||"BR"==n)){d={position:"absolute",border:"none",margin:0,padding:0,overflow:"hidden",backgroundColor:i.style.borderColor};
j=a("<div/>").css(d).css({width:g+"px",height:"1px"});switch(n){case "TL":j.css({bottom:0,left:0});break;case "TR":j.css({bottom:0,right:0});break;case "BL":j.css({top:0,left:0});break;case "BR":j.css({top:0,right:0})}k.appendChild(j[0]);d=a("<div/>").css(d).css({top:0,bottom:0,width:"1px",height:g+"px"});switch(n){case "TL":d.css({left:g});break;case "TR":d.css({right:g});break;case "BL":d.css({left:g});break;case "BR":d.css({right:g})}k.appendChild(d[0])}}})};a.fn.uncorner=function(){if(m||l||s)this.css(m?
"border-radius":l?"-moz-border-radius":"-webkit-border-radius",0);a("div.jquery-corner",this).remove();return this};a.fn.corner.defaults={useNative:!0,metaAttr:"data-corner"}})(jQuery);