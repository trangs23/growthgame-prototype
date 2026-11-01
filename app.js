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
