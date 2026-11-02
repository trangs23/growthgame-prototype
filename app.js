(function(){
  function getToast(){
    let toast=document.getElementById('toast');
    if(!toast){
      toast=document.createElement('div');
      toast.id='toast';
      toast.className='toast';
      document.body.appendChild(toast);
    }
    return toast;
  }
  function showToast(msg){
    const toast=getToast();
    toast.textContent=msg;
    toast.classList.add('show');
    setTimeout(()=>toast.classList.remove('show'),2200);
  }
  window.ggToast=showToast;

  function cartCount(){
    try{return parseInt(localStorage.getItem('gg_cart')||'0',10)||0;}catch(e){return 0;}
  }
  function setCart(n){
    try{localStorage.setItem('gg_cart',String(n));}catch(e){}
    document.querySelectorAll('[data-cart-count]').forEach(el=>{el.textContent=n;});
  }
  setCart(cartCount());

  document.querySelectorAll('[data-add-cart]').forEach(btn=>{
    btn.addEventListener('click',e=>{
      e.preventDefault();
      const qtyEl=document.querySelector('[data-qty]');
      const qty=qtyEl?Math.max(1,parseInt(qtyEl.value,10)||1):1;
      const n=cartCount()+qty;
      setCart(n);
      showToast('ใส่ตะกร้าแล้ว (ม็อก) · รวม '+n+' ชิ้น');
    });
  });

  document.querySelectorAll('[data-download]').forEach(btn=>{
    btn.addEventListener('click',e=>{
      e.preventDefault();
      showToast('ดาวน์โหลด (ม็อก) · ไฟล์ยังไม่จริง');
    });
  });

  const notify=document.getElementById('notify-form');
  if(notify){
    notify.addEventListener('submit',e=>{
      e.preventDefault();
      const ok=document.getElementById('notify-ok');
      if(ok) ok.classList.add('show');
      notify.reset();
      showToast('รับเรื่องแล้ว (ม็อก) · ยังไม่เก็บอีเมลจริง');
    });
  }

  document.querySelectorAll('[data-chip]').forEach(chip=>{
    chip.addEventListener('click',()=>{
      const cat=chip.getAttribute('data-chip');
      document.querySelectorAll('[data-chip]').forEach(c=>c.classList.toggle('active',c===chip));
      document.querySelectorAll('[data-cat]').forEach(card=>{
        const cats=(card.getAttribute('data-cat')||'').split(/\s+/);
        const show=cat==='all'||cats.includes(cat);
        card.style.display=show?'':'none';
      });
    });
  });

  document.querySelectorAll('[data-complete]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const pts=btn.getAttribute('data-pts')||'50';
      const tick=document.querySelector('.lesson-list a.current .tick');
      if(tick){tick.classList.add('done');tick.textContent='✓';}
      const bar=document.querySelector('[data-progress]');
      if(bar){
        const n=Math.min(100,parseInt(bar.getAttribute('data-progress'),10)+12);
        bar.setAttribute('data-progress',n);
        bar.style.width=n+'%';
        const lbl=document.querySelector('[data-progress-label]');
        if(lbl) lbl.textContent=n+'%';
      }
      btn.disabled=true;
      btn.textContent='เรียนจบแล้ว ✓';
      showToast('+'+pts+' XP  · บทเรียนเสร็จแล้ว!');
    });
  });

  document.querySelectorAll('[data-quiz]').forEach(q=>{
    q.querySelectorAll('.quiz-opt').forEach(opt=>{
      opt.addEventListener('click',()=>{
        if(q.dataset.done) return;
        q.dataset.done='1';
        const ok=opt.hasAttribute('data-ok');
        opt.classList.add(ok?'correct':'wrong');
        if(!ok){
          const right=q.querySelector('[data-ok]');
          if(right) right.classList.add('correct');
        }
        showToast(ok?'ถูกต้อง! +30 XP':'ยังไม่ถูก — ดูเฉลยแล้ว +10 XP');
      });
    });
  });

  const form=document.getElementById('reg-form');
  if(form){
    form.addEventListener('submit',e=>{
      e.preventDefault();
      const src=form.querySelector('[name="source"]');
      if(src && !src.value){src.focus();showToast('กรุณาเลือก “รู้จักเราจากที่ไหน”');return;}
      location.href='thankyou.html';
    });
  }
})();

