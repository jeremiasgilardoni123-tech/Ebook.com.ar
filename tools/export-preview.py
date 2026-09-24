# Exporta el WordPress local (tools/dev-setup.sh) a HTML estático navegable en preview/.
# Uso: python3 tools/export-preview.py   (con el servidor local corriendo en :8080)
import re, os, base64, urllib.request, shutil, posixpath
BASE='http://127.0.0.1:8080'
OUT=os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'preview')
shutil.rmtree(OUT, ignore_errors=True); os.makedirs(OUT)
def get(u):
    return urllib.request.urlopen(BASE+u).read()
seen=set(); queue=['/']; pages={}
while queue:
    u=queue.pop(0)
    if u in seen: continue
    seen.add(u)
    try: html=get(u).decode()
    except Exception as e: print('skip',u,e); continue
    pages[u]=html
    for m in re.findall(r'href="(?:%s)?(/[^"#?]*)"'%re.escape(BASE), html):
        if re.search(r'wp-(admin|login|json|content|includes)|xmlrpc|feed|\.\w+$', m): continue
        if m not in seen: queue.append(m)
# assets
assets=set()
for h in pages.values():
    assets.update(re.findall(r'%s(/wp-(?:content|includes)/[^"\'?) ]+)'%re.escape(BASE), h))
for a in assets:
    p=OUT+a; os.makedirs(os.path.dirname(p), exist_ok=True)
    try: open(p,'wb').write(get(a))
    except Exception as e: print('asset fail',a,e)
def rel(frm, to):
    d=posixpath.dirname(frm)
    r=posixpath.relpath(to, d)
    return r

def target_of(path):
    m=re.match(r'([^?#]*)(\?[^#]*)?(#.*)?$', path)
    base, q, frag = m.group(1) or '/', m.group(2) or '', m.group(3) or ''
    if base.startswith('/wp-'): return base, ''
    t = base if re.search(r'\.\w+$', base) else base.rstrip('/')+'/index.html'
    return t, q.replace('&#038;','&')+frag
for u,h in pages.items():
    page=u.rstrip('/')+'/index.html' if u!='/' else '/index.html'
    def rep(m):
        t, rest = target_of(m.group(1) or '/')
        return rel(page, t) + rest
    h=re.sub(r'%s(/[^"\'\s)<]*)?'%re.escape(BASE), rep, h)
    # URLs escapadas dentro del JSON del simulador
    def rep_json(m):
        t, rest = target_of((m.group(1) or '/').replace('\\/','/'))
        return rel(page, t).replace('/','\\/') + rest
    h=re.sub(r'http:\\/\\/127\.0\.0\.1:8080((?:\\/[^"\\]*)*\\/?)', rep_json, h)
    p=OUT+page; os.makedirs(os.path.dirname(p), exist_ok=True); open(p,'w').write(h)
print(len(pages),'páginas', len(assets),'assets')
