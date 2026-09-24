# Empaqueta preview/ (tools/export-preview.py) en una sola página navegable por #hash.
# Uso: OUT=motocred-preview.html python3 tools/build-preview-spa.py
import re, os, base64, posixpath, json, html as H
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'preview')
OUT=os.environ['OUT']
pages={}
for dp,_,fs in os.walk(ROOT):
    if 'wp-content' in dp: continue
    for f in fs:
        if f=='index.html':
            rel=os.path.relpath(dp,ROOT); rel='' if rel=='.' else rel
            pages[rel]=open(os.path.join(dp,f)).read()
def token(path, query=''):
    t=path.strip('/').replace('/','--') or 'inicio'
    for k,v in re.findall(r'(\w+)=(\d+)', query):
        t+='~%s-%s'%(k,v)
    return t
def rewrite(snippet, pagedir):
    def rep(m):
        href=m.group(1)
        if href.startswith(('http','#','tel:','mailto:')): return m.group(0)
        mm=re.match(r'([^?#]*)(\?[^#]*)?(#.*)?$', href)
        p=posixpath.normpath(posixpath.join('/'+pagedir, mm.group(1)))
        if not p.endswith('index.html'): return m.group(0)
        p=p[:-len('index.html')]
        return 'href="#%s"'%token(p, (mm.group(2) or '').replace('&amp;','&').replace('&#038;','&'))
    return re.sub(r'href="([^"]*)"', rep, snippet)
home=pages['']
header=re.search(r'<header class="mc-header".*?</header>', home, re.S).group(0)
footer=re.search(r'<footer class="mc-footer".*?</nav>', home, re.S).group(0)  # footer + tabbar
demo=re.search(r'<div class="mc-demo-bar".*?</div>', home, re.S).group(0)
payload=re.search(r'window\.MOTOCRED=(\{.*?\});\n', home, re.S).group(1)
# payload urls -> tokens
def rep_json(m):
    p=m.group(1).replace('\\/','/')
    p=posixpath.normpath(posixpath.join('/', p)).replace('index.html','')
    return '"#%s"'%token(p)
payload=re.sub(r'"((?:\.\.\\/)*[^"]*?index\.html)"', rep_json, payload)
templates=[]
titles={}
for rel,h in sorted(pages.items()):
    main=re.search(r'<main id="contenido" class="mc-main">(.*)</main>', h, re.S).group(1)
    t=re.search(r'<title>(.*?)</title>', h).group(1)
    tok=token('/'+rel)
    titles[tok]=H.unescape(t)
    templates.append('<template id="route-%s">%s</template>'%(tok, rewrite(main, rel)))
