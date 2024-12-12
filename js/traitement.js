document.getElementById('fg_form').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche la soumission normale du formulaire
    console.log("Formulaire soumis !") ;
    envoyerFormulaire();
});
function recupererFormulaire() {
    console.log("Fonction appelée !");
    // Récupérer les valeurs du formulaire
    var statut = document.getElementById('statut').value;
    var nom = document.getElementById('nom').value;
    var telephone = document.getElementById('telephone').value;
    var email = document.getElementById('email').value;
    var produit = document.getElementById('produit').value;
    var livraison = document.getElementById('livraison').value;


    // Vérification des champs requis
    var erreurs = {};
    var message_global="";

    if (!statut) {
        erreurs.statut = "Ce champ est obligatoire";
    }
    if (!email) {
        erreurs.email = "Ce champ est obligatoire";
    } else if (!validateEmail(email)) {
        erreurs.email = "Cet e-mail n'est pas valide";
    }

    if (!produit) {
        erreurs.produit = "Ce champ est obligatoire";
    }

    if (Object.keys(erreurs).length > 0) {
        // Afficher les erreurs
        document.getElementById('erreur_email').textContent = erreurs.email || '';
        document.getElementById('erreur_statut').textContent = erreurs.statut || '';
        document.getElementById('erreur_produit').textContent = erreurs.produit || '';
        document.getElementById("message_global").innerHTML = "Veuillez corriger les erreurs avant d\'envoyer ce formulaire";

        return; // Arrêter l'envoi du formulaire si erreurs
    }else{
        
    }

    function validateEmail(email) {
        var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }
       
    // Désactiver le bouton de soumission
    var boutonSubmit = document.querySelector('.fg_submit');
    boutonSubmit.disabled = true;
    // // boutonSubmit.value = "Envoi en cours...";
    
    // Créer un objet FormData avec les valeurs du formulaire

    var formData = new FormData();
    formData.append('email', email);
    formData.append('statut', statut);
    formData.append('produit', produit);
    formData.append('nom', nom);
    formData.append('telephone', telephone);
    formData.append('livraison', livraison);

    formData.append('action', 'submit'); // Identifiant pour l'action AJAX

    // Faire la requête AJAX avec fetch
    fetch(ajax_object.ajax_url, {  // ajaxurl est une variable définie dans WordPress pour gérer AJAX
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Gérer les erreurs
        if (data.erreurs) {
            document.getElementById('erreur_email').textContent = data.erreurs.email || '';
            document.getElementById('erreur_statut').textContent = data.erreurs.statut || '';
            document.getElementById('erreur_produit').textContent = data.erreurs.produit || '';
            document.getElementById('message_global').textContent = data.message_global || '';
            ajusterHauteurFormulaire();
        } else if (data.success) {
            // Afficher un message de succès
            document.getElementById('message_global').textContent = data.message_global || "Formulaire envoyé avec succès !";
            window.location.href = data.redirect_url;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('message_global').textContent = "Une erreur est survenue. Veuillez réessayer.";
    })
    .finally(() => {
        // Réactiver le bouton de soumission
        boutonSubmit.disabled = false;
        boutonSubmit.value = "DECOUVRIR NOS TARIFS";
    });
}
