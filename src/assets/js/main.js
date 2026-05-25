// Scroll reveal
const io = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
},{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

// 3D tilt on mouse move
document.querySelectorAll('[data-tilt]').forEach(card=>{
  const inner = card.querySelector('.tilt-inner') || card;
  card.addEventListener('mousemove',(e)=>{
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left)/r.width - .5;
    const y = (e.clientY - r.top)/r.height - .5;
    inner.style.transform = `perspective(1000px) rotateY(${x*12}deg) rotateX(${-y*12}deg) translateZ(10px)`;
  });
  card.addEventListener('mouseleave',()=>{ inner.style.transform=''; });
});

// Demo: chat send
const ci = document.getElementById('chatInput');
if(ci){
  ci.addEventListener('keydown',(e)=>{
    if(e.key==='Enter' && ci.value.trim()){
      const body=document.getElementById('chatBody');
      const b=document.createElement('div');
      b.className='bubble me'; b.textContent=ci.value;
      body.appendChild(b); ci.value=''; body.scrollTop=body.scrollHeight;
    }
  });
}