css=open(ROOT+'/wp-content/plugins/motocred-core/assets/css/motocred.css').read()+'\n'+open(ROOT+'/wp-content/themes/motocred-2026/assets/css/theme.css').read()
font=base64.b64encode(open(ROOT+'/wp-content/themes/motocred-2026/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2','rb').read()).decode()
brand=re.search(r":root\{--mc-brand:[^}]*\}", home).group(0)
mcjs=open(ROOT+'/wp-content/plugins/motocred-core/assets/js/motocred.js').read().replace('	function boot() {','	window.mcBoot = boot;\n	function boot() {').replace("	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }",'')
themejs=open(ROOT+'/wp-content/themes/motocred-2026/assets/js/theme.js').read()
i=themejs.index('	/* Catálogo: filtros en el cliente */')
theme_once=themejs[:i]+'})();'
theme_page="window.mcThemePage=function(){'use strict';\n"+themejs[i:themejs.rindex('})();')]+'};'
extra_css='''
.mc-header{top:env(safe-area-inset-top,0px)}
.mc-branch__map{display:none}
.mc-preview-bar{display:flex;flex-wrap:wrap;gap:.25rem 1rem;justify-content:center;padding:.55rem 1rem;background:#0f1217;color:#fff;font:600 .8125rem/1.4 var(--mc-font);text-align:center}
.mc-preview-bar span{opacity:.7;font-weight:500}
.mc-wa-sheet{position:fixed;inset:0;z-index:300;display:grid;place-items:end center;padding:16px;background:rgb(15 18 23/.45)}
@media(min-width:700px){.mc-wa-sheet{place-items:center}}
.mc-wa-sheet__card{width:min(100%,440px);padding:1.25rem;border-radius:20px;background:#fff;color:var(--mc-ink);font-family:var(--mc-font);box-shadow:0 30px 80px -30px rgb(0 0 0/.5);margin-bottom:env(safe-area-inset-bottom,0px)}
.mc-wa-sheet__card h2{font-size:1.125rem;font-weight:800;margin:0 0 .25rem}
.mc-wa-sheet__card p{color:var(--mc-muted);font-size:.875rem;margin:0 0 .9rem}
.mc-wa-sheet__bubble{white-space:pre-wrap;padding:.9rem 1rem;border-radius:14px 14px 4px 14px;background:#d9fdd3;color:#111b21;font-size:.9375rem;line-height:1.45;margin:0 0 1rem}
.mc-wa-sheet__meta{font-size:.75rem;color:var(--mc-muted);margin:-.5rem 0 1rem}
'''
shell=r'''
(function(){
  var TITLES=%s;
  var main=document.getElementById('contenido');
  function parse(h){var t=(h||'').replace(/^#/,'')||'inicio';var parts=t.split('~');var q={};parts.slice(1).forEach(function(p){var m=p.match(/^(\w+)-(\d+)$/);if(m)q[m[1]]=m[2];});return{route:parts[0],q:q,raw:t};}
  function render(push){
    var r=parse(location.hash);
    var tpl=document.getElementById('route-'+r.route);
    if(!tpl){ var el=document.getElementById(r.raw); if(el){el.scrollIntoView({behavior:'smooth'});} return; }
    main.replaceChildren(tpl.content.cloneNode(true));
    document.title=TITLES[r.route]||'MotoCred';
    // preselección del simulador (equivale a ?plan= / ?plazo= / ?moto=)
    main.querySelectorAll('[data-mc-sim]').forEach(function(f){
      if(r.q.moto){var o=f.querySelector('select[data-mc-sim-moto]');if(o)o.value=r.q.moto;var mo=(window.MOTOCRED.motos||[]).filter(function(m){return String(m.id)===r.q.moto;})[0];if(mo&&!r.q.plan)r.q.plan=String(mo.plan);}
      if(r.q.plan){var p=f.querySelector('input[name=plan][value="'+r.q.plan+'"]');if(p)p.checked=true;}
      if(r.q.plazo){var z=f.querySelector('input[name=plazo][value="'+r.q.plazo+'"]');if(z)z.checked=true;}
    });
    window.mcBoot&&window.mcBoot();
    window.mcThemePage&&window.mcThemePage();
    document.querySelectorAll('.mc-nav__list a, .mc-drawer__list a').forEach(function(a){var on=parse(a.getAttribute('href')).route.split('--')[0]===r.route.split('--')[0];a.parentNode.classList.toggle('current-menu-item',on);});
    document.querySelectorAll('.mc-tabbar__item').forEach(function(a){var h=a.getAttribute('href')||'';a.classList.toggle('is-active',h.charAt(0)==='#'&&parse(h).route===r.route.split('--')[0]&&r.route!=='inicio');});
    var target=r.q && main.querySelector('#simulador') && r.route==='simulador' ? null : null;
    window.scrollTo(0,0);
  }
  window.addEventListener('hashchange',function(){render();});
  // WhatsApp: en la vista previa se muestra el mensaje que recibiría el asesor
  document.addEventListener('click',function(e){
    var a=e.target.closest('a[href^="https://wa.me/"]'); if(!a)return;
    e.preventDefault();
    var u=new URL(a.href); var txt=u.searchParams.get('text')||'';
    var s=document.createElement('div'); s.className='mc-wa-sheet'; s.setAttribute('role','dialog'); s.setAttribute('aria-modal','true');
    s.innerHTML='<div class="mc-wa-sheet__card"><h2>Así le llega al asesor</h2><p>En el sitio real este botón abre WhatsApp con este mensaje ya escrito.</p><div class="mc-wa-sheet__bubble"></div><p class="mc-wa-sheet__meta">Número de ejemplo: +'+u.pathname.slice(1)+'</p><button type="button" class="mc-btn mc-btn--primary mc-btn--block">Entendido</button></div>';
    s.querySelector('.mc-wa-sheet__bubble').textContent=txt;
    function close(){s.remove();}
    s.addEventListener('click',function(ev){if(ev.target===s||ev.target.closest('button'))close();});
    document.addEventListener('keydown',function k(ev){if(ev.key==='Escape'){close();document.removeEventListener('keydown',k);}});
    document.body.appendChild(s); s.querySelector('button').focus();
  });
  render();
})();
''' % json.dumps(titles, ensure_ascii=False)
doc='''<title>MotoCred 2026</title>
<meta name="description" content="Vista previa del nuevo sitio de MotoCred con datos de ejemplo.">
<style>@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-display:swap;font-weight:200 800;src:url(data:font/woff2;base64,%s) format('woff2');}
%s
%s
%s</style>
<div class="mc-preview-bar">Vista previa del nuevo MotoCred <span>Importes de ejemplo marcados «A validar»: no son precios reales. El logo real se toma del WordPress.</span></div>
%s
<main id="contenido" class="mc-main"></main>
%s
%s
<script>window.MOTOCRED=%s;</script>
<script>%s</script>
<script>%s</script>
<script>%s</script>
<script>%s</script>
''' % (font, css, brand, extra_css, rewrite(header,''), rewrite(footer,''), '\n'.join(templates), payload, mcjs, theme_once, theme_page, shell)
open(OUT,'w').write(doc)
print(len(pages),'rutas', len(doc)//1024,'KB', sorted(titles))
