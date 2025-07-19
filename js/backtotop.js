document.addEventListener("DOMContentLoaded", () => {
  const btn = document.createElement("button");
  btn.id = "backToTopBtn";
  btn.title = "Back to top";
  btn.textContent = "↑";
  document.body.appendChild(btn);

  const style = document.createElement("style");
  style.innerHTML = `
    #backToTopBtn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1000;
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 16px;
      font-size: 18px;
      border-radius: 50%;
      cursor: pointer;
      display: none;
      transition: opacity 0.3s;
    }
    #backToTopBtn:hover {
      background-color: #0056b3;
    }
  `;
  document.head.appendChild(style);

  window.addEventListener("scroll", () => {
    btn.style.display = window.scrollY > 300 ? "block" : "none";
  });

  btn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
});
