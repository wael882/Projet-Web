const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", function () {
    navLinks.classList.toggle("active");
  });
}

document.querySelectorAll(".alert").forEach(function (alerte) {
  setTimeout(function () {
    alerte.classList.add("alerte-masquee");
    setTimeout(function () {
      alerte.remove();
    }, 500);
  }, 5000);
});
