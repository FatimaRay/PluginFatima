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
    var gclid = document.getElementById('gclid').value;


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
    formData.append('gclid', gclid);

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


// Après la soumission et si tout est valide, rediriger vers l'URL personnalisée
             setTimeout(() => {
                // Récupérer les valeurs des champs
                const email = document.getElementById('email').value;
                const tel = document.getElementById('telephone').value;
                const Livraison = document.getElementById('livraison').value;
                const gclid=document.getElementById('gclid').value;

                // Construire l'URL de redirection avec les paramètres
                const redirectionUrl = `https://www.goliat.fr/validation-form/?email=${encodeURIComponent(email)}&tel=${encodeURIComponent(tel)}&Lieu-livraison=${encodeURIComponent(livraison)}&gclid=${encodeURIComponent(gclid)}`;   
                // Rediriger l'utilisateur
                window.location.href = redirectionUrl;
            }, 500); // Redirection après 500ms ou ajustez selon votre besoin

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

// Fonction pour valider tous les champs
function validerChamps(statut, email, produit) {
    let erreurs = {};
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
    return erreurs;
}
//gestion de la disparition des messages d'érreur lorsque l'utilisateur corrige les valeurs du champs
// Validation des champs lors de la modification
const inputs = document.querySelectorAll('#fg_form input, #fg_form select');
inputs.forEach(input => {
    input.addEventListener('input', function() {
        const errorDiv = document.querySelector(`#erreur_${input.name}`);
        if (errorDiv) {
            errorDiv.textContent = ''; // Réinitialiser le message d'erreur spécifique
        }

        // Vérifier si tous les champs sont valides avant de masquer le message global
        const erreurs = validerChamps(  // Utiliser ta fonction de validation avec les valeurs courantes
            document.getElementById('statut').value,
            document.getElementById('email').value,
            document.getElementById('produit').value
        );

        // Si toutes les erreurs sont corrigées, réinitialiser le message global
        if (Object.keys(erreurs).length === 0) {
            document.getElementById("message_global").textContent = ''; // Masquer le message global d'erreur
        }
    });
});
// Remplissage automatique du champs caché avec la valeur du paramètre gclid sur le navigateur si il existe
// Attendre que le DOM soit complètement chargé
document.addEventListener("DOMContentLoaded", function() {
    // Récupérer les paramètres de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    
    // Vérifier si le paramètre "gclid" existe dans l'URL
    const gclid = urlParams.get('gclid'); // Récupère "gclid" dans l'URL
    
    // Si le paramètre existe, le mettre dans le champ caché
    if (gclid) {
        document.querySelector('input[name="gclid"]').value = gclid; // Met la valeur dans le champ
        console.log("Valeur insérée dans le champ caché :", gclid);
    } else {
        console.log("Paramètre gclid non trouvé dans l'URL.");
    }
    
    // Afficher la valeur actuelle du champ caché pour vérification
    console.log("Valeur actuelle du champ caché :", document.querySelector('input[name="gclid"]').value);
});