/* === coming-soon playful === */
(function(){
  if(!document.body.classList.contains('page-soon')) return;
  const lang = document.body.getAttribute('data-soon-lang') || 'th';
  const lines = lang === 'en' ? [
    'Loading 99%… that last 1% is the boss fight',
    'Mushroom asked for “just one more snack quest.” Suspicious.',
    'Achievement unlocked: Curious Visitor — you arrived before the mushroom',
    'Please stand by. XP is buffering… emotionally.',
    'Don’t refresh — it might drop back to 98% out of spite',
    'Courses & events are AFK. Mushroom is “optimizing vibes.”'
  ] : [
    'เห็ดขอแอบเก็บเลเวลแป๊บ อย่าเพิ่งเร่งนะ เดี๋ยวโกรธ',
    'Loading 99%… อีก 1% นี่แหละยากสุด',
    'Achievement unlocked: คนขี้สงสัย — คุณมาเร็วกว่าเห็ด',
    'ระบบกำลังฟาร์ม XP… ค้างที่ 99% (สงสัยแอบทำเควสต์ของว่าง)',
    'อย่าเพิ่งรีเฟรช เดี๋ยวหลุดเซฟแล้วหล่นเหลือ 98%',
    'คอร์สกับกิจกรรมขอ AFK ก่อน เห็ดกำลัง “ปรับบาลานซ์ความน่ารัก”'
  ];
  const pokeLines = lang === 'en' ? [
    'Hey! Soft poke only. Cap is delicate.',
    'Squish logged. XP still 99%. Rude.',
    'Stop speedrunning my level-up!',
    'That tickles. Still not 100% though.'
  ] : [
    'เฮ้ย! จิ้มเบา ๆ หน่อย หมวกยังอ่อนอยู่',
    'บันทึกการบีบแล้ว — XP ยัง 99% อยู่ดี ใจร้าย',
    'อย่าสปีดรันเลเวลของเห็ดสิ!',
    'คันนะ… แต่ยังไม่ครบ 100% อยู่ดี'
  ];
  const lineEl = document.getElementById('soon-line');
  const mush = document.getElementById('soon-mush');
  const xp = document.getElementById('soon-xp');
  const pct = document.getElementById('soon-pct');
  let i = 0;
  function setLine(text, markDraft){
    if(!lineEl) return;
    lineEl.classList.add('is-swap');
    setTimeout(()=>{
      lineEl.innerHTML = text + (markDraft ? ' <span class="draft-note">'+(lang==='en'?'draft':'ร่าง')+'</span>' : '');
      lineEl.classList.remove('is-swap');
    }, 180);
  }
  setInterval(()=>{
    i = (i+1) % lines.length;
    setLine(lines[i], true);
    if(xp){
      xp.classList.remove('jiggle');
      void xp.offsetWidth;
      xp.classList.add('jiggle');
    }
    if(pct){
      pct.textContent = '99%';
      setTimeout(()=>{ pct.textContent = '99.9%?'; }, 180);
      setTimeout(()=>{ pct.textContent = '99%'; }, 520);
    }
  }, 4200);

  // visit achievement toast
  function showAchieve(tag, msg){
    let el = document.getElementById('gg-achieve');
    if(!el){
      el = document.createElement('div');
      el.id = 'gg-achieve';
      el.className = 'gg-achieve';
      el.innerHTML = '<span class="tag"></span><span class="msg"></span>';
      document.body.appendChild(el);
    }
    el.querySelector('.tag').textContent = tag;
    el.querySelector('.msg').textContent = msg;
    el.classList.add('show');
    setTimeout(()=>el.classList.remove('show'), 3200);
  }
  setTimeout(()=>{
    if(lang==='en') showAchieve('ACHIEVEMENT UNLOCKED', 'Curious Visitor — you showed up before the mushroom finished grinding.');
    else showAchieve('ACHIEVEMENT UNLOCKED', 'คนขี้สงสัย — คุณมาเร็วกว่าเห็ดที่ยังค้าง 99%');
  }, 700);

  let poke = 0;
  if(mush){
    mush.addEventListener('click', ()=>{
      poke++;
      mush.classList.remove('squish','jump');
      void mush.offsetWidth;
      mush.classList.add(poke % 2 ? 'squish' : 'jump');
      setLine(pokeLines[poke % pokeLines.length], true);
      if(xp){ xp.classList.remove('jiggle'); void xp.offsetWidth; xp.classList.add('jiggle'); }
      if(poke === 3){
        if(lang==='en') showAchieve('SECRET POKE', 'Mushroom respect +1. Courses still loading.');
        else showAchieve('อีสเตอร์เอ้ก', 'เห็ดให้หัวใจ +1 · คอร์สยังโหลดอยู่ดี');
      }
    });
  }
})();
