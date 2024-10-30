document.getElementsByTagName('head')[0].appendChild(function(s){
    var d=document,m2g=d.createElement('script'), l=function(){Mobi2Go.load(s.container,s.ready);},jq=window.jQuery&&+window.jQuery.fn.jquery.substring(0,3)>=1.7,qs=window.location.search.substring(1),re='=(.*?)(?:;|$)',c=d.cookie.match('MOBI2GO_SESSIONID'+re);
    m2g.src='https://www.mobi2go.com/store/embed/' + mobi2go.site + '.js?'+qs+(jq?'&no_jquery':'')+(c?'&s='+c[1]:'');
    if(m2g.onload!==undefined)m2g.onload=l;else m2g.onreadystatechange=function(){if(m2g.readyState!=='loaded'&&m2g.readyState!=='complete')return;m2g.onreadystatechange=null;l();}
    return m2g;
}({
    container: mobi2go.container,
    ready: function() {}
}));
