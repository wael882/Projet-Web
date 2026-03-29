const modal = document.getElementById('modal-edit-avis');

document.querySelectorAll('.btn-avis-edit').forEach(function (btn) {
  btn.addEventListener('click', function () {
    const id          = this.dataset.id;
    const note        = this.dataset.note;
    const commentaire = this.dataset.commentaire;

    document.getElementById('edit-id-evaluation').value = id;
    document.getElementById('edit-commentaire').value   = commentaire;
    const radio = document.getElementById('edit-star' + note);
    if (radio) radio.checked = true;
    modal.style.display = 'flex';
  });
});

document.getElementById('fermer-modal-edit')?.addEventListener('click', function () {
  modal.style.display = 'none';
});

modal?.addEventListener('click', function (e) {
  if (e.target === this) this.style.display = 'none';
});
