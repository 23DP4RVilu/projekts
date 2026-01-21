function applyTheme(theme) {
  const body = document.body;
  body.classList.remove("light", "dark");

  if (theme === "system") {
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    body.classList.add(prefersDark ? "dark" : "light");
  } else {
    body.classList.add(theme);
  }
}

function setTheme(theme) {
  localStorage.setItem("theme", theme);
  applyTheme(theme);
}

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "system";
  applyTheme(savedTheme);

  document.body.addEventListener("click", (e) => {
    if (e.target.matches("[data-theme]")) {
      e.preventDefault();
      const theme = e.target.getAttribute("data-theme");
      setTheme(theme);
    }
  });
});

// === MODAL LOGIC ===
document.querySelectorAll('.learn-more-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const modal = document.getElementById(btn.dataset.modal);
    modal.style.display = 'flex';
  });
});

document.querySelectorAll('.close').forEach(close => {
  close.addEventListener('click', () => {
    close.closest('.modal').style.display = 'none';
  });
});

window.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal')) {
    e.target.style.display = 'none';
  }
});