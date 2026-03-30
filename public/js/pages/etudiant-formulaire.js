(function () {
  const champPilote   = document.getElementById('champ-pilote');
  const idPiloteCache = document.getElementById('id-pilote-cache');
  const options       = document.querySelectorAll('#liste-pilotes option');

  champPilote.addEventListener('change', function () {
    const valeurSaisie = champPilote.value.trim();
    let idTrouve       = '';

    options.forEach(function (option) {
      if (option.value === valeurSaisie) {
        idTrouve = option.getAttribute('data-id') || '';
      }
    });

    idPiloteCache.value = idTrouve;
  });
})();
