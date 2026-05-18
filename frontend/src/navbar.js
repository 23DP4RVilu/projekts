const navbar = `
<nav>
  <ul>
    <li><a href="home.html">Home</a></li>
    <li><a href="lookup.html">Lookup</a></li>
    <li><a href="threads.html">Threads</a></li>

    <li class="dropdown">
      <a href="#" class="dropbtn">Language</a>
      <div class="dropdown-content">
        <a href="#">Latvian</a>
        <a href="#">English</a>
      </div>
    </li>

    <li class="dropdown">
      <a href="#" class="dropbtn">Theme</a>
      <div class="dropdown-content">
        <a href="#" data-theme="light">Light</a>
        <a href="#" data-theme="dark">Dark</a>
        <a href="#" data-theme="system">System Default</a>
      </div>
    </li>
  </ul>
</nav>
`;

document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector("header");
  if (header) header.innerHTML = navbar;
});

const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('nav ul');

hamburger.addEventListener('click', () => {
  navMenu.classList.toggle('show');
});