function htmlspecialchars(b,a,e,c){var d=e=0,f=!1;if("undefined"===typeof a||null===a)a=2;b=b.toString();!1!==c&&(b=b.replace(/&/g,"&amp;"));b=b.replace(/</g,"&lt;").replace(/>/g,"&gt;");c={ENT_NOQUOTES:0,ENT_HTML_QUOTE_SINGLE:1,ENT_HTML_QUOTE_DOUBLE:2,ENT_COMPAT:2,ENT_QUOTES:3,ENT_IGNORE:4};0===a&&(f=!0);if("number"!==typeof a){a=[].concat(a);for(d=0;d<a.length;d++)0===c[a[d]]?f=!0:c[a[d]]&&(e|=c[a[d]]);a=e}a&c.ENT_HTML_QUOTE_SINGLE&&(b=b.replace(/'/g,"&#039;"));f||(b=b.replace(/"/g,"&quot;"));return b};
function uniqid(c,e){"undefined"===typeof c&&(c="");var a,d=function(b,a){b=parseInt(b,10).toString(16);return a<b.length?b.slice(b.length-a):a>b.length?Array(1+(a-b.length)).join("0")+b:b};this.php_js||(this.php_js={});this.php_js.uniqidSeed||(this.php_js.uniqidSeed=Math.floor(123456789*Math.random()));this.php_js.uniqidSeed++;a=c+d(parseInt((new Date).getTime()/1E3,10),8);a+=d(this.php_js.uniqidSeed,5);e&&(a+=(10*Math.random()).toFixed(8).toString());return a};
function nl2br(b,a){return(b+"").replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,"$1"+(a||"undefined"===typeof a?"<br />":"<br>")+"$2")};
function number_format(b,c,d,e){b=(b+"").replace(/[^0-9+\-Ee.]/g,"");b=isFinite(+b)?+b:0;c=isFinite(+c)?Math.abs(c):0;e="undefined"===typeof e?",":e;d="undefined"===typeof d?".":d;var a="",a=function(a,b){var c=Math.pow(10,b);return""+(Math.round(a*c)/c).toFixed(b)},a=(c?a(b,c):""+Math.round(b)).split(".");3<a[0].length&&(a[0]=a[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,e));(a[1]||"").length<c&&(a[1]=a[1]||"",a[1]+=Array(c-a[1].length+1).join("0"));return a.join(d)};
function stristr(a,c,d){var b=0;a+="";b=a.toLowerCase().indexOf((c+"").toLowerCase());return-1==b?!1:d?a.substr(0,b):a.slice(b)};