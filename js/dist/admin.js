module.exports=function(e){var t={};function n(a){if(t[a])return t[a].exports;var r=t[a]={i:a,l:!1,exports:{}};return e[a].call(r.exports,r,r.exports,n),r.l=!0,r.exports}return n.m=e,n.c=t,n.d=function(e,t,a){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(n.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(a,r,function(t){return e[t]}.bind(null,r));return a},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=8)}({0:function(e,t,n){"use strict";t.a={module:{name:"away-puls-oauth"}}},1:function(e,t){e.exports=flarum.core.compat.app},8:function(e,t,n){"use strict";n.r(t);var a=n(1),r=n.n(a),o=n(0);r.a.initializers.add(o.a.module.name,(function(e){e.extensionData.for(o.a.module.name).registerSetting({setting:o.a.module.name+".appurl",label:e.translator.trans(o.a.module.name+".admin.appurl_label"),help:e.translator.trans(o.a.module.name+".admin.appurl_help"),type:"text"},30).registerSetting({setting:o.a.module.name+".appid",label:e.translator.trans(o.a.module.name+".admin.appid_label"),type:"text"},30).registerSetting({setting:o.a.module.name+".appkey",label:e.translator.trans(o.a.module.name+".admin.appkey_label"),type:"text"},30).registerSetting({setting:o.a.module.name+".openqq",label:e.translator.trans(o.a.module.name+".admin.openqq_label"),type:"boolean"},30).registerSetting({setting:o.a.module.name+".openwx",label:e.translator.trans(o.a.module.name+".admin.openwx_label"),type:"boolean"},30).registerSetting({setting:o.a.module.name+".opensina",label:e.translator.trans(o.a.module.name+".admin.opensina_label"),type:"boolean"},30)}))}});
//# sourceMappingURL=admin.js.map