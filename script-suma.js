
  document.querySelectorAll('.zoom-img').forEach(img => {
    const panzoom = Panzoom(img, {
      maxScale: 5,
      minScale: 1,
      contain: 'outside'
    });
    img.parentElement.addEventListener('wheel', panzoom.zoomWithWheel);
  });


  document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.querySelector(".menu-toggle");
    const menu = document.querySelector(".menu-list");
    toggleBtn.addEventListener("click", () => {
      menu.classList.toggle("active");
    });
  });



  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener("click", function (e) {
        const targetId = this.getAttribute("href");
        if (targetId.length > 1 && document.querySelector(targetId)) {
          e.preventDefault();
          const target = document.querySelector(targetId);
          const startPosition = window.scrollY;
          const targetPosition = target.getBoundingClientRect().top;
          const startTime = performance.now();
          const duration = 400; // ← スクロール速度（ms）。値を小さくすれば速くなる

          function scrollStep(timestamp) {
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = progress < 0.5
              ? 2 * progress * progress
              : -1 + (4 - 2 * progress) * progress;
            window.scrollTo(0, startPosition + targetPosition * ease);
            if (elapsed < duration) {
              requestAnimationFrame(scrollStep);
            }
          }

          requestAnimationFrame(scrollStep);
        }
      });
    });
  });
window.addEventListener('DOMContentLoaded', () => {
  const images = document.querySelectorAll('img');

 function showProposalConfirm() {
  const form = document.getElementById('proposalForm');
  const confirmScreen = document.getElementById('proposalConfirmScreen');
  const confirmContent = document.getElementById('proposalConfirmContent');
  const confirmForm = document.getElementById('proposalConfirmForm');

  // 入力値を取得してオブジェクトに格納
  const formData = new FormData(form);
  let html = '';
  
  for (const [key, value] of formData.entries()) {
    html += `<p><strong>${key}：</strong><br>${String(value).replace(/\n/g, '<br>')}</p>`;
    if (confirmForm[key]) confirmForm[key].value = value;
  }

  // 画面切り替え
  confirmContent.innerHTML = html;
  form.style.display = 'none';
  confirmScreen.style.display = 'block';
}

function backToProposalForm() {
  document.getElementById('proposalForm').style.display = 'block';
  document.getElementById('proposalConfirmScreen').style.display = 'none';
} 
