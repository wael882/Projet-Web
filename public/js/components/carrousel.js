(function () {
  const piste        = document.getElementById('carrouselPiste');
  const indicateurs  = document.querySelectorAll('#carrouselIndicateurs .indicateur');
  const nombreCartes = indicateurs.length;
  let indexCourant   = 0;

  function allerALaCarte(index) {
    indexCourant = (index + nombreCartes) % nombreCartes;
    piste.style.transform = 'translateX(-' + (indexCourant * 100) + '%)';
    indicateurs.forEach((ind, i) => {
      ind.classList.toggle('indicateur-actif', i === indexCourant);
    });
  }

  document.querySelector('.carrousel-bouton-precedent').addEventListener('click', function () {
    allerALaCarte(indexCourant - 1);
  });

  document.querySelector('.carrousel-bouton-suivant').addEventListener('click', function () {
    allerALaCarte(indexCourant + 1);
  });

  indicateurs.forEach(function (ind) {
    ind.addEventListener('click', function () {
      allerALaCarte(parseInt(ind.dataset.index));
    });
  });
})();
