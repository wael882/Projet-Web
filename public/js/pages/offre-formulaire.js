// Résolution autocomplete entreprise → id
const cartEntreprises = {};
document.querySelectorAll('#liste-entreprises option').forEach(opt => {
  cartEntreprises[opt.value.toLowerCase()] = opt.dataset.id;
});

document.getElementById('saisie-entreprise').addEventListener('input', function () {
  const id = cartEntreprises[this.value.trim().toLowerCase()] ?? '';
  document.getElementById('id_entreprise').value = id;
});

document.querySelector('form').addEventListener('submit', function (e) {
  const id = document.getElementById('id_entreprise').value;
  if (!id) {
    e.preventDefault();
    document.getElementById('saisie-entreprise').setCustomValidity('Veuillez choisir une entreprise de la liste.');
    document.getElementById('saisie-entreprise').reportValidity();
  } else {
    document.getElementById('saisie-entreprise').setCustomValidity('');
  }
});

// Le conteneur diffère entre la création et la modification
const conteneurCompetences = document.getElementById('tags-competences')
  || document.getElementById('tags-nouvelles-competences');

function ajouterCompetence() {
  const saisie  = document.getElementById('saisie-competence');
  const libelle = saisie.value.trim();
  if (!libelle) return;

  const dejaSaisie = [...document.querySelectorAll('.tag-competence span')].map(s => s.textContent.toLowerCase());
  if (dejaSaisie.includes(libelle.toLowerCase())) {
    saisie.value = '';
    return;
  }

  const tag = document.createElement('span');
  tag.className = 'tag-competence';
  tag.innerHTML = `<span>${libelle}</span><input type="hidden" name="nouvelles_competences[]" value="${libelle}"><button type="button" onclick="this.parentElement.remove(); mettreAJourRecapitulatif();" title="Retirer">×</button>`;
  conteneurCompetences.appendChild(tag);
  saisie.value = '';
  saisie.focus();
  mettreAJourRecapitulatif();
}

function mettreAJourRecapitulatif() {
  const tags     = [...document.querySelectorAll('.tag-competence span')];
  const liste    = document.getElementById('recapitulatif-liste');
  const vide     = document.getElementById('recapitulatif-vide');
  const compteur = document.getElementById('compteur-competences');

  liste.innerHTML = '';
  compteur.textContent = tags.length;

  if (tags.length === 0) {
    vide.style.display = 'block';
  } else {
    vide.style.display = 'none';
    tags.forEach((tag, index) => {
      const ligne = document.createElement('div');
      ligne.className = 'recapitulatif-ligne';
      ligne.innerHTML = `<span class="recapitulatif-numero">${index + 1}</span><span class="recapitulatif-nom">${tag.textContent}</span>`;
      liste.appendChild(ligne);
    });
  }
}

document.getElementById('saisie-competence').addEventListener('keydown', function (e) {
  if (e.key === 'Enter') { e.preventDefault(); ajouterCompetence(); }
});

// Initialise le récapitulatif (utile sur la page de modification)
mettreAJourRecapitulatif();
