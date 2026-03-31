const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", function () {
    navLinks.classList.toggle("active");
  });
}

document.querySelectorAll("[data-recherche-live]").forEach(function (champRecherche) {
  var delai;
  champRecherche.addEventListener("input", function () {
    clearTimeout(delai);
    delai = setTimeout(function () {
      var formulaire = champRecherche.closest("form");
      var params = new URLSearchParams(new FormData(formulaire));
      var url = (formulaire.action || window.location.pathname) + "?" + params.toString();

      history.replaceState(null, "", url);

      fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(function (reponse) { return reponse.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, "text/html");
          var nouvelleZone = doc.getElementById("zone-resultats");
          var zoneActuelle = document.getElementById("zone-resultats");
          if (nouvelleZone && zoneActuelle) {
            zoneActuelle.innerHTML = nouvelleZone.innerHTML;
          }
        });
    }, 400);
  });
});

document.querySelectorAll(".alert").forEach(function (alerte) {
  setTimeout(function () {
    alerte.classList.add("alerte-masquee");
    setTimeout(function () {
      alerte.remove();
    }, 500);
  }, 5000);
});
