document.getElementById('fg_form').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche la soumission normale du formulaire
    console.log("Formulaire soumis !");
    recupererFormulaire(); // Appeler la bonne fonction
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

         // Agrandir le formulaire
       var formContainer = document.querySelector('.fg_plugin');
       formContainer.classList.add('enlarged');
       document.getElementById("message_global").innerHTML = "Veuillez corriger les erreurs avant d\'envoyer ce formulaire";

        return; // Arrêter l'envoi du formulaire si erreurs
    }

    function validateEmail(email) {
        var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return regex.test(email);
    }
       
    // Désactiver le bouton de soumission
    var boutonSubmit = document.querySelector('.fg_submit');
    boutonSubmit.disabled = true;
    boutonSubmit.value = "en cours de traitement...";
    
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
            // Masquer tous les champs du formulaire
            var inputs = document.querySelectorAll('#fg_form input, #fg_form select, #fg_form .fg_submit');
            inputs.forEach(input => {
                input.style.display = 'none'; // Cacher chaque champ de formulaire
            });

            // faire revenir le formulaire à sa taille normale si elle a été agrandie à cause des messages d'érreurs
            var formContainer = document.querySelector('.fg_plugin');
            formContainer.classList.remove('enlarged');

            // Reduire la taille du formulaire lorsque le message de succès s'affiche
            var formContainer = document.querySelector('.fg_plugin'); 
            formContainer.classList.add('reduced'); // Ajouter la classe pour réduire la taille


            // Afficher le titre et le message de succès
            var titre = document.getElementById('titre_formulaire'); // Assurez-vous que le titre a cet ID
            titre.style.display = 'block'; // Afficher le titre
            document.getElementById('message_global').textContent = data.message_global || "Formulaire envoyé avec succès !";

            //Rédiriger l'utilisateur vers la page de validation de goliat après 1s
            setTimeout(() => {
                window.location.href = "https://www.goliat.fr/validation-form";
            }, 1000);
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

