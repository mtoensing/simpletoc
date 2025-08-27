(function(){
  function pickContainer(){
    var candidates = [];
    function pushAll(sel){ document.querySelectorAll(sel).forEach(function(el){ candidates.push(el); }); }

    pushAll('[role="main"]');
    pushAll('.wp-block-post-content');
    pushAll('article .entry-content');
    pushAll('.entry-content');
    pushAll('main');
    pushAll('.content-area');
    pushAll('#content');

    var blacklist = ['header','footer','nav','aside','.widget','[aria-hidden="true"]'];
    function isBlacklisted(el){
      return blacklist.some(function(sel){ return el.closest(sel); });
    }
    function countHeadings(root){
      if(!root || isBlacklisted(root)) return 0;
      return root.querySelectorAll('h1,h2,h3,h4,h5,h6').length;
    }

    var best = null, bestCount = 0;
    candidates.forEach(function(c){
      var n = countHeadings(c);
      if(n > bestCount){ best = c; bestCount = n; }
    });
    if(!best && countHeadings(document.body) > 0) return document.body;
    return best;
  }

  function slugify(t){
    return t.trim().toLowerCase()
      .replace(/[^\w\s-]/g,'')
      .replace(/\s+/g,'-')
      .replace(/-+/g,'-')
      .slice(0,80);
  }

  function buildTOC(container, title){
    var hs = container.querySelectorAll('h1,h2,h3,h4,h5,h6');
    if(!hs.length) return null;

    var current = parseInt(hs[0].tagName.substring(1),10);
    var frag = document.createDocumentFragment();

    if(title){
      var tt = document.createElement('div');
      tt.className = 'simpletoc-title';
      tt.textContent = title;
      frag.appendChild(tt);
    }

    var root = document.createElement('ul');
    root.className = 'simpletoc-list';
    var stack = [root];

    hs.forEach(function(h){
      var level = parseInt(h.tagName.substring(1),10);
      if(!h.id){
        var id = slugify(h.textContent || 'section');
        var u = id, n = 2;
        while(document.getElementById(u)) u = id + '-' + n++;
        h.id = u;
      }

      if(level > current){
        for(var up=current; up<level; up++){
          var ul = document.createElement('ul');
          stack[stack.length-1].appendChild(ul);
          stack.push(ul);
        }
      } else if(level < current){
        for(var down=current; down>level; down--){
          stack.pop();
        }
      }
      current = level;

      var li = document.createElement('li');
      var a = document.createElement('a');
      a.href = '#' + encodeURIComponent(h.id);
      a.textContent = (h.textContent || '').trim();
      li.appendChild(a);
      stack[stack.length-1].appendChild(li);
    });

    frag.appendChild(root);
    return frag;
  }

  function enableSmooth(){
    if(document.getElementById('simpletoc-smooth-style')) return;
    var s = document.createElement('style');
    s.id = 'simpletoc-smooth-style';
    s.textContent = 'html{scroll-behavior:smooth}';
    document.head.appendChild(s);
  }

  function init(){
    document.querySelectorAll('.simpletoc.simpletoc-autoscope[data-simpletoc-autoscope="1"]').forEach(function(nav){
      var title = nav.getAttribute('data-simpletoc-title') || '';
      var smooth = nav.getAttribute('data-simpletoc-smooth') === '1';

      var container = pickContainer();
      if(!container){ nav.remove(); return; }

      var built = buildTOC(container, title);
      if(!built){ nav.remove(); return; }

      nav.appendChild(built);

      if(smooth){
        enableSmooth();
        nav.addEventListener('click', function(e){
          var a = e.target.closest('a[href^="#"]');
          if(!a) return;
          var id = decodeURIComponent(a.getAttribute('href').slice(1));
          var target = document.getElementById(id);
          if(target){
            e.preventDefault();
            try{ target.scrollIntoView({behavior:'smooth', block:'start'}); }
            catch(err){ window.location.hash = id; }
          }
        });
      }
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
